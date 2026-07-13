<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>@yield('title', 'MyGest')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            background:#0b0f1a;
            color:#fff;
            min-height:100vh;
        }

        .dashboard-shell {
            display:flex;
            min-height:100vh;
        }

        .dashboard-sidebar {
            width:270px;
            background:linear-gradient(180deg,#0f172a 0%,#1a2444 50%,#0f172a 100%);
            border-right:1px solid rgba(99,102,241,.15);
            padding:20px 14px;
            display:flex;
            flex-direction:column;
            flex-shrink:0;
            position:sticky;
            top:0;
            height:100vh;
            overflow-y:auto;
        }

        .sidebar-brand {
            display:flex;
            align-items:center;
            gap:12px;
            padding-bottom:20px;
            border-bottom:1px solid rgba(99,102,241,.15);
            margin-bottom:20px;
        }

        .sidebar-logo {
            width:38px;
            height:38px;
            border-radius:12px;
            background:linear-gradient(135deg,#6366f1,#06b6d4);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:800;
            font-size:16px;
            color:#fff;
            flex-shrink:0;
            box-shadow:0 4px 12px rgba(99,102,241,.3);
        }

        .sidebar-title {
            font-size:16px;
            font-weight:700;
            color:#f1f5f9;
            letter-spacing:-.2px;
        }

        .sidebar-subtitle {
            font-size:11px;
            color:rgba(148,163,184,.5);
            margin-top:1px;
        }

        .sidebar-nav {
            display:flex;
            flex-direction:column;
            gap:2px;
            flex:1;
        }

        .nav-group {}
        .nav-group-header {
            display:flex;
            align-items:center;
            justify-content:space-between;
            padding:12px 12px 6px;
            cursor:pointer;
            user-select:none;
            transition:color .2s;
        }
        .nav-group-header:hover .group-label { color:rgba(255,255,255,.5); }
        .group-label {
            display:flex;
            align-items:center;
            gap:8px;
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.6px;
            color:rgba(148,163,184,.4);
            font-weight:700;
            transition:color .2s;
        }
        .group-label .ico {
            font-size:14px;
            opacity:.6;
        }
        .nav-group-header .arrow {
            font-size:10px;
            color:rgba(148,163,184,.3);
            transition:transform .25s ease;
        }
        .nav-group-header .arrow.open {
            transform:rotate(90deg);
        }
        .nav-group-body {
            display:flex;
            flex-direction:column;
            gap:2px;
            padding-left:4px;
            overflow:hidden;
            max-height:0;
            transition:max-height .3s ease;
        }
        .nav-group-body.open {
            max-height:500px;
        }

        .sidebar-link {
            display:flex;
            align-items:center;
            gap:10px;
            padding:10px 12px;
            border-radius:10px;
            font-size:13px;
            font-weight:500;
            color:rgba(255,255,255,.45);
            text-decoration:none;
            transition:all .25s ease;
            position:relative;
        }

        .sidebar-link svg {
            width:17px;
            height:17px;
            flex-shrink:0;
            opacity:.5;
            transition:all .25s ease;
        }

        .sidebar-link:hover {
            background:rgba(255,255,255,.05);
            color:rgba(255,255,255,.85);
            transform:translateX(3px);
        }
        .sidebar-link:hover svg {
            opacity:.9;
            transform:scale(1.1);
        }

        .sidebar-link.active {
            background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(6,182,212,.08));
            color:#818cf8;
            border-left:3px solid #6366f1;
            border-radius:10px 6px 6px 10px;
        }
        .sidebar-link.active svg {
            opacity:1;
            color:#818cf8;
        }

        .sidebar-summary {
            padding-top:14px;
            border-top:1px solid rgba(99,102,241,.12);
            margin-top:auto;
        }

        .summary-title {
            font-size:11px;
            color:rgba(148,163,184,.4);
            margin-bottom:6px;
            text-transform:uppercase;
            letter-spacing:.5px;
            font-weight:600;
        }

        .summary-chip {
            display:inline-flex;
            align-items:center;
            gap:6px;
            padding:5px 14px;
            border-radius:20px;
            font-size:12px;
            font-weight:600;
            background:linear-gradient(135deg,rgba(99,102,241,.12),rgba(6,182,212,.08));
            color:#818cf8;
        }
        .summary-chip::before {
            content:'';
            width:7px;
            height:7px;
            border-radius:50%;
            background:#4ade80;
            box-shadow:0 0 8px rgba(74,222,128,.5);
        }

        .dashboard-content {
            flex:1;
            padding:32px 40px;
            min-width:0;
        }

        .dashboard-topbar {
            display:flex;
            align-items:flex-start;
            justify-content:space-between;
            margin-bottom:32px;
            gap:24px;
        }

        .eyebrow {
            font-size:13px;
            color:rgba(255,255,255,.35);
            font-weight:500;
        }

        .dashboard-topbar h1 {
            font-size:26px;
            font-weight:700;
            color:#f1f5f9;
            letter-spacing:-.4px;
            margin-top:2px;
        }

        .topbar-actions {
            display:flex;
            gap:12px;
            flex-shrink:0;
        }

        .btn-outline {
            padding:9px 18px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:8px;
            color:rgba(255,255,255,.55);
            font-size:13px;
            font-weight:500;
            font-family:inherit;
            cursor:pointer;
            transition:all .2s ease;
        }
        .btn-outline:hover { background:rgba(255,255,255,.07); color:#fff; }

        .btn-primary {
            padding:9px 18px;
            background:#2563eb;
            border:none;
            border-radius:8px;
            color:#fff;
            font-size:13px;
            font-weight:600;
            font-family:inherit;
            cursor:pointer;
            transition:all .2s ease;
        }
        .btn-primary:hover { background:#3b82f6; }

        .dashboard-grid { display:flex; flex-direction:column; }

        /* ── Hamburger (caché sur desktop) ───────────────────────── */
        .sidebar-toggle {
            display:none;
            align-items:center;
            justify-content:center;
            width:38px;
            height:38px;
            background:rgba(255,255,255,.05);
            border:1px solid rgba(255,255,255,.08);
            border-radius:10px;
            cursor:pointer;
            color:rgba(255,255,255,.65);
            flex-shrink:0;
            transition:all .2s;
        }
        .sidebar-toggle:hover { background:rgba(255,255,255,.1); color:#fff; }

        /* ── Backdrop (caché par défaut) ──────────────────────────── */
        .sidebar-backdrop {
            display:none;
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.55);
            backdrop-filter:blur(2px);
            z-index:99;
        }
        .sidebar-backdrop.open { display:block; }

        /* ── Mobile ≤ 768px ───────────────────────────────────────── */
        @media (max-width:768px) {
            body.sidebar-open { overflow:hidden; }

            .dashboard-sidebar {
                position:fixed;
                top:0; left:0;
                height:100dvh;
                z-index:100;
                transform:translateX(-100%);
                transition:transform .3s cubic-bezier(.4,0,.2,1);
                box-shadow:none;
            }
            .dashboard-sidebar.open {
                transform:translateX(0);
                box-shadow:12px 0 40px rgba(0,0,0,.5);
            }

            .sidebar-toggle { display:flex; }

            .dashboard-content { padding:16px; }

            .dashboard-topbar {
                flex-wrap:nowrap;
                align-items:center;
                gap:12px;
                margin-bottom:20px;
            }
            .dashboard-topbar h1 { font-size:18px; }
            .eyebrow { display:none; }

            .topbar-actions .btn-primary {
                padding:8px 14px;
                font-size:12px;
            }
        }

        /* ── Tablette 769-1024px ──────────────────────────────────── */
        @media (min-width:769px) and (max-width:1024px) {
            .dashboard-sidebar { width:220px; }
            .dashboard-content { padding:24px 28px; }
        }
    </style>
</head>
<body>
    <div class="dashboard-shell">
        <div id="sidebarBackdrop" class="sidebar-backdrop" onclick="toggleSidebar()"></div>
        <aside class="dashboard-sidebar" id="dashboardSidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">MG</div>
                <div>
                    <div class="sidebar-title">MyGest</div>
                    <div class="sidebar-subtitle">Gestion d'entreprise</div>
                </div>
            </div>

@php
$_cr = Route::currentRouteName() ?? '';
$_domainMap = [
    'employees'=>'rh','dossiers-employees'=>'rh','conges'=>'rh','retenus'=>'rh',
    'primes'=>'rh','payements-employees'=>'rh','compagnies'=>'rh','agences'=>'rh','fonctions'=>'rh',
    'projects'=>'projets','taches-projects'=>'projets','affectation-taches'=>'projets',
    'factures'=>'finance','contacts'=>'finance','banques'=>'finance','livrets-bancaires'=>'finance',
    'stocks'=>'stock','produits'=>'stock','mouvements-stocks'=>'stock','depots'=>'stock','categories-produits'=>'stock',
    'documents'=>'archives','categories-documents'=>'archives',
    'users'=>'systeme',
];
$_activeDomain = 'dashboard';
foreach ($_domainMap as $_prefix => $_domain) {
    if (str_starts_with($_cr, $_prefix)) { $_activeDomain = $_domain; break; }
}
@endphp
            <nav class="sidebar-nav" id="sidebarNav">

                <a href="{{ url('/dashboard') }}"
                   class="sidebar-link {{ $_activeDomain === 'dashboard' ? 'active' : '' }}"
                   data-permission="administration.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    Tableau de bord
                </a>

                <div style="height:1px;background:rgba(255,255,255,.05);margin:8px 0;"></div>

                <a href="{{ route('employees.index') }}"
                   class="sidebar-link {{ $_activeDomain === 'rh' ? 'active' : '' }}"
                   data-permission="rh.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    Ressources Humaines
                </a>

                <a href="{{ route('projects.index') }}"
                   class="sidebar-link {{ $_activeDomain === 'projets' ? 'active' : '' }}"
                   data-permission="projets.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    Projets
                </a>

                <a href="{{ route('factures.index') }}"
                   class="sidebar-link {{ $_activeDomain === 'finance' ? 'active' : '' }}"
                   data-permission="finance.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Finance
                </a>

                <a href="{{ route('stocks.dashboard') }}"
                   class="sidebar-link {{ $_activeDomain === 'stock' ? 'active' : '' }}"
                   data-permission="stock.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2Z"/><path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/></svg>
                    Stock
                </a>

                <a href="{{ route('documents.index') }}"
                   class="sidebar-link {{ $_activeDomain === 'archives' ? 'active' : '' }}"
                   data-permission="archives.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                    Archives
                </a>

                <div style="height:1px;background:rgba(255,255,255,.05);margin:8px 0;"></div>

                <a href="{{ route('users.index') }}"
                   class="sidebar-link {{ $_activeDomain === 'systeme' ? 'active' : '' }}"
                   data-permission="administration.view">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5.52 19c.64-2.2 1.84-3 3.22-4.5A6.5 6.5 0 0 1 12 13c1.46 0 2.84.56 3.93 1.5 1.38 1.5 2.58 2.3 3.22 4.5"/></svg>
                    Système
                </a>

            </nav>

            <div class="sidebar-summary">
                <div class="summary-title">Connecté</div>
                <div class="summary-chip">{{ auth()->user()?->name ?? 'Visiteur' }}</div>
            </div>
        </aside>

        <main class="dashboard-content">
            <header class="dashboard-topbar">
                <div style="display:flex;align-items:center;gap:12px;min-width:0;">
                    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="Menu">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                            <line x1="3" y1="6" x2="21" y2="6"/>
                            <line x1="3" y1="12" x2="21" y2="12"/>
                            <line x1="3" y1="18" x2="21" y2="18"/>
                        </svg>
                    </button>
                    <div style="min-width:0;">
                        <span class="eyebrow">Bienvenue sur MyGest</span>
                        <h1 style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">@yield('page-title', 'Tableau de bord')</h1>
                    </div>
                </div>
                <div class="topbar-actions" style="flex-shrink:0;">
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-primary">Se déconnecter</button>
                    </form>
                </div>
            </header>

            @include('partials.domain-tabs')

            <section class="dashboard-grid">
                @yield('content')
            </section>
        </main>
    </div>

    {{-- Modal accès refusé --}}
    <div id="accessDeniedModal" style="display:none;position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,.65);backdrop-filter:blur(4px);align-items:center;justify-content:center;">
        <div style="background:linear-gradient(135deg,#0f172a,#1a2444);border:1px solid rgba(248,113,113,.25);border-radius:18px;padding:36px 40px;max-width:420px;width:90%;text-align:center;box-shadow:0 24px 80px rgba(0,0,0,.5);animation:modalIn .2s ease;">
            <div style="width:56px;height:56px;border-radius:50%;background:rgba(248,113,113,.12);border:1.5px solid rgba(248,113,113,.3);display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#f87171" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            </div>
            <h3 style="font-size:18px;font-weight:700;color:#f1f5f9;margin:0 0 10px;">Accès refusé</h3>
            <p style="font-size:13px;color:rgba(255,255,255,.5);margin:0 0 8px;" id="accessDeniedMessage">
                Vous n'êtes pas autorisé à accéder à cette section.
            </p>
            <p style="font-size:12px;color:rgba(248,113,113,.6);margin:0 0 28px;" id="accessDeniedPermission"></p>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button onclick="closeAccessModal()" style="padding:10px 24px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:rgba(255,255,255,.65);font-size:13px;font-weight:500;font-family:inherit;cursor:pointer;transition:all .2s;" onmouseover="this.style.background='rgba(255,255,255,.1)'" onmouseout="this.style.background='rgba(255,255,255,.06)'">
                    Fermer
                </button>
                <a href="{{ url('/dashboard') }}" style="padding:10px 24px;background:#2563eb;border:none;border-radius:10px;color:#fff;font-size:13px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;transition:all .2s;" onmouseover="this.style.background='#3b82f6'" onmouseout="this.style.background='#2563eb'">
                    Tableau de bord
                </a>
            </div>
        </div>
    </div>

    <style>
        @keyframes modalIn {
            from { opacity:0; transform:scale(.94) translateY(8px); }
            to   { opacity:1; transform:scale(1)  translateY(0); }
        }
    </style>

    @stack('scripts')

    <script>
        @auth
        const userPermissions = @json(auth()->user()->getAllPermissions()->pluck('name'));
        @endauth

        function showAccessModal(permission, label) {
            const msg = document.getElementById('accessDeniedMessage');
            const perm = document.getElementById('accessDeniedPermission');
            msg.textContent = 'Vous n\'êtes pas autorisé à accéder à cette section.';
            perm.textContent = label ? 'Section : ' + label : '';
            const modal = document.getElementById('accessDeniedModal');
            modal.style.display = 'flex';
            document.addEventListener('keydown', escHandler);
        }

        function closeAccessModal() {
            document.getElementById('accessDeniedModal').style.display = 'none';
            document.removeEventListener('keydown', escHandler);
        }

        function escHandler(e) {
            if (e.key === 'Escape') closeAccessModal();
        }

        document.getElementById('accessDeniedModal').addEventListener('click', function(e) {
            if (e.target === this) closeAccessModal();
        });

        document.querySelectorAll('.sidebar-link[data-permission]').forEach(function(link) {
            link.addEventListener('click', function(e) {
                const required = this.getAttribute('data-permission');
                if (typeof userPermissions !== 'undefined' && !userPermissions.includes(required)) {
                    e.preventDefault();
                    const label = this.textContent.trim();
                    showAccessModal(required, label);
                }
            });
        });

        // ── Mobile sidebar drawer ───────────────────────────────────
        function toggleSidebar() {
            const sidebar  = document.getElementById('dashboardSidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const isOpen   = sidebar.classList.toggle('open');
            backdrop.classList.toggle('open', isOpen);
            document.body.classList.toggle('sidebar-open', isOpen);
        }

        // Ferme la sidebar au clic sur un lien (mobile)
        document.querySelectorAll('.sidebar-link').forEach(function(link) {
            link.addEventListener('click', function() {
                if (window.innerWidth <= 768) {
                    const sidebar  = document.getElementById('dashboardSidebar');
                    const backdrop = document.getElementById('sidebarBackdrop');
                    sidebar.classList.remove('open');
                    backdrop.classList.remove('open');
                    document.body.classList.remove('sidebar-open');
                }
            });
        });

    </script>
</body>
</html>
