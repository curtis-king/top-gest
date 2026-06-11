@extends('layouts.app')

@section('title', 'Utilisateurs - MyGest')

@section('page-title', 'Utilisateurs')

@section('content')
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-size:16px;font-weight:600;color:#f1f5f9;margin:0;">Liste des utilisateurs</h2>
        <a href="{{ route('users.create') }}" style="display:inline-block;padding:9px 18px;background:#2563eb;border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">Nouvel utilisateur</a>
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
                    <a href="{{ $s('name') }}" style="color:{{ $sort == 'name' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Nom{!! $ind('name') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('email') }}" style="color:{{ $sort == 'email' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Email{!! $ind('email') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">
                    <a href="{{ $s('role') }}" style="color:{{ $sort == 'role' ? '#60a5fa' : 'rgba(255,255,255,.4)' }};text-decoration:none;display:inline-flex;align-items:center;">Rôle{!! $ind('role') !!}</a>
                </th>
                <th style="padding:12px 14px;font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.4px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $user->name }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $user->email }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">{{ $user->role }}</td>
                    <td style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.65);border-bottom:1px solid rgba(255,255,255,.04);">
                        <a href="{{ route('users.show', $user) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Voir</a>
                        <a href="{{ route('users.edit', $user) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;margin-right:12px;">Modifier</a>
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Confirmer la suppression ?')" style="background:none;border:none;color:#f87171;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" style="padding:12px 14px;font-size:13px;color:rgba(255,255,255,.4);border-bottom:1px solid rgba(255,255,255,.04);text-align:center;">Aucun utilisateur trouvé.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top:16px;">
        {{ $users->links() }}
    </div>
</div>
</div>
@endsection
