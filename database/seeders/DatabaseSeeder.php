<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(PermissionSeeder::class);
        $this->call(PlanComptableSeeder::class);
        $this->call(JournauxComptablesSeeder::class);
        $this->call(CategoriesDepensesSeeder::class);
    }
}
