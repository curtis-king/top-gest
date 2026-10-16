<?php

namespace App\Http\Controllers;

use App\Models\AffectationTache;
use App\Models\Employee;
use App\Models\TacheProject;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AffectationTacheController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'date_affectation', 'nom_complet', 'employee_id', 'tache_project_id', 'created_at'];
        $query = AffectationTache::with(['tache', 'employee']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhereHas('employee', fn($eq) => $eq->where('nom_complet', 'like', "%{$search}%"));
            });
        }

        $query = $this->applySorting($query, $sortable);
        $affectations = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('affectation_taches.index', compact('affectations', 'sort', 'direction'));
    }

    public function create(): View
    {
        $taches = TacheProject::pluck('nom_tache', 'id');
        $employees = Employee::pluck('nom_complet', 'id');

        return view('affectation_taches.create', compact('taches', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'date_affectation' => ['required', 'date'],
            'nom_complet' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'tache_project_id' => ['required', 'exists:taches_projects,id'],
        ]);

        AffectationTache::create($validated);

        return redirect()->route('affectation-taches.index')
            ->with('success', 'Affectation créée avec succès.');
    }

    public function show(AffectationTache $affectationTache): View
    {
        $affectationTache->load(['tache', 'employee']);

        return view('affectation_taches.show', compact('affectationTache'));
    }

    public function edit(AffectationTache $affectationTache): View
    {
        $taches = TacheProject::pluck('nom_tache', 'id');
        $employees = Employee::pluck('nom_complet', 'id');

        return view('affectation_taches.edit', compact('affectationTache', 'taches', 'employees'));
    }

    public function update(Request $request, AffectationTache $affectationTache): RedirectResponse
    {
        $validated = $request->validate([
            'date_affectation' => ['required', 'date'],
            'nom_complet' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'exists:employees,id'],
            'tache_project_id' => ['required', 'exists:taches_projects,id'],
        ]);

        $affectationTache->update($validated);

        return redirect()->route('affectation-taches.index')
            ->with('success', 'Affectation mise à jour avec succès.');
    }

    public function destroy(AffectationTache $affectationTache): RedirectResponse
    {
        $affectationTache->delete();

        return redirect()->route('affectation-taches.index')
            ->with('success', 'Affectation supprimée avec succès.');
    }

    public function storeInline(Request $request, TacheProject $tacheProject): RedirectResponse
    {
        $validated = $request->validate([
            'date_affectation' => ['required', 'date'],
            'nom_complet' => ['nullable', 'string', 'max:255'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $validated['tache_project_id'] = $tacheProject->id;
        AffectationTache::create($validated);

        return redirect()->route('projects.manage', $tacheProject->project_id)
            ->with('success', 'Affectation ajoutée.');
    }

    public function destroyInline(AffectationTache $affectationTache): RedirectResponse
    {
        $projectId = $affectationTache->tache->project_id;
        $affectationTache->delete();

        return redirect()->route('projects.manage', $projectId)
            ->with('success', 'Affectation supprimée.');
    }
}
