@props(['savedTheme' => null, 'storageKey' => 'admin'])

@php
    $themePresets = [
        'blue-purple'   => '#3b82f6',
        'slate-emerald' => '#10b981',
        'indigo-rose'   => '#f43f5e',
        'teal-amber'    => '#f59e0b',
        'cyan-teal'     => '#06b6d4',
        'mint-emerald'  => '#34d399',
    ];

    $serverThemeObj = $savedTheme ?? [];
    if (is_string($serverThemeObj)) {
        $serverThemeObj = json_decode($serverThemeObj, true) ?: [];
    }

    $initialHex = '#10b981';
    if (!empty($serverThemeObj['isCustom']) && !empty($serverThemeObj['customHex'])) {
        $initialHex = $serverThemeObj['customHex'];
    } elseif (!empty($serverThemeObj['accent']) && isset($themePresets[$serverThemeObj['accent']])) {
        $initialHex = $themePresets[$serverThemeObj['accent']];
    }

    $cleanHex = ltrim($initialHex, '#');
    if (strlen($cleanHex) === 3) {
        $cleanHex = $cleanHex[0].$cleanHex[0].$cleanHex[1].$cleanHex[1].$cleanHex[2].$cleanHex[2];
    }
    $r = hexdec(substr($cleanHex, 0, 2) ?: '10');
    $g = hexdec(substr($cleanHex, 2, 2) ?: 'b9');
    $b = hexdec(substr($cleanHex, 4, 2) ?: '81');
    $initialRgbStr = "{$r}, {$g}, {$b}";
@endphp

<!-- Server-Rendered Zero-FOUC Theme Styles (Prevents Green Flash on page load) -->
<style id="theme-accent-dynamic-style" data-hex="{{ $initialHex }}">
    :root {
        --theme-accent: {{ $initialHex }} !important;
        --theme-accent-light: rgba({{ $initialRgbStr }}, 0.15) !important;
        --theme-accent-border: rgba({{ $initialRgbStr }}, 0.3) !important;
        --theme-accent-glow: rgba({{ $initialRgbStr }}, 0.35) !important;
    }

    .theme-save-btn,
    .btn-primary,
    .theme-btn-primary,
    .bg-amber-500,
    .bg-red-500,
    .bg-emerald-500,
    .bg-emerald-600 {
        background-color: {{ $initialHex }} !important;
        color: #ffffff !important;
    }

    .hover\:bg-emerald-600:hover,
    .hover\:bg-emerald-500:hover,
    .hover\:bg-amber-600:hover,
    .hover\:bg-red-600:hover {
        background-color: {{ $initialHex }} !important;
        filter: brightness(0.9);
    }

    aside nav a.bg-amber-500,
    aside nav a.bg-red-500,
    aside nav a.bg-emerald-500,
    aside nav a[class*="bg-amber-500"],
    aside nav a[class*="bg-red-500"],
    aside nav a[class*="bg-emerald-500"] {
        background-color: {{ $initialHex }} !important;
        color: #ffffff !important;
        box-shadow: 0 4px 14px 0 rgba({{ $initialRgbStr }}, 0.35) !important;
    }

    html.light aside nav a.bg-amber-500,
    html.light aside nav a.bg-red-500,
    html.light aside nav a.bg-emerald-500,
    html.light aside nav a[class*="bg-amber-500"],
    html.light aside nav a[class*="bg-red-500"],
    html.light aside nav a[class*="bg-emerald-500"] {
        background-color: rgba({{ $initialRgbStr }}, 0.12) !important;
        color: {{ $initialHex }} !important;
        border: 1px solid rgba({{ $initialRgbStr }}, 0.25) !important;
        box-shadow: none !important;
        font-weight: 700 !important;
    }

    .text-amber-400,
    .text-amber-500,
    .text-red-400,
    .text-emerald-400,
    .text-emerald-500,
    .text-emerald-600,
    .text-cyan-400,
    .theme-accent-text {
        color: {{ $initialHex }} !important;
    }

    .hover\:text-emerald-400:hover,
    .hover\:text-emerald-300:hover,
    .hover\:text-emerald-600:hover,
    .hover\:text-amber-400:hover {
        color: {{ $initialHex }} !important;
    }

    .bg-amber-500\/5,
    .bg-emerald-500\/5,
    .bg-amber-500\/10,
    .bg-red-500\/10,
    .bg-emerald-500\/10,
    .bg-amber-500\/15,
    .bg-red-500\/15,
    .bg-emerald-500\/15,
    .bg-emerald-500\/20,
    .bg-emerald-100,
    .bg-emerald-50,
    .bg-emerald-50\/50 {
        background-color: rgba({{ $initialRgbStr }}, 0.14) !important;
        color: {{ $initialHex }} !important;
    }

    .border-amber-500,
    .border-red-500,
    .border-emerald-500,
    .border-amber-500\/20,
    .border-red-500\/20,
    .border-emerald-500\/15,
    .border-emerald-500\/20,
    .border-emerald-500\/25,
    .border-emerald-500\/30,
    .border-emerald-500\/40,
    .border-emerald-200 {
        border-color: rgba({{ $initialRgbStr }}, 0.35) !important;
    }

    .focus\:ring-amber-500:focus,
    .focus\:ring-emerald-500:focus,
    .focus\:border-amber-500:focus,
    .focus\:border-emerald-500:focus,
    .ring-emerald-500\/30,
    .ring-emerald-500\/50 {
        --tw-ring-color: {{ $initialHex }} !important;
        border-color: {{ $initialHex }} !important;
    }
