<script>
/**
 * Subscription upgrade + renewal JS.
 *
 * KEY RULES:
 *  - selectPlan()      → opens upgrade modal. NO plan change.
 *  - checkPromoCode()  → validates code, updates price. NO plan change.
 *  - confirmUpgrade()  → the ONLY function that triggers an upgrade request.
 *  - selectRenewal()   → opens renewal modal with duration picker. NO change.
 *  - confirmRenewal()  → submits a renewal request (pending superadmin approval).
 */

// ── Shared plan data from Blade ───────────────────────────────────────────────
const planSlugs    = @json($planSlugs);
const currentPlan  = '{{ $currentPlan }}';
const currentIndex = planSlugs.indexOf(currentPlan);

// Plan base prices keyed by slug — used for pro-rating prices
const PLAN_BASE_PRICES = {};
@foreach($plans as $plan)
PLAN_BASE_PRICES['{{ $plan->slug }}'] = {{ (float) $plan->price }};
PLAN_BASE_PRICES['{{ $plan->slug }}__days'] = {{ (int) $plan->duration_days }};
@endforeach

// Current tenant expiry (ISO string or empty)
const TENANT_EXPIRES_AT = '{{ $tenant->expires_at ? $tenant->expires_at->toIso8601String() : '' }}';

// ── Duration chips — shared by both modals ────────────────────────────────────
const DURATION_CHIPS = [
    { label: '1 month',  days: 30  },
    { label: '3 months', days: 90  },
    { label: '6 months', days: 180 },
    { label: '1 year',   days: 365 },
    { label: '2 years',  days: 730 },
];

// ══════════════════════════════════════════════════════════════════════════════
// UPGRADE MODAL
// ══════════════════════════════════════════════════════════════════════════════

let selectedPlanKey      = null;
let selectedPlanBase     = 0;    // full plan price (for the standard duration)
let selectedPlanPPD      = 0;    // price-per-day for pro-rating
let selectedPlanStdDays  = 0;    // standard duration_days for this plan
let upgradeSelectedDays  = 0;    // days chosen by tenant (0 = use standard)
let validatedCode        = null;
let activeCodeFinal      = 0;
let codeTimer            = null;

// ── Open upgrade modal ────────────────────────────────────────────────────────
function selectPlan(slug, name, basePrice, autoPrice) {
    const newIndex = planSlugs.indexOf(slug);
    if (newIndex <= currentIndex) return;

    selectedPlanKey     = slug;
    selectedPlanBase    = parseFloat(basePrice) || 0;
    selectedPlanStdDays = PLAN_BASE_PRICES[slug + '__days'] || 30;
    selectedPlanPPD     = selectedPlanBase > 0
        ? selectedPlanBase / selectedPlanStdDays
        : 0;
    validatedCode       = null;
    activeCodeFinal     = 0;

    document.getElementById('planName').textContent        = name;
    document.getElementById('successPlanName').textContent = name;
    document.getElementById('modal-discount-code').value   = '';
    document.getElementById('modal-discount-result').style.display = 'none';

    // Show duration picker only for paid plans
    const durSection = document.getElementById('upgrade-duration-section');
    if (selectedPlanBase > 0) {
        durSection.style.display = 'block';
        buildUpgradeChips();
        setUpgradeDays(selectedPlanStdDays, true); // default to standard duration
    } else {
        durSection.style.display = 'none';
        upgradeSelectedDays = selectedPlanStdDays;
        refreshUpgradeDurationNote();
    }

    // Auto-discount notice
    const autoFinal   = parseFloat(autoPrice) || selectedPlanBase;
    const hasAutoDisc = autoFinal < selectedPlanBase;
    const notice      = document.getElementById('auto-discount-notice');
    if (hasAutoDisc) {
        const saved = selectedPlanBase - autoFinal;
        document.getElementById('auto-discount-text').textContent =
            'Automatic discount applied — you save ₱' + fmt(saved) +
            ' (₱' + fmt(selectedPlanBase) + ' → ₱' + fmt(autoFinal) + ')';
        notice.style.display = 'block';
    } else {
        notice.style.display = 'none';
    }

    refreshUpgradePriceSummary();

    document.getElementById('confirmView').style.display = 'block';
    document.getElementById('successView').style.display  = 'none';
    document.getElementById('upgradeModal').style.display = 'flex';
}

