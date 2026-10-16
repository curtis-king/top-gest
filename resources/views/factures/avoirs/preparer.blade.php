@extends('layouts.app')

@section('title', 'Avoir sur ' . $facture->numero_facture . ' - MyGest')

@section('page-title', 'Avoir sur la facture ' . $facture->numero_facture)

@section('content')
@php
    $client = $facture->contact?->raison_social ?: $facture->contact?->nom_complet ?: $facture->raison_social ?: '—';
    $champ = 'width:100%;padding:8px 10px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:8px;font-size:13px;font-family:inherit;color:#fff;outline:none;';
    $fmt = fn($n) => number_format((float) $n, 0, ',', ' ');
@endphp
<div style="max-width:1000px;">
    <a href="{{ route('factures.avoirs.create') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Choisir une autre facture</a>

    {{-- Facture d'origine --}}
    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:20px 24px;margin-bottom:16px;">
        <div style="font-size:12px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;">Facture d'origine</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:14px;">
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);">Numéro</div><div style="font-size:14px;font-weight:600;color:#f1f5f9;">{{ $facture->numero_facture }}</div></div>
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);">Date</div><div style="font-size:14px;color:#f1f5f9;">{{ $facture->date_facture?->format('d/m/Y') ?? '—' }}</div></div>
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);">Client</div><div style="font-size:14px;color:#f1f5f9;">{{ $client }}</div></div>
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);">Total TTC</div><div style="font-size:14px;font-weight:600;color:#4ade80;">{{ $fmt($facture->total_ttc) }} FCFA</div></div>
            <div><div style="font-size:11px;color:rgba(255,255,255,.4);">SFEC</div><div style="font-size:14px;color:{{ $facture->estCertifiee() ? '#4ade80' : '#f59e0b' }};">{{ $facture->estCertifiee() ? 'Certifiée' : 'Non certifiée' }}</div></div>
        </div>
        @unless($facture->estCertifiee())
            <div style="font-size:12px;color:#f59e0b;margin-top:12px;">
                Cette facture n'est pas certifiée : vous pouvez encore la <a href="{{ route('factures.manage', $facture) }}" style="color:#60a5fa;">modifier directement</a>.
                L'avoir ne pourra être certifié qu'après la facture d'origine.
            </div>
        @endunless
    </div>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <div style="font-size:15px;font-weight:600;color:#f1f5f9;margin-bottom:6px;">Étape 2 sur 2 — Contenu de l'avoir</div>
        <div style="font-size:13px;color:rgba(255,255,255,.45);margin-bottom:18px;">
            Toutes les lignes sont reprises : l'avoir annule alors la facture en totalité. Pour un avoir partiel, décochez les lignes à exclure
            ou réduisez la quantité ou le prix (jamais au-delà de ce qui a été facturé).
        </div>

        @error('lignes') <div class="alerte-formulaire" role="alert">⚠ {{ $message }}</div> @enderror

        <form method="POST" action="{{ route('factures.avoirs.store', $facture) }}" id="form-avoir">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Numéro de l'avoir</label>
                    <input type="text" name="numero_facture" value="{{ old('numero_facture', $numero) }}" required style="{{ $champ }}padding:10px 14px;">
                    @error('numero_facture') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date de l'avoir</label>
                    <input type="date" name="date_facture" value="{{ old('date_facture', now()->format('Y-m-d')) }}" required style="{{ $champ }}padding:10px 14px;color-scheme:dark;">
                    @error('date_facture') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Motif / objet</label>
                <input type="text" name="objet" value="{{ old('objet', 'Avoir sur facture ' . $facture->numero_facture) }}" style="{{ $champ }}padding:10px 14px;" placeholder="Ex: Retour de marchandise, erreur de prix…">
            </div>

            <div style="display:flex;gap:8px;margin-bottom:10px;">
                <button type="button" data-tout="1" class="mg-btn" style="padding:6px 12px;background:rgba(255,255,255,.06);border:none;border-radius:8px;color:#f1f5f9;font-size:12px;cursor:pointer;">Tout reprendre</button>
                <button type="button" data-tout="0" class="mg-btn" style="padding:6px 12px;background:rgba(255,255,255,.06);border:none;border-radius:8px;color:#f1f5f9;font-size:12px;cursor:pointer;">Tout décocher</button>
            </div>

            <div style="overflow-x:auto;">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="color:rgba(255,255,255,.4);font-size:11px;text-transform:uppercase;letter-spacing:.4px;text-align:left;">
                            <th style="padding:8px;width:36px;"></th>
                            <th style="padding:8px;">Désignation</th>
                            <th style="padding:8px;width:110px;">Qté <span style="text-transform:none;">(facturée)</span></th>
                            <th style="padding:8px;width:150px;">Prix unitaire</th>
                            <th style="padding:8px;width:130px;text-align:right;">Montant HT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($facture->items as $item)
                            @php
                                $inclus = old('lignes') === null || !empty(old("lignes.{$item->id}.inclure"));
                            @endphp
                            <tr class="ligne-avoir" data-tva="{{ (float) $item->taux_tva }}" style="border-top:1px solid rgba(255,255,255,.05);">
                                <td style="padding:8px;vertical-align:top;">
                                    <input type="checkbox" name="lignes[{{ $item->id }}][inclure]" value="1" class="inclure" {{ $inclus ? 'checked' : '' }} style="width:16px;height:16px;margin-top:8px;cursor:pointer;">
                                </td>
                                <td style="padding:8px;vertical-align:top;">
                                    <input type="text" name="lignes[{{ $item->id }}][description]" value="{{ old("lignes.{$item->id}.description", $item->description) }}" required style="{{ $champ }}">
                                    @if($item->produit_id && $item->depot_id)
                                        <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:3px;">Produit — retour en stock à la validation de l'avoir</div>
                                    @endif
                                    @error("lignes.{$item->id}.description") <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                                </td>
                                <td style="padding:8px;vertical-align:top;">
                                    <input type="number" name="lignes[{{ $item->id }}][quantite]" value="{{ old("lignes.{$item->id}.quantite", $item->quantite) }}" min="1" max="{{ $item->quantite }}" step="1" required class="quantite" style="{{ $champ }}">
                                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:3px;">max {{ $item->quantite }}</div>
                                    @error("lignes.{$item->id}.quantite") <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                                </td>
                                <td style="padding:8px;vertical-align:top;">
                                    <input type="number" name="lignes[{{ $item->id }}][prix_unitaire]" value="{{ old("lignes.{$item->id}.prix_unitaire", (float) $item->prix_unitaire) }}" min="0" max="{{ (float) $item->prix_unitaire }}" step="any" required class="prix" style="{{ $champ }}">
                                    <div style="font-size:11px;color:rgba(255,255,255,.35);margin-top:3px;">max {{ $fmt($item->prix_unitaire) }}</div>
                                    @error("lignes.{$item->id}.prix_unitaire") <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                                </td>
                                <td class="montant" style="padding:8px;vertical-align:top;text-align:right;color:#f1f5f9;padding-top:16px;"></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div style="margin-top:16px;padding-top:14px;border-top:1px solid rgba(255,255,255,.06);display:flex;justify-content:flex-end;">
                <div style="min-width:280px;font-size:13px;">
                    <div style="display:flex;justify-content:space-between;padding:3px 0;color:rgba(255,255,255,.5);"><span>Total HT</span><span id="total-ht"></span></div>
                    @if($facture->tva_active)
                        <div style="display:flex;justify-content:space-between;padding:3px 0;color:rgba(255,255,255,.5);"><span>TVA</span><span id="total-tva"></span></div>
                    @endif
                    @if($facture->centimes_additionnels_actif)
                        <div style="display:flex;justify-content:space-between;padding:3px 0;color:rgba(255,255,255,.5);"><span>Centimes additionnels (5%)</span><span id="total-cent"></span></div>
                    @endif
                    <div style="display:flex;justify-content:space-between;padding-top:8px;margin-top:4px;border-top:1px solid rgba(255,255,255,.06);">
                        <span style="color:rgba(255,255,255,.6);">Montant de l'avoir TTC</span>
                        <span id="total-ttc" style="font-size:20px;font-weight:700;color:#f87171;"></span>
                    </div>
                    <div id="nature-avoir" style="text-align:right;font-size:11px;color:rgba(255,255,255,.4);margin-top:4px;"></div>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;">Créer l'avoir (brouillon)</button>
                <a href="{{ route('factures.manage', $facture) }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;">Annuler</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const form = document.getElementById('form-avoir');
    const totalOrigine = {{ round($facture->total_ttc, 2) }};
    const centimes = {{ $facture->centimes_additionnels_actif ? 'true' : 'false' }};
    const fmt = n => Math.round(n).toLocaleString('fr-FR') + ' FCFA';

    function recalculer() {
        let ht = 0, tva = 0;
        form.querySelectorAll('.ligne-avoir').forEach(function (tr) {
            const inclus = tr.querySelector('.inclure').checked;
            // Ligne exclue : champs désactivés (non envoyés, non contrôlés) et grisés.
            tr.querySelectorAll('input:not(.inclure)').forEach(i => i.disabled = !inclus);
            tr.style.opacity = inclus ? '1' : '.4';
            const montant = inclus ? (parseFloat(tr.querySelector('.quantite').value) || 0) * (parseFloat(tr.querySelector('.prix').value) || 0) : 0;
            tr.querySelector('.montant').textContent = inclus ? fmt(montant) : '—';
            ht += montant;
            tva += Math.round(montant * parseFloat(tr.dataset.tva) / 100);
        });
        const cent = centimes ? Math.round(tva * 0.05) : 0;
        const ttc = ht + tva + cent;
        document.getElementById('total-ht').textContent = fmt(ht);
        if (document.getElementById('total-tva')) document.getElementById('total-tva').textContent = fmt(tva);
        if (document.getElementById('total-cent')) document.getElementById('total-cent').textContent = fmt(cent);
        document.getElementById('total-ttc').textContent = '− ' + fmt(ttc);
        document.getElementById('nature-avoir').textContent = ttc <= 0 ? 'Aucune ligne reprise'
            : (Math.round(ttc) >= Math.round(totalOrigine) ? 'Avoir total : annule la facture' : 'Avoir partiel : corrige la facture');
    }

    form.addEventListener('input', recalculer);
    form.addEventListener('change', recalculer);
    form.querySelectorAll('[data-tout]').forEach(b => b.addEventListener('click', function () {
        form.querySelectorAll('.inclure').forEach(c => c.checked = b.dataset.tout === '1');
        recalculer();
    }));
    recalculer();
})();
</script>
@endpush
@endsection
