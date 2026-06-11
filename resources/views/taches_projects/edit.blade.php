@extends('layouts.app')

@section('title', 'Modifier - ' . $tacheProject->nom_tache . ' - MyGest')

@section('page-title', 'Modifier la tâche')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('taches-projects.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('taches-projects.update', $tacheProject) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Nom de la tâche</label>
                <input type="text" name="nom_tache" value="{{ old('nom_tache', $tacheProject->nom_tache) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('nom_tache') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Description</label>
                <textarea name="description_tache" rows="3" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;resize:vertical;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">{{ old('description_tache', $tacheProject->description_tache) }}</textarea>
                @error('description_tache') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Projet</label>
                    <select name="project_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($projects as $id => $name)
                            <option value="{{ $id }}" {{ old('project_id', $tacheProject->project_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('project_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Agence</label>
                    <select name="agence_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($agences as $id => $name)
                            <option value="{{ $id }}" {{ old('agence_id', $tacheProject->agence_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('agence_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Coût (FCFA)</label>
                    <input type="number" name="cout_tache" value="{{ old('cout_tache', $tacheProject->cout_tache) }}" min="0" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('cout_tache') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Statut</label>
                    <select name="status" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($statuses as $st)
                            <option value="{{ $st->value }}" {{ old('status', $tacheProject->status?->value) == $st->value ? 'selected' : '' }} style="color:#000;">{{ $st->value }}</option>
                        @endforeach
                    </select>
                    @error('status') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Mettre à jour</button>
                <a href="{{ route('taches-projects.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
