{{--
    Champs obligatoires, pour tous les formulaires de l'application :
    - étoile rouge sur le libellé des champs `required` ;
    - `data-required-if="champ:val1,val2"` rend un champ obligatoire selon la valeur d'un autre ;
    - à l'envoi, les champs vides sont signalés (bordure, message, bandeau) au lieu d'envoyer le formulaire.
    La validation serveur reste la référence : ceci n'évite que les allers-retours inutiles.
--}}
<style>
    .req-star { color: #f87171; font-weight: 600; margin-left: 2px; }
    .req-star[hidden] { display: none; }
    .legende-obligatoire { font-size: 11px; color: rgba(255,255,255,.4); margin-bottom: 14px; }
    .champ-invalide { border-color: #f87171 !important; box-shadow: 0 0 0 1px #f87171 !important; }
    .msg-obligatoire { color: #f87171; font-size: 12px; margin-top: 4px; }
    .alerte-formulaire {
        display: flex; align-items: center; gap: 10px;
        background: rgba(248,113,113,.1); border: 1px solid rgba(248,113,113,.3); color: #fca5a5;
        border-radius: 10px; padding: 10px 14px; font-size: 13px; margin-bottom: 16px;
    }
</style>

<script>
(function () {
    const CHAMPS = 'input:not([type=hidden]):not([type=submit]):not([type=button]), select, textarea';

    function libelle(champ) {
        const bloc = champ.closest('div');
        return bloc ? bloc.querySelector('label, .mg-label') : null;
    }

    function etoile(champ) {
        const lab = libelle(champ);
        if (!lab) return null;
        let star = lab.querySelector('.req-star');
        if (!star) {
            star = document.createElement('span');
            star.className = 'req-star';
            star.setAttribute('aria-hidden', 'true');
            star.textContent = '*';
            lab.appendChild(star);
        }
        return star;
    }

    function majEtoile(champ) {
        const star = etoile(champ);
        if (star) star.hidden = !champ.required;
    }

    function effacerErreur(champ) {
        champ.classList.remove('champ-invalide');
        const msg = champ.parentElement.querySelector(':scope > .msg-obligatoire[data-pour="' + champ.name + '"]');
        if (msg) msg.remove();
    }

    function signaler(champ, texte) {
        effacerErreur(champ);
        champ.classList.add('champ-invalide');
        const msg = document.createElement('div');
        msg.className = 'msg-obligatoire';
        msg.dataset.pour = champ.name;
        msg.textContent = texte;
        champ.insertAdjacentElement('afterend', msg);
    }

    function bandeau(form, nombre) {
        let alerte = form.querySelector(':scope > .alerte-formulaire');
        if (!nombre) {
            if (alerte) alerte.remove();
            return;
        }
        if (!alerte) {
            alerte = document.createElement('div');
            alerte.className = 'alerte-formulaire';
            alerte.setAttribute('role', 'alert');
            form.prepend(alerte);
        }
        alerte.textContent = nombre === 1
            ? '⚠ 1 champ est vide ou invalide.'
            : '⚠ ' + nombre + ' champs sont vides ou invalides.';
    }

    function initialiser(form) {
        const conditionnels = form.querySelectorAll('[data-required-if]');
        const champs = () => Array.from(form.querySelectorAll(CHAMPS));

        if (!form.querySelector('[required]') && !conditionnels.length) return;
        form.noValidate = true;

        // Champs obligatoires sous condition
        conditionnels.forEach(function (champ) {
            const [source, valeurs] = champ.dataset.requiredIf.split(':');
            const attendues = valeurs.split(',');
            const pilote = form.querySelector('[name="' + source + '"]');
            const evaluer = function () {
                champ.required = !!pilote && attendues.includes(pilote.value);
                majEtoile(champ);
                if (!champ.required) effacerErreur(champ);
            };
            if (pilote) pilote.addEventListener('change', evaluer);
            evaluer();
        });

        champs().filter(c => c.required).forEach(majEtoile);

        // Légende, seulement pour les vrais formulaires (au moins 2 champs saisissables)
        if (champs().length >= 2 && !form.querySelector('.legende-obligatoire')) {
            const legende = document.createElement('div');
            legende.className = 'legende-obligatoire';
            legende.innerHTML = '<span class="req-star">*</span> Champ obligatoire';
            const ancre = form.querySelector(':scope > input[type=hidden]:last-of-type');
            ancre ? ancre.insertAdjacentElement('afterend', legende) : form.prepend(legende);
        }

        form.addEventListener('input', e => { if (e.target.matches(CHAMPS)) effacerErreur(e.target); });
        form.addEventListener('change', e => { if (e.target.matches(CHAMPS)) effacerErreur(e.target); });

        form.addEventListener('submit', function (e) {
            const invalides = champs().filter(function (champ) {
                if (champ.disabled) return false;
                if (champ.checkValidity()) {
                    effacerErreur(champ);
                    return false;
                }
                const v = champ.validity;
                signaler(champ,
                    v.valueMissing ? 'Ce champ est obligatoire.'
                    : v.rangeOverflow ? 'Valeur maximale : ' + champ.max + '.'
                    : v.rangeUnderflow ? 'Valeur minimale : ' + champ.min + '.'
                    : champ.validationMessage);
                return true;
            });

            bandeau(form, invalides.length);

            if (invalides.length) {
                e.preventDefault();
                e.stopImmediatePropagation();
                invalides[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                invalides[0].focus({ preventScroll: true });
            }
        }, true);
    }

    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('form').forEach(initialiser);

        // Champs refusés par le serveur : encadrés en rouge (le message est déjà affiché par la vue).
        const erreursServeur = @json($errors->keys());
        erreursServeur.forEach(function (nom) {
            const champ = document.querySelector('[name="' + nom + '"]');
            if (champ) champ.classList.add('champ-invalide');
        });
        const premier = document.querySelector('.champ-invalide');
        if (premier) premier.scrollIntoView({ block: 'center' });
    });
})();
</script>
