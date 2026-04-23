@extends('layouts.app')
@section('title', 'Plan & Discount Management')

@section('content')

@include('superadmin.plans._index_styles')

<div class="space-y-6">

    {{-- ── Page Header ── --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-3xl font-bold" style="color:var(--sa-primary);">
                <i class="fas fa-layer-group mr-2" style="color:var(--sa-accent);"></i> Plan & Discount Management
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-muted);">
                Edit subscription plan features, limits, and availability — or manage discounts and promo codes
            </p>
        </div>
        <a href="{{ route('superadmin.dashboard') }}" class="btn btn-outline">
            <i class="fas fa-arrow-left"></i> Dashboard
        </a>
    </div>

    {{-- ── Flash Messages ── --}}
    @if(session('success'))
        <div class="rounded-xl border-2 p-4" style="background:rgba(22,163,74,.05);border-color:var(--sa-success);">
            <div style="color:var(--sa-success);" class="font-semibold flex items-center gap-3">
                <i class="fas fa-check-circle"></i> {{ session('success') }}
            </div>
        </div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border-2 p-4" style="background:rgba(206,17,38,.05);border-color:var(--sa-danger);">
            <div style="color:var(--sa-danger);" class="font-semibold flex items-start gap-3">
                <i class="fas fa-exclamation-circle mt-0.5"></i>
                <div>@foreach($errors->all() as $e)<p>{{ $e }}</p>@endforeach</div>
            </div>
        </div>
    @endif

    {{-- ── Tab Bar ── --}}
    <div class="tab-bar">
        <button class="tab-btn active" id="tab-plans-btn" onclick="switchTab('plans')">
            <i class="fas fa-layer-group"></i> Subscription Plans
        </button>
        <button class="tab-btn" id="tab-discounts-btn" onclick="switchTab('discounts')">
            <i class="fas fa-percent"></i> Discounts & Promos
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: Plans                                                            --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-plans">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <p class="text-sm font-semibold" style="color:var(--sa-text);">
                    These are the plans tenants see on their upgrade page.
                    Edit features, pricing, limits, and availability.
                </p>
            </div>
            <a href="{{ route('superadmin.plans.manage.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Plan
            </a>
        </div>

        {{-- Stats --}}
        <div class="flex flex-wrap gap-3 mb-6">
            <div class="stat-pill">
                <span class="stat-pill-val">{{ $plans->where('is_active', true)->count() }}</span>
                <span class="stat-pill-lbl">Active Plans</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-val" style="color:var(--sa-muted);">{{ $plans->where('is_active', false)->count() }}</span>
                <span class="stat-pill-lbl">Inactive Plans</span>
            </div>
            <div class="stat-pill">
                <span class="stat-pill-val">{{ $plans->count() }}</span>
                <span class="stat-pill-lbl">Total Plans</span>
            </div>
        </div>

        @if($plans->count() > 0)
            <div class="plan-cards-grid">
                @foreach($plans as $plan)
                    @include('superadmin.plans._plan_card')
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border-2 p-14 text-center" style="background:var(--sa-bg);border-color:var(--sa-border);">
                <i class="fas fa-layer-group text-5xl mb-4" style="color:var(--sa-muted);opacity:.3;"></i>
                <p style="color:var(--sa-muted);" class="mb-3">No plans yet. Create your first plan.</p>
                <a href="{{ route('superadmin.plans.manage.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Plan
                </a>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB: Discounts                                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div id="tab-discounts" style="display:none;">

        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div class="flex gap-3">
                <div class="stat-pill">
                    <span class="stat-pill-val" style="color:var(--sa-success);">{{ $discounts->where('is_active', true)->count() }}</span>
                    <span class="stat-pill-lbl">Active</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-pill-val">{{ $discounts->where('is_automatic', true)->count() }}</span>
                    <span class="stat-pill-lbl">Auto</span>
                </div>
                <div class="stat-pill">
                    <span class="stat-pill-val">{{ $discounts->where('is_automatic', false)->count() }}</span>
                    <span class="stat-pill-lbl">Codes</span>
                </div>
            </div>
            <button onclick="openModal('modal-new-discount')" class="btn btn-gold">
                <i class="fas fa-percent"></i> New Discount
            </button>
        </div>

        <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border);">
            @if($discounts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="disc-table">
                        <thead>
                            <tr>
                                <th>Type</th>
                                <th>Code / Label</th>
                                <th>Discount</th>
                                <th>Applies To</th>
                                <th>Tenants</th>
                                <th>Valid Period</th>
                                <th>Status</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($discounts as $d)
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
                                                @php
                                                    $matchedPlan = $plans->firstWhere('slug', $slug);
                                                    $pillLabel   = $matchedPlan ? $matchedPlan->name : ucfirst($slug);
                                                    $isCustom    = !in_array($slug, ['basic','standard','premium']);
                                                @endphp
                                                <span class="plan-pill {{ $isCustom ? 'pill-custom' : 'pill-'.$slug }}">
                                                    {{ $pillLabel }}
                                                </span>
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
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-14 text-center">
                    <i class="fas fa-percent text-5xl mb-4" style="color:var(--sa-muted);opacity:.3;"></i>
                    <p style="color:var(--sa-muted);" class="mb-3">No discounts yet.</p>
                    <button onclick="openModal('modal-new-discount')" class="btn btn-gold">
                        <i class="fas fa-plus"></i> Create First Discount
                    </button>
                </div>
            @endif
        </div>
    </div>

</div>{{-- end space-y-6 --}}

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: New Discount                                                       --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-new-discount">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-percent mr-2" style="color:var(--sa-accent);"></i> New Discount</span>
            <button onclick="closeModal('modal-new-discount')" style="background:none;border:none;cursor:pointer;color:var(--sa-muted);font-size:18px;"><i class="fas fa-times"></i></button>
        </div>
        <form action="{{ route('superadmin.plans.discounts.store') }}" method="POST">
            @csrf
            <div class="modal-body space-y-4">
                @include('superadmin.plans._discount_fields', ['tenants' => $tenants, 'plans' => $plans])
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modal-new-discount')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-plus"></i> Create</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- MODAL: Edit Discount                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-edit-discount">
    <div class="modal-box">
        <div class="modal-header">
            <span class="modal-title"><i class="fas fa-pencil-alt mr-2" style="color:var(--sa-accent);"></i> Edit Discount</span>
            <button onclick="closeModal('modal-edit-discount')" style="background:none;border:none;cursor:pointer;color:var(--sa-muted);font-size:18px;"><i class="fas fa-times"></i></button>
        </div>
        <form id="edit-discount-form" method="POST">
            @csrf @method('PATCH')
            <div class="modal-body space-y-4">
                @include('superadmin.plans._discount_fields', ['isEdit' => true, 'tenants' => $tenants, 'plans' => $plans])
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modal-edit-discount')" class="btn btn-outline">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('tab-plans').style.display     = tab === 'plans'     ? '' : 'none';
    document.getElementById('tab-discounts').style.display = tab === 'discounts' ? '' : 'none';
    document.getElementById('tab-plans-btn').classList.toggle('active',     tab === 'plans');
    document.getElementById('tab-discounts-btn').classList.toggle('active', tab === 'discounts');
    sessionStorage.setItem('planTab', tab);
}

