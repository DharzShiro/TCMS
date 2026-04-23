<div class="section-card">
    <div class="section-title">
        <i class="fas fa-table" style="color:var(--sa-accent);"></i>
        Tenant Activity Breakdown
        <span class="ml-auto text-xs font-normal" style="color:var(--sa-muted);">{{ count($tenantStats) }} approved tenant(s)</span>
    </div>

    @if(count($tenantStats) > 0)
        <div class="overflow-x-auto">
            <table class="tenant-table">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Plan</th>
                        <th>Trainers</th>
                        <th>Trainees</th>
                        <th>Courses</th>
                        <th>Enrollments</th>
                        <th>Assessments</th>
                        <th>Certificates</th>
                        <th>Expires</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenantStats as $row)
                        @php
                            $t = $row['tenant'];
                            $expired = $t->expires_at && $t->expires_at->isPast();
                            $planBadgeClass = match($t->subscription) {
                                'basic'    => 'plan-basic',
                                'standard' => 'plan-standard',
                                'premium'  => 'plan-premium',
                                default    => 'plan-custom',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="font-semibold" style="color:var(--sa-text);">{{ $t->name }}</div>
                                <div class="text-xs" style="color:var(--sa-muted);">{{ $t->subdomain }}.tcm.com</div>
                            </td>
                            <td>
                                <span class="plan-badge {{ $planBadgeClass }}">
                                    {{ ucfirst($t->subscription) }}
                                </span>
                            </td>
                            <td class="text-center font-semibold">{{ $row['trainers'] }}</td>
                            <td class="text-center font-semibold">{{ $row['trainees'] }}</td>
                            <td class="text-center font-semibold">{{ $row['courses'] }}</td>
                            <td class="text-center font-semibold">{{ $row['enrollments'] }}</td>
                            <td class="text-center font-semibold">{{ $row['assessments'] }}</td>
                            <td class="text-center font-semibold">{{ $row['certificates'] }}</td>
                            <td class="text-xs" style="color:{{ $expired ? 'var(--sa-danger)' : 'var(--sa-muted)' }};">
                                {{ $t->expires_at ? $t->expires_at->format('M d, Y') : '—' }}
                                @if($expired)
                                    <span class="ml-1 font-bold">(expired)</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('superadmin.tenants.show', $t) }}"
                                   class="text-xs px-3 py-1 rounded-lg font-semibold transition"
                                   style="background:rgba(0,87,184,.10);color:var(--sa-accent);">
                                    View
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="flex flex-col items-center justify-center py-10 text-center">
            <i class="fas fa-inbox text-4xl mb-3" style="color:var(--sa-muted);opacity:.4;"></i>
            <p style="color:var(--sa-muted);">No approved tenants yet.</p>
        </div>
    @endif
</div>
