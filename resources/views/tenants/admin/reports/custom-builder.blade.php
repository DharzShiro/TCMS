@extends('layouts.app')

@section('title', 'Custom Report Builder — TCMS')

@push('styles')
<style>
    /* ══════════════════════════════════════════════════════
       CUSTOM REPORT BUILDER — TESDA Design System
       Navy: #003087 / #1a3a6b   Gold: #c9a84c   Red: #CE1126
    ══════════════════════════════════════════════════════ */

    :root {
        --crb-navy:      #003087;
        --crb-navy-dk:   #1a3a6b;
        --crb-blue:      #0057B8;
        --crb-blue-lt:   #e8f0fb;
        --crb-pale:      #f0f5ff;
        --crb-gold:      #c9a84c;
        --crb-gold-lt:   #fef9ec;
        --crb-red:       #CE1126;
        --crb-red-lt:    #fff0f2;
        --crb-border:    #c5d8f5;
        --crb-muted:     #5a7aaa;
        --crb-text:      #001a4d;
        --crb-surface:   #ffffff;
        --crb-surface2:  #f8fbff;
        --crb-shadow:    0 2px 12px rgba(0,48,135,.10), 0 1px 3px rgba(0,48,135,.06);
        --crb-shadow-lg: 0 8px 32px rgba(0,48,135,.14), 0 2px 8px rgba(0,48,135,.08);
    }

    .dark {
        --crb-border:   #1e3a6b;
        --crb-muted:    #6b8abf;
        --crb-text:     #dde8ff;
        --crb-surface:  #0a1628;
        --crb-surface2: #0d1f3c;
        --crb-blue-lt:  rgba(0,87,184,.12);
        --crb-pale:     rgba(0,87,184,.08);
        --crb-gold-lt:  rgba(201,168,76,.08);
        --crb-red-lt:   rgba(206,17,38,.10);
        --crb-shadow:   0 2px 12px rgba(0,0,0,.35);
        --crb-shadow-lg:0 8px 32px rgba(0,0,0,.45);
    }

    /* ── Page header ── */
    .crb-page-header {
        background: linear-gradient(135deg, var(--crb-navy) 0%, var(--crb-blue) 100%);
        border-radius: 16px;
        padding: 28px 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
    }
    .crb-page-header::before {
        content: '';
        position: absolute; inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
    }
    .crb-page-header .tricolor {
        position: absolute; bottom: 0; left: 0; right: 0; height: 4px;
        background: linear-gradient(90deg, #CE1126 33%, #0057B8 33% 66%, #c9a84c 66%);
    }

    /* ── Panel / card ── */
    .crb-panel {
        background: var(--crb-surface);
        border: 1px solid var(--crb-border);
        border-radius: 14px;
        box-shadow: var(--crb-shadow);
        overflow: hidden;
    }
    .crb-panel-header {
        padding: 16px 20px;
        border-bottom: 1px solid var(--crb-border);
        background: var(--crb-surface2);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .crb-panel-header-icon {
        width: 32px; height: 32px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px;
    }
    .crb-panel-header-icon.blue  { background: var(--crb-blue-lt); color: var(--crb-blue); }
    .crb-panel-header-icon.gold  { background: var(--crb-gold-lt); color: var(--crb-gold); }
    .crb-panel-header-icon.red   { background: var(--crb-red-lt);  color: var(--crb-red);  }
    .crb-panel-header-icon.navy  { background: var(--crb-pale);    color: var(--crb-navy); }
    .crb-panel-title {
        font-size: 13px; font-weight: 700;
        color: var(--crb-text); letter-spacing: .2px;
    }
    .crb-panel-body { padding: 20px; }

    /* ── Step badge ── */
    .crb-step {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%;
        font-size: 11px; font-weight: 800;
        background: var(--crb-navy); color: #fff;
        flex-shrink: 0;
    }

    /* ── Source cards ── */
    .crb-source-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 10px;
    }
    .crb-source-card {
        border: 2px solid var(--crb-border);
        border-radius: 10px;
        padding: 14px 10px;
        text-align: center;
        cursor: pointer;
        transition: all .18s ease;
        background: var(--crb-surface);
    }
    .crb-source-card:hover {
        border-color: var(--crb-blue);
        background: var(--crb-blue-lt);
        transform: translateY(-2px);
        box-shadow: var(--crb-shadow);
    }
    .crb-source-card.selected {
        border-color: var(--crb-blue);
        background: var(--crb-blue-lt);
        box-shadow: 0 0 0 3px rgba(0,87,184,.15);
    }
    .crb-source-card .source-icon {
        font-size: 22px; margin-bottom: 8px;
        color: var(--crb-muted);
        transition: color .18s;
    }
    .crb-source-card.selected .source-icon,
    .crb-source-card:hover .source-icon { color: var(--crb-blue); }
    .crb-source-label {
        font-size: 11.5px; font-weight: 600;
        color: var(--crb-text); line-height: 1.3;
    }

    /* ── Column checkboxes ── */
    .crb-col-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
        gap: 8px;
    }
    .crb-col-item {
        display: flex; align-items: center; gap: 8px;
        padding: 8px 12px;
        border: 1px solid var(--crb-border);
        border-radius: 8px;
        cursor: pointer;
        transition: all .15s ease;
        background: var(--crb-surface);
        user-select: none;
    }
    .crb-col-item:hover { border-color: var(--crb-blue); background: var(--crb-blue-lt); }
    .crb-col-item.checked { border-color: var(--crb-blue); background: var(--crb-blue-lt); }
    .crb-col-item input[type=checkbox] { accent-color: var(--crb-blue); }
    .crb-col-item label {
        font-size: 12px; font-weight: 500; color: var(--crb-text);
        cursor: pointer;
    }

    /* ── Filters ── */
    .crb-filter-row {
        display: grid;
        grid-template-columns: 1fr 140px 1fr auto;
        gap: 8px; align-items: center;
        padding: 10px 12px;
        background: var(--crb-surface2);
        border: 1px solid var(--crb-border);
        border-radius: 8px;
        margin-bottom: 8px;
    }
    .crb-filter-row.between { grid-template-columns: 1fr 140px 1fr 1fr auto; }

    /* ── Form controls ── */
    .crb-select, .crb-input {
        width: 100%;
        padding: 8px 12px;
        border: 1px solid var(--crb-border);
        border-radius: 8px;
        font-size: 12.5px;
        color: var(--crb-text);
        background: var(--crb-surface);
        outline: none;
        transition: border-color .15s, box-shadow .15s;
        appearance: none; -webkit-appearance: none;
    }
    .crb-select { background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%235a7aaa' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 8px center; background-size: 16px; padding-right: 32px; }
    .crb-select:focus, .crb-input:focus {
        border-color: var(--crb-blue);
        box-shadow: 0 0 0 3px rgba(0,87,184,.12);
    }
    .dark .crb-select, .dark .crb-input {
        background-color: var(--crb-surface2);
        color: var(--crb-text);
        border-color: var(--crb-border);
    }

    /* ── Buttons ── */
    .crb-btn {
        display: inline-flex; align-items: center; gap: 7px;
        padding: 9px 18px; border-radius: 9px;
        font-size: 12.5px; font-weight: 600;
        cursor: pointer; border: none;
        transition: all .18s ease;
        text-decoration: none;
    }
    .crb-btn-primary {
        background: linear-gradient(135deg, var(--crb-navy) 0%, var(--crb-blue) 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(0,87,184,.25);
    }
    .crb-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(0,87,184,.35); }
    .crb-btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    .crb-btn-gold {
        background: linear-gradient(135deg, #b8922a 0%, var(--crb-gold) 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(201,168,76,.30);
    }
    .crb-btn-gold:hover { transform: translateY(-1px); box-shadow: 0 4px 16px rgba(201,168,76,.45); }
    .crb-btn-gold:disabled { opacity: .5; cursor: not-allowed; transform: none; }

    .crb-btn-red {
        background: linear-gradient(135deg, #a5091a 0%, var(--crb-red) 100%);
        color: #fff;
        box-shadow: 0 2px 8px rgba(206,17,38,.25);
    }
    .crb-btn-red:hover { transform: translateY(-1px); }

    .crb-btn-outline {
        background: var(--crb-surface);
        color: var(--crb-muted);
        border: 1px solid var(--crb-border);
    }
    .crb-btn-outline:hover { border-color: var(--crb-blue); color: var(--crb-blue); background: var(--crb-blue-lt); }

    .crb-btn-sm { padding: 6px 12px; font-size: 11.5px; }

    /* ── Sort controls ── */
    .crb-sort-row {
        display: flex; gap: 10px; align-items: center;
        flex-wrap: wrap;
    }
    .crb-sort-row label { font-size: 12px; font-weight: 600; color: var(--crb-muted); white-space: nowrap; }
    .crb-sort-row .crb-select { max-width: 200px; }

    /* ── Preview table ── */
    .crb-preview-wrap {
        overflow-x: auto;
        border-radius: 10px;
        border: 1px solid var(--crb-border);
    }
    .crb-table {
        width: 100%; border-collapse: collapse; font-size: 12px;
    }
    .crb-table thead tr {
        background: var(--crb-navy);
    }
    .crb-table thead th {
        padding: 10px 14px;
        text-align: left;
        font-weight: 700; font-size: 11px;
        color: #fff;
        letter-spacing: .4px; text-transform: uppercase;
        white-space: nowrap;
        border-right: 1px solid rgba(255,255,255,.08);
    }
    .crb-table thead th:last-child { border-right: none; }
    .crb-table tbody tr:nth-child(even) { background: var(--crb-pale); }
    .crb-table tbody tr:hover { background: var(--crb-blue-lt); }
    .crb-table tbody td {
        padding: 9px 14px;
        color: var(--crb-text);
        border-bottom: 1px solid var(--crb-border);
        white-space: nowrap;
        max-width: 240px;
        overflow: hidden; text-overflow: ellipsis;
    }
    .crb-table tfoot td {
        padding: 10px 14px;
        background: var(--crb-navy);
        color: var(--crb-gold);
        font-size: 11px; font-weight: 700;
        font-style: italic;
    }

    /* ── Export buttons row ── */
    .crb-export-row {
        display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
    }

    /* ── Loading spinner ── */
    .crb-spinner {
        display: inline-block;
        width: 16px; height: 16px;
        border: 2px solid rgba(255,255,255,.3);
        border-top-color: #fff;
        border-radius: 50%;
        animation: crb-spin .7s linear infinite;
    }
    @keyframes crb-spin { to { transform: rotate(360deg); } }

    /* ── Empty / placeholder ── */
    .crb-empty {
        text-align: center; padding: 48px 20px;
        color: var(--crb-muted);
    }
    .crb-empty i { font-size: 36px; opacity: .3; display: block; margin-bottom: 12px; }
    .crb-empty p { font-size: 13px; }

    /* ── Premium lock banner ── */
    .crb-lock-banner {
        border-radius: 12px;
        padding: 16px 20px;
        display: flex; align-items: center; gap: 14px;
        background: linear-gradient(135deg, rgba(201,168,76,.12), rgba(201,168,76,.05));
        border: 1px solid rgba(201,168,76,.30);
        margin-bottom: 16px;
    }
    .crb-lock-banner i { font-size: 20px; color: var(--crb-gold); }
    .crb-lock-banner p { font-size: 12.5px; color: var(--crb-text); }
    .crb-lock-banner strong { color: var(--crb-gold); }

    /* ── Badge ── */
    .crb-badge {
        display: inline-flex; align-items: center;
        padding: 2px 8px; border-radius: 20px;
        font-size: 10px; font-weight: 700; letter-spacing: .5px;
        text-transform: uppercase;
    }
    .crb-badge-gold { background: rgba(201,168,76,.15); color: #b8922a; border: 1px solid rgba(201,168,76,.3); }
    .dark .crb-badge-gold { color: var(--crb-gold); }
    .crb-badge-blue { background: var(--crb-blue-lt); color: var(--crb-blue); border: 1px solid rgba(0,87,184,.2); }

    /* ── Alert ── */
    .crb-alert {
        padding: 12px 16px; border-radius: 8px; font-size: 12.5px;
        display: flex; align-items: flex-start; gap: 10px; margin-bottom: 16px;
    }
    .crb-alert-error { background: var(--crb-red-lt); border: 1px solid rgba(206,17,38,.25); color: var(--crb-red); }
    .crb-alert-info  { background: var(--crb-blue-lt); border: 1px solid rgba(0,87,184,.2); color: var(--crb-navy); }
    .dark .crb-alert-info { color: #5b9cf6; }

    /* ── Responsive ── */
    @media (max-width: 768px) {
        .crb-filter-row { grid-template-columns: 1fr 1fr; }
        .crb-filter-row > *:last-child { grid-column: span 2; }
        .crb-filter-row.between { grid-template-columns: 1fr 1fr; }
    }
</style>
@endpush

@section('content')
@php
    $isPremium  = $plan === 'premium';
    $isStandard = $plan === 'standard';
    $isBasic    = $plan === 'basic';
@endphp

<div x-data="reportBuilder()" x-init="init()">

    {{-- ── Page header ────────────────────────────────────────────────────── --}}
    <div class="crb-page-header">
        <div class="tricolor"></div>
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div style="background:rgba(255,255,255,.12); width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center;">
                        <i class="fas fa-wrench" style="color:#c9a84c; font-size:18px;"></i>
                    </div>
                    <div>
                        <h1 style="font-size:20px; font-weight:800; color:#fff; margin:0; line-height:1.2;">Custom Report Builder</h1>
                        <p style="font-size:11px; color:rgba(255,255,255,.65); margin:0;">Premium Feature — Build tailored reports from any data source</p>
                    </div>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="crb-badge crb-badge-gold">⚡ Premium</span>
                <a href="{{ route('admin.reports.index') }}" class="crb-btn crb-btn-outline" style="color:#fff; border-color:rgba(255,255,255,.3);">
                    <i class="fas fa-arrow-left"></i> Back to Reports
                </a>
            </div>
        </div>
    </div>

    {{-- ── Plan gate ───────────────────────────────────────────────────────── --}}
    @if($isBasic)
    <div class="crb-lock-banner">
        <i class="fas fa-lock"></i>
        <div>
            <p><strong>Custom Report Builder requires Standard or Premium.</strong> Your current plan is Basic. Upgrade to access this feature.</p>
        </div>
        <a href="{{ route('admin.subscription.index') }}" class="crb-btn crb-btn-gold" style="margin-left:auto; white-space:nowrap;">
            <i class="fas fa-arrow-up"></i> Upgrade
        </a>
    </div>
    @endif

    @if($isStandard)
    <div class="crb-lock-banner">
        <i class="fas fa-info-circle"></i>
        <div>
            <p><strong>Standard Plan:</strong> Preview up to 100 rows · CSV export only · 3,000 record cap. <strong>Upgrade to Premium</strong> for unlimited exports, Excel & PDF.</p>
        </div>
        <a href="{{ route('admin.subscription.index') }}" class="crb-btn crb-btn-outline crb-btn-sm" style="margin-left:auto; white-space:nowrap;">
            Upgrade
        </a>
    </div>
    @endif

    {{-- ── Error display ────────────────────────────────────────────────────── --}}
    <template x-if="error">
        <div class="crb-alert crb-alert-error">
            <i class="fas fa-exclamation-circle mt-0.5"></i>
            <span x-text="error"></span>
        </div>
    </template>

    <div class="grid gap-5" style="grid-template-columns: 340px 1fr;" x-bind:class="{ 'opacity-50 pointer-events-none': {{ $isBasic ? 'true' : 'false' }} }">

        {{-- ════════════════════════════════════════════════
             LEFT COLUMN — Builder controls
        ════════════════════════════════════════════════ --}}
        <div class="flex flex-col gap-5">

            {{-- Step 1: Data Source --}}
            <div class="crb-panel">
                <div class="crb-panel-header">
                    <span class="crb-step">1</span>
                    <div class="crb-panel-header-icon blue"><i class="fas fa-database"></i></div>
                    <span class="crb-panel-title">Choose Data Source</span>
                </div>
                <div class="crb-panel-body">
                    <div class="crb-source-grid">
                        @foreach($schema as $src)
                        <div class="crb-source-card"
                             :class="{ selected: config.source === '{{ $src['key'] }}' }"
                             @click="selectSource('{{ $src['key'] }}')">
                            <div class="source-icon">
                                @php
                                    $icons = [
                                        'trainees'     => 'fa-user-graduate',
                                        'trainers'     => 'fa-chalkboard-teacher',
                                        'enrollments'  => 'fa-file-signature',
                                        'attendance'   => 'fa-calendar-check',
                                        'assessments'  => 'fa-clipboard-check',
                                        'certificates' => 'fa-certificate',
                                    ];
                                @endphp
                                <i class="fas {{ $icons[$src['key']] ?? 'fa-table' }}"></i>
                            </div>
                            <div class="crb-source-label">{{ $src['label'] }}</div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Step 2: Columns --}}
            <div class="crb-panel" x-show="config.source" x-transition>
                <div class="crb-panel-header">
                    <span class="crb-step">2</span>
                    <div class="crb-panel-header-icon navy"><i class="fas fa-columns"></i></div>
                    <span class="crb-panel-title">Select Columns</span>
                    <button @click="toggleAllColumns()" class="crb-btn crb-btn-outline crb-btn-sm ml-auto">
                        <span x-text="allColumnsSelected ? 'Deselect All' : 'Select All'"></span>
                    </button>
                </div>
                <div class="crb-panel-body">
                    <div class="crb-col-grid">
                        <template x-for="col in availableColumns" :key="col.key">
                            <div class="crb-col-item" :class="{ checked: config.columns.includes(col.key) }"
                                 @click="toggleColumn(col.key)">
                                <input type="checkbox"
                                       :checked="config.columns.includes(col.key)"
                                       @click.stop="toggleColumn(col.key)">
                                <label x-text="col.label"></label>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Step 3: Filters --}}
            <div class="crb-panel" x-show="config.source" x-transition>
                <div class="crb-panel-header">
                    <span class="crb-step">3</span>
                    <div class="crb-panel-header-icon gold"><i class="fas fa-filter"></i></div>
                    <span class="crb-panel-title">Filters</span>
                    <button @click="addFilter()" class="crb-btn crb-btn-outline crb-btn-sm ml-auto">
                        <i class="fas fa-plus"></i> Add
                    </button>
                </div>
                <div class="crb-panel-body">
                    <template x-if="config.filters.length === 0">
                        <p style="font-size:12px; color:var(--crb-muted); text-align:center; padding:12px 0;">
                            No filters — showing all records.
                        </p>
                    </template>

                    <template x-for="(filter, idx) in config.filters" :key="idx">
                        <div class="crb-filter-row" :class="{ between: filter.operator === 'between' }">
                            {{-- Column --}}
                            <select class="crb-select" x-model="filter.column">
                                <option value="">-- Column --</option>
                                <template x-for="col in availableColumns" :key="col.key">
                                    <option :value="col.key" x-text="col.label"></option>
                                </template>
                            </select>

                            {{-- Operator --}}
                            <select class="crb-select" x-model="filter.operator">
                                <option value="=">equals</option>
                                <option value="!=">not equals</option>
                                <option value="like">contains</option>
                                <option value=">">greater than</option>
                                <option value="<">less than</option>
                                <option value=">=">≥</option>
                                <option value="<=">≤</option>
                                <option value="between">between</option>
                            </select>

                            {{-- Value --}}
                            <input class="crb-input" type="text" x-model="filter.value"
                                   :placeholder="filter.operator === 'between' ? 'From' : 'Value'">

                            {{-- Value 2 (between only) --}}
                            <template x-if="filter.operator === 'between'">
                                <input class="crb-input" type="text" x-model="filter.value2" placeholder="To">
                            </template>

                            {{-- Remove --}}
                            <button @click="removeFilter(idx)" class="crb-btn crb-btn-sm"
                                    style="background:var(--crb-red-lt); color:var(--crb-red); border:1px solid rgba(206,17,38,.2);">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Step 4: Sort --}}
            <div class="crb-panel" x-show="config.source" x-transition>
                <div class="crb-panel-header">
                    <span class="crb-step">4</span>
                    <div class="crb-panel-header-icon red"><i class="fas fa-sort"></i></div>
                    <span class="crb-panel-title">Sort</span>
                </div>
                <div class="crb-panel-body">
                    <div class="crb-sort-row">
                        <label>Sort by</label>
                        <select class="crb-select" x-model="config.sort_by">
                            <option value="">-- None --</option>
                            <template x-for="col in availableColumns" :key="col.key">
                                <option :value="col.key" x-text="col.label"></option>
                            </template>
                        </select>
                        <label>Direction</label>
                        <select class="crb-select" style="max-width:120px;" x-model="config.sort_dir">
                            <option value="asc">A → Z</option>
                            <option value="desc">Z → A</option>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Run button --}}
            <button @click="runPreview()"
                    class="crb-btn crb-btn-primary w-full justify-center"
                    style="padding:13px; font-size:13.5px;"
                    :disabled="!config.source || loading">
                <template x-if="loading"><span class="crb-spinner"></span></template>
                <template x-if="!loading"><i class="fas fa-play"></i></template>
                <span x-text="loading ? 'Running…' : 'Preview Report'"></span>
            </button>

        </div>

        {{-- ════════════════════════════════════════════════
             RIGHT COLUMN — Preview + Export
        ════════════════════════════════════════════════ --}}
        <div class="flex flex-col gap-5">

            {{-- Preview panel --}}
            <div class="crb-panel" style="flex:1;">
                <div class="crb-panel-header">
                    <div class="crb-panel-header-icon blue"><i class="fas fa-table"></i></div>
                    <span class="crb-panel-title">Preview</span>

                    <template x-if="previewRows.length > 0">
                        <span class="crb-badge crb-badge-blue ml-2">
                            <span x-text="previewRows.length + ' rows'"></span>
                        </span>
                    </template>

                    <div class="ml-auto flex items-center gap-2" style="font-size:11px; color:var(--crb-muted);">
                        @if($isStandard)
                        <i class="fas fa-info-circle"></i> Preview capped at 100 rows
                        @else
                        <i class="fas fa-info-circle"></i> Preview shows up to 500 rows
                        @endif
                    </div>
                </div>
                <div class="crb-panel-body" style="padding:0;">

                    {{-- Empty state --}}
                    <template x-if="!hasRun && !loading">
                        <div class="crb-empty">
                            <i class="fas fa-table"></i>
                            <p>Configure your report on the left,<br>then click <strong>Preview Report</strong> to see results.</p>
                        </div>
                    </template>

                    {{-- Loading --}}
                    <template x-if="loading">
                        <div class="crb-empty">
                            <i class="fas fa-circle-notch fa-spin" style="opacity:.5;"></i>
                            <p>Running query…</p>
                        </div>
                    </template>

                    {{-- No results --}}
                    <template x-if="hasRun && !loading && previewRows.length === 0">
                        <div class="crb-empty">
                            <i class="fas fa-search"></i>
                            <p>No records matched your criteria.<br>Try adjusting your filters.</p>
                        </div>
                    </template>

                    {{-- Table --}}
                    <template x-if="hasRun && !loading && previewRows.length > 0">
                        <div class="crb-preview-wrap" style="border-radius:0; border:none; border-top:1px solid var(--crb-border);">
                            <table class="crb-table">
                                <thead>
                                    <tr>
                                        <template x-for="col in previewColumns" :key="col">
                                            <th x-text="col"></th>
                                        </template>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="(row, ri) in previewRows" :key="ri">
                                        <tr>
                                            <template x-for="col in previewColumns" :key="col">
                                                <td :title="row[col]" x-text="row[col] ?? '—'"></td>
                                            </template>
                                        </tr>
                                    </template>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td :colspan="previewColumns.length">
                                            <span x-text="'Showing ' + previewRows.length + ' of ' + totalCount + ' total records'"></span>
                                            @if($isStandard)
                                            &nbsp;·&nbsp; Standard plan: export capped at 3,000 records
                                            @endif
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Export panel --}}
            <div class="crb-panel" x-show="hasRun && previewRows.length > 0" x-transition>
                <div class="crb-panel-header">
                    <div class="crb-panel-header-icon gold"><i class="fas fa-download"></i></div>
                    <span class="crb-panel-title">Export Report</span>
                </div>
                <div class="crb-panel-body">
                    <div class="crb-export-row">

                        {{-- CSV — Standard + Premium --}}
                        <button @click="doExport('csv')" class="crb-btn crb-btn-outline" :disabled="exportLoading">
                            <i class="fas fa-file-csv" style="color:#22c55e;"></i>
                            CSV Export
                            @if($isStandard)
                            <span class="crb-badge crb-badge-blue" style="font-size:9px;">3K limit</span>
                            @endif
                        </button>

                        {{-- Excel — Premium only --}}
                        @if($isPremium)
                        <button @click="doExport('excel')" class="crb-btn crb-btn-gold" :disabled="exportLoading">
                            <i class="fas fa-file-excel"></i>
                            Excel (.xlsx)
                        </button>
                        @else
                        <button class="crb-btn crb-btn-outline" disabled title="Upgrade to Premium">
                            <i class="fas fa-lock" style="font-size:11px;"></i>
                            Excel (.xlsx)
                            <span class="crb-badge crb-badge-gold">Premium</span>
                        </button>
                        @endif

                        {{-- PDF — Premium only --}}
                        @if($isPremium)
                        <button @click="doExport('pdf')" class="crb-btn crb-btn-red" :disabled="exportLoading">
                            <i class="fas fa-file-pdf"></i>
                            PDF Export
                        </button>
                        @else
                        <button class="crb-btn crb-btn-outline" disabled title="Upgrade to Premium">
                            <i class="fas fa-lock" style="font-size:11px;"></i>
                            PDF Export
                            <span class="crb-badge crb-badge-gold">Premium</span>
                        </button>
                        @endif

                        <template x-if="exportLoading">
                            <div style="display:flex; align-items:center; gap:6px; color:var(--crb-muted); font-size:12px;">
                                <div style="width:14px;height:14px;border:2px solid var(--crb-border);border-top-color:var(--crb-blue);border-radius:50%;animation:crb-spin .7s linear infinite;"></div>
                                Preparing…
                            </div>
                        </template>

                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- Hidden export form --}}
    <form id="crb-export-form" method="POST" action="{{ route('admin.reports.custom.export') }}" style="display:none;">
        @csrf
        <input type="hidden" name="format" id="crb-format-input">
        <input type="hidden" name="source" id="crb-source-input">
        <input type="hidden" name="sort_by" id="crb-sort-by-input">
        <input type="hidden" name="sort_dir" id="crb-sort-dir-input">
        <div id="crb-dynamic-inputs"></div>
    </form>

