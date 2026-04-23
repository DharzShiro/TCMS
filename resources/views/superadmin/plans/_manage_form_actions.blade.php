{{-- Actions --}}
<div class="pm-card" style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
    <button type="submit" class="btn btn-primary btn-lg">
        <i class="fas fa-{{ isset($plan) ? 'save' : 'plus' }}"></i>
        {{ isset($plan) ? 'Save Changes' : 'Create Plan' }}
    </button>
    <a href="{{ route('superadmin.plans.index') }}" class="btn btn-outline btn-lg">
        Cancel
    </a>
    @if(isset($plan))
        <div style="flex:1;"></div>
        <span style="font-size:12px;color:var(--sa-muted);">
            Last updated: {{ $plan->updated_at->format('M d, Y h:i A') }}
        </span>
    @endif
</div>
