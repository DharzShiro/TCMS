<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold" style="color:var(--sa-muted);">Total Tenants</span>
            <div class="stat-icon" style="background:rgba(0,87,184,.10);color:var(--sa-accent);">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:var(--sa-primary);">{{ $totalTenants }}</div>
        <p class="text-xs mt-1" style="color:var(--sa-muted);">registered organizations</p>
    </div>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold" style="color:var(--sa-muted);">Approved</span>
            <div class="stat-icon" style="background:rgba(22,163,74,.10);color:var(--sa-success);">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:var(--sa-success);">{{ $approvedTenants }}</div>
        <p class="text-xs mt-1" style="color:var(--sa-muted);">active tenants</p>
    </div>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold" style="color:var(--sa-muted);">Pending</span>
            <div class="stat-icon" style="background:rgba(179,138,0,.10);color:var(--sa-warning);">
                <i class="fas fa-hourglass-half"></i>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:var(--sa-warning);">{{ $pendingTenants }}</div>
        <p class="text-xs mt-1" style="color:var(--sa-muted);">awaiting approval</p>
    </div>

    <div class="stat-card">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold" style="color:var(--sa-muted);">Rejected</span>
            <div class="stat-icon" style="background:rgba(206,17,38,.10);color:var(--sa-danger);">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
        <div class="text-3xl font-bold" style="color:var(--sa-danger);">{{ $rejectedTenants }}</div>
        <p class="text-xs mt-1" style="color:var(--sa-muted);">rejected requests</p>
    </div>

</div>
