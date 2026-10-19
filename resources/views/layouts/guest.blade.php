<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-[#090d16] text-slate-100">
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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html, body {
            background-color: #090d16 !important;
            color: #f1f5f9 !important;
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        [x-cloak] { display: none !important; }
        * {
            scrollbar-width: thin;
            scrollbar-color: #334155 transparent;
        }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #334155; border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: #475569; }

        /* Subtle grid pattern */
        .bg-subtle-grid {
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Card hover lift */
        .card-surface {
            background-color: #0f1624;
            border: 1px solid rgba(255, 255, 255, 0.08);
            transition: border-color 0.2s ease, transform 0.2s ease, box-shadow 0.2s ease;
        }
        .card-surface:hover {
            border-color: rgba(255, 255, 255, 0.16);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px -8px rgba(0, 0, 0, 0.5);
        }

        /* Prevent browser autofill from turning inputs bright white/blue */
        input:-webkit-autofill,
        input:-webkit-autofill:hover, 
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-text-fill-color: #f8fafc !important;
            -webkit-box-shadow: 0 0 0 1000px #0b111e inset !important;
            box-shadow: 0 0 0 1000px #0b111e inset !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        /* Seamless Infinite Horizontal Marquee */
        @keyframes marquee {
            0% { transform: translateX(0%); }
            100% { transform: translateX(-50%); }
        }
        .animate-marquee {
            display: flex;
            width: max-content;
            animation: marquee 35s linear infinite;
        }
        .animate-marquee:hover {
            animation-play-state: paused;
        }
    </style>

    @if(!empty($platformSettings['google_analytics_id']))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ $platformSettings['google_analytics_id'] }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $platformSettings['google_analytics_id'] }}');
        </script>
    @endif

    @if(!empty($platformSettings['facebook_pixel_id']))
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
<body class="flex flex-col min-h-screen antialiased bg-[#090d16] selection:bg-amber-500/25 selection:text-amber-200">

    @if(!($hideNav ?? false))
    <!-- ═══════════════════════════════════════════════════
         CLEAN SAAS NAVIGATION HEADER
    ═══════════════════════════════════════════════════ -->
    <header
        class="sticky top-0 z-50 bg-[#090d16]/90 backdrop-blur-md border-b border-slate-800/80 transition-all"
        x-data="{ mobileMenuOpen: false }"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">

                <!-- ── LOGO ── -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 shrink-0 focus:outline-none">
                    @if(!empty($platformSettings['logo_url']))
                        <img src="{{ $platformSettings['logo_url'] }}"
                             alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}"
                             class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl object-contain border border-slate-800 bg-[#0f1624] p-1 shadow-sm">
                        <div class="flex flex-col">
                            <span class="text-lg sm:text-xl font-bold tracking-tight text-white leading-none">
                                {{ $platformSettings['app_name'] ?? 'Gym Console' }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider mt-0.5">Management Platform</span>
                        </div>
                    @else
                        <div class="w-9 h-9 sm:h-10 sm:w-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black shadow-sm shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-lg sm:text-xl font-extrabold tracking-tight leading-none text-white">
                                Gym<span class="text-amber-400">Console</span>
                            </span>
                            <span class="text-[10px] font-medium text-slate-400 uppercase tracking-wider mt-0.5">Operating System</span>
                        </div>
                    @endif
                </a>

                <!-- ── DESKTOP NAV ── -->
                <nav class="hidden md:flex items-center gap-1">
                    @php
                        $navLinks = [
                            ['route' => 'home',     'label' => 'Overview'],
                            ['route' => 'features', 'label' => 'Features'],
                            ['route' => 'pricing',  'label' => 'Pricing'],
                            ['route' => 'about',    'label' => 'About'],
                            ['route' => 'contact',  'label' => 'Contact'],
                        ];
                    @endphp
                    @foreach($navLinks as $link)
                        <a href="{{ route($link['route']) }}"
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($link['route']) ? 'text-white bg-slate-800/80 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <!-- ── DESKTOP ACTIONS ── -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Super Admin
                            </a>
                        @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                            <a href="{{ route('app.dashboard') }}"
                               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold shadow-sm transition-all">
                                Go to Console
                            </a>
                        @else
                            <a href="{{ route('auth.checkout') }}"
                               class="px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold transition-colors">
                                Activate Account
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2.5 rounded-xl text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800/60 transition-colors">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-bold shadow-sm transition-all">
                            <span>Get Started</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- ── MOBILE MENU BUTTON ── -->
                <div class="flex md:hidden items-center gap-2">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white focus:outline-none cursor-pointer"
                            aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── MOBILE MENU DRAWER ── -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden border-t border-slate-800/80 bg-[#090d16] px-4 py-4 space-y-3">
            <nav class="flex flex-col gap-1">
                @foreach($navLinks ?? [['route'=>'home','label'=>'Overview'],['route'=>'features','label'=>'Features'],['route'=>'pricing','label'=>'Pricing'],['route'=>'about','label'=>'About'],['route'=>'contact','label'=>'Contact']] as $link)
                    <a href="{{ route($link['route']) }}"
                       class="px-3 py-2 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($link['route']) ? 'bg-slate-800 text-white font-semibold' : 'text-slate-300 hover:bg-slate-900 hover:text-white' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="pt-3 border-t border-slate-800/80 flex flex-col gap-2">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-center font-bold px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 text-sm">Super Admin Panel</a>
                    @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                        <a href="{{ route('app.dashboard') }}" class="text-center font-bold px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 text-sm">Gym Dashboard</a>
                    @else
                        <a href="{{ route('auth.checkout') }}" class="text-center font-bold px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 text-sm">Activate Plan</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-center font-medium px-4 py-2 rounded-lg bg-slate-900 border border-slate-800 text-white text-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="text-center font-bold px-4 py-2.5 rounded-lg bg-amber-500 text-slate-950 text-sm shadow-sm">Get Started Free</a>
                @endauth
            </div>
        </div>
    </header>
    @endif

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    @if(!($hideFooter ?? false))
    <!-- ═══════════════════════════════════════════════════
         PROFESSIONAL B2B FOOTER
    ═══════════════════════════════════════════════════ -->
    <footer class="bg-[#070a10] border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10">

                <!-- Brand Column -->
                <div class="lg:col-span-2 space-y-4">
                    <a href="{{ route('home') }}" class="flex items-center gap-2.5">
                        @if(!empty($platformSettings['logo_url']))
                            <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="w-8 h-8 rounded-lg object-contain bg-[#0f1624] p-1 border border-slate-800">
                        @else
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-950 flex items-center justify-center font-bold">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        @endif
                        <span class="text-lg font-bold text-white">{{ $platformSettings['app_name'] ?? 'Gym Console' }}</span>
                    </a>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-sm">
                        The all-in-one operating system for modern gyms and fitness clubs. Universal biometric access, automated fee collections, WhatsApp messaging, and multi-branch management.
                    </p>
                    @php
                        $guestPhone = $platformSettings['support_phone'] ?? '+91 9974995179';
                        $cleanGuestPhone = preg_replace('/[^0-9]/', '', (string)$guestPhone);
                        if (empty($cleanGuestPhone)) {
                            $cleanGuestPhone = '919974995179';
                        } elseif (strlen($cleanGuestPhone) === 10) {
                            $cleanGuestPhone = '91' . $cleanGuestPhone;
                        }
                    @endphp
                    <div class="pt-2 flex items-center gap-3">
                        <a href="https://wa.me/{{ $cleanGuestPhone }}?text={{ urlencode('Hi, I need assistance with Gym Console') }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition-colors">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                            <span>WhatsApp Support</span>
                        </a>
                    </div>
                </div>

                <!-- Product Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Platform</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">Biometric Turnstiles</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">Multi-Branch Setup</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">GST & POS Billing</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">WhatsApp Automation</a></li>
                    </ul>
                </div>

                <!-- Resources Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Company</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('about') }}" class="text-slate-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-slate-400 hover:text-white transition-colors">Plans & Pricing</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">Hardware Consultation</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">Contact Sales</a></li>
                    </ul>
                </div>

                <!-- Account Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Access</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Member & Staff Login</a></li>
                        <li><a href="{{ route('register') }}" class="text-slate-400 hover:text-white transition-colors">Register New Gym</a></li>
                        <li><a href="{{ route('register', ['plan'=>'free_forever']) }}" class="text-slate-400 hover:text-white transition-colors">Free Forever Tier</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-slate-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <p>
                    {{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'Gym Console') . '. All rights reserved.') }}
                </p>
                <div class="flex items-center gap-4">
                    <span>Enterprise Security &amp; Tenant Isolation</span>
                </div>
            </div>
        </div>
    </footer>
    @endif

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
</body>
</html>
