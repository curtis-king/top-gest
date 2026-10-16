@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    @if(round($totalDebit, 2) !== round($totalCredit, 2))
        <div class="avertissement">Balance déséquilibrée : total débit ≠ total crédit. Vérifiez les écritures.</div>
    @endif

    <table class="donnees">
        <thead>
            <tr>
                <th style="width:80px;">Compte</th>
                <th>Libellé</th>
                <th class="num" style="width:110px;">Total débit</th>
                <th class="num" style="width:110px;">Total crédit</th>
                <th class="num" style="width:110px;">Solde débiteur</th>
                <th class="num" style="width:110px;">Solde créditeur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lignes as $ligne)
                @php $net = $ligne['total_debit'] - $ligne['total_credit']; @endphp
                <tr>
                    <td>{{ $ligne['compte']->numero_compte }}</td>
                    <td>{{ $ligne['compte']->libelle }}</td>
                    <td class="num">{{ $fmt($ligne['total_debit']) }}</td>
                    <td class="num">{{ $fmt($ligne['total_credit']) }}</td>
                    <td class="num">{{ $net > 0 ? $fmt($net) : '' }}</td>
                    <td class="num">{{ $net < 0 ? $fmt(-$net) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;" class="muet">Aucune écriture validée sur la période.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            @php
                $soldesDebiteurs = $lignes->sum(fn($l) => max(0, $l['total_debit'] - $l['total_credit']));
                $soldesCrediteurs = $lignes->sum(fn($l) => max(0, $l['total_credit'] - $l['total_debit']));
            @endphp
            <tr>
                <td colspan="2">Totaux</td>
                <td class="num">{{ $fmt($totalDebit) }}</td>
                <td class="num">{{ $fmt($totalCredit) }}</td>
                <td class="num">{{ $fmt($soldesDebiteurs) }}</td>
                <td class="num">{{ $fmt($soldesCrediteurs) }}</td>
            </tr>
        </tfoot>
    </table>
@endsection
