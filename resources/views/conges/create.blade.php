@extends('layouts.app')

@section('title', 'Créer - Congé - MyGest')

@section('page-title', 'Créer un(e) Congé')

@section('content')
<a href="{{ route('conges.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
    <form method="POST" action="{{ route('conges.store') }}">
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
            <label for="date_debut" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date début</label>
            <input type="date" name="date_debut" id="date_debut" value="{{ old('date_debut') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('date_debut')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label for="date_fin" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date fin</label>
            <input type="date" name="date_fin" id="date_fin" value="{{ old('date_fin') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
            @error('date_fin')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <div style="margin-bottom:18px;">
            <label for="type_conge" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Type de congé</label>
            <select name="type_conge" id="type_conge" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                <option value="" style="color:#000;">Sélectionner un type</option>
                @foreach($types as $type)
                    <option value="{{ $type->value }}" style="color:#000;" {{ old('type_conge') == $type->value ? 'selected' : '' }}>{{ $type->value }}</option>
                @endforeach
            </select>
            @error('type_conge')
                <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
    </form>
</div>
@endsection
