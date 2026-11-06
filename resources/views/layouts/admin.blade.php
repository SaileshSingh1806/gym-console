<!DOCTYPE html>
@php
    $serverThemeMode = 'dark';
    if (!empty($savedThemeSettings)) {
        $st = is_array($savedThemeSettings) ? $savedThemeSettings : json_decode($savedThemeSettings, true);
        if (!empty($st['mode']) && $st['mode'] === 'light') {
            $serverThemeMode = 'light';
        }
    }
@endphp
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ $serverThemeMode }} h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, viewport-fit=cover">
    
    <x-theme-head-styles :saved-theme="$savedThemeSettings ?? null" :storage-key="$themeStorageKey ?? 'admin'" />

    <title>{{ $title ?? (($platformSettings['app_name'] ?? 'GymDeskHub') . ' - Super Admin') }}</title>

    @if(!empty($platformSettings['favicon_url']))
        <link rel="icon" type="image/x-icon" href="{{ $platformSettings['favicon_url'] }}">
        <link rel="shortcut icon" href="{{ $platformSettings['favicon_url'] }}">
    @else
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}?v=3">
        <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v=3">
        <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}?v=3">
        <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=3">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v=3">
    @endif

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Mobile touch & rendering ergonomics */
        * {
            -webkit-tap-highlight-color: transparent;
        }
        html {
            -webkit-text-size-adjust: 100%;
        }

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

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>

    @if(!empty($platformSettings['custom_header_scripts']))
        {!! $platformSettings['custom_header_scripts'] !!}
    @endif
