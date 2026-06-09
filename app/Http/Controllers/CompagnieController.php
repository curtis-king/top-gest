<?php

namespace App\Http\Controllers;

use App\Models\Compagnie;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompagnieController extends Controller
{
    public function index(): RedirectResponse
    {
        $compagnie = Compagnie::first();

        if ($compagnie) {
            return redirect()->route('compagnies.show', $compagnie);
        }

        return redirect()->route('compagnies.create');
    }

    public function create(): View
    {
        return view('compagnies.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'forme_juridique' => ['nullable', 'string', 'max:255'],
            'nui' => ['nullable', 'string', 'max:255'],
            'rccm' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('logos/compagnies', 'public');
        }

        $compagnie = Compagnie::create($validated);

        return redirect()->route('compagnies.show', $compagnie)
            ->with('success', 'Compagnie créée avec succès.');
    }

    public function show(Compagnie $compagnie): View
    {
        $compagnie->load('agences');

        return view('compagnies.show', compact('compagnie'));
    }

    public function edit(Compagnie $compagnie): View
    {
        return view('compagnies.edit', compact('compagnie'));
    }

    public function update(Request $request, Compagnie $compagnie): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'forme_juridique' => ['nullable', 'string', 'max:255'],
            'nui' => ['nullable', 'string', 'max:255'],
            'rccm' => ['nullable', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
        ]);

        if ($request->hasFile('logo')) {
            if ($compagnie->logo) {
                Storage::disk('public')->delete($compagnie->logo);
            }

            $validated['logo'] = $request->file('logo')->store('logos/compagnies', 'public');
        }

        $compagnie->update($validated);

        return redirect()->route('compagnies.show', $compagnie)
            ->with('success', 'Compagnie mise à jour avec succès.');
    }

    public function destroy(Compagnie $compagnie): RedirectResponse
    {
        if ($compagnie->logo) {
            Storage::disk('public')->delete($compagnie->logo);
        }

        $compagnie->delete();

        return redirect()->route('compagnies.create')
            ->with('success', 'Compagnie supprimée avec succès.');
    }
}
