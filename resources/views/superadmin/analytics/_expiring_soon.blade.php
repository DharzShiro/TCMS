<div class="expiry-strip mb-6">
    <div class="flex items-center gap-2 mb-3">
        <i class="fas fa-exclamation-triangle" style="color:var(--sa-warning);"></i>
        <span class="font-bold text-sm" style="color:var(--sa-warning);">
            {{ $expiringSoon->count() }} tenant{{ $expiringSoon->count() > 1 ? 's' : '' }} expiring within 7 days
        </span>
    </div>
    @foreach($expiringSoon as $t)
        <div class="expiry-row">
            <div>
                <span class="font-semibold" style="color:var(--sa-text);">{{ $t->name }}</span>
                <span class="text-xs ml-2" style="color:var(--sa-muted);">{{ $t->subdomain }}.tcm.com</span>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-xs font-semibold" style="color:var(--sa-warning);">
                    expires {{ $t->expires_at->format('M d, Y') }}
                </span>
                <a href="{{ route('superadmin.tenants.show', $t) }}"
                   class="text-xs px-3 py-1 rounded-lg font-semibold transition"
                   style="background:var(--sa-accent);color:#fff;">
                    Manage
                </a>
            </div>
        </div>
    @endforeach
</div>
