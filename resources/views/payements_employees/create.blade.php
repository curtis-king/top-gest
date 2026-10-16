@extends('layouts.app')

@section('title', 'Nouveau paiement - MyGest')

@section('page-title', 'Nouveau paiement')

@section('content')
<style>
    .pay-field { margin-bottom:16px; }
    .pay-label { display:block; font-size:12px; font-weight:500; color:rgba(255,255,255,.55); margin-bottom:6px; letter-spacing:.2px; }
    .pay-input { width:100%; padding:11px 14px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:10px; font-size:14px; font-family:inherit; color:#fff; outline:none; transition:border-color .2s,box-shadow .2s; }
    .pay-input:focus { border-color:#3b82f6; box-shadow:0 0 0 3px rgba(59,130,246,.12); }
    .pay-input::placeholder { color:rgba(255,255,255,.2); }
    .pay-error { font-size:12px; color:#f87171; margin-top:5px; display:block; }
    .pay-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
    .pay-readonly { cursor:default; color:#60a5fa; font-weight:600; }
    .pay-auto { font-size:11px; color:rgba(255,255,255,.3); display:block; margin-top:2px; }
    .pay-summary { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:12px; padding:20px; margin-top:8px; }
    .pay-summary-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; }
    .pay-summary-row + .pay-summary-row { border-top:1px solid rgba(255,255,255,.04); }
    .pay-summary-label { font-size:13px; color:rgba(255,255,255,.45); }
    .pay-summary-value { font-size:14px; font-weight:600; color:#f1f5f9; }
    .pay-summary-value.net { color:#4ade80; font-size:16px; }
    .pay-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:16px; }
    .pay-detail-card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:12px; padding:16px; }
    .pay-detail-title { font-size:12px; font-weight:600; margin-bottom:12px; display:flex; align-items:center; gap:6px; }
    .pay-detail-item { display:flex; align-items:center; justify-content:space-between; padding:8px 0; border-bottom:1px solid rgba(255,255,255,.04); }
    .pay-detail-empty { text-align:center; padding:12px; color:rgba(255,255,255,.25); font-size:13px; }
    @media (max-width:640px) { .pay-grid, .pay-detail-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('payements-employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

<div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:28px;">
    <form method="POST" action="{{ route('payements-employees.store') }}" id="payForm">
        @csrf

        <div class="pay-grid">
            <div class="pay-field">
                <label class="pay-label" for="employee_id">Employé</label>
                <select name="employee_id" id="employee_id" class="pay-input" required onchange="onPayChange()">
                    <option value="" style="color:#000;">Sélectionner</option>
                    @foreach($employees as $id => $nom)
                        <option value="{{ $id }}" style="color:#000;" {{ old('employee_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                    @endforeach
                </select>
                @error('employee_id') <span class="pay-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-field">
                <label class="pay-label" for="status">Statut</label>
                <select name="status" id="status" class="pay-input" required>
                    <option value="" style="color:#000;">Sélectionner</option>
                    @foreach($statuses as $s)
                        <option value="{{ $s->value }}" style="color:#000;" {{ old('status') == $s->value ? 'selected' : '' }}>{{ $s->value }}</option>
                    @endforeach
                </select>
                @error('status') <span class="pay-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pay-grid">
            <div class="pay-field">
                <label class="pay-label" for="mois">Mois</label>
                <select name="mois" id="mois" class="pay-input" required onchange="onPayChange()">
                    <option value="" style="color:#000;">Mois</option>
                    @php $moisFr = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre']; @endphp
                    @foreach($moisFr as $i => $m)
                        <option value="{{ $m }}" style="color:#000;" {{ old('mois') == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
                @error('mois') <span class="pay-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-field">
                <label class="pay-label" for="annee">Année</label>
                <select name="annee" id="annee" class="pay-input" required onchange="onPayChange()">
                    @for($y = date('Y'); $y >= date('Y') - 5; $y--)
                        <option value="{{ $y }}" style="color:#000;" {{ old('annee') == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                @error('annee') <span class="pay-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pay-grid">
            <div class="pay-field">
                <label class="pay-label" for="salaire_base">Salaire de base (FCFA)</label>
                <input type="number" name="salaire_base" id="salaire_base" value="{{ old('salaire_base') }}" step="0.01" min="0" class="pay-input pay-readonly" placeholder="0" readonly>
                <span class="pay-auto" id="salaireInfo">Sélectionnez un employé</span>
                @error('salaire_base') <span class="pay-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-field">
                <label class="pay-label" for="date_paiement">Date de paiement</label>
                <input type="datetime-local" name="date_paiement" id="date_paiement" value="{{ old('date_paiement') }}" class="pay-input">
                @error('date_paiement') <span class="pay-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pay-grid">
            <div class="pay-field">
                <label class="pay-label" for="total_primes">Total primes (FCFA)</label>
                <input type="number" name="total_primes" id="total_primes" value="{{ old('total_primes') }}" step="0.01" min="0" class="pay-input" placeholder="0" readonly style="color:#4ade80;cursor:default;">
                <span class="pay-auto">Calculé automatiquement</span>
                @error('total_primes') <span class="pay-error">{{ $message }}</span> @enderror
            </div>

            <div class="pay-field">
                <label class="pay-label" for="total_retenus">Total retenus (FCFA)</label>
                <input type="number" name="total_retenus" id="total_retenus" value="{{ old('total_retenus') }}" step="0.01" min="0" class="pay-input" placeholder="0" readonly style="color:#f87171;cursor:default;">
                <span class="pay-auto">Calculé automatiquement</span>
                @error('total_retenus') <span class="pay-error">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="pay-summary">
            <div class="pay-summary-row">
                <span class="pay-summary-label">Salaire de base</span>
                <span class="pay-summary-value" id="preview_base">0 FCFA</span>
            </div>
            <div class="pay-summary-row">
                <span class="pay-summary-label">+ Total primes</span>
                <span class="pay-summary-value" style="color:#4ade80;" id="preview_primes">0 FCFA</span>
            </div>
            <div class="pay-summary-row">
                <span class="pay-summary-label">- Total retenus</span>
                <span class="pay-summary-value" style="color:#f87171;" id="preview_retenus">0 FCFA</span>
            </div>
            <div class="pay-summary-row">
                <span class="pay-summary-label">Net à payer</span>
                <span class="pay-summary-value net" id="preview_net">0 FCFA</span>
            </div>
        </div>

        <div id="payDetail" style="display:none;" class="pay-detail-grid">
            <div class="pay-detail-card">
                <div class="pay-detail-title" style="color:#4ade80;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Primes du mois
                </div>
                <div id="payDetailPrimes"><div class="pay-detail-empty">Aucune prime pour cette période</div></div>
            </div>
            <div class="pay-detail-card">
                <div class="pay-detail-title" style="color:#f87171;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                    Retenus du mois
                </div>
                <div id="payDetailRetenus"><div class="pay-detail-empty">Aucune retenue pour cette période</div></div>
            </div>
        </div>

        <input type="hidden" name="net_a_payer" id="net_a_payer" value="{{ old('net_a_payer', 0) }}">

        <button type="submit" style="margin-top:20px;padding:12px 32px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:14px;font-weight:600;font-family:inherit;cursor:pointer;transition:background .2s;" onmouseover="this.style.background='#3b82f6'" onmouseout="this.style.background='#2563eb'">Créer le paiement</button>
    </form>
</div>

<script>
    const employeesData = @json($employeesData);
    const moisMap = {'Janvier':1,'Février':2,'Mars':3,'Avril':4,'Mai':5,'Juin':6,'Juillet':7,'Août':8,'Septembre':9,'Octobre':10,'Novembre':11,'Décembre':12};

    function onPayChange() {
        const empId = parseInt(document.getElementById('employee_id').value);
        const moisNom = document.getElementById('mois').value;
        const annee = parseInt(document.getElementById('annee').value);
        const moisNum = moisMap[moisNom];

        const emp = employeesData.find(e => e.id === empId);

        if (emp) {
            document.getElementById('salaire_base').value = emp.salaire.toFixed(2);
            document.getElementById('salaireInfo').textContent = 'Salaire de la fonction';
        } else {
            document.getElementById('salaire_base').value = '0.00';
            document.getElementById('salaireInfo').textContent = 'Sélectionnez un employé';
        }

        if (empId && moisNum && annee) {
            let totalPrimes = 0, totalRetenus = 0;
            const primesHtml = [], retenusHtml = [];

            (emp ? emp.primes : []).forEach(function(p) {
                if (p.mois === moisNum && p.annee === annee) {
                    totalPrimes += p.montant;
                    primesHtml.push(
                        '<div class="pay-detail-item">' +
                        '<span style="font-size:13px;color:rgba(255,255,255,.65);">' + p.motif + '</span>' +
                        '<span style="font-size:13px;font-weight:600;color:#4ade80;">' + p.montant.toLocaleString('fr-FR', {minimumFractionDigits:0,maximumFractionDigits:0}) + ' F</span>' +
                        '</div>'
                    );
                }
            });

            (emp ? emp.retenus : []).forEach(function(r) {
                if (r.mois === moisNum && r.annee === annee) {
                    totalRetenus += r.montant;
                    retenusHtml.push(
                        '<div class="pay-detail-item">' +
                        '<div><span style="font-size:13px;color:rgba(255,255,255,.65);">' + r.motif + '</span><span style="font-size:11px;color:rgba(255,255,255,.3);display:block;">' + r.date + '</span></div>' +
                        '<span style="font-size:13px;font-weight:600;color:#f87171;">- ' + r.montant.toLocaleString('fr-FR', {minimumFractionDigits:0,maximumFractionDigits:0}) + ' F</span>' +
                        '</div>'
                    );
                }
            });

            document.getElementById('total_primes').value = totalPrimes.toFixed(2);
            document.getElementById('total_retenus').value = totalRetenus.toFixed(2);

            const detailDiv = document.getElementById('payDetail');
            detailDiv.style.display = 'grid';
            document.getElementById('payDetailPrimes').innerHTML = primesHtml.length
                ? primesHtml.join('')
                : '<div class="pay-detail-empty">Aucune prime pour cette période</div>';
            document.getElementById('payDetailRetenus').innerHTML = retenusHtml.length
                ? retenusHtml.join('')
                : '<div class="pay-detail-empty">Aucune retenue pour cette période</div>';
        } else {
            document.getElementById('total_primes').value = '0.00';
            document.getElementById('total_retenus').value = '0.00';
            document.getElementById('payDetail').style.display = 'none';
        }

        calcNet();
    }

    function calcNet() {
        var base = parseFloat(document.getElementById('salaire_base').value) || 0;
        var primes = parseFloat(document.getElementById('total_primes').value) || 0;
        var retenus = parseFloat(document.getElementById('total_retenus').value) || 0;
        var net = base + primes - retenus;
        var fmt = function(v) { return v.toLocaleString('fr-FR', {minimumFractionDigits:0,maximumFractionDigits:0}) + ' FCFA'; };
        document.getElementById('preview_base').textContent = fmt(base);
        document.getElementById('preview_primes').textContent = fmt(primes);
        document.getElementById('preview_retenus').textContent = fmt(retenus);
        document.getElementById('preview_net').textContent = fmt(net);
        document.getElementById('net_a_payer').value = net.toFixed(2);
    }

    document.addEventListener('DOMContentLoaded', function() {
        if (document.getElementById('employee_id').value) onPayChange();
    });
</script>
@endsection
