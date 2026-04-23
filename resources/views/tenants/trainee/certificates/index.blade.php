@extends('layouts.app')

@section('title', 'My Certificates')

@section('content')
<style>
    /* ══════════════════════════════════════════
       CERTIFICATE LIST DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --cert-surface:      #ffffff;
        --cert-surface2:     #f0f5ff;
        --cert-border:       #c5d8f5;
        --cert-text:         #001a4d;
        --cert-text-sec:     #1a3a6b;
        --cert-muted:        #5a7aaa;
        --cert-accent:       #0057B8;
        --cert-accent-bg:    #e8f0fb;
        --cert-primary:      #003087;
        --cert-red:          #CE1126;
        --cert-red-bg:       #fff0f2;
    }
    .dark {
        --cert-surface:      #0a1628;
        --cert-surface2:     #0d1f3c;
        --cert-border:       #1e3a6b;
        --cert-text:         #dde8ff;
        --cert-text-sec:     #adc4f0;
        --cert-muted:        #6b8abf;
        --cert-accent-bg:    rgba(0,87,184,0.15);
        --cert-primary:      #5b9cf6;
        --cert-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="space-y-6">

    {{-- Page Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--cert-primary);">
                <i class="fas fa-certificate mr-2" style="color:var(--cert-red);"></i>
                My Certificates
            </h1>
            <p class="text-sm mt-1" style="color:var(--cert-muted);">
                View your earned training certificates and credentials.
            </p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium"
             style="background:#f0fdf4; border:1px solid #bbf7d0; color:#16a34a;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Filters --}}
    <div class="rounded-2xl border p-5"
         style="background:var(--cert-surface); border-color:var(--cert-border);">
        <form method="GET" action="{{ route('trainee.certificates.index') }}"
              class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-1">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs" style="color:var(--cert-muted);"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by certificate #, course name or code..."
                       class="w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm outline-none transition"
                       style="border-color:var(--cert-border); color:var(--cert-text);"
                       onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                       onblur="this.style.borderColor='var(--cert-border)'; this.style.boxShadow='none'">
            </div>
            <select name="expired"
                    class="px-4 py-2.5 rounded-xl border text-sm outline-none transition"
                    style="border-color:var(--cert-border); color:var(--cert-text);"
                    onfocus="this.style.borderColor='#0057B8'; this.style.boxShadow='0 0 0 3px rgba(0,87,184,0.10)'"
                    onblur="this.style.borderColor='var(--cert-border)'; this.style.boxShadow='none'">
                <option value="">All Certificates</option>
                <option value="no" {{ request('expired') === 'no' ? 'selected' : '' }}>Active</option>
                <option value="yes" {{ request('expired') === 'yes' ? 'selected' : '' }}>Expired</option>
            </select>
            <button type="submit"
                    class="px-5 py-2.5 rounded-xl text-white text-sm font-bold transition hover:-translate-y-0.5"
                    style="background:linear-gradient(135deg,#0057B8,#003087);">
                <i class="fas fa-filter mr-1"></i> Filter
            </button>
            @if (request()->filled('search') || request()->filled('expired'))
                <a href="{{ route('trainee.certificates.index') }}"
                   class="px-4 py-2.5 rounded-xl text-sm font-semibold border transition"
                   style="border-color:var(--cert-border); color:var(--cert-muted);">
                    Clear
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--cert-surface); border-color:var(--cert-border);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="background:var(--cert-accent-bg); border-bottom:1px solid var(--cert-border);">
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Certificate #</th>
                        <th class="px-5 py-3 text-left font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Course</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Issued</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Expires</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Status</th>
                        <th class="px-5 py-3 text-center font-700 text-xs uppercase tracking-wide" style="color:var(--cert-accent);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--cert-border);">
                    @forelse ($certificates as $certificate)
                        @php
                            $isExpired = $certificate->expires_at && $certificate->expires_at < now();
                        @endphp
                        <tr style="background:var(--cert-surface);">
                            <td class="px-5 py-4">
                                <div class="font-700" style="color:var(--cert-text);">{{ $certificate->certificate_number }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-700 text-white flex-shrink-0"
                                         style="background:linear-gradient(135deg,#0057B8,#003087);">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-600" style="color:var(--cert-text);">
                                            {{ $certificate->enrollment->course->name }}
                                        </div>
                                        <div class="text-xs" style="color:var(--cert-muted);">
                                            {{ $certificate->enrollment->course->code }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-600" style="color:var(--cert-muted);">
                                {{ $certificate->issued_at->format('M d, Y') }}
                            </td>
                            <td class="px-5 py-4 text-center text-xs font-600" style="color:var(--cert-muted);">
                                @if ($certificate->expires_at)
                                    {{ $certificate->expires_at->format('M d, Y') }}
                                @else
                                    <span style="color:#6b7280;">Never</span>
                                @endif
                            </td>
                            <td class="px-5 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-700 inline-block"
                                      style="background:{{ $isExpired ? 'rgba(206,17,38,0.15)' : 'rgba(34,197,94,0.15)' }}; color:{{ $isExpired ? '#CE1126' : '#22C55E' }};">
                                    <i class="fas {{ $isExpired ? 'fa-times-circle' : 'fa-check-circle' }} mr-1" style="font-size:9px;"></i> 
                                    {{ $isExpired ? 'Expired' : 'Active' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('trainee.certificates.show', $certificate) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:var(--cert-accent-bg); color:var(--cert-accent);" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('trainee.certificates.download', $certificate) }}"
                                       class="w-8 h-8 rounded-lg flex items-center justify-center text-xs transition hover:scale-110"
                                       style="background:rgba(34,197,94,0.15); color:#22C55E;" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-16 text-center">
                                <div class="flex flex-col items-center gap-3" style="color:var(--cert-muted);">
                                    <i class="fas fa-certificate text-4xl opacity-25"></i>
                                    <p class="font-600">No certificates found</p>
                                    <p class="text-xs">You haven't earned any certificates yet.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($certificates->hasPages())
            <div class="px-5 py-4 border-t" style="border-color:var(--cert-border);">
                {{ $certificates->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
