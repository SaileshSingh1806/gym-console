<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? ($platformSettings['meta_title'] ?? (($platformSettings['app_name'] ?? 'Gym Console') . ' - Gym Management Platform')) }}</title>

    @if(!empty($platformSettings['meta_description']))
        <meta name="description" content="{{ $platformSettings['meta_description'] }}">
    @endif
    @if(!empty($platformSettings['meta_keywords']))
        <meta name="keywords" content="{{ $platformSettings['meta_keywords'] }}">
    @endif

    <!-- OpenGraph / Social Sharing -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $platformSettings['meta_title'] ?? ($platformSettings['app_name'] ?? 'Gym Console') }}">
    @if(!empty($platformSettings['meta_description']))
        <meta property="og:description" content="{{ $platformSettings['meta_description'] }}">
    @endif
    @if(!empty($platformSettings['og_image_url']))
        <meta property="og:image" content="{{ $platformSettings['og_image_url'] }}">
    @endif

    @if(!empty($platformSettings['favicon_url']))
        <link rel="icon" type="image/x-icon" href="{{ $platformSettings['favicon_url'] }}">
        <link rel="shortcut icon" href="{{ $platformSettings['favicon_url'] }}">
    @endif

    <!-- Google Fonts & Tailwind -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        /* Immediate Critical Dark Mode Background */
        html, body {
            background-color: #020617 !important;
            color: #f8fafc !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* Sleek Transparent Scrollbar */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(245, 158, 11, 0.3) transparent;
        }
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(245, 158, 11, 0.3);
            border-radius: 9999px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(245, 158, 11, 0.6);
        }
        ::-webkit-scrollbar-corner {
            background: transparent;
        }
    </style>

    @if(!empty($platformSettings['google_analytics_id']))
        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $platformSettings['google_analytics_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $platformSettings['google_analytics_id'] }}');
        </script>
    @endif

    @if(!empty($platformSettings['facebook_pixel_id']))
        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $platformSettings['facebook_pixel_id'] }}');
            fbq('track', 'PageView');
        </script>
    @endif

    @if(!empty($platformSettings['custom_header_scripts']))
        {!! $platformSettings['custom_header_scripts'] !!}
    @endif
