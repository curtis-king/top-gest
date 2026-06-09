<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class EmployeePdfController extends Controller
{
    public function fiche(Employee $employee): Response
    {
        $employee->load([
            'agence.compagnie',
            'fonction',
            'user',
            'dossier',
            'conges',
            'primes',
            'retenus',
            'payements',
        ]);

        $agence = $employee->agence;
        $compagnie = $agence?->compagnie;

        $pdf = Pdf::loadView('employees.pdf.fiche', compact('employee', 'agence', 'compagnie'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("fiche_{$employee->nom_complet}.pdf");
    }
}
