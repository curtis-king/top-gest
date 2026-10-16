@extends('pdf.rapport')

@section('contenu')
    @php
        $fmt = fn($n) => number_format((float) $n, 0, ',', ' ');
        $tranches = \App\Http\Controllers\EncaissementController::TRANCHES;
    @endphp

    <table class="cartes">
        <tr>
            <td><div class="libelle">Total dû</div><div class="valeur">{{ $fmt($total) }}</div><div class="muet">{{ $factures->count() }} facture(s)</div></td>
            @foreach($parTranche as $t)
                <td><div class="libelle">{{ $t['libelle'] }}</div><div class="valeur">{{ $fmt($t['montant']) }}</div><div class="muet">{{ $t['nombre'] }} facture(s)</div></td>
            @endforeach
        </tr>
    </table>

    <h3>Balance âgée par client</h3>
    <table class="donnees">
        <thead>
            <tr>
                <th>Client</th>
                @foreach($tranches as $libelle)
                    <th class="num" style="width:85px;">{{ $libelle }}</th>
                @endforeach
                <th class="num" style="width:95px;">Total dû</th>
            </tr>
        </thead>
        <tbody>
            @forelse($parClient as $nom => $c)
                <tr>
                    <td>{{ $nom }} <span class="muet">({{ $c['nombre'] }})</span></td>
                    @foreach($tranches as $cle => $libelle)
                        <td class="num">{{ $c['tranches'][$cle] > 0 ? $fmt($c['tranches'][$cle]) : '' }}</td>
                    @endforeach
                    <td class="num"><strong>{{ $fmt($c['montant']) }}</strong></td>
                </tr>
            @empty
                <tr><td colspan="{{ count($tranches) + 2 }}" style="text-align:center;" class="muet">Aucune créance.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td>Total</td>
                @foreach($parTranche as $t)
                    <td class="num">{{ $fmt($t['montant']) }}</td>
                @endforeach
                <td class="num">{{ $fmt($total) }}</td>
            </tr>
        </tfoot>
    </table>

    <h3>Détail des factures dues</h3>
    <table class="donnees">
        <thead>
            <tr>
                <th style="width:90px;">Facture</th>
                <th>Client</th>
                <th style="width:62px;">Date</th>
                <th class="num" style="width:50px;">Âge</th>
                <th class="num" style="width:85px;">TTC</th>
                <th class="num" style="width:85px;">Reste dû</th>
            </tr>
        </thead>
        <tbody>
            @forelse($factures as $f)
                <tr>
                    <td>{{ $f->numero_facture }}</td>
                    <td>{{ \App\Http\Controllers\EncaissementController::nomTiers($f) }}</td>
                    <td>{{ $f->date_facture?->format('d/m/Y') }}</td>
                    <td class="num">{{ $f->age }} j</td>
                    <td class="num">{{ $fmt($f->total_ttc) }}</td>
                    <td class="num">{{ $fmt($f->reste) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;" class="muet">Aucune facture due.</td></tr>
            @endforelse
        </tbody>
    </table>

    @if($remboursements->isNotEmpty())
        <h3>Remboursements à faire (avoirs sur factures déjà payées)</h3>
        <table class="donnees">
            <thead><tr><th style="width:90px;">Avoir</th><th>Client</th><th style="width:90px;">Facture d'origine</th><th class="num" style="width:95px;">À rembourser</th></tr></thead>
            <tbody>
                @foreach($remboursements as $a)
                    <tr>
                        <td>{{ $a->numero_facture }}</td>
                        <td>{{ \App\Http\Controllers\EncaissementController::nomTiers($a) }}</td>
                        <td>{{ $a->factureOrigine?->numero_facture }}</td>
                        <td class="num">{{ $fmt($a->reste) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
@endsection
