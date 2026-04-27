@extends('layouts.app')

@section('title', 'Feedback')

@section('content')
<style>
    :root{--p:#003087;--a:#0057B8;--g:#16a34a;--r:#CE1126;--b:#c5d8f5;--t:#001a4d;--m:#5a7aaa;--bg:#fff;}
    .dark{--bg:#0a1628;--b:#1e3a6b;--t:#dde8ff;--m:#6b8abf;}
    .star-btn{background:none;border:none;font-size:2rem;cursor:pointer;color:#d1d5db;padding:0 2px;transition:color .15s;}
    .star-btn:hover,.star-btn.active{color:#f59e0b;}
    .card{background:var(--bg);border:1.5px solid var(--b);border-radius:1rem;padding:1.5rem;}
    .badge{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:999px;font-size:11px;font-weight:700;}
</style>

<div class="max-w-3xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--p)">
                <i class="fas fa-star mr-2" style="color:#f59e0b"></i>System Feedback
            </h1>
            <p class="text-sm mt-1" style="color:var(--m)">Rate the system and share your thoughts with the support team.</p>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div class="rounded-xl p-4 text-sm font-medium flex items-center gap-2"
         style="background:rgba(22,163,74,.1);color:#16a34a;border:1px solid rgba(22,163,74,.2)">
        <i class="fas fa-check-circle"></i>{{ session('success') }}
    </div>
    @endif

    {{-- Submit Form --}}
    <div class="card">
        <h2 class="font-bold text-base mb-4" style="color:var(--p)">
            <i class="fas fa-pencil-alt mr-2" style="color:var(--a)"></i>Submit Feedback
        </h2>

        <form action="{{ route('admin.feedback.store') }}" method="POST" class="space-y-4">
            @csrf

            {{-- Star Rating --}}
            <div>
                <label class="block text-sm font-semibold mb-2" style="color:var(--t)">Rating</label>
                <div class="flex gap-1" id="star-group">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" class="star-btn" data-value="{{ $i }}">★</button>
                    @endfor
                </div>
                <input type="hidden" name="rating" id="rating-input" value="{{ old('rating', 0) }}" required>
                @error('rating')<p class="text-xs mt-1" style="color:var(--r)">{{ $message }}</p>@enderror
            </div>

            {{-- Category --}}
            <div>
                <label class="block text-sm font-semibold mb-2" style="color:var(--t)">Category</label>
                <select name="category" required
                    class="w-full rounded-xl px-4 py-2.5 text-sm border focus:outline-none focus:ring-2"
                    style="border-color:var(--b);color:var(--t);background:var(--bg)">
                    <option value="">— Select category —</option>
                    @foreach(\App\Models\SystemFeedback::categories() as $key => $label)
                    <option value="{{ $key }}" @selected(old('category') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')<p class="text-xs mt-1" style="color:var(--r)">{{ $message }}</p>@enderror
            </div>

            {{-- Message --}}
            <div>
                <label class="block text-sm font-semibold mb-2" style="color:var(--t)">Message <span style="color:var(--m)">(optional)</span></label>
                <textarea name="message" rows="3" maxlength="1000"
                    placeholder="Any details, suggestions, or issues you'd like to share…"
                    class="w-full rounded-xl px-4 py-2.5 text-sm border focus:outline-none focus:ring-2 resize-none"
                    style="border-color:var(--b);color:var(--t);background:var(--bg)">{{ old('message') }}</textarea>
                @error('message')<p class="text-xs mt-1" style="color:var(--r)">{{ $message }}</p>@enderror
            </div>

            <button type="submit"
                class="px-6 py-2.5 rounded-xl text-white text-sm font-bold hover:opacity-90 transition"
                style="background:var(--a)">
                <i class="fas fa-paper-plane mr-1"></i> Submit Feedback
            </button>
        </form>
    </div>

    {{-- Past Submissions --}}
    @if($feedback->isNotEmpty())
    <div class="card p-0 overflow-hidden">
        <div class="px-5 py-4 border-b" style="border-color:var(--b)">
            <h2 class="font-bold text-sm" style="color:var(--p)">
                <i class="fas fa-history mr-2" style="color:var(--a)"></i>Your Previous Feedback
            </h2>
        </div>
        <div class="divide-y" style="border-color:var(--b)">
            @foreach($feedback as $fb)
            <div class="px-5 py-4 flex items-start gap-4">
                <div class="text-2xl leading-none select-none" style="color:#f59e0b">
                    {{ str_repeat('★', $fb->rating) }}<span style="color:#e5e7eb">{{ str_repeat('★', 5 - $fb->rating) }}</span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="badge" style="background:rgba(0,87,184,.1);color:var(--a)">
                            {{ \App\Models\SystemFeedback::categories()[$fb->category] ?? $fb->category }}
                        </span>
                        <span class="text-xs" style="color:var(--m)">{{ $fb->created_at->format('M j, Y') }}</span>
                    </div>
                    @if($fb->message)
                    <p class="text-sm mt-1" style="color:var(--t)">{{ $fb->message }}</p>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @if($feedback->hasPages())
        <div class="px-5 py-3 border-t" style="border-color:var(--b)">{{ $feedback->links() }}</div>
        @endif
    </div>
    @endif

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const stars  = document.querySelectorAll('.star-btn');
    const input  = document.getElementById('rating-input');
    let current  = parseInt(input.value) || 0;

    function paint(n) {
        stars.forEach((s, i) => s.classList.toggle('active', i < n));
    }

    paint(current);

    stars.forEach((star, idx) => {
        star.addEventListener('click', () => {
            current = idx + 1;
            input.value = current;
            paint(current);
        });
        star.addEventListener('mouseenter', () => paint(idx + 1));
        star.addEventListener('mouseleave', () => paint(current));
    });
});
</script>
@endsection
