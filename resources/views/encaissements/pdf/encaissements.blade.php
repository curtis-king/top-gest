@extends('pdf.rapport')

@section('contenu')
    @php
        $fmt = fn($n) => number_format((float) $n, 0, ',', ' ');
        $modes = \App\Http\Controllers\EncaissementController::MODES;
    @endphp

    <div class="muet" style="margin-bottom:8px;">{{ $nature }}</div>

    <table class="cartes">
        <tr>
            <td><div class="libelle">Total</div><div class="valeur">{{ $fmt($total) }}</div><div class="muet">{{ $paiements->count() }} règlement(s)</div></td>
            @foreach($parMode as $ligne)
                <td><div class="libelle">{{ $modes[$ligne->mode_paiement] ?? ($ligne->mode_paiement ?: 'Non précisé') }}</div>
                    <div class="valeur">{{ $fmt($ligne->total) }}</div><div class="muet">{{ $ligne->nombre }} règlement(s)</div></td>
            @endforeach
        </tr>
    </table>

    <table class="donnees">
        <thead>
            <tr>
                <th style="width:62px;">Date</th>
                <th style="width:90px;">Facture</th>
                <th>Client / tiers</th>
                <th style="width:90px;">Moyen</th>
                <th style="width:90px;">Banque</th>
                <th style="width:100px;">Référence</th>
                <th style="width:80px;">Agence</th>
                <th class="num" style="width:85px;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paiements as $p)
                @php $f = $p->facture; @endphp
                <tr>
                    <td>{{ $p->date_paiement?->format('d/m/Y') }}</td>
                    <td>{{ $f->numero_facture }}@if($f->type_facture?->value !== 'vente') <span class="negatif">({{ $f->type_facture?->value }})</span>@endif</td>
                    <td>{{ \App\Http\Controllers\EncaissementController::nomTiers($f) }}</td>
                    <td>{{ $modes[$p->mode_paiement] ?? '—' }}</td>
                    <td>{{ $p->banque?->nom }}</td>
                    <td>{{ $p->reference }}</td>
                    <td>{{ $f->agence?->name_agence }}</td>
                    <td class="num">{{ $fmt($p->montant) }}</td>
                </tr>
            @empty
                <tr><td colspan="8" style="text-align:center;" class="muet">Aucun règlement sur la période.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr><td colspan="7">Total</td><td class="num">{{ $fmt($total) }}</td></tr>
        </tfoot>
    </table>
@endsection
