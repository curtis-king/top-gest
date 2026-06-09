@extends('layouts.app')

@section('title', 'Créer - Dossier employé - MyGest')

@section('page-title', 'Créer un dossier employé')

@section('content')
    <a href="{{ route('dossiers-employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
        <form method="POST" action="{{ route('dossiers-employees.store') }}">
            @csrf

            <div style="margin-bottom:18px;">
                <label for="employee_id" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Employé</label>
                <select id="employee_id" name="employee_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez un employé</option>
                    @foreach($employees as $id => $name)
                        <option value="{{ $id }}" style="color:#000;" {{ old('employee_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('employee_id')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="date_engagement" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date d'engagement</label>
                <input type="date" id="date_engagement" name="date_engagement" value="{{ old('date_engagement') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('date_engagement')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="date_fin" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Date de fin</label>
                <input type="date" id="date_fin" name="date_fin" value="{{ old('date_fin') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('date_fin')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="type_contrat" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Type de contrat</label>
                <select id="type_contrat" name="type_contrat" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez un type</option>
                    @foreach($typesContrat as $value)
                        <option value="{{ $value->value }}" style="color:#000;" {{ old('type_contrat') == $value->value ? 'selected' : '' }}>{{ $value->value }}</option>
                    @endforeach
                </select>
                @error('type_contrat')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="status" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Statut</label>
                <select id="status" name="status" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez un statut</option>
                    @foreach($statuses as $value)
                        <option value="{{ $value->value }}" style="color:#000;" {{ old('status') == $value->value ? 'selected' : '' }}>{{ $value->value }}</option>
                    @endforeach
                </select>
                @error('status')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
            </div>
        </form>
    </div>
@endsection
