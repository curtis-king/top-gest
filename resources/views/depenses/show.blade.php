@extends('layouts.app')

@section('title', 'Dépense - ' . $depense->numero_depense . ' - MyGest')

@section('page-title', 'Dépense : ' . $depense->numero_depense)

@section('content')
<style>
    .mg-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .mg-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .mg-card-full { grid-column:1 / -1; }
    .mg-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .mg-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .mg-section-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.06); }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    .mg-select { width:100%; padding:8px 12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:13px; font-family:inherit; color:#fff; outline:none; cursor:pointer; box-sizing:border-box; }
    .mg-select:focus { border-color:#3b82f6; }
    .mg-select option { color:#000; }
    .mg-btn { padding:7px 16px; border:none; border-radius:8px; font-size:12px; font-weight:500; font-family:inherit; cursor:pointer; transition:all .2s; }
    @media (max-width:768px) { .mg-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('depenses.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux dépenses</a>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ session('error') }}
    </div>
@endif

@php
    $sv = $depense->statut?->value ?? 'en_attente';
    $sc = ['en_attente'=>'#f59e0b','payee'=>'#4ade80','annulee'=>'#6b7280'][$sv] ?? 'rgba(255,255,255,.45)';
@endphp

<div class="mg-grid">

    {{-- DEPENSE INFO --}}
    <div class="mg-card">
        <div class="mg-section-title">Dépense</div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Numéro</div>
            <div class="mg-value">{{ $depense->numero_depense }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Date</div>
            <div class="mg-value">{{ $depense->date_depense ? \Carbon\Carbon::parse($depense->date_depense)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Objet</div>
            <div class="mg-value">{{ $depense->objet }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Catégorie</div>
            <div class="mg-value">{{ $depense->categorie?->libelle ?? '—' }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Mode de paiement</div>
            <div class="mg-value">{{ $depense->mode_paiement?->value }} @if($depense->banque) &mdash; {{ $depense->banque->nom }} @endif</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Fournisseur / Contact</div>
            <div class="mg-value">{{ $depense->contact?->raison_social ?? '—' }}</div>
        </div>

        <div>
            <div class="mg-label">Agence</div>
            <div class="mg-value">{{ $depense->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>

    {{-- STATUT + MONTANT --}}
    <div class="mg-card">
        <div class="mg-section-title">Statut & Montant</div>

        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>

        @if($sv !== 'annulee')
            <form method="POST" action="{{ route('depenses.statut', $depense) }}" style="display:flex;align-items:flex-end;gap:10px;margin-bottom:16px;">
                @csrf
                @method('PATCH')
                <div style="flex:1;">
                    <div class="mg-label" style="margin-bottom:4px;">Statut</div>
                    <select name="statut" class="mg-select">
                        @foreach(\App\Enums\StatutDepense::cases() as $s)
                            <option value="{{ $s->value }}" {{ $sv == $s->value ? 'selected' : '' }}>{{ $s->value }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;white-space:nowrap;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Mettre à jour</button>
            </form>
        @endif

        <div style="padding-top:12px;border-top:1px solid rgba(255,255,255,.04);margin-bottom:16px;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">Montant</span>
                <span style="font-size:26px;font-weight:700;color:#4ade80;">{{ number_format($depense->montant, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        @if($sv === 'payee')
            @php
                $ecritureLiee = \App\Models\EcritureComptable::where('source_type', 'depense')->where('source_id', $depense->id)->first();
            @endphp
            @if($ecritureLiee)
                <a href="{{ route('ecritures-comptables.show', $ecritureLiee) }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;width:100%;justify-content:center;">Voir l'écriture {{ $ecritureLiee->numero_ecriture }} &rarr;</a>
            @else
                <a href="{{ route('ecritures-comptables.create', ['source_type' => 'depense', 'source_id' => $depense->id]) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;width:100%;justify-content:center;box-sizing:border-box;">Comptabiliser</a>
            @endif
        @endif
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('depenses.edit', $depense) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;">Modifier</a>
    <form method="POST" action="{{ route('depenses.destroy', $depense) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
