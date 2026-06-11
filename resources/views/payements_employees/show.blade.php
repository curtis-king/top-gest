@extends('layouts.app')

@section('title', 'Paiement - ' . $payementEmployee->employee->nom_complet . ' - MyGest')

@section('page-title', 'Détails du paiement')

@section('content')
<style>
    .det-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .det-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .det-card-full { grid-column:1 / -1; }
    .det-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .det-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:640px) { .det-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('payements-employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

<div class="print-header" style="display:none;text-align:center;padding:10px 0 20px;border-bottom:2px solid #2563eb;margin-bottom:20px;">
    @php
        $emp = $payementEmployee->employee;
        $ag = $emp?->agence;
        $comp = $ag?->compagnie;
    @endphp
    @if($comp && $comp->logo)
        <img src="{{ Storage::url($comp->logo) }}" style="max-height:50px;margin-bottom:6px;">
    @endif
    <div style="font-size:18px;font-weight:700;color:#1e293b;">{{ $comp?->name ?? 'MyGest' }}</div>
    <div style="font-size:12px;color:#64748b;">{{ $ag?->name_agence ?? '' }} | {{ $ag?->adresse ?? '' }} | Tel: {{ $ag?->numero_telephone ?? '' }}</div>
    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">RCCM: {{ $comp?->rccm ?? '—' }} | NUI: {{ $comp?->nui ?? '—' }}</div>
</div>

<div class="det-grid">

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Employé</div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Nom</div>
            <div class="det-value">{{ $payementEmployee->employee->nom_complet }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="det-label">Période</div>
            <div class="det-value">{{ $payementEmployee->mois }} {{ $payementEmployee->annee }}</div>
        </div>

        <div>
            <div class="det-label">Statut</div>
            @php
                $sv = $payementEmployee->status?->value ?? '—';
                $sc = ['en_attente'=>'#facc15','valide'=>'#4ade80','paye'=>'#3b82f6','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
            @endphp
            <span class="badge" style="background:{{$sc}}22;color:{{$sc}};">{{ $sv }}</span>
        </div>
    </div>

    <div class="det-card">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Montants</div>

        <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;">
            <span style="font-size:13px;color:rgba(255,255,255,.45);">Salaire de base</span>
            <span style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ number_format($payementEmployee->salaire_base, 0, ',', ' ') }} FCFA</span>
        </div>
        <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,.04);">
            <span style="font-size:13px;color:rgba(255,255,255,.45);">Total primes</span>
            <span style="font-size:14px;font-weight:600;color:#4ade80;">+ {{ number_format($payementEmployee->total_primes ?? 0, 0, ',', ' ') }} FCFA</span>
        </div>
        <div style="padding:10px 0;display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,.04);">
            <span style="font-size:13px;color:rgba(255,255,255,.45);">Total retenus</span>
            <span style="font-size:14px;font-weight:600;color:#f87171;">- {{ number_format($payementEmployee->total_retenus ?? 0, 0, ',', ' ') }} FCFA</span>
        </div>
        <div style="padding:12px 0;display:flex;justify-content:space-between;align-items:center;border-top:1px solid rgba(255,255,255,.08);margin-top:4px;">
            <span style="font-size:14px;color:rgba(255,255,255,.6);font-weight:600;">Net à payer</span>
            <span style="font-size:18px;font-weight:700;color:#4ade80;">{{ number_format($payementEmployee->net_a_payer, 0, ',', ' ') }} FCFA</span>
        </div>
    </div>

    @if($payementEmployee->date_paiement)
    <div class="det-card det-card-full">
        <div style="font-size:13px;font-weight:600;color:rgba(255,255,255,.5);text-transform:uppercase;letter-spacing:.5px;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">Paiement</div>
        <div class="det-label">Date de paiement</div>
        <div class="det-value">{{ $payementEmployee->date_paiement->format('d/m/Y H:i') }}</div>
    </div>
    @endif

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <button onclick="window.print()" class="btn-primary" style="background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.08);text-decoration:none;display:inline-flex;align-items:center;gap:6px;color:rgba(255,255,255,.7);">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:14px;height:14px;"><path d="M6 9V2h12v7"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><path d="M6 14h12v8H6z"/></svg>
        Imprimer le reçu
    </button>
    <a href="{{ route('payements-employees.edit', $payementEmployee) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>
    <form action="{{ route('payements-employees.destroy', $payementEmployee) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')
        <button type="submit" onclick="return confirm('Confirmer la suppression ?')" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>

<style>
    @media print {
        @page { margin: 10mm 8mm 8mm; }
        body { background:#fff !important; }
        .dashboard-sidebar,
        .dashboard-topbar,
        .topbar-actions,
        .print-header ~ .det-grid > .det-card:first-child > div:first-child { display:none !important; }
        .btn-primary, .btn-outline, .wz-back, a[href] { display:none !important; }
        .dashboard-content { padding:0 !important; max-width:100% !important; }
        .dashboard-grid { display:block !important; }
        .print-header { display:block !important; }
        .det-grid { display:block !important; }
        .det-card { background:#fff !important; border:1px solid #e2e8f0 !important; border-radius:6px !important; padding:14px 16px !important; margin-bottom:10px !important; box-shadow:none !important; }
        .det-card-full { border-top:2px solid #2563eb !important; }
        .det-label { color:#64748b !important; font-size:10px !important; }
        .det-value { color:#1e293b !important; font-size:13px !important; }
        .badge { border:1px solid #e2e8f0 !important; background:#f8fafc !important; }
        .badge span { color:#1e293b !important; }
        .det-card:last-of-type .det-value { color:#16a34a !important; }
    }
</style>
<script>
    function printPay() { window.print(); }
</script>
@endsection