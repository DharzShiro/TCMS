<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Instrument+Serif:ital@0;1&display=swap" rel="stylesheet">

<div class="up-header">
    <div class="up-badge">
        <i class="fas fa-rocket"></i> Subscription Plans
    </div>
    <h1 class="up-title">
        Choose the plan that<br><em>fits your center</em>
    </h1>
    <p class="up-subtitle">
        Unlock more features as your training center grows. Upgrade anytime.
    </p>
    <div class="up-current-pill">
        <div class="up-current-dot"></div>
        Currently on <strong style="margin-left:4px; text-transform:capitalize;">{{ $currentPlan }} Plan</strong>
        &nbsp;·&nbsp;
        @if($tenant->expires_at)
            Expires {{ $tenant->expires_at->format('M d, Y') }}
        @else
            No expiry set
        @endif
    </div>
</div>