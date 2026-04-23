{{-- resources/views/superadmin/renewals/index.blade.php --}}
@extends('layouts.app')
@section('title', 'Renewal Requests')

@section('content')
<div class="space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold" style="color:#003087;">
                <i class="fas fa-rotate mr-2" style="color:#0057B8;"></i> Renewal Requests
            </h1>
            <p style="color:#5a7aaa;" class="text-sm mt-1">Review and process tenant subscription renewals</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border-2 p-4" style="background:rgba(22,163,74,.05);border-color:#16a34a;">
            <p class="font-semibold" style="color:#16a34a;"><i class="fas fa-check-circle mr-2"></i>{{ session('success') }}</p>
        </div>
    @endif

    {{-- Expiring Soon Alert --}}
    @if($expiringSoon->count())
        <div class="rounded-xl border-2 p-4" style="background:rgba(245,197,24,.05);border-color:#b38a00;">
            <p class="font-semibold text-sm mb-3" style="color:#a07800;">
                <i class="fas fa-clock mr-2"></i>{{ $expiringSoon->count() }} tenant(s) expiring within 10 days (no renewal request yet)
            </p>
            <div class="flex flex-wrap gap-2">
                @foreach($expiringSoon as $t)
                    <span style="background:rgba(179,138,0,.12);color:#a07800;padding:3px 12px;
                                 border-radius:20px;font-size:12px;font-weight:600;">
                        {{ $t->name }} — {{ $t->expires_at->diffForHumans() }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Status Tabs --}}
    <div class="flex gap-2 flex-wrap">
        @foreach(['pending', 'approved', 'rejected', 'all'] as $tab)
            <a href="{{ route('superadmin.renewals.index', ['status' => $tab]) }}"
               style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:700;
                      border:1.5px solid {{ $status === $tab ? '#0057B8' : '#c5d8f5' }};
                      background:{{ $status === $tab ? '#0057B8' : '#f4f8ff' }};
                      color:{{ $status === $tab ? '#fff' : '#5a7aaa' }};
                      text-decoration:none;">
                {{ ucfirst($tab) }}
                @if(isset($counts[$tab]))
                    <span style="background:{{ $status === $tab ? 'rgba(255,255,255,.25)' : 'rgba(0,87,184,.12)' }};
                                 padding:1px 7px;border-radius:10px;font-size:11px;margin-left:4px;">
                        {{ $counts[$tab] }}
                    </span>
                @endif
            </a>
        @endforeach
    </div>

    {{-- Renewals Table --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:#fff;border-color:#c5d8f5;">
        @if($renewals->count())
            <div class="overflow-x-auto">
                <table style="width:100%;border-collapse:collapse;font-size:13px;">
                    <thead>
                        <tr style="background:#f4f8ff;border-bottom:2px solid #c5d8f5;">
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#5a7aaa;text-transform:uppercase;letter-spacing:.4px;">Tenant</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#5a7aaa;text-transform:uppercase;letter-spacing:.4px;">Plan</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#5a7aaa;text-transform:uppercase;letter-spacing:.4px;">Amount</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#5a7aaa;text-transform:uppercase;letter-spacing:.4px;">Requested</th>
                            <th style="padding:12px 16px;text-align:left;font-size:11px;font-weight:700;color:#5a7aaa;text-transform:uppercase;letter-spacing:.4px;">Status</th>
                            <th style="padding:12px 16px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($renewals as $renewal)
                            @php
                                $statusColor = match($renewal->status) {
                                    'approved' => 'rgba(22,163,74,.1)|#16a34a',
                                    'rejected' => 'rgba(206,17,38,.1)|#CE1126',
                                    default    => 'rgba(179,138,0,.1)|#b38a00',
                                };
                                [$sbg, $sfg] = explode('|', $statusColor);
                            @endphp
                            <tr style="border-bottom:1px solid #e8f0fb;">
                                <td style="padding:14px 16px;">
                                    <div style="font-weight:700;color:#001a4d;">{{ $renewal->tenant->name }}</div>
                                    <div style="font-size:11px;color:#5a7aaa;">{{ $renewal->tenant->admin_email }}</div>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:600;color:#001a4d;">{{ ucfirst($renewal->plan_slug) }}</span>
                                    @if($renewal->discount_code)
                                        <div style="font-size:11px;color:#16a34a;">Code: {{ $renewal->discount_code }}</div>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($renewal->discount_amount > 0)
                                        <div style="font-size:11px;text-decoration:line-through;color:#9aaccc;">
                                            ₱{{ number_format($renewal->original_price, 2) }}
                                        </div>
                                    @endif
                                    <div style="font-weight:700;color:#001a4d;">₱{{ number_format($renewal->final_price, 2) }}</div>
                                </td>
                                <td style="padding:14px 16px;font-size:12px;color:#5a7aaa;">
                                    {{ $renewal->created_at->format('M d, Y') }}<br>
                                    {{ $renewal->created_at->diffForHumans() }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="background:{{ $sbg }};color:{{ $sfg }};
                                                 padding:3px 12px;border-radius:20px;
                                                 font-size:11px;font-weight:700;text-transform:uppercase;">
                                        {{ ucfirst($renewal->status) }}
                                    </span>
                                    @if($renewal->reviewer)
                                        <div style="font-size:10px;color:#9aaccc;margin-top:3px;">
                                            by {{ $renewal->reviewer->name }}
                                        </div>
                                    @endif
                                </td>
                                <td style="padding:14px 16px;">
                                    @if($renewal->isPending())
                                        <div style="display:flex;gap:6px;">
                                            <form action="{{ route('superadmin.renewals.approve', $renewal) }}" method="POST">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Approve renewal for {{ $renewal->tenant->name }}?')"
                                                        style="padding:6px 14px;border-radius:8px;border:none;
                                                               background:#16a34a;color:#fff;font-size:12px;
                                                               font-weight:700;cursor:pointer;">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <button onclick="openRejectModal({{ $renewal->id }})"
                                                    style="padding:6px 14px;border-radius:8px;
                                                           border:1.5px solid #CE1126;background:transparent;
                                                           color:#CE1126;font-size:12px;font-weight:700;cursor:pointer;">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </div>

                                        {{-- Hidden reject form --}}
                                        <form id="reject-form-{{ $renewal->id }}"
                                              action="{{ route('superadmin.renewals.reject', $renewal) }}"
                                              method="POST" style="display:none;">
                                            @csrf
                                            <input type="hidden" name="notes" id="reject-notes-{{ $renewal->id }}">
                                        </form>
                                    @else
                                        @if($renewal->notes)
                                            <span style="font-size:11px;color:#5a7aaa;" title="{{ $renewal->notes }}">
                                                <i class="fas fa-note-sticky"></i> Has notes
                                            </span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="p-4">{{ $renewals->appends(['status' => $status])->links() }}</div>
        @else
            <div style="padding:60px 24px;text-align:center;">
                <i class="fas fa-rotate" style="font-size:40px;color:#c5d8f5;margin-bottom:16px;display:block;"></i>
                <p style="color:#5a7aaa;">No {{ $status === 'all' ? '' : $status }} renewal requests.</p>
            </div>
        @endif
    </div>
</div>

{{-- Reject Modal --}}
<div id="reject-modal" style="display:none;position:fixed;inset:0;z-index:100;
     background:rgba(0,0,0,.45);align-items:center;justify-content:center;padding:24px;">
    <div style="background:#fff;border-radius:20px;padding:32px;max-width:440px;width:100%;">
        <h3 style="font-size:18px;font-weight:800;color:#001a4d;margin-bottom:8px;">Reject Renewal</h3>
        <p style="font-size:13px;color:#5a7aaa;margin-bottom:16px;">
            Optionally provide a reason for rejection (visible to the tenant admin).
        </p>
        <textarea id="reject-notes-input" placeholder="Reason for rejection (optional)…"
                  style="width:100%;padding:10px 12px;border-radius:10px;border:1.5px solid #c5d8f5;
                         font-size:13px;resize:vertical;min-height:90px;outline:none;"></textarea>
        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px;">
            <button onclick="document.getElementById('reject-modal').style.display='none'"
                    style="padding:9px 18px;border-radius:9px;border:1.5px solid #c5d8f5;
                           background:transparent;color:#5a7aaa;font-weight:600;cursor:pointer;">Cancel</button>
            <button onclick="submitReject()"
                    style="padding:9px 18px;border-radius:9px;border:none;
                           background:#CE1126;color:#fff;font-weight:700;cursor:pointer;">
                Confirm Reject
            </button>
        </div>
    </div>
</div>

<script>
let activeRejectId = null;

function openRejectModal(id) {
    activeRejectId = id;
    document.getElementById('reject-notes-input').value = '';
    const modal = document.getElementById('reject-modal');
    modal.style.display = 'flex';
}

function submitReject() {
    if (! activeRejectId) return;
    const notes = document.getElementById('reject-notes-input').value.trim();
    document.getElementById('reject-notes-' + activeRejectId).value = notes;
    document.getElementById('reject-form-' + activeRejectId).submit();
}
</script>
@endsection