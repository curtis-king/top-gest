<?php

namespace App\Api;

/**
 * Contrôles locaux reprenant les règles de docs.sfec.gouv.cg/api/certify, pour refuser
 * une facture incomplète avant de l'envoyer (le SFEC la rejetterait en 400).
 */
class SfecPayloadValidator
{
    /**
     * @return list<string> messages d'erreur lisibles (vide si le payload est conforme)
     */
    public function erreurs(array $payload): array
    {
        $erreurs = [];

        if (empty($payload['items'])) {
            $erreurs[] = 'La facture ne contient aucun article.';
        }

        if (($payload['total_amount'] ?? 0) <= 0) {
            $erreurs[] = 'Le montant total de la facture doit être supérieur à zéro.';
        }

        $niuEmetteur = $payload['taxpayer_niu'] ?? null;
        if (empty($niuEmetteur)) {
            $erreurs[] = 'Le NIU de l\'entreprise émettrice est manquant (fiche Compagnie de l\'agence).';
        } elseif (mb_strlen($niuEmetteur) > 20) {
            $erreurs[] = 'Le NIU de l\'entreprise émettrice dépasse 20 caractères.';
        }

        $erreurs = array_merge($erreurs, $this->erreursDestinataire($payload));

        if (($payload['invoice_type'] ?? null) === 'creditNote' && empty($payload['reference_invoice_id'])) {
            $erreurs[] = 'Un avoir doit référencer la facture d\'origine.';
        }

        return $erreurs;
    }

    /**
     * Règles portant sur le client (recipient_*), partagées avec la fiche contact.
     *
     * @return list<string>
     */
    public function erreursDestinataire(array $payload): array
    {
        $erreurs = [];
        $type = $payload['recipient_type'] ?? null;

        if (in_array($type, ['business', 'government', 'foreign'], true) && empty($payload['recipient_name'])) {
            $erreurs[] = 'Le nom du client est obligatoire pour ce type de client.';
        }

        if (in_array($type, ['business', 'government'], true)) {
            $niu = $payload['recipient_niu'] ?? '';
            if (!in_array(mb_strlen((string) $niu), [16, 17], true)) {
                $erreurs[] = 'Le NIU du client (entreprise ou État) est obligatoire et doit comporter 16 ou 17 caractères.';
            }
        }

        if (in_array($type, ['government', 'foreign'], true)) {
            foreach (['recipient_address' => 'l\'adresse', 'recipient_phone' => 'le téléphone', 'recipient_email' => 'l\'e-mail'] as $champ => $libelle) {
                if (empty($payload[$champ])) {
                    $erreurs[] = "Pour un client État ou étranger, {$libelle} du client est obligatoire.";
                }
            }
        }

        return $erreurs;
    }
}
