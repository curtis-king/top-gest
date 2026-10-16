@extends('layouts.app')

@section('title', $tacheProject->nom_tache . ' - MyGest')

@section('page-title', $tacheProject->nom_tache)

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

<a href="{{ route('taches-projects.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux tâches</a>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Tâche</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Nom</div>
            <div class="det-value">{{ $tacheProject->nom_tache }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Description</div>
            <div class="det-value" style="white-space:pre-wrap;">{{ $tacheProject->description_tache ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Projet</div>
            <div class="det-value">{{ $tacheProject->project?->nom_project ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Agence</div>
            <div class="det-value">{{ $tacheProject->agence?->name_agence ?? '—' }}</div>
        </div>

        <div>
            <div class="det-label">Statut</div>
            @php
                $sv = $tacheProject->status?->value ?? 'inconnu';
                $sc = ['a_faire'=>'#94a3b8','en_cours'=>'#3b82f6','terminee'=>'#4ade80','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
            @endphp
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>

        @if($tacheProject->cout_tache)
        <div style="margin-top:14px;">
            <div class="det-label">Coût</div>
            <div class="det-value" style="color:#4ade80;">{{ number_format($tacheProject->cout_tache, 0, ',', ' ') }} FCFA</div>
        </div>
        @endif
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Affectations ({{ $tacheProject->affectations->count() }})</div>

        @forelse($tacheProject->affectations as $aff)
            <div style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);">
                <div style="font-size:13px;color:#f1f5f9;font-weight:500;">{{ $aff->employee?->nom_complet ?? $aff->nom_complet ?? '—' }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.35);">
                    {{ $aff->date_affectation ? \Carbon\Carbon::parse($aff->date_affectation)->format('d/m/Y') : '—' }}
                    @if($aff->employee_id) &middot; Interne @elseif($aff->nom_complet) &middot; Externe @endif
                </div>
            </div>
        @empty
            <div style="font-size:13px;color:rgba(255,255,255,.3);padding:10px 0;">Aucune affectation.</div>
        @endforelse
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('taches-projects.edit', $tacheProject) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form method="POST" action="{{ route('taches-projects.destroy', $tacheProject) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
