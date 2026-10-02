<x-guest-layout title="{{ ($platformSettings['app_name'] ?? 'Gym Console') }} — Professional Gym Management Platform">

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="relative overflow-hidden bg-grid">
        {{-- Ambient glows --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[500px] bg-gradient-to-b from-amber-500/10 via-orange-500/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/3 -left-32 w-64 h-64 bg-blue-600/8 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute top-1/4 -right-32 w-64 h-64 bg-violet-600/8 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 sm:pt-28 sm:pb-32 lg:pt-36 lg:pb-40">
            <div class="text-center max-w-5xl mx-auto">

                {{-- Pill badge --}}
                <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-300 text-xs sm:text-sm font-bold mb-8 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse inline-block"></span>
                    ⚡ Biometric Access Control · Multi-Branch · Mobile Apps · GST Billing
                </div>

                {{-- Headline --}}
                <h1 class="text-5xl sm:text-6xl lg:text-7xl xl:text-8xl font-black tracking-tight text-white leading-[1.08] mb-6">
                    The Smartest Way<br>
                    to Run Your <span class="text-gradient">Gym Business</span>
                </h1>

                {{-- Subheadline --}}
                <p class="text-base sm:text-xl lg:text-2xl text-slate-400 leading-relaxed max-w-3xl mx-auto font-normal mb-10">
                    Automate memberships, control biometric entry gates, manage staff, track finances, and grow with a mobile app — all from one powerful platform.
                </p>

                {{-- CTA Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-500 text-slate-950 font-black text-base hover:brightness-110 shadow-2xl shadow-amber-500/30 hover:scale-[1.03] active:scale-[0.98] transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        Start Free — No Card Needed
                    </a>
                    <a href="#features"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-2xl bg-slate-900/80 border border-slate-700/80 text-slate-200 font-bold text-base hover:bg-slate-800 hover:border-slate-600 transition-all backdrop-blur-sm">
                        See All Features
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                </div>

                {{-- Trust Indicators --}}
                <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-8">
                    @foreach([
                        ['icon'=>'✅','text'=>'Free forever plan'],
                        ['icon'=>'🔒','text'=>'Data isolation per gym'],
                        ['icon'=>'⚡','text'=>'Live in under 2 minutes'],
                        ['icon'=>'📱','text'=>'Android & iOS apps'],
                    ] as $trust)
                        <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                            <span>{{ $trust['icon'] }}</span>
                            <span>{{ $trust['text'] }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         STATS BAR
    ══════════════════════════════════════════════════════════════ --}}
    <section class="border-y border-slate-800/60 bg-slate-900/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                @foreach([
                    ['number'=>'10,000+', 'label'=>'Members Managed',    'color'=>'text-amber-400'],
                    ['number'=>'500+',    'label'=>'Gym Branches',        'color'=>'text-orange-400'],
                    ['number'=>'99.9%',   'label'=>'Uptime Reliability',  'color'=>'text-emerald-400'],
                    ['number'=>'< 2 min', 'label'=>'Setup Time',          'color'=>'text-blue-400'],
                ] as $stat)
                    <div class="text-center">
                        <div class="text-3xl lg:text-4xl font-black {{ $stat['color'] }} mb-1">{{ $stat['number'] }}</div>
                        <div class="text-sm text-slate-400 font-medium">{{ $stat['label'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FEATURES SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section id="features" class="py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Section header --}}
            <div class="text-center max-w-3xl mx-auto mb-16 lg:mb-20">
                <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-3">Why Gym Console</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-4">
                    Everything Your Gym Needs, <span class="text-gradient">Nothing It Doesn't</span>
                </h2>
                <p class="text-slate-400 text-lg leading-relaxed">
                    Built specifically for the fitness industry — from boutique studios to national gym chains.
                </p>
            </div>

            {{-- Feature Cards Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

                @php
                $features = [
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
                        'color' => 'amber',
                        'title' => 'Biometric Access Control',
                        'desc'  => 'Hikvision DS-K1T343EWX facial recognition, thumb scanners, RFID cards, and QR codes. Auto-deny expired members at turnstile gates.',
                        'tag'   => 'Hardware Integration',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>',
                        'color' => 'orange',
                        'title' => 'Multi-Branch Management',
                        'desc'  => 'Manage unlimited gym branches under one account. Consolidated revenue reports, member transfers, and centralized staff control.',
                        'tag'   => 'Enterprise Ready',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>',
                        'color' => 'blue',
                        'title' => 'Member Lifecycle Manager',
                        'desc'  => 'Complete profiles, membership history, renewal alerts, body measurements, progress photos, and automated birthday/expiry messages.',
                        'tag'   => 'Member Management',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'color' => 'emerald',
                        'title' => 'Financial POS & GST Billing',
                        'desc'  => 'Collect fees, generate GST-compliant invoices, track dues, record expenses, and get daily/monthly financial reports with PDF export.',
                        'tag'   => 'Finance & Billing',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>',
                        'color' => 'violet',
                        'title' => 'WhatsApp Automation',
                        'desc'  => 'Automated renewal reminders with UPI payment links, birthday wishes, attendance alerts, and offer broadcasts — all via WhatsApp.',
                        'tag'   => 'Communication',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>',
                        'color' => 'rose',
                        'title' => 'Member Mobile App',
                        'desc'  => 'White-labeled Flutter app for Android & iOS. Members view routines, diet plans, attendance logs, and generate biometric QR codes.',
                        'tag'   => 'Mobile Apps',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
                        'color' => 'cyan',
                        'title' => 'Advanced Reports & Analytics',
                        'desc'  => 'Revenue trends, member retention rates, attendance heatmaps, package popularity, and expiry forecasts — in real-time dashboards.',
                        'tag'   => 'Analytics',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>',
                        'color' => 'teal',
                        'title' => 'Workout & Diet Builders',
                        'desc'  => 'Assign personalized exercise routines with 600+ animated videos. Build macro-specific diet plans and track body transformation progress.',
                        'tag'   => 'Fitness Tools',
                    ],
                    [
                        'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>',
                        'color' => 'amber',
                        'title' => 'Staff & Role Management',
                        'desc'  => 'Create staff accounts with granular role-based permissions. Trainers, receptionists, managers — each sees only what they need.',
                        'tag'   => 'Team Control',
                    ],
                ];
                $colorMap = [
                    'amber'  => ['bg'=>'bg-amber-500/10',  'text'=>'text-amber-400',  'border'=>'hover:border-amber-500/40'],
                    'orange' => ['bg'=>'bg-orange-500/10', 'text'=>'text-orange-400', 'border'=>'hover:border-orange-500/40'],
                    'blue'   => ['bg'=>'bg-blue-500/10',   'text'=>'text-blue-400',   'border'=>'hover:border-blue-500/40'],
                    'emerald'=> ['bg'=>'bg-emerald-500/10','text'=>'text-emerald-400','border'=>'hover:border-emerald-500/40'],
                    'violet' => ['bg'=>'bg-violet-500/10', 'text'=>'text-violet-400', 'border'=>'hover:border-violet-500/40'],
                    'rose'   => ['bg'=>'bg-rose-500/10',   'text'=>'text-rose-400',   'border'=>'hover:border-rose-500/40'],
                    'cyan'   => ['bg'=>'bg-cyan-500/10',   'text'=>'text-cyan-400',   'border'=>'hover:border-cyan-500/40'],
                    'teal'   => ['bg'=>'bg-teal-500/10',   'text'=>'text-teal-400',   'border'=>'hover:border-teal-500/40'],
                ];
                @endphp

                @foreach($features as $feature)
                    @php $c = $colorMap[$feature['color']] ?? $colorMap['amber']; @endphp
                    <div class="group p-7 rounded-2xl bg-slate-900/60 border border-slate-800/80 {{ $c['border'] }} card-hover backdrop-blur-sm">
                        <div class="flex items-start gap-4 mb-4">
                            <div class="w-12 h-12 rounded-xl {{ $c['bg'] }} {{ $c['text'] }} flex items-center justify-center shrink-0">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $feature['icon'] !!}</svg>
                            </div>
                            <span class="mt-1 text-[10px] font-bold tracking-widest uppercase {{ $c['text'] }} opacity-70">{{ $feature['tag'] }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2 group-hover:text-amber-400 transition-colors">{{ $feature['title'] }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">{{ $feature['desc'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="text-center mt-12">
                <a href="{{ route('features') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-amber-400 hover:text-amber-300 transition-colors border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/10 px-6 py-3 rounded-xl">
                    View All Features
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                </a>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         HOW IT WORKS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 lg:py-32 bg-slate-900/40 border-y border-slate-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-3">Quick Setup</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-4">
                    Up & Running in <span class="text-gradient">Under 2 Minutes</span>
                </h2>
                <p class="text-slate-400 text-lg">No technical expertise needed. No installation. No server setup.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 relative">
                {{-- Connector line on desktop --}}
                <div class="hidden lg:block absolute top-10 left-[15%] right-[15%] h-px bg-gradient-to-r from-transparent via-amber-500/30 to-transparent"></div>

                @foreach([
                    ['step'=>'01', 'title'=>'Create Free Account', 'desc'=>'Sign up with your gym name and email. No credit card required.', 'icon'=>'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
                    ['step'=>'02', 'title'=>'Configure Your Gym',  'desc'=>'Add branches, membership packages, staff accounts and opening hours.', 'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ['step'=>'03', 'title'=>'Add Members',         'desc'=>'Import existing data or add members manually with photos and packages.', 'icon'=>'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                    ['step'=>'04', 'title'=>'Go Live',             'desc'=>'Start collecting fees, scanning biometrics, and managing everything from your dashboard.', 'icon'=>'M13 10V3L4 14h7v7l9-11h-7z'],
                ] as $i => $step)
                    <div class="relative text-center">
                        <div class="relative inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-amber-500/15 to-orange-500/10 border border-amber-500/20 mb-5 mx-auto">
                            <svg class="w-8 h-8 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $step['icon'] }}"/>
                            </svg>
                            <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-amber-500 text-slate-950 text-xs font-black flex items-center justify-center">{{ $i+1 }}</span>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">{{ $step['title'] }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FEATURE HIGHLIGHT: BIOMETRICS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">

                {{-- Left: Content --}}
                <div>
                    <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-4">Biometric Integration</span>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-6">
                        Real Biometric Access Control with <span class="text-gradient">Hikvision Hardware</span>
                    </h2>
                    <p class="text-slate-400 text-lg leading-relaxed mb-8">
                        Gym Console is one of the few platforms with real, production-tested integration for Hikvision DS-K1T343EWX facial recognition terminals. No more manual check-ins.
                    </p>

                    <div class="space-y-4">
                        @foreach([
                            ['title'=>'Facial Recognition',       'desc'=>'DS-K1T343EWX terminal identifies members in under 0.3 seconds.'],
                            ['title'=>'RFID & QR Code Entry',     'desc'=>'Tap your card or scan a QR code from the mobile app.'],
                            ['title'=>'Turnstile Gate Automation','desc'=>'Gate unlocks for valid members, stays locked for expired ones.'],
                            ['title'=>'Anti-Passback Enforcement','desc'=>'Prevents credential sharing and unauthorized access attempts.'],
                        ] as $item)
                            <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-900/60 border border-slate-800/60">
                                <div class="w-8 h-8 rounded-lg bg-amber-500/15 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                </div>
                                <div>
                                    <div class="font-bold text-white text-sm mb-0.5">{{ $item['title'] }}</div>
                                    <div class="text-slate-400 text-xs leading-relaxed">{{ $item['desc'] }}</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Right: Illustrated terminal card --}}
                <div class="relative flex items-center justify-center">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/10 to-orange-500/5 rounded-3xl blur-2xl"></div>
                    <div class="relative w-full max-w-sm mx-auto">
                        <div class="rounded-3xl bg-slate-900 border border-slate-800 p-8 shadow-2xl shadow-black/40">
                            {{-- Device mockup --}}
                            <div class="flex items-center gap-3 mb-6">
                                <div class="w-12 h-12 rounded-xl bg-amber-500/15 border border-amber-500/30 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11"/></svg>
                                </div>
                                <div>
                                    <div class="text-white font-bold text-sm">DS-K1T343EWX</div>
                                    <div class="text-emerald-400 text-xs font-semibold flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block animate-pulse"></span>
                                        Online · Active
                                    </div>
                                </div>
                            </div>
                            {{-- Simulated face scan --}}
                            <div class="relative bg-slate-950 rounded-2xl aspect-square flex items-center justify-center border border-slate-800 mb-6 overflow-hidden">
                                <div class="absolute inset-4 rounded-xl border-2 border-amber-500/40 border-dashed animate-pulse"></div>
                                <svg class="w-20 h-20 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                {{-- Scan line --}}
                                <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-transparent via-amber-400 to-transparent opacity-80" style="animation: scan 2s linear infinite">
                                </div>
                            </div>
                            <style>
                                @keyframes scan {
                                    0%   { transform: translateY(0); opacity: 1; }
                                    100% { transform: translateY(220px); opacity: 0; }
                                }
                            </style>
                            {{-- Status --}}
                            <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20">
                                <span class="text-emerald-400 text-sm font-bold">✓ Access Granted</span>
                                <span class="text-emerald-400/60 text-xs">Gate Unlocked</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         TESTIMONIALS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 lg:py-32 bg-slate-900/40 border-y border-slate-800/60">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-3">Trusted by Gyms</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                    Gym Owners <span class="text-gradient">Love It</span>
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8">
                @foreach([
                    [
                        'quote' => 'We switched from paper registers to Gym Console in one afternoon. The biometric gate saved us ₹40,000 in unauthorized access losses in just 3 months.',
                        'name'  => 'Rajesh Sharma',
                        'role'  => 'Owner, PowerFit Gym, Delhi',
                        'stars' => 5,
                        'initials' => 'RS',
                        'color' => 'from-amber-500 to-orange-600',
                    ],
                    [
                        'quote' => 'The WhatsApp renewal reminders alone recovered 60+ lapsed members last quarter. The GST invoicing saves my accountant 8 hours every month.',
                        'name'  => 'Priya Nair',
                        'role'  => 'Manager, IronHouse Fitness, Bengaluru',
                        'stars' => 5,
                        'initials' => 'PN',
                        'color' => 'from-orange-500 to-rose-500',
                    ],
                    [
                        'quote' => 'Running 4 branches from one dashboard is a game changer. I can see real-time revenue and attendance across all locations on my phone.',
                        'name'  => 'Vikram Desai',
                        'role'  => 'CEO, FitZone Chain, Mumbai',
                        'stars' => 5,
                        'initials' => 'VD',
                        'color' => 'from-rose-500 to-violet-600',
                    ],
                ] as $review)
                    <div class="p-7 rounded-2xl bg-slate-900 border border-slate-800/80 card-hover flex flex-col gap-5">
                        {{-- Stars --}}
                        <div class="flex gap-1">
                            @for($i = 0; $i < $review['stars']; $i++)
                                <svg class="w-4 h-4 text-amber-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        {{-- Quote --}}
                        <p class="text-slate-300 text-sm leading-relaxed flex-1">"{{ $review['quote'] }}"</p>
                        {{-- Author --}}
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-800/60">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $review['color'] }} flex items-center justify-center text-white font-black text-sm shrink-0">
                                {{ $review['initials'] }}
                            </div>
                            <div>
                                <div class="text-white font-bold text-sm">{{ $review['name'] }}</div>
                                <div class="text-slate-500 text-xs">{{ $review['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         PRICING SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section id="plans" class="py-24 lg:py-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <div class="text-center max-w-3xl mx-auto mb-6">
                <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-3">Simple Pricing</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight mb-4">
                    Start Free. <span class="text-gradient">Scale When Ready.</span>
                </h2>
                <p class="text-slate-400 text-lg">No hidden fees. No per-member charges. Pay once a year and grow without limits.</p>
            </div>

            {{-- WhatsApp CTA --}}
            <div class="text-center mb-12">
                <a href="https://wa.me/919876543210?text=Hi,%20I%20want%20to%20know%20more%20about%20Gym%20Console%20pricing" target="_blank"
                   class="inline-flex items-center gap-2.5 text-sm font-semibold text-emerald-400 hover:text-emerald-300 transition-colors bg-emerald-500/10 border border-emerald-500/20 px-6 py-3 rounded-full hover:bg-emerald-500/15">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                    Chat with us on WhatsApp for custom enterprise pricing
                </a>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch max-w-6xl mx-auto">

                {{-- FREE PLAN --}}
                <div class="rounded-3xl bg-white text-slate-800 p-8 border border-slate-200 shadow-xl flex flex-col card-hover">
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-black uppercase tracking-wider mb-4">
                            🎁 Free Forever
                        </span>
                        <div class="text-5xl font-black text-slate-950 mt-2">₹0</div>
                        <div class="text-xs font-medium text-slate-500 mt-1">No credit card · No expiry</div>
                        <p class="text-xs text-slate-500 mt-3 leading-relaxed max-w-xs mx-auto">Perfect for small gyms getting started. Upgrade only when you need more.</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl px-4 py-2.5 text-center text-xs font-bold text-slate-600 mb-6">
                        Up to 75 members · 1 branch
                    </div>
                    <ul class="space-y-2.5 text-xs text-slate-700 mb-8 flex-1">
                        @foreach(['Member database & profiles','Manual attendance tracking','Membership packages & offers','Payment recording & PDF invoices','Body measurements & progress tracking','Dashboard & member reports','Email notifications','Data export anytime'] as $feat)
                            <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ $feat }}</li>
                        @endforeach
                        @foreach(['WhatsApp reminders','Biometric attendance','Mobile member app','GST reports'] as $feat)
                            <li class="flex items-center gap-2.5 text-slate-400 line-through"><svg class="w-4 h-4 shrink-0 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>{{ $feat }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                       class="block w-full py-3.5 rounded-2xl bg-slate-950 hover:bg-slate-800 text-white font-bold text-sm text-center transition-all shadow-md">
                        Create Free Account →
                    </a>
                    <p class="text-center text-[11px] text-slate-400 mt-2 font-medium">Live in 2 minutes. No sales call.</p>
                </div>

                {{-- STARTER PLAN (FEATURED) --}}
                <div class="rounded-3xl bg-[#ea580c] text-white p-8 shadow-2xl shadow-orange-600/40 flex flex-col card-hover border-2 border-orange-400 lg:-translate-y-3">
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-950 text-white text-[11px] font-black uppercase tracking-wider mb-4">
                            🔥 Most Popular
                        </span>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="line-through text-white/60 text-base font-medium">₹9,000</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-950/60 text-white font-black text-[10px] tracking-wide">33% OFF</span>
                        </div>
                        <div class="text-5xl font-black text-white">₹6,000</div>
                        <div class="text-xs font-medium text-white/80 mt-1">per year + 18% GST</div>
                        <p class="text-xs text-white/80 mt-3 leading-relaxed max-w-xs mx-auto">Just ₹500/month. You save ₹3,000 on the launch offer.</p>
                    </div>
                    <div class="bg-white/20 backdrop-blur rounded-xl px-4 py-2.5 text-center text-xs font-bold text-white border border-white/30 mb-6">
                        Unlimited members · 1 branch
                    </div>
                    <div class="text-xs font-bold text-white/80 mb-3">Everything in Free, plus:</div>
                    <ul class="space-y-2.5 text-xs text-white/95 mb-8 flex-1">
                        @foreach(['Unlimited members — no caps','Face / Thumb / RFID biometrics','WhatsApp automation with UPI links','GST billing & reports','Fitness mobile app (Android + iOS)','600+ animated workout videos','Staff & trainer role accounts','Free data migration'] as $feat)
                            <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ $feat }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['plan' => 'starter']) }}"
                       class="block w-full py-3.5 rounded-2xl bg-slate-950 hover:bg-black text-white font-black text-sm text-center transition-all shadow-xl">
                        Buy Starter Plan
                    </a>
                </div>

                {{-- PRO PLAN --}}
                <div class="rounded-3xl bg-white text-slate-800 p-8 border border-slate-200 shadow-xl flex flex-col card-hover">
                    <div class="text-center mb-6">
                        <span class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-slate-100 text-slate-600 text-[11px] font-black uppercase tracking-wider mb-4">
                            🏆 Pro Chain
                        </span>
                        <div class="flex items-center justify-center gap-2 mt-2">
                            <span class="line-through text-slate-400 text-base font-medium">₹18,000</span>
                            <span class="px-2 py-0.5 rounded-md bg-[#ea580c] text-white font-black text-[10px] tracking-wide">33% OFF</span>
                        </div>
                        <div class="text-5xl font-black text-slate-950">₹12,000</div>
                        <div class="text-xs font-medium text-slate-500 mt-1">per year + 18% GST</div>
                        <p class="text-xs text-slate-500 mt-3 leading-relaxed max-w-xs mx-auto">₹1,000/month for a full gym chain. You save ₹6,000.</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl px-4 py-2.5 text-center text-xs font-bold text-slate-600 mb-6">
                        Unlimited members · Multiple branches
                    </div>
                    <div class="text-xs font-bold text-slate-700 mb-3">Everything in Starter, plus:</div>
                    <ul class="space-y-2.5 text-xs text-slate-700 mb-8 flex-1">
                        @foreach(['Branded mobile app under your gym name','Multi-branch consolidation reports','Door lock & access control integration','Supplement shop POS & inventory','Class scheduling & batch management','Priority onboarding & support','Dedicated WhatsApp account manager'] as $feat)
                            <li class="flex items-center gap-2.5"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>{{ $feat }}</li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register', ['plan' => 'pro']) }}"
                       class="block w-full py-3.5 rounded-2xl bg-slate-950 hover:bg-slate-800 text-white font-bold text-sm text-center transition-all shadow-md">
                        Buy Pro Plan →
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FAQ SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 lg:py-32 bg-slate-900/40 border-y border-slate-800/60">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="inline-block text-xs font-extrabold tracking-widest text-amber-400 uppercase mb-3">FAQ</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white tracking-tight">
                    Common <span class="text-gradient">Questions</span>
                </h2>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                @foreach([
                    ['q'=>'Is the free plan really free forever?',                 'a'=>'Yes. The Free Forever plan has no time limit. You can manage up to 75 members at no cost, with no credit card required. Upgrade only when your gym grows beyond 75 members.'],
                    ['q'=>'Which biometric devices are supported?',                 'a'=>'We officially support Hikvision DS-K1T343EWX (facial recognition + RFID), Hikvision MinMoe series terminals, and standard RFID readers. QR code generation via the mobile app also works with most readers.'],
                    ['q'=>'Can I import my existing member data?',                  'a'=>'Yes. We support CSV import for member data. Our team also provides free assisted migration from common gym software during your onboarding.'],
                    ['q'=>'How does multi-branch management work?',                 'a'=>'Each branch operates as a separate location under your account. Members can be enrolled at specific branches, and you get consolidated reports across all locations from a single Super Admin dashboard.'],
                    ['q'=>'Is the mobile app available for download right now?',   'a'=>'The Fitness Member App (Android & iOS) is included in Starter and Pro plans. Your branded version for the Pro plan takes 3–5 working days after account setup.'],
                    ['q'=>'What happens to my data if I cancel?',                  'a'=>'Your data belongs to you. You can export everything as CSV/PDF at any time before or after cancellation. We never delete data without explicit request.'],
                ] as $i => $faq)
                    <div class="rounded-2xl bg-slate-900 border border-slate-800/80 overflow-hidden">
                        <button class="w-full flex items-center justify-between gap-4 px-6 py-5 text-left font-bold text-white hover:text-amber-400 transition-colors cursor-pointer focus:outline-none"
                                @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="text-sm lg:text-base">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-slate-400 transition-transform duration-200"
                                 :class="open === {{ $i }} ? 'rotate-180 text-amber-400' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}"
                             x-collapse
                             x-cloak
                             class="px-6 pb-5">
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FINAL CTA SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-24 lg:py-32 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500/8 via-transparent to-orange-500/5 pointer-events-none"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-amber-500/8 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-black text-white tracking-tight mb-6">
                Ready to Transform<br>
                <span class="text-gradient">Your Gym?</span>
            </h2>
            <p class="text-slate-400 text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
                Join hundreds of gym owners who've automated their operations, stopped revenue leakage, and grown their member base with Gym Console.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-10 py-5 rounded-2xl bg-gradient-to-r from-amber-500 via-amber-400 to-orange-500 text-slate-950 font-black text-lg hover:brightness-110 shadow-2xl shadow-amber-500/30 hover:scale-[1.03] active:scale-[0.98] transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Start Free Today
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-5 rounded-2xl bg-slate-900 border border-slate-700 text-slate-200 font-bold text-base hover:bg-slate-800 hover:border-slate-600 transition-all">
                    Talk to Sales
                </a>
            </div>
            <p class="mt-6 text-sm text-slate-600">Free plan available · No credit card · Cancel anytime</p>
        </div>
    </section>

</x-guest-layout>
