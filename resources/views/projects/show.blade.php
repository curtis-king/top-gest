@extends('layouts.app')

@section('title', $project->nom_project . ' - MyGest')

@section('page-title', $project->nom_project)

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

<a href="{{ route('projects.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux projets</a>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Projet</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Nom</div>
            <div class="det-value">{{ $project->nom_project }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Description</div>
            <div class="det-value" style="white-space:pre-wrap;">{{ $project->description ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date d'échéance</div>
            <div class="det-value">{{ $project->date_echeance ? \Carbon\Carbon::parse($project->date_echeance)->format('d/m/Y') : '—' }}</div>
        </div>

        <div>
            <div class="det-label">Statut</div>
            @php
                $sv = $project->status_project?->value ?? 'inconnu';
                $sc = ['actif'=>'#4ade80','en_cours'=>'#3b82f6','termine'=>'#94a3b8','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
            @endphp
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Progression</div>

        @php $progressColor = $progress >= 100 ? '#4ade80' : ($progress >= 50 ? '#facc15' : '#60a5fa'); @endphp
        <div style="margin-bottom:14px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">{{ $completed }}/{{ $total }} tâches terminées</span>
                <span style="font-size:16px;font-weight:700;color:{{ $progressColor }};">{{ $progress }}%</span>
            </div>
            <div style="height:8px;background:rgba(255,255,255,.06);border-radius:8px;overflow:hidden;">
                <div style="height:100%;width:{{ $progress }}%;border-radius:8px;background:linear-gradient(90deg,#3b82f6,{{ $progressColor }});transition:width .5s ease;"></div>
            </div>
        </div>

        @if($totalCout > 0)
        <div style="padding-top:10px;border-top:1px solid rgba(255,255,255,.04);">
            <div class="det-label">Coût total des tâches</div>
            <div class="det-value" style="color:#4ade80;font-size:18px;">{{ number_format($totalCout, 0, ',', ' ') }} FCFA</div>
        </div>
        @endif
    </div>

    <div class="det-card det-card-full">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Tâches ({{ $total }})</div>

        @forelse($project->taches as $tache)
            @php
                $tsv = $tache->status?->value ?? 'inconnu';
                $tsc = ['a_faire'=>'#94a3b8','en_cours'=>'#3b82f6','terminee'=>'#4ade80','annule'=>'#f87171'][$tsv]??'rgba(255,255,255,.45)';
            @endphp
            <div style="padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:13px;color:#f1f5f9;font-weight:500;">{{ $tache->nom_tache }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $tache->agence?->name_agence ?? '—' }}</div>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <span class="badge" style="background:{{ $tsc }}22;color:{{ $tsc }};padding:2px 8px;font-size:10px;">{{ $tsv }}</span>
                    @if($tache->cout_tache)
                        <span style="font-size:12px;color:#4ade80;font-weight:600;">{{ number_format($tache->cout_tache, 0, ',', ' ') }} FCFA</span>
                    @endif
                </div>
            </div>
        @empty
            <div style="font-size:13px;color:rgba(255,255,255,.3);padding:10px 0;">Aucune tâche associée.</div>
        @endforelse
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('projects.edit', $project) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form method="POST" action="{{ route('projects.destroy', $project) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
