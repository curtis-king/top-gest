<?php

namespace App\Http\Controllers;

use App\Models\PayementEmployee;
use App\Models\Employee;
use App\Models\Fonction;
use App\Enums\StatusPayement;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PayementEmployeeController extends Controller
{
    use Sortable;

    public function index(): View
    {
        $sortable = ['employee_id', 'mois', 'annee', 'salaire_base', 'net_a_payer', 'status', 'created_at'];
        $query = PayementEmployee::with('employee');
        $query = $this->applySorting($query, $sortable);
        $payements = $query->paginate(10)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        return view('payements_employees.index', compact('payements', 'sort', 'direction'));
    }

    public function create(): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $statuses = StatusPayement::cases();

        $employeesData = Employee::with(['fonction', 'primes', 'retenus'])->get()->map(fn($e) => [
            'id' => $e->id,
            'salaire' => (float)($e->fonction?->salaire ?? 0),
            'primes' => $e->primes->map(fn($p) => [
                'mois' => (int)$p->mois, 'annee' => (int)$p->annee,
                'montant' => (float)$p->montant, 'motif' => $p->motif
            ]),
            'retenus' => $e->retenus->map(fn($r) => [
                'mois' => (int)$r->date_retenu->format('n'), 'annee' => (int)$r->date_retenu->format('Y'),
                'montant' => (float)$r->montant, 'motif' => $r->motif,
                'date' => $r->date_retenu->format('d/m/Y')
            ]),
        ]);

        return view('payements_employees.create', compact('employees', 'statuses', 'employeesData'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'mois' => ['required', 'string', 'max:20'],
            'annee' => ['required', 'string', 'max:10'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'total_primes' => ['nullable', 'numeric', 'min:0'],
            'total_retenus' => ['nullable', 'numeric', 'min:0'],
            'net_a_payer' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'date_paiement' => ['nullable', 'date'],
        ]);

        PayementEmployee::create($validated);

        return redirect()->route('payements-employees.index')
            ->with('success', 'Paiement créé avec succès.');
    }

    public function show(PayementEmployee $payementEmployee): View
    {
        $payementEmployee->load('employee');

        return view('payements_employees.show', compact('payementEmployee'));
    }

    public function edit(PayementEmployee $payementEmployee): View
    {
        $employees = Employee::pluck('nom_complet', 'id');
        $statuses = StatusPayement::cases();

        $employeesData = Employee::with(['fonction', 'primes', 'retenus'])->get()->map(fn($e) => [
            'id' => $e->id,
            'salaire' => (float)($e->fonction?->salaire ?? 0),
            'primes' => $e->primes->map(fn($p) => [
                'mois' => (int)$p->mois, 'annee' => (int)$p->annee,
                'montant' => (float)$p->montant, 'motif' => $p->motif
            ]),
            'retenus' => $e->retenus->map(fn($r) => [
                'mois' => (int)$r->date_retenu->format('n'), 'annee' => (int)$r->date_retenu->format('Y'),
                'montant' => (float)$r->montant, 'motif' => $r->motif,
                'date' => $r->date_retenu->format('d/m/Y')
            ]),
        ]);

        return view('payements_employees.edit', compact(
            'payementEmployee', 'employees', 'statuses', 'employeesData'
        ));
    }

    public function update(Request $request, PayementEmployee $payementEmployee): RedirectResponse
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'mois' => ['required', 'string', 'max:20'],
            'annee' => ['required', 'string', 'max:10'],
            'salaire_base' => ['required', 'numeric', 'min:0'],
            'total_primes' => ['nullable', 'numeric', 'min:0'],
            'total_retenus' => ['nullable', 'numeric', 'min:0'],
            'net_a_payer' => ['required', 'numeric', 'min:0'],
            'status' => ['required', 'string'],
            'date_paiement' => ['nullable', 'date'],
        ]);

        $payementEmployee->update($validated);

        return redirect()->route('payements-employees.index')
            ->with('success', 'Paiement mis à jour avec succès.');
    }

    public function destroy(PayementEmployee $payementEmployee): RedirectResponse
    {
        $payementEmployee->delete();

        return redirect()->route('payements-employees.index')
            ->with('success', 'Paiement supprimé avec succès.');
    }
}
