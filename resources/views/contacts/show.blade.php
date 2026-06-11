@extends('layouts.app')

@section('title', $contact->raison_social ?? $contact->nom_complet ?? 'Contact' . ' - MyGest')

@section('page-title', $contact->raison_social ?? $contact->nom_complet ?? 'Contact')

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

<a href="{{ route('contacts.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux contacts</a>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Contact</div>

        @if($contact->raison_social)
        <div style="margin-bottom:14px;">
            <div class="det-label">Raison sociale</div>
            <div class="det-value">{{ $contact->raison_social }}</div>
        </div>
        @endif

        @if($contact->nom_complet)
        <div style="margin-bottom:14px;">
            <div class="det-label">Nom complet</div>
            <div class="det-value">{{ $contact->nom_complet }}</div>
        </div>
        @endif

        <div style="margin-bottom:14px;">
            <div class="det-label">Type</div>
            @php
                $tv = $contact->type_contact?->value ?? 'autre';
                $tc = ['fournisseur'=>'#f59e0b','client'=>'#3b82f6','autre'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
            @endphp
            <span class="badge" style="background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Secteur d'activités</div>
            <div class="det-value">{{ $contact->secteur_activites ?? '—' }}</div>
        </div>

        <div>
            <div class="det-label">Agence</div>
            <div class="det-value">{{ $contact->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Coordonnées</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Email</div>
            <div class="det-value">{{ $contact->adresse_email }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Téléphone</div>
            <div class="det-value">{{ $contact->telephone }}</div>
        </div>

        <div>
            <div class="det-label">Adresse</div>
            <div class="det-value" style="white-space:pre-wrap;">{{ $contact->adresse ?? '—' }}</div>
        </div>
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('contacts.edit', $contact) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form method="POST" action="{{ route('contacts.destroy', $contact) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
