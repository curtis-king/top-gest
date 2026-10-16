<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DiagnosticTest extends TestCase
{
    use RefreshDatabase;

    public function test_signale_les_comptes_et_factures_a_ajuster(): void
    {
        $this->seed(PermissionSeeder::class);
        User::create(['name' => 'Sans rôle', 'email' => 'sansrole@test.local', 'password' => 'x', 'scope' => 'dg']);
        User::create(['name' => 'Agent perdu', 'email' => 'perdu@test.local', 'password' => 'x', 'scope' => 'agent'])
            ->syncRoles(['assistant-finance']);
        DB::table('factures')->insert([
            'type_facture' => 'vente', 'numero_facture' => 'FAC-00008', 'date_facture' => '2026-07-29',
            'statut_facture' => 'brouillon', 'statut_certification' => 'certifiee', 'tva_active' => false, 'centimes_additionnels_actif' => false,
        ]);

        $this->artisan('mygest:diagnostic')
            ->expectsOutputToContain('Administrateurs système : 1')
            ->expectsOutputToContain('1 compte(s) sans rôle')
            ->expectsOutputToContain('compte(s) non-DG sans agence')
            ->expectsOutputToContain('1 facture(s) sans agence')
            ->expectsOutputToContain('FAC-00008')
            ->assertSuccessful();

        $this->assertDatabaseHas('factures', ['numero_facture' => 'FAC-00008', 'statut_facture' => 'brouillon']);
    }
}
