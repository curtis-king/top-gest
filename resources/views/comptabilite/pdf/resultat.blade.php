<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Compte de résultat</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; color: #000; font-size: 11px; }
        h2 { font-size: 16px; margin-bottom: 4px; }
        h3 { font-size: 12px; margin: 10px 0 6px 0; text-transform: uppercase; }
        .subtitle { font-size: 11px; color: #444; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 18px; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 6px 8px; }
        th { background: #eee; text-align: left; font-size: 10px; text-transform: uppercase; }
        td.num { text-align: right; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
        .resultat-box { border: 1px solid #333; padding: 12px; text-align: center; margin-top: 10px; }
        .resultat-box .amount { font-size: 18px; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Compte de résultat</h2>
    <div class="subtitle">
        Période :
        {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Début' }}
        &mdash;
        {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : "Aujourd'hui" }}
    </div>

    <h3>Charges</h3>
    <table>
        <thead>
            <tr><th>Numéro</th><th>Libellé</th><th>Montant</th></tr>
        </thead>
        <tbody>
            @forelse($charges as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total charges</td><td class="num">{{ number_format($totalCharges, 0, ',', ' ') }}</td></tr>
        </tfoot>
    </table>

    <h3>Produits</h3>
    <table>
        <thead>
            <tr><th>Numéro</th><th>Libellé</th><th>Montant</th></tr>
        </thead>
        <tbody>
            @forelse($produits as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total produits</td><td class="num">{{ number_format($totalProduits, 0, ',', ' ') }}</td></tr>
        </tfoot>
    </table>

    <div class="resultat-box">
        <div>Résultat net de l'exercice ({{ $resultatNet >= 0 ? 'Bénéfice' : 'Perte' }})</div>
        <div class="amount">{{ number_format($resultatNet, 0, ',', ' ') }} FCFA</div>
    </div>
</body>
</html>
