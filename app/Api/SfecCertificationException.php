<?php

namespace App\Api;

use Exception;

/**
 * Codes documentés par docs.sfec.gouv.cg/api/certify : 400 (validation / règle d'avoir),
 * 401 (clé API ou NIU invalide), 404 (facture référencée introuvable), 409 (numéro déjà
 * utilisé ou facture référencée ayant déjà un avoir), 429 (quota), 500. Le 422 n'est pas utilisé.
 */
class SfecCertificationException extends Exception
{
    public function __construct(
        string $message,
        public readonly int $status,
        public readonly array $responseBody = []
    ) {
        parent::__construct($message);
    }

    /**
     * Conflit : soit cette facture a déjà été certifiée (ex. double envoi), soit le numéro est
     * pris par une autre facture, soit l'avoir vise une facture qui en a déjà un. Seule une
     * consultation GET permet de trancher : ne jamais en déduire seul que la facture est certifiée.
     */
    public function isConflict(): bool
    {
        return $this->status === 409;
    }
}
