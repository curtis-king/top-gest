@extends('layouts.app')

@section('title', 'Gestion - ' . $project->nom_project . ' - MyGest')

@section('page-title', 'Gestion : ' . $project->nom_project)

@section('content')
<style>
    .mg-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .mg-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .mg-card-full { grid-column:1 / -1; }
    .mg-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .mg-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .mg-section-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.06); }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    .mg-input { width:100%; padding:8px 12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:13px; font-family:inherit; color:#fff; outline:none; box-sizing:border-box; transition:all .2s; }
    .mg-input:focus { border-color:#3b82f6; }
    .mg-select { width:100%; padding:8px 12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:13px; font-family:inherit; color:#fff; outline:none; cursor:pointer; box-sizing:border-box; }
    .mg-select:focus { border-color:#3b82f6; }
    .mg-select option { color:#000; }
    .mg-btn { padding:7px 16px; border:none; border-radius:8px; font-size:12px; font-weight:500; font-family:inherit; cursor:pointer; transition:all .2s; }
    @media (max-width:768px) { .mg-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('projects.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux projets</a>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ session('success') }}
    </div>
@endif

<div class="mg-grid">

    {{-- PROJECT INFO + STATUS --}}
    <div class="mg-card">
        <div class="mg-section-title">Projet</div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Nom</div>
            <div class="mg-value">{{ $project->nom_project }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Description</div>
            <div class="mg-value" style="white-space:pre-wrap;font-size:13px;">{{ $project->description ?? '—' }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Date d'échéance</div>
            <div class="mg-value">{{ $project->date_echeance ? \Carbon\Carbon::parse($project->date_echeance)->format('d/m/Y') : '—' }}</div>
        </div>

        <form method="POST" action="{{ route('projects.status', $project) }}" style="display:flex;align-items:flex-end;gap:10px;">
            @csrf
            <div style="flex:1;">
                <div class="mg-label" style="margin-bottom:4px;">Statut</div>
                <select name="status_project" class="mg-select">
                    @foreach($statuses as $st)
                        <option value="{{ $st->value }}" {{ $project->status_project?->value == $st->value ? 'selected' : '' }}>{{ $st->value }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;white-space:nowrap;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Mettre à jour</button>
        </form>
    </div>

    {{-- PROGRESS + COST --}}
    <div class="mg-card">
        <div class="mg-section-title">Progression</div>

        @php $progressColor = $progress >= 100 ? '#4ade80' : ($progress >= 50 ? '#facc15' : '#60a5fa'); @endphp
        <div style="margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">{{ $completed }}/{{ $total }} tâches terminées</span>
                <span style="font-size:20px;font-weight:700;color:{{ $progressColor }};">{{ $progress }}%</span>
            </div>
            <div style="height:10px;background:rgba(255,255,255,.06);border-radius:10px;overflow:hidden;">
                <div style="height:100%;width:{{ $progress }}%;border-radius:10px;background:linear-gradient(90deg,#3b82f6,{{ $progressColor }});transition:width .5s ease;"></div>
            </div>
        </div>

        @if($totalCout > 0)
        <div style="padding-top:12px;border-top:1px solid rgba(255,255,255,.04);display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);">Coût total des tâches</span>
            <span style="font-size:18px;font-weight:700;color:#4ade80;">{{ number_format($totalCout, 0, ',', ' ') }} FCFA</span>
        </div>
        @endif

        @php
            $byStatus = $project->taches->groupBy(fn($t) => $t->status?->value ?? 'inconnu');
        @endphp
        <div style="margin-top:14px;padding-top:12px;border-top:1px solid rgba(255,255,255,.04);">
            <div style="display:flex;gap:12px;flex-wrap:wrap;">
                @foreach(['a_faire' => '#94a3b8', 'en_cours' => '#3b82f6', 'terminee' => '#4ade80', 'annule' => '#f87171'] as $st => $col)
                    <div style="display:flex;align-items:center;gap:4px;font-size:11px;">
                        <span style="width:8px;height:8px;border-radius:8px;background:{{ $col }};"></span>
                        <span style="color:rgba(255,255,255,.45);">{{ $st }}</span>
                        <span style="color:rgba(255,255,255,.65);font-weight:600;">{{ $byStatus->get($st, collect())->count() }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- TASKS --}}
    <div class="mg-card mg-card-full">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">
            <span class="mg-section-title" style="margin:0;padding:0;border:none;">Tâches ({{ $total }})</span>
            <button onclick="document.getElementById('modal-task').style.display='flex'" class="mg-btn" style="background:#2563eb;color:#fff;">+ Ajouter une tâche</button>
        </div>
        @error('nom_tache') <div style="color:#f87171;font-size:12px;margin-bottom:8px;">{{ $message }}</div> @enderror

        {{-- Modal --}}
        <div id="modal-task" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
            <div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px;width:480px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.5);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <span style="font-size:16px;font-weight:600;color:#f1f5f9;">Nouvelle tâche</span>
                    <button onclick="document.getElementById('modal-task').style.display='none'" style="background:none;border:none;color:rgba(255,255,255,.35);font-size:20px;cursor:pointer;padding:0;line-height:1;">&times;</button>
                </div>
                <form method="POST" action="{{ route('projects.taches.store', $project) }}">
                    @csrf
                    <div style="margin-bottom:16px;">
                        <div class="mg-label" style="margin-bottom:4px;">Nom de la tâche</div>
                        <input type="text" name="nom_tache" placeholder="Ex: Conception" class="mg-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;">
                        <div>
                            <div class="mg-label" style="margin-bottom:4px;">Agence</div>
                            <select name="agence_id" class="mg-select">
                                <option value="">Sélectionner</option>
                                @foreach($agences as $id => $name)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="mg-label" style="margin-bottom:4px;">Coût (FCFA)</div>
                            <input type="number" name="cout_tache" placeholder="0" min="0" class="mg-input">
                        </div>
                    </div>
                    <div style="margin-bottom:20px;">
                        <div class="mg-label" style="margin-bottom:4px;">Statut</div>
                        <select name="status" class="mg-select">
                            @foreach($tacheStatuses as $st)
                                <option value="{{ $st->value }}">{{ $st->value }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button type="button" onclick="document.getElementById('modal-task').style.display='none'" class="mg-btn" style="background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);">Annuler</button>
                        <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Task list --}}
        @forelse($project->taches as $tache)
            @php
                $tsv = $tache->status?->value ?? 'inconnu';
                $tsc = ['a_faire'=>'#94a3b8','en_cours'=>'#3b82f6','terminee'=>'#4ade80','annule'=>'#f87171'][$tsv]??'rgba(255,255,255,.45)';
            @endphp
            <div style="border:1px solid rgba(255,255,255,.05);border-radius:10px;margin-bottom:14px;overflow:hidden;">
                {{-- Task header --}}
                <div style="display:flex;align-items:center;justify-content:space-between;padding:12px 16px;background:rgba(255,255,255,.02);">
                    <div style="display:flex;align-items:center;gap:12px;flex:1;">
                        <div style="width:8px;height:8px;border-radius:8px;background:{{ $tsc }};flex-shrink:0;"></div>
                        <div>
                            <span style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ $tache->nom_tache }}</span>
                            <span style="font-size:11px;color:rgba(255,255,255,.35);margin-left:8px;">{{ $tache->agence?->name_agence ?? '—' }}</span>
                        </div>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <form method="POST" action="{{ route('taches-projects.status-inline', $tache) }}" style="display:flex;gap:6px;align-items:center;">
                            @csrf
                            @method('PUT')
                            <select name="status" class="mg-select" style="padding:5px 8px;font-size:11px;width:auto;">
                                @foreach($tacheStatuses as $st)
                                    <option value="{{ $st->value }}" {{ $tache->status?->value == $st->value ? 'selected' : '' }}>{{ $st->value }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="mg-btn" style="background:rgba(59,130,246,.1);color:#60a5fa;padding:5px 10px;font-size:11px;">OK</button>
                        </form>
                        @if($tache->cout_tache)
                            <span style="font-size:12px;color:#4ade80;font-weight:600;">{{ number_format($tache->cout_tache, 0, ',', ' ') }} FCFA</span>
                        @endif
                        <form method="POST" action="{{ route('taches-projects.destroy', $tache) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette tâche ?');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="from_manage" value="1">
                            <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:4px;font-size:13px;" title="Supprimer">&times;</button>
                        </form>
                    </div>
                </div>

                {{-- Affectations for this task --}}
                <div style="padding:8px 16px 8px 36px;background:rgba(255,255,255,.01);border-top:1px solid rgba(255,255,255,.04);">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <span style="font-size:11px;color:rgba(255,255,255,.35);">Affectations:</span>
                        @forelse($tache->affectations as $aff)
                            <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;background:rgba(255,255,255,.04);border-radius:6px;font-size:11px;color:rgba(255,255,255,.6);">
                                {{ $aff->employee?->nom_complet ?? $aff->nom_complet ?? '—' }}
                                <form method="POST" action="{{ route('affectation-taches.destroy-inline', $aff) }}" style="display:inline;" onsubmit="return confirm('Retirer cette affectation ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:0;font-size:12px;line-height:1;">&times;</button>
                                </form>
                            </span>
                        @empty
                            <span style="font-size:11px;color:rgba(255,255,255,.25);">Aucune</span>
                        @endforelse
                    </div>

                    {{-- Add affectation form --}}
                    <form method="POST" action="{{ route('taches-projects.affectations.store', $tache) }}" style="display:flex;gap:8px;align-items:center;margin-top:6px;flex-wrap:wrap;">
                        @csrf
                        <input type="date" name="date_affectation" value="{{ date('Y-m-d') }}" class="mg-input" style="width:auto;padding:5px 8px;font-size:11px;" required>
                        <select name="employee_id" class="mg-select" style="width:auto;padding:5px 8px;font-size:11px;">
                            <option value="">Interne...</option>
                            @foreach($employees as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="nom_complet" placeholder="Ou externe..." class="mg-input" style="width:auto;padding:5px 8px;font-size:11px;min-width:140px;">
                        <button type="submit" class="mg-btn" style="background:rgba(74,222,128,.1);color:#4ade80;padding:5px 10px;font-size:11px;">+ Affecter</button>
                    </form>
                </div>
            </div>
        @empty
            <div style="padding:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucune tâche. Ajoutez-en une ci-dessus.</div>
        @endforelse
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('projects.edit', $project) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier le projet</a>
    <form method="POST" action="{{ route('projects.destroy', $project) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression du projet ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer le projet</button>
    </form>
</div>
@endsection
