<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Sign In — {{ config('app.name', 'TCMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=playfair-display:700,800i&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/css/tenants/auth/login.css', 'resources/js/app.js'])
    @endif
    @php
        $tenant       = tenancy()->tenant ?? null;
        $colorPrimary = $tenant?->brand_color_primary ?? '#003087';
        $colorAccent  = $tenant?->brand_color_accent  ?? '#CE1126';
    @endphp
    <style>
        :root {
            --navy:   {{ $colorPrimary }};
            --blue:   color-mix(in srgb, {{ $colorPrimary }} 60%, #0057B8 40%);
            --red:    {{ $colorAccent }};
            --red-dk: color-mix(in srgb, {{ $colorAccent }} 70%, #000 30%);
        }
    </style>

</head>
<body>

    <div class="stripe"></div>
    <div class="bg-blobs"><span></span><span></span><span></span></div>

    @php $tenant = tenancy()->tenant ?? null; @endphp

    <div class="login-card">

        {{-- LEFT HERO --}}
        <div class="hero-side">
            <div class="hero-dot"></div>
            <div class="hero-arc"></div>

            <div class="hero-top">
                <a href="{{ url('/') }}" class="hero-brand">
                    <div class="hero-brand-logo">
                        @if ($tenant?->brand_logo)
                            <img src="{{ asset('storage/' . $tenant->brand_logo) }}" alt="Logo">
                        @else
                            <img src="{{ asset('assets/app_logo.PNG') }}" alt="Logo">
                        @endif
                    </div>
                    <div>
                        <div class="hero-brand-name">{{ $tenant?->brand_name ?? $tenant?->name ?? config('app.name', 'TCMS') }}</div>
                        <div class="hero-brand-sub">TESDA Training Management</div>
                    </div>
                </a>

                <div class="hero-heading">
                    Welcome<br>
                    <em>back.</em>
                </div>

                <p class="hero-sub">
                    Sign in to your training portal to continue your courses, check your assessments, and download your certificates.
                </p>

                <ul class="hero-perks">
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-graduation-cap"></i></div> View your enrolled courses</li>
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-clipboard-check"></i></div> Check assessment results</li>
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-certificate"></i></div> Download certificates</li>
                    <li class="hero-perk"><div class="perk-pip w"><i class="fas fa-calendar-alt"></i></div> View attendance records</li>
                </ul>
            </div>

            <div class="hero-bottom">
                @if ($tenant)
                    <div class="tenant-tag">
                        <i class="fas fa-building"></i>
                        {{ $tenant->brand_name ?? $tenant->name }}
                    </div>
                @endif
            </div>
        </div>

        {{-- RIGHT FORM --}}
        <div class="form-side">

            <div class="form-eyebrow">Trainee Portal</div>
            <div class="form-title">Sign in to your <span>account</span></div>
            <p class="form-sub">Enter your credentials to access your training dashboard.</p>

            @if (session('status'))
                <div class="alert-ok">
                    <i class="fas fa-circle-check"></i>
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                {{-- Email --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-envelope fld-icon"></i>
                        <input
                            id="email" type="email" name="email"
                            value="{{ old('email') }}"
                            placeholder=" "
                            required autofocus autocomplete="username"
                            class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                        >
                        <label for="email">Email Address</label>
                    </div>
                    @error('email')
                        <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Password --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-lock fld-icon"></i>
                        <input
                            id="password" type="password" name="password"
                            placeholder=" "
                            required autocomplete="current-password"
                            class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                        >
                        <label for="password">Password</label>
                        <button type="button" class="toggle-pw" onclick="togglePw('password','pw-icon')">
                            <i class="fas fa-eye" id="pw-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Remember me --}}
                <div class="remember-row" style="margin-top:4px;">
                    <input id="remember_me" type="checkbox" name="remember">
                    <label for="remember_me">Keep me signed in</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt" style="font-size:13px;"></i>
                    Sign In
                    <span class="arrow-box"><i class="fas fa-arrow-right"></i></span>
                </button>

                <div style="display:flex;align-items:center;gap:10px;margin:16px 0;">
                    <div style="flex:1;height:1px;background:var(--border);"></div>
                    <span style="font-size:10px;font-weight:800;color:var(--muted);letter-spacing:0.8px;text-transform:uppercase;">or continue with</span>
                    <div style="flex:1;height:1px;background:var(--border);"></div>
                </div>

                <a href="{{ route('auth.google') }}"
                style="
                    display:flex;align-items:center;justify-content:center;gap:10px;
                    width:100%;padding:13px 20px;border-radius:12px;
                    border:1.5px solid var(--border);background:#fff;
                    font-family:inherit;font-size:14px;font-weight:700;color:var(--text);
                    text-decoration:none;cursor:pointer;
                    transition:all 0.22s cubic-bezier(0.4,0,0.2,1);
                    box-shadow:0 2px 8px rgba(0,48,135,0.07);
                "
                onmouseover="this.style.borderColor='#4285F4';this.style.boxShadow='0 4px 16px rgba(66,133,244,0.18)';"
                onmouseout="this.style.borderColor='var(--border)';this.style.boxShadow='0 2px 8px rgba(0,48,135,0.07)';"
                >
                    <svg width="18" height="18" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#4285F4" d="M45.12 24.5c0-1.57-.14-3.08-.4-4.54H24v8.58h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.2z"/>
                        <path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/>
                        <path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34C2.85 17.09 2 20.45 2 24c0 3.55.85 6.91 2.34 9.88l7.35-5.7z"/>
                        <path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/>
                    </svg>
                    Sign in with Google
                </a>

                <div class="register-row">
                    Don't have an account?
                    <a href="/register">Create one here</a>
                </div>

            </form>

            <div class="form-note">
                This portal is for
                <strong style="color:#7a9abf;">{{ $tenant?->brand_name ?? $tenant?->name ?? config('app.name') }}</strong>
                trainees only. If you're an admin or trainer, contact your training center administrator.
            </div>

        </div>
    </div>

    <div class="page-footer">
        &copy; {{ date('Y') }} {{ $tenant?->brand_name ?? $tenant?->name ?? config('app.name', 'TCMS') }}
        &nbsp;·&nbsp; Powered by TESDA
    </div>

    <script>
        function togglePw(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon  = document.getElementById(iconId);
            input.type  = input.type === 'password' ? 'text' : 'password';
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        }
    </script>

</body>
</html>