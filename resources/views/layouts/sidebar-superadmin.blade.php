{{-- sidebar-superadmin.blade.php --}}
<style>
    /* ══════════════════════════════════════════
       SUPERADMIN SIDEBAR DESIGN TOKENS
    ══════════════════════════════════════════ */
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

    /* ── Section label ── */
    .sb-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--sb-muted);
        padding: 0 16px;
        margin-bottom: 6px;
        margin-top: 16px;
        display: block;
    }

    /* ── Nav link base ── */
    .sb-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13.5px;
        font-weight: 500;
        color: var(--sb-muted);
        text-decoration: none;
        transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
        position: relative;
        overflow: hidden;
    }

    /* icon wrapper */
    .sb-link .sb-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 13px;
        flex-shrink: 0;
        background: transparent;
        transition: background 0.18s ease, color 0.18s ease;
    }

    /* hover */
    .sb-link:hover {
        background: var(--sb-surface2);
        color: var(--sb-text-sec);
        box-shadow: var(--sb-shadow);
    }
    .sb-link:hover .sb-icon {
        background: var(--sb-border);
        color: var(--sb-text);
    }

    /* active — TESDA blue accent */
    .sb-link.active {
        background: var(--sb-accent-bg);
        color: var(--sb-accent);
        font-weight: 600;
        box-shadow: 0 1px 4px rgba(0,87,184,0.12);
    }
    .dark .sb-link.active {
        color: var(--sb-accent-dark);
        background: var(--sb-accent-bg);
        box-shadow: 0 1px 6px rgba(0,87,184,0.20);
    }
    .sb-link.active .sb-icon {
        background: rgba(0,87,184,0.12);
        color: var(--sb-accent);
    }
    .dark .sb-link.active .sb-icon {
        background: rgba(0,87,184,0.22);
        color: var(--sb-accent-dark);
    }

    /* active left bar — TESDA blue */
    .sb-link.active::before {
        content: '';
        position: absolute;
        left: 0; top: 20%; bottom: 20%;
        width: 3px;
        border-radius: 0 3px 3px 0;
        background: var(--sb-accent);
    }
    .dark .sb-link.active::before {
        background: var(--sb-accent-dark);
    }

    /* ── Divider ── */
    .sb-divider {
        height: 1px;
        background: var(--sb-border);
        margin: 12px 16px;
    }
</style>

<nav class="space-y-1 p-3">

    <span class="sb-section-label">Super Admin</span>

    <a href="{{ route('superadmin.dashboard') }}"
       class="sb-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-crown"></i></span>
        Dashboard
    </a>

    <div class="sb-divider"></div>
    <span class="sb-section-label">Tenant Management</span>

    <a href="{{ route('superadmin.tenants.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.tenants.index') || (request()->routeIs('superadmin.tenants*') && !request('filter')) ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-layer-group"></i></span>
        All Tenants
    </a>

    <a href="{{ route('superadmin.tenants.index') }}?filter=approved"
       class="sb-link {{ request()->routeIs('superadmin.tenants.index') && request('filter') === 'approved' ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-check-circle"></i></span>
        Approved
    </a>

    <a href="{{ route('superadmin.tenants.index') }}?filter=pending"
       class="sb-link {{ request()->routeIs('superadmin.tenants.index') && request('filter') === 'pending' ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-hourglass-half"></i></span>
        Pending
    </a>

    <a href="{{ route('superadmin.tenants.index') }}?filter=rejected"
       class="sb-link {{ request()->routeIs('superadmin.tenants.index') && request('filter') === 'rejected' ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-times-circle"></i></span>
        Rejected
    </a>

    <a href="{{ route('superadmin.tenants.create') }}"
       class="sb-link {{ request()->routeIs('superadmin.tenants.create') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-user-plus"></i></span>
        Register New Tenant
    </a>

    <div class="sb-divider"></div>
    <span class="sb-section-label">Subscription Management</span>

    <a href="{{ route('superadmin.plans.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.plans*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-tags"></i></span>
        Plans & Discounts
    </a>

    <a href="{{ route('superadmin.renewals.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.renewals*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-sync-alt"></i></span>
        Renewal Requests
    </a>

    <div class="sb-divider"></div>
    <span class="sb-section-label">Analytics & Reports</span>

    <a href="{{ route('superadmin.analytics.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.analytics') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-chart-line"></i></span>
        Platform Analytics
    </a>

    <a href="{{ route('superadmin.reports.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.reports*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-file-export"></i></span>
        Export Reports
    </a>
    <a href="{{ route('superadmin.activity-logs.index') }}"
        class="sb-link {{ request()->routeIs('superadmin.activity-logs*') ? 'active' : '' }}">
            <span class="sb-icon"><i class="fas fa-shield-alt"></i></span>
            Activity Logs
    </a>
    
    <div class="sb-divider"></div>
    <span class="sb-section-label">Infrastructure</span>

    <a href="{{ route('superadmin.monitoring.index') }}"
       class="sb-link {{ request()->routeIs('superadmin.monitoring*') ? 'active' : '' }}">
        <span class="sb-icon"><i class="fas fa-server"></i></span>
        DB Storage & Bandwidth
    </a>
<!-- 
    <div class="sb-divider"></div>
    <span class="sb-section-label">Configuration</span>

    <a href="#"
       class="sb-link">
        <span class="sb-icon"><i class="fas fa-sliders-h"></i></span>
        System Settings
    </a>

    <a href="#"
       class="sb-link">
        <span class="sb-icon"><i class="fas fa-cog"></i></span>
        Site Configuration
    </a>

    <a href="#"
       class="sb-link">
        <span class="sb-icon"><i class="fas fa-users-cog"></i></span>
        User Management
    </a>

    <div class="sb-divider"></div>
    <span class="sb-section-label">Support & Tools</span>

    <a href="#"
       class="sb-link">
        <span class="sb-icon"><i class="fas fa-file-alt"></i></span>
        System Logs
    </a>

    <a href="#"
       class="sb-link">
        <span class="sb-icon"><i class="fas fa-envelope"></i></span>
        Email Templates
    </a> -->

</nav>