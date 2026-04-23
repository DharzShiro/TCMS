<div class="section-card mb-6">
    <div class="section-title">
        <i class="fas fa-layer-group" style="color:var(--sa-gold);"></i>
        Subscription Plans
        <span class="ml-auto text-xs font-normal" style="color:var(--sa-muted);">
            {{ $allPlans->count() }} defined · {{ $allPlans->where('is_active', true)->count() }} active
        </span>
        <a href="{{ route('superadmin.plans.index') }}"
           class="text-xs font-semibold ml-3"
           style="color:var(--sa-accent);">Manage →</a>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @foreach($allPlans as $plan)
        @php $tenantCount = $tenantCountByPlan[$plan->slug] ?? 0; @endphp
        <div class="plan-card {{ $plan->is_active ? 'active' : '' }}" style="{{ !$plan->is_active ? 'opacity:.65;' : '' }}">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-2">
                <span class="text-base font-bold" style="color:var(--sa-text);">
                    {{ $plan->icon }} {{ $plan->name }}
                </span>
                <span class="text-xs px-2 py-0.5 rounded-full font-semibold"
                      style="{{ $plan->is_active
                            ? 'background:rgba(22,163,74,.10);color:var(--sa-success);'
                            : 'background:rgba(90,122,170,.10);color:var(--sa-muted);' }}">
                    {{ $plan->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>

            {{-- Price + duration --}}
            <div class="text-2xl font-bold mb-1" style="color:var(--sa-primary);">
                {{ $plan->formatted_price }}
            </div>
            <p class="text-xs mb-3" style="color:var(--sa-muted);">{{ $plan->duration_label }}</p>

            {{-- Limits --}}
            <div class="text-xs mb-3" style="color:var(--sa-muted);">
                <span>Trainees: <strong style="color:var(--sa-text);">{{ $plan->max_trainees ?? '∞' }}</strong></span>
                <span class="mx-1">·</span>
                <span>Trainers: <strong style="color:var(--sa-text);">{{ $plan->max_trainers ?? '∞' }}</strong></span>
                @if($plan->max_courses)
                <span class="mx-1">·</span>
                <span>Courses: <strong style="color:var(--sa-text);">{{ $plan->max_courses }}</strong></span>
                @endif
            </div>

            {{-- Feature tags --}}
            <div class="flex flex-wrap gap-1 mb-4">
                @if($plan->has_assessments)
                    <span class="feat-tag" style="background:rgba(0,87,184,.08);color:var(--sa-accent);">Assessments</span>
                @endif
                @if($plan->has_certificates)
                    <span class="feat-tag" style="background:rgba(245,197,24,.12);color:#a07800;">Certificates</span>
                @endif
                @if($plan->has_branding)
                    <span class="feat-tag" style="background:rgba(22,163,74,.08);color:var(--sa-success);">Branding</span>
                @endif
                @if($plan->has_custom_reports)
                    <span class="feat-tag" style="background:rgba(206,17,38,.08);color:var(--sa-danger);">Custom Reports</span>
                @endif
                @if($plan->has_trainers)
                    <span class="feat-tag" style="background:rgba(90,122,170,.10);color:var(--sa-muted);">Trainers</span>
                @endif
            </div>

            {{-- Tenant count footer --}}
            <div class="pt-3" style="border-top:1.5px solid var(--sa-border);">
                <span class="text-lg font-bold" style="color:var(--sa-primary);">{{ $tenantCount }}</span>
                <span class="text-xs ml-1" style="color:var(--sa-muted);">
                    tenant{{ $tenantCount !== 1 ? 's' : '' }} on this plan
                </span>
            </div>

        </div>
        @endforeach
    </div>
</div>
