<?php

namespace App\Http\Controllers;

use App\Enums\TypeMouvement;
use Carbon\Carbon;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StockController extends Controller
{
    public function dashboard(): View
    {
        Carbon::setLocale('fr');

        $depotIds = null;
        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $depotIds = Depot::where('agence_id', $userEmp->agence_id)->pluck('id');
            }
        }

        $stockQ  = fn() => Stock::query()->when($depotIds, fn($q) => $q->whereIn('depot_id', $depotIds));
        $mouvQ   = fn() => MouvementStock::query()->when($depotIds, fn($q) => $q->whereHas('depot', fn($d) => $d->whereIn('id', $depotIds)));

        // Cartes de synthèse
        $valeurTotale = $stockQ()
            ->join('produits', 'stocks.produit_id', '=', 'produits.id')
            ->sum(DB::raw('stocks.quantite * produits.prix_achat'));

        $allStocks   = $stockQ()->with('produit')->get();
        $nbRupture   = $allStocks->filter(fn($s) => $s->quantite <= 0)->count();
        $nbAlerte    = $allStocks->filter(fn($s) => $s->quantite > 0 && $s->produit->stock_min > 0 && $s->quantite <= $s->produit->stock_min)->count();
        $nbNormaux   = $allStocks->count() - $nbRupture - $nbAlerte;
        $nbMouvMois  = $mouvQ()->whereMonth('date_mouvement', now()->month)->whereYear('date_mouvement', now()->year)->count();

        // Graphique entrées vs sorties — 6 derniers mois (2 requêtes)
        $sixMoisAgo = now()->subMonths(5)->startOfMonth();
        $rawE = $mouvQ()->where('type_mouvement', TypeMouvement::Entree->value)->where('date_mouvement', '>=', $sixMoisAgo)
            ->selectRaw('MONTH(date_mouvement) as mois, YEAR(date_mouvement) as annee, SUM(quantite) as total')
            ->groupBy('annee', 'mois')->get()->keyBy(fn($r) => "{$r->annee}-{$r->mois}");
        $rawS = $mouvQ()->where('type_mouvement', TypeMouvement::Sortie->value)->where('date_mouvement', '>=', $sixMoisAgo)
            ->selectRaw('MONTH(date_mouvement) as mois, YEAR(date_mouvement) as annee, SUM(quantite) as total')
            ->groupBy('annee', 'mois')->get()->keyBy(fn($r) => "{$r->annee}-{$r->mois}");

        $labels = $dataEntrees = $dataSorties = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->subMonths($i);
            $k = "{$d->year}-{$d->month}";
            $labels[]      = $d->translatedFormat('M Y');
            $dataEntrees[] = (int) ($rawE[$k]->total ?? 0);
            $dataSorties[] = (int) ($rawS[$k]->total ?? 0);
        }

        // Produits en alerte (top 5 les plus critiques)
        $produitsEnAlerte = $stockQ()->with(['produit.categorie', 'depot'])
            ->whereHas('produit', fn($q) => $q->where('stock_min', '>', 0)->whereColumn('stocks.quantite', '<=', 'produits.stock_min'))
            ->orderBy('quantite')
            ->take(5)->get();

        // Top 5 produits mouvementés ce mois
        $topProduits = $mouvQ()->with('produit')
            ->whereMonth('date_mouvement', now()->month)->whereYear('date_mouvement', now()->year)
            ->selectRaw('produit_id, COUNT(*) as nb, SUM(quantite) as total_qte')
            ->groupBy('produit_id')->orderByDesc('nb')->take(5)->get();

        // Valeur par dépôt
        $valeurParDepot = $stockQ()
            ->join('produits', 'stocks.produit_id', '=', 'produits.id')
            ->join('depots', 'stocks.depot_id', '=', 'depots.id')
            ->selectRaw('depots.nom, SUM(stocks.quantite * produits.prix_achat) as valeur')
            ->groupBy('depots.id', 'depots.nom')
            ->orderByDesc('valeur')->get();

        // Derniers mouvements
        $derniersMovts = $mouvQ()->with(['produit', 'depot', 'user'])->latest('date_mouvement')->take(8)->get();

        return view('stocks.dashboard', compact(
            'valeurTotale', 'nbRupture', 'nbAlerte', 'nbNormaux', 'nbMouvMois',
            'labels', 'dataEntrees', 'dataSorties',
            'produitsEnAlerte', 'topProduits', 'valeurParDepot', 'derniersMovts'
        ));
    }

    public function index(Request $request): View
    {
        $depotQuery = Depot::with('agence');

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $depotQuery->where('agence_id', $userEmp->agence_id);
            }
        }

        $depots = $depotQuery->orderBy('nom')->get();

        $stockQuery = Stock::with(['produit.categorie', 'depot']);

        if ($depot_id = $request->input('depot_id')) {
            $stockQuery->where('depot_id', $depot_id);
        } elseif (!auth()->user()->isAdmin()) {
            $stockQuery->whereIn('depot_id', $depots->pluck('id'));
        }

        if ($produit_id = $request->input('produit_id')) {
            $stockQuery->where('produit_id', $produit_id);
        }

        if ($request->boolean('alerte')) {
            $stockQuery->whereHas('produit', fn($q) => $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min'));
        }

        $stocks = $stockQuery->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();
        $produits = Produit::pluck('nom', 'id');

        return view('stocks.index', compact('stocks', 'depots', 'produits'));
    }
}
