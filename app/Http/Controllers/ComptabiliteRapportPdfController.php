<?php

namespace App\Http\Controllers;

use App\Enums\StatutEcriture;
use App\Http\Controllers\Concerns\CalculeComptabilite;
use App\Models\CompteComptable;
use App\Models\EcritureComptable;
use App\Models\JournalComptable;
use App\Support\RapportPdf;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ComptabiliteRapportPdfController extends Controller
{
    use CalculeComptabilite;

    public function balance(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $agenceId = $this->resolveAgenceFilter($request);

        $soldes = $this->soldesParCompte($dateDebut, $dateFin, $agenceId);
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get();

        $lignes = $comptes->map(function ($compte) use ($soldes) {
            $s = $soldes->get($compte->id, ['debit' => 0, 'credit' => 0]);
            $debit = (float) $s['debit'];
            $credit = (float) $s['credit'];
            $solde = $compte->sens_normal?->value === 'debit' ? $debit - $credit : $credit - $debit;

            return ['compte' => $compte, 'total_debit' => $debit, 'total_credit' => $credit, 'solde' => $solde];
        })->filter(fn($l) => $l['total_debit'] != 0 || $l['total_credit'] != 0)->values();

        return RapportPdf::generer('comptabilite.pdf.balance', [
            'lignes' => $lignes,
            'totalDebit' => $lignes->sum('total_debit'),
            'totalCredit' => $lignes->sum('total_credit'),
        ], 'Balance des comptes', 'balance_des_comptes.pdf', RapportPdf::periode($dateDebut, $dateFin), $agenceId, true);
    }

    public function bilan(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$actif, $passif, $resultat] = $this->calculerBilan($dateDebut, $dateFin, $agenceId);

        return RapportPdf::generer('comptabilite.pdf.bilan', [
            'actif' => $actif,
            'passif' => $passif,
            'resultat' => $resultat,
            'totalActif' => $actif->sum('solde'),
            'totalPassif' => $passif->sum('solde') + $resultat,
        ], 'Bilan', 'bilan.pdf', RapportPdf::periode($dateDebut, $dateFin), $agenceId);
    }

    public function resultat(Request $request): Response
    {
        $dateDebut = $request->input('date_debut') ?: now()->startOfYear()->format('Y-m-d');
        $dateFin = $request->input('date_fin') ?: now()->format('Y-m-d');
        $agenceId = $this->resolveAgenceFilter($request);

        [$charges, $produits, $totalCharges, $totalProduits, $resultatNet] = $this->calculerResultat($dateDebut, $dateFin, $agenceId);

        return RapportPdf::generer('comptabilite.pdf.resultat',
            compact('charges', 'produits', 'totalCharges', 'totalProduits', 'resultatNet'),
            'Compte de résultat', 'compte_de_resultat.pdf', RapportPdf::periode($dateDebut, $dateFin), $agenceId);
    }

    /**
     * Grand livre d'un compte, ou de tous les comptes mouvementés si aucun n'est choisi.
     */
    public function grandLivre(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $agenceId = $this->resolveAgenceFilter($request);

        $comptes = $request->filled('compte_comptable_id')
            ? CompteComptable::whereKey($request->input('compte_comptable_id'))->get()
            : CompteComptable::where('actif', true)->orderBy('numero_compte')->get();

        $blocs = $comptes->map(function (CompteComptable $compte) use ($dateDebut, $dateFin, $agenceId) {
            $lignes = $this->lignesGrandLivre($compte, $dateDebut, $dateFin, $agenceId);

            return [
                'compte' => $compte,
                'lignes' => $lignes,
                'total_debit' => $lignes->sum(fn ($l) => (float) $l->debit),
                'total_credit' => $lignes->sum(fn ($l) => (float) $l->credit),
                'solde' => $lignes->last()?->solde_cumule ?? 0,
            ];
        })->filter(fn ($b) => $b['lignes']->isNotEmpty())->values();

        $titre = $comptes->count() === 1
            ? 'Grand livre — ' . $comptes->first()->numero_compte
            : 'Grand livre général';

        return RapportPdf::generer('comptabilite.pdf.grand-livre', ['comptes' => $blocs],
            $titre, 'grand_livre.pdf', RapportPdf::periode($dateDebut, $dateFin), $agenceId, true);
    }

    /**
     * Journal général : écritures validées de la période, regroupées par journal.
     */
    public function journal(Request $request): Response
    {
        $dateDebut = $request->input('date_debut');
        $dateFin = $request->input('date_fin');
        $agenceId = $this->resolveAgenceFilter($request);

        $ecritures = EcritureComptable::with(['journal', 'lignes.compte'])
            ->where('statut', StatutEcriture::Validee->value)
            ->when($dateDebut, fn ($q) => $q->whereDate('date_ecriture', '>=', $dateDebut))
            ->when($dateFin, fn ($q) => $q->whereDate('date_ecriture', '<=', $dateFin))
            ->when($agenceId, fn ($q) => $q->where('agence_id', $agenceId))
            ->when($request->input('journal_comptable_id'), fn ($q, $j) => $q->where('journal_comptable_id', $j))
            ->orderBy('date_ecriture')->orderBy('id')
            ->get();

        $parJournal = $ecritures->groupBy('journal_comptable_id')->map(fn ($groupe) => [
            'journal' => $groupe->first()->journal,
            'ecritures' => $groupe,
            'debit' => $groupe->sum(fn ($e) => $e->lignes->sum(fn ($l) => (float) $l->debit)),
            'credit' => $groupe->sum(fn ($e) => $e->lignes->sum(fn ($l) => (float) $l->credit)),
        ])->sortBy(fn ($b) => $b['journal']?->code)->values();

        $journal = $request->filled('journal_comptable_id') ? JournalComptable::find($request->input('journal_comptable_id')) : null;

        return RapportPdf::generer('comptabilite.pdf.journal', ['parJournal' => $parJournal],
            $journal ? "Journal {$journal->code} — {$journal->libelle}" : 'Journal général',
            'journal.pdf', RapportPdf::periode($dateDebut, $dateFin), $agenceId, true);
    }
}
