@extends('layouts.app')

@section('title', 'Tableau de bord Stock - MyGest')
@section('page-title', 'Tableau de bord Stock')

@section('content')
@php $fmt = fn($n) => number_format((float)$n, 0, ',', '.'); @endphp

<style>
    .stat-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; margin-bottom:28px; }
    .stat-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.07); border-radius:14px; padding:20px 22px; }
    .stat-label { font-size:11px; font-weight:600; color:rgba(255,255,255,.35); text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; }
    .stat-val { font-size:26px; font-weight:700; color:#f1f5f9; letter-spacing:-.5px; }
    .stat-sub { font-size:11px; color:rgba(255,255,255,.3); margin-top:4px; }
    .section-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; margin-bottom:24px; }
    .card { background:rgba(255,255,255,.02); border:1px solid rgba(255,255,255,.06); border-radius:14px; overflow:hidden; }
    .card-head { padding:14px 18px; border-bottom:1px solid rgba(255,255,255,.05); font-size:13px; font-weight:600; color:#f1f5f9; display:flex; justify-content:space-between; align-items:center; }
    .card-body { padding:0; }
    .mini-row { display:flex; align-items:center; justify-content:space-between; padding:10px 18px; border-bottom:1px solid rgba(255,255,255,.03); font-size:13px; }
    .mini-row:last-child { border-bottom:none; }
    .badge { display:inline-block; padding:2px 8px; border-radius:20px; font-size:10px; font-weight:600; }
    .badge-red { background:rgba(248,113,113,.15); color:#f87171; }
    .badge-yellow { background:rgba(251,191,36,.15); color:#fbbf24; }
    .badge-green { background:rgba(74,222,128,.15); color:#4ade80; }
    .chart-wrap { padding:18px; }
    @media(max-width:900px) { .stat-grid { grid-template-columns:1fr 1fr; } .section-grid { grid-template-columns:1fr; } }
</style>

{{-- Flash warning --}}
@if(session('warning'))
    <div style="padding:12px 16px;background:rgba(251,191,36,.1);border:1px solid rgba(251,191,36,.2);border-radius:8px;color:#fbbf24;font-size:13px;font-weight:500;margin-bottom:20px;">
        ⚠ {{ session('warning') }}
    </div>
@endif

{{-- Actions --}}
<div style="display:flex;gap:12px;margin-bottom:24px;flex-wrap:wrap;">
    <a href="{{ route('mouvements-stocks.create') }}" class="btn-primary" style="text-decoration:none;">+ Nouveau mouvement</a>
    <a href="{{ route('stocks.index') }}" class="btn-outline" style="text-decoration:none;">État des stocks</a>
    <a href="{{ route('stocks.pdf.etat') }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ Export état (PDF)</a>
    <a href="{{ route('mouvements-stocks.pdf.rapport') }}" target="_blank" class="btn-outline" style="text-decoration:none;">↓ Rapport mouvements (PDF)</a>
</div>

{{-- Cartes de synthèse --}}
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-label">Valeur totale du stock</div>
        <div class="stat-val">{{ $fmt($valeurTotale) }}</div>
        <div class="stat-sub">FCFA (prix d'achat)</div>
    </div>
    <div class="stat-card" style="border-color:rgba(74,222,128,.15);">
        <div class="stat-label">Mouvements ce mois</div>
        <div class="stat-val" style="color:#4ade80;">{{ $nbMouvMois }}</div>
        <div class="stat-sub">{{ now()->translatedFormat('F Y') }}</div>
    </div>
    <div class="stat-card" style="border-color:rgba(251,191,36,.15);">
        <div class="stat-label">En alerte</div>
        <div class="stat-val" style="color:#fbbf24;">{{ $nbAlerte }}</div>
        <div class="stat-sub">Sous le seuil minimum</div>
    </div>
    <div class="stat-card" style="border-color:rgba(248,113,113,.15);">
        <div class="stat-label">En rupture</div>
        <div class="stat-val" style="color:#f87171;">{{ $nbRupture }}</div>
        <div class="stat-sub">Stock = 0</div>
    </div>
</div>

{{-- Graphique entrées / sorties --}}
<div class="card" style="margin-bottom:24px;">
    <div class="card-head">
        Entrées vs Sorties — 6 derniers mois
        <span style="font-size:11px;color:rgba(255,255,255,.3);font-weight:400;">En quantités</span>
    </div>
    <div class="chart-wrap">
        <canvas id="chartEvolution" height="80"></canvas>
    </div>
</div>

{{-- Alerte + Top produits --}}
<div class="section-grid">
    <div class="card">
        <div class="card-head">
            Produits en alerte
            <a href="{{ route('stocks.index', ['alerte'=>1]) }}" style="font-size:11px;color:#60a5fa;text-decoration:none;">Voir tout</a>
        </div>
        <div class="card-body">
            @forelse($produitsEnAlerte as $s)
                <div class="mini-row">
                    <div>
                        <div style="font-weight:500;color:#f1f5f9;">{{ $s->produit->nom }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.3);">{{ $s->depot->nom }}</div>
                    </div>
                    <div style="text-align:right;">
                        <span class="{{ $s->quantite <= 0 ? 'badge badge-red' : 'badge badge-yellow' }}">
                            {{ $s->quantite }} / {{ $s->produit->stock_min }}
                        </span>
                    </div>
                </div>
            @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucune alerte</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            Top produits mouvementés
            <span style="font-size:11px;color:rgba(255,255,255,.3);font-weight:400;">Ce mois</span>
        </div>
        <div class="card-body">
            @forelse($topProduits as $tp)
                <div class="mini-row">
                    <div style="font-weight:500;color:#f1f5f9;">{{ $tp->produit->nom }}</div>
                    <div style="text-align:right;">
                        <span class="badge badge-green">{{ $tp->nb }} mvt(s)</span>
                        <div style="font-size:11px;color:rgba(255,255,255,.3);margin-top:2px;">{{ $tp->total_qte }} unités</div>
                    </div>
                </div>
            @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucun mouvement ce mois</div>
            @endforelse
        </div>
    </div>
</div>

{{-- Valeur par dépôt + Derniers mouvements --}}
<div class="section-grid">
    <div class="card">
        <div class="card-head">Valeur par dépôt</div>
        <div class="card-body">
            @php $totalDepots = $valeurParDepot->sum('valeur'); @endphp
            @forelse($valeurParDepot as $vd)
                @php $pct = $totalDepots > 0 ? round($vd->valeur / $totalDepots * 100) : 0; @endphp
                <div style="padding:12px 18px;border-bottom:1px solid rgba(255,255,255,.03);">
                    <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:13px;font-weight:500;color:#f1f5f9;">{{ $vd->nom }}</span>
                        <span style="font-size:12px;color:#60a5fa;">{{ $fmt($vd->valeur) }} FCFA</span>
                    </div>
                    <div style="background:rgba(255,255,255,.06);border-radius:4px;height:4px;">
                        <div style="background:#6366f1;height:4px;border-radius:4px;width:{{ $pct }}%;"></div>
                    </div>
                </div>
            @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucun dépôt</div>
            @endforelse
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            Derniers mouvements
            <a href="{{ route('mouvements-stocks.index') }}" style="font-size:11px;color:#60a5fa;text-decoration:none;">Voir tout</a>
        </div>
        <div class="card-body">
            @php $tc = ['entree'=>'#4ade80','sortie'=>'#f87171','transfert'=>'#60a5fa','ajustement'=>'#f59e0b']; @endphp
            @forelse($derniersMovts as $mv)
                <div class="mini-row">
                    <div>
                        <div style="font-weight:500;color:#f1f5f9;font-size:12px;">{{ $mv->produit->nom }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.3);">{{ $mv->depot->nom }} · {{ $mv->date_mouvement?->format('d/m/Y') }}</div>
                    </div>
                    <div style="text-align:right;">
                        @php $color = $tc[$mv->type_mouvement?->value] ?? '#94a3b8'; @endphp
                        <span style="font-size:12px;font-weight:600;color:{{ $color }};">
                            {{ in_array($mv->type_mouvement?->value, ['sortie']) ? '-' : '+' }}{{ $mv->quantite }}
                        </span>
                        <div style="font-size:10px;color:{{ $color }};opacity:.7;">{{ $mv->type_mouvement?->value }}</div>
                    </div>
                </div>
            @empty
                <div style="padding:24px;text-align:center;font-size:13px;color:rgba(255,255,255,.3);">Aucun mouvement</div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('chartEvolution').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($labels),
            datasets: [
                {
                    label: 'Entrées',
                    data: @json($dataEntrees),
                    backgroundColor: 'rgba(74,222,128,.25)',
                    borderColor: '#4ade80',
                    borderWidth: 1.5,
                    borderRadius: 4,
                },
                {
                    label: 'Sorties',
                    data: @json($dataSorties),
                    backgroundColor: 'rgba(248,113,113,.25)',
                    borderColor: '#f87171',
                    borderWidth: 1.5,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: 'rgba(255,255,255,.6)', font: { size: 12 } } }
            },
            scales: {
                x: { ticks: { color: 'rgba(255,255,255,.4)' }, grid: { color: 'rgba(255,255,255,.04)' } },
                y: { ticks: { color: 'rgba(255,255,255,.4)' }, grid: { color: 'rgba(255,255,255,.04)' }, beginAtZero: true }
            }
        }
    });
</script>
@endpush
@endsection
