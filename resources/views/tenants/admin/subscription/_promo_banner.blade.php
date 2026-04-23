@php
    $shownAutoIds = [];
    $uniqueAutos  = [];
    foreach ($autoDiscounts ?? [] as $slug => $disc) {
        if ($disc && !in_array($disc->id, $shownAutoIds)) {
            $shownAutoIds[] = $disc->id;
            $uniqueAutos[]  = ['discount' => $disc, 'slug' => $slug];
        }
    }

    $plansJson = $plans->map(fn($p) => [
        'slug'  => $p->slug,
        'name'  => $p->name,
        'price' => (float) $p->price,
        // ── Read icon from DB, fall back to slug-based default ──────────────
        'icon'  => $p->icon ?? match($p->slug) {
            'basic'    => '🌱',
            'standard' => '🚀',
            'premium'  => '💎',
            default    => '📦',
        },
    ])->values();
@endphp


<div class="promo-section" id="promo-section">
    @foreach($uniqueAutos as $item)
        @php
            $disc  = $item['discount'];
            $scope = empty($disc->plan_slugs)
                ? 'all plans'
                : implode(', ', array_map('ucfirst', $disc->plan_slugs));

            $until = $disc->valid_until
                ? 'Ends ' . $disc->valid_until->format('M d, Y')
                : 'Limited time';
        @endphp
        <div class="promo-auto-banner">
            <div class="promo-auto-icon">🏷️</div>
            <div class="promo-auto-body">
                <div class="promo-auto-title">
                    <span class="promo-auto-value">{{ $disc->formatted_value }} OFF</span>
                    &nbsp;·&nbsp; {{ $disc->label }}
                </div>
                <div class="promo-auto-meta">
                    Automatically applied to {{ $scope }} &nbsp;·&nbsp; {{ $until }}
                </div>
            </div>
            <div class="promo-auto-badge">Auto</div>
        </div>
    @endforeach
    <div class="promo-code-card" id="promo-code-card">

        {{-- Collapsed header --}}
        <button type="button" class="promo-code-toggle" id="promo-toggle-btn"
                onclick="togglePromoWidget()" aria-expanded="false">
            <div class="promo-toggle-left">
                <span class="promo-tag-icon">🎟️</span>
                <div>
                    <div class="promo-toggle-title">Have a promo code?</div>
                    <div class="promo-toggle-sub" id="promo-toggle-sub">
                        Enter a code to get a discount on your upgrade
                    </div>
                </div>
            </div>
            <i class="fas fa-chevron-down promo-chevron" id="promo-chevron"></i>
        </button>

        <div class="promo-code-body" id="promo-code-body" style="display:none;">
            <div class="promo-input-row">
                <div style="position:relative;flex:1;">
                    <i class="fas fa-ticket-alt"
                       style="position:absolute;left:12px;top:50%;transform:translateY(-50%);
                              color:var(--up-muted);font-size:13px;pointer-events:none;"></i>
                    <input type="text"
                           id="standalone-promo-input"
                           placeholder="e.g. SAVE20"
                           autocomplete="off"
                           maxlength="50"
                           oninput="this.value=this.value.toUpperCase(); onStandaloneCodeInput()"
                           style="width:100%;padding:10px 12px 10px 36px;border-radius:10px;
                                  border:1.5px solid var(--up-border);background:var(--up-input-bg);
                                  color:var(--up-text);font-family:inherit;font-size:14px;
                                  outline:none;transition:border-color .15s;letter-spacing:.5px;
                                  font-weight:600;">
                </div>
                <button type="button" id="standalone-apply-btn"
                        onclick="applyStandaloneCode()"
                        class="promo-apply-btn" disabled>
                    Apply
                </button>
            </div>

            {{-- Result feedback --}}
            <div id="standalone-result" style="display:none;margin-top:10px;"></div>

            {{-- Applied code summary --}}
            <div id="standalone-applied-summary" style="display:none;margin-top:12px;"
                 class="promo-applied-summary">
                <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <span style="font-size:18px;">✅</span>
                        <div>
                            <div style="font-size:13px;font-weight:700;color:var(--up-success);">
                                Code applied! <span id="applied-code-label"></span>
                            </div>
                            <div style="font-size:12px;color:var(--up-muted);margin-top:1px;">
                                <span id="applied-code-desc"></span>
                            </div>
                        </div>
                    </div>
                    <button type="button" onclick="clearStandaloneCode()"
                            style="font-size:11px;font-weight:700;color:var(--up-muted);
                                   background:none;border:none;cursor:pointer;padding:4px 8px;
                                   border-radius:6px;transition:background .15s;"
                            onmouseover="this.style.background='rgba(0,0,0,.06)'"
                            onmouseout="this.style.background='none'">
                        Remove
                    </button>
                </div>

                {{-- Per-plan savings preview --}}
                <div id="applied-savings-grid" style="margin-top:12px;display:grid;
                     grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:8px;">
                </div>
            </div>

            <p style="margin:10px 0 0;font-size:11px;color:var(--up-muted);line-height:1.5;">
                Promo codes are applied to the plan price when you confirm your upgrade.
                <br>One code per upgrade — cannot be combined with other codes.
            </p>
        </div>
    </div>
