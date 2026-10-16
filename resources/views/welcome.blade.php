<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TopGest</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #020c2e;
            overflow-x: hidden; /* cache les cartes qui glissent depuis les côtés */
            font-family: 'Segoe UI', system-ui, sans-serif;
        }

        /* ── Lueurs latérales ── */
        .glow-left, .glow-right {
            position: fixed;
            top: 50%;
            transform: translateY(-50%);
            width: 440px;
            height: 85vh;
            pointer-events: none;
            z-index: 0;
        }
        .glow-left  { left: -110px;  background: radial-gradient(ellipse at center, rgba(0,80,255,.68) 0%, transparent 70%); }
        .glow-right { right: -110px; background: radial-gradient(ellipse at center, rgba(0,80,255,.68) 0%, transparent 70%); }

        /* ── Contenu ── */
        .wrap {
            position: relative;
            z-index: 1;
            text-align: center;
            padding: 40px 24px;
            max-width: 1000px;
            width: 100%;
        }

        /* ════════ LOGO ════════ */
        .logo-area {
            display: inline-block;
            margin-bottom: 28px;
            opacity: 0;
            animation: fadeInScale .8s ease .1s forwards;
        }

        /* Conteneur anneau + image */
        .logo-ring-wrap {
            position: relative;
            display: inline-block;
            border-radius: 50%;
            padding: 3px;
            overflow: hidden;
        }

        /* Anneau lumineux INFINI */
        .logo-ring-wrap::before {
            content: '';
            position: absolute;
            inset: -150%;
            background: conic-gradient(
                from 0deg,
                transparent 52%,
                #4db8ff    68%,
                #c8eeff    76%,
                #4db8ff    84%,
                transparent
            );
            animation: spinBorder 3s linear infinite;
        }

        .logo-ring-inner {
            position: relative;
            z-index: 1;
            width: 110px;
            height: 110px;
            border-radius: 50%;
            overflow: hidden;
            background: #071a5e;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-ring-inner img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        /* ════════ TITRE ════════ */
        .app-name {
            font-size: clamp(40px, 7vw, 64px);
            font-weight: 900;
            font-style: italic;
            color: #4db8ff;
            letter-spacing: -1px;
            opacity: 0;
            animation: fadeUp .7s ease .8s forwards;
            margin-bottom: 14px;
        }

        /* ════════ TAGLINE ════════ */
        .tagline {
            font-size: clamp(14px, 2.2vw, 19px);
            font-weight: 700;
            font-style: italic;
            color: #fff;
            opacity: 0;
            animation: fadeUp .7s ease 1.3s forwards;
            margin-bottom: 28px;
        }

        /* ════════ PYRAMIDE — CARTES MODULES ════════ */
        .pyramid { margin-bottom: 36px; }

        .p-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            gap: 16px;
        }
        .p-row.r2 { padding: 0 9%; }
        .p-row.r3 { padding: 0 26%; justify-content: center; }

        /* Carte avec bordure lumineuse tournante INFINIE */
        .mod-card {
            position: relative;
            border-radius: 14px;
            padding: 2px;
            overflow: hidden;
            opacity: 0;
            flex-shrink: 0;
        }

        .mod-card::before {
            content: '';
            position: absolute;
            /* Agrandi à -150% pour que la rotation couvre tous les angles de la carte */
            inset: -150%;
            background: conic-gradient(
                from 0deg,
                transparent 55%,
                #1a6fff    70%,
                rgba(255,255,255,.75) 78%,
                #1a6fff    86%,
                transparent
            );
            animation: spinBorder 4s linear infinite;
        }

        /* Vitesses différentes pour éviter la synchronisation */
        .p-row.r1 .mod-card::before { animation-duration: 5s; }
        .p-row.r2 .mod-card::before { animation-duration: 4s; }
        .p-row.r3 .mod-card::before { animation-duration: 3s; }

        /* Décalages de phase */
        .mod-rh::before       { animation-delay: -1s;   }
        .mod-banque::before   { animation-delay: -3.2s; }
        .mod-clients::before  { animation-delay: -0.7s; }
        .mod-projets::before  { animation-delay: -2.1s; }
        .mod-stock::before    { animation-delay: -1.5s; }
        .mod-archives::before { animation-delay: -3.8s; }

        .mod-card-inner {
            position: relative;
            z-index: 1;
            background: rgba(4, 14, 68, 0.93);
            border-radius: 12px;
            padding: 11px 24px;
            font-size: clamp(12px, 1.8vw, 18px);
            font-weight: 800;
            font-style: italic;
            color: #fff;
            white-space: nowrap;
        }

        /* Entrées en cascade depuis les côtés */
        .mod-rh       { transform: translateX(-500px); animation: fromLeft  .75s cubic-bezier(.22,.68,0,1.2) 1.9s forwards; }
        .mod-banque   { transform: translateX( 500px); animation: fromRight .75s cubic-bezier(.22,.68,0,1.2) 1.9s forwards; }
        .mod-clients  { transform: translateX(-330px); animation: fromLeft  .75s cubic-bezier(.22,.68,0,1.2) 2.5s forwards; }
        .mod-projets  { transform: translateX( 330px); animation: fromRight .75s cubic-bezier(.22,.68,0,1.2) 2.5s forwards; }
        .mod-stock    { transform: translateX(-165px); animation: fromLeft  .75s cubic-bezier(.22,.68,0,1.2) 3.1s forwards; }
        .mod-archives { transform: translateX( 165px); animation: fromRight .75s cubic-bezier(.22,.68,0,1.2) 3.1s forwards; }

        /* ════════ SOUS-TITRE ════════ */
        .sub {
            font-size: clamp(12px, 1.5vw, 14px);
            font-style: italic;
            font-weight: 600;
            color: rgba(255,255,255,.72);
            opacity: 0;
            animation: fadeUp .7s ease 3.8s forwards;
            margin-bottom: 28px;
        }

        /* ════════ BOUTON ════════ */
        .btn-start {
            display: inline-block;
            padding: 15px 64px;
            background: linear-gradient(135deg, #1260ff, #0099ff);
            border-radius: 50px;
            color: #fff;
            font-size: clamp(18px, 2.5vw, 24px);
            font-weight: 900;
            font-style: italic;
            text-decoration: none;
            opacity: 0;
            animation: fadeInScale .65s cubic-bezier(.34,1.56,.64,1) 4.4s forwards;
            box-shadow: 0 8px 36px rgba(18,96,255,.45);
            transform-origin: center bottom;
        }

        /* ════════ KEYFRAMES ════════ */

        /* Rotation de la bordure lumineuse */
        @keyframes spinBorder {
            from { transform: rotate(0deg); }
            to   { transform: rotate(360deg); }
        }

        @keyframes fadeInScale {
            from { opacity: 0; transform: scale(.82); }
            to   { opacity: 1; transform: scale(1); }
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fromLeft {
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fromRight {
            to { opacity: 1; transform: translateX(0); }
        }

        /* Fusée : recul → vibration → envol */
        @keyframes rocketLaunch {
            0%   { opacity: 1; transform: scale(1)    translateY(0)      rotate(0deg);  }
            10%  {             transform: scale(1.1)  translateY(10px)   rotate(1deg);  }
            18%  {             transform: scale(1.05) translateY(4px)    rotate(-.5deg);}
            100% { opacity: 0; transform: scale(.25)  translateY(-130vh) rotate(-6deg); }
        }

        /* Estompage de la page */
        @keyframes pageBlast {
            to { opacity: 0; }
        }
        .wrap.blasting {
            animation: pageBlast .65s ease .3s forwards;
            pointer-events: none;
        }
    </style>
</head>
<body>

<div class="glow-left"></div>
<div class="glow-right"></div>

<div class="wrap" id="wrap">

    {{-- Logo --}}
    <div class="logo-area">
        <div class="logo-ring-wrap">
            <div class="logo-ring-inner">
                <img src="{{ asset('images/logo.png') }}" alt="TopGest">
            </div>
        </div>
    </div>

    {{-- Nom --}}
    <div class="app-name">TopGest</div>

    {{-- Tagline --}}
    <div class="tagline">TopGest centralise la gestion de votre entreprise</div>

    {{-- Pyramide inversée --}}
    <div class="pyramid">
        <div class="p-row r1">
            <div class="mod-card mod-rh">
                <div class="mod-card-inner">Gestion Ressources Humaines</div>
            </div>
            <div class="mod-card mod-banque">
                <div class="mod-card-inner">Banque &amp; Finance</div>
            </div>
        </div>
        <div class="p-row r2">
            <div class="mod-card mod-clients">
                <div class="mod-card-inner">Clients &amp; Facturation</div>
            </div>
            <div class="mod-card mod-projets">
                <div class="mod-card-inner">Projets &amp; Tâches</div>
            </div>
        </div>
        <div class="p-row r3">
            <div class="mod-card mod-stock">
                <div class="mod-card-inner">Stock</div>
            </div>
            <div class="mod-card mod-archives">
                <div class="mod-card-inner">Archives</div>
            </div>
        </div>
    </div>

    {{-- Sous-titre --}}
    <div class="sub">accessible par toutes vos agences depuis une seule plateforme.</div>

    {{-- Bouton --}}
    <a href="{{ route('login') }}" class="btn-start" id="btnStart">Démarrer</a>

</div>

<script>
    const btn  = document.getElementById('btnStart');
    const wrap = document.getElementById('wrap');

    btn.addEventListener('click', function(e) {
        e.preventDefault();
        const href = this.href;

        // Surcharge l'animation CSS existante par l'animation fusée
        this.style.opacity = '1';
        this.style.animation = 'rocketLaunch 1.3s cubic-bezier(.4,0,.2,1) forwards';

        // Estompe toute la page un peu après
        setTimeout(() => wrap.classList.add('blasting'), 320);

        // Redirige vers le login une fois l'animation terminée
        setTimeout(() => window.location.href = href, 1450);
    });
</script>

</body>
</html>
