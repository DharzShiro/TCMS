<style>
    :root {
        --sa-primary:  #003087;
        --sa-accent:   #0057B8;
        --sa-success:  #16a34a;
        --sa-warning:  #b38a00;
        --sa-danger:   #CE1126;
        --sa-gold:     #F5C518;
        --sa-border:   #c5d8f5;
        --sa-text:     #001a4d;
        --sa-muted:    #5a7aaa;
        --sa-bg:       #ffffff;
        --sa-surface:  #f4f8ff;

        --sa-cb-bg:             #ffffff;
        --sa-cb-border:         #c5d8f5;
        --sa-cb-checked-bg:     rgba(0,87,184,.12);
        --sa-cb-checked-border: #0057B8;
        --sa-cb-checked-text:   #003087;
        --sa-cb-hover-bg:       rgba(0,87,184,.06);
        --sa-cb-hover-border:   #0057B8;
        --sa-cb-hover-text:     #0057B8;
    }
    .dark {
        --sa-bg:      #0a1628;
        --sa-surface: #0d1f3c;
        --sa-border:  #1e3a6b;
        --sa-text:    #dde8ff;
        --sa-muted:   #6b8abf;

        --sa-cb-bg:             #0d1f3c;
        --sa-cb-border:         #2a4a7f;
        --sa-cb-checked-bg:     rgba(0,120,255,.18);
        --sa-cb-checked-border: #4d9fff;
        --sa-cb-checked-text:   #a8d0ff;
        --sa-cb-hover-bg:       rgba(0,120,255,.10);
        --sa-cb-hover-border:   #4d9fff;
        --sa-cb-hover-text:     #7ab8ff;
    }

    .tab-bar {
        display: flex;
        gap: 4px;
        padding: 4px;
        background: var(--sa-surface);
        border: 1.5px solid var(--sa-border);
        border-radius: 12px;
        margin-bottom: 24px;
        width: fit-content;
    }
    .tab-btn {
        padding: 9px 22px;
        border-radius: 9px;
        border: none;
        background: transparent;
        color: var(--sa-muted);
        font-family: inherit;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: all .15s;
        display: flex;
        align-items: center;
        gap: 7px;
    }
    .tab-btn:hover { background: var(--sa-bg); color: var(--sa-text); }
    .tab-btn.active { background: var(--sa-accent); color: #fff; box-shadow: 0 2px 8px rgba(0,87,184,.25); }

    .plan-cards-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 18px;
        margin-bottom: 8px;
    }
    .plan-card {
        background: var(--sa-bg);
        border: 2px solid var(--sa-border);
        border-radius: 18px;
        padding: 24px;
        transition: border-color .2s, box-shadow .2s;
        position: relative;
        overflow: hidden;
    }
    .plan-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: #7c8faa;
    }
    .plan-card-basic::before {
        background: linear-gradient(90deg, #5a7aaa, #8fa5c7);
    }
    .plan-card-standard::before {
        background: linear-gradient(90deg, var(--sa-accent), #0075d6);
    }
    .plan-card-premium::before {
        background: linear-gradient(90deg, var(--sa-gold), #d4a800);
        box-shadow: 0 1px 4px rgba(245,197,24,.3);
    }
    .plan-card-custom::before {
        background: linear-gradient(90deg, #9333ea, #c084fc);
        box-shadow: 0 1px 4px rgba(147,51,234,.3);
    }
    .plan-card:hover {
        border-color: var(--sa-accent);
        box-shadow: 0 8px 30px rgba(0,87,184,.12);
    }
    .plan-card.inactive { 
        opacity: .65;
    }

    .plan-card-header {
        margin-bottom: 12px;
    }
    .plan-header-top {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        margin-bottom: 8px;
    }
    .plan-icon-wrapper {
        flex-shrink: 0;
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--sa-surface);
        font-size: 24px;
    }
    .plan-card-basic .plan-icon-wrapper {
        background: rgba(90,122,170,.1);
    }
    .plan-card-standard .plan-icon-wrapper {
        background: rgba(0,87,184,.1);
    }
    .plan-card-premium .plan-icon-wrapper {
        background: rgba(245,197,24,.12);
    }
    .plan-card-custom .plan-icon-wrapper {
        background: rgba(147,51,234,.12);
    }
    .plan-icon {
        line-height: 1;
    }
    .plan-header-meta {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .plan-header-status {
        display: flex;
        justify-content: flex-end;
    }
    .plan-slug-badge {
        padding: 4px 12px;
        border-radius: 100px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .6px;
    }
    .slug-basic    {
        background: rgba(90,122,170,.12);
        color: var(--sa-muted);
    }
    .slug-standard {
        background: rgba(0,87,184,.12);
        color: var(--sa-accent);
    }
    .slug-premium {
        background: rgba(245,197,24,.15);
        color: #a07800;
    }
    .slug-custom {
        background: rgba(147,51,234,.15);
        color: #9333ea;
    }

    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 5px 12px;
        border-radius: 100px;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: .4px;
    }
    .status-active {
        background: rgba(22,163,74,.12);
        color: var(--sa-success);
    }
    .status-scheduled {
        background: rgba(179,138,0,.12);
        color: var(--sa-warning);
    }
    .status-inactive {
        background: rgba(90,122,170,.12);
        color: var(--sa-muted);
    }

    .plan-name {
        font-size: 18px;
        font-weight: 800;
        color: var(--sa-primary);
        margin-bottom: 2px;
        line-height: 1.3;
    }
    .dark .plan-name {
        color: #dde8ff;
    }
    .plan-desc { font-size: 12px; color: var(--sa-muted); line-height: 1.5; margin-bottom: 14px; }

    .plan-price-section {
        margin-bottom: 16px;
    }
    .plan-price-row {
        display: flex;
        align-items: baseline;
        gap: 3px;
        margin-bottom: 0;
    }
    .plan-currency {
        font-size: 18px;
        font-weight: 600;
        color: var(--sa-muted);
        align-self: flex-start;
        margin-top: 2px;
    }
    .plan-amount {
        font-size: 32px;
        font-weight: 900;
        color: var(--sa-primary);
        line-height: 1;
    }
    .dark .plan-amount {
        color: #dde8ff;
    }
    .plan-duration {
        font-size: 11px;
        color: var(--sa-muted);
        font-weight: 500;
        margin-left: 2px;
    }
    .plan-price-free {
        font-size: 24px;
        font-weight: 800;
        color: var(--sa-muted);
        letter-spacing: .5px;
    }

    .plan-limits {
        display: flex;
        flex-direction: column;
        gap: 5px;
        margin-bottom: 14px;
        font-size: 12px;
    }
    .plan-limit-row {
        display: flex;
        justify-content: space-between;
        padding: 8px 10px;
        border-radius: 8px;
        background: var(--sa-surface);
        align-items: center;
    }
    .plan-limit-key {
        color: var(--sa-muted);
        font-weight: 600;
        font-size: 11px;
    }
    .plan-limit-val {
        font-weight: 700;
        color: var(--sa-text);
    }
    .unlimited-badge {
        background: rgba(22,163,74,.12);
        color: var(--sa-success);
        padding: 2px 8px;
        border-radius: 6px;
        font-weight: 700;
        font-size: 11px;
        display: inline-block;
        letter-spacing: .3px;
    }

    .plan-flags {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 16px;
    }
    .flag-pill {
        padding: 2px 10px;
        border-radius: 100px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .4px;
    }
    .flag-on  { background: rgba(22,163,74,.10);  color: var(--sa-success); border: 1px solid rgba(22,163,74,.2); }
    .flag-off { background: rgba(90,122,170,.08); color: var(--sa-muted);   border: 1px solid rgba(90,122,170,.15); text-decoration: line-through; opacity: .7; }

    .plan-avail {
        font-size: 11px;
        color: var(--sa-muted);
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .plan-card-actions {
        display: flex;
        gap: 8px;
        padding-top: 14px;
        border-top: 1px solid var(--sa-border);
    }

    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .fi { display: flex; flex-direction: column; gap: 5px; }
    .fi label { font-size: 11px; font-weight: 700; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .4px; }
    .fi input, .fi select, .fi textarea {
        padding: 8px 10px; border-radius: 8px; border: 1.5px solid var(--sa-border);
        background: var(--sa-bg); color: var(--sa-text); font-family: inherit;
        font-size: 13px; outline: none; transition: border-color .15s;
    }
    .fi input:focus, .fi select:focus, .fi textarea:focus { border-color: var(--sa-accent); }

    .check-group { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
    .check-item input[type="checkbox"] {
        position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none;
    }
    .check-item {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 600; color: var(--sa-muted);
        cursor: pointer; padding: 6px 12px; border-radius: 8px;
        border: 1.5px solid var(--sa-cb-border); background: var(--sa-cb-bg);
        transition: background .15s, border-color .15s, color .15s;
        user-select: none; position: relative;
    }
    .check-item::before {
        content: ''; display: inline-flex; flex-shrink: 0;
        width: 14px; height: 14px; border-radius: 4px;
        border: 1.5px solid var(--sa-cb-border); background: var(--sa-cb-bg);
        transition: background .15s, border-color .15s;
    }
    .check-item:hover { border-color: var(--sa-cb-hover-border); color: var(--sa-cb-hover-text); background: var(--sa-cb-hover-bg); }
    .check-item:hover::before { border-color: var(--sa-cb-hover-border); }
    .check-item:has(input:checked) { background: var(--sa-cb-checked-bg); border-color: var(--sa-cb-checked-border); color: var(--sa-cb-checked-text); }
    .check-item:has(input:checked)::before {
        background: var(--sa-cb-checked-border); border-color: var(--sa-cb-checked-border);
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 10 8' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3 3 5-6' stroke='%23fff' stroke-width='1.8' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-repeat: no-repeat; background-position: center; background-size: 10px 8px;
    }

    .disc-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .disc-table th {
        padding: 10px 14px; text-align: left; font-weight: 700;
        font-size: 11px; letter-spacing: .4px; text-transform: uppercase;
        color: var(--sa-muted); border-bottom: 2px solid var(--sa-border);
        background: var(--sa-surface);
    }
    .disc-table td { padding: 11px 14px; color: var(--sa-text); border-bottom: 1px solid var(--sa-border); }
    .disc-table tr:last-child td { border-bottom: none; }
    .disc-table tr:hover td { background: var(--sa-surface); }

    .status-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700;
    }
    .sb-success { background: rgba(22,163,74,.10);  color: var(--sa-success); }
    .sb-warning { background: rgba(179,138,0,.10);  color: var(--sa-warning); }
    .sb-danger  { background: rgba(206,17,38,.10);  color: var(--sa-danger);  }
    .sb-muted   { background: rgba(90,122,170,.10); color: var(--sa-muted);   }

    .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 16px; border-radius: 9px; font-size: 12px; font-weight: 700; border: none; cursor: pointer; font-family: inherit; text-decoration: none; transition: all .15s; }
    .btn:hover { transform: translateY(-1px); }
    .btn-primary { background: var(--sa-accent); color: #fff; box-shadow: 0 2px 8px rgba(0,87,184,.22); }
    .btn-danger  { background: rgba(206,17,38,.10); color: var(--sa-danger); border: 1.5px solid rgba(206,17,38,.25); }
    .btn-outline { background: var(--sa-surface); color: var(--sa-text); border: 1.5px solid var(--sa-border); }
    .btn-gold    { background: linear-gradient(135deg,var(--sa-gold) 0%,#d4a800 100%); color: #001a4d; }
    .btn-success { background: rgba(22,163,74,.10); color: var(--sa-success); border: 1.5px solid rgba(22,163,74,.25); }
    .btn-sm { padding: 5px 10px; font-size: 11px; border-radius: 7px; }

    .modal-overlay {
        position: fixed; inset: 0; background: rgba(0,0,0,.45);
        z-index: 1000; display: flex; align-items: center; justify-content: center;
        padding: 24px; opacity: 0; pointer-events: none; transition: opacity .2s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: all; }
    .modal-box {
        background: var(--sa-bg); border-radius: 20px; border: 2px solid var(--sa-border);
        width: 100%; max-width: 620px; max-height: 90vh; overflow-y: auto;
        box-shadow: 0 20px 60px rgba(0,0,0,.25); transform: translateY(20px); transition: transform .2s;
    }
    .modal-overlay.open .modal-box { transform: translateY(0); }
    .modal-header {
        padding: 22px 26px 18px; border-bottom: 2px solid var(--sa-border);
        display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; background: var(--sa-bg); z-index: 1;
    }
    .modal-title  { font-size: 17px; font-weight: 800; color: var(--sa-primary); }
    .modal-body   { padding: 24px 26px; }
    .modal-footer { padding: 16px 26px; border-top: 2px solid var(--sa-border); display: flex; gap: 10px; justify-content: flex-end; position: sticky; bottom: 0; background: var(--sa-bg); }

    .plan-form-section {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid var(--sa-border);
    }
    .plan-form-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
    .section-label {
        font-size: 11px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .6px; color: var(--sa-muted);
        margin-bottom: 12px;
        display: flex; align-items: center; gap: 7px;
    }
    .section-label i { color: var(--sa-accent); }

    .limit-input-wrap { display: flex; align-items: center; gap: 8px; }
    .limit-input-wrap input { flex: 1; }
    .unlimited-toggle {
        display: flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 600; color: var(--sa-muted);
        cursor: pointer; white-space: nowrap; user-select: none;
    }
    .unlimited-toggle input { accent-color: var(--sa-accent); width: 13px; height: 13px; cursor: pointer; }

    .feat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 7px; }
    .feat-toggle { position: relative; }
    .feat-toggle input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .feat-toggle label {
        display: flex; align-items: center; gap: 8px;
        padding: 9px 12px; border-radius: 9px; border: 1.5px solid var(--sa-border);
        background: var(--sa-surface); cursor: pointer; transition: all .15s;
        font-size: 12px; font-weight: 600; color: var(--sa-text); user-select: none;
    }
    .feat-check {
        flex-shrink: 0; width: 16px; height: 16px; border-radius: 4px;
        border: 1.5px solid var(--sa-border); background: var(--sa-bg);
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; font-weight: 700; color: transparent; transition: all .15s; line-height: 1;
    }
    .feat-toggle input:checked + label { border-color: var(--sa-accent); background: rgba(0,87,184,.07); }
    .feat-toggle input:checked + label .feat-check { background: var(--sa-accent); border-color: var(--sa-accent); color: #fff; }

    .export-group { display: flex; gap: 8px; flex-wrap: wrap; }
    .exp-toggle { position: relative; }
    .exp-toggle input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .exp-toggle label {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 100px; border: 1.5px solid var(--sa-border); background: var(--sa-surface);
        cursor: pointer; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        color: var(--sa-muted); transition: all .15s; user-select: none;
    }
    .exp-toggle input:checked + label { border-color: var(--sa-success); background: rgba(22,163,74,.08); color: var(--sa-success); }

    .slug-group { display: flex; gap: 8px; }
    .slug-pill { flex: 1; position: relative; }
    .slug-pill input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .slug-pill label {
        display: flex; flex-direction: column; align-items: center; gap: 4px;
        padding: 10px 8px; border-radius: 10px; border: 1.5px solid var(--sa-border);
        background: var(--sa-surface); cursor: pointer; transition: all .15s;
        text-align: center; user-select: none;
    }
    .slug-pill input:checked + label { border-color: var(--sa-accent); background: rgba(0,87,184,.07); }

    .type-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 20px; font-size: 10px; font-weight: 700; text-transform: uppercase;
    }
    .type-auto { background: rgba(22,163,74,.10); color: var(--sa-success); }
    .type-code { background: rgba(0,87,184,.10);  color: var(--sa-accent); }

    .plan-pill {
        display: inline-flex; align-items: center;
        padding: 1px 8px; border-radius: 20px; font-size: 10px; font-weight: 700;
        text-transform: uppercase; letter-spacing: .4px; margin: 1px 2px;
    }
    .pill-basic    { background: rgba(90,122,170,.12); color: var(--sa-muted); }
    .pill-standard { background: rgba(0,87,184,.12);   color: var(--sa-accent); }
    .pill-premium  { background: rgba(245,197,24,.15); color: #a07800; }
    .pill-custom   { background: rgba(147,51,234,.12); color: #9333ea; }

    .tenant-pill {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 1px 8px; border-radius: 20px; font-size: 10px; font-weight: 600;
        background: rgba(0,87,184,.08); color: var(--sa-accent); margin: 1px 2px;
        max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .tenant-pill-all {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 1px 8px; border-radius: 20px; font-size: 10px; font-weight: 600;
        background: rgba(22,163,74,.10); color: var(--sa-success); margin: 1px 2px;
    }

    .stat-pill { display: flex; flex-direction: column; align-items: center; padding: 14px 20px; border-radius: 14px; background: var(--sa-surface); border: 1.5px solid var(--sa-border); gap: 4px; }
    .stat-pill-val { font-size: 22px; font-weight: 800; color: var(--sa-primary); line-height: 1; }
    .stat-pill-lbl { font-size: 10px; font-weight: 600; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .5px; }

    #tenant-list::-webkit-scrollbar, #ed-tenant-list::-webkit-scrollbar,
    #pm-tenant-list::-webkit-scrollbar, #pm-ed-tenant-list::-webkit-scrollbar { width: 4px; }
    #tenant-list::-webkit-scrollbar-thumb, #ed-tenant-list::-webkit-scrollbar-thumb,
    #pm-tenant-list::-webkit-scrollbar-thumb, #pm-ed-tenant-list::-webkit-scrollbar-thumb {
        background: var(--sa-border); border-radius: 4px;
    }
</style>
