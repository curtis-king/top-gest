<?php

namespace Database\Seeders;

use App\Models\CompteComptable;
use Illuminate\Database\Seeder;

class PlanComptableSeeder extends Seeder
{
    public function run(): void
    {
        $comptes = [
            // Classe 1 - Capitaux
            ['101000', 'Capital social', 'capitaux', 'credit'],
            ['106000', 'Réserves', 'capitaux', 'credit'],
            ['120000', 'Résultat de l\'exercice', 'capitaux', 'credit'],

            // Classe 2 - Immobilisations
            ['221000', 'Terrains', 'actif', 'debit'],
            ['231000', 'Bâtiments, installations', 'actif', 'debit'],
            ['241000', 'Matériel et mobilier', 'actif', 'debit'],
            ['281000', 'Amortissements des immobilisations', 'actif', 'credit'],

            // Classe 3 - Stocks
            ['311000', 'Marchandises', 'actif', 'debit'],

            // Classe 4 - Tiers
            ['401000', 'Fournisseurs', 'passif', 'credit'],
            ['411000', 'Clients', 'actif', 'debit'],
            ['421000', 'Personnel, rémunérations dues', 'passif', 'credit'],
            ['431000', 'Sécurité sociale (CNSS)', 'passif', 'credit'],
            ['445000', 'État, TVA', 'passif', 'credit'],
            ['447000', 'État, impôts retenus à la source', 'passif', 'credit'],

            // Classe 5 - Trésorerie (les comptes bancaires spécifiques sont créés à la volée sous 521xxx)
            ['520000', 'Banques', 'tresorerie', 'debit'],
            ['570000', 'Caisse', 'tresorerie', 'debit'],

            // Classe 6 - Charges
            ['601000', 'Achats de marchandises', 'charge', 'debit'],
            ['604000', 'Achats de matières et fournitures', 'charge', 'debit'],
            ['611000', 'Transports', 'charge', 'debit'],
            ['613000', 'Locations', 'charge', 'debit'],
            ['622000', 'Rémunérations d\'intermédiaires', 'charge', 'debit'],
            ['628000', 'Frais divers de gestion', 'charge', 'debit'],
            ['661000', 'Rémunérations directes du personnel', 'charge', 'debit'],
            ['663000', 'Charges sociales', 'charge', 'debit'],
            ['674000', 'Autres charges financières (frais bancaires)', 'charge', 'debit'],
            ['681000', 'Dotations aux amortissements', 'charge', 'debit'],

            // Classe 7 - Produits
            ['701000', 'Ventes de marchandises', 'produit', 'credit'],
            ['706000', 'Services vendus', 'produit', 'credit'],
            ['771000', 'Revenus financiers (intérêts)', 'produit', 'credit'],
        ];

        foreach ($comptes as [$numero, $libelle, $type, $sens]) {
            CompteComptable::firstOrCreate(
                ['numero_compte' => $numero],
                [
                    'libelle' => $libelle,
                    'classe' => (int) $numero[0],
                    'type_compte' => $type,
                    'sens_normal' => $sens,
                    'is_systeme' => true,
                    'actif' => true,
                ]
            );
        }
    }
}
