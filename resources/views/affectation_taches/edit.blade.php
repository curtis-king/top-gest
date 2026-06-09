@extends('layouts.app')

@section('title', 'Modifier affectation - MyGest')

@section('page-title', "Modifier l'affectation")

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('affectation-taches.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('affectation-taches.update', $affectationTache) }}">
            @csrf
            @method('PUT')

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Tâche</label>
                <select name="tache_project_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @foreach($taches as $id => $name)
                        <option value="{{ $id }}" {{ old('tache_project_id', $affectationTache->tache_project_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                    @endforeach
                </select>
                @error('tache_project_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date d'affectation</label>
                <input type="date" name="date_affectation" value="{{ old('date_affectation', $affectationTache->date_affectation?->format('Y-m-d')) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;color-scheme:dark;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('date_affectation') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Type d'affectation</label>
                @php $isInterne = $affectationTache->employee_id; @endphp
                <div style="display:flex;gap:16px;margin-bottom:12px;">
                    <label style="font-size:13px;color:rgba(255,255,255,.65);display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="type_affectation" value="interne" {{ $isInterne ? 'checked' : '' }} onchange="document.getElementById('employee-field').style.display='block';document.getElementById('externe-field').style.display='none'">
                        Employé (interne)
                    </label>
                    <label style="font-size:13px;color:rgba(255,255,255,.65);display:flex;align-items:center;gap:6px;cursor:pointer;">
                        <input type="radio" name="type_affectation" value="externe" {{ !$isInterne ? 'checked' : '' }} onchange="document.getElementById('employee-field').style.display='none';document.getElementById('externe-field').style.display='block'">
                        Externe
                    </label>
                </div>
            </div>

            <div id="employee-field" style="margin-bottom:18px;display:{{ $isInterne ? 'block' : 'none' }};">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Employé</label>
                <select name="employee_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <option value="" style="color:#000;">Sélectionner un employé</option>
                    @foreach($employees as $id => $name)
                        <option value="{{ $id }}" {{ old('employee_id', $affectationTache->employee_id) == $id ? 'selected' : '' }} style="color:#000;">{{ $name }}</option>
                    @endforeach
                </select>
                @error('employee_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div id="externe-field" style="margin-bottom:18px;display:{{ !$isInterne ? 'block' : 'none' }};">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Nom complet / Raison sociale</label>
                <input type="text" name="nom_complet" value="{{ old('nom_complet', $affectationTache->nom_complet) }}" placeholder="Nom de l'externe ou raison sociale" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                @error('nom_complet') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Mettre à jour</button>
                <a href="{{ route('affectation-taches.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    document.querySelectorAll('input[name="type_affectation"]').forEach(r => {
        r.addEventListener('change', function() {
            document.getElementById('employee-field').style.display = this.value === 'interne' ? 'block' : 'none';
            document.getElementById('externe-field').style.display = this.value === 'externe' ? 'block' : 'none';
        });
    });
</script>
@endsection
