{{-- resources/views/tenants/admin/subscription/expired.blade.php --}}
@extends('layouts.app')
@section('title', 'Subscription Expired')

@section('content')
<div style="min-height:100vh;display:flex;align-items:center;justify-content:center;
            padding:40px 24px;background:var(--bg);">
    <div style="max-width:560px;width:100%;text-align:center;">

        {{-- Icon --}}
        <div style="width:90px;height:90px;border-radius:24px;background:rgba(206,17,38,.10);
                    display:flex;align-items:center;justify-content:center;
                    font-size:40px;margin:0 auto 28px;">⏰</div>

        <h1 style="font-size:32px;font-weight:800;color:#001a4d;margin-bottom:12px;">
            Your Subscription Has Expired
        </h1>
        <p style="font-size:15px;color:#5a7aaa;line-height:1.7;margin-bottom:32px;">
            Your <strong>{{ ucfirst($tenant->subscription) }} Plan</strong> expired on
            <strong>{{ $tenant->expires_at?->format('F d, Y') ?? 'N/A' }}</strong>.
            Submit a renewal request to regain full access.
        </p>

        @if($pendingRequest)
            {{-- Already submitted --}}
            <div style="background:rgba(245,197,24,.08);border:2px solid rgba(245,197,24,.35);
                        border-radius:16px;padding:24px;margin-bottom:24px;">
                <div style="font-size:20px;margin-bottom:8px;">⏳</div>
                <p style="font-weight:700;color:#a07800;margin-bottom:4px;">Renewal Request Pending</p>
                <p style="font-size:13px;color:#5a7aaa;">
                    Submitted on {{ $pendingRequest->created_at->format('M d, Y h:i A') }}.
                    Please wait for super admin approval.
                </p>
                <form action="{{ route('admin.renewal.cancel') }}" method="POST" style="margin-top:16px;">
                    @csrf
                    <button type="submit"
                            onclick="return confirm('Cancel your renewal request?')"
                            style="padding:8px 20px;border-radius:8px;border:1.5px solid #CE1126;
                                   background:transparent;color:#CE1126;font-weight:600;
                                   cursor:pointer;font-size:13px;">
                        Cancel Request
                    </button>
                </form>
            </div>
        @else
            {{-- Plan picker --}}
            <div style="display:grid;gap:12px;margin-bottom:28px;text-align:left;">
                @foreach($plans as $plan)
                    <label style="display:flex;align-items:center;gap:14px;padding:16px 20px;
                                  border-radius:14px;border:2px solid #c5d8f5;background:#fff;
                                  cursor:pointer;transition:border-color .15s;"
                           id="plan-label-{{ $plan->slug }}"
                           onclick="selectRenewalPlan('{{ $plan->slug }}', '{{ $plan->name }}', {{ $plan->price }})">
                        <input type="radio" name="renewal_plan" value="{{ $plan->slug }}"
                               id="plan-radio-{{ $plan->slug }}"
                               style="position:absolute;opacity:0;pointer-events:none;">
                        <span style="font-size:24px;">{{ ['basic'=>'🌱','standard'=>'🚀','premium'=>'💎'][$plan->slug] }}</span>
                        <div style="flex:1;">
                            <div style="font-weight:700;color:#001a4d;">{{ $plan->name }}</div>
                            <div style="font-size:12px;color:#5a7aaa;">{{ $plan->duration_label }} · ₱{{ number_format($plan->price, 0) }}</div>
                        </div>
                        <div id="plan-check-{{ $plan->slug }}"
                             style="width:20px;height:20px;border-radius:50%;border:2px solid #c5d8f5;
                                    background:#f4f8ff;flex-shrink:0;transition:all .15s;"></div>
                    </label>
                @endforeach
            </div>

            {{-- Optional promo code --}}
            <div style="margin-bottom:20px;">
                <input type="text" id="renewal-promo-code" placeholder="Promo code (optional)"
                       style="width:100%;padding:10px 14px;border-radius:10px;border:1.5px solid #c5d8f5;
                              font-size:13px;outline:none;text-transform:uppercase;"
                       oninput="this.value=this.value.toUpperCase()">
            </div>

            <button onclick="submitRenewalRequest()"
                    id="renewal-btn"
                    style="width:100%;padding:15px;border-radius:12px;border:none;
                           background:linear-gradient(135deg,#003087,#0057B8);color:#fff;
                           font-size:15px;font-weight:700;cursor:pointer;">
                <i class="fas fa-paper-plane" style="margin-right:8px;"></i>
                Submit Renewal Request
            </button>

            <div id="renewal-result" style="display:none;margin-top:16px;border-radius:10px;
                 padding:12px 16px;font-size:13px;font-weight:600;"></div>
        @endif

        <p style="margin-top:24px;font-size:12px;color:#9aaccc;">
            Need help? Contact your system administrator.
        </p>
    </div>
</div>

<script>
let selectedPlanForRenewal = null;

function selectRenewalPlan(slug, name, price) {
    selectedPlanForRenewal = slug;

    // Reset all labels
    document.querySelectorAll('[id^="plan-label-"]').forEach(el => {
        el.style.borderColor = '#c5d8f5';
        el.style.background  = '#fff';
    });
    document.querySelectorAll('[id^="plan-check-"]').forEach(el => {
        el.style.background   = '#f4f8ff';
        el.style.borderColor  = '#c5d8f5';
    });

    // Highlight selected
    const label = document.getElementById('plan-label-' + slug);
    const check = document.getElementById('plan-check-' + slug);
    if (label) { label.style.borderColor = '#0057B8'; label.style.background = 'rgba(0,87,184,.04)'; }
    if (check) { check.style.background = '#0057B8'; check.style.borderColor = '#0057B8'; }

    document.getElementById('plan-radio-' + slug).checked = true;
}

function submitRenewalRequest() {
    if (! selectedPlanForRenewal) {
        alert('Please select a plan to renew.');
        return;
    }

    const btn    = document.getElementById('renewal-btn');
    const result = document.getElementById('renewal-result');
    const code   = document.getElementById('renewal-promo-code')?.value.trim();

    btn.disabled  = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting…';

    fetch('{{ route("admin.renewal.request") }}', {
        method  : 'POST',
        headers : { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body    : JSON.stringify({ plan_slug: selectedPlanForRenewal, discount_code: code }),
    })
    .then(r => r.json())
    .then(data => {
        result.style.display = 'block';
        if (data.success) {
            result.style.cssText = 'display:block;margin-top:16px;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;background:rgba(22,163,74,.08);border:1.5px solid rgba(22,163,74,.3);color:#16a34a;';
            result.innerHTML = '✅ ' + data.message;
            setTimeout(() => location.reload(), 1800);
        } else {
            result.style.cssText = 'display:block;margin-top:16px;border-radius:10px;padding:12px 16px;font-size:13px;font-weight:600;background:rgba(206,17,38,.08);border:1.5px solid rgba(206,17,38,.3);color:#CE1126;';
            result.innerHTML = '❌ ' + data.message;
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:8px;"></i>Submit Renewal Request';
        }
    });
}
</script>
@endsection