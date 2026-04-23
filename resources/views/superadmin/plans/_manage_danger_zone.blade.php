@if(isset($plan))
    <div class="pm-card danger-zone mt-5">
        <div class="pm-card-title" style="color:var(--sa-danger);">
            <i class="fas fa-exclamation-triangle" style="color:var(--sa-danger);"></i> Danger Zone
        </div>
        <p class="text-sm mb-4" style="color:var(--sa-muted);">
            Deleting this plan is permanent. Tenants currently on
            <strong>{{ $plan->name }}</strong> will keep their <code>subscription</code>
            value in the database, but the plan record itself will no longer exist.
        </p>
        <form action="{{ route('superadmin.plans.manage.destroy', $plan) }}"
              method="POST"
              onsubmit="return confirm('Permanently delete the {{ addslashes($plan->name) }} plan? This cannot be undone.')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-trash"></i> Delete This Plan
            </button>
        </form>
    </div>
@endif
