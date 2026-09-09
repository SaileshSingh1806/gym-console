<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? (($platformSettings['app_name'] ?? 'Gym Console') . ' - Super Admin') }}</title>

    @if(!empty($platformSettings['favicon_url']))
        <link rel="icon" type="image/x-icon" href="{{ $platformSettings['favicon_url'] }}">
        <link rel="shortcut icon" href="{{ $platformSettings['favicon_url'] }}">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Sleek Transparent Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(148, 163, 184, 0.25) transparent;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(148, 163, 184, 0.25);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(148, 163, 184, 0.45);
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }

        .light * {
            scrollbar-color: rgba(100, 116, 139, 0.3) transparent;
        }
        .light ::-webkit-scrollbar-thumb {
            background: rgba(100, 116, 139, 0.3);
        }
        .light ::-webkit-scrollbar-thumb:hover {
            background: rgba(100, 116, 139, 0.5);
        }
    </style>

    <script>
        // Set server theme settings in global JS context cleanly
        window.__SAVED_THEME__ = {{ Js::from($savedThemeSettings ?? null) }};
        window.__THEME_STORAGE_KEY__ = '{{ $themeStorageKey ?? "admin" }}';

        // Immediately apply saved theme to avoid FOUC (Gym specific & Super Admin specific)
        (function() {
            try {
                const serverTheme = window.__SAVED_THEME__;
                const storageKey = 'gym_console_theme_' + window.__THEME_STORAGE_KEY__;
                let theme = serverTheme;
                if (!theme) {
                    const local = localStorage.getItem(storageKey) || localStorage.getItem('gym_console_theme_settings');
                    if (local) theme = JSON.parse(local);
                }
                if (theme) {
                    const isDark = theme.mode === 'dark' || (theme.mode === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                        document.documentElement.classList.remove('light');
                    } else {
                        document.documentElement.classList.add('light');
                        document.documentElement.classList.remove('dark');
                    }
                    if (theme.direction) {
                        document.documentElement.setAttribute('dir', theme.direction);
                    }
                    if (theme.fontSize) {
                        const scale = { 'sm': '92%', 'md': '100%', 'lg': '108%' }[theme.fontSize] || '100%';
                        document.documentElement.style.fontSize = scale;
                    }
                    if (theme.fontFamily) {
                        document.body ? (document.body.style.fontFamily = `'${theme.fontFamily}', sans-serif`) : null;
                    }
                }
            } catch (e) {}
        })();
    </script>

    @if(!empty($platformSettings['custom_header_scripts']))
        {!! $platformSettings['custom_header_scripts'] !!}
    @endif
</head>
<body class="flex h-screen overflow-hidden antialiased bg-slate-950">

    <!-- Super Admin Sidebar -->
    <aside class="w-64 bg-slate-900 border-r border-slate-800 flex flex-col flex-shrink-0 h-screen">
        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800 bg-slate-900/60">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2.5">
                @if(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="h-9 w-9 rounded-xl object-contain bg-slate-950/60 p-1 border border-slate-800 shadow-sm shrink-0">
                    <div class="flex flex-col min-w-0">
                        <span class="font-extrabold text-white text-sm tracking-tight leading-none truncate max-w-[135px]">{{ $platformSettings['app_name'] ?? 'Gym Console' }}</span>
                        <span class="text-[9px] text-red-400 font-semibold uppercase tracking-widest mt-0.5">Super Admin</span>
                    </div>
                @else
                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-red-500 to-rose-600 flex items-center justify-center text-white font-bold shadow-md shadow-red-500/20">
                        ⚡
                    </div>
                    <div class="flex flex-col">
                        <span class="font-extrabold text-white text-base tracking-tight leading-none">GYM<span class="text-red-400">CONSOLE</span></span>
                        <span class="text-[9px] text-slate-400 uppercase tracking-widest mt-0.5">Super Admin</span>
                    </div>
                @endif
            </a>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow px-3 py-4 space-y-1 text-xs font-semibold overflow-y-auto">
            <div class="px-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Main Core</div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Platform Dashboard
            </a>

            <a href="{{ route('admin.gyms') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.gyms*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Gyms & Tenants
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">SaaS Billing & Plans</div>

            <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.plans*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                SaaS Plans & Features
            </a>

            <a href="{{ route('admin.subscriptions') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.subscriptions*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Subscriptions & Invoices
            </a>

            <a href="{{ route('admin.coupons') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.coupons*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Coupons & Discounts
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold text-slate-500 uppercase tracking-wider">Administration</div>

            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.users*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Platform Users
            </a>

            <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.logs*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Activity Audit Logs
            </a>

            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.settings*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Platform Settings
            </a>
        </nav>

        <!-- User Logout Footer -->
        <div class="p-4 border-t border-slate-800 bg-slate-900/80 flex items-center justify-between">
            <div class="truncate">
                <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-red-400 font-semibold uppercase">Super Admin</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Sign out" class="p-2 text-slate-400 hover:text-red-400 hover:bg-slate-800 rounded-lg transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 h-screen overflow-y-auto">
        <!-- Top Navbar -->
        <header class="h-16 bg-slate-900/80 backdrop-blur border-b border-slate-800 px-6 flex items-center justify-between sticky top-0 z-30 shrink-0">
            <h1 class="text-sm font-bold text-white">{{ $header ?? 'SaaS Platform Control' }}</h1>
            <div class="flex items-center gap-3">
                <x-theme-customizer />
                <span class="px-3 py-1 rounded-full bg-red-500/10 text-red-400 border border-red-500/20 text-xs font-bold">
                    Super Admin Mode
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-6 mt-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-6 mt-4 p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="flex-1 p-6">
            {{ $slot }}
        </main>
    </div>

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
</body>
</html>
