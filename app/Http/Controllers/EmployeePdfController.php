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

    public function streamContrat(Employee $employee): Response
    {
        $employee->load(['agence.compagnie', 'fonction', 'dossier', 'payements']);

        $agence     = $employee->agence;
        $compagnie  = $agence?->compagnie;
        $typeContrat = $employee->dossier?->type_contrat?->value;
        $salaire    = $employee->payements->sortByDesc('created_at')->first()?->salaire_base;

        $view = $typeContrat === 'stage'
            ? 'employees.pdf.attestation_stage'
            : 'employees.pdf.contrat';

        $pdf = Pdf::loadView($view, compact('employee', 'agence', 'compagnie', 'salaire'));
        $pdf->setPaper('A4', 'portrait');

        $slug = $typeContrat === 'stage' ? 'attestation_stage' : 'contrat';
        return $pdf->stream("{$slug}_{$employee->nom_complet}.pdf");
    }

    public function downloadContrat(Employee $employee): Response
    {
        $employee->load(['agence.compagnie', 'fonction', 'dossier', 'payements']);

        $agence     = $employee->agence;
        $compagnie  = $agence?->compagnie;
        $typeContrat = $employee->dossier?->type_contrat?->value;
        $salaire    = $employee->payements->sortByDesc('created_at')->first()?->salaire_base;

        $view = $typeContrat === 'stage'
            ? 'employees.pdf.attestation_stage'
            : 'employees.pdf.contrat';

        $pdf = Pdf::loadView($view, compact('employee', 'agence', 'compagnie', 'salaire'));
        $pdf->setPaper('A4', 'portrait');

        $slug = $typeContrat === 'stage' ? 'attestation_stage' : 'contrat';
        return $pdf->download("{$slug}_{$employee->nom_complet}.pdf");
    }
}