</div>
<style>
    /* ── CSS variable bridge (maps to the subscription page vars) ── */
    :root {
        --up-accent:   #0057B8;
        --up-success:  #16a34a;
        --up-warning:  #b38a00;
        --up-danger:   #CE1126;
        --up-border:   #c5d8f5;
        --up-text:     #001a4d;
        --up-muted:    #5a7aaa;
        --up-bg:       #ffffff;
        --up-surface:  #f4f8ff;
        --up-input-bg: #ffffff;
    }
    .dark {
        --up-accent:   #5b9cf6;
        --up-success:  #4ade80;
        --up-warning:  #fbbf24;
        --up-danger:   #f87171;
        --up-border:   #1e3a6b;
        --up-text:     #dde8ff;
        --up-muted:    #6b8abf;
        --up-bg:       #0a1628;
        --up-surface:  #0d1f3c;
        --up-input-bg: #0d1f3c;
    }

    .promo-section {
        max-width: 1100px;
        margin: 0 auto 40px;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .promo-auto-banner {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 14px 18px;
        border-radius: 14px;
        border: 1.5px solid rgba(22,163,74,.30);
        background: rgba(22,163,74,.07);
        animation: promoBannerIn .4s ease both;
    }
    .dark .promo-auto-banner {
        border-color: rgba(74,222,128,.25);
        background: rgba(74,222,128,.06);
    }
    .promo-auto-icon { font-size: 22px; flex-shrink: 0; }
    .promo-auto-body { flex: 1; min-width: 0; }
    .promo-auto-title {
        font-size: 14px; font-weight: 700;
        color: var(--up-text); line-height: 1.3;
    }
    .promo-auto-value { color: var(--up-success); }
    .promo-auto-meta  { font-size: 11px; color: var(--up-muted); margin-top: 2px; }
    .promo-auto-badge {
        flex-shrink: 0;
        padding: 3px 10px; border-radius: 100px;
        font-size: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: .6px;
        background: rgba(22,163,74,.15); color: var(--up-success);
        border: 1px solid rgba(22,163,74,.25);
    }
    .dark .promo-auto-badge {
        background: rgba(74,222,128,.12);
        border-color: rgba(74,222,128,.22);
        color: var(--up-success);
    }

    .promo-code-card {
        border-radius: 14px;
        border: 1.5px solid var(--up-border);
        background: var(--up-bg);
        overflow: hidden;
        transition: border-color .2s, box-shadow .2s;
    }
    .promo-code-card:focus-within,
    .promo-code-card.expanded {
        border-color: var(--up-accent);
        box-shadow: 0 0 0 3px rgba(0,87,184,.10);
    }
    .dark .promo-code-card:focus-within,
    .dark .promo-code-card.expanded {
        box-shadow: 0 0 0 3px rgba(91,156,246,.12);
    }

    .promo-code-toggle {
        width: 100%;
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; padding: 14px 18px;
        background: none; border: none; cursor: pointer;
        font-family: inherit; text-align: left;
        transition: background .15s;
    }
    .promo-code-toggle:hover { background: var(--up-surface); }
    .promo-toggle-left {
        display: flex; align-items: center; gap: 12px; min-width: 0;
    }
    .promo-tag-icon { font-size: 20px; flex-shrink: 0; }
    .promo-toggle-title {
        font-size: 14px; font-weight: 700; color: var(--up-text); line-height: 1.2;
    }
    .promo-toggle-sub {
        font-size: 12px; color: var(--up-muted); margin-top: 2px;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .promo-chevron {
        font-size: 12px; color: var(--up-muted);
        flex-shrink: 0;
        transition: transform .25s ease;
    }
    .promo-chevron.open { transform: rotate(180deg); }

    .promo-code-body {
        padding: 0 18px 18px;
        border-top: 1px solid var(--up-border);
        animation: promoBodyIn .2s ease;
    }
    @keyframes promoBodyIn {
        from { opacity: 0; transform: translateY(-6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .promo-input-row {
        display: flex; gap: 8px; margin-top: 14px;
    }

    .promo-apply-btn {
        padding: 10px 18px; border-radius: 10px; border: none;
        background: var(--up-accent); color: #fff;
        font-family: inherit; font-size: 13px; font-weight: 700;
        cursor: pointer; white-space: nowrap;
        transition: opacity .15s, transform .15s;
        flex-shrink: 0;
    }
    .promo-apply-btn:disabled {
        opacity: .38; cursor: not-allowed;
    }
    .promo-apply-btn:not(:disabled):hover {
        opacity: .88; transform: translateY(-1px);
    }

    .promo-applied-summary {
        padding: 12px 14px;
        border-radius: 10px;
        border: 1.5px solid rgba(22,163,74,.30);
        background: rgba(22,163,74,.06);
    }
    .dark .promo-applied-summary {
        border-color: rgba(74,222,128,.22);
        background: rgba(74,222,128,.05);
    }

    .savings-plan-pill {
        display: flex; flex-direction: column; align-items: center;
        padding: 8px 10px; border-radius: 10px;
        border: 1.5px solid var(--up-border);
        background: var(--up-surface);
        font-size: 11px; color: var(--up-muted);
        text-align: center; gap: 3px;
    }
    .savings-plan-pill .savings-amount {
        font-size: 14px; font-weight: 800; color: var(--up-success);
        line-height: 1;
    }
    .savings-plan-pill .savings-original {
        text-decoration: line-through; font-size: 10px; color: var(--up-muted);
    }
    .savings-plan-pill .savings-final {
        font-weight: 700; color: var(--up-text); font-size: 12px;
    }

    .promo-result-valid {
        padding: 9px 13px; border-radius: 9px; font-size: 13px; font-weight: 600;
        background: rgba(22,163,74,.08); border: 1.5px solid rgba(22,163,74,.28);
        color: var(--up-success); display: flex; align-items: center; gap: 7px;
    }
    .promo-result-invalid {
        padding: 9px 13px; border-radius: 9px; font-size: 13px; font-weight: 600;
        background: rgba(206,17,38,.07); border: 1.5px solid rgba(206,17,38,.25);
        color: var(--up-danger); display: flex; align-items: center; gap: 7px;
    }

    @keyframes promoBannerIn {
        from { opacity: 0; transform: translateY(-8px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 520px) {
        .promo-auto-banner { flex-wrap: wrap; }
        .promo-input-row   { flex-direction: column; }
        .promo-apply-btn   { width: 100%; }
    }
</style>

<script>
(function () {
    const PLANS = @json($plansJson);
    const CURRENT_PLAN  = '{{ $currentPlan }}';
    const VALIDATE_URL  = '{{ route("admin.subscription.validate-code") }}';
    const CSRF          = '{{ csrf_token() }}';
    const PLAN_SLUGS    = @json($plans->pluck('slug'));
    const CURRENT_INDEX = PLAN_SLUGS.indexOf(CURRENT_PLAN);

    window.standaloneCode      = null;
    window.standaloneFinalMap  = {};

    window.togglePromoWidget = function () {
        const body    = document.getElementById('promo-code-body');
        const chevron = document.getElementById('promo-chevron');
        const card    = document.getElementById('promo-code-card');
        const btn     = document.getElementById('promo-toggle-btn');
        const isOpen  = body.style.display !== 'none';

        body.style.display = isOpen ? 'none' : 'block';
        chevron.classList.toggle('open', !isOpen);
        card.classList.toggle('expanded', !isOpen);
        btn.setAttribute('aria-expanded', String(!isOpen));
    };

    window.onStandaloneCodeInput = function () {
        const val = document.getElementById('standalone-promo-input').value.trim();
        document.getElementById('standalone-apply-btn').disabled = val.length < 2;
        if (!val) clearStandaloneCode();
    };

    window.applyStandaloneCode = function () {
        const code = document.getElementById('standalone-promo-input').value.trim();
        if (!code) return;

        const btn    = document.getElementById('standalone-apply-btn');
        const result = document.getElementById('standalone-result');

        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin" style="font-size:11px;"></i>';
        result.style.display = 'none';

        const upgradeable = PLANS.filter((_, i) => i > CURRENT_INDEX);
        const testSlug    = upgradeable.length ? upgradeable[0].slug : PLAN_SLUGS[PLAN_SLUGS.length - 1];

        fetch(VALIDATE_URL, {
            method  : 'POST',
            headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body    : JSON.stringify({ code, plan_slug: testSlug }),
        })
        .then(r => r.json())
        .then(data => {
            btn.disabled  = false;
            btn.innerHTML = 'Apply';
            result.style.display = 'block';

            if (data.valid) {
                window.standaloneCode = code;

                const ratio = data.discount_amount / data.original_price;
                window.standaloneFinalMap = {};
                PLANS.forEach(plan => {
                    const base  = plan.price;
                    const saved = Math.min(data.discount_amount, base * ratio);
                    window.standaloneFinalMap[plan.slug] = Math.max(0, base - saved);
                });

                result.className = 'promo-result-valid';
                result.innerHTML =
                    '<i class="fas fa-check-circle"></i>' +
                    'Code <strong>' + code + '</strong> is valid — ' + data.formatted_value + ' off!';

                showAppliedSummary(code, data);
                updateToggleSubtext(code, data.formatted_value);

                const modalInput = document.getElementById('modal-discount-code');
                if (modalInput) {
                    modalInput.value = code;
                    if (typeof scheduleCodeCheck === 'function') scheduleCodeCheck();
                }

            } else {
                window.standaloneCode     = null;
                window.standaloneFinalMap = {};

                result.className = 'promo-result-invalid';
                result.innerHTML =
                    '<i class="fas fa-times-circle"></i>' +
                    (data.message || 'Invalid or inapplicable promo code.');

                document.getElementById('standalone-applied-summary').style.display = 'none';
                resetToggleSubtext();
            }
        })
        .catch(() => {
            btn.disabled  = false;
            btn.innerHTML = 'Apply';
            result.className     = 'promo-result-invalid';
            result.style.display = 'block';
            result.innerHTML     = '<i class="fas fa-exclamation-circle"></i> Network error. Please try again.';
        });
    };

    function showAppliedSummary(code, data) {
        const summary   = document.getElementById('standalone-applied-summary');
        const grid      = document.getElementById('applied-savings-grid');
        const codeLabel = document.getElementById('applied-code-label');
        const codeDesc  = document.getElementById('applied-code-desc');

        codeLabel.textContent = code;
        codeDesc.textContent  = data.formatted_value + ' discount on eligible plans';

        const upgradeable = PLANS.filter((_, i) => PLAN_SLUGS.indexOf(_.slug) > CURRENT_INDEX);
        grid.innerHTML = upgradeable.map(plan => {
            const base  = plan.price;
            if (base <= 0) return '';

            const saved = Math.round(data.discount_amount / data.original_price * base * 100) / 100;
            const final = Math.max(0, base - saved);

            return `
                <div class="savings-plan-pill">
                    <span style="font-size:18px;line-height:1;">${plan.icon}</span>
                    <span style="font-weight:700;color:var(--up-text);font-size:12px;">${plan.name}</span>
                    <span class="savings-original">₱${fmt(base)}</span>
                    <span class="savings-amount">−₱${fmt(saved)}</span>
                    <span class="savings-final">₱${fmt(final)}</span>
                </div>
            `;
        }).join('');

        document.getElementById('standalone-result').style.display = 'none';
        summary.style.display = 'block';
    }

    window.clearStandaloneCode = function () {
        window.standaloneCode     = null;
        window.standaloneFinalMap = {};

        document.getElementById('standalone-promo-input').value             = '';
        document.getElementById('standalone-result').style.display          = 'none';
        document.getElementById('standalone-applied-summary').style.display = 'none';
        document.getElementById('standalone-apply-btn').disabled            = true;

        resetToggleSubtext();

        const modalInput = document.getElementById('modal-discount-code');
        if (modalInput) {
            modalInput.value = '';
            if (typeof scheduleCodeCheck === 'function') scheduleCodeCheck();
        }
    };

    function updateToggleSubtext(code, fmtVal) {
        const el = document.getElementById('promo-toggle-sub');
        if (el) {
            el.innerHTML =
                '<span style="color:var(--up-success);font-weight:700;">✓ ' +
                code + ' — ' + fmtVal + ' applied</span>';
        }
    }
    function resetToggleSubtext() {
        const el = document.getElementById('promo-toggle-sub');
        if (el) el.textContent = 'Enter a code to get a discount on your upgrade';
    }

    function fmt(n) {
        return parseFloat(n).toLocaleString('en-PH', { minimumFractionDigits: 2 });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const origSelectPlan = window.selectPlan;
        if (typeof origSelectPlan === 'function') {
            window.selectPlan = function (slug, name, basePrice, autoPrice) {
                if (window.standaloneCode && window.standaloneFinalMap[slug] !== undefined) {
                    autoPrice = window.standaloneFinalMap[slug];
                }
                origSelectPlan(slug, name, basePrice, autoPrice);

                const modalInput = document.getElementById('modal-discount-code');
                if (modalInput && window.standaloneCode) {
                    modalInput.value = window.standaloneCode;
                    setTimeout(function () {
                        if (typeof checkPromoCode === 'function') checkPromoCode();
                    }, 100);
                }
            };
        }
    });

})();
</script>