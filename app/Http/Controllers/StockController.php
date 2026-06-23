<?php

namespace App\Http\Controllers;

use App\Models\Depot;
use App\Models\Employee;
use App\Models\Produit;
use App\Models\Stock;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        $depotQuery = Depot::with('agence');

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $depotQuery->where('agence_id', $userEmp->agence_id);
            }
        }

        $depots = $depotQuery->orderBy('nom')->get();

        $stockQuery = Stock::with(['produit.categorie', 'depot']);

        if ($depot_id = $request->input('depot_id')) {
            $stockQuery->where('depot_id', $depot_id);
        } elseif (!auth()->user()->isAdmin()) {
            $stockQuery->whereIn('depot_id', $depots->pluck('id'));
        }

        if ($produit_id = $request->input('produit_id')) {
            $stockQuery->where('produit_id', $produit_id);
        }

        if ($request->boolean('alerte')) {
            $stockQuery->whereHas('produit', fn($q) => $q->whereColumn('stocks.quantite', '<=', 'produits.stock_min'));
        }

        $stocks = $stockQuery->orderBy('updated_at', 'desc')->paginate(15)->withQueryString();
        $produits = Produit::pluck('nom', 'id');

        return view('stocks.index', compact('stocks', 'depots', 'produits'));
    }
}
