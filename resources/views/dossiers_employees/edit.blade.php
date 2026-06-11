@extends('layouts.app')

@section('title', 'Modifier - Dossier Employé - MyGest')

@section('page-title', 'Modifier un dossier employé')

@section('content')
<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <a href="{{ route('dossiers-employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

    <form method="POST" action="{{ route('dossiers-employees.update', $dossierEmployee) }}">
        @csrf
        @method('PUT')

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Employé</label>
            <select name="employee_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                <option value="" style="color:#000;">Sélectionnez</option>
                @foreach ($employees as $id => $nom)
                    <option value="{{ $id }}" style="color:#000;" {{ old('employee_id', $dossierEmployee->employee_id) == $id ? 'selected' : '' }}>{{ $nom }}</option>
                @endforeach
            </select>
            @error('employee_id')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date Engagement</label>
            <input type="date" name="date_engagement" value="{{ old('date_engagement', $dossierEmployee->date_engagement) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('date_engagement')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date Fin</label>
            <input type="date" name="date_fin" value="{{ old('date_fin', $dossierEmployee->date_fin) }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('date_fin')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Type Contrat</label>
            <select name="type_contrat" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                <option value="" style="color:#000;">Sélectionnez</option>
                @foreach ($typesContrat as $typeContrat)
                    <option value="{{ $typeContrat->value }}" style="color:#000;" {{ old('type_contrat', $dossierEmployee->type_contrat) == $typeContrat->value ? 'selected' : '' }}>{{ $typeContrat->value }}</option>
                @endforeach
            </select>
            @error('type_contrat')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Status</label>
            <select name="status" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                <option value="" style="color:#000;">Sélectionnez</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" style="color:#000;" {{ old('status', $dossierEmployee->status) == $status->value ? 'selected' : '' }}>{{ $status->value }}</option>
                @endforeach
            </select>
            @error('status')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Modifier</button>
    </form>
</div>
@endsection
