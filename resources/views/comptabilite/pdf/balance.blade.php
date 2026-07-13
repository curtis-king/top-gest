<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Balance des comptes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: sans-serif; color: #000; font-size: 11px; }
        h2 { font-size: 16px; margin-bottom: 4px; }
        .subtitle { font-size: 11px; color: #444; margin-bottom: 16px; }
        table { border-collapse: collapse; width: 100%; }
        table, th, td { border: 1px solid #333; }
        th, td { padding: 6px 8px; }
        th { background: #eee; text-align: left; font-size: 10px; text-transform: uppercase; }
        td.num { text-align: right; }
        tfoot td { font-weight: bold; background: #f5f5f5; }
    </style>
</head>
<body>
    <h2>Balance des comptes</h2>
    <div class="subtitle">
        Période :
        {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Début' }}
        &mdash;
        {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : "Aujourd'hui" }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Numéro</th>
                <th>Libellé</th>
                <th>Total débit</th>
                <th>Total crédit</th>
                <th>Solde</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lignes as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ number_format($ligne['total_debit'], 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($ligne['total_credit'], 0, ',', ' ') }}</td>
                    <td class="num">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Aucune donnée.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="2">Total</td>
                <td class="num">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                <td class="num">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
