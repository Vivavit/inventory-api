<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #eef1f8;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* ── Main Card ── */
        .login-card {
            display: flex;
            width: 100%;
            max-width: 860px;
            min-height: 500px;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 24px 64px rgba(80, 100, 200, 0.18);
        }

        /* ── Left Panel ── */
        .left-panel {
            flex: 1;
            background: linear-gradient(140deg, #03624C 0%, #06775d 55%, #02503e 100%);
            padding: 48px 40px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        /* Decorative shapes */
        .left-panel::before {
            content: '';
            position: absolute;
            top: -40px; left: -40px;
            width: 160px; height: 160px;
            border-radius: 50%;
            background: rgba(255,255,255,0.10);
        }
        .left-panel::after {
            content: '';
            position: absolute;
            bottom: 60px; right: -30px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.08);
        }

        .left-accent {
            width: 36px; height: 4px;
            background: rgba(255,255,255,0.55);
            border-radius: 2px;
            margin-bottom: 28px;
        }

        .left-panel h1 {
            color: #fff;
            font-size: 34px;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 16px;
            position: relative;
            z-index: 1;
        }

        .left-panel p {
            color: rgba(255,255,255,0.80);
            font-size: 14px;
            line-height: 1.7;
            position: relative;
            z-index: 1;
        }

        .left-dots {
            display: flex;
            gap: 8px;
            position: relative;
            z-index: 1;
        }

        .left-dots span {
            height: 8px;
            border-radius: 4px;
            background: rgba(255,255,255,0.40);
            transition: all 0.3s;
        }

        .left-dots span.active {
            width: 22px !important;
            background: #fff;
        }

        /* Decorative floating shapes */
        .shape-bar {
            position: absolute;
            top: 48px; left: 50%;
            width: 16px; height: 56px;
            background: rgba(255,255,255,0.22);
            border-radius: 8px;
        }
        .shape-bar-sm {
            position: absolute;
            top: 64px; left: calc(50% + 24px);
            width: 16px; height: 36px;
            background: rgba(255,255,255,0.14);
            border-radius: 8px;
        }
        .shape-dot {
            position: absolute;
            top: 56px; right: 52px;
            width: 10px; height: 10px;
            background: rgba(255,255,255,0.55);
            border-radius: 50%;
        }
        .shape-ring {
            position: absolute;
            bottom: 110px; left: 44px;
            width: 18px; height: 18px;
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 50%;
        }

        /* ── Right Panel ── */
        .right-panel {
            flex: 1.1;
            background: #fff;
            padding: 48px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .app-icon {
            width: 58px; height: 58px;
            background: #f0f2ff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 22px;
            font-size: 26px;
            color: #03624C;
        }

        .right-panel h2 {
            font-size: 22px;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .right-panel .subtitle {
            font-size: 13px;
            color: #999;
            margin-bottom: 30px;
        }

        /* ── Form ── */
        .form-label {
            font-size: 11px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 7px;
        }

        .input-group-custom {
            display: flex;
            align-items: center;
            background: #f5f7ff;
            border: 1.5px solid #e8ecff;
            border-radius: 12px;
            padding: 0 14px;
            margin-bottom: 16px;
            transition: border-color 0.2s, background 0.2s;
        }

        .input-group-custom:focus-within {
            border-color: #03624C;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(91,109,248,0.12);
        }

        .input-group-custom i {
            color: #bbb;
            font-size: 15px;
            flex-shrink: 0;
        }

        .input-group-custom input {
            flex: 1;
            border: none;
            background: transparent;
            padding: 13px 10px;
            font-size: 14px;
            color: #1a1a2e;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        .input-group-custom input::placeholder { color: #bbb; }

        .toggle-pw {
            background: none;
            border: none;
            color: #bbb;
            cursor: pointer;
            padding: 4px;
            line-height: 1;
            font-size: 15px;
        }

        .toggle-pw:hover { color: #03624C; }

        /* Options row */
        .options-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: #666;
            cursor: pointer;
            margin: 0;
        }

        .remember-label input[type="checkbox"] {
            accent-color: #5b6df8;
            width: 15px; height: 15px;
        }

        .reset-link {
            font-size: 13px;
            color: #5b6df8;
            font-weight: 500;
            text-decoration: none;
        }

        /* Login button */
        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #03624C, #06775d);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 14px;
            font-size: 15px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            letter-spacing: 0.3px;
            margin-bottom: 22px;
            transition: opacity 0.2s, transform 0.15s;
        }

        .btn-login:hover { opacity: 0.92; transform: translateY(-1px); }
        .btn-login:active { transform: translateY(0); }


        .or-sep hr { flex: 1; border: none; border-top: 1px solid #eee; }
        .or-sep span { font-size: 12px; color: #bbb; }

        /* Alert */
        .alert-custom {
            background: #fff0f0;
            border: 1px solid #ffd0d0;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #c0392b;
        }

        /* Responsive */
        @media (max-width: 640px) {
            .left-panel { display: none; }
            .right-panel { padding: 36px 28px; }
            .login-card { max-width: 400px; }
        }
    </style>
</head>
<body>

<div class="login-card">

    {{-- ── Left Panel ── --}}
    <div class="left-panel">
        <div class="shape-bar"></div>
        <div class="shape-bar-sm"></div>
        <div class="shape-dot"></div>
        <div class="shape-ring"></div>

        <div>
            <div class="left-accent"></div>
            <h1>Manage Your<br>Inventory</h1>
            <p>Sign in to manage your inventory,<br>track stock, and stay in control.</p>
        </div>

    </div>

    {{-- ── Right Panel ── --}}
    <div class="right-panel">

        <div class="app-icon">
            <i class="bi bi-box-seam"></i>
        </div>

        <h2>Hello! Welcome back</h2>
        <p class="subtitle">Sign in to your Inventory System account</p>

        {{-- Error messages --}}
        @if($errors->any())
            <div class="alert-custom">
                @foreach($errors->all() as $error)
                    <div><i class="bi bi-exclamation-circle me-1"></i>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            {{-- Email --}}
            <label class="form-label">Email</label>
            <div class="input-group-custom">
                <i class="bi bi-envelope"></i>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email address" required autofocus class="flex-1 border-none bg-transparent px-2.5 py-3 text-sm text-gray-900 outline-none">
            </div>

            {{-- Password --}}
            <label class="form-label">Password</label>
            <div class="input-group-custom">
                <i class="bi bi-lock"></i>
                <input type="password" name="password" id="password" placeholder="••••••••••••" required class="flex-1 border-none bg-transparent px-2.5 py-3 text-sm text-gray-900 outline-none">
                <button type="button" class="toggle-pw" onclick="togglePassword()" id="toggleIcon">
                    <i class="bi bi-eye" id="eyeIcon"></i>
                </button>
            </div>

            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Login
            </button>
        </form>

    </div>
</div>

<script>
    function togglePassword() {
        const input = document.getElementById('password');
        const icon  = document.getElementById('eyeIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'bi bi-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'bi bi-eye';
        }
    }
</script>
</body>
</html>