@extends('layouts.app')

@section('title', 'Écriture - ' . $ecritureComptable->numero_ecriture . ' - MyGest')

@section('page-title', 'Écriture : ' . $ecritureComptable->numero_ecriture)

@section('content')
<style>
    .mg-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .mg-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .mg-card-full { grid-column:1 / -1; }
    .mg-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .mg-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .mg-section-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.06); }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    @media (max-width:768px) { .mg-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('ecritures-comptables.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux écritures</a>

@if(session('success'))
    <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ session('success') }}
    </div>
@endif

@if(session('error'))
    <div style="padding:12px 16px;background:rgba(248,113,113,.1);border:1px solid rgba(248,113,113,.2);border-radius:8px;color:#f87171;font-size:13px;font-weight:500;margin-bottom:20px;">
        {{ session('error') }}
    </div>
@endif

@php
    $sv = $ecritureComptable->statut?->value ?? 'brouillon';
    $sc = ['brouillon'=>'#94a3b8','validee'=>'#4ade80'][$sv] ?? 'rgba(255,255,255,.45)';
    $totalDebit = $ecritureComptable->lignes->sum('debit');
    $totalCredit = $ecritureComptable->lignes->sum('credit');
@endphp

<div class="mg-grid">

    {{-- ECRITURE INFO --}}
    <div class="mg-card">
        <div class="mg-section-title">Écriture</div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Numéro</div>
            <div class="mg-value">{{ $ecritureComptable->numero_ecriture }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Journal</div>
            <div class="mg-value">{{ $ecritureComptable->journal?->code }} &mdash; {{ $ecritureComptable->journal?->libelle }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Date</div>
            <div class="mg-value">{{ $ecritureComptable->date_ecriture ? \Carbon\Carbon::parse($ecritureComptable->date_ecriture)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Libellé</div>
            <div class="mg-value">{{ $ecritureComptable->libelle }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Référence</div>
            <div class="mg-value">{{ $ecritureComptable->reference ?? '—' }}</div>
        </div>

        <div>
            <div class="mg-label">Agence</div>
            <div class="mg-value">{{ $ecritureComptable->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>

    {{-- STATUT --}}
    <div class="mg-card">
        <div class="mg-section-title">Statut</div>

        <div style="display:flex;gap:8px;margin-bottom:16px;">
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Créé par</div>
            <div class="mg-value">{{ $ecritureComptable->createur?->name ?? '—' }}</div>
        </div>

        @if($sv === 'validee')
            <div style="margin-bottom:12px;">
                <div class="mg-label">Validé par</div>
                <div class="mg-value">{{ $ecritureComptable->validateur?->name ?? '—' }} @if($ecritureComptable->validated_at) le {{ $ecritureComptable->validated_at->format('d/m/Y à H:i') }} @endif</div>
            </div>

            <div style="padding:12px 16px;background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.2);border-radius:8px;color:#4ade80;font-size:12px;font-weight:500;margin-top:12px;">
                Écriture validée et intégrée au grand livre.
            </div>
        @else
            <div style="padding-top:12px;border-top:1px solid rgba(255,255,255,.04);">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-size:12px;color:rgba(255,255,255,.45);">Équilibre</span>
                    <span style="font-size:14px;font-weight:700;color:{{ round($totalDebit,2) === round($totalCredit,2) ? '#4ade80' : '#f87171' }};">{{ round($totalDebit,2) === round($totalCredit,2) ? 'Équilibrée' : 'Non équilibrée' }}</span>
                </div>
            </div>
        @endif
    </div>

    {{-- LIGNES --}}
    <div class="mg-card mg-card-full">
        <div class="mg-section-title">Lignes d'écriture ({{ $ecritureComptable->lignes->count() }})</div>

        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="text-align:left;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Compte</th>
                        <th style="text-align:left;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Contact</th>
                        <th style="text-align:left;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Libellé</th>
                        <th style="text-align:right;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Débit</th>
                        <th style="text-align:right;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Crédit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ecritureComptable->lignes as $ligne)
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:12px 8px;color:#f1f5f9;">{{ $ligne->compte?->numero_compte }} - {{ $ligne->compte?->libelle }}</td>
                            <td style="padding:12px 8px;color:rgba(255,255,255,.65);">{{ $ligne->contact?->raison_social ?? '—' }}</td>
                            <td style="padding:12px 8px;color:rgba(255,255,255,.65);">{{ $ligne->libelle ?? '—' }}</td>
                            <td style="padding:12px 8px;text-align:right;color:rgba(255,255,255,.65);">{{ $ligne->debit > 0 ? number_format($ligne->debit, 0, ',', ' ') : '' }}</td>
                            <td style="padding:12px 8px;text-align:right;color:rgba(255,255,255,.65);">{{ $ligne->credit > 0 ? number_format($ligne->credit, 0, ',', ' ') : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:24px;text-align:center;color:rgba(255,255,255,.3);font-size:13px;">Aucune ligne.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($ecritureComptable->lignes->count() > 0)
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td colspan="3" style="padding:12px 8px;text-align:right;font-weight:600;color:#f1f5f9;font-size:14px;">Total</td>
                        <td style="padding:12px 8px;text-align:right;color:#4ade80;font-weight:700;font-size:15px;">{{ number_format($totalDebit, 0, ',', ' ') }}</td>
                        <td style="padding:12px 8px;text-align:right;color:#4ade80;font-weight:700;font-size:15px;">{{ number_format($totalCredit, 0, ',', ' ') }}</td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    @if($sv === 'brouillon')
        <a href="{{ route('ecritures-comptables.edit', $ecritureComptable) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;">Modifier</a>
        <form method="POST" action="{{ route('ecritures-comptables.valider', $ecritureComptable) }}" style="display:inline;" onsubmit="return confirm('Confirmer la validation de cette écriture ? Elle ne sera plus modifiable.');">
            @csrf
            <button type="submit" class="btn-primary" style="background:#16a34a;border:none;cursor:pointer;font-family:inherit;">Valider</button>
        </form>
        <form method="POST" action="{{ route('ecritures-comptables.destroy', $ecritureComptable) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
        </form>
    @else
        <a href="{{ route('ecritures-comptables.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;">Retour à la liste</a>
    @endif
</div>
@endsection
