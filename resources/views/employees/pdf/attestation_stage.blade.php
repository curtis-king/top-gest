<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Attestation de stage - {{ $employee->nom_complet }}</title>
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

    .ref-line { text-align:right; font-size:10px; color:#64748b; margin-bottom:6mm; }

    .title-block { text-align:center; margin:8mm 0 10mm; }
    .title-block h1 { font-size:18px; font-weight:800; color:#1e293b; margin:0; text-transform:uppercase; letter-spacing:1.5px; }
    .title-sep { width:60mm; border:none; border-top:2.5px solid #2563eb; margin:3mm auto 0; }

    .body-text { font-size:12px; line-height:2; text-align:justify; margin-bottom:6mm; }
    .body-text .highlight { font-weight:700; color:#1e293b; text-decoration:underline; }

    .info-table { width:100%; border-collapse:collapse; margin:6mm 0; }
    .info-table td { padding:2.5mm 4mm; border:1px solid #e2e8f0; font-size:11px; }
    .info-table td.lbl { background:#f8fafc; font-weight:600; color:#475569; width:40%; text-transform:uppercase; font-size:10px; letter-spacing:.3px; }

    .mention { font-size:10.5px; line-height:1.8; text-align:justify; color:#334155; margin-top:4mm; font-style:italic; }

    .signatures { margin-top:16mm; display:table; width:100%; }
    .sig-col { display:table-cell; width:50%; text-align:center; vertical-align:top; padding:0 5mm; }
    .sig-title { font-size:11px; font-weight:700; margin-bottom:18mm; }
    .sig-line { border-top:1px solid #1e293b; display:block; width:120px; margin:0 auto 2mm; }
    .sig-name { font-size:10px; color:#475569; }
</style>
</head>
<body>

@php
    $dossier        = $employee->dossier;
    $dateDebut      = $dossier?->date_engagement?->format('d/m/Y') ?? '___/___/______';
    $dateFin        = $dossier?->date_fin?->format('d/m/Y') ?? '___/___/______';
    $dateDebutObj   = $dossier?->date_engagement;
    $dateFinObj     = $dossier?->date_fin;
    $duree          = ($dateDebutObj && $dateFinObj) ? $dateDebutObj->diffInDays($dateFinObj) . ' jours' : '____________________';
    $fonction       = $employee->fonction?->name ?? '____________________';
    $logoPath       = $compagnie?->logo ? storage_path('app/public/' . $compagnie->logo) : null;
    $ville          = $agence?->ville ?? 'Brazzaville';
    $today          = now()->format('d/m/Y');
    $ref            = 'ATT-STG-' . str_pad($employee->id, 4, '0', STR_PAD_LEFT) . '-' . now()->year;
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
    <div>Réf : {{ $ref }} — Émis le {{ $today }}</div>
</div>

<div class="ref-line">Réf : {{ $ref }} &nbsp;&nbsp; {{ $ville }}, le {{ $today }}</div>

<div class="title-block">
    <h1>Attestation de stage</h1>
    <hr class="title-sep">
</div>

<div class="body-text">
    <p>
        Nous soussignés, <span class="highlight">{{ $compagnie?->name ?? $agence?->name_agence ?? '____________________' }}</span>
        @if($compagnie?->forme_juridique), {{ $compagnie->forme_juridique }}@endif,
        sise à <span class="highlight">{{ $agence?->adresse ?? '____________________' }}</span>,
        @if($compagnie?->rccm)immatriculée au RCCM sous le N° <span class="highlight">{{ $compagnie->rccm }}</span>,@endif
        certifions par la présente que :
    </p>
</div>

<table class="info-table">
    <tr>
        <td class="lbl">Nom et prénom(s)</td>
        <td><strong>{{ $employee->nom_complet }}</strong></td>
    </tr>
    @if($employee->type_piece && $employee->numero_piece)
    <tr>
        <td class="lbl">Pièce d'identité</td>
        <td>{{ $employee->type_piece->value }} N° {{ $employee->numero_piece }}</td>
    </tr>
    @endif
    @if($employee->adresse)
    <tr>
        <td class="lbl">Adresse</td>
        <td>{{ $employee->adresse }}</td>
    </tr>
    @endif
    <tr>
        <td class="lbl">Département / Service</td>
        <td>{{ $fonction }}</td>
    </tr>
    <tr>
        <td class="lbl">Période de stage</td>
        <td>Du <strong>{{ $dateDebut }}</strong> au <strong>{{ $dateFin }}</strong></td>
    </tr>
    <tr>
        <td class="lbl">Durée totale</td>
        <td><strong>{{ $duree }}</strong></td>
    </tr>
</table>

<div class="body-text">
    <p>
        a effectué un stage au sein de notre structure, dans le département
        <span class="highlight">{{ $fonction }}</span>,
        du <span class="highlight">{{ $dateDebut }}</span> au <span class="highlight">{{ $dateFin }}</span>.
    </p>
    <p>
        Durant toute la durée de son stage, M. / Mme <span class="highlight">{{ $employee->nom_complet }}</span>
        a fait preuve de sérieux, de rigueur et de professionnalisme dans l'accomplissement des tâches
        qui lui ont été confiées. Nous lui souhaitons plein succès dans la poursuite de ses études et de sa carrière.
    </p>
</div>

<div class="mention">
    La présente attestation est délivrée à l'intéressé(e) pour servir et valoir ce que de droit,
    et ne saurait en aucun cas constituer un contrat de travail ou une promesse d'embauche.
</div>

<div class="signatures">
    <div class="sig-col">
        <div class="sig-title">Le Directeur Général</div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $compagnie?->name ?? $agence?->name_agence ?? '' }}</div>
        <div class="sig-name" style="margin-top:1mm;font-style:italic;">Signature et cachet</div>
    </div>
    <div class="sig-col">
        <div class="sig-title">Le / La Stagiaire</div>
        <div class="sig-line"></div>
        <div class="sig-name">{{ $employee->nom_complet }}</div>
        <div class="sig-name" style="margin-top:1mm;font-style:italic;">(Lu et approuvé)</div>
    </div>
</div>

</body>
</html>
