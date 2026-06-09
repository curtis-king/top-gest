@extends('layouts.app')

@section('title', 'Projets - MyGest')

@section('page-title', 'Projets')

@section('content')
    <div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des projets</h2>
            <a href="{{ route('projects.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouveau projet</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('projects.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;position:relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom de projet..." style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            </div>
            <select name="sort" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }} style="color:#000;">Plus récents</option>
                <option value="nom_project" {{ request('sort') == 'nom_project' ? 'selected' : '' }} style="color:#000;">Nom</option>
                <option value="date_echeance" {{ request('sort') == 'date_echeance' ? 'selected' : '' }} style="color:#000;">Date échéance</option>
                <option value="status_project" {{ request('sort') == 'status_project' ? 'selected' : '' }} style="color:#000;">Statut</option>
                <option value="id" {{ request('sort') == 'id' ? 'selected' : '' }} style="color:#000;">ID</option>
            </select>
            <select name="direction" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }} style="color:#000;">Descendant</option>
                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }} style="color:#000;">Ascendant</option>
            </select>
            <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
            @if(request()->anyFilled(['search', 'sort', 'direction']))
                <a href="{{ route('projects.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
            @endif
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:16px;">
            @forelse($projects as $project)
                @php
                    $sv = $project->status_project?->value ?? 'inconnu';
                    $sc = ['actif'=>'#4ade80','en_cours'=>'#3b82f6','termine'=>'#94a3b8','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
                @endphp
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px;transition:all .2s;position:relative;" onmouseover="this.style.borderColor='rgba(59,130,246,.3)';this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)';this.style.background='rgba(255,255,255,.03)'">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:42px;height:42px;border-radius:42px;background:linear-gradient(135deg,#f59e0b,#d97706);display:flex;align-items:center;justify-content:center;font-size:15px;font-weight:600;color:#fff;flex-shrink:0;">
                                {{ strtoupper(substr($project->nom_project, 0, 2)) }}
                            </div>
                            <div>
                                <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ $project->nom_project }}</div>
                                <div style="font-size:12px;color:rgba(255,255,255,.35);">#{{ $project->id }}</div>
                            </div>
                        </div>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('projects.show', $project) }}" style="width:32px;height:32px;border-radius:8px;background:rgba(96,165,250,.1);border:1px solid rgba(96,165,250,.15);display:flex;align-items:center;justify-content:center;color:#60a5fa;text-decoration:none;transition:all .15s;" onmouseover="this.style.background='rgba(96,165,250,.2)'" onmouseout="this.style.background='rgba(96,165,250,.1)'" title="Voir">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                        </div>
                    </div>

                    <div style="padding:12px 0;border-top:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                            <div>
                                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Statut</div>
                                <div><span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span></div>
                            </div>
                            <div>
                                <div style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);margin-bottom:2px;">Échéance</div>
                                <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $project->date_echeance ? \Carbon\Carbon::parse($project->date_echeance)->format('d/m/Y') : '—' }}</div>
                            </div>
                        </div>
                        <div style="margin-bottom:6px;">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
                                <span style="font-size:10px;text-transform:uppercase;letter-spacing:.4px;color:rgba(255,255,255,.3);">Progression</span>
                                <span style="font-size:11px;font-weight:600;color:{{ $project->progress >= 100 ? '#4ade80' : ($project->progress >= 50 ? '#facc15' : '#60a5fa') }};">{{ $project->progress }}%</span>
                            </div>
                            <div style="height:6px;background:rgba(255,255,255,.06);border-radius:6px;overflow:hidden;">
                                <div style="height:100%;width:{{ $project->progress }}%;border-radius:6px;background:linear-gradient(90deg,#3b82f6,{{ $project->progress >= 100 ? '#4ade80' : ($project->progress >= 50 ? '#facc15' : '#60a5fa') }});transition:width .5s ease;"></div>
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;font-size:11px;color:rgba(255,255,255,.45);">
                            <span>Coût total: <strong style="color:#4ade80;">{{ $project->total_cout ? number_format($project->total_cout, 0, ',', ' ') . ' FCFA' : '—' }}</strong></span>
                        </div>
                    </div>

                    <div style="padding-top:10px;display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('projects.manage', $project) }}" style="font-size:12px;font-weight:600;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.background='rgba(59,130,246,.1)'" onmouseout="this.style.background='transparent'">Gérer</a>
                        <a href="{{ route('projects.edit', $project) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                        <form method="POST" action="{{ route('projects.destroy', $project) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;text-decoration:none;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
                    <div style="font-size:14px;color:rgba(255,255,255,.3);">Aucun projet trouvé.</div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:24px;">
            {{ $projects->links() }}
        </div>
    </div>
@endsection
