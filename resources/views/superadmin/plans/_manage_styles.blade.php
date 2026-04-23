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
    }
    .dark {
        --sa-bg:      #0a1628;
        --sa-surface: #0d1f3c;
        --sa-border:  #1e3a6b;
        --sa-text:    #dde8ff;
        --sa-muted:   #6b8abf;
    }

    .pm-wrap { max-width: 860px; margin: 0 auto; }

    .pm-card {
        background: var(--sa-bg);
        border: 1.5px solid var(--sa-border);
        border-radius: 18px;
        padding: 28px 32px;
        margin-bottom: 20px;
    }
    .pm-card-title {
        font-size: 13px; font-weight: 800; text-transform: uppercase;
        letter-spacing: .7px; color: var(--sa-muted);
        margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
    }
    .pm-card-title i { font-size: 14px; color: var(--sa-accent); }

    .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .form-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
    .fi { display: flex; flex-direction: column; gap: 6px; }
    .fi label { font-size: 11px; font-weight: 700; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .4px; }
    .fi input[type="text"],
    .fi input[type="number"],
    .fi input[type="date"],
    .fi select,
    .fi textarea {
        padding: 9px 12px; border-radius: 10px; border: 1.5px solid var(--sa-border);
        background: var(--sa-bg); color: var(--sa-text); font-family: inherit;
        font-size: 13.5px; outline: none; transition: border-color .15s, box-shadow .15s; width: 100%;
    }
    .fi input:focus, .fi select:focus, .fi textarea:focus {
        border-color: var(--sa-accent); box-shadow: 0 0 0 3px rgba(0,87,184,.10);
    }
    .fi .hint { font-size: 11px; color: var(--sa-muted); margin-top: 1px; }
    .fi-full { grid-column: 1 / -1; }

    .slug-preview {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 100px;
        font-size: 12px; font-weight: 700; letter-spacing: .5px;
        background: rgba(0,87,184,.10); color: var(--sa-accent);
        border: 1.5px solid rgba(0,87,184,.20);
        margin-top: 6px; transition: all .2s;
    }

    .feat-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 10px; }
    .feat-toggle { position: relative; }
    .feat-toggle input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .feat-toggle label {
        display: flex; align-items: center; gap: 10px; padding: 11px 14px;
        border-radius: 10px; border: 1.5px solid var(--sa-border); background: var(--sa-surface);
        cursor: pointer; transition: all .15s; font-size: 13px; font-weight: 600; color: var(--sa-text); user-select: none;
    }
    .feat-check {
        flex-shrink: 0; width: 18px; height: 18px; border-radius: 5px;
        border: 1.5px solid var(--sa-border); background: var(--sa-bg);
        display: flex; align-items: center; justify-content: center;
        font-size: 10px; font-weight: 700; color: transparent; transition: all .15s; line-height: 1;
    }
    .feat-toggle input:checked + label { border-color: var(--sa-accent); background: rgba(0,87,184,.07); }
    .feat-toggle input:checked + label .feat-check { background: var(--sa-accent); border-color: var(--sa-accent); color: #fff; }

    .export-group { display: flex; gap: 10px; flex-wrap: wrap; }
    .exp-toggle { position: relative; }
    .exp-toggle input[type="checkbox"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .exp-toggle label {
        display: inline-flex; align-items: center; gap: 7px; padding: 8px 16px;
        border-radius: 100px; border: 1.5px solid var(--sa-border); background: var(--sa-surface);
        cursor: pointer; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
        color: var(--sa-muted); transition: all .15s; user-select: none;
    }
    .exp-toggle input:checked + label { border-color: var(--sa-success); background: rgba(22,163,74,.08); color: var(--sa-success); }

    .limit-row { display: flex; flex-direction: column; gap: 6px; }
    .limit-input-wrap { position: relative; display: flex; align-items: center; gap: 8px; }
    .limit-input-wrap input[type="number"] { flex: 1; }
    .limit-input-wrap input:disabled { opacity: .4; cursor: not-allowed; }
    .unlimited-toggle {
        display: flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600;
        color: var(--sa-muted); cursor: pointer; white-space: nowrap; user-select: none;
    }
    .unlimited-toggle input[type="checkbox"] { accent-color: var(--sa-accent); width: 14px; height: 14px; cursor: pointer; }

    .date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .always-available-wrap {
        grid-column: 1 / -1; display: flex; align-items: center; gap: 8px;
        font-size: 13px; font-weight: 600; color: var(--sa-text); cursor: pointer;
        user-select: none; margin-bottom: 4px;
    }
    .always-available-wrap input[type="checkbox"] { accent-color: var(--sa-accent); width: 15px; height: 15px; cursor: pointer; }

    .active-row { display: flex; align-items: center; gap: 12px; }
    .switch { position: relative; width: 44px; height: 24px; flex-shrink: 0; }
    .switch input { opacity: 0; width: 0; height: 0; }
    .switch-track {
        position: absolute; inset: 0; background: var(--sa-border);
        border-radius: 100px; cursor: pointer; transition: background .2s;
    }
    .switch input:checked + .switch-track { background: var(--sa-accent); }
    .switch-track::after {
        content: ''; position: absolute; width: 18px; height: 18px; border-radius: 50%;
        background: #fff; top: 3px; left: 3px; transition: transform .2s;
        box-shadow: 0 1px 4px rgba(0,0,0,.2);
    }
    .switch input:checked + .switch-track::after { transform: translateX(20px); }

    .price-preview {
        display: flex; align-items: center; gap: 10px; padding: 10px 16px;
        background: var(--sa-surface); border: 1.5px solid var(--sa-border);
        border-radius: 12px; margin-top: 14px; font-size: 13px; color: var(--sa-muted);
    }
    .price-preview .pp-val { font-size: 22px; font-weight: 800; color: var(--sa-primary); }
    .price-preview .pp-dur { font-size: 12px; color: var(--sa-muted); }

    .icon-picker { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 4px; }
    .icon-opt { position: relative; }
    .icon-opt input[type="radio"] { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
    .icon-opt label {
        display: flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; border-radius: 10px; font-size: 20px;
        border: 1.5px solid var(--sa-border); background: var(--sa-surface);
        cursor: pointer; transition: all .15s; user-select: none;
    }
    .icon-opt input:checked + label { border-color: var(--sa-accent); background: rgba(0,87,184,.08); }

    .btn {
        display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px;
        border-radius: 10px; font-size: 13px; font-weight: 700; border: none;
        cursor: pointer; font-family: inherit; text-decoration: none; transition: all .15s;
    }
    .btn:hover { transform: translateY(-1px); }
    .btn-primary { background: var(--sa-accent); color: #fff; box-shadow: 0 2px 8px rgba(0,87,184,.22); }
    .btn-outline  { background: var(--sa-surface); color: var(--sa-text); border: 1.5px solid var(--sa-border); }
    .btn-danger   { background: rgba(206,17,38,.08); color: var(--sa-danger); border: 1.5px solid rgba(206,17,38,.22); }
    .btn-lg { padding: 13px 28px; font-size: 14px; border-radius: 12px; }

    .danger-zone { border-color: rgba(206,17,38,.30); background: rgba(206,17,38,.03); }

    @media (max-width: 640px) {
        .form-grid-2, .form-grid-3 { grid-template-columns: 1fr; }
        .date-row { grid-template-columns: 1fr; }
        .pm-card { padding: 20px 18px; }
    }
</style>
