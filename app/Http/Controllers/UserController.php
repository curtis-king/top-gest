<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Employee;
use App\Models\User;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $query = User::with('roles', 'agence');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    public function create(): View
    {
        $roles     = Role::orderBy('name')->pluck('name');
        $scopes    = User::SCOPES;
        $agences   = Agence::orderBy('name_agence')->pluck('name_agence', 'id');
        $employees = Employee::with('agence')
            ->whereNull('user_id')
            ->orderBy('nom_complet')
            ->get();

        return view('users.create', compact('roles', 'scopes', 'agences', 'employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $roleNames = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password'    => ['required', 'string', 'min:8'],
            'scope'       => ['required', 'string', Rule::in(User::SCOPES)],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => ['string', Rule::in($roleNames)],
            'agence_id'   => ['nullable', 'exists:agences,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $selectedRoles = $validated['roles'];
        $employeeId    = $validated['employee_id'] ?? null;
        unset($validated['roles'], $validated['employee_id']);
        $validated['password'] = bcrypt($validated['password']);

        $user = User::create($validated);
        $user->syncRoles($selectedRoles);

        if ($employeeId) {
            Employee::find($employeeId)?->update(['user_id' => $user->id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur créé avec succès.');
    }

    public function show(User $user): View
    {
        $user->load('roles');
        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load('roles', 'employee');
        $roles     = Role::orderBy('name')->pluck('name');
        $scopes    = User::SCOPES;
        $agences   = Agence::orderBy('name_agence')->pluck('name_agence', 'id');
        $employees = Employee::with('agence')
            ->where(function ($q) use ($user) {
                $q->whereNull('user_id')->orWhere('user_id', $user->id);
            })
            ->orderBy('nom_complet')
            ->get();

        return view('users.edit', compact('user', 'roles', 'scopes', 'agences', 'employees'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $roleNames = Role::pluck('name')->toArray();

        $validated = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password'    => ['nullable', 'string', 'min:8'],
            'scope'       => ['required', 'string', Rule::in(User::SCOPES)],
            'roles'       => ['required', 'array', 'min:1'],
            'roles.*'     => ['string', Rule::in($roleNames)],
            'agence_id'   => ['nullable', 'exists:agences,id'],
            'employee_id' => ['nullable', 'exists:employees,id'],
        ]);

        $selectedRoles = $validated['roles'];
        $newEmployeeId = $validated['employee_id'] ?? null;
        unset($validated['roles'], $validated['employee_id']);

        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        $user->syncRoles($selectedRoles);

        // Délier l'ancien employé, lier le nouveau
        Employee::where('user_id', $user->id)->update(['user_id' => null]);
        if ($newEmployeeId) {
            Employee::find($newEmployeeId)?->update(['user_id' => $user->id]);
        }

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur mis à jour avec succès.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('users.index')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Utilisateur supprimé avec succès.');
    }
}
