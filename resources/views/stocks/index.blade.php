@extends('layouts.app')

@section('title', 'État des stocks - MyGest')
@section('page-title', 'État des stocks')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Stock par dépôt</h2>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('stocks.dashboard') }}" class="btn-outline" style="text-decoration:none;">Tableau de bord</a>
            <a href="{{ route('stocks.pdf.etat') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ PDF état</a>
            <a href="{{ route('mouvements-stocks.create') }}" class="btn-primary" style="text-decoration:none;">+ Saisir un mouvement</a>
        </div>
    </div>

    <form method="GET" action="{{ route('stocks.index') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <select name="depot_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les dépôts</option>
            @foreach($depots as $depot)
                <option value="{{ $depot->id }}" {{ request('depot_id') == $depot->id ? 'selected' : '' }} style="color:#000;">{{ $depot->nom }}</option>
            @endforeach
        </select>
        <select name="produit_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Tous les produits</option>
            @foreach($produits as $id => $nom)
                <option value="{{ $id }}" {{ request('produit_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
            @endforeach
        </select>
        <label style="display:flex;align-items:center;gap:8px;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;cursor:pointer;">
            <input type="checkbox" name="alerte" value="1" {{ request('alerte') ? 'checked' : '' }} style="accent-color:#f87171;">
            <span style="font-size:13px;color:rgba(255,255,255,.6);">Alertes seulement</span>
        </label>
        <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
        @if(request()->anyFilled(['depot_id', 'produit_id', 'alerte']))
            <a href="{{ route('stocks.index') }}" style="padding:10px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;" onmouseover="this.style.borderColor='rgba(248,113,113,.3)';this.style.color='#f87171'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)';this.style.color='rgba(255,255,255,.5)'">Réinitialiser</a>
        @endif
    </form>

    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Produit</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Dépôt</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Catégorie</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Seuil min.</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Quantité</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($stocks as $stock)
                    @php $enAlerte = $stock->quantite <= $stock->produit->stock_min; @endphp
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:12px 16px;">
                            <div style="font-size:13px;font-weight:500;color:#f1f5f9;">{{ $stock->produit->nom }}</div>
                            <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $stock->produit->code }}</div>
                        </td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">{{ $stock->depot->nom }}</td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.45);">{{ $stock->produit->categorie?->nom ?? '—' }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.45);">{{ $stock->produit->stock_min }}</td>
                        <td style="padding:12px 16px;text-align:right;">
                            <span style="font-size:15px;font-weight:700;color:{{ $enAlerte ? '#f87171' : '#4ade80' }};">{{ $stock->quantite }}</span>
                            <span style="font-size:11px;color:rgba(255,255,255,.35);margin-left:4px;">{{ $stock->produit->unite_mesure?->value }}</span>
                            @if($enAlerte)
                                <div style="font-size:10px;color:#f87171;">⚠ Alerte</div>
                            @endif
                        </td>
                        <td style="padding:12px 16px;text-align:right;">
                            <a href="{{ route('produits.show', $stock->produit) }}" style="font-size:12px;color:#60a5fa;text-decoration:none;padding:4px 8px;border-radius:6px;transition:all .15s;" onmouseover="this.style.background='rgba(96,165,250,.1)'" onmouseout="this.style.background='transparent'">Détails</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucun stock trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:24px;">{{ $stocks->links() }}</div>
</div>
@endsection
