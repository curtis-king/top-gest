@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    @if(round($totalActif, 2) !== round($totalPassif, 2))
        <div class="avertissement">Le bilan n'est pas équilibré — vérifiez les écritures non validées.</div>
    @endif

    <table class="cartes">
        <tr>
            <td><div class="libelle">Total actif</div><div class="valeur">{{ $fmt($totalActif) }}</div></td>
            <td><div class="libelle">Total passif</div><div class="valeur">{{ $fmt($totalPassif) }}</div></td>
            <td><div class="libelle">Résultat de l'exercice</div><div class="valeur {{ $resultat >= 0 ? 'positif' : 'negatif' }}">{{ $fmt($resultat) }}</div></td>
        </tr>
    </table>

    <h3>Actif</h3>
    <table class="donnees">
        <thead><tr><th style="width:80px;">Compte</th><th>Libellé</th><th class="num" style="width:120px;">Solde</th></tr></thead>
        <tbody>
            @forelse($actif as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ $fmt($ligne['solde']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;" class="muet">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="2">Total actif</td><td class="num">{{ $fmt($totalActif) }}</td></tr></tfoot>
    </table>

    <h3>Passif</h3>
    <table class="donnees">
        <thead><tr><th style="width:80px;">Compte</th><th>Libellé</th><th class="num" style="width:120px;">Solde</th></tr></thead>
        <tbody>
            @forelse($passif as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ $fmt($ligne['solde']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;" class="muet">Aucune donnée.</td></tr>
            @endforelse
            <tr class="sous-total">
                <td colspan="2">Résultat de l'exercice</td>
                <td class="num">{{ $fmt($resultat) }}</td>
            </tr>
        </tbody>
        <tfoot><tr><td colspan="2">Total passif</td><td class="num">{{ $fmt($totalPassif) }}</td></tr></tfoot>
    </table>
@endsection
