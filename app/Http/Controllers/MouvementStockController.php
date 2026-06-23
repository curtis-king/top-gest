<?php

namespace App\Http\Controllers;

use App\Enums\TypeMouvement;
use App\Models\Depot;
use App\Models\Employee;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Stock;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MouvementStockController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'type_mouvement', 'quantite', 'date_mouvement', 'created_at'];
        $query = MouvementStock::with(['produit', 'depot', 'depotDestination', 'user']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->whereHas('depot', fn($q) => $q->where('agence_id', $userEmp->agence_id));
            }
        }

        if ($type = $request->input('type_mouvement')) {
            $query->where('type_mouvement', $type);
        }

        if ($produit_id = $request->input('produit_id')) {
            $query->where('produit_id', $produit_id);
        }

        if ($depot_id = $request->input('depot_id')) {
            $query->where('depot_id', $depot_id);
        }

        $query = $this->applySorting($query, $sortable);
        $mouvements = $query->paginate(15)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $types = TypeMouvement::cases();
        $produits = Produit::pluck('nom', 'id');
        $depots = Depot::pluck('nom', 'id');

        return view('mouvements-stocks.index', compact('mouvements', 'sort', 'direction', 'types', 'produits', 'depots'));
    }

    public function create(Request $request): View
    {
        $types = TypeMouvement::cases();
        $produits = Produit::orderBy('nom')->get(['id', 'nom', 'code']);
        $depots = Depot::with('agence')->orderBy('nom')->get();

        // Pre-fill autocomplete after validation failure
        $oldContactId = old('contact_id');
        $oldFactureId = old('facture_id');

        $oldContactText = $oldContactId
            ? \App\Models\Contact::where('id', $oldContactId)->value('raison_social') ?? ''
            : '';
        $oldFactureText = $oldFactureId
            ? \App\Models\Facture::where('id', $oldFactureId)->value('numero_facture') ?? ''
            : '';

        return view('mouvements-stocks.create', compact(
            'types', 'produits', 'depots',
            'oldContactId', 'oldContactText',
            'oldFactureId', 'oldFactureText'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'type_mouvement' => ['required', Rule::in(array_column(TypeMouvement::cases(), 'value'))],
            'quantite' => [
                'required',
                'integer',
                $request->input('type_mouvement') === TypeMouvement::Ajustement->value ? 'not_in:0' : 'min:1',
            ],
            'date_mouvement' => ['required', 'date'],
            'motif' => ['nullable', 'string', 'max:255'],
            'produit_id' => ['required', 'exists:produits,id'],
            'depot_id' => ['required', 'exists:depots,id'],
            'depot_destination_id' => [
                'nullable',
                Rule::requiredIf($request->input('type_mouvement') === TypeMouvement::Transfert->value),
                'exists:depots,id',
                'different:depot_id',
            ],
            'contact_id' => ['nullable', 'exists:contacts,id'],
            'facture_id' => ['nullable', 'exists:factures,id'],
        ]);

        $validated['user_id'] = auth()->id();

        DB::transaction(function () use ($validated) {
            MouvementStock::create($validated);
            $this->updateStock($validated);
        });

        return redirect()->route('mouvements-stocks.index')
            ->with('success', 'Mouvement enregistré avec succès.');
    }

    private function updateStock(array $data): void
    {
        $type = TypeMouvement::from($data['type_mouvement']);
        $produitId = $data['produit_id'];
        $depotId = $data['depot_id'];
        $quantite = $data['quantite'];

        match ($type) {
            TypeMouvement::Entree => $this->ajusterStock($produitId, $depotId, $quantite),
            TypeMouvement::Sortie => $this->ajusterStock($produitId, $depotId, -$quantite),
            TypeMouvement::Transfert => $this->transferer($produitId, $depotId, $data['depot_destination_id'], $quantite),
            TypeMouvement::Ajustement => $this->ajusterStock($produitId, $depotId, $quantite),
        };
    }

    private function ajusterStock(int $produitId, int $depotId, int $delta): void
    {
        $stock = Stock::firstOrCreate(
            ['produit_id' => $produitId, 'depot_id' => $depotId],
            ['quantite' => 0]
        );
        $stock->increment('quantite', $delta);
    }

    private function transferer(int $produitId, int $depotSource, int $depotDest, int $quantite): void
    {
        $this->ajusterStock($produitId, $depotSource, -$quantite);
        $this->ajusterStock($produitId, $depotDest, $quantite);
    }
}
