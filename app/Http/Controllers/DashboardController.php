<?php

namespace App\Http\Controllers;

use App\Enums\StatutFacture;
use App\Enums\StatusProject;
use App\Enums\TypeContact;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\Produit;
use App\Models\Project;
use App\Models\MouvementStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    public function index()
    {
        Carbon::setLocale('fr');

        $clientsCount = Contact::where('type_contact', TypeContact::Client)->count();

        $revenuMois = DB::table('factures')
            ->join('item_factures', 'factures.id', '=', 'item_factures.facture_id')
            ->where('factures.statut_facture', 'payee')
            ->whereMonth('factures.date_facture', now()->month)
            ->whereYear('factures.date_facture', now()->year)
            ->select(DB::raw('COALESCE(SUM(item_factures.quantite * item_factures.prix_unitaire), 0) as total'))
            ->value('total');

        $projetsActifs = Project::whereIn('status_project', [
            StatusProject::Actif->value,
            StatusProject::EnCours->value,
        ])->count();

        $produits = Produit::with('stocks')->get();
        $produitsEnAlerte = $produits->filter(fn($p) => $p->en_alerte)->count();

        $employesCount = Employee::count();
        $fournisseursCount = Contact::where('type_contact', TypeContact::Fournisseur)->count();

        $revenusMensuels = DB::table('factures')
            ->join('item_factures', 'factures.id', '=', 'item_factures.facture_id')
            ->where('factures.statut_facture', 'payee')
            ->whereYear('factures.date_facture', now()->year)
            ->select(
                DB::raw('MONTH(factures.date_facture) as mois'),
                DB::raw('SUM(item_factures.quantite * item_factures.prix_unitaire) as total')
            )
            ->groupBy(DB::raw('MONTH(factures.date_facture)'))
            ->orderBy('mois')
            ->pluck('total', 'mois');

        $revenusLabels = [];
        $revenusData = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthName = now()->month($m)->translatedFormat('M');
            $revenusLabels[] = $monthName;
            $revenusData[] = (float) ($revenusMensuels[$m] ?? 0);
        }

        $projectStatuses = Project::selectRaw('status_project, count(*) as total')
            ->groupBy('status_project')
            ->pluck('total', 'status_project');

        $projectLabels = [
            'actif' => 'Actif',
            'en_cours' => 'En cours',
            'termine' => 'Terminé',
            'annule' => 'Annulé',
        ];
        $projectColors = [
            'actif' => '#4ade80',
            'en_cours' => '#60a5fa',
            'termine' => '#a78bfa',
            'annule' => '#f87171',
        ];

        $factureStatuses = Facture::selectRaw('statut_facture, count(*) as total')
            ->groupBy('statut_facture')
            ->pluck('total', 'statut_facture');

        $factureLabels = [
            'brouillon' => 'Brouillon',
            'impayee' => 'Impayée',
            'partielle' => 'Partielle',
            'payee' => 'Payée',
            'annulee' => 'Annulée',
        ];
        $factureColors = [
            'brouillon' => '#94a3b8',
            'impayee' => '#f87171',
            'partielle' => '#facc15',
            'payee' => '#4ade80',
            'annulee' => '#64748b',
        ];

        $recentFactures = Facture::with('contact')->latest()->take(5)->get();
        $recentProjects = Project::latest()->take(5)->get();
        $recentMouvements = MouvementStock::with('produit', 'depot')->latest()->take(5)->get();

        $tauxChange = Cache::remember('taux_change_xaf', now()->addHours(6), function () {
            try {
                $response = Http::timeout(5)->get('https://open.er-api.com/v6/latest/XAF');
                if ($response->successful()) {
                    $rates = $response->json('rates');
                    return [
                        'EUR' => $rates['EUR'] ?? null,
                        'USD' => $rates['USD'] ?? null,
                        'GBP' => $rates['GBP'] ?? null,
                        'CAD' => $rates['CAD'] ?? null,
                        'CHF' => $rates['CHF'] ?? null,
                        'NGN' => $rates['NGN'] ?? null,
                        'date' => $response->json('time_last_update_utc') ?? now()->format('d/m/Y'),
                    ];
                }
                return null;
            } catch (\Exception $e) {
                return null;
            }
        });

        return view('dashboard', compact(
            'clientsCount', 'revenuMois', 'projetsActifs', 'produitsEnAlerte',
            'employesCount', 'fournisseursCount',
            'revenusLabels', 'revenusData',
            'projectStatuses', 'projectLabels', 'projectColors',
            'factureStatuses', 'factureLabels', 'factureColors',
            'recentFactures', 'recentProjects', 'recentMouvements',
            'tauxChange'
        ));
    }
}
