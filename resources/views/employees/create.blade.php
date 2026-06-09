@extends('layouts.app')

@section('title', 'Créer - Employé - MyGest')

@section('page-title', 'Créer un employé')

@section('content')
    <a href="{{ route('employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-block;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;max-width:640px;">
        <form method="POST" action="{{ route('employees.store') }}">
            @csrf

            <div style="margin-bottom:18px;">
                <label for="nom_complet" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Nom complet</label>
                <input type="text" id="nom_complet" name="nom_complet" value="{{ old('nom_complet') }}" required style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('nom_complet')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="adresse" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Adresse</label>
                <input type="text" id="adresse" name="adresse" value="{{ old('adresse') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('adresse')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="telephone" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('telephone')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="email" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('email')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="type_piece" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Type de pièce</label>
                <select id="type_piece" name="type_piece" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez un type</option>
                    @foreach($typePieces as $value)
                        <option value="{{ $value->value }}" style="color:#000;" {{ old('type_piece') == $value->value ? 'selected' : '' }}>{{ $value->value }}</option>
                    @endforeach
                </select>
                @error('type_piece')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="numero_piece" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Numéro de pièce</label>
                <input type="text" id="numero_piece" name="numero_piece" value="{{ old('numero_piece') }}" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                @error('numero_piece')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="status_matrimonial" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Statut matrimonial</label>
                <select id="status_matrimonial" name="status_matrimonial" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez un statut</option>
                    @foreach($statuses as $value)
                        <option value="{{ $value->value }}" style="color:#000;" {{ old('status_matrimonial') == $value->value ? 'selected' : '' }}>{{ $value->value }}</option>
                    @endforeach
                </select>
                @error('status_matrimonial')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="agence_id" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Agence</label>
                <select id="agence_id" name="agence_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez une agence</option>
                    @foreach($agences as $id => $name)
                        <option value="{{ $id }}" style="color:#000;" {{ old('agence_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('agence_id')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="fonction_id" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Fonction</label>
                <select id="fonction_id" name="fonction_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">Sélectionnez une fonction</option>
                    @foreach($fonctions as $id => $name)
                        <option value="{{ $id }}" style="color:#000;" {{ old('fonction_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('fonction_id')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label for="user_id" style="display:block;font-size:12px;font-weight:500;color:rgba(255,255,255,.65);margin-bottom:6px;">Utilisateur</label>
                <select id="user_id" name="user_id" style="width:100%;padding:11px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:14px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6';this.style.boxShadow='0 0 0 3px rgba(59,130,246,.12)'" onblur="this.style.borderColor='rgba(255,255,255,.08)';this.style.boxShadow='none'">
                    <option value="" style="color:#000;">-- Aucun utilisateur --</option>
                    @foreach($users as $id => $name)
                        <option value="{{ $id }}" style="color:#000;" {{ old('user_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
                @error('user_id')
                    <span style="font-size:12px;color:#f87171;margin-top:5px;display:block;">{{ $message }}</span>
                @enderror
            </div>

            <div>
                <button type="submit" style="padding:11px 28px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;font-family:inherit;cursor:pointer;">Créer</button>
            </div>
        </form>
    </div>
@endsection
