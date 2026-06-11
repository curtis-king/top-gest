<?php

namespace App\Http\Controllers;

use App\Models\Agence;
use App\Models\Conge;
use App\Models\Employee;
use App\Models\Fonction;
use App\Models\PayementEmployee;
use App\Models\Prime;
use App\Models\Retenu;
use App\Models\User;
use App\Enums\StatusMatrimonial;
use App\Enums\StatusPayement;
use App\Enums\TypePiece;
use App\Enums\TypeContrat;
use App\Enums\StatusDossier;
use App\Enums\TypeConge;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EmployeeWizardController extends Controller
{
    public function create(): View
    {
        $data = $this->getFormData();
        return view('employees.wizard', array_merge($data, [
            'mode' => 'create',
            'employee' => null,
        ]));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'type_piece' => ['nullable', 'string'],
            'numero_piece' => ['nullable', 'string', 'max:255'],
            'status_matrimonial' => ['nullable', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $employee = Employee::create($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Employé créé avec succès.');
    }

    public function edit(Employee $employee): View
    {
        $employee->load([
            'agence', 'fonction', 'user', 'dossier',
            'conges', 'primes', 'retenus', 'payements'
        ]);

        $data = $this->getFormData();
        return view('employees.wizard', array_merge($data, [
            'mode' => 'edit',
            'employee' => $employee,
        ]));
    }

    public function updateStep1(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'nom_complet' => ['required', 'string', 'max:255'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'telephone' => ['nullable', 'string', 'max:50'],
            'type_piece' => ['nullable', 'string'],
            'numero_piece' => ['nullable', 'string', 'max:255'],
            'status_matrimonial' => ['nullable', 'string'],
            'agence_id' => ['required', 'exists:agences,id'],
            'fonction_id' => ['required', 'exists:fonctions,id'],
            'user_id' => ['nullable', 'exists:users,id'],
        ]);

        $employee->update($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Informations mises à jour.');
    }

    public function saveStep2(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'date_engagement' => ['required', 'date'],
            'date_fin' => ['nullable', 'date', 'after:date_engagement'],
            'type_contrat' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $data = array_merge($validated, ['employee_id' => $employee->id]);

        $dossier = $employee->dossier;
        if ($dossier) {
            $dossier->update($data);
        } else {
            $employee->dossier()->create($data);
        }

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Dossier mis à jour.');
    }

    public function addConge(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'date_debut' => ['required', 'date'],
            'date_fin' => ['required', 'date', 'after_or_equal:date_debut'],
            'type_conge' => ['required', 'string'],
        ]);

        $employee->conges()->create($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Congé ajouté.');
    }

    public function deleteConge(Employee $employee, Conge $conge): RedirectResponse
    {
        if ($conge->employee_id !== $employee->id) {
            abort(403);
        }
        $conge->delete();
        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Congé supprimé.');
    }

    public function addPrime(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
            'mois' => ['required', 'integer', 'min:1', 'max:12'],
            'annee' => ['required', 'integer', 'min:2020', 'max:2100'],
        ]);

        $employee->primes()->create($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Prime ajoutée.');
    }

    public function deletePrime(Employee $employee, Prime $prime): RedirectResponse
    {
        if ($prime->employee_id !== $employee->id) {
            abort(403);
        }
        $prime->delete();
        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Prime supprimée.');
    }

    public function addRetenu(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'date_retenu' => ['required', 'date'],
            'motif' => ['required', 'string', 'max:255'],
            'montant' => ['required', 'numeric', 'min:0'],
        ]);

        $employee->retenus()->create($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Retenue ajoutée.');
    }

    public function deleteRetenu(Employee $employee, Retenu $retenu): RedirectResponse
    {
        if ($retenu->employee_id !== $employee->id) {
            abort(403);
        }
        $retenu->delete();
        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Retenue supprimée.');
    }

    public function addPayement(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'mois' => ['required', 'string', 'max:20'],
            'annee' => ['required', 'string', 'max:10'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'total_primes' => ['nullable', 'numeric', 'min:0'],
            'total_retenus' => ['nullable', 'numeric', 'min:0'],
            'net_a_payer' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'date_paiement' => ['nullable', 'date'],
        ]);

        $employee->payements()->create($validated);

        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Paiement ajouté.');
    }

    public function deletePayement(Employee $employee, PayementEmployee $payement): RedirectResponse
    {
        if ($payement->employee_id !== $employee->id) {
            abort(403);
        }
        $payement->delete();
        return redirect()->route('employees.wizard.edit', $employee)
            ->with('success', 'Paiement supprimé.');
    }

    private function getFormData(): array
    {
        return [
            'agences' => Agence::pluck('name_agence', 'id'),
            'fonctions' => Fonction::pluck('name', 'id'),
            'users' => User::pluck('name', 'id'),
            'statuses' => StatusMatrimonial::cases(),
            'typePieces' => TypePiece::cases(),
            'typesContrat' => TypeContrat::cases(),
            'statusDossiers' => StatusDossier::cases(),
            'typesConge' => TypeConge::cases(),
            'statusPayements' => StatusPayement::cases(),
        ];
    }
}
