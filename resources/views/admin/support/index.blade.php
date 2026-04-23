@extends('layouts.app')

@section('title', 'Support Center')

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;--sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600;}
    .badge-open{background:rgba(0,87,184,.12);color:#0057B8;}
    .badge-in_progress{background:rgba(179,138,0,.12);color:#b38a00;}
    .badge-resolved{background:rgba(22,163,74,.12);color:#16a34a;}
    .badge-closed{background:rgba(90,122,170,.12);color:#5a7aaa;}
</style>

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-primary)">
                <i class="fas fa-headset mr-2" style="color:var(--sa-accent)"></i>Support Center
            </h1>
            <p class="text-sm mt-1" style="color:var(--sa-text-muted)">Submit and track your support requests.</p>
        </div>
        <a href="{{ route('admin.support.create') }}"
           class="flex items-center gap-2 px-4 py-2 rounded-xl text-white text-sm font-semibold hover:opacity-90"
           style="background:var(--sa-accent)">
            <i class="fas fa-plus"></i> New Ticket
        </a>
    </div>

    @if(session('success'))
    <div class="rounded-xl p-3 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @foreach([
            ['label'=>'Open','value'=>$stats['open'],'color'=>'#0057B8','icon'=>'fa-folder-open'],
            ['label'=>'Total','value'=>$stats['total'],'color'=>'var(--sa-text-muted)','icon'=>'fa-list'],
            ['label'=>'Unread Replies','value'=>$stats['unread'],'color'=>'#CE1126','icon'=>'fa-bell'],
            ['label'=>'Resolved','value'=>$stats['resolved'],'color'=>'#16a34a','icon'=>'fa-check-circle'],
        ] as $s)
        <div class="rounded-2xl border-2 p-4" style="background:var(--sa-bg);border-color:var(--sa-border)">
            <div class="flex items-center justify-between mb-1">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-text-muted)">{{ $s['label'] }}</p>
                <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }}"></i>
            </div>
            <p class="text-2xl font-bold" style="color:{{ $s['color'] }}">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filter bar --}}
    <form method="GET" class="flex flex-wrap gap-3 items-end">
        <div>
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Status</label>
            <select name="status" class="rounded-lg border px-3 py-2 text-sm"
                    style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                <option value="">All Statuses</option>
                @foreach(['open','in_progress','resolved','closed'] as $s)
                <option value="{{ $s }}" @selected(request('status')===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Category</label>
            <select name="category" class="rounded-lg border px-3 py-2 text-sm"
                    style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                <option value="">All Categories</option>
                @foreach(['bug_report','technical_issue','account_concern','billing_concern','feature_request','general_inquiry'] as $c)
                <option value="{{ $c }}" @selected(request('category')===$c)>{{ ucfirst(str_replace('_',' ',$c)) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:var(--sa-accent)">
            <i class="fas fa-filter mr-1"></i> Filter
        </button>
        @if(request()->hasAny(['status','category']))
        <a href="{{ route('admin.support.index') }}" class="px-4 py-2 rounded-lg text-sm font-semibold border"
           style="border-color:var(--sa-border);color:var(--sa-text-muted)">Clear</a>
        @endif
    </form>

    {{-- Ticket list --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        @forelse($tickets as $ticket)
        <a href="{{ route('admin.support.show', $ticket->id) }}"
           class="flex items-start gap-4 px-5 py-4 border-b hover:bg-gray-50 dark:hover:bg-white/5 transition {{ !$loop->last ? '' : 'border-b-0' }}"
           style="border-color:var(--sa-border)">
            {{-- Unread dot --}}
            <div class="flex-shrink-0 mt-1">
                <span class="w-2.5 h-2.5 rounded-full block {{ $ticket->unread_tenant > 0 ? '' : 'opacity-0' }}"
                      style="background:#CE1126"></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="font-bold text-sm flex-shrink-0" style="color:var(--sa-primary)">{{ $ticket->ticket_number }}</span>
                        @if($ticket->unread_tenant > 0)
                        <span class="text-xs font-bold px-1.5 py-0.5 rounded-full text-white flex-shrink-0"
                              style="background:#CE1126">{{ $ticket->unread_tenant }} new</span>
                        @endif
                        <span class="text-sm font-medium truncate" style="color:var(--sa-text)">{{ $ticket->subject }}</span>
                    </div>
                    <span class="badge badge-{{ $ticket->status }} flex-shrink-0">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                </div>
                <div class="flex items-center gap-3 mt-1">
                    <span class="text-xs" style="color:var(--sa-text-muted)">{{ $ticket->category_label }}</span>
                    <span class="text-xs" style="color:var(--sa-text-muted)">·</span>
                    <span class="text-xs capitalize" style="color:{{ $ticket->priority_color }}">{{ $ticket->priority }}</span>
                    <span class="text-xs" style="color:var(--sa-text-muted)">·</span>
                    <span class="text-xs" style="color:var(--sa-text-muted)">
                        {{ $ticket->last_reply_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}
                    </span>
                </div>
            </div>
            <i class="fas fa-chevron-right text-xs flex-shrink-0 mt-2" style="color:var(--sa-text-muted)"></i>
        </a>
        @empty
        <div class="px-6 py-16 text-center">
            <i class="fas fa-inbox text-4xl mb-3 block" style="color:var(--sa-border)"></i>
            <p class="font-semibold" style="color:var(--sa-text-muted)">No tickets yet.</p>
            <p class="text-sm mt-1" style="color:var(--sa-text-muted)">Having an issue? Open a support ticket and we'll help you.</p>
            <a href="{{ route('admin.support.create') }}"
               class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 rounded-xl text-white text-sm font-semibold"
               style="background:var(--sa-accent)">
                <i class="fas fa-plus"></i> New Ticket
            </a>
        </div>
        @endforelse
    </div>

    @if($tickets->hasPages())
    <div>{{ $tickets->links() }}</div>
    @endif

</div>
@endsection
