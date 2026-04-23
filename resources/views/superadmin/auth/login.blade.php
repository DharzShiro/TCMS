<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Super Admin — {{ config('app.name', 'TCMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #003087;
            --blue:   #0057B8;
            --red:    #CE1126;
            --red-dk: #A50E1E;
            --gold:   #F5C518;
            --border: #c5d8f5;
            --text:   #001a4d;
            --muted:  #5a7aaa;
        }

        html, body {
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
            background: #0a0f1e;
            overflow-x: hidden;
        }

        body {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed; inset: 0; pointer-events: none; z-index: 0;
            background:
                radial-gradient(ellipse 70% 50% at 20% 20%, rgba(0,87,184,0.15) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(206,17,38,0.10) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(245,197,24,0.04) 0%, transparent 70%);
        }

        /* Gold top stripe for superadmin — different from tenant */
        .stripe {
            position: fixed; top: 0; left: 0; right: 0; height: 4px; z-index: 100;
            background: linear-gradient(90deg,
                #F5C518 0%, #F5C518 33%,
                #003087 33%, #003087 66%,
                #CE1126 66%, #CE1126 100%);
        }

        .login-card {
            position: relative; z-index: 1;
            width: 100%; max-width: 440px;
            background: #0d1f3c;
            border-radius: 20px;
            border: 1px solid #1e3a6b;
            box-shadow: 0 8px 40px rgba(0,0,0,0.50), 0 2px 8px rgba(0,0,0,0.30);
            overflow: hidden;
        }

        .card-top {
            background: linear-gradient(135deg, #001a4d 0%, #003087 100%);
            padding: 32px 36px 28px;
            position: relative; overflow: hidden;
        }

        .card-top::before {
            content: ''; position: absolute; top: -30px; right: -30px;
            width: 140px; height: 140px; border-radius: 50%;
            background: rgba(245,197,24,0.08);
        }
        .card-top::after {
            content: ''; position: absolute; bottom: -40px; left: -20px;
            width: 120px; height: 120px; border-radius: 50%;
            background: rgba(206,17,38,0.06);
        }

        .card-brand {
            display: flex; align-items: center; gap: 12px;
            text-decoration: none; margin-bottom: 20px;
            position: relative; z-index: 1;
        }

        .card-logo {
            width: 44px; height: 44px; border-radius: 10px;
            background: rgba(245,197,24,0.15);
            border: 1px solid rgba(245,197,24,0.25);
            display: flex; align-items: center; justify-content: center;
            overflow: hidden; flex-shrink: 0;
        }
        .card-logo img { width: 30px; height: 30px; object-fit: contain; filter: brightness(0) invert(1); }

        .card-brand-text { line-height: 1.15; }
        .card-brand-name { font-size: 15px; font-weight: 800; color: #fff; letter-spacing: 0.3px; }
        .card-brand-sub  { font-size: 9.5px; font-weight: 500; color: rgba(255,255,255,0.50); letter-spacing: 1.2px; text-transform: uppercase; }

        /* Gold badge */
        .superadmin-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(245,197,24,0.15);
            border: 1px solid rgba(245,197,24,0.30);
            color: #F5C518;
            font-size: 10px; font-weight: 700;
            letter-spacing: 1.2px; text-transform: uppercase;
            padding: 3px 9px; border-radius: 20px;
            margin-bottom: 10px;
            position: relative; z-index: 1;
        }

        .card-headline { position: relative; z-index: 1; }
        .card-headline h1 { font-size: 22px; font-weight: 800; color: #fff; line-height: 1.2; }
        .card-headline p  { font-size: 13px; color: rgba(255,255,255,0.50); margin-top: 4px; line-height: 1.5; }

        .card-body { padding: 32px 36px 36px; }

        .alert-status {
            padding: 10px 14px; border-radius: 10px; margin-bottom: 20px;
            background: rgba(245,197,24,0.08); border: 1px solid rgba(245,197,24,0.20);
            font-size: 13px; color: #F5C518; font-weight: 500;
            display: flex; align-items: center; gap: 8px;
        }

        .field { margin-bottom: 18px; }

        .field label {
            display: block; font-size: 12.5px; font-weight: 700;
            color: #adc4f0; margin-bottom: 6px; letter-spacing: 0.2px;
        }

        .input-wrap { position: relative; }

        .input-wrap i {
            position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
            font-size: 13px; color: #3a5a8a; pointer-events: none;
        }

        .field input[type="email"],
        .field input[type="password"],
        .field input[type="text"] {
            width: 100%; padding: 11px 14px 11px 38px;
            border-radius: 10px; border: 1.5px solid #1e3a6b;
            font-family: inherit; font-size: 13.5px; color: #dde8ff;
            background: #0a1628; outline: none;
            transition: border-color 0.18s, box-shadow 0.18s;
        }

        .field input:focus {
            border-color: #F5C518;
            box-shadow: 0 0 0 3px rgba(245,197,24,0.10);
        }

        .field input.is-invalid { border-color: var(--red); }
        .field input.is-invalid:focus { box-shadow: 0 0 0 3px rgba(206,17,38,0.15); }

        .toggle-pw {
            position: absolute; right: 13px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #3a5a8a; font-size: 13px; padding: 2px;
            transition: color 0.15s;
        }
        .toggle-pw:hover { color: #F5C518; }

        .field-error {
            display: flex; align-items: center; gap: 5px;
            margin-top: 5px; font-size: 11.5px; color: #f87171; font-weight: 500;
        }
        .field-error i { font-size: 10px; }

        .remember-row {
            display: flex; align-items: center; gap: 8px;
        }

        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px; border-radius: 4px;
            accent-color: #F5C518; cursor: pointer; flex-shrink: 0;
        }

        .remember-row label {
            font-size: 13px; color: #6b8abf; cursor: pointer;
            user-select: none;
        }

        .form-footer-row {
            display: flex; align-items: center;
            justify-content: space-between; flex-wrap: wrap; gap: 10px;
            margin-bottom: 22px;
        }

        /* Gold submit button for superadmin */
        .btn-login {
            width: 100%; padding: 13px;
            border-radius: 10px; border: none; cursor: pointer;
            font-family: inherit; font-size: 15px; font-weight: 700;
            color: #001a4d; letter-spacing: 0.2px;
            background: linear-gradient(135deg, #F5C518 0%, #d4a800 100%);
            box-shadow: 0 3px 12px rgba(245,197,24,0.30);
            transition: all 0.18s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(245,197,24,0.42); }
        .btn-login:active { transform: translateY(0); }

        .page-footer {
            margin-top: 20px; font-size: 11px; color: #3a5a8a;
            text-align: center; position: relative; z-index: 1;
        }
    </style>
</head>
<body>

    <div class="stripe"></div>

    <div class="login-card">

        <div class="card-top">
            <a href="{{ url('/') }}" class="card-brand">
                <div class="card-logo">
                    <img src="{{ asset('assets/app_logo.PNG') }}" alt="TCMS Logo">
                </div>
                <div class="card-brand-text">
                    <div class="card-brand-name">{{ config('app.name', 'TCMS') }}</div>
                    <div class="card-brand-sub">TESDA Training Management</div>
                </div>
            </a>
            <div class="superadmin-badge">
                <i class="fas fa-shield-halved"></i>
                Super Admin Portal
            </div>
            <div class="card-headline">
                <h1>Admin Access</h1>
                <p>Restricted area. Authorized personnel only.</p>
            </div>
        </div>

        <div class="card-body">

            @if (session('status'))
                <div class="alert-status">
                    <i class="fas fa-info-circle"></i>
                    {{ session('status') }}
                </div>
            @endif

            {{-- Action points to superadmin login route --}}
            <form method="POST" action="{{ route('superadmin.login') }}">
                @csrf

                <!-- Email -->
                <div class="field">
                    <label for="email">Email Address</label>
                    <div class="input-wrap">
                        <i class="fas fa-envelope"></i>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required autofocus
                            autocomplete="username"
                            placeholder="admin@tcms.com"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                    </div>
                    @error('email')
                        <div class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock"></i>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        >
                        <button type="button" class="toggle-pw" onclick="togglePassword()" title="Show/hide password">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error">
                            <i class="fas fa-exclamation-circle"></i>
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <!-- Remember me -->
                <div class="form-footer-row">
                    <div class="remember-row">
                        <input id="remember_me" type="checkbox" name="remember">
                        <label for="remember_me">Remember me</label>
                    </div>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-shield-halved" style="font-size:14px;"></i>
                    Access Dashboard
                </button>

            </form>
        </div>
    </div>

    <div class="page-footer">
        &copy; {{ date('Y') }} {{ config('app.name', 'TCMS') }} &nbsp;·&nbsp; Powered by TESDA
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('pw-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }
    </script>

</body>
</html>