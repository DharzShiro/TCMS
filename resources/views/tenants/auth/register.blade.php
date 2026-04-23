<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Create Account — {{ config('app.name', 'TCMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=playfair-display:700,800i&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css','resources/css/tenants/auth/register.css', 'resources/js/app.js'])
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

    <div class="register-card">

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
                    Begin your<br>
                    <em>learning journey</em><br>
                    with us.
                </div>

                <p class="hero-sub">
                    Join your training center's digital portal. Access TESDA-aligned courses, track attendance, and earn nationally recognized certificates.
                </p>

                <ul class="hero-perks">
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-graduation-cap"></i></div> Enroll in available courses</li>
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-clipboard-check"></i></div> View competency assessments</li>
                    <li class="hero-perk"><div class="perk-pip g"><i class="fas fa-certificate"></i></div> Download your certificates</li>
                    <li class="hero-perk"><div class="perk-pip w"><i class="fas fa-calendar-alt"></i></div> Monitor your attendance</li>
                    <li class="hero-perk"><div class="perk-pip w"><i class="fas fa-chart-line"></i></div> Track training progress</li>
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

            <div class="form-eyebrow">New Account</div>
            <div class="form-title">Create your <span>profile</span></div>
            <p class="form-sub">Fill in your details to register as a trainee.</p>

            <div class="trainee-pill">
                <div class="trainee-pill-icon"><i class="fas fa-user-graduate"></i></div>
                <div class="trainee-pill-text">
                    <strong>Registering as Trainee</strong>
                    <span>Enroll in courses &amp; track your progress</span>
                </div>
            </div>

            <form method="POST" action="/register">
                @csrf

                {{-- Full Name --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-user fld-icon"></i>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" placeholder=" " required autofocus autocomplete="name" class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                        <label for="name">Full Name</label>
                    </div>
                    @error('name')
                        <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-envelope fld-icon"></i>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" placeholder=" " required autocomplete="username" class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                        <label for="email">Email Address</label>
                    </div>
                    @error('email')
                        <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-sep"><span>Set a password</span></div>

                {{-- Password --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-lock fld-icon"></i>
                        <input id="password" type="password" name="password" placeholder=" " required autocomplete="new-password" class="{{ $errors->has('password') ? 'is-invalid' : '' }}" oninput="checkStrength(this.value)">
                        <label for="password">Password</label>
                        <button type="button" class="toggle-pw" onclick="togglePw('password','pw-icon')"><i class="fas fa-eye" id="pw-icon"></i></button>
                    </div>
                    <div class="strength-wrap">
                        <div class="strength-bar"><span id="s1"></span><span id="s2"></span><span id="s3"></span><span id="s4"></span></div>
                        <div class="strength-label" id="strength-label"></div>
                    </div>
                    @error('password')
                        <div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>
                    @enderror
                </div>

                {{-- Confirm Password --}}
                <div class="field">
                    <div class="field-float">
                        <i class="fas fa-lock fld-icon"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder=" " required autocomplete="new-password">
                        <label for="password_confirmation">Confirm Password</label>
                        <button type="button" class="toggle-pw" onclick="togglePw('password_confirmation','cpw-icon')"><i class="fas fa-eye" id="cpw-icon"></i></button>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus" style="font-size:13px;"></i>
                    Create My Account
                    <span class="arrow-box"><i class="fas fa-arrow-right"></i></span>
                </button>

                <div class="signin-row">
                    Already have an account?
                    <a href="/login">Sign in here</a>
                </div>

            </form>

            <div class="form-note">
                By registering, you agree to your training center's policies.
                Your account will be linked to
                <strong style="color:#7a9abf;">{{ $tenant?->brand_name ?? $tenant?->name ?? config('app.name') }}</strong>.
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

        function checkStrength(value) {
            const bars    = ['s1','s2','s3','s4'].map(id => document.getElementById(id));
            const label   = document.getElementById('strength-label');
            const palette = { 1:'#CE1126', 2:'#f97316', 3:'#eab308', 4:'#16a34a' };
            const words   = { 1:'Weak', 2:'Fair', 3:'Good', 4:'Strong' };
            let score = 0;
            if (value.length >= 8)          score++;
            if (/[A-Z]/.test(value))        score++;
            if (/[0-9]/.test(value))        score++;
            if (/[^A-Za-z0-9]/.test(value)) score++;
            bars.forEach((bar, i) => { bar.style.background = i < score ? (palette[score] || '') : ''; });
            label.textContent = value.length > 0 ? (words[score] || '') : '';
            label.style.color = palette[score] || '#5a7aaa';
        }
    </script>

</body>
</html>