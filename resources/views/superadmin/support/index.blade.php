@extends('layouts.app')

@section('title', 'Support Inbox')

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;--sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:999px;font-size:11px;font-weight:600;}
    .badge-open{background:rgba(0,87,184,.12);color:#0057B8;}
    .badge-in_progress{background:rgba(179,138,0,.12);color:#b38a00;}
    .badge-resolved{background:rgba(22,163,74,.12);color:#16a34a;}
    .badge-closed{background:rgba(90,122,170,.12);color:#5a7aaa;}
    .priority-dot{width:8px;height:8px;border-radius:50%;display:inline-block;}
    .filter-btn{padding:6px 14px;border-radius:9px;font-size:12px;font-weight:600;cursor:pointer;border:1.5px solid var(--sa-border);background:transparent;color:var(--sa-text-muted);transition:.15s;}
    .filter-btn.active,.filter-btn:hover{background:var(--sa-accent);border-color:var(--sa-accent);color:#fff;}
</style>

<div class="space-y-5">
    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:var(--sa-primary)">
            <i class="fas fa-headset mr-2" style="color:var(--sa-accent)"></i> Support Inbox
        </h1>
        <p class="text-sm mt-1" style="color:var(--sa-text-muted)">All tenant support tickets in one place.</p>
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
            ['label'=>'In Progress','value'=>$stats['in_progress'],'color'=>'#b38a00','icon'=>'fa-spinner'],
            ['label'=>'Resolved','value'=>$stats['resolved'],'color'=>'#16a34a','icon'=>'fa-check-circle'],
            ['label'=>'Unread','value'=>$stats['unread'],'color'=>'#CE1126','icon'=>'fa-bell'],
        ] as $s)
        <div class="rounded-2xl border-2 p-4" style="background:var(--sa-bg);border-color:var(--sa-border)">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-text-muted)">{{ $s['label'] }}</p>
                <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }}"></i>
            </div>
            <p class="text-2xl font-bold" style="color:{{ $s['color'] }}">{{ $s['value'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Filters --}}
    <form method="GET" class="rounded-2xl border-2 p-4 flex flex-wrap items-end gap-3"
          style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="flex-1 min-w-[180px]">
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Ticket #, subject, name…"
                   class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none"
                   style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
        </div>
        <div>
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Status</label>
            <select name="status" class="rounded-lg border px-3 py-2 text-sm"
                    style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                <option value="">All</option>
                <option value="open" @selected(request('status')=='open')>Open</option>
                <option value="in_progress" @selected(request('status')=='in_progress')>In Progress</option>
                <option value="resolved" @selected(request('status')=='resolved')>Resolved</option>
                <option value="closed" @selected(request('status')=='closed')>Closed</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Priority</label>
            <select name="priority" class="rounded-lg border px-3 py-2 text-sm"
                    style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                <option value="">All</option>
                <option value="urgent" @selected(request('priority')=='urgent')>Urgent</option>
                <option value="high" @selected(request('priority')=='high')>High</option>
                <option value="medium" @selected(request('priority')=='medium')>Medium</option>
                <option value="low" @selected(request('priority')=='low')>Low</option>
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold block mb-1" style="color:var(--sa-text-muted)">Tenant</label>
            <select name="tenant_id" class="rounded-lg border px-3 py-2 text-sm"
                    style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                <option value="">All Tenants</option>
                @foreach($tenants as $t)
                <option value="{{ $t->id }}" @selected(request('tenant_id')==$t->id)>{{ $t->name }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-4 py-2 rounded-lg text-sm font-semibold text-white" style="background:var(--sa-accent)">
            <i class="fas fa-search mr-1"></i> Filter
        </button>
        @if(request()->hasAny(['search','status','priority','tenant_id','category']))
        <a href="{{ route('superadmin.support.index') }}"
           class="px-4 py-2 rounded-lg text-sm font-semibold border" style="border-color:var(--sa-border);color:var(--sa-text-muted)">
            Clear
        </a>
        @endif
    </form>

    {{-- Ticket Table --}}
    <div class="rounded-2xl border-2 overflow-hidden" style="background:var(--sa-bg);border-color:var(--sa-border)">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border)">
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Ticket</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Tenant</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Category</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Status</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Priority</th>
                        <th class="text-left px-5 py-3 font-semibold" style="color:var(--sa-text-muted)">Last Reply</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($tickets as $ticket)
                <tr class="border-b hover:bg-gray-50 dark:hover:bg-white/5 transition cursor-pointer"
                    style="border-color:var(--sa-border)"
                    onclick="window.location='{{ route('superadmin.support.show', $ticket) }}'">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-2">
                            @if($ticket->unread_admin > 0)
                            <span class="w-2 h-2 rounded-full flex-shrink-0" style="background:#CE1126"></span>
                            @endif
                            <div>
                                <div class="font-semibold" style="color:var(--sa-primary)">
                                    {{ $ticket->ticket_number }}
                                    @if($ticket->unread_admin > 0)
                                    <span class="ml-1 text-xs font-bold px-1.5 py-0.5 rounded-full text-white" style="background:#CE1126">{{ $ticket->unread_admin }}</span>
                                    @endif
                                </div>
                                <div class="text-xs mt-0.5 line-clamp-1" style="color:var(--sa-text-muted)">{{ $ticket->subject }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        <div class="text-sm font-medium" style="color:var(--sa-text)">{{ $ticket->tenant?->name ?? '—' }}</div>
                        <div class="text-xs" style="color:var(--sa-text-muted)">{{ $ticket->requester_name }}</div>
                    </td>
                    <td class="px-5 py-4 text-xs" style="color:var(--sa-text-muted)">{{ $ticket->category_label }}</td>
                    <td class="px-5 py-4">
                        <span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                    </td>
                    <td class="px-5 py-4">
                        <span class="priority-dot" style="background:{{ $ticket->priority_color }}"></span>
                        <span class="ml-1 text-xs capitalize" style="color:var(--sa-text-muted)">{{ $ticket->priority }}</span>
                    </td>
                    <td class="px-5 py-4 text-xs" style="color:var(--sa-text-muted)">
                        {{ $ticket->last_reply_at?->diffForHumans() ?? $ticket->created_at->diffForHumans() }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-16 text-center" style="color:var(--sa-text-muted)">
                        <i class="fas fa-inbox text-3xl mb-3 block" style="opacity:.4"></i>
                        No support tickets found.
                    </td>
                </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($tickets->hasPages())
        <div class="px-5 py-4 border-t" style="border-color:var(--sa-border)">{{ $tickets->links() }}</div>
        @endif
    </div>
</div>
@endsection
