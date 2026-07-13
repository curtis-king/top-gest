@extends('layouts.app')

@section('title', 'Écritures comptables - MyGest')

@section('page-title', 'Écritures comptables')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des écritures</h2>
        <a href="{{ route('ecritures-comptables.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle écriture</a>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:12px;margin-bottom:20px;">
        <div style="background:rgba(59,130,246,.08);border:1px solid rgba(59,130,246,.15);border-radius:12px;padding:16px;text-align:center;">
            <div style="font-size:24px;font-weight:700;color:#60a5fa;">{{ $stats['total'] }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Écritures</div>
        </div>
        <div style="background:rgba(148,163,184,.08);border:1px solid rgba(148,163,184,.15);border-radius:12px;padding:16px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:#94a3b8;">{{ $stats['brouillon'] }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Brouillons</div>
        </div>
        <div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.15);border-radius:12px;padding:16px;text-align:center;">
            <div style="font-size:20px;font-weight:700;color:#4ade80;">{{ $stats['validee'] }}</div>
            <div style="font-size:11px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.4px;margin-top:2px;">Validées</div>
        </div>
    </div>

    <form method="GET" action="{{ route('ecritures-comptables.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;position:relative;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par numéro ou libellé..." style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
        </div>
        <select name="journal_comptable_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les journaux</option>
            @foreach($journaux as $id => $libelle)
                <option value="{{ $id }}" {{ request('journal_comptable_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
            @endforeach
        </select>
        <select name="statut" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les statuts</option>
            @foreach($statuts as $s)
                <option value="{{ $s->value }}" {{ request('statut') == $s->value ? 'selected' : '' }} style="color:#000;">{{ $s->value }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
        @if(request()->anyFilled(['search', 'journal_comptable_id', 'statut']))
            <a href="{{ route('ecritures-comptables.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
        @endif
    </form>

    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Numéro</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Date</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Journal</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Libellé</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Statut</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ecritures as $ecriture)
                    @php
                        $sv = $ecriture->statut?->value;
                        $sc = ['brouillon'=>'#94a3b8','validee'=>'#4ade80'][$sv] ?? '#94a3b8';
                    @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:12px 16px;">
                            <a href="{{ route('ecritures-comptables.show', $ecriture) }}" style="font-size:13px;font-weight:600;color:#60a5fa;text-decoration:none;">{{ $ecriture->numero_ecriture }}</a>
                        </td>
                        <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $ecriture->date_ecriture ? \Carbon\Carbon::parse($ecriture->date_ecriture)->format('d/m/Y') : '—' }}</td>
                        <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $ecriture->journal?->code }}</td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">{{ $ecriture->libelle }}</td>
                        <td style="padding:12px 16px;">
                            <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
                        </td>
                        <td style="padding:12px 16px;text-align:right;">
                            <a href="{{ route('ecritures-comptables.show', $ecriture) }}" style="font-size:12px;font-weight:500;color:rgba(255,255,255,.45);text-decoration:none;padding:4px 10px;border-radius:6px;transition:all .15s;" onmouseover="this.style.color='#60a5fa';this.style.background='rgba(96,165,250,.08)'" onmouseout="this.style.color='rgba(255,255,255,.45)';this.style.background='transparent'">Détails</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucune écriture trouvée.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">{{ $ecritures->links() }}</div>
</div>
@endsection