</style>

<!-- Synchronous Zero-FOUC Theme Initializer (Must execute in head before body render) -->
<script>
    window.__SAVED_THEME__ = {{ Js::from($savedTheme ?? null) }};
    window.__THEME_STORAGE_KEY__ = '{{ $storageKey ?? "admin" }}';

    (function() {
        try {
            const presets = {
                'blue-purple': '#3b82f6',
                'slate-emerald': '#10b981',
                'indigo-rose': '#f43f5e',
                'teal-amber': '#f59e0b',
                'cyan-teal': '#06b6d4',
                'mint-emerald': '#34d399'
            };
            const serverTheme = window.__SAVED_THEME__;
            const storageKey = 'gym_console_theme_' + window.__THEME_STORAGE_KEY__;
            let theme = serverTheme;
            const local = localStorage.getItem(storageKey) || localStorage.getItem('gym_console_theme_settings');
            if (local) {
                try {
                    const parsed = JSON.parse(local);
                    if (parsed && typeof parsed === 'object') {
                        theme = Object.assign({}, theme || {}, parsed);
                    }
                } catch(e) {}
            }

            if (theme && theme.mode === 'light') {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            }

            if (theme && theme.direction) {
                document.documentElement.setAttribute('dir', theme.direction);
            }

            if (theme && theme.fontSize) {
                const scale = { 'sm': '92%', 'md': '100%', 'lg': '108%' }[theme.fontSize] || '100%';
                document.documentElement.style.fontSize = scale;
            }

            if (theme && theme.fontFamily) {
                document.body ? (document.body.style.fontFamily = `'${theme.fontFamily}', sans-serif`) : null;
            }

            // Sync accent if localStorage differs from server
            if (theme) {
                let hex = (theme.isCustom && theme.customHex) ? theme.customHex : (presets[theme.accent] || '#10b981');
                let clean = hex.replace('#', '');
                if (clean.length === 3) clean = clean.split('').map(x => x + x).join('');
                const num = parseInt(clean, 16) || 0;
                const r = (num >> 16) & 255;
                const g = (num >> 8) & 255;
                const b = num & 255;
                const rgbStr = `${r}, ${g}, ${b}`;

                document.documentElement.style.setProperty('--theme-accent', hex);
                document.documentElement.style.setProperty('--theme-accent-rgb', rgbStr);

                let styleTag = document.getElementById('theme-accent-dynamic-style');
                if (!styleTag) {
                    styleTag = document.createElement('style');
                    styleTag.id = 'theme-accent-dynamic-style';
                    document.head.appendChild(styleTag);
                }

                if (styleTag.getAttribute('data-hex') !== hex) {
                    styleTag.setAttribute('data-hex', hex);
                    styleTag.innerHTML = `
                        :root {
                            --theme-accent: ${hex} !important;
                            --theme-accent-light: rgba(${rgbStr}, 0.15) !important;
                            --theme-accent-border: rgba(${rgbStr}, 0.3) !important;
                            --theme-accent-glow: rgba(${rgbStr}, 0.35) !important;
                        }
                        .theme-save-btn, .btn-primary, .theme-btn-primary, .bg-amber-500, .bg-red-500, .bg-emerald-500, .bg-emerald-600 {
                            background-color: ${hex} !important;
                            color: #ffffff !important;
                        }
                        .hover\\:bg-emerald-600:hover, .hover\\:bg-emerald-500:hover, .hover\\:bg-amber-600:hover, .hover\\:bg-red-600:hover {
                            background-color: ${hex} !important;
                            filter: brightness(0.9);
                        }
                        aside nav a.bg-amber-500, aside nav a.bg-red-500, aside nav a.bg-emerald-500,
                        aside nav a[class*="bg-amber-500"], aside nav a[class*="bg-red-500"], aside nav a[class*="bg-emerald-500"] {
                            background-color: ${hex} !important;
                            color: #ffffff !important;
                            box-shadow: 0 4px 14px 0 rgba(${rgbStr}, 0.35) !important;
                        }
                        html.light aside nav a.bg-amber-500, html.light aside nav a.bg-red-500, html.light aside nav a.bg-emerald-500,
                        html.light aside nav a[class*="bg-amber-500"], html.light aside nav a[class*="bg-red-500"], html.light aside nav a[class*="bg-emerald-500"] {
                            background-color: rgba(${rgbStr}, 0.12) !important;
                            color: ${hex} !important;
                            border: 1px solid rgba(${rgbStr}, 0.25) !important;
                            box-shadow: none !important;
                            font-weight: 700 !important;
                        }
                        .text-amber-400, .text-amber-500, .text-red-400, .text-emerald-400, .text-emerald-500, .text-emerald-600, .text-cyan-400, .theme-accent-text {
                            color: ${hex} !important;
                        }
                        .hover\\:text-emerald-400:hover, .hover\\:text-emerald-300:hover, .hover\\:text-emerald-600:hover, .hover\\:text-amber-400:hover {
                            color: ${hex} !important;
                        }
                        .bg-amber-500\\/5, .bg-emerald-500\\/5, .bg-amber-500\\/10, .bg-red-500\\/10, .bg-emerald-500\\/10, .bg-amber-500\\/15, .bg-red-500\\/15, .bg-emerald-500\\/15, .bg-emerald-500\\/20, .bg-emerald-100, .bg-emerald-50, .bg-emerald-50\\/50 {
                            background-color: rgba(${rgbStr}, 0.14) !important;
                            color: ${hex} !important;
                        }
                        .border-amber-500, .border-red-500, .border-emerald-500, .border-amber-500\\/20, .border-red-500\\/20, .border-emerald-500\\/15, .border-emerald-500\\/20, .border-emerald-500\\/25, .border-emerald-500\\/30, .border-emerald-500\\/40, .border-emerald-200 {
                            border-color: rgba(${rgbStr}, 0.35) !important;
                        }
                        .focus\\:ring-amber-500:focus, .focus\\:ring-emerald-500:focus, .focus\\:border-amber-500:focus, .focus\\:border-emerald-500:focus, .ring-emerald-500\\/30, .ring-emerald-500\\/50 {
                            --tw-ring-color: ${hex} !important;
                            border-color: ${hex} !important;
                        }
                    `;
                }
            }
        } catch (e) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>

