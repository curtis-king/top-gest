@extends('layouts.app')

@section('title', 'Modifier — {{ $user->name }} - MyGest')
@section('page-title', 'Modifier un utilisateur')

@section('content')
@php
$scopeLabels = [
    'dg'          => 'Directeur Général — toutes agences, tous domaines',
    'chef-agence' => 'Chef d\'agence — son agence, tous domaines',
    'agent'       => 'Agent — son agence, son domaine uniquement',
    'employe'     => 'Employé — son propre dossier uniquement',
];
$roleGroups = [
    'Système'             => ['admin-systeme', 'dg', 'chef-agence'],
    'Ressources Humaines' => ['responsable-rh', 'assistant-rh'],
    'Projets'             => ['responsable-projets', 'assistant-projets'],
    'Finance'             => ['responsable-finance', 'assistant-finance'],
    'Stock'               => ['responsable-stock', 'assistant-stock'],
    'Archives'            => ['responsable-archives', 'assistant-archives'],
];
$currentRoles  = old('roles', $user->roles->pluck('name')->toArray());
$currentScope  = old('scope', $user->scope ?? 'agent');
$existingRoles = $roles->toArray();
@endphp

<a href="{{ route('users.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

{{-- Fiche identité --}}
<div style="background:rgba(99,102,241,.06);border:1px solid rgba(99,102,241,.15);border-radius:12px;padding:14px 18px;max-width:640px;margin-bottom:20px;display:flex;align-items:center;gap:14px;">
    <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#06b6d4);display:flex;align-items:center;justify-content:center;font-weight:700;font-size:15px;color:#fff;flex-shrink:0;">
        {{ strtoupper(substr($user->name, 0, 1)) }}
    </div>
    <div>
        <div style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ $user->name }}</div>
        <div style="font-size:12px;color:rgba(255,255,255,.4);">{{ $user->email }}</div>
    </div>
    @if(count($currentRoles))
    <div style="margin-left:auto;display:flex;gap:5px;flex-wrap:wrap;justify-content:flex-end;">
        @foreach($currentRoles as $cr)
        <span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:500;background:rgba(255,255,255,.07);color:rgba(255,255,255,.65);">
            {{ $cr }}
        </span>
        @endforeach
    </div>
    @endif
