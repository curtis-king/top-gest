@extends('layouts.app')

@section('title', $facture->numero_facture . ' - MyGest')

@section('page-title', $facture->numero_facture)

@section('content')
<style>
    .det-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .det-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .det-card-full { grid-column:1 / -1; }
    .det-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .det-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:640px) { .det-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('factures.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux factures</a>

@php
    $tv = $facture->type_facture?->value ?? 'vente';
    $sv = $facture->statut_facture?->value ?? 'brouillon';
    $tc = ['vente'=>'#3b82f6','achat'=>'#f59e0b','avoir'=>'#f87171','proforma'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
    $sc = ['brouillon'=>'#94a3b8','impayee'=>'#f87171','partielle'=>'#f59e0b','payee'=>'#4ade80','annulee'=>'#6b7280'][$sv]??'rgba(255,255,255,.45)';
@endphp

<div class="det-grid">
    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Facture</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Numéro</div>
            <div class="det-value">{{ $facture->numero_facture }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Date</div>
            <div class="det-value">{{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Type</div>
            <span class="badge" style="background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span>
        </div>

        <div>
            <div class="det-label">Statut</div>
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Client</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Raison sociale</div>
            <div class="det-value">{{ $facture->raison_social ?? $facture->contact?->raison_social ?? '—' }}</div>
        </div>

        @if($facture->contact)
        <div style="margin-bottom:14px;">
            <div class="det-label">Email</div>
            <div class="det-value">{{ $facture->contact->adresse_email }}</div>
        </div>
        <div style="margin-bottom:14px;">
            <div class="det-label">Téléphone</div>
            <div class="det-value">{{ $facture->contact->telephone }}</div>
        </div>
        @endif

        <div>
            <div class="det-label">Agence</div>
            <div class="det-value">{{ $facture->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>
</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('factures.manage', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Gérer les articles</a>
    <a href="{{ route('factures.edit', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;">Modifier</a>
    <form method="POST" action="{{ route('factures.destroy', $facture) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
