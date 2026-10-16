<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use App\Models\Employee;
use App\Enums\TypeConge;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CongeController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['employee_id', 'date_debut', 'date_fin', 'type_conge', 'created_at'];
        $query = Conge::with('employee');
        $query = $this->applySorting($query, $sortable);
        $conges = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('conges.index', compact('conges', 'sort', 'direction'));
    }

    public function create(): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $types = TypeConge::cases();

        return view('conges.create', compact('employees', 'types'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'type_conge' => ['required', 'string'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        Conge::create($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Congé créé avec succès.');
    }

    public function show(Conge $conge): View
    {
        $conge->load('employee');

        return view('conges.show', compact('conge'));
    }

    public function edit(Conge $conge): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $types = TypeConge::cases();

        return view('conges.edit', compact('conge', 'employees', 'types'));
    }

    public function update(Request $request, Conge $conge): RedirectResponse
    {
        $validated = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'type_conge' => ['required', 'string'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $conge->update($validated);

        return redirect()->route('conges.index')
            ->with('success', 'Congé mis à jour avec succès.');
    }

    public function destroy(Conge $conge): RedirectResponse
    {
        $conge->delete();

        return redirect()->route('conges.index')
            ->with('success', 'Congé supprimé avec succès.');
    }
}
