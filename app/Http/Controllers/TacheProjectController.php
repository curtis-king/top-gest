<?php

namespace App\Http\Controllers;

use App\Enums\StatusTache;
use App\Models\TacheProject;
use App\Models\Project;
use App\Models\Agence;
use App\Models\Employee;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TacheProjectController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'nom_tache', 'cout_tache', 'status', 'agence_id', 'project_id', 'created_at'];
        $query = TacheProject::with(['project', 'agence']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where('nom_tache', 'like', "%{$search}%");
        }

        $query = $this->applySorting($query, $sortable);
        $taches = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $totalCoutGlobal = $taches->total() > 0
            ? TacheProject::query()
                ->when(!auth()->user()->isAdmin(), function ($q) {
                    $userEmp = Employee::where('user_id', auth()->id())->first();
                    if ($userEmp && $userEmp->agence_id) {
                        $q->where('agence_id', $userEmp->agence_id);
                    }
                })
                ->sum('cout_tache')
            : 0;

        return view('taches_projects.index', compact('taches', 'sort', 'direction', 'totalCoutGlobal'));
    }

    public function create(): View
    {
        $projects = Project::pluck('nom_project', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $statuses = StatusTache::cases();

        return view('taches_projects.create', compact('projects', 'agences', 'statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_tache' => ['required', 'string', 'max:255'],
            'description_tache' => ['nullable', 'string'],
            'cout_tache' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        TacheProject::create($validated);

        return redirect()->route('taches-projects.index')
            ->with('success', 'Tâche créée avec succès.');
    }

    public function show(TacheProject $tacheProject): View
    {
        $tacheProject->load(['project', 'agence', 'affectations.employee']);

        return view('taches_projects.show', compact('tacheProject'));
    }

    public function edit(TacheProject $tacheProject): View
    {
        $projects = Project::pluck('nom_project', 'id');
        $agences = Agence::pluck('name_agence', 'id');
        $statuses = StatusTache::cases();

        return view('taches_projects.edit', compact('tacheProject', 'projects', 'agences', 'statuses'));
    }

    public function update(Request $request, TacheProject $tacheProject): RedirectResponse
    {
        $validated = $request->validate([
            'nom_tache' => ['required', 'string', 'max:255'],
            'description_tache' => ['nullable', 'string'],
            'cout_tache' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'project_id' => ['required', 'exists:projects,id'],
        ]);

        $tacheProject->update($validated);

        return redirect()->route('taches-projects.index')
            ->with('success', 'Tâche mise à jour avec succès.');
    }

    public function destroy(TacheProject $tacheProject): RedirectResponse
    {
        $projectId = $tacheProject->project_id;
        $tacheProject->delete();

        if (request()->has('from_manage')) {
            return redirect()->route('projects.manage', $projectId)
                ->with('success', 'Tâche supprimée.');
        }

        return redirect()->route('taches-projects.index')
            ->with('success', 'Tâche supprimée avec succès.');
    }

    public function updateStatusInline(Request $request, TacheProject $tacheProject): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string'],
        ]);

        $tacheProject->update($validated);

        return redirect()->route('projects.manage', $tacheProject->project_id)
            ->with('success', 'Statut de la tâche mis à jour.');
    }
}
