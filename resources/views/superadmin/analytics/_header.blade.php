<div class="mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-chart-line mr-2" style="color:var(--sa-accent);"></i>
                Platform Analytics
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-muted);">
                Consolidated insights across all tenants · as of {{ now()->format('M d, Y H:i') }}
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}"
           class="text-sm px-4 py-2 rounded-lg font-semibold transition"
           style="background:rgba(0,48,135,.08);color:var(--sa-accent);">
            <i class="fas fa-arrow-left mr-1"></i> Dashboard
        </a>
    </div>
</div>
