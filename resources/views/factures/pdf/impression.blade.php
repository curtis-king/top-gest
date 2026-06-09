<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->numero_facture }}</title>
    <style>
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1e293b; margin: 0; padding: 30px; }
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 2px solid #2563eb; }
        .header h1 { font-size: 22px; color: #2563eb; margin: 0; }
        .header .numero { font-size: 14px; color: #64748b; }
        .infos { display: flex; justify-content: space-between; margin-bottom: 30px; }
        .infos > div { width: 45%; }
        .infos h3 { font-size: 11px; text-transform: uppercase; color: #94a3b8; margin: 0 0 6px 0; letter-spacing: 1px; }
        .infos p { margin: 2px 0; font-size: 12px; color: #334155; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table th { background: #f1f5f9; color: #475569; font-size: 10px; text-transform: uppercase; letter-spacing: .5px; padding: 10px 8px; text-align: left; }
        table td { padding: 10px 8px; border-bottom: 1px solid #e2e8f0; font-size: 12px; }
        table td:not(:first-child) { text-align: right; }
        .total { text-align: right; font-size: 16px; font-weight: bold; color: #16a34a; padding-top: 10px; border-top: 2px solid #e2e8f0; }
        .footer { margin-top: 40px; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 10px; color: #94a3b8; text-align: center; }
        .statut { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .badge-payee { background: #dcfce7; color: #16a34a; }
        .badge-impayee { background: #fee2e2; color: #dc2626; }
        .badge-partielle { background: #fef3c7; color: #d97706; }
        .badge-annulee { background: #e2e8f0; color: #64748b; }
        .badge-brouillon { background: #e2e8f0; color: #64748b; }
        @page { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <h1>FACTURE</h1>
            <div class="numero">{{ $facture->numero_facture }}</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:14px;font-weight:600;color:#1e293b;">{{ $facture->agence?->name_agence ?? 'MyGest' }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;">
                {{ $facture->agence?->adresse ?? '' }}<br>
                {{ $facture->agence?->numero_telephone ?? '' }}<br>
                {{ $facture->agence?->adresse_email ?? '' }}
            </div>
        </div>
    </div>

    <div class="infos">
        <div>
            <h3>Client</h3>
            <p><strong>{{ $facture->raison_social ?? $facture->contact?->raison_social ?? '—' }}</strong></p>
            @if($facture->contact)
                <p>{{ $facture->contact->adresse }}</p>
                <p>{{ $facture->contact->telephone }}</p>
                <p>{{ $facture->contact->adresse_email }}</p>
            @endif
        </div>
        <div style="text-align:right;">
            <h3>Références</h3>
            <p><strong>Date :</strong> {{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '—' }}</p>
            <p><strong>Type :</strong> {{ $facture->type_facture?->value ?? '—' }}</p>
            <p><strong>Statut :</strong>
                @php
                    $sv = $facture->statut_facture?->value ?? 'brouillon';
                    $cls = match($sv) { 'payee'=>'badge-payee', 'impayee'=>'badge-impayee', 'partielle'=>'badge-partielle', 'annulee'=>'badge-annulee', default=>'badge-brouillon' };
                @endphp
                <span class="statut {{ $cls }}">{{ $sv }}</span>
            </p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:50%;">Description</th>
                <th>Quantité</th>
                <th>Prix unit.</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($facture->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ $item->quantite }}</td>
                    <td>{{ number_format($item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                    <td style="font-weight:600;">{{ number_format($item->quantite * $item->prix_unitaire, 0, ',', ' ') }} FCFA</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="text-align:center;color:#94a3b8;">Aucun article</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Total : {{ number_format($total, 0, ',', ' ') }} FCFA
    </div>

    <div class="footer">
        <p>Merci de votre confiance</p>
        <p>Document généré le {{ date('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
