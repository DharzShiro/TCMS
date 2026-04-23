{{--
    Shared discount form fields.
    Used inside both the New and Edit modals.
    When $isEdit is true, inputs get the `ed-` id prefix so JS can populate them.
    $tenants and $plans must be passed from the parent view.
--}}
@php $e = isset($isEdit) && $isEdit; $p = $e ? 'ed-' : ''; @endphp
@php
    $canonicalColors = [
        'basic'    => ['accent' => '#5a7aaa', 'rgba' => 'rgba(90,122,170'],
        'standard' => ['accent' => '#0057B8', 'rgba' => 'rgba(0,87,184'],
        'premium'  => ['accent' => '#a07800', 'rgba' => 'rgba(161,122,0'],
    ];
    $customAccent = '#9333ea';
    $customRgba   = 'rgba(147,51,234';
    $allPlans     = $plans ?? \App\Models\SubscriptionPlan::orderBy('sort_order')->get();
@endphp

{{-- ── Discount Type Toggle ────────────────────────────────────────────────── --}}
<div style="margin-bottom:16px;">
    <label style="display:block;font-size:11px;font-weight:700;color:var(--sa-muted);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px;">
        Discount Type *
    </label>
    <div style="display:grid;grid-template-columns:1fr 1fr;border-radius:10px;overflow:hidden;border:1.5px solid var(--sa-border);">
        <label id="{{ $p }}lbl-automatic"
               style="display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;cursor:pointer;font-size:13px;font-weight:600;background:var(--sa-surface);color:var(--sa-muted);transition:background .15s,color .15s;user-select:none;">
            <input type="radio" name="is_automatic" value="1" id="{{ $p }}radio-automatic"
                   style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;">
            <span style="font-size:15px;line-height:1;pointer-events:none;">🗓</span> Automatic
        </label>
        <label id="{{ $p }}lbl-code"
               style="display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;cursor:pointer;font-size:13px;font-weight:600;border-left:1.5px solid var(--sa-border);background:var(--sa-surface);color:var(--sa-muted);transition:background .15s,color .15s;user-select:none;">
            <input type="radio" name="is_automatic" value="0" id="{{ $p }}radio-code"
                   style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;"
                   checked>
            <span style="font-size:15px;line-height:1;pointer-events:none;">🔑</span> Promo Code
        </label>
    </div>
    <p id="{{ $p }}hint-automatic" style="display:none;margin:6px 0 0;font-size:12px;color:var(--sa-muted);">
        Shown automatically on plan cards — no code needed by the tenant.
    </p>
    <p id="{{ $p }}hint-code" style="margin:6px 0 0;font-size:12px;color:var(--sa-muted);">
        Tenant must enter this code manually on the upgrade page.
    </p>
</div>

{{-- ── Label & Code ─────────────────────────────────────────────────────────── --}}
<div class="form-row">
    <div class="fi">
        <label>Discount Label *</label>
        <input type="text" name="label" id="{{ $p }}label"
               placeholder="e.g. TESDA Anniversary Promo" required>
    </div>
    <div class="fi" id="{{ $p }}code-field">
        <label>Code (uppercase) *</label>
        <input type="text" name="code" id="{{ $p }}code"
               placeholder="SAVE20"
               style="text-transform:uppercase;"
               oninput="this.value=this.value.toUpperCase()">
    </div>
</div>

{{-- ── Type & Value ─────────────────────────────────────────────────────────── --}}
<div class="form-row">
    <div class="fi">
        <label>Type *</label>
        <select name="type" id="{{ $p }}type" required>
            <option value="percentage">Percentage (%)</option>
            <option value="fixed">Fixed Amount (₱)</option>
        </select>
    </div>
    <div class="fi">
        <label>Value *</label>
        <input type="number" name="value" id="{{ $p }}value"
               min="0.01" step="0.01" placeholder="e.g. 20 or 500" required>
    </div>
</div>

