<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\User;
use App\Support\AgencyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AvoirParcoursTest extends TestCase
{
    use RefreshDatabase;

    private int $agence;
    private int $contact;
    private Facture $vente;
    private int $ligneA;
    private int $ligneB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'Société', 'nui' => 'M0123456789012345']);
        $this->agence = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);
        $this->contact = DB::table('contacts')->insertGetId([
            'raison_social' => 'ACME SARL', 'adresse_email' => 'a@test.local', 'telephone' => '0',
            'type_contact' => 'client', 'agence_id' => $this->agence,
        ]);

        $id = DB::table('factures')->insertGetId([
            'type_facture' => 'vente', 'numero_facture' => 'FAC-00010', 'date_facture' => '2026-10-01',
            'statut_facture' => 'impayee', 'statut_certification' => 'certifiee', 'tva_active' => true,
            'centimes_additionnels_actif' => false, 'agence_id' => $this->agence, 'contact_id' => $this->contact,
            'interlocuteur' => 'M. Mavoungou',
        ]);
        $this->ligneA = DB::table('item_factures')->insertGetId([
            'description' => 'Ordinateur', 'quantite' => 3, 'prix_unitaire' => 100000, 'taux_tva' => 18,
            'type_article' => 'service', 'facture_id' => $id,
        ]);
        $this->ligneB = DB::table('item_factures')->insertGetId([
            'description' => 'Installation', 'quantite' => 1, 'prix_unitaire' => 50000, 'taux_tva' => 18,
            'type_article' => 'service', 'facture_id' => $id,
        ]);
        $this->vente = Facture::find($id);

        $dg = User::create(['name' => 'DG', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $dg->syncRoles(['dg']);
        $this->actingAs($dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    private function lignes(array $surcharges = []): array
    {
        $lignes = [
            $this->ligneA => ['inclure' => '1', 'description' => 'Ordinateur', 'quantite' => 3, 'prix_unitaire' => 100000],
            $this->ligneB => ['inclure' => '1', 'description' => 'Installation', 'quantite' => 1, 'prix_unitaire' => 50000],
        ];
        foreach ($surcharges as $id => $champs) {
            $champs === null ? $lignes[$id] = [] : $lignes[$id] = array_merge($lignes[$id], $champs);
        }

        return ['numero_facture' => 'FAC-00011', 'date_facture' => '2026-10-13', 'objet' => 'Retour', 'lignes' => $lignes];
    }

    // ── Étape 1 : recherche par numéro ────────────────────────────────

    public function test_recherche_par_numero(): void
    {
        $this->get(route('factures.avoirs.create'))->assertOk()->assertSee('FAC-00010');

        $this->get(route('factures.avoirs.create', ['numero' => 'FAC-99999']))
            ->assertSessionHasErrors(['numero' => 'Aucune facture n° FAC-99999 trouvée.']);

        $this->get(route('factures.avoirs.create', ['numero' => 'FAC-00010']))
            ->assertRedirect(route('factures.avoirs.preparer', $this->vente));
    }

    public function test_brouillon_refuse_sauf_si_deja_certifie(): void
    {
        $this->vente->update(['statut_facture' => 'brouillon', 'statut_certification' => 'non_certifiee']);
        $this->get(route('factures.avoirs.create', ['numero' => 'FAC-00010']))
            ->assertSessionHasErrors('numero');

        // Certifiée, elle est figée : l'avoir est la seule correction possible.
        $this->vente->update(['statut_certification' => 'certifiee']);
        $this->get(route('factures.avoirs.create', ['numero' => 'FAC-00010']))
            ->assertRedirect(route('factures.avoirs.preparer', $this->vente));
    }

    // ── Étape 2 : lignes reprises ─────────────────────────────────────

    public function test_preparation_affiche_les_lignes_de_la_facture(): void
    {
        $this->get(route('factures.avoirs.preparer', $this->vente))
            ->assertOk()
            ->assertSee('ACME SARL')
            ->assertSee('Ordinateur')
            ->assertSee('Installation')
            ->assertSee('max 3');
    }

    public function test_avoir_total_reprend_tout(): void
    {
        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes())->assertSessionHasNoErrors();

        $avoir = Facture::where('numero_facture', 'FAC-00011')->first();
        $this->assertSame('avoir', $avoir->type_facture->value);
        $this->assertSame('brouillon', $avoir->statut_facture->value);
        $this->assertSame($this->vente->id, $avoir->facture_origine_id);
        $this->assertSame($this->contact, $avoir->contact_id);
        $this->assertSame('M. Mavoungou', $avoir->interlocuteur);
        $this->assertTrue($avoir->tva_active);
        $this->assertSame(2, $avoir->items()->count());
        $this->assertEqualsWithDelta($this->vente->total_ttc, $avoir->fresh()->load('items')->total_ttc, 0.01);
        $this->assertTrue($this->vente->fresh()->estAnnuleeParAvoir());
    }

    public function test_avoir_partiel(): void
    {
        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes([
            $this->ligneA => ['quantite' => 1],
            $this->ligneB => null,
        ]))->assertSessionHasNoErrors();

        $avoir = Facture::where('numero_facture', 'FAC-00011')->first();
        $this->assertSame(1, $avoir->items()->count());
        $this->assertSame(1, (int) $avoir->items()->first()->quantite);
        $this->assertFalse($this->vente->fresh()->estAnnuleeParAvoir());
    }

    public function test_plafonds_par_ligne(): void
    {
        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes([$this->ligneA => ['quantite' => 4]]))
            ->assertSessionHasErrors("lignes.{$this->ligneA}.quantite");

        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes([$this->ligneB => ['prix_unitaire' => 60000]]))
            ->assertSessionHasErrors("lignes.{$this->ligneB}.prix_unitaire");

        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes([$this->ligneA => null, $this->ligneB => null]))
            ->assertSessionHasErrors('lignes');

        $this->assertDatabaseMissing('factures', ['numero_facture' => 'FAC-00011']);
    }

    public function test_un_seul_avoir(): void
    {
        $this->post(route('factures.avoirs.store', $this->vente), $this->lignes());

        $this->get(route('factures.avoirs.preparer', $this->vente))->assertRedirect(route('factures.avoirs.create'));
        $this->post(route('factures.avoirs.store', $this->vente), array_merge($this->lignes(), ['numero_facture' => 'FAC-00012']))
            ->assertSessionHasErrors('numero');
        $this->assertDatabaseMissing('factures', ['numero_facture' => 'FAC-00012']);
    }

    // ── Accès et anciens liens ────────────────────────────────────────

    public function test_lecture_seule_ne_peut_pas_creer_d_avoir(): void
    {
        Role::create(['name' => 'lecteur-finance'])->givePermissionTo('finance.view');
        $lecteur = User::create(['name' => 'L', 'email' => 'l@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $lecteur->syncRoles(['lecteur-finance']);

        $this->actingAs($lecteur)->get(route('factures.avoirs.preparer', $this->vente))->assertForbidden();
    }

    public function test_ancien_lien_redirige_vers_le_parcours(): void
    {
        $this->get(route('factures.create', ['avoir_de' => $this->vente->id]))
            ->assertRedirect(route('factures.avoirs.preparer', $this->vente->id));
    }
}
