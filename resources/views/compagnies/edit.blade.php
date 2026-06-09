@extends('layouts.app')

@section('title', 'Modifier - Compagnie - MyGest')

@section('page-title', 'Modifier une compagnie')

@section('content')
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <a href="{{ route('compagnies.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

    <form method="POST" action="{{ route('compagnies.update', $compagnie) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Name</label>
            <input type="text" name="name" value="{{ old('name', $compagnie->name) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('name')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Slogan</label>
            <input type="text" name="slogan" value="{{ old('slogan', $compagnie->slogan) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('slogan')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Forme Juridique</label>
            <input type="text" name="forme_juridique" value="{{ old('forme_juridique', $compagnie->forme_juridique) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('forme_juridique')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">NUI</label>
            <input type="text" name="nui" value="{{ old('nui', $compagnie->nui) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('nui')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">RCCM</label>
            <input type="text" name="rccm" value="{{ old('rccm', $compagnie->rccm) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('rccm')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Logo</label>
            @if ($compagnie->logo)
                <div style="margin-bottom:10px;">
                    <img src="{{ Storage::url($compagnie->logo) }}" alt="Logo" style="max-width:120px;max-height:80px;border-radius:6px;border:1px solid rgba(255,255,255,.06);display:block;margin-bottom:6px;">
                    <span style="font-size:12px;color:rgba(255,255,255,.4);">{{ basename($compagnie->logo) }}</span>
                </div>
            @endif
            <input type="file" name="logo" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('logo')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Modifier</button>
    </form>
</div>
@endsection
