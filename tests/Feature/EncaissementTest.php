<?php

namespace Tests\Feature;

use App\Models\EcritureComptable;
use App\Models\Facture;
use App\Models\PaiementFacture;
use App\Models\User;
use App\Support\AgencyContext;
use Database\Seeders\JournauxComptablesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanComptableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EncaissementTest extends TestCase
{
    use RefreshDatabase;

    private int $agence;
    private int $contact;
    private int $banque;
    private int $compteBanque;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, PlanComptableSeeder::class, JournauxComptablesSeeder::class]);
        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'Société', 'nui' => 'M0123456789012345']);
        $this->agence = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);
        $this->contact = DB::table('contacts')->insertGetId([
            'raison_social' => 'ACME SARL', 'adresse_email' => 'a@test.local', 'telephone' => '0',
            'type_contact' => 'client', 'agence_id' => $this->agence,
        ]);
        $this->compteBanque = DB::table('comptes_comptables')->insertGetId([
            'numero_compte' => '521100', 'libelle' => 'BGFI', 'classe' => 5, 'type_compte' => 'tresorerie', 'sens_normal' => 'debit',
        ]);
        $this->banque = DB::table('banques')->insertGetId(['nom' => 'BGFI', 'numero_compte' => '0001', 'compte_comptable_id' => $this->compteBanque]);

        $dg = User::create(['name' => 'DG', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $dg->syncRoles(['dg']);
        $this->actingAs($dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    private function facture(string $type, float $montant, array $attributs = []): Facture
    {
        $id = DB::table('factures')->insertGetId(array_merge([
            'type_facture' => $type, 'numero_facture' => uniqid('FAC-'), 'date_facture' => now()->toDateString(),
            'statut_facture' => 'impayee', 'statut_certification' => 'non_certifiee', 'tva_active' => false,
            'centimes_additionnels_actif' => false, 'agence_id' => $this->agence, 'contact_id' => $this->contact,
        ], $attributs));
        DB::table('item_factures')->insert([
            'description' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => $montant, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $id,
        ]);

        return Facture::find($id);
    }

    private function payer(Facture $f, float $montant, string $mode = 'virement', bool $avecBanque = true)
    {
        return $this->post(route('factures.paiements.store', $f), array_filter([
            'date_paiement' => now()->toDateString(), 'montant' => $montant, 'mode_paiement' => $mode,
            'banque_id' => $avecBanque ? $this->banque : null, 'reference' => 'REF-1',
        ]));
    }

    // ── Lien avec le livret bancaire ──────────────────────────────────

    public function test_virement_cree_et_supprime_la_ligne_de_livret(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 4000)->assertSessionHas('success');

        $paiement = PaiementFacture::first();
        $this->assertNotNull($paiement->livret_bancaire_id);
        $this->assertDatabaseHas('livrets_bancaires', [
            'id' => $paiement->livret_bancaire_id, 'type_action' => 'depot', 'montant' => 4000,
            'banque_id' => $this->banque, 'contact_id' => $this->contact, 'agence_id' => $this->agence,
        ]);

        $this->delete(route('factures.paiements.destroy', $paiement))->assertSessionHas('success');
        $this->assertSame(0, DB::table('livrets_bancaires')->count());
    }

    public function test_banque_obligatoire_pour_un_virement_pas_pour_les_especes(): void
    {
        $f = $this->facture('vente', 10000);

        $this->payer($f, 1000, 'virement', false)->assertSessionHasErrors('banque_id');
        $this->payer($f, 1000, 'especes', false)->assertSessionHas('success');
        $this->assertNull(PaiementFacture::first()->livret_bancaire_id);
    }

    public function test_ligne_de_livret_issue_d_un_paiement_verrouillee(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 4000);
        $livret = PaiementFacture::first()->livret_bancaire_id;

        $this->get(route('livrets-bancaires.edit', $livret))->assertSessionHas('error');
        $this->delete(route('livrets-bancaires.destroy', $livret))->assertSessionHas('error');
        $this->assertDatabaseHas('livrets_bancaires', ['id' => $livret]);
    }

    // ── Avoirs et reste dû ────────────────────────────────────────────

    public function test_avoir_sur_facture_impayee_reduit_le_reste_du(): void
    {
        $vente = $this->facture('vente', 10000);
        $avoir = $this->facture('avoir', 4000, ['facture_origine_id' => $vente->id, 'statut_facture' => 'brouillon']);

        $this->post(route('factures.statut', $avoir), ['statut_facture' => 'impayee'])->assertSessionHas('success');

        $this->assertEquals(6000, $vente->fresh()->resteAPayer());
        $this->assertSame('impayee', $vente->fresh()->statut_facture->value);
        $this->assertEquals(0, $avoir->fresh()->montantDu(), 'rien à rembourser : le client n\'avait pas payé');
        $this->assertSame('payee', $avoir->fresh()->statut_facture->value);
    }

    public function test_avoir_total_solde_la_facture(): void
    {
        $vente = $this->facture('vente', 10000);
        $avoir = $this->facture('avoir', 10000, ['facture_origine_id' => $vente->id, 'statut_facture' => 'brouillon']);

        $this->post(route('factures.statut', $avoir), ['statut_facture' => 'impayee']);

        $this->assertSame('payee', $vente->fresh()->statut_facture->value);
        $this->get(route('factures.manage', $vente))->assertSee('Soldée par avoir.');
    }

    public function test_avoir_sur_facture_payee_donne_un_remboursement(): void
    {
        $vente = $this->facture('vente', 10000);
        $this->payer($vente, 10000);
        $avoir = $this->facture('avoir', 4000, ['facture_origine_id' => $vente->id, 'statut_facture' => 'brouillon']);
        $this->post(route('factures.statut', $avoir), ['statut_facture' => 'impayee']);

        $avoir = $avoir->fresh();
        $this->assertEquals(4000, $avoir->resteAPayer());
        $this->assertSame('impayee', $avoir->statut_facture->value);
        $this->get(route('creances.index'))->assertSee($avoir->numero_facture);

        $this->payer($avoir, 4000, 'especes', false)->assertSessionHas('success');
        $this->assertSame('payee', $avoir->fresh()->statut_facture->value);
        $this->assertSame('payee', $vente->fresh()->statut_facture->value);
    }

    // ── Pages Encaissements et Créances ───────────────────────────────

    public function test_page_encaissements(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 3000);
        $this->payer($f->fresh(), 2000, 'especes', false);

        $this->get(route('encaissements.index'))
            ->assertOk()
            ->assertSee($f->numero_facture)
            ->assertViewHas('total', 5000.0)
            ->assertViewHas('parMode', fn ($m) => $m->firstWhere('mode_paiement', 'virement')->total == 3000);

        $this->get(route('encaissements.index', ['mode_paiement' => 'especes']))->assertViewHas('total', 2000.0);
    }

    public function test_page_creances_par_anciennete(): void
    {
        $vieille = $this->facture('vente', 8000, ['date_facture' => now()->subDays(100)->toDateString()]);
        $recente = $this->facture('vente', 5000);
        $this->payer($recente, 2000);
        $reglee = $this->facture('vente', 1000);
        $this->payer($reglee, 1000);

        $this->get(route('creances.index'))
            ->assertOk()
            ->assertSee($vieille->numero_facture)
            ->assertSee($recente->numero_facture)
            ->assertDontSee($reglee->numero_facture)
            ->assertViewHas('total', 11000.0)
            ->assertViewHas('parTranche', fn ($t) => $t['90+']['montant'] == 8000 && $t['0-30']['montant'] == 3000);
    }

    // ── Comptabilisation des règlements ───────────────────────────────

    public function test_ecriture_proposee_pour_un_paiement(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 4000);
        $paiement = PaiementFacture::first();

        $this->get(route('ecritures-comptables.create', ['source_type' => 'paiement_facture', 'source_id' => $paiement->id]))
            ->assertOk()
            ->assertViewHas('prefill', fn ($p) => $p['journal_code'] === 'BQ'
                && $p['lignes'][0]['compte_comptable_id'] === $this->compteBanque && $p['lignes'][0]['debit'] == 4000
                && DB::table('comptes_comptables')->where('id', $p['lignes'][1]['compte_comptable_id'])->value('numero_compte') === '411000'
                && $p['lignes'][1]['credit'] == 4000);

        // Le livret lié renvoie vers l'écriture du paiement (pas de double comptabilisation).
        $this->get(route('ecritures-comptables.create', ['source_type' => 'livret_bancaire', 'source_id' => $paiement->livret_bancaire_id]))
            ->assertRedirect(route('ecritures-comptables.create', ['source_type' => 'paiement_facture', 'source_id' => $paiement->id]));
    }

    public function test_paiement_en_especes_va_en_caisse(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 1000, 'especes', false);

        $this->get(route('ecritures-comptables.create', ['source_type' => 'paiement_facture', 'source_id' => PaiementFacture::first()->id]))
            ->assertViewHas('prefill', fn ($p) => $p['journal_code'] === 'CA'
                && DB::table('comptes_comptables')->where('id', $p['lignes'][0]['compte_comptable_id'])->value('numero_compte') === '570000');
    }

    public function test_paiement_comptabilise_non_supprimable(): void
    {
        $f = $this->facture('vente', 10000);
        $this->payer($f, 1000);
        $paiement = PaiementFacture::first();
        EcritureComptable::forceCreate([
            'numero_ecriture' => 'BQ-2026-000001', 'journal_comptable_id' => DB::table('journaux_comptables')->where('code', 'BQ')->value('id'),
            'date_ecriture' => now(), 'libelle' => 'x', 'statut' => 'brouillon', 'source_type' => 'paiement_facture', 'source_id' => $paiement->id,
            'agence_id' => $this->agence,
        ]);

        $this->delete(route('factures.paiements.destroy', $paiement))->assertSessionHas('error');
        $this->assertDatabaseHas('paiements_factures', ['id' => $paiement->id]);
    }
}
