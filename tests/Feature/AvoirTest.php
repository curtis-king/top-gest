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

class AvoirTest extends TestCase
{
    use RefreshDatabase;

    private int $agence;
    private int $depot;
    private int $produit;
    private User $dg;

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

        $this->dg = User::create(['name' => 'DG', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $this->dg->syncRoles(['dg']);
        $this->actingAs($this->dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    private function facture(string $type, array $attributs = []): Facture
    {
        $id = DB::table('factures')->insertGetId(array_merge([
            'type_facture' => $type,
            'numero_facture' => uniqid(strtoupper($type).'-'),
            'date_facture' => '2026-10-01',
            'statut_facture' => 'brouillon',
            'statut_certification' => 'non_certifiee',
            'tva_active' => false,
            'centimes_additionnels_actif' => false,
            'agence_id' => $this->agence,
        ], $attributs));

        return Facture::find($id);
    }

    private function ajouterProduit(Facture $facture, int $quantite, int $prix = 1000)
    {
        return $this->post(route('factures.items.store', $facture), [
            'description' => 'Produit', 'quantite' => $quantite, 'prix_unitaire' => $prix,
            'type_article' => 'produit', 'produit_id' => $this->produit, 'depot_id' => $this->depot,
        ]);
    }

    private function stock(): int
    {
        return (int) DB::table('stocks')->where('produit_id', $this->produit)->where('depot_id', $this->depot)->value('quantite');
    }

    private function valider(Facture $facture): void
    {
        $this->post(route('factures.statut', $facture), ['statut_facture' => 'impayee']);
    }

    // ── Stock ─────────────────────────────────────────────────────────

    public function test_vente_sort_du_stock_et_avoir_le_remet(): void
    {
        $vente = $this->facture('vente');
        $this->ajouterProduit($vente, 10);
        $this->valider($vente);
        $this->assertSame(90, $this->stock());

        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id]);
        $this->ajouterProduit($avoir, 4);
        $this->valider($avoir);

        $this->assertSame(94, $this->stock());
        $this->assertDatabaseHas('mouvements_stocks', ['facture_id' => $avoir->id, 'type_mouvement' => 'entree', 'quantite' => 4]);
    }

    public function test_achat_entre_en_stock_et_proforma_ne_bouge_rien(): void
    {
        $achat = $this->facture('achat');
        $this->ajouterProduit($achat, 5);
        $this->valider($achat);
        $this->assertSame(105, $this->stock());

        $proforma = $this->facture('proforma');
        $this->ajouterProduit($proforma, 7);
        $this->valider($proforma);
        $this->assertSame(105, $this->stock());
        $this->assertDatabaseMissing('mouvements_stocks', ['facture_id' => $proforma->id]);
    }

    public function test_suppression_d_un_article_d_avoir_contre_passe_l_entree(): void
    {
        $vente = $this->facture('vente', ['statut_facture' => 'impayee']);
        $this->ajouterProduit($vente, 10);
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id, 'statut_facture' => 'impayee']);
        $this->ajouterProduit($avoir, 3);
        $this->assertSame(93, $this->stock());

        $item = $avoir->items()->first();
        $this->delete(route('factures.items.destroy', $item));

        $this->assertSame(90, $this->stock());
    }

    // ── Plafonds ──────────────────────────────────────────────────────

    public function test_avoir_ne_retourne_pas_plus_que_la_quantite_vendue(): void
    {
        $vente = $this->facture('vente');
        $this->ajouterProduit($vente, 2);
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id]);

        $this->ajouterProduit($avoir, 3)->assertSessionHas('error');
        $this->assertSame(0, $avoir->items()->count());
    }

    public function test_avoir_ne_depasse_pas_le_montant_de_la_facture(): void
    {
        $vente = $this->facture('vente');
        $this->ajouterProduit($vente, 2, 1000);
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id]);

        $this->ajouterProduit($avoir, 2, 5000)->assertSessionHas('error');
        $this->assertSame(0, $avoir->items()->count());

        $this->ajouterProduit($avoir, 2, 1000)->assertSessionHas('success');
        $this->post(route('factures.tva.toggle', $avoir))->assertSessionHas('error');
        $this->assertFalse($avoir->fresh()->tva_active);
    }

    // ── Comptabilité, PDF, affichage, statistiques ────────────────────

    public function test_ecriture_proposee_pour_un_avoir(): void
    {
        $vente = $this->facture('vente', ['statut_facture' => 'payee']);
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id, 'statut_facture' => 'payee']);
        DB::table('item_factures')->insert([
            'description' => 'Remise', 'quantite' => 1, 'prix_unitaire' => 300, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $avoir->id,
        ]);

        $this->get(route('ecritures-comptables.create', ['source_type' => 'facture', 'source_id' => $avoir->id]))
            ->assertOk()
            ->assertViewHas('prefill', function ($prefill) use ($vente) {
                $parCompte = collect($prefill['lignes'])->keyBy(
                    fn ($l) => DB::table('comptes_comptables')->where('id', $l['compte_comptable_id'])->value('numero_compte')
                );

                return $prefill['journal_code'] === 'VE'
                    && $parCompte['701000']['debit'] == 300
                    && $parCompte['411000']['credit'] == 300
                    && str_contains($prefill['libelle'], $vente->numero_facture);
            });
    }

    public function test_pdf_de_l_avoir_reference_la_facture_d_origine(): void
    {
        $vente = $this->facture('vente');
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id]);
        $avoir->load('items');

        $html = view('factures.pdf.impression', ['facture' => $avoir, 'client' => null, 'total' => 0])->render();

        $this->assertStringContainsString("Avoir sur facture n° :</strong> {$vente->numero_facture} du 01-10-2026", $html);
    }

    public function test_facture_d_origine_indique_l_avoir_et_stats_nettes(): void
    {
        $vente = $this->facture('vente', ['statut_facture' => 'payee', 'statut_certification' => 'certifiee']);
        DB::table('item_factures')->insert([
            'description' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => 1000, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $vente->id,
        ]);
        $avoir = $this->facture('avoir', ['facture_origine_id' => $vente->id, 'statut_facture' => 'payee']);
        DB::table('item_factures')->insert([
            'description' => 'Remise', 'quantite' => 1, 'prix_unitaire' => 400, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $avoir->id,
        ]);

        $this->get(route('factures.manage', $vente))->assertSee('Corrigée par l')->assertSee($avoir->numero_facture);

        $this->get(route('factures.index'))
            ->assertSee('corrigée par avoir')
            ->assertViewHas('stats', fn ($stats) => $stats['montant_payee'] == 600);
    }
}
