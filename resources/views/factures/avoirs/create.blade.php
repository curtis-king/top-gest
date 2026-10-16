@extends('layouts.app')

@section('title', 'Nouvel avoir - MyGest')

@section('page-title', 'Nouvel avoir')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('factures.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:6px;">Étape 1 sur 2 — Facture à corriger</div>
        <div style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:20px;">
            Saisissez le numéro de la facture de vente sur laquelle porte l'avoir. Ses lignes seront reprises à l'étape suivante, où vous pourrez retirer ou ajuster ce qui doit l'être.
        </div>

        <form method="GET" action="{{ route('factures.avoirs.create') }}">
            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Numéro de la facture d'origine</label>
                <input type="text" name="numero" list="factures-avoirables" value="{{ old('numero', request('numero')) }}" required autofocus autocomplete="off"
                       placeholder="Ex: FAC-00012"
                       style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;"
                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                <datalist id="factures-avoirables">
                    @foreach($suggestions as $s)
                        <option value="{{ $s->numero_facture }}">{{ $s->contact?->raison_social ?: $s->contact?->nom_complet ?: $s->raison_social ?: '—' }} — {{ $s->date_facture?->format('d/m/Y') }}</option>
                    @endforeach
                </datalist>
                <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:4px;">
                    Seules les factures de vente validées et sans avoir peuvent être corrigées ({{ $suggestions->count() }} disponible(s)).
                </div>
                @error('numero') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;">Charger la facture &rarr;</button>
                <a href="{{ route('factures.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