// ── Build upgrade quick-select chips ─────────────────────────────────────────
function buildUpgradeChips() {
    const container = document.getElementById('upgrade-duration-chips');
    container.innerHTML = '';
    DURATION_CHIPS.forEach(chip => {
        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'upgrade-chip';
        btn.id        = 'upgrade-chip-' + chip.days;
        btn.textContent = chip.label;
        btn.onclick   = () => setUpgradeDays(chip.days);
        container.appendChild(btn);
    });
}

// ── Set upgrade duration ──────────────────────────────────────────────────────
function setUpgradeDays(days, silent) {
    upgradeSelectedDays = days;

    // Highlight the matching chip (if any)
    document.querySelectorAll('.upgrade-chip').forEach(c => c.classList.remove('active'));
    const chip = document.getElementById('upgrade-chip-' + days);
    if (chip) chip.classList.add('active');

    // Clear the custom input unless called silently (on open)
    if (!silent) {
        document.getElementById('upgrade-custom-amount').value = '';
        document.getElementById('upgrade-custom-unit').value   = 'months';
    }

    document.getElementById('upgrade-days-label').textContent = days;
    refreshUpgradeDurationNote();
    refreshUpgradePriceSummary();
}

// ── Custom duration input for upgrade ────────────────────────────────────────
function onUpgradeCustomDurationInput() {
    const amount = parseInt(document.getElementById('upgrade-custom-amount').value) || 0;
    const unit   = document.getElementById('upgrade-custom-unit').value;

    let days = 0;
    if (unit === 'months')     days = Math.round(amount * 30.44);
    else if (unit === 'years') days = amount * 365;
    else                       days = amount;

    if (days < 1) return;

    document.querySelectorAll('.upgrade-chip').forEach(c => c.classList.remove('active'));

    upgradeSelectedDays = days;
    document.getElementById('upgrade-days-label').textContent = days;
    refreshUpgradeDurationNote();
    refreshUpgradePriceSummary();
}

// ── Pro-rated price for selected upgrade days ─────────────────────────────────
function upgradeBasePrice() {
    if (selectedPlanPPD <= 0) return selectedPlanBase; // free plan — no pro-rating
    return Math.round(selectedPlanPPD * upgradeSelectedDays * 100) / 100;
}

// ── Refresh the "(N days)" note in the price row ──────────────────────────────
function refreshUpgradeDurationNote() {
    const note = document.getElementById('upgrade-duration-note');
    if (note && upgradeSelectedDays && upgradeSelectedDays !== selectedPlanStdDays) {
        note.textContent = '(' + upgradeSelectedDays + ' days)';
    } else if (note) {
        note.textContent = '';
    }
}

// ── Refresh upgrade price summary ─────────────────────────────────────────────
function refreshUpgradePriceSummary() {
    const base  = upgradeBasePrice();
    const final = activeCodeFinal > 0 ? activeCodeFinal : base;
    updatePriceSummary(base, final,
        validatedCode ? 'Promo code (' + validatedCode + ')' : null
    );
}

// ── Upgrade promo code ────────────────────────────────────────────────────────
function scheduleCodeCheck() {
    clearTimeout(codeTimer);
    const code = document.getElementById('modal-discount-code').value.trim();
    if (!code) {
        validatedCode   = null;
        activeCodeFinal = 0;
        document.getElementById('modal-discount-result').style.display = 'none';
        refreshUpgradePriceSummary();
        return;
    }
    codeTimer = setTimeout(checkPromoCode, 500);
}

