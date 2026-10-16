<?php

namespace Tests\Feature;

use App\Models\Facture;
use App\Models\User;
use App\Support\AgencyContext;
use Database\Seeders\JournauxComptablesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanComptableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CycleFactureTest extends TestCase
{
    use RefreshDatabase;

    private int $agence;
    private int $depot;
    private int $produit;
    private int $banque;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, PlanComptableSeeder::class, JournauxComptablesSeeder::class]);
        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'Société', 'nui' => 'M0123456789012345']);
        $this->agence = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);
        $this->depot = DB::table('depots')->insertGetId(['nom' => 'Dépôt', 'agence_id' => $this->agence]);
        $this->produit = DB::table('produits')->insertGetId([
            'code' => 'P1', 'nom' => 'Produit', 'unite_mesure' => 'piece', 'prix_achat' => 500, 'prix_vente' => 1000, 'stock_min' => 0,
        ]);
        DB::table('stocks')->insert(['produit_id' => $this->produit, 'depot_id' => $this->depot, 'quantite' => 100]);
        $this->banque = DB::table('banques')->insertGetId(['nom' => 'BGFI', 'numero_compte' => '00001']);

        $dg = User::create(['name' => 'DG', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $dg->syncRoles(['dg']);
        $this->actingAs($dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    /** Vente de 10 produits à 1 000 (HT 10 000), en brouillon. */
    private function vente(array $attributs = []): Facture
    {
        $id = DB::table('factures')->insertGetId(array_merge([
            'type_facture' => 'vente', 'numero_facture' => uniqid('FAC-'), 'date_facture' => '2026-10-01',
            'statut_facture' => 'brouillon', 'statut_certification' => 'non_certifiee',
            'tva_active' => false, 'centimes_additionnels_actif' => false, 'agence_id' => $this->agence,
        ], $attributs));
        DB::table('item_factures')->insert([
            'description' => 'Produit', 'quantite' => 10, 'prix_unitaire' => 1000, 'taux_tva' => 0,
            'type_article' => 'produit', 'produit_id' => $this->produit, 'depot_id' => $this->depot, 'facture_id' => $id,
        ]);

        return Facture::find($id);
    }

    private function stock(): int
    {
        return (int) DB::table('stocks')->where('produit_id', $this->produit)->value('quantite');
    }

    private function statut(Facture $f, string $cible)
    {
        return $this->post(route('factures.statut', $f), ['statut_facture' => $cible]);
    }

    private function payer(Facture $f, float $montant)
    {
        return $this->post(route('factures.paiements.store', $f), [
            'date_paiement' => '2026-10-10', 'montant' => $montant, 'mode_paiement' => 'virement', 'banque_id' => $this->banque, 'reference' => 'VIR-1',
        ]);
    }

    // ── 1. Stock suivi au fil des statuts ─────────────────────────────

    public function test_brouillon_et_annulation_contre_passent_le_stock(): void
    {
        $f = $this->vente();

        $this->statut($f, 'impayee')->assertSessionHas('success');
        $this->assertSame(90, $this->stock());

        $this->statut($f->fresh(), 'brouillon')->assertSessionHas('success');
        $this->assertSame(100, $this->stock());

        $this->statut($f->fresh(), 'impayee');
        $this->assertSame(90, $this->stock(), 'revalidation : une seule sortie, pas de doublon');

        $this->statut($f->fresh(), 'annulee')->assertSessionHas('success');
        $this->assertSame(100, $this->stock());

        $this->statut($f->fresh(), 'brouillon');
        $this->assertSame(100, $this->stock());
    }

    public function test_article_ajoute_sur_facture_annulee_ne_bouge_pas_le_stock(): void
    {
        $f = $this->vente(['statut_facture' => 'annulee']);

        $this->post(route('factures.items.store', $f), [
            'description' => 'x', 'quantite' => 5, 'prix_unitaire' => 1,
            'type_article' => 'produit', 'produit_id' => $this->produit, 'depot_id' => $this->depot,
        ]);

        $this->assertSame(100, $this->stock());
    }

    // ── 2. Transitions encadrées et historisées ───────────────────────

    public function test_statut_de_paiement_non_modifiable_a_la_main(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee']);

        $this->statut($f, 'payee')->assertSessionHas('error', 'Le statut de paiement se met à jour automatiquement : enregistrez un paiement.');
        $this->assertSame('impayee', $f->fresh()->statut_facture->value);
    }

    public function test_facture_certifiee_ne_s_annule_pas(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee', 'statut_certification' => 'certifiee']);

        $this->assertSame([], $f->transitionsPossibles());
        $this->statut($f, 'annulee')->assertSessionHas('error');
    }

    public function test_historique_des_evenements(): void
    {
        $f = $this->vente();
        $this->statut($f, 'impayee');
        $this->payer($f->fresh(), 4000);

        $actions = DB::table('historique_factures')->where('facture_id', $f->id)->orderBy('id')->get();
        $this->assertSame(['statut', 'paiement', 'statut'], $actions->pluck('action')->all());
        $this->assertSame(['brouillon', 'impayee'], [$actions[0]->ancien_statut, $actions[0]->nouveau_statut]);
        $this->assertSame('partielle', $actions[2]->nouveau_statut);
        $this->assertNotNull($actions[0]->user_id);

        // L'historique de la page de gestion reprend les libellés des actions.
        $this->get(route('factures.manage', $f))
            ->assertSee('Valider la facture')
            ->assertSee('Paiement de 4 000 FCFA')
            ->assertSee('Mis à jour selon les paiements');
    }

    // ── 3. Paiements → statut ─────────────────────────────────────────

    public function test_paiements_partiels_puis_complets(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee']);

        $this->payer($f, 4000)->assertSessionHas('success');
        $this->assertSame('partielle', $f->fresh()->statut_facture->value);
        $this->assertEquals(6000, $f->fresh()->resteAPayer());

        $this->payer($f->fresh(), 7000)->assertSessionHas('error');
        $this->payer($f->fresh(), 6000)->assertSessionHas('success');
        $this->assertSame('payee', $f->fresh()->statut_facture->value);

        $paiement = $f->paiements()->first();
        $this->delete(route('factures.paiements.destroy', $paiement))->assertSessionHas('success');
        $this->assertSame('partielle', $f->fresh()->statut_facture->value);
    }

    public function test_pas_de_paiement_sur_brouillon_ni_proforma(): void
    {
        $this->payer($this->vente(), 1000)->assertSessionHas('error');
        $this->payer($this->vente(['type_facture' => 'proforma', 'statut_facture' => 'impayee']), 1000)->assertSessionHas('error');
        $this->assertSame(0, DB::table('paiements_factures')->count());
    }

    public function test_paiements_bloquent_brouillon_annulation_et_suppression(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee']);
        $this->payer($f, 1000);

        $this->statut($f->fresh(), 'annulee')->assertSessionHas('error');
        $this->statut($f->fresh(), 'brouillon')->assertSessionHas('error');
        $this->delete(route('factures.destroy', $f))->assertSessionHas('error');
        $this->assertDatabaseHas('factures', ['id' => $f->id, 'statut_facture' => 'partielle']);
    }

    public function test_ajout_d_article_sur_facture_payee_la_repasse_en_partielle(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee']);
        $this->payer($f, 10000);
        $this->assertSame('payee', $f->fresh()->statut_facture->value);

        $this->post(route('factures.items.store', $f), ['description' => 'Frais', 'quantite' => 1, 'prix_unitaire' => 500]);

        $this->assertSame('partielle', $f->fresh()->statut_facture->value);
    }

    // ── 4. Écriture TTC ───────────────────────────────────────────────

    public function test_ecriture_proposee_ttc_avec_tva_et_centimes(): void
    {
        $f = $this->vente(['statut_facture' => 'impayee', 'tva_active' => true, 'centimes_additionnels_actif' => true]);
        DB::table('item_factures')->where('facture_id', $f->id)->update(['taux_tva' => 18]);

        $this->get(route('ecritures-comptables.create', ['source_type' => 'facture', 'source_id' => $f->id]))
            ->assertOk()
            ->assertViewHas('prefill', function ($prefill) {
                $l = collect($prefill['lignes'])->keyBy(
                    fn ($x) => DB::table('comptes_comptables')->where('id', $x['compte_comptable_id'])->value('numero_compte')
                );

                // HT 10 000, TVA 1 800, centimes 90 → TTC 11 890
                return $l['411000']['debit'] == 11890
                    && $l['701000']['credit'] == 10000
                    && $l['445000']['credit'] == 1800
                    && $l['443400']['credit'] == 90
                    && collect($prefill['lignes'])->sum('debit') == collect($prefill['lignes'])->sum('credit');
            });
    }
}
