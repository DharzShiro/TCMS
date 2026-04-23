<style>
    :root {
        --sa-primary: #003087;
        --sa-accent:  #0057B8;
        --sa-success: #16a34a;
        --sa-warning: #b38a00;
        --sa-danger:  #CE1126;
        --sa-gold:    #F5C518;
        --sa-border:  #c5d8f5;
        --sa-text:    #001a4d;
        --sa-muted:   #5a7aaa;
        --sa-bg:      #ffffff;
        --sa-surface: #f4f8ff;
    }
    .dark {
        --sa-bg:      #0a1628;
        --sa-surface: #0d1f3c;
        --sa-border:  #1e3a6b;
        --sa-text:    #dde8ff;
        --sa-muted:   #6b8abf;
    }

    /* ── Stat Cards ── */
    .stat-card {
        border-radius: 16px;
        border: 2px solid var(--sa-border);
        background: var(--sa-bg);
        padding: 22px 24px;
        transition: transform .18s, box-shadow .18s;
    }
    .stat-card:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,48,135,.10); }

    .stat-icon {
        width: 46px; height: 46px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 17px; flex-shrink: 0;
    }

    /* ── Section Cards ── */
    .section-card {
        border-radius: 18px;
        border: 2px solid var(--sa-border);
        background: var(--sa-bg);
        padding: 26px 28px;
    }

    .section-title {
        font-size: 15px; font-weight: 700;
        color: var(--sa-primary); margin-bottom: 18px;
        display: flex; align-items: center; gap: 8px;
    }

    /* ── Bar Chart ── */
    .bar-group { display: flex; flex-direction: column; gap: 10px; }
    .bar-row { display: flex; align-items: center; gap: 10px; }
    .bar-label {
        width: 64px; font-size: 11px; font-weight: 600;
        color: var(--sa-muted); text-align: right; flex-shrink: 0;
    }
    .bar-track {
        flex: 1; height: 22px; border-radius: 6px;
        background: var(--sa-surface); overflow: hidden; position: relative;
    }
    .bar-fill {
        height: 100%; border-radius: 6px; min-width: 4px;
        display: flex; align-items: center; justify-content: flex-end;
        padding-right: 8px;
        transition: width .6s cubic-bezier(.4,0,.2,1);
    }
    .bar-val { font-size: 11px; font-weight: 700; color: #fff; white-space: nowrap; }

    /* ── Donut ── */
    .donut-wrap { position: relative; width: 130px; height: 130px; flex-shrink: 0; }
    .donut-wrap svg { width: 100%; height: 100%; }
    .donut-center {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        pointer-events: none;
    }
    .donut-center-val  { font-size: 22px; font-weight: 800; color: var(--sa-primary); line-height: 1; }
    .donut-center-lbl  { font-size: 10px; font-weight: 600; color: var(--sa-muted); letter-spacing: .5px; text-transform: uppercase; }

    .legend-item { display: flex; align-items: center; gap: 8px; font-size: 12px; color: var(--sa-text); }
    .legend-dot { width: 10px; height: 10px; border-radius: 3px; flex-shrink: 0; }

    /* ── Tenant Table ── */
    .tenant-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .tenant-table th {
        padding: 10px 14px; text-align: left; font-weight: 700;
        font-size: 11px; letter-spacing: .4px; text-transform: uppercase;
        color: var(--sa-muted);
        border-bottom: 2px solid var(--sa-border);
        background: var(--sa-surface);
    }
    .tenant-table td {
        padding: 11px 14px; color: var(--sa-text);
        border-bottom: 1px solid var(--sa-border);
    }
    .tenant-table tr:last-child td { border-bottom: none; }
    .tenant-table tr:hover td { background: var(--sa-surface); }

    .plan-badge { display: inline-block; padding: 2px 10px; border-radius: 20px; font-size: 11px; font-weight: 700; }
    .plan-basic    { background: rgba(90,122,170,.12); color: var(--sa-muted); }
    .plan-standard { background: rgba(0,87,184,.12);   color: var(--sa-accent); }
    .plan-premium  { background: rgba(245,197,24,.15); color: #a07800; }
    .plan-custom   { background: rgba(206,17,38,.08);  color: var(--sa-danger); }

    /* ── Alert strip ── */
    .expiry-strip {
        background: rgba(179,138,0,.08);
        border: 2px solid rgba(179,138,0,.25);
        border-radius: 14px; padding: 16px 20px;
    }
    .expiry-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 8px 0; border-bottom: 1px solid rgba(179,138,0,.12);
        font-size: 13px;
    }
    .expiry-row:last-child { border-bottom: none; }

    /* ── Aggregate pills ── */
    .agg-pill {
        display: flex; flex-direction: column; align-items: center;
        padding: 14px 18px; border-radius: 14px;
        background: var(--sa-surface); border: 1.5px solid var(--sa-border);
        gap: 4px; flex: 1; min-width: 90px;
    }
    .agg-pill-val { font-size: 20px; font-weight: 800; color: var(--sa-primary); }
    .agg-pill-lbl { font-size: 10px; font-weight: 600; color: var(--sa-muted); text-transform: uppercase; letter-spacing: .5px; }

    /* ── Plan Cards ── */
    .plan-card {
        border-radius: 16px;
        border: 2px solid var(--sa-border);
        background: var(--sa-bg);
        padding: 20px;
        transition: transform .18s, box-shadow .18s;
    }
    .plan-card:hover { transform: translateY(-2px); box-shadow: 0 6px 24px rgba(0,48,135,.08); }
    .plan-card.active { border-color: rgba(245,197,24,.5); }

    .feat-tag {
        display: inline-block; padding: 3px 10px;
        border-radius: 20px; font-size: 11px; font-weight: 600;
    }

    /* ── Renewal rows ── */
    .renewal-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 9px 0; border-bottom: 1px solid var(--sa-border);
        font-size: 13px;
    }
    .renewal-row:last-child { border-bottom: none; }

    /* ── Storage bars ── */
    .storage-bar-row { display: flex; align-items: center; gap: 10px; margin-bottom: 9px; }
    .storage-bar-label {
        width: 115px; font-size: 12px; color: var(--sa-text);
        flex-shrink: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .storage-bar-track {
        flex: 1; height: 16px; border-radius: 5px;
        background: var(--sa-surface); overflow: hidden;
    }
    .storage-bar-fill {
        height: 100%; border-radius: 5px;
        background: linear-gradient(90deg, var(--sa-accent), var(--sa-primary));
        display: flex; align-items: center; justify-content: flex-end;
        padding-right: 6px;
    }
    .storage-bar-val { font-size: 10px; font-weight: 700; color: #fff; white-space: nowrap; }
</style>
