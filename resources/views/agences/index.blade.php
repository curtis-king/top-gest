@extends('layouts.app')

@section('title', 'Agences - MyGest')

@section('page-title', 'Agences')

@section('content')
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h2 style="font-size:18px;font-weight:600;color:#f1f5f9;">Liste des agences</h2>
            <a href="{{ route('agences.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouvelle agence</a>
        </div>

        @if(session('success'))
            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
                {{ session('success') }}
            </div>
        @endif

        <div style="overflow-x:auto;">
            @php
                $s = fn($col) => request()->fullUrlWithQuery(['sort' => $col, 'direction' => ($sort == $col && $direction == 'asc') ? 'desc' : 'asc']);
                $ind = fn($col) => $sort == $col ? '<span style="font-size:10px;margin-left:3px;">'.($direction == 'asc' ? '&#9650;' : '&#9660;').'</span>' : '';
            @endphp
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('id') }}" style="color:{{ $sort == 'id' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">#{!! $ind('id') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('name_agence') }}" style="color:{{ $sort == 'name_agence' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Nom{!! $ind('name_agence') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('compagnie_id') }}" style="color:{{ $sort == 'compagnie_id' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Compagnie{!! $ind('compagnie_id') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('adresse') }}" style="color:{{ $sort == 'adresse' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Adresse{!! $ind('adresse') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('ville') }}" style="color:{{ $sort == 'ville' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Ville{!! $ind('ville') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('numero_telephone') }}" style="color:{{ $sort == 'numero_telephone' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Téléphone{!! $ind('numero_telephone') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:left;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">
                            <a href="{{ $s('adresse_email') }}" style="color:{{ $sort == 'adresse_email' ? '#60a5fa' : 'rgba(255,255,255,.45)' }};text-decoration:none;display:inline-flex;align-items:center;">Email{!! $ind('adresse_email') !!}</a>
                        </th>
                        <th style="padding:12px 16px;text-align:center;color:rgba(255,255,255,.45);font-weight:500;font-size:12px;text-transform:uppercase;letter-spacing:.4px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agences as $agence)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);transition:background .15s;">
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->id }}</td>
                            <td style="padding:12px 16px;color:#f1f5f9;font-weight:500;">{{ $agence->name_agence }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->compagnie?->name ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->adresse ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->ville ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->numero_telephone ?? '—' }}</td>
                            <td style="padding:12px 16px;color:rgba(255,255,255,.65);">{{ $agence->adresse_email ?? '—' }}</td>
                            <td style="padding:12px 16px;text-align:center;">
                                <div style="display:flex;gap:8px;justify-content:center;">
                                    <a href="{{ route('agences.show', $agence) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;">Voir</a>
                                    <a href="{{ route('agences.edit', $agence) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;">Modifier</a>
                                    <form method="POST" action="{{ route('agences.destroy', $agence) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" style="background:none;border:none;color:#f87171;text-decoration:none;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" style="padding:32px 16px;text-align:center;color:rgba(255,255,255,.3);font-size:14px;">Aucune agence trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $agences->links() }}
        </div>
    </div>
@endsection
