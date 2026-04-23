{{-- Platform-Wide Totals --}}
<div class="section-card mb-6">
    <div class="section-title">
        <i class="fas fa-globe" style="color:var(--sa-accent);"></i>
        Platform-Wide Totals
        <span class="ml-auto text-xs font-normal" style="color:var(--sa-muted);">across all approved tenants</span>
    </div>
    <div class="flex flex-wrap gap-3">
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['trainees']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-user-graduate mr-1"></i>Trainees</span>
        </div>
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['trainers']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-chalkboard-teacher mr-1"></i>Trainers</span>
        </div>
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['courses']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-book mr-1"></i>Courses</span>
        </div>
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['enrollments']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-clipboard-list mr-1"></i>Enrollments</span>
        </div>
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['assessments']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-clipboard-check mr-1"></i>Assessments</span>
        </div>
        <div class="agg-pill">
            <span class="agg-pill-val">{{ number_format($platformTotals['certificates']) }}</span>
            <span class="agg-pill-lbl"><i class="fas fa-certificate mr-1"></i>Certificates</span>
        </div>
    </div>
</div>

{{-- Charts Row: Monthly Registrations + Subscription Donut --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">

    {{-- Monthly Registrations Bar Chart --}}
    <div class="section-card lg:col-span-2">
        <div class="section-title">
            <i class="fas fa-calendar-alt" style="color:var(--sa-accent);"></i>
            Monthly Registrations
        </div>

        @php
            $regCounts = array_column($monthlyRegistrations, 'count');
            $maxReg = count($regCounts) ? max(max($regCounts), 1) : 1;
        @endphp

        <div class="bar-group">
            @foreach($monthlyRegistrations as $month)
                @php $pct = ($month['count'] / $maxReg) * 100; @endphp
                <div class="bar-row">
                    <div class="bar-label">{{ $month['label'] }}</div>
                    <div class="bar-track">
                        <div class="bar-fill"
                             style="width:{{ max($pct, 3) }}%; background: linear-gradient(90deg, var(--sa-accent), var(--sa-primary));">
                            @if($month['count'] > 0)
                                <span class="bar-val">{{ $month['count'] }}</span>
                            @endif
                        </div>
                    </div>
                    @if($month['count'] === 0)
                        <span style="font-size:11px;color:var(--sa-muted);">0</span>
                    @endif
                </div>
            @endforeach
        </div>
    </div>

    {{-- Subscription Donut — dynamic, covers all plans --}}
    <div class="section-card flex flex-col">
        <div class="section-title">
            <i class="fas fa-credit-card" style="color:var(--sa-accent);"></i>
            Subscription Mix
        </div>

        @php
            // Color palette cycles for any number of plans
            $donutPalette = [
                '#7fa8d4', // soft blue  — basic
                '#0057B8', // accent blue — standard
                '#d4a800', // gold        — premium
                '#16a34a', // green
                '#CE1126', // red
                '#7c3aed', // violet
                '#0891b2', // cyan
                '#ea580c', // orange
                '#be185d', // pink
                '#4f7942', // forest
            ];

            $circ     = 2 * M_PI * 50;
            $offset   = -$circ / 4; // start at top (12 o'clock)
            $subTotal = max($approvedTenants, 1);

            // Build segments from ALL active plans + any tenants on unlisted slugs
            $segments = [];
            foreach ($allPlans as $idx => $plan) {
                $count = $tenantCountByPlan[$plan->slug] ?? 0;
                if ($count === 0) continue;
                $segments[] = [
                    'label'  => $plan->name,
                    'icon'   => $plan->icon,
                    'count'  => $count,
                    'color'  => $donutPalette[$idx % count($donutPalette)],
                    'dash'   => $circ * ($count / $subTotal),
                ];
            }

            // Catch tenants whose plan slug isn't in $allPlans (edge case)
            $accountedFor = array_sum(array_column($segments, 'count'));
            $orphans      = $approvedTenants - $accountedFor;
            if ($orphans > 0) {
                $segments[] = [
                    'label' => 'Other',
                    'icon'  => '?',
                    'count' => $orphans,
                    'color' => '#94a3b8',
                    'dash'  => $circ * ($orphans / $subTotal),
                ];
            }
        @endphp

        <div class="flex items-start gap-6 flex-1">
            {{-- SVG donut --}}
            <div class="donut-wrap" style="flex-shrink:0;">
                <svg viewBox="0 0 130 130">
                    <circle cx="65" cy="65" r="50" fill="none" stroke="var(--sa-surface)" stroke-width="18"/>
                    @foreach($segments as $seg)
                        <circle cx="65" cy="65" r="50" fill="none"
                                stroke="{{ $seg['color'] }}"
                                stroke-width="18"
                                stroke-linecap="round"
                                stroke-dasharray="{{ $seg['dash'] }} {{ $circ - $seg['dash'] }}"
                                stroke-dashoffset="{{ $offset }}"/>
                        @php $offset -= $seg['dash']; @endphp
                    @endforeach
                </svg>
                <div class="donut-center">
                    <span class="donut-center-val">{{ $approvedTenants }}</span>
                    <span class="donut-center-lbl">active</span>
                </div>
            </div>

            {{-- Legend --}}
            <div class="flex flex-col gap-2 flex-1" style="min-width:0;">
                @foreach($segments as $seg)
                <div class="legend-item">
                    <div class="legend-dot" style="background:{{ $seg['color'] }};"></div>
                    <div class="flex items-baseline gap-1 min-w-0">
                        <span class="font-semibold truncate" style="color:var(--sa-text);">
                            {{ $seg['icon'] }} {{ $seg['label'] }}
                        </span>
                        <span class="text-xs flex-shrink-0" style="color:var(--sa-muted);">
                            {{ $seg['count'] }}
                            <span style="opacity:.6;">({{ round($seg['count'] / $subTotal * 100) }}%)</span>
                        </span>
                    </div>
                </div>
                @endforeach

                @if($expiredTenants > 0)
                <div class="legend-item mt-1 pt-2" style="border-top:1px solid var(--sa-border);">
                    <div class="legend-dot" style="background:var(--sa-danger);"></div>
                    <div>
                        <span class="font-semibold" style="color:var(--sa-danger);">Expired</span>
                        <span class="ml-2 text-xs" style="color:var(--sa-muted);">{{ $expiredTenants }}</span>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>