</div>

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf
        @method('PUT')

        @php $inputStyle = "width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box;"; @endphp

        {{-- Nom --}}
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Nom complet</label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required style="{{ $inputStyle }}" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            @error('name')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
        </div>

        {{-- Email --}}
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Email</label>
            <input type="email" name="email" value="{{ old('email', $user->email) }}" required style="{{ $inputStyle }}" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            @error('email')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
        </div>

        {{-- Mot de passe --}}
        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Nouveau mot de passe</label>
            <input type="password" name="password" style="{{ $inputStyle }}" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            <span style="font-size:11px;color:rgba(255,255,255,.3);margin-top:4px;display:block;">Laisser vide pour conserver le mot de passe actuel</span>
            @error('password')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
        </div>

        {{-- Séparateur --}}
        <div style="border-top:1px solid rgba(255,255,255,.06);margin:24px 0 20px;"></div>
        <p style="font-size:12px;color:rgba(255,255,255,.35);margin-bottom:18px;">Modifier les rôles change immédiatement ce que l'utilisateur peut accéder.</p>

        {{-- Rôles (cases à cocher groupées) --}}
        <div style="margin-bottom:22px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:14px;">Rôles & domaines d'accès</label>
            @foreach($roleGroups as $domain => $domainRoles)
            @php $available = array_filter($domainRoles, fn($r) => in_array($r, $existingRoles)); @endphp
            @if(count($available))
            <div style="margin-bottom:14px;">
                <div style="font-size:10px;font-weight:600;color:rgba(255,255,255,.3);text-transform:uppercase;letter-spacing:.08em;margin-bottom:7px;">{{ $domain }}</div>
                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach($available as $r)
                    <label style="display:flex;align-items:center;gap:6px;padding:6px 12px;background:{{ in_array($r, $currentRoles) ? 'rgba(59,130,246,.1)' : 'rgba(255,255,255,.03)' }};border:1px solid {{ in_array($r, $currentRoles) ? 'rgba(59,130,246,.45)' : 'rgba(255,255,255,.07)' }};border-radius:8px;cursor:pointer;user-select:none;transition:all .15s;">
                        <input type="checkbox" name="roles[]" value="{{ $r }}"
                               {{ in_array($r, $currentRoles) ? 'checked' : '' }}
                               onchange="onRoleChange()"
                               style="accent-color:#3b82f6;width:14px;height:14px;cursor:pointer;flex-shrink:0;">
                        <span style="font-size:12px;color:rgba(255,255,255,.75);font-family:inherit;white-space:nowrap;">{{ $r }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            @endif
            @endforeach
            @error('roles')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
        </div>

        {{-- Portée --}}
        <div style="margin-bottom:28px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">
                Portée d'accès aux données
                <span style="font-size:10px;color:rgba(255,255,255,.3);font-weight:400;margin-left:6px;">(auto-rempli selon le rôle)</span>
            </label>
            <select name="scope" id="scopeSelect" style="{{ $inputStyle }}cursor:pointer;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @foreach($scopes as $s)
                    <option value="{{ $s }}" style="color:#000;" {{ $currentScope == $s ? 'selected' : '' }}>
                        {{ $s }} — {{ $scopeLabels[$s] ?? $s }}
                    </option>
                @endforeach
            </select>
            @error('scope')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
        </div>

        {{-- Agence + Fiche employé --}}
        @php
            $currentEmployeeId = old('employee_id', $user->employee?->id);
            $currentAgenceId   = old('agence_id', $user->agence_id);
        @endphp
        <div id="agenceSection">
            <div style="border-top:1px solid rgba(255,255,255,.06);margin:24px 0 20px;"></div>

            {{-- Agence --}}
            <div style="margin-bottom:18px;">
                <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">
                    Agence
                    <span style="font-size:10px;color:rgba(255,255,255,.3);font-weight:400;margin-left:6px;">accès restreint à cette agence</span>
                </label>
                <select name="agence_id" id="agenceSelect" style="{{ $inputStyle }}cursor:pointer;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <option value="" style="color:#000;">— Sélectionner une agence —</option>
                    @foreach($agences as $id => $nom)
                        <option value="{{ $id }}" style="color:#000;" {{ $currentAgenceId == $id ? 'selected' : '' }}>
                            {{ $nom }}
                        </option>
                    @endforeach
                </select>
                @error('agence_id')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>

            {{-- Fiche employé --}}
            <div style="margin-bottom:28px;">
                <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">
                    Fiche employé
                    <span style="font-size:10px;color:rgba(255,255,255,.3);font-weight:400;margin-left:6px;">optionnel — synchronise l'agence automatiquement</span>
                </label>
                <select name="employee_id" id="employeeSelect" style="{{ $inputStyle }}cursor:pointer;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'" onchange="syncAgenceFromEmployee(this)">
                    <option value="" data-agence="" style="color:#000;">— Aucune fiche liée —</option>
                    @foreach($employees as $emp)
                        <option value="{{ $emp->id }}" data-agence="{{ $emp->agence_id ?? '' }}" style="color:#000;" {{ $currentEmployeeId == $emp->id ? 'selected' : '' }}>
                            {{ $emp->nom_complet }}{{ $emp->agence ? ' — '.$emp->agence->name_agence : '' }}
                        </option>
                    @endforeach
                </select>
                @error('employee_id')<span style="font-size:12px;color:#f87171;margin-top:4px;display:block;">{{ $message }}</span>@enderror
            </div>
        </div>

        <div style="display:flex;gap:12px;">
            <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Enregistrer</button>
            <a href="{{ route('users.index') }}" style="padding:11px 20px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);font-size:13px;font-weight:500;text-decoration:none;display:inline-block;">Annuler</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
const dgRoles   = ['admin-systeme', 'dg'];
const chefRoles = ['chef-agence'];

function onRoleChange() {
    const checked = [...document.querySelectorAll('input[name="roles[]"]:checked')].map(c => c.value);
    let scope = 'agent';
    if (checked.some(r => dgRoles.includes(r)))        scope = 'dg';
    else if (checked.some(r => chefRoles.includes(r))) scope = 'chef-agence';
    document.getElementById('scopeSelect').value = scope;
    toggleAgenceSection(scope);

    document.querySelectorAll('input[name="roles[]"]').forEach(cb => {
        const label = cb.closest('label');
        label.style.borderColor = cb.checked ? 'rgba(59,130,246,.45)' : 'rgba(255,255,255,.07)';
        label.style.background  = cb.checked ? 'rgba(59,130,246,.1)'  : 'rgba(255,255,255,.03)';
    });
}

function toggleAgenceSection(scope) {
    const s = document.getElementById('agenceSection');
    if (s) s.style.display = scope === 'dg' ? 'none' : '';
}

function syncAgenceFromEmployee(select) {
    const agenceId = select.options[select.selectedIndex].getAttribute('data-agence');
    const agenceSelect = document.getElementById('agenceSelect');
    if (agenceId && agenceSelect) agenceSelect.value = agenceId;
}

document.getElementById('scopeSelect').addEventListener('change', function() {
    toggleAgenceSection(this.value);
});

onRoleChange();
</script>
@endpush
@endsection
