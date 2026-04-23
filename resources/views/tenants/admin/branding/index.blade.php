@extends('layouts.app')

@section('title', 'Custom Branding — ' . config('app.name'))

@push('styles')
<style>
    .branding-hero {
        background: linear-gradient(135deg, var(--brand-primary, #003087) 0%, color-mix(in srgb, var(--brand-primary, #003087) 60%, #0057B8 40%) 100%);
        border-radius: 16px;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
        margin-bottom: 28px;
    }
    .branding-hero::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.05);
    }
    .branding-hero::after {
        content: '';
        position: absolute;
        bottom: -60px; right: 80px;
        width: 280px; height: 280px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }

    .section-card {
        background: var(--card-bg, #fff);
        border: 1px solid var(--card-border, #c5d8f5);
        border-radius: 14px;
        overflow: hidden;
        margin-bottom: 20px;
        transition: box-shadow 0.2s;
    }
    .dark .section-card {
        --card-bg: #0d1f3c;
        --card-border: #1e3a6b;
    }
    .section-card:hover {
        box-shadow: 0 4px 20px rgba(0,48,135,0.10);
    }
    .section-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--card-border, #c5d8f5);
        display: flex;
        align-items: center;
        gap: 12px;
        background: var(--section-header-bg, #f0f5ff);
    }
    .dark .section-header {
        --section-header-bg: #0a1628;
    }
    .section-icon {
        width: 36px; height: 36px;
        border-radius: 9px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px;
        flex-shrink: 0;
    }
    .section-body {
        padding: 20px;
    }

    .color-field {
        display: flex;
        align-items: center;
        gap: 10px;
        border: 1.5px solid var(--card-border, #c5d8f5);
        border-radius: 10px;
        padding: 8px 12px;
        background: var(--card-bg, #fff);
        transition: border-color 0.15s;
        cursor: pointer;
    }
    .dark .color-field { --card-bg: #0d1f3c; }
    .color-field:focus-within {
        border-color: var(--brand-primary, #003087);
        box-shadow: 0 0 0 3px rgba(0,48,135,0.10);
    }
    .color-field input[type="color"] {
        width: 32px; height: 32px;
        border: none; padding: 0;
        background: transparent;
        border-radius: 6px;
        cursor: pointer;
    }
    .color-field input[type="text"] {
        border: none; outline: none;
        background: transparent;
        font-size: 13px; font-weight: 600;
        font-family: 'Courier New', monospace;
        color: var(--text-primary, #001a4d);
        width: 80px;
    }
    .dark .color-field input[type="text"] { color: #dde8ff; }

    .logo-upload-zone {
        border: 2px dashed var(--card-border, #c5d8f5);
        border-radius: 12px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .logo-upload-zone:hover, .logo-upload-zone.dragover {
        border-color: var(--brand-primary, #003087);
        background: rgba(0,48,135,0.03);
    }
    .logo-upload-zone input[type="file"] {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer; width: 100%; height: 100%;
    }

    .preview-bar {
        border-radius: 12px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 14px;
        transition: background 0.3s, border-color 0.3s;
        border: 1px solid transparent;
    }

    .brand-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid var(--card-border, #c5d8f5);
        border-radius: 10px;
        font-size: 14px;
        background: var(--card-bg, #fff);
        color: var(--text-primary, #001a4d);
        transition: border-color 0.15s, box-shadow 0.15s;
        outline: none;
    }
    .dark .brand-input {
        --card-bg: #0a1628;
        --text-primary: #dde8ff;
        --card-border: #1e3a6b;
    }
    .brand-input:focus {
        border-color: var(--brand-primary, #003087);
        box-shadow: 0 0 0 3px rgba(0,48,135,0.10);
    }

    .btn-save {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 11px 28px;
        border-radius: 10px;
        font-size: 14px; font-weight: 700;
        color: #fff;
        border: none; cursor: pointer;
        background: linear-gradient(135deg, var(--brand-primary, #003087) 0%, color-mix(in srgb, var(--brand-primary, #003087) 60%, #0057B8 40%) 100%);
        box-shadow: 0 4px 14px rgba(0,48,135,0.25);
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(0,48,135,0.35);
    }
    .btn-save:active { transform: translateY(0); }

    .btn-danger {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 12px; font-weight: 600;
        color: #CE1126;
        border: 1.5px solid rgba(206,17,38,0.25);
        cursor: pointer; background: rgba(206,17,38,0.06);
        transition: all 0.15s;
    }
    .btn-danger:hover {
        background: rgba(206,17,38,0.12);
        border-color: rgba(206,17,38,0.5);
    }

    .plan-badge {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 3px 10px; border-radius: 20px;
        font-size: 11px; font-weight: 700; letter-spacing: 0.5px;
        background: linear-gradient(135deg, #F5C518, #e0a800);
        color: #1a1a00;
    }
</style>
@endpush

@section('content')
@php
    $tenant       = tenancy()->tenant;
    $colorPrimary = $tenant->brand_color_primary ?? '#003087';
    $colorAccent  = $tenant->brand_color_accent  ?? '#CE1126';
    $brandName    = $tenant->brand_name    ?? config('app.name', 'TCMS');
    $brandTagline = $tenant->brand_tagline ?? 'Skills Development Authority';
    $brandLogo    = $brandLogo ?? asset('assets/app_logo.PNG');
@endphp

{{--
    ╔══════════════════════════════════════════════════════════════════╗
    ║  RESET LOGO FORM — must live OUTSIDE the main form.             ║
    ║  HTML does not allow nested <form> elements. The inner form     ║
    ║  is silently ignored by browsers, causing the outer form's      ║
    ║  action/method to be used instead — which produced the          ║
    ║  MethodNotAllowedHttpException.                                 ║
    ║                                                                  ║
    ║  The Reset button below uses onclick to submit this form by ID. ║
    ╚══════════════════════════════════════════════════════════════════╝
--}}
@if($tenant->brand_logo)
    <form id="resetLogoForm"
          action="{{ route('admin.branding.logo.reset') }}"
          method="POST"
          style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endif

<div x-data="brandingPage()" x-init="init()">

    {{-- Hero --}}
    <div class="branding-hero">
        <div class="relative z-10">
            <div class="flex items-center gap-3 mb-1">
                <span class="plan-badge">⚡ Premium</span>
            </div>
            <h1 class="text-2xl font-black text-white mb-1">Custom Branding</h1>
            <p class="text-sm" style="color: rgba(255,255,255,0.65);">
                Personalize your training center's appearance — logo, colors, and name.
            </p>
        </div>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 px-4 py-3 mb-5 text-sm font-600"
             style="background: #f0fdf4; border: 1px solid #86efac; color: #15803d; border-radius: 10px;">
            <i class="fas fa-check-circle"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="flex items-start gap-3 px-4 py-3 mb-5 text-sm"
             style="background: #fff0f2; border: 1px solid #fca5a5; color: #b91c1c; border-radius: 10px;">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <ul class="list-none m-0 p-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Main save form --}}
    <form action="{{ route('admin.branding.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Left column --}}
            <div class="lg:col-span-2 space-y-5">

                {{-- Identity --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: #e8f0fb; color: #0057B8;">
                            <i class="fas fa-id-card"></i>
                        </div>
                        <div>
                            <div class="text-sm font-700" style="color: var(--text-primary, #001a4d);">Identity</div>
                            <div class="text-xs" style="color: #5a7aaa;">Name and tagline shown in the navigation bar</div>
                        </div>
                    </div>
                    <div class="section-body space-y-4">
                        <div>
                            <label class="block text-xs font-700 mb-1.5"
                                   style="color: #5a7aaa; text-transform: uppercase; letter-spacing: 0.06em;">
                                Training Center Name
                            </label>
                            <input type="text"
                                   name="brand_name"
                                   class="brand-input"
                                   placeholder="{{ config('app.name', 'TCMS') }}"
                                   value="{{ old('brand_name', $tenant->brand_name) }}"
                                   @input="preview.name = $event.target.value || defaultName" />
                        </div>
                        <div>
                            <label class="block text-xs font-700 mb-1.5"
                                   style="color: #5a7aaa; text-transform: uppercase; letter-spacing: 0.06em;">
                                Tagline
                            </label>
                            <input type="text"
                                   name="brand_tagline"
                                   class="brand-input"
                                   placeholder="Skills Development Authority"
                                   value="{{ old('brand_tagline', $tenant->brand_tagline) }}"
                                   @input="preview.tagline = $event.target.value || defaultTagline" />
                        </div>
                    </div>
                </div>

                {{-- Logo --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: #fef9ec; color: #d4a800;">
                            <i class="fas fa-image"></i>
                        </div>
                        <div>
                            <div class="text-sm font-700" style="color: var(--text-primary, #001a4d);">Logo</div>
                            <div class="text-xs" style="color: #5a7aaa;">PNG, JPG or SVG · Max 2 MB · Recommended 128×128 px</div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="flex items-start gap-5 flex-wrap">

                            {{-- Current logo preview --}}
                            <div class="flex-shrink-0">
                                <div class="w-20 h-20 bg-white border-2 flex items-center justify-center overflow-hidden shadow"
                                     style="border-color: var(--card-border, #c5d8f5); border-radius: 14px;">
                                    <img id="logoPreviewImg"
                                         src="{{ $brandLogo }}"
                                         alt="Current logo"
                                         class="w-14 h-14 object-contain" />
                                </div>

                                {{--
                                    type="button" prevents this from submitting the enclosing
                                    save form. The onclick targets the separate hidden form above.
                                --}}
                                @if($tenant->brand_logo)
                                    <button type="button"
                                            class="btn-danger w-full justify-center mt-2"
                                            onclick="if(confirm('Reset logo to default?')) document.getElementById('resetLogoForm').submit();">
                                        <i class="fas fa-trash-alt"></i> Reset
                                    </button>
                                @endif
                            </div>

                            {{-- Upload zone --}}
                            <div class="flex-1 min-w-48">
                                <div class="logo-upload-zone"
                                     @dragover.prevent="$el.classList.add('dragover')"
                                     @dragleave="$el.classList.remove('dragover')"
                                     @drop.prevent="handleLogoDrop($event)">
                                    <input type="file" name="brand_logo" accept="image/*"
                                           @change="handleLogoChange($event)" />
                                    <i class="fas fa-cloud-upload-alt text-2xl mb-2" style="color: #5a7aaa;"></i>
                                    <p class="text-sm font-600" style="color: #1a3a6b;">
                                        Drop your logo here or <span style="color: #0057B8;">browse</span>
                                    </p>
                                    <p class="text-xs mt-1" style="color: #5a7aaa;"
                                       x-text="logoFileName || 'No file chosen'"></p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Colors --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: #f3f0ff; color: #7c3aed;">
                            <i class="fas fa-palette"></i>
                        </div>
                        <div>
                            <div class="text-sm font-700" style="color: var(--text-primary, #001a4d);">Brand Colors</div>
                            <div class="text-xs" style="color: #5a7aaa;">Used for the navigation bar and accent elements</div>
                        </div>
                    </div>
                    <div class="section-body">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                            <div>
                                <label class="block text-xs font-700 mb-1.5"
                                       style="color: #5a7aaa; text-transform: uppercase; letter-spacing: 0.06em;">
                                    Primary Color <span class="normal-case font-400">(navbar background)</span>
                                </label>
                                <div class="color-field">
                                    <input type="color"
                                           id="primaryColorPicker"
                                           value="{{ old('brand_color_primary', $colorPrimary) }}"
                                           @input="syncColor('primary', $event.target.value)" />
                                    <input type="text"
                                           id="primaryColorText"
                                           name="brand_color_primary"
                                           value="{{ old('brand_color_primary', $colorPrimary) }}"
                                           maxlength="7"
                                           placeholder="#003087"
                                           @input="syncColorFromText('primary', $event.target.value)" />
                                    <button type="button"
                                            class="ml-auto text-xs px-2 py-1 font-600"
                                            style="background: rgba(0,48,135,0.08); color: #5a7aaa; border-radius: 6px;"
                                            @click="resetColor('primary')">
                                        Reset
                                    </button>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-700 mb-1.5"
                                       style="color: #5a7aaa; text-transform: uppercase; letter-spacing: 0.06em;">
                                    Accent Color <span class="normal-case font-400">(buttons &amp; highlights)</span>
                                </label>
                                <div class="color-field">
                                    <input type="color"
                                           id="accentColorPicker"
                                           value="{{ old('brand_color_accent', $colorAccent) }}"
                                           @input="syncColor('accent', $event.target.value)" />
                                    <input type="text"
                                           id="accentColorText"
                                           name="brand_color_accent"
                                           value="{{ old('brand_color_accent', $colorAccent) }}"
                                           maxlength="7"
                                           placeholder="#CE1126"
                                           @input="syncColorFromText('accent', $event.target.value)" />
                                    <button type="button"
                                            class="ml-auto text-xs px-2 py-1 font-600"
                                            style="background: rgba(0,48,135,0.08); color: #5a7aaa; border-radius: 6px;"
                                            @click="resetColor('accent')">
                                        Reset
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </div>

            {{-- Right column --}}
            <div class="space-y-5">

                {{-- Live Preview --}}
                <div class="section-card">
                    <div class="section-header">
                        <div class="section-icon" style="background: #f0fdf4; color: #16a34a;">
                            <i class="fas fa-eye"></i>
                        </div>
                        <div>
                            <div class="text-sm font-700" style="color: var(--text-primary, #001a4d);">Live Preview</div>
                            <div class="text-xs" style="color: #5a7aaa;">Updates as you make changes</div>
                        </div>
                    </div>
                    <div class="section-body">

                        <div class="preview-bar mb-3"
                             :style="`background: linear-gradient(90deg, ${preview.primary} 0%, ${preview.primary}cc 100%); border-color: ${preview.accent};`"
                             style="border-width: 1px;">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center flex-shrink-0 overflow-hidden">
                                <img :src="preview.logo" class="w-6 h-6 object-contain" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-800 text-white leading-tight truncate" x-text="preview.name"></div>
                                <div class="text-xs truncate" style="color: rgba(255,255,255,0.6);" x-text="preview.tagline"></div>
                            </div>
                            <div class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
                                 :style="`background: ${preview.accent};`">
                                <i class="fas fa-bars text-white" style="font-size: 9px;"></i>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 mb-3">
                            <div class="flex-1 h-8 transition-all duration-300 flex items-center justify-center"
                                 :style="`background: ${preview.primary}; border-radius: 8px;`">
                                <span class="text-white text-xs font-700" x-text="preview.primary"></span>
                            </div>
                            <div class="flex-1 h-8 transition-all duration-300 flex items-center justify-center"
                                 :style="`background: ${preview.accent}; border-radius: 8px;`">
                                <span class="text-white text-xs font-700" x-text="preview.accent"></span>
                            </div>
                        </div>

                        <div class="p-3 text-center"
                             style="background: var(--card-bg, #f0f5ff); border-radius: 10px;">
                            <span class="inline-flex items-center gap-2 px-4 py-2 text-white text-xs font-700 transition-all duration-300"
                                  :style="`background: ${preview.accent}; border-radius: 8px;`">
                                <i class="fas fa-save"></i> Sample Button
                            </span>
                        </div>

                    </div>
                </div>

                {{-- Save --}}
                <div class="section-card">
                    <div class="section-body">
                        <button type="submit" class="btn-save w-full justify-center">
                            <i class="fas fa-save"></i> Save Branding
                        </button>
                        <p class="text-xs text-center mt-3" style="color: #5a7aaa;">
                            Changes apply immediately for all users on your tenant.
                        </p>
                    </div>
                </div>

                {{-- Tips --}}
                <div class="p-4 text-xs space-y-2"
                     style="background: #e8f0fb; border: 1px solid #c5d8f5; border-radius: 12px; color: #1a3a6b;">
                    <div class="font-700 flex items-center gap-2">
                        <i class="fas fa-info-circle" style="color: #0057B8;"></i> Tips
                    </div>
                    <ul class="space-y-1 pl-4 list-disc">
                        <li>Use a square PNG logo with a transparent background for best results.</li>
                        <li>Dark primary colors work best for the navigation bar readability.</li>
                        <li>Leave a field blank to use the system default.</li>
                    </ul>
                </div>

            </div>
        </div>

    </form>{{-- end main save form --}}
</div>

@push('scripts')
<script>
function brandingPage() {
    return {
        defaultName:    @json($brandName),
        defaultTagline: @json($brandTagline),
        defaultPrimary: '#003087',
        defaultAccent:  '#CE1126',

        logoFileName: '',
        preview: {
            name:    @json($brandName),
            tagline: @json($brandTagline),
            logo:    @json($brandLogo),
            primary: @json($colorPrimary),
            accent:  @json($colorAccent),
        },

        init() {
            this.$watch('preview.primary', val => {
                document.documentElement.style.setProperty('--brand-primary', val);
            });
            this.$watch('preview.accent', val => {
                document.documentElement.style.setProperty('--brand-accent', val);
            });
        },

        syncColor(type, value) {
            if (type === 'primary') {
                this.preview.primary = value;
                document.getElementById('primaryColorText').value = value;
            } else {
                this.preview.accent = value;
                document.getElementById('accentColorText').value = value;
            }
        },

        syncColorFromText(type, value) {
            if (!/^#[0-9A-Fa-f]{6}$/.test(value)) return;
            if (type === 'primary') {
                this.preview.primary = value;
                document.getElementById('primaryColorPicker').value = value;
            } else {
                this.preview.accent = value;
                document.getElementById('accentColorPicker').value = value;
            }
        },

        resetColor(type) {
            if (type === 'primary') {
                this.syncColor('primary', this.defaultPrimary);
            } else {
                this.syncColor('accent', this.defaultAccent);
            }
        },

        handleLogoChange(event) {
            const file = event.target.files[0];
            if (!file) return;
            this.logoFileName = file.name;
            const reader = new FileReader();
            reader.onload = e => {
                this.preview.logo = e.target.result;
                document.getElementById('logoPreviewImg').src = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        handleLogoDrop(event) {
            const file = event.dataTransfer.files[0];
            if (!file || !file.type.startsWith('image/')) return;
            const dt = new DataTransfer();
            dt.items.add(file);
            document.querySelector('input[name="brand_logo"]').files = dt.files;
            this.handleLogoChange({ target: { files: [file] } });
        },
    }
}
</script>
@endpush
@endsection