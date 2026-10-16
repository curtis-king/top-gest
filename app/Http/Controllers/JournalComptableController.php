<?php

namespace App\Http\Controllers;

use App\Models\JournalComptable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JournalComptableController extends Controller
{
    public function index(): View
    {
        $journaux = JournalComptable::withCount('ecritures')->orderBy('code')->get();

        return view('journaux-comptables.index', compact('journaux'));
    }

    public function create(): View
    {
        return view('journaux-comptables.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:journaux_comptables,code'],
            'libelle' => ['required', 'string', 'max:255'],
        ]);

        JournalComptable::create($validated);

        return redirect()->route('journaux-comptables.index')
            ->with('success', 'Journal créé avec succès.');
    }

    public function edit(JournalComptable $journalComptable): View
    {
        return view('journaux-comptables.edit', compact('journalComptable'));
    }

    public function update(Request $request, JournalComptable $journalComptable): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:10', 'unique:journaux_comptables,code,' . $journalComptable->id],
            'libelle' => ['required', 'string', 'max:255'],
        ]);

        $journalComptable->update($validated);

        return redirect()->route('journaux-comptables.index')
            ->with('success', 'Journal mis à jour avec succès.');
    }

    public function destroy(JournalComptable $journalComptable): RedirectResponse
    {
        if ($journalComptable->ecritures()->exists()) {
            return back()->with('error', 'Ce journal contient des écritures et ne peut pas être supprimé.');
        }

        $journalComptable->delete();

        return redirect()->route('journaux-comptables.index')
            ->with('success', 'Journal supprimé avec succès.');
    }
}
