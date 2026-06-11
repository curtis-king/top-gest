@extends('layouts.app')

@section('title', 'Modifier écriture - MyGest')

@section('page-title', 'Modifier écriture bancaire')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('livrets-bancaires.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('livrets-bancaires.update', $livretBancaire) }}">
            @csrf
            @method('PUT')

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date</label>
                    <input type="date" name="date_action" value="{{ old('date_action', $livretBancaire->date_action?->format('Y-m-d')) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;color-scheme:dark;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('date_action') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Type d'action</label>
                    <select name="type_action" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" {{ old('type_action', $livretBancaire->type_action?->value) == $t->value ? 'selected' : '' }} style="color:#000;">{{ $t->value }}</option>
                        @endforeach
                    </select>
                    @error('type_action') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Motif</label>
                <input type="text" name="motif" value="{{ old('motif', $livretBancaire->motif) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('motif') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Montant (FCFA)</label>
                    <input type="number" name="montant" value="{{ old('montant', $livretBancaire->montant) }}" min="0" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('montant') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Banque</label>
                    <select name="banque_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($banques as $id => $name)
                            <option value="{{ $id }}" {{ old('banque_id', $livretBancaire->banque_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('banque_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Raison sociale (contact libre)</label>
                <input type="text" name="raison_social" value="{{ old('raison_social', $livretBancaire->raison_social) }}" placeholder="Ou sélectionner un contact ci-dessous" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('raison_social') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Contact lié</label>
                    <select name="contact_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Aucun</option>
                        @foreach($contacts as $id => $name)
                            <option value="{{ $id }}" {{ old('contact_id', $livretBancaire->contact_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('contact_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Agence</label>
                    <select name="agence_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Sélectionner</option>
                        @foreach($agences as $id => $name)
                            <option value="{{ $id }}" {{ old('agence_id', $livretBancaire->agence_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('agence_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Enregistrer</button>
                <a href="{{ route('livrets-bancaires.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
