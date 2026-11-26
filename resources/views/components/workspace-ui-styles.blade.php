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


    /* Legacy utility compatibility for dynamic Blade/Alpine classes, scoped to owner pages. */
    .operations-workspace :is(.font-black,.font-extrabold,.font-bold,.sm\:font-bold,.sm\:font-black,.lg\:font-black) { font-weight:600; }
    .operations-workspace :is(.text-\[9px\],.text-\[10px\],.text-\[10\.5px\],.text-\[11px\],.text-\[11\.5px\],.sm\:text-\[10px\],.sm\:text-\[11px\]) { font-size:12px; }
    .operations-workspace :is(.rounded-2xl,.rounded-3xl,.sm\:rounded-2xl,.sm\:rounded-3xl) { border-radius:12px; }
    .operations-workspace :is(.shadow-sm,.shadow-md,.shadow-lg,.shadow-xl,.shadow-2xl,.shadow-inner) { box-shadow:none; }
    .operations-workspace :is(.uppercase,.tracking-widest,.tracking-wider,.tracking-wide) { text-transform:none; letter-spacing:normal; }
    .operations-workspace :is(.animate-ping,.animate-pulse) { animation:none; }
    .operations-workspace :is(.bg-white,.bg-slate-900,.bg-slate-950,.bg-gray-900,.bg-gray-950,.dark\:bg-slate-900,.dark\:bg-slate-950,.dark\:bg-gray-900,.dark\:bg-gray-950) { background-color:rgb(var(--c-surface)); }
    .operations-workspace :is(.bg-slate-50,.bg-slate-100,.bg-gray-50,.bg-gray-100,.bg-slate-800,.bg-gray-800,.dark\:bg-slate-800,.dark\:bg-gray-800) { background-color:rgb(var(--c-raised)); }
    .operations-workspace :is(.border-slate-200,.border-slate-300,.border-slate-700,.border-slate-800,.border-gray-200,.border-gray-700,.border-gray-800,.dark\:border-slate-700,.dark\:border-slate-800,.dark\:border-gray-700,.dark\:border-gray-800) { border-color:rgb(var(--c-line)); }
    .operations-workspace :is(.text-slate-900,.text-slate-800,.text-gray-900,.text-gray-800,.dark\:text-white,.dark\:text-slate-100,.dark\:text-slate-200) { color:rgb(var(--c-fg)); }
    .operations-workspace :is(.text-slate-500,.text-slate-600,.text-slate-400,.text-gray-500,.text-gray-600,.dark\:text-slate-300,.dark\:text-slate-400,.dark\:text-gray-400) { color:rgb(var(--c-fg-muted)); }
    .operations-workspace :is(.text-emerald-400,.dark\:text-emerald-400,.text-emerald-500,.dark\:text-emerald-500,.text-emerald-600,.dark\:text-emerald-600,.text-emerald-700,.dark\:text-emerald-700,.text-emerald-800,.dark\:text-emerald-800) { color:rgb(var(--c-success)); }
    .operations-workspace :is(.bg-emerald-50,.bg-emerald-100,.bg-emerald-500\/10,.dark\:bg-emerald-500\/10,.bg-emerald-500\/15,.dark\:bg-emerald-500\/15,.bg-emerald-500\/20,.dark\:bg-emerald-500\/20) { background-color:rgb(var(--c-success) / .12); }
    .operations-workspace :is(.border-emerald-200,.border-emerald-300,.border-emerald-500\/20,.dark\:border-emerald-500\/20,.border-emerald-500\/30,.dark\:border-emerald-500\/30) { border-color:rgb(var(--c-success) / .24); }
    .operations-workspace :is(.text-green-400,.dark\:text-green-400,.text-green-500,.dark\:text-green-500,.text-green-600,.dark\:text-green-600,.text-green-700,.dark\:text-green-700,.text-green-800,.dark\:text-green-800) { color:rgb(var(--c-success)); }
    .operations-workspace :is(.bg-green-50,.bg-green-100,.bg-green-500\/10,.dark\:bg-green-500\/10,.bg-green-500\/15,.dark\:bg-green-500\/15,.bg-green-500\/20,.dark\:bg-green-500\/20) { background-color:rgb(var(--c-success) / .12); }
    .operations-workspace :is(.border-green-200,.border-green-300,.border-green-500\/20,.dark\:border-green-500\/20,.border-green-500\/30,.dark\:border-green-500\/30) { border-color:rgb(var(--c-success) / .24); }
    .operations-workspace :is(.text-rose-400,.dark\:text-rose-400,.text-rose-500,.dark\:text-rose-500,.text-rose-600,.dark\:text-rose-600,.text-rose-700,.dark\:text-rose-700,.text-rose-800,.dark\:text-rose-800) { color:rgb(var(--c-danger)); }
    .operations-workspace :is(.bg-rose-50,.bg-rose-100,.bg-rose-500\/10,.dark\:bg-rose-500\/10,.bg-rose-500\/15,.dark\:bg-rose-500\/15,.bg-rose-500\/20,.dark\:bg-rose-500\/20) { background-color:rgb(var(--c-danger) / .12); }
    .operations-workspace :is(.border-rose-200,.border-rose-300,.border-rose-500\/20,.dark\:border-rose-500\/20,.border-rose-500\/30,.dark\:border-rose-500\/30) { border-color:rgb(var(--c-danger) / .24); }
    .operations-workspace :is(.text-red-400,.dark\:text-red-400,.text-red-500,.dark\:text-red-500,.text-red-600,.dark\:text-red-600,.text-red-700,.dark\:text-red-700,.text-red-800,.dark\:text-red-800) { color:rgb(var(--c-danger)); }
    .operations-workspace :is(.bg-red-50,.bg-red-100,.bg-red-500\/10,.dark\:bg-red-500\/10,.bg-red-500\/15,.dark\:bg-red-500\/15,.bg-red-500\/20,.dark\:bg-red-500\/20) { background-color:rgb(var(--c-danger) / .12); }
    .operations-workspace :is(.border-red-200,.border-red-300,.border-red-500\/20,.dark\:border-red-500\/20,.border-red-500\/30,.dark\:border-red-500\/30) { border-color:rgb(var(--c-danger) / .24); }
    .operations-workspace :is(.text-amber-400,.dark\:text-amber-400,.text-amber-500,.dark\:text-amber-500,.text-amber-600,.dark\:text-amber-600,.text-amber-700,.dark\:text-amber-700,.text-amber-800,.dark\:text-amber-800) { color:rgb(var(--c-warning)); }
    .operations-workspace :is(.bg-amber-50,.bg-amber-100,.bg-amber-500\/10,.dark\:bg-amber-500\/10,.bg-amber-500\/15,.dark\:bg-amber-500\/15,.bg-amber-500\/20,.dark\:bg-amber-500\/20) { background-color:rgb(var(--c-warning) / .12); }
    .operations-workspace :is(.border-amber-200,.border-amber-300,.border-amber-500\/20,.dark\:border-amber-500\/20,.border-amber-500\/30,.dark\:border-amber-500\/30) { border-color:rgb(var(--c-warning) / .24); }
    .operations-workspace :is(.text-yellow-400,.dark\:text-yellow-400,.text-yellow-500,.dark\:text-yellow-500,.text-yellow-600,.dark\:text-yellow-600,.text-yellow-700,.dark\:text-yellow-700,.text-yellow-800,.dark\:text-yellow-800) { color:rgb(var(--c-warning)); }
    .operations-workspace :is(.bg-yellow-50,.bg-yellow-100,.bg-yellow-500\/10,.dark\:bg-yellow-500\/10,.bg-yellow-500\/15,.dark\:bg-yellow-500\/15,.bg-yellow-500\/20,.dark\:bg-yellow-500\/20) { background-color:rgb(var(--c-warning) / .12); }
    .operations-workspace :is(.border-yellow-200,.border-yellow-300,.border-yellow-500\/20,.dark\:border-yellow-500\/20,.border-yellow-500\/30,.dark\:border-yellow-500\/30) { border-color:rgb(var(--c-warning) / .24); }
    .operations-workspace :is(.text-blue-400,.dark\:text-blue-400,.text-blue-500,.dark\:text-blue-500,.text-blue-600,.dark\:text-blue-600,.text-blue-700,.dark\:text-blue-700,.text-blue-800,.dark\:text-blue-800) { color:rgb(var(--c-info)); }
    .operations-workspace :is(.bg-blue-50,.bg-blue-100,.bg-blue-500\/10,.dark\:bg-blue-500\/10,.bg-blue-500\/15,.dark\:bg-blue-500\/15,.bg-blue-500\/20,.dark\:bg-blue-500\/20) { background-color:rgb(var(--c-info) / .12); }
    .operations-workspace :is(.border-blue-200,.border-blue-300,.border-blue-500\/20,.dark\:border-blue-500\/20,.border-blue-500\/30,.dark\:border-blue-500\/30) { border-color:rgb(var(--c-info) / .24); }
    .operations-workspace :is(.text-sky-400,.dark\:text-sky-400,.text-sky-500,.dark\:text-sky-500,.text-sky-600,.dark\:text-sky-600,.text-sky-700,.dark\:text-sky-700,.text-sky-800,.dark\:text-sky-800) { color:rgb(var(--c-info)); }
    .operations-workspace :is(.bg-sky-50,.bg-sky-100,.bg-sky-500\/10,.dark\:bg-sky-500\/10,.bg-sky-500\/15,.dark\:bg-sky-500\/15,.bg-sky-500\/20,.dark\:bg-sky-500\/20) { background-color:rgb(var(--c-info) / .12); }
    .operations-workspace :is(.border-sky-200,.border-sky-300,.border-sky-500\/20,.dark\:border-sky-500\/20,.border-sky-500\/30,.dark\:border-sky-500\/30) { border-color:rgb(var(--c-info) / .24); }
    .operations-workspace :is(.bg-gradient-to-br,.bg-gradient-to-r,.bg-gradient-to-tr,.bg-gradient-to-b,.bg-gradient-to-t) { background-image:none; }
    .operations-workspace :is(button,a)[class~="ops-primary"] { background:rgb(var(--c-accent)); color:rgb(var(--c-on-accent)); }
    .operations-workspace :is(button,a)[class~="ops-primary"]:hover { background:rgb(var(--c-accent-hover)); }
    .operations-workspace :is(button,a)[class~="ops-primary"]:focus-visible { outline:2px solid rgb(var(--c-accent)); outline-offset:2px; }
    .operations-workspace :is(.bg-surface,.bg-raised).text-white { color:rgb(var(--c-fg)); }
    .operations-workspace :is(.diet-ui-scope,.workout-ui-scope) { font-family:Inter,system-ui,sans-serif; }
    .operations-workspace :is(.enquiry-kicker,.agreement-eyebrow) { font-size:12px; text-transform:none; letter-spacing:normal; }
    .operations-workspace :is(a,button).ops-action { min-height:32px; border-radius:8px; font-size:12px; font-weight:600; }
    .operations-workspace :is(a,button).ops-action-warning { color:rgb(var(--c-warning)); background:rgb(var(--c-warning) / .12); border-color:rgb(var(--c-warning) / .24); }
    .operations-workspace :is(a,button).ops-action-danger { color:rgb(var(--c-danger)); background:rgb(var(--c-danger) / .12); border-color:rgb(var(--c-danger) / .24); }
    .operations-workspace :is(a,button).ops-action-success { color:rgb(var(--c-success)); background:rgb(var(--c-success) / .12); border-color:rgb(var(--c-success) / .24); }
    .operations-workspace :is(a,button).ops-action-info { color:rgb(var(--c-info)); background:rgb(var(--c-info) / .12); border-color:rgb(var(--c-info) / .24); }

</style>