document.addEventListener('DOMContentLoaded', function () {
    const saved = sessionStorage.getItem('planTab');
    if (saved) switchTab(saved);
});

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function (e) {
        if (e.target === this) closeModal(this.id);
    });
});

function openEditDiscount(id) {
    const d = document.getElementById('disc-data-' + id).dataset;
    document.getElementById('edit-discount-form').action = d.updateUrl;

    const isAuto = d.isAutomatic === '1';
    document.getElementById('ed-radio-automatic').checked = isAuto;
    document.getElementById('ed-radio-code').checked      = !isAuto;
    document.getElementById('ed-radio-automatic').dispatchEvent(new Event('change'));

    document.getElementById('ed-code').value        = d.code;
    document.getElementById('ed-label').value       = d.label;
    document.getElementById('ed-type').value        = d.type;
    document.getElementById('ed-value').value       = d.value;
    document.getElementById('ed-valid-from').value  = d.validFrom;
    document.getElementById('ed-valid-until').value = d.validUntil;
    document.getElementById('ed-active').checked    = d.active === '1';

    let planSlugs = [];
    try { planSlugs = JSON.parse(d.planSlugs || '[]'); } catch (e) {}

    // Dynamic: works for all plans including custom ones
    document.querySelectorAll('input[name="plan_slugs[]"]').forEach(cb => {
        if (!cb.id.startsWith('ed-plan-')) return;
        const slug = cb.value;
        cb.checked = planSlugs.includes(slug);
        const onch = cb.getAttribute('onchange') || '';
        const m    = onch.match(/syncPlanRow\('[^']*','[^']*','([^']*)','([^']*)'\)/);
        const accent    = m ? m[1] : '#9333ea';
        const colorBase = m ? m[2] : 'rgba(147,51,234';
        syncPlanRow('ed-', slug, accent, colorBase);
    });

    let tenantIds = [];
    try { tenantIds = JSON.parse(d.tenantIds || '[]'); } catch (e) {}
    const searchEl = document.getElementById('ed-tenant-search');
    if (searchEl) { searchEl.value = ''; filterTenants('ed-'); }
    document.querySelectorAll('#ed-tenant-list input[type="checkbox"]').forEach(cb => {
        cb.checked = tenantIds.includes(cb.value);
        syncTenantRow('ed-', cb.value);
    });

    openModal('modal-edit-discount');
}

@if($errors->any())
    document.addEventListener('DOMContentLoaded', function () {
        @if(session('_old_input.label') || session('_old_input.code'))
            switchTab('discounts');
        @endif
    });
@endif
</script>

@endsection