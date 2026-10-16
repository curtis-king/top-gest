@extends('pdf.rapport')

@section('contenu')
    @php $fmt = fn($n) => number_format((float) $n, 0, ',', ' '); @endphp

    <table style="width:100%;margin-bottom:12px;">
        <tr>
            <td style="width:55%;"></td>
            <td style="border:1px solid #94a3b8;padding:8px 10px;">
                <strong style="font-size:11px;">{{ $contact->raison_social ?: $contact->nom_complet }}</strong><br>
                @if($contact->niu)NIU : {{ $contact->niu }}<br>@endif
                @if($contact->adresse){{ $contact->adresse }}<br>@endif
                @if($contact->telephone)Tél. : {{ $contact->telephone }}@endif
                @if($contact->adresse_email) · {{ $contact->adresse_email }}@endif
            </td>
        </tr>
    </table>

    <table class="donnees">
        <thead>
            <tr>
                <th style="width:62px;">Date</th>
                <th style="width:90px;">Pièce</th>
                <th>Libellé</th>
                <th class="num" style="width:85px;">Débit</th>
                <th class="num" style="width:85px;">Crédit</th>
                <th class="num" style="width:90px;">Solde</th>
            </tr>
        </thead>
        <tbody>
            @if($du)
                <tr class="sous-total">
                    <td>{{ \Illuminate\Support\Carbon::parse($du)->format('d/m/Y') }}</td>
                    <td></td>
                    <td>Solde à l'ouverture de la période</td>
                    <td></td><td></td>
                    <td class="num">{{ $fmt($soldeOuverture) }}</td>
                </tr>
            @endif
            @forelse($lignes as $l)
                <tr>
                    <td>{{ $l['date']?->format('d/m/Y') }}</td>
                    <td>{{ $l['piece'] }}</td>
                    <td>{{ $l['libelle'] }}</td>
                    <td class="num">{{ $l['debit'] ? $fmt($l['debit']) : '' }}</td>
                    <td class="num">{{ $l['credit'] ? $fmt($l['credit']) : '' }}</td>
                    <td class="num">{{ $fmt($l['solde']) }}</td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;" class="muet">Aucun mouvement sur la période.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3">Totaux de la période</td>
                <td class="num">{{ $fmt($lignes->sum('debit')) }}</td>
                <td class="num">{{ $fmt($lignes->sum('credit')) }}</td>
                <td class="num">{{ $fmt($soldeFinal) }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="donnees" style="margin-top:8px;">
        <tr class="sous-total">
            <td>
                @if($soldeFinal > 0.01)
                    Solde dû par le client
                @elseif($soldeFinal < -0.01)
                    Solde en faveur du client (à rembourser)
                @else
                    Compte soldé
                @endif
            </td>
            <td class="num {{ $soldeFinal > 0.01 ? 'negatif' : 'positif' }}" style="width:160px;font-size:12px;">{{ $fmt(abs($soldeFinal)) }} FCFA</td>
        </tr>
    </table>

    @if($dues->isNotEmpty())
        <h3>Factures restant dues</h3>
        <table class="donnees">
            <thead><tr><th style="width:90px;">Facture</th><th style="width:62px;">Date</th><th>Objet</th><th class="num" style="width:85px;">TTC</th><th class="num" style="width:85px;">Reste dû</th></tr></thead>
            <tbody>
                @foreach($dues as $f)
                    <tr>
                        <td>{{ $f->numero_facture }}</td>
                        <td>{{ $f->date_facture?->format('d/m/Y') }}</td>
                        <td>{{ $f->objet }}</td>
                        <td class="num">{{ $fmt($f->total_ttc) }}</td>
                        <td class="num">{{ $fmt($f->reste) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <p class="muet" style="margin-top:14px;">Débit : sommes facturées ou remboursées au client. Crédit : avoirs et règlements reçus. Pour toute question sur ce relevé, merci de nous contacter.</p>
@endsection
