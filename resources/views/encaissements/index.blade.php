@extends('layouts.app')

@section('title', 'Encaissements - MyGest')

@section('page-title', 'Encaissements')

@section('content')
@php
    $champ = 'padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;';
    $fmt = fn($n) => number_format((float) $n, 0, ',', ' ');
    $modes = ['especes' => 'Espèces', 'virement' => 'Virement', 'mobile_money' => 'Mobile money', 'cheque' => 'Chèque', 'carte' => 'Carte', 'credit' => 'Crédit'];
    $types = ['vente' => 'Encaissements clients (ventes)', 'avoir' => 'Remboursements clients (avoirs)', 'achat' => 'Règlements fournisseurs (achats)', 'tous' => 'Tous les règlements'];
@endphp
<div>
    {{-- Filtres --}}
    <form method="GET" action="{{ route('encaissements.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Du</div>
            <input type="date" name="du" value="{{ $du->format('Y-m-d') }}" style="{{ $champ }}color-scheme:dark;"></div>
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Au</div>
            <input type="date" name="au" value="{{ $au->format('Y-m-d') }}" style="{{ $champ }}color-scheme:dark;"></div>
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Nature</div>
            <select name="type" style="{{ $champ }}">
                @foreach($types as $cle => $libelle)
                    <option value="{{ $cle }}" {{ $type === $cle ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
                @endforeach
            </select></div>
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Client / tiers</div>
            <select name="contact_id" style="{{ $champ }}max-width:200px;">
                <option value="" style="color:#000;">Tous</option>
                @foreach($clients as $id => $nom)
                    <option value="{{ $id }}" {{ request('contact_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                @endforeach
            </select></div>
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Moyen</div>
            <select name="mode_paiement" style="{{ $champ }}">
                <option value="" style="color:#000;">Tous</option>
                @foreach($modes as $cle => $libelle)
                    <option value="{{ $cle }}" {{ request('mode_paiement') === $cle ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
                @endforeach
            </select></div>
        @if($agences->isNotEmpty())
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Agence</div>
                <select name="agence_id" style="{{ $champ }}">
                    <option value="" style="color:#000;">Toutes</option>
                    @foreach($agences as $id => $nom)
                        <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                    @endforeach
                </select></div>
        @endif
        <label style="display:flex;align-items:center;gap:6px;font-size:12px;color:rgba(255,255,255,.6);padding-bottom:10px;">
            <input type="checkbox" name="non_comptabilises" value="1" {{ request()->boolean('non_comptabilises') ? 'checked' : '' }}> Non comptabilisés
        </label>
        <button type="submit" class="btn-primary" style="border:none;cursor:pointer;">Filtrer</button>
        <a href="{{ route('encaissements.index') }}" style="color:rgba(255,255,255,.5);font-size:12px;text-decoration:none;padding-bottom:10px;">Réinitialiser</a>
        <a href="{{ route('encaissements.pdf', request()->query()) }}" target="_blank" class="btn-outline" style="text-decoration:none;margin-left:auto;">↓ Exporter en PDF</a>
    </form>

    {{-- Totaux --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(170px,1fr));gap:12px;margin-bottom:20px;">
        <div style="background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.2);border-radius:12px;padding:14px 16px;">
            <div style="font-size:11px;color:rgba(255,255,255,.5);">Total sur la période</div>
            <div style="font-size:22px;font-weight:700;color:#4ade80;">{{ $fmt($total) }} <span style="font-size:12px;">FCFA</span></div>
            <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ $paiements->total() }} règlement(s) · {{ $du->format('d/m/Y') }} → {{ $au->format('d/m/Y') }}</div>
        </div>
        @foreach($parMode as $ligne)
            <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:14px 16px;">
                <div style="font-size:11px;color:rgba(255,255,255,.5);">{{ $modes[$ligne->mode_paiement] ?? ($ligne->mode_paiement ?: 'Non précisé') }}</div>
                <div style="font-size:18px;font-weight:600;color:#f1f5f9;">{{ $fmt($ligne->total) }} <span style="font-size:11px;">FCFA</span></div>
                <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ $ligne->nombre }} règlement(s)</div>
            </div>
        @endforeach
    </div>

    {{-- Liste --}}
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:13px;">
            <thead>
                <tr style="color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;text-align:left;">
                    <th style="padding:12px;">Date</th><th style="padding:12px;">Facture</th><th style="padding:12px;">Client / tiers</th>
                    <th style="padding:12px;">Moyen</th><th style="padding:12px;">Référence</th><th style="padding:12px;text-align:right;">Montant</th>
                    <th style="padding:12px;">Comptabilité</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paiements as $p)
                    @php $f = $p->facture; @endphp
                    <tr style="border-top:1px solid rgba(255,255,255,.05);">
                        <td style="padding:10px 12px;color:rgba(255,255,255,.75);white-space:nowrap;">{{ $p->date_paiement?->format('d/m/Y') }}</td>
                        <td style="padding:10px 12px;">
                            <a href="{{ route('factures.manage', $f) }}" style="color:#60a5fa;text-decoration:none;">{{ $f->numero_facture }}</a>
                            @if($f->type_facture?->value !== 'vente')
                                <span style="font-size:10px;color:#f87171;margin-left:4px;">{{ $f->type_facture?->value }}</span>
                            @endif
                        </td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.75);">{{ $f->contact?->raison_social ?: $f->contact?->nom_complet ?: $f->raison_social ?: '—' }}</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">
                            {{ $modes[$p->mode_paiement] ?? '—' }}
                            @if($p->banque)<div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $p->banque->nom }}</div>@endif
                        </td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.55);">{{ $p->reference ?: '—' }}</td>
                        <td style="padding:10px 12px;text-align:right;font-weight:600;color:{{ $f->type_facture?->value === 'vente' ? '#4ade80' : '#f87171' }};white-space:nowrap;">
                            {{ $f->type_facture?->value === 'vente' ? '' : '− ' }}{{ $fmt($p->montant) }}
                        </td>
                        <td style="padding:10px 12px;">
                            @if(isset($comptabilises[$p->id]))
                                <span style="font-size:12px;color:#4ade80;">{{ $comptabilises[$p->id] }}</span>
                            @else
                                <a href="{{ route('ecritures-comptables.create', ['source_type' => 'paiement_facture', 'source_id' => $p->id]) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;">Comptabiliser</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="padding:28px;text-align:center;color:rgba(255,255,255,.35);">Aucun règlement sur cette période.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top:16px;">{{ $paiements->links() }}</div>
</div>
@endsection
