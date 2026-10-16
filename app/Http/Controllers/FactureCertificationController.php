<?php

namespace App\Http\Controllers;

use App\Api\FacturePayloadBuilder;
use App\Api\SfecCertificationException;
use App\Api\SfecClient;
use App\Api\SfecPayloadValidator;
use App\Enums\StatutCertification;
use App\Enums\StatutFacture;
use App\Enums\TypeFacture;
use App\Models\Facture;
use App\Services\FactureWorkflow;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class FactureCertificationController extends Controller
{
    public function certifier(
        Facture $facture,
        FacturePayloadBuilder $payloadBuilder,
        SfecPayloadValidator $validator,
        SfecClient $client
    ): RedirectResponse {
        // Un seul envoi à la fois par facture (double clic, deux onglets…).
        $lock = Cache::lock("sfec-certification-{$facture->id}", 60);
        if (!$lock->get()) {
            return back()->with('error', 'Une certification de cette facture est déjà en cours.');
        }

        try {
            $facture->refresh();

            if ($refus = $this->verifierPrerequis($facture)) {
                return back()->with('error', $refus);
            }

            $payload = $payloadBuilder->build($facture);

            if ($erreurs = $validator->erreurs($payload)) {
                return back()->with('error', 'Certification impossible : ' . implode(' ', $erreurs));
            }

            try {
                $result = $client->certifyInvoice($payload);
            } catch (SfecCertificationException $e) {
                return $this->gererEchec($facture, $payload, $e, $client);
            }

            $facture->update(array_merge(
                ['statut_certification' => StatutCertification::Certifiee->value, 'certification_error' => null, 'client_certifie' => self::clientCertifie($payload)],
                $this->extractCertificationFields($result)
            ));

            app(FactureWorkflow::class)->journaliser($facture, 'certification', null, null, 'Certifiée SFEC : ' . ($facture->sfec_identifier ?? $facture->certification_number));

            return back()->with('success', 'Facture certifiée avec succès auprès du SFEC.');
        } finally {
            $lock->release();
        }
    }

    /**
     * Données client telles qu'envoyées au SFEC : le PDF d'une facture certifiée les réutilise,
     * même si la fiche contact change ensuite.
     */
    private static function clientCertifie(array $payload): array
    {
        return array_intersect_key($payload, array_flip([
            'recipient_type', 'is_recipient_taxable', 'recipient_name', 'recipient_niu',
            'recipient_rccm', 'recipient_address', 'recipient_phone', 'recipient_email',
        ]));
    }

    private function verifierPrerequis(Facture $facture): ?string
    {
        if (!in_array($facture->type_facture, [TypeFacture::Vente, TypeFacture::Avoir], true)) {
            return 'Seules les factures de vente et les avoirs peuvent être certifiés.';
        }

        if ($facture->estCertifiee()) {
            return 'Cette facture est déjà certifiée.';
        }

        if (in_array($facture->statut_facture, [StatutFacture::Brouillon, StatutFacture::Annulee], true)) {
            return 'Une facture en brouillon ou annulée ne peut pas être certifiée : validez-la d\'abord.';
        }

        if ($facture->type_facture === TypeFacture::Avoir) {
            $origine = $facture->factureOrigine;

            if (!$origine || $origine->type_facture !== TypeFacture::Vente || !$origine->estCertifiee()) {
                return 'Cet avoir doit référencer une facture de vente déjà certifiée.';
            }

            $avoirDejaCertifie = Facture::withoutGlobalScope('agence')
                ->where('facture_origine_id', $origine->id)
                ->where('type_facture', TypeFacture::Avoir->value)
                ->where('statut_certification', StatutCertification::Certifiee->value)
                ->whereKeyNot($facture->id)
                ->exists();

            if ($avoirDejaCertifie) {
                return 'La facture d\'origine possède déjà un avoir certifié (le SFEC n\'en accepte qu\'un).';
            }

            if ($facture->depasseFactureOrigine()) {
                return 'Le montant de cet avoir dépasse celui de la facture d\'origine.';
            }
        }

        return null;
    }

    private function gererEchec(Facture $facture, array $payload, SfecCertificationException $e, SfecClient $client): RedirectResponse
    {
        // 409 : on ne marque la facture certifiée que si le SFEC renvoie bien CETTE facture.
        if ($e->isConflict() && ($invoice = $this->retrouverMemeFacture($payload, $client))) {
            $facture->update(array_merge(
                ['statut_certification' => StatutCertification::Certifiee->value, 'certification_error' => null, 'client_certifie' => self::clientCertifie($payload)],
                $this->extractCertificationFields($invoice)
            ));

            app(FactureWorkflow::class)->journaliser($facture, 'certification', null, null, 'Certification SFEC récupérée (déjà certifiée)');

            return back()->with('success', 'Cette facture était déjà certifiée auprès du SFEC : signature récupérée.');
        }

        $detail = $this->extractErrorDetail($e);

        $facture->update([
            'statut_certification' => StatutCertification::Echec->value,
            'certification_error' => $detail,
        ]);

        app(FactureWorkflow::class)->journaliser($facture, 'certification', null, null, "Échec SFEC (HTTP {$e->status}) : {$detail}");

        $message = match (true) {
            $e->status === 400 => "Certification refusée par le SFEC : {$detail}",
            $e->status === 401 => 'Certification refusée : clé API SFEC ou NIU de l\'entreprise invalide. Vérifiez la configuration.',
            $e->status === 404 => 'Le SFEC ne trouve pas la facture d\'origine référencée par cet avoir.',
            $e->status === 409 => "Conflit SFEC : {$detail} (numéro déjà utilisé par une autre facture, ou facture d'origine ayant déjà un avoir).",
            $e->status === 429 => 'Quota de requêtes SFEC dépassé. Réessayez dans quelques minutes.',
            default => 'Le SFEC est momentanément indisponible. Réessayez plus tard.',
        };

        return back()->with('error', $message);
    }

    /**
     * Consulte la facture portant ce numéro au SFEC et vérifie qu'il s'agit bien de la même
     * (montant total identique) avant de la considérer comme certifiée.
     */
    private function retrouverMemeFacture(array $payload, SfecClient $client): ?array
    {
        try {
            $invoice = $client->getInvoice($payload['invoice_id']);
        } catch (SfecCertificationException) {
            return null;
        }

        $signature = $invoice['signature'] ?? $invoice['certification_signature'] ?? null;
        $memeMontant = isset($invoice['total_amount'])
            && abs((float) $invoice['total_amount'] - (float) $payload['total_amount']) < 0.01;

        return $signature && $memeMontant ? $invoice : null;
    }

    /**
     * Le SFEC renvoie {"error": "Validation failed", "errors": [{"field","message"},...]} en 400,
     * et uniquement {"message": "..."} pour les autres codes.
     */
    private function extractErrorDetail(SfecCertificationException $e): string
    {
        $body = $e->responseBody;

        if (!empty($body['errors']) && is_array($body['errors'])) {
            return collect($body['errors'])
                ->map(fn($err) => trim(($err['field'] ?? '') . ' : ' . ($err['message'] ?? ''), ' : '))
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
        $qrCode = $data['qr_code'] ?? $data['certification_qr_code'] ?? null;

        return [
            'certification_number' => $data['certification_number'] ?? $signature,
            'certification_signature' => $signature,
            'certification_short_signature' => $shortSignature,
            // Le SFEC renvoie déjà le QR code préfixé "data:image/png;base64,...", alors que
            // les vues rajoutent ce même préfixe à l'affichage : on ne stocke que le base64 brut.
            'certification_qr_code' => $qrCode ? preg_replace('/^data:image\/[a-z]+;base64,/i', '', $qrCode) : null,
            'certification_date' => $data['certification_date'] ?? now(),
            'sfec_identifier' => $data['identifier'] ?? $shortSignature,
        ];
    }
}