{{-- ── Tenant Restriction (promo codes only) ───────────────────────────────── --}}
<div class="fi" id="{{ $p }}tenant-field" style="margin-bottom:14px;">
    <label style="margin-bottom:6px;">
        Restrict to Tenant(s)
        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--sa-muted);">
            — leave all unchecked to allow any tenant
        </span>
    </label>

    <div style="position:relative;margin-bottom:8px;">
        <span style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--sa-muted);font-size:12px;pointer-events:none;">
            <i class="fas fa-search"></i>
        </span>
        <input type="text"
               id="{{ $p }}tenant-search"
               placeholder="Search tenants…"
               oninput="filterTenants('{{ $p }}')"
               autocomplete="off"
               style="width:100%;padding:7px 10px 7px 30px;border-radius:8px;border:1.5px solid var(--sa-border);
                      background:var(--sa-bg);color:var(--sa-text);font-family:inherit;font-size:13px;
                      outline:none;transition:border-color .15s;">
    </div>

    <div id="{{ $p }}tenant-list"
         style="display:flex;flex-direction:column;gap:5px;max-height:180px;overflow-y:auto;padding-right:2px;">
        @forelse($tenants ?? [] as $tenant)
            <label id="{{ $p }}tenant-row-{{ $tenant->id }}"
                   data-name="{{ strtolower($tenant->name) }}"
                   style="display:flex;align-items:center;gap:10px;padding:8px 12px;border-radius:9px;
                          cursor:pointer;border:1.5px solid var(--sa-border);background:var(--sa-surface);
                          transition:border-color .15s,background .15s;user-select:none;">
                <span id="{{ $p }}tenant-check-{{ $tenant->id }}"
                      style="flex-shrink:0;width:18px;height:18px;border-radius:5px;border:1.5px solid var(--sa-border);
                             background:var(--sa-bg);display:flex;align-items:center;justify-content:center;
                             font-size:11px;font-weight:700;color:transparent;transition:all .15s;line-height:1;">✓</span>
                <input type="checkbox"
                       name="tenant_ids[]"
                       value="{{ $tenant->id }}"
                       id="{{ $p }}tenant-cb-{{ $tenant->id }}"
                       style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;"
                       onchange="syncTenantRow('{{ $p }}', '{{ $tenant->id }}')">
                <span style="font-size:13px;font-weight:600;color:var(--sa-text);flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $tenant->name }}
                </span>
                <span style="font-size:11px;color:var(--sa-muted);flex-shrink:0;">
                    {{ ucfirst($tenant->subscription ?? 'basic') }}
                </span>
            </label>
        @empty
            <p style="font-size:12px;color:var(--sa-muted);padding:8px 0;">No tenants found.</p>
        @endforelse
    </div>

    <p id="{{ $p }}tenant-hint" style="margin:8px 0 0;font-size:11px;color:var(--sa-muted);">
        No tenants selected — promo code works for any tenant.
    </p>
</div>

{{-- ── Plan Restriction ─────────────────────────────────────────────────────── --}}
<div class="fi" style="margin-bottom:14px;">
    <label style="margin-bottom:6px;">
        Restrict to Plan(s)
        <span style="font-weight:400;text-transform:none;font-size:11px;color:var(--sa-muted);">
            — leave all unchecked to apply to all plans
        </span>
    </label>

    <div style="display:flex;flex-direction:column;gap:6px;">
        @foreach($allPlans as $pl)
            @php
                $isCustomPlan = !in_array($pl->slug, ['basic','standard','premium']);
                $accent       = $isCustomPlan ? $customAccent : ($canonicalColors[$pl->slug]['accent'] ?? $customAccent);
                $rgba         = $isCustomPlan ? $customRgba   : ($canonicalColors[$pl->slug]['rgba']   ?? $customRgba);
                $priceLabel   = $pl->price == 0 ? 'Free' : '₱' . number_format($pl->price, 0);
                $durLabel     = $pl->duration_label ?? ($pl->duration_days . ' days');
            @endphp
            <label id="{{ $p }}plan-label-{{ $pl->slug }}"
                   style="display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:9px;
                          cursor:pointer;border:1.5px solid var(--sa-border);background:var(--sa-surface);
                          transition:border-color .15s,background .15s;user-select:none;">
                <span id="{{ $p }}plan-check-{{ $pl->slug }}"
                      style="flex-shrink:0;width:18px;height:18px;border-radius:5px;border:1.5px solid var(--sa-border);
                             background:var(--sa-bg);display:flex;align-items:center;justify-content:center;
                             font-size:11px;font-weight:700;color:transparent;transition:all .15s;line-height:1;">✓</span>
                <input type="checkbox" name="plan_slugs[]" value="{{ $pl->slug }}"
                       id="{{ $p }}plan-{{ $pl->slug }}"
                       style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;"
                       onchange="syncPlanRow('{{ $p }}','{{ $pl->slug }}','{{ $accent }}','{{ $rgba }}')">
                <span style="font-size:15px;line-height:1;">{{ $pl->icon ?? '📦' }}</span>
                <span style="font-size:13px;font-weight:600;color:var(--sa-text);">{{ $pl->name }}</span>
                <span style="font-size:11px;color:var(--sa-muted);margin-left:auto;">
                    {{ $priceLabel }} · {{ $durLabel }}
                </span>
            </label>
        @endforeach
    </div>

    <p id="{{ $p }}plan-hint" style="margin:8px 0 0;font-size:11px;color:var(--sa-muted);">
        No plans selected — discount applies to all plans.
    </p>
