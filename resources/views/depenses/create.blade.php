@extends('layouts.app')

@section('title', 'Nouvelle dépense - MyGest')

@section('page-title', 'Nouvelle dépense')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('depenses.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('depenses.store') }}">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Numéro de dépense</label>
                    <input type="text" name="numero_depense" value="{{ old('numero_depense', $nextNum) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('numero_depense') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date</label>
                    <input type="date" name="date_depense" value="{{ old('date_depense', now()->format('Y-m-d')) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('date_depense') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Objet</label>
                <input type="text" name="objet" value="{{ old('objet') }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('objet') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Catégorie</label>
                    <select name="categorie_depense_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Aucune</option>
                        @foreach($categories as $id => $libelle)
                            <option value="{{ $id }}" {{ old('categorie_depense_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
                        @endforeach
                    </select>
                    @error('categorie_depense_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Montant (FCFA)</label>
                    <input type="number" name="montant" value="{{ old('montant') }}" min="0" step="1" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('montant') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Mode de paiement</label>
                <select name="mode_paiement" id="mode_paiement" onchange="toggleBanque()" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @foreach($modes as $m)
                        <option value="{{ $m->value }}" {{ old('mode_paiement') == $m->value ? 'selected' : '' }} style="color:#000;">{{ $m->value }}</option>
                    @endforeach
                </select>
                @error('mode_paiement') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div id="banque_wrapper" style="margin-bottom:18px;display:none;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Banque</label>
                <select name="banque_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <option value="" style="color:#000;">Sélectionner une banque</option>
                    @foreach($banques as $id => $nom)
                        <option value="{{ $id }}" {{ old('banque_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                    @endforeach
                </select>
                @error('banque_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Fournisseur / Contact</label>
                    <select name="contact_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Aucun</option>
                        @foreach($contacts as $id => $raison)
                            <option value="{{ $id }}" {{ old('contact_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $raison }}</option>
                        @endforeach
                    </select>
                    @error('contact_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Statut</label>
                    <select name="statut" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($statuts as $s)
                            <option value="{{ $s->value }}" {{ old('statut', 'en_attente') == $s->value ? 'selected' : '' }} style="color:#000;">{{ $s->value }}</option>
                        @endforeach
                    </select>
                    @error('statut') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Agence</label>
                <select name="agence_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;box-sizing:border-box;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <option value="" style="color:#000;">Aucune</option>
                    @foreach($agences as $id => $nom)
                        <option value="{{ $id }}" {{ old('agence_id') == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                    @endforeach
                </select>
                @error('agence_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Enregistrer</button>
                <a href="{{ route('depenses.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleBanque() {
        var mode = document.getElementById('mode_paiement').value;
        var wrapper = document.getElementById('banque_wrapper');
        wrapper.style.display = (mode === 'banque') ? 'block' : 'none';
    }
    document.addEventListener('DOMContentLoaded', toggleBanque);
</script>
@endsection
