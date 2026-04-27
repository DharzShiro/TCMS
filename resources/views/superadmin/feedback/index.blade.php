@extends('layouts.app')

@section('title', 'Feedback Inbox')

@section('content')
<style>
    :root{--p:#003087;--a:#0057B8;--b:#c5d8f5;--t:#001a4d;--m:#5a7aaa;--bg:#fff;}
    .dark{--bg:#0a1628;--b:#1e3a6b;--t:#dde8ff;--m:#6b8abf;}
    .card{background:var(--bg);border:1.5px solid var(--b);border-radius:1rem;}
    .stat-box{background:var(--bg);border:1.5px solid var(--b);border-radius:.875rem;padding:1.25rem;text-align:center;}
</style>

<div class="space-y-6">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold" style="color:var(--p)">
            <i class="fas fa-star mr-2" style="color:#f59e0b"></i>Feedback Inbox
        </h1>
        <p class="text-sm mt-1" style="color:var(--m)">All tenant feedback submissions across the platform.</p>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="stat-box">
            <p class="text-3xl font-bold font-mono" style="color:var(--p)">{{ $total }}</p>
            <p class="text-xs mt-1 font-semibold" style="color:var(--m)">Total Submissions</p>
        </div>
        <div class="stat-box">
            <p class="text-3xl font-bold font-mono" style="color:#f59e0b">
                {{ $avgRating ? number_format($avgRating, 1) : '—' }}
            </p>
            <p class="text-xs mt-1 font-semibold" style="color:var(--m)">Avg. Rating</p>
        </div>
        @foreach([5 => 'Excellent', 1 => 'Poor'] as $stars => $label)
        <div class="stat-box">
            <p class="text-3xl font-bold font-mono" style="color:var(--a)">{{ $byRating[$stars] ?? 0 }}</p>
            <p class="text-xs mt-1 font-semibold" style="color:var(--m)">{{ str_repeat('★', $stars) }} {{ $label }}</p>
        </div>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b flex items-center justify-between" style="border-color:var(--b)">
            <h2 class="font-bold text-sm" style="color:var(--p)">
                <i class="fas fa-list mr-2" style="color:var(--a)"></i>All Feedback
            </h2>
        </div>

        @if($feedback->isEmpty())
        <div class="px-5 py-12 text-center">
            <i class="fas fa-inbox text-4xl mb-3" style="color:var(--b)"></i>
            <p class="text-sm" style="color:var(--m)">No feedback submitted yet.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--b)">
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Tenant</th>
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Submitted By</th>
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Rating</th>
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Category</th>
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Message</th>
                        <th class="text-left px-5 py-3 font-semibold text-xs" style="color:var(--m)">Date</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($feedback as $fb)
                <tr class="border-b hover:bg-opacity-50" style="border-color:var(--b)">
                    <td class="px-5 py-3 font-mono text-xs" style="color:var(--a)">{{ $fb->tenant_id }}</td>
                    <td class="px-5 py-3 font-medium" style="color:var(--t)">{{ $fb->submitted_by }}</td>
                    <td class="px-5 py-3" style="color:#f59e0b">
                        {{ str_repeat('★', $fb->rating) }}<span style="color:#e5e7eb">{{ str_repeat('★', 5 - $fb->rating) }}</span>
                    </td>
                    <td class="px-5 py-3 text-xs" style="color:var(--m)">
                        {{ \App\Models\SystemFeedback::categories()[$fb->category] ?? $fb->category }}
                    </td>
                    <td class="px-5 py-3 text-xs max-w-xs" style="color:var(--t)">
                        {{ Str::limit($fb->message, 80) ?? '—' }}
                    </td>
                    <td class="px-5 py-3 text-xs" style="color:var(--m)">{{ $fb->created_at->format('M j, Y') }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @if($feedback->hasPages())
        <div class="px-5 py-3 border-t" style="border-color:var(--b)">{{ $feedback->links() }}</div>
        @endif
        @endif
    </div>

</div>
@endsection
