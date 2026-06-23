<?php

namespace App\Http\Controllers;

use App\Enums\TypeFacture;
use App\Enums\StatutFacture;
use App\Models\Agence;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\Facture;
use App\Models\ItemFacture;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FactureController extends Controller
{
    use Sortable;

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

    public function create(): View
    {
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $nextNum = $this->nextNumero();

        return view('factures.create', compact('types', 'statuts', 'contacts', 'agences', 'nextNum'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type_facture' => ['required', 'string'],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture'],
            'date_facture' => ['required', 'date'],
            'statut_facture' => ['required', 'string'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
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

        return view('factures.edit', compact('facture', 'types', 'statuts', 'contacts', 'agences'));
    }

    public function update(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'type_facture' => ['required', 'string'],
            'numero_facture' => ['required', 'string', 'max:255', 'unique:factures,numero_facture,' . $facture->id],
            'date_facture' => ['required', 'date'],
            'statut_facture' => ['required', 'string'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        $facture->update($validated);

        return redirect()->route('factures.index')
            ->with('success', 'Facture mise à jour avec succès.');
    }

    public function destroy(Facture $facture): RedirectResponse
    {
        $facture->delete();

        return redirect()->route('factures.index')
            ->with('success', 'Facture supprimée avec succès.');
    }

    public function manage(Facture $facture): View
    {
        $facture->load(['items', 'agence', 'contact']);
        $types = TypeFacture::cases();
        $statuts = StatutFacture::cases();

        return view('factures.manage', compact('facture', 'types', 'statuts'));
    }

    public function storeItem(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'quantite' => ['required', 'integer', 'min:1'],
            'prix_unitaire' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['facture_id'] = $facture->id;
        ItemFacture::create($validated);

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Article ajouté à la facture.');
    }

    public function destroyItem(ItemFacture $itemFacture): RedirectResponse
    {
        $factureId = $itemFacture->facture_id;
        $itemFacture->delete();

        return redirect()->route('factures.manage', $factureId)
            ->with('success', 'Article supprimé.');
    }

    public function updateStatut(Request $request, Facture $facture): RedirectResponse
    {
        $validated = $request->validate([
            'statut_facture' => ['required', 'string'],
        ]);

        $facture->update($validated);

        return redirect()->route('factures.manage', $facture)
            ->with('success', 'Statut mis à jour.');
    }

    private function nextNumero(): string
    {
        $last = Facture::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->numero_facture, 4)) + 1 : 1;
        return 'FAC-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
