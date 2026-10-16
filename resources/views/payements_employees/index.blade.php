@extends('layouts.app')

@section('title', 'Paiements employés - MyGest')

@section('page-title', 'Paiements employés')

@section('content')
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-size:16px;font-weight:600;color:#f1f5f9;margin:0;">Liste des paiements</h2>
        <a href="{{ route('payements-employees.create') }}" style="display:inline-block;padding:9px 18px;background:#2563eb;border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">Nouveau paiement</a>
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
                    <a href="{{ $s('mois') }}" style="color:{{ $sort == 'mois' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Mois{!! $ind('mois') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('annee') }}" style="color:{{ $sort == 'annee' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Année{!! $ind('annee') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('salaire_base') }}" style="color:{{ $sort == 'salaire_base' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Salaire base{!! $ind('salaire_base') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('net_a_payer') }}" style="color:{{ $sort == 'net_a_payer' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Net à payer{!! $ind('net_a_payer') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('status') }}" style="color:{{ $sort == 'status' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Statut{!! $ind('status') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payements as $payement)
                <tr>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $payement->employee->nom_complet }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $payement->mois }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $payement->annee }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ number_format($payement->salaire_base, 0, ',', ' ') }} FCFA</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ number_format($payement->net_a_payer, 0, ',', ' ') }} FCFA</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">
                        @php
                            $sv = $payement->status?->value ?? '—';
                            $sc = ['en_attente'=>'#facc15','valide'=>'#4ade80','paye'=>'#3b82f6','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
                        @endphp
                        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:{{$sc}}22;color:{{$sc}};">{{ $sv }}</span>
                    </td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">
                        <a href="{{ route('payements-employees.show', $payement) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Voir</a>
                        <a href="{{ route('payements-employees.edit', $payement) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Modifier</a>
                        <form action="{{ route('payements-employees.destroy', $payement) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Confirmer la suppression ?')" style="background:none;border:none;color:#f87171;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.4);border-bottom:1px solid rgba(255,255,255,.04);text-align:center;">Aucun paiement trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;">
        {{ $payements->links() }}
    </div>
</div>
</div>
@endsection
