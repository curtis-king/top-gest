<?php

namespace App\Http\Controllers;

use App\Enums\SensCompte;
use App\Enums\TypeCompteComptable;
use App\Http\Controllers\Traits\Sortable;
use App\Models\CompteComptable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompteComptableController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'numero_compte', 'libelle', 'classe', 'created_at'];
        $query = CompteComptable::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('numero_compte', 'like', "%{$search}%")
                  ->orWhere('libelle', 'like', "%{$search}%");
            });
        }

        if ($classe = $request->input('classe')) {
            $query->where('classe', $classe);
        }

        $query = $this->applySorting($query, $sortable, 'numero_compte', 'asc');
        $comptes = $query->paginate(30)->withQueryString();
        $sort = request('sort', 'numero_compte');
        $direction = request('direction', 'asc');

        return view('comptes-comptables.index', compact('comptes', 'sort', 'direction'));
    }

    public function create(): View
    {
        $types = TypeCompteComptable::cases();
        $sens = SensCompte::cases();
        $comptesParents = CompteComptable::orderBy('numero_compte')->pluck('numero_compte', 'id');

        return view('comptes-comptables.create', compact('types', 'sens', 'comptesParents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'numero_compte' => ['required', 'string', 'max:20', 'unique:comptes_comptables,numero_compte'],
            'libelle' => ['required', 'string', 'max:255'],
            'type_compte' => ['required', 'string'],
            'sens_normal' => ['required', 'string'],
            'compte_parent_id' => ['nullable', 'exists:comptes_comptables,id'],
        ]);

        $validated['classe'] = (int) $validated['numero_compte'][0];
        $validated['is_systeme'] = false;
        $validated['actif'] = true;

        CompteComptable::create($validated);

        return redirect()->route('comptes-comptables.index')
            ->with('success', 'Compte créé avec succès.');
    }

    public function edit(CompteComptable $compteComptable): View
    {
        $types = TypeCompteComptable::cases();
        $sens = SensCompte::cases();
        $comptesParents = CompteComptable::where('id', '!=', $compteComptable->id)->orderBy('numero_compte')->pluck('numero_compte', 'id');

        return view('comptes-comptables.edit', compact('compteComptable', 'types', 'sens', 'comptesParents'));
    }

    public function update(Request $request, CompteComptable $compteComptable): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'type_compte' => ['required', 'string'],
            'sens_normal' => ['required', 'string'],
            'compte_parent_id' => ['nullable', 'exists:comptes_comptables,id'],
            'actif' => ['nullable', 'boolean'],
        ]);

        if ($compteComptable->is_systeme) {
            unset($validated['type_compte'], $validated['sens_normal']);
        }

        $validated['actif'] = $request->boolean('actif');

        $compteComptable->update($validated);

        return redirect()->route('comptes-comptables.index')
            ->with('success', 'Compte mis à jour avec succès.');
    }

    public function destroy(CompteComptable $compteComptable): RedirectResponse
    {
        if ($compteComptable->is_systeme) {
            return back()->with('error', 'Ce compte fait partie du plan comptable standard et ne peut pas être supprimé.');
        }

        if ($compteComptable->lignesEcritures()->exists()) {
            return back()->with('error', 'Ce compte est utilisé dans des écritures et ne peut pas être supprimé.');
        }

        $compteComptable->delete();

        return redirect()->route('comptes-comptables.index')
            ->with('success', 'Compte supprimé avec succès.');
    }
}
