<style>
    .workspace-shell {
        font-size: 0.875rem;
        line-height: 1.6;
    }

    .workspace-shell :is(a, button, input, select, textarea):focus-visible {
        outline: 2px solid var(--theme-accent, #10b981);
        outline-offset: 3px;
    }

    .workspace-shell > aside nav > a {
        min-height: 42px;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 600;
    }

    .workspace-shell > aside nav > div[class*="uppercase"] {
        padding-top: 1.5rem;
        padding-bottom: 0.5rem;
        font-size: 0.625rem;
        letter-spacing: 0.12em;
        font-weight: 600;
    }

    .workspace-shell main table {
        font-variant-numeric: tabular-nums;
    }

    .dashboard-overview .dashboard-actions > a {
        min-height: 42px;
        border-radius: 8px;
    }

    .dashboard-overview .dashboard-metrics > :is(a, button) {
        align-items: flex-start;
        min-height: 112px;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgb(15 23 42 / 0.025);
    }

    .dashboard-overview .dashboard-metrics .leading-none {
        font-weight: 700;
        font-variant-numeric: tabular-nums;
        letter-spacing: -0.035em;
    }

    .dashboard-overview .dashboard-metrics .truncate {
        white-space: normal;
        font-size: 0.6875rem;
        line-height: 1.5;
        font-weight: 500;
        letter-spacing: 0;
        text-transform: none;
    }

    @media (prefers-reduced-motion: reduce) {
        .workspace-shell *, .workspace-shell *::before, .workspace-shell *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
            scroll-behavior: auto !important;
        }
    }

    /* Owner operations: reuse the dashboard design tokens; never affect member/trainer portals or printable documents. */
    .ds-body .operations-workspace { min-width:0; color:rgb(var(--c-fg)); font-size:14px; line-height:1.5; }
    .operations-workspace :is(h1,h2,h3,h4) { font-weight:600; letter-spacing:normal; }
    .operations-workspace h1 { font-size:20px; line-height:28px; }
    .operations-workspace :is(h2,h3,h4) { font-size:14px; line-height:20px; }
    .operations-workspace :is(.enquiry-workspace,.agreement-workspace) {
        --eq-surface:rgb(var(--c-surface)); --eq-line:rgb(var(--c-line)); --eq-muted:rgb(var(--c-fg-muted)); --eq-text:rgb(var(--c-fg)); --eq-soft:rgb(var(--c-raised));
        --ag-surface:rgb(var(--c-surface)); --ag-line:rgb(var(--c-line)); --ag-muted:rgb(var(--c-fg-muted)); --ag-text:rgb(var(--c-fg)); --ag-soft:rgb(var(--c-raised));
    }
    .operations-workspace :is(.enquiry-kicker,.agreement-eyebrow) { font-size:12px; letter-spacing:normal; text-transform:none; }
    .operations-workspace :is(.enquiry-summary dd,.agreement-metric strong) { font-size:24px; margin-top:6px; letter-spacing:normal; }
    .operations-workspace :is(.enquiry-summary>div,.agreement-metric) { padding:14px 16px; }
    .operations-workspace .agreement-metric p { min-height:0; }
    .operations-workspace .agreement-button-primary { background:rgb(var(--c-accent)); border-color:rgb(var(--c-accent)); color:rgb(var(--c-on-accent)); }
    .operations-workspace .agreement-button-primary:hover { background:rgb(var(--c-accent-hover)); }
    .operations-workspace .metric-all { border-top-color:rgb(var(--c-line-strong)); }
    .operations-workspace .metric-completed strong { color:rgb(var(--c-success)); }
    .operations-workspace .metric-pending strong { color:rgb(var(--c-warning)); }
    .operations-workspace .metric-health strong { color:rgb(var(--c-danger)); }
    .operations-workspace :is(.enquiry-panel,.agreement-overview,.enquiry-summary) { border-radius:12px; }
    .operations-workspace :is(.enquiry-panel,.agreement-table) th { font-size:12px; letter-spacing:normal; text-transform:none; }
    .operations-workspace :is(.enquiry-panel,.agreement-table) td { padding-block:11px; }
    .operations-workspace :is(input:not([type]),input[type=text],input[type=search],input[type=email],input[type=tel],input[type=number],input[type=date],input[type=time],input[type=password],input[type=datetime-local],select,textarea) {
        background:rgb(var(--c-surface)); color:rgb(var(--c-fg)); border-color:rgb(var(--c-line-strong)); border-radius:8px; font-size:14px; box-shadow:none;
    }
    .operations-workspace :is(input:not([type]),input[type=text],input[type=search],input[type=email],input[type=tel],input[type=number],input[type=date],input[type=time],input[type=password],input[type=datetime-local],select) { min-height:36px; }
    .operations-workspace :is(input,select,textarea):focus-visible { outline:none; border-color:rgb(var(--c-accent)); box-shadow:none; }
    .operations-workspace table { font-variant-numeric:tabular-nums; }
    .operations-workspace thead { background:rgb(var(--c-raised)); color:rgb(var(--c-fg-muted)); }
    .operations-workspace th { font-size:12px; font-weight:600; text-transform:none; letter-spacing:normal; }
    .operations-workspace tbody tr:hover { background:rgb(var(--c-raised)); }
    .operations-workspace :is(a,button)[href^="https://wa.me/"] { color:#128C4B; background:rgba(37,211,102,.10); border-color:rgba(37,211,102,.24); border-radius:8px; }
    html.dark .operations-workspace :is(a,button)[href^="https://wa.me/"] { color:#25D366; }
    .operations-workspace :is(a,button)[href^="https://wa.me/"]:hover { background:rgba(37,211,102,.18); }
    .operations-workspace .ops-primary { background:rgb(var(--c-accent)); color:rgb(var(--c-on-accent)); border-color:rgb(var(--c-accent)); }
    .operations-workspace .ops-primary:hover { background:rgb(var(--c-accent-hover)); }
    .operations-workspace .ops-secondary { background:rgb(var(--c-surface)); color:rgb(var(--c-fg)); border-color:rgb(var(--c-line-strong)); }
    .operations-workspace .ops-secondary:hover { background:rgb(var(--c-raised)); }
    .operations-workspace :is(.ops-primary,.ops-secondary) { border-radius:8px; min-height:36px; font-size:14px; font-weight:500; box-shadow:none; }
    .operations-workspace .ops-kpi { font-size:24px; font-weight:600; font-variant-numeric:tabular-nums; }
    .operations-workspace .ops-status { border-radius:999px; font-size:12px; font-weight:500; }
    @media(max-width:640px) {
        .operations-workspace :is(.enquiry-heading,.agreement-page-header) { gap:12px; }
        .operations-workspace :is(.enquiry-summary>div,.agreement-metric) { padding:12px; }
        .operations-workspace :is(.enquiry-summary dd,.agreement-metric strong) { font-size:24px; }
    }

</style>
