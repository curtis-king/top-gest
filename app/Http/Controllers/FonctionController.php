<?php

namespace App\Http\Controllers;

use App\Models\Fonction;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FonctionController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['id', 'name', 'description', 'salaire', 'created_at'];
        $query = Fonction::query();
        $query = $this->applySorting($query, $sortable);
        $fonctions = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('fonctions.index', compact('fonctions', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('fonctions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'salaire' => ['required', 'numeric', 'min:0'],
        ]);

        Fonction::create($validated);

        return redirect()->route('fonctions.index')
            ->with('success', 'Fonction créée avec succès.');
    }

    public function show(Fonction $fonction): View
    {
        $fonction->load('employees');

        return view('fonctions.show', compact('fonction'));
    }

    public function edit(Fonction $fonction): View
    {
        return view('fonctions.edit', compact('fonction'));
    }

    public function update(Request $request, Fonction $fonction): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'salaire' => ['required', 'numeric', 'min:0'],
        ]);

        $fonction->update($validated);

        return redirect()->route('fonctions.index')
            ->with('success', 'Fonction mise à jour avec succès.');
    }

    public function destroy(Fonction $fonction): RedirectResponse
    {
        $fonction->delete();

        return redirect()->route('fonctions.index')
            ->with('success', 'Fonction supprimée avec succès.');
    }
}
