@extends('layouts.app')

@section('title', 'Congé - ' . $conge->employee->nom_complet . ' - MyGest')

@section('page-title', 'Détails du congé')

@section('content')
<style>
    .det-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .det-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .det-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .det-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:640px) { .det-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('conges.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux congés</a>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Employé</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Nom complet</div>
            <div class="det-value">{{ $conge->employee->nom_complet }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Téléphone</div>
            <div class="det-value">{{ $conge->employee->telephone ?? '—' }}</div>
        </div>

        <div>
            <div class="det-label">Email</div>
            <div class="det-value">{{ $conge->employee->email ?? '—' }}</div>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Congé</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Type</div>
            <div class="det-value">{{ $conge->type_conge?->value ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date de début</div>
            <div class="det-value">{{ $conge->date_debut->format('d/m/Y') }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date de fin</div>
            <div class="det-value">{{ $conge->date_fin->format('d/m/Y') }}</div>
        </div>

        <div>
            <div class="det-label">Durée</div>
            <div class="det-value">{{ $conge->date_debut->diffInDays($conge->date_fin) + 1 }} jour(s)</div>
        </div>
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('conges.edit', $conge) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form method="POST" action="{{ route('conges.destroy', $conge) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection