@extends('layouts.app')

@section('title', 'Créer - Prime - MyGest')

@section('page-title', 'Créer un(e) Prime')

@section('content')
<a href="{{ route('primes.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <form method="POST" action="{{ route('primes.store') }}">
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

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
            <div>
                <label for="mois" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Mois</label>
                <input type="number" name="mois" id="mois" value="{{ old('mois') }}" min="1" max="12" placeholder="1 - 12" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('mois')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>
            <div>
                <label for="annee" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Année</label>
                <input type="number" name="annee" id="annee" value="{{ old('annee', date('Y')) }}" min="2020" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('annee')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
    </form>
</div>
@endsection
