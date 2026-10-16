@extends('layouts.app')

@section('title', 'Dossier - ' . ($dossierEmployee->employee?->nom_complet ?? 'N/D') . ' - MyGest')

@section('page-title', 'Dossier employé')

@section('content')
<style>
    .det-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .det-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .det-card-full { grid-column:1 / -1; }
    .det-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .det-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:640px) { .det-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('dossiers-employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux dossiers</a>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Employé</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Nom complet</div>
            <div class="det-value">{{ $dossierEmployee->employee?->nom_complet ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Téléphone</div>
            <div class="det-value">{{ $dossierEmployee->employee?->telephone ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Email</div>
            <div class="det-value">{{ $dossierEmployee->employee?->email ?? '—' }}</div>
        </div>

        <div>
            <div class="det-label">Agence</div>
            <div class="det-value">{{ $dossierEmployee->employee?->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Contrat</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date d'engagement</div>
            <div class="det-value">{{ $dossierEmployee->date_engagement ? \Carbon\Carbon::parse($dossierEmployee->date_engagement)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date de fin</div>
            <div class="det-value">{{ $dossierEmployee->date_fin ? \Carbon\Carbon::parse($dossierEmployee->date_fin)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Type de contrat</div>
            <div class="det-value">{{ $dossierEmployee->type_contrat?->value ?? '—' }}</div>
        </div>

        <div>
            <div class="det-label">Statut</div>
            @php
                $sv = $dossierEmployee->status?->value ?? 'inconnu';
                $sc = ['actif'=>'#4ade80','inactif'=>'#f87171','suspendu'=>'#facc15','termine'=>'#f87171','annule'=>'#94a3b8'][$sv]??'rgba(255,255,255,.45)';
            @endphp
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('dossiers-employees.edit', $dossierEmployee) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form method="POST" action="{{ route('dossiers-employees.destroy', $dossierEmployee) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection