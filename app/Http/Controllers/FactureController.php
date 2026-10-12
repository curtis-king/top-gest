<?php

namespace App\Http\Controllers;

use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use App\Enums\StatutCertification;
use App\Models\Agence;
use App\Models\Contact;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\ItemFacture;
use App\Models\Produit;
use App\Services\StockMovementService;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactureController extends Controller
{
    use Sortable;

    public function __construct(private StockMovementService $stockMovementService)
    {
    }

    public function index(Request $request): View
    {
        $sortable = ['id', 'numero_facture', 'date_facture', 'type_facture', 'statut_facture', 'created_at'];
        $query = Facture::with(['items', 'agence']);

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
        $stats = [
            'total' => $all->count(),
            'payee' => $all->filter(fn($f) => $f->statut_facture?->value === 'payee')->count(),
            'impayee' => $all->filter(fn($f) => $f->statut_facture?->value === 'impayee')->count(),
            'montant_payee' => $all->filter(fn($f) => $f->statut_facture?->value === 'payee')->sum(fn($f) => $f->items->sum(fn($i) => $i->quantite * $i->prix_unitaire)),
            'montant_impayee' => $all->filter(fn($f) => $f->statut_facture?->value === 'impayee')->sum(fn($f) => $f->items->sum(fn($i) => $i->quantite * $i->prix_unitaire)),
        ];

        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $agences = Agence::pluck('name_agence', 'id');

        return view('factures.index', compact('factures', 'sort', 'direction', 'types', 'statuts', 'agences', 'stats'));
    }

    public function create(Request $request): View
    {
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $factures = Facture::where('type_facture', 'vente')->orderByDesc('id')->pluck('numero_facture', 'id');
        $nextNum = $this->nextNumero();

        $factureOrigine = null;
        if ($request->filled('avoir_de')) {
            $factureOrigine = Facture::find($request->integer('avoir_de'));
        }

        return view('factures.create', compact('types', 'statuts', 'contacts', 'agences', 'factures', 'nextNum', 'factureOrigine'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type_facture' => ['required', 'string'],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture'],
            'date_facture' => ['required', 'date'],
            'statut_facture' => ['required', 'string'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'interlocuteur' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'facture_origine_id' => ['nullable', 'exists:factures,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'objet' => ['nullable', 'string', 'max:500'],
        ]);

        Facture::create($validated);

        return redirect()->route('factures.index')
            ->with('success', 'Facture créée avec succès.');
    }

    public function show(Facture $facture): View
    {
        $facture->load(['items', 'agence', 'contact']);

        return view('factures.show', compact('facture'));
    }

    public function edit(Facture $facture): View
    {
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $factures = Facture::where('type_facture', 'vente')->where('id', '!=', $facture->id)->orderByDesc('id')->pluck('numero_facture', 'id');

        return view('factures.edit', compact('facture', 'types', 'statuts', 'contacts', 'agences', 'factures'));
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'type_facture' => ['required', 'string'],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture,' . $facture->id],
            'date_facture' => ['required', 'date'],
            'statut_facture' => ['required', 'string'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'interlocuteur' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'facture_origine_id' => ['nullable', 'exists:factures,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'objet' => ['nullable', 'string', 'max:500'],
        ]);

        $facture->update($validated);

        return redirect()->route('factures.index')
            ->with('success', 'Facture mise à jour avec succès.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        $facture->load('items');
        foreach ($facture->items as $item) {
            $this->stockMovementService->annulerPourArticle($item);
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
        $facture->load(['items', 'agence', 'contact']);
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $produits = Produit::orderBy('nom')->pluck('nom', 'id');
        $depots = Depot::when($facture->agence_id, fn($q) => $q->where('agence_id', $facture->agence_id))
            ->orderBy('nom')->pluck('nom', 'id');

        return view('factures.manage', compact('facture', 'types', 'statuts', 'produits', 'depots'));
    }

    public function storeItem(Request $request, Facture $facture): RedirectResponse
    {
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

        $item = ItemFacture::create($validated);

        if ($facture->statut_facture?->value !== 'brouillon') {
            $this->stockMovementService->sortirPourArticle($item);
        }

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Article ajouté à la facture.');
    }

    public function destroyItem(ItemFacture $itemFacture): RedirectResponse
    {
        $factureId = $itemFacture->facture_id;

        $this->stockMovementService->annulerPourArticle($itemFacture);
        $itemFacture->delete();

        return redirect()->route('factures.manage', $factureId)
            ->with('success', 'Article supprimé.');
    }

    public function updateStatut(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'statut_facture' => ['required', 'string'],
        ]);

        if ($validated['statut_facture'] === 'annulee' && $facture->statut_certification === StatutCertification::Certifiee) {
            return redirect()->route('factures.manage', $facture)->with('error',
                'Cette facture est certifiée auprès du SFEC : elle ne peut plus être annulée directement. '
                . 'Créez une facture d\'avoir pour l\'annuler/corriger.'
            );
        }

        $quittaitBrouillon = $facture->statut_facture?->value === 'brouillon' && $validated['statut_facture'] !== 'brouillon';

        $facture->update($validated);

        if ($quittaitBrouillon) {
            $this->stockMovementService->sortirPourFacture($facture);
        }

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Statut mis à jour.');
    }

    public function toggleTva(Facture $facture): RedirectResponse
    {
        $facture->tva_active = !$facture->tva_active;
        $facture->save();

        $facture->items()->update(['taux_tva' => $facture->tva_active ? 18 : 0]);

        return redirect()->route('factures.manage', $facture)
            ->with('success', $facture->tva_active ? 'TVA (18%) activée sur la facture.' : 'TVA désactivée sur la facture.');
    }

    public function toggleCentimesAdditionnels(Facture $facture): RedirectResponse
    {
        $facture->centimes_additionnels_actif = !$facture->centimes_additionnels_actif;
        $facture->save();

        return redirect()->route('factures.manage', $facture)
            ->with('success', $facture->centimes_additionnels_actif ? 'Centimes additionnels (5%) activés.' : 'Centimes additionnels désactivés.');
    }

    private function nextNumero(): string
    {
        $last = Facture::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->numero_facture, 4)) + 1 : 1;
        return 'FAC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
