@extends('layouts.app')

@section('title', 'Utilisateurs - MyGest')
@section('page-title', 'Utilisateurs')

@section('content')
@php
$scopeLabels = [
    'dg'          => 'Directeur Général',
    'chef-agence' => 'Chef d\'agence',
    'agent'       => 'Agent',
    'employe'     => 'Employé',
];
$scopeColors = [
    'dg'          => ['bg' => 'rgba(99,102,241,.15)', 'text' => '#818cf8'],
    'chef-agence' => ['bg' => 'rgba(59,130,246,.15)', 'text' => '#60a5fa'],
    'agent'       => ['bg' => 'rgba(74,222,128,.12)', 'text' => '#4ade80'],
    'employe'     => ['bg' => 'rgba(148,163,184,.12)', 'text' => '#94a3b8'],
];
$roleColors = [
    'admin-systeme'       => 'rgba(248,113,113,.15)',
    'dg'                  => 'rgba(167,139,250,.15)',
    'chef-agence'         => 'rgba(96,165,250,.15)',
    'responsable-rh'      => 'rgba(52,211,153,.12)',
    'assistant-rh'        => 'rgba(52,211,153,.08)',
    'responsable-projets' => 'rgba(251,191,36,.12)',
    'assistant-projets'   => 'rgba(251,191,36,.08)',
    'responsable-finance' => 'rgba(245,158,11,.12)',
    'assistant-finance'   => 'rgba(245,158,11,.08)',
    'responsable-stock'   => 'rgba(6,182,212,.12)',
    'assistant-stock'     => 'rgba(6,182,212,.08)',
    'responsable-archives'=> 'rgba(168,85,247,.12)',
    'assistant-archives'  => 'rgba(168,85,247,.08)',
];
@endphp

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">

    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;gap:12px;flex-wrap:wrap;">
        <h2 style="font-size:16px;font-weight:600;color:#f1f5f9;margin:0;">
            Liste des utilisateurs
            <span style="font-size:12px;font-weight:400;color:rgba(255,255,255,.35);margin-left:8px;">{{ $users->total() }} compte(s)</span>
        </h2>
        <a href="{{ route('users.create') }}" style="display:inline-block;padding:9px 18px;background:#2563eb;border-radius:8px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;">+ Nouvel utilisateur</a>
    </div>

    <form id="userSearchForm" method="GET" action="{{ route('users.index') }}" style="margin-bottom:20px;">
        <div style="position:relative;max-width:360px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;position:absolute;left:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,.3);pointer-events:none;"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input id="userSearch" type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou email…" autocomplete="off" style="width:100%;padding:10px 14px 10px 40px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
        </div>
    </form>

    @if(session('success'))
        <div style="padding:12px 16px;background:rgba(74,222,128,.08);border:1px solid rgba(74,222,128,.15);border-radius:10px;color:#4ade80;font-size:13px;margin-bottom:20px;">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="padding:12px 16px;background:rgba(248,113,113,.08);border:1px solid rgba(248,113,113,.15);border-radius:10px;color:#f87171;font-size:13px;margin-bottom:20px;">
            {{ session('error') }}
        </div>
    @endif

    <div style="overflow-x:auto;">
    <table style="border-collapse:collapse;width:100%;">
        <thead>
            <tr>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Nom</th>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Email</th>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Agence</th>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Portée</th>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Rôle</th>
                <th style="padding:12px 14px;font-size:11px;color:rgba(255,255,255,.35);text-transform:uppercase;letter-spacing:.5px;text-align:left;border-bottom:1px solid rgba(255,255,255,.06);">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            @php
                $userRoles = $user->roles->pluck('name');
                $scopeInfo = $scopeColors[$user->scope] ?? $scopeColors['agent'];
                $isSelf    = $user->id === auth()->id();
            @endphp
            <tr style="transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background='transparent'">
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);">
                    <div style="font-size:13px;font-weight:500;color:#f1f5f9;">
                        {{ $user->name }}
                        @if($isSelf)
                            <span style="font-size:10px;color:rgba(255,255,255,.3);margin-left:6px;">(vous)</span>
                        @endif
                    </div>
                </td>
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;color:rgba(255,255,255,.55);">
                    {{ $user->email }}
                </td>
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;color:rgba(255,255,255,.55);">
                    {{ $user->agence?->name_agence ?? ($user->scope === 'dg' ? '— toutes —' : '—') }}
                </td>
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);">
                    <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;background:{{ $scopeInfo['bg'] }};color:{{ $scopeInfo['text'] }};">
                        {{ $scopeLabels[$user->scope] ?? $user->scope }}
                    </span>
                </td>
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);">
                    @if($userRoles->count())
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                        @foreach($userRoles as $rn)
                            <span style="display:inline-block;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $roleColors[$rn] ?? 'rgba(148,163,184,.1)' }};color:#f1f5f9;">
                                {{ $rn }}
                            </span>
                        @endforeach
                        </div>
                    @else
                        <span style="font-size:12px;color:rgba(255,255,255,.25);">—</span>
                    @endif
                </td>
                <td style="padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04);">
                    <div style="display:flex;gap:12px;align-items:center;">
                        <a href="{{ route('users.edit', $user) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;font-weight:500;">Modifier</a>
                        @if(!$isSelf)
                        <form action="{{ route('users.destroy', $user) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" onclick="return confirm('Supprimer {{ addslashes($user->name) }} ?')" style="background:none;border:none;color:#f87171;font-size:12px;font-weight:500;cursor:pointer;padding:0;font-family:inherit;">Supprimer</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="padding:40px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucun utilisateur trouvé.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    </div>

    <div style="margin-top:20px;">{{ $users->links() }}</div>
</div>

@push('scripts')
<script>
(function () {
    const form  = document.getElementById('userSearchForm');
    const input = document.getElementById('userSearch');
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => form.submit(), 350);
    });
})();
</script>
@endpush
@endsection
