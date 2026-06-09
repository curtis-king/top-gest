@extends('layouts.app')

@section('title', $employee ? $employee->nom_complet.' - MyGest' : 'Nouvel employé - MyGest')

@section('page-title', $employee ? $employee->nom_complet : 'Nouvel employé')

@section('content')
<style>
    .wz-shell { max-width:960px; margin:0 auto; }
    .wz-back { color:#60a5fa; text-decoration:none; font-size:13px; font-weight:500; display:inline-flex; align-items:center; gap:6px; margin-bottom:24px; }

    .wz-progress { display:flex; align-items:flex-start; gap:0; margin-bottom:36px; padding:0 4px; }
    .wz-step { display:flex; flex-direction:column; align-items:center; flex:1; position:relative; }
    .wz-step-circle { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:600; position:relative; z-index:2; transition:all .35s ease; flex-shrink:0; }
    .wz-step-circle .wz-icon { width:16px; height:16px; }
    .wz-step-label { font-size:11px; font-weight:500; margin-top:8px; text-align:center; transition:color .35s ease; white-space:nowrap; }

    .wz-step.pending .wz-step-circle { background:rgba(255,255,255,.06); color:rgba(255,255,255,.3); border:2px solid rgba(255,255,255,.08); }
    .wz-step.pending .wz-step-label { color:rgba(255,255,255,.25); }
    .wz-step.active .wz-step-circle { background:#2563eb; color:#fff; border:2px solid #3b82f6; box-shadow:0 0 0 4px rgba(59,130,246,.2); }
    .wz-step.active .wz-step-label { color:#60a5fa; font-weight:600; }
    .wz-step.completed .wz-step-circle { background:#059669; color:#fff; border:2px solid #10b981; }
    .wz-step.completed .wz-step-label { color:rgba(255,255,255,.55); }
    .wz-step:not(:last-child)::after { content:''; position:absolute; top:19px; left:calc(50% + 22px); width:calc(100% - 44px); height:2px; z-index:1; transition:background .35s ease; border-radius:1px; }
    .wz-step.completed:not(:last-child)::after { background:#059669; }
    .wz-step.active:not(:last-child)::after,
    .wz-step.pending:not(:last-child)::after { background:rgba(255,255,255,.08); }

    .wz-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:16px; padding:32px; display:none; animation:wzFadeIn .4s ease; }
    .wz-card.active { display:block; }
    @keyframes wzFadeIn { from { opacity:0; transform:translateY(12px); } to { opacity:1; transform:translateY(0); } }

    .wz-card-title { font-size:18px; font-weight:700; color:#f1f5f9; margin-bottom:4px; }
    .wz-card-desc { font-size:13px; color:rgba(255,255,255,.4); margin-bottom:24px; }

    .wz-grid { display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .wz-grid-full { grid-column:1 / -1; }
    .wz-field { display:flex; flex-direction:column; gap:5px; }
    .wz-label { font-size:12px; font-weight:500; color:rgba(255,255,255,.55); letter-spacing:.2px; }
    .wz-input, .wz-select, .wz-textarea { width:100%; padding:11px 14px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:10px; font-size:14px; font-family:inherit; color:#fff; outline:none; transition:all .25s ease; }
    .wz-input:focus, .wz-select:focus, .wz-textarea:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
    .wz-input::placeholder { color:rgba(255,255,255,.2); }
    .wz-select option { color:#000; background:#1a1f2e; }
    .wz-textarea { resize:vertical; min-height:80px; }
    .wz-error { font-size:12px; color:#f87171; margin-top:3px; }
    .wz-hint { font-size:11px; color:rgba(255,255,255,.3); margin-top:2px; }

    .wz-nav { display:flex; align-items:center; justify-content:space-between; margin-top:28px; padding-top:24px; border-top:1px solid rgba(255,255,255,.06); }
    .wz-nav-left { display:flex; gap:10px; }
    .wz-nav-right { display:flex; gap:10px; }

    .wz-btn { padding:11px 24px; border-radius:10px; font-size:13px; font-weight:600; font-family:inherit; cursor:pointer; transition:all .25s ease; display:inline-flex; align-items:center; gap:7px; border:none; text-decoration:none; }
    .wz-btn-primary { background:#2563eb; color:#fff; }
    .wz-btn-primary:hover { background:#3b82f6; transform:translateY(-1px); }
    .wz-btn-secondary { background:rgba(255,255,255,.06); color:rgba(255,255,255,.7); border:1px solid rgba(255,255,255,.08); }
    .wz-btn-secondary:hover { background:rgba(255,255,255,.1); color:#fff; }
    .wz-btn-success { background:#059669; color:#fff; }
    .wz-btn-success:hover { background:#10b981; transform:translateY(-1px); }
    .wz-btn-danger { background:rgba(239,68,68,.15); color:#f87171; border:1px solid rgba(239,68,68,.2); }
    .wz-btn-danger:hover { background:rgba(239,68,68,.25); }
    .wz-btn:disabled { opacity:.4; cursor:not-allowed; transform:none !important; }

    .wz-list { margin-top:16px; }
    .wz-list-item { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:10px; margin-bottom:8px; transition:background .2s; }
    .wz-list-item:hover { background:rgba(255,255,255,.04); }
    .wz-list-item-info { display:flex; flex-direction:column; gap:2px; }
    .wz-list-item-title { font-size:13px; font-weight:500; color:#f1f5f9; }
    .wz-list-item-sub { font-size:12px; color:rgba(255,255,255,.4); }
    .wz-list-item-actions { display:flex; gap:8px; }
    .wz-list-empty { text-align:center; padding:32px 16px; color:rgba(255,255,255,.25); font-size:14px; }

    .wz-badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; }

    .wz-add-form { margin-top:16px; padding:20px; background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:12px; }
    .wz-add-form-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); margin-bottom:16px; text-transform:uppercase; letter-spacing:.4px; }

    .wz-summary { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px; }
    .wz-summary-item { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:12px; padding:16px; }
    .wz-summary-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .wz-summary-value { font-size:18px; font-weight:700; color:#f1f5f9; }
    .wz-summary-value.positive { color:#4ade80; }
    .wz-summary-value.negative { color:#f87171; }

    @media (max-width:768px) {
        .wz-grid { grid-template-columns:1fr; }
        .wz-progress { overflow-x:auto; padding-bottom:8px; }
        .wz-step-label { font-size:10px; white-space:normal; max-width:60px; }
        .wz-step-circle { width:32px; height:32px; font-size:12px; }
        .wz-step:not(:last-child)::after { top:15px; left:calc(50% + 18px); width:calc(100% - 36px); }
        .wz-card { padding:20px; }
        .wz-summary { grid-template-columns:1fr 1fr; }
    }
</style>

<div class="wz-shell">
    <a href="{{ route('employees.index') }}" class="wz-back">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
        Retour aux employés
    </a>

    @if(session('success'))
        <div style="padding:14px 18px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:10px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:24px;display:flex;align-items:center;gap:10px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:18px;height:18px;flex-shrink:0;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @php
        $steps = [
            1 => ['label' => 'Informations', 'icon' => 'person'],
            2 => ['label' => 'Dossier', 'icon' => 'folder'],
            3 => ['label' => 'Congés', 'icon' => 'calendar'],
            4 => ['label' => 'Primes', 'icon' => 'star'],
            5 => ['label' => 'Retenus', 'icon' => 'minus'],
            6 => ['label' => 'Paiements', 'icon' => 'card'],
        ];
    @endphp

    @if($mode === 'create')
        <div class="wz-card active" data-step="1">
            <div class="wz-card-title">Informations personnelles</div>
            <div class="wz-card-desc">Renseignez les informations de base du nouvel employé.</div>

            <form method="POST" action="{{ route('employees.wizard.store') }}">
                @csrf
                <div class="wz-grid">
                    <div class="wz-field wz-grid-full">
                        <label class="wz-label">Nom complet</label>
                        <input type="text" name="nom_complet" value="{{ old('nom_complet') }}" required class="wz-input" placeholder="Ex: Jean Dupont">
                        @error('nom_complet') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field wz-grid-full">
                        <label class="wz-label">Adresse</label>
                        <textarea name="adresse" class="wz-textarea" placeholder="Adresse complète">{{ old('adresse') }}</textarea>
                        @error('adresse') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone') }}" class="wz-input" placeholder="+221 77 123 45 67">
                        @error('telephone') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Statut matrimonial</label>
                        <select name="status_matrimonial" class="wz-select">
                            <option value="">Sélectionnez</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->value }}" {{ old('status_matrimonial') == $s->value ? 'selected' : '' }}>{{ ucfirst($s->value) }}</option>
                            @endforeach
                        </select>
                        @error('status_matrimonial') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Type de pièce</label>
                        <select name="type_piece" class="wz-select">
                            <option value="">Sélectionnez</option>
                            @foreach($typePieces as $tp)
                                <option value="{{ $tp->value }}" {{ old('type_piece') == $tp->value ? 'selected' : '' }}>{{ ucfirst($tp->value) }}</option>
                            @endforeach
                        </select>
                        @error('type_piece') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Numéro de pièce</label>
                        <input type="text" name="numero_piece" value="{{ old('numero_piece') }}" class="wz-input" placeholder="N° de la pièce d'identité">
                        @error('numero_piece') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Agence</label>
                        <select name="agence_id" class="wz-select" required>
                            <option value="">Sélectionnez une agence</option>
                            @foreach($agences as $id => $name)
                                <option value="{{ $id }}" {{ old('agence_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('agence_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Fonction</label>
                        <select name="fonction_id" class="wz-select" required>
                            <option value="">Sélectionnez une fonction</option>
                            @foreach($fonctions as $id => $name)
                                <option value="{{ $id }}" {{ old('fonction_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('fonction_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Utilisateur lié</label>
                        <select name="user_id" class="wz-select">
                            <option value="">-- Aucun --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="wz-nav">
                    <div></div>
                    <div class="wz-nav-right">
                        <button type="submit" class="wz-btn wz-btn-primary">
                            Créer l'employé
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @else
        <div class="wz-progress" id="wzProgress">
            @foreach($steps as $num => $step)
                <div class="wz-step {{ $num === 1 ? 'active' : 'pending' }}" data-step="{{ $num }}">
                    <div class="wz-step-circle">
                        @if($num === 1)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        @elseif($num === 2)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                        @elseif($num === 3)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/></svg>
                        @elseif($num === 4)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                        @elseif($num === 5)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        @elseif($num === 6)
                            <svg class="wz-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/></svg>
                        @endif
                    </div>
                    <span class="wz-step-label">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>

        <div class="wz-summary">
            <div class="wz-summary-item">
                <div class="wz-summary-label">Agence</div>
                <div class="wz-summary-value" style="font-size:14px;">{{ $employee->agence?->name_agence ?? '—' }}</div>
            </div>
            <div class="wz-summary-item">
                <div class="wz-summary-label">Fonction</div>
                <div class="wz-summary-value" style="font-size:14px;">{{ $employee->fonction?->name ?? '—' }}</div>
            </div>
            <div class="wz-summary-item">
                <div class="wz-summary-label">Congés</div>
                <div class="wz-summary-value">{{ $employee->conges->count() }}</div>
            </div>
            <div class="wz-summary-item">
                <div class="wz-summary-label">Primes</div>
                <div class="wz-summary-value positive">{{ number_format($employee->primes->sum('montant'), 0, ',', ' ') }} F</div>
            </div>
            <div class="wz-summary-item">
                <div class="wz-summary-label">Retenus</div>
                <div class="wz-summary-value negative">{{ number_format($employee->retenus->sum('montant'), 0, ',', ' ') }} F</div>
            </div>
            <div class="wz-summary-item">
                <div class="wz-summary-label">Dernier paiement</div>
                <div class="wz-summary-value" style="font-size:14px;">
                    @php $last = $employee->payements->last(); @endphp
                    {{ $last ? number_format($last->net_a_payer, 0, ',', ' ').' F' : '—' }}
                </div>
            </div>
        </div>

        {{-- STEP 1: Informations personnelles --}}
        <div class="wz-card active" data-step="1">
            <div class="wz-card-title">Informations personnelles</div>
            <div class="wz-card-desc">Modifiez les informations de base de l'employé.</div>

            <form method="POST" action="{{ route('employees.wizard.save-step1', $employee) }}">
                @csrf
                <div class="wz-grid">
                    <div class="wz-field wz-grid-full">
                        <label class="wz-label">Nom complet</label>
                        <input type="text" name="nom_complet" value="{{ old('nom_complet', $employee->nom_complet) }}" required class="wz-input">
                        @error('nom_complet') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field wz-grid-full">
                        <label class="wz-label">Adresse</label>
                        <textarea name="adresse" class="wz-textarea">{{ old('adresse', $employee->adresse) }}</textarea>
                        @error('adresse') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Téléphone</label>
                        <input type="text" name="telephone" value="{{ old('telephone', $employee->telephone) }}" class="wz-input">
                        @error('telephone') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Statut matrimonial</label>
                        <select name="status_matrimonial" class="wz-select">
                            <option value="">Sélectionnez</option>
                            @foreach($statuses as $s)
                                <option value="{{ $s->value }}" {{ old('status_matrimonial', $employee->status_matrimonial?->value) == $s->value ? 'selected' : '' }}>{{ ucfirst($s->value) }}</option>
                            @endforeach
                        </select>
                        @error('status_matrimonial') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Type de pièce</label>
                        <select name="type_piece" class="wz-select">
                            <option value="">Sélectionnez</option>
                            @foreach($typePieces as $tp)
                                <option value="{{ $tp->value }}" {{ old('type_piece', $employee->type_piece?->value) == $tp->value ? 'selected' : '' }}>{{ ucfirst($tp->value) }}</option>
                            @endforeach
                        </select>
                        @error('type_piece') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Numéro de pièce</label>
                        <input type="text" name="numero_piece" value="{{ old('numero_piece', $employee->numero_piece) }}" class="wz-input">
                        @error('numero_piece') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Agence</label>
                        <select name="agence_id" class="wz-select" required>
                            <option value="">Sélectionnez</option>
                            @foreach($agences as $id => $name)
                                <option value="{{ $id }}" {{ old('agence_id', $employee->agence_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('agence_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Fonction</label>
                        <select name="fonction_id" class="wz-select" required>
                            <option value="">Sélectionnez</option>
                            @foreach($fonctions as $id => $name)
                                <option value="{{ $id }}" {{ old('fonction_id', $employee->fonction_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('fonction_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Utilisateur lié</label>
                        <select name="user_id" class="wz-select">
                            <option value="">-- Aucun --</option>
                            @foreach($users as $id => $name)
                                <option value="{{ $id }}" {{ old('user_id', $employee->user_id) == $id ? 'selected' : '' }}>{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('user_id') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="wz-nav">
                    <div></div>
                    <div class="wz-nav-right">
                        <button type="submit" class="wz-btn wz-btn-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- STEP 2: Dossier --}}
        <div class="wz-card" data-step="2">
            <div class="wz-card-title">Dossier & Contrat</div>
            <div class="wz-card-desc">Gérez le dossier administratif et le contrat de l'employé.</div>

            @php $dossier = $employee->dossier; @endphp

            <form method="POST" action="{{ route('employees.wizard.save-step2', $employee) }}">
                @csrf
                <div class="wz-grid">
                    <div class="wz-field">
                        <label class="wz-label">Date d'engagement</label>
                        <input type="date" name="date_engagement" value="{{ old('date_engagement', $dossier?->date_engagement?->format('Y-m-d')) }}" required class="wz-input">
                        @error('date_engagement') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Date de fin <span class="wz-hint">(optionnelle)</span></label>
                        <input type="date" name="date_fin" value="{{ old('date_fin', $dossier?->date_fin?->format('Y-m-d')) }}" class="wz-input">
                        @error('date_fin') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Type de contrat</label>
                        <select name="type_contrat" class="wz-select" required>
                            <option value="">Sélectionnez</option>
                            @foreach($typesContrat as $tc)
                                <option value="{{ $tc->value }}" {{ old('type_contrat', $dossier?->type_contrat?->value) == $tc->value ? 'selected' : '' }}>{{ strtoupper($tc->value) }}</option>
                            @endforeach
                        </select>
                        @error('type_contrat') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="wz-field">
                        <label class="wz-label">Statut du dossier</label>
                        <select name="status" class="wz-select" required>
                            <option value="">Sélectionnez</option>
                            @foreach($statusDossiers as $sd)
                                <option value="{{ $sd->value }}" {{ old('status', $dossier?->status?->value) == $sd->value ? 'selected' : '' }}>{{ ucfirst($sd->value) }}</option>
                            @endforeach
                        </select>
                        @error('status') <span class="wz-error">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="wz-nav">
                    <div></div>
                    <div class="wz-nav-right">
                        <button type="submit" class="wz-btn wz-btn-success">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="M22 4 12 14.01l-3-3"/></svg>
                            Enregistrer
                        </button>
                    </div>
                </div>
            </form>
        </div>

        {{-- STEP 3: Congés --}}
        <div class="wz-card" data-step="3">
            <div class="wz-card-title">Congés</div>
            <div class="wz-card-desc">Gérez les congés de l'employé.</div>

            @if($employee->conges->count())
                <div class="wz-list">
                    @foreach($employee->conges as $c)
                        <div class="wz-list-item">
                            <div class="wz-list-item-info">
                                <span class="wz-list-item-title">{{ $c->type_conge?->value ?? 'Congé' }} — du {{ $c->date_debut->format('d/m/Y') }} au {{ $c->date_fin->format('d/m/Y') }}</span>
                                <span class="wz-list-item-sub">
                                    {{ (int) $c->date_debut->diffInDays($c->date_fin) + 1 }} jour(s)
                                </span>
                            </div>
                            <div class="wz-list-item-actions">
                                <form method="POST" action="{{ route('employees.wizard.delete-conge', [$employee, $c]) }}" onsubmit="return confirm('Supprimer ce congé ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="wz-btn wz-btn-danger" style="padding:6px 12px;font-size:12px;">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="wz-list-empty">Aucun congé enregistré.</div>
            @endif

            <div class="wz-add-form">
                <div class="wz-add-form-title">Ajouter un congé</div>
                <form method="POST" action="{{ route('employees.wizard.add-conge', $employee) }}">
                    @csrf
                    <div class="wz-grid">
                        <div class="wz-field">
                            <label class="wz-label">Type de congé</label>
                            <select name="type_conge" class="wz-select" required>
                                <option value="">Sélectionnez</option>
                                @foreach($typesConge as $tc)
                                    <option value="{{ $tc->value }}">{{ ucfirst($tc->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Date début</label>
                            <input type="date" name="date_debut" required class="wz-input">
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Date fin</label>
                            <input type="date" name="date_fin" required class="wz-input">
                        </div>
                        <div class="wz-field" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="wz-btn wz-btn-primary" style="width:100%;justify-content:center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- STEP 4: Primes --}}
        <div class="wz-card" data-step="4">
            <div class="wz-card-title">Primes</div>
            <div class="wz-card-desc">Gérez les primes et bonus de l'employé.</div>

            @if($employee->primes->count())
                <div class="wz-list">
                    @foreach($employee->primes as $p)
                        <div class="wz-list-item">
                            <div class="wz-list-item-info">
                                <span class="wz-list-item-title">{{ $p->motif }}</span>
                                <span class="wz-list-item-sub">{{ $p->mois }}/{{ $p->annee }}</span>
                            </div>
                            <div class="wz-list-item-actions" style="align-items:center;gap:12px;">
                                <span style="color:#4ade80;font-weight:600;font-size:14px;">{{ number_format($p->montant, 0, ',', ' ') }} F</span>
                                <form method="POST" action="{{ route('employees.wizard.delete-prime', [$employee, $p]) }}" onsubmit="return confirm('Supprimer cette prime ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="wz-btn wz-btn-danger" style="padding:6px 12px;font-size:12px;">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="wz-list-empty">Aucune prime enregistrée.</div>
            @endif

            <div class="wz-add-form">
                <div class="wz-add-form-title">Ajouter une prime</div>
                <form method="POST" action="{{ route('employees.wizard.add-prime', $employee) }}">
                    @csrf
                    <div class="wz-grid">
                        <div class="wz-field wz-grid-full">
                            <label class="wz-label">Motif</label>
                            <input type="text" name="motif" required class="wz-input" placeholder="Ex: Prime de performance">
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Montant (FCFA)</label>
                            <input type="number" name="montant" step="0.01" min="0" required class="wz-input" placeholder="50000">
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Mois</label>
                            <select name="mois" class="wz-select" required>
                                @for($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}">{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Année</label>
                            <select name="annee" class="wz-select" required>
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="wz-field" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="wz-btn wz-btn-primary" style="width:100%;justify-content:center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- STEP 5: Retenus --}}
        <div class="wz-card" data-step="5">
            <div class="wz-card-title">Retenus</div>
            <div class="wz-card-desc">Gérez les retenues sur salaire de l'employé.</div>

            @if($employee->retenus->count())
                <div class="wz-list">
                    @foreach($employee->retenus as $r)
                        <div class="wz-list-item">
                            <div class="wz-list-item-info">
                                <span class="wz-list-item-title">{{ $r->motif }}</span>
                                <span class="wz-list-item-sub">{{ $r->date_retenu->format('d/m/Y') }}</span>
                            </div>
                            <div class="wz-list-item-actions" style="align-items:center;gap:12px;">
                                <span style="color:#f87171;font-weight:600;font-size:14px;">- {{ number_format($r->montant, 0, ',', ' ') }} F</span>
                                <form method="POST" action="{{ route('employees.wizard.delete-retenu', [$employee, $r]) }}" onsubmit="return confirm('Supprimer cette retenue ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="wz-btn wz-btn-danger" style="padding:6px 12px;font-size:12px;">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="wz-list-empty">Aucune retenue enregistrée.</div>
            @endif

            <div class="wz-add-form">
                <div class="wz-add-form-title">Ajouter une retenue</div>
                <form method="POST" action="{{ route('employees.wizard.add-retenu', $employee) }}">
                    @csrf
                    <div class="wz-grid">
                        <div class="wz-field">
                            <label class="wz-label">Date</label>
                            <input type="date" name="date_retenu" required class="wz-input">
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Montant (FCFA)</label>
                            <input type="number" name="montant" step="0.01" min="0" required class="wz-input" placeholder="10000">
                        </div>
                        <div class="wz-field wz-grid-full">
                            <label class="wz-label">Motif</label>
                            <input type="text" name="motif" required class="wz-input" placeholder="Ex: Avance sur salaire">
                        </div>
                        <div class="wz-field" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="wz-btn wz-btn-primary" style="width:100%;justify-content:center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- STEP 6: Paiements --}}
        <div class="wz-card" data-step="6">
            <div class="wz-card-title">Paiements</div>
            <div class="wz-card-desc">Gérez les paiements et salaires de l'employé.</div>

            @if($employee->payements->count())
                <div class="wz-list">
                    @foreach($employee->payements as $pay)
                        @php
                            $sv = $pay->status?->value ?? '—';
                            $sc = ['en_attente'=>'#facc15','valide'=>'#4ade80','paye'=>'#3b82f6','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
                        @endphp
                        <div class="wz-list-item">
                            <div class="wz-list-item-info">
                                <span class="wz-list-item-title">{{ $pay->mois }} {{ $pay->annee }}</span>
                                <span class="wz-list-item-sub">Base: {{ number_format($pay->salaire_base, 0, ',', ' ') }} F</span>
                            </div>
                            <div style="display:flex;align-items:center;gap:12px;">
                                <span class="wz-badge" style="background:{{$sc}}22;color:{{$sc}};">{{ $sv }}</span>
                                <span style="color:#4ade80;font-weight:600;font-size:14px;">{{ number_format($pay->net_a_payer, 0, ',', ' ') }} F</span>
                                <form method="POST" action="{{ route('employees.wizard.delete-payement', [$employee, $pay]) }}" onsubmit="return confirm('Supprimer ce paiement ?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="wz-btn wz-btn-danger" style="padding:6px 12px;font-size:12px;">Supprimer</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="wz-list-empty">Aucun paiement enregistré.</div>
            @endif

            <div class="wz-add-form">
                <div class="wz-add-form-title">Ajouter un paiement</div>
                <form method="POST" action="{{ route('employees.wizard.add-payement', $employee) }}">
                    @csrf
                    <div class="wz-grid">
                        <div class="wz-field">
                            <label class="wz-label">Mois</label>
                            <select name="mois" id="payMois" class="wz-select" required onchange="calcPayTotals()">
                                @php $moisFr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
                                @foreach($moisFr as $i => $mois)
                                    <option value="{{ $mois }}" data-num="{{ $i + 1 }}">{{ $mois }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Année</label>
                            <select name="annee" id="payAnnee" class="wz-select" required onchange="calcPayTotals()">
                                @for($y = date('Y'); $y >= 2020; $y--)
                                    <option value="{{ $y }}">{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Salaire de base</label>
                            <input type="number" name="salaire_base" id="paySalaireBase" step="0.01" min="0" required class="wz-input" value="{{ $employee->fonction?->salaire ?? 0 }}" readonly style="cursor:default;color:#60a5fa;font-weight:600;">
                            <span style="font-size:11px;color:rgba(255,255,255,.3);">{{ $employee->fonction?->name ? 'Fonction: '.$employee->fonction->name : 'Aucune fonction définie' }}</span>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Total primes</label>
                            <input type="number" name="total_primes" id="payTotalPrimes" step="0.01" min="0" class="wz-input" placeholder="0" value="0" readonly style="color:#4ade80;cursor:default;">
                            <span style="font-size:11px;color:rgba(255,255,255,.3);">Calculé automatiquement</span>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Total retenus</label>
                            <input type="number" name="total_retenus" id="payTotalRetenus" step="0.01" min="0" class="wz-input" placeholder="0" value="0" readonly style="color:#f87171;cursor:default;">
                            <span style="font-size:11px;color:rgba(255,255,255,.3);">Calculé automatiquement</span>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Net à payer</label>
                            <input type="number" name="net_a_payer" id="payNetAPayer" step="0.01" min="0" required class="wz-input" placeholder="150000" readonly style="color:#60a5fa;font-weight:700;cursor:default;">
                            <span style="font-size:11px;color:rgba(255,255,255,.3);">Salaire + Primes - Retenus</span>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Statut</label>
                            <select name="status" class="wz-select" required>
                                @foreach($statusPayements as $sp)
                                    <option value="{{ $sp->value }}">{{ ucfirst($sp->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="wz-field">
                            <label class="wz-label">Date de paiement</label>
                            <input type="date" name="date_paiement" class="wz-input">
                        </div>
                        <div class="wz-field" style="display:flex;align-items:flex-end;">
                            <button type="submit" class="wz-btn wz-btn-primary" style="width:100%;justify-content:center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 5v14"/><path d="M5 12h14"/></svg>
                                Ajouter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            @php
                $primesData = $employee->primes->map(fn($p) => [
                    'mois' => (int)$p->mois, 'annee' => (int)$p->annee,
                    'montant' => (float)$p->montant, 'motif' => $p->motif
                ]);
                $retenusData = $employee->retenus->map(fn($r) => [
                    'mois' => (int)$r->date_retenu->format('n'), 'annee' => (int)$r->date_retenu->format('Y'),
                    'montant' => (float)$r->montant, 'motif' => $r->motif, 'date' => $r->date_retenu->format('d/m/Y')
                ]);
            @endphp

            <div id="payDetail" style="display:none;margin-top:16px;">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px;">
                        <div style="font-size:12px;font-weight:600;color:#4ade80;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Primes du mois
                        </div>
                        <div id="payDetailPrimes">
                            <div style="text-align:center;padding:12px;color:rgba(255,255,255,.25);font-size:13px;">Aucune prime pour cette période</div>
                        </div>
                    </div>
                    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:16px;">
                        <div style="font-size:12px;font-weight:600;color:#f87171;margin-bottom:12px;display:flex;align-items:center;gap:6px;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Retenus du mois
                        </div>
                        <div id="payDetailRetenus">
                            <div style="text-align:center;padding:12px;color:rgba(255,255,255,.25);font-size:13px;">Aucune retenue pour cette période</div>
                        </div>
                    </div>
                </div>
            </div>

            <script>
                const employeePrimes = @json($primesData);
                const employeeRetenus = @json($retenusData);
                const moisMap = {'Janvier':1,'Février':2,'Mars':3,'Avril':4,'Mai':5,'Juin':6,'Juillet':7,'Août':8,'Septembre':9,'Octobre':10,'Novembre':11,'Décembre':12};

                function calcPayTotals() {
                    const moisSelect = document.getElementById('payMois');
                    const anneeSelect = document.getElementById('payAnnee');
                    const moisNom = moisSelect.value;
                    const annee = parseInt(anneeSelect.value);
                    const moisNum = moisMap[moisNom];

                    if (!moisNum || !annee) { return; }

                    let totalPrimes = 0, totalRetenus = 0;
                    const primesHtml = [], retenusHtml = [];

                    employeePrimes.forEach(function(p) {
                        if (p.mois === moisNum && p.annee === annee) {
                            totalPrimes += p.montant;
                            primesHtml.push(
                                '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);">' +
                                '<span style="font-size:13px;color:rgba(255,255,255,.65);">' + p.motif + '</span>' +
                                '<span style="font-size:13px;font-weight:600;color:#4ade80;">' + p.montant.toLocaleString('fr-FR', {minimumFractionDigits:0,maximumFractionDigits:0}) + ' F</span>' +
                                '</div>'
                            );
                        }
                    });

                    employeeRetenus.forEach(function(r) {
                        if (r.mois === moisNum && r.annee === annee) {
                            totalRetenus += r.montant;
                            retenusHtml.push(
                                '<div style="display:flex;align-items:center;justify-content:space-between;padding:8px 0;border-bottom:1px solid rgba(255,255,255,.04);">' +
                                '<div><span style="font-size:13px;color:rgba(255,255,255,.65);">' + r.motif + '</span><span style="font-size:11px;color:rgba(255,255,255,.3);display:block;">' + r.date + '</span></div>' +
                                '<span style="font-size:13px;font-weight:600;color:#f87171;">- ' + r.montant.toLocaleString('fr-FR', {minimumFractionDigits:0,maximumFractionDigits:0}) + ' F</span>' +
                                '</div>'
                            );
                        }
                    });

                    document.getElementById('payTotalPrimes').value = totalPrimes.toFixed(2);
                    document.getElementById('payTotalRetenus').value = totalRetenus.toFixed(2);

                    const detailDiv = document.getElementById('payDetail');
                    detailDiv.style.display = 'block';
                    document.getElementById('payDetailPrimes').innerHTML = primesHtml.length
                        ? primesHtml.join('')
                        : '<div style="text-align:center;padding:12px;color:rgba(255,255,255,.25);font-size:13px;">Aucune prime pour cette période</div>';
                    document.getElementById('payDetailRetenus').innerHTML = retenusHtml.length
                        ? retenusHtml.join('')
                        : '<div style="text-align:center;padding:12px;color:rgba(255,255,255,.25);font-size:13px;">Aucune retenue pour cette période</div>';

                    calcPayNet();
                }

                function calcPayNet() {
                    const base = parseFloat(document.getElementById('paySalaireBase').value) || 0;
                    const primes = parseFloat(document.getElementById('payTotalPrimes').value) || 0;
                    const retenus = parseFloat(document.getElementById('payTotalRetenus').value) || 0;
                    const net = base + primes - retenus;
                    document.getElementById('payNetAPayer').value = net >= 0 ? net.toFixed(2) : '0.00';
                }

                if (document.readyState === 'complete' || document.readyState === 'interactive') {
                    setTimeout(calcPayTotals, 100);
                } else {
                    document.addEventListener('DOMContentLoaded', function() { setTimeout(calcPayTotals, 100); });
                }
            </script>
        </div>

        <div class="wz-nav">
            <div class="wz-nav-left">
                <a href="{{ route('employees.pdf', $employee) }}" class="wz-btn wz-btn-secondary" style="text-decoration:none;" target="_blank">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="M9 15l3-3 3 3"/></svg>
                    PDF
                </a>
                <button type="button" class="wz-btn wz-btn-secondary" id="wzPrevBtn" onclick="prevStep()" disabled>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M19 12H5"/><path d="M12 19l-7-7 7-7"/></svg>
                    Précédent
                </button>
            </div>
            <div class="wz-nav-right">
                <button type="button" class="wz-btn wz-btn-primary" id="wzNextBtn" onclick="nextStep()">
                    Suivant
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>
                </button>
            </div>
        </div>
    @endif
</div>

<script>
    let currentStep = 1;
    const totalSteps = 6;
    const steps = document.querySelectorAll('.wz-step');
    const cards = document.querySelectorAll('.wz-card');
    const prevBtn = document.getElementById('wzPrevBtn');
    const nextBtn = document.getElementById('wzNextBtn');

    function updateSteps() {
        steps.forEach((el, i) => {
            const num = i + 1;
            el.classList.remove('active', 'completed', 'pending');
            if (num === currentStep) {
                el.classList.add('active');
            } else if (num < currentStep) {
                el.classList.add('completed');
                const circle = el.querySelector('.wz-step-circle');
                if (circle && !circle.querySelector('svg.wz-check')) {
                    circle.innerHTML = '<svg class="wz-icon wz-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6 9 17l-5-5"/></svg>';
                }
            } else {
                el.classList.add('pending');
            }
        });

        cards.forEach((el, i) => {
            el.classList.toggle('active', (i + 1) === currentStep);
        });

        if (prevBtn) prevBtn.disabled = currentStep === 1;
        if (nextBtn) {
            if (currentStep === totalSteps) {
                nextBtn.innerHTML = 'Terminer';
                nextBtn.querySelector('svg')?.remove();
            } else {
                nextBtn.innerHTML = 'Suivant <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M5 12h14"/><path d="M12 5l7 7-7 7"/></svg>';
            }
        }
    }

    function goToStep(n) {
        if (n < 1 || n > totalSteps) return;
        currentStep = n;
        updateSteps();
        window.scrollTo({ top: 0, behavior: 'smooth' });
        if (n === 6 && typeof calcPayTotals === 'function') calcPayTotals();
    }

    function prevStep() { goToStep(currentStep - 1); }

    function nextStep() {
        if (currentStep === totalSteps) {
            goToStep(1);
            return;
        }
        goToStep(currentStep + 1);
    }

    document.addEventListener('DOMContentLoaded', function () {
        const hash = window.location.hash.replace('#step-', '');
        if (hash && parseInt(hash) >= 1 && parseInt(hash) <= totalSteps) {
            goToStep(parseInt(hash));
        }
        updateSteps();
    });
</script>
@endsection
