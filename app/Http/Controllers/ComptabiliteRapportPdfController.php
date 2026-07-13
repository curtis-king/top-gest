<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculeComptabilite;
use App\Models\CompteComptable;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ComptabiliteRapportPdfController extends Controller
{
    use CalculeComptabilite;

    public function balance(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $agenceId = $this->resolveAgenceFilter($request);

        $soldes = $this->soldesParCompte($dateDebut, $dateFin, $agenceId);
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get();

        $lignes = $comptes->map(function ($compte) use ($soldes) {
            $s = $soldes->get($compte->id, ['debit' => 0, 'credit' => 0]);
            $debit = (float) $s['debit'];
            $credit = (float) $s['credit'];
            $solde = $compte->sens_normal?->value === 'debit' ? $debit - $credit : $credit - $debit;

            return ['compte' => $compte, 'total_debit' => $debit, 'total_credit' => $credit, 'solde' => $solde];
        })->filter(fn($l) => $l['total_debit'] != 0 || $l['total_credit'] != 0)->values();

        $totalDebit = $lignes->sum('total_debit');
        $totalCredit = $lignes->sum('total_credit');

        $pdf = Pdf::loadView('comptabilite.pdf.balance', compact('lignes', 'totalDebit', 'totalCredit', 'dateDebut', 'dateFin'));
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream('balance_des_comptes.pdf');
    }

    public function bilan(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$actif, $passif, $resultat] = $this->calculerBilan($dateDebut, $dateFin, $agenceId);
        $totalActif = $actif->sum('solde');
        $totalPassif = $passif->sum('solde') + $resultat;

        $pdf = Pdf::loadView('comptabilite.pdf.bilan', compact('actif', 'passif', 'resultat', 'totalActif', 'totalPassif', 'dateDebut', 'dateFin'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('bilan.pdf');
    }

    public function resultat(Request $request): Response
    {
        $dateDebut = $request->input('date_debut') ?: now()->startOfYear()->format('Y-m-d');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$charges, $produits, $totalCharges, $totalProduits, $resultatNet] = $this->calculerResultat($dateDebut, $dateFin, $agenceId);

        $pdf = Pdf::loadView('comptabilite.pdf.resultat', compact('charges', 'produits', 'totalCharges', 'totalProduits', 'resultatNet', 'dateDebut', 'dateFin'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream('compte_de_resultat.pdf');
    }
}
