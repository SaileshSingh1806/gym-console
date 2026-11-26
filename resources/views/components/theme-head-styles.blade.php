@props(['savedTheme' => null, 'storageKey' => 'admin'])

{{--
    Theme bootstrap (runs in <head>, before first paint).

    - Light / dark mode, text size and text direction are still user preferences.
    - The brand accent and typeface are NOT user preferences any more. They live in design-system.blade.php.
      The old accent picker re-coloured every .bg-red-500 / .bg-emerald-500 / .text-red-400 on the page with
      !important, which turned danger and success states into whatever accent the user picked.
    - Member and trainer apps keep their champagne-gold skin (legacy block below) until they are migrated.
--}}
@php
    $legacyAccent = in_array($storageKey, ['member_portal', 'trainer_portal'], true);

    $serverThemeObj = $savedTheme ?? [];
    if (is_string($serverThemeObj)) {
        $serverThemeObj = json_decode($serverThemeObj, true) ?: [];
    }

    $legacyHex = '#C5A880';
    if ($legacyAccent && !empty($serverThemeObj['isCustom']) && !empty($serverThemeObj['customHex'])) {
        $legacyHex = $serverThemeObj['customHex'];
    }

    $cleanHex = ltrim($legacyHex, '#');
    if (strlen($cleanHex) === 3) {
        $cleanHex = $cleanHex[0].$cleanHex[0].$cleanHex[1].$cleanHex[1].$cleanHex[2].$cleanHex[2];
    }
    $legacyRgb = hexdec(substr($cleanHex, 0, 2) ?: 'c5') . ', ' . hexdec(substr($cleanHex, 2, 2) ?: 'a8') . ', ' . hexdec(substr($cleanHex, 4, 2) ?: '80');
@endphp

<style id="theme-accent-dynamic-style" data-hex="{{ $legacyAccent ? $legacyHex : 'brand' }}">
    :root {
        --theme-accent: {{ $legacyAccent ? $legacyHex : '#F5A524' }};
    }
@if($legacyAccent)
    :root {
        --theme-accent-light: rgba({{ $legacyRgb }}, 0.15) !important;
        --theme-accent-border: rgba({{ $legacyRgb }}, 0.3) !important;
        --theme-accent-glow: rgba({{ $legacyRgb }}, 0.35) !important;
    }

    .theme-save-btn,
    .btn-primary,
    .theme-btn-primary,
    .bg-amber-500,
    .bg-red-500,
    .bg-emerald-500,
    .bg-emerald-600 {
        background-color: {{ $legacyHex }} !important;
        color: #ffffff !important;
    }

    .hover\:bg-emerald-600:hover,
    .hover\:bg-emerald-500:hover,
    .hover\:bg-amber-600:hover,
    .hover\:bg-red-600:hover {
        background-color: {{ $legacyHex }} !important;
        filter: brightness(0.9);
    }

    .text-amber-400,
    .text-amber-500,
    .text-red-400,
    .text-emerald-400,
    .text-emerald-500,
    .text-emerald-600,
    .text-cyan-400,
    .theme-accent-text {
        color: {{ $legacyHex }} !important;
    }

    .hover\:text-emerald-400:hover,
    .hover\:text-emerald-300:hover,
    .hover\:text-emerald-600:hover,
    .hover\:text-amber-400:hover {
        color: {{ $legacyHex }} !important;
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
        background-color: rgba({{ $legacyRgb }}, 0.14) !important;
        color: {{ $legacyHex }} !important;
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
        border-color: rgba({{ $legacyRgb }}, 0.35) !important;
    }

    .focus\:ring-amber-500:focus,
    .focus\:ring-emerald-500:focus,
    .focus\:border-amber-500:focus,
    .focus\:border-emerald-500:focus,
    .ring-emerald-500\/30,
    .ring-emerald-500\/50 {
        --tw-ring-color: {{ $legacyHex }} !important;
        border-color: {{ $legacyHex }} !important;
    }
@endif
</style>

<!-- Synchronous theme initializer (must execute in <head> before body renders) -->
<script>
    window.__SAVED_THEME__ = {{ Js::from($savedTheme ?? null) }};
    window.__THEME_STORAGE_KEY__ = '{{ $storageKey ?? "admin" }}';

    (function() {
        try {
            const serverTheme = window.__SAVED_THEME__;
            const storageKey = 'gym_console_theme_' + window.__THEME_STORAGE_KEY__;
            let theme = serverTheme;
            const isMember = (window.__THEME_STORAGE_KEY__ === 'member_portal');
            const local = isMember ? localStorage.getItem(storageKey) : (localStorage.getItem(storageKey) || localStorage.getItem('gym_console_theme_settings'));
            if (local) {
                try {
                    const parsed = JSON.parse(local);
                    if (parsed && typeof parsed === 'object') {
                        theme = Object.assign({}, theme || {}, parsed);
                    }
                } catch (e) {}
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
        } catch (e) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
