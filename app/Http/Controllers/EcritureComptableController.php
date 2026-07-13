<?php

namespace App\Http\Controllers;

use App\Enums\StatutEcriture;
use App\Http\Controllers\Traits\Sortable;
use App\Models\Agence;
use App\Models\CompteComptable;
use App\Models\Contact;
use App\Models\Depense;
use App\Models\EcritureComptable;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\JournalComptable;
use App\Models\LivretBancaire;
use App\Models\PayementEmployee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EcritureComptableController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'numero_ecriture', 'date_ecriture', 'statut', 'created_at'];
        $query = EcritureComptable::with(['journal', 'lignes']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($journal_comptable_id = $request->input('journal_comptable_id')) {
            $query->where('journal_comptable_id', $journal_comptable_id);
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_ecriture', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        $query = $this->applySorting($query, $sortable);
        $ecritures = $query->paginate(15)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $stats = [
            'total' => (clone $query)->count(),
            'brouillon' => (clone $query)->where('statut', StatutEcriture::Brouillon->value)->count(),
            'validee' => (clone $query)->where('statut', StatutEcriture::Validee->value)->count(),
        ];

        $journaux = JournalComptable::pluck('libelle', 'id');
        $statuts = StatutEcriture::cases();

        return view('ecritures-comptables.index', compact('ecritures', 'sort', 'direction', 'journaux', 'statuts', 'stats'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        $journaux = JournalComptable::pluck('libelle', 'id');
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get(['id', 'numero_compte', 'libelle']);
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $prefill = null;

        if ($request->filled('source_type') && $request->filled('source_id')) {
            $sourceType = $request->string('source_type')->toString();
            $sourceId = (int) $request->input('source_id');

            $existing = EcritureComptable::dejaGenereePour($sourceType, $sourceId);
            if ($existing) {
                return redirect()->route('ecritures-comptables.show', $existing);
            }

            $prefill = $this->prefillFromSource($sourceType, $sourceId);
        }

        return view('ecritures-comptables.create', compact('journaux', 'comptes', 'contacts', 'agences', 'prefill'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'journal_comptable_id' => ['required', 'exists:journaux_comptables,id'],
            'date_ecriture' => ['required', 'date'],
            'libelle' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'source_type' => ['nullable', 'string', 'max:50'],
            'source_id' => ['nullable', 'integer'],
            'lignes' => ['required', 'array', 'min:2'],
            'lignes.*.compte_comptable_id' => ['required', 'exists:comptes_comptables,id'],
            'lignes.*.contact_id' => ['nullable', 'exists:contacts,id'],
            'lignes.*.libelle' => ['nullable', 'string', 'max:255'],
            'lignes.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDebit = round(collect($validated['lignes'])->sum(fn($l) => (float) ($l['debit'] ?? 0)), 2);
        $totalCredit = round(collect($validated['lignes'])->sum(fn($l) => (float) ($l['credit'] ?? 0)), 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            return back()->withInput()->withErrors([
                'lignes' => "Le total des débits ({$totalDebit}) doit être égal au total des crédits ({$totalCredit}) et supérieur à zéro.",
            ]);
        }

        foreach ($validated['lignes'] as $ligne) {
            if ((float) ($ligne['debit'] ?? 0) > 0 && (float) ($ligne['credit'] ?? 0) > 0) {
                return back()->withInput()->withErrors([
                    'lignes' => 'Une ligne ne peut pas avoir à la fois un débit et un crédit.',
                ]);
            }
        }

        if (!empty($validated['source_type']) && !empty($validated['source_id'])
            && EcritureComptable::dejaGenereePour($validated['source_type'], (int) $validated['source_id'])) {
            return redirect()->route('ecritures-comptables.index')
                ->with('error', 'Une écriture existe déjà pour cette opération.');
        }

        $journal = JournalComptable::findOrFail($validated['journal_comptable_id']);

        $ecriture = EcritureComptable::create([
            'numero_ecriture' => $this->nextNumero($journal),
            'journal_comptable_id' => $journal->id,
            'date_ecriture' => $validated['date_ecriture'],
            'libelle' => $validated['libelle'],
            'reference' => $validated['reference'] ?? null,
            'statut' => StatutEcriture::Brouillon->value,
            'source_type' => $validated['source_type'] ?? null,
            'source_id' => $validated['source_id'] ?? null,
            'agence_id' => $validated['agence_id'] ?? null,
            'created_by' => auth()->id(),
        ]);

        foreach ($validated['lignes'] as $i => $ligne) {
            $ecriture->lignes()->create([
                'compte_comptable_id' => $ligne['compte_comptable_id'],
                'contact_id' => $ligne['contact_id'] ?? null,
                'libelle' => $ligne['libelle'] ?? null,
                'debit' => $ligne['debit'] ?? 0,
                'credit' => $ligne['credit'] ?? 0,
                'ordre' => $i,
            ]);
        }

        return redirect()->route('ecritures-comptables.show', $ecriture)
            ->with('success', 'Écriture créée avec succès. Vérifiez-la puis validez-la.');
    }

    public function show(EcritureComptable $ecritureComptable): View
    {
        $ecritureComptable->load(['journal', 'lignes.compte', 'lignes.contact', 'agence', 'createur', 'validateur']);

        return view('ecritures-comptables.show', compact('ecritureComptable'));
    }

    public function edit(EcritureComptable $ecritureComptable): View
    {
        abort_if($ecritureComptable->statut !== StatutEcriture::Brouillon, 403, 'Écriture validée : non modifiable.');

        $ecritureComptable->load('lignes');
        $journaux = JournalComptable::pluck('libelle', 'id');
        $comptes = CompteComptable::where('actif', true)->orderBy('numero_compte')->get(['id', 'numero_compte', 'libelle']);
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');

        return view('ecritures-comptables.edit', compact('ecritureComptable', 'journaux', 'comptes', 'contacts', 'agences'));
    }

    public function update(Request $request, EcritureComptable $ecritureComptable): RedirectResponse
    {
        abort_if($ecritureComptable->statut !== StatutEcriture::Brouillon, 403, 'Écriture validée : non modifiable.');

        $validated = $request->validate([
            'journal_comptable_id' => ['required', 'exists:journaux_comptables,id'],
            'date_ecriture' => ['required', 'date'],
            'libelle' => ['required', 'string', 'max:255'],
            'reference' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'lignes' => ['required', 'array', 'min:2'],
            'lignes.*.compte_comptable_id' => ['required', 'exists:comptes_comptables,id'],
            'lignes.*.contact_id' => ['nullable', 'exists:contacts,id'],
            'lignes.*.libelle' => ['nullable', 'string', 'max:255'],
            'lignes.*.debit' => ['nullable', 'numeric', 'min:0'],
            'lignes.*.credit' => ['nullable', 'numeric', 'min:0'],
        ]);

        $totalDebit = round(collect($validated['lignes'])->sum(fn($l) => (float) ($l['debit'] ?? 0)), 2);
        $totalCredit = round(collect($validated['lignes'])->sum(fn($l) => (float) ($l['credit'] ?? 0)), 2);

        if ($totalDebit <= 0 || $totalDebit !== $totalCredit) {
            return back()->withInput()->withErrors([
                'lignes' => "Le total des débits ({$totalDebit}) doit être égal au total des crédits ({$totalCredit}) et supérieur à zéro.",
            ]);
        }

        $ecritureComptable->update([
            'journal_comptable_id' => $validated['journal_comptable_id'],
            'date_ecriture' => $validated['date_ecriture'],
            'libelle' => $validated['libelle'],
            'reference' => $validated['reference'] ?? null,
            'agence_id' => $validated['agence_id'] ?? null,
        ]);

        $ecritureComptable->lignes()->delete();
        foreach ($validated['lignes'] as $i => $ligne) {
            $ecritureComptable->lignes()->create([
                'compte_comptable_id' => $ligne['compte_comptable_id'],
                'contact_id' => $ligne['contact_id'] ?? null,
                'libelle' => $ligne['libelle'] ?? null,
                'debit' => $ligne['debit'] ?? 0,
                'credit' => $ligne['credit'] ?? 0,
                'ordre' => $i,
            ]);
        }

        return redirect()->route('ecritures-comptables.show', $ecritureComptable)
            ->with('success', 'Écriture mise à jour avec succès.');
    }

    public function destroy(EcritureComptable $ecritureComptable): RedirectResponse
    {
        abort_if($ecritureComptable->statut === StatutEcriture::Validee, 403, 'Écriture validée : suppression interdite.');

        $ecritureComptable->delete();

        return redirect()->route('ecritures-comptables.index')
            ->with('success', 'Écriture supprimée avec succès.');
    }

    public function valider(EcritureComptable $ecritureComptable): RedirectResponse
    {
        $ecritureComptable->load('lignes');

        if ($ecritureComptable->statut === StatutEcriture::Validee) {
            return back()->with('error', 'Cette écriture est déjà validée.');
        }

        if ($ecritureComptable->lignes->isEmpty() || !$ecritureComptable->estEquilibree()) {
            return back()->with('error', "L'écriture n'est pas équilibrée (débit ≠ crédit).");
        }

        $ecritureComptable->update([
            'statut' => StatutEcriture::Validee->value,
            'validated_by' => auth()->id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('ecritures-comptables.show', $ecritureComptable)
            ->with('success', 'Écriture validée et intégrée au grand livre.');
    }

    private function nextNumero(JournalComptable $journal): string
    {
        $year = now()->year;
        $count = EcritureComptable::where('journal_comptable_id', $journal->id)
            ->whereYear('date_ecriture', $year)
            ->count();

        return sprintf('%s-%d-%06d', $journal->code, $year, $count + 1);
    }

    private function compteId(string $numeroCompte): ?int
    {
        return CompteComptable::where('numero_compte', $numeroCompte)->value('id');
    }

    private function prefillFromSource(string $type, int $id): ?array
    {
        return match ($type) {
            'facture' => $this->prefillFromFacture($id),
            'payement_employee' => $this->prefillFromPayementEmployee($id),
            'livret_bancaire' => $this->prefillFromLivretBancaire($id),
            'depense' => $this->prefillFromDepense($id),
            default => null,
        };
    }

    private function prefillFromFacture(int $id): ?array
    {
        $facture = Facture::with(['items', 'contact'])->find($id);
        if (!$facture) {
            return null;
        }

        $total = $facture->total;
        $typeValue = $facture->type_facture?->value;

        if ($typeValue === 'vente') {
            $compteContrepartie = $this->compteId('701000');
            $compteTiers = $this->compteId('411000');
            $journalCode = 'VE';
        } elseif ($typeValue === 'achat') {
            $compteContrepartie = $this->compteId('601000');
            $compteTiers = $this->compteId('401000');
            $journalCode = 'AC';
        } else {
            return null;
        }

        $lignes = $typeValue === 'vente'
            ? [
                ['compte_comptable_id' => $compteTiers, 'contact_id' => $facture->contact_id, 'libelle' => "Facture {$facture->numero_facture}", 'debit' => $total, 'credit' => 0],
                ['compte_comptable_id' => $compteContrepartie, 'contact_id' => null, 'libelle' => "Facture {$facture->numero_facture}", 'debit' => 0, 'credit' => $total],
            ]
            : [
                ['compte_comptable_id' => $compteContrepartie, 'contact_id' => null, 'libelle' => "Facture {$facture->numero_facture}", 'debit' => $total, 'credit' => 0],
                ['compte_comptable_id' => $compteTiers, 'contact_id' => $facture->contact_id, 'libelle' => "Facture {$facture->numero_facture}", 'debit' => 0, 'credit' => $total],
            ];

        return [
            'journal_code' => $journalCode,
            'libelle' => "Facturation {$facture->numero_facture}",
            'date_ecriture' => optional($facture->date_facture)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'agence_id' => $facture->agence_id,
            'lignes' => $lignes,
        ];
    }

    private function prefillFromPayementEmployee(int $id): ?array
    {
        $payement = PayementEmployee::with('employee')->find($id);
        if (!$payement) {
            return null;
        }

        $chargePersonnel = (float) $payement->salaire_base + (float) $payement->total_primes;
        $retenues = (float) $payement->total_retenus;
        $net = (float) $payement->net_a_payer;
        $nomEmploye = $payement->employee?->nom_complet ?? '';

        $lignes = [
            ['compte_comptable_id' => $this->compteId('661000'), 'contact_id' => null, 'libelle' => "Salaire {$payement->mois}/{$payement->annee} - {$nomEmploye}", 'debit' => $chargePersonnel, 'credit' => 0],
        ];

        if ($retenues > 0) {
            $lignes[] = ['compte_comptable_id' => $this->compteId('431000'), 'contact_id' => null, 'libelle' => 'Retenues sur salaire', 'debit' => 0, 'credit' => $retenues];
        }

        $lignes[] = ['compte_comptable_id' => $this->compteId('520000'), 'contact_id' => null, 'libelle' => "Paiement net - {$nomEmploye}", 'debit' => 0, 'credit' => $net];

        return [
            'journal_code' => 'SA',
            'libelle' => "Paie {$payement->mois}/{$payement->annee} - {$nomEmploye}",
            'date_ecriture' => $payement->date_paiement ? $payement->date_paiement->format('Y-m-d') : now()->format('Y-m-d'),
            'agence_id' => null,
            'lignes' => $lignes,
        ];
    }

    private function prefillFromLivretBancaire(int $id): ?array
    {
        $livret = LivretBancaire::with('banque')->find($id);
        if (!$livret) {
            return null;
        }

        $compteBanque = $livret->banque?->compte_comptable_id ?: $this->compteId('520000');
        $montant = (float) $livret->montant;
        $typeValue = $livret->type_action?->value;

        $lignes = match ($typeValue) {
            'frais' => [
                ['compte_comptable_id' => $this->compteId('674000'), 'contact_id' => null, 'libelle' => $livret->motif, 'debit' => $montant, 'credit' => 0],
                ['compte_comptable_id' => $compteBanque, 'contact_id' => null, 'libelle' => $livret->motif, 'debit' => 0, 'credit' => $montant],
            ],
            'interet' => [
                ['compte_comptable_id' => $compteBanque, 'contact_id' => null, 'libelle' => $livret->motif, 'debit' => $montant, 'credit' => 0],
                ['compte_comptable_id' => $this->compteId('771000'), 'contact_id' => null, 'libelle' => $livret->motif, 'debit' => 0, 'credit' => $montant],
            ],
            'depot' => [
                ['compte_comptable_id' => $compteBanque, 'contact_id' => $livret->contact_id, 'libelle' => $livret->motif, 'debit' => $montant, 'credit' => 0],
            ],
            default => [
                ['compte_comptable_id' => $compteBanque, 'contact_id' => $livret->contact_id, 'libelle' => $livret->motif, 'debit' => 0, 'credit' => $montant],
            ],
        };

        return [
            'journal_code' => 'BQ',
            'libelle' => $livret->motif,
            'date_ecriture' => optional($livret->date_action)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'agence_id' => $livret->agence_id,
            'lignes' => $lignes,
        ];
    }

    private function prefillFromDepense(int $id): ?array
    {
        $depense = Depense::with(['categorie', 'banque'])->find($id);
        if (!$depense) {
            return null;
        }

        $compteCharge = $depense->categorie?->compte_comptable_id ?: $this->compteId('628000');
        $montant = (float) $depense->montant;
        $modeValue = $depense->mode_paiement?->value;

        $compteContrepartie = match ($modeValue) {
            'banque' => $depense->banque?->compte_comptable_id ?: $this->compteId('520000'),
            'caisse' => $this->compteId('570000'),
            default => $this->compteId('401000'),
        };
        $journalCode = $modeValue === 'caisse' ? 'CA' : ($modeValue === 'banque' ? 'BQ' : 'AC');

        return [
            'journal_code' => $journalCode,
            'libelle' => "Dépense {$depense->numero_depense} - {$depense->objet}",
            'date_ecriture' => optional($depense->date_depense)->format('Y-m-d') ?? now()->format('Y-m-d'),
            'agence_id' => $depense->agence_id,
            'lignes' => [
                ['compte_comptable_id' => $compteCharge, 'contact_id' => $depense->contact_id, 'libelle' => $depense->objet, 'debit' => $montant, 'credit' => 0],
                ['compte_comptable_id' => $compteContrepartie, 'contact_id' => $depense->contact_id, 'libelle' => $depense->objet, 'debit' => 0, 'credit' => $montant],
            ],
        ];
    }
}
