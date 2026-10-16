<?php

namespace App\Http\Controllers;

use App\Enums\TypeFacture;
use App\Models\Agence;
use App\Models\Contact;
use App\Models\EcritureComptable;
use App\Models\Facture;
use App\Models\PaiementFacture;
use App\Support\AgencyContext;
use App\Support\RapportPdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Vues transverses sur les règlements de factures, à l'écran et en PDF :
 *  - Encaissements : tous les paiements enregistrés, filtrables, avec totaux ;
 *  - Créances : factures de vente qui restent dues, par ancienneté (balance âgée) ;
 *  - Relevé de compte d'un client : factures, avoirs et règlements avec solde.
 * Le cloisonnement par agence s'applique automatiquement (modèles cloisonnés).
 */
class EncaissementController extends Controller
{
    public const TRANCHES = ['0-30' => '0 à 30 j', '31-60' => '31 à 60 j', '61-90' => '61 à 90 j', '90+' => 'Plus de 90 j'];

    public const MODES = ['especes' => 'Espèces', 'virement' => 'Virement', 'mobile_money' => 'Mobile money', 'cheque' => 'Chèque', 'carte' => 'Carte', 'credit' => 'Crédit'];

    public const NATURES = ['vente' => 'Encaissements clients (ventes)', 'avoir' => 'Remboursements clients (avoirs)', 'achat' => 'Règlements fournisseurs (achats)', 'tous' => 'Tous les règlements'];

    // ── Encaissements ─────────────────────────────────────────────────

    public function index(Request $request): View
    {
        [$query, $filtres] = $this->requeteEncaissements($request);
        $parMode = $this->totauxParMode($query);

        $paiements = $query->orderByDesc('date_paiement')->orderByDesc('id')->paginate(25)->withQueryString();
        $comptabilises = EcritureComptable::where('source_type', 'paiement_facture')
            ->whereIn('source_id', $paiements->pluck('id'))->pluck('numero_ecriture', 'source_id');

        return view('encaissements.index', $filtres + [
            'paiements' => $paiements,
            'parMode' => $parMode,
            'total' => (float) $parMode->sum('total'),
            'comptabilises' => $comptabilises,
            'clients' => $this->clients(),
            'agences' => $this->agencesFiltrables(),
        ]);
    }

    public function indexPdf(Request $request): Response
    {
        [$query, $filtres] = $this->requeteEncaissements($request);
        $parMode = $this->totauxParMode($query);

        return RapportPdf::generer('encaissements.pdf.encaissements', [
            'paiements' => $query->orderBy('date_paiement')->orderBy('id')->get(),
            'parMode' => $parMode,
            'total' => (float) $parMode->sum('total'),
            'nature' => self::NATURES[$filtres['type']] ?? '',
        ], 'Journal des encaissements', 'encaissements.pdf',
            RapportPdf::periode($filtres['du']->toDateString(), $filtres['au']->toDateString()),
            $request->integer('agence_id') ?: null, true);
    }

    /**
     * @return array{0: Builder, 1: array}
     */
    private function requeteEncaissements(Request $request): array
    {
        $du = $request->date('du') ?? now()->startOfMonth();
        $au = $request->date('au') ?? now();
        $type = $request->input('type', 'vente');

        $query = PaiementFacture::with(['facture.contact', 'facture.agence', 'banque', 'user'])
            ->whereDate('date_paiement', '>=', $du->toDateString())
            ->whereDate('date_paiement', '<=', $au->toDateString())
            ->whereHas('facture', function ($q) use ($request, $type) {
                if ($type !== 'tous') {
                    $q->where('type_facture', $type);
                }
                if ($contact = $request->input('contact_id')) {
                    $q->where('contact_id', $contact);
                }
                if ($agence = $request->input('agence_id')) {
                    $q->where('agence_id', $agence);
                }
            })
            ->when($request->input('mode_paiement'), fn ($q, $mode) => $q->where('mode_paiement', $mode))
            ->when($request->boolean('non_comptabilises'), fn ($q) => $q->whereNotIn('id',
                EcritureComptable::where('source_type', 'paiement_facture')->select('source_id')));

        return [$query, compact('du', 'au', 'type')];
    }

