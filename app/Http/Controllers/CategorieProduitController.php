<?php

namespace App\Http\Controllers;

use App\Models\CategorieProduit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorieProduitController extends Controller
{
    public function index(): View
    {
        $categories = CategorieProduit::withCount('produits')->orderBy('nom')->get();

        return view('categories-produits.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories-produits.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        CategorieProduit::create($validated);

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(CategorieProduit $categorieProduit): View
    {
        return view('categories-produits.edit', compact('categorieProduit'));
    }

    public function update(Request $request, CategorieProduit $categorieProduit): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $categorieProduit->update($validated);

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie mise à jour avec succès.');
    }

    public function destroy(CategorieProduit $categorieProduit): RedirectResponse
    {
        $categorieProduit->delete();

        return redirect()->route('categories-produits.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
