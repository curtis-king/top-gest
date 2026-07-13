@extends('layouts.app')

@section('title', 'Nouvelle écriture comptable - MyGest')

@section('page-title', 'Nouvelle écriture comptable')

@section('content')
<style>
    .mg-input { width:100%; padding:10px 14px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:10px; font-size:13px; font-family:inherit; color:#fff; outline:none; box-sizing:border-box; transition:all .2s; }
    .mg-input:focus { border-color:#3b82f6; }
    .mg-select { width:100%; padding:10px 14px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:10px; font-size:13px; font-family:inherit; color:#fff; outline:none; cursor:pointer; box-sizing:border-box; }
    .mg-select:focus { border-color:#3b82f6; }
    .mg-select option { color:#000; }
    .mg-label { font-size:13px; font-weight:500; color:rgba(255,255,255,.7); display:block; margin-bottom:6px; }
    .lignes-table { width:100%; border-collapse:collapse; font-size:13px; }
    .lignes-table th { text-align:left; padding:10px 8px; color:rgba(255,255,255,.35); font-weight:500; font-size:11px; text-transform:uppercase; border-bottom:1px solid rgba(255,255,255,.06); }
    .lignes-table td { padding:8px; border-bottom:1px solid rgba(255,255,255,.04); vertical-align:middle; }
    .ligne-input { width:100%; padding:7px 10px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:12px; font-family:inherit; color:#fff; outline:none; box-sizing:border-box; }
    .ligne-input:focus { border-color:#3b82f6; }
    .ligne-select { width:100%; padding:7px 10px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:12px; font-family:inherit; color:#fff; outline:none; cursor:pointer; box-sizing:border-box; }
    .ligne-select option { color:#000; }
    .btn-remove-ligne { background:none; border:none; color:#f87171; cursor:pointer; font-size:16px; padding:2px 8px; line-height:1; }
</style>

<a href="{{ route('ecritures-comptables.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

@error('lignes')
    <div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ $message }}
    </div>
@enderror

@php
    $journalPreselectId = \App\Models\JournalComptable::where('code', $prefill['journal_code'] ?? null)->value('id');
@endphp

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
    <form method="POST" action="{{ route('ecritures-comptables.store') }}" id="ecriture-form">
        @csrf

        <input type="hidden" name="source_type" value="{{ request('source_type') }}">
        <input type="hidden" name="source_id" value="{{ request('source_id') }}">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
            <div>
                <label class="mg-label">Journal</label>
                <select name="journal_comptable_id" class="mg-select" required>
                    <option value="" style="color:#000;">Sélectionner un journal</option>
                    @foreach($journaux as $id => $libelle)
                        <option value="{{ $id }}" {{ (old('journal_comptable_id', $journalPreselectId) == $id) ? 'selected' : '' }} style="color:#000;">{{ $libelle }}</option>
                    @endforeach
                </select>
                @error('journal_comptable_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="mg-label">Date d'écriture</label>
                <input type="date" name="date_ecriture" value="{{ old('date_ecriture', $prefill['date_ecriture'] ?? now()->format('Y-m-d')) }}" class="mg-input">
                @error('date_ecriture') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="margin-bottom:18px;">
            <label class="mg-label">Libellé</label>
            <input type="text" name="libelle" value="{{ old('libelle', $prefill['libelle'] ?? '') }}" class="mg-input">
            @error('libelle') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:24px;">
            <div>
                <label class="mg-label">Référence</label>
                <input type="text" name="reference" value="{{ old('reference') }}" class="mg-input">
                @error('reference') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
            <div>
                <label class="mg-label">Agence</label>
                <select name="agence_id" class="mg-select">
                    <option value="" style="color:#000;">Aucune</option>
                    @foreach($agences as $id => $nom)
                        <option value="{{ $id }}" {{ old('agence_id', $prefill['agence_id'] ?? null) == $id ? 'selected' : '' }} style="color:#000;">{{ $nom }}</option>
                    @endforeach
                </select>
                @error('agence_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
            </div>
        </div>

        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
            <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;">Lignes d'écriture</div>
            <button type="button" onclick="ajouterLigne()" class="mg-btn" style="padding:7px 16px;border:none;border-radius:8px;font-size:12px;font-weight:500;font-family:inherit;cursor:pointer;background:#2563eb;color:#fff;">+ Ajouter une ligne</button>
        </div>

        <div style="overflow-x:auto;">
            <table class="lignes-table">
                <thead>
                    <tr>
                        <th style="width:26%;">Compte</th>
                        <th style="width:18%;">Contact</th>
                        <th style="width:20%;">Libellé</th>
                        <th style="width:14%;">Débit</th>
                        <th style="width:14%;">Crédit</th>
                        <th style="width:30px;"></th>
                    </tr>
                </thead>
                <tbody id="lignes-body"></tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" style="text-align:right;padding:10px 8px;font-weight:600;color:#f1f5f9;">Totaux</td>
                        <td style="padding:10px 8px;"><span id="total-debit" style="font-weight:700;">0</span></td>
                        <td style="padding:10px 8px;"><span id="total-credit" style="font-weight:700;">0</span></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="6" style="padding:6px 8px;">
                            <span id="equilibre-message" style="font-size:12px;font-weight:500;"></span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="display:flex;gap:12px;margin-top:24px;">
            <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Enregistrer</button>
            <a href="{{ route('ecritures-comptables.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
        </div>
    </form>
</div>

<script>
    const comptes = @json($comptes->map(fn($c) => ['id' => $c->id, 'label' => $c->numero_compte . ' - ' . $c->libelle]));
    const contacts = @json(collect($contacts)->map(fn($nom, $id) => ['id' => $id, 'label' => $nom])->values());
    const prefillLignes = @json($prefill['lignes'] ?? []);

    let ligneIndex = 0;

    function optionsComptes(selectedId) {
        let html = '<option value="" style="color:#000;">Sélectionner</option>';
        comptes.forEach(function(c) {
            const sel = (selectedId && parseInt(selectedId) === c.id) ? 'selected' : '';
            html += '<option value="' + c.id + '" ' + sel + ' style="color:#000;">' + c.label + '</option>';
        });
        return html;
    }

    function optionsContacts(selectedId) {
        let html = '<option value="" style="color:#000;">Aucun</option>';
        contacts.forEach(function(c) {
            const sel = (selectedId && parseInt(selectedId) === c.id) ? 'selected' : '';
            html += '<option value="' + c.id + '" ' + sel + ' style="color:#000;">' + c.label + '</option>';
        });
        return html;
    }

    function creerLigneHtml(index, data) {
        data = data || {};
        const tr = document.createElement('tr');
        tr.innerHTML =
            '<td><select name="lignes[' + index + '][compte_comptable_id]" class="ligne-select" required>' + optionsComptes(data.compte_comptable_id) + '</select></td>' +
            '<td><select name="lignes[' + index + '][contact_id]" class="ligne-select">' + optionsContacts(data.contact_id) + '</select></td>' +
            '<td><input type="text" name="lignes[' + index + '][libelle]" value="' + (data.libelle ? String(data.libelle).replace(/"/g, '&quot;') : '') + '" class="ligne-input"></td>' +
            '<td><input type="number" step="0.01" min="0" name="lignes[' + index + '][debit]" value="' + (data.debit ? data.debit : '') + '" class="ligne-input ligne-montant" oninput="recalculerTotaux()"></td>' +
            '<td><input type="number" step="0.01" min="0" name="lignes[' + index + '][credit]" value="' + (data.credit ? data.credit : '') + '" class="ligne-input ligne-montant" oninput="recalculerTotaux()"></td>' +
            '<td><button type="button" class="btn-remove-ligne" onclick="supprimerLigne(this)">&times;</button></td>';
        return tr;
    }

    function ajouterLigne(data) {
        const body = document.getElementById('lignes-body');
        const tr = creerLigneHtml(ligneIndex, data || {});
        body.appendChild(tr);
        ligneIndex++;
    }

    function supprimerLigne(btn) {
        const tr = btn.closest('tr');
        tr.parentNode.removeChild(tr);
        recalculerTotaux();
    }

    function recalculerTotaux() {
        let totalDebit = 0;
        let totalCredit = 0;
        document.querySelectorAll('#lignes-body tr').forEach(function(tr) {
            const debitInput = tr.querySelector('input[name$="[debit]"]');
            const creditInput = tr.querySelector('input[name$="[credit]"]');
            totalDebit += parseFloat(debitInput && debitInput.value ? debitInput.value : 0) || 0;
            totalCredit += parseFloat(creditInput && creditInput.value ? creditInput.value : 0) || 0;
        });

        document.getElementById('total-debit').textContent = totalDebit.toLocaleString('fr-FR');
        document.getElementById('total-credit').textContent = totalCredit.toLocaleString('fr-FR');

        const message = document.getElementById('equilibre-message');
        const equilibree = Math.abs(totalDebit - totalCredit) < 0.001 && totalDebit > 0;
        if (totalDebit === 0 && totalCredit === 0) {
            message.textContent = '';
        } else if (equilibree) {
            message.textContent = 'Écriture équilibrée.';
            message.style.color = '#4ade80';
        } else {
            message.textContent = 'Écriture non équilibrée : le total débit doit être égal au total crédit.';
            message.style.color = '#f87171';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (prefillLignes && prefillLignes.length > 0) {
            prefillLignes.forEach(function(l) {
                ajouterLigne(l);
            });
        } else {
            ajouterLigne();
            ajouterLigne();
        }
        recalculerTotaux();
    });
</script>
@endsection
