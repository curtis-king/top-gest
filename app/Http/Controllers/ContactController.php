<?php

namespace App\Http\Controllers;

use App\Enums\TypeContact;
use App\Models\Agence;
use App\Models\Contact;
use App\Models\Employee;
use App\Http\Controllers\Traits\Sortable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    use Sortable;

    public function index(Request $request): View
    {
        $sortable = ['id', 'raison_social', 'nom_complet', 'adresse_email', 'type_contact', 'created_at'];
        $query = Contact::with('agence');

        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $query->where('agence_id', $userEmp->agence_id);
            }
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('raison_social', 'like', "%{$search}%")
                  ->orWhere('nom_complet', 'like', "%{$search}%")
                  ->orWhere('adresse_email', 'like', "%{$search}%")
                  ->orWhere('telephone', 'like', "%{$search}%");
            });
        }

        $query = $this->applySorting($query, $sortable);
        $contacts = $query->paginate(12)->withQueryString();
        $sort = request('sort', 'created_at');
        $direction = request('direction', 'desc');

        $allQuery = Contact::query();
        if (!auth()->user()->isAdmin()) {
            $userEmp = Employee::where('user_id', auth()->id())->first();
            if ($userEmp && $userEmp->agence_id) {
                $allQuery->where('agence_id', $userEmp->agence_id);
            }
        }
        $all = $allQuery->get();
        $stats = [
            'total' => $all->count(),
            'client' => $all->filter(fn($c) => $c->type_contact?->value === 'client')->count(),
            'fournisseur' => $all->filter(fn($c) => $c->type_contact?->value === 'fournisseur')->count(),
            'autre' => $all->filter(fn($c) => $c->type_contact?->value === 'autre')->count(),
        ];

        return view('contacts.index', compact('contacts', 'sort', 'direction', 'stats'));
    }

    public function create(): View
    {
        $types = TypeContact::cases();
        $agences = Agence::pluck('name_agence', 'id');

        return view('contacts.create', compact('types', 'agences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->valider($request);


        Contact::create($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact créé avec succès.');
    }

    public function show(Contact $contact): View
    {
        $contact->load('agence');

        return view('contacts.show', compact('contact'));
    }

    public function edit(Contact $contact): View
    {
        $types = TypeContact::cases();
        $agences = Agence::pluck('name_agence', 'id');

        return view('contacts.edit', compact('contact', 'types', 'agences'));
    }

    public function update(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $this->valider($request);


        $contact->update($validated);

        return redirect()->route('contacts.index')
            ->with('success', 'Contact mis à jour avec succès.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')
            ->with('success', 'Contact supprimé avec succès.');
    }

    public function search(Request $request): JsonResponse
    {
        $q = trim($request->input('q', ''));

        $contacts = Contact::where('raison_social', 'like', "%{$q}%")
            ->orWhere('nom_complet', 'like', "%{$q}%")
            ->limit(10)
            ->get(['id', 'raison_social', 'nom_complet', 'type_contact']);

        return response()->json($contacts->map(fn($c) => [
            'id'  => $c->id,
            'text' => $c->raison_social ?: $c->nom_complet,
            'sub'  => $c->type_contact?->value,
        ]));
    }
    /**
     * Validation de la fiche, alignée sur les exigences du SFEC pour les clients
     * (docs.sfec.gouv.cg/api/certify) : un client mal renseigné bloquerait la certification.
     */
    private function valider(Request $request): array
    {
        // NIU et RCCM saisis avec espaces ou en minuscules : on normalise avant de contrôler.
        $request->merge([
            'niu' => $request->filled('niu') ? strtoupper(preg_replace('/\s+/', '', $request->input('niu'))) : null,
            'rccm' => $request->filled('rccm') ? strtoupper(trim($request->input('rccm'))) : null,
        ]);

        $typeSfec = $request->input('type_client_sfec');
        $avecNiu = in_array($typeSfec, ['entreprise', 'etat'], true);
        $avecAdresse = in_array($typeSfec, ['etat', 'etranger'], true);

        return $request->validate([
            'raison_social' => [Rule::requiredIf($avecNiu), 'nullable', 'string', 'max:255'],
            'nom_complet' => [Rule::requiredIf($typeSfec === 'etranger' && !$request->filled('raison_social')), 'nullable', 'string', 'max:255'],
            'adresse_email' => ['required', 'email', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'type_contact' => ['required', Rule::enum(TypeContact::class)],
            'adresse' => [Rule::requiredIf($avecAdresse), 'nullable', 'string'],
            'secteur_activites' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
            'type_client_sfec' => ['required_if:type_contact,client', 'nullable', Rule::in(array_keys(config('sfec.recipient_types')))],
            'niu' => [Rule::requiredIf($avecNiu), 'nullable', 'string', 'alpha_num', 'min:16', 'max:17'],
            'rccm' => ['nullable', 'string', 'max:50'],
            'assujetti_tva' => ['nullable', 'boolean'],
        ], [
            'raison_social.required' => 'La raison sociale est obligatoire pour un client entreprise ou État (SFEC).',
            'nom_complet.required' => 'Un nom (ou une raison sociale) est obligatoire pour un client étranger (SFEC).',
            'adresse.required' => 'L\'adresse est obligatoire pour un client État ou étranger (SFEC).',
            'type_client_sfec.required_if' => 'Le type de client SFEC est obligatoire pour un client.',
            'type_client_sfec.in' => 'Type de client SFEC invalide.',
            'niu.required' => 'Le NIU est obligatoire pour un client entreprise ou État (SFEC).',
            'niu.min' => 'Le NIU doit comporter 16 ou 17 caractères (SFEC).',
            'niu.max' => 'Le NIU doit comporter 16 ou 17 caractères (SFEC).',
            'niu.alpha_num' => 'Le NIU ne doit contenir que des lettres et des chiffres.',
        ]);
    }
}
