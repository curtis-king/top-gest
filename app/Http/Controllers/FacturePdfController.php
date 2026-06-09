<?php

namespace App\Http\Controllers;

use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class FacturePdfController extends Controller
{
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
