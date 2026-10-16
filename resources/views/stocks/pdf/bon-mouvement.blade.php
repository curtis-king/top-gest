<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Bon de mouvement #{{ $mouvement->id }}</title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'DejaVu Sans',sans-serif; font-size:12px; color:#111; }
    @page { margin:15mm 18mm; size:A4 portrait; }

    .header { display:table; width:100%; margin-bottom:20px; }
    .header-left { display:table-cell; vertical-align:top; width:60%; }
    .header-right { display:table-cell; vertical-align:top; width:40%; text-align:right; }

    .doc-title { font-size:20px; font-weight:bold; letter-spacing:2px; margin-bottom:4px; }
    .doc-sub { font-size:11px; color:#555; }

    .type-badge { display:inline-block; padding:4px 14px; border-radius:4px; font-size:13px; font-weight:bold; }

    .meta-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    .meta-table td { padding:7px 10px; border:0.5px solid #ddd; font-size:12px; }
    .meta-table td.label { background:#f5f5f5; font-weight:bold; width:30%; color:#444; }

    .produit-table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    .produit-table th { background:#1e3a5f; color:#fff; padding:8px 10px; font-size:11px; text-align:left; border:0.5px solid #1e3a5f; }
    .produit-table td { padding:10px; border:0.5px solid #ddd; font-size:12px; }
    .produit-table tr:nth-child(even) td { background:#f9f9f9; }

    .qte-cell { font-size:22px; font-weight:bold; text-align:center; }

    .footer-grid { display:table; width:100%; margin-top:30px; }
    .footer-cell { display:table-cell; width:50%; text-align:center; vertical-align:top; font-size:11px; }
    .sign-line { display:inline-block; width:120px; border-top:0.5px solid #333; margin-top:32px; }
    .sign-label { font-weight:bold; margin-bottom:4px; }

    .note-box { background:#fffbeb; border:0.5px solid #f59e0b; border-radius:4px; padding:10px 14px; font-size:11px; color:#444; margin-bottom:20px; }
</style>
</head>
<body>
@php
    $typeColors = [
        'entree'     => ['bg'=>'#dcfce7','text'=>'#166534','label'=>'ENTRÉE'],
        'sortie'     => ['bg'=>'#fee2e2','text'=>'#991b1b','label'=>'SORTIE'],
        'transfert'  => ['bg'=>'#dbeafe','text'=>'#1e40af','label'=>'TRANSFERT'],
        'ajustement' => ['bg'=>'#fef3c7','text'=>'#92400e','label'=>'AJUSTEMENT'],
    ];
    $tc = $typeColors[$mouvement->type_mouvement?->value] ?? ['bg'=>'#f3f4f6','text'=>'#111','label'=>strtoupper($mouvement->type_mouvement?->value)];
@endphp

<div class="header">
    <div class="header-left">
        <div class="doc-title">BON DE MOUVEMENT</div>
        <div class="doc-sub">N° {{ str_pad($mouvement->id, 6, '0', STR_PAD_LEFT) }} &nbsp;&middot;&nbsp; {{ $mouvement->date_mouvement?->format('d/m/Y') }}</div>
    </div>
    <div class="header-right">
        <span class="type-badge" style="background:{{ $tc['bg'] }};color:{{ $tc['text'] }};">
            {{ $tc['label'] }}
        </span>
    </div>
</div>

<table class="meta-table">
    <tr>
        <td class="label">Dépôt source</td>
        <td>{{ $mouvement->depot->nom }}</td>
        <td class="label">Date</td>
        <td>{{ $mouvement->date_mouvement?->format('d/m/Y') }}</td>
    </tr>
    @if($mouvement->depotDestination)
    <tr>
        <td class="label">Dépôt destination</td>
        <td>{{ $mouvement->depotDestination->nom }}</td>
        <td class="label">Opérateur</td>
        <td>{{ $mouvement->user?->name ?? '—' }}</td>
    </tr>
    @else
    <tr>
        <td class="label">Opérateur</td>
        <td colspan="3">{{ $mouvement->user?->name ?? '—' }}</td>
    </tr>
    @endif
    @if($mouvement->contact)
    <tr>
        <td class="label">Contact</td>
        <td colspan="3">{{ $mouvement->contact->raison_social ?? $mouvement->contact->nom_complet }}</td>
    </tr>
    @endif
    @if($mouvement->facture)
    <tr>
        <td class="label">Facture liée</td>
        <td colspan="3">{{ $mouvement->facture->numero_facture }}</td>
    </tr>
    @endif
    @if($mouvement->motif)
    <tr>
        <td class="label">Motif</td>
        <td colspan="3">{{ $mouvement->motif }}</td>
    </tr>
    @endif
</table>

<table class="produit-table">
    <thead>
        <tr>
            <th style="width:12%;">Code</th>
            <th style="width:40%;">Désignation</th>
            <th style="width:18%;">Catégorie</th>
            <th style="width:15%;">Unité</th>
            <th style="width:15%;text-align:center;">Quantité</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $mouvement->produit->code }}</td>
            <td><strong>{{ $mouvement->produit->nom }}</strong>
                @if($mouvement->produit->description)
                    <br><span style="font-size:10px;color:#666;">{{ Str::limit($mouvement->produit->description, 80) }}</span>
                @endif
            </td>
            <td>{{ $mouvement->produit->categorie?->nom ?? '—' }}</td>
            <td>{{ $mouvement->produit->unite_mesure?->value ?? '—' }}</td>
            <td class="qte-cell" style="color:{{ $tc['text'] }};">{{ $mouvement->quantite }}</td>
        </tr>
    </tbody>
</table>

<div class="footer-grid">
    <div class="footer-cell">
        <div class="sign-label">Remis par</div>
        <div class="sign-line"></div>
    </div>
    <div class="footer-cell">
        <div class="sign-label">Reçu par</div>
        <div class="sign-line"></div>
    </div>
</div>
</body>
</html>
