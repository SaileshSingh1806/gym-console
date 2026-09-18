<script>
function googleTranslateElementInit() {
    if (window.google && window.google.translate) {
        new window.google.translate.TranslateElement({
            pageLanguage: 'en',
            includedLanguages: 'en,hi,es,fr,ar,de,it,pt,ru,ja,zh-CN',
            autoDisplay: false
        }, 'google_translate_element');
    }
}

function themeManager(serverSettings = null, storageKey = 'admin') {
    return {
        drawerOpen: false,
        langDropdownOpen: false,
        saving: false,
        showCustomPicker: false,
        activeTab: 'layout',
        selectedLang: 'en',
        storageKey: 'gym_console_theme_' + storageKey,
        pickerHue: 160,
        pickerDotX: 65,
        pickerDotY: 30,
        rgb: { r: 16, g: 185, b: 129 },
        languages: [
            { code: 'en', name: 'English', native: 'English', flag: '🇺🇸', dir: 'ltr' },
            { code: 'hi', name: 'Hindi', native: 'हिन्दी', flag: '🇮🇳', dir: 'ltr' },
            { code: 'es', name: 'Spanish', native: 'Español', flag: '🇪🇸', dir: 'ltr' },
            { code: 'fr', name: 'French', native: 'Français', flag: '🇫🇷', dir: 'ltr' },
            { code: 'ar', name: 'Arabic', native: 'العربية', flag: '🇸🇦', dir: 'rtl' },
            { code: 'de', name: 'German', native: 'Deutsch', flag: '🇩🇪', dir: 'ltr' },
            { code: 'it', name: 'Italian', native: 'Italiano', flag: '🇮🇹', dir: 'ltr' },
            { code: 'pt', name: 'Portuguese', native: 'Português', flag: '🇧🇷', dir: 'ltr' },
            { code: 'ru', name: 'Russian', native: 'Русский', flag: '🇷🇺', dir: 'ltr' },
            { code: 'ja', name: 'Japanese', native: '日本語', flag: '🇯🇵', dir: 'ltr' },
            { code: 'zh-CN', name: 'Chinese', native: '中文', flag: '🇨🇳', dir: 'ltr' },
        ],
        colorPresets: [
            { id: 'blue-purple', name: 'Blue Violet', hex: '#3b82f6', hover: '#2563eb', gradient: 'linear-gradient(135deg, #3b82f6 50%, #8b5cf6 50%)' },
            { id: 'slate-emerald', name: 'Teal Jade', hex: '#10b981', hover: '#059669', gradient: 'linear-gradient(135deg, #64748b 50%, #10b981 50%)' },
            { id: 'indigo-rose', name: 'Crimson Pink', hex: '#f43f5e', hover: '#e11d48', gradient: 'linear-gradient(135deg, #1e293b 50%, #f43f5e 50%)' },
            { id: 'teal-amber', name: 'Amber Gold', hex: '#f59e0b', hover: '#d97706', gradient: 'linear-gradient(135deg, #0f766e 50%, #f59e0b 50%)' },
            { id: 'cyan-teal', name: 'Cyan Blue', hex: '#06b6d4', hover: '#0891b2', gradient: 'linear-gradient(135deg, #134e4a 50%, #06b6d4 50%)' },
            { id: 'mint-emerald', name: 'Mint Green', hex: '#34d399', hover: '#10b981', gradient: 'linear-gradient(135deg, #064e3b 50%, #34d399 50%)' },
        ],
        fontFamilies: [
            { name: 'Plus Jakarta Sans', preview: 'Modern Clean Sans' },
            { name: 'Inter', preview: 'Crisp Interface UI' },
            { name: 'Poppins', preview: 'Geometric & Friendly' },
            { name: 'Outfit', preview: 'Bold Futuristic Sans' },
            { name: 'Montserrat', preview: 'Premium Architectural' },
            { name: 'Roboto', preview: 'Classic Android Sans' },
            { name: 'Nunito', preview: 'Soft Rounded Friendly' },
            { name: 'Lexend', preview: 'High Legibility Pro' },
        ],
        theme: {
            mode: 'dark',
            accent: 'slate-emerald',
            customHex: '#10b981',
            isCustom: false,
            sidebarCaption: true,
            direction: 'ltr',
            layoutWidth: 'fluid',
            fontFamily: 'Plus Jakarta Sans',
            fontSize: 'md'
        },

        get currentLangObj() {
            return this.languages.find(l => l.code === this.selectedLang) || this.languages[0];
        },

        get currentAccentHex() {
            if (this.theme.isCustom && this.theme.customHex) {
                return this.theme.customHex;
            }
            const preset = this.colorPresets.find(c => c.id === this.theme.accent);
            return preset ? preset.hex : '#10b981';
        },

        get pickerBaseColor() {
            return `hsl(${this.pickerHue}, 100%, 50%)`;
        },

        initTheme() {
            try {
                if (serverSettings && typeof serverSettings === 'object') {
                    this.theme = Object.assign({}, this.theme, serverSettings);
                } else {
                    const savedTheme = localStorage.getItem(this.storageKey) || localStorage.getItem('gym_console_theme_settings');
                    if (savedTheme) {
                        this.theme = Object.assign({}, this.theme, JSON.parse(savedTheme));
                    }
                }

                if (this.theme.customHex) {
                    this.rgb = this.hexToRgb(this.theme.customHex);
                }

                const savedLang = localStorage.getItem('gym_console_lang');
                if (savedLang) {
                    this.selectedLang = savedLang;
                }
            } catch (e) {}

            this.applyAll();
            this.initGoogleTranslate();
        },

        initGoogleTranslate() {
            if (!document.getElementById('google-translate-script')) {
                const script = document.createElement('script');
                script.id = 'google-translate-script';
                script.type = 'text/javascript';
                script.src = '//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
                document.head.appendChild(script);
            }
        },

        saveLocalTheme() {
            try {
                localStorage.setItem(this.storageKey, JSON.stringify(this.theme));
                localStorage.setItem('gym_console_theme_settings', JSON.stringify(this.theme));
            } catch (e) {}
        },

        async saveSettingsToServer() {
            this.saving = true;
            this.saveLocalTheme();
            this.applyAll();

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                              document.querySelector('input[name="_token"]')?.value;

                const response = await fetch('/theme-settings/save', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token || ''
                    },
                    body: JSON.stringify(this.theme)
                });

                if (response.ok) {
                    this.drawerOpen = false;
                    this.showToast('Theme settings saved successfully!');
                }
            } catch (e) {
                console.error('Failed to save theme to server:', e);
            } finally {
                this.saving = false;
                this.drawerOpen = false;
            }
        },

        showToast(message) {
            const toast = document.createElement('div');
            toast.className = 'fixed bottom-6 right-6 z-[999999] bg-emerald-500 text-slate-950 font-bold px-5 py-3 rounded-2xl shadow-2xl flex items-center gap-2 transform transition-all duration-300 translate-y-0 opacity-100';
            toast.innerHTML = `<span>✓</span> <span>${message}</span>`;
            document.body.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        },

        setMode(mode) {
            this.theme.mode = mode;
            this.saveLocalTheme();
            this.applyMode();
            this.applyAccent();
        },

        setAccent(colorObj) {
            this.theme.accent = colorObj.id;
            this.theme.isCustom = false;
            this.showCustomPicker = false;
            this.saveLocalTheme();
            this.applyAccent();
        },

        toggleCustomPicker() {
            this.theme.isCustom = true;
            this.theme.accent = 'custom';
            this.showCustomPicker = !this.showCustomPicker;
            this.saveLocalTheme();
            this.applyAccent();
        },

        setCustomHex(hex) {
            if (!hex) return;
            if (!hex.startsWith('#')) hex = '#' + hex;
            this.theme.isCustom = true;
            this.theme.customHex = hex;
            this.theme.accent = 'custom';
            this.rgb = this.hexToRgb(hex);
            this.saveLocalTheme();
            this.applyAccent();
        },

        updateFromRgb() {
            const r = Math.min(255, Math.max(0, parseInt(this.rgb.r) || 0));
            const g = Math.min(255, Math.max(0, parseInt(this.rgb.g) || 0));
            const b = Math.min(255, Math.max(0, parseInt(this.rgb.b) || 0));
            const hex = '#' + ((1 << 24) + (r << 16) + (g << 8) + b).toString(16).slice(1);
            this.setCustomHex(hex);
        },

        updateFromHue() {
            const hslToHex = (h, s, l) => {
                l /= 100;
                const a = s * Math.min(l, 1 - l) / 100;
                const f = n => {
                    const k = (n + h / 30) % 12;
                    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                    return Math.round(255 * color).toString(16).padStart(2, '0');
                };
                return `#${f(0)}${f(8)}${f(4)}`;
            };
            const hex = hslToHex(this.pickerHue, 85, 50);
            this.setCustomHex(hex);
        },

        pickCanvasColor(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
            const y = Math.max(0, Math.min(rect.height, e.clientY - rect.top));
            this.pickerDotX = Math.round((x / rect.width) * 100);
            this.pickerDotY = Math.round((y / rect.height) * 100);

            const sat = this.pickerDotX;
            const light = 100 - this.pickerDotY * 0.75 - (100 - this.pickerDotX) * 0.25;
            
            const hslToHex = (h, s, l) => {
                l /= 100;
                const a = s * Math.min(l, 1 - l) / 100;
                const f = n => {
                    const k = (n + h / 30) % 12;
                    const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
                    return Math.round(255 * color).toString(16).padStart(2, '0');
                };
                return `#${f(0)}${f(8)}${f(4)}`;
            };

            const hex = hslToHex(this.pickerHue, Math.max(0, Math.min(100, sat)), Math.max(0, Math.min(100, light)));
            this.setCustomHex(hex);
        },

        async triggerEyeDropper() {
            if ('EyeDropper' in window) {
                try {
                    const eyeDropper = new window.EyeDropper();
                    const result = await eyeDropper.open();
                    if (result && result.sRGBHex) {
                        this.setCustomHex(result.sRGBHex);
                    }
                } catch (e) {}
            } else {
                alert('Eyedropper tool is supported in Chromium browsers (Chrome/Edge). You can use the color palette sliders below.');
            }
        },

        setSidebarCaption(show) {
            this.theme.sidebarCaption = show;
            this.saveLocalTheme();
            this.applySidebarCaption();
        },

        setDirection(dir) {
            this.theme.direction = dir;
            this.saveLocalTheme();
            this.applyDirection();
        },

        setLayoutWidth(width) {
            this.theme.layoutWidth = width;
            this.saveLocalTheme();
            this.applyLayoutWidth();
        },

        setFontFamily(font) {
            this.theme.fontFamily = font;
            this.saveLocalTheme();
            this.applyFont();
        },

        setFontSize(size) {
            this.theme.fontSize = size;
            this.saveLocalTheme();
            this.applyFontSize();
        },

        selectLanguage(code) {
            this.selectedLang = code;
            this.langDropdownOpen = false;
            try {
                localStorage.setItem('gym_console_lang', code);
            } catch (e) {}
            
            const langObj = this.languages.find(l => l.code === code);
            if (langObj && langObj.dir) {
                this.setDirection(langObj.dir);
            }

            this.translatePage(code);
        },

        translatePage(langCode) {
            if (langCode === 'en') {
                document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                document.cookie = "googtrans=; expires=Thu, 01 Jan 1970 00:00:00 UTC; domain=" + window.location.hostname + "; path=/;";
                window.location.reload();
                return;
            }

            const cookieValue = '/en/' + langCode;
            document.cookie = 'googtrans=' + cookieValue + '; path=/;';
            document.cookie = 'googtrans=' + cookieValue + '; domain=' + window.location.hostname + '; path=/;';

            const select = document.querySelector('.goog-te-combo');
            if (select) {
                select.value = langCode;
                select.dispatchEvent(new Event('change'));
            } else {
                window.location.reload();
            }
        },

        applyAll() {
            this.applyMode();
            this.applyAccent();
            this.applySidebarCaption();
            this.applyDirection();
            this.applyLayoutWidth();
            this.applyFont();
            this.applyFontSize();
        },

        applyMode() {
            const root = document.documentElement;
            const isDark = this.theme.mode === 'dark' || (this.theme.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (isDark) {
                root.classList.add('dark');
                root.classList.remove('light');
            } else {
                root.classList.add('light');
                root.classList.remove('dark');
            }
        },

        hexToRgb(hex) {
            if (!hex) return { r: 16, g: 185, b: 129 };
            let c = hex.replace('#', '');
            if (c.length === 3) c = c.split('').map(x => x + x).join('');
            const num = parseInt(c, 16) || 0;
            return {
                r: (num >> 16) & 255,
                g: (num >> 8) & 255,
                b: num & 255
            };
        },

        applyAccent() {
            const hex = this.currentAccentHex;
            const rgb = this.hexToRgb(hex);
            const rgbStr = `${rgb.r}, ${rgb.g}, ${rgb.b}`;
            const root = document.documentElement;

            root.style.setProperty('--theme-accent', hex);
            root.style.setProperty('--theme-accent-rgb', rgbStr);

            let styleTag = document.getElementById('theme-accent-dynamic-style');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'theme-accent-dynamic-style';
                document.head.appendChild(styleTag);
            }

            const isLight = root.classList.contains('light');

            styleTag.innerHTML = `
                :root {
                    --theme-accent: ${hex} !important;
                    --theme-accent-light: rgba(${rgbStr}, 0.15) !important;
                    --theme-accent-border: rgba(${rgbStr}, 0.3) !important;
                    --theme-accent-glow: rgba(${rgbStr}, 0.35) !important;
                }

                .theme-save-btn,
                .btn-primary,
                .theme-btn-primary,
                .bg-amber-500,
                .bg-red-500,
                .bg-emerald-500,
                .bg-emerald-600 {
                    background-color: ${hex} !important;
                    color: #ffffff !important;
                }

                aside nav a.bg-amber-500,
                aside nav a.bg-red-500,
                aside nav a.bg-emerald-500,
                aside nav a[class*="bg-amber-500"],
                aside nav a[class*="bg-red-500"],
                aside nav a[class*="bg-emerald-500"] {
                    background-color: ${hex} !important;
                    color: #ffffff !important;
                    box-shadow: 0 4px 14px 0 rgba(${rgbStr}, 0.35) !important;
                }

                html.light aside nav a.bg-amber-500,
                html.light aside nav a.bg-red-500,
                html.light aside nav a.bg-emerald-500,
                html.light aside nav a[class*="bg-amber-500"],
                html.light aside nav a[class*="bg-red-500"],
                html.light aside nav a[class*="bg-emerald-500"] {
                    background-color: rgba(${rgbStr}, 0.12) !important;
                    color: ${hex} !important;
                    border: 1px solid rgba(${rgbStr}, 0.25) !important;
                    box-shadow: none !important;
                    font-weight: 700 !important;
                }

                .text-amber-400,
                .text-red-400,
                .text-emerald-400,
                .text-emerald-500,
                .text-cyan-400,
                .theme-accent-text {
                    color: ${hex} !important;
                }

                .bg-amber-500\\/10,
                .bg-red-500\\/10,
                .bg-emerald-500\\/10,
                .bg-amber-500\\/15,
                .bg-red-500\\/15,
                .bg-emerald-500\\/15,
                .bg-emerald-500\\/20 {
                    background-color: rgba(${rgbStr}, 0.14) !important;
                    color: ${hex} !important;
                }

                .border-amber-500,
                .border-red-500,
                .border-emerald-500,
                .border-amber-500\\/20,
                .border-red-500\\/20,
                .border-emerald-500\\/20,
                .border-emerald-500\\/30 {
                    border-color: rgba(${rgbStr}, 0.35) !important;
                }

                .focus\\:ring-amber-500:focus,
                .focus\\:ring-emerald-500:focus,
                .focus\\:border-amber-500:focus,
                .focus\\:border-emerald-500:focus {
                    --tw-ring-color: ${hex} !important;
                    border-color: ${hex} !important;
                }
            `;
        },

        applySidebarCaption() {
            const captions = document.querySelectorAll('aside nav div[class*="uppercase"]');
            captions.forEach(el => {
                el.style.display = this.theme.sidebarCaption ? '' : 'none';
            });
        },

        applyDirection() {
            document.documentElement.setAttribute('dir', this.theme.direction);
        },

        applyLayoutWidth() {
            const mainContainer = document.querySelector('main');
            if (mainContainer) {
                if (this.theme.layoutWidth === 'boxed') {
                    mainContainer.classList.add('max-w-7xl', 'mx-auto', 'w-full');
                } else {
                    mainContainer.classList.remove('max-w-7xl', 'mx-auto');
                }
            }
        },

        applyFont() {
            const font = this.theme.fontFamily;
            const fontSlug = font.replace(/ /g, '+');
            let linkEl = document.getElementById('dynamic-google-font');
            if (!linkEl) {
                linkEl = document.createElement('link');
                linkEl.id = 'dynamic-google-font';
                linkEl.rel = 'stylesheet';
                document.head.appendChild(linkEl);
            }
            linkEl.href = `https://fonts.googleapis.com/css2?family=${fontSlug}:wght@300;400;500;600;700;800&display=swap`;
            document.body.style.fontFamily = `'${font}', sans-serif`;
        },

        applyFontSize() {
            const scale = {
                'sm': '92%',
                'md': '100%',
                'lg': '108%'
            }[this.theme.fontSize] || '100%';
            document.documentElement.style.fontSize = scale;
        }
    };
}
window.themeManager = themeManager;
window.googleTranslateElementInit = googleTranslateElementInit;
document.addEventListener('alpine:init', () => {
    if (window.Alpine) {
        window.Alpine.data('themeManager', themeManager);
    }
});
</script>

