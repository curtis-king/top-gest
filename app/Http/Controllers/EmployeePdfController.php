<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EmployeePdfController extends Controller
{
    public function preview(Employee $employee): View
    {
        return view('employees.pdf.preview', compact('employee'));
    }

    public function stream(Employee $employee): Response
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

        return $pdf->stream("fiche_{$employee->nom_complet}.pdf");
    }

    public function download(Employee $employee): Response
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
