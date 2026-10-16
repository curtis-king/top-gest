<?php

namespace App\Api;

use App\Models\Facture;

class FacturePayloadBuilder
{
    public function build(Facture $facture): array
    {
        $facture->loadMissing(['items', 'contact', 'agence.compagnie', 'factureOrigine']);

        $items = $facture->items->map(fn($item) => $this->buildItem($item));

        $subtotal = $items->sum('net_amount');
        $totalTaxTAmount = $items->where('_taux_tva', '>', 0)->sum('tax_amount');
        $totalTaxRAmount = 0;
        $totalExemptAmount = $items->where('_taux_tva', '<=', 0)->sum('net_amount');
        $totalTaxAmount = $items->sum('tax_amount');
        $additionalCentTax = $facture->centimes_additionnels_actif ? round($totalTaxAmount * 0.05) : 0;
        $totalAmount = $subtotal + $totalTaxAmount + $additionalCentTax;

        $destinataire = $this->destinataire($facture);

        $paymentMethodKey = $facture->mode_paiement ?? 'especes';
        $paymentMethod = config("sfec.payment_methods.{$paymentMethodKey}", config('sfec.payment_methods.especes'));

        return [
            'invoice_id' => $facture->numero_facture,
            'taxpayer_niu' => $facture->agence?->compagnie?->nui,
            'invoice_type' => config('sfec.invoice_types.' . $facture->type_facture?->value, config('sfec.invoice_types.vente')),
            'invoice_subject' => $facture->objet,
            'invoice_due_date' => null,
            'reference_invoice_id' => $facture->factureOrigine?->numero_facture,
            'sciet' => config('sfec.sciet'),
            'currency' => 'XAF',
            'subtotal' => $subtotal,
            'total_tax_t_amount' => $totalTaxTAmount,
            'total_tax_r_amount' => $totalTaxRAmount,
            'total_exempt_amount' => $totalExemptAmount,
            'total_tax_amount' => $totalTaxAmount,
            'discount_amount' => 0,
            'total_line_discount_amount' => 0,
            'additional_cent_tax' => $additionalCentTax,
            'electronic_stamp_duty' => 0,
            'amount_due' => $totalAmount,
            'total_amount' => $totalAmount,
            ...$destinataire,
            'payment_method' => $paymentMethod,
            'payment_reference' => null,
            'payment_date' => null,
            'notes' => null,
            'items' => $items->map(fn($item) => collect($item)->except(['_taux_tva'])->all())->values()->all(),
        ];
    }

    /**
     * Champs recipient_* : depuis la fiche contact, ou client particulier saisi en texte libre.
     */
    public function destinataire(Facture $facture): array
    {
        if ($facture->contact) {
            return $facture->contact->donneesSfec();
        }

        return [
            'recipient_type' => config('sfec.recipient_types.particulier'),
            'is_recipient_taxable' => false,
            'recipient_name' => $facture->raison_social,
            'recipient_niu' => null,
            'recipient_rccm' => null,
            'recipient_address' => null,
            'recipient_phone' => null,
            'recipient_email' => null,
        ];
    }

    private function buildItem($item): array
    {
        $netAmount = $item->quantite * $item->prix_unitaire;
        $taxAmount = round($netAmount * ((float) $item->taux_tva) / 100);
        $typeKey = $item->type_article ?? 'produit';

        return [
            'designation' => $item->description,
            'classification_code' => null,
            'type' => config("sfec.item_types.{$typeKey}", config('sfec.item_types.produit')),
            'unit_price' => (float) $item->prix_unitaire,
            'quantity' => (int) $item->quantite,
            'subtotal' => $netAmount,
            'discount_amount' => 0,
            'discount_type' => 'fixed',
            'net_amount' => $netAmount,
            'amount_after_discount' => $netAmount,
            'tax_rate' => (string) ((float) $item->taux_tva),
            'tax_amount' => $taxAmount,
            'total_amount' => $netAmount + $taxAmount,
            '_taux_tva' => (float) $item->taux_tva,
        ];
    }
}
