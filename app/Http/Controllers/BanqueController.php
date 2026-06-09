<?php

namespace App\Http\Controllers;

use App\Models\Banque;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BanqueController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'nom', 'numero_compte', 'created_at'];
        $query = Banque::query();

        if ($search = $request->input('search')) {
            $query->where('nom', 'like', "%{$search}%")
                  ->orWhere('numero_compte', 'like', "%{$search}%");
        }

        $query = $this->applySorting($query, $sortable);
        $banques = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('banques.index', compact('banques', 'sort', 'direction'));
    }

    public function create(): View
    {
        return view('banques.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'numero_compte' => ['required', 'string', 'max:255'],
        ]);

        Banque::create($validated);

        return redirect()->route('banques.index')
            ->with('success', 'Banque créée avec succès.');
    }

    public function show(Banque $banque): View
    {
        return view('banques.show', compact('banque'));
    }

    public function edit(Banque $banque): View
    {
        return view('banques.edit', compact('banque'));
    }

    public function update(Request $request, Banque $banque): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'numero_compte' => ['required', 'string', 'max:255'],
        ]);

        $banque->update($validated);

        return redirect()->route('banques.index')
            ->with('success', 'Banque mise à jour avec succès.');
    }

    public function destroy(Banque $banque): RedirectResponse
    {
        $banque->delete();

        return redirect()->route('banques.index')
            ->with('success', 'Banque supprimée avec succès.');
    }
}
