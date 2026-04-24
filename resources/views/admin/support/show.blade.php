@extends('layouts.app')

@section('title', $ticket->ticket_number)

@section('content')
<style>
    :root{--sa-primary:#003087;--sa-accent:#0057B8;--sa-success:#16a34a;--sa-warning:#b38a00;--sa-danger:#CE1126;--sa-border:#c5d8f5;--sa-text:#001a4d;--sa-text-muted:#5a7aaa;--sa-bg:#ffffff;}
    .dark{--sa-bg:#0a1628;--sa-border:#1e3a6b;--sa-text:#dde8ff;--sa-text-muted:#6b8abf;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:12px;font-weight:600;}
    .badge-open{background:rgba(0,87,184,.12);color:#0057B8;}
    .badge-in_progress{background:rgba(179,138,0,.12);color:#b38a00;}
    .badge-resolved{background:rgba(22,163,74,.12);color:#16a34a;}
    .badge-closed{background:rgba(90,122,170,.12);color:#5a7aaa;}
    .bubble-admin{background:rgba(0,87,184,.07);border:1.5px solid rgba(0,87,184,.18);border-radius:16px 16px 4px 16px;padding:14px 18px;}
    .bubble-tenant{background:var(--sa-bg);border:1.5px solid var(--sa-border);border-radius:16px 16px 16px 4px;padding:14px 18px;}
</style>

<div class="max-w-3xl mx-auto space-y-5">

    {{-- Back + header --}}
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.support.index') }}"
           class="w-9 h-9 flex items-center justify-center rounded-xl border-2 hover:bg-gray-50 dark:hover:bg-white/5 transition"
           style="border-color:var(--sa-border);color:var(--sa-text-muted)">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
                <h1 class="text-lg font-bold" style="color:var(--sa-primary)">{{ $ticket->ticket_number }}</h1>
                <span class="badge badge-{{ $ticket->status }}">{{ ucfirst(str_replace('_',' ',$ticket->status)) }}</span>
                <span class="text-xs capitalize font-medium" style="color:{{ $ticket->priority_color }}">
                    <span class="inline-block w-2 h-2 rounded-full mr-1" style="background:{{ $ticket->priority_color }}"></span>
                    {{ $ticket->priority }}
                </span>
            </div>
            <p class="text-sm mt-0.5 truncate" style="color:var(--sa-text-muted)">{{ $ticket->subject }}</p>
        </div>
    </div>

    @if(session('success'))
    <div class="rounded-xl p-3 text-sm font-medium" style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-xl p-3 text-sm font-medium" style="background:rgba(206,17,38,.08);color:#CE1126;border:1px solid rgba(206,17,38,.2)">
        <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
    </div>
    @endif

    {{-- Ticket meta --}}
    <div class="rounded-xl p-4 flex flex-wrap gap-5 text-xs"
         style="background:rgba(0,87,184,.04);border:1.5px solid rgba(0,87,184,.12)">
        <div><span style="color:var(--sa-text-muted)">Category: </span><strong style="color:var(--sa-text)">{{ $ticket->category_label }}</strong></div>
        <div><span style="color:var(--sa-text-muted)">Opened: </span><strong style="color:var(--sa-text)">{{ $ticket->created_at->format('M j, Y g:i A') }}</strong></div>
        <div><span style="color:var(--sa-text-muted)">Last reply: </span><strong style="color:var(--sa-text)">{{ $ticket->last_reply_at?->diffForHumans() ?? '—' }}</strong></div>
    </div>

    {{-- Thread --}}
    <div class="space-y-4">
        @foreach($ticket->messages as $msg)
        {{-- Skip internal notes for tenant view --}}
        @if($msg->is_internal) @continue @endif

        <div class="flex {{ $msg->isFromAdmin() ? 'justify-end' : 'justify-start' }}">
            <div class="max-w-[90%]">
                <p class="text-xs font-semibold mb-1.5 {{ $msg->isFromAdmin() ? 'text-right' : '' }}"
                   style="color:var(--sa-text-muted)">
                    {{ $msg->isFromAdmin() ? 'Support Team' : 'You' }}
                    <span class="font-normal ml-1">{{ $msg->created_at->format('M j, g:i A') }}</span>
                </p>
                <div class="{{ $msg->isFromAdmin() ? 'bubble-admin' : 'bubble-tenant' }}">
                    <div class="text-sm whitespace-pre-wrap leading-relaxed" style="color:var(--sa-text)">{{ $msg->body }}</div>

                    @if($msg->attachments->isNotEmpty())
                    <div class="mt-3 pt-3 border-t flex flex-wrap gap-2" style="border-color:var(--sa-border)">
                        @foreach($msg->attachments as $att)
                            @if($att->isImage())
                            <button type="button"
                                    onclick="openLightbox('{{ route('admin.support.preview', [$ticket->id, $att]) }}', '{{ addslashes($att->original_name) }}')"
                                    class="group block rounded-xl overflow-hidden border-2 hover:border-blue-400 transition"
                                    style="border-color:var(--sa-border);background:var(--sa-bg)"
                                    title="{{ $att->original_name }}">
                                <img src="{{ route('admin.support.preview', [$ticket->id, $att]) }}"
                                     alt="{{ $att->original_name }}"
                                     style="max-height:160px;max-width:260px;width:auto;display:block;object-fit:cover;">
                                <div class="px-2 py-1 text-xs flex items-center justify-between gap-3" style="color:var(--sa-text-muted)">
                                    <span class="truncate max-w-[160px]">{{ Str::limit($att->original_name, 22) }}</span>
                                    <a href="{{ route('admin.support.attachment', [$ticket->id, $att]) }}"
                                       onclick="event.stopPropagation()"
                                       title="Download" style="color:var(--sa-accent)">
                                        <i class="fas fa-download text-xs"></i>
                                    </a>
                                </div>
                            </button>
                            @else
                            <a href="{{ route('admin.support.attachment', [$ticket->id, $att]) }}"
                               class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium border hover:opacity-80 transition"
                               style="border-color:var(--sa-border);color:var(--sa-accent);background:var(--sa-bg)">
                                <i class="fas fa-paperclip"></i>
                                {{ Str::limit($att->original_name, 28) }}
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
            <i class="fas fa-reply mr-2" style="color:var(--sa-accent)"></i>Add Reply
        </h3>
        <form action="{{ route('admin.support.reply', $ticket->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <textarea name="body" rows="5" required placeholder="Type your message…"
                      class="w-full rounded-xl border px-4 py-3 text-sm focus:outline-none resize-none"
                      style="border-color:var(--sa-border);background:var(--sa-bg);color:var(--sa-text);transition:border-color .15s"
                      onfocus="this.style.borderColor='var(--sa-accent)'"
                      onblur="this.style.borderColor='var(--sa-border)'">{{ old('body') }}</textarea>

            <div class="flex items-center justify-between mt-3 flex-wrap gap-3">
                <label class="flex items-center gap-2 text-xs font-medium cursor-pointer" style="color:var(--sa-text-muted)">
                    <i class="fas fa-paperclip"></i>
                    Attach files (max 5 · 5MB each)
                    <input type="file" name="attachments[]" multiple accept="image/*,.pdf,.txt" class="text-xs">
                </label>
                <button type="submit"
                        class="px-5 py-2 rounded-xl text-sm font-semibold text-white hover:opacity-90"
                        style="background:var(--sa-accent)">
                    <i class="fas fa-paper-plane mr-1"></i> Send
                </button>
            </div>
        </form>
    </div>
    @else
    <div class="rounded-xl p-4 text-center text-sm" style="background:rgba(90,122,170,.08);color:var(--sa-text-muted);border:1.5px solid var(--sa-border)">
        <i class="fas fa-lock mr-2"></i>
        This ticket is <strong>{{ ucfirst($ticket->status) }}</strong>. Please
        <a href="{{ route('admin.support.create') }}" style="color:var(--sa-accent)">open a new ticket</a>
        if you need further assistance.
    </div>
    @endif

</div>
@endsection

@push('scripts')
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
    document.getElementById('lbDownload').href = src.replace('/preview', '');
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
