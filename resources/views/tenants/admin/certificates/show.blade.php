@extends('layouts.app')

@section('title', 'Certificate Details')

@section('content')
<style>
    /* ══════════════════════════════════════════
       CERTIFICATE DETAIL DESIGN TOKENS — TESDA Theme
    ══════════════════════════════════════════ */
    :root {
        --certd-surface:      #ffffff;
        --certd-surface2:     #f0f5ff;
        --certd-border:       #c5d8f5;
        --certd-text:         #001a4d;
        --certd-text-sec:     #1a3a6b;
        --certd-muted:        #5a7aaa;
        --certd-accent:       #0057B8;
        --certd-accent-bg:    #e8f0fb;
        --certd-primary:      #003087;
        --certd-red:          #CE1126;
        --certd-red-bg:       #fff0f2;
    }
    .dark {
        --certd-surface:      #0a1628;
        --certd-surface2:     #0d1f3c;
        --certd-border:       #1e3a6b;
        --certd-text:         #dde8ff;
        --certd-text-sec:     #adc4f0;
        --certd-muted:        #6b8abf;
        --certd-accent-bg:    rgba(0,87,184,0.15);
        --certd-primary:      #5b9cf6;
        --certd-red-bg:       rgba(206,17,38,0.12);
    }
</style>
<div class="max-w-3xl mx-auto space-y-6">

    {{-- Page Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.certificates.index') }}"
           class="w-9 h-9 rounded-xl flex items-center justify-center border text-sm transition"
           style="border-color:var(--certd-border); color:var(--certd-muted);">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--certd-primary);">
                <i class="fas fa-certificate mr-2" style="color:var(--certd-red);"></i> Certificate Details
            </h1>
            <p class="text-sm mt-0.5" style="color:var(--certd-muted);">Viewing certificate {{ $certificate->certificate_number }}</p>
        </div>
    </div>

    {{-- Certificate Card --}}
    <div class="rounded-2xl border overflow-hidden"
         style="background:var(--certd-surface); border-color:var(--certd-border); box-shadow:0 4px 24px rgba(0,48,135,0.07);">

        <div class="h-1" style="background:linear-gradient(90deg,#CE1126 33%,#0057B8 33% 66%,#F5C518 66%);"></div>

        {{-- Header --}}
        <div class="p-8 flex flex-col sm:flex-row items-start sm:items-center gap-6"
             style="background: linear-gradient(135deg, #003087 0%, #0057B8 100%); position:relative; overflow:hidden;">
            <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(255,255,255,0.05);"></div>
            <div style="position:absolute;bottom:-40px;left:-20px;width:120px;height:120px;border-radius:50%;background:rgba(245,197,24,0.07);"></div>

            <div class="w-20 h-20 rounded-2xl flex items-center justify-center text-3xl font-900 text-white flex-shrink-0"
                 style="background:rgba(255,255,255,0.15); border:2px solid rgba(255,255,255,0.20); position:relative; z-index:1;">
                <i class="fas fa-certificate"></i>
            </div>

            <div style="position:relative;z-index:1;">
                <div class="text-xl font-800 text-white">{{ $certificate->certificate_number }}</div>
                <div class="text-sm mt-0.5" style="color:rgba(255,255,255,0.65);">{{ $certificate->enrollment->course->name }}</div>
                <div class="flex flex-wrap gap-2 mt-3">
                    @php
                        $isExpired = $certificate->expires_at && $certificate->expires_at < now();
                    @endphp
                    <span class="px-2.5 py-1 rounded-lg text-xs font-700"
                          style="background:{{ $isExpired ? 'rgba(206,17,38,0.25)' : 'rgba(34,197,94,0.25)' }}; border:1px solid {{ $isExpired ? 'rgba(206,17,38,0.40)' : 'rgba(34,197,94,0.40)' }}; color:#fff;">
                        <i class="fas {{ $isExpired ? 'fa-times-circle' : 'fa-check-circle' }} mr-1" style="font-size:9px;"></i> {{ $isExpired ? 'Expired' : 'Active' }}
                    </span>
                </div>
            </div>

            <div class="sm:ml-auto flex gap-2" style="position:relative;z-index:1;">
                <a href="{{ route('admin.certificates.edit', $certificate) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                   style="background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.22); color:#fff;">
                    <i class="fas fa-pen text-xs"></i> Edit
                </a>
            </div>
        </div>

        {{-- Details --}}
        <div class="p-8 space-y-8">
            {{-- Trainee & Course Info --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Trainee --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:rgba(245,197,24,0.15); color:#F5C518;">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Trainee</div>
                        <div class="text-sm font-600" style="color:var(--certd-text);">
                            {{ $certificate->enrollment->trainee->name }}
                        </div>
                        <div class="text-xs mt-1" style="color:var(--certd-muted);">
                            {{ $certificate->enrollment->trainee->email }}
                        </div>
                    </div>
                </div>

                {{-- Course --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:var(--certd-accent-bg); color:var(--certd-accent);">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Course</div>
                        <div class="text-sm font-600" style="color:var(--certd-text);">
                            {{ $certificate->enrollment->course->name }}
                        </div>
                        <div class="text-xs mt-1" style="color:var(--certd-muted);">
                            {{ $certificate->enrollment->course->code }} • {{ $certificate->enrollment->course->duration_hours }} hours
                        </div>
                    </div>
                </div>
            </div>

            <hr style="border-color:#c5d8f5; margin:0;">

            {{-- Certificate Details --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                {{-- Certificate Number --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:var(--certd-red-bg); color:var(--certd-red);">
                        <i class="fas fa-hashtag"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Certificate Number</div>
                        <div class="font-mono text-sm font-700" style="color:var(--certd-text);">
                            {{ $certificate->certificate_number }}
                        </div>
                    </div>
                </div>

                {{-- Issued Date --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:var(--certd-red-bg); color:var(--certd-red);">
                        <i class="fas fa-calendar-plus"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Issued Date</div>
                        <div class="text-sm font-600" style="color:var(--certd-text);">
                            {{ $certificate->issued_at->format('F d, Y') }}
                        </div>
                        <div class="text-xs mt-1" style="color:var(--certd-muted);">
                            {{ $certificate->issued_at->format('l') }}
                        </div>
                    </div>
                </div>

                {{-- Expiry Date --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:{{ $certificate->expires_at ? 'rgba(245,197,24,0.15)' : 'rgba(107,114,128,0.15)' }}; color:{{ $certificate->expires_at ? '#F5C518' : '#6B7280' }};">
                        <i class="fas {{ $certificate->expires_at ? 'fa-calendar-times' : 'fa-infinity' }}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Expiry Date</div>
                        @if ($certificate->expires_at)
                            <div class="text-sm font-600" style="color:var(--certd-text);">
                                {{ $certificate->expires_at->format('F d, Y') }}
                            </div>
                            <div class="text-xs mt-1" style="color:var(--certd-muted);">
                                {{ $certificate->expires_at->format('l') }}
                            </div>
                        @else
                            <div class="text-sm font-600" style="color:var(--certd-text);">
                                No Expiry
                            </div>
                            <div class="text-xs mt-1" style="color:var(--certd-muted);">
                                This certificate does not expire
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Status --}}
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center text-sm flex-shrink-0"
                         style="background:{{ $isExpired ? 'rgba(206,17,38,0.15)' : 'rgba(34,197,94,0.15)' }}; color:{{ $isExpired ? '#CE1126' : '#22C55E' }};">
                        <i class="fas {{ $isExpired ? 'fa-times-circle' : 'fa-check-circle' }}"></i>
                    </div>
                    <div class="flex-1">
                        <div class="text-xs font-700 uppercase tracking-wide mb-0.5" style="color:var(--certd-muted);">Status</div>
                        <div class="text-sm font-600" style="color:{{ $isExpired ? '#CE1126' : '#22C55E' }};">
                            {{ $isExpired ? 'Expired' : 'Active' }}
                        </div>
                        @if ($isExpired)
                            <div class="text-xs mt-1" style="color:var(--certd-muted);">
                                Expired on {{ $certificate->expires_at->format('M d, Y') }}
                            </div>
                        @else
                            @if ($certificate->expires_at)
                                <div class="text-xs mt-1" style="color:var(--certd-muted);">
                                    {{ $certificate->expires_at->diffForHumans() }}
                                </div>
                            @else
                                <div class="text-xs mt-1" style="color:var(--certd-muted);">
                                    Valid indefinitely
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <hr style="border-color:#c5d8f5; margin:0;">

            {{-- Metadata --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <div class="text-xs font-700 uppercase tracking-wide mb-1" style="color:var(--certd-muted);">Created</div>
                    <div class="text-sm font-600" style="color:var(--certd-text);">
                        {{ $certificate->created_at->format('F d, Y') }}
                    </div>
                    <div class="text-xs mt-0.5" style="color:var(--certd-muted);">
                        {{ $certificate->created_at->format('h:i A') }}
                    </div>
                </div>
                <div>
                    <div class="text-xs font-700 uppercase tracking-wide mb-1" style="color:var(--certd-muted);">Updated</div>
                    <div class="text-sm font-600" style="color:var(--certd-text);">
                        {{ $certificate->updated_at->format('F d, Y') }}
                    </div>
                    <div class="text-xs mt-0.5" style="color:var(--certd-muted);">
                        {{ $certificate->updated_at->format('h:i A') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Action Buttons --}}
        <div class="flex flex-col sm:flex-row flex-wrap gap-3">
            <a href="{{ route('admin.certificates.preview', $certificate) }}"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
            style="background:linear-gradient(135deg,#F5C518,#CE1126); box-shadow:0 3px 12px rgba(245,197,24,0.25);"
            target="_blank">
                <i class="fas fa-eye"></i> View Certificate
            </a>
            <a href="{{ route('admin.certificates.download', $certificate) }}"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
            style="background:linear-gradient(135deg,#22C55E,#16A34A); box-shadow:0 3px 12px rgba(34,197,94,0.25);">
                <i class="fas fa-download"></i> Download
            </a>
            <a href="{{ route('admin.certificates.edit', $certificate) }}"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-white text-sm font-700 transition hover:-translate-y-0.5"
            style="background:linear-gradient(135deg,#0057B8,#003087); box-shadow:0 3px 12px rgba(0,87,184,0.25);">
                <i class="fas fa-pen"></i> Edit
            </a>
            <form method="POST" action="{{ route('admin.certificates.destroy', $certificate) }}"
                onsubmit="return confirm('Delete this certificate? This action cannot be undone.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-700 transition hover:-translate-y-0.5"
                        style="background:linear-gradient(135deg,#CE1126,#a00d1e); color:#fff; box-shadow:0 3px 12px rgba(206,17,38,0.25);">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
            <a href="{{ route('admin.certificates.index') }}"
            class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl text-sm font-600 border transition"
            style="border-color:var(--certd-border); color:var(--certd-muted);">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

</div>
@endsection
