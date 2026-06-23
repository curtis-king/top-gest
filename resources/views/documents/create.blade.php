@extends('layouts.app')

@section('title', 'Ajouter un document - MyGest')
@section('page-title', 'Ajouter un document')

@section('content')
<div style="max-width:600px;">
    <a href="{{ $dossierId > 0 ? route('documents.index', ['dossier' => $dossierId]) : route('documents.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux archives</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data">
            @csrf

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Nom du document <span style="color:#f87171;">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Registre de commerce 2024"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box;transition:all .25s;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('nom') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Fichier <span style="color:#f87171;">*</span></label>
                <input type="file" name="fichier" accept=".pdf,.jpg,.jpeg,.png,.tif,.tiff" id="fichierInput"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:rgba(255,255,255,.7);outline:none;box-sizing:border-box;cursor:pointer;"
                    onchange="previewFichier(this)">
                <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:4px;">PDF, JPG, PNG, TIFF — max 20 Mo</div>
                <div id="fichierPreview" style="display:none;margin-top:10px;padding:10px;background:rgba(255,255,255,.03);border-radius:8px;font-size:12px;color:rgba(255,255,255,.5);"></div>
                @error('fichier') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Catégorie</label>
                    <select name="categorie_document_id"
                        style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;">
                        <option value="" style="color:#000;">Sans catégorie</option>
                        @foreach($categories as $id => $nom)
                            <option value="{{ $id }}" {{ old('categorie_document_id', $dossierId ?: '') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date du document</label>
                    <input type="date" name="date_document" value="{{ old('date_document') }}"
                        style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;color-scheme:dark;box-sizing:border-box;transition:all .25s;"
                        onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                </div>
            </div>

            @if(auth()->user()->isAdmin())
            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Agence</label>
                <select name="agence_id"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;">
                    <option value="" style="color:#000;">Aucune</option>
                    @foreach($agences as $id => $nom)
                        <option value="{{ $id }}" {{ old('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div style="margin-bottom:24px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Description</label>
                <textarea name="description" rows="3" placeholder="Notes ou description optionnelle…"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;resize:vertical;box-sizing:border-box;transition:all .25s;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">{{ old('description') }}</textarea>
            </div>

            <div style="display:flex;gap:12px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;">Archiver</button>
                <a href="{{ $dossierId > 0 ? route('documents.index', ['dossier' => $dossierId]) : route('documents.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
function previewFichier(input) {
    const preview = document.getElementById('fichierPreview');
    if (input.files && input.files[0]) {
        const f = input.files[0];
        const size = f.size >= 1048576 ? (f.size/1048576).toFixed(1)+' Mo' : (f.size/1024).toFixed(0)+' Ko';
        preview.innerHTML = '📄 <strong style="color:#f1f5f9;">' + f.name + '</strong> — ' + size;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
</script>
@endsection
