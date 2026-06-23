@extends('layouts.app')

@section('title', 'Nouvelle catégorie - MyGest')
@section('page-title', 'Nouvelle catégorie de document')

@section('content')
<div style="max-width:480px;">
    <a href="{{ route('categories-documents.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;margin-bottom:20px;display:inline-block;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('categories-documents.store') }}">
            @csrf
            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Nom <span style="color:#f87171;">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Juridique"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('nom') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div style="margin-bottom:24px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" placeholder="Optionnel"
                    style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;box-sizing:border-box;"
                    onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            </div>
            <div style="display:flex;gap:12px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;">Créer</button>
                <a href="{{ route('categories-documents.index') }}" style="padding:10px 20px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
