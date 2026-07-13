<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\CalculeComptabilite;
use App\Models\Agence;
use App\Models\CompteComptable;
use App\Models\LigneEcriture;
use App\Enums\StatutEcriture;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComptabiliteRapportController extends Controller
{
    use CalculeComptabilite;

    public function grandLivre(Request $request): View
    {
        $compteId = $request->input('compte_comptable_id');
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $agenceId = $this->resolveAgenceFilter($request);

        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get(['id', 'numero_compte', 'libelle', 'sens_normal']);
        $compte = null;
        $lignes = collect();
        $soldeCumule = 0;

        if ($compteId) {
            $compte = CompteComptable::findOrFail($compteId);

            $lignes = LigneEcriture::with(['ecriture.journal', 'contact'])
                ->where('compte_comptable_id', $compteId)
                ->whereHas('ecriture', function ($q) use ($dateDebut, $dateFin, $agenceId) {
                    $q->where('statut', StatutEcriture::Validee->value);
                    if ($dateDebut) {
                        $q->whereDate('date_ecriture', '>=', $dateDebut);
                    }
                    if ($dateFin) {
                        $q->whereDate('date_ecriture', '<=', $dateFin);
                    }
                    if ($agenceId) {
                        $q->where('agence_id', $agenceId);
                    }
                })
                ->get()
                ->sortBy(fn($l) => $l->ecriture->date_ecriture)
                ->values()
                ->map(function ($ligne) use (&$soldeCumule, $compte) {
                    $mouvement = $compte->sens_normal?->value === 'debit'
                        ? (float) $ligne->debit - (float) $ligne->credit
                        : (float) $ligne->credit - (float) $ligne->debit;
                    $soldeCumule += $mouvement;
                    $ligne->solde_cumule = $soldeCumule;

                    return $ligne;
                });
        }

        $agences = Agence::pluck('name_agence', 'id');

        return view('comptabilite.grand-livre', compact('comptes', 'compte', 'lignes', 'dateDebut', 'dateFin', 'agences'));
    }

    public function balance(Request $request): View
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
        $agences = Agence::pluck('name_agence', 'id');

        return view('comptabilite.balance', compact('lignes', 'totalDebit', 'totalCredit', 'dateDebut', 'dateFin', 'agences'));
    }

    public function bilan(Request $request): View
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$actif, $passif, $resultat] = $this->calculerBilan($dateDebut, $dateFin, $agenceId);

        $totalActif = $actif->sum('solde');
        $totalPassif = $passif->sum('solde') + $resultat;
        $agences = Agence::pluck('name_agence', 'id');

        return view('comptabilite.bilan', compact('actif', 'passif', 'resultat', 'totalActif', 'totalPassif', 'dateDebut', 'dateFin', 'agences'));
    }

    public function resultat(Request $request): View
    {
        $dateDebut = $request->input('date_debut') ?: now()->startOfYear()->format('Y-m-d');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$charges, $produits, $totalCharges, $totalProduits, $resultatNet] = $this->calculerResultat($dateDebut, $dateFin, $agenceId);
        $agences = Agence::pluck('name_agence', 'id');

        return view('comptabilite.resultat', compact('charges', 'produits', 'totalCharges', 'totalProduits', 'resultatNet', 'dateDebut', 'dateFin', 'agences'));
    }
}
