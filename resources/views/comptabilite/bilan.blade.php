@extends('layouts.app')

@section('title', 'Bilan - MyGest')

@section('page-title', 'Bilan')

@section('content')
<div style="margin-bottom:20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
        <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Bilan</h2>
        <a href="{{ route('comptabilite.bilan.pdf', request()->query()) }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ Exporter en PDF</a>
    </div>

    <form method="GET" action="{{ route('comptabilite.bilan') }}" style="display:flex;gap:12px;margin-bottom:20px;flex-wrap:wrap;">
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

    @if(round($totalActif, 2) !== round($totalPassif, 2))
        <div style="padding:12px 16px;background:rgba(245,158,11,.1);border:1px solid rgba(245,158,11,.2);border-radius:8px;color:#f59e0b;font-size:13px;font-weight:500;margin-bottom:20px;">
            Le bilan n'est pas équilibré — vérifiez les écritures non validées.
        </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
        {{-- ACTIF --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Actif</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <tbody>
                    @forelse($actif as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:8px 4px;color:#f1f5f9;">{{ $ligne['compte']->numero_compte }} - {{ $ligne['compte']->libelle }}</td>
                            <td style="padding:8px 4px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:20px;text-align:center;color:rgba(255,255,255,.3);">Aucune donnée.</td></tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td style="padding:10px 4px;font-weight:600;color:#f1f5f9;">Total actif</td>
                        <td style="padding:10px 4px;text-align:right;font-weight:700;color:#4ade80;">{{ number_format($totalActif, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- PASSIF --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Passif</div>
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <tbody>
                    @forelse($passif as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:8px 4px;color:#f1f5f9;">{{ $ligne['compte']->numero_compte }} - {{ $ligne['compte']->libelle }}</td>
                            <td style="padding:8px 4px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($ligne['solde'], 0, ',', ' ') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="2" style="padding:20px;text-align:center;color:rgba(255,255,255,.3);">Aucune donnée.</td></tr>
                    @endforelse
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:8px 4px;color:#f1f5f9;">Résultat de l'exercice</td>
                        <td style="padding:8px 4px;text-align:right;color:{{ $resultat >= 0 ? '#4ade80' : '#f87171' }};">{{ number_format($resultat, 0, ',', ' ') }}</td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td style="padding:10px 4px;font-weight:600;color:#f1f5f9;">Total passif</td>
                        <td style="padding:10px 4px;text-align:right;font-weight:700;color:#4ade80;">{{ number_format($totalPassif, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