</head>
<body class="flex min-h-screen min-h-[100dvh] lg:h-screen lg:overflow-hidden antialiased bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100 selection:bg-red-500/30" :class="{ 'overflow-hidden': sidebarOpen }" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-50 bg-slate-950/75 backdrop-blur-xs lg:hidden" 
         @click="sidebarOpen = false" 
         x-cloak></div>

    <!-- Super Admin Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0 shadow-2xl shadow-black/60' : '-translate-x-full lg:translate-x-0'" 
           class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] lg:w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:inset-auto lg:h-screen lg:shrink-0">
        <!-- Logo -->
        <div class="h-16 flex items-center justify-between px-5 sm:px-6 border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/60 backdrop-blur">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center min-w-0 group focus:outline-none">
                <img src="{{ asset('images/gymdeskhub_horizontal_dark.png') }}"
                     alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                     class="h-8 w-auto max-w-[170px] object-contain hidden dark:block transition-opacity group-hover:opacity-90">
                <img src="{{ asset('images/gymdeskhub_horizontal_light.png') }}"
                     alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                     class="h-8 w-auto max-w-[170px] object-contain block dark:hidden transition-opacity group-hover:opacity-90">
            </a>
            <button @click="sidebarOpen = false" type="button" aria-label="Close sidebar" class="lg:hidden w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-grow px-3 py-4 space-y-1 text-xs font-semibold overflow-y-auto">
            <div class="px-3 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Main Core</div>

            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.dashboard') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Platform Dashboard
            </a>

            <a href="{{ route('admin.gyms') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.gyms*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Gyms & Tenants
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Billing & Plans</div>

            <a href="{{ route('admin.plans') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.plans*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                Subscription Plans & Features
            </a>

            <a href="{{ route('admin.subscriptions') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.subscriptions*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Subscriptions & Invoices
            </a>

            <a href="{{ route('admin.coupons') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.coupons*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                Coupons & Discounts
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Helpdesk & Support</div>

            <a href="{{ route('admin.tickets.index') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.tickets*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>Support Tickets</span>
                </div>
                @php
                    $pendingTicketsCount = \App\Models\SupportTicket::withoutGlobalScopes()->whereIn('status', ['open', 'in_progress'])->count();
                @endphp
                @if($pendingTicketsCount > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-black bg-red-600 text-white animate-pulse">{{ $pendingTicketsCount }}</span>
                @endif
            </a>

            <div class="pt-4 px-3 pb-1 text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Administration</div>

            <a href="{{ route('admin.users') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.users*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                Platform Users
            </a>

            <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.logs*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Activity Audit Logs
            </a>

            <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ request()->routeIs('admin.settings*') ? 'bg-red-500 text-white shadow-lg shadow-red-500/20' : 'text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Platform Settings
            </a>
        </nav>

        <!-- User Logout Footer -->
        <div class="p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 flex items-center justify-between">
            <div class="truncate">
                <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-red-500 font-bold uppercase">Super Admin</p>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" title="Sign out" class="p-2 text-slate-500 dark:text-slate-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 w-full min-h-screen lg:h-screen lg:overflow-y-auto bg-slate-100 dark:bg-slate-950 transition-colors duration-200">
        <!-- Top Navbar -->
        <header class="h-16 bg-white/95 dark:bg-slate-900/90 backdrop-blur border-b border-slate-200 dark:border-slate-800 px-3 sm:px-4 md:px-6 flex items-center justify-between sticky top-0 z-30 shrink-0 transition-colors duration-200">
            <div class="flex items-center gap-2 sm:gap-3 min-w-0">
                <button @click="sidebarOpen = true" type="button" aria-label="Open Navigation Menu" class="lg:hidden p-2 -ml-1 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0 cursor-pointer">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <h1 class="text-xs sm:text-sm md:text-base font-extrabold text-slate-900 dark:text-white truncate max-w-[130px] xs:max-w-[170px] sm:max-w-none tracking-tight">{{ $header ?? 'Platform Control' }}</h1>
            </div>
            <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                <x-theme-customizer />
                <span class="px-2.5 sm:px-3 py-1 rounded-full bg-red-500/10 text-red-600 dark:text-red-400 border border-red-500/20 text-[10px] sm:text-xs font-bold whitespace-nowrap">
                    Super Admin
                </span>
            </div>
        </header>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()" class="text-emerald-400 hover:text-emerald-300">✕</button>
            </div>
        @endif

        @if (session('error'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button onclick="this.parentElement.remove()" class="text-red-400 hover:text-red-300">✕</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-xs">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <main class="flex-1 p-3.5 sm:p-5 md:p-6 pb-28 lg:pb-8 w-full max-w-full overflow-x-hidden">
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        <!-- Super Admin Footer -->
        <footer class="mt-auto px-4 sm:px-6 py-3.5 sm:py-4 border-t border-slate-200 dark:border-slate-800 bg-white/80 dark:bg-slate-900/60 backdrop-blur text-slate-500 dark:text-slate-400 text-xs flex flex-col sm:flex-row items-center justify-between gap-2.5 transition-colors duration-200">
            <div class="flex items-center gap-2 text-center sm:text-left flex-wrap justify-center sm:justify-start">
                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}</span>
                <span>•</span>
                <span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' All rights reserved.') }}</span>
            </div>
            <div class="flex items-center gap-3 text-[11px]">
                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700/60 font-mono font-bold text-slate-700 dark:text-slate-300">v{{ config('app.version', '2.5.0') }}</span>
                <span class="flex items-center gap-1.5 text-emerald-600 dark:text-emerald-400 font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Systems Operational
                </span>
            </div>
        </footer>
    </div>

    <!-- Super Admin Mobile Bottom Navigation Bar -->
    <nav class="lg:hidden fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-slate-900/95 backdrop-blur-lg border-t border-slate-200/90 dark:border-slate-800/90 px-1 pt-1.5 pb-[max(0.4rem,env(safe-area-inset-bottom))] shadow-[0_-4px_20px_rgba(0,0,0,0.06)] dark:shadow-[0_-4px_20px_rgba(0,0,0,0.35)] transition-colors">
        <div class="grid grid-cols-5 items-center gap-1 max-w-lg mx-auto">
            <!-- 1. Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all {{ request()->routeIs('admin.dashboard') ? 'text-red-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <div class="relative p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    @if(request()->routeIs('admin.dashboard'))
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 truncate">Dashboard</span>
            </a>

            <!-- 2. Gyms -->
            <a href="{{ route('admin.gyms') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all {{ request()->routeIs('admin.gyms*') ? 'text-red-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <div class="relative p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @if(request()->routeIs('admin.gyms*'))
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 truncate">Gyms</span>
            </a>

            <!-- 3. Plans -->
            <a href="{{ route('admin.plans') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all {{ request()->routeIs('admin.plans*') ? 'text-red-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <div class="relative p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                    @if(request()->routeIs('admin.plans*'))
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 truncate">Plans</span>
            </a>

            <!-- 4. Subscriptions -->
            <a href="{{ route('admin.subscriptions') }}" 
               class="flex flex-col items-center justify-center py-1 px-1 rounded-xl transition-all {{ request()->routeIs('admin.subscriptions*') ? 'text-red-500 font-bold' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}">
                <div class="relative p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @if(request()->routeIs('admin.subscriptions*'))
                        <span class="absolute -bottom-0.5 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-red-500"></span>
                    @endif
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 truncate">Billing</span>
            </a>

            <!-- 5. Menu / Drawer Toggle -->
            <button type="button" 
                    @click="sidebarOpen = true" 
                    class="flex flex-col items-center justify-center py-1 px-1 rounded-xl text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200 transition-all cursor-pointer">
                <div class="relative p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </div>
                <span class="text-[10px] tracking-tight mt-0.5 truncate">Menu</span>
            </button>
        </div>
    </nav>

    <x-confirm-modal />

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
</body>
</html>
