<?php

namespace App\Http\Controllers;

use App\Enums\StatusProject;
use App\Enums\StatusTache;
use App\Models\Project;
use App\Models\TacheProject;
use App\Models\Agence;
use App\Models\Employee;
use App\Models\AffectationTache;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'nom_project', 'date_echeance', 'status_project', 'created_at'];
        $query = Project::with('taches');

        if ($search = $request->input('search')) {
            $query->where('nom_project', 'like', "%{$search}%");
        }

        $query = $this->applySorting($query, $sortable);
        $projects = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $projects->getCollection()->transform(function ($p) {
            $total = $p->taches->count();
            $completed = $p->taches->filter(fn($t) => $t->status?->value === 'terminee')->count();
            $p->progress = $total > 0 ? round(($completed / $total) * 100) : 0;
            $p->total_cout = $p->taches->sum('cout_tache');
            return $p;
        });

        $all = Project::with('taches')->get();
        $stats = [
            'total' => $all->count(),
            'actif' => $all->filter(fn($p) => $p->status_project?->value === 'actif')->count(),
            'en_cours' => $all->filter(fn($p) => $p->status_project?->value === 'en_cours')->count(),
            'termine' => $all->filter(fn($p) => $p->status_project?->value === 'termine')->count(),
            'annule' => $all->filter(fn($p) => $p->status_project?->value === 'annule')->count(),
            'total_cout' => $all->sum(fn($p) => $p->taches->sum('cout_tache')),
        ];

        return view('projects.index', compact('projects', 'sort', 'direction', 'stats'));
    }

    public function create(): View
    {
        $statuses = StatusProject::cases();

        return view('projects.create', compact('statuses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_project' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
            'status_project' => ['required', 'string'],
        ]);

        Project::create($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Projet créé avec succès.');
    }

    public function show(Project $project): View
    {
        $project->load('taches');

        $total = $project->taches->count();
        $completed = $project->taches->filter(fn($t) => $t->status?->value === 'terminee')->count();
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
        $totalCout = $project->taches->sum('cout_tache');

        return view('projects.show', compact('project', 'progress', 'totalCout', 'total', 'completed'));
    }

    public function edit(Project $project): View
    {
        $statuses = StatusProject::cases();

        return view('projects.edit', compact('project', 'statuses'));
    }

    public function update(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'nom_project' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date_echeance' => ['nullable', 'date'],
            'status_project' => ['required', 'string'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.index')
            ->with('success', 'Projet mis à jour avec succès.');
    }

    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()->route('projects.index')
            ->with('success', 'Projet supprimé avec succès.');
    }

    public function manage(Project $project): View
    {
        $project->load(['taches.affectations.employee', 'taches.agence']);

        $total = $project->taches->count();
        $completed = $project->taches->filter(fn($t) => $t->status?->value === 'terminee')->count();
        $progress = $total > 0 ? round(($completed / $total) * 100) : 0;
        $totalCout = $project->taches->sum('cout_tache');
        $statuses = StatusProject::cases();
        $tacheStatuses = StatusTache::cases();
        $agences = Agence::pluck('name_agence', 'id');
        $employees = Employee::pluck('nom_complet', 'id');

        return view('projects.manage', compact(
            'project', 'progress', 'totalCout', 'total', 'completed',
            'statuses', 'tacheStatuses', 'agences', 'employees'
        ));
    }

    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'status_project' => ['required', 'string'],
        ]);

        $project->update($validated);

        return redirect()->route('projects.manage', $project)
            ->with('success', 'Statut du projet mis à jour.');
    }

    public function storeTache(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'nom_tache' => ['required', 'string', 'max:255'],
            'description_tache' => ['nullable', 'string'],
            'cout_tache' => ['nullable', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
        ]);

        $validated['project_id'] = $project->id;
        TacheProject::create($validated);

        return redirect()->route('projects.manage', $project)
            ->with('success', 'Tâche ajoutée au projet.');
    }
}
