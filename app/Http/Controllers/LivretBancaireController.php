<?php

namespace App\Http\Controllers;

use App\Enums\TypeActionLivret;
use App\Models\Agence;
use App\Models\Banque;
use App\Models\Contact;
use App\Models\Employee;
use App\Models\LivretBancaire;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LivretBancaireController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'date_action', 'type_action', 'montant', 'banque_id', 'created_at'];
        $query = LivretBancaire::with(['banque', 'agence']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('motif', 'like', "%{$search}%")
                  ->orWhere('raison_social', 'like', "%{$search}%");
            });
        }

        if ($banque_id = $request->input('banque_id')) {
            $query->where('banque_id', $banque_id);
        }

        if ($type_action = $request->input('type_action')) {
            $query->where('type_action', $type_action);
        }

        if ($agence_id = $request->input('agence_id')) {
            $query->where('agence_id', $agence_id);
        }

        $query = $this->applySorting($query, $sortable);
        $livrets = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $banques = Banque::pluck('nom', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $types = TypeActionLivret::cases();

        return view('livrets_bancaires.index', compact('livrets', 'sort', 'direction', 'banques', 'agences', 'types'));
    }

    public function create(): View
    {
        $types = TypeActionLivret::cases();
        $banques = Banque::pluck('nom', 'id');
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');

        return view('livrets_bancaires.create', compact('types', 'banques', 'contacts', 'agences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_action' => ['required', 'date'],
            'type_action' => ['required', 'string'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'banque_id' => ['required', 'exists:banques,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        LivretBancaire::create($validated);

        return redirect()->route('livrets-bancaires.index')
            ->with('success', 'Écriture bancaire ajoutée avec succès.');
    }

    public function show(LivretBancaire $livretBancaire): View
    {
        $livretBancaire->load(['banque', 'agence', 'contact']);

        return view('livrets_bancaires.show', compact('livretBancaire'));
    }

    public function edit(LivretBancaire $livretBancaire): View
    {
        $types = TypeActionLivret::cases();
        $banques = Banque::pluck('nom', 'id');
        $contacts = Contact::pluck('raison_social', 'id');
        $agences = Agence::pluck('name_agence', 'id');

        return view('livrets_bancaires.edit', compact('livretBancaire', 'types', 'banques', 'contacts', 'agences'));
    }

    public function update(Request $request, LivretBancaire $livretBancaire): RedirectResponse
    {
        $validated = $request->validate([
            'date_action' => ['required', 'date'],
            'type_action' => ['required', 'string'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'raison_social' => ['nullable', 'string', 'max:255'],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'banque_id' => ['required', 'exists:banques,id'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        $livretBancaire->update($validated);

        return redirect()->route('livrets-bancaires.index')
            ->with('success', 'Écriture bancaire mise à jour avec succès.');
    }

    public function destroy(LivretBancaire $livretBancaire): RedirectResponse
    {
        $livretBancaire->delete();

        return redirect()->route('livrets-bancaires.index')
            ->with('success', 'Écriture bancaire supprimée avec succès.');
    }
}
