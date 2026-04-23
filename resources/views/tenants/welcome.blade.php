<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $brandName }} — TESDA Training Center Management</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/css/tenants/welcome.css', 'resources/js/app.js'])
        @endif

        {{-- Tenant brand overrides — must come AFTER Vite CSS to win the cascade --}}
        <style>
            :root {
                --tesda-navy:   {{ $colorPrimary }};
                --tesda-blue:   color-mix(in srgb, {{ $colorPrimary }} 60%, #0057B8 40%);
                --tesda-red:    {{ $colorAccent }};
                --tesda-red-dk: color-mix(in srgb, {{ $colorAccent }} 70%, #000 30%);
            }
        </style>
    </head>
    <body>

        {{-- Top stripe uses accent + primary --}}
        <div class="top-stripe" style="background: linear-gradient(90deg,
            {{ $colorAccent }} 0%, {{ $colorAccent }} 33%,
            {{ $colorPrimary }} 33%, {{ $colorPrimary }} 66%,
            #F5C518 66%, #F5C518 100%
        );"></div>

        <!-- Header -->
        <header>
            <a href="#" class="header-brand">
                <div class="header-logo">
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }} Logo">
                </div>
                <div class="header-brand-text">
                    <div class="header-brand-name">{{ $brandName }}</div>
                    <div class="header-brand-sub">{{ $brandTagline }}</div>
                </div>
            </a>

            @if (Route::has('login'))
                <nav class="header-nav">
                    @auth
                        @php
                            $role = Auth::user()->role ?? null;
                            $dashboardRoute = match($role) {
                                'admin'   => 'admin.dashboard',
                                'trainer' => 'trainer.dashboard',
                                'trainee' => 'trainee.dashboard',
                                default   => 'dashboard'
                            };
                        @endphp
                        <a href="{{ route($dashboardRoute) }}" class="btn-dashboard">
                            <i class="fas fa-tachometer-alt"></i>Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-ghost">Log in</a>
                        <a href="/register" class="btn-tenant-header">
                            <i class="fas fa-building" style="font-size:11px;"></i> Register Center
                        </a>
                    @endauth
                </nav>
            @endif
        </header>

        <!-- Main card -->
        <div class="main-card">

            <!-- Left hero panel — gradient driven by tenant primary color -->
            <div class="hero-panel" style="background: linear-gradient(155deg, {{ $colorPrimary }} 0%, color-mix(in srgb, {{ $colorPrimary }} 60%, #0057B8 40%) 100%);">
                <div class="hero-seal">
                    <img src="{{ $brandLogo }}" alt="{{ $brandName }}">
                </div>
                <div class="hero-title">
                    {{ $brandName }}<br><span>Competency</span><br>Management
                </div>
                <p class="hero-desc">
                    A cloud-based multi-tenant SaaS system for TESDA-accredited training centers — digitizing operations from enrollment to certification.
                </p>
                <div class="hero-badge">
                    <i class="fas fa-shield-halved" style="font-size:10px;"></i>
                    TESDA-Accredited Platform
                </div>
            </div>

            <!-- Right content panel -->
            <div class="content-panel">
                <div class="content-label">What is {{ $brandName }}?</div>
                <div class="content-title">Training Center Management System</div>
                <p class="content-sub">
                    Each training center operates as its own secure tenant — with isolated records for trainees, courses, and assessments — while super admins maintain centralized oversight across all institutions.
                </p>

                <ul class="feature-list">
                    <li class="feature-item">
                        <div class="feature-dot blue"><i class="fas fa-user-graduate"></i></div>
                        <div class="feature-text">
                            <strong>Trainee Enrollment & Records</strong>
                            <span>Register trainees, manage enrollments, and track course completion per training center.</span>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-dot red"><i class="fas fa-clipboard-check"></i></div>
                        <div class="feature-text">
                            <strong>Assessment & Competency Tracking</strong>
                            <span>Record TESDA-aligned competency assessments and monitor trainee progress in real time.</span>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-dot gold"><i class="fas fa-certificate"></i></div>
                        <div class="feature-text">
                            <strong>Certification Management</strong>
                            <span>Issue and track national certificates and course completions automatically upon assessment.</span>
                        </div>
                    </li>
                    <li class="feature-item">
                        <div class="feature-dot navy"><i class="fas fa-chart-bar"></i></div>
                        <div class="feature-text">
                            <strong>Reports & Analytics</strong>
                            <span>Generate training performance reports per center with CSV, Excel, and PDF export options.</span>
                        </div>
                    </li>
                </ul>

                <!-- Primary CTAs -->
                <div class="cta-row">
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn-cta-primary">
                            <i class="fas fa-rocket" style="font-size:12px;"></i> Get Started
                        </a>
                    @endif
                    @if (Route::has('login'))
                        <a href="{{ route('login') }}" class="btn-cta-secondary">
                            <i class="fas fa-sign-in-alt" style="font-size:12px;"></i> Log In
                        </a>
                    @endif
                </div>

                <!-- Training Center CTA block -->
                <div class="tenant-cta-block">
                    <div class="tenant-cta-text">
                        <strong><i class="fas fa-building-columns" style="font-size:11px;margin-right:5px;color:var(--tesda-blue);"></i>Are you a training center?</strong>
                        <span>Apply to get your own tenant portal with trainee records, assessments, and certifications.</span>
                    </div>
                    <a href="{{ route('register') }}" class="btn-cta-tenant">
                        <i class="fas fa-building" style="font-size:11px;"></i> Register Your Center
                    </a>
                </div>

            </div>
        </div>

        <footer>
            &copy; {{ date('Y') }} {{ $brandName }} &nbsp;·&nbsp; Powered by TESDA &nbsp;·&nbsp; All rights reserved.
        </footer>

    </body>
</html>