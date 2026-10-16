@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    @forelse($parJournal as $bloc)
        <h3>{{ $bloc['journal']?->code }} — {{ $bloc['journal']?->libelle }}</h3>
        <table class="donnees">
            <thead>
                <tr>
                    <th style="width:62px;">Date</th>
                    <th style="width:95px;">N° écriture</th>
                    <th style="width:60px;">Compte</th>
                    <th>Libellé</th>
                    <th class="num" style="width:85px;">Débit</th>
                    <th class="num" style="width:85px;">Crédit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bloc['ecritures'] as $e)
                    @foreach($e->lignes as $i => $l)
                        <tr>
                            <td>{{ $i === 0 ? $e->date_ecriture?->format('d/m/Y') : '' }}</td>
                            <td>{{ $i === 0 ? $e->numero_ecriture : '' }}</td>
                            <td>{{ $l->compte?->numero_compte }}</td>
                            <td>{{ $l->libelle ?: $e->libelle }}</td>
                            <td class="num">{{ (float) $l->debit ? $fmt($l->debit) : '' }}</td>
                            <td class="num">{{ (float) $l->credit ? $fmt($l->credit) : '' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4">Total {{ $bloc['journal']?->code }} ({{ $bloc['ecritures']->count() }} écriture(s))</td>
                    <td class="num">{{ $fmt($bloc['debit']) }}</td>
                    <td class="num">{{ $fmt($bloc['credit']) }}</td>
                </tr>
            </tfoot>
        </table>
    @empty
        <p class="muet">Aucune écriture validée sur la période.</p>
    @endforelse

    @if($parJournal->count() > 1)
        <table class="donnees" style="margin-top:10px;">
            <tr class="sous-total">
                <td>Total général</td>
                <td class="num" style="width:85px;">{{ $fmt($parJournal->sum('debit')) }}</td>
                <td class="num" style="width:85px;">{{ $fmt($parJournal->sum('credit')) }}</td>
            </tr>
        </table>
    @endif
@endsection
