<?php

namespace App\Http\Controllers;

use App\Api\FacturePayloadBuilder;
use App\Api\SfecCertificationException;
use App\Api\SfecClient;
use App\Enums\StatutCertification;
use App\Models\Facture;
use Illuminate\Http\RedirectResponse;

class FactureCertificationController extends Controller
{
    public function certifier(Facture $facture, FacturePayloadBuilder $payloadBuilder, SfecClient $client): RedirectResponse
    {
        if ($facture->type_facture?->value !== 'vente') {
            return back()->with('error', 'Seules les factures de vente peuvent être certifiées.');
        }

        if ($facture->statut_certification === StatutCertification::Certifiee) {
            return back()->with('error', 'Cette facture est déjà certifiée.');
        }

        try {
            $payload = $payloadBuilder->build($facture);
            $result = $client->certifyInvoice($payload);

            $facture->update(array_merge(
                ['statut_certification' => StatutCertification::Certifiee->value, 'certification_error' => null],
                $this->extractCertificationFields($result)
            ));

            return back()->with('success', 'Facture certifiée avec succès auprès du SFEC.');
        } catch (SfecCertificationException $e) {
            if ($e->isAlreadyCertified()) {
                $fields = ['statut_certification' => StatutCertification::Certifiee->value, 'certification_error' => null];

                try {
                    $invoice = $client->getInvoice($facture->numero_facture);
                    $fields = array_merge($fields, $this->extractCertificationFields($invoice));
                } catch (SfecCertificationException) {
                    // La facture est bien certifiée côté SFEC, mais on n'a pas pu récupérer le détail : tant pis.
                }

                $facture->update($fields);

                return back()->with('success', 'Cette facture était déjà certifiée auprès du SFEC.');
            }

            $detail = $this->extractErrorDetail($e);

            $facture->update([
                'statut_certification' => StatutCertification::Echec->value,
                'certification_error' => $detail,
            ]);

            $message = match (true) {
                $e->status === 401 => 'Certification refusée : clé API SFEC absente ou invalide. Vérifiez la configuration.',
                in_array($e->status, [400, 422]) => "Certification refusée par le SFEC : {$detail}",
                default => 'Le SFEC est momentanément indisponible. Réessayez plus tard.',
            };

            return back()->with('error', $message);
        }
    }

    /**
     * Le SFEC renvoie soit {"message": "..."}, soit {"error": "...", "errors": [{"field","message"},...]}
     * selon que l'échec est structurel ou une validation métier détaillée.
     */
    private function extractErrorDetail(SfecCertificationException $e): string
    {
        $body = $e->responseBody;

        if (!empty($body['errors']) && is_array($body['errors'])) {
            return collect($body['errors'])
                ->map(fn($err) => trim(($err['field'] ?? '') . ' : ' . ($err['message'] ?? '')), ' : ')
                ->implode(' — ');
        }

        return $body['message'] ?? $body['error'] ?? $e->getMessage();
    }

    /**
     * Les champs diffèrent légèrement entre la réponse de POST /invoices (certification_number,
     * signature, short_signature, qr_code, identifier) et celle de GET /invoices/{id}
     * (certification_signature, certification_short_signature, certification_qr_code) : on essaie
     * les deux jeux de clés pour rester compatible avec les deux appels.
     */
    private function extractCertificationFields(array $data): array
    {
        $signature = $data['signature'] ?? $data['certification_signature'] ?? null;
        $shortSignature = $data['short_signature'] ?? $data['certification_short_signature'] ?? null;

        return [
            'certification_number' => $data['certification_number'] ?? $signature,
            'certification_signature' => $signature,
            'certification_short_signature' => $shortSignature,
            'certification_qr_code' => $data['qr_code'] ?? $data['certification_qr_code'] ?? null,
            'certification_date' => $data['certification_date'] ?? now(),
            'sfec_identifier' => $data['identifier'] ?? $shortSignature,
        ];
    }
}
