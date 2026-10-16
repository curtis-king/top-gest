@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    @forelse($comptes as $bloc)
        <h3>{{ $bloc['compte']->numero_compte }} — {{ $bloc['compte']->libelle }}</h3>
        <table class="donnees">
            <thead>
                <tr>
                    <th style="width:62px;">Date</th>
                    <th style="width:36px;">Jnl</th>
                    <th style="width:95px;">N° écriture</th>
                    <th>Libellé</th>
                    <th style="width:110px;">Tiers</th>
                    <th class="num" style="width:80px;">Débit</th>
                    <th class="num" style="width:80px;">Crédit</th>
                    <th class="num" style="width:85px;">Solde</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloc['lignes'] as $l)
                    <tr>
                        <td>{{ $l->ecriture->date_ecriture?->format('d/m/Y') }}</td>
                        <td>{{ $l->ecriture->journal?->code }}</td>
                        <td>{{ $l->ecriture->numero_ecriture }}</td>
                        <td>{{ $l->libelle ?: $l->ecriture->libelle }}</td>
                        <td>{{ $l->contact?->raison_social ?: $l->contact?->nom_complet }}</td>
                        <td class="num">{{ (float) $l->debit ? $fmt($l->debit) : '' }}</td>
                        <td class="num">{{ (float) $l->credit ? $fmt($l->credit) : '' }}</td>
                        <td class="num">{{ $fmt($l->solde_cumule) }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5">Total {{ $bloc['compte']->numero_compte }} ({{ $bloc['lignes']->count() }} mouvement(s))</td>
                    <td class="num">{{ $fmt($bloc['total_debit']) }}</td>
                    <td class="num">{{ $fmt($bloc['total_credit']) }}</td>
                    <td class="num">{{ $fmt($bloc['solde']) }}</td>
                </tr>
            </tfoot>
        </table>
    @empty
        <p class="muet">Aucun mouvement validé sur la période.</p>
    @endforelse
@endsection
