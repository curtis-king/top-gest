<?php

namespace Tests\Feature;

use App\Api\FacturePayloadBuilder;
use App\Models\Contact;
use App\Models\Facture;
use App\Models\User;
use App\Support\AgencyContext;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ContactSfecTest extends TestCase
{
    use RefreshDatabase;

    private int $agence;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionSeeder::class);
        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'Société', 'nui' => 'M0123456789012345']);
        $this->agence = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);

        $dg = User::create(['name' => 'DG', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $dg->syncRoles(['dg']);
        $this->actingAs($dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    private function client(array $champs): \Illuminate\Testing\TestResponse
    {
        return $this->post(route('contacts.store'), array_merge([
            'raison_social' => 'ACME SARL', 'adresse_email' => 'acme@test.local', 'telephone' => '060000000',
            'type_contact' => 'client', 'type_client_sfec' => 'entreprise', 'niu' => 'M2005110000123456',
        ], $champs));
    }

    private function contact(array $champs = []): Contact
    {
        $id = DB::table('contacts')->insertGetId(array_merge([
            'raison_social' => 'ACME SARL', 'adresse_email' => 'acme@test.local', 'telephone' => '060000000',
            'type_contact' => 'client', 'type_client_sfec' => 'entreprise', 'niu' => 'M2005110000123456',
            'adresse' => 'BP 100 Brazzaville', 'agence_id' => $this->agence,
        ], $champs));

        return Contact::find($id);
    }

    // ── Validation de la fiche ────────────────────────────────────────

    public function test_niu_obligatoire_et_de_16_ou_17_caracteres_pour_une_entreprise(): void
    {
        $this->client(['niu' => null])->assertSessionHasErrors(['niu' => 'Le NIU est obligatoire pour un client entreprise ou État (SFEC).']);
        $this->client(['niu' => 'M20051100001'])->assertSessionHasErrors(['niu' => 'Le NIU doit comporter 16 ou 17 caractères (SFEC).']);

        $this->client(['niu' => 'm2005 1100 0012 3456'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('contacts', ['raison_social' => 'ACME SARL', 'niu' => 'M2005110000123456']);
    }

    public function test_type_sfec_obligatoire_pour_un_client(): void
    {
        $this->client(['type_client_sfec' => null])->assertSessionHasErrors('type_client_sfec');
        $this->client(['type_client_sfec' => 'martien'])->assertSessionHasErrors('type_client_sfec');
    }

    public function test_adresse_obligatoire_pour_l_etat(): void
    {
        $this->client(['type_client_sfec' => 'etat'])->assertSessionHasErrors('adresse');
        $this->client(['type_client_sfec' => 'etat', 'adresse' => 'Ministère, Brazzaville'])->assertSessionHasNoErrors();
    }

    public function test_fournisseur_sans_champs_sfec(): void
    {
        $this->client(['type_contact' => 'fournisseur', 'type_client_sfec' => null, 'niu' => null])->assertSessionHasNoErrors();
    }

    // ── Envoi au SFEC ─────────────────────────────────────────────────

    public function test_rccm_et_assujettissement_transmis(): void
    {
        $contact = $this->contact(['rccm' => 'CG-BZV-01-2020-B12-00001', 'assujetti_tva' => false]);
        $facture = Facture::forceCreate([
            'type_facture' => 'vente', 'numero_facture' => 'FAC-1', 'date_facture' => now(), 'statut_facture' => 'impayee',
            'statut_certification' => 'non_certifiee', 'contact_id' => $contact->id, 'agence_id' => $this->agence,
        ]);

        $payload = app(FacturePayloadBuilder::class)->build($facture);

        $this->assertSame('business', $payload['recipient_type']);
        $this->assertSame('CG-BZV-01-2020-B12-00001', $payload['recipient_rccm']);
        $this->assertFalse($payload['is_recipient_taxable']);
    }

    public function test_pdf_certifie_garde_le_client_tel_que_certifie(): void
    {
        Http::fake(['*/api/v1/invoices' => Http::response([
            'signature' => str_repeat('a', 64), 'certification_number' => str_repeat('a', 64),
            'short_signature' => 'AAAAAAAAAAAAAAAAAAAA', 'identifier' => 'AAAAAAAAAAAAAAAAAAAA',
            'invoice_number' => 'FAC-2', 'certification_date' => now()->toRfc3339String(), 'qr_code' => null,
        ], 201)]);

        $contact = $this->contact();
        $facture = Facture::forceCreate([
            'type_facture' => 'vente', 'numero_facture' => 'FAC-2', 'date_facture' => now(), 'statut_facture' => 'impayee',
            'statut_certification' => 'non_certifiee', 'contact_id' => $contact->id, 'agence_id' => $this->agence,
        ]);
        DB::table('item_factures')->insert([
            'description' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => 1000, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $facture->id,
        ]);

        $this->post(route('factures.certifier', $facture))->assertSessionHas('success');
        $contact->update(['raison_social' => 'NOUVEAU NOM', 'niu' => 'X9999999999999999']);

        $facture = $facture->fresh();
        $this->assertSame('ACME SARL', $facture->client_certifie['recipient_name']);

        $html = view('factures.pdf.impression', ['facture' => $facture, 'client' => null, 'total' => 1000])->render();
        $this->assertStringContainsString('ACME SARL', $html);
        $this->assertStringContainsString('M2005110000123456', $html);
        $this->assertStringNotContainsString('NOUVEAU NOM', $html);
    }

    // ── Indicateurs ───────────────────────────────────────────────────

    public function test_indicateurs_et_commande_de_verification(): void
    {
        $incomplet = $this->contact(['raison_social' => 'SANS NIU', 'niu' => null]);
        $this->contact(['raison_social' => 'COMPLET']);

        $this->get(route('contacts.show', $incomplet))->assertSee('Incomplet pour la certification');
        $this->get(route('contacts.index'))->assertSee('SFEC incomplet')->assertSee('SFEC prêt');
        $this->get(route('factures.create'))->assertSee('SANS NIU — SFEC incomplet');

        $this->artisan('sfec:verifier-contacts')
            ->expectsOutputToContain('2 contact(s) vérifié(s), 1 incomplet(s)')
            ->assertFailed();
    }
}
