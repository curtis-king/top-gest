<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Connexion - MyGest</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            min-height:100vh;
            display:flex;
            align-items:center;
            justify-content:center;
            background:#0b0f1a;
            position:relative;
            overflow-y:auto;
            padding:24px;
        }

        body::before {
            content:'';
            position:fixed;
            inset:0;
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(37,99,235,.2) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 80%, rgba(59,130,246,.15) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 50% 50%, rgba(96,165,250,.06) 0%, transparent 60%);
            pointer-events:none;
            z-index:0;
        }

        .orb {
            position:fixed;
            border-radius:50%;
            filter:blur(80px);
            pointer-events:none;
            z-index:0;
            animation:orbFloat 8s ease-in-out infinite alternate;
        }

        .orb--1 { width:400px; height:400px; background:rgba(37,99,235,.12); top:-10%; left:-5%; animation-delay:0s; }
        .orb--2 { width:300px; height:300px; background:rgba(59,130,246,.1); bottom:-5%; right:-5%; animation-delay:-3s; }
        .orb--3 { width:200px; height:200px; background:rgba(96,165,250,.08); top:50%; left:50%; translate:-50% -50%; animation-delay:-6s; }

        @keyframes orbFloat {
            0% { transform:translate(0,0) scale(1); }
            100% { transform:translate(40px,-30px) scale(1.15); }
        }

        .grid-lines {
            position:fixed;
            inset:0;
            background-image:
                linear-gradient(rgba(255,255,255,.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,.015) 1px, transparent 1px);
            background-size:60px 60px;
            pointer-events:none;
            z-index:0;
        }

        .login-container {
            position:relative;
            z-index:1;
            width:100%;
            max-width:420px;
        }

        .login-card {
            background:rgba(255,255,255,.03);
            backdrop-filter:blur(24px);
            -webkit-backdrop-filter:blur(24px);
            border:1px solid rgba(255,255,255,.07);
            border-radius:20px;
            padding:36px 32px 28px;
            box-shadow:0 24px 80px rgba(0,0,0,.5);
        }

        .brand {
            display:flex;
            align-items:center;
            gap:12px;
            margin-bottom:32px;
        }

        .logo {
            width:40px;
            height:40px;
            border-radius:10px;
            background:linear-gradient(135deg,#2563eb,#3b82f6);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
            font-size:16px;
            color:#fff;
            box-shadow:0 8px 24px rgba(37,99,235,.25);
        }

        .brand-text h1 {
            font-size:18px;
            font-weight:700;
            color:#f1f5f9;
            letter-spacing:-.3px;
        }

        .brand-text p {
            font-size:12px;
            color:rgba(255,255,255,.4);
            margin-top:1px;
        }

        .form-title {
            font-size:14px;
            font-weight:500;
            color:rgba(255,255,255,.55);
            margin-bottom:24px;
        }

        .field { margin-bottom:18px; }

        .field label {
            display:block;
            font-size:12px;
            font-weight:500;
            color:rgba(255,255,255,.65);
            margin-bottom:6px;
        }

        .field input {
            width:100%;
            padding:11px 14px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:10px;
            font-size:14px;
            font-family:inherit;
            color:#fff;
            outline:none;
            transition:all .25s ease;
        }

        .field input::placeholder { color:rgba(255,255,255,0.2); }
        .field input:focus { border-color:#3b82f6; background:rgba(59,130,246,.05); box-shadow:0 0 0 3px rgba(59,130,246,.12); }

        .status {
            padding:11px 14px;
            background:rgba(34,197,94,.08);
            border:1px solid rgba(34,197,94,.15);
            border-radius:10px;
            color:#4ade80;
            font-size:13px;
            margin-bottom:20px;
        }

        .actions {
            display:flex;
            align-items:center;
            justify-content:space-between;
            margin-top:24px;
        }

        .remember {
            display:flex;
            align-items:center;
            gap:7px;
            font-size:13px;
            color:rgba(255,255,255,.5);
            cursor:pointer;
        }

        .remember input[type="checkbox"] {
            width:15px;
            height:15px;
            accent-color:#3b82f6;
            border-radius:3px;
            cursor:pointer;
        }

        .btn-primary {
            padding:11px 24px;
            background:#2563eb;
            border:none;
            border-radius:10px;
            color:#fff;
            font-size:13px;
            font-weight:600;
            font-family:inherit;
            cursor:pointer;
            transition:all .25s ease;
            white-space:nowrap;
        }

        .btn-primary:hover { background:#3b82f6; }

        .aux-links {
            display:flex;
            justify-content:center;
            gap:20px;
            margin-top:20px;
            padding-top:20px;
            border-top:1px solid rgba(255,255,255,.05);
        }

        .link {
            font-size:13px;
            color:rgba(255,255,255,.35);
            text-decoration:none;
            transition:color .2s;
        }
        .link:hover { color:#93c5fd; }

        footer.small {
            text-align:center;
            margin-top:24px;
            font-size:11px;
            color:rgba(255,255,255,.2);
        }

        .error {
            font-size:12px;
            color:#f87171;
            margin-top:5px;
            display:block;
        }

        @media (max-width:480px) {
            .login-card { padding:28px 20px 24px; }
            .actions { flex-direction:column; gap:14px; }
            .btn-primary { width:100%; text-align:center; }
        }
    </style>
</head>
<body>
    <div class="orb orb--1"></div>
    <div class="orb orb--2"></div>
    <div class="orb orb--3"></div>
    <div class="grid-lines"></div>

    <div class="login-container">
        <div class="login-card">
            <div class="brand">
                <div class="logo">MG</div>
                <div class="brand-text">
                    <h1>MyGest</h1>
                    <p>Logiciel de gestion d'entreprise</p>
                </div>
            </div>

            <div class="form-title">Connectez-vous à votre compte</div>

            @if(session('status'))
                <div class="status">{{ session('status') }}</div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="email">Adresse e-mail</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder="vous@exemple.com" required autofocus>
                    @error('email') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="field">
                    <label for="password">Mot de passe</label>
                    <input id="password" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
                    @error('password') <span class="error">{{ $message }}</span> @enderror
                </div>

                <div class="actions">
                    <label class="remember">
                        <input type="checkbox" name="remember"> Se souvenir
                    </label>
                    <button type="submit" class="btn-primary">Se connecter</button>
                </div>

                <div class="aux-links">
                    @if(Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="link">Mot de passe oublié ?</a>
                    @endif
                    @if(Route::has('register'))
                        <a href="{{ route('register') }}" class="link">Créer un compte</a>
                    @endif
                </div>
            </form>

            <footer class="small">© {{ date('Y') }} MyGest — Gestion d'entreprise</footer>
        </div>
    </div>
</body>
</html>
