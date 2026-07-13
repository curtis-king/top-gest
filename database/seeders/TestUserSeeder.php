<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TestUserSeeder extends Seeder
{
    public function run(): void
    {
        // ── Données structurelles ─────────────────────────────────────
        $compagnie = DB::table('compagnies')->insertGetId([
            'name'       => 'Société Test',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $agenceAlpha = DB::table('agences')->insertGetId([
            'name_agence'  => 'Agence Alpha',
            'ville'        => 'Yaoundé',
            'compagnie_id' => $compagnie,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $agenceBeta = DB::table('agences')->insertGetId([
            'name_agence'  => 'Agence Beta',
            'ville'        => 'Douala',
            'compagnie_id' => $compagnie,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $fonction = DB::table('fonctions')->insertGetId([
            'name'       => 'Poste Test',
            'salaire'    => 150000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ── Comptes de test ───────────────────────────────────────────
        // Format : [email, nom, scope, role, agence_id|null]
        $users = [
            // Accès total – aucun filtre agence
            ['admin@test.local',           'Admin Système Test',      'dg',          'admin-systeme',        null],
            ['dg@test.local',              'Directeur Général Test',  'dg',          'dg',                   null],

            // Agence Alpha – accès transversal
            ['chef-alpha@test.local',      'Chef Agence Alpha',       'chef-agence', 'chef-agence',          $agenceAlpha],

            // Agence Alpha – domaine RH
            ['rh@test.local',              'Responsable RH Alpha',    'agent',       'responsable-rh',       $agenceAlpha],
            ['rh-assist@test.local',       'Assistant RH Alpha',      'agent',       'assistant-rh',         $agenceAlpha],

            // Agence Alpha – domaine Projets
            ['projets@test.local',         'Resp. Projets Alpha',     'agent',       'responsable-projets',  $agenceAlpha],
            ['projets-assist@test.local',  'Assist. Projets Alpha',   'agent',       'assistant-projets',    $agenceAlpha],

            // Agence Alpha – domaine Finance
            ['finance@test.local',         'Resp. Finance Alpha',     'agent',       'responsable-finance',  $agenceAlpha],
            ['finance-assist@test.local',  'Assist. Finance Alpha',   'agent',       'assistant-finance',    $agenceAlpha],

            // Agence Alpha – domaine Stock
            ['stock@test.local',           'Resp. Stock Alpha',       'agent',       'responsable-stock',    $agenceAlpha],
            ['stock-assist@test.local',    'Assist. Stock Alpha',     'agent',       'assistant-stock',      $agenceAlpha],

            // Agence Alpha – domaine Archives
            ['archives@test.local',        'Resp. Archives Alpha',    'agent',       'responsable-archives', $agenceAlpha],
            ['archives-assist@test.local', 'Assist. Archives Alpha',  'agent',       'assistant-archives',   $agenceAlpha],

            // Agence Beta – pour tester l'isolation inter-agences
            ['chef-beta@test.local',       'Chef Agence Beta',        'chef-agence', 'chef-agence',          $agenceBeta],
            ['rh-beta@test.local',         'Resp. RH Beta',           'agent',       'responsable-rh',       $agenceBeta],
            ['finance-beta@test.local',    'Resp. Finance Beta',      'agent',       'responsable-finance',  $agenceBeta],
            ['stock-beta@test.local',      'Resp. Stock Beta',        'agent',       'responsable-stock',    $agenceBeta],
        ];

        foreach ($users as [$email, $nom, $scope, $role, $agenceId]) {
            $user = User::firstOrCreate(
                ['email' => $email],
                [
                    'name'     => $nom,
                    'password' => bcrypt('password'),
                    'scope'    => $scope,
                ]
            );
            $user->update(['scope' => $scope]);
            $user->syncRoles([$role]);

            // Créer la fiche employé pour les utilisateurs scopés à une agence
            if ($agenceId) {
                Employee::firstOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nom_complet' => $nom,
                        'agence_id'   => $agenceId,
                        'fonction_id' => $fonction,
                    ]
                );
            }
        }

        // ── Résumé ────────────────────────────────────────────────────
        $this->command->info('');
        $this->command->info('  Comptes de test créés — mot de passe : password');
        $this->command->info('  ──────────────────────────────────────────────────────');
        $this->command->info('  Accès global (toutes agences)');
        $this->command->info('    admin@test.local        → Admin Système (dashboard + users uniquement)');
        $this->command->info('    dg@test.local           → Directeur Général (tout voir)');
        $this->command->info('');
        $this->command->info('  Agence Alpha (Yaoundé)');
        $this->command->info('    chef-alpha@test.local   → Chef d\'agence (tous domaines)');
        $this->command->info('    rh@test.local           → Responsable RH');
        $this->command->info('    rh-assist@test.local    → Assistant RH (pas de suppression)');
        $this->command->info('    projets@test.local      → Responsable Projets');
        $this->command->info('    projets-assist@test.local → Assistant Projets');
        $this->command->info('    finance@test.local      → Responsable Finance');
        $this->command->info('    finance-assist@test.local → Assistant Finance');
        $this->command->info('    stock@test.local        → Responsable Stock');
        $this->command->info('    stock-assist@test.local → Assistant Stock');
        $this->command->info('    archives@test.local     → Responsable Archives');
        $this->command->info('    archives-assist@test.local → Assistant Archives');
        $this->command->info('');
        $this->command->info('  Agence Beta (Douala) — pour tester l\'isolation inter-agences');
        $this->command->info('    chef-beta@test.local    → Chef d\'agence Beta');
        $this->command->info('    rh-beta@test.local      → Responsable RH Beta');
        $this->command->info('    finance-beta@test.local → Responsable Finance Beta');
        $this->command->info('    stock-beta@test.local   → Responsable Stock Beta');
        $this->command->info('');
    }
}
