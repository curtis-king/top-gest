<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Depot;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DepotController extends Controller
{
    public function index(): View
    {
        $query = Depot::with('agence')->withCount('stocks');

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        $depots = $query->orderBy('nom')->get();

        return view('depots.index', compact('depots'));
    }

    public function create(): View
    {
        $agences = Agence::pluck('name_agence', 'id');

        return view('depots.create', compact('agences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        Depot::create($validated);

        return redirect()->route('depots.index')
            ->with('success', 'Dépôt créé avec succès.');
    }

    public function edit(Depot $depot): View
    {
        $agences = Agence::pluck('name_agence', 'id');

        return view('depots.edit', compact('depot', 'agences'));
    }

    public function update(Request $request, Depot $depot): RedirectResponse
    {
        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

        $depot->update($validated);

        return redirect()->route('depots.index')
            ->with('success', 'Dépôt mis à jour avec succès.');
    }

    public function destroy(Depot $depot): RedirectResponse
    {
        $depot->delete();

        return redirect()->route('depots.index')
            ->with('success', 'Dépôt supprimé.');
    }
}