</div>

{{-- ── Valid Dates ─────────────────────────────────────────────────────────── --}}
<div class="form-row">
    <div class="fi">
        <label>Valid From</label>
        <input type="date" name="valid_from" id="{{ $p }}valid-from">
    </div>
    <div class="fi">
        <label>Valid Until</label>
        <input type="date" name="valid_until" id="{{ $p }}valid-until">
    </div>
</div>

{{-- ── Active ───────────────────────────────────────────────────────────────── --}}
<div class="fi">
    <label class="check-item" style="width:fit-content;">
        <input type="checkbox" name="is_active" id="{{ $p }}active" value="1"
               {{ !$e ? 'checked' : '' }}>
        Active
    </label>
</div>

<script>
(function () {
    var p = '{{ $p }}';

    /* ── Discount type toggle ─────────────────────────────────────────────── */
    function setToggleStyles() {
        var isAuto  = document.getElementById(p + 'radio-automatic').checked;
        var lblAuto = document.getElementById(p + 'lbl-automatic');
        var lblCode = document.getElementById(p + 'lbl-code');

        var base        = 'display:flex;align-items:center;justify-content:center;gap:7px;padding:10px 14px;cursor:pointer;font-size:13px;user-select:none;transition:background .15s,color .15s;';
        var active      = base + 'font-weight:700;background:#e8f0fb;color:var(--sa-accent);';
        var inactive    = base + 'font-weight:600;background:var(--sa-surface);color:var(--sa-muted);';
        var rightBorder = 'border-left:1.5px solid var(--sa-border);';

        lblAuto.style.cssText = isAuto  ? active            : inactive;
        lblCode.style.cssText = !isAuto ? active + rightBorder : inactive + rightBorder;

        var codeField   = document.getElementById(p + 'code-field');
        var codeInput   = document.getElementById(p + 'code');
        var hintAuto    = document.getElementById(p + 'hint-automatic');
        var hintCode    = document.getElementById(p + 'hint-code');
        var tenantField = document.getElementById(p + 'tenant-field');

        codeField.style.display   = isAuto ? 'none' : '';
        tenantField.style.display = isAuto ? 'none' : '';

        if (codeInput) isAuto ? codeInput.removeAttribute('required') : codeInput.setAttribute('required', 'required');
        hintAuto.style.display = isAuto ? 'block' : 'none';
        hintCode.style.display = isAuto ? 'none'  : 'block';
    }

    document.getElementById(p + 'radio-automatic').addEventListener('change', setToggleStyles);
    document.getElementById(p + 'radio-code').addEventListener('change',      setToggleStyles);

    /* ── Tenant row checkbox sync ─────────────────────────────────────────── */
    window.syncTenantRow = function (prefix, tenantId) {
        var cb    = document.getElementById(prefix + 'tenant-cb-' + tenantId);
        var row   = document.getElementById(prefix + 'tenant-row-' + tenantId);
        var check = document.getElementById(prefix + 'tenant-check-' + tenantId);

        if (!cb || !row || !check) return;

        if (cb.checked) {
            row.style.borderColor   = '#0057B8';
            row.style.background    = 'rgba(0,87,184,.07)';
            check.style.background  = '#0057B8';
            check.style.borderColor = '#0057B8';
            check.style.color       = '#fff';
        } else {
            row.style.borderColor   = 'var(--sa-border)';
            row.style.background    = 'var(--sa-surface)';
            check.style.background  = 'var(--sa-bg)';
            check.style.borderColor = 'var(--sa-border)';
            check.style.color       = 'transparent';
        }
        refreshTenantHint(prefix);
    };

    function refreshTenantHint(prefix) {
        var hint      = document.getElementById(prefix + 'tenant-hint');
        var checkboxes = document.querySelectorAll('#' + prefix + 'tenant-list input[type="checkbox"]:checked');
        var count      = checkboxes.length;
        if (hint) {
            hint.textContent = count
                ? count + ' tenant' + (count > 1 ? 's' : '') + ' selected — promo code restricted to them only.'
                : 'No tenants selected — promo code works for any tenant.';
        }
    }

    /* ── Tenant search filter ─────────────────────────────────────────────── */
    window.filterTenants = function (prefix) {
        var input = document.getElementById(prefix + 'tenant-search');
        if (!input) return;
        var q    = input.value.toLowerCase().trim();
        var rows = document.querySelectorAll('#' + prefix + 'tenant-list label[data-name]');
        rows.forEach(function (row) {
            row.style.display = (!q || row.dataset.name.indexOf(q) !== -1) ? '' : 'none';
        });
    };

    /* ── Plan row checkbox sync ───────────────────────────────────────────── */
    window.syncPlanRow = function (prefix, slug, accent, colorBase) {
        var cb    = document.getElementById(prefix + 'plan-' + slug);
        var row   = document.getElementById(prefix + 'plan-label-' + slug);
        var check = document.getElementById(prefix + 'plan-check-' + slug);
        var hint  = document.getElementById(prefix + 'plan-hint');

        if (!cb || !row || !check) return;

        if (cb.checked) {
            row.style.borderColor   = accent;
            row.style.background    = colorBase + ',.07)';
            check.style.background  = accent;
            check.style.borderColor = accent;
            check.style.color       = '#fff';
        } else {
            row.style.borderColor   = 'var(--sa-border)';
            row.style.background    = 'var(--sa-surface)';
            check.style.background  = 'var(--sa-bg)';
            check.style.borderColor = 'var(--sa-border)';
            check.style.color       = 'transparent';
        }

        /* Refresh hint text dynamically from all plan checkboxes in this form */
        if (hint) {
            var selected = [];
            document.querySelectorAll('input[name="plan_slugs[]"]').forEach(function (el) {
                if (el.id.indexOf(prefix + 'plan-') === 0 && el.checked) {
                    var labelEl = document.getElementById(prefix + 'plan-label-' + el.value);
                    if (labelEl) {
                        var nameSpan = labelEl.querySelectorAll('span')[3];
                        selected.push(nameSpan ? nameSpan.textContent.trim() : el.value);
                    }
                }
            });
            hint.textContent = selected.length
                ? 'Applies to: ' + selected.join(', ') + ' only.'
                : 'No plans selected — discount applies to all plans.';
        }
    };

    /* ── Init on load ─────────────────────────────────────────────────────── */
    setToggleStyles();

    /* Sync all plan rows by reading accent/rgba from each checkbox's onchange */
    document.querySelectorAll('input[name="plan_slugs[]"]').forEach(function (cb) {
        if (cb.id.indexOf(p + 'plan-') !== 0) return;
        var onch = cb.getAttribute('onchange') || '';
        var m    = onch.match(/syncPlanRow\('[^']*','[^']*','([^']*)','([^']*)'\)/);
        var accent    = m ? m[1] : '#9333ea';
        var colorBase = m ? m[2] : 'rgba(147,51,234';
        syncPlanRow(p, cb.value, accent, colorBase);
    });
})();
</script>