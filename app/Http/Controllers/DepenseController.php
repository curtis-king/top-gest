<?php

namespace App\Http\Controllers;

use App\Enums\ModePaiementDepense;
use App\Enums\StatutDepense;
use App\Http\Controllers\Traits\Sortable;
use App\Models\Agence;
use App\Models\Banque;
use App\Models\CategorieDepense;
use App\Models\Contact;
use App\Models\Depense;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepenseController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'numero_depense', 'date_depense', 'montant', 'statut', 'created_at'];
        $query = Depense::with(['categorie', 'banque', 'contact', 'agence']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_depense', 'like', "%{$search}%")
                  ->orWhere('objet', 'like', "%{$search}%");
            });
        }

        if ($statut = $request->input('statut')) {
            $query->where('statut', $statut);
        }

        if ($categorie_depense_id = $request->input('categorie_depense_id')) {
            $query->where('categorie_depense_id', $categorie_depense_id);
        }

        $query = $this->applySorting($query, $sortable);
        $depenses = $query->paginate(15)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $stats = [
            'total' => (clone $query)->count(),
            'montant_total' => (clone $query)->sum('montant'),
            'montant_payee' => (clone $query)->where('statut', StatutDepense::Payee->value)->sum('montant'),
        ];

        $categories = CategorieDepense::pluck('libelle', 'id');
        $statuts = StatutDepense::cases();

        return view('depenses.index', compact('depenses', 'sort', 'direction', 'categories', 'statuts', 'stats'));
    }

    public function create(): View
    {
        $categories = CategorieDepense::pluck('libelle', 'id');
        $banques = Banque::pluck('nom', 'id');
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $modes = ModePaiementDepense::cases();
        $statuts = StatutDepense::cases();
        $nextNum = $this->nextNumero();

        return view('depenses.create', compact('categories', 'banques', 'contacts', 'agences', 'modes', 'statuts', 'nextNum'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_depense' => ['required', 'string', 'max:255', 'unique:depenses,numero_depense'],
            'date_depense' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'categorie_depense_id' => ['nullable', 'exists:categories_depenses,id'],
            'montant' => ['required', 'numeric', 'min:0'],
            'mode_paiement' => ['required', 'string'],
            'banque_id' => ['nullable', 'exists:banques,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'statut' => ['required', 'string'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        $validated['created_by'] = auth()->id();

        Depense::create($validated);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense créée avec succès.');
    }

    public function show(Depense $depense): View
    {
        $depense->load(['categorie', 'banque', 'contact', 'agence', 'createur']);

        return view('depenses.show', compact('depense'));
    }

    public function edit(Depense $depense): View
    {
        $categories = CategorieDepense::pluck('libelle', 'id');
        $banques = Banque::pluck('nom', 'id');
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $modes = ModePaiementDepense::cases();
        $statuts = StatutDepense::cases();

        return view('depenses.edit', compact('depense', 'categories', 'banques', 'contacts', 'agences', 'modes', 'statuts'));
    }

    public function update(Request $request, Depense $depense): RedirectResponse
    {
        $validated = $request->validate([
            'numero_depense' => ['required', 'string', 'max:255', 'unique:depenses,numero_depense,' . $depense->id],
            'date_depense' => ['required', 'date'],
            'objet' => ['required', 'string', 'max:255'],
            'categorie_depense_id' => ['nullable', 'exists:categories_depenses,id'],
            'montant' => ['required', 'numeric', 'min:0'],
            'mode_paiement' => ['required', 'string'],
            'banque_id' => ['nullable', 'exists:banques,id'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'statut' => ['required', 'string'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        $depense->update($validated);

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense mise à jour avec succès.');
    }

    public function destroy(Depense $depense): RedirectResponse
    {
        $depense->delete();

        return redirect()->route('depenses.index')
            ->with('success', 'Dépense supprimée avec succès.');
    }

    public function updateStatut(Request $request, Depense $depense): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => ['required', 'string'],
        ]);

        $depense->update($validated);

        return redirect()->route('depenses.show', $depense)
            ->with('success', 'Statut mis à jour.');
    }

    private function nextNumero(): string
    {
        $last = Depense::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->numero_depense, 4)) + 1 : 1;

        return 'DEP-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
