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
</style>
