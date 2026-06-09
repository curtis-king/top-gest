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
            background:rgba(255,255,255,.02);
            border-right:1px solid rgba(255,255,255,.06);
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
            border-bottom:1px solid rgba(255,255,255,.06);
            margin-bottom:20px;
        }

        .sidebar-logo {
            width:36px;
            height:36px;
            border-radius:10px;
            background:linear-gradient(135deg,#2563eb,#3b82f6);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            font-size:15px;
            color:#fff;
            flex-shrink:0;
        }

        .sidebar-title {
            font-size:15px;
            font-weight:600;
            color:#f1f5f9;
        }

        .sidebar-subtitle {
            font-size:11px;
            color:rgba(255,255,255,.35);
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
            padding:10px 12px 6px;
            cursor:pointer;
            user-select:none;
            transition:color .2s;
        }
        .nav-group-header span {
            font-size:11px;
            text-transform:uppercase;
            letter-spacing:.6px;
            color:rgba(255,255,255,.25);
            font-weight:600;
        }
        .nav-group-header .arrow {
            font-size:10px;
            color:rgba(255,255,255,.2);
            transition:transform .25s ease;
        }
        .nav-group-header .arrow.open {
            transform:rotate(90deg);
        }
        .nav-group-body {
            display:flex;
            flex-direction:column;
            gap:1px;
            overflow:hidden;
            max-height:0;
            transition:max-height .3s ease;
        }
        .nav-group-body.open {
            max-height:400px;
        }

        .sidebar-link {
            display:flex;
            align-items:center;
            gap:10px;
            padding:9px 12px;
            border-radius:8px;
            font-size:13px;
            font-weight:500;
            color:rgba(255,255,255,.5);
            text-decoration:none;
            transition:all .2s ease;
        }

        .sidebar-link svg {
            width:16px;
            height:16px;
            flex-shrink:0;
            opacity:.6;
            transition:opacity .2s;
        }

        .sidebar-link:hover {
            background:rgba(255,255,255,.04);
            color:rgba(255,255,255,.8);
        }
        .sidebar-link:hover svg { opacity:.9; }

        .sidebar-link.active {
            background:rgba(59,130,246,.1);
            color:#60a5fa;
        }
        .sidebar-link.active svg { opacity:1; }

        .sidebar-summary {
            padding-top:14px;
            border-top:1px solid rgba(255,255,255,.06);
            margin-top:auto;
        }

        .summary-title {
            font-size:11px;
            color:rgba(255,255,255,.3);
            margin-bottom:6px;
            text-transform:uppercase;
            letter-spacing:.5px;
        }

        .summary-chip {
            display:inline-block;
            padding:4px 12px;
            border-radius:20px;
            font-size:12px;
            font-weight:500;
            background:rgba(59,130,246,.1);
            color:#60a5fa;
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

        @media (max-width:768px) {
            .dashboard-sidebar { display:none; }
            .dashboard-content { padding:24px 20px; }
            .dashboard-topbar { flex-direction:column; }
        }
    </style>
</head>
<body>
    <div class="dashboard-shell">
        <aside class="dashboard-sidebar">
            <div class="sidebar-brand">
                <div class="sidebar-logo">MG</div>
                <div>
                    <div class="sidebar-title">MyGest</div>
                    <div class="sidebar-subtitle">Gestion d'entreprise</div>
                </div>
            </div>

            <nav class="sidebar-nav" id="sidebarNav">

                <a href="{{ url('/dashboard') }}" class="sidebar-link active">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>
                    Tableau de bord
                </a>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Administration</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('compagnies.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                            Compagnies
                        </a>
                        <a href="{{ route('agences.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1Z"/><path d="M9 21V12h6v9"/></svg>
                            Agences
                        </a>
                        <a href="{{ route('fonctions.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Fonctions
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Employés</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('employees.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            Employés
                        </a>
                        <a href="{{ route('dossiers-employees.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                            Dossiers
                        </a>
                        <a href="{{ route('conges.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M3 10h18"/><path d="M10 14h4"/><path d="M12 12v4"/></svg>
                            Congés
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Projets</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('projects.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
                            Projets
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Contacts</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('contacts.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            Contacts
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Finance</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('retenus.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            Retenus
                        </a>
                        <a href="{{ route('primes.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            Primes
                        </a>
                        <a href="{{ route('payements-employees.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2"/><path d="M1 10h22"/><path d="M7 15h.01"/><path d="M11 15h2"/></svg>
                            Paiements
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Banque</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('banques.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="2" width="18" height="4" rx="1"/><path d="M4 6v14"/><path d="M20 6v14"/><path d="M8 10h8"/><path d="M8 14h8"/><path d="M8 18h4"/></svg>
                            Banques
                        </a>
                        <a href="{{ route('livrets-bancaires.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v4M16 2v4M4 8h16"/><rect x="2" y="6" width="20" height="16" rx="1"/><path d="M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01M16 18h.01"/></svg>
                            Livret bancaire
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Facturation</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('factures.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                            Factures
                        </a>
                    </div>
                </div>

                <div class="nav-group">
                    <div class="nav-group-header" onclick="toggleGroup(this)">
                        <span>Système</span>
                        <span class="arrow">&#9654;</span>
                    </div>
                    <div class="nav-group-body">
                        <a href="{{ route('users.index') }}" class="sidebar-link">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5.52 19c.64-2.2 1.84-3 3.22-4.5A6.5 6.5 0 0 1 12 13c1.46 0 2.84.56 3.93 1.5 1.38 1.5 2.58 2.3 3.22 4.5"/><circle cx="12" cy="8" r="4"/><circle cx="20" cy="8" r="2.5"/><path d="M22 13c-.33-.63-.9-1.1-1.6-1.34"/></svg>
                            Utilisateurs
                        </a>
                    </div>
                </div>

            </nav>

            <div class="sidebar-summary">
                <div class="summary-title">Connecté</div>
                <div class="summary-chip">{{ auth()->user()?->name ?? 'Visiteur' }}</div>
            </div>
        </aside>

        <main class="dashboard-content">
            <header class="dashboard-topbar">
                <div>
                    <span class="eyebrow">Bienvenue sur MyGest</span>
                    <h1>@yield('page-title', 'Tableau de bord')</h1>
                </div>
                <div class="topbar-actions">
                    <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn-primary">Se déconnecter</button>
                    </form>
                </div>
            </header>

            <section class="dashboard-grid">
                @yield('content')
            </section>
        </main>
    </div>

    <script>
        function toggleGroup(header) {
            const body = header.nextElementSibling;
            const arrow = header.querySelector('.arrow');
            const isOpen = body.classList.contains('open');

            body.classList.toggle('open');
            arrow.classList.toggle('open');

            if (!isOpen) {
                const allBodies = document.querySelectorAll('.nav-group-body');
                const allArrows = document.querySelectorAll('.nav-group .arrow');
                allBodies.forEach(b => { if (b !== body) b.classList.remove('open'); });
                allArrows.forEach(a => { if (a !== arrow) a.classList.remove('open'); });
            }
        }
    </script>
</body>
</html>