function checkPromoCode() {
    const code   = document.getElementById('modal-discount-code').value.trim();
    const result = document.getElementById('modal-discount-result');
    if (!code || !selectedPlanKey) return;

    fetch('{{ route("admin.subscription.validate-code") }}', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body    : JSON.stringify({ code, plan_slug: selectedPlanKey }),
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        if (data.valid) {
            result.style.cssText = styleBox('green');
            result.innerHTML = '<i class="fas fa-check-circle" style="margin-right:5px;"></i>' +
                               'Code valid — saves ' + data.formatted_value;
            validatedCode = code;

            // Apply discount ratio to the pro-rated base
            const base  = upgradeBasePrice();
            const ratio = data.discount_amount / data.original_price;
            const saved = Math.min(data.discount_amount, base * ratio);
            activeCodeFinal = Math.max(0, base - saved);
        } else {
            result.style.cssText = styleBox('red');
            result.innerHTML = '<i class="fas fa-times-circle" style="margin-right:5px;"></i>' + data.message;
            validatedCode   = null;
            activeCodeFinal = 0;
        }
        refreshUpgradePriceSummary();
    });
}

// ── Upgrade price summary ─────────────────────────────────────────────────────
function updatePriceSummary(base, final, discountLabel) {
    document.getElementById('summary-original').textContent = '₱' + fmt(base);
    document.getElementById('summary-final').textContent    = '₱' + fmt(final);

    const row   = document.getElementById('summary-discount-row');
    const saved = base - final;

    if (discountLabel && saved > 0) {
        document.getElementById('summary-discount-label').textContent = discountLabel;
        document.getElementById('summary-discount').textContent = '−₱' + fmt(saved);
        row.style.display = 'flex';
    } else if (!discountLabel && final < base) {
        document.getElementById('summary-discount-label').textContent = 'Automatic discount';
        document.getElementById('summary-discount').textContent = '−₱' + fmt(saved);
        row.style.display = 'flex';
    } else {
        row.style.display = 'none';
    }
}

