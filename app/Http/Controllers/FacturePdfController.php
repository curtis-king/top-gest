<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class FacturePdfController extends Controller
{
    public function preview(Facture $facture): View
    {
        return view('factures.pdf.preview', compact('facture'));
    }

    public function stream(Facture $facture): Response
    {
        $facture->load(['items', 'agence.contacts']);

        $client = $facture->contact ?? null;
        $total = $facture->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);

        $pdf = Pdf::loadView('factures.pdf.impression', compact('facture', 'client', 'total'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("facture_{$facture->numero_facture}.pdf");
    }

    public function download(Facture $facture): Response
    {
        $facture->load(['items', 'agence.contacts']);

        $client = $facture->contact ?? null;
        $total = $facture->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);

        $pdf = Pdf::loadView('factures.pdf.impression', compact('facture', 'client', 'total'));
        $pdf->setPaper('A4', 'portrait');

        return $pdf->download("facture_{$facture->numero_facture}.pdf");
    }
}
