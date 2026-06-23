@extends('layouts.app')

@section('title', 'Nouveau mouvement - MyGest')
@section('page-title', 'Nouveau mouvement de stock')

@section('content')
<div style="max-width:700px;">
    <a href="{{ route('mouvements-stocks.index') }}" style="color:#60a5fa;text-decoration:none;font-size:13px;font-weight:500;display:inline-flex;align-items:center;gap:6px;margin-bottom:20px;">&larr; Retour</a>

    <div style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:14px;padding:24px;">
        <form method="POST" action="{{ route('mouvements-stocks.store') }}" id="formMouvement">
            @csrf

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Type de mouvement <span style="color:#f87171;">*</span></label>
                    <select name="type_mouvement" id="typeMouvement" onchange="toggleDepotDest()" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        @foreach($types as $t)
                            <option value="{{ $t->value }}" {{ old('type_mouvement') == $t->value ? 'selected' : '' }} style="color:#000;">{{ ucfirst($t->value) }}</option>
                        @endforeach
                    </select>
                    @error('type_mouvement') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Date <span style="color:#f87171;">*</span></label>
                    <input type="date" name="date_mouvement" value="{{ old('date_mouvement', date('Y-m-d')) }}" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;color-scheme:dark;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    @error('date_mouvement') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Produit <span style="color:#f87171;">*</span></label>
                    <select name="produit_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Sélectionner</option>
                        @foreach($produits as $p)
                            <option value="{{ $p->id }}" {{ old('produit_id', request('produit_id')) == $p->id ? 'selected' : '' }} style="color:#000;">{{ $p->nom }} ({{ $p->code }})</option>
                        @endforeach
                    </select>
                    @error('produit_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Quantité <span style="color:#f87171;">*</span></label>
                    <input type="number" name="quantite" id="champQuantite" value="{{ old('quantite', 1) }}" min="1" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                    <div id="ajustementHint" style="display:none;font-size:11px;color:rgba(255,255,255,.4);margin-top:4px;">Valeur négative pour diminuer le stock (ex: -3)</div>
                    @error('quantite') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Dépôt source <span style="color:#f87171;">*</span></label>
                    <select name="depot_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Sélectionner</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" {{ old('depot_id') == $depot->id ? 'selected' : '' }} style="color:#000;">{{ $depot->nom }}{{ $depot->agence ? ' — ' . $depot->agence->name_agence : '' }}</option>
                        @endforeach
                    </select>
                    @error('depot_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
                <div id="depotDestBlock" style="display:none;">
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Dépôt destination <span style="color:#f87171;">*</span></label>
                    <select name="depot_destination_id" style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;cursor:pointer;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <option value="" style="color:#000;">Sélectionner</option>
                        @foreach($depots as $depot)
                            <option value="{{ $depot->id }}" {{ old('depot_destination_id') == $depot->id ? 'selected' : '' }} style="color:#000;">{{ $depot->nom }}{{ $depot->agence ? ' — ' . $depot->agence->name_agence : '' }}</option>
                        @endforeach
                    </select>
                    @error('depot_destination_id') <div style="color:#f87171;font-size:12px;margin-top:4px;">{{ $message }}</div> @enderror
                </div>
            </div>

            <div style="margin-bottom:18px;">
                <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Motif / Référence</label>
                <input type="text" name="motif" value="{{ old('motif') }}" placeholder="Ex: Commande fournisseur #C001, Vente client..." style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:18px;">
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Contact lié (optionnel)</label>
                    <div style="position:relative;">
                        <input type="text" id="contactText" placeholder="Rechercher un contact…" autocomplete="off"
                            value="{{ $oldContactText }}"
                            style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <input type="hidden" name="contact_id" id="contactId" value="{{ $oldContactId }}">
                        <ul id="contactDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;margin:4px 0 0;padding:4px 0;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:10px;list-style:none;z-index:999;max-height:220px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.4);"></ul>
                    </div>
                </div>
                <div>
                    <label style="font-size:13px;font-weight:500;color:rgba(255,255,255,.7);display:block;margin-bottom:6px;">Facture liée (optionnel)</label>
                    <div style="position:relative;">
                        <input type="text" id="factureText" placeholder="Rechercher une facture…" autocomplete="off"
                            value="{{ $oldFactureText }}"
                            style="width:100%;padding:10px 14px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;font-size:13px;font-family:inherit;color:#fff;outline:none;transition:all .25s ease;box-sizing:border-box;"
                            onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='rgba(255,255,255,.08)'">
                        <input type="hidden" name="facture_id" id="factureId" value="{{ $oldFactureId }}">
                        <ul id="factureDropdown" style="display:none;position:absolute;top:100%;left:0;right:0;margin:4px 0 0;padding:4px 0;background:#1e293b;border:1px solid rgba(255,255,255,.1);border-radius:10px;list-style:none;z-index:999;max-height:220px;overflow-y:auto;box-shadow:0 8px 24px rgba(0,0,0,.4);"></ul>
                    </div>
                </div>
            </div>

            <div style="display:flex;gap:12px;margin-top:24px;">
                <button type="submit" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .25s ease;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">Enregistrer</button>
                <a href="{{ route('mouvements-stocks.index') }}" style="padding:10px 24px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:rgba(255,255,255,.5);text-decoration:none;font-size:13px;font-weight:500;text-align:center;transition:all .25s ease;" onmouseover="this.style.borderColor='rgba(255,255,255,.15)'" onmouseout="this.style.borderColor='rgba(255,255,255,.08)'">Annuler</a>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleDepotDest() {
        const type = document.getElementById('typeMouvement').value;
        document.getElementById('depotDestBlock').style.display = type === 'transfert' ? 'block' : 'none';

        const quantiteInput = document.getElementById('champQuantite');
        const hint = document.getElementById('ajustementHint');
        if (type === 'ajustement') {
            quantiteInput.removeAttribute('min');
            hint.style.display = 'block';
        } else {
            quantiteInput.setAttribute('min', '1');
            hint.style.display = 'none';
            if (parseInt(quantiteInput.value) < 1) quantiteInput.value = 1;
        }
    }
    toggleDepotDest();

    function initAutocomplete(inputId, hiddenId, dropdownId, searchUrl) {
        const input     = document.getElementById(inputId);
        const hidden    = document.getElementById(hiddenId);
        const dropdown  = document.getElementById(dropdownId);
        let timer;

        function renderItems(items) {
            dropdown.innerHTML = '';
            if (!items.length) {
                dropdown.innerHTML = '<li style="padding:10px 14px;color:rgba(255,255,255,.35);font-size:13px;">Aucun résultat</li>';
            } else {
                items.forEach(function(item) {
                    const li = document.createElement('li');
                    li.style.cssText = 'padding:9px 14px;cursor:pointer;font-size:13px;color:#f1f5f9;transition:background .15s;';
                    li.innerHTML = '<span style="font-weight:500;">' + escapeHtml(item.text) + '</span>'
                        + (item.sub ? '<span style="display:block;font-size:11px;color:rgba(255,255,255,.35);margin-top:1px;">' + escapeHtml(item.sub) + '</span>' : '');
                    li.addEventListener('mouseenter', function() { this.style.background = 'rgba(255,255,255,.06)'; });
                    li.addEventListener('mouseleave', function() { this.style.background = ''; });
                    li.addEventListener('mousedown', function(e) {
                        e.preventDefault();
                        input.value  = item.text;
                        hidden.value = item.id;
                        dropdown.style.display = 'none';
                    });
                    dropdown.appendChild(li);
                });
            }
            dropdown.style.display = 'block';
        }

        input.addEventListener('input', function() {
            clearTimeout(timer);
            hidden.value = '';
            const q = input.value.trim();
            if (q.length < 1) { dropdown.style.display = 'none'; return; }
            timer = setTimeout(function() {
                fetch(searchUrl + '?q=' + encodeURIComponent(q), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(renderItems);
            }, 220);
        });

        input.addEventListener('focus', function() {
            if (input.value.trim().length >= 1 && dropdown.innerHTML) {
                dropdown.style.display = 'block';
            }
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') { dropdown.style.display = 'none'; }
        });
    }

    function escapeHtml(str) {
        if (!str) return '';
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    initAutocomplete('contactText', 'contactId', 'contactDropdown', '{{ route('contacts.search') }}');
    initAutocomplete('factureText', 'factureId', 'factureDropdown', '{{ route('factures.search') }}');
</script>
@endsection