// ── Confirm upgrade ───────────────────────────────────────────────────────────
function confirmUpgrade() {
    if (!selectedPlanKey) return;

    // Require a duration for paid plans
    if (selectedPlanBase > 0 && !upgradeSelectedDays) {
        alert('Please choose a duration.');
        return;
    }

    const btn = document.getElementById('confirmBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Upgrading…';

    fetch('{{ route("admin.subscription.upgrade") }}', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body    : JSON.stringify({
            subscription  : selectedPlanKey,
            duration_days : upgradeSelectedDays || selectedPlanStdDays,
            discount_code : validatedCode ?? '',
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('confirmView').style.display = 'none';
            document.getElementById('successView').style.display  = 'block';
        } else {
            alert(data.message || 'Upgrade failed. Please try again.');
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-check"></i> Yes, Upgrade Now';
        }
    })
    .catch(() => {
        alert('An error occurred. Please try again.');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-check"></i> Yes, Upgrade Now';
    });
}

function closeUpgradeModal(event) {
    document.getElementById('upgradeModal').style.display = 'none';
}

// ══════════════════════════════════════════════════════════════════════════════
// RENEWAL MODAL — with duration picker
// ══════════════════════════════════════════════════════════════════════════════

let renewalPlanKey         = null;
let renewalPlanBasePPD     = 0;
let renewalSelectedDays    = 0;
let renewalValidCode       = null;
let renewalActiveCodeFinal = 0;
let renewalCodeTimer       = null;

function selectRenewal(slug, name, basePrice, autoPrice) {
    renewalPlanKey         = slug;
    renewalValidCode       = null;
    renewalActiveCodeFinal = 0;

    const planDays = PLAN_BASE_PRICES[slug + '__days'] || 30;
    renewalPlanBasePPD = basePrice > 0 ? basePrice / planDays : 0;

    document.getElementById('renewalPlanName').textContent        = name;
    document.getElementById('renewalSuccessPlanName').textContent = name;
    document.getElementById('renewal-discount-code').value        = '';
    document.getElementById('renewal-discount-result').style.display = 'none';
    document.getElementById('renewal-pending-warning').style.display  = 'none';
    document.getElementById('renewal-auto-discount-notice').style.display = 'none';

    buildRenewalChips();
    setRenewalDays(planDays, true);

    document.getElementById('renewalConfirmView').style.display  = 'block';
    document.getElementById('renewalSuccessView').style.display  = 'none';
    document.getElementById('renewalModal').style.display        = 'flex';
}

// ── Build renewal quick-select chips ─────────────────────────────────────────
function buildRenewalChips() {
    const container = document.getElementById('renewal-duration-chips');
    container.innerHTML = '';
    DURATION_CHIPS.forEach(chip => {
        const btn = document.createElement('button');
        btn.type      = 'button';
        btn.className = 'renewal-chip';
        btn.id        = 'chip-' + chip.days;
        btn.textContent = chip.label;
        btn.onclick   = () => setRenewalDays(chip.days);
        container.appendChild(btn);
    });
}

// ── Set duration (days) and refresh everything ────────────────────────────────
function setRenewalDays(days, silent) {
    renewalSelectedDays = days;

    document.querySelectorAll('.renewal-chip').forEach(c => c.classList.remove('active'));
    const chip = document.getElementById('chip-' + days);
    if (chip) chip.classList.add('active');

    if (!silent) {
        document.getElementById('renewal-custom-amount').value = '';
        document.getElementById('renewal-custom-unit').value   = 'months';
    }

    document.getElementById('renewal-days-label').textContent = days;
    refreshRenewalPriceSummary();
    refreshRenewalDates();
}

// ── Custom duration input ─────────────────────────────────────────────────────
function onCustomDurationInput() {
    const amount = parseInt(document.getElementById('renewal-custom-amount').value) || 0;
    const unit   = document.getElementById('renewal-custom-unit').value;

    let days = 0;
    if (unit === 'months')     days = Math.round(amount * 30.44);
    else if (unit === 'years') days = amount * 365;
    else                       days = amount;

    if (days < 1) return;

    document.querySelectorAll('.renewal-chip').forEach(c => c.classList.remove('active'));

    renewalSelectedDays = days;
    document.getElementById('renewal-days-label').textContent = days;
    refreshRenewalPriceSummary();
    refreshRenewalDates();
}

// ── Compute pro-rated price for renewal ──────────────────────────────────────
function renewalBasePrice() {
    return Math.round(renewalPlanBasePPD * renewalSelectedDays * 100) / 100;
}

// ── Refresh renewal price summary ─────────────────────────────────────────────
function refreshRenewalPriceSummary() {
    const base  = renewalBasePrice();
    const final = renewalActiveCodeFinal > 0 ? renewalActiveCodeFinal : base;

    const note = document.getElementById('renewal-duration-note');
    if (note) note.textContent = '(' + renewalSelectedDays + ' days)';

    updateRenewalPriceSummary(base, final,
        renewalValidCode ? 'Promo code (' + renewalValidCode + ')' : null
    );
}

// ── Refresh "extending from → to" dates ──────────────────────────────────────
function refreshRenewalDates() {
    const fromEl = document.getElementById('renewal-from-date');
    const toEl   = document.getElementById('renewal-to-date');
    if (!fromEl || !toEl) return;

    const now      = new Date();
    const expiry   = TENANT_EXPIRES_AT ? new Date(TENANT_EXPIRES_AT) : null;
    const baseDate = (expiry && expiry > now) ? expiry : now;

    const toDate = new Date(baseDate);
    toDate.setDate(toDate.getDate() + renewalSelectedDays);

    fromEl.textContent = baseDate.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
    toEl.textContent   = toDate.toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' });
}

// ── Renewal promo code ────────────────────────────────────────────────────────
function scheduleRenewalCodeCheck() {
    clearTimeout(renewalCodeTimer);
    const code = document.getElementById('renewal-discount-code').value.trim();
    if (!code) {
        renewalValidCode       = null;
        renewalActiveCodeFinal = 0;
        document.getElementById('renewal-discount-result').style.display = 'none';
        refreshRenewalPriceSummary();
        return;
    }
    renewalCodeTimer = setTimeout(checkRenewalPromoCode, 500);
}

function checkRenewalPromoCode() {
    const code   = document.getElementById('renewal-discount-code').value.trim();
    const result = document.getElementById('renewal-discount-result');
    if (!code || !renewalPlanKey) return;

    fetch('{{ route("admin.subscription.validate-code") }}', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body    : JSON.stringify({ code, plan_slug: renewalPlanKey }),
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        if (data.valid) {
            result.style.cssText = styleBox('green');
            result.innerHTML = '<i class="fas fa-check-circle" style="margin-right:5px;"></i>' +
                               'Code valid — saves ' + data.formatted_value;

            const base  = renewalBasePrice();
            const ratio = data.discount_amount / data.original_price;
            const saved = Math.min(data.discount_amount, base * ratio);
            renewalActiveCodeFinal = Math.max(0, base - saved);
            renewalValidCode       = code;
        } else {
            result.style.cssText = styleBox('red');
            result.innerHTML = '<i class="fas fa-times-circle" style="margin-right:5px;"></i>' + data.message;
            renewalValidCode       = null;
            renewalActiveCodeFinal = 0;
        }
        refreshRenewalPriceSummary();
    });
}

