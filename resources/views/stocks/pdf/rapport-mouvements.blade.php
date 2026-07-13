<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Rapport de mouvements — {{ $dateGeneration->format('d/m/Y') }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:10px; color:#111; }
    @page { margin:12mm 15mm; size:A4 landscape; }

    .doc-header { display:table; width:100%; margin-bottom:14px; border-bottom:1.5px solid #1e3a5f; padding-bottom:10px; }
    .doc-header-left { display:table-cell; vertical-align:bottom; }
    .doc-header-right { display:table-cell; vertical-align:bottom; text-align:right; font-size:9px; color:#555; }
    .doc-title { font-size:17px; font-weight:bold; color:#1e3a5f; }
    .doc-sub { font-size:9px; color:#666; margin-top:2px; }

    .filtres { font-size:9px; color:#666; margin-bottom:12px; }
    .filtres span { margin-right:14px; }

    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1e3a5f; color:#fff; }
    thead th { padding:6px 7px; font-size:9px; font-weight:bold; text-align:left; border:0.5px solid #17304f; }
    thead th.r { text-align:right; }
    tbody td { padding:5px 7px; border:0.5px solid #e5e7eb; font-size:9px; vertical-align:middle; }
    tbody tr:nth-child(even) td { background:#f9fafb; }

    .badge { display:inline-block; padding:1px 6px; border-radius:10px; font-size:8px; font-weight:bold; }
    .b-entree     { background:#dcfce7; color:#166534; }
    .b-sortie     { background:#fee2e2; color:#991b1b; }
    .b-transfert  { background:#dbeafe; color:#1e40af; }
    .b-ajustement { background:#fef3c7; color:#92400e; }

    .totaux { display:table; width:100%; margin-top:14px; }
    .totaux-cell { display:table-cell; padding:8px 14px; border:0.5px solid #ddd; text-align:center; }
    .totaux-cell .val { font-size:16px; font-weight:bold; }
    .totaux-cell .lab { font-size:9px; color:#666; }
    .footer { margin-top:10px; font-size:8px; color:#aaa; text-align:right; }
</style>
</head>
<body>
<div class="doc-header">
    <div class="doc-header-left">
        <div class="doc-title">RAPPORT DE MOUVEMENTS DE STOCK</div>
        <div class="doc-sub">{{ $query->count() }} mouvement(s)</div>
    </div>
    <div class="doc-header-right">
        Généré le {{ $dateGeneration->format('d/m/Y à H:i') }}
    </div>
</div>

@if($filtres)
<div class="filtres">
    Filtres appliqués :
    @foreach($filtres as $k => $v)
        <span><strong>{{ $k }}</strong> : {{ $v }}</span>
    @endforeach
</div>
@endif

<table>
    <thead>
        <tr>
            <th>Date</th>
            <th>Type</th>
            <th>Produit</th>
            <th>Code</th>
            <th>Dépôt source</th>
            <th>Dépôt dest.</th>
            <th class="r">Quantité</th>
            <th>Contact</th>
            <th>Motif</th>
            <th>Opérateur</th>
        </tr>
    </thead>
    <tbody>
        @forelse($query as $mv)
        @php
            $badgeClass = 'b-'.($mv->type_mouvement?->value ?? 'ajustement');
        @endphp
        <tr>
            <td>{{ $mv->date_mouvement?->format('d/m/Y') }}</td>
            <td><span class="badge {{ $badgeClass }}">{{ strtoupper($mv->type_mouvement?->value) }}</span></td>
            <td>{{ $mv->produit->nom }}</td>
            <td style="color:#888;">{{ $mv->produit->code }}</td>
            <td>{{ $mv->depot->nom }}</td>
            <td>{{ $mv->depotDestination?->nom ?? '—' }}</td>
            <td style="text-align:right;font-weight:bold;">{{ $mv->quantite }}</td>
            <td>{{ $mv->contact?->raison_social ?? $mv->contact?->nom_complet ?? '—' }}</td>
            <td style="color:#666;">{{ Str::limit($mv->motif ?? '—', 30) }}</td>
            <td>{{ $mv->user?->name ?? '—' }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="10" style="text-align:center;padding:16px;color:#888;">Aucun mouvement trouvé.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="totaux">
    <div class="totaux-cell" style="background:#dcfce7;">
        <div class="val" style="color:#166534;">+ {{ $totaux['entree'] }}</div>
        <div class="lab">Total entrées</div>
    </div>
    <div class="totaux-cell" style="background:#fee2e2;">
        <div class="val" style="color:#991b1b;">- {{ $totaux['sortie'] }}</div>
        <div class="lab">Total sorties</div>
    </div>
    <div class="totaux-cell" style="background:#dbeafe;">
        <div class="val" style="color:#1e40af;">{{ $totaux['transfert'] }}</div>
        <div class="lab">Total transferts</div>
    </div>
</div>

<div class="footer">Document généré le {{ $dateGeneration->format('d/m/Y à H:i') }}</div>
</body>
</html>
