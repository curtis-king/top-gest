<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Compagnie;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AgenceController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['id', 'name_agence', 'compagnie_id', 'adresse', 'ville', 'numero_telephone', 'adresse_email', 'created_at'];
        $query = Agence::with('compagnie');
        $query = $this->applySorting($query, $sortable);
        $agences = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('agences.index', compact('agences', 'sort', 'direction'));
    }

    public function create(): View
    {
        $compagnies = Compagnie::pluck('name', 'id');

        return view('agences.create', compact('compagnies'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name_agence' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'numero_telephone' => ['nullable', 'string', 'max:50'],
            'adresse_email' => ['nullable', 'email', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'compagnie_id' => ['required', 'exists:compagnies,id'],
        ]);

        Agence::create($validated);

        return redirect()->route('agences.index')
            ->with('success', 'Agence créée avec succès.');
    }

    public function show(Agence $agence): View
    {
        $agence->load('compagnie', 'employees');

        return view('agences.show', compact('agence'));
    }

    public function edit(Agence $agence): View
    {
        $compagnies = Compagnie::pluck('name', 'id');

        return view('agences.edit', compact('agence', 'compagnies'));
    }

    public function update(Request $request, Agence $agence): RedirectResponse
    {
        $validated = $request->validate([
            'name_agence' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'numero_telephone' => ['nullable', 'string', 'max:50'],
            'adresse_email' => ['nullable', 'email', 'max:255'],
            'ville' => ['nullable', 'string', 'max:255'],
            'compagnie_id' => ['required', 'exists:compagnies,id'],
        ]);

        $agence->update($validated);

        return redirect()->route('agences.index')
            ->with('success', 'Agence mise à jour avec succès.');
    }

    public function destroy(Agence $agence): RedirectResponse
    {
        $agence->delete();

        return redirect()->route('agences.index')
            ->with('success', 'Agence supprimée avec succès.');
    }
}