</head>
<body class="flex flex-col min-h-screen antialiased bg-slate-950 text-slate-100 selection:bg-amber-500 selection:text-slate-950">

    <!-- Top Navigation Header -->
    <header class="sticky top-0 z-50 backdrop-blur-xl bg-slate-950/85 border-b border-slate-800/80 transition-all shadow-sm" x-data="{ mobileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 sm:h-24 flex items-center justify-between">
            
            <!-- Prominent Logo -->
            <a href="{{ route('home') }}" class="flex items-center gap-3.5 group focus:outline-none">
                @if(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="h-11 w-11 sm:h-12 sm:w-12 rounded-2xl object-contain bg-slate-900 p-1.5 border border-slate-800 shadow-lg group-hover:scale-105 transition-transform shrink-0">
                    <span class="text-2xl sm:text-3xl font-black tracking-tight text-white group-hover:text-amber-400 transition-colors">
                        {{ $platformSettings['app_name'] ?? 'Gym Console' }}
                    </span>
                @else
                    <div class="w-11 h-11 sm:w-12 sm:h-12 rounded-2xl bg-gradient-to-tr from-amber-500 via-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25 group-hover:scale-105 group-hover:shadow-amber-500/40 transition-all shrink-0">
                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-slate-950 font-black" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-2xl sm:text-3xl font-black tracking-tight text-white group-hover:text-amber-400 transition-colors">
                            GYM<span class="text-amber-400 group-hover:text-white transition-colors">CONSOLE</span>
                        </span>
                    </div>
                @endif
            </a>

            <!-- Larger, Refined Navigation Menu -->
            <nav class="hidden md:flex items-center gap-1.5 lg:gap-3 bg-slate-900/60 p-1.5 rounded-2xl border border-slate-800/80">
                <a href="{{ route('home') }}" class="px-4 py-2 rounded-xl text-[15px] lg:text-[16px] font-semibold transition-all duration-200 {{ request()->routeIs('home') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Home
                </a>
                <a href="{{ route('features') }}" class="px-4 py-2 rounded-xl text-[15px] lg:text-[16px] font-semibold transition-all duration-200 {{ request()->routeIs('features') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Features
                </a>
                <a href="{{ route('pricing') }}" class="px-4 py-2 rounded-xl text-[15px] lg:text-[16px] font-semibold transition-all duration-200 {{ request()->routeIs('pricing') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Pricing
                </a>
                <a href="{{ route('about') }}" class="px-4 py-2 rounded-xl text-[15px] lg:text-[16px] font-semibold transition-all duration-200 {{ request()->routeIs('about') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    About
                </a>
                <a href="{{ route('contact') }}" class="px-4 py-2 rounded-xl text-[15px] lg:text-[16px] font-semibold transition-all duration-200 {{ request()->routeIs('contact') ? 'bg-amber-500/15 text-amber-400 border border-amber-500/30' : 'text-slate-300 hover:text-white hover:bg-slate-800/60' }}">
                    Contact
                </a>
            </nav>

            <!-- Desktop Action Buttons -->
            <div class="flex items-center gap-3">
                <div class="hidden sm:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 hover:brightness-110 shadow-lg shadow-amber-500/20 transition-all">
                                Super Admin
                            </a>
                        @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                            <a href="{{ route('app.dashboard') }}" class="text-sm font-bold px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 hover:brightness-110 shadow-lg shadow-amber-500/20 transition-all">
                                Gym Dashboard
                            </a>
                        @else
                            <a href="{{ route('auth.checkout') }}" class="text-xs font-bold px-4 py-2 rounded-xl bg-amber-500 text-slate-950 hover:bg-amber-400 transition-colors shadow-md shadow-amber-500/10">
                                💳 Pay to Activate
                            </a>
                            <form method="POST" action="{{ route('logout') }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-slate-400 hover:text-white transition-colors cursor-pointer px-2 py-1">
                                    Sign Out
                                </button>
                            </form>
                        @endif
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-slate-300 hover:text-white transition-colors px-3 py-2">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}" class="text-sm font-bold px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-500 text-slate-950 hover:brightness-110 shadow-lg shadow-amber-500/25 transition-all">
                            Get Started Free
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white focus:outline-none cursor-pointer" aria-label="Toggle navigation menu">
                    <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>

        <!-- Mobile Navigation Drawer -->
        <div x-show="mobileMenuOpen" x-cloak class="md:hidden border-t border-slate-800/80 bg-slate-950/98 backdrop-blur-2xl px-5 pt-4 pb-6 space-y-3">
            <nav class="flex flex-col space-y-1.5 text-base font-medium text-slate-300">
                <a href="{{ route('home') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('home') ? 'bg-amber-500/15 text-amber-400 font-bold' : 'hover:bg-slate-900 hover:text-amber-400' }} transition-colors">Home</a>
                <a href="{{ route('features') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('features') ? 'bg-amber-500/15 text-amber-400 font-bold' : 'hover:bg-slate-900 hover:text-amber-400' }} transition-colors">Features</a>
                <a href="{{ route('pricing') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('pricing') ? 'bg-amber-500/15 text-amber-400 font-bold' : 'hover:bg-slate-900 hover:text-amber-400' }} transition-colors">Pricing</a>
                <a href="{{ route('about') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('about') ? 'bg-amber-500/15 text-amber-400 font-bold' : 'hover:bg-slate-900 hover:text-amber-400' }} transition-colors">About</a>
                <a href="{{ route('contact') }}" class="px-4 py-2.5 rounded-xl {{ request()->routeIs('contact') ? 'bg-amber-500/15 text-amber-400 font-bold' : 'hover:bg-slate-900 hover:text-amber-400' }} transition-colors">Contact</a>
            </nav>

            <div class="pt-4 border-t border-slate-800/80 flex flex-col gap-2.5">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">Super Admin</a>
                    @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                        <a href="{{ route('app.dashboard') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">Gym Dashboard</a>
                    @else
                        <a href="{{ route('auth.checkout') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">💳 Pay to Activate</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-center font-bold px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-sm shadow-md shadow-amber-500/20">Get Started Free</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Professional Footer -->
    <footer class="bg-slate-950 border-t border-slate-800/80 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                @if(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="w-9 h-9 rounded-xl object-contain bg-slate-900 p-1 border border-slate-800">
                @else
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-500 to-orange-500 flex items-center justify-center text-slate-950 font-black shadow-md shadow-amber-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                @endif
                <span class="font-bold text-slate-200 text-lg">{{ $platformSettings['app_name'] ?? 'Gym Console' }}</span>
            </div>
            <p class="text-sm text-slate-500 text-center md:text-right">
                {{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'Gym Console') . '. Complete Gym Management Platform.') }}
            </p>
        </div>
    </footer>

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
</body>
</html>
