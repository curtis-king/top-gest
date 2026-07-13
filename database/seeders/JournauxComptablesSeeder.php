<?php

namespace Database\Seeders;

use App\Models\JournalComptable;
use Illuminate\Database\Seeder;

class JournauxComptablesSeeder extends Seeder
{
    public function run(): void
    {
        $journaux = [
            ['VE', 'Journal des ventes'],
            ['AC', 'Journal des achats'],
            ['BQ', 'Journal de banque'],
            ['CA', 'Journal de caisse'],
            ['SA', 'Journal des salaires'],
            ['OD', 'Journal des opérations diverses'],
        ];

        foreach ($journaux as [$code, $libelle]) {
            JournalComptable::firstOrCreate(['code' => $code], ['libelle' => $libelle]);
        }
    }
}