</div>
@endsection

@push('scripts')
<script>
function reportBuilder() {
    return {
        // State
        config: {
            source:   '',
            columns:  [],
            filters:  [],
            sort_by:  '',
            sort_dir: 'asc',
        },
        schema: @json($schema),
        availableColumns: [],
        allColumnsSelected: false,

        // Preview state
        hasRun:        false,
        loading:       false,
        exportLoading: false,
        error:         null,
        previewRows:   [],
        previewColumns:[],
        totalCount:    0,

        // ── Init ──────────────────────────────────────────────────────────
        init() {
            this.$watch('config.source', () => this.onSourceChange());
            this.$watch('config.columns', () => this.updateAllSelected(), { deep: true });
        },

        // ── Source selection ──────────────────────────────────────────────
        selectSource(key) {
            if (this.config.source === key) return;
            this.config.source  = key;
            this.config.filters = [];
            this.config.sort_by = '';
        },

        onSourceChange() {
            const src = this.schema.find(s => s.key === this.config.source);
            this.availableColumns = src ? src.columns : [];
            // Default: select all columns
            this.config.columns   = this.availableColumns.map(c => c.key);
            this.previewRows      = [];
            this.previewColumns   = [];
            this.hasRun           = false;
            this.error            = null;
        },

        // ── Column toggles ────────────────────────────────────────────────
        toggleColumn(key) {
            const idx = this.config.columns.indexOf(key);
            if (idx === -1) this.config.columns.push(key);
            else            this.config.columns.splice(idx, 1);
        },

        toggleAllColumns() {
            if (this.allColumnsSelected) this.config.columns = [];
            else this.config.columns = this.availableColumns.map(c => c.key);
        },

        updateAllSelected() {
            this.allColumnsSelected =
                this.availableColumns.length > 0 &&
                this.availableColumns.every(c => this.config.columns.includes(c.key));
        },

        // ── Filters ───────────────────────────────────────────────────────
        addFilter() {
            this.config.filters.push({ column: '', operator: '=', value: '', value2: '' });
        },

        removeFilter(idx) {
            this.config.filters.splice(idx, 1);
        },

        // ── Preview (AJAX) ────────────────────────────────────────────────
        async runPreview() {
            if (!this.config.source) return;
            this.loading = true;
            this.error   = null;
            this.hasRun  = false;

            try {
                const resp = await fetch('{{ route('admin.reports.custom.preview') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(this.config),
                });

                const json = await resp.json();

                if (!json.success) {
                    this.error = json.message ?? 'Query failed.';
                } else {
                    this.previewRows    = json.rows;
                    this.previewColumns = json.columns;
                    this.totalCount     = json.count;
                    this.hasRun         = true;
                }
            } catch(e) {
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },

        // ── Export (form POST) ────────────────────────────────────────────
        doExport(format) {
            this.exportLoading = true;

            const form     = document.getElementById('crb-export-form');
            const dynDiv   = document.getElementById('crb-dynamic-inputs');
            dynDiv.innerHTML = '';

            document.getElementById('crb-format-input').value  = format;
            document.getElementById('crb-source-input').value  = this.config.source;
            document.getElementById('crb-sort-by-input').value = this.config.sort_by;
            document.getElementById('crb-sort-dir-input').value= this.config.sort_dir;

            // Columns
            this.config.columns.forEach((col, i) => {
                const inp = document.createElement('input');
                inp.type = 'hidden'; inp.name = `columns[${i}]`; inp.value = col;
                dynDiv.appendChild(inp);
            });

            // Filters
            this.config.filters.forEach((f, i) => {
                ['column','operator','value','value2'].forEach(k => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden'; inp.name = `filters[${i}][${k}]`; inp.value = f[k] ?? '';
                    dynDiv.appendChild(inp);
                });
            });

            form.submit();

            // Re-enable after a short delay (export starts downloading)
            setTimeout(() => { this.exportLoading = false; }, 2500);
        },
    };
}
</script>
@endpush