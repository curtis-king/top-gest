@extends('layouts.app')

@section('title', 'Balance des comptes - MyGest')

@section('page-title', 'Balance des comptes')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Balance des comptes</h2>
        <a href="{{ route('comptabilite.balance.pdf', request()->query()) }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ Exporter en PDF</a>
    </div>

    <form method="GET" action="{{ route('comptabilite.balance') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <input type="date" name="date_debut" value="{{ $dateDebut }}" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
        <input type="date" name="date_fin" value="{{ $dateFin }}" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
        <select name="agence_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Toutes les agences</option>
            @foreach($agences as $id => $nom)
                <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Filtrer</button>
    </form>

    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
        <table style="width:100%;border-collapse:collapse;">
            <thead>
                <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Numéro</th>
                    <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Libellé</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Total débit</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Total crédit</th>
                    <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Solde</th>
                </tr>
            </thead>
            <tbody>
                @forelse($lignes as $ligne)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:12px 16px;font-size:13px;font-weight:600;color:#f1f5f9;">{{ $ligne['compte']->numero_compte }}</td>
                        <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">{{ $ligne['compte']->libelle }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.65);">{{ number_format($ligne['total_debit'], 0, ',', ' ') }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.65);">{{ number_format($ligne['total_credit'], 0, ',', ' ') }}</td>
                        <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:{{ $ligne['solde'] >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucune donnée pour la période sélectionnée.</td>
                    </tr>
                @endforelse
            </tbody>
            @if($lignes->count() > 0)
            <tfoot>
                <tr style="border-top:2px solid rgba(255,255,255,.1);">
                    <td colspan="2" style="padding:12px 16px;text-align:right;font-weight:600;color:#f1f5f9;font-size:14px;">Total</td>
                    <td style="padding:12px 16px;text-align:right;color:#f1f5f9;font-weight:700;">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                    <td style="padding:12px 16px;text-align:right;color:#f1f5f9;font-weight:700;">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                    <td></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
