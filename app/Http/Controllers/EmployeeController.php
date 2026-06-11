<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Employee;
use App\Models\Fonction;
use App\Models\User;
use App\Enums\StatusMatrimonial;
use App\Enums\TypePiece;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'nom_complet', 'telephone', 'agence_id', 'fonction_id', 'created_at'];
        $query = Employee::with(['agence', 'fonction', 'user']);

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('nom_complet', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $query = $this->applySorting($query, $sortable);
        $employees = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('employees.index', compact('employees', 'sort', 'direction'));
    }

    public function create(): View
    {
        $agences = Agence::pluck('name_agence', 'id');
        $fonctions = Fonction::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        $statuses = StatusMatrimonial::cases();
        $typePieces = TypePiece::cases();

        return view('employees.create', compact(
            'agences', 'fonctions', 'users', 'statuses', 'typePieces'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'type_piece' => ['nullable', 'string'],
            'numero_piece' => ['nullable', 'string', 'max:255'],
            'status_matrimonial' => ['nullable', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        Employee::create($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employé créé avec succès.');
    }

    public function show(Employee $employee): View
    {
        $employee->load([
            'agence.compagnie',
            'fonction',
            'user',
            'dossier',
            'conges',
            'retenus',
            'primes',
            'payements',
        ]);

        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee): View
    {
        $agences = Agence::pluck('name_agence', 'id');
        $fonctions = Fonction::pluck('name', 'id');
        $users = User::pluck('name', 'id');
        $statuses = StatusMatrimonial::cases();
        $typePieces = TypePiece::cases();

        return view('employees.edit', compact(
            'employee', 'agences', 'fonctions', 'users', 'statuses', 'typePieces'
        ));
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'type_piece' => ['nullable', 'string'],
            'numero_piece' => ['nullable', 'string', 'max:255'],
            'status_matrimonial' => ['nullable', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $employee->update($validated);

        return redirect()->route('employees.index')
            ->with('success', 'Employé mis à jour avec succès.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', 'Employé supprimé avec succès.');
    }
}
