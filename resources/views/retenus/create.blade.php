@extends('layouts.app')

@section('title', 'Créer - Retenu - MyGest')

@section('page-title', 'Créer un(e) Retenu')

@section('content')
<a href="{{ route('retenus.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <form method="POST" action="{{ route('retenus.store') }}">
        @csrf

        <div style="margin-bottom:18px;">
            <label for="employee_id" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Employé</label>
            <select name="employee_id" id="employee_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                <option value="" style="color:#000;">Sélectionner un employé</option>
                @foreach($employees as $id => $nom)
                    <option value="{{ $id }}" style="color:#000;" {{ old('employee_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                @endforeach
            </select>
            @error('employee_id')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label for="date_retenu" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date</label>
            <input type="date" name="date_retenu" id="date_retenu" value="{{ old('date_retenu') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('date_retenu')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label for="motif" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Motif</label>
            <textarea name="motif" id="motif" rows="3" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;resize:vertical;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">{{ old('motif') }}</textarea>
            @error('motif')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label for="montant" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Montant</label>
            <input type="number" name="montant" id="montant" value="{{ old('montant') }}" step="0.01" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('montant')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
    </form>
</div>
@endsection
