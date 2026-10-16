@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    <table class="cartes">
        <tr>
            <td><div class="libelle">Total produits</div><div class="valeur">{{ $fmt($totalProduits) }}</div></td>
            <td><div class="libelle">Total charges</div><div class="valeur">{{ $fmt($totalCharges) }}</div></td>
            <td><div class="libelle">Résultat net ({{ $resultatNet >= 0 ? 'bénéfice' : 'perte' }})</div>
                <div class="valeur {{ $resultatNet >= 0 ? 'positif' : 'negatif' }}">{{ $fmt($resultatNet) }}</div></td>
        </tr>
    </table>

    <h3>Produits</h3>
    <table class="donnees">
        <thead><tr><th style="width:80px;">Compte</th><th>Libellé</th><th class="num" style="width:120px;">Montant</th></tr></thead>
        <tbody>
            @forelse($produits as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ $fmt($ligne['montant']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;" class="muet">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="2">Total produits</td><td class="num">{{ $fmt($totalProduits) }}</td></tr></tfoot>
    </table>

    <h3>Charges</h3>
    <table class="donnees">
        <thead><tr><th style="width:80px;">Compte</th><th>Libellé</th><th class="num" style="width:120px;">Montant</th></tr></thead>
        <tbody>
            @forelse($charges as $ligne)
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ $fmt($ligne['montant']) }}</td>
                </tr>
            @empty
                <tr><td colspan="3" style="text-align:center;" class="muet">Aucune donnée.</td></tr>
            @endforelse
        </tbody>
        <tfoot><tr><td colspan="2">Total charges</td><td class="num">{{ $fmt($totalCharges) }}</td></tr></tfoot>
    </table>

    <table class="donnees" style="margin-top:12px;">
        <tr class="sous-total">
            <td>Résultat net de l'exercice ({{ $resultatNet >= 0 ? 'bénéfice' : 'perte' }})</td>
            <td class="num {{ $resultatNet >= 0 ? 'positif' : 'negatif' }}" style="width:160px;font-size:12px;">{{ $fmt($resultatNet) }} FCFA</td>
        </tr>
    </table>
@endsection
