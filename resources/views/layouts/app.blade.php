<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <script>
        // Synchronous Zero-FOUC Theme Initializer (Must execute before any rendering)
        window.__SAVED_THEME__ = {{ Js::from($savedThemeSettings ?? null) }};
        window.__THEME_STORAGE_KEY__ = '{{ $themeStorageKey ?? "admin" }}';
        (function() {
            try {
                const serverTheme = window.__SAVED_THEME__;
                const storageKey = 'gym_console_theme_' + window.__THEME_STORAGE_KEY__;
                let theme = serverTheme;
                if (!theme) {
                    const local = localStorage.getItem(storageKey) || localStorage.getItem('gym_console_theme_settings');
                    if (local) theme = JSON.parse(local);
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
            } catch (e) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>

    <title>{{ $title ?? ($platformSettings['app_name'] ?? 'Gym Console') }}</title>

    @if(!empty($platformSettings['favicon_url']))
        <link rel="icon" type="image/x-icon" href="{{ $platformSettings['favicon_url'] }}">
        <link rel="shortcut icon" href="{{ $platformSettings['favicon_url'] }}">
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

    @if(!empty($platformSettings['custom_header_scripts']))
        {!! $platformSettings['custom_header_scripts'] !!}
    @endif
</head>
<body class="flex h-screen h-[100dvh] overflow-hidden antialiased bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100" x-data="{ sidebarOpen: false }">

    <!-- Mobile Sidebar Backdrop -->
    <div x-show="sidebarOpen" class="fixed inset-0 z-40 bg-black/80 lg:hidden" @click="sidebarOpen = false" x-cloak></div>

    <!-- Sidebar Navigation (Fixed on Left) -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 flex flex-col transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:h-screen lg:shrink-0">
        <!-- Brand Header -->
        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-200 dark:border-slate-800 bg-white/90 dark:bg-slate-900/50 backdrop-blur">
            <a href="{{ route('app.dashboard') }}" class="flex items-center gap-2.5">
                @php
                    $tenant = auth()->user()->tenant ?? null;
                    $gymLogo = $tenant?->logo_url;
                    $gymName = $tenant?->name ?? ($platformSettings['app_name'] ?? 'Gym Console');
                @endphp
                @if(!empty($gymLogo))
                    <img src="{{ $gymLogo }}" alt="{{ $gymName }}" class="h-9 w-9 rounded-xl object-contain bg-slate-100 dark:bg-slate-950/60 p-1 border border-slate-200 dark:border-slate-800 shadow-sm shrink-0">
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight truncate max-w-[140px]">{{ $gymName }}</span>
                @elseif(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $gymName }}" class="h-9 w-9 rounded-xl object-contain bg-slate-100 dark:bg-slate-950/60 p-1 border border-slate-200 dark:border-slate-800 shadow-sm shrink-0">
                    <span class="font-extrabold text-slate-900 dark:text-white text-sm tracking-tight truncate max-w-[140px]">{{ $gymName }}</span>
                @else
                    <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center text-slate-950 font-bold shadow-md shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <span class="font-extrabold text-slate-900 dark:text-white text-base tracking-tight truncate max-w-[140px]">{{ $gymName }}</span>
                @endif
            </a>
            <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 dark:hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-grow px-3 py-3 space-y-1 overflow-y-auto text-xs font-semibold">
            @if(auth()->user()->role === 'member')
            <!-- Member Self-Service Portal Navigation -->
            <a href="{{ route('app.portal') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl {{ (request()->routeIs('app.portal') && !request()->has('tab')) ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>MY PORTAL</span>
                </div>
            </a>

            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">My Gym Hub</div>

            <a href="{{ route('app.portal', ['tab' => 'memberships']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'memberships' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>MEMBERSHIP & BILLING</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'workouts']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'workouts' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>WORKOUT PLANS</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'diets']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'diets' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>DIET & NUTRITION</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'pt']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'pt' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>PERSONAL TRAINING</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'services']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'services' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>SERVICES & LOCKERS</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'classes']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'classes' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>CLASSES</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'measurements']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'measurements' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>MEASUREMENTS</span>
                </div>
            </a>

            <a href="{{ route('app.portal', ['tab' => 'attendance']) }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->query('tab') === 'attendance' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>ATTENDANCE</span>
                </div>
            </a>
            @else
            <!-- Dashboard -->
            <a href="{{ route('app.dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl {{ request()->routeIs('app.dashboard') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span>DASHBOARD</span>
                </div>
            </a>

            @php
                $user = auth()->user();
                $canGymOps = ($user->hasPermission('members.view') && $user->hasFeature('members_management'))
                    || ($user->hasPermission('memberships.view') && $user->hasFeature('memberships_billing'))
                    || ($user->hasPermission('attendance.view') && $user->hasFeature('attendance_checkin'))
                    || ($user->hasPermission('payments.view') && $user->hasFeature('payments_pos'));
            @endphp

            @if($canGymOps)
            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Gym Operations</div>

            <!-- Members -->
            @if($user->hasPermission('members.view') && $user->hasFeature('members_management'))
            <a href="{{ route('app.members.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.members.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>MEMBERS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Memberships -->
            @if($user->hasPermission('memberships.view') && $user->hasFeature('memberships_billing'))
            <a href="{{ route('app.memberships.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ (request()->routeIs('app.memberships.*') || request()->routeIs('app.membership-plans.*')) ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>MEMBERSHIPS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Attendance -->
            @if($user->hasPermission('attendance.view') && $user->hasFeature('attendance_checkin'))
            <a href="{{ route('app.attendance.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.attendance.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>ATTENDANCE</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Payments & POS -->
            @if($user->hasPermission('payments.view') && $user->hasFeature('payments_pos'))
            <a href="{{ route('app.payments.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.payments.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>PAYMENTS & POS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
            @endif

            @php
                $canFitness = ($user->hasPermission('classes.view') && $user->hasFeature('group_classes'))
                    || ($user->hasPermission('trainers.view') && $user->hasFeature('trainers_management'))
                    || ($user->hasPermission('pt.view') && $user->hasFeature('personal_training'))
                    || ($user->hasPermission('workouts.view') && $user->hasFeature('workout_plans'))
                    || ($user->hasPermission('diets.view') && $user->hasFeature('diet_nutrition'))
                    || ($user->hasPermission('services.view') && $user->hasFeature('gym_services'));
            @endphp

            @if($canFitness)
            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Fitness & Coaching</div>

            <!-- Training -->
            @if($user->hasPermission('classes.view') && $user->hasFeature('group_classes'))
            <a href="{{ route('app.classes.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.classes.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>TRAINING & CLASSES</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Trainers -->
            @if($user->hasPermission('trainers.view') && $user->hasFeature('trainers_management'))
            <a href="{{ route('app.trainers.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.trainers.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>TRAINERS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Personal Training -->
            @if($user->hasPermission('pt.view') && $user->hasFeature('personal_training'))
            <a href="{{ route('app.pt.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ (request()->routeIs('app.pt.*') || request()->routeIs('app.pt-plans.*')) ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>PERSONAL TRAINING</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Workout Plans -->
            @if($user->hasPermission('workouts.view') && $user->hasFeature('workout_plans'))
            <a href="{{ route('app.workouts.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.workouts.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>WORKOUT PLANS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Diet & Nutrition -->
            @if($user->hasPermission('diets.view') && $user->hasFeature('diet_nutrition'))
            <a href="{{ route('app.diets.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.diets.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>DIET & NUTRITION</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Services -->
            @if($user->hasPermission('services.view') && $user->hasFeature('gym_services'))
            <a href="{{ route('app.services.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.services.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    <span>SERVICES</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
            @endif

            @php
                $canBiz = ($user->hasPermission('crm.view') && $user->hasFeature('crm_leads'))
                    || ($user->hasAnyPermission(['reports.member_report', 'reports.balance_sheet', 'expenses.manage']) && $user->hasFeature('reports_finance'))
                    || ($user->hasPermission('inventory.view') && $user->hasAnyFeature(['inventory_stock', 'equipment_maintenance']));
            @endphp

            @if($canBiz)
            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Business & Growth</div>

            <!-- CRM & Leads Expandable Section -->
            @if($user->hasPermission('crm.view') && $user->hasFeature('crm_leads'))
            @php
                $isCrmActive = request()->routeIs('app.crm.*') || request()->routeIs('app.leads.*') || request()->routeIs('app.trials.*');
            @endphp
            <div x-data="{ open: {{ $isCrmActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl {{ $isCrmActive ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        <span>CRM & LEADS</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-600 dark:text-amber-400 text-[9px] font-black tracking-wider uppercase border border-amber-500/30">NEW</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </button>

                <div x-show="open" x-cloak class="pl-4 pr-1 py-1 space-y-0.5 text-xs">
                    <!-- CRM Dashboard -->
                    <a href="{{ route('app.crm.dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ (request()->routeIs('app.crm.dashboard') || request()->routeIs('app.crm.index')) ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('app.crm.dashboard') || request()->routeIs('app.crm.index')) ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>CRM Dashboard</span>
                    </a>

                    <!-- Leads -->
                    <a href="{{ route('app.leads.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ (request()->routeIs('app.leads.*') || request()->routeIs('app.crm.leads')) ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('app.leads.*') || request()->routeIs('app.crm.leads')) ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Leads</span>
                    </a>

                    <!-- Enquiries -->
                    <a href="{{ route('app.crm.enquiries') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.crm.enquiries') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.crm.enquiries') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Enquiries</span>
                    </a>

                    <!-- Trial -->
                    <a href="{{ route('app.crm.trials') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ (request()->routeIs('app.crm.trials') || request()->routeIs('app.trials.*')) ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('app.crm.trials') || request()->routeIs('app.trials.*')) ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Trial</span>
                    </a>

                    <!-- Conversions -->
                    <a href="{{ route('app.crm.conversions') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.crm.conversions') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.crm.conversions') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Conversions</span>
                    </a>

                    <!-- CRM Reports -->
                    <a href="{{ route('app.crm.reports') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.crm.reports') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.crm.reports') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>CRM Reports</span>
                    </a>
                </div>
            </div>
            @endif

            <!-- WhatsApp Management Section -->
            @if($user->hasFeature('whatsapp_marketing') && $user->hasAnyPermission(['whatsapp.view', 'whatsapp.contacts', 'whatsapp.campaigns', 'whatsapp.templates', 'whatsapp.inbox', 'whatsapp.settings']))
            @php
                $isWhatsAppActive = request()->routeIs('app.whatsapp.*');
            @endphp
            <div x-data="{ open: {{ $isWhatsAppActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl {{ $isWhatsAppActive ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors cursor-pointer">
                    <div class="flex items-center gap-3 min-w-0">
                        <svg class="w-4 h-4 text-emerald-500 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                        </svg>
                        <span class="whitespace-nowrap tracking-wide">WHATSAPP</span>
                    </div>
                    <div class="flex items-center gap-1.5 shrink-0">
                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-[9px] font-black tracking-wider uppercase border border-emerald-500/30">META</span>
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </div>
                </button>

                <div x-show="open" x-cloak class="pl-4 pr-1 py-1 space-y-0.5 text-xs">
                    <!-- Dashboard -->
                    @if($user->hasPermission('whatsapp.view'))
                    <a href="{{ route('app.whatsapp.dashboard') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.dashboard') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.dashboard') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Dashboard</span>
                    </a>
                    @endif

                    <!-- Contacts -->
                    @if($user->hasPermission('whatsapp.contacts'))
                    <a href="{{ route('app.whatsapp.contacts.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.contacts.index') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.contacts.index') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Contacts</span>
                    </a>

                    <!-- Import Contacts -->
                    <a href="{{ route('app.whatsapp.contacts.import') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.contacts.import') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.contacts.import') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Import Contacts</span>
                    </a>
                    @endif

                    <!-- Message Templates -->
                    @if($user->hasPermission('whatsapp.templates'))
                    <a href="{{ route('app.whatsapp.templates.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.templates.*') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.templates.*') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Message Templates</span>
                    </a>
                    @endif

                    <!-- Bulk Campaigns -->
                    @if($user->hasPermission('whatsapp.campaigns'))
                    <a href="{{ route('app.whatsapp.campaigns.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.campaigns.index') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.campaigns.index') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Bulk Campaigns</span>
                    </a>
                    @endif

                    <!-- WhatsApp Inbox -->
                    @if($user->hasPermission('whatsapp.inbox'))
                    <a href="{{ route('app.whatsapp.inbox') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.inbox') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.inbox') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>WhatsApp Inbox</span>
                    </a>
                    @endif

                    <!-- Automations -->
                    @if($user->hasPermission('whatsapp.settings'))
                    <a href="{{ route('app.whatsapp.automations.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.automations.*') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.automations.*') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Automations</span>
                    </a>

                    <!-- Settings -->
                    <a href="{{ route('app.whatsapp.settings') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.whatsapp.settings') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.whatsapp.settings') ? 'bg-emerald-500' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Settings</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif


            <!-- Report & Finance -->
            @if($user->hasAnyPermission(['reports.member_report', 'reports.balance_sheet', 'expenses.manage']) && $user->hasFeature('reports_finance'))
            @php
                $isFinanceActive = request()->routeIs('app.finance.*') || request()->routeIs('app.reports.*') || request()->routeIs('app.expenses.*') || request()->routeIs('app.balance-sheet.*');
            @endphp
            <div x-data="{ open: {{ $isFinanceActive ? 'true' : 'false' }} }">
                <button @click="open = !open" type="button" class="w-full flex items-center justify-between px-3 py-2 rounded-xl {{ $isFinanceActive ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        <span>REPORT & FINANCE</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div x-show="open" x-cloak class="pl-4 pr-1 py-1 space-y-0.5 text-xs">
                    <!-- Member Report -->
                    @if($user->hasPermission('reports.member_report'))
                    <a href="{{ route('app.finance.expense-report') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ (request()->routeIs('app.finance.expense-report') || request()->routeIs('app.finance.member-report') || request()->routeIs('app.reports.*')) ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('app.finance.expense-report') || request()->routeIs('app.finance.member-report') || request()->routeIs('app.reports.*')) ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Member Report</span>
                    </a>
                    @endif

                    <!-- Balance Sheet -->
                    @if($user->hasPermission('reports.balance_sheet'))
                    <a href="{{ route('app.finance.balance-sheet') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ (request()->routeIs('app.finance.balance-sheet') || request()->routeIs('app.balance-sheet.*')) ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ (request()->routeIs('app.finance.balance-sheet') || request()->routeIs('app.balance-sheet.*')) ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Balance Sheet</span>
                    </a>
                    @endif

                    <!-- Expenses -->
                    @if($user->hasPermission('expenses.view') || $user->hasPermission('expenses.create'))
                    <a href="{{ route('app.expenses.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.expenses.*') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.expenses.*') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Expenses</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- Inventory & Equipment Maintenance -->
            @if($user->hasPermission('inventory.view') && $user->hasAnyFeature(['inventory_stock', 'equipment_maintenance']))
            <a href="{{ route('app.inventory.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.inventory.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3 min-w-0">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span class="whitespace-nowrap tracking-wide">INVENTORY & ASSETS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
            @endif

            @php
                $canAdmin = ($user->hasAnyPermission(['staff.view', 'roles.manage']) && $user->hasFeature('staff_roles'))
                    || $user->isGymOwner()
                    || $user->isSuperAdmin()
                    || $user->hasPermission('settings.manage');
            @endphp

            @if($canAdmin)
            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Administration</div>

            <!-- Staff & Team Management Expandable -->
            @if($user->hasAnyPermission(['staff.view', 'roles.manage']) && $user->hasFeature('staff_roles'))
            @php
                $isStaffActive = request()->routeIs('app.staff.*') || request()->routeIs('app.roles.*');
            @endphp
            <div x-data="{ open: {{ $isStaffActive ? 'true' : 'false' }} }" class="space-y-0.5">
                <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-3 py-2 rounded-xl {{ $isStaffActive ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors cursor-pointer">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span>STAFF & ROLES</span>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 transition-transform duration-200" :class="{ 'rotate-90': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </button>

                <div x-show="open" x-cloak class="pl-4 pr-1 py-1 space-y-0.5 text-xs">
                    <!-- Staff Members List -->
                    @if($user->hasPermission('staff.view'))
                    <a href="{{ route('app.staff.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.staff.*') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.staff.*') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Staff Members</span>
                    </a>
                    @endif

                    <!-- Roles & Permissions -->
                    @if($user->hasPermission('roles.manage'))
                    <a href="{{ route('app.roles.index') }}" class="flex items-center gap-2.5 px-3 py-1.5 rounded-lg {{ request()->routeIs('app.roles.*') ? 'text-slate-950 dark:text-white bg-slate-200/80 dark:bg-slate-800 font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800/60 font-semibold' }} transition-colors">
                        <span class="w-1.5 h-1.5 rounded-full {{ request()->routeIs('app.roles.*') ? 'bg-slate-900 dark:bg-white' : 'bg-slate-400 dark:bg-slate-600' }}"></span>
                        <span>Roles & Permissions</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            <!-- SaaS Subscription -->
            @if($user->isGymOwner() || $user->isSuperAdmin())
            <a href="{{ route('app.subscription.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.subscription.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    <span>SAAS SUBSCRIPTION</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif

            <!-- Settings -->
            @if($user->hasPermission('settings.manage') || $user->isGymOwner())
            <a href="{{ route('app.settings.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.settings.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>SETTINGS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
            @endif

            <!-- Helpdesk & Support Tickets -->
            <div class="pt-2.5 pb-1 px-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Help & Support</div>
            <a href="{{ route('app.support.index') }}" class="flex items-center justify-between px-3 py-2 rounded-xl {{ request()->routeIs('app.support.*') ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-bold' }} transition-colors">
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <span>SUPPORT TICKETS</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
            @endif
        </nav>

        <!-- User Profile & Branch Switcher Footer -->
        <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-900/80 space-y-2.5">
            @php
                $user = auth()->user();
                $tenantBranches = $user->tenant ? $user->tenant->branches : collect();
                $allowedBranchIds = $user->tenant ? $user->tenant->allowedBranches()->pluck('id')->toArray() : [];
                $currentActiveBranch = $user->currentBranch();
            @endphp

            @if($tenantBranches->count() > 1)
            <!-- Multi-Branch Switching Popover -->
            <div class="relative" x-data="{ branchDropdownOpen: false }">
                <!-- Trigger Button with Pulse -->
                <button type="button" @click="branchDropdownOpen = !branchDropdownOpen" 
                        class="w-full flex items-center justify-between p-2 rounded-xl bg-slate-100 hover:bg-slate-200 border border-slate-200 dark:bg-slate-950/60 dark:hover:bg-slate-800 dark:border-slate-800 transition-all text-xs cursor-pointer group">
                    <div class="flex items-center gap-2 truncate">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                        </span>
                        <div class="truncate text-left">
                            <span class="font-extrabold text-slate-900 dark:text-white block text-[11px] truncate">{{ $currentActiveBranch->name ?? 'Main Branch' }}</span>
                            <span class="text-[9px] text-slate-500 dark:text-slate-400 font-mono font-normal block">{{ $currentActiveBranch->code ?? 'MAIN' }}</span>
                        </div>
                    </div>
                    <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-slate-700 dark:group-hover:text-slate-300 shrink-0 transition-transform duration-200" :class="{ 'rotate-180': branchDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                <!-- Popover Dropdown (Opens upward above the button) -->
                <div x-show="branchDropdownOpen" @click.away="branchDropdownOpen = false" x-cloak 
                     class="absolute bottom-full left-0 mb-2 w-full rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-1.5 z-50 space-y-1 text-xs max-h-64 overflow-y-auto">
                    <div class="px-2.5 py-1.5 text-[10px] font-extrabold uppercase text-slate-500 dark:text-slate-400 tracking-wider flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-1.5">
                        <span>Select Branch</span>
                        <span class="text-[9px] text-slate-500 dark:text-slate-400 font-mono">{{ count($allowedBranchIds) }}/{{ $tenantBranches->count() }} Available</span>
                    </div>

                    @foreach($tenantBranches as $tb)
                    @php
                        $isAllowed = in_array($tb->id, $allowedBranchIds, true);
                    @endphp
                    @if($isAllowed)
                    <form action="{{ route('app.branches.switch', $tb->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full text-left px-2.5 py-2 rounded-xl flex items-center justify-between {{ ($currentActiveBranch && $currentActiveBranch->id === $tb->id) ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-bold border border-slate-700 shadow-sm' : 'text-slate-700 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white' }} transition-colors cursor-pointer">
                            <div class="truncate">
                                <div class="truncate font-semibold flex items-center gap-1.5">
                                    @if($tb->is_main)
                                        <span class="text-amber-500 text-[10px]">★</span>
                                    @endif
                                    <span>{{ $tb->name }}</span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $tb->code ?? 'MAIN' }} {{ $tb->city ? '• '.$tb->city : '' }}</div>
                            </div>
                            @if($currentActiveBranch && $currentActiveBranch->id === $tb->id)
                                <svg class="w-3.5 h-3.5 text-white dark:text-slate-200 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                            @endif
                        </button>
                    </form>
                    @else
                    <a href="{{ route('app.subscription.index') }}" class="w-full text-left px-2.5 py-2 rounded-xl flex items-center justify-between bg-slate-100 dark:bg-slate-950/40 opacity-70 hover:opacity-100 hover:bg-slate-200 dark:hover:bg-slate-800/80 border border-slate-200 dark:border-slate-800/60 transition-all cursor-pointer group" title="Upgrade your plan to unlock this branch">
                        <div class="truncate">
                            <div class="truncate font-semibold flex items-center gap-1.5 text-slate-500 dark:text-slate-400 group-hover:text-amber-600 dark:group-hover:text-amber-300">
                                <svg class="w-3 h-3 text-amber-500/80 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                <span>{{ $tb->name }}</span>
                            </div>
                            <div class="text-[9px] text-amber-600 dark:text-amber-400/90 font-medium">Upgrade to Unlock</div>
                        </div>
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">LOCKED</span>
                    </a>
                    @endif
                    @endforeach

                    <div class="pt-1 border-t border-slate-200 dark:border-slate-800">
                        <a href="{{ route('app.settings.index', ['tab' => 'branches']) }}" class="flex items-center gap-1.5 px-2.5 py-1.5 text-[11px] text-slate-900 dark:text-white hover:underline font-bold rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Manage Branches</span>
                        </a>
                    </div>
                </div>
            </div>
            @endif

            <div class="flex items-center justify-between pt-1">
                <div class="flex items-center gap-2 overflow-hidden">
                    <div class="w-7 h-7 rounded-full bg-slate-900 dark:bg-slate-800 border border-slate-700 flex items-center justify-center font-extrabold text-white text-[11px] shadow-sm">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] text-slate-500 dark:text-slate-400 capitalize font-medium">{{ str_replace('_', ' ', auth()->user()->role) }}</p>
                    </div>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" title="Sign out" class="p-1.5 text-slate-500 dark:text-slate-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content Shell -->
    <div class="flex-1 flex flex-col min-w-0 bg-slate-100 dark:bg-slate-950 transition-colors duration-200">
        @php
            $currentTenant = auth()->user()->tenant;
        @endphp
        @if($currentTenant && $currentTenant->isSubscriptionExpired())
            <div class="bg-gradient-to-r from-red-600 via-rose-600 to-amber-600 text-white px-6 py-2.5 shadow-lg flex flex-col sm:flex-row items-center justify-between gap-3 text-xs border-b border-red-500/30">
                <div class="flex items-center gap-2.5">
                    <span class="flex h-2.5 w-2.5 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-200"></span>
                    </span>
                    <span class="font-bold tracking-tight">⚠️ SaaS Trial / Plan Expired:</span>
                    <span class="text-white/90 hidden md:inline">Your gym operations are locked. Please renew or select a plan to restore full access immediately.</span>
                </div>
                <a href="{{ route('app.subscription.index') }}" class="px-3.5 py-1 rounded-lg bg-white text-slate-950 font-bold hover:bg-amber-100 transition-all shadow-sm flex items-center gap-1.5 whitespace-nowrap">
                    <span>⚡ Renew / Choose Plan</span>
                    <span>&rarr;</span>
                </a>
            </div>
        @endif

        <!-- Top Navbar -->
        <header class="h-16 bg-white/90 dark:bg-slate-900/80 backdrop-blur border-b border-slate-200 dark:border-slate-800 px-3 sm:px-4 md:px-6 flex items-center justify-between sticky top-0 z-30 transition-colors duration-200">
            <div class="flex items-center gap-2 sm:gap-4 min-w-0">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-1.5 rounded-xl text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-sm font-bold text-slate-900 dark:text-white truncate max-w-[150px] xs:max-w-[200px] sm:max-w-none">{{ $header ?? 'Dashboard' }}</span>
                </div>
            </div>

            <!-- Right Telemetry & Widget Badges -->
            <div class="flex items-center gap-1.5 sm:gap-3 shrink-0">
                
                <!-- Live Gym Footfall Widget -->
                @php
                    $todayInsideCount = \App\Models\Attendance::where('date', now()->toDateString())->whereNull('check_out')->count();
                    $todayTotalAttendance = \App\Models\Attendance::where('date', now()->toDateString())->count();
                @endphp
                <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-xs font-semibold">
                    <span class="flex items-center gap-1 text-slate-700 dark:text-slate-300">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-slate-900 dark:text-white font-bold">{{ $todayInsideCount }}</span> In Gym
                    </span>
                    <span class="text-slate-300 dark:text-slate-600">|</span>
                    <span class="text-slate-500 dark:text-slate-400">{{ $todayTotalAttendance }} Today</span>
                </div>

                <!-- Live Date Pill -->
                <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800/80 border border-slate-200 dark:border-slate-700/60 text-slate-800 dark:text-slate-200 text-xs font-bold shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-600 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>{{ now()->format('D, j M Y') }}</span>
                </div>

                <!-- Live Digital Ticking Clock -->
                <div x-data="{ 
                        time: '',
                        init() {
                            const update = () => {
                                const d = new Date();
                                let h = d.getHours();
                                const m = String(d.getMinutes()).padStart(2, '0');
                                const s = String(d.getSeconds()).padStart(2, '0');
                                const ampm = h >= 12 ? 'PM' : 'AM';
                                h = h % 12 || 12;
                                this.time = `${String(h).padStart(2, '0')} : ${m} : ${s} ${ampm}`;
                            };
                            update();
                            setInterval(update, 1000);
                        }
                     }" 
                     class="hidden lg:flex items-center px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-amber-700 dark:text-amber-400 font-mono text-xs font-bold tracking-wider shadow-inner">
                    <span x-text="time">--:--:-- --</span>
                </div>

                <x-theme-customizer />

                @if(auth()->user()->isSuperAdmin())
                    <a href="{{ route('admin.dashboard') }}" class="hidden sm:inline-flex px-3 py-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 text-xs font-bold hover:bg-amber-500 hover:text-slate-950 transition-all">
                        ⚙️ Super Admin
                    </a>
                @endif

                <!-- Interactive Notification Center -->
                <div x-data="notificationCenter()" x-init="init()" @click.outside="isOpen = false" @keydown.escape.window="isOpen = false" class="relative">
                    <!-- Bell Button -->
                    <button type="button" 
                            @click="toggleDropdown()" 
                            title="Notifications & Operational Alerts" 
                            class="relative p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700/60 transition-all cursor-pointer focus:outline-none">
                        <svg class="w-4 h-4 text-current transition-transform duration-200" :class="{ 'scale-110 rotate-12': isOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>

                        <!-- Dynamic Pulsating Unread Badge -->
                        <template x-if="unreadCount > 0">
                            <span class="absolute -top-1 -right-1 flex h-4 min-w-4 px-1 items-center justify-center">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full px-1 py-0.2 bg-rose-600 text-white text-[9px] font-black leading-none shadow-sm" x-text="unreadCount > 9 ? '9+' : unreadCount"></span>
                            </span>
                        </template>
                    </button>

                    <!-- Dropdown Panel -->
                    <div x-show="isOpen" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         x-cloak
                         class="absolute right-0 sm:right-0 mt-2.5 w-[330px] sm:w-[400px] md:w-[440px] max-w-[calc(100vw-1.5rem)] rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl z-50 overflow-hidden font-sans">
                        
                        <!-- Header -->
                        <div class="p-3.5 sm:p-4 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/70 dark:bg-slate-950/50">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-xs">
                                    🔔
                                </div>
                                <div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Alert Center</h4>
                                    <p class="text-[10px] text-slate-500 dark:text-slate-400">Gym Operations & WhatsApp Insights</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <template x-if="unreadCount > 0">
                                    <button type="button" 
                                            @click="markAllAsRead()" 
                                            class="text-[10px] text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-bold hover:underline cursor-pointer">
                                        Mark all read
                                    </button>
                                </template>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300" 
                                      x-text="unreadCount + ' unread'"></span>
                            </div>
                        </div>

                        <!-- Filter Chips Row -->
                        <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800/80 bg-white dark:bg-slate-900 flex items-center gap-1.5 overflow-x-auto text-[10px] font-bold">
                            <button type="button" 
                                    @click="activeFilter = 'all'" 
                                    :class="activeFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                                    class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                All (<span x-text="counts.all"></span>)
                            </button>
                            <template x-if="counts.whatsapp > 0">
                                <button type="button" 
                                        @click="activeFilter = 'whatsapp'" 
                                        :class="activeFilter === 'whatsapp' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400'" 
                                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                    WhatsApp (<span x-text="counts.whatsapp"></span>)
                                </button>
                            </template>
                            <template x-if="counts.renewal > 0">
                                <button type="button" 
                                        @click="activeFilter = 'renewal'" 
                                        :class="activeFilter === 'renewal' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400'" 
                                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                    Renewals (<span x-text="counts.renewal"></span>)
                                </button>
                            </template>
                            <template x-if="counts.payment > 0">
                                <button type="button" 
                                        @click="activeFilter = 'payment'" 
                                        :class="activeFilter === 'payment' ? 'bg-rose-600 text-white shadow-sm' : 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400'" 
                                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                    Dues (<span x-text="counts.payment"></span>)
                                </button>
                            </template>
                            <template x-if="counts.birthday > 0">
                                <button type="button" 
                                        @click="activeFilter = 'birthday'" 
                                        :class="activeFilter === 'birthday' ? 'bg-purple-600 text-white shadow-sm' : 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400'" 
                                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                    Birthdays (<span x-text="counts.birthday"></span>)
                                </button>
                            </template>
                            <template x-if="counts.lead > 0">
                                <button type="button" 
                                        @click="activeFilter = 'lead'" 
                                        :class="activeFilter === 'lead' ? 'bg-sky-600 text-white shadow-sm' : 'bg-sky-50 dark:bg-sky-500/10 text-sky-700 dark:text-sky-400'" 
                                        class="px-2.5 py-1 rounded-lg transition-colors whitespace-nowrap cursor-pointer">
                                    Leads (<span x-text="counts.lead"></span>)
                                </button>
                            </template>
                        </div>

                        <!-- Notification Items List -->
                        <div class="max-h-[380px] sm:max-h-[420px] overflow-y-auto divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-900">
                            <!-- Loading Skeleton -->
                            <template x-if="isLoading && notifications.length === 0">
                                <div class="p-6 text-center space-y-3">
                                    <div class="inline-block animate-spin rounded-full h-5 w-5 border-2 border-indigo-500 border-t-transparent"></div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Checking live alerts...</p>
                                </div>
                            </template>

                            <!-- Empty State -->
                            <template x-if="!isLoading && filteredNotifications().length === 0">
                                <div class="p-8 text-center space-y-2.5">
                                    <div class="w-10 h-10 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg">
                                        ✓
                                    </div>
                                    <h5 class="text-xs font-bold text-slate-900 dark:text-white">All Caught Up!</h5>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto">No pending operational alerts or unread messages right now.</p>
                                </div>
                            </template>

                            <!-- Notification Cards -->
                            <template x-for="item in filteredNotifications()" :key="item.id">
                                <div class="p-3 sm:p-3.5 transition-colors relative group"
                                     :class="item.is_read ? 'hover:bg-slate-50 dark:hover:bg-slate-800/40 opacity-80 hover:opacity-100' : 'bg-indigo-50/30 dark:bg-slate-800/40 hover:bg-indigo-50/60 dark:hover:bg-slate-800/70 border-l-2 border-indigo-500'">
                                    
                                    <div class="flex items-start gap-2.5 sm:gap-3">
                                        <!-- Category Icon Avatar -->
                                        <div class="w-8 h-8 rounded-xl flex-shrink-0 flex items-center justify-center font-bold text-xs"
                                             :class="{
                                                 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400': item.type === 'whatsapp',
                                                 'bg-amber-100 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400': item.type === 'renewal',
                                                 'bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400': item.type === 'payment',
                                                 'bg-purple-100 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400': item.type === 'birthday',
                                                 'bg-sky-100 dark:bg-sky-500/20 text-sky-600 dark:text-sky-400': item.type === 'lead',
                                                 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400': !['whatsapp', 'renewal', 'payment', 'birthday', 'lead'].includes(item.type)
                                             }">
                                            <template x-if="item.type === 'whatsapp'"><span>💬</span></template>
                                            <template x-if="item.type === 'renewal'"><span>⏳</span></template>
                                            <template x-if="item.type === 'payment'"><span>💰</span></template>
                                            <template x-if="item.type === 'birthday'"><span>🎂</span></template>
                                            <template x-if="item.type === 'lead'"><span>👥</span></template>
                                            <template x-if="!['whatsapp', 'renewal', 'payment', 'birthday', 'lead'].includes(item.type)"><span>🔔</span></template>
                                        </div>

                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-1">
                                                <h5 class="text-xs font-bold text-slate-900 dark:text-white truncate" x-text="item.title"></h5>
                                                <span class="text-[9px] text-slate-400 dark:text-slate-500 font-semibold whitespace-nowrap" x-text="item.time_ago"></span>
                                            </div>
                                            <p class="text-[11px] text-slate-600 dark:text-slate-300 mt-0.5 line-clamp-2 leading-relaxed" x-text="item.message"></p>

                                            <!-- Bottom Action Row -->
                                            <div class="mt-2 flex items-center justify-between gap-2">
                                                <a :href="item.link" 
                                                   :target="item.is_external ? '_blank' : '_self'"
                                                   @click="markAsRead(item.id)"
                                                   class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-bold text-white transition-all shadow-sm cursor-pointer"
                                                   :class="{
                                                       'bg-emerald-600 hover:bg-emerald-500': item.type === 'whatsapp',
                                                       'bg-amber-600 hover:bg-amber-500': item.type === 'renewal',
                                                       'bg-rose-600 hover:bg-rose-500': item.type === 'payment',
                                                       'bg-purple-600 hover:bg-purple-500': item.type === 'birthday',
                                                       'bg-sky-600 hover:bg-sky-500': item.type === 'lead',
                                                       'bg-indigo-600 hover:bg-indigo-500': !['whatsapp', 'renewal', 'payment', 'birthday', 'lead'].includes(item.type)
                                                   }">
                                                    <span x-text="item.action_label"></span>
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                                                </a>

                                                <template x-if="!item.is_read">
                                                    <button type="button" 
                                                            @click="markAsRead(item.id)" 
                                                            title="Mark as read"
                                                            class="text-[10px] text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 font-semibold transition-colors cursor-pointer">
                                                        Dismiss
                                                    </button>
                                                </template>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>

                        <!-- Footer -->
                        <div class="p-2.5 px-3.5 border-t border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-950/70 flex items-center justify-between text-[10px] text-slate-500 dark:text-slate-400">
                            <span class="flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Live Operations Feed</span>
                            </span>
                            <div class="flex items-center gap-2 font-bold">
                                <a href="{{ route('app.crm.leads') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Leads</a>
                                <span>•</span>
                                <a href="{{ route('app.whatsapp.inbox') }}" class="hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors">WhatsApp</a>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- User Profile & Account Dropdown -->
                <div class="relative flex items-center pl-0.5 sm:pl-1" 
                     x-data="{ 
                         userMenuOpen: false, 
                         showPasswordModal: {{ ($errors->has('current_password') || $errors->has('password')) ? 'true' : 'false' }},
                         showCurrentPass: false,
                         showNewPass: false,
                         showConfirmPass: false
                     }">
                    <!-- Trigger Avatar Button -->
                    <button @click="userMenuOpen = !userMenuOpen" 
                            type="button" 
                            class="group relative flex items-center gap-2 p-0.5 rounded-full hover:ring-2 hover:ring-indigo-500/50 focus:outline-none transition-all cursor-pointer"
                            title="Account Profile & Settings">
                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white font-black text-xs flex items-center justify-center shadow-md shadow-indigo-500/20 group-hover:scale-105 transition-transform border border-white/20">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </div>
                        <span class="absolute bottom-0 right-0 w-2.5 h-2.5 rounded-full bg-emerald-500 ring-2 ring-white dark:ring-slate-900"></span>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="userMenuOpen"
                         x-cloak
                         @click.away="userMenuOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                         x-transition:leave-end="opacity-0 scale-95 translate-y-2"
                         class="absolute right-0 top-full mt-2 w-72 sm:w-80 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl z-50 overflow-hidden divide-y divide-slate-100 dark:divide-slate-800">
                        
                        <!-- Account Summary Header -->
                        <div class="p-4 bg-gradient-to-br from-slate-50 via-white to-indigo-50/30 dark:from-slate-900 dark:via-slate-900/90 dark:to-indigo-950/20">
                            <div class="flex items-start gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-indigo-600 via-indigo-500 to-purple-600 text-white font-black text-base flex items-center justify-center shadow-lg shadow-indigo-500/25 border border-white/20 shrink-0">
                                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ auth()->user()->name }}</h4>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                                        @php
                                            $role = auth()->user()->role;
                                            $roleBadgeClass = match($role) {
                                                'super_admin' => 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border-rose-500/20',
                                                'gym_owner'   => 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border-indigo-500/20',
                                                'trainer'     => 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border-amber-500/20',
                                                'staff'       => 'bg-blue-500/10 text-blue-600 dark:text-blue-400 border-blue-500/20',
                                                default       => 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border-emerald-500/20',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold border capitalize {{ $roleBadgeClass }}">
                                            {{ str_replace('_', ' ', $role) }}
                                        </span>

                                        @if(auth()->user()->tenant)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 max-w-[130px] truncate" title="{{ auth()->user()->tenant->name }}">
                                                <svg class="w-2.5 h-2.5 text-indigo-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h8a2 2 0 012 2v12a1 1 0 110 2h-3a1 1 0 01-1-1v-2a1 1 0 00-1-1H9a1 1 0 00-1 1v2a1 1 0 01-1 1H4a1 1 0 110-2V4zm3 1h2v2H7V5zm2 4H7v2h2V9zm2-4h2v2h-2V5zm2 4h-2v2h2V9z" clip-rule="evenodd"/></svg>
                                                <span class="truncate">{{ auth()->user()->tenant->name }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(auth()->user()->tenant && auth()->user()->tenant->activeSubscription)
                                <div class="mt-3 pt-2.5 border-t border-slate-200/60 dark:border-slate-800/80 flex items-center justify-between text-[11px]">
                                    <span class="text-slate-500 dark:text-slate-400">SaaS Plan:</span>
                                    <span class="font-bold text-indigo-600 dark:text-indigo-400 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                        {{ auth()->user()->tenant->activeSubscription->plan?->name ?? 'Standard' }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Navigation Shortcuts -->
                        <div class="py-1.5 text-xs">
                            @if(auth()->user()->isSuperAdmin())
                                <a href="{{ route('admin.dashboard') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-rose-600 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-colors font-medium">
                                    <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>Super Admin Portal</span>
                                </a>
                            @endif

                            @if(auth()->user()->isSuperAdmin() || auth()->user()->isGymOwner())
                                <a href="{{ route('app.settings.index') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    <span>Gym Settings</span>
                                </a>

                                <a href="{{ route('app.subscription.index') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                    <span>Subscription & Billing</span>
                                </a>

                                <a href="{{ route('app.roles.index') }}" 
                                   class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">
                                    <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    <span>Roles & Permissions</span>
                                </a>
                            @endif

                            <a href="{{ route('app.whatsapp.dashboard') }}" 
                               class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-emerald-50/50 dark:hover:bg-emerald-950/20 transition-colors font-medium">
                                <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                <span>WhatsApp Marketing Hub</span>
                            </a>

                            <a href="{{ route('app.support.index') }}" 
                               class="flex items-center gap-2.5 px-4 py-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors font-medium">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                                <span>Help & Support</span>
                            </a>
                        </div>

                        <!-- Account Actions (Change Password & Logout) -->
                        <div class="p-2 space-y-1 text-xs">
                            <button @click="userMenuOpen = false; showPasswordModal = true"
                                    type="button"
                                    class="w-full flex items-center gap-2.5 px-3 py-2 text-slate-700 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 hover:bg-slate-100 dark:hover:bg-slate-800/70 rounded-xl transition-colors font-medium text-left cursor-pointer">
                                <svg class="w-4 h-4 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                <span>Change Password</span>
                            </button>

                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" 
                                        class="w-full flex items-center gap-2.5 px-3 py-2 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 rounded-xl transition-colors font-semibold text-left cursor-pointer">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                    <span>Sign Out</span>
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Change Password Modal -->
                    <div x-show="showPasswordModal" 
                         x-cloak
                         @keydown.escape.window="showPasswordModal = false"
                         class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-950/60 backdrop-blur-sm"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        
                        <div @click.away="showPasswordModal = false"
                             class="w-full max-w-md bg-white dark:bg-slate-900 rounded-3xl border border-slate-200 dark:border-slate-800 shadow-2xl overflow-hidden relative"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95">
                            
                            <!-- Modal Header -->
                            <div class="px-6 pt-6 pb-4 border-b border-slate-100 dark:border-slate-800 flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    </div>
                                    <div>
                                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Change Password</h3>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Secure your account with a strong new password.</p>
                                    </div>
                                </div>
                                <button @click="showPasswordModal = false" type="button" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 p-1 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <!-- Modal Form -->
                            <form action="{{ route('app.profile.change-password') }}" method="POST" class="p-6 space-y-4">
                                @csrf

                                @if ($errors->any())
                                    <div class="p-3 rounded-xl bg-red-500/10 border border-red-500/20 text-red-500 text-xs space-y-1">
                                        @foreach ($errors->all() as $error)
                                            <p class="flex items-center gap-1.5 font-medium">
                                                <span>•</span> {{ $error }}
                                            </p>
                                        @endforeach
                                    </div>
                                @endif

                                <!-- Current Password -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Current Password</label>
                                    <div class="relative">
                                        <input :type="showCurrentPass ? 'text' : 'password'" 
                                               name="current_password" 
                                               required 
                                               placeholder="Enter current password"
                                               class="w-full pl-3.5 pr-10 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all">
                                        <button type="button" 
                                                @click="showCurrentPass = !showCurrentPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none">
                                            <svg x-show="!showCurrentPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showCurrentPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- New Password -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">New Password</label>
                                    <div class="relative">
                                        <input :type="showNewPass ? 'text' : 'password'" 
                                               name="password" 
                                               required 
                                               minlength="8"
                                               placeholder="Minimum 8 characters"
                                               class="w-full pl-3.5 pr-10 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all">
                                        <button type="button" 
                                                @click="showNewPass = !showNewPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none">
                                            <svg x-show="!showNewPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showNewPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                    <p class="text-[10px] text-slate-400 mt-1">Must be at least 8 characters long and different from current password.</p>
                                </div>

                                <!-- Confirm Password -->
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Confirm New Password</label>
                                    <div class="relative">
                                        <input :type="showConfirmPass ? 'text' : 'password'" 
                                               name="password_confirmation" 
                                               required 
                                               placeholder="Repeat new password"
                                               class="w-full pl-3.5 pr-10 py-2.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all">
                                        <button type="button" 
                                                @click="showConfirmPass = !showConfirmPass"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 focus:outline-none">
                                            <svg x-show="!showConfirmPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <svg x-show="showConfirmPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="pt-3 flex items-center justify-end gap-2.5">
                                    <button type="button" 
                                            @click="showPasswordModal = false"
                                            class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                                        Cancel
                                    </button>
                                    <button type="submit" 
                                            class="px-5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-lg shadow-indigo-500/25 hover:from-indigo-700 hover:to-purple-700 focus:ring-2 focus:ring-indigo-500/50 transition-all cursor-pointer">
                                        Update Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

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

        @if (session('warning'))
            <div class="mx-3 sm:mx-4 md:mx-6 mt-3 sm:mt-4 p-3.5 sm:p-4 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs flex items-center justify-between">
                <span>{{ session('warning') }}</span>
                <button onclick="this.parentElement.remove()" class="text-amber-400 hover:text-amber-300">✕</button>
            </div>
        @endif

        <!-- Page View Body -->
        <main class="flex-1 p-3 sm:p-4 md:p-6 pb-24 sm:pb-8 overflow-y-auto overscroll-y-contain">
            {{ $slot }}
        </main>
    </div>

    <x-confirm-modal />

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif

    <script>
        function notificationCenter() {
            return {
                isOpen: false,
                isLoading: false,
                activeFilter: 'all',
                notifications: [],
                unreadCount: 0,
                counts: { all: 0, whatsapp: 0, renewal: 0, payment: 0, birthday: 0, lead: 0 },
                feedInterval: null,

                init() {
                    this.fetchFeed();
                    // Auto-refresh notifications periodically every 45 seconds
                    this.feedInterval = setInterval(() => {
                        this.fetchFeed(true);
                    }, 45000);
                },

                toggleDropdown() {
                    this.isOpen = !this.isOpen;
                    if (this.isOpen) {
                        this.fetchFeed();
                    }
                },

                async fetchFeed(silent = false) {
                    if (!silent) this.isLoading = true;
                    try {
                        const res = await fetch("{{ route('app.notifications.feed') }}", {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        });
                        if (res.ok) {
                            const data = await res.json();
                            if (data && data.success) {
                                this.notifications = data.notifications || [];
                                this.unreadCount = data.unread_count || 0;
                                this.counts = data.counts || this.counts;
                            }
                        }
                    } catch (err) {
                        console.error("Failed to load notifications", err);
                    } finally {
                        if (!silent) this.isLoading = false;
                    }
                },

                filteredNotifications() {
                    if (this.activeFilter === 'all') {
                        return this.notifications;
                    }
                    return this.notifications.filter(item => item.type === this.activeFilter);
                },

                async markAsRead(id) {
                    const item = this.notifications.find(n => n.id === id);
                    if (item && !item.is_read) {
                        item.is_read = true;
                        this.unreadCount = Math.max(0, this.unreadCount - 1);
                    }

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch("{{ route('app.notifications.mark-read') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ id: id })
                        });
                    } catch (err) {
                        console.error("Failed to mark notification read", err);
                    }
                },

                async markAllAsRead() {
                    const allIds = this.notifications.map(n => n.id);
                    this.notifications.forEach(n => n.is_read = true);
                    this.unreadCount = 0;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                        await fetch("{{ route('app.notifications.mark-all-read') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify({ ids: allIds })
                        });
                    } catch (err) {
                        console.error("Failed to mark all notifications read", err);
                    }
                }
            };
        }
    </script>

    @stack('scripts')
</body>
</html>

