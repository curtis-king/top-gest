<?php

namespace App\Api;

use App\Models\Facture;

class FacturePayloadBuilder
{
    public function build(Facture $facture): array
    {
        $facture->loadMissing(['items', 'contact', 'agence.compagnie']);

        $items = $facture->items->map(fn($item) => $this->buildItem($item));

        $subtotal = $items->sum('net_amount');
        $totalTaxTAmount = $items->where('_taux_tva', '>', 0)->sum('tax_amount');
        $totalTaxRAmount = 0;
        $totalExemptAmount = $items->where('_taux_tva', '<=', 0)->sum('net_amount');
        $totalTaxAmount = $items->sum('tax_amount');
        $totalAmount = $subtotal + $totalTaxAmount;

        $contact = $facture->contact;
        $recipientTypeKey = $contact?->type_client_sfec ?? ($contact?->raison_social ? 'entreprise' : 'particulier');
        $recipientType = config("sfec.recipient_types.{$recipientTypeKey}", config('sfec.recipient_types.particulier'));
        $isRecipientTaxable = in_array($recipientTypeKey, ['entreprise', 'etat']);

        $paymentMethodKey = $facture->mode_paiement ?? 'especes';
        $paymentMethod = config("sfec.payment_methods.{$paymentMethodKey}", config('sfec.payment_methods.especes'));

        return [
            'invoice_id' => $facture->numero_facture,
            'taxpayer_niu' => $facture->agence?->compagnie?->nui,
            'invoice_type' => config('sfec.invoice_types.' . $facture->type_facture?->value, config('sfec.invoice_types.vente')),
            'invoice_subject' => $facture->objet,
            'invoice_due_date' => null,
            'reference_invoice_id' => null,
            'sciet' => config('sfec.sciet'),
            'currency' => 'XAF',
            'subtotal' => $subtotal,
            'total_tax_t_amount' => $totalTaxTAmount,
            'total_tax_r_amount' => $totalTaxRAmount,
            'total_exempt_amount' => $totalExemptAmount,
            'total_tax_amount' => $totalTaxAmount,
            'discount_amount' => 0,
            'total_line_discount_amount' => 0,
            'additional_cent_tax' => 0,
            'electronic_stamp_duty' => 0,
            'amount_due' => $totalAmount,
            'total_amount' => $totalAmount,
            'recipient_type' => $recipientType,
            'is_recipient_taxable' => $isRecipientTaxable,
            'recipient_name' => $contact?->raison_social ?? $contact?->nom_complet ?? $facture->raison_social,
            'recipient_niu' => $contact?->niu,
            'recipient_rccm' => null,
            'recipient_address' => $contact?->adresse,
            'recipient_phone' => $contact?->telephone,
            'recipient_email' => $contact?->adresse_email,
            'payment_method' => $paymentMethod,
            'payment_reference' => null,
            'payment_date' => null,
            'notes' => null,
            'items' => $items->map(fn($item) => collect($item)->except(['_taux_tva'])->all())->values()->all(),
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
            'tax_rate' => (string) ((float) $item->taux_tva),
            'tax_amount' => $taxAmount,
            'total_amount' => $netAmount + $taxAmount,
            '_taux_tva' => (float) $item->taux_tva,
        ];
    }
}
