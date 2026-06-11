@extends('layouts.app')

@section('title', 'Livret bancaire - MyGest')

@section('page-title', 'Livret bancaire')

@section('content')
    <div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des écritures</h2>
            <a href="{{ route('livrets-bancaires.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle écriture</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('livrets-bancaires.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;position:relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par motif ou raison sociale..." style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            </div>
            <select name="type_action" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Tous les types</option>
                @foreach($types as $t)
                    <option value="{{ $t->value }}" {{ request('type_action') == $t->value ? 'selected' : '' }} style="color:#000;">{{ $t->value }}</option>
                @endforeach
            </select>
            <select name="banque_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Toutes les banques</option>
                @foreach($banques as $id => $name)
                    <option value="{{ $id }}" {{ request('banque_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                @endforeach
            </select>
            <select name="agence_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Tous les sites</option>
                @foreach($agences as $id => $name)
                    <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                @endforeach
            </select>
            <select name="sort" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }} style="color:#000;">Plus récents</option>
                <option value="date_action" {{ request('sort') == 'date_action' ? 'selected' : '' }} style="color:#000;">Date action</option>
                <option value="type_action" {{ request('sort') == 'type_action' ? 'selected' : '' }} style="color:#000;">Type</option>
                <option value="montant" {{ request('sort') == 'montant' ? 'selected' : '' }} style="color:#000;">Montant</option>
                <option value="banque_id" {{ request('sort') == 'banque_id' ? 'selected' : '' }} style="color:#000;">Banque</option>
            </select>
            <select name="direction" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }} style="color:#000;">Descendant</option>
                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }} style="color:#000;">Ascendant</option>
            </select>
            <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
            @if(request()->anyFilled(['search', 'type_action', 'banque_id', 'agence_id', 'sort', 'direction']))
                <a href="{{ route('livrets-bancaires.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
            @endif
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
            @forelse($livrets as $livret)
                @php
                    $tv = $livret->type_action?->value ?? 'autre';
                    $tc = ['depot'=>'#4ade80','retrait'=>'#f87171','virement'=>'#3b82f6','frais'=>'#f59e0b','interet'=>'#a78bfa','autre'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
                @endphp
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px;transition:all .2s;" onmouseover="this.style.borderColor='rgba(59,130,246,.3)';this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)';this.style.background='rgba(255,255,255,.03)'">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                        <div>
                            <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:2px;">{{ $livret->date_action ? \Carbon\Carbon::parse($livret->date_action)->format('d/m/Y') : '—' }}</div>
                            <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ $livret->motif }}</div>
                        </div>
                        <div style="text-align:right;">
                            <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span>
                        </div>
                    </div>

                    <div style="padding:10px 0;border-top:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);display:flex;justify-content:space-between;align-items:center;">
                        <div>
                            <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $livret->banque?->nom ?? '—' }}</div>
                            <div style="font-size:12px;color:rgba(255,255,255,.45);">{{ $livret->raison_social ?? $livret->contact?->raison_social ?? '—' }}</div>
                            @if($livret->agence)
                                <div style="font-size:11px;color:rgba(255,255,255,.3);">{{ $livret->agence->name_agence }}</div>
                            @endif
                        </div>
                        <div style="font-size:18px;font-weight:700;color:{{ in_array($tv, ['depot','interet']) ? '#4ade80' : '#f87171' }};">{{ number_format($livret->montant, 0, ',', ' ') }} FCFA</div>
                    </div>

                    <div style="padding-top:10px;display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('livrets-bancaires.edit', $livret) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                        <form method="POST" action="{{ route('livrets-bancaires.destroy', $livret) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;text-decoration:none;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
                    <div style="font-size:14px;color:rgba(255,255,255,.3);">Aucune écriture trouvée.</div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:24px;">
            {{ $livrets->links() }}
        </div>
    </div>
@endsection
