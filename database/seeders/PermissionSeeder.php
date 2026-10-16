<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ── Catalogue de permissions ──────────────────────────────────
        $permissions = [
            // RH
            'rh.view', 'rh.create', 'rh.update', 'rh.delete',
            // Projets
            'projets.view', 'projets.create', 'projets.update', 'projets.delete',
            'projets.status',
            // Finance
            'finance.view', 'finance.create', 'finance.update', 'finance.delete',
            'finance.factures.valider',
            'finance.factures.certifier',
            'finance.ecritures.valider',
            'finance.depenses.valider',
            // Stock
            'stock.view', 'stock.create', 'stock.update', 'stock.delete',
            // Archives
            'archives.view', 'archives.create', 'archives.update', 'archives.delete',
            // Administration
            'administration.view', 'administration.create',
            'administration.update', 'administration.delete',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm, 'guard_name' => 'web']);
        }

        // ── Bundles réutilisables ─────────────────────────────────────
        $crud = fn(string $domain) => [
            "{$domain}.view", "{$domain}.create", "{$domain}.update", "{$domain}.delete",
        ];
        $assistant = fn(string $domain) => [
            "{$domain}.view", "{$domain}.create", "{$domain}.update",
        ];
        $allBusiness = array_merge(
            $crud('rh'),
            $crud('projets'), ['projets.status'],
            $crud('finance'), ['finance.factures.valider', 'finance.factures.certifier', 'finance.ecritures.valider', 'finance.depenses.valider'],
            $crud('stock'),
            $crud('archives'),
        );

        // ── Rôles et assignation ──────────────────────────────────────
        $roles = [
            'admin-systeme' => $crud('administration'),

            'dg' => array_merge($allBusiness, ['administration.view']),

            'chef-agence' => array_merge($allBusiness, ['administration.view']),

            'responsable-rh'      => $crud('rh'),
            'assistant-rh'        => $assistant('rh'),

            'responsable-projets' => array_merge($crud('projets'), ['projets.status']),
            'assistant-projets'   => $assistant('projets'),

            'responsable-finance' => array_merge($crud('finance'), ['finance.factures.valider', 'finance.factures.certifier', 'finance.ecritures.valider', 'finance.depenses.valider']),
            'assistant-finance'   => $assistant('finance'),

            'responsable-stock'   => $crud('stock'),
            'assistant-stock'     => $assistant('stock'),

            'responsable-archives' => $crud('archives'),
            'assistant-archives'   => $assistant('archives'),
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }

        // ── Utilisateurs par défaut ───────────────────────────────────
        // Mot de passe aléatoire, affiché une seule fois à la création (jamais de mot de passe connu).
        $this->compteParDefaut(env('SEED_ADMIN_EMAIL', 'adminm@gmail.com'), 'Administrator', 'admin-systeme');
        $this->compteParDefaut(env('SEED_DG_EMAIL', 'dg@mygest.local'), 'Directeur Général', 'dg');
    }

    private function compteParDefaut(string $email, string $nom, string $role): void
    {
        $user = User::where('email', $email)->first();

        if (!$user) {
            $motDePasse = Str::password(16);
            $user = User::create(['email' => $email, 'name' => $nom, 'password' => $motDePasse, 'scope' => 'dg']);
            $this->command?->warn("  Compte {$email} créé — mot de passe : {$motDePasse} (à changer dès la première connexion)");
        }

        $user->update(['scope' => 'dg']);
        $user->syncRoles([$role]);
    }
}
