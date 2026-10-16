<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\User;
use App\Support\AgencyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecuriteTest extends TestCase
{
    use RefreshDatabase;

    private int $alpha;
    private int $beta;
    private int $fonction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);

        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'Société', 'nui' => 'M0123456789012345']);
        $this->alpha = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);
        $this->beta = DB::table('agences')->insertGetId(['name_agence' => 'Beta', 'compagnie_id' => $compagnie]);
        $this->fonction = DB::table('fonctions')->insertGetId(['name' => 'Poste', 'salaire' => 1000]);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    private function utilisateur(string $role, ?int $agence, string $scope = 'agent'): User
    {
        $user = User::create([
            'name' => $role,
            'email' => uniqid($role).'@test.local',
            'password' => 'MotDePasse123',
            'scope' => $scope,
        ]);
        $user->syncRoles([$role]);

        if ($agence) {
            DB::table('employees')->insert([
                'nom_complet' => $role, 'agence_id' => $agence, 'fonction_id' => $this->fonction, 'user_id' => $user->id,
            ]);
        }

        return $user;
    }

    private function facture(int $agence, array $attributs = []): Facture
    {
        $id = DB::table('factures')->insertGetId(array_merge([
            'type_facture' => 'vente',
            'numero_facture' => uniqid('FAC-'),
            'date_facture' => now()->toDateString(),
            'statut_facture' => 'impayee',
            'statut_certification' => 'non_certifiee',
            'tva_active' => false,
            'centimes_additionnels_actif' => false,
            'agence_id' => $agence,
        ], $attributs));

        DB::table('item_factures')->insert([
            'description' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => 10000,
            'taux_tva' => 0, 'type_article' => 'service', 'facture_id' => $id,
        ]);

        return Facture::withoutGlobalScopes()->find($id);
    }

    // ── Authentification ──────────────────────────────────────────────

    public function test_inscription_fermee_des_qu_un_utilisateur_existe(): void
    {
        $this->post('/register', [
            'name' => 'Intrus', 'email' => 'intrus@test.local',
            'password' => 'MotDePasse123', 'password_confirmation' => 'MotDePasse123',
        ])->assertRedirect(route('login'));

        $this->assertDatabaseMissing('users', ['email' => 'intrus@test.local']);
    }

    public function test_premier_compte_cree_admin_systeme_avec_mot_de_passe_robuste(): void
    {
        User::query()->delete();

        $this->post('/register', [
            'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => 'faible', 'password_confirmation' => 'faible',
        ])->assertSessionHasErrors('password');

        $this->post('/register', [
            'name' => 'Admin', 'email' => 'admin@test.local',
            'password' => 'MotDePasse123', 'password_confirmation' => 'MotDePasse123',
        ])->assertRedirect(route('dashboard'));

        $this->assertTrue(User::where('email', 'admin@test.local')->first()->hasRole('admin-systeme'));
    }

    public function test_connexion_limitee_apres_cinq_echecs(): void
    {
        $user = $this->utilisateur('dg', null, 'dg');

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'mauvais']);
        }

        $this->post('/login', ['email' => $user->email, 'password' => 'MotDePasse123'])
            ->assertSessionHasErrors('email');
        $this->assertStringStartsWith('Trop de tentatives.', session('errors')->first('email'));
        $this->assertGuest();
    }

    // ── Permissions fines ─────────────────────────────────────────────

    public function test_assistant_ne_peut_pas_supprimer(): void
    {
        $facture = $this->facture($this->alpha);

        $this->actingAs($this->utilisateur('assistant-finance', $this->alpha))
            ->delete(route('factures.destroy', $facture))
            ->assertForbidden();

        $this->assertDatabaseHas('factures', ['id' => $facture->id]);
    }

    public function test_responsable_peut_supprimer(): void
    {
        $facture = $this->facture($this->alpha);

        $this->actingAs($this->utilisateur('responsable-finance', $this->alpha))
            ->delete(route('factures.destroy', $facture))
            ->assertRedirect(route('factures.index'));

        $this->assertDatabaseMissing('factures', ['id' => $facture->id]);
    }

    public function test_dg_ne_peut_que_consulter_les_utilisateurs(): void
    {
        $dg = $this->utilisateur('dg', null, 'dg');

        $this->actingAs($dg)->get(route('users.index'))->assertOk();
        $this->actingAs($dg)->get(route('users.create'))->assertForbidden();
    }

    public function test_contacts_interdits_sans_role_metier(): void
    {
        $this->actingAs($this->utilisateur('admin-systeme', null, 'dg'))
            ->get(route('contacts.index'))
            ->assertForbidden();
    }

    // ── Cloisonnement par agence ──────────────────────────────────────

    public function test_facture_d_une_autre_agence_introuvable(): void
    {
        $facture = $this->facture($this->alpha);

        $this->actingAs($this->utilisateur('responsable-finance', $this->beta))
            ->get(route('factures.show', $facture))
            ->assertNotFound();

        $this->actingAs($this->utilisateur('responsable-finance', $this->beta))
            ->delete(route('factures.destroy', $facture))
            ->assertNotFound();

        $this->actingAs($this->utilisateur('dg', null, 'dg'))
            ->get(route('factures.show', $facture))
            ->assertOk();
    }

    public function test_creation_forcee_dans_l_agence_de_l_utilisateur(): void
    {
        $this->actingAs($this->utilisateur('responsable-finance', $this->beta))
            ->post(route('factures.store'), [
                'type_facture' => 'vente', 'numero_facture' => 'FAC-X1', 'date_facture' => now()->toDateString(),
                'statut_facture' => 'brouillon', 'agence_id' => $this->alpha,
            ])->assertRedirect(route('factures.index'));

        $this->assertDatabaseHas('factures', ['numero_facture' => 'FAC-X1', 'agence_id' => $this->beta]);
    }

    public function test_article_ne_peut_pas_puiser_dans_le_depot_d_une_autre_agence(): void
    {
        $facture = $this->facture($this->beta, ['statut_facture' => 'brouillon']);
        $depotAlpha = DB::table('depots')->insertGetId(['nom' => 'Dépôt Alpha', 'agence_id' => $this->alpha]);
        $produit = DB::table('produits')->insertGetId([
            'code' => 'P1', 'nom' => 'Produit', 'unite_mesure' => 'piece', 'prix_achat' => 1, 'prix_vente' => 2, 'stock_min' => 0,
        ]);

        $this->actingAs($this->utilisateur('responsable-finance', $this->beta))
            ->post(route('factures.items.store', $facture), [
                'description' => 'Vol', 'quantite' => 1, 'prix_unitaire' => 1,
                'type_article' => 'produit', 'produit_id' => $produit, 'depot_id' => $depotAlpha,
            ])->assertForbidden();
    }

    public function test_utilisateur_sans_agence_ne_voit_rien(): void
    {
        DB::table('contacts')->insert([
            'raison_social' => 'Client', 'adresse_email' => 'c@test.local', 'telephone' => '0',
            'type_contact' => 'client', 'agence_id' => $this->alpha,
        ]);

        $this->actingAs($this->utilisateur('responsable-finance', null));

        $this->assertSame(0, \App\Models\Contact::count());
    }

    // ── Factures et SFEC ──────────────────────────────────────────────

    public function test_facture_certifiee_figee(): void
    {
        $facture = $this->facture($this->alpha, ['statut_certification' => 'certifiee', 'objet' => 'Original']);
        $this->actingAs($this->utilisateur('dg', null, 'dg'));

        $this->put(route('factures.update', $facture), [
            'type_facture' => 'vente', 'numero_facture' => $facture->numero_facture,
            'date_facture' => now()->toDateString(), 'objet' => 'Modifié',
        ])->assertSessionHas('error');
        $this->delete(route('factures.destroy', $facture))->assertSessionHas('error');
        $this->post(route('factures.items.store', $facture), ['description' => 'x', 'quantite' => 1, 'prix_unitaire' => 1])
            ->assertSessionHas('error');
        $this->post(route('factures.tva.toggle', $facture))->assertSessionHas('error');
        $this->post(route('factures.statut', $facture), ['statut_facture' => 'annulee'])->assertSessionHas('error');

        $this->assertDatabaseHas('factures', ['id' => $facture->id, 'objet' => 'Original', 'statut_facture' => 'impayee']);
        $this->assertSame(1, DB::table('item_factures')->where('facture_id', $facture->id)->count());
    }

    public function test_modification_ne_change_pas_le_statut(): void
    {
        $facture = $this->facture($this->alpha, ['statut_facture' => 'brouillon']);

        $this->actingAs($this->utilisateur('assistant-finance', $this->alpha))
            ->put(route('factures.update', $facture), [
                'type_facture' => 'vente', 'numero_facture' => $facture->numero_facture,
                'date_facture' => now()->toDateString(), 'statut_facture' => 'payee',
            ])->assertRedirect(route('factures.index'));

        $this->assertDatabaseHas('factures', ['id' => $facture->id, 'statut_facture' => 'brouillon']);
    }

    public function test_creation_en_brouillon_sans_droit_de_validation(): void
    {
        $this->actingAs($this->utilisateur('assistant-finance', $this->alpha));

        $this->post(route('factures.store'), [
            'type_facture' => 'vente', 'numero_facture' => 'FAC-X2',
            'date_facture' => now()->toDateString(), 'statut_facture' => 'impayee',
        ]);
        $this->assertDatabaseHas('factures', ['numero_facture' => 'FAC-X2', 'statut_facture' => 'brouillon']);

        // « payée » ne se choisit pas : il découle des paiements enregistrés.
        $this->post(route('factures.store'), [
            'type_facture' => 'vente', 'numero_facture' => 'FAC-X3',
            'date_facture' => now()->toDateString(), 'statut_facture' => 'payee',
        ])->assertSessionHasErrors('statut_facture');
    }

    public function test_un_seul_avoir_par_facture(): void
    {
        $origine = $this->facture($this->alpha);
        $this->facture($this->alpha, ['type_facture' => 'avoir', 'facture_origine_id' => $origine->id]);

        $this->actingAs($this->utilisateur('dg', null, 'dg'))
            ->post(route('factures.store'), [
                'type_facture' => 'avoir', 'numero_facture' => 'AV-2', 'date_facture' => now()->toDateString(),
                'statut_facture' => 'impayee', 'facture_origine_id' => $origine->id,
            ])->assertSessionHasErrors('facture_origine_id');
    }

    public function test_sfec_refuse_un_brouillon_sans_appel_api(): void
    {
        Http::fake();
        $facture = $this->facture($this->alpha, ['statut_facture' => 'brouillon']);

        $this->actingAs($this->utilisateur('dg', null, 'dg'))
            ->post(route('factures.certifier', $facture))
            ->assertSessionHas('error');

        Http::assertNothingSent();
    }

    public function test_sfec_conflit_409_sur_une_autre_facture_ne_certifie_pas(): void
    {
        Http::fake([
            '*/api/v1/invoices' => Http::response(['message' => 'Invoice already exists'], 409),
            '*/api/v1/invoices/*' => Http::response(['signature' => str_repeat('a', 64), 'total_amount' => 999999], 200),
        ]);
        $facture = $this->facture($this->alpha);

        $this->actingAs($this->utilisateur('dg', null, 'dg'))
            ->post(route('factures.certifier', $facture))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('factures', ['id' => $facture->id, 'statut_certification' => 'echec']);
    }

    public function test_sfec_conflit_409_sur_la_meme_facture_recupere_la_signature(): void
    {
        Http::fake([
            '*/api/v1/invoices' => Http::response(['message' => 'Invoice already exists'], 409),
            '*/api/v1/invoices/*' => Http::response([
                'certification_signature' => str_repeat('b', 64), 'total_amount' => 10000,
            ], 200),
        ]);
        $facture = $this->facture($this->alpha);

        $this->actingAs($this->utilisateur('dg', null, 'dg'))
            ->post(route('factures.certifier', $facture))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('factures', [
            'id' => $facture->id, 'statut_certification' => 'certifiee', 'certification_signature' => str_repeat('b', 64),
        ]);
    }

    // ── Gestion des utilisateurs ──────────────────────────────────────

    public function test_admin_ne_modifie_pas_ses_propres_roles(): void
    {
        $admin = $this->utilisateur('admin-systeme', null, 'dg');

        $this->actingAs($admin)->put(route('users.update', $admin), [
            'name' => 'Admin', 'email' => $admin->email, 'scope' => 'dg', 'roles' => ['dg'],
        ]);

        $this->assertTrue($admin->fresh()->hasRole('admin-systeme'));
        $this->assertFalse($admin->fresh()->hasRole('dg'));
    }

    public function test_seul_un_admin_systeme_attribue_admin_systeme(): void
    {
        Role::create(['name' => 'gestionnaire-comptes'])
            ->givePermissionTo(['administration.view', 'administration.create']);
        $gestionnaire = $this->utilisateur('gestionnaire-comptes', null, 'dg');

        $this->actingAs($gestionnaire)->post(route('users.store'), [
            'name' => 'X', 'email' => 'x@test.local', 'password' => 'MotDePasse123',
            'scope' => 'dg', 'roles' => ['admin-systeme'],
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'x@test.local']);
    }
}
