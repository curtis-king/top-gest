<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Contrat de travail - {{ $employee->nom_complet }}</title>
<style>
    @page { margin: 20mm 18mm 22mm 18mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.6; margin:0; padding:0; }

    .header { position: fixed; top: -18mm; left: 0; right: 0; height: 16mm; display:flex; align-items:center; justify-content:space-between; padding:0 4mm; border-bottom:3px solid #2563eb; }
    .header-left { display:flex; align-items:center; gap:4mm; }
    .header-logo { width:12mm; height:12mm; object-fit:contain; }
    .header-info .name { font-size:14px; font-weight:700; color:#1e293b; }
    .header-info .slogan { font-size:9px; color:#64748b; font-style:italic; }
    .header-right { text-align:right; font-size:9px; color:#475569; }
    .header-right .agency { font-weight:700; font-size:11px; color:#2563eb; }

    .footer { position: fixed; bottom: -18mm; left: 0; right: 0; height: 14mm; border-top:2px solid #2563eb; padding:3mm 4mm 0; font-size:8px; color:#64748b; display:flex; justify-content:space-between; align-items:flex-start; }

    .title-block { text-align:center; margin:6mm 0 8mm; }
    .title-block h1 { font-size:17px; font-weight:800; color:#1e293b; margin:0 0 2mm; text-transform:uppercase; letter-spacing:1px; }
    .title-block .type-badge { display:inline-block; background:#2563eb; color:#fff; font-size:10px; font-weight:700; padding:2px 10px; border-radius:10px; letter-spacing:.5px; }

    .parties { margin-bottom:6mm; }
    .parties-title { font-size:12px; font-weight:700; color:#2563eb; text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #2563eb; padding-bottom:1mm; margin-bottom:3mm; }
    .party-block { background:#f8fafc; border-left:3px solid #2563eb; padding:3mm 4mm; margin-bottom:3mm; }
    .party-block .party-role { font-size:10px; font-weight:700; text-transform:uppercase; color:#2563eb; margin-bottom:1.5mm; letter-spacing:.3px; }
    .party-block p { margin:0; line-height:1.7; font-size:11px; }

    .article { margin-bottom:5mm; }
    .article-title { font-size:11px; font-weight:700; color:#1e293b; text-transform:uppercase; letter-spacing:.3px; margin-bottom:2mm; }
    .article-body { font-size:11px; line-height:1.7; text-align:justify; }

    .highlight { font-weight:700; color:#1e293b; }
    .underline { text-decoration:underline; }

    .signatures { margin-top:14mm; display:table; width:100%; }
    .sig-col { display:table-cell; width:50%; text-align:center; vertical-align:top; padding:0 5mm; }
    .sig-title { font-size:11px; font-weight:700; margin-bottom:18mm; }
    .sig-line { border-top:1px solid #1e293b; display:block; width:120px; margin:0 auto 2mm; }
    .sig-name { font-size:10px; color:#475569; }

    .blank { display:inline-block; min-width:60px; border-bottom:1px solid #475569; }
</style>
</head>
<body>

@php
    $dossier     = $employee->dossier;
    $typeLabel   = strtoupper($dossier?->type_contrat?->value ?? 'CDI');
    $dateEngagement = $dossier?->date_engagement?->format('d/m/Y') ?? '___/___/______';
    $dateFin     = $dossier?->date_fin?->format('d/m/Y') ?? null;
    $fonction    = $employee->fonction?->name ?? '____________________';
    $salaireStr  = $salaire ? number_format($salaire, 0, ',', ' ') . ' FCFA' : '____________________';
    $logoPath    = $compagnie?->logo ? storage_path('app/public/' . $compagnie->logo) : null;
    $ville       = $agence?->ville ?? 'Brazzaville';
    $today       = now()->format('d/m/Y');
@endphp

<div class="header">
    <div class="header-left">
        @if($logoPath && file_exists($logoPath))
            <img class="header-logo" src="{{ $logoPath }}" alt="Logo">
        @endif
        <div class="header-info">
            <div class="name">{{ $compagnie?->name ?? 'MyGest' }}</div>
            <div class="slogan">{{ $compagnie?->slogan ?? '' }}</div>
        </div>
    </div>
    <div class="header-right">
        <div class="agency">{{ $agence?->name_agence ?? '' }}</div>
        <div>{{ $agence?->adresse ?? '' }}</div>
        <div>Tél : {{ $agence?->numero_telephone ?? '' }}</div>
        <div>{{ $agence?->adresse_email ?? '' }}</div>
    </div>
</div>

<div class="footer">
    <div>
        <span>RCCM : {{ $compagnie?->rccm ?? '—' }}</span> &nbsp;&nbsp;
        <span>NUI : {{ $compagnie?->nui ?? '—' }}</span>
    </div>
    <div>Document généré le {{ $today }} — Confidentiel</div>
</div>

<div class="title-block">
    <h1>Contrat de travail</h1>
    <span class="type-badge">{{ $typeLabel }}</span>
</div>

{{-- ENTRE LES PARTIES --}}
<div class="parties">
    <div class="parties-title">Entre les soussignés</div>

    <div class="party-block">
        <div class="party-role">L'Employeur</div>
        <p>
            <span class="highlight">{{ $compagnie?->name ?? $agence?->name_agence ?? '____________________' }}</span>
            @if($compagnie?->forme_juridique), {{ $compagnie->forme_juridique }}@endif,
            sise à {{ $agence?->adresse ?? '____________________' }},
            @if($compagnie?->rccm)immatriculée au RCCM sous le N° <span class="highlight">{{ $compagnie->rccm }}</span>,@endif
            @if($compagnie?->nui)NUI : <span class="highlight">{{ $compagnie->nui }}</span>,@endif
            représentée par son Directeur Général,
            <br>ci-après dénommée <span class="underline">« l'Employeur »</span>,
        </p>
    </div>

    <div style="text-align:center;font-size:11px;font-weight:700;margin:2mm 0;">Et</div>

    <div class="party-block">
        <div class="party-role">L'Employé(e)</div>
        <p>
            M. / Mme <span class="highlight">{{ $employee->nom_complet }}</span>,
            @if($employee->adresse)demeurant à {{ $employee->adresse }},@endif
            @if($employee->telephone)Tél : {{ $employee->telephone }},@endif
            @if($employee->type_piece && $employee->numero_piece)
                porteur(se) de la {{ $employee->type_piece->value }} N° <span class="highlight">{{ $employee->numero_piece }}</span>,
            @endif
            <br>ci-après dénommé(e) <span class="underline">« l'Employé(e) »</span>,
        </p>
    </div>
</div>

{{-- ARTICLES --}}
<div class="article">
    <div class="article-title">Article 1 — Objet et nature du contrat</div>
    <div class="article-body">
        La société <span class="highlight">{{ $compagnie?->name ?? $agence?->name_agence ?? '____________________' }}</span>
        engage M. / Mme <span class="highlight">{{ $employee->nom_complet }}</span>
        en qualité de <span class="highlight">{{ $fonction }}</span>,
        dans le cadre d'un contrat à durée
        @if($typeLabel === 'CDI')
            <span class="highlight">indéterminée (CDI)</span>, conformément aux dispositions du Code du Travail en vigueur.
        @else
            <span class="highlight">déterminée (CDD)</span>, conformément aux dispositions du Code du Travail en vigueur.
        @endif
    </div>
</div>

<div class="article">
    <div class="article-title">Article 2 — Date de prise d'effet
        @if($typeLabel === 'CDD')et durée@endif
    </div>
    <div class="article-body">
        Le présent contrat prend effet à compter du <span class="highlight">{{ $dateEngagement }}</span>.
        @if($typeLabel === 'CDD' && $dateFin)
            Il est conclu pour une durée déterminée prenant fin le <span class="highlight">{{ $dateFin }}</span>, sans possibilité de renouvellement tacite sauf accord exprès des deux parties.
        @elseif($typeLabel === 'CDI')
            Il est conclu pour une durée indéterminée et pourra être rompu dans les conditions prévues par la législation du travail en vigueur.
        @endif
    </div>
</div>

<div class="article">
    <div class="article-title">Article 3 — Lieu et conditions de travail</div>
    <div class="article-body">
        L'Employé(e) exercera ses fonctions au siège de l'agence
        <span class="highlight">{{ $agence?->name_agence ?? '____________________' }}</span>
        située à <span class="highlight">{{ $agence?->adresse ?? '____________________' }}</span>,
        ou en tout autre lieu que l'Employeur pourrait lui indiquer pour les besoins du service.
        La durée de travail est celle en vigueur au sein de la société, conformément à la réglementation applicable.
    </div>
</div>

<div class="article">
    <div class="article-title">Article 4 — Rémunération</div>
    <div class="article-body">
        En contrepartie de ses prestations, l'Employé(e) percevra une rémunération mensuelle brute de
        <span class="highlight">{{ $salaireStr }}</span>,
        versée selon les modalités en usage dans la société.
        Ce salaire pourra faire l'objet de révisions périodiques en fonction des performances et des conditions économiques.
    </div>
</div>

<div class="article">
    <div class="article-title">Article 5 — Période d'essai</div>
    <div class="article-body">
        Le présent contrat est soumis à une période d'essai de <span class="blank"></span> mois,
        durant laquelle chacune des parties pourra y mettre fin librement, sans préavis ni indemnité,
        conformément aux dispositions légales en vigueur.
    </div>
</div>

<div class="article">
    <div class="article-title">Article 6 — Obligations de l'employé(e)</div>
    <div class="article-body">
        L'Employé(e) s'engage à exercer ses fonctions avec diligence et loyauté, à respecter le règlement intérieur
        de la société, à préserver la confidentialité des informations auxquelles il/elle aura accès dans le cadre
        de ses fonctions, et à ne pas exercer d'activité concurrente sans autorisation préalable écrite de l'Employeur.
    </div>
</div>

<div class="article">
    <div class="article-title">Article 7 — Droit applicable</div>
    <div class="article-body">
        Le présent contrat est soumis aux dispositions du Code du Travail de la République du Congo
        et aux conventions collectives applicables. Tout litige relatif à son exécution ou à sa résiliation
        sera soumis aux juridictions compétentes de {{ $ville }}.
    </div>
</div>

<div class="article">
    <div class="article-body">
        Fait à <span class="highlight">{{ $ville }}</span>, le <span class="highlight">{{ $today }}</span>,
        en deux (2) exemplaires originaux, dont un remis à chaque partie.
    </div>
</div>

{{-- SIGNATURES --}}
<div class="signatures">
    <div class="sig-col">
        <div class="sig-title">Pour l'Employeur</div>
        <div class="sig-line"></div>
        <div class="sig-name">Le Directeur Général</div>
        <div class="sig-name" style="margin-top:1mm;">{{ $compagnie?->name ?? $agence?->name_agence ?? '' }}</div>
    </div>
    <div class="sig-col">
        <div class="sig-title">L'Employé(e)</div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $employee->nom_complet }}</div>
        <div class="sig-name" style="margin-top:1mm;font-style:italic;">(Lu et approuvé)</div>
    </div>
</div>

</body>
</html>
