@extends('layouts.app')

@section('title', 'Nouveau dépôt - MyGest')
@section('page-title', 'Nouveau dépôt')

@section('content')
<div style="max-width:560px;">
    <a href="{{ route('depots.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('depots.store') }}">
            @csrf

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Nom <span style="color:#f87171;">*</span></label>
                <input type="text" name="nom" value="{{ old('nom') }}" placeholder="Ex: Réserve Salle 3, Armoire Direction..." style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('nom') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Description</label>
                <input type="text" name="description" value="{{ old('description') }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('description') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Agence</label>
                <select name="agence_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <option value="" style="color:#000;">Sélectionner</option>
                    @foreach($agences as $id => $name)
                        <option value="{{ $id }}" {{ old('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                    @endforeach
                </select>
                @error('agence_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Enregistrer</button>
                <a href="{{ route('depots.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
