<?php

namespace App\Http\Controllers;

use App\Models\CategorieDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategorieDocumentController extends Controller
{
    public function index(): View
    {
        $categories = CategorieDocument::withCount('documents')->orderBy('nom')->get();
        return view('categories-documents.index', compact('categories'));
    }

    public function create(): View
    {
        return view('categories-documents.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        CategorieDocument::create($validated);

        return redirect()->route('categories-documents.index')
            ->with('success', 'Catégorie créée avec succès.');
    }

    public function edit(CategorieDocument $categoriesDocument): View
    {
        return view('categories-documents.edit', ['categorie' => $categoriesDocument]);
    }

    public function update(Request $request, CategorieDocument $categoriesDocument): RedirectResponse
    {
        $validated = $request->validate([
            'nom'         => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $categoriesDocument->update($validated);

        return redirect()->route('categories-documents.index')
            ->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(CategorieDocument $categoriesDocument): RedirectResponse
    {
        $categoriesDocument->delete();

        return redirect()->route('categories-documents.index')
            ->with('success', 'Catégorie supprimée.');
    }
}