    private function totauxParMode(Builder $query): Collection
    {
        return (clone $query)->toBase()->selectRaw('mode_paiement, SUM(montant) AS total, COUNT(*) AS nombre')
            ->groupBy('mode_paiement')->get();
    }

    // ── Créances ──────────────────────────────────────────────────────

    public function creances(Request $request): View
    {
        return view('encaissements.creances', $this->donneesCreances($request) + [
            'clients' => $this->clients(),
            'agences' => $this->agencesFiltrables(),
        ]);
    }

    public function creancesPdf(Request $request): Response
    {
        $donnees = $this->donneesCreances($request);
        $tranche = $request->input('tranche');

        return RapportPdf::generer('encaissements.pdf.creances', $donnees,
            'Créances clients — balance âgée', 'creances_clients.pdf',
            'Situation au ' . now()->format('d/m/Y') . ($tranche ? ' — ancienneté ' . (self::TRANCHES[$tranche] ?? $tranche) : ''),
            $request->integer('agence_id') ?: null);
    }

    private function donneesCreances(Request $request): array
    {
        $aujourdhui = Carbon::today();

        $factures = Facture::with(['items', 'contact', 'agence'])
            ->where('type_facture', TypeFacture::Vente->value)
            ->whereIn('statut_facture', Facture::STATUTS_ACTIFS)
            ->when($request->input('contact_id'), fn ($q, $c) => $q->where('contact_id', $c))
            ->when($request->input('agence_id'), fn ($q, $a) => $q->where('agence_id', $a))
            ->orderBy('date_facture')
            ->get()
            ->map(function (Facture $f) use ($aujourdhui) {
                $f->reste = $f->resteAPayer();
                $f->age = $f->date_facture ? (int) $f->date_facture->diffInDays($aujourdhui) : 0;
                $f->tranche = match (true) {
                    $f->age <= 30 => '0-30',
                    $f->age <= 60 => '31-60',
                    $f->age <= 90 => '61-90',
                    default => '90+',
                };

                return $f;
            })
            ->filter(fn (Facture $f) => $f->reste > 0.01)
            ->when($request->input('tranche'), fn ($c, $t) => $c->where('tranche', $t))
            ->values();

        $parTranche = collect(self::TRANCHES)->map(fn ($libelle, $cle) => [
            'libelle' => $libelle,
            'montant' => $factures->where('tranche', $cle)->sum('reste'),
            'nombre' => $factures->where('tranche', $cle)->count(),
        ]);

        // Par client, avec la répartition par tranche (balance âgée).
        $parClient = $factures->groupBy(fn ($f) => self::nomTiers($f))
            ->map(fn ($groupe) => [
                'montant' => $groupe->sum('reste'),
                'nombre' => $groupe->count(),
                'contact_id' => $groupe->first()->contact_id,
                'tranches' => collect(self::TRANCHES)->map(fn ($l, $cle) => $groupe->where('tranche', $cle)->sum('reste')),
            ])
            ->sortByDesc('montant');

        // Avoirs validés qui laissent un trop-perçu à rembourser au client.
        $remboursements = Facture::with(['items', 'contact', 'factureOrigine.items'])
            ->where('type_facture', TypeFacture::Avoir->value)
            ->whereIn('statut_facture', Facture::STATUTS_ACTIFS)
            ->when($request->input('contact_id'), fn ($q, $c) => $q->where('contact_id', $c))
            ->get()
            ->map(fn (Facture $a) => tap($a, fn ($a) => $a->reste = $a->resteAPayer()))
            ->filter(fn (Facture $a) => $a->reste > 0.01)
            ->values();

        return [
            'factures' => $factures,
            'parTranche' => $parTranche,
            'parClient' => $parClient,
            'total' => $factures->sum('reste'),
            'remboursements' => $remboursements,
        ];
    }

    // ── Relevé de compte client ───────────────────────────────────────

