<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>État du stock — {{ $dateEtat->format('d/m/Y') }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:11px; color:#111; }
    @page { margin:12mm 15mm; size:A4 landscape; }

    .doc-header { display:table; width:100%; margin-bottom:16px; border-bottom:1.5px solid #1e3a5f; padding-bottom:10px; }
    .doc-header-left { display:table-cell; vertical-align:bottom; }
    .doc-header-right { display:table-cell; vertical-align:bottom; text-align:right; font-size:10px; color:#555; }
    .doc-title { font-size:18px; font-weight:bold; color:#1e3a5f; }
    .doc-sub { font-size:10px; color:#666; margin-top:2px; }

    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1e3a5f; color:#fff; }
    thead th { padding:7px 8px; font-size:10px; font-weight:bold; text-align:left; border:0.5px solid #17304f; }
    thead th.r { text-align:right; }
    tbody td { padding:6px 8px; border:0.5px solid #e5e7eb; font-size:10px; vertical-align:middle; }
    tbody tr:nth-child(even) td { background:#f9fafb; }

    .badge-ok     { background:#dcfce7; color:#166534; padding:1px 6px; border-radius:10px; font-size:9px; font-weight:bold; }
    .badge-alert  { background:#fef3c7; color:#92400e; padding:1px 6px; border-radius:10px; font-size:9px; font-weight:bold; }
    .badge-rupture{ background:#fee2e2; color:#991b1b; padding:1px 6px; border-radius:10px; font-size:9px; font-weight:bold; }

    .total-row td { background:#1e3a5f; color:#fff; font-weight:bold; padding:7px 8px; border:0.5px solid #17304f; }
    .footer { margin-top:14px; font-size:9px; color:#888; text-align:right; }
</style>
</head>
<body>
@php $fmt = fn($n) => number_format((float)$n, 0, ',', '.'); @endphp

<div class="doc-header">
    <div class="doc-header-left">
        <div class="doc-title">ÉTAT DES STOCKS</div>
        <div class="doc-sub">Inventaire au {{ $dateEtat->format('d/m/Y à H:i') }}</div>
    </div>
    <div class="doc-header-right">
        Total valeur : <strong>{{ $fmt($valeurTotale) }} FCFA</strong><br>
        {{ $query->count() }} ligne(s)
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Code</th>
            <th>Désignation</th>
            <th>Catégorie</th>
            <th>Dépôt</th>
            <th class="r">Seuil min.</th>
            <th class="r">Quantité</th>
            <th class="r">Prix achat</th>
            <th class="r">Valeur stock</th>
            <th style="text-align:center;">Statut</th>
        </tr>
    </thead>
    <tbody>
        @foreach($query as $s)
        @php
            $valeur = $s->quantite * $s->produit->prix_achat;
            $rupture = $s->quantite <= 0;
            $alerte  = !$rupture && $s->produit->stock_min > 0 && $s->quantite <= $s->produit->stock_min;
        @endphp
        <tr>
            <td>{{ $s->produit->code }}</td>
            <td><strong>{{ $s->produit->nom }}</strong></td>
            <td>{{ $s->produit->categorie?->nom ?? '—' }}</td>
            <td>{{ $s->depot->nom }}</td>
            <td style="text-align:right;">{{ $s->produit->stock_min ?: '—' }}</td>
            <td style="text-align:right;font-weight:bold;color:{{ $rupture ? '#991b1b' : ($alerte ? '#92400e' : '#166534') }};">
                {{ $s->quantite }}
                @if($s->produit->unite_mesure) <span style="font-size:9px;color:#888;">{{ $s->produit->unite_mesure->value }}</span> @endif
            </td>
            <td style="text-align:right;">{{ $fmt($s->produit->prix_achat) }}</td>
            <td style="text-align:right;font-weight:bold;">{{ $fmt($valeur) }}</td>
            <td style="text-align:center;">
                @if($rupture)
                    <span class="badge-rupture">RUPTURE</span>
                @elseif($alerte)
                    <span class="badge-alert">ALERTE</span>
                @else
                    <span class="badge-ok">OK</span>
                @endif
            </td>
        </tr>
        @endforeach
        <tr class="total-row">
            <td colspan="7" style="text-align:right;">VALEUR TOTALE DU STOCK</td>
            <td style="text-align:right;">{{ $fmt($valeurTotale) }} FCFA</td>
            <td></td>
        </tr>
    </tbody>
</table>

<div class="footer">Document généré le {{ $dateEtat->format('d/m/Y à H:i') }}</div>
</body>
</html>
