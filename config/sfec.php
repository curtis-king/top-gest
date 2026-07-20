<?php

return [

    'base_url' => env('SFEC_BASE_URL', 'https://sandbox.api.sfec.gouv.cg'),

    'api_key' => env('SFEC_API_KEY'),

    /*
     * Vocabulaire attendu par l'API SFEC, confirmé via docs.sfec.gouv.cg/api/certify.
     */
    'invoice_types' => [
        'vente' => 'salesInvoice',
        'avoir' => 'creditNote',
    ],

    'recipient_types' => [
        'entreprise' => 'business',
        'particulier' => 'individual',
        'etat' => 'government',
        'etranger' => 'foreign',
    ],

    'payment_methods' => [
        'especes' => 'cash',
        'mobile_money' => 'mobile_money',
        'virement' => 'bank_transfer',
        'cheque' => 'cheque',
        'carte' => 'card',
    ],

    'item_types' => [
        'produit' => 'product',
        'service' => 'service',
    ],

    // Identifiant SCIET associé à l'intégration (fourni par le SFEC), optionnel.
    'sciet' => env('SFEC_SCIET'),

];
