@extends('layouts.app')

@section('title', 'Compte de résultat - MyGest')

@section('page-title', 'Compte de résultat')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Compte de résultat</h2>
        <a href="{{ route('comptabilite.resultat.pdf', request()->query()) }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ Exporter en PDF</a>
    </div>

    <form method="GET" action="{{ route('comptabilite.resultat') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
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

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">
        {{-- CHARGES --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Charges</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <tbody>
                    @forelse($charges as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:8px 4px;color:#f1f5f9;">{{ $ligne['compte']->numero_compte }} - {{ $ligne['compte']->libelle }}</td>
                            <td style="padding:8px 4px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:20px;text-align:center;color:rgba(255,255,255,.3);">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td style="padding:10px 4px;font-weight:600;color:#f1f5f9;">Total charges</td>
                        <td style="padding:10px 4px;text-align:right;font-weight:700;color:#f87171;">{{ number_format($totalCharges, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PRODUITS --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Produits</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <tbody>
                    @forelse($produits as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:8px 4px;color:#f1f5f9;">{{ $ligne['compte']->numero_compte }} - {{ $ligne['compte']->libelle }}</td>
                            <td style="padding:8px 4px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($ligne['montant'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:20px;text-align:center;color:rgba(255,255,255,.3);">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td style="padding:10px 4px;font-weight:600;color:#f1f5f9;">Total produits</td>
                        <td style="padding:10px 4px;text-align:right;font-weight:700;color:#4ade80;">{{ number_format($totalProduits, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div style="background:{{ $resultatNet >= 0 ? 'rgba(74,222,128,.08)' : 'rgba(248,113,113,.08)' }};border:1px solid {{ $resultatNet >= 0 ? 'rgba(74,222,128,.2)' : 'rgba(248,113,113,.2)' }};border-radius:14px;padding:24px;text-align:center;">
        <div style="font-size:12px;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">Résultat net de l'exercice ({{ $resultatNet >= 0 ? 'Bénéfice' : 'Perte' }})</div>
        <div style="font-size:32px;font-weight:700;color:{{ $resultatNet >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($resultatNet, 0, ',', ' ') }} FCFA</div>
    </div>
</div>
@endsection
