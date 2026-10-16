@extends('layouts.app')

@section('title', 'Archives - MyGest')
@section('page-title', 'Archives')

@section('content')
<style>
    .mg-input { width:100%;padding:8px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;font-size:13px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box; }
    .mg-input:focus { border-color:#3b82f6; }
    .mg-select { width:100%;padding:8px 12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box; }
    .mg-select option { color:#000; }
    .folder-card { background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px 16px;display:flex;flex-direction:column;align-items:center;gap:10px;text-decoration:none;color:inherit;transition:border-color .2s,background .2s;text-align:center; }
    .folder-card:hover { background:rgba(255,255,255,.06);border-color:rgba(255,255,255,.14); }
    .doc-card { background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:12px;padding:18px;display:flex;gap:16px;align-items:flex-start;transition:border-color .2s; }
    .doc-card:hover { border-color:rgba(255,255,255,.12); }
    .doc-icon { width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .breadcrumb { display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.4);margin-bottom:20px; }
    .breadcrumb a { color:#60a5fa;text-decoration:none; }
    .breadcrumb a:hover { text-decoration:underline; }
</style>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">{{ session('success') }}</div>
@endif

@isset($dossiers)
{{-- ===================== VUE DOSSIERS ===================== --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;">
    <div class="breadcrumb" style="margin-bottom:0;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
        <span>Archives</span>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ route('categories-documents.create') }}" style="padding:8px 16px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;font-weight:500;">+ Nouveau dossier</a>
        <a href="{{ route('documents.create') }}" class="btn-primary" style="text-decoration:none;">+ Ajouter un fichier</a>
    </div>
</div>

@if($dossiers->isEmpty() && $nonClassesCount === 0)
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:56px;text-align:center;">
        <svg viewBox="0 0 24 24" fill="currentColor" style="width:56px;height:56px;color:rgba(255,255,255,.1);margin-bottom:16px;">
            <path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/>
        </svg>
        <div style="color:rgba(255,255,255,.4);font-size:14px;margin-bottom:16px;">Aucun dossier créé pour l'instant.</div>
        <a href="{{ route('categories-documents.create') }}" style="display:inline-block;padding:8px 20px;background:#2563eb;border-radius:8px;color:#fff;text-decoration:none;font-size:13px;font-weight:500;">Créer le premier dossier</a>
    </div>
@else
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(150px,1fr));gap:12px;">
        @foreach($dossiers as $dossier)
            <a href="{{ route('documents.index', ['dossier' => $dossier->id]) }}" class="folder-card">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:52px;height:52px;color:#fbbf24;flex-shrink:0;">
                    <path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/>
                </svg>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#f1f5f9;line-height:1.3;word-break:break-word;">{{ $dossier->nom }}</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:4px;">{{ $dossier->documents_count }} fichier{{ $dossier->documents_count > 1 ? 's' : '' }}</div>
                </div>
            </a>
        @endforeach

        @if($nonClassesCount > 0)
            <a href="{{ route('documents.index', ['dossier' => 0]) }}" class="folder-card">
                <svg viewBox="0 0 24 24" fill="currentColor" style="width:52px;height:52px;color:#64748b;flex-shrink:0;">
                    <path d="M20 6h-8l-2-2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2z"/>
                </svg>
                <div>
                    <div style="font-size:13px;font-weight:600;color:#f1f5f9;line-height:1.3;">Sans catégorie</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:4px;">{{ $nonClassesCount }} fichier{{ $nonClassesCount > 1 ? 's' : '' }}</div>
                </div>
            </a>
        @endif
    </div>
@endif

@else
{{-- ===================== VUE FICHIERS ===================== --}}
<div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;">
    <div class="breadcrumb" style="margin-bottom:0;">
        <a href="{{ route('documents.index') }}" style="display:inline-flex;align-items:center;gap:5px;">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:13px;height:13px;"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            Archives
        </a>
        <span style="color:rgba(255,255,255,.2);">/</span>
        <span style="color:#f1f5f9;">{{ isset($dossier) ? $dossier->nom : 'Sans catégorie' }}</span>
    </div>
    <a href="{{ route('documents.create', $dossierId > 0 ? ['dossier' => $dossierId] : []) }}" class="btn-primary" style="text-decoration:none;">+ Ajouter un fichier</a>
</div>

<form method="GET" style="display:grid;grid-template-columns:1fr @if(auth()->user()->isAdmin()) 1fr @endif auto;gap:12px;margin-bottom:24px;align-items:end;">
    <input type="hidden" name="dossier" value="{{ $dossierId }}">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un fichier…" class="mg-input">
    @if(auth()->user()->isAdmin())
    <select name="agence_id" class="mg-select">
        <option value="">Toutes les agences</option>
        @foreach($agences as $id => $nom)
            <option value="{{ $id }}" {{ request('agence_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
        @endforeach
    </select>
    @endif
    <button type="submit" style="padding:8px 18px;background:#2563eb;border:none;border-radius:8px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;white-space:nowrap;">Filtrer</button>
</form>

@if($documents->isEmpty())
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;text-align:center;padding:56px;">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width:48px;height:48px;color:rgba(255,255,255,.1);margin-bottom:16px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
        <div style="color:rgba(255,255,255,.4);font-size:14px;margin-bottom:16px;">Aucun fichier dans ce dossier.</div>
        <a href="{{ route('documents.create', $dossierId > 0 ? ['dossier' => $dossierId] : []) }}" style="display:inline-block;padding:8px 20px;background:#2563eb;border-radius:8px;color:#fff;text-decoration:none;font-size:13px;font-weight:500;">Ajouter le premier fichier</a>
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:12px;">
        @foreach($documents as $doc)
            @php
                $isPdf = $doc->est_pdf;
                $isImg = $doc->est_image;
                $iconBg    = $isPdf ? 'rgba(248,113,113,.12)' : ($isImg ? 'rgba(96,165,250,.12)' : 'rgba(148,163,184,.1)');
                $iconColor = $isPdf ? '#f87171' : ($isImg ? '#60a5fa' : '#94a3b8');
            @endphp
            <div class="doc-card">
                <div class="doc-icon" style="background:{{ $iconBg }};color:{{ $iconColor }};">
                    @if($isImg)
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                    @else
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:20px;height:20px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/></svg>
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="margin-bottom:4px;">
                        <a href="{{ route('documents.show', $doc) }}" style="font-size:14px;font-weight:600;color:#f1f5f9;text-decoration:none;">{{ $doc->nom }}</a>
                    </div>
                    @if($doc->description)
                        <div style="font-size:12px;color:rgba(255,255,255,.4);margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $doc->description }}</div>
                    @endif
                    <div style="display:flex;gap:16px;flex-wrap:wrap;font-size:11px;color:rgba(255,255,255,.3);">
                        @if($doc->date_document)
                            <span>{{ $doc->date_document->format('d/m/Y') }}</span>
                        @endif
                        <span>{{ $doc->taille_formatee }}</span>
                        @if($doc->agence)
                            <span>{{ $doc->agence->name_agence }}</span>
                        @endif
                        <span>Ajouté par {{ $doc->user?->name ?? '—' }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:6px;flex-shrink:0;">
                    @if($isPdf || $isImg)
                        <a href="{{ route('documents.preview', $doc) }}" target="_blank"
                           style="padding:6px 12px;background:rgba(99,102,241,.15);border:1px solid rgba(99,102,241,.2);border-radius:7px;color:#a5b4fc;text-decoration:none;font-size:12px;font-weight:500;">
                            Aperçu
                        </a>
                    @endif
                    <a href="{{ route('documents.download', $doc) }}"
                       style="padding:6px 12px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.15);border-radius:7px;color:#4ade80;text-decoration:none;font-size:12px;font-weight:500;">
                        Télécharger
                    </a>
                    <form method="POST" action="{{ route('documents.destroy', $doc) }}" onsubmit="return confirm('Supprimer ce fichier ?');">
                        @csrf @method('DELETE')
                        <button type="submit" style="padding:6px 10px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.15);border-radius:7px;color:#f87171;font-size:12px;cursor:pointer;font-family:inherit;">&times;</button>
                    </form>
                </div>
            </div>
        @endforeach
    </div>
    <div style="margin-top:24px;">{{ $documents->links() }}</div>
@endif
@endisset
@endsection
