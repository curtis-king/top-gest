@extends('layouts.app')

@section('title', $document->nom . ' - Archives - MyGest')
@section('page-title', 'Document : ' . $document->nom)

@section('content')
<style>
    .mg-label { font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:rgba(255,255,255,.35);margin-bottom:4px; }
    .mg-value { font-size:14px;color:#f1f5f9;font-weight:500; }
</style>

<a href="{{ route('documents.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux archives</a>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;margin-bottom:20px;">{{ session('success') }}</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Informations</div>

        <div style="margin-bottom:14px;">
            <div class="mg-label">Nom</div>
            <div class="mg-value">{{ $document->nom }}</div>
        </div>
        @if($document->description)
        <div style="margin-bottom:14px;">
            <div class="mg-label">Description</div>
            <div class="mg-value" style="font-weight:400;color:rgba(255,255,255,.7);">{{ $document->description }}</div>
        </div>
        @endif
        <div style="margin-bottom:14px;">
            <div class="mg-label">Catégorie</div>
            <div class="mg-value">{{ $document->categorie?->nom ?? '—' }}</div>
        </div>
        <div style="margin-bottom:14px;">
            <div class="mg-label">Date du document</div>
            <div class="mg-value">{{ $document->date_document ? $document->date_document->format('d/m/Y') : '—' }}</div>
        </div>
        <div style="margin-bottom:14px;">
            <div class="mg-label">Taille</div>
            <div class="mg-value">{{ $document->taille_formatee }}</div>
        </div>
        <div style="margin-bottom:14px;">
            <div class="mg-label">Agence</div>
            <div class="mg-value">{{ $document->agence?->name_agence ?? '—' }}</div>
        </div>
        <div style="margin-bottom:14px;">
            <div class="mg-label">Ajouté par</div>
            <div class="mg-value">{{ $document->user?->name ?? '—' }}</div>
        </div>
        <div>
            <div class="mg-label">Archivé le</div>
            <div class="mg-value">{{ $document->created_at->format('d/m/Y à H:i') }}</div>
        </div>
    </div>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Aperçu</div>

        @if($document->est_pdf)
            <iframe src="{{ route('documents.preview', $document) }}"
                style="width:100%;height:420px;border:none;border-radius:8px;background:#fff;"></iframe>
        @elseif($document->est_image)
            <img src="{{ route('documents.preview', $document) }}" alt="{{ $document->nom }}"
                style="width:100%;border-radius:8px;object-fit:contain;max-height:420px;">
        @else
            <div style="padding:48px;text-align:center;color:rgba(255,255,255,.3);font-size:13px;">
                Aperçu non disponible pour ce type de fichier.
            </div>
        @endif
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:10px;">
    @if($document->est_pdf || $document->est_image)
        <a href="{{ route('documents.preview', $document) }}" target="_blank" class="btn-primary" style="text-decoration:none;">Ouvrir dans un nouvel onglet</a>
    @endif
    <a href="{{ route('documents.download', $document) }}" class="btn-primary" style="text-decoration:none;background:rgba(74,222,128,.15);border:1px solid rgba(74,222,128,.2);color:#4ade80;">Télécharger</a>
    <form method="POST" action="{{ route('documents.destroy', $document) }}" onsubmit="return confirm('Supprimer ce document ?');" style="display:inline;">
        @csrf @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
