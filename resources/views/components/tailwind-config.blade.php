{{-- Must render AFTER the Tailwind CDN <script>. Maps design tokens (see design-system.blade.php) to utilities. --}}
<script>
    tailwind.config = {
        darkMode: 'class',
        theme: {
            extend: {
                screens: { 'xs': '420px' },
                fontFamily: {
                    sans: ['Inter', 'ui-sans-serif', 'system-ui', '-apple-system', 'Segoe UI', 'Roboto', 'sans-serif'],
                },
                colors: {
                    canvas: 'rgb(var(--c-canvas) / <alpha-value>)',
                    surface: 'rgb(var(--c-surface) / <alpha-value>)',
                    raised: 'rgb(var(--c-raised) / <alpha-value>)',
                    line: {
                        DEFAULT: 'rgb(var(--c-line) / <alpha-value>)',
                        strong: 'rgb(var(--c-line-strong) / <alpha-value>)',
                    },
                    fg: {
                        DEFAULT: 'rgb(var(--c-fg) / <alpha-value>)',
                        muted: 'rgb(var(--c-fg-muted) / <alpha-value>)',
                        subtle: 'rgb(var(--c-fg-subtle) / <alpha-value>)',
                    },
                    accent: {
                        DEFAULT: 'rgb(var(--c-accent) / <alpha-value>)',
                        fg: 'rgb(var(--c-accent-fg) / <alpha-value>)',
                        on: 'rgb(var(--c-on-accent) / <alpha-value>)',
                    },
                    success: 'rgb(var(--c-success) / <alpha-value>)',
                    warning: 'rgb(var(--c-warning) / <alpha-value>)',
                    danger: 'rgb(var(--c-danger) / <alpha-value>)',
                    info: 'rgb(var(--c-info) / <alpha-value>)',
                },
            },
        },
    };
</script>
