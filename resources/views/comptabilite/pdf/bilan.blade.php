<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bilan</title>
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
        .warning { background: #fff3cd; border: 1px solid #f5c542; padding: 8px; margin-bottom: 14px; font-size: 11px; }
    </style>
</head>
<body>
    <h2>Bilan</h2>
    <div class="subtitle">
        Période :
        {{ $dateDebut ? \Carbon\Carbon::parse($dateDebut)->format('d/m/Y') : 'Début' }}
        &mdash;
        {{ $dateFin ? \Carbon\Carbon::parse($dateFin)->format('d/m/Y') : "Aujourd'hui" }}
    </div>

    @if(round($totalActif, 2) !== round($totalPassif, 2))
        <div class="warning">Le bilan n'est pas équilibré — vérifiez les écritures non validées.</div>
    @endif

    <h3>Actif</h3>
    <table>
        <thead>
            <tr><th>Numéro</th><th>Libellé</th><th>Solde</th></tr>
        </thead>
        <tbody>
            @forelse($actif as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total actif</td><td class="num">{{ number_format($totalActif, 0, ',', ' ') }}</td></tr>
        </tfoot>
    </table>

    <h3>Passif</h3>
    <table>
        <thead>
            <tr><th>Numéro</th><th>Libellé</th><th>Solde</th></tr>
        </thead>
        <tbody>
            @forelse($passif as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;">Aucune donnée.</td></tr>
            @endforelse
            <tr>
                <td colspan="2">Résultat de l'exercice</td>
                <td class="num">{{ number_format($resultat, 0, ',', ' ') }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr><td colspan="2">Total passif</td><td class="num">{{ number_format($totalPassif, 0, ',', ' ') }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
