@extends('layouts.app')

@section('title', 'Mouvements de stock - MyGest')
@section('page-title', 'Mouvements de stock')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Historique des mouvements</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('stocks.dashboard') }}" class="btn-outline" style="text-decoration:none;">Tableau de bord</a>
            <a href="{{ route('mouvements-stocks.pdf.rapport') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ PDF rapport</a>
            <a href="{{ route('mouvements-stocks.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouveau mouvement</a>
        </div>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="GET" action="{{ route('mouvements-stocks.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <select name="type_mouvement" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les types</option>
            @foreach($types as $t)
                <option value="{{ $t->value }}" {{ request('type_mouvement') == $t->value ? 'selected' : '' }} style="color:#000;">{{ $t->value }}</option>
            @endforeach
        </select>
        <select name="produit_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les produits</option>
            @foreach($produits as $id => $nom)
                <option value="{{ $id }}" {{ request('produit_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
            @endforeach
        </select>
        <select name="depot_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les dépôts</option>
            @foreach($depots as $id => $nom)
                <option value="{{ $id }}" {{ request('depot_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
        @if(request()->anyFilled(['type_mouvement', 'produit_id', 'depot_id']))
            <a href="{{ route('mouvements-stocks.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
        @endif
    </form>

    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Type</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Produit</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Dépôt</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Motif</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Qté</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Date</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Par</th>
                    <th style="padding:12px 16px;"></th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvements as $mv)
                    @php
                        $tc = ['entree'=>'#4ade80','sortie'=>'#f87171','transfert'=>'#60a5fa','ajustement'=>'#f59e0b'][$mv->type_mouvement?->value] ?? '#94a3b8';
                    @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:12px 16px;">
                            <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $tc }}22;color:{{ $tc }};">{{ $mv->type_mouvement?->value }}</span>
                        </td>
                        <td style="padding:12px 16px;">
                            <div style="font-size:13px;font-weight:500;color:#f1f5f9;">{{ $mv->produit->nom }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $mv->produit->code }}</div>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">
                            {{ $mv->depot->nom }}
                            @if($mv->depotDestination)
                                <span style="color:rgba(255,255,255,.3);margin:0 4px;">→</span>{{ $mv->depotDestination->nom }}
                            @endif
                        </td>
                        <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $mv->motif ?? '—' }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:14px;font-weight:600;color:{{ in_array($mv->type_mouvement?->value, ['entree']) ? '#4ade80' : '#f87171' }};">
                            {{ in_array($mv->type_mouvement?->value, ['sortie']) ? '-' : '+' }}{{ $mv->quantite }}
                        </td>
                        <td style="padding:12px 16px;text-align:right;font-size:12px;color:rgba(255,255,255,.4);">{{ $mv->date_mouvement?->format('d/m/Y') }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:12px;color:rgba(255,255,255,.4);">{{ $mv->user?->name ?? '—' }}</td>
                        <td style="padding:12px 16px;text-align:right;">
                            <a href="{{ route('mouvements-stocks.pdf', $mv) }}" target="_blank" style="font-size:11px;color:#60a5fa;text-decoration:none;padding:3px 8px;border:1px solid rgba(96,165,250,.3);border-radius:6px;transition:all .15s;" onmouseover="this.style.background='rgba(96,165,250,.1)'" onmouseout="this.style.background='transparent'">PDF</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucun mouvement trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">{{ $mouvements->links() }}</div>
</div>
@endsection
