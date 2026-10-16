<?php

namespace App\Http\Controllers;

use App\Models\Prime;
use App\Models\Employee;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PrimeController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['employee_id', 'motif', 'montant', 'mois', 'annee', 'created_at'];
        $query = Prime::with('employee');
        $query = $this->applySorting($query, $sortable);
        $primes = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('primes.index', compact('primes', 'sort', 'direction'));
    }

    public function create(): View
    {
        $employees = Employee::pluck('nom_complet', 'id');

        return view('primes.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
            'annee' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        Prime::create($validated);

        return redirect()->route('primes.index')
            ->with('success', 'Prime créée avec succès.');
    }

    public function show(Prime $prime): View
    {
        $prime->load('employee');

        return view('primes.show', compact('prime'));
    }

    public function edit(Prime $prime): View
    {
        $employees = Employee::pluck('nom_complet', 'id');

        return view('primes.edit', compact('prime', 'employees'));
    }

    public function update(Request $request, Prime $prime): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
            'annee' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $prime->update($validated);

        return redirect()->route('primes.index')
            ->with('success', 'Prime mise à jour avec succès.');
    }

    public function destroy(Prime $prime): RedirectResponse
    {
        $prime->delete();

        return redirect()->route('primes.index')
            ->with('success', 'Prime supprimée avec succès.');
    }
}
