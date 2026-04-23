@php
    $statusClass   = match($d->status_label) {
        'Active'    => 'sb-success',
        'Scheduled' => 'sb-warning',
        default     => 'sb-danger',
    };
    $planSlugsJson = json_encode($d->plan_slugs ?? []);
    $tenantIdsJson = json_encode($d->tenant_ids ?? []);
    $tenantNames   = [];
    if (!empty($d->tenant_ids)) {
        $tenantNames = $tenants->whereIn('id', $d->tenant_ids)->pluck('name', 'id')->toArray();
    }
@endphp
<tr>
    <td>
        @if($d->is_automatic)
            <span class="type-badge type-auto">🗓 Auto</span>
        @else
            <span class="type-badge type-code">🔑 Code</span>
        @endif
    </td>
    <td>
        @if($d->is_automatic)
            <span class="font-semibold">{{ $d->label }}</span>
            <div class="text-xs mt-0.5" style="color:var(--sa-muted);">No code needed</div>
        @else
            <code class="px-2 py-1 rounded text-xs font-bold"
                  style="background:rgba(0,48,135,.08);color:var(--sa-accent);">
                {{ $d->code }}
            </code>
            <div class="text-xs mt-0.5" style="color:var(--sa-muted);">{{ $d->label }}</div>
        @endif
    </td>
    <td>
        <span class="font-bold text-base" style="color:var(--sa-success);">
            {{ $d->formatted_value }}
            @if($d->type === 'percentage')
                <span class="text-xs font-normal" style="color:var(--sa-muted);">off</span>
            @endif
        </span>
        <div class="text-xs" style="color:var(--sa-muted);">{{ ucfirst($d->type) }}</div>
    </td>
    <td>
        @if(empty($d->plan_slugs))
            <span class="plan-pill pill-all">All plans</span>
        @else
            @foreach($d->plan_slugs as $slug)
                <span class="plan-pill pill-{{ $slug }}">{{ ucfirst($slug) }}</span>
            @endforeach
        @endif
    </td>
    <td>
        @if($d->is_automatic)
            <span style="font-size:11px;color:var(--sa-muted);">—</span>
        @elseif(empty($d->tenant_ids))
            <span class="tenant-pill-all"><i class="fas fa-users" style="font-size:9px;"></i> Any</span>
        @else
            @foreach(array_slice($tenantNames, 0, 2) as $tName)
                <span class="tenant-pill" title="{{ $tName }}">{{ $tName }}</span>
            @endforeach
            @if(count($tenantNames) > 2)
                <span class="tenant-pill" style="background:rgba(90,122,170,.10);color:var(--sa-muted);">
                    +{{ count($tenantNames) - 2 }} more
                </span>
            @endif
        @endif
    </td>
    <td class="text-xs" style="color:var(--sa-muted);">
        @if($d->valid_from || $d->valid_until)
            {{ $d->valid_from?->format('M d, Y') ?? '—' }} → {{ $d->valid_until?->format('M d, Y') ?? '—' }}
        @else
            No limit
        @endif
    </td>
    <td>
        <span class="status-badge {{ $statusClass }}">{{ $d->status_label }}</span>
    </td>
    <td>
        <div class="flex items-center gap-1">
            <button onclick="openEditDiscount({{ $d->id }})" class="btn btn-outline btn-sm">
                <i class="fas fa-pencil-alt"></i>
            </button>
            <form action="{{ route('superadmin.plans.discounts.destroy', $d) }}" method="POST"
                  onsubmit="return confirm('Delete this discount?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
        <div id="disc-data-{{ $d->id }}" class="hidden"
             data-id="{{ $d->id }}"
             data-is-automatic="{{ $d->is_automatic ? '1' : '0' }}"
             data-code="{{ $d->code }}"
             data-label="{{ $d->label }}"
             data-type="{{ $d->type }}"
             data-value="{{ $d->value }}"
             data-plan-slugs="{{ $planSlugsJson }}"
             data-tenant-ids="{{ $tenantIdsJson }}"
             data-valid-from="{{ $d->valid_from?->format('Y-m-d') ?? '' }}"
             data-valid-until="{{ $d->valid_until?->format('Y-m-d') ?? '' }}"
             data-active="{{ $d->is_active ? '1' : '0' }}"
             data-update-url="{{ route('superadmin.plans.discounts.update', $d) }}">
        </div>
    </td>
</tr>
