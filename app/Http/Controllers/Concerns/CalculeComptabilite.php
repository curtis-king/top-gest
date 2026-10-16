<?php

namespace App\Http\Controllers\Concerns;

use App\Enums\StatutEcriture;
use App\Models\CompteComptable;
use App\Models\LigneEcriture;
use App\Support\AgencyContext;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

trait CalculeComptabilite
{
    protected function calculerBilan(?string $dateDebut, ?string $dateFin, ?int $agenceId): array
    {
        $soldes = $this->soldesParCompte($dateDebut, $dateFin, $agenceId);
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get();

        $actif = collect();
        $passif = collect();
        $totalCharges = 0.0;
        $totalProduits = 0.0;

        foreach ($comptes as $compte) {
            $s = $soldes->get($compte->id);
            if (!$s) {
                continue;
            }
            $debit = (float) $s['debit'];
            $credit = (float) $s['credit'];
            $typeValue = $compte->type_compte?->value;

            if (in_array($typeValue, ['actif', 'tresorerie'])) {
                $solde = $debit - $credit;
                if ($solde != 0) {
                    $actif->push(['compte' => $compte, 'solde' => $solde]);
                }
            } elseif (in_array($typeValue, ['passif', 'capitaux'])) {
                $solde = $credit - $debit;
                if ($solde != 0) {
                    $passif->push(['compte' => $compte, 'solde' => $solde]);
                }
            } elseif ($typeValue === 'charge') {
                $totalCharges += ($debit - $credit);
            } elseif ($typeValue === 'produit') {
                $totalProduits += ($credit - $debit);
            }
        }

        $resultat = $totalProduits - $totalCharges;

        return [$actif, $passif, $resultat];
    }

    protected function calculerResultat(?string $dateDebut, ?string $dateFin, ?int $agenceId): array
    {
        $soldes = $this->soldesParCompte($dateDebut, $dateFin, $agenceId);
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get();

        $charges = collect();
        $produits = collect();

        foreach ($comptes as $compte) {
            $s = $soldes->get($compte->id);
            if (!$s) {
                continue;
            }
            $debit = (float) $s['debit'];
            $credit = (float) $s['credit'];
            $typeValue = $compte->type_compte?->value;

            if ($typeValue === 'charge' && ($debit - $credit) != 0) {
                $charges->push(['compte' => $compte, 'montant' => $debit - $credit]);
            } elseif ($typeValue === 'produit' && ($credit - $debit) != 0) {
                $produits->push(['compte' => $compte, 'montant' => $credit - $debit]);
            }
        }

        $totalCharges = $charges->sum('montant');
        $totalProduits = $produits->sum('montant');
        $resultatNet = $totalProduits - $totalCharges;

        return [$charges, $produits, $totalCharges, $totalProduits, $resultatNet];
    }

    protected function soldesParCompte(?string $dateDebut, ?string $dateFin, ?int $agenceId): Collection
    {
        $query = LigneEcriture::query()
            ->join('ecritures_comptables', 'ecritures_comptables.id', '=', 'lignes_ecritures.ecriture_comptable_id')
            ->where('ecritures_comptables.statut', StatutEcriture::Validee->value);

        if ($dateDebut) {
            $query->whereDate('ecritures_comptables.date_ecriture', '>=', $dateDebut);
        }
        if ($dateFin) {
            $query->whereDate('ecritures_comptables.date_ecriture', '<=', $dateFin);
        }
        if ($agenceId) {
            $query->where('ecritures_comptables.agence_id', $agenceId);
        }

        return $query->selectRaw('lignes_ecritures.compte_comptable_id as compte_id, SUM(lignes_ecritures.debit) as debit, SUM(lignes_ecritures.credit) as credit')
            ->groupBy('lignes_ecritures.compte_comptable_id')
            ->get()
            ->keyBy('compte_id')
            ->map(fn($row) => ['debit' => (float) $row->debit, 'credit' => (float) $row->credit]);
    }

    protected function resolveAgenceFilter(Request $request): ?int
    {
        // Un utilisateur d'agence ne voit que la sienne, quel que soit le paramètre transmis.
        if (AgencyContext::isRestricted()) {
            return AgencyContext::agenceId();
        }

        return $request->filled('agence_id') ? (int) $request->input('agence_id') : null;
    }
    /**
     * Mouvements validés d'un compte sur la période, avec solde cumulé (sens normal du compte).
     */
    protected function lignesGrandLivre(CompteComptable $compte, ?string $dateDebut, ?string $dateFin, ?int $agenceId): Collection
    {
        $soldeCumule = 0;

        return LigneEcriture::with(['ecriture.journal', 'contact'])
            ->where('compte_comptable_id', $compte->id)
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
            ->sortBy(fn ($l) => $l->ecriture->date_ecriture?->format('Y-m-d') . sprintf('-%010d-%010d', $l->ecriture_comptable_id, $l->id))
            ->values()
            ->map(function ($ligne) use (&$soldeCumule, $compte) {
                $soldeCumule += $compte->sens_normal?->value === 'debit'
                    ? (float) $ligne->debit - (float) $ligne->credit
                    : (float) $ligne->credit - (float) $ligne->debit;
                $ligne->solde_cumule = $soldeCumule;

                return $ligne;
            });
    }
}
