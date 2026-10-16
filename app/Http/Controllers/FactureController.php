<?php

namespace App\Http\Controllers;

use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use App\Models\Agence;
use App\Models\Banque;
use App\Models\Contact;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\ItemFacture;
use App\Models\Produit;
use App\Models\PaiementFacture;
use App\Services\FactureWorkflow;
use App\Services\StockMovementService;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class FactureController extends Controller
{
    use Sortable;

    public function __construct(
        private StockMovementService $stockMovementService,
        private FactureWorkflow $workflow,
    ) {
    }

    public function index(Request $request): View
    {
        $sortable = ['id', 'numero_facture', 'date_facture', 'type_facture', 'statut_facture', 'created_at'];
        $query = Facture::with(['items', 'agence', 'avoirs.items']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_facture', 'like', "%{$search}%")
                  ->orWhere('raison_social', 'like', "%{$search}%");
            });
        }

        if ($type = $request->input('type_facture')) {
            $query->where('type_facture', $type);
        }

        if ($statut = $request->input('statut_facture')) {
            $query->where('statut_facture', $statut);
        }

        if ($agence_id = $request->input('agence_id')) {
            $query->where('agence_id', $agence_id);
        }

        $query = $this->applySorting($query, $sortable);
        $factures = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $factures->getCollection()->transform(function ($f) {
            $f->montant_total = $f->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
            return $f;
        });

        $allFactures = Facture::with('items');
        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $allFactures->where('agence_id', $userEmp->agence_id);
            }
        }
        $all = $allFactures->get();
        $clients = $all->filter(fn($f) => in_array($f->type_facture, [TypeFacture::Vente, TypeFacture::Avoir], true));
        $stats = [
            'total' => $all->count(),
            'payee' => $all->filter(fn($f) => $f->statut_facture?->value === 'payee')->count(),
            'impayee' => $all->filter(fn($f) => $f->statut_facture?->value === 'impayee')->count(),
            // Montants côté clients : ventes moins avoirs (achats et proformas exclus).
            'montant_payee' => $clients->filter(fn($f) => $f->statut_facture?->value === 'payee')->sum(fn($f) => $f->montant_signe),
            'montant_impayee' => $clients->filter(fn($f) => $f->statut_facture?->value === 'impayee')->sum(fn($f) => $f->montant_signe),
        ];

        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $agences = Agence::pluck('name_agence', 'id');

        return view('factures.index', compact('factures', 'sort', 'direction', 'types', 'statuts', 'agences', 'stats'));
    }

    public function create(Request $request): View|RedirectResponse
    {
        // Les avoirs se créent à partir de la facture à corriger (FactureAvoirController).
        if ($request->filled('avoir_de')) {
            return redirect()->route('factures.avoirs.preparer', $request->integer('avoir_de'));
        }

        $types = array_values(array_filter(TypeFacture::cases(), fn ($t) => $t !== TypeFacture::Avoir));
        $statuts = [StatutFacture::Brouillon, StatutFacture::Impayee];
        $contacts = $this->contactsPourSelection();
        $agences = Agence::pluck('name_agence', 'id');
        $factures = Facture::where('type_facture', 'vente')->orderByDesc('id')->pluck('numero_facture', 'id');
        $nextNum = Facture::prochainNumero();
        $factureOrigine = null;

        return view('factures.create', compact('types', 'statuts', 'contacts', 'agences', 'factures', 'nextNum', 'factureOrigine'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validator = validator($request->all(), [
            'type_facture' => ['required', Rule::enum(TypeFacture::class)],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture'],
            'date_facture' => ['required', 'date'],
            // Les statuts de paiement découlent des encaissements : à la création, brouillon ou validée.
            'statut_facture' => ['required', Rule::in([StatutFacture::Brouillon->value, StatutFacture::Impayee->value])],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'interlocuteur' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'facture_origine_id' => ['nullable', 'exists:factures,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'objet' => ['nullable', 'string', 'max:500'],
        ]);
        $validator->after(fn (Validator $v) => $this->verifierAvoir($v));
        $validated = $validator->validate();

        // Sans droit de validation, une facture naît en brouillon (le statut change ensuite via updateStatut).
        if (!$request->user()->can('finance.factures.valider')) {
            $validated['statut_facture'] = StatutFacture::Brouillon->value;
        }

        $facture = Facture::create($validated);
        $this->workflow->journaliser($facture, 'creation', null, $facture->statut_facture?->value, 'Création de la facture');

        return redirect()->route('factures.index')
            ->with('success', 'Facture créée avec succès.');
    }

    public function show(Facture $facture): View
    {
        $facture->load(['items', 'agence', 'contact']);

        return view('factures.show', compact('facture'));
    }

    public function edit(Facture $facture): View|RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $contacts = $this->contactsPourSelection();
        $agences = Agence::pluck('name_agence', 'id');
        $factures = Facture::where('type_facture', 'vente')->where('id', '!=', $facture->id)->orderByDesc('id')->pluck('numero_facture', 'id');

        return view('factures.edit', compact('facture', 'types', 'statuts', 'contacts', 'agences', 'factures'));
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        // Le statut ne se modifie que via updateStatut (permission finance.factures.valider + mouvements de stock).
        $validator = validator($request->all(), [
            'type_facture' => ['required', Rule::enum(TypeFacture::class)],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture,' . $facture->id],
            'date_facture' => ['required', 'date'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'interlocuteur' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'facture_origine_id' => ['nullable', 'exists:factures,id', Rule::notIn([$facture->id])],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'objet' => ['nullable', 'string', 'max:500'],
        ]);
        $validator->after(fn (Validator $v) => $this->verifierAvoir($v, $facture));
        $validated = $validator->validate();

        $facture->update($validated);

        return redirect()->route('factures.index')
            ->with('success', 'Facture mise à jour avec succès.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        if ($facture->paiements()->exists()) {
            return redirect()->route('factures.manage', $facture)
                ->with('error', 'Des paiements sont enregistrés sur cette facture : supprimez-les d\'abord, ou annulez plutôt la facture.');
        }

        $facture->load('items');
        foreach ($facture->items as $item) {
            $this->stockMovementService->annulerPourArticle($item, 'suppression de la facture');
        }

        $facture->delete();

        return redirect()->route('factures.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        $factures = Facture::where('numero_facture', 'like', "%{$q}%")
            ->orWhere('raison_social', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'numero_facture', 'raison_social']);

        return response()->json($factures->map(fn($f) => [
            'id'   => $f->id,
            'text' => $f->numero_facture,
            'sub'  => $f->raison_social,
        ]));
    }

    public function manage(Facture $facture): View
    {
        $facture->load(['items', 'agence', 'contact', 'paiements.user', 'paiements.banque', 'historique.user']);
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $transitions = $facture->transitionsPossibles();
        $produits = Produit::orderBy('nom')->pluck('nom', 'id');
        $depots = Depot::when($facture->agence_id, fn($q) => $q->where('agence_id', $facture->agence_id))
            ->orderBy('nom')->pluck('nom', 'id');

        $banques = Banque::orderBy('nom')->pluck('nom', 'id');

        return view('factures.manage', compact('facture', 'types', 'statuts', 'produits', 'depots', 'transitions', 'banques'));
    }

    public function storeItem(Request $request, Facture $facture): RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantite' => ['required', 'integer', 'min:1'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
            'type_article' => ['nullable', 'string', 'in:produit,service'],
            'produit_id' => ['nullable', 'required_if:type_article,produit', 'exists:produits,id'],
            'depot_id' => ['nullable', 'required_if:type_article,produit', 'exists:depots,id'],
        ]);

        $validated['facture_id'] = $facture->id;
        $validated['taux_tva'] = $facture->tva_active ? 18 : 0;
        $validated['type_article'] = $validated['type_article'] ?? 'service';
        if ($validated['type_article'] !== 'produit') {
            $validated['produit_id'] = null;
            $validated['depot_id'] = null;
        }

        if ($facture->type_facture === TypeFacture::Avoir && $validated['produit_id']) {
            $retournable = $facture->quantiteRetournable((int) $validated['produit_id']);
            if ($validated['quantite'] > $retournable) {
                return back()->withInput()->with('error',
                    "Quantité trop élevée : seulement {$retournable} unité(s) de ce produit peuvent encore être retournées sur la facture d'origine."
                );
            }
        }

        try {
            DB::transaction(function () use ($facture, $validated) {
                $item = ItemFacture::create($validated);

                if ($facture->depasseFactureOrigine()) {
                    throw new \DomainException($this->messageDepassement($facture));
                }

                if ($facture->estActive()) {
                    $this->stockMovementService->appliquerPourArticle($item);
                }
            });
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $this->workflow->recalculerLies($facture);

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Article ajouté à la facture.');
    }

    public function destroyItem(ItemFacture $itemFacture): RedirectResponse
    {
        $factureId = $itemFacture->facture_id;

        if ($refus = $this->refuserSiCertifiee($itemFacture->facture)) {
            return $refus;
        }

        $facture = $itemFacture->facture;
        $this->stockMovementService->annulerPourArticle($itemFacture);
        $itemFacture->delete();
        $this->workflow->recalculerLies($facture);

        return redirect()->route('factures.manage', $factureId)
            ->with('success', 'Article supprimé.');
    }

    public function updateStatut(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'statut_facture' => ['required', Rule::enum(StatutFacture::class)],
        ]);

        try {
            $this->workflow->changerStatut($facture, $validated['statut_facture']);
        } catch (\DomainException $e) {
            return redirect()->route('factures.manage', $facture)->with('error', $e->getMessage());
        }

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Statut mis à jour.');
    }

    public function storePaiement(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'date_paiement' => ['required', 'date', 'before_or_equal:today'],
            'montant' => ['required', 'numeric', 'gt:0'],
            'mode_paiement' => ['required', Rule::in(array_keys(config('sfec.payment_methods')))],
            'banque_id' => [Rule::requiredIf(in_array($request->input('mode_paiement'), PaiementFacture::MODES_BANCAIRES, true)), 'nullable', 'exists:banques,id'],
            'reference' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string', 'max:1000'],
        ], [
            'montant.gt' => 'Le montant doit être supérieur à zéro.',
            'date_paiement.before_or_equal' => 'La date de paiement ne peut pas être dans le futur.',
            'banque_id.required' => 'Choisissez la banque sur laquelle le paiement est passé.',
        ]);

        try {
            $this->workflow->enregistrerPaiement($facture, $validated);
        } catch (\DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('factures.manage', $facture)->with('success', 'Paiement enregistré.');
    }

    public function destroyPaiement(PaiementFacture $paiement): RedirectResponse
    {
        $facture = $paiement->facture;

        try {
            $this->workflow->supprimerPaiement($paiement);
        } catch (\DomainException $e) {
            return redirect()->route('factures.manage', $facture)->with('error', $e->getMessage());
        }

        return redirect()->route('factures.manage', $facture)->with('success', 'Paiement supprimé.');
    }

    public function toggleTva(Facture $facture): RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        try {
            DB::transaction(function () use ($facture) {
                $facture->tva_active = !$facture->tva_active;
                $facture->save();

                $facture->items()->update(['taux_tva' => $facture->tva_active ? 18 : 0]);

                if ($facture->depasseFactureOrigine()) {
                    throw new \DomainException($this->messageDepassement($facture));
                }
            });
        } catch (\DomainException $e) {
            return redirect()->route('factures.manage', $facture)->with('error', $e->getMessage());
        }

        $this->workflow->recalculerLies($facture);

        return redirect()->route('factures.manage', $facture)
            ->with('success', $facture->tva_active ? 'TVA (18%) activée sur la facture.' : 'TVA désactivée sur la facture.');
    }

    public function toggleCentimesAdditionnels(Facture $facture): RedirectResponse
    {
        if ($refus = $this->refuserSiCertifiee($facture)) {
            return $refus;
        }

        try {
            DB::transaction(function () use ($facture) {
                $facture->centimes_additionnels_actif = !$facture->centimes_additionnels_actif;
                $facture->save();

                if ($facture->depasseFactureOrigine()) {
                    throw new \DomainException($this->messageDepassement($facture));
                }
            });
        } catch (\DomainException $e) {
            return redirect()->route('factures.manage', $facture)->with('error', $e->getMessage());
        }

        $this->workflow->recalculerLies($facture);

        return redirect()->route('factures.manage', $facture)
            ->with('success', $facture->centimes_additionnels_actif ? 'Centimes additionnels (5%) activés.' : 'Centimes additionnels désactivés.');
    }

    /**
     * Libellés des contacts pour les listes de choix, avec un signal si le client
     * ne pourra pas être certifié auprès du SFEC en l'état.
     */
    private function contactsPourSelection(): \Illuminate\Support\Collection
    {
        return Contact::orderBy('raison_social')->get()->mapWithKeys(fn (Contact $c) => [
            $c->id => ($c->raison_social ?: $c->nom_complet ?: "Contact #{$c->id}")
                . ($c->type_contact === \App\Enums\TypeContact::Client && !$c->estPretSfec() ? ' — SFEC incomplet' : ''),
        ]);
    }

    private function refuserSiCertifiee(Facture $facture): ?RedirectResponse
    {
        if (!$facture->estCertifiee()) {
            return null;
        }

        return redirect()->route('factures.manage', $facture)->with('error',
            'Cette facture est certifiée auprès du SFEC : elle ne peut plus être modifiée ni supprimée. '
            . 'Créez une facture d\'avoir pour la corriger ou l\'annuler.'
        );
    }

    /**
     * Règles SFEC des avoirs : un avoir référence une facture de vente (jamais un autre avoir),
     * et une facture ne peut recevoir qu'un seul avoir.
     */
    private function verifierAvoir(Validator $validator, ?Facture $facture = null): void
    {
        $data = $validator->getData();
        if (($data['type_facture'] ?? null) !== TypeFacture::Avoir->value) {
            return;
        }

        $origine = !empty($data['facture_origine_id']) ? Facture::find($data['facture_origine_id']) : null;
        if (!$origine) {
            $validator->errors()->add('facture_origine_id', 'Un avoir doit référencer la facture de vente d\'origine.');
            return;
        }

        if ($origine->type_facture !== TypeFacture::Vente) {
            $validator->errors()->add('facture_origine_id', 'La facture d\'origine d\'un avoir doit être une facture de vente.');
        }

        $autreAvoir = Facture::withoutGlobalScope('agence')
            ->where('facture_origine_id', $origine->id)
            ->where('type_facture', TypeFacture::Avoir->value)
            ->when($facture, fn ($q) => $q->whereKeyNot($facture->id))
            ->exists();

        if ($autreAvoir) {
            $validator->errors()->add('facture_origine_id', 'Cette facture possède déjà un avoir (le SFEC n\'accepte qu\'un avoir par facture).');
        }

        if ($facture && round($facture->total_ttc, 2) > round($origine->total_ttc, 2)) {
            $validator->errors()->add('facture_origine_id', 'Le montant de cet avoir dépasse celui de la facture d\'origine.');
        }
    }

    private function messageDepassement(Facture $avoir): string
    {
        $plafond = number_format($avoir->factureOrigine->total_ttc, 0, ',', ' ');

        return "Un avoir ne peut pas dépasser le montant de la facture d'origine ({$plafond} FCFA TTC).";
    }
}
