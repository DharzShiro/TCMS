@extends('layouts.app')

@section('title', $ticket->ticket_number . ' — Support')

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;--sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;}
    .bubble-admin{background:rgba(0,87,184,.08);border:1.5px solid rgba(0,87,184,.18);border-radius:16px 16px 4px 16px;padding:14px 18px;}
    .bubble-tenant{background:var(--sa-bg);border:1.5px solid var(--sa-border);border-radius:16px 16px 16px 4px;padding:14px 18px;}
    .bubble-internal{background:rgba(179,138,0,.07);border:1.5px dashed rgba(179,138,0,.35);border-radius:12px;padding:14px 18px;}
</style>

<div class="max-w-5xl mx-auto space-y-5">
    {{-- Back + header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('superadmin.support.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border-2 transition hover:bg-gray-50 dark:hover:bg-white/5"
           style="border-color:var(--sa-border);color:var(--sa-text-muted)">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div>
            <h1 class="text-xl font-bold" style="color:var(--sa-primary)">
                {{ $ticket->ticket_number }} — {{ $ticket->subject }}
            </h1>
            <p class="text-xs mt-0.5" style="color:var(--sa-text-muted)">
                {{ $ticket->tenant?->name }} &bull; {{ $ticket->requester_name }} &lt;{{ $ticket->requester_email }}&gt;
                &bull; Opened {{ $ticket->created_at->format('M j, Y g:i A') }}
            </p>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl p-3 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Chat thread (left/main) --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Messages --}}
            <div class="space-y-4">
                @foreach($ticket->messages as $msg)
                <div class="flex {{ $msg->isFromAdmin() ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[85%]">
                        {{-- Sender label --}}
                        <p class="text-xs font-semibold mb-1.5 {{ $msg->isFromAdmin() ? 'text-right' : '' }}"
                           style="color:var(--sa-text-muted)">
                            @if($msg->is_internal)
                                <span style="color:#b38a00"><i class="fas fa-lock text-xs mr-1"></i>Internal Note —</span>
                            @endif
                            {{ $msg->sender_name }}
                            <span class="font-normal ml-1">{{ $msg->created_at->format('M j, g:i A') }}</span>
                        </p>

                        {{-- Bubble --}}
                        <div class="{{ $msg->is_internal ? 'bubble-internal' : ($msg->isFromAdmin() ? 'bubble-admin' : 'bubble-tenant') }}">
                            <div class="text-sm whitespace-pre-wrap leading-relaxed" style="color:var(--sa-text)">{{ $msg->body }}</div>

                            {{-- Attachments --}}
                            @if($msg->attachments->isNotEmpty())
                            <div class="mt-3 pt-3 border-t flex flex-wrap gap-2" style="border-color:var(--sa-border)">
                                @foreach($msg->attachments as $att)
                                    @if($att->isImage())
                                    <button type="button"
                                            onclick="openLightbox('{{ route('superadmin.support.preview', $att) }}', '{{ addslashes($att->original_name) }}')"
                                            class="group block rounded-xl overflow-hidden border-2 hover:border-blue-400 transition"
                                            style="border-color:var(--sa-border);background:var(--sa-bg)"
                                            title="{{ $att->original_name }}">
                                        <img src="{{ route('superadmin.support.preview', $att) }}"
                                             alt="{{ $att->original_name }}"
                                             style="max-height:160px;max-width:260px;width:auto;display:block;object-fit:cover;">
                                        <div class="px-2 py-1 text-xs flex items-center justify-between gap-3" style="color:var(--sa-text-muted)">
                                            <span class="truncate max-w-[160px]">{{ Str::limit($att->original_name, 22) }}</span>
                                            <a href="{{ route('superadmin.support.attachment', $att) }}"
                                               onclick="event.stopPropagation()"
                                               title="Download" style="color:var(--sa-accent)">
                                                <i class="fas fa-download text-xs"></i>
                                            </a>
                                        </div>
                                    </button>
                                    @else
                                    <a href="{{ route('superadmin.support.attachment', $att) }}"
                                       class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border hover:opacity-80 transition"
                                       style="border-color:var(--sa-border);color:var(--sa-accent);background:var(--sa-bg)">
                                        <i class="fas fa-paperclip"></i>
                                        {{ Str::limit($att->original_name, 30) }}
                                        <span style="color:var(--sa-text-muted)">({{ $att->formatted_size }})</span>
                                    </a>
                                    @endif
                                @endforeach
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Reply form --}}
            @if($ticket->isOpen())
            <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
                <h3 class="font-bold text-sm mb-4" style="color:var(--sa-primary)">
                    <i class="fas fa-reply mr-2" style="color:var(--sa-accent)"></i>Reply to Ticket
                </h3>
                <form action="{{ route('superadmin.support.reply', $ticket) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <textarea name="body" rows="5" required placeholder="Type your reply…"
                              class="w-full rounded-xl border px-4 py-3 text-sm focus:outline-none resize-none"
                              style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">{{ old('body') }}</textarea>

                    <div class="flex items-center justify-between mt-3 gap-3 flex-wrap">
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-xs font-medium cursor-pointer" style="color:var(--sa-text-muted)">
                                <input type="checkbox" name="is_internal" value="1" class="rounded">
                                Internal note (hidden from tenant)
                            </label>
                            <label class="flex items-center gap-2 text-xs font-medium cursor-pointer" style="color:var(--sa-text-muted)">
                                <i class="fas fa-paperclip"></i>
                                <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.txt" class="text-xs">
                            </label>
                        </div>
                        <button type="submit"
                                class="px-5 py-2 rounded-xl text-sm font-semibold text-white hover:opacity-90"
                                style="background:var(--sa-accent)">
                            <i class="fas fa-paper-plane mr-1"></i> Send Reply
                        </button>
                    </div>
                </form>
            </div>
            @else
            <div class="rounded-xl p-4 text-center text-sm" style="background:rgba(90,122,170,.08);color:var(--sa-text-muted);border:1.5px solid var(--sa-border)">
                <i class="fas fa-lock mr-2"></i>This ticket is {{ $ticket->status }}. Reopen it to reply.
            </div>
            @endif
        </div>

        {{-- Sidebar (right) --}}
        <div class="space-y-4">
            {{-- Ticket Info --}}
            <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
                <h3 class="font-bold text-sm mb-4" style="color:var(--sa-primary)">Ticket Details</h3>
                <dl class="space-y-3 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Category</dt>
                        <dd style="color:var(--sa-text)">{{ $ticket->category_label }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Priority</dt>
                        <dd>
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold capitalize"
                                  style="color:{{ $ticket->priority_color }}">
                                <span class="w-2 h-2 rounded-full" style="background:{{ $ticket->priority_color }}"></span>
                                {{ $ticket->priority }}
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Tenant</dt>
                        <dd style="color:var(--sa-text)">{{ $ticket->tenant?->name ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Requester</dt>
                        <dd style="color:var(--sa-text)">{{ $ticket->requester_name }}<br>
                            <span class="text-xs" style="color:var(--sa-text-muted)">{{ $ticket->requester_email }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wide mb-1" style="color:var(--sa-text-muted)">Opened</dt>
                        <dd class="text-xs" style="color:var(--sa-text-muted)">{{ $ticket->created_at->format('M j, Y g:i A') }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Change Status --}}
            <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
                <h3 class="font-bold text-sm mb-3" style="color:var(--sa-primary)">Update Status</h3>
                <form action="{{ route('superadmin.support.status', $ticket) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')
                    <select name="status" class="w-full rounded-lg border px-3 py-2 text-sm"
                            style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                        @foreach(['open','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected($ticket->status===$s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white"
                            style="background:var(--sa-accent)">Update Status</button>
                </form>
            </div>

            {{-- Change Priority --}}
            <div class="rounded-2xl border-2 p-5" style="background:var(--sa-bg);border-color:var(--sa-border)">
                <h3 class="font-bold text-sm mb-3" style="color:var(--sa-primary)">Update Priority</h3>
                <form action="{{ route('superadmin.support.priority', $ticket) }}" method="POST" class="space-y-3">
                    @csrf @method('PATCH')
                    <select name="priority" class="w-full rounded-lg border px-3 py-2 text-sm"
                            style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text)">
                        @foreach(['low','medium','high','urgent'] as $p)
                        <option value="{{ $p }}" @selected($ticket->priority===$p)>{{ ucfirst($p) }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="w-full py-2 rounded-lg text-sm font-semibold text-white"
                            style="background:var(--sa-accent)">Update Priority</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Lightbox overlay --}}
<div id="lbOverlay" onclick="closeLightbox()" style="
    display:none; position:fixed; inset:0; z-index:9999;
    background:rgba(0,0,0,.88); align-items:center; justify-content:center;
    padding:20px; cursor:zoom-out;
">
    <div onclick="event.stopPropagation()" style="position:relative;max-width:92vw;max-height:90vh;cursor:default;">
        <img id="lbImg" src="" alt="" style="
            max-width:92vw; max-height:86vh; border-radius:12px;
            box-shadow:0 24px 64px rgba(0,0,0,.6); display:block; object-fit:contain;
        ">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-top:10px;gap:12px;">
            <span id="lbName" style="color:rgba(255,255,255,.75);font-size:13px;"></span>
            <div style="display:flex;gap:8px;">
                <a id="lbDownload" href="#" download
                   style="color:white;background:rgba(255,255,255,.15);padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;text-decoration:none;backdrop-filter:blur(6px);">
                    <i class="fas fa-download mr-1"></i>Download
                </a>
                <button onclick="closeLightbox()"
                        style="color:white;background:rgba(255,255,255,.15);padding:6px 14px;border-radius:8px;font-size:12px;font-weight:600;backdrop-filter:blur(6px);border:none;cursor:pointer;">
                    <i class="fas fa-times mr-1"></i>Close
                </button>
            </div>
        </div>
    </div>
</div>
<script>
function openLightbox(src, name) {
    document.getElementById('lbImg').src = src;
    document.getElementById('lbName').textContent = name;
    document.getElementById('lbDownload').href = src.replace('/preview', '/download');
    const el = document.getElementById('lbOverlay');
    el.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    document.getElementById('lbOverlay').style.display = 'none';
    document.getElementById('lbImg').src = '';
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
</script>
@endpush