    /**
     * Relevé chronologique : factures (débit), avoirs et règlements (crédit), remboursements
     * (débit), avec solde d'ouverture si une période est choisie, puis factures restant dues.
     */
    public function releve(Request $request, Contact $contact): Response
    {
        $du = $request->input('du');
        $au = $request->input('au') ?: now()->toDateString();

        $factures = Facture::with(['items', 'paiements', 'factureOrigine'])
            ->where('contact_id', $contact->id)
            ->whereIn('type_facture', [TypeFacture::Vente->value, TypeFacture::Avoir->value])
            ->whereIn('statut_facture', Facture::STATUTS_ACTIFS)
            ->get();

        $mouvements = collect();
        foreach ($factures as $f) {
            $estAvoir = $f->type_facture === TypeFacture::Avoir;
            $mouvements->push([
                'date' => $f->date_facture,
                'piece' => $f->numero_facture,
                'libelle' => $estAvoir
                    ? 'Avoir' . ($f->factureOrigine ? " sur facture {$f->factureOrigine->numero_facture}" : '')
                    : 'Facture' . ($f->objet ? " — {$f->objet}" : ''),
                'debit' => $estAvoir ? 0 : $f->total_ttc,
                'credit' => $estAvoir ? $f->total_ttc : 0,
                'tri' => 0,
            ]);

            foreach ($f->paiements as $p) {
                $mouvements->push([
                    'date' => $p->date_paiement,
                    'piece' => $f->numero_facture,
                    'libelle' => ($estAvoir ? 'Remboursement' : 'Règlement')
                        . ' (' . (self::MODES[$p->mode_paiement] ?? $p->mode_paiement) . ')'
                        . ($p->reference ? " réf. {$p->reference}" : ''),
                    'debit' => $estAvoir ? (float) $p->montant : 0,
                    'credit' => $estAvoir ? 0 : (float) $p->montant,
                    'tri' => 1,
                ]);
            }
        }

        $mouvements = $mouvements->sortBy(fn ($m) => $m['date']?->format('Y-m-d') . $m['tri'])->values();

        $avant = $du ? $mouvements->filter(fn ($m) => $m['date']?->toDateString() < $du) : collect();
        $soldeOuverture = $avant->sum('debit') - $avant->sum('credit');

        $solde = $soldeOuverture;
        $lignes = $mouvements
            ->filter(fn ($m) => (!$du || $m['date']?->toDateString() >= $du) && $m['date']?->toDateString() <= $au)
            ->map(function ($m) use (&$solde) {
                $solde += $m['debit'] - $m['credit'];

                return $m + ['solde' => $solde];
            })->values();

        $dues = $factures->where('type_facture', TypeFacture::Vente)
            ->map(fn (Facture $f) => tap($f, fn ($f) => $f->reste = $f->resteAPayer()))
            ->filter(fn ($f) => $f->reste > 0.01)
            ->sortBy('date_facture')->values();

        return RapportPdf::generer('encaissements.pdf.releve', [
            'contact' => $contact,
            'lignes' => $lignes,
            'soldeOuverture' => $soldeOuverture,
            'soldeFinal' => $solde,
            'du' => $du,
            'dues' => $dues,
        ], 'Relevé de compte client', 'releve_' . str($contact->raison_social ?: $contact->nom_complet ?: $contact->id)->slug() . '.pdf',
            RapportPdf::periode($du, $au));
    }

    // ── Utilitaires ───────────────────────────────────────────────────

    public static function nomTiers(Facture $f): string
    {
        return $f->contact?->raison_social ?: $f->contact?->nom_complet ?: $f->raison_social ?: '—';
    }

    private function clients(): Collection
    {
        return Contact::orderBy('raison_social')->get()
            ->mapWithKeys(fn (Contact $c) => [$c->id => $c->raison_social ?: $c->nom_complet ?: "Contact #{$c->id}"]);
    }

    private function agencesFiltrables(): Collection
    {
        return AgencyContext::isRestricted() ? collect() : Agence::orderBy('name_agence')->pluck('name_agence', 'id');
    }
}
