<?php

namespace App\Http\Controllers;

use App\Enums\UniteMesure;
use App\Models\CategorieProduit;
use App\Models\Employee;
use App\Models\Produit;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProduitController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'code', 'nom', 'prix_achat', 'prix_vente', 'stock_min', 'created_at'];
        $query = Produit::with(['categorie', 'gestionnaire', 'stocks']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if ($categorie_id = $request->input('categorie_produit_id')) {
            $query->where('categorie_produit_id', $categorie_id);
        }

        $query = $this->applySorting($query, $sortable);
        $produits = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $categories = CategorieProduit::pluck('nom', 'id');

        return view('produits.index', compact('produits', 'sort', 'direction', 'categories'));
    }

    public function create(): View
    {
        $categories = CategorieProduit::pluck('nom', 'id');
        $employees = Employee::pluck('nom_complet', 'id');
        $unites = UniteMesure::cases();
        $nextCode = $this->nextCode();

        return view('produits.create', compact('categories', 'employees', 'unites', 'nextCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:produits,code'],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unite_mesure' => ['required', 'string'],
            'prix_achat' => ['required', 'numeric', 'min:0'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock_min' => ['required', 'integer', 'min:0'],
            'categorie_produit_id' => ['nullable', 'exists:categories_produits,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        Produit::create($validated);

        return redirect()->route('produits.index')
            ->with('success', 'Produit créé avec succès.');
    }

    public function show(Produit $produit): View
    {
        $produit->load(['categorie', 'gestionnaire', 'stocks.depot', 'mouvements' => fn($q) => $q->latest()->limit(20)->with(['depot', 'depotDestination', 'user'])]);

        return view('produits.show', compact('produit'));
    }

    public function edit(Produit $produit): View
    {
        $categories = CategorieProduit::pluck('nom', 'id');
        $employees = Employee::pluck('nom_complet', 'id');
        $unites = UniteMesure::cases();

        return view('produits.edit', compact('produit', 'categories', 'employees', 'unites'));
    }

    public function update(Request $request, Produit $produit): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:255', 'unique:produits,code,' . $produit->id],
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'unite_mesure' => ['required', 'string'],
            'prix_achat' => ['required', 'numeric', 'min:0'],
            'prix_vente' => ['required', 'numeric', 'min:0'],
            'stock_min' => ['required', 'integer', 'min:0'],
            'categorie_produit_id' => ['nullable', 'exists:categories_produits,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $produit->update($validated);

        return redirect()->route('produits.index')
            ->with('success', 'Produit mis à jour avec succès.');
    }

    public function destroy(Produit $produit): RedirectResponse
    {
        try {
            $produit->delete();
        } catch (\Illuminate\Database\QueryException) {
            return redirect()->route('produits.index')
                ->with('error', 'Impossible de supprimer ce produit : il possède un historique de mouvements de stock.');
        }

        return redirect()->route('produits.index')
            ->with('success', 'Produit supprimé.');
    }

    private function nextCode(): string
    {
        $last = Produit::orderBy('id', 'desc')->first();
        $num = $last ? intval(substr($last->code, 5)) + 1 : 1;
        return 'PROD-' . str_pad($num, 5, '0', STR_PAD_LEFT);
    }
}
