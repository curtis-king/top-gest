<?php

namespace App\Http\Controllers;

use App\Models\CategorieDepense;
use App\Models\CompteComptable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorieDepenseController extends Controller
{
    public function index(): View
    {
        $categories = CategorieDepense::with('compte')->withCount('depenses')->orderBy('libelle')->get();

        return view('categories-depenses.index', compact('categories'));
    }

    public function create(): View
    {
        $comptes = CompteComptable::where('type_compte', 'charge')->where('actif', true)->orderBy('numero_compte')->pluck('libelle', 'id');

        return view('categories-depenses.create', compact('comptes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'compte_comptable_id' => ['required', 'exists:comptes_comptables,id'],
        ]);

        CategorieDepense::create($validated);

        return redirect()->route('categories-depenses.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(CategorieDepense $categorieDepense): View
    {
        $comptes = CompteComptable::where('type_compte', 'charge')->where('actif', true)->orderBy('numero_compte')->pluck('libelle', 'id');

        return view('categories-depenses.edit', compact('categorieDepense', 'comptes'));
    }

    public function update(Request $request, CategorieDepense $categorieDepense): RedirectResponse
    {
        $validated = $request->validate([
            'libelle' => ['required', 'string', 'max:255'],
            'compte_comptable_id' => ['required', 'exists:comptes_comptables,id'],
        ]);

        $categorieDepense->update($validated);

        return redirect()->route('categories-depenses.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(CategorieDepense $categorieDepense): RedirectResponse
    {
        if ($categorieDepense->depenses()->exists()) {
            return back()->with('error', 'Cette catégorie est utilisée par des dépenses et ne peut pas être supprimée.');
        }

        $categorieDepense->delete();

        return redirect()->route('categories-depenses.index')
            ->with('success', 'Catégorie supprimée avec succès.');
    }
}
