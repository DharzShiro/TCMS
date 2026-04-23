<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Register Your Training Center — {{ config('app.name', 'TCMS') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css','resources/css/auth/register.css', 'resources/js/app.js'])
    @endif

</head>
<body>

    <div class="stripe"></div>

    <div class="page-header">
        <a href="{{ url('/') }}" class="header-brand">
            <div class="header-logo">
                <img src="{{ asset('assets/app_logo.PNG') }}" alt="TCMS Logo">
            </div>
            <div>
                <div class="header-brand-name">{{ config('app.name', 'TCMS') }}</div>
                <div class="header-brand-sub">TESDA Training Management</div>
            </div>
        </a>
        <a href="{{ route('login') }}" class="btn-back">
            <i class="fas fa-arrow-left" style="font-size:10px;"></i> Back to Login
        </a>
    </div>

    <div class="card">
        <div class="card-top">
            <div class="card-top-inner">
                <div class="icon-badge">
                    <i class="fas fa-building"></i>
                </div>
                <div class="card-headline">
                    <h1>Register Your Training Center</h1>
                    <p>Apply for a TCMS tenant account. Your registration will be reviewed and approved by a super admin before access is granted.</p>
                </div>
            </div>
            <div class="step-pills">
                <div class="step-pill active"><i class="fas fa-circle-dot"></i> Organization Info</div>
                <div class="step-pill active"><i class="fas fa-circle-dot"></i> Subscription Plan</div>
                <div class="step-pill active"><i class="fas fa-circle-dot"></i> Submit for Review</div>
            </div>
        </div>

        <div class="card-body">

            @if (session('status'))
                <div class="alert alert-success">
                    <i class="fas fa-circle-check"></i> {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-error">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}">
                @csrf

                {{-- ── Section 1: Organization Details ── --}}
                <div class="section-header">
                    <div class="section-icon blue"><i class="fas fa-building-columns"></i></div>
                    <div>
                        <div class="section-label">Organization Details</div>
                        <div class="section-sub">Basic information about your training center</div>
                    </div>
                </div>

                <div class="field-row-2">
                    <div class="field">
                        <label for="name">
                            <span class="required-dot"></span> Organization / Center Name
                        </label>
                        <div class="input-wrap">
                            <i class="fas fa-building field-icon"></i>
                            <input id="name" type="text" name="name" value="{{ old('name') }}"
                                   required autofocus autocomplete="organization"
                                   placeholder="e.g. MACC Vocational Institute"
                                   class="{{ $errors->has('name') ? 'is-invalid' : '' }}">
                        </div>
                        @error('name')<div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    </div>

                    <div class="field">
                        <label for="admin_email">
                            <span class="required-dot"></span> Admin Email Address
                        </label>
                        <div class="input-wrap">
                            <i class="fas fa-envelope field-icon"></i>
                            <input id="admin_email" type="email" name="admin_email" value="{{ old('admin_email') }}"
                                   required autocomplete="email"
                                   placeholder="admin@yourcenter.com"
                                   class="{{ $errors->has('admin_email') ? 'is-invalid' : '' }}">
                        </div>
                        @error('admin_email')<div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                        <div class="field-hint"><i class="fas fa-info-circle"></i> Login credentials will be sent here upon approval.</div>
                    </div>
                </div>

                <div class="field">
                    <label for="subdomain">
                        <span class="required-dot"></span> Preferred Subdomain
                    </label>
                    <div class="input-wrap subdomain-wrap">
                        <i class="fas fa-globe field-icon"></i>
                        <input id="subdomain" type="text" name="subdomain" value="{{ old('subdomain') }}"
                               required autocomplete="off"
                               placeholder="yourcenter"
                               pattern="[a-z0-9\-_]+"
                               oninput="this.value = this.value.toLowerCase().replace(/[^a-z0-9\-_]/g, '')"
                               class="{{ $errors->has('subdomain') ? 'is-invalid' : '' }}">
                        <span class="subdomain-suffix">.tcm.com</span>
                    </div>
                    @error('subdomain')<div class="field-error"><i class="fas fa-exclamation-circle"></i>{{ $message }}</div>@enderror
                    <div class="field-hint"><i class="fas fa-info-circle"></i> Lowercase letters, numbers, hyphens and underscores only.</div>
                </div>

                <div class="section-divider"></div>

                {{-- ── Section 2: Subscription Plan ── --}}
                <div class="section-header">
                    <div class="section-icon gold"><i class="fas fa-layer-group"></i></div>
                    <div>
                        <div class="section-label">Choose a Subscription Plan</div>
                        <div class="section-sub">Select the plan that fits your training center's needs</div>
                    </div>
                </div>

                {{-- Hidden input keeps Laravel validation working --}}
                <input type="hidden" name="subscription" id="subscription-value" value="{{ old('subscription', '') }}">

                @if($plans->isEmpty())
                    {{-- ── Empty state: table not yet migrated or no active plans ── --}}
                    <div style="text-align:center;padding:32px 24px;border:2px dashed #c5d8f5;border-radius:14px;color:#5a7aaa;">
                        <i class="fas fa-layer-group" style="font-size:28px;opacity:.3;margin-bottom:10px;display:block;"></i>
                        <p style="font-size:13.5px;">No subscription plans are available at this time.<br>Please contact the administrator.</p>
                    </div>
                @else
                    <div class="plan-grid" id="plan-grid">
                        @foreach($plans as $plan)
                            @php
                                $slug       = $plan->slug;
                                $isCustom   = !in_array($slug, ['basic', 'standard', 'premium']);
                                $isFree     = $plan->price == 0;
                                $isPopular  = $slug === 'standard';
                                $isBest     = $slug === 'premium';

                                // Icon from DB, fall back to slug-based default
                                $icon = $plan->icon ?? match($slug) {
                                    'basic'    => '🌱',
                                    'standard' => '🚀',
                                    'premium'  => '💎',
                                    default    => '📦',
                                };

                                // Price display
                                $formattedPrice = $isFree
                                    ? 'Free'
                                    : '₱' . number_format($plan->price, 0);

                                // Trainee limit label
                                $traineeLabel = $plan->max_trainees
                                    ? 'Up to ' . number_format($plan->max_trainees) . ' trainees'
                                    : 'Unlimited trainees';

                                // Export formats
                                $exportFmts   = $plan->allowed_export_formats ?? [];
                                $exportLabel  = count($exportFmts) === 0
                                    ? null
                                    : strtoupper(implode('/', $exportFmts)) . ' export';
                                $exportLimit  = $plan->max_exports_monthly;
                                $exportDesc   = $exportLimit === null
                                    ? 'unlimited'
                                    : number_format($exportLimit) . ' records/mo';

                                // Color theme per slug (custom plans get a purple accent)
                                $accentColor = match($slug) {
                                    'basic'    => '#5a7aaa',
                                    'standard' => '#CE1126',
                                    'premium'  => '#b38a00',
                                    default    => '#7c3aed',
                                };
                                $badgeBg = match($slug) {
                                    'basic'    => 'rgba(90,122,170,.12)',
                                    'standard' => '#fff0f2',
                                    'premium'  => 'rgba(245,197,24,.14)',
                                    default    => 'rgba(124,58,237,.12)',
                                };
                                $badgeText = match($slug) {
                                    'basic'    => '#5a7aaa',
                                    'standard' => 'var(--red)',
                                    'premium'  => '#b38a00',
                                    default    => '#7c3aed',
                                };
                            @endphp

                            <div class="plan-card {{ old('subscription') === $slug ? 'selected' . ($slug === 'premium' ? ' selected-premium' : '') : '' }}"
                                 data-plan="{{ $slug }}"
                                 onclick="selectPlan('{{ $slug }}')">

                                {{-- Badge (Popular / Best Value / Custom) --}}
                                @if($isPopular)
                                    <div class="plan-badge popular">Popular</div>
                                @elseif($isBest)
                                    <div class="plan-badge best">Best Value</div>
                                @elseif($isCustom)
                                    <div class="plan-badge" style="background:rgba(124,58,237,.15);color:#7c3aed;">Custom</div>
                                @endif

                                <div class="plan-check"><i class="fas fa-check"></i></div>

                                {{-- Icon --}}
                                <div class="plan-icon {{ $slug }}" style="font-size:22px;line-height:1;margin-bottom:6px;">
                                    {{ $icon }}
                                </div>

                                <div class="plan-name">{{ $plan->name }}</div>

                                {{-- Price --}}
                                <div class="plan-price">
                                    @if($isFree)
                                        <span style="font-size:22px;font-weight:800;color:#5a7aaa;">Free</span>
                                    @else
                                        Starts at <span>{{ $formattedPrice }}</span>
                                    @endif
                                </div>

                                {{-- Feature list --}}
                                <ul class="plan-features">
                                    <li><i class="fas fa-check"></i> {{ $traineeLabel }}</li>

                                    @if($plan->max_courses)
                                        <li><i class="fas fa-check"></i> Up to {{ number_format($plan->max_courses) }} courses</li>
                                    @else
                                        <li><i class="fas fa-check"></i> Unlimited courses</li>
                                    @endif

                                    <li><i class="fas fa-check"></i> Enrollments &amp; attendance</li>

                                    @if($plan->has_trainers)
                                        <li><i class="fas fa-check"></i> Trainer management</li>
                                    @else
                                        <li class="muted"><i class="fas fa-xmark"></i> Trainer management</li>
                                    @endif

                                    @if($plan->has_assessments)
                                        <li><i class="fas fa-check"></i> Assessments &amp; schedules</li>
                                    @else
                                        <li class="muted"><i class="fas fa-xmark"></i> Assessments &amp; reports</li>
                                    @endif

                                    @if($exportLabel)
                                        <li><i class="fas fa-check"></i> {{ $exportLabel }} ({{ $exportDesc }})</li>
                                    @else
                                        <li class="muted"><i class="fas fa-xmark"></i> Data exports</li>
                                    @endif

                                    @if($plan->has_certificates)
                                        <li><i class="fas fa-check"></i> Certifications</li>
                                    @else
                                        <li class="muted"><i class="fas fa-xmark"></i> Certificates</li>
                                    @endif

                                    @if($plan->has_branding)
                                        <li><i class="fas fa-check"></i> Custom branding</li>
                                    @endif

                                    @if($plan->has_custom_reports)
                                        <li><i class="fas fa-check"></i> Custom reports</li>
                                    @endif
                                </ul>

                                {{-- Duration badge --}}
                                <div style="margin-top:10px;">
                                    <span style="font-size:10px;background:{{ $badgeBg }};color:{{ $badgeText }};padding:3px 8px;border-radius:5px;font-weight:700;">
                                        {{ $plan->duration_label }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                @error('subscription')
                    <div class="field-error" style="margin-top:8px;"><i class="fas fa-exclamation-circle"></i> {{ $message }}</div>
                @enderror

                <div class="section-divider"></div>

                <div class="notice-box">
                    <i class="fas fa-clock"></i>
                    <p>
                        <strong>Your application will be reviewed.</strong>
                        After submitting, a super admin will review and approve your registration. You'll receive your login credentials and tenant URL at your admin email once approved.
                    </p>
                </div>

                <button type="submit" class="btn-submit" id="submit-btn">
                    <i class="fas fa-paper-plane" style="font-size:13px;"></i>
                    Submit Application
                </button>

                <div class="login-row">
                    Already have an account?
                    <a href="{{ route('login') }}">Log in here</a>
                </div>
            </form>
        </div>
    </div>

    <div class="page-footer">
        &copy; {{ date('Y') }} {{ config('app.name', 'TCMS') }} &nbsp;·&nbsp; Powered by TESDA &nbsp;·&nbsp; All rights reserved.
    </div>

    <script>
        function selectPlan(plan) {
            document.getElementById('subscription-value').value = plan;
            document.querySelectorAll('.plan-card').forEach(c => {
                c.classList.remove('selected', 'selected-premium');
            });
            const sel = document.querySelector('[data-plan="' + plan + '"]');
            if (!sel) return;
            sel.classList.add('selected');
            // Keep the gold glow for premium; apply it to any card whose accent is gold-ish
            // We detect this by checking if the card has the "best" badge or slug === 'premium'
            if (plan === 'premium') sel.classList.add('selected-premium');
        }

        document.addEventListener('DOMContentLoaded', function () {
            const val = document.getElementById('subscription-value').value;
            if (val) selectPlan(val);

            const subdomainInput = document.getElementById('subdomain');
            if (subdomainInput) {
                subdomainInput.addEventListener('input', function () {
                    const suffix = this.closest('.subdomain-wrap').querySelector('.subdomain-suffix');
                    suffix.style.color      = this.value.length > 0 ? 'var(--blue)' : '';
                    suffix.style.fontWeight = this.value.length > 0 ? '700' : '';
                });
            }
        });
    </script>

    @if (session('status') === 'submitted')
    <div id="successModal" style="position:fixed;inset:0;z-index:9999;background:rgba(0,20,60,0.55);display:flex;align-items:center;justify-content:center;padding:24px;">
        <div style="background:#fff;border-radius:20px;width:100%;max-width:420px;overflow:hidden;animation:modalIn 0.35s cubic-bezier(0.34,1.56,0.64,1) forwards;">
            <div style="height:4px;background:linear-gradient(90deg,#CE1126 0%,#CE1126 33%,#0057B8 33%,#0057B8 66%,#F5C518 66%,#F5C518 100%);"></div>
            <div style="padding:32px 28px 28px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:#e9f8ef;border:2px solid #6ee7a0;display:flex;align-items:center;justify-content:center;margin:0 auto 20px;">
                    <i class="fas fa-check" style="font-size:22px;color:#16a34a;"></i>
                </div>
                <h2 style="font-size:18px;font-weight:700;color:#001a4d;margin:0 0 8px;">Application submitted!</h2>
                <p style="font-size:13.5px;color:#5a7aaa;line-height:1.6;margin:0 0 20px;">
                    Your training center registration has been received. A super admin will review your application shortly.
                </p>
                <div style="background:#f0f5ff;border:1px solid #c5d8f5;border-radius:10px;padding:12px 14px;text-align:left;margin-bottom:22px;">
                    <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#1a3a6b;margin-bottom:6px;">
                        <i class="fas fa-envelope" style="color:#0057B8;width:14px;"></i>
                        Credentials will be sent to your admin email
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#1a3a6b;margin-bottom:6px;">
                        <i class="fas fa-clock" style="color:#0057B8;width:14px;"></i>
                        Review typically takes 1–2 business days
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;font-size:12.5px;color:#1a3a6b;">
                        <i class="fas fa-layer-group" style="color:#0057B8;width:14px;"></i>
                        Your tenant URL will be confirmed upon approval
                    </div>
                </div>
                <a href="{{ url('/') }}" style="display:block;width:100%;padding:12px;border-radius:10px;border:none;font-size:14px;font-weight:700;color:#fff;background:linear-gradient(135deg,#003087 0%,#0057B8 100%);text-decoration:none;margin-bottom:10px;">
                    Back to home page
                </a>
                <a href="{{ route('login') }}" style="display:block;width:100%;padding:11px;border-radius:10px;border:1.5px solid #c5d8f5;font-size:13.5px;font-weight:600;color:#5a7aaa;text-decoration:none;">
                    Go to login
                </a>
            </div>
        </div>
    </div>
    <style>
        @keyframes modalIn {
            from { opacity:0; transform:scale(0.88) translateY(12px); }
            to   { opacity:1; transform:scale(1) translateY(0); }
        }
    </style>
    @endif

</body>
</html>