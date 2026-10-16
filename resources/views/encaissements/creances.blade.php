@extends('layouts.app')

@section('title', 'Créances clients - MyGest')

@section('page-title', 'Créances clients')

@section('content')
@php
    $champ = 'padding:9px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;';
    $fmt = fn($n) => number_format((float) $n, 0, ',', ' ');
    $couleurs = ['0-30' => '#4ade80', '31-60' => '#facc15', '61-90' => '#fb923c', '90+' => '#f87171'];
@endphp
<div>
    <div style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:16px;">
        Factures de vente validées qui restent dues (TTC, après paiements et avoirs), classées par ancienneté depuis la date de facture.
    </div>

    {{-- Filtres --}}
    <form method="GET" action="{{ route('creances.index') }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;margin-bottom:20px;">
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Client</div>
            <select name="contact_id" style="{{ $champ }}max-width:220px;">
                <option value="" style="color:#000;">Tous</option>
                @foreach($clients as $id => $nom)
                    <option value="{{ $id }}" {{ request('contact_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                @endforeach
            </select></div>
        <div><div style="font-size:11px;color:rgba(255,255,255,.4);margin-bottom:4px;">Ancienneté</div>
            <select name="tranche" style="{{ $champ }}">
                <option value="" style="color:#000;">Toutes</option>
                @foreach(\App\Http\Controllers\EncaissementController::TRANCHES as $cle => $libelle)
                    <option value="{{ $cle }}" {{ request('tranche') === $cle ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
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
        <button type="submit" class="btn-primary" style="border:none;cursor:pointer;">Filtrer</button>
        <a href="{{ route('creances.index') }}" style="color:rgba(255,255,255,.5);font-size:12px;text-decoration:none;padding-bottom:10px;">Réinitialiser</a>
        <a href="{{ route('creances.pdf', request()->query()) }}" target="_blank" class="btn-outline" style="text-decoration:none;margin-left:auto;">↓ Balance âgée (PDF)</a>
    </form>

    {{-- Balance âgée --}}
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:12px;margin-bottom:20px;">
        <div style="background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.2);border-radius:12px;padding:14px 16px;">
            <div style="font-size:11px;color:rgba(255,255,255,.5);">Total dû par les clients</div>
            <div style="font-size:22px;font-weight:700;color:#f59e0b;">{{ $fmt($total) }} <span style="font-size:12px;">FCFA</span></div>
            <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ $factures->count() }} facture(s)</div>
        </div>
        @foreach($parTranche as $cle => $t)
            <a href="{{ route('creances.index', array_merge(request()->query(), ['tranche' => $cle])) }}" style="text-decoration:none;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-left:3px solid {{ $couleurs[$cle] }};border-radius:12px;padding:14px 16px;">
                <div style="font-size:11px;color:rgba(255,255,255,.5);">{{ $t['libelle'] }}</div>
                <div style="font-size:18px;font-weight:600;color:{{ $couleurs[$cle] }};">{{ $fmt($t['montant']) }} <span style="font-size:11px;">FCFA</span></div>
                <div style="font-size:11px;color:rgba(255,255,255,.4);">{{ $t['nombre'] }} facture(s)</div>
            </a>
        @endforeach
    </div>

    <div style="display:grid;grid-template-columns:minmax(0,2fr) minmax(240px,1fr);gap:16px;align-items:start;">
        {{-- Factures dues --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;text-align:left;">
                        <th style="padding:12px;">Facture</th><th style="padding:12px;">Client</th><th style="padding:12px;">Date</th>
                        <th style="padding:12px;text-align:right;">TTC</th><th style="padding:12px;text-align:right;">Reste dû</th><th style="padding:12px;">Ancienneté</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($factures as $f)
                        <tr style="border-top:1px solid rgba(255,255,255,.05);">
                            <td style="padding:10px 12px;"><a href="{{ route('factures.manage', $f) }}" style="color:#60a5fa;text-decoration:none;">{{ $f->numero_facture }}</a></td>
                            <td style="padding:10px 12px;color:rgba(255,255,255,.75);">{{ $f->contact?->raison_social ?: $f->contact?->nom_complet ?: $f->raison_social ?: '—' }}</td>
                            <td style="padding:10px 12px;color:rgba(255,255,255,.6);white-space:nowrap;">{{ $f->date_facture?->format('d/m/Y') }}</td>
                            <td style="padding:10px 12px;text-align:right;color:rgba(255,255,255,.6);white-space:nowrap;">{{ $fmt($f->total_ttc) }}</td>
                            <td style="padding:10px 12px;text-align:right;font-weight:600;color:#f59e0b;white-space:nowrap;">{{ $fmt($f->reste) }}</td>
                            <td style="padding:10px 12px;white-space:nowrap;"><span style="font-size:12px;color:{{ $couleurs[$f->tranche] }};">{{ $f->age }} j</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="padding:28px;text-align:center;color:rgba(255,255,255,.35);">Aucune créance : toutes les factures validées sont réglées.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="display:flex;flex-direction:column;gap:16px;">
            {{-- Par client --}}
            <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:16px;">
                <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.4px;margin-bottom:10px;">Par client</div>
                @forelse($parClient as $nom => $c)
                    <div style="display:flex;justify-content:space-between;gap:8px;padding:6px 0;border-top:1px solid rgba(255,255,255,.04);font-size:13px;">
                        <span style="color:rgba(255,255,255,.75);">
                            {{ $nom }} <span style="color:rgba(255,255,255,.35);font-size:11px;">({{ $c['nombre'] }})</span>
                            @if($c['contact_id'])
                                <a href="{{ route('releves.pdf', $c['contact_id']) }}" target="_blank" style="color:#60a5fa;text-decoration:none;font-size:11px;margin-left:4px;">relevé</a>
                            @endif
                        </span>
                        <span style="color:#f59e0b;font-weight:600;white-space:nowrap;">{{ $fmt($c['montant']) }}</span>
                    </div>
                @empty
                    <div style="font-size:12px;color:rgba(255,255,255,.35);">—</div>
                @endforelse
            </div>

            {{-- Remboursements dus --}}
            <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:16px;">
                <div style="font-size:12px;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.4px;margin-bottom:4px;">Remboursements à faire</div>
                <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:10px;">Avoirs sur des factures déjà payées (trop-perçu).</div>
                @forelse($remboursements as $a)
                    <div style="display:flex;justify-content:space-between;gap:8px;padding:6px 0;border-top:1px solid rgba(255,255,255,.04);font-size:13px;">
                        <a href="{{ route('factures.manage', $a) }}" style="color:#60a5fa;text-decoration:none;">{{ $a->numero_facture }}</a>
                        <span style="color:#f87171;font-weight:600;white-space:nowrap;">{{ $fmt($a->reste) }}</span>
                    </div>
                @empty
                    <div style="font-size:12px;color:rgba(255,255,255,.35);">Aucun.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
