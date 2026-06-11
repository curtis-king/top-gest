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

        return view('contacts.index', compact('contacts', 'sort', 'direction'));
    }

    public function create(): View
    {
        $types = TypeContact::cases();
        $agences = Agence::pluck('name_agence', 'id');

        return view('contacts.create', compact('types', 'agences'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'raison_social' => ['nullable', 'string', 'max:255'],
            'nom_complet' => ['nullable', 'string', 'max:255'],
            'adresse_email' => ['required', 'email', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'type_contact' => ['required', 'string'],
            'adresse' => ['nullable', 'string'],
            'secteur_activites' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

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
        $validated = $request->validate([
            'raison_social' => ['nullable', 'string', 'max:255'],
            'nom_complet' => ['nullable', 'string', 'max:255'],
            'adresse_email' => ['required', 'email', 'max:255'],
            'telephone' => ['required', 'string', 'max:50'],
            'type_contact' => ['required', 'string'],
            'adresse' => ['nullable', 'string'],
            'secteur_activites' => ['nullable', 'string', 'max:255'],
            'agence_id' => ['nullable', 'exists:agences,id'],
        ]);

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
}
