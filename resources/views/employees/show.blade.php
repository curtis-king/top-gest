@extends('layouts.app')

@section('title', $employee->nom_complet . ' - MyGest')

@section('page-title', $employee->nom_complet)

@section('content')
<style>
    .prof-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .prof-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .prof-card-full { grid-column:1 / -1; }
    .prof-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .prof-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .prof-section-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.06); }
    .prof-stat { padding:12px 0; display:flex; align-items:center; justify-content:space-between; }
    .prof-stat + .prof-stat { border-top:1px solid rgba(255,255,255,.04); }
    .prof-stat-label { font-size:13px; color:rgba(255,255,255,.45); }
    .prof-stat-value { font-size:14px; font-weight:600; color:#f1f5f9; }
    .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:768px) { .prof-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('employees.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux employés</a>

<div class="prof-grid">

    <div class="prof-card">
        <div class="prof-section-title">Informations personnelles</div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Nom complet</div>
            <div class="prof-value">{{ $employee->nom_complet }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Adresse</div>
            <div class="prof-value">{{ $employee->adresse ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Téléphone</div>
            <div class="prof-value">{{ $employee->telephone ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Email</div>
            <div class="prof-value">{{ $employee->email ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Statut matrimonial</div>
            <div class="prof-value">{{ $employee->status_matrimonial?->value ?? '—' }}</div>
        </div>
    </div>

    <div class="prof-card">
        <div class="prof-section-title">Informations professionnelles</div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Agence</div>
            <div class="prof-value">{{ $employee->agence?->name_agence ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Compagnie</div>
            <div class="prof-value">{{ $employee->agence?->compagnie?->name ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Fonction</div>
            <div class="prof-value">{{ $employee->fonction?->name ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Utilisateur lié</div>
            <div class="prof-value">{{ $employee->user?->name ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">Type de pièce</div>
            <div class="prof-value">{{ $employee->type_piece?->value ?? '—' }}</div>
        </div>

        <div style="margin-bottom:14px;">
            <div class="prof-label">N° pièce</div>
            <div class="prof-value">{{ $employee->numero_piece ?? '—' }}</div>
        </div>
    </div>

    @if($employee->dossier)
    <div class="prof-card prof-card-full">
        <div class="prof-section-title">Dossier</div>
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;">
            <div>
                <div class="prof-label">Date d'engagement</div>
                <div class="prof-value">{{ \Carbon\Carbon::parse($employee->dossier->date_engagement)->format('d/m/Y') }}</div>
            </div>
            <div>
                <div class="prof-label">Date de fin</div>
                <div class="prof-value">{{ $employee->dossier->date_fin ? \Carbon\Carbon::parse($employee->dossier->date_fin)->format('d/m/Y') : '—' }}</div>
            </div>
            <div>
                <div class="prof-label">Type de contrat</div>
                <div class="prof-value">{{ $employee->dossier->type_contrat?->value ?? '—' }}</div>
            </div>
            <div>
                <div class="prof-label">Statut</div>
                @php
                    $dv = $employee->dossier->status?->value ?? 'inconnu';
                    $dc = ['actif'=>'#4ade80','inactif'=>'#f87171','suspendu'=>'#facc15','termine'=>'#94a3b8'][$dv]??'rgba(255,255,255,.45)';
                @endphp
                <span class="badge" style="background:{{$dc}}22;color:{{$dc}};">{{ $dv }}</span>
            </div>
        </div>
    </div>
    @endif

    @if($employee->conges->count())
    <div class="prof-card prof-card-full">
        <div class="prof-section-title">Congés ({{ $employee->conges->count() }})</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Date début</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Date fin</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Type</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->conges as $c)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $c->date_debut->format('d/m/Y') }}</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $c->date_fin->format('d/m/Y') }}</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $c->type_conge?->value ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($employee->primes->count())
    <div class="prof-card prof-card-full">
        <div class="prof-section-title">Primes ({{ $employee->primes->count() }})</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Motif</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Montant</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Période</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->primes as $p)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $p->motif }}</td>
                        <td style="padding:10px 12px;color:#4ade80;font-weight:600;">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $p->mois }}/{{ $p->annee }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($employee->retenus->count())
    <div class="prof-card prof-card-full">
        <div class="prof-section-title">Retenus ({{ $employee->retenus->count() }})</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Date</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Motif</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Montant</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->retenus as $r)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $r->date_retenu->format('d/m/Y') }}</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $r->motif }}</td>
                        <td style="padding:10px 12px;color:#f87171;font-weight:600;">{{ number_format($r->montant, 0, ',', ' ') }} FCFA</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if($employee->payements->count())
    <div class="prof-card prof-card-full">
        <div class="prof-section-title">Paiements ({{ $employee->payements->count() }})</div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Période</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Salaire base</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Net</th>
                        <th style="padding:10px 12px;text-align:left;color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employee->payements as $pay)
                    <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ $pay->mois }} {{ $pay->annee }}</td>
                        <td style="padding:10px 12px;color:rgba(255,255,255,.65);">{{ number_format($pay->salaire_base, 0, ',', ' ') }} FCFA</td>
                        <td style="padding:10px 12px;color:#4ade80;font-weight:600;">{{ number_format($pay->net_a_payer, 0, ',', ' ') }} FCFA</td>
                        <td style="padding:10px 12px;">
                            @php
                                $sv = $pay->status?->value ?? '—';
                                $sc = ['en_attente'=>'#facc15','valide'=>'#4ade80','paye'=>'#3b82f6','annule'=>'#f87171'][$sv]??'rgba(255,255,255,.45)';
                            @endphp
                            <span class="badge" style="background:{{$sc}}22;color:{{$sc}};">{{ $sv }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

</div>

<div style="margin-top:20px;display:flex;gap:12px;flex-wrap:wrap;">
    <a href="{{ route('employees.edit', $employee) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Modifier</a>

    @if($employee->dossier)
        @php
            $tc = $employee->dossier->type_contrat?->value;
            $contratLabel = $tc === 'stage' ? 'Attestation de stage' : 'Contrat de travail';
            $contratIcon  = $tc === 'stage' ? '🎓' : '📄';
        @endphp
        <a href="{{ route('employees.contrat.stream', $employee) }}"
           target="_blank"
           style="padding:10px 18px;background:rgba(37,99,235,.15);border:1px solid rgba(37,99,235,.35);border-radius:10px;color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;transition:all .25s ease;"
           onmouseover="this.style.background='rgba(37,99,235,.25)'"
           onmouseout="this.style.background='rgba(37,99,235,.15)'">
            {{ $contratIcon }} {{ $contratLabel }}
        </a>
        <a href="{{ route('employees.contrat.download', $employee) }}"
           style="padding:10px 18px;background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);border-radius:10px;color:#34d399;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;transition:all .25s ease;"
           onmouseover="this.style.background='rgba(16,185,129,.2)'"
           onmouseout="this.style.background='rgba(16,185,129,.1)'">
            ⬇ Télécharger
        </a>
    @endif

    <a href="{{ route('employees.pdf', $employee) }}" target="_blank"
       style="padding:10px 18px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.6);text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;transition:all .25s ease;"
       onmouseover="this.style.background='rgba(255,255,255,.08)'"
       onmouseout="this.style.background='rgba(255,255,255,.04)'">
        Fiche PDF
    </a>

    <form method="POST" action="{{ route('employees.destroy', $employee) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;">Supprimer</button>
    </form>
</div>
@endsection