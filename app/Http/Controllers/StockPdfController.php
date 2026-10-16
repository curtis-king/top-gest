<?php

namespace App\Http\Controllers;

use App\Enums\TypeMouvement;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class StockPdfController extends Controller
{
    public function bonMouvement(MouvementStock $mouvement): Response
    {
        $mouvement->load(['produit', 'depot', 'depotDestination', 'contact', 'facture', 'user']);

        $pdf = Pdf::loadView('stocks.pdf.bon-mouvement', compact('mouvement'));
        $pdf->setPaper('A4', 'portrait');

        $nom = "bon_mouvement_{$mouvement->id}_{$mouvement->date_mouvement->format('Ymd')}.pdf";
        return $pdf->stream($nom);
    }

    public function etatStock(Request $request): Response
    {
        $depotIds = null;
        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $depotIds = Depot::where('agence_id', $userEmp->agence_id)->pluck('id');
            }
        }

        $query = Stock::with(['produit.categorie', 'depot'])
            ->when($depotIds, fn($q) => $q->whereIn('depot_id', $depotIds))
            ->when($request->depot_id, fn($q) => $q->where('depot_id', $request->depot_id))
            ->when($request->produit_id, fn($q) => $q->where('produit_id', $request->produit_id))
            ->when($request->alerte, fn($q) => $q->whereHas('produit', fn($p) => $p->whereColumn('stocks.quantite', '<=', 'produits.stock_min')))
            ->orderBy('depot_id')->orderBy('produit_id')
            ->get();

        $valeurTotale = $query->sum(fn($s) => $s->quantite * $s->produit->prix_achat);
        $dateEtat = now();

        $pdf = Pdf::loadView('stocks.pdf.etat-stock', compact('query', 'valeurTotale', 'dateEtat'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("etat_stock_{$dateEtat->format('Ymd_His')}.pdf");
    }

    public function rapportMouvements(Request $request): Response
    {
        $depotIds = null;
        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $depotIds = Depot::where('agence_id', $userEmp->agence_id)->pluck('id');
            }
        }

        $query = MouvementStock::with(['produit', 'depot', 'depotDestination', 'contact', 'user'])
            ->when($depotIds, fn($q) => $q->whereHas('depot', fn($d) => $d->whereIn('id', $depotIds)))
            ->when($request->type_mouvement, fn($q) => $q->where('type_mouvement', $request->type_mouvement))
            ->when($request->produit_id, fn($q) => $q->where('produit_id', $request->produit_id))
            ->when($request->depot_id, fn($q) => $q->where('depot_id', $request->depot_id))
            ->when($request->date_debut, fn($q) => $q->where('date_mouvement', '>=', $request->date_debut))
            ->when($request->date_fin, fn($q) => $q->where('date_mouvement', '<=', $request->date_fin))
            ->orderBy('date_mouvement', 'desc')
            ->get();

        $totaux = [
            TypeMouvement::Entree->value    => $query->where('type_mouvement', TypeMouvement::Entree)->sum('quantite'),
            TypeMouvement::Sortie->value    => $query->where('type_mouvement', TypeMouvement::Sortie)->sum('quantite'),
            TypeMouvement::Transfert->value => $query->where('type_mouvement', TypeMouvement::Transfert)->sum('quantite'),
        ];

        $dateGeneration = now();
        $filtres = array_filter([
            'Type'    => $request->type_mouvement,
            'Du'      => $request->date_debut,
            'Au'      => $request->date_fin,
        ]);

        $pdf = Pdf::loadView('stocks.pdf.rapport-mouvements', compact('query', 'totaux', 'dateGeneration', 'filtres'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("rapport_mouvements_{$dateGeneration->format('Ymd_His')}.pdf");
    }
}
