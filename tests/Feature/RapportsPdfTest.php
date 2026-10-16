<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\AgencyContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Database\Seeders\JournauxComptablesSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanComptableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RapportsPdfTest extends TestCase
{
    use RefreshDatabase;

    private int $alpha;
    private int $beta;
    private int $contact;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([PermissionSeeder::class, PlanComptableSeeder::class, JournauxComptablesSeeder::class]);
        $compagnie = DB::table('compagnies')->insertGetId(['name' => 'TOPINFO', 'nui' => 'M26000000797821C', 'rccm' => 'CG-BZV-01-2026-B12-00122']);
        $this->alpha = DB::table('agences')->insertGetId(['name_agence' => 'Alpha', 'compagnie_id' => $compagnie]);
        $this->beta = DB::table('agences')->insertGetId(['name_agence' => 'Beta', 'compagnie_id' => $compagnie]);
        $this->contact = DB::table('contacts')->insertGetId([
            'raison_social' => 'ACME SARL', 'adresse_email' => 'a@test.local', 'telephone' => '060000000',
            'type_contact' => 'client', 'agence_id' => $this->alpha, 'niu' => 'M2005110000123456',
        ]);

        $dg = User::create(['name' => 'Directeur', 'email' => 'dg@test.local', 'password' => 'MotDePasse123', 'scope' => 'dg']);
        $dg->syncRoles(['dg']);
        $this->actingAs($dg);
    }

    protected function tearDown(): void
    {
        AgencyContext::flush();
        parent::tearDown();
    }

    /** Remplace DomPDF par un rendu HTML pour pouvoir vérifier le contenu des rapports. */
    private function rendreEnHtml(): void
    {
        Pdf::clearResolvedInstances();
        $this->app->bind('dompdf.wrapper', fn ($app) => new class($app['dompdf'], $app['config'], $app['files'], $app['view']) extends DomPdfWrapper {
            private string $rendu = '';

            public function loadView(string $view, array $data = [], array $mergeData = [], ?string $encoding = null): self
            {
                $this->rendu = view($view, $data, $mergeData)->render();

                return $this->loadHTML($this->rendu);
            }

            public function stream(string $filename = 'document.pdf'): Response
            {
                return new Response($this->rendu);
            }
        });
    }

    private function compte(string $numero): int
    {
        return DB::table('comptes_comptables')->where('numero_compte', $numero)->value('id');
    }

    /** Écriture validée équilibrée : vente de $montant (411 / 701). */
    private function ecritureVente(float $montant, string $date, int $agence, string $numero): void
    {
        $id = DB::table('ecritures_comptables')->insertGetId([
            'numero_ecriture' => $numero, 'journal_comptable_id' => DB::table('journaux_comptables')->where('code', 'VE')->value('id'),
            'date_ecriture' => $date, 'libelle' => "Facture {$numero}", 'statut' => 'validee', 'agence_id' => $agence,
        ]);
        DB::table('lignes_ecritures')->insert([
            ['ecriture_comptable_id' => $id, 'compte_comptable_id' => $this->compte('411000'), 'contact_id' => $this->contact, 'libelle' => "Facture {$numero}", 'debit' => $montant, 'credit' => 0, 'ordre' => 1],
            ['ecriture_comptable_id' => $id, 'compte_comptable_id' => $this->compte('701000'), 'contact_id' => null, 'libelle' => "Facture {$numero}", 'debit' => 0, 'credit' => $montant, 'ordre' => 2],
        ]);
    }

    private function facture(string $type, float $montant, string $date, array $attributs = []): int
    {
        $id = DB::table('factures')->insertGetId(array_merge([
            'type_facture' => $type, 'numero_facture' => uniqid('FAC-'), 'date_facture' => $date,
            'statut_facture' => 'impayee', 'statut_certification' => 'non_certifiee', 'tva_active' => false,
            'centimes_additionnels_actif' => false, 'agence_id' => $this->alpha, 'contact_id' => $this->contact,
        ], $attributs));
        DB::table('item_factures')->insert([
            'description' => 'Prestation', 'quantite' => 1, 'prix_unitaire' => $montant, 'taux_tva' => 0,
            'type_article' => 'service', 'facture_id' => $id,
        ]);

        return $id;
    }

    // ── Génération réelle ─────────────────────────────────────────────

    public function test_tous_les_rapports_produisent_un_pdf(): void
    {
        $this->ecritureVente(10000, '2026-10-01', $this->alpha, 'VE-2026-000001');
        $this->facture('vente', 5000, '2026-09-01');

        foreach ([
            route('comptabilite.balance.pdf'),
            route('comptabilite.bilan.pdf'),
            route('comptabilite.resultat.pdf'),
            route('comptabilite.grand-livre.pdf'),
            route('comptabilite.journal.pdf'),
            route('encaissements.pdf'),
            route('creances.pdf'),
            route('releves.pdf', $this->contact),
        ] as $url) {
            $reponse = $this->get($url);
            $reponse->assertOk();
            $this->assertSame('application/pdf', $reponse->headers->get('Content-Type'), $url);
            $this->assertStringStartsWith('%PDF', $reponse->getContent(), $url);
        }
    }

    // ── Contenu ───────────────────────────────────────────────────────

    public function test_en_tete_societe_et_grand_livre(): void
    {
        $this->rendreEnHtml();
        $this->ecritureVente(10000, '2026-10-01', $this->alpha, 'VE-2026-000001');
        $this->ecritureVente(2500, '2026-10-05', $this->alpha, 'VE-2026-000002');

        $this->get(route('comptabilite.grand-livre.pdf', ['compte_comptable_id' => $this->compte('411000')]))
            ->assertSee('TOPINFO')
            ->assertSee('NIU : M26000000797821C')
            ->assertSee('Grand livre — 411000')
            ->assertSee('VE-2026-000002')
            ->assertSee('12 500')          // solde cumulé
            ->assertSee('Édité le')
            ->assertSee('par Directeur');
    }

    public function test_journal_regroupe_par_journal(): void
    {
        $this->rendreEnHtml();
        $this->ecritureVente(10000, '2026-10-01', $this->alpha, 'VE-2026-000001');

        $this->get(route('comptabilite.journal.pdf'))
            ->assertSee('Journal général')
            ->assertSee('VE — Journal des ventes')
            ->assertSee('VE-2026-000001')
            ->assertSee('701000');
    }

    public function test_utilisateur_d_agence_ne_voit_pas_l_autre_agence(): void
    {
        $this->rendreEnHtml();
        $this->ecritureVente(10000, '2026-10-01', $this->alpha, 'VE-ALPHA');
        $this->ecritureVente(7000, '2026-10-01', $this->beta, 'VE-BETA');

        $financeBeta = User::create(['name' => 'F', 'email' => 'f@test.local', 'password' => 'MotDePasse123', 'scope' => 'agent']);
        $financeBeta->syncRoles(['responsable-finance']);
        DB::table('fonctions')->insert(['name' => 'Poste', 'salaire' => 1]);
        DB::table('employees')->insert(['nom_complet' => 'F', 'agence_id' => $this->beta, 'fonction_id' => 1, 'user_id' => $financeBeta->id]);

        $this->actingAs($financeBeta)
            ->get(route('comptabilite.journal.pdf', ['agence_id' => $this->alpha]))
            ->assertSee('VE-BETA')
            ->assertDontSee('VE-ALPHA')
            ->assertSee('Agence : Beta');
    }

    public function test_balance_agee_des_creances(): void
    {
        $this->rendreEnHtml();
        $this->facture('vente', 8000, now()->subDays(100)->toDateString());
        $this->facture('vente', 3000, now()->toDateString());

        $this->get(route('creances.pdf'))
            ->assertSee('Créances clients — balance âgée')
            ->assertSee('ACME SARL')
            ->assertSee('11 000')
            ->assertSee('8 000');
    }

    public function test_releve_client_avec_solde(): void
    {
        $this->rendreEnHtml();
        $vente = $this->facture('vente', 10000, '2026-09-01');
        DB::table('paiements_factures')->insert([
            'facture_id' => $vente, 'date_paiement' => '2026-09-10', 'montant' => 4000, 'mode_paiement' => 'virement', 'reference' => 'VIR-9',
        ]);
        $this->facture('avoir', 1000, '2026-09-15', ['facture_origine_id' => $vente]);

        $this->get(route('releves.pdf', $this->contact))
            ->assertSee('Relevé de compte client')
            ->assertSee('ACME SARL')
            ->assertSee('NIU : M2005110000123456')
            ->assertSee('Règlement (Virement) réf. VIR-9')
            ->assertSee('Solde dû par le client')
            ->assertSee('5 000 FCFA');

        // Avec une période : les mouvements antérieurs forment le solde d'ouverture.
        $this->get(route('releves.pdf', ['contact' => $this->contact, 'du' => '2026-09-12']))
            ->assertSee("Solde à l'ouverture de la période", false)
            ->assertSee('6 000');
    }
}
