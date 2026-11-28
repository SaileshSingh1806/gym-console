@props([
    'title' => null,
    'metaDescription' => null,
    'metaKeywords' => null,
    'ogImage' => null,
    'canonicalUrl' => null,
    'noindex' => false,
    'hideNav' => false,
    'hideFooter' => false,
])

@php
    $marketingPage = request()->routeIs('home', 'features', 'pricing', 'about', 'contact', 'terms', 'privacy', 'refund-policy', 'shipping-policy');
    $siteName = $platformSettings['app_name'] ?? 'GymDeskHub';
    
    // Page Title
    $resolvedTitle = $title 
        ?? ($platformSettings['meta_title'] ?? null) 
        ?? ($siteName . ' — #1 Gym Management Software & Biometric Access Control');

    // Meta Description (optimally 150-160 characters for maximum CTR and zero truncation in Google SERPs)
    $resolvedDescription = $metaDescription 
        ?? ($platformSettings['meta_description'] ?? null) 
        ?? "GymDeskHub is India's leading gym management software. Automate member billing, WhatsApp fee reminders, biometric integration, POS & multi-branch gym operations.";

    // Meta Keywords
    $resolvedKeywords = $metaKeywords 
        ?? ($platformSettings['meta_keywords'] ?? null) 
        ?? "gym management software, best gym software india, gym membership software, biometric gym access control, biometric integration, gym billing pos, whatsapp gym reminders, fitness club management system, gym erp software, cloud gym software, turnstile access system gym";

    // OG Image
    $resolvedOgImage = $ogImage 
        ?? ($platformSettings['og_image_url'] ?? null) 
        ?? asset('images/gymdeskhub-official-dark.svg');

    // Canonical URL
    $resolvedCanonical = $canonicalUrl ?? url()->current();
    $faviconUrl = $platformSettings['favicon_url'] ?? asset('images/gymdeskhub-official-icon.svg');
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark h-full bg-[#090d16] text-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $resolvedTitle }}</title>

    {{-- Core Search Engine Directives --}}
    <meta name="description" content="{{ $resolvedDescription }}">
    <meta name="keywords" content="{{ $resolvedKeywords }}">
    <meta name="author" content="{{ $siteName }}">
    <meta name="publisher" content="{{ $siteName }}">

    {{-- Googlebot & Crawler Controls --}}
    @if($noindex)
        <meta name="robots" content="noindex, nofollow">
        <meta name="googlebot" content="noindex, nofollow">
    @else
        <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
        <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
        <meta name="bingbot" content="index, follow, max-snippet:-1, max-image-preview:large">
    @endif

    {{-- Canonical Authority Link --}}
    <link rel="canonical" href="{{ $resolvedCanonical }}">

    {{-- Open Graph / Facebook Meta --}}
    <meta property="og:locale" content="en_US">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $resolvedCanonical }}">
    <meta property="og:site_name" content="{{ $siteName }}">
    <meta property="og:title" content="{{ $resolvedTitle }}">
    <meta property="og:description" content="{{ $resolvedDescription }}">
    <meta property="og:image" content="{{ $resolvedOgImage }}">
    <meta property="og:image:secure_url" content="{{ $resolvedOgImage }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="{{ $siteName }} - Gym Management System">

    {{-- Twitter / X Cards Meta --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="{{ $resolvedCanonical }}">
    <meta name="twitter:title" content="{{ $resolvedTitle }}">
    <meta name="twitter:description" content="{{ $resolvedDescription }}">
    <meta name="twitter:image" content="{{ $resolvedOgImage }}">

    {{-- Geographic & Regional Targeting --}}
    <meta name="geo.region" content="IN">
    <meta name="geo.placename" content="India">
    <meta name="rating" content="General">
    <meta name="revisit-after" content="7 days">

    {{-- Favicon & App Icons --}}
    <link rel="icon" href="{{ !empty($platformSettings['favicon_url']) ? $platformSettings['favicon_url'] : asset('images/gymdeskhub-official-icon.svg') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}?v=4">
    <link rel="shortcut icon" href="{{ !empty($platformSettings['favicon_url']) ? $platformSettings['favicon_url'] : asset('images/gymdeskhub-official-icon.svg') }}">

    {{-- Schema.org JSON-LD Structured Data for Google Rich Snippets --}}
    @php
        $schemaGraph = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => url('/') . '#organization',
                    'name' => $siteName,
                    'url' => url('/'),
                    'logo' => [
                        '@type' => 'ImageObject',
                        'url' => asset('images/gymdeskhub-official-dark.svg'),
                    ],
                    'contactPoint' => [
                        '@type' => 'ContactPoint',
                        'telephone' => $platformSettings['support_phone'] ?? '+91-9974995179',
                        'contactType' => 'customer service',
                        'areaServed' => 'IN',
                        'availableLanguage' => ['English', 'Hindi'],
                    ],
                ],
                [
                    '@type' => 'WebSite',
                    '@id' => url('/') . '#website',
                    'url' => url('/'),
                    'name' => $siteName,
                    'publisher' => [
                        '@id' => url('/') . '#organization',
                    ],
                ],
                [
                    '@type' => 'SoftwareApplication',
                    '@id' => url('/') . '#software',
                    'name' => $siteName,
                    'applicationCategory' => 'BusinessApplication',
                    'operatingSystem' => 'Web, Android, iOS',
                    'description' => $resolvedDescription,
                ],
            ],
        ];
    @endphp
    <script type="application/ld+json">
    {!! json_encode($schemaGraph, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
    </script>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    @if($marketingPage)
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @endif
    @unless($marketingPage)
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,400;1,600&display=swap" rel="stylesheet">
    @endunless
    @if($marketingPage)
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { theme: { extend: { colors: { slate: { 900: "#151618" } } } } };</script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        html, body {
            background-color: #090d16 !important;
            color: #f1f5f9 !important;
            font-family: 'Poppins', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
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
        /* Public marketing pages only; authentication and public check-in retain their styles. */
        .marketing-site { background:#0D0E10 !important; color:#ECEDEF !important; font-family:Inter,system-ui,sans-serif; }
        .marketing-site header { background:#0D0E10 !important; border-color:#26282D !important; backdrop-filter:none !important; }
        .marketing-site footer { background:#141518 !important; border-color:#26282D !important; }
        .marketing-site main > section { scroll-margin-top:80px; }
        .marketing-site { --brand:#F5A524; }
        .marketing-site [class~="bg-amber-500"] { background-color:var(--brand); }
        .marketing-site [class~="text-amber-400"] { color:var(--brand); }
        .marketing-site [class~="border-amber-500"] { border-color:var(--brand); }
        .marketing-site [class~="bg-slate-900"] { background-color:#141518; }
        .marketing-site [class~="bg-slate-800"] { background-color:#1B1D21; }
        .marketing-site [class~="border-slate-800"] { border-color:#26282D; }
        .marketing-site [class~="border-slate-700"] { border-color:#373A41; }
        .marketing-site [class~="text-slate-300"] { color:#C7C9CD; }
        .marketing-site [class~="text-slate-400"] { color:#A0A4AB; }
        .marketing-site [class~="text-slate-500"] { color:#92969E; }
        .marketing-site header nav a[aria-current="page"] { color:#F5A524; background:transparent; }
        .marketing-site main a[class~="bg-amber-500"]:hover, .marketing-site header a[class~="bg-amber-500"]:hover { background-color:#E09414; }

        .marketing-site main h1, .marketing-site main h2, .marketing-site main h3 { letter-spacing:-.035em; font-weight:600; }
        .marketing-site main h1 { font-size:clamp(2.25rem,5vw,4rem); line-height:1.08; }
        .marketing-site main h2 { font-size:clamp(1.6rem,3vw,2.5rem); line-height:1.18; }
        .marketing-site main h3 { line-height:1.35; }
        .marketing-site a:focus-visible, .marketing-site button:focus-visible, .marketing-site input:focus-visible, .marketing-site select:focus-visible, .marketing-site textarea:focus-visible { outline:2px solid #F5A524; outline-offset:3px; }
        .marketing-site main input, .marketing-site main select, .marketing-site main textarea { font-family:inherit; }
        .marketing-site .marketing-hero { padding-block:clamp(3rem,6vw,6rem); }
        .marketing-site .marketing-hero p { max-width:60ch; }
        .marketing-site .marketing-section { padding-block:clamp(3rem,6vw,5rem); }
        .marketing-site .marketing-legal { max-width:760px; margin-inline:auto; font-size:15px; line-height:1.85; }
        .marketing-site .marketing-legal h2 { font-size:1.125rem; letter-spacing:-.015em; }
        .marketing-site .marketing-legal ul { padding-left:1.25rem; }
        @media(prefers-reduced-motion:reduce) { .marketing-site *, .marketing-site *::before, .marketing-site *::after { animation-duration:.01ms !important; transition-duration:.01ms !important; scroll-behavior:auto !important; } }
        .marketing-site main > section > div:first-child { max-width:1200px; padding-inline:20px; }
        .marketing-site .marketing-hero, .marketing-site .marketing-section { padding-block:64px; }
        .marketing-site main h1 { font-size:44px; line-height:1.05; }
        .marketing-site main h2 { font-size:32px; line-height:1.15; }
        .marketing-site main h3 { font-size:20px; }
        .marketing-site main p { line-height:1.6; }
        .marketing-site main [class~="font-bold"], .marketing-site main [class~="font-black"] { font-weight:600; }
        .marketing-site main [class~="uppercase"] { text-transform:none; }
        .marketing-site main a[class~="bg-amber-500"], .marketing-site main button[class~="bg-amber-500"] { min-height:48px; border-radius:8px; }
        .marketing-site main input:not([type="checkbox"]):not([type="radio"]), .marketing-site main select { min-height:48px; border-radius:8px; }
        .marketing-site .marketing-light { background:#F6F6F4 !important; color:#17181B; }
        .marketing-site .marketing-light :is(h1,h2,h3,h4,[class~="text-white"],[class~="text-slate-200"]) { color:#17181B; }
        .marketing-site .marketing-light :is([class~="text-slate-300"],[class~="text-slate-400"],[class~="text-slate-500"]) { color:#5C6068; }
        .marketing-site .marketing-light :is([class~="bg-[#1B1D21]"],[class~="bg-[#141518]"],[class~="bg-[#0D0E10]"],[class~="bg-slate-900"],[class~="bg-slate-800"]) { background:#FFFFFF; }
        .marketing-site .marketing-light [class~="border-slate-800"] { border-color:#E4E4DF; }
        .marketing-site .marketing-light [class~="text-amber-400"] { color:#875000; }
        .marketing-site .marketing-light a[class~="bg-amber-500"] { color:#16130B; }
        .marketing-site .marketing-light [class~="border-slate-700"] { border-color:#BFC1C5; }
        .marketing-site .marketing-light .divide-slate-800 > * + * { border-color:#E4E4DF; }
        .marketing-site main .marketing-legal { width:100%; }
        .marketing-site .marketing-hero:has(.marketing-legal), .marketing-site:has(.marketing-legal) .marketing-hero { padding-block:48px; }
        .marketing-site main a:focus-visible, .marketing-site main summary:focus-visible { outline:2px solid #F5A524; outline-offset:3px; }
        .marketing-site main summary { list-style:none; position:relative; padding-right:28px; }
        .marketing-site main summary::-webkit-details-marker { display:none; }
        .marketing-site main summary::after { content:'+'; position:absolute; right:0; top:0; }
        .marketing-site main details[open] summary::after { content:'−'; }
        .marketing-site main .marketing-section, .marketing-site main .marketing-hero { scroll-margin-top:80px; }
        .marketing-site .marketing-light :is([class~="text-emerald-400"],[class~="text-rose-400"]) { color:#5C6068; }
        .marketing-site header > div:first-child > div { height:72px; transition:height .2s cubic-bezier(.2,.7,.2,1); }
        .marketing-site header.is-scrolled > div:first-child > div { height:64px; }
        .marketing-site .marketing-legal { max-width:720px; }
        .marketing-site .marketing-legal h2 { font-size:20px; }
        .marketing-site [data-parallax] { transform:translate3d(0,0,0); }
        .marketing-site .marketing-reveal-ready { opacity:0; transform:translateY(16px); transition:opacity .6s cubic-bezier(.2,.7,.2,1),transform .6s cubic-bezier(.2,.7,.2,1); }
        .marketing-site .marketing-reveal-ready.is-in { opacity:1; transform:none; }
        @media(min-width:768px) {
            .marketing-site main > section > div:first-child { padding-inline:32px; }
            .marketing-site .marketing-hero, .marketing-site .marketing-section { padding-block:96px; }
            .marketing-site main h1 { font-size:clamp(44px,5.4vw,72px); }
            .marketing-site main h2 { font-size:48px; }
            .marketing-site main h3 { font-size:24px; }
            .marketing-site .marketing-legal h2 { font-size:20px; }
        }
        @media(prefers-reduced-motion:reduce) {
            .marketing-site [data-parallax], .marketing-site .marketing-reveal-ready { opacity:1!important; transform:none!important; transition:none!important; }
        }
    </style>

    @if($marketingPage)
        <link rel="stylesheet" href="{{ asset('assets/css/site.css') }}?v=1">
        <script defer src="{{ asset('assets/js/site.js') }}?v=1"></script>
    @endif
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
<body class="{{ $marketingPage ? 'marketing-site' : '' }} flex flex-col min-h-screen antialiased bg-[#090d16] selection:bg-amber-500/25 selection:text-amber-200 overflow-x-hidden">

    <a href="#main-content" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[100] focus:bg-white focus:text-black focus:p-3">Skip to content</a>
    @if(!($hideNav ?? false))
    <!-- ═══════════════════════════════════════════════════
         CLEAN SAAS NAVIGATION HEADER
    ═══════════════════════════════════════════════════ -->
    <header
        class="sticky top-0 z-50 bg-[#090d16]/90 backdrop-blur-md border-b border-slate-800/80 transition-all"
        x-data="{ mobileMenuOpen: false }" @keydown.escape.window="mobileMenuOpen = false"
    >
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">

                <!-- ── LOGO ── -->
                <a href="{{ route('home') }}" class="flex items-center shrink-0 focus:outline-none group">
                    <img src="{{ asset('images/gymdeskhub-official-dark.svg') }}"
                         alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                         class="h-9 sm:h-10 w-auto object-contain transition-opacity group-hover:opacity-90">
                </a>

                <!-- ── DESKTOP NAV ── -->
                <nav class="hidden lg:flex items-center gap-1">
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
                        <a href="{{ route($link['route']) }}" @if(request()->routeIs($link['route'])) aria-current="page" @endif
                           class="px-3.5 py-1.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs($link['route']) ? 'text-white bg-slate-800/80 font-semibold' : 'text-slate-300 hover:text-white hover:bg-slate-800/40' }}">
                            {{ $link['label'] }}
                        </a>
                    @endforeach
                </nav>

                <!-- ── DESKTOP ACTIONS ── -->
                <div class="hidden lg:flex items-center gap-5">
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
                           class="text-sm font-medium text-slate-300 hover:text-white transition-colors">
                            Login
                        </a>
                        <a href="{{ route('register') }}"
                           class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm font-semibold shadow-sm transition-all active:scale-[0.98]">
                            <span>{{ $marketingPage ? 'Start free' : 'Get Started' }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    @endauth
                </div>

                <!-- ── MOBILE MENU BUTTON ── -->
                <div class="flex lg:hidden items-center gap-2">
                    <button @click="mobileMenuOpen = !mobileMenuOpen"
                            class="p-2 rounded-lg bg-slate-900 border border-slate-800 text-slate-300 hover:text-white focus:outline-none cursor-pointer"
                            aria-label="Toggle menu" :aria-expanded="mobileMenuOpen.toString()">
                        <svg x-show="!mobileMenuOpen" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                        <svg x-show="mobileMenuOpen" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- ── MOBILE MENU DRAWER ── -->
        <div x-show="mobileMenuOpen" x-cloak
             @click.outside="mobileMenuOpen = false"
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="lg:hidden border-t border-slate-800/80 bg-[#090d16] px-4 py-5 space-y-4 shadow-2xl">
            <nav class="flex flex-col gap-1">
                @foreach($navLinks ?? [['route'=>'home','label'=>'Home'],['route'=>'features','label'=>'Features'],['route'=>'pricing','label'=>'Pricing'],['route'=>'about','label'=>'About'],['route'=>'contact','label'=>'Contact']] as $link)
                    <a href="{{ route($link['route']) }}" @if(request()->routeIs($link['route'])) aria-current="page" @endif
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
                    <a href="{{ route('login') }}" class="text-center font-medium px-4 py-2.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-sm hover:bg-slate-800 transition-colors">Login</a>
                    <a href="{{ route('register') }}" class="text-center font-semibold px-4 py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-sm shadow-sm transition-colors">Get Started Free &rarr;</a>
                @endauth
            </div>
        </div>
    </header>
    @endif

    <!-- Main Content -->
    <main id="main-content" class="flex-grow">
        {{ $slot }}
    </main>

    @if(!($hideFooter ?? false))
    <!-- ═══════════════════════════════════════════════════
         PROFESSIONAL B2B FOOTER
    ═══════════════════════════════════════════════════ -->
    @if($marketingPage)
<footer class="border-t border-slate-800 bg-[#141518]">
<div class="max-w-[1200px] mx-auto px-5 lg:px-8 py-12">
<a href="{{ route('home') }}" class="inline-block mb-8"><img src="{{ asset('images/gymdeskhub-official-dark.svg') }}" alt="GymDeskHub" class="h-9 w-auto"></a>
<div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-sm">
@foreach(['Product'=>['features'=>'Features','pricing'=>'Pricing','register'=>'Start free','login'=>'Log in'],'Company'=>['about'=>'About','contact'=>'Contact'],'Legal'=>['terms'=>'Terms & conditions','privacy'=>'Privacy policy','refund-policy'=>'Refund & cancellation','shipping-policy'=>'Shipping & delivery']] as $heading=>$links)
<div><h4 class="font-semibold text-white mb-4">{{ $heading }}</h4><ul class="space-y-3">@foreach($links as $route=>$label)<li><a href="{{ route($route) }}" class="text-slate-400 hover:text-white hover:underline">{{ $label }}</a></li>@endforeach</ul></div>
@endforeach
<div><h4 class="font-semibold text-white mb-4">Contact</h4><ul class="space-y-3 text-slate-400"><li><a href="{{ route('contact') }}" class="hover:underline">WhatsApp &amp; phone</a></li><li><a href="mailto:support@gymdeskhub.in" class="break-all hover:underline">support@gymdeskhub.in</a></li><li><a href="mailto:grievance@gymdeskhub.in" class="hover:underline">Grievance contact</a></li><li>Ahmedabad, India</li></ul></div>
</div>
<div class="mt-10 pt-6 border-t border-slate-800 text-xs text-slate-400 space-y-2"><p>{{ $platformSettings['footer_copyright'] ?? ('© '.date('Y').' GymDeskHub. All rights reserved.') }}</p><p>GymDeskHub is an individual enterprise owned and operated by Sailesh Singh.</p></div>
</div></footer>
@else
<footer class="bg-[#070a10] border-t border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-8 lg:gap-8">

                <!-- Brand Column (2 cols) -->
                <div class="col-span-2 space-y-4">
                    <a href="{{ route('home') }}" class="inline-flex items-center group">
                        <img src="{{ asset('images/gymdeskhub-official-dark.svg') }}"
                             alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                             class="h-9 w-auto object-contain transition-opacity group-hover:opacity-90">
                    </a>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed max-w-sm">
                        GymDeskHub helps gym and fitness center owners manage members, collect fees on time, and track attendance seamlessly without manual registers.
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
                    <div class="pt-2 flex flex-wrap items-center gap-3">
                        <a href="https://wa.me/{{ $cleanGuestPhone }}?text={{ urlencode('Hi, I need assistance with GymDeskHub') }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 hover:bg-emerald-500/20 text-xs font-semibold transition-colors">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                            <span>WhatsApp Support</span>
                        </a>
                        <span class="text-[11px] text-slate-500">Ahmedabad, Gujarat, India</span>
                    </div>
                </div>

                <!-- Product Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Platform</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">Biometric Integration</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">WhatsApp Fee Recovery</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">Trainer Mobile App</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">Member Digital Pass</a></li>
                        <li><a href="{{ route('features') }}" class="text-slate-400 hover:text-white transition-colors">GST &amp; POS Billing</a></li>
                    </ul>
                </div>

                <!-- Company Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Company</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('about') }}" class="text-slate-400 hover:text-white transition-colors">About Us</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-slate-400 hover:text-white transition-colors">Plans & Pricing</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">Contact & Support</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">Hardware Help</a></li>
                    </ul>
                </div>

                <!-- Legal & Compliance Links (Payment Gateway KYC Mandatory) -->
                <div>
                    <h4 class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-3.5">Legal &amp; Policy</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('terms') }}" class="text-slate-400 hover:text-white transition-colors">Terms &amp; Conditions</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-slate-400 hover:text-white transition-colors">Privacy Policy</a></li>
                        <li><a href="{{ route('refund-policy') }}" class="text-slate-400 hover:text-white transition-colors">Refund &amp; Cancellation</a></li>
                        <li><a href="{{ route('shipping-policy') }}" class="text-slate-400 hover:text-white transition-colors">Shipping &amp; Delivery</a></li>
                        <li><a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">Grievance Redressal</a></li>
                    </ul>
                </div>

                <!-- Access Links -->
                <div>
                    <h4 class="text-xs font-bold text-slate-300 uppercase tracking-wider mb-3.5">Access</h4>
                    <ul class="space-y-2 text-xs sm:text-sm">
                        <li><a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors">Sign In</a></li>
                        <li><a href="{{ route('register') }}" class="text-slate-400 hover:text-white transition-colors">Register New Gym</a></li>
                        <li><a href="{{ route('register', ['plan'=>'free_forever']) }}" class="text-slate-400 hover:text-white transition-colors">Free Forever Tier</a></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Payment Security & Compliance Strip -->
        <div class="border-t border-slate-800/80 bg-[#06090f]/70 py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-4 text-[11px] text-slate-400">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-semibold text-slate-300">Accepted Payment Methods:</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-mono">UPI</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300">Credit / Debit Cards</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300">NetBanking</span>
                    <span class="px-2 py-0.5 rounded bg-slate-900 border border-slate-800 text-slate-300 font-mono">RuPay</span>
                </div>
                <div class="flex items-center gap-3 text-slate-400">
                    <span class="inline-flex items-center gap-1.5 text-emerald-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        <span>256-Bit SSL Encrypted Checkout</span>
                    </span>
                    <span class="text-slate-600">•</span>
                    <span>PCI-DSS Compliant Gateways</span>
                </div>
            </div>
        </div>

        <!-- Footer Bottom -->
        <div class="border-t border-slate-800/60">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
                <div>
                    <p>
                        {{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'GymDeskHub') . '. All rights reserved.') }}
                    </p>
                    <p class="mt-0.5 text-[11px] text-slate-600">
                        GymDeskHub is an individual enterprise owned and operated by <strong class="text-slate-400 font-normal">Sailesh Singh</strong>.
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span>Enterprise Security &amp; Tenant Isolation</span>
                </div>
            </div>
        </div>
    </footer>
@endif
    @endif

    @if(!empty($platformSettings['custom_footer_scripts']))
        {!! $platformSettings['custom_footer_scripts'] !!}
    @endif
@if($marketingPage)
<script>
(() => {
    const root = document.querySelector('.marketing-site');
    if (!root) return;
    const reduce = matchMedia('(prefers-reduced-motion: reduce)');
    const header = root.querySelector('header');
    const layers = [...root.querySelectorAll('[data-parallax]')];
    let ticking = false;
    const update = () => {
        header?.classList.toggle('is-scrolled', scrollY > 24);
        const mobile = innerWidth < 768 ? .4 : 1;
        for (const el of layers) {
            if (reduce.matches) { el.style.transform = ''; continue; }
            const r = el.parentElement.getBoundingClientRect();
            if (r.bottom < -200 || r.top > innerHeight + 200) continue;
            const travel = Math.max(-60, Math.min(60, -(r.top + r.height / 2 - innerHeight / 2) * Number(el.dataset.speed || .08) * mobile));
            el.style.transform = 'translate3d(0,' + travel.toFixed(1) + 'px,0)';
        }
        ticking = false;
    };
    const schedule = () => { if (!ticking) { ticking = true; requestAnimationFrame(update); } };
    addEventListener('scroll', schedule, {passive:true});
    addEventListener('resize', schedule, {passive:true});
    reduce.addEventListener?.('change', schedule);
    update();
    if (!reduce.matches && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) { entry.target.classList.add('is-in'); observer.unobserve(entry.target); }
            });
        }, {threshold:.2});
        root.querySelectorAll('[data-reveal]').forEach((el,i) => {
            el.style.transitionDelay = (i % 4) * 60 + 'ms';
            if (el.getBoundingClientRect().top > innerHeight) {
                el.classList.add('marketing-reveal-ready');
                observer.observe(el);
            }
        });
    }
})();
</script>
@endif
</body>
</html>
