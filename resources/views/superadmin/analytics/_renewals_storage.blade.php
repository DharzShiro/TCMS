<div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-6">

    {{-- Renewal Requests --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-sync-alt" style="color:var(--sa-warning);"></i>
            Renewal Requests
            <a href="{{ route('superadmin.renewals.index') }}"
               class="ml-auto text-xs font-normal"
               style="color:var(--sa-accent);">View all →</a>
        </div>

        {{-- Summary pills --}}
        <div class="flex flex-wrap gap-3 mb-5">
            <div class="agg-pill">
                <span class="agg-pill-val" style="color:var(--sa-warning);">{{ $renewalStats['pending'] }}</span>
                <span class="agg-pill-lbl">Pending</span>
            </div>
            <div class="agg-pill">
                <span class="agg-pill-val" style="color:var(--sa-success);">{{ $renewalStats['approved'] }}</span>
                <span class="agg-pill-lbl">Approved</span>
            </div>
            <div class="agg-pill">
                <span class="agg-pill-val" style="color:var(--sa-danger);">{{ $renewalStats['rejected'] }}</span>
                <span class="agg-pill-lbl">Rejected</span>
            </div>
            <div class="agg-pill">
                <span class="agg-pill-val" style="color:var(--sa-muted);">{{ $renewalStats['cancelled_by_upgrade'] }}</span>
                <span class="agg-pill-lbl">Cancelled</span>
            </div>
        </div>

        {{-- Pending list --}}
        @if($pendingRenewals->isEmpty())
            <div class="flex flex-col items-center justify-center py-6 text-center">
                <i class="fas fa-check-circle text-2xl mb-2" style="color:var(--sa-success);opacity:.5;"></i>
                <p class="text-sm" style="color:var(--sa-muted);">No pending renewal requests.</p>
            </div>
        @else
            @foreach($pendingRenewals as $rr)
            <div class="renewal-row">
                <div>
                    <span class="font-semibold" style="color:var(--sa-text);">{{ $rr->tenant->name ?? '—' }}</span>
                    <span class="text-xs ml-2" style="color:var(--sa-muted);">
                        {{ ucfirst($rr->plan_slug) }} · {{ $rr->duration_days }}d · ₱{{ number_format($rr->final_price, 2) }}
                    </span>
                </div>
                <a href="{{ route('superadmin.renewals.index') }}"
                   class="text-xs px-3 py-1 rounded-lg font-semibold"
                   style="background:var(--sa-accent);color:#fff;">Review</a>
            </div>
            @endforeach
        @endif
    </div>

    {{-- DB & File Storage --}}
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-database" style="color:var(--sa-accent);"></i>
            Database &amp; File Storage
            <a href="{{ route('superadmin.monitoring.index') }}"
               class="ml-auto text-xs font-normal"
               style="color:var(--sa-accent);">Full report →</a>
        </div>

        {{-- Storage totals --}}
        <div class="flex flex-wrap gap-3 mb-5">
            <div class="agg-pill">
                <span class="agg-pill-val">{{ \App\Models\TenantUsageStat::formatBytes($storageAggregates['db_bytes']) }}</span>
                <span class="agg-pill-lbl"><i class="fas fa-database mr-1"></i>DB Total</span>
            </div>
            <div class="agg-pill">
                <span class="agg-pill-val">{{ \App\Models\TenantUsageStat::formatBytes($storageAggregates['file_bytes']) }}</span>
                <span class="agg-pill-lbl"><i class="fas fa-folder mr-1"></i>Files Total</span>
            </div>
            <div class="agg-pill">
                <span class="agg-pill-val" style="color:var(--sa-primary);">
                    {{ \App\Models\TenantUsageStat::formatBytes($storageAggregates['total_bytes']) }}
                </span>
                <span class="agg-pill-lbl"><i class="fas fa-hdd mr-1"></i>Combined</span>
            </div>
        </div>

        {{-- Top consumers --}}
        <p class="text-xs font-semibold mb-3" style="color:var(--sa-muted);text-transform:uppercase;letter-spacing:.5px;">
            Top consumers
        </p>
        @forelse($topDbStorage as $stat)
            @php $pct = $maxDbBytes > 0 ? ($stat->db_size_bytes / $maxDbBytes) * 100 : 0; @endphp
            <div class="storage-bar-row">
                <div class="storage-bar-label">{{ $stat->tenant->name ?? '—' }}</div>
                <div class="storage-bar-track">
                    <div class="storage-bar-fill" style="width:{{ max($pct, 2) }}%;">
                        @if($stat->db_size_bytes > 0)
                            <span class="storage-bar-val">{{ $stat->formatted_db_size }}</span>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-sm" style="color:var(--sa-muted);">No storage data yet.</p>
        @endforelse
    </div>

</div>
