<?php

namespace App\Http\Controllers;

use App\Models\DossierEmployee;
use App\Models\Employee;
use App\Enums\StatusDossier;
use App\Enums\TypeContrat;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DossierEmployeeController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'employee_id', 'date_engagement', 'type_contrat', 'status', 'created_at'];
        $query = DossierEmployee::with('employee');

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->whereHas('employee', fn($q) => $q->where('agence_id', $userEmp->agence_id));
            }
        }

        if ($search = $request->input('search')) {
            $query->whereHas('employee', function ($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%");
            });
        }

        $query = $this->applySorting($query, $sortable);
        $dossiers = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('dossiers_employees.index', compact('dossiers', 'sort', 'direction'));
    }

    public function create(): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $typesContrat = TypeContrat::cases();
        $statuses = StatusDossier::cases();

        return view('dossiers_employees.create', compact(
            'employees', 'typesContrat', 'statuses'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_engagement' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_engagement'],
            'type_contrat' => ['required', 'string'],
            'status' => ['required', 'string'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        DossierEmployee::create($validated);

        return redirect()->route('dossiers-employees.index')
            ->with('success', 'Dossier créé avec succès.');
    }

    public function show(DossierEmployee $dossierEmployee): View
    {
        $dossierEmployee->load('employee');

        return view('dossiers_employees.show', compact('dossierEmployee'));
    }

    public function edit(DossierEmployee $dossierEmployee): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $typesContrat = TypeContrat::cases();
        $statuses = StatusDossier::cases();

        return view('dossiers_employees.edit', compact(
            'dossierEmployee', 'employees', 'typesContrat', 'statuses'
        ));
    }

    public function update(Request $request, DossierEmployee $dossierEmployee): RedirectResponse
    {
        $validated = $request->validate([
            'date_engagement' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_engagement'],
            'type_contrat' => ['required', 'string'],
            'status' => ['required', 'string'],
            'employee_id' => ['required', 'exists:employees,id'],
        ]);

        $dossierEmployee->update($validated);

        return redirect()->route('dossiers-employees.index')
            ->with('success', 'Dossier mis à jour avec succès.');
    }

    public function destroy(DossierEmployee $dossierEmployee): RedirectResponse
    {
        $dossierEmployee->delete();

        return redirect()->route('dossiers-employees.index')
            ->with('success', 'Dossier supprimé avec succès.');
    }
}
