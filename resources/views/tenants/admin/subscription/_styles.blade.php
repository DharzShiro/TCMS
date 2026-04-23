<style>
    * { box-sizing: border-box; }

    .up-page {
        font-family: 'Plus Jakarta Sans', sans-serif;
        min-height: 100vh;
        padding: 48px 24px 80px;
        position: relative;
        overflow: hidden;
    }

    .up-bg { position: fixed; inset: 0; z-index: 0; pointer-events: none; }
    .up-bg::before {
        content: '';
        position: absolute; inset: 0;
        background:
            radial-gradient(ellipse 80% 60% at 10% 10%, rgba(0,87,184,0.09) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 90% 20%, rgba(206,17,38,0.07) 0%, transparent 55%),
            radial-gradient(ellipse 50% 60% at 50% 90%, rgba(0,48,135,0.06) 0%, transparent 55%);
    }
    .dark .up-bg::before {
        background:
            radial-gradient(ellipse 80% 60% at 10% 10%, rgba(0,87,184,0.18) 0%, transparent 60%),
            radial-gradient(ellipse 60% 50% at 90% 20%, rgba(206,17,38,0.12) 0%, transparent 55%),
            radial-gradient(ellipse 50% 60% at 50% 90%, rgba(0,48,135,0.14) 0%, transparent 55%);
    }

    .up-inner { position: relative; z-index: 1; max-width: 1100px; margin: 0 auto; }

    /* ── Header ── */
    .up-header { text-align: center; margin-bottom: 56px; }
    .up-badge {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 6px 16px; border-radius: 100px;
        font-size: 12px; font-weight: 700; letter-spacing: 0.8px; text-transform: uppercase;
        background: rgba(0,87,184,0.10); color: #0057B8;
        border: 1px solid rgba(0,87,184,0.20); margin-bottom: 20px;
    }
    .dark .up-badge { background: rgba(91,156,246,0.12); color: #5b9cf6; border-color: rgba(91,156,246,0.25); }

    .up-title {
        font-family: 'Instrument Serif', Georgia, serif;
        font-size: clamp(36px, 5vw, 58px); font-weight: 400;
        line-height: 1.12; color: #001a4d; margin-bottom: 16px;
    }
    .dark .up-title { color: #dde8ff; }
    .up-title em { font-style: italic; color: #0057B8; }
    .dark .up-title em { color: #5b9cf6; }

    .up-subtitle { font-size: 17px; color: #5a7aaa; max-width: 500px; margin: 0 auto; line-height: 1.65; }
    .dark .up-subtitle { color: #6b8abf; }

    .up-current-pill {
        display: inline-flex; align-items: center; gap: 8px;
        margin-top: 20px; padding: 8px 18px; border-radius: 100px;
        font-size: 13px; font-weight: 600;
        border: 1.5px dashed rgba(0,87,184,0.30); color: #1a3a6b;
        background: rgba(255,255,255,0.7);
    }
    .dark .up-current-pill { border-color: rgba(91,156,246,0.30); color: #adc4f0; background: rgba(13,31,60,0.7); }
    .up-current-dot {
        width: 8px; height: 8px; border-radius: 50%;
        background: #22c55e; box-shadow: 0 0 0 3px rgba(34,197,94,0.20);
    }

    /* ── Cards ── */
    .up-plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; align-items: start; }

    .up-card {
        position: relative; border-radius: 20px; overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease; cursor: pointer;
    }
    .up-card:hover { transform: translateY(-6px); }

    .up-card-inner {
        background: #fff; border: 1.5px solid #c5d8f5;
        border-radius: 20px; padding: 32px 28px 28px; height: 100%;
    }
    .dark .up-card-inner { background: #0d1f3c; border-color: #1e3a6b; }
    .up-card:hover .up-card-inner { box-shadow: 0 20px 60px rgba(0,48,135,0.14); border-color: #0057B8; }
    .dark .up-card:hover .up-card-inner { box-shadow: 0 20px 60px rgba(0,0,0,0.40); border-color: #5b9cf6; }

    .up-card.featured .up-card-inner {
        background: linear-gradient(145deg, #003087 0%, #0057B8 60%, #0070e0 100%);
        border-color: transparent; box-shadow: 0 20px 60px rgba(0,87,184,0.35);
    }
    .up-card.featured:hover { transform: translateY(-10px); }
    .up-card.featured:hover .up-card-inner { box-shadow: 0 30px 80px rgba(0,87,184,0.45); }

    .up-card.current-plan .up-card-inner { border-color: #22c55e; background: rgba(240,253,244,0.8); }
    .dark .up-card.current-plan .up-card-inner { border-color: rgba(74,222,128,0.40); background: rgba(5,46,22,0.25); }

    .up-popular-badge {
        position: absolute; top: 20px; right: 20px;
        background: #F5C518; color: #1a1a00;
        font-size: 10px; font-weight: 800; letter-spacing: 0.8px; text-transform: uppercase;
        padding: 4px 12px; border-radius: 100px; box-shadow: 0 2px 10px rgba(245,197,24,0.40);
    }
    .up-current-badge {
        position: absolute; top: 20px; right: 20px;
        background: #22c55e; color: #fff;
        font-size: 10px; font-weight: 800; letter-spacing: 0.8px; text-transform: uppercase;
        padding: 4px 12px; border-radius: 100px;
        display: flex; align-items: center; gap: 5px;
    }

    .up-plan-icon {
        width: 52px; height: 52px; border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px; margin-bottom: 16px; background: rgba(0,87,184,0.10);
    }
    .up-card.featured .up-plan-icon { background: rgba(255,255,255,0.15); }

    .up-plan-name { font-size: 13px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: #5a7aaa; margin-bottom: 4px; }
    .up-card.featured .up-plan-name { color: rgba(255,255,255,0.70); }

    /* ── Price block ── */
    .up-plan-price { display: flex; align-items: baseline; gap: 4px; margin-bottom: 4px; }
    .up-price-amount { font-family: 'Instrument Serif', Georgia, serif; font-size: 52px; line-height: 1; color: #001a4d; }
    .dark .up-price-amount { color: #dde8ff; }
    .up-card.featured .up-price-amount { color: #fff; }

    .up-price-period { font-size: 14px; color: #5a7aaa; padding-bottom: 6px; }
    .up-card.featured .up-price-period { color: rgba(255,255,255,0.65); }

    /* Strikethrough original price shown when auto-discount is active */
    .up-price-original {
        font-size: 16px; color: #9aaccc; text-decoration: line-through;
        font-weight: 600; margin-bottom: 2px; display: block;
    }
    .up-card.featured .up-price-original { color: rgba(255,255,255,0.45); }

    /* Auto-discount badge shown under the price */
    .up-auto-discount-badge {
        display: inline-block;
        background: rgba(22,163,74,0.12); color: #16a34a;
        border: 1px solid rgba(22,163,74,0.25);
        font-size: 11px; font-weight: 700;
        padding: 3px 10px; border-radius: 100px; margin-top: 4px;
    }
    .up-card.featured .up-auto-discount-badge {
        background: rgba(255,255,255,0.18); color: #fff; border-color: rgba(255,255,255,0.3);
    }

    .up-plan-desc { font-size: 13.5px; color: #5a7aaa; line-height: 1.6; margin-bottom: 24px; min-height: 42px; }
    .dark .up-plan-desc { color: #6b8abf; }
    .up-card.featured .up-plan-desc { color: rgba(255,255,255,0.72); }

    .up-card-divider { height: 1px; background: #c5d8f5; margin: 0 0 22px; }
    .dark .up-card-divider { background: #1e3a6b; }
    .up-card.featured .up-card-divider { background: rgba(255,255,255,0.18); }

    .up-features { list-style: none; padding: 0; margin: 0 0 28px; }
    .up-feat-item { display: flex; align-items: flex-start; gap: 10px; padding: 5px 0; font-size: 13.5px; color: #1a3a6b; }
    .dark .up-feat-item { color: #adc4f0; }
    .up-card.featured .up-feat-item { color: rgba(255,255,255,0.90); }

    .up-feat-icon {
        width: 18px; height: 18px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 9px; flex-shrink: 0; margin-top: 1px;
        background: rgba(34,197,94,0.15); color: #16a34a;
    }
    .up-card.featured .up-feat-icon { background: rgba(255,255,255,0.20); color: #fff; }
    .up-feat-item.locked .up-feat-icon { background: rgba(90,122,170,0.12); color: #5a7aaa; }
    .up-feat-item.locked { opacity: 0.45; }

    .up-cta-btn {
        width: 100%; padding: 14px 20px; border-radius: 12px;
        font-size: 14px; font-weight: 700; letter-spacing: 0.3px;
        border: none; cursor: pointer; transition: all 0.22s ease;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .up-cta-btn.primary { background: linear-gradient(135deg, #003087 0%, #0057B8 100%); color: #fff; box-shadow: 0 4px 20px rgba(0,87,184,0.30); }
    .up-cta-btn.primary:hover { background: linear-gradient(135deg, #0057B8 0%, #0070e0 100%); box-shadow: 0 6px 28px rgba(0,87,184,0.45); transform: scale(1.02); }
    .up-cta-btn.on-dark { background: rgba(255,255,255,0.92); color: #003087; box-shadow: 0 4px 20px rgba(0,0,0,0.20); }
    .up-cta-btn.on-dark:hover { background: #fff; box-shadow: 0 6px 28px rgba(0,0,0,0.30); transform: scale(1.02); }
    .up-cta-btn.current { background: rgba(34,197,94,0.10); color: #16a34a; border: 1.5px solid rgba(34,197,94,0.30); cursor: default; }
    .dark .up-cta-btn.current { background: rgba(74,222,128,0.10); color: #4ade80; border-color: rgba(74,222,128,0.25); }

    .up-guarantee {
        text-align: center; margin-top: 48px; font-size: 13px; color: #5a7aaa;
        display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .dark .up-guarantee { color: #6b8abf; }

    /* ── Duration badge ── */
    .up-duration-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 11px; font-weight: 600; color: #5a7aaa;
        background: rgba(0,87,184,0.07); padding: 3px 10px;
        border-radius: 100px; margin-bottom: 12px;
    }
    .up-card.featured .up-duration-badge { background: rgba(255,255,255,0.15); color: rgba(255,255,255,0.80); }

    /* ── Modal ── */
    .up-modal-backdrop {
        position: fixed; inset: 0; z-index: 100;
        background: rgba(0,26,77,0.55); backdrop-filter: blur(6px);
        display: flex; align-items: center; justify-content: center; padding: 24px;
    }
    .up-modal {
        background: #fff; border-radius: 24px; padding: 40px;
        max-width: 460px; width: 100%;
        box-shadow: 0 40px 100px rgba(0,48,135,0.25); text-align: center;
        animation: modalIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        max-height: 90vh; overflow-y: auto;
    }
    .dark .up-modal { background: #0d1f3c; box-shadow: 0 40px 100px rgba(0,0,0,0.60); }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.85) translateY(20px); }
        to   { opacity: 1; transform: scale(1)   translateY(0); }
    }
    .up-modal-icon { width: 72px; height: 72px; border-radius: 20px; margin: 0 auto 20px; display: flex; align-items: center; justify-content: center; font-size: 30px; }
    .up-modal-title { font-family: 'Instrument Serif', Georgia, serif; font-size: 28px; color: #001a4d; margin-bottom: 10px; }
    .dark .up-modal-title { color: #dde8ff; }
    .up-modal-sub { font-size: 14px; color: #5a7aaa; line-height: 1.65; margin-bottom: 28px; }
    .dark .up-modal-sub { color: #6b8abf; }
    .up-modal-plan-pill {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 20px; border-radius: 100px; margin-bottom: 20px;
        font-size: 15px; font-weight: 700;
        background: linear-gradient(135deg, #003087 0%, #0057B8 100%); color: #fff;
        box-shadow: 0 4px 16px rgba(0,87,184,0.30);
    }
    .up-modal-actions { display: flex; gap: 12px; flex-direction: column; }
    .up-modal-confirm {
        padding: 14px; border-radius: 12px; border: none; cursor: pointer;
        font-size: 14px; font-weight: 700;
        background: linear-gradient(135deg, #003087 0%, #0057B8 100%); color: #fff;
        box-shadow: 0 4px 16px rgba(0,87,184,0.30); transition: all 0.2s;
    }
    .up-modal-confirm:hover { transform: scale(1.02); box-shadow: 0 6px 24px rgba(0,87,184,0.40); }
    .up-modal-cancel {
        padding: 12px; border-radius: 12px; border: 1.5px solid #c5d8f5;
        cursor: pointer; font-size: 14px; font-weight: 600;
        background: transparent; color: #5a7aaa; transition: all 0.2s;
    }
    .dark .up-modal-cancel { border-color: #1e3a6b; color: #6b8abf; }
    .up-modal-cancel:hover { background: rgba(0,87,184,0.06); color: #1a3a6b; }

    .up-success { animation: successPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1); }
    @keyframes successPop {
        from { transform: scale(0.5); opacity: 0; }
        to   { transform: scale(1);   opacity: 1; }
    }

    /* ── Comparison Table ── */
    .up-compare { margin-top: 64px; }
    .up-compare-title { text-align: center; font-family: 'Instrument Serif', Georgia, serif; font-size: 32px; color: #001a4d; margin-bottom: 32px; }
    .dark .up-compare-title { color: #dde8ff; }
    .up-table-wrap { overflow-x: auto; border-radius: 16px; border: 1.5px solid #c5d8f5; }
    .dark .up-table-wrap { border-color: #1e3a6b; }
    .up-table { width: 100%; border-collapse: collapse; font-size: 13.5px; background: #fff; }
    .dark .up-table { background: #0d1f3c; }
    .up-table th { padding: 16px 20px; font-weight: 700; text-align: left; background: #f0f5ff; color: #1a3a6b; border-bottom: 1.5px solid #c5d8f5; }
    .dark .up-table th { background: #0a1628; color: #adc4f0; border-color: #1e3a6b; }
    .up-table th:not(:first-child) { text-align: center; }
    .up-table td { padding: 13px 20px; border-bottom: 1px solid #e8f0fb; color: #1a3a6b; }
    .dark .up-table td { border-color: #1e3a6b; color: #adc4f0; }
    .up-table tr:last-child td { border-bottom: none; }
    .up-table td:not(:first-child) { text-align: center; }
    .up-check { color: #22c55e; font-size: 16px; }
    .up-cross { color: #d1d5db; font-size: 16px; }
    .up-table tbody tr:hover td { background: rgba(0,87,184,0.03); }
    .dark .up-table tbody tr:hover td { background: rgba(91,156,246,0.05); }
    .up-table th.highlight { background: #e8f0fb; color: #0057B8; }
    .dark .up-table th.highlight { background: rgba(0,87,184,0.15); color: #5b9cf6; }
    .up-table td.highlight { background: rgba(0,87,184,0.03); }
    .dark .up-table td.highlight { background: rgba(0,87,184,0.06); }

    /* ── Animations ── */
    .up-card { animation: cardFadeIn 0.5s ease both; }
    .up-card:nth-child(1) { animation-delay: 0.05s; }
    .up-card:nth-child(2) { animation-delay: 0.15s; }
    .up-card:nth-child(3) { animation-delay: 0.25s; }
    @keyframes cardFadeIn {
        from { opacity: 0; transform: translateY(24px); }
        to   { opacity: 1; transform: translateY(0); }
    }
</style>