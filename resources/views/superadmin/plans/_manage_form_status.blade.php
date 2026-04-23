{{-- 7. Status --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-power-off"></i> Plan Status</div>

    <div class="active-row">
        <label class="switch">
            <input type="checkbox" name="is_active" value="1" id="sw-active"
                   {{ old('is_active', $plan->is_active ?? true) ? 'checked' : '' }}>
            <span class="switch-track"></span>
        </label>
        <div>
            <div style="font-size:14px;font-weight:700;color:var(--sa-text);" id="active-label">
                {{ old('is_active', $plan->is_active ?? true) ? 'Active' : 'Inactive' }}
            </div>
            <div class="hint">Inactive plans are hidden from the tenant upgrade page.</div>
        </div>
    </div>
</div>