<!-- Theme & Language Customizer Component (FitPlex Reference Parity) -->
<div x-data="themeManager(window.__SAVED_THEME__ || null, window.__THEME_STORAGE_KEY__ || 'admin')" x-init="initTheme()" class="flex items-center gap-2">

    <!-- Language Selector Dropdown Trigger -->
    <div class="relative" @click.away="langDropdownOpen = false">
        <button @click="langDropdownOpen = !langDropdownOpen"
                type="button"
                title="Change Language"
                class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-300 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 dark:text-slate-300 dark:hover:text-white dark:border-slate-700/60 transition-all text-xs font-semibold shadow-sm cursor-pointer theme-btn">
            <span class="text-sm leading-none" x-text="currentLangObj.flag">🌐</span>
            <span class="font-bold uppercase tracking-wider text-[11px]" x-text="currentLangObj.code">EN</span>
            <svg class="w-3 h-3 text-slate-500 dark:text-slate-400 transition-transform duration-200" :class="langDropdownOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <!-- Language Dropdown Menu -->
        <div x-show="langDropdownOpen"
             x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
             class="absolute right-0 mt-2 w-56 bg-white dark:bg-slate-900 rounded-xl border border-slate-200 dark:border-slate-800 shadow-2xl z-[9999] overflow-hidden py-1 divide-y divide-slate-100 dark:divide-slate-800/60">
            
            <div class="px-3.5 py-2 bg-slate-50 dark:bg-slate-950/70 flex items-center justify-between border-b border-slate-100 dark:border-slate-800">
                <span class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Select Language</span>
                <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Translate</span>
            </div>

            <div class="max-h-64 overflow-y-auto p-1 space-y-0.5">
                <template x-for="lang in languages" :key="lang.code">
                    <button type="button"
                            @click="selectLanguage(lang.code)"
                            class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-xs transition-colors text-left cursor-pointer"
                            :class="selectedLang === lang.code ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 font-bold' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white'">
                        <div class="flex items-center gap-2.5">
                            <span class="text-base leading-none" x-text="lang.flag"></span>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-white" x-text="lang.name"></div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-normal" x-text="lang.native"></div>
                            </div>
                        </div>
                        <svg x-show="selectedLang === lang.code" class="w-4 h-4 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                        </svg>
                    </button>
                </template>
            </div>
        </div>
    </div>

    <!-- Theme Customizer Drawer Trigger Button -->
    <button @click="drawerOpen = true"
            type="button"
            title="Theme Settings"
            class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-300 dark:bg-slate-800/80 dark:hover:bg-slate-700/80 dark:text-slate-300 dark:hover:text-white dark:border-slate-700/60 transition-all text-xs font-semibold shadow-sm group cursor-pointer theme-btn">
        <svg class="w-4 h-4 text-emerald-500 dark:text-emerald-400 group-hover:rotate-90 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <span class="hidden md:inline-block">Theme</span>
    </button>

    <!-- Slide-Over Drawer -->
    <div x-show="drawerOpen" x-cloak class="relative z-[99999]">
            <!-- Slide-Over Backdrop -->
            <div x-show="drawerOpen"
                 x-transition:enter="transition-opacity ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="drawerOpen = false"
                 class="fixed inset-0 bg-slate-900/60 dark:bg-black/70 backdrop-blur-xs"></div>

            <!-- Slide-Over Theme Drawer Panel -->
            <div x-show="drawerOpen"
                 x-transition:enter="transition transform ease-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition transform ease-in duration-200"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="fixed inset-y-0 right-0 max-w-[380px] sm:max-w-[400px] w-full bg-white dark:bg-[#0b1329] border-l border-slate-200 dark:border-slate-800/80 shadow-2xl flex flex-col justify-between text-slate-900 dark:text-slate-100 overflow-hidden h-screen z-50">

                <!-- Drawer Header -->
                <div class="px-5 py-4 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-[#080e1e] flex items-center justify-between shrink-0">
                    <h3 class="font-extrabold text-slate-900 dark:text-white text-sm tracking-wider uppercase">THEME SETTINGS</h3>
                    <button @click="drawerOpen = false" type="button" class="p-1.5 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Navigation Tabs (Palette Icon & Font 'A' Icon) -->
                <div class="px-5 pt-3 pb-2 border-b border-slate-200 dark:border-slate-800/60 bg-slate-50/70 dark:bg-[#0a1024] flex items-center justify-center gap-8 shrink-0">
                    <!-- Tab 1: Theme & Palette -->
                    <button type="button"
                            @click="activeTab = 'layout'"
                            class="pb-2 px-3 relative font-bold text-xs transition-all flex items-center gap-2 cursor-pointer"
                            :class="activeTab === 'layout' ? 'text-slate-900 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                        </svg>
                        <div x-show="activeTab === 'layout'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 dark:bg-emerald-400 rounded-full"></div>
                    </button>

                    <!-- Tab 2: Typography 'A' -->
                    <button type="button"
                            @click="activeTab = 'typography'"
                            class="pb-2 px-3 relative font-bold text-sm transition-all flex items-center gap-2 cursor-pointer"
                            :class="activeTab === 'typography' ? 'text-slate-900 dark:text-white' : 'text-slate-400 hover:text-slate-600 dark:hover:text-slate-200'">
                        <span class="font-extrabold text-base tracking-tight leading-none font-serif">A</span>
                        <div x-show="activeTab === 'typography'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-emerald-500 dark:bg-emerald-400 rounded-full"></div>
                    </button>
                </div>

                <!-- Drawer Content Body -->
                <div class="flex-1 overflow-y-auto px-5 py-5 space-y-6 bg-white dark:bg-[#0b1329]">

                    <!-- TAB 1: LAYOUT & COLOR SETTINGS -->
                    <div x-show="activeTab === 'layout'" class="space-y-6">

                        <!-- 1. Theme Mode -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">THEME MODE</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Light / Dark / System</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <!-- Light Box -->
                                <button type="button"
                                        @click="setMode('light')"
                                        class="h-14 rounded-xl border transition-all flex items-center justify-center p-2 cursor-pointer relative"
                                        :class="theme.mode === 'light' ? 'border-emerald-500 ring-2 ring-emerald-500/40 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-[#111c38] hover:border-slate-300 dark:hover:border-slate-600'">
                                    <div class="w-full h-full bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 flex items-center justify-center text-slate-900 dark:text-white text-xs font-bold">
                                        Light
                                    </div>
                                </button>

                                <!-- Dark Box -->
                                <button type="button"
                                        @click="setMode('dark')"
                                        class="h-14 rounded-xl border transition-all flex items-center justify-center p-2 cursor-pointer relative"
                                        :class="theme.mode === 'dark' ? 'border-emerald-500 ring-2 ring-emerald-500/40 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-[#111c38] hover:border-slate-300 dark:hover:border-slate-600'">
                                    <div class="w-full h-full bg-slate-900 rounded-lg border border-slate-700/60 flex items-center justify-center text-white text-xs font-bold shadow-sm">
                                        Dark
                                    </div>
                                </button>

                                <!-- System Box -->
                                <button type="button"
                                        @click="setMode('system')"
                                        class="h-14 rounded-xl border transition-all flex items-center justify-center p-2 cursor-pointer relative"
                                        :class="theme.mode === 'system' ? 'border-emerald-500 ring-2 ring-emerald-500/40 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-700/80 bg-slate-50 dark:bg-[#111c38] hover:border-slate-300 dark:hover:border-slate-600'">
                                    <div class="w-full h-full bg-slate-100 dark:bg-[#111c38] rounded-lg border border-slate-200 dark:border-slate-700/60 flex items-center justify-center text-slate-700 dark:text-cyan-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                                    </div>
                                </button>
                            </div>
                        </div>

                        <!-- 2. Accent Color Palette -->
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">ACCENT COLOR</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Choose your primary theme color</p>
                                </div>
                            </div>
                            
                            <!-- Preset Circles Grid (5 on top, 2 on bottom) -->
                            <div class="grid grid-cols-5 gap-3 pt-1">
                                <!-- Row 1, Col 1 -->
                                <button type="button"
                                        @click="setAccent(colorPresets[0])"
                                        :title="colorPresets[0].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[0].gradient};`">
                                    <div x-show="theme.accent === colorPresets[0].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 1, Col 2 -->
                                <button type="button"
                                        @click="setAccent(colorPresets[1])"
                                        :title="colorPresets[1].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[1].gradient};`">
                                    <div x-show="theme.accent === colorPresets[1].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 1, Col 3 -->
                                <button type="button"
                                        @click="setAccent(colorPresets[2])"
                                        :title="colorPresets[2].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[2].gradient};`">
                                    <div x-show="theme.accent === colorPresets[2].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 1, Col 4 -->
                                <button type="button"
                                        @click="setAccent(colorPresets[3])"
                                        :title="colorPresets[3].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[3].gradient};`">
                                    <div x-show="theme.accent === colorPresets[3].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 1, Col 5 -->
                                <button type="button"
                                        @click="setAccent(colorPresets[4])"
                                        :title="colorPresets[4].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[4].gradient};`">
                                    <div x-show="theme.accent === colorPresets[4].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 2, Col 1: Mint-Emerald -->
                                <button type="button"
                                        @click="setAccent(colorPresets[5])"
                                        :title="colorPresets[5].name"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105"
                                        :style="`background: ${colorPresets[5].gradient};`">
                                    <div x-show="theme.accent === colorPresets[5].id && !theme.isCustom" class="w-6 h-6 rounded-full bg-black/40 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </button>

                                <!-- Row 2, Col 2: Custom Color Circle with checkmark -->
                                <button type="button"
                                        @click="toggleCustomPicker()"
                                        title="Pick Custom Color"
                                        class="w-12 h-12 rounded-full flex items-center justify-center transition-all cursor-pointer relative shadow-md hover:scale-105 border-2 bg-slate-100 dark:bg-slate-950"
                                        :class="theme.isCustom ? 'border-emerald-500 ring-2 ring-emerald-500/50' : 'border-slate-300 dark:border-slate-700'"
                                        :style="theme.isCustom ? `background-color: ${theme.customHex};` : ''">
                                    <div x-show="theme.isCustom" class="w-6 h-6 rounded-full bg-black/50 backdrop-blur-xs flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white drop-shadow-md" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div x-show="!theme.isCustom" class="text-slate-800 dark:text-white font-bold text-sm">
                                        ✓
                                    </div>
                                </button>
                            </div>

                            <!-- Integrated Inline Color Picker Panel -->
                            <div x-show="showCustomPicker"
                                 x-cloak
                                 x-transition
                                 class="mt-4 p-4 rounded-2xl bg-slate-50 dark:bg-slate-900 text-slate-900 dark:text-white shadow-xl border border-slate-200 dark:border-slate-800 space-y-3.5 w-full">
                                
                                <div class="flex items-center justify-between">
                                    <span class="text-xs font-extrabold uppercase tracking-wide text-slate-800 dark:text-slate-200">Custom Color</span>
                                    <button @click="showCustomPicker = false" class="text-slate-400 hover:text-slate-800 dark:hover:text-white text-sm font-bold cursor-pointer">✕</button>
                                </div>

                                <!-- Spectrum / Gradient Canvas Area -->
                                <div class="h-28 w-full rounded-xl relative overflow-hidden shadow-inner cursor-crosshair border border-slate-200 dark:border-slate-700"
                                     :style="`background: linear-gradient(to top, #000, transparent), linear-gradient(to right, #fff, ${pickerBaseColor});`"
                                     @click="pickCanvasColor($event)">
                                    <div class="absolute w-4 h-4 rounded-full border-2 border-white shadow-md pointer-events-none -translate-x-1/2 -translate-y-1/2"
                                         :style="`background-color: ${theme.customHex}; left: ${pickerDotX}%; top: ${pickerDotY}%;`"></div>
                                </div>

                                <!-- Controls Row: Eyedropper + Color Swatch Circle + Hue Bar -->
                                <div class="flex items-center gap-2.5">
                                    <!-- Eyedropper -->
                                    <button type="button"
                                            @click="triggerEyeDropper()"
                                            title="Eyedropper"
                                            class="p-2 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 transition-colors cursor-pointer shrink-0">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                        </svg>
                                    </button>

                                    <!-- Swatch Circle -->
                                    <div class="w-7 h-7 rounded-full shadow-inner border border-slate-300 dark:border-slate-600 shrink-0"
                                         :style="`background-color: ${theme.customHex};`"></div>

                                    <!-- Rainbow Hue Slider -->
                                    <div class="flex-1 relative flex items-center">
                                        <input type="range"
                                               min="0"
                                               max="360"
                                               x-model="pickerHue"
                                               @input="updateFromHue()"
                                               class="w-full h-3.5 rounded-lg appearance-none cursor-pointer"
                                               style="background: linear-gradient(to right, #f00 0%, #ff0 17%, #0f0 33%, #0ff 50%, #00f 67%, #f0f 83%, #f00 100%);">
                                    </div>

                                    <!-- Native picker icon -->
                                    <label class="p-1.5 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-700 cursor-pointer shrink-0" title="Native Color Picker">
                                        <input type="color" :value="theme.customHex" @input="setCustomHex($event.target.value)" class="sr-only">
                                        🎨
                                    </label>
                                </div>

                                <!-- R, G, B Inputs Row -->
                                <div class="grid grid-cols-4 gap-2 text-center text-xs font-semibold">
                                    <div>
                                        <input type="number" min="0" max="255" x-model.number="rgb.r" @input="updateFromRgb()" class="w-full px-1 py-1.5 text-center font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-1 focus:ring-slate-900 dark:focus:ring-white focus:outline-none">
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase mt-1 block">R</span>
                                    </div>
                                    <div>
                                        <input type="number" min="0" max="255" x-model.number="rgb.g" @input="updateFromRgb()" class="w-full px-1 py-1.5 text-center font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-1 focus:ring-slate-900 dark:focus:ring-white focus:outline-none">
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase mt-1 block">G</span>
                                    </div>
                                    <div>
                                        <input type="number" min="0" max="255" x-model.number="rgb.b" @input="updateFromRgb()" class="w-full px-1 py-1.5 text-center font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs focus:ring-1 focus:ring-slate-900 dark:focus:ring-white focus:outline-none">
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase mt-1 block">B</span>
                                    </div>
                                    <div>
                                        <input type="text" x-model="theme.customHex" @input="setCustomHex(theme.customHex)" class="w-full px-1 py-1.5 text-center font-mono rounded-lg border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white text-xs uppercase focus:ring-1 focus:ring-slate-900 dark:focus:ring-white focus:outline-none">
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase mt-1 block">HEX</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Sidebar Option (Caption Hide / Show) -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">SIDEBAR OPTION</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Caption Hide / Show</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Caption Show Card -->
                                <button type="button"
                                        @click="setSidebarCaption(true)"
                                        class="h-16 rounded-xl border p-2.5 transition-all flex flex-col justify-between cursor-pointer"
                                        :class="theme.sidebarCaption ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="flex items-center gap-1.5">
                                        <div class="w-2.5 h-1 bg-emerald-500 rounded-full"></div>
                                        <div class="w-8 h-1 bg-slate-400 dark:bg-slate-500 rounded-full"></div>
                                    </div>
                                    <div class="space-y-1">
                                        <div class="w-12 h-1 bg-slate-300 dark:bg-slate-400 rounded-full"></div>
                                        <div class="w-10 h-1 bg-slate-400 dark:bg-slate-600 rounded-full"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Show Captions</span>
                                </button>

                                <!-- Caption Hide Card -->
                                <button type="button"
                                        @click="setSidebarCaption(false)"
                                        class="h-16 rounded-xl border p-2.5 transition-all flex flex-col justify-between cursor-pointer"
                                        :class="!theme.sidebarCaption ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="space-y-1.5 pt-1">
                                        <div class="w-12 h-1 bg-slate-300 dark:bg-slate-400 rounded-full"></div>
                                        <div class="w-10 h-1 bg-slate-400 dark:bg-slate-600 rounded-full"></div>
                                        <div class="w-8 h-1 bg-slate-400 dark:bg-slate-600 rounded-full"></div>
                                    </div>
                                    <span class="text-[10px] font-bold text-slate-700 dark:text-slate-300">Hide Captions</span>
                                </button>
                            </div>
                        </div>

                        <!-- 4. Theme Layout (LTR / RTL) -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">THEME LAYOUT</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">LTR / RTL</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- LTR Card -->
                                <button type="button"
                                        @click="setDirection('ltr')"
                                        class="h-14 rounded-xl border p-2.5 transition-all flex items-center justify-between cursor-pointer"
                                        :class="theme.direction === 'ltr' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="w-3 h-8 bg-slate-300 dark:bg-slate-700 rounded-xs"></div>
                                    <div class="flex-1 ml-2 space-y-1">
                                        <div class="w-full h-2 bg-slate-400 dark:bg-slate-600 rounded-xs"></div>
                                        <div class="w-3/4 h-2 bg-slate-300 dark:bg-slate-700 rounded-xs"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 ml-2">LTR</span>
                                </button>

                                <!-- RTL Card -->
                                <button type="button"
                                        @click="setDirection('rtl')"
                                        class="h-14 rounded-xl border p-2.5 transition-all flex items-center justify-between cursor-pointer"
                                        :class="theme.direction === 'rtl' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 mr-2">RTL</span>
                                    <div class="flex-1 mr-2 space-y-1 text-right">
                                        <div class="w-full h-2 bg-slate-400 dark:bg-slate-600 rounded-xs ml-auto"></div>
                                        <div class="w-3/4 h-2 bg-slate-300 dark:bg-slate-700 rounded-xs ml-auto"></div>
                                    </div>
                                    <div class="w-3 h-8 bg-slate-300 dark:bg-slate-700 rounded-xs"></div>
                                </button>
                            </div>
                        </div>

                        <!-- 5. Layout Width (Full / Fixed width) -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                            <div class="flex items-center justify-between mb-2">
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">LAYOUT WIDTH</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Full / Fixed width</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-3">
                                <!-- Full Width -->
                                <button type="button"
                                        @click="setLayoutWidth('fluid')"
                                        class="h-14 rounded-xl border p-2.5 transition-all flex items-center justify-center gap-2 cursor-pointer"
                                        :class="theme.layoutWidth === 'fluid' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="w-full h-6 border border-dashed border-slate-400 dark:border-slate-500 rounded flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                        Full Width
                                    </div>
                                </button>

                                <!-- Fixed Boxed Width -->
                                <button type="button"
                                        @click="setLayoutWidth('boxed')"
                                        class="h-14 rounded-xl border p-2.5 transition-all flex items-center justify-center gap-2 cursor-pointer"
                                        :class="theme.layoutWidth === 'boxed' ? 'border-emerald-500 bg-emerald-50/50 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div class="w-3/4 h-6 border border-dashed border-slate-400 dark:border-slate-500 rounded flex items-center justify-center text-[10px] font-bold text-slate-700 dark:text-slate-300">
                                        Fixed Boxed
                                    </div>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: TYPOGRAPHY & FONTS -->
                    <div x-show="activeTab === 'typography'" class="space-y-6">

                        <!-- Font Family -->
                        <div>
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">FONT FAMILY</h4>
                            <div class="grid grid-cols-2 gap-2.5">
                                <template x-for="font in fontFamilies" :key="font.name">
                                    <button type="button"
                                            @click="setFontFamily(font.name)"
                                            class="p-3 rounded-xl border text-left transition-all cursor-pointer"
                                            :class="theme.fontFamily === font.name ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-500/15 text-emerald-900 dark:text-emerald-400 font-bold' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] text-slate-800 dark:text-slate-300 hover:border-slate-300 dark:hover:border-slate-700'">
                                        <div class="font-bold text-xs" :style="`font-family: '${font.name}', sans-serif;`" x-text="font.name"></div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5" x-text="font.preview"></div>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Font Size Scaling -->
                        <div class="pt-3 border-t border-slate-200 dark:border-slate-800">
                            <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider mb-2">FONT SIZE</h4>
                            <div class="grid grid-cols-3 gap-2.5">
                                <button type="button"
                                        @click="setFontSize('sm')"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1 transition-all cursor-pointer"
                                        :class="theme.fontSize === 'sm' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-500/15 text-emerald-900 dark:text-emerald-400 font-bold' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] text-slate-700 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <span class="text-xs">A</span>
                                    <span class="text-[10px]">Small</span>
                                </button>
                                <button type="button"
                                        @click="setFontSize('md')"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1 transition-all cursor-pointer"
                                        :class="theme.fontSize === 'md' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-500/15 text-emerald-900 dark:text-emerald-400 font-bold' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] text-slate-700 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <span class="text-sm font-bold">A</span>
                                    <span class="text-[10px]">Regular</span>
                                </button>
                                <button type="button"
                                        @click="setFontSize('lg')"
                                        class="py-2.5 px-3 rounded-xl border text-xs font-semibold flex flex-col items-center gap-1 transition-all cursor-pointer"
                                        :class="theme.fontSize === 'lg' ? 'border-emerald-500 bg-emerald-50/70 dark:bg-emerald-500/15 text-emerald-900 dark:text-emerald-400 font-bold' : 'border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#0e162c] text-slate-700 dark:text-slate-400 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <span class="text-base font-extrabold">A</span>
                                    <span class="text-[10px]">Large</span>
                                </button>
                            </div>
                        </div>

                    </div>

                </div>

                <!-- Drawer Footer (Sticky Full Width Button) -->
                <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-[#080e1e] flex items-center justify-between gap-3 shrink-0">
                    <button @click="saveSettingsToServer()"
                            type="button"
                            :disabled="saving"
                            class="w-full py-3 px-4 rounded-xl text-white font-extrabold text-sm transition-all shadow-xl hover:opacity-95 active:scale-[0.99] text-center cursor-pointer flex items-center justify-center gap-2 theme-save-btn"
                            :style="`background-color: ${currentAccentHex};`">
                        <svg x-show="saving" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                        </svg>
                        <span x-text="saving ? 'Saving...' : 'Save Settings'">Save Settings</span>
                    </button>
                </div>

            </div>
        </div>

    <!-- Hidden Google Translate Element Container -->
    <div id="google_translate_element" class="hidden" style="display: none !important;"></div>

</div>

<!-- Theme Engine Core Styles & Script -->
<style>
    /* Google translate banner cleanup */
    .goog-te-banner-frame, .skiptranslate, iframe.skiptranslate { display: none !important; }
    body { top: 0px !important; }

    /* Clean light theme rules without destructive text-white resets */
    html.light {
        color-scheme: light;
    }

    html.light body {
        background-color: #f4f6f9;
        color: #1e293b;
    }
</style>