function updateRenewalPriceSummary(base, final, discountLabel) {
    document.getElementById('renewal-summary-original').textContent = '₱' + fmt(base);
    document.getElementById('renewal-summary-final').textContent    = '₱' + fmt(final);

    const row   = document.getElementById('renewal-summary-discount-row');
    const saved = base - final;

    if (discountLabel && saved > 0) {
        document.getElementById('renewal-summary-discount-label').textContent = discountLabel;
        document.getElementById('renewal-summary-discount').textContent       = '−₱' + fmt(saved);
        row.style.display = 'flex';
    } else if (!discountLabel && final < base) {
        document.getElementById('renewal-summary-discount-label').textContent = 'Automatic discount';
        document.getElementById('renewal-summary-discount').textContent       = '−₱' + fmt(saved);
        row.style.display = 'flex';
    } else {
        row.style.display = 'none';
    }
}

// ── Submit renewal request ────────────────────────────────────────────────────
function confirmRenewal() {
    if (!renewalPlanKey)       return;
    if (!renewalSelectedDays)  { alert('Please choose a duration.'); return; }

    const btn = document.getElementById('renewalConfirmBtn');
    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

    fetch('{{ route("admin.renewal.request") }}', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body    : JSON.stringify({
            plan_slug     : renewalPlanKey,
            duration_days : renewalSelectedDays,
            discount_code : renewalValidCode ?? '',
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            document.getElementById('renewalConfirmView').style.display = 'none';
            document.getElementById('renewalSuccessView').style.display  = 'block';
        } else {
            if (data.message && data.message.toLowerCase().includes('pending')) {
                document.getElementById('renewal-pending-warning').style.display = 'block';
            } else {
                alert(data.message || 'Submission failed. Please try again.');
            }
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Renewal Request';
        }
    })
    .catch(() => {
        alert('A network error occurred. Please try again.');
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Renewal Request';
    });
}

function closeRenewalModal(event) {
    if (event && event.target !== document.getElementById('renewalModal')) return;
    document.getElementById('renewalModal').style.display = 'none';
}

// ── Shared utilities ──────────────────────────────────────────────────────────
function fmt(n) {
    return parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
}

function styleBox(color) {
    const map = {
        green : 'display:block;margin-top:8px;border-radius:8px;padding:9px 12px;font-size:13px;font-weight:600;background:rgba(22,163,74,.08);border:1.5px solid rgba(22,163,74,.3);color:#16a34a;',
        red   : 'display:block;margin-top:8px;border-radius:8px;padding:9px 12px;font-size:13px;font-weight:600;background:rgba(206,17,38,.08);border:1.5px solid rgba(206,17,38,.3);color:#CE1126;',
    };
    return map[color] || '';
}
</script>