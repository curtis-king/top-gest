@extends('layouts.app')

@section('title', 'Grand livre - MyGest')

@section('page-title', 'Grand livre')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">
            @if($compte)
                Grand livre &mdash; {{ $compte->numero_compte }} {{ $compte->libelle }}
            @else
                Grand livre
            @endif
        </h2>
    </div>

    <form method="GET" action="{{ route('comptabilite.grand-livre') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
        <select name="compte_comptable_id" required style="flex:1;min-width:240px;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Sélectionner un compte</option>
            @foreach($comptes as $c)
                <option value="{{ $c->id }}" {{ request('compte_comptable_id') == $c->id ? 'selected' : '' }} style="color:#000;">{{ $c->numero_compte }} - {{ $c->libelle }}</option>
            @endforeach
        </select>
        <input type="date" name="date_debut" value="{{ $dateDebut }}" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
        <input type="date" name="date_fin" value="{{ $dateFin }}" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
        <select name="agence_id" style="padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <option value="" style="color:#000;">Toutes les agences</option>
            @foreach($agences as $id => $nom)
                <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:10px 20px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Afficher</button>
    </form>

    @if($compte)
        <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Date</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Journal</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Libellé</th>
                        <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Contact</th>
                        <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Débit</th>
                        <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Crédit</th>
                        <th style="padding:12px 16px;text-align:right;font-size:11px;font-weight:600;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;">Solde cumulé</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($lignes as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $ligne->ecriture?->date_ecriture ? \Carbon\Carbon::parse($ligne->ecriture->date_ecriture)->format('d/m/Y') : '—' }}</td>
                            <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $ligne->ecriture?->journal?->code }}</td>
                            <td style="padding:12px 16px;font-size:13px;color:rgba(255,255,255,.65);">{{ $ligne->libelle ?? $ligne->ecriture?->libelle }}</td>
                            <td style="padding:12px 16px;font-size:12px;color:rgba(255,255,255,.45);">{{ $ligne->contact?->raison_social ?? '—' }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.65);">{{ $ligne->debit > 0 ? number_format($ligne->debit, 0, ',', ' ') : '' }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;color:rgba(255,255,255,.65);">{{ $ligne->credit > 0 ? number_format($ligne->credit, 0, ',', ' ') : '' }}</td>
                            <td style="padding:12px 16px;text-align:right;font-size:13px;font-weight:600;color:{{ $ligne->solde_cumule >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($ligne->solde_cumule, 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="padding:40px;text-align:center;font-size:14px;color:rgba(255,255,255,.3);">Aucune ligne trouvée pour ce compte.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @else
        <div style="padding:40px;text-align:center;background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;">
            <div style="font-size:14px;color:rgba(255,255,255,.3);">Sélectionnez un compte pour afficher son grand livre.</div>
        </div>
    @endif
</div>
@endsection
