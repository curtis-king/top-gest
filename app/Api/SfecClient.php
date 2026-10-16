<?php

namespace App\Api;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class SfecClient
{
    public function certifyInvoice(array $payload): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => config('sfec.api_key'),
            ])
                ->baseUrl(config('sfec.base_url'))
                ->timeout(15)
                ->post('/api/v1/invoices', $payload);
        } catch (ConnectionException $e) {
            throw new SfecCertificationException(
                "Impossible de joindre le SFEC : {$e->getMessage()}",
                0,
                []
            );
        }

        if ($response->failed()) {
            throw new SfecCertificationException(
                "Échec de la certification SFEC (HTTP {$response->status()})",
                $response->status(),
                $response->json() ?? []
            );
        }

        return $response->json();
    }

    public function getInvoice(string $invoiceId): array
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => config('sfec.api_key'),
            ])
                ->baseUrl(config('sfec.base_url'))
                ->timeout(15)
                ->get("/api/v1/invoices/{$invoiceId}");
        } catch (ConnectionException $e) {
            throw new SfecCertificationException(
                "Impossible de joindre le SFEC : {$e->getMessage()}",
                0,
                []
            );
        }

        if ($response->failed()) {
            throw new SfecCertificationException(
                "Échec de la consultation SFEC (HTTP {$response->status()})",
                $response->status(),
                $response->json() ?? []
            );
        }

        return $response->json();
    }
}
