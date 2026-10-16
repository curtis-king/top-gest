<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Fiche employé - {{ $employee->nom_complet }}</title>
<style>
    @page { margin: 20mm 12mm 22mm 12mm; }
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.5; margin:0; padding:0; }

    .header { position: fixed; top: -18mm; left: 0; right: 0; height: 16mm; display:flex; align-items:center; justify-content:space-between; padding:0 4mm; border-bottom:3px solid #2563eb; }
    .header-left { display:flex; align-items:center; gap:4mm; }
    .header-logo { width:12mm; height:12mm; object-fit:contain; }
    .header-info { text-align:left; }
    .header-info .name { font-size:14px; font-weight:700; color:#1e293b; }
    .header-info .slogan { font-size:9px; color:#64748b; font-style:italic; }
    .header-right { text-align:right; font-size:9px; color:#475569; }
    .header-right .agency { font-weight:700; font-size:11px; color:#2563eb; }
    .header-right div { margin-top:1px; }

    .footer { position: fixed; bottom: -18mm; left: 0; right: 0; height: 14mm; border-top:2px solid #2563eb; padding:3mm 4mm 0; font-size:8px; color:#64748b; display:flex; justify-content:space-between; align-items:flex-start; }
    .footer-left { text-align:left; }
    .footer-right { text-align:right; }

    .title-section { text-align:center; margin:4mm 0 6mm; padding:3mm 0; border-bottom:1px solid #e2e8f0; }
    .title-section h1 { font-size:18px; font-weight:800; color:#1e293b; margin:0 0 1mm; text-transform:uppercase; letter-spacing:1px; }
    .title-section span { font-size:10px; color:#64748b; }

    .info-grid { width:100%; border-collapse:collapse; margin-bottom:5mm; }
    .info-grid td { padding:2.5mm 3mm; border:1px solid #e2e8f0; vertical-align:top; }
    .info-grid .label { background:#f8fafc; font-weight:600; color:#475569; width:35%; font-size:10px; text-transform:uppercase; letter-spacing:0.3px; }
    .info-grid .value { color:#1e293b; font-size:11px; }

    .section-title { font-size:12px; font-weight:700; color:#2563eb; text-transform:uppercase; letter-spacing:0.5px; margin:5mm 0 2mm; padding-bottom:1mm; border-bottom:2px solid #2563eb; }

    .data-table { width:100%; border-collapse:collapse; margin-bottom:4mm; }
    .data-table th { background:#2563eb; color:#fff; padding:2mm 2.5mm; font-size:9px; font-weight:600; text-transform:uppercase; letter-spacing:0.3px; text-align:left; }
    .data-table td { padding:2mm 2.5mm; border-bottom:1px solid #e2e8f0; font-size:10px; color:#334155; }
    .data-table tr:nth-child(even) td { background:#f8fafc; }

    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:9px; font-weight:600; }
    .badge-actif { background:#dcfce7; color:#16a34a; }
    .badge-suspendu { background:#fef9c3; color:#ca8a04; }
    .badge-termine { background:#f1f5f9; color:#64748b; }
    .badge-annule { background:#fee2e2; color:#dc2626; }
    .badge-en_attente { background:#fef9c3; color:#ca8a04; }
    .badge-valide { background:#dcfce7; color:#16a34a; }
    .badge-paye { background:#dbeafe; color:#2563eb; }

    .amount-positive { color:#16a34a; font-weight:700; }
    .amount-negative { color:#dc2626; font-weight:700; }

    .page-break { page-break-before: always; }
</style>
</head>
<body>

    <div class="header">
        <div class="header-left">
            @if($compagnie && $compagnie->logo)
                <img class="header-logo" src="{{ storage_path('app/public/'.$compagnie->logo) }}" alt="Logo">
            @endif
            <div class="header-info">
                <div class="name">{{ $compagnie?->name ?? 'MyGest' }}</div>
                <div class="slogan">{{ $compagnie?->slogan ?? '' }}</div>
            </div>
        </div>
        <div class="header-right">
            <div class="agency">{{ $agence?->name_agence ?? '' }}</div>
            <div>{{ $agence?->adresse ?? '' }}</div>
            <div>Tel: {{ $agence?->numero_telephone ?? '' }}</div>
            <div>{{ $agence?->adresse_email ?? '' }}</div>
        </div>
    </div>

    <div class="footer">
        <div class="footer-left">
            <div><strong>RCCM:</strong> {{ $compagnie?->rccm ?? '—' }}</div>
            <div><strong>NUI:</strong> {{ $compagnie?->nui ?? '—' }}</div>
        </div>
        <div class="footer-right">
            <div>Document généré le {{ now()->format('d/m/Y') }}</div>
            <div>{{ $compagnie?->forme_juridique ?? '' }}</div>
        </div>
    </div>

    <div class="title-section">
        <h1>Fiche d'employé</h1>
        <span>Réf: EMP-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
    </div>

    <table class="info-grid">
        <tr>
            <td class="label">Nom complet</td>
            <td class="value">{{ $employee->nom_complet }}</td>
            <td class="label">Téléphone</td>
            <td class="value">{{ $employee->telephone ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Adresse</td>
            <td class="value">{{ $employee->adresse ?? '—' }}</td>
            <td class="label">Statut matrimonial</td>
            <td class="value">{{ $employee->status_matrimonial?->value ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Type pièce</td>
            <td class="value">{{ $employee->type_piece?->value ?? '—' }}</td>
            <td class="label">N° pièce</td>
            <td class="value">{{ $employee->numero_piece ?? '—' }}</td>
        </tr>
        <tr>
            <td class="label">Agence</td>
            <td class="value">{{ $agence?->name_agence ?? '—' }}</td>
            <td class="label">Fonction</td>
            <td class="value">{{ $employee->fonction?->name ?? '—' }}</td>
        </tr>
    </table>

    @if($employee->dossier)
        <div class="section-title">Dossier & Contrat</div>
        <table class="info-grid">
            <tr>
                <td class="label">Date d'engagement</td>
                <td class="value">{{ $employee->dossier->date_engagement->format('d/m/Y') }}</td>
                <td class="label">Date de fin</td>
                <td class="value">{{ $employee->dossier->date_fin ? $employee->dossier->date_fin->format('d/m/Y') : '—' }}</td>
            </tr>
            <tr>
                <td class="label">Type de contrat</td>
                <td class="value">{{ strtoupper($employee->dossier->type_contrat?->value ?? '—') }}</td>
                <td class="label">Statut</td>
                <td class="value">
                    @php
                        $dv = $employee->dossier->status?->value ?? 'inconnu';
                        $bc = ['actif'=>'badge-actif','suspendu'=>'badge-suspendu','termine'=>'badge-termine','annule'=>'badge-annule'][$dv]??'';
                    @endphp
                    <span class="badge {{ $bc }}">{{ $dv }}</span>
                </td>
            </tr>
        </table>
    @endif

    @if($employee->conges->count())
        <div class="section-title">Congés ({{ $employee->conges->count() }})</div>
        <table class="data-table">
            <thead>
                <tr><th>Type</th><th>Date début</th><th>Date fin</th><th>Durée</th></tr>
            </thead>
            <tbody>
                @foreach($employee->conges as $c)
                    <tr>
                        <td>{{ $c->type_conge?->value ?? '—' }}</td>
                        <td>{{ $c->date_debut->format('d/m/Y') }}</td>
                        <td>{{ $c->date_fin->format('d/m/Y') }}</td>
                        <td>{{ (int) $c->date_debut->diffInDays($c->date_fin) + 1 }} jour(s)</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($employee->primes->count())
        <div class="section-title">Primes ({{ $employee->primes->count() }})</div>
        <table class="data-table">
            <thead>
                <tr><th>Motif</th><th>Période</th><th style="text-align:right;">Montant</th></tr>
            </thead>
            <tbody>
                @foreach($employee->primes as $p)
                    <tr>
                        <td>{{ $p->motif }}</td>
                        <td>{{ $p->mois }}/{{ $p->annee }}</td>
                        <td style="text-align:right;" class="amount-positive">{{ number_format($p->montant, 0, ',', ' ') }} F</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($employee->retenus->count())
        <div class="section-title">Retenus ({{ $employee->retenus->count() }})</div>
        <table class="data-table">
            <thead>
                <tr><th>Motif</th><th>Date</th><th style="text-align:right;">Montant</th></tr>
            </thead>
            <tbody>
                @foreach($employee->retenus as $r)
                    <tr>
                        <td>{{ $r->motif }}</td>
                        <td>{{ $r->date_retenu->format('d/m/Y') }}</td>
                        <td style="text-align:right;" class="amount-negative">{{ number_format($r->montant, 0, ',', ' ') }} F</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    @if($employee->payements->count())
        <div class="section-title">Paiements ({{ $employee->payements->count() }})</div>
        <table class="data-table">
            <thead>
                <tr><th>Période</th><th>Salaire base</th><th>Primes</th><th>Retenus</th><th style="text-align:right;">Net</th><th>Statut</th></tr>
            </thead>
            <tbody>
                @foreach($employee->payements as $pay)
                    @php
                        $sv = $pay->status?->value ?? 'inconnu';
                        $sc = ['en_attente'=>'badge-en_attente','valide'=>'badge-valide','paye'=>'badge-paye','annule'=>'badge-annule'][$sv]??'';
                    @endphp
                    <tr>
                        <td>{{ $pay->mois }} {{ $pay->annee }}</td>
                        <td>{{ number_format($pay->salaire_base, 0, ',', ' ') }} F</td>
                        <td class="amount-positive">{{ number_format($pay->total_primes ?? 0, 0, ',', ' ') }} F</td>
                        <td class="amount-negative">{{ number_format($pay->total_retenus ?? 0, 0, ',', ' ') }} F</td>
                        <td style="text-align:right;font-weight:700;">{{ number_format($pay->net_a_payer, 0, ',', ' ') }} F</td>
                        <td><span class="badge {{ $sc }}">{{ $sv }}</span></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

</body>
</html>
