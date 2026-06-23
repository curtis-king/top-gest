<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #111;
        }

        @page {
            margin: 0;
            size: A4 portrait;
        }

        /* ── Fond de page (image exportée depuis fond_facture.pdf) ── */
        .bg-page {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            z-index: -1;
        }
        .bg-page img { width: 100%; height: 100%; }

        /* ── Pied de page fixe ── */
        .page-footer {
            position: fixed;
            bottom: 7mm;
            left: 0; right: 0;
            text-align: center;
            font-size: 9px;
            color: #333;
            border-top: 0.5px solid #999;
            padding-top: 3px;
        }

        /* ── Corps ── */
        .page {
            padding: 22mm 20mm 35mm 20mm;
        }

        /* Titre */
        .facture-title {
            font-size: 22px;
            font-weight: bold;
            color: #006ed9;
            letter-spacing: 3px;
            margin-bottom: 6px;
        }

        .title-sep {
            border: none;
            border-top: 1.5px solid #006ed9;
            margin-bottom: 10px;
        }

        /* Info client + logo */
        .header-table { width: 100%; margin-bottom: 14px; }
        .header-table td { vertical-align: top; padding: 0; }

        .client-info div { line-height: 1.8; font-size: 12px; }
        .client-info strong { font-weight: bold; }

        .logo-cell { text-align: right; }
        .logo-cell img { width: 100px; height: auto; }
        .company-name {
            font-size: 15px;
            font-weight: bold;
            color: #006ed9;
            margin-top: 4px;
        }
        .company-sub { font-size: 10px; color: #555; }

        /* OBJET */
        .objet {
            font-size: 12.5px;
            margin-bottom: 8px;
        }
        .objet-label {
            font-weight: bold;
            font-style: italic;
            text-decoration: underline;
        }
        .objet-text { font-weight: bold; font-style: italic; }

        /* DOIT */
        .doit {
            font-size: 12.5px;
            font-weight: bold;
            font-style: italic;
            margin-bottom: 12px;
        }

        /* Tableau items */
        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead tr {
            background-color: #006ed9;
            color: #fff;
        }
        .items-table thead th {
            padding: 8px 6px;
            font-size: 11px;
            font-weight: bold;
            text-align: center;
            border: 0.5px solid #0058b0;
        }
        .items-table thead th:first-child { text-align: left; }

        .items-table tbody tr { background: #fff; }
        .items-table tbody tr:nth-child(even) { background: #f4f7fc; }

        .items-table tbody td {
            padding: 7px 6px;
            font-size: 11px;
            text-align: center;
            border-bottom: 0.5px solid #dde4ec;
            border-left: 0.5px solid #dde4ec;
            border-right: 0.5px solid #dde4ec;
        }
        .items-table tbody td:first-child { text-align: left; }

        /* Lignes totaux intégrées dans la table */
        .row-net td {
            background: #dce5f0;
            font-weight: bold;
            padding: 6px 5px;
            border: 0.5px solid #bfcfe0;
        }
        .row-tva td {
            background: #eef2f8;
            padding: 5px 5px;
            border: 0.5px solid #dde4ec;
        }
        .row-ttc td {
            background: #006ed9;
            color: #fff;
            font-weight: bold;
            font-size: 12px;
            padding: 8px 6px;
            border: 0.5px solid #0058b0;
        }

        /* Montant en lettres */
        .amount-words {
            font-style: italic;
            font-size: 11.5px;
            margin: 18px 0 16px 0;
            line-height: 1.55;
        }

        /* Signature — fixée en bas de page, au-dessus du footer */
        .signature-area {
            position: fixed;
            bottom: 22mm;
            right: 20mm;
            text-align: center;
            font-size: 12px;
            font-style: italic;
        }
        .signature-title {
            font-weight: bold;
            margin-bottom: 36px;
        }
        .signature-line { border-top: 0.5px solid #333; display: inline-block; width: 140px; }
        .signature-sub { font-size: 10px; }
    </style>
</head>
<body>

@php
    // ── Infos entreprise (fond facture) ──────────────────────────
    $NIU    = 'M26000000797821C';
    $RCCM   = 'CG-BZV-01-2026-B12';
    $SCIEN  = '2162866';
    $SCIET  = '2162866015';
    $COMPTE = '10335710009/92';

    // ── Chargement des relations ──────────────────────────────────
    $facture->loadMissing(['items', 'contact', 'agence']);

    $contact   = $facture->contact;
    $net       = $facture->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
    $tva       = (int) round($net * 0.10);
    $ttc       = $net + $tva;

    $typeLabel = match($facture->type_facture?->value) {
        'proforma' => 'PROFORMA',
        'avoir'    => 'AVOIR',
        'achat'    => 'ACHAT',
        default    => '',
    };

    $clientNom = $contact?->raison_social
              ?? $contact?->nom_complet
              ?? $facture->raison_social
              ?? '—';

    // Formatage nombre → CFA (1.017.500)
    $fmt = fn($n) => number_format((float)$n, 0, ',', '.');

    $logoPath = public_path('images/logo topinfo.png');
    $fondPath = public_path('images/fond_facture.png');
@endphp

{{-- Fond de page --}}
@if(file_exists($fondPath))
<div class="bg-page">
    <img src="{{ $fondPath }}">
</div>
@endif

{{-- Pied de page fixe --}}
<div class="page-footer">
    NIU : {{ $NIU }} &nbsp;&nbsp;&nbsp; RCCM {{ $RCCM }} &nbsp;&nbsp;&nbsp;
    SCIEN: {{ $SCIEN }} &nbsp;&nbsp; SCIET: {{ $SCIET }} &nbsp;&nbsp;&nbsp;
    N° Compte : {{ $COMPTE }}
</div>

<div class="page">

    {{-- ── Titre ── --}}
    <div class="facture-title">FACTURE &nbsp; {{ $typeLabel }}</div>
    <hr class="title-sep">

    {{-- ── En-tête : infos client + logo ── --}}
    <table class="header-table">
        <tr>
            <td style="width:55%;">
                <div class="client-info">
                    <div><strong>Numéro :</strong> {{ $facture->numero_facture }}</div>
                    <div><strong>Date :</strong> {{ $facture->date_facture?->format('d-m-Y') ?? '—' }}</div>
                    <div><strong>Client :</strong> {{ $clientNom }}</div>
                    @if($contact?->adresse)
                        <div><strong>BP :</strong> {{ $contact->adresse }}</div>
                    @endif
                    @if($contact?->telephone)
                        <div><strong>Tel :</strong> {{ $contact->telephone }}</div>
                    @endif
                    <div><strong>Interlocuteur :</strong> {{ $clientNom }}</div>
                </div>
            </td>
            <td style="width:45%;" class="logo-cell">
                @if(file_exists($logoPath))
                    <img src="{{ $logoPath }}">
                @endif
                <div class="company-name">{{ $facture->agence?->name_agence ?? 'TOPINFO' }}</div>
                <div class="company-sub">SOLUTIONS INFORMATIQUES</div>
            </td>
        </tr>
    </table>

    {{-- ── OBJET ── --}}
    <div class="objet">
        <span class="objet-label">OBJET</span> :&nbsp;
        <span class="objet-text">{{ $facture->objet ?: ($facture->type_facture?->value === 'proforma' ? 'Facture Proforma' : ucfirst($facture->type_facture?->value ?? '')) }}</span>
    </div>

    {{-- ── DOIT ── --}}
    <div class="doit">Doit : &nbsp; {{ $clientNom }}</div>

    {{-- ── Tableau ── --}}
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:36%;text-align:left;">Désignation</th>
                <th style="width:10%;">Quantité</th>
                <th style="width:12%;">Coût unit.</th>
                <th style="width:9%;">Remise %</th>
                <th style="width:12%;">Prix unit.</th>
                <th style="width:21%;">Montant total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facture->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantite }}</td>
                    <td style="text-align:right;">{{ $fmt($item->prix_unitaire) }}</td>
                    <td></td>
                    <td style="text-align:right;">{{ $fmt($item->prix_unitaire) }}</td>
                    <td style="text-align:right;font-weight:bold;">{{ $fmt($item->sous_total) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align:center;color:#888;padding:16px;">Aucun article</td>
                </tr>
            @endforelse

            {{-- NET A PAYER --}}
            <tr class="row-net">
                <td colspan="5" style="text-align:right;">NET A PAYER</td>
                <td style="text-align:right;">{{ $fmt($net) }}</td>
            </tr>

            {{-- TVA --}}
            <tr class="row-tva">
                <td colspan="5" style="text-align:right;">TVA 10%</td>
                <td style="text-align:right;">{{ $fmt($tva) }}</td>
            </tr>

            {{-- TOTAL TTC --}}
            <tr class="row-ttc">
                <td colspan="5" style="text-align:right;">TOTAL TTC</td>
                <td style="text-align:right;">{{ $fmt($ttc) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- ── Montant en lettres ── --}}
    <div class="amount-words">
        Arrêté la présente facture {{ strtolower($typeLabel ?: 'facture') }} à la somme
        de <strong>{{ $fmt($ttc) }}</strong> francs CFA.
    </div>

    {{-- ── Signature ── --}}
    <div class="signature-area">
        <div class="signature-title">La Direction</div>
        <div class="signature-line"></div><br>
        <div class="signature-sub">Directeur Général</div>
    </div>

</div>
</body>
</html>
