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
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html, body {
            background-color: #020617 !important;
            color: #f8fafc !important;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        [x-cloak] { display: none !important; }
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(245,158,11,0.3) transparent;
        }
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(245,158,11,0.3); border-radius: 9999px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(245,158,11,0.6); }

        /* Gradient text utility */
        .text-gradient {
            background: linear-gradient(135deg, #f59e0b, #fb923c, #fbbf24);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Subtle grid background */
        .bg-grid {
            background-image: linear-gradient(rgba(148,163,184,0.04) 1px, transparent 1px),
                              linear-gradient(90deg, rgba(148,163,184,0.04) 1px, transparent 1px);
            background-size: 64px 64px;
        }

        /* Smooth scroll */
        html { scroll-behavior: smooth; }

        /* Card hover lift */
        .card-hover {
            transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
        }
        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
        }

        /* Nav scroll shadow */
        .nav-scrolled {
            box-shadow: 0 4px 24px rgba(0,0,0,0.4);
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        .float-anim { animation: float 4s ease-in-out infinite; }

        @keyframes glow-pulse {
            0%, 100% { opacity: 0.4; }
            50% { opacity: 0.8; }
        }
        .glow-pulse { animation: glow-pulse 3s ease-in-out infinite; }
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
<body class="flex flex-col min-h-screen antialiased bg-slate-950 selection:bg-amber-500/30 selection:text-amber-300">

    <!-- ═══════════════════════════════════════════════════
         NAVIGATION HEADER
    ═══════════════════════════════════════════════════ -->
    <header
        class="sticky top-0 z-50 backdrop-blur-2xl bg-slate-950/90 border-b border-slate-800/60 transition-all duration-300"
        x-data="{ mobileMenuOpen: false }"
        x-init="window.addEventListener('scroll', () => {
            $el.classList.toggle('nav-scrolled', window.scrollY > 20);
        })"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20 lg:h-24">

                <!-- ── LOGO ── -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 lg:gap-4 group shrink-0 focus:outline-none">
                    @if(!empty($platformSettings['logo_url']))
                        <div class="relative">
                            <div class="absolute inset-0 rounded-2xl bg-amber-500/20 blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            <img src="{{ $platformSettings['logo_url'] }}"
                                 alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}"
                                 class="relative h-12 w-12 lg:h-14 lg:w-14 rounded-2xl object-contain border border-slate-700/60 shadow-lg shadow-black/30 group-hover:scale-105 group-hover:border-amber-500/50 transition-all duration-300 shrink-0 bg-slate-900/60 p-1">
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl lg:text-3xl font-black tracking-tight text-white group-hover:text-amber-400 transition-colors duration-200 leading-none">
                                {{ $platformSettings['app_name'] ?? 'Gym Console' }}
                            </span>
                            <span class="text-[10px] lg:text-xs font-semibold text-slate-500 tracking-wider uppercase mt-0.5">Gym Management Platform</span>
                        </div>
                    @else
                        <div class="relative">
                            <div class="absolute inset-0 rounded-2xl bg-amber-500/30 blur-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 glow-pulse"></div>
                            <div class="relative w-12 h-12 lg:w-14 lg:h-14 rounded-2xl bg-gradient-to-br from-amber-400 via-amber-500 to-orange-600 flex items-center justify-center shadow-xl shadow-amber-500/30 group-hover:scale-105 group-hover:shadow-amber-500/50 transition-all duration-300 shrink-0">
                                <svg class="w-7 h-7 lg:w-8 lg:h-8 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="flex flex-col">
                            <span class="text-2xl lg:text-3xl font-black tracking-tight leading-none">
                                <span class="text-white group-hover:text-amber-400 transition-colors duration-200">GYM</span><span class="text-amber-400 group-hover:text-white transition-colors duration-200">CONSOLE</span>
                            </span>
                            <span class="text-[10px] lg:text-xs font-semibold text-slate-500 tracking-wider uppercase mt-0.5">Gym Management Platform</span>
                        </div>
                    @endif
                </a>

                <!-- ── DESKTOP NAV ── -->
                <nav class="hidden lg:flex items-center">
                    <div class="flex items-center gap-1 bg-slate-900/70 border border-slate-800/80 rounded-2xl p-1.5 backdrop-blur-sm">
                        @php
                            $navLinks = [
                                ['route' => 'home',     'label' => 'Home'],
                                ['route' => 'features', 'label' => 'Features'],
                                ['route' => 'pricing',  'label' => 'Pricing'],
                                ['route' => 'about',    'label' => 'About'],
                                ['route' => 'contact',  'label' => 'Contact'],
                            ];
                        @endphp
                        @foreach($navLinks as $link)
                            <a href="{{ route($link['route']) }}"
                               class="px-4 xl:px-5 py-2.5 rounded-xl text-[15px] xl:text-base font-semibold transition-all duration-200 {{ request()->routeIs($link['route']) ? 'bg-amber-500/15 text-amber-400 shadow-sm' : 'text-slate-300 hover:text-white hover:bg-slate-800/70' }}">
                                {{ $link['label'] }}
                            </a>
                        @endforeach
                    </div>
                </nav>

                <!-- ── DESKTOP ACTION BUTTONS ── -->
                <div class="hidden lg:flex items-center gap-3">
                    @auth
                        @if(auth()->user()->isSuperAdmin())
                            <a href="{{ route('admin.dashboard') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-sm font-bold hover:brightness-110 shadow-lg shadow-amber-500/20 transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                Super Admin
                            </a>
                        @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                            <a href="{{ route('app.dashboard') }}"
                               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-sm font-bold hover:brightness-110 shadow-lg shadow-amber-500/20 transition-all">
                                Gym Dashboard
                            </a>
                        @else
                            <a href="{{ route('auth.checkout') }}"
                               class="px-4 py-2.5 rounded-xl bg-amber-500 text-slate-950 text-sm font-bold hover:bg-amber-400 transition-colors">
                                💳 Activate
                            </a>
                        @endif
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-300 hover:text-white hover:bg-slate-900 transition-all border border-transparent hover:border-slate-800">
                            Sign In
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-500 text-slate-950 text-sm font-black hover:brightness-110 shadow-xl shadow-amber-500/25 hover:scale-[1.02] active:scale-[0.98] transition-all">
                            Get Started Free
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- ── TABLET/MOBILE: Sign In + Hamburger ── -->
                <div class="flex lg:hidden items-center gap-2">
                    @guest
                        <a href="{{ route('login') }}" class="hidden sm:block px-3 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors">Sign In</a>
                    @endguest
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 hover:text-white focus:outline-none cursor-pointer transition-colors"
                            aria-label="Toggle menu">
                        <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── MOBILE MENU DRAWER ── -->
        <div x-show="mobileMenuOpen" x-cloak
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="lg:hidden border-t border-slate-800/80 bg-slate-950/98 backdrop-blur-2xl">
            <nav class="px-4 py-4 flex flex-col gap-1">
                @foreach($navLinks ?? [['route'=>'home','label'=>'Home'],['route'=>'features','label'=>'Features'],['route'=>'pricing','label'=>'Pricing'],['route'=>'about','label'=>'About'],['route'=>'contact','label'=>'Contact']] as $link)
                    <a href="{{ route($link['route']) }}"
                       class="px-4 py-3 rounded-xl text-base font-semibold transition-colors {{ request()->routeIs($link['route']) ? 'bg-amber-500/15 text-amber-400' : 'text-slate-300 hover:bg-slate-900 hover:text-amber-400' }}">
                        {{ $link['label'] }}
                    </a>
                @endforeach
            </nav>
            <div class="px-4 pb-5 pt-2 border-t border-slate-800/60 flex flex-col gap-2.5">
                @auth
                    @if(auth()->user()->isSuperAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">Super Admin Panel</a>
                    @elseif(auth()->user()->tenant && auth()->user()->tenant->isSubscriptionActive())
                        <a href="{{ route('app.dashboard') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">Gym Dashboard</a>
                    @else
                        <a href="{{ route('auth.checkout') }}" class="text-center font-bold px-4 py-3 rounded-xl bg-amber-500 text-slate-950 text-sm">💳 Pay to Activate</a>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="text-center font-semibold px-4 py-2.5 rounded-xl bg-slate-900 border border-slate-800 text-white text-sm">Sign In</a>
                    <a href="{{ route('register') }}" class="text-center font-black px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-sm shadow-lg shadow-amber-500/20">Get Started Free →</a>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- ═══════════════════════════════════════════════════
         PROFESSIONAL FOOTER
    ═══════════════════════════════════════════════════ -->
    <footer class="bg-slate-950 border-t border-slate-800/60">
        <!-- Footer Top -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10">

                <!-- Brand Column -->
                <div class="lg:col-span-2">
                    <a href="{{ route('home') }}" class="flex items-center gap-3 mb-4">
                        @if(!empty($platformSettings['logo_url']))
                            <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="w-10 h-10 rounded-xl object-contain bg-slate-900 p-1 border border-slate-800">
                        @else
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-md shadow-amber-500/20">
                                <svg class="w-6 h-6 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        @endif
                        <span class="text-xl font-black text-white">{{ $platformSettings['app_name'] ?? 'Gym Console' }}</span>
                    </a>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-sm">
                        The complete gym management platform for modern fitness businesses. Automate memberships, control biometric access, and grow your gym — all from one dashboard.
                    </p>
                    <div class="flex items-center gap-3 mt-5">
                        <a href="https://wa.me/919876543210" target="_blank" class="w-9 h-9 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 hover:bg-emerald-500/20 transition-colors">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Platform Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Platform</h4>
                    <ul class="space-y-2.5">
                        @foreach([['route'=>'features','label'=>'Features'],['route'=>'pricing','label'=>'Pricing'],['route'=>'about','label'=>'About Us'],['route'=>'contact','label'=>'Contact']] as $item)
                            <li><a href="{{ route($item['route']) }}" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">{{ $item['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>

                <!-- Account Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Account</h4>
                    <ul class="space-y-2.5">
                        <li><a href="{{ route('login') }}" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">Create Account</a></li>
                        <li><a href="{{ route('register', ['plan'=>'free_forever']) }}" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">Free Plan</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-sm text-slate-400 hover:text-amber-400 transition-colors">View Pricing</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-slate-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3">
                <p class="text-xs text-slate-500">
                    {{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'Gym Console') . '. All rights reserved.') }}
                </p>
                <div class="flex items-center gap-4 text-xs text-slate-600">
                    <span>Built with ❤️ for Fitness</span>
                </div>
            </div>
        </div>
    </footer>

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
</body>
</html>
