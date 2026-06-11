@extends('layouts.app')

@section('title', 'Produit - MyGest')
@section('page-title', 'Fiche produit')

@section('content')
<div style="max-width:900px;">
    <a href="{{ route('produits.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
        {{-- Infos produit --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:16px;">
                <div>
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:4px;">{{ $produit->code }}</div>
                    <div style="font-size:20px;font-weight:700;color:#f1f5f9;">{{ $produit->nom }}</div>
                    @if($produit->categorie)
                        <div style="font-size:12px;color:rgba(255,255,255,.4);margin-top:3px;">{{ $produit->categorie->nom }}</div>
                    @endif
                </div>
                <a href="{{ route('produits.edit', $produit) }}" style="font-size:12px;font-weight:500;color:#60a5fa;text-decoration:none;padding:6px 12px;border:1px solid rgba(96,165,250,.2);border-radius:8px;">Modifier</a>
            </div>

            @if($produit->description)
                <div style="font-size:13px;color:rgba(255,255,255,.5);margin-bottom:16px;">{{ $produit->description }}</div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
                <div style="padding:12px;background:rgba(255,255,255,.02);border-radius:8px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:4px;">Prix achat</div>
                    <div style="font-size:15px;font-weight:600;color:#f1f5f9;">{{ number_format($produit->prix_achat, 0, ',', ' ') }} FCFA</div>
                </div>
                <div style="padding:12px;background:rgba(255,255,255,.02);border-radius:8px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:4px;">Prix vente</div>
                    <div style="font-size:15px;font-weight:600;color:#4ade80;">{{ number_format($produit->prix_vente, 0, ',', ' ') }} FCFA</div>
                </div>
                <div style="padding:12px;background:rgba(255,255,255,.02);border-radius:8px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:4px;">Unité</div>
                    <div style="font-size:14px;font-weight:500;color:#f1f5f9;">{{ $produit->unite_mesure?->value }}</div>
                </div>
                <div style="padding:12px;background:rgba(255,255,255,.02);border-radius:8px;">
                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-bottom:4px;">Seuil d'alerte</div>
                    <div style="font-size:14px;font-weight:500;color:#f59e0b;">{{ $produit->stock_min }}</div>
                </div>
            </div>

            @if($produit->gestionnaire)
                <div style="margin-top:14px;padding:12px;background:rgba(255,255,255,.02);border-radius:8px;display:flex;align-items:center;gap:10px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width:16px;height:16px;color:rgba(255,255,255,.4);flex-shrink:0;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    <div>
                        <div style="font-size:11px;color:rgba(255,255,255,.35);">Gestionnaire</div>
                        <div style="font-size:13px;font-weight:500;color:#f1f5f9;">{{ $produit->gestionnaire->nom_complet }}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Stock par dépôt --}}
        <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                <div style="font-size:14px;font-weight:600;color:#f1f5f9;">Stock par dépôt</div>
                <div style="font-size:22px;font-weight:700;color:{{ $produit->en_alerte ? '#f87171' : '#4ade80' }};">
                    {{ $produit->stock_total }}
                    <span style="font-size:12px;font-weight:400;color:rgba(255,255,255,.4);">total</span>
                </div>
            </div>

            @forelse($produit->stocks as $stock)
                <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);">
                    <div style="font-size:13px;color:rgba(255,255,255,.7);">{{ $stock->depot->nom }}</div>
                    <div style="font-size:14px;font-weight:600;color:{{ $stock->quantite <= $produit->stock_min ? '#f87171' : '#f1f5f9' }};">
                        {{ $stock->quantite }} {{ $produit->unite_mesure?->value }}
                    </div>
                </div>
            @empty
                <div style="font-size:13px;color:rgba(255,255,255,.3);text-align:center;padding:20px 0;">Aucun stock enregistré.</div>
            @endforelse

            <div style="margin-top:14px;">
                <a href="{{ route('mouvements-stocks.create', ['produit_id' => $produit->id]) }}" style="display:block;text-align:center;padding:10px;background:rgba(59,130,246,.1);border:1px solid rgba(59,130,246,.2);border-radius:8px;color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;">+ Saisir un mouvement</a>
            </div>
        </div>
    </div>

    {{-- Derniers mouvements --}}
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="font-size:14px;font-weight:600;color:#f1f5f9;margin-bottom:16px;">Derniers mouvements</div>
        @forelse($produit->mouvements as $mv)
            @php
                $tc = ['entree'=>'#4ade80','sortie'=>'#f87171','transfert'=>'#60a5fa','ajustement'=>'#f59e0b'][$mv->type_mouvement?->value] ?? '#94a3b8';
            @endphp
            <div style="display:flex;align-items:center;gap:16px;padding:10px 0;border-bottom:1px solid rgba(255,255,255,.04);">
                <span style="display:inline-block;padding:2px 10px;border-radius:20px;font-size:11px;font-weight:500;background:{{ $tc }}22;color:{{ $tc }};min-width:80px;text-align:center;">{{ $mv->type_mouvement?->value }}</span>
                <div style="flex:1;font-size:13px;color:rgba(255,255,255,.6);">{{ $mv->depot->nom }}{{ $mv->depotDestination ? ' → ' . $mv->depotDestination->nom : '' }}</div>
                <div style="font-size:13px;font-weight:600;color:{{ in_array($mv->type_mouvement?->value, ['entree']) ? '#4ade80' : '#f87171' }};">{{ in_array($mv->type_mouvement?->value, ['sortie']) ? '-' : '+' }}{{ $mv->quantite }}</div>
                <div style="font-size:11px;color:rgba(255,255,255,.3);min-width:80px;text-align:right;">{{ $mv->date_mouvement?->format('d/m/Y') }}</div>
            </div>
        @empty
            <div style="font-size:13px;color:rgba(255,255,255,.3);text-align:center;padding:20px 0;">Aucun mouvement.</div>
        @endforelse
    </div>
</div>
@endsection
