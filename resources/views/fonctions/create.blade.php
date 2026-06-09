@extends('layouts.app')

@section('title', 'Créer - Fonction - MyGest')

@section('page-title', 'Créer une fonction')

@section('content')
    <a href="{{ route('fonctions.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
        <form method="POST" action="{{ route('fonctions.store') }}">
            @csrf

            <div style="margin-bottom:18px;">
                <label for="name" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Nom</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}" required style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('name')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="description" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Description</label>
                <textarea id="description" name="description" rows="3" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;resize:vertical;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">{{ old('description') }}</textarea>
                @error('description')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="salaire" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Salaire</label>
                <input type="number" id="salaire" name="salaire" value="{{ old('salaire') }}" step="0.01" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('salaire')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
            </div>
        </form>
    </div>
@endsection
