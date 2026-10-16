@extends('layouts.app')

@section('title', 'Gestion - ' . $facture->numero_facture . ' - MyGest')

@section('page-title', 'Gestion : ' . $facture->numero_facture)

@section('content')
<style>
    .mg-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .mg-card { background:rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:24px; }
    .mg-card-full { grid-column:1 / -1; }
    .mg-label { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.35); margin-bottom:4px; }
    .mg-value { font-size:14px; color:#f1f5f9; font-weight:500; }
    .mg-section-title { font-size:13px; font-weight:600; color:rgba(255,255,255,.5); text-transform:uppercase; letter-spacing:.5px; margin-bottom:16px; padding-bottom:10px; border-bottom:1px solid rgba(255,255,255,.06); }
    .badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:12px; font-weight:500; }
    .mg-input { width:100%; padding:8px 12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:13px; font-family:inherit; color:#fff; outline:none; box-sizing:border-box; transition:all .2s; }
    .mg-input:focus { border-color:#3b82f6; }
    .mg-select { width:100%; padding:8px 12px; background:rgba(255,255,255,.04); border:1px solid rgba(255,255,255,.08); border-radius:8px; font-size:13px; font-family:inherit; color:#fff; outline:none; cursor:pointer; box-sizing:border-box; }
    .mg-select:focus { border-color:#3b82f6; }
    .mg-select option { color:#000; }
    .mg-btn { padding:7px 16px; border:none; border-radius:8px; font-size:12px; font-weight:500; font-family:inherit; cursor:pointer; transition:all .2s; }
    @media (max-width:768px) { .mg-grid { grid-template-columns:1fr; } }
</style>

<a href="{{ route('factures.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour aux factures</a>

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
    $tv = $facture->type_facture?->value ?? 'vente';
    $sv = $facture->statut_facture?->value ?? 'brouillon';
    $tc = ['vente'=>'#3b82f6','achat'=>'#f59e0b','avoir'=>'#f87171','proforma'=>'#94a3b8'][$tv]??'rgba(255,255,255,.45)';
    $sc = ['brouillon'=>'#94a3b8','impayee'=>'#f87171','partielle'=>'#f59e0b','payee'=>'#4ade80','annulee'=>'#6b7280'][$sv]??'rgba(255,255,255,.45)';
    $total = $facture->items->sum(fn($i) => $i->quantite * $i->prix_unitaire);
@endphp

<div class="mg-grid">

    {{-- FACTURE INFO --}}
    <div class="mg-card">
        <div class="mg-section-title">Facture</div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Numéro</div>
            <div class="mg-value">{{ $facture->numero_facture }}</div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Date</div>
            <div class="mg-value">{{ $facture->date_facture ? \Carbon\Carbon::parse($facture->date_facture)->format('d/m/Y') : '—' }}</div>
        </div>

        <div style="display:flex;gap:8px;margin-bottom:12px;">
            <div><span class="badge" style="background:{{ $tc }}22;color:{{ $tc }};">{{ $tv }}</span></div>
            <div><span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span></div>
        </div>

        <div style="margin-bottom:12px;">
            <div class="mg-label">Client</div>
            <div class="mg-value">{{ $facture->raison_social ?? $facture->contact?->raison_social ?? '—' }}</div>
        </div>

        <div>
            <div class="mg-label">Agence</div>
            <div class="mg-value">{{ $facture->agence?->name_agence ?? '—' }}</div>
        </div>
    </div>

    {{-- STATUT + TOTAL --}}
    <div class="mg-card">
        <div class="mg-section-title">Statut & Total</div>

        <div style="margin-bottom:16px;">
            <div class="mg-label" style="margin-bottom:6px;">Statut</div>
            <span class="badge" style="background:{{ $sc }}22;color:{{ $sc }};">{{ $sv }}</span>
            @if($facture->estActive() && $tv !== 'proforma')
                <span style="font-size:11px;color:rgba(255,255,255,.35);margin-left:6px;">mis à jour selon les paiements</span>
            @endif

            @can('finance.factures.valider')
                @if($transitions)
                    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:10px;">
                        @foreach($transitions as $cible => $libelleAction)
                            <form method="POST" action="{{ route('factures.statut', $facture) }}"
                                  onsubmit="return confirm('{{ $libelleAction }} ?{{ $cible === 'impayee' ? ' Le stock sera mis à jour.' : ($facture->estActive() ? ' Les mouvements de stock seront contre-passés.' : '') }}');">
                                @csrf
                                <input type="hidden" name="statut_facture" value="{{ $cible }}">
                                <button type="submit" class="mg-btn" style="{{ $cible === 'impayee' ? 'background:#2563eb;color:#fff;' : ($cible === 'annulee' ? 'background:rgba(248,113,113,.12);color:#f87171;' : 'background:rgba(255,255,255,.06);color:#f1f5f9;') }}">{{ $libelleAction }}</button>
                            </form>
                        @endforeach
                    </div>
                @elseif($facture->estActive() && $facture->estCertifiee())
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:8px;">Certifiée : seule une facture d'avoir peut l'annuler ou la corriger.</div>
                @elseif($facture->estActive())
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:8px;">Des paiements sont enregistrés : supprimez-les pour remettre la facture en brouillon ou l'annuler.</div>
                @endif
            @endcan
        </div>

        <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap;">
            @unless($facture->estCertifiee())
            <form method="POST" action="{{ route('factures.tva.toggle', $facture) }}">
                @csrf
                <button type="submit" class="mg-btn" style="background:{{ $facture->tva_active ? 'rgba(74,222,128,.12)' : 'rgba(255,255,255,.06)' }};color:{{ $facture->tva_active ? '#4ade80' : 'rgba(255,255,255,.5)' }};">
                    TVA 18% {{ $facture->tva_active ? '✓' : '—' }}
                </button>
            </form>
            <form method="POST" action="{{ route('factures.centimes-additionnels.toggle', $facture) }}">
                @csrf
                <button type="submit" class="mg-btn" style="background:{{ $facture->centimes_additionnels_actif ? 'rgba(74,222,128,.12)' : 'rgba(255,255,255,.06)' }};color:{{ $facture->centimes_additionnels_actif ? '#4ade80' : 'rgba(255,255,255,.5)' }};">
                    Centimes additionnels 5% {{ $facture->centimes_additionnels_actif ? '✓' : '—' }}
                </button>
            </form>
            @endunless
        </div>

        <div style="padding-top:12px;border-top:1px solid rgba(255,255,255,.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">Total HT</span>
                <span style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            </div>
            @if($facture->tva_active)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">TVA (18%)</span>
                <span style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ number_format($facture->montant_tva, 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
            @if($facture->centimes_additionnels_actif)
            <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 0;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">Centimes additionnels (5%)</span>
                <span style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ number_format($facture->montant_centimes_additionnels, 0, ',', ' ') }} FCFA</span>
            </div>
            @endif
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:8px;margin-top:4px;border-top:1px solid rgba(255,255,255,.06);">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">Total TTC</span>
                <span style="font-size:26px;font-weight:700;color:#4ade80;">{{ number_format($facture->total_ttc, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        @php
            $ecritureLiee = \App\Models\EcritureComptable::where('source_type', 'facture')->where('source_id', $facture->id)->first();
        @endphp
        @if($facture->estActive() && in_array($tv, ['vente', 'achat', 'avoir']))
            <div style="padding-top:12px;margin-top:12px;border-top:1px solid rgba(255,255,255,.04);">
                @if($ecritureLiee)
                    <a href="{{ route('ecritures-comptables.show', $ecritureLiee) }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;">Voir l'écriture {{ $ecritureLiee->numero_ecriture }} &rarr;</a>
                @else
                    <a href="{{ route('ecritures-comptables.create', ['source_type' => 'facture', 'source_id' => $facture->id]) }}" class="mg-btn" style="background:#2563eb;color:#fff;text-decoration:none;display:inline-block;">Comptabiliser cette facture</a>
                @endif
            </div>
        @endif
    </div>

    {{-- CERTIFICATION SFEC --}}
    @if(in_array($tv, ['vente', 'avoir']))
    @php
        $scert = $facture->statut_certification?->value ?? 'non_certifiee';
        $ccert = ['non_certifiee'=>'#94a3b8','certifiee'=>'#4ade80','echec'=>'#f87171'][$scert]??'rgba(255,255,255,.45)';
        $labelCert = ['non_certifiee'=>'Non certifiée','certifiee'=>'Certifiée','echec'=>'Échec'][$scert]??$scert;
        $origineNonCertifiee = $tv === 'avoir' && (!$facture->facture_origine_id || $facture->factureOrigine?->statut_certification?->value !== 'certifiee');
    @endphp
    <div class="mg-card mg-card-full">
        <div class="mg-section-title">Certification SFEC</div>

        @if($tv === 'avoir')
            <div style="margin-bottom:14px;">
                <div class="mg-label">Facture d'origine</div>
                <div class="mg-value">
                    @if($facture->factureOrigine)
                        <a href="{{ route('factures.manage', $facture->factureOrigine) }}" style="color:#60a5fa;text-decoration:none;">{{ $facture->factureOrigine->numero_facture }}</a>
                    @else
                        <span style="color:#f87171;">Aucune — requise pour certifier cet avoir</span>
                    @endif
                </div>
            </div>
        @endif

        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;margin-bottom:14px;">
            <span class="badge" style="background:{{ $ccert }}22;color:{{ $ccert }};">{{ $labelCert }}</span>
            @if($scert === 'echec' && $facture->certification_error)
                <span style="font-size:12px;color:rgba(255,255,255,.4);" title="{{ $facture->certification_error }}">{{ \Illuminate\Support\Str::limit($facture->certification_error, 80) }}</span>
            @endif
        </div>

        @if($scert === 'certifiee')
            <div style="display:flex;gap:24px;flex-wrap:wrap;align-items:flex-start;">
                <div>
                    <div class="mg-label">Numéro de certification</div>
                    <div class="mg-value">{{ $facture->certification_number ?? '—' }}</div>
                    <div class="mg-label" style="margin-top:10px;">Date de certification</div>
                    <div class="mg-value">{{ $facture->certification_date?->format('d/m/Y H:i') ?? '—' }}</div>
                </div>
                @if($facture->certification_qr_code)
                    <div>
                        <img src="data:image/png;base64,{{ $facture->certification_qr_code }}" alt="QR code de certification" style="width:110px;height:110px;background:#fff;border-radius:8px;padding:4px;">
                    </div>
                @endif
            </div>
            @if($tv === 'vente')
                @php $avoirEmis = $facture->avoirEmis(); @endphp
                <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,.06);">
                    @if($avoirEmis)
                        <div class="mg-label">{{ $facture->estAnnuleeParAvoir() ? 'Annulée par l\'avoir' : 'Corrigée par l\'avoir' }}</div>
                        <div class="mg-value">
                            <a href="{{ route('factures.manage', $avoirEmis) }}" style="color:#f87171;text-decoration:none;">{{ $avoirEmis->numero_facture }}</a>
                            <span style="font-size:12px;color:rgba(255,255,255,.45);">— {{ number_format($avoirEmis->total_ttc, 0, ',', ' ') }} FCFA TTC{{ $avoirEmis->estCertifiee() ? ', certifié' : ', non certifié' }}</span>
                        </div>
                    @else
                        <a href="{{ route('factures.avoirs.preparer', $facture) }}" class="mg-btn" style="background:rgba(255,255,255,.06);color:#f1f5f9;text-decoration:none;display:inline-block;">Créer un avoir pour cette facture</a>
                    @endif
                </div>
            @endif
        @else
            @if($origineNonCertifiee)
                <div style="font-size:12px;color:#f59e0b;margin-bottom:10px;">La facture d'origine doit d'abord être certifiée avant de pouvoir certifier cet avoir.</div>
            @endif
            @php $manquesClient = $facture->contact?->manquesSfec() ?? []; @endphp
            @if($manquesClient)
                <div style="font-size:12px;color:#f59e0b;margin-bottom:10px;">
                    Fiche client incomplète pour le SFEC :
                    <ul style="margin:6px 0;padding-left:18px;">
                        @foreach($manquesClient as $manque)
                            <li>{{ $manque }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('contacts.edit', $facture->contact) }}" style="color:#60a5fa;text-decoration:none;">Compléter la fiche de {{ $facture->contact->raison_social ?: $facture->contact->nom_complet }} &rarr;</a>
                </div>
            @endif
            <form method="POST" action="{{ route('factures.certifier', $facture) }}">
                @csrf
                <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;" {{ $origineNonCertifiee ? 'disabled' : '' }}>Certifier {{ $tv === 'avoir' ? 'cet avoir' : 'cette facture' }}</button>
            </form>
        @endif
    </div>
    @endif

    {{-- PAIEMENTS --}}
    @if($tv !== 'proforma' && ($facture->estActive() || $facture->paiements->isNotEmpty()))
    @php
        $paye = (float) $facture->paiements->sum('montant');
        $du = $facture->montantDu();
        $reste = $facture->resteAPayer();
        $avoirsImputes = $tv === 'vente' ? $facture->montantAvoirsValides() : 0;
        $libellePaiement = ['achat' => 'Règlements au fournisseur', 'avoir' => 'Remboursements au client'][$tv] ?? 'Paiements reçus';
    @endphp
    <div class="mg-card mg-card-full">
        <div class="mg-section-title">{{ $libellePaiement }}</div>

        <div style="display:flex;gap:28px;flex-wrap:wrap;margin-bottom:16px;">
            <div><div class="mg-label">Total TTC</div><div class="mg-value">{{ number_format($facture->total_ttc, 0, ',', ' ') }} FCFA</div></div>
            @if($avoirsImputes > 0)
                <div><div class="mg-label">Avoirs imputés</div><div class="mg-value" style="color:#f87171;">− {{ number_format($avoirsImputes, 0, ',', ' ') }} FCFA</div></div>
            @endif
            @if($tv === 'avoir')
                <div><div class="mg-label">À rembourser</div><div class="mg-value">{{ number_format($du, 0, ',', ' ') }} FCFA</div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35);">trop-perçu sur la facture d'origine</div></div>
            @endif
            <div><div class="mg-label">Déjà réglé</div><div class="mg-value" style="color:#4ade80;">{{ number_format($paye, 0, ',', ' ') }} FCFA</div></div>
            <div><div class="mg-label">Reste à régler</div><div class="mg-value" style="color:{{ $reste > 0 ? '#f59e0b' : '#4ade80' }};font-weight:600;">{{ number_format($reste, 0, ',', ' ') }} FCFA</div></div>
        </div>

        @if($facture->paiements->isNotEmpty())
            <table style="width:100%;border-collapse:collapse;font-size:13px;margin-bottom:16px;">
                <thead>
                    <tr style="color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;text-align:left;">
                        <th style="padding:6px 8px;">Date</th><th style="padding:6px 8px;">Moyen</th><th style="padding:6px 8px;">Référence</th>
                        <th style="padding:6px 8px;">Saisi par</th><th style="padding:6px 8px;text-align:right;">Montant</th>
                        <th style="padding:6px 8px;">Comptabilité</th><th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($facture->paiements as $p)
                        <tr style="border-top:1px solid rgba(255,255,255,.05);">
                            <td style="padding:8px;color:rgba(255,255,255,.75);">{{ $p->date_paiement?->format('d/m/Y') }}</td>
                            <td style="padding:8px;color:rgba(255,255,255,.65);">
                                {{ $p->mode_paiement ? str_replace('_', ' ', $p->mode_paiement) : '—' }}
                                @if($p->banque) <div style="font-size:11px;color:rgba(255,255,255,.35);">{{ $p->banque->nom }}@if($p->livret_bancaire_id) · livret ✓@endif</div> @endif
                            </td>
                            <td style="padding:8px;color:rgba(255,255,255,.65);" title="{{ $p->note }}">{{ $p->reference ?: '—' }}</td>
                            <td style="padding:8px;color:rgba(255,255,255,.45);">{{ $p->user?->name ?? '—' }}</td>
                            <td style="padding:8px;text-align:right;color:#4ade80;font-weight:600;">{{ number_format($p->montant, 0, ',', ' ') }}</td>
                            <td style="padding:8px;">
                                @php $ecr = $p->ecriture(); @endphp
                                @if($ecr)
                                    <a href="{{ route('ecritures-comptables.show', $ecr) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;">{{ $ecr->numero_ecriture }}</a>
                                @else
                                    <a href="{{ route('ecritures-comptables.create', ['source_type' => 'paiement_facture', 'source_id' => $p->id]) }}" style="color:#60a5fa;text-decoration:none;font-size:12px;">Comptabiliser</a>
                                @endif
                            </td>
                            <td style="padding:8px;text-align:center;">
                                @can('finance.delete')
                                    <form method="POST" action="{{ route('factures.paiements.destroy', $p) }}" onsubmit="return confirm('Supprimer ce paiement ? Le statut de la facture sera recalculé.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" title="Supprimer" style="background:none;border:none;color:#f87171;cursor:pointer;font-size:14px;">&times;</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        @if($facture->accepteLesPaiements() && $reste > 0)
            @can('finance.create')
                <form method="POST" action="{{ route('factures.paiements.store', $facture) }}">
                    @csrf
                    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px;align-items:end;">
                        <div>
                            <label class="mg-label" style="display:block;margin-bottom:4px;">Date</label>
                            <input type="date" name="date_paiement" value="{{ old('date_paiement', now()->format('Y-m-d')) }}" max="{{ now()->format('Y-m-d') }}" required class="mg-input" style="color-scheme:dark;">
                            @error('date_paiement') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="mg-label" style="display:block;margin-bottom:4px;">Montant</label>
                            <input type="number" name="montant" value="{{ old('montant', $reste) }}" min="1" max="{{ $reste }}" step="any" required class="mg-input">
                            @error('montant') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="mg-label" style="display:block;margin-bottom:4px;">Moyen</label>
                            <select name="mode_paiement" required class="mg-select">
                                @foreach(['especes' => 'Espèces', 'virement' => 'Virement', 'mobile_money' => 'Mobile money', 'cheque' => 'Chèque', 'carte' => 'Carte', 'credit' => 'Crédit'] as $cle => $libelle)
                                    <option value="{{ $cle }}" {{ old('mode_paiement', $facture->mode_paiement) === $cle ? 'selected' : '' }}>{{ $libelle }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mg-label" style="display:block;margin-bottom:4px;">Banque</label>
                            <select name="banque_id" data-required-if="mode_paiement:virement,cheque,carte" class="mg-select">
                                <option value="">— Aucune —</option>
                                @foreach($banques as $id => $nom)
                                    <option value="{{ $id }}" {{ old('banque_id') == $id ? 'selected' : '' }}>{{ $nom }}</option>
                                @endforeach
                            </select>
                            @error('banque_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="mg-label" style="display:block;margin-bottom:4px;">Référence</label>
                            <input type="text" name="reference" value="{{ old('reference') }}" placeholder="N° de chèque, transaction…" class="mg-input">
                        </div>
                        <div>
                            <button type="submit" class="mg-btn" style="background:#16a34a;color:#fff;width:100%;padding:9px 16px;">Enregistrer le paiement</button>
                        </div>
                    </div>
                </form>
            @endcan
        @elseif($facture->estActive() && $reste <= 0)
            <div style="font-size:12px;color:#4ade80;">
                {{ match(true) {
                    $tv === 'avoir' && $du <= 0 => 'Rien à rembourser : l\'avoir s\'impute sur ce qui restait dû.',
                    $tv === 'vente' && $paye <= 0 && $avoirsImputes > 0 => 'Soldée par avoir.',
                    default => 'Entièrement réglée.',
                } }}
            </div>
        @endif
    </div>
    @endif

    {{-- ITEMS --}}
    <div class="mg-card mg-card-full">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">
            <span class="mg-section-title" style="margin:0;padding:0;border:none;">Articles ({{ $facture->items->count() }})</span>
            @unless($facture->estCertifiee())
            <button onclick="document.getElementById('modal-item').style.display='flex'" class="mg-btn" style="background:#2563eb;color:#fff;">+ Ajouter un article</button>
            @endunless
        </div>

        {{-- Modal --}}
        <div id="modal-item" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.6);z-index:9999;align-items:center;justify-content:center;" onclick="if(event.target===this)this.style.display='none'">
            <div style="background:#1e293b;border:1px solid rgba(255,255,255,.08);border-radius:16px;padding:28px;width:460px;max-width:95vw;box-shadow:0 20px 60px rgba(0,0,0,.5);">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
                    <span style="font-size:16px;font-weight:600;color:#f1f5f9;">Nouvel article</span>
                    <button onclick="document.getElementById('modal-item').style.display='none'" style="background:none;border:none;color:rgba(255,255,255,.35);font-size:20px;cursor:pointer;padding:0;line-height:1;">&times;</button>
                </div>
                <form method="POST" action="{{ route('factures.items.store', $facture) }}">
                    @csrf
                    <div style="margin-bottom:16px;">
                        <div class="mg-label" style="margin-bottom:4px;">Type d'article</div>
                        <select name="type_article" id="item-type-article" class="mg-select" onchange="document.getElementById('bloc-item-produit').style.display = this.value === 'produit' ? 'block' : 'none';">
                            <option value="service">Service / prestation intellectuelle</option>
                            <option value="produit">Produit (stocké)</option>
                        </select>
                    </div>
                    <div id="bloc-item-produit" style="display:none;margin-bottom:16px;padding:12px;background:rgba(255,255,255,.03);border-radius:10px;">
                        <div style="margin-bottom:12px;">
                            <div class="mg-label" style="margin-bottom:4px;">Produit</div>
                            <select name="produit_id" data-required-if="type_article:produit" id="item-produit-id" class="mg-select" onchange="const opt=this.options[this.selectedIndex]; if(opt.value){ document.querySelector('[name=description]').value = opt.text; }">
                                <option value="">Sélectionner un produit</option>
                                @foreach($produits as $id => $nom)
                                    <option value="{{ $id }}">{{ $nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <div class="mg-label" style="margin-bottom:4px;">Dépôt (sortie de stock)</div>
                            <select name="depot_id" data-required-if="type_article:produit" class="mg-select">
                                <option value="">Sélectionner un dépôt</option>
                                @foreach($depots as $id => $nom)
                                    <option value="{{ $id }}">{{ $nom }}</option>
                                @endforeach
                            </select>
                            @if($depots->isEmpty())
                                <div style="font-size:11px;color:#f59e0b;margin-top:4px;">Aucun dépôt pour l'agence de cette facture.</div>
                            @endif
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div class="mg-label" style="margin-bottom:4px;">Description</div>
                        <input type="text" name="description" placeholder="Ex: Prestation de service" class="mg-input" required>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:20px;">
                        <div>
                            <div class="mg-label" style="margin-bottom:4px;">Quantité</div>
                            <input type="number" name="quantite" value="1" min="1" class="mg-input" required>
                        </div>
                        <div>
                            <div class="mg-label" style="margin-bottom:4px;">Prix unitaire (FCFA)</div>
                            <input type="number" name="prix_unitaire" min="0" class="mg-input" required>
                        </div>
                    </div>
                    <div style="display:flex;gap:10px;justify-content:flex-end;">
                        <button type="button" onclick="document.getElementById('modal-item').style.display='none'" class="mg-btn" style="background:rgba(255,255,255,.06);color:rgba(255,255,255,.5);">Annuler</button>
                        <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Items list --}}
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr style="border-bottom:1px solid rgba(255,255,255,.06);">
                        <th style="text-align:left;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Description</th>
                        <th style="text-align:center;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Qté</th>
                        <th style="text-align:right;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Prix unit.</th>
                        <th style="text-align:right;padding:10px 8px;color:rgba(255,255,255,.35);font-weight:500;font-size:11px;text-transform:uppercase;">Total</th>
                        <th style="text-align:center;padding:10px 8px;width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($facture->items as $item)
                        @php $st = $item->quantite * $item->prix_unitaire; @endphp
                        <tr style="border-bottom:1px solid rgba(255,255,255,.04);">
                            <td style="padding:12px 8px;color:#f1f5f9;">
                                {{ $item->description }}
                                @if($item->produit_id)
                                    <span class="badge" style="background:rgba(96,165,250,.12);color:#60a5fa;font-size:10px;padding:2px 8px;margin-left:6px;">stock</span>
                                @endif
                            </td>
                            <td style="padding:12px 8px;text-align:center;color:rgba(255,255,255,.65);">{{ $item->quantite }}</td>
                            <td style="padding:12px 8px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($item->prix_unitaire, 0, ',', ' ') }}</td>
                            <td style="padding:12px 8px;text-align:right;color:#4ade80;font-weight:600;">{{ number_format($st, 0, ',', ' ') }}</td>
                            <td style="padding:12px 8px;text-align:center;">
                                @unless($facture->estCertifiee())
                                <form method="POST" action="{{ route('factures.items.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:2px 6px;font-size:14px;" title="Supprimer">&times;</button>
                                </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" style="padding:24px;text-align:center;color:rgba(255,255,255,.3);font-size:13px;">Aucun article. Ajoutez-en un ci-dessus.</td>
                        </tr>
                    @endforelse
                </tbody>
                @if($facture->items->count() > 0)
                <tfoot>
                    <tr style="border-top:2px solid rgba(255,255,255,.1);">
                        <td colspan="3" style="padding:12px 8px;text-align:right;font-weight:600;color:#f1f5f9;font-size:14px;">Total</td>
                        <td style="padding:12px 8px;text-align:right;color:#4ade80;font-weight:700;font-size:18px;">{{ number_format($total, 0, ',', ' ') }} FCFA</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>


    {{-- HISTORIQUE --}}
    <div class="mg-card mg-card-full">
        <div class="mg-section-title">Historique</div>
        @forelse($facture->historique as $h)
            <div style="display:flex;gap:12px;padding:7px 0;border-top:1px solid rgba(255,255,255,.04);font-size:12px;">
                <span style="color:rgba(255,255,255,.35);white-space:nowrap;min-width:120px;">{{ $h->created_at?->format('d/m/Y H:i') }}</span>
                <span style="color:rgba(255,255,255,.75);flex:1;">
                    {{ $h->details }}
                    @if($h->ancien_statut || $h->nouveau_statut)
                        <span style="color:rgba(255,255,255,.4);">({{ $h->ancien_statut ?? '—' }} → {{ $h->nouveau_statut ?? '—' }})</span>
                    @endif
                </span>
                <span style="color:rgba(255,255,255,.45);white-space:nowrap;">{{ $h->user?->name ?? 'Système' }}</span>
            </div>
        @empty
            <div style="font-size:12px;color:rgba(255,255,255,.35);">Aucun événement enregistré (facture antérieure à l'historique).</div>
        @endforelse
    </div>

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('factures.pdf', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Aperçu / Imprimer</a>
    @unless($facture->estCertifiee())
    <a href="{{ route('factures.edit', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;">Modifier</a>
    <form method="POST" action="{{ route('factures.destroy', $facture) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
    @endunless
</div>
@endsection
