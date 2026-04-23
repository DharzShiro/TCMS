{{-- sidebar-admin.blade.php --}}
<style>
    :root {
        --sb-surface:      #ffffff;
        --sb-surface2:     #f0f5ff;
        --sb-border:       #c5d8f5;
        --sb-text:         #001a4d;
        --sb-text-sec:     #1a3a6b;
        --sb-muted:        #5a7aaa;
        --sb-shadow:       0 1px 3px rgba(0,48,135,0.08), 0 1px 2px rgba(0,48,135,0.04);
        --sb-accent:       #0057B8;
        --sb-accent-bg:    #e8f0fb;
        --sb-accent-hover: #dde8f8;
        --sb-accent-dark:  #5b9cf6;
        --sb-red:          #CE1126;
        --sb-red-bg:       #fff0f2;
    }
    .dark {
        --sb-surface:      #0a1628;
        --sb-surface2:     #0d1f3c;
        --sb-border:       #1e3a6b;
        --sb-text:         #dde8ff;
        --sb-text-sec:     #adc4f0;
        --sb-muted:        #6b8abf;
        --sb-accent-bg:    rgba(0,87,184,0.15);
        --sb-accent-hover: rgba(0,87,184,0.10);
        --sb-red-bg:       rgba(206,17,38,0.12);
    }
    .sb-section-label {
        font-size: 10px; font-weight: 700; letter-spacing: .1em;
        text-transform: uppercase; color: var(--sb-muted);
        padding: 0 16px; margin-bottom: 6px; margin-top: 16px; display: block;
    }
    .sb-link {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 16px; border-radius: 10px;
        font-size: 13.5px; font-weight: 500; color: var(--sb-muted);
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        position: relative; overflow: hidden;
    }
    .sb-link .sb-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0; background: transparent;
        transition: background 0.18s ease, color 0.18s ease;
    }
    .sb-link:hover { background: var(--sb-surface2); color: var(--sb-text-sec); box-shadow: var(--sb-shadow); }
    .sb-link:hover .sb-icon { background: var(--sb-border); color: var(--sb-text); }
    .sb-link.active { background: var(--sb-accent-bg); color: var(--sb-accent); font-weight: 600; box-shadow: 0 1px 4px rgba(0,87,184,0.12); }
    .dark .sb-link.active { color: var(--sb-accent-dark); background: var(--sb-accent-bg); box-shadow: 0 1px 6px rgba(0,87,184,0.20); }
    .sb-link.active .sb-icon { background: rgba(0,87,184,0.12); color: var(--sb-accent); }
    .dark .sb-link.active .sb-icon { background: rgba(0,87,184,0.22); color: var(--sb-accent-dark); }
    .sb-link.active::before {
        content: ''; position: absolute; left: 0; top: 20%; bottom: 20%;
        width: 3px; border-radius: 0 3px 3px 0; background: var(--sb-accent);
    }
    .dark .sb-link.active::before { background: var(--sb-accent-dark); }
    .sb-divider { height: 1px; background: var(--sb-border); margin: 12px 16px; }

    /* Locked link style */
    .sb-link-locked {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 16px; border-radius: 10px;
        font-size: 13.5px; font-weight: 500;
        color: var(--sb-border);
        cursor: not-allowed; position: relative; overflow: hidden;
        opacity: 0.5;
    }
    .sb-link-locked .sb-icon {
        width: 32px; height: 32px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0;
    }
    .sb-lock-badge {
        margin-left: auto; font-size: 9px; font-weight: 700;
        letter-spacing: 0.8px; text-transform: uppercase;
        padding: 2px 6px; border-radius: 6px;
        background: rgba(245,197,24,0.15); color: #d4a800;
        border: 1px solid rgba(245,197,24,0.30);
    }
    .dark .sb-lock-badge { background: rgba(245,197,24,0.10); color: #F5C518; }
</style>

<nav class="space-y-1 p-3">

    <span class="sb-section-label">Main</span>

    <a href="{{ route('admin.dashboard') }}"
       class="sb-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-chart-line"></i></span>
        Dashboard
    </a>

    <div class="sb-divider"></div>
    <span class="sb-section-label">Management</span>

    {{-- Standard+ --}}
    @plan('trainers')
        <a href="{{ route('admin.trainers.index') }}"
           class="sb-link {{ request()->routeIs('admin.trainers*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-chalkboard-teacher"></i></span>
            Trainers
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-chalkboard-teacher"></i></span>
            Trainers
            <span class="sb-lock-badge">Standard</span>
        </span>
    @endplan

    {{-- Basic+ --}}
    @plan('trainees')
        <a href="{{ route('admin.trainees.index') }}"
           class="sb-link {{ request()->routeIs('admin.trainees*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-user-graduate"></i></span>
            Trainees
        </a>
    @endplan

    {{-- Standard+ --}}
    @plan('users')
        <a href="{{ route('admin.users.index') }}"
           class="sb-link {{ request()->routeIs('admin.users*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-user-shield"></i></span>
            Users
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-user-shield"></i></span>
            Users
            <span class="sb-lock-badge">Standard</span>
        </span>
    @endplan

    <div class="sb-divider"></div>
    <span class="sb-section-label">Training</span>

    {{-- Basic+ --}}
    @plan('courses')
        <a href="{{ route('admin.courses.index') }}"
           class="sb-link {{ request()->routeIs('admin.courses*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-book-open"></i></span>
            Courses
        </a>
    @endplan

    @plan('enrollments')
        <a href="{{ route('admin.enrollments.index') }}"
           class="sb-link {{ request()->routeIs('admin.enrollments*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-user-plus"></i></span>
            Enrollments
        </a>
    @endplan

    {{-- Standard+ --}}
    @plan('training-schedules')
        <a href="{{ route('admin.training-schedules.index') }}"
           class="sb-link {{ request()->routeIs('admin.training-schedules*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-calendar-alt"></i></span>
            Schedules
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-calendar-alt"></i></span>
            Schedules
            <span class="sb-lock-badge">Standard</span>
        </span>
    @endplan

    {{-- Basic+ --}}
    @plan('attendances')
        <a href="{{ route('admin.attendances.index') }}"
           class="sb-link {{ request()->routeIs('admin.attendances*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-calendar-check"></i></span>
            Attendance
        </a>
    @endplan

    <div class="sb-divider"></div>
    <span class="sb-section-label">Assessment</span>

    {{-- Standard+ --}}
    @plan('assessments')
        <a href="{{ route('admin.assessments.index') }}"
           class="sb-link {{ request()->routeIs('admin.assessments*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-clipboard-check"></i></span>
            Assessments
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-clipboard-check"></i></span>
            Assessments
            <span class="sb-lock-badge">Standard</span>
        </span>
    @endplan

    {{-- Premium only --}}
    @plan('certificates')
        <a href="{{ route('admin.certificates.index') }}"
           class="sb-link {{ request()->routeIs('admin.certificates*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-certificate"></i></span>
            Certifications
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-certificate"></i></span>
            Certifications
            <span class="sb-lock-badge">Premium</span>
        </span>
    @endplan

    <div class="sb-divider"></div>
    <span class="sb-section-label">Reports</span>

    @plan('reports')
        <a href="{{ route('admin.reports.index') }}"
           class="sb-link {{ request()->routeIs('admin.reports.index') || (request()->routeIs('admin.reports*') && !request()->routeIs('admin.reports.custom*')) ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-chart-bar"></i></span>
            Analytics & Reports
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-chart-bar"></i></span>
            Analytics & Reports
            <span class="sb-lock-badge">Standard</span>
        </span>
    @endplan

    @plan('custom-reports')
        <a href="{{ route('admin.reports.custom.index') }}"
           class="sb-link {{ request()->routeIs('admin.reports.custom*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-wrench"></i></span>
            Custom Builder
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-wrench"></i></span>
            Custom Builder
            <span class="sb-lock-badge">Premium</span>
        </span>
    @endplan

    @plan('branding')
        <a href="{{ route('admin.branding.index') }}"
           class="sb-link {{ request()->routeIs('admin.branding*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-palette"></i></span>
            Custom Branding
        </a>
    @else
        <span class="sb-link-locked">
            <span class="sb-icon"><i class="fas fa-palette"></i></span>
            Custom Branding
            <span class="sb-lock-badge">Premium</span>
        </span>
    @endplan

    <div class="sb-divider"></div>
    <span class="sb-section-label">System</span>

    <a href="#"
       class="sb-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-sliders-h"></i></span>
        Settings
    </a>

    {{-- Update Status --}}
    @php
        try {
            $tenantId       = tenancy()->tenant->id ?? null;
            $updateStatus   = $tenantId
                ? \App\Models\TenantVersionStatus::where('tenant_id', $tenantId)->value('update_status')
                : null;
            $updatePending  = in_array($updateStatus, ['update_available', 'failed']);
            $unreadTickets  = $tenantId
                ? \App\Models\SupportTicket::where('tenant_id', $tenantId)->where('unread_tenant', '>', 0)->count()
                : 0;
        } catch(\Throwable $e) {
            $updatePending = false;
            $unreadTickets = 0;
        }
    @endphp

    <a href="{{ route('admin.update.index') }}"
       class="sb-link {{ request()->routeIs('admin.update*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-cloud-download-alt"></i></span>
        System Update
        @if($updatePending)
            <span style="margin-left:auto;background:#b38a00;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;">NEW</span>
        @endif
    </a>

    <a href="{{ route('admin.support.index') }}"
       class="sb-link {{ request()->routeIs('admin.support*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-headset"></i></span>
        Support
        @if($unreadTickets > 0)
            <span style="margin-left:auto;background:#CE1126;color:#fff;font-size:10px;font-weight:700;padding:1px 7px;border-radius:999px;">{{ $unreadTickets }}</span>
        @endif
    </a>

    {{-- Upgrade Plan CTA --}}
    @php
        $tenant      = tenancy()->tenant;
        $currentPlan = $tenant->subscription ?? 'basic';
    @endphp

    @if($currentPlan !== 'premium')
    <div class="sb-divider"></div>

    <a href="{{ route('admin.subscription.index') }}"
       style="
            display: flex; align-items: center; gap: 12px;
            padding: 11px 16px; border-radius: 12px; text-decoration: none;
            background: linear-gradient(135deg, #003087 0%, #CE1126 150%);
            margin: 0 0 4px; position: relative; overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 16px rgba(0,48,135,0.25);
       "
       onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 24px rgba(0,48,135,0.35)'"
       onmouseout="this.style.transform=''; this.style.boxShadow='0 4px 16px rgba(0,48,135,0.25)'">

        <!-- Shimmer effect -->
        <span style="
            position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            animation: shimmer 2.5s infinite;
        "></span>

        <span style="
            width: 32px; height: 32px; border-radius: 8px;
            background: rgba(255,255,255,0.18);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        ">⚡</span>

        <div style="flex: 1; min-width: 0;">
            <div style="font-size: 12.5px; font-weight: 700; color: #fff; line-height: 1.2;">
                Upgrade Plan
            </div>
            <div style="font-size: 11px; color: rgba(255,255,255,0.72); margin-top: 1px; text-transform: capitalize;">
                Currently on {{ $currentPlan }}
            </div>
        </div>

        <span style="
            font-size: 9px; font-weight: 800; letter-spacing: 0.6px;
            text-transform: uppercase; padding: 3px 7px; border-radius: 6px;
            background: #F5C518; color: #1a1a00;
        ">PRO</span>
    </a>

    <style>
        @keyframes shimmer {
            0%   { left: -100%; }
            100% { left: 200%; }
        }
    </style>
    @endif
</nav>