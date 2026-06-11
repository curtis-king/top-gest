@extends('layouts.app')

@section('title', 'Retenus - MyGest')

@section('page-title', 'Retenus')

@section('content')
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-size:16px;font-weight:600;color:#f1f5f9;margin:0;">Liste des retenus</h2>
        <a href="{{ route('retenus.create') }}" style="display:inline-block;padding:9px 18px;background:#2563eb;border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">Nouvelle retenue</a>
    </div>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(34,197,94,.08);border:1px solid rgba(34,197,94,.15);border-radius:10px;color:#4ade80;font-size:13px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif

    @php
        $s = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'direction' => ($sort == $col && $direction == 'asc') ? 'desc' : 'asc']);
        $ind = fn($col) => $sort == $col ? '<span style="font-size:10px;margin-left:3px;">'.($direction == 'asc' ? '&#9650;' : '&#9660;').'</span>' : '';
    @endphp
    <div style="overflow-x:auto;">
    <table style="border-collapse:collapse;width:100%;">
        <thead>
            <tr>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('employee_id') }}" style="color:{{ $sort == 'employee_id' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Employé{!! $ind('employee_id') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('date_retenu') }}" style="color:{{ $sort == 'date_retenu' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Date{!! $ind('date_retenu') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('motif') }}" style="color:{{ $sort == 'motif' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Motif{!! $ind('motif') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('montant') }}" style="color:{{ $sort == 'montant' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Montant{!! $ind('montant') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($retenus as $retenu)
                <tr>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $retenu->employee->nom_complet }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $retenu->date_retenu->format('d/m/Y') }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $retenu->motif }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ number_format($retenu->montant, 0, ',', ' ') }} FCFA</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">
                        <a href="{{ route('retenus.show', $retenu) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Voir</a>
                        <a href="{{ route('retenus.edit', $retenu) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Modifier</a>
                        <form action="{{ route('retenus.destroy', $retenu) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Confirmer la suppression ?')" style="background:none;border:none;color:#f87171;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.4);border-bottom:1px solid rgba(255,255,255,.04);text-align:center;">Aucune retenue trouvée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;">
        {{ $retenus->links() }}
    </div>
</div>
</div>
@endsection
