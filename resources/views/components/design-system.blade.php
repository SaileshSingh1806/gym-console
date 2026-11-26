{{--
    GymDeskHub design system — single source of truth for colour, type and component primitives.

    Rules
    - Neutral surfaces + ONE brand accent (amber, taken from the logo). Status colours are reserved for status.
    - One typeface (Inter). Sizes: 12 / 13 / 14 / 16 / 20 / 24. Nothing smaller than 12px.
    - Radius: 8px for controls, 12px for cards. Depth comes from 1px borders, not shadows.
    - Dark is the default; `html.light` swaps the same tokens. Values are RGB channels so Tailwind can apply alpha.
--}}
<style>
    :root,
    html.dark {
        color-scheme: dark;
        --c-canvas: 13 14 16;
        --c-surface: 20 21 24;
        --c-raised: 27 29 33;
        --c-line: 38 40 45;
        --c-line-strong: 55 58 65;
        --c-fg: 236 237 239;
        --c-fg-muted: 160 164 171;
        --c-fg-subtle: 112 116 124;
        --c-accent: 245 165 36;
        --c-accent-hover: 224 148 20;
        --c-accent-fg: 245 165 36;
        --c-on-accent: 22 19 11;
        --c-success: 74 190 140;
        --c-warning: 229 176 46;
        --c-danger: 240 98 98;
        --c-info: 96 160 240;
    }

    html.light {
        color-scheme: light;
        --c-canvas: 246 246 244;
        --c-surface: 255 255 255;
        --c-raised: 241 241 238;
        --c-line: 228 228 223;
        --c-line-strong: 210 210 203;
        --c-fg: 23 24 27;
        --c-fg-muted: 92 95 102;
        --c-fg-subtle: 138 141 148;
        --c-accent: 245 165 36;
        --c-accent-hover: 224 148 20;
        --c-accent-fg: 146 84 0;
        --c-on-accent: 22 19 11;
        --c-success: 21 128 85;
        --c-warning: 161 98 7;
        --c-danger: 200 40 40;
        --c-info: 29 98 200;
    }

    html { -webkit-text-size-adjust: 100%; }

    body.ds-body {
        font-family: 'Inter', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
        background-color: rgb(var(--c-canvas));
        color: rgb(var(--c-fg));
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    .ds-body ::selection { background: rgb(var(--c-accent) / 0.28); }

    .ds-body :is(a, button, input, select, textarea, [tabindex]):focus-visible {
        outline: 2px solid rgb(var(--c-accent));
        outline-offset: 2px;
    }

    /* Scrollbars: thin and quiet */
    .ds-body * { scrollbar-width: thin; scrollbar-color: rgb(var(--c-line-strong)) transparent; }
    .ds-body ::-webkit-scrollbar { width: 8px; height: 8px; }
    .ds-body ::-webkit-scrollbar-track { background: transparent; }
    .ds-body ::-webkit-scrollbar-thumb { background: rgb(var(--c-line-strong)); border-radius: 9999px; }

    .tabular { font-variant-numeric: tabular-nums; }

    /* ───────── Surfaces ───────── */
    .ds-card {
        background: rgb(var(--c-surface));
        border: 1px solid rgb(var(--c-line));
        border-radius: 12px;
    }
    /* KPI strip: cells divided by hairlines inside one bordered container */
    .ds-stat-grid {
        display: grid; gap: 1px; overflow: hidden;
        background: rgb(var(--c-line));
        border: 1px solid rgb(var(--c-line));
        border-radius: 12px;
    }
    .ds-card-link { transition: border-color .15s ease, background-color .15s ease; }
    .ds-card-link:hover { border-color: rgb(var(--c-line-strong)); background: rgb(var(--c-raised) / 0.6); }

    /* ───────── Buttons ───────── */
    .ds-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        height: 36px; padding: 0 14px;
        border-radius: 8px; border: 1px solid transparent;
        font-size: 13px; font-weight: 500; line-height: 1; white-space: nowrap;
        cursor: pointer; transition: background-color .15s ease, border-color .15s ease, color .15s ease;
    }
    .ds-btn-primary { background: rgb(var(--c-accent)); color: rgb(var(--c-on-accent)); }
    .ds-btn-primary:hover { background: rgb(var(--c-accent-hover)); }
    .ds-btn-secondary { background: rgb(var(--c-surface)); color: rgb(var(--c-fg)); border-color: rgb(var(--c-line-strong)); }
    .ds-btn-secondary:hover { background: rgb(var(--c-raised)); }
    .ds-btn-ghost { background: transparent; color: rgb(var(--c-fg-muted)); }
    .ds-btn-ghost:hover { background: rgb(var(--c-raised)); color: rgb(var(--c-fg)); }
    .ds-btn[disabled] { opacity: .5; cursor: not-allowed; }

    /* ───────── Badges (status only) ───────── */
    .ds-badge {
        display: inline-flex; align-items: center; gap: 6px;
        height: 22px; padding: 0 8px; border-radius: 6px;
        font-size: 12px; font-weight: 500; line-height: 1; white-space: nowrap;
    }
    .ds-badge-neutral { background: rgb(var(--c-raised)); color: rgb(var(--c-fg-muted)); }
    .ds-badge-success { background: rgb(var(--c-success) / 0.12); color: rgb(var(--c-success)); }
    .ds-badge-warning { background: rgb(var(--c-warning) / 0.14); color: rgb(var(--c-warning)); }
    .ds-badge-danger  { background: rgb(var(--c-danger) / 0.12);  color: rgb(var(--c-danger)); }
    .ds-badge-info    { background: rgb(var(--c-info) / 0.12);    color: rgb(var(--c-info)); }
    .ds-dot { width: 6px; height: 6px; border-radius: 9999px; background: currentColor; }

    /* ───────── Form controls ───────── */
    .ds-label { display: block; font-size: 12px; font-weight: 500; color: rgb(var(--c-fg-muted)); margin-bottom: 6px; }
    .ds-input, .ds-select {
        width: 100%; height: 36px; padding: 0 12px;
        background: rgb(var(--c-surface)); color: rgb(var(--c-fg));
        border: 1px solid rgb(var(--c-line-strong)); border-radius: 8px;
        font-size: 14px; transition: border-color .15s ease;
    }
    .ds-input::placeholder { color: rgb(var(--c-fg-subtle)); }
    .ds-input:focus, .ds-select:focus { outline: none; border-color: rgb(var(--c-accent)); }

    /* ───────── Typography helpers ───────── */
    .ds-title   { font-size: 20px; line-height: 28px; font-weight: 600; letter-spacing: -0.01em; color: rgb(var(--c-fg)); }
    .ds-heading { font-size: 14px; line-height: 20px; font-weight: 600; color: rgb(var(--c-fg)); }
    .ds-caption { font-size: 12px; line-height: 16px; font-weight: 500; color: rgb(var(--c-fg-muted)); }
    .ds-kpi     { font-size: 24px; line-height: 32px; font-weight: 600; letter-spacing: -0.02em; font-variant-numeric: tabular-nums; color: rgb(var(--c-fg)); }

    /* ───────── Sidebar navigation ───────── */
    .ds-nav-section { padding: 18px 10px 6px; font-size: 12px; line-height: 16px; font-weight: 500; color: rgb(var(--c-fg-subtle)); }
    .ds-nav-item {
        display: flex; align-items: center; gap: 10px;
        height: 36px; padding: 0 10px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: rgb(var(--c-fg-muted));
        transition: background-color .12s ease, color .12s ease;
    }
    .ds-nav-item:hover { background: rgb(var(--c-raised)); color: rgb(var(--c-fg)); }
    .ds-nav-item[aria-current="page"] { background: rgb(var(--c-raised)); color: rgb(var(--c-fg)); }
    .ds-nav-item[aria-current="page"] .ds-nav-icon,
    .ds-nav-item[data-active="true"] .ds-nav-icon { color: rgb(var(--c-accent-fg)); }
    .ds-nav-item[data-active="true"] { color: rgb(var(--c-fg)); }
    .ds-nav-icon { color: rgb(var(--c-fg-subtle)); }
    .ds-nav-chevron { margin-left: auto; color: rgb(var(--c-fg-subtle)); transition: transform .15s ease; display: inline-flex; }
    .ds-nav-count {
        margin-left: auto; min-width: 20px; height: 20px; padding: 0 6px; border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 500; font-variant-numeric: tabular-nums;
        background: rgb(var(--c-accent) / 0.16); color: rgb(var(--c-accent-fg));
    }
    .ds-nav-children { margin: 2px 0 4px 19px; padding-left: 8px; border-left: 1px solid rgb(var(--c-line)); }
    .ds-nav-sub {
        display: flex; align-items: center; height: 32px; padding: 0 10px; border-radius: 8px;
        font-size: 13px; font-weight: 500; color: rgb(var(--c-fg-muted));
        transition: background-color .12s ease, color .12s ease;
    }
    .ds-nav-sub:hover { background: rgb(var(--c-raised)); color: rgb(var(--c-fg)); }
    .ds-nav-sub[aria-current="page"] { background: rgb(var(--c-raised)); color: rgb(var(--c-fg)); }

    @media (prefers-reduced-motion: reduce) {
        .ds-body *, .ds-body *::before, .ds-body *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
