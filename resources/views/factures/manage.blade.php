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

        <form method="POST" action="{{ route('factures.statut', $facture) }}" style="display:flex;align-items:flex-end;gap:10px;margin-bottom:16px;">
            @csrf
            <div style="flex:1;">
                <div class="mg-label" style="margin-bottom:4px;">Statut</div>
                <select name="statut_facture" class="mg-select">
                    @foreach($statuts as $s)
                        <option value="{{ $s->value }}" {{ $facture->statut_facture?->value == $s->value ? 'selected' : '' }}>{{ $s->value }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="mg-btn" style="background:#2563eb;color:#fff;white-space:nowrap;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Mettre à jour</button>
        </form>

        <div style="padding-top:12px;border-top:1px solid rgba(255,255,255,.04);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <span style="font-size:12px;color:rgba(255,255,255,.45);">Total</span>
                <span style="font-size:26px;font-weight:700;color:#4ade80;">{{ number_format($total, 0, ',', ' ') }} FCFA</span>
            </div>
        </div>

        @php
            $ecritureLiee = \App\Models\EcritureComptable::where('source_type', 'facture')->where('source_id', $facture->id)->first();
        @endphp
        @if(in_array($sv, ['payee', 'partielle']) && in_array($tv, ['vente', 'achat']))
            <div style="padding-top:12px;margin-top:12px;border-top:1px solid rgba(255,255,255,.04);">
                @if($ecritureLiee)
                    <a href="{{ route('ecritures-comptables.show', $ecritureLiee) }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;">Voir l'écriture {{ $ecritureLiee->numero_ecriture }} &rarr;</a>
                @else
                    <a href="{{ route('ecritures-comptables.create', ['source_type' => 'facture', 'source_id' => $facture->id]) }}" class="mg-btn" style="background:#2563eb;color:#fff;text-decoration:none;display:inline-block;">Comptabiliser cette facture</a>
                @endif
            </div>
        @endif
    </div>

    {{-- ITEMS --}}
    <div class="mg-card mg-card-full">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid rgba(255,255,255,.06);">
            <span class="mg-section-title" style="margin:0;padding:0;border:none;">Articles ({{ $facture->items->count() }})</span>
            <button onclick="document.getElementById('modal-item').style.display='flex'" class="mg-btn" style="background:#2563eb;color:#fff;">+ Ajouter un article</button>
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
                            <td style="padding:12px 8px;color:#f1f5f9;">{{ $item->description }}</td>
                            <td style="padding:12px 8px;text-align:center;color:rgba(255,255,255,.65);">{{ $item->quantite }}</td>
                            <td style="padding:12px 8px;text-align:right;color:rgba(255,255,255,.65);">{{ number_format($item->prix_unitaire, 0, ',', ' ') }}</td>
                            <td style="padding:12px 8px;text-align:right;color:#4ade80;font-weight:600;">{{ number_format($st, 0, ',', ' ') }}</td>
                            <td style="padding:12px 8px;text-align:center;">
                                <form method="POST" action="{{ route('factures.items.destroy', $item) }}" style="display:inline;" onsubmit="return confirm('Supprimer cet article ?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" style="background:none;border:none;color:#f87171;cursor:pointer;padding:2px 6px;font-size:14px;" title="Supprimer">&times;</button>
                                </form>
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

</div>

<div style="margin-top:20px;display:flex;gap:12px;">
    <a href="{{ route('factures.pdf', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;">Aperçu / Imprimer</a>
    <a href="{{ route('factures.edit', $facture) }}" class="btn-primary" style="text-decoration:none;display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);color:#f1f5f9;">Modifier</a>
    <form method="POST" action="{{ route('factures.destroy', $facture) }}" style="display:inline;" onsubmit="return confirm('Confirmer la suppression ?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn-outline" style="border-color:rgba(248,113,113,.2);color:#f87171;cursor:pointer;font-family:inherit;">Supprimer</button>
    </form>
</div>
@endsection
