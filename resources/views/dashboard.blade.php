@extends('layouts.app')

@section('title', 'Dashboard - MyGest')

@section('page-title', 'Tableau de bord')

@section('content')
    <div class="stats-row" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Clients</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">{{ number_format($clientsCount) }}</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">{{ $fournisseursCount }} fournisseurs</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Revenu du mois</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">{{ number_format($revenuMois, 0, ',', ' ') }} F CFA</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">Factures payées</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Projets</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">{{ $projetsActifs }}</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">En cours</p>
        </article>
        <article style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:22px 24px;">
            <span style="font-size:12px;color:rgba(255,255,255,.45);font-weight:500;text-transform:uppercase;letter-spacing:.4px;">Employés</span>
            <strong style="display:block;font-size:30px;font-weight:700;color:#f1f5f9;margin:8px 0 3px;letter-spacing:-1px;">{{ $employesCount }}</strong>
            <p style="font-size:13px;color:rgba(255,255,255,.3);">{{ $produitsEnAlerte }} alertes stock</p>
        </article>
    </div>

    @if($tauxChange)
    <div style="margin-bottom:16px;">
        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Taux de change (F CFA / XAF)</h2>
                <span style="font-size:11px;color:rgba(255,255,255,.3);">Mise à jour : {{ $tauxChange['date'] }}</span>
            </div>
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:12px;">
                @foreach(['EUR' => 'Euro', 'USD' => 'Dollar US', 'GBP' => 'Livre sterling', 'CAD' => 'Dollar CA', 'CHF' => 'Franc suisse', 'NGN' => 'Naira'] as $code => $nom)
                    @if($tauxChange[$code])
                    <div style="background:rgba(255,255,255,.02);border:1px solid rgba(255,255,255,.04);border-radius:10px;padding:14px 16px;text-align:center;">
                        <div style="font-size:13px;color:rgba(255,255,255,.45);font-weight:500;">{{ $nom }}</div>
                        <div style="font-size:20px;font-weight:700;color:#f1f5f9;margin-top:4px;">{{ number_format(1 / $tauxChange[$code], 2, ',', ' ') }}</div>
                        <div style="font-size:11px;color:rgba(255,255,255,.3);">1 {{ $code }}</div>
                    </div>
                    @endif
                @endforeach
            </div>
        </section>
    </div>
    @endif

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Revenus {{ now()->year }}</h2>
            </div>
            <div style="position:relative;height:220px;">
                <canvas id="revenueChart"></canvas>
            </div>
        </section>

        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Statut des projets</h2>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center;">
                <div style="position:relative;height:180px;">
                    <canvas id="projectChart"></canvas>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($projectStatuses as $status => $count)
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.65);">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $projectColors[$status] ?? '#666' }};flex-shrink:0;"></span>
                            <span>{{ $projectLabels[$status] ?? $status }}</span>
                            <strong style="color:#f1f5f9;margin-left:auto;">{{ $count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Statut des factures</h2>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;align-items:center;">
                <div style="position:relative;height:180px;">
                    <canvas id="factureChart"></canvas>
                </div>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    @foreach($factureStatuses as $status => $count)
                        <div style="display:flex;align-items:center;gap:8px;font-size:13px;color:rgba(255,255,255,.65);">
                            <span style="width:10px;height:10px;border-radius:50%;background:{{ $factureColors[$status] ?? '#666' }};flex-shrink:0;"></span>
                            <span>{{ $factureLabels[$status] ?? $status }}</span>
                            <strong style="color:#f1f5f9;margin-left:auto;">{{ $count }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
                <h2 style="font-size:15px;font-weight:600;color:#f1f5f9;">Activité récente</h2>
            </div>
            <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;">
                @forelse($recentFactures as $facture)
                    <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                        <span style="width:7px;height:7px;border-radius:50%;background:#4ade80;flex-shrink:0;"></span>
                        Facture <strong style="color:rgba(255,255,255,.8);">{{ $facture->numero_facture }}</strong>
                        — {{ $facture->contact?->raison_social ?? 'N/A' }}
                        <span style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.3);">{{ $facture->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li style="font-size:13px;color:rgba(255,255,255,.3);text-align:center;padding:16px;">Aucune facture récente</li>
                @endforelse

                @forelse($recentProjects as $project)
                    <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                        <span style="width:7px;height:7px;border-radius:50%;background:#60a5fa;flex-shrink:0;"></span>
                        Projet <strong style="color:rgba(255,255,255,.8);">{{ $project->nom_project }}</strong>
                        <span style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.3);">{{ $project->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li style="font-size:13px;color:rgba(255,255,255,.3);text-align:center;padding:16px;">Aucun projet récent</li>
                @endforelse

                @forelse($recentMouvements as $mvt)
                    <li style="display:flex;align-items:center;gap:10px;font-size:13px;color:rgba(255,255,255,.65);">
                        <span style="width:7px;height:7px;border-radius:50%;background:#facc15;flex-shrink:0;"></span>
                        {{ $mvt->type_mouvement->value }} — <strong style="color:rgba(255,255,255,.8);">{{ $mvt->produit?->nom ?? 'N/A' }}</strong>
                        ({{ $mvt->quantite }})
                        <span style="margin-left:auto;font-size:11px;color:rgba(255,255,255,.3);">{{ $mvt->created_at->diffForHumans() }}</span>
                    </li>
                @empty
                    <li style="font-size:13px;color:rgba(255,255,255,.3);text-align:center;padding:16px;">Aucun mouvement récent</li>
                @endforelse
            </ul>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/chart.umd.min.js') }}"></script>
    <script>
        (function() {
            var data = {
                revLabels: @json($revenusLabels),
                revData: @json($revenusData),
                projLabels: @json($projectStatuses->keys()->map(fn($s) => $projectLabels[$s] ?? $s)),
                projData: @json($projectStatuses->values()),
                projColors: @json($projectStatuses->keys()->map(fn($s) => $projectColors[$s] ?? '#666')),
                factLabels: @json($factureStatuses->keys()->map(fn($s) => $factureLabels[$s] ?? $s)),
                factData: @json($factureStatuses->values()),
                factColors: @json($factureStatuses->keys()->map(fn($s) => $factureColors[$s] ?? '#666')),
            };

            function init() {
                if (typeof Chart === 'undefined') {
                    return setTimeout(init, 100);
                }
                var gridColor = 'rgba(255,255,255,.08)';
                var textColor = 'rgba(255,255,255,.5)';

                new Chart(document.getElementById('revenueChart'), {
                    type: 'bar',
                    data: {
                        labels: data.revLabels,
                        datasets: [{
                            label: 'Revenus (F CFA)',
                            data: data.revData,
                            backgroundColor: 'rgba(59,130,246,.5)',
                            borderColor: '#3b82f6',
                            borderWidth: 1,
                            borderRadius: 4,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: gridColor },
                                ticks: { color: textColor, callback: function(v) { return v.toLocaleString('fr') + ' F'; } }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: textColor }
                            }
                        }
                    }
                });

                new Chart(document.getElementById('projectChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.projLabels,
                        datasets: [{
                            data: data.projData,
                            backgroundColor: data.projColors,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: { legend: { display: false } }
                    }
                });

                new Chart(document.getElementById('factureChart'), {
                    type: 'doughnut',
                    data: {
                        labels: data.factLabels,
                        datasets: [{
                            data: data.factData,
                            backgroundColor: data.factColors,
                            borderWidth: 0,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: { legend: { display: false } }
                    }
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush
