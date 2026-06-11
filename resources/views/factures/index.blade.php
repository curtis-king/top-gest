@extends('layouts.app')

@section('title', 'Factures - MyGest')

@section('page-title', 'Factures')

@section('content')
    <div style="margin-bottom:20px;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des factures</h2>
            <a href="{{ route('factures.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle facture</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <form method="GET" action="{{ route('factures.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
            <div style="flex:1;min-width:200px;position:relative;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par numéro ou raison sociale..." style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            </div>
            <select name="type_facture" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Tous les types</option>
                @foreach($types as $t)
                    <option value="{{ $t->value }}" {{ request('type_facture') == $t->value ? 'selected' : '' }} style="color:#000;">{{ $t->value }}</option>
                @endforeach
            </select>
            <select name="statut_facture" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Tous les statuts</option>
                @foreach($statuts as $s)
                    <option value="{{ $s->value }}" {{ request('statut_facture') == $s->value ? 'selected' : '' }} style="color:#000;">{{ $s->value }}</option>
                @endforeach
            </select>
            <select name="agence_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="" style="color:#000;">Toutes les agences</option>
                @foreach($agences as $id => $name)
                    <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                @endforeach
            </select>
            <select name="sort" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="created_at" {{ request('sort', 'created_at') == 'created_at' ? 'selected' : '' }} style="color:#000;">Plus récentes</option>
                <option value="numero_facture" {{ request('sort') == 'numero_facture' ? 'selected' : '' }} style="color:#000;">Numéro</option>
                <option value="date_facture" {{ request('sort') == 'date_facture' ? 'selected' : '' }} style="color:#000;">Date</option>
                <option value="type_facture" {{ request('sort') == 'type_facture' ? 'selected' : '' }} style="color:#000;">Type</option>
                <option value="statut_facture" {{ request('sort') == 'statut_facture' ? 'selected' : '' }} style="color:#000;">Statut</option>
            </select>
            <select name="direction" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <option value="desc" {{ request('direction', 'desc') == 'desc' ? 'selected' : '' }} style="color:#000;">Descendant</option>
                <option value="asc" {{ request('direction') == 'asc' ? 'selected' : '' }} style="color:#000;">Ascendant</option>
            </select>
            <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
            @if(request()->anyFilled(['search', 'type_facture', 'statut_facture', 'agence_id', 'sort', 'direction']))
                <a href="{{ route('factures.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
            @endif
        </form>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(340px,1fr));gap:16px;">
            @forelse($factures as $facture)
                @php
                    $tv = $facture->type_facture?->value ?? 'vente';
                    $sv = $facture->statut_facture?->value ?? 'brouillon';
                    $tc = ['vente'=>'#3b82f6','achat'=>'#f59e0b','avoir'=>'#f87171','proforma'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
                    $sc = ['brouillon'=>'#94a3b8','impayee'=>'#f87171','partielle'=>'#f59e0b','payee'=>'#4ade80','annulee'=>'#6b7280'][$sv]??'rgba(255,255,255,.45)';
                @endphp
                <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px;transition:all .2s;" onmouseover="this.style.borderColor='rgba(59,130,246,.3)';this.style.background='rgba(255,255,255,.05)'" onmouseout="this.style.borderColor='rgba(255,255,255,.06)';this.style.background='rgba(255,255,255,.03)'">
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;">
                        <div>
                            <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:2px;">{{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '—' }}</div>
                            <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ $facture->numero_facture }}</div>
                        </div>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;justify-content:flex-end;">
                            <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span>
                            <span style="display:inline-block;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:500;background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
                        </div>
                    </div>

                    <div style="padding:10px 0;border-top:1px solid rgba(255,255,255,.04);border-bottom:1px solid rgba(255,255,255,.04);">
                        <div style="font-size:13px;color:rgba(255,255,255,.65);">{{ $facture->raison_social ?? $facture->contact?->raison_social ?? '—' }}</div>
                        @if($facture->agence)
                            <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $facture->agence->name_agence }}</div>
                        @endif
                        <div style="margin-top:6px;font-size:16px;font-weight:700;color:#4ade80;">{{ number_format($facture->montant_total, 0, ',', ' ') }} FCFA</div>
                    </div>

                    <div style="padding-top:10px;display:flex;gap:8px;justify-content:flex-end;">
                        <a href="{{ route('factures.manage', $facture) }}" style="font-size:12px;font-weight:600;color:#3b82f6;text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.background='rgba(59,130,246,.1)'" onmouseout="this.style.background='transparent'">Gérer</a>
                        <a href="{{ route('factures.edit', $facture) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Modifier</a>
                        <form method="POST" action="{{ route('factures.destroy', $facture) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size:12px;font-weight:500;color:#f87171;text-decoration:none;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;font-family:inherit;transition:all .15s;" onmouseover="this.style.background='rgba(248,113,113,.08)'" onmouseout="this.style.background='transparent'">Supprimer</button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
                    <div style="font-size:14px;color:rgba(255,255,255,.3);">Aucune facture trouvée.</div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:24px;">
            {{ $factures->links() }}
        </div>
    </div>
@endsection
