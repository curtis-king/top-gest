<?php

namespace Database\Seeders;

use App\Models\CategorieDepense;
use App\Models\CompteComptable;
use Illuminate\Database\Seeder;

class CategoriesDepensesSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Loyer' => '613000',
            'Fournitures' => '604000',
            'Transport' => '611000',
            'Services / Prestataires' => '622000',
            'Divers' => '628000',
        ];

        foreach ($categories as $libelle => $numeroCompte) {
            $compte = CompteComptable::where('numero_compte', $numeroCompte)->first();
            if ($compte) {
                CategorieDepense::firstOrCreate(
                    ['libelle' => $libelle],
                    ['compte_comptable_id' => $compte->id]
                );
            }
        }
    }
}
