{{-- 2. Pricing & Duration --}}
<div class="pm-card">
    <div class="pm-card-title"><i class="fas fa-tag"></i> Pricing &amp; Duration</div>

    <div class="form-grid-2">
        <div class="fi">
            <label>Price (₱) *</label>
            <input type="number" name="price" id="inp-price"
                   value="{{ old('price', $plan->price ?? 0) }}"
                   min="0" step="0.01" required oninput="updatePreview()">
            <span class="hint">Set to 0 for a free plan.</span>
        </div>
        <div class="fi">
            <label>Duration (days) *</label>
            <input type="number" name="duration_days" id="inp-duration"
                   value="{{ old('duration_days', $plan->duration_days ?? 30) }}"
                   min="1" required oninput="updatePreview()">
            <span class="hint">30 = 1 month · 180 = 6 months · 365 = 1 year</span>
        </div>
    </div>

    <div class="price-preview" id="price-preview">
        <i class="fas fa-receipt" style="color:var(--sa-accent);"></i>
        <div>
            <div class="pp-val" id="preview-price">₱0</div>
            <div class="pp-dur" id="preview-dur">for 30 days</div>
        </div>
        <div style="flex:1;"></div>
        <div id="preview-ppd" style="font-size:12px;color:var(--sa-muted);"></div>
    </div>
</div>
