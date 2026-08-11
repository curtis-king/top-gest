<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Prime;
use App\Models\Retenu;

class PaieCalculatorService
{
    private const MOIS = [
        'Janvier' => 1, 'Février' => 2, 'Mars' => 3, 'Avril' => 4,
        'Mai' => 5, 'Juin' => 6, 'Juillet' => 7, 'Août' => 8,
        'Septembre' => 9, 'Octobre' => 10, 'Novembre' => 11, 'Décembre' => 12,
    ];

    /**
     * Recalcule salaire_base/total_primes/total_retenus/net_a_payer à partir des
     * données réelles (Fonction, Prime, Retenu) — jamais des valeurs soumises par le
     * client, pour que le bulletin ne puisse pas diverger de ce qui est réellement
     * enregistré pour l'employé sur cette période.
     */
    public function calculer(Employee $employee, string $mois, string $annee): array
    {
        $employee->loadMissing('fonction');
        $moisNum = self::MOIS[$mois] ?? null;

        $salaireBase = (float) ($employee->fonction?->salaire ?? 0);

        $totalPrimes = $moisNum
            ? (float) Prime::where('employee_id', $employee->id)->where('mois', $moisNum)->where('annee', $annee)->sum('montant')
            : 0.0;

        $totalRetenus = $moisNum
            ? (float) Retenu::where('employee_id', $employee->id)->whereMonth('date_retenu', $moisNum)->whereYear('date_retenu', $annee)->sum('montant')
            : 0.0;

        return [
            'salaire_base' => $salaireBase,
            'total_primes' => $totalPrimes,
            'total_retenus' => $totalRetenus,
            'net_a_payer' => $salaireBase + $totalPrimes - $totalRetenus,
        ];
    }
}
