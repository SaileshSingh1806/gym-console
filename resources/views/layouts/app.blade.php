<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
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
<body class="flex h-screen overflow-hidden antialiased bg-slate-100 text-slate-900 dark:bg-slate-950 dark:text-slate-100" x-data="{ sidebarOpen: false }">

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
                    @if($user->hasPermission('expenses.manage'))
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
                <div class="flex items-center gap-3">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span>INVENTORY & MAINTENANCE</span>
                </div>
                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
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

                <!-- Notification Bell -->
                <button type="button" title="Notifications" class="relative p-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800/80 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700/60 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-current" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    <span class="absolute -top-1 -right-1 px-1.5 py-0.2 rounded-full bg-red-500 text-white text-[9px] font-extrabold shadow-sm">3+</span>
                </button>

                <!-- User Profile Pill / Avatar -->
                <div class="flex items-center gap-2 pl-0.5 sm:pl-1">
                    <div class="w-8 h-8 rounded-full bg-slate-900 dark:bg-slate-800 border border-slate-700 text-white font-black text-xs flex items-center justify-center shadow-sm">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
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
        <main class="flex-1 p-3 sm:p-4 md:p-6 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>

    <x-confirm-modal />

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif

    @stack('scripts')
</body>
</html>

