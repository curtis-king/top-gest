<?php

namespace App\Http\Controllers;

use App\Models\Retenu;
use App\Models\Employee;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RetenuController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['employee_id', 'date_retenu', 'motif', 'montant', 'created_at'];
        $query = Retenu::with('employee');
        $query = $this->applySorting($query, $sortable);
        $retenus = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('retenus.index', compact('retenus', 'sort', 'direction'));
    }

    public function create(): View
    {
        $employees = Employee::pluck('nom_complet', 'id');

        return view('retenus.create', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_retenu' => ['required', 'date'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        Retenu::create($validated);

        return redirect()->route('retenus.index')
            ->with('success', 'Retenu créée avec succès.');
    }

    public function show(Retenu $retenu): View
    {
        $retenu->load('employee');

        return view('retenus.show', compact('retenu'));
    }

    public function edit(Retenu $retenu): View
    {
        $employees = Employee::pluck('nom_complet', 'id');

        return view('retenus.edit', compact('retenu', 'employees'));
    }

    public function update(Request $request, Retenu $retenu): RedirectResponse
    {
        $validated = $request->validate([
            'date_retenu' => ['required', 'date'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $retenu->update($validated);

        return redirect()->route('retenus.index')
            ->with('success', 'Retenu mise à jour avec succès.');
    }

    public function destroy(Retenu $retenu): RedirectResponse
    {
        $retenu->delete();

        return redirect()->route('retenus.index')
            ->with('success', 'Retenu supprimée avec succès.');
    }
}
