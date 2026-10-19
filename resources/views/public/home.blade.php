<x-guest-layout title="{{ ($platformSettings['app_name'] ?? 'Gym Console') }} — Modern Gym Management & Access Control Platform">

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="relative pt-12 pb-20 sm:pt-20 sm:pb-28 lg:pt-24 lg:pb-32 overflow-hidden">
        {{-- Subtle radial gradient --}}
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-7xl h-[500px] bg-gradient-to-b from-amber-500/[0.07] via-transparent to-transparent pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                {{-- Announcement Badge --}}
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6 sm:mb-8">
                    <span class="w-2 h-2 rounded-full bg-amber-400 shrink-0"></span>
                    <span>All-in-One Gym Operating System</span>
                </div>

                {{-- Authoritative Headline --}}
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-white leading-[1.12] mb-6">
                    Run your entire gym on autopilot, <br class="hidden sm:inline">
                    <span class="text-amber-400">from turnstiles to billing</span>
                </h1>

                {{-- Focused Subheadline --}}
                <p class="text-base sm:text-lg lg:text-xl text-slate-400 leading-relaxed max-w-3xl mx-auto font-normal mb-10">
                    The complete gym operating system: biometric access control, automated WhatsApp fee collection, multi-branch management, workout builders, and member mobile apps.
                </p>

                {{-- Direct Action Buttons --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-7 py-3.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-base hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20 active:scale-[0.99]">
                        <span>Start Free Forever</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('features') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-7 py-3.5 rounded-xl bg-slate-800/90 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-700/80 hover:text-white transition-all">
                        Explore Features
                    </a>
                </div>

                {{-- Trust Highlights --}}
                <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs sm:text-sm text-slate-400 font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Free Forever Plan (Up to 75 members)</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>No credit card required</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Sub-second Biometric Sync</span>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════
                 PRODUCT DASHBOARD PREVIEW MOCKUP
            ══════════════════════════════════════════════════════════ --}}
            <div class="mt-16 sm:mt-20 max-w-6xl mx-auto">
                <div class="rounded-2xl bg-slate-900 border border-slate-800 p-3 sm:p-4 shadow-2xl shadow-black/60">
                    {{-- App Header Bar --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-slate-950/80 rounded-xl border border-slate-800/80 mb-4">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                                <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                                <div class="w-3 h-3 rounded-full bg-slate-700"></div>
                            </div>
                            <span class="text-xs font-semibold text-slate-400 hidden sm:inline">app.gymconsole.in &bull; Downtown Branch</span>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-medium border border-emerald-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span> 2 Turnstiles Connected
                            </span>
                            <span class="text-slate-400 font-mono hidden md:inline">Live Sync: Active</span>
                        </div>
                    </div>

                    {{-- Main Mockup Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                        {{-- Quick Metrics (Left / 4 cols) --}}
                        <div class="md:col-span-4 space-y-4">
                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                <div class="text-xs text-slate-400 font-medium">Monthly Collections</div>
                                <div class="text-2xl font-bold text-white mt-1">₹4,82,500</div>
                                <div class="flex items-center gap-1.5 text-xs text-emerald-400 font-medium mt-2">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    <span>+18.4% vs last month</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                <div class="text-xs text-slate-400 font-medium">Active Members & Attendance</div>
                                <div class="text-2xl font-bold text-white mt-1">428 <span class="text-xs font-normal text-slate-400">/ 450 active</span></div>
                                <div class="w-full bg-slate-800 rounded-full h-2 mt-3 overflow-hidden">
                                    <div class="bg-amber-400 h-2 rounded-full" style="width: 95%"></div>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-2">142 checked in today</div>
                            </div>

                            <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                                <div class="text-xs text-slate-400 font-medium">Automated WhatsApp Queue</div>
                                <div class="text-sm font-semibold text-white mt-1">12 Renewal Alerts Sent Today</div>
                                <div class="text-xs text-emerald-400 mt-1">₹34,000 collected via UPI links</div>
                            </div>
                        </div>

                        {{-- Live Biometric Logs (Right / 8 cols) --}}
                        <div class="md:col-span-8 p-4 rounded-xl bg-slate-950/60 border border-slate-800/80">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-800/80 mb-3">
                                <div class="text-xs font-bold text-white uppercase tracking-wider">Live Turnstile & Access Activity</div>
                                <span class="text-[11px] text-slate-400">Real-Time Event Stream</span>
                            </div>

                            <div class="space-y-2 text-xs">
                                <div class="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        <span class="font-medium text-white">Rohit Sharma</span>
                                        <span class="text-slate-400">Plan: Annual Platinum</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px]">
                                        <span>Gate #1 (Face ID)</span>
                                        <span class="text-emerald-400 font-medium">0.24s &bull; Access Granted</span>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                        <span class="font-medium text-white">Ananya Sen</span>
                                        <span class="text-rose-400 font-medium">Expired 2 days ago</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px]">
                                        <span>Gate #2 (Thumb)</span>
                                        <span class="text-rose-400 font-medium">Access Denied &bull; WhatsApp Link Sent</span>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                        <span class="font-medium text-white">Karan Gill</span>
                                        <span class="text-slate-400">Plan: 6-Month Gold</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px]">
                                        <span>Gate #1 (RFID Card)</span>
                                        <span class="text-emerald-400 font-medium">0.18s &bull; Access Granted</span>
                                    </div>
                                </div>

                                <div class="p-2.5 rounded-lg bg-slate-900/90 border border-slate-800 flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                                        <span class="font-medium text-white">Priya Nair (Trainer)</span>
                                        <span class="text-slate-400">Staff Check-in</span>
                                    </div>
                                    <div class="flex items-center gap-3 text-slate-400 font-mono text-[11px]">
                                        <span>Staff Gate (Face ID)</span>
                                        <span class="text-blue-400 font-medium">Shift Started &bull; 06:00 AM</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         INFINITE CAPABILITIES TICKER RIBBON (Brand Color Theme)
    ══════════════════════════════════════════════════════════════ --}}
    <section class="border-y border-slate-800 bg-[#0a0e18]/90 py-4 sm:py-5 overflow-hidden relative select-none">
        <!-- Edge Fading Gradient Masks matching page background -->
        <div class="pointer-events-none absolute inset-y-0 left-0 w-24 sm:w-48 bg-gradient-to-r from-[#090d16] to-transparent z-10"></div>
        <div class="pointer-events-none absolute inset-y-0 right-0 w-24 sm:w-48 bg-gradient-to-l from-[#090d16] to-transparent z-10"></div>

        <!-- Marquee Track -->
        <div class="animate-marquee items-center">
            @php
                $tickerItems = [
                    'Membership Management',
                    'Payment Collection',
                    'Attendance Tracking',
                    'WhatsApp Automation',
                    'Multi-Branch Support',
                    'Biometric Attendance and Access',
                    'Personal Training & POS',
                    'Diet & Workout Plans',
                ];
            @endphp

            {{-- First Copy --}}
            <div class="flex items-center shrink-0">
                @foreach($tickerItems as $item)
                    <div class="flex items-center whitespace-nowrap px-6 sm:px-8">
                        <span class="text-slate-200 hover:text-amber-400 font-bold text-sm sm:text-base tracking-wide transition-colors cursor-default">
                            {{ $item }}
                        </span>
                        <span class="text-amber-400/60 text-xs ml-6 sm:ml-8 select-none">&bull;</span>
                    </div>
                @endforeach
            </div>

            {{-- Second Copy (for seamless loop) --}}
            <div class="flex items-center shrink-0" aria-hidden="true">
                @foreach($tickerItems as $item)
                    <div class="flex items-center whitespace-nowrap px-6 sm:px-8">
                        <span class="text-slate-200 hover:text-amber-400 font-bold text-sm sm:text-base tracking-wide transition-colors cursor-default">
                            {{ $item }}
                        </span>
                        <span class="text-amber-400/60 text-xs ml-6 sm:ml-8 select-none">&bull;</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         METRICS / SOCIAL PROOF STRIP
    ══════════════════════════════════════════════════════════════ --}}
    <section class="border-y border-slate-800 bg-slate-900/40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">500+</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Active Gym Branches</div>
                </div>
                <div>
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">100,000+</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Members Managed</div>
                </div>
                <div>
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">99.98%</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Biometric Gate Uptime</div>
                </div>
                <div>
                    <div class="text-3xl lg:text-4xl font-bold text-white mb-1">&lt; 2 mins</div>
                    <div class="text-xs sm:text-sm text-slate-400 font-medium">Setup &amp; Go Live Time</div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         EXCEPTIONAL BRANDS CLIENT SHOWCASE
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-16 sm:py-20 lg:py-24 border-b border-slate-800 bg-[#090d16] relative overflow-hidden">
        {{-- Subtle radial ambient glow --}}
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[300px] bg-amber-500/[0.03] rounded-full blur-3xl pointer-events-none -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-10 sm:mb-14">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/80 text-amber-400 text-xs font-semibold mb-4">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                    <span>Trusted Fitness Community</span>
                </div>
                <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold text-white tracking-tight">
                    We are fortunate to work with exceptional brands
                </h2>
                <p class="text-xs sm:text-sm text-slate-400 mt-2.5 font-normal max-w-xl mx-auto">
                    Powering premier gyms, fitness studios, and athletic clubs across India with enterprise automation.
                </p>
            </div>

            <!-- Brand Logos Only -->
            <div class="flex flex-wrap items-center justify-center gap-10 sm:gap-16 lg:gap-24 max-w-4xl mx-auto pt-2">
                <div class="flex items-center justify-center p-3 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('images/brands/armour-gym.png') }}" 
                         alt="Armour 24-7 Gym" 
                         loading="lazy"
                         class="h-16 sm:h-20 lg:h-24 w-auto max-w-[280px] object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.8)]">
                </div>

                <div class="flex items-center justify-center p-3 transition-transform duration-300 hover:scale-105">
                    <img src="{{ asset('images/brands/fitness-world.png') }}" 
                         alt="Fitness World Gym" 
                         loading="lazy"
                         class="h-16 sm:h-20 lg:h-24 w-auto max-w-[280px] object-contain drop-shadow-[0_10px_25px_rgba(0,0,0,0.8)]">
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         BENTO GRID FEATURES
    ══════════════════════════════════════════════════════════════ --}}
    <section id="features" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Core Capabilities</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight mt-2 mb-4">
                    Built for every part of your gym business
                </h2>
                <p class="text-slate-400 text-base sm:text-lg">
                    No clunky add-ons or fragmented tools. Everything is connected out of the box.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                {{-- 1. Biometrics --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Biometric & Turnstile Gate Sync</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Universal integration with eSSL, Hikvision, Realtime, ZKTeco, and Mantra. Sub-second face recognition and thumb scanners with automatic expired-member gating.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        Universal Hardware Middleware &bull; Anti-Passback
                    </div>
                </div>

                {{-- 2. WhatsApp Automation --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Automated WhatsApp & 1-Click UPI</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Stop chasing fee renewals. Automatically send WhatsApp reminders with dynamic UPI payment links 7 days, 3 days, and on expiry day.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        Instant Reconciliation &bull; PDF Receipts
                    </div>
                </div>

                {{-- 3. Multi-Branch --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Multi-Branch Chain Management</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Run 1, 5, or 50 gym branches under a single unified login. Consolidated revenue reports, cross-branch member access, and branch-isolated staff.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        Tenant-Level Isolation &bull; Consolidated Audits
                    </div>
                </div>

                {{-- 4. Mobile Apps & Workouts --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Member App & 600+ Workout Library</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Give members an Android and iOS app with digital QR check-in, animated workout video splits, diet macros, and weight transformation tracking.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        Flutter Native &bull; Play Store & App Store
                    </div>
                </div>

                {{-- 5. GST Billing & POS --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-violet-500/10 text-violet-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l4-2 4 2 4-2 4 2z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">GST Billing, POS & Expense Tracker</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Generate GST-compliant tax invoices, manage supplement shop inventory, track gym expenses, and export B2B/B2C filing reports in 1 click.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        HSN/SAC Codes &bull; GSTR Ready
                    </div>
                </div>

                {{-- 6. Granular Staff Roles --}}
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between hover:border-slate-700 transition-colors">
                    <div>
                        <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 flex items-center justify-center mb-4">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <h3 class="text-lg font-bold text-white mb-2">Granular Roles & Team Permissions</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">
                            Assign individual permissions to receptionists, personal trainers, accountants, and floor managers. Protect your financial metrics from unauthorized view.
                        </p>
                    </div>
                    <div class="mt-6 pt-4 border-t border-slate-800/80 text-xs font-semibold text-slate-400">
                        View / Edit / Delete Permissions
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         4-STEP ONBOARDING
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-slate-900/40 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Implementation</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mt-2 mb-4">
                    Get up and running in 4 simple steps
                </h2>
                <p class="text-slate-400 text-base">No technical experience needed. Fully cloud-hosted and ready in minutes.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach([
                    [
                        'num' => '01',
                        'title' => 'Register Gym Profile',
                        'desc' => 'Sign up your gym branch in 30 seconds. Add your branch details, gym logo, operating hours, and billing preferences.',
                    ],
                    [
                        'num' => '02',
                        'title' => 'Create Membership Plans',
                        'desc' => 'Set up your flexible packages — Monthly, Quarterly, Annual, or Personal Training with custom pricing, discounts, and GST.',
                    ],
                    [
                        'num' => '03',
                        'title' => 'Import Members',
                        'desc' => 'Bulk upload your existing member database via Excel/CSV in 1 click or enroll new walk-ins with instant profile creation.',
                    ],
                    [
                        'num' => '04',
                        'title' => 'Automate & Collect',
                        'desc' => 'Send automatic WhatsApp expiry reminders with direct UPI pay links and manage digital invoices and check-ins on autopilot.',
                    ],
                ] as $step)
                    <div class="p-6 sm:p-7 rounded-2xl bg-slate-900 border border-slate-800 hover:border-amber-500/40 transition-all duration-200 group">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-2xl font-black text-amber-400 font-mono tracking-wider">{{ $step['num'] }}</span>
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-700 group-hover:bg-amber-400 transition-colors"></span>
                        </div>
                        <h3 class="text-base font-bold text-white mb-2 group-hover:text-amber-300 transition-colors">{{ $step['title'] }}</h3>
                        <p class="text-xs sm:text-sm text-slate-400 leading-relaxed">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         PRICING PREVIEW
    ══════════════════════════════════════════════════════════════ --}}
    <section id="plans" class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Transparent Pricing</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight mt-2 mb-4">
                    Simple, transparent pricing
                </h2>
                <p class="text-slate-400 text-base sm:text-lg">
                    Start on our free tier, upgrade as your facility expands. No surprise per-member transaction charges.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch max-w-6xl mx-auto">

                {{-- FREE FOREVER --}}
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Free Forever</span>
                        </div>
                        <div class="text-4xl font-bold text-white">₹0</div>
                        <div class="text-xs text-slate-400 mt-1">Free forever &bull; Up to 75 members</div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            Ideal for personal trainers, boutique studios, and gyms starting out.
                        </p>
                        <ul class="space-y-3 text-xs text-slate-300 my-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Member database & profiles</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Manual attendance tracking</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Packages & PDF invoices</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Body measurement logs</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                       class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm text-center transition-colors">
                        Create Free Account
                    </a>
                </div>

                {{-- STARTER (HIGHLIGHTED) --}}
                <div class="p-8 rounded-2xl bg-slate-900 border-2 border-amber-500/80 flex flex-col justify-between relative shadow-xl shadow-amber-500/10">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-3 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-bold uppercase tracking-wider">
                        Most Popular
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-wider">Starter</span>
                            <span class="text-xs text-amber-400 font-semibold">Save 33%</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-bold text-white">₹6,000</span>
                            <span class="text-xs text-slate-400 line-through">₹9,000</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">per year + 18% GST &bull; 1 branch, unlimited members</div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            Everything you need to automate attendance, WhatsApp fee renewals, and member mobile app.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Free, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Unlimited member capacity</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Biometric face / thumb / RFID turnstile sync</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Automated WhatsApp reminders + UPI pay link</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Member App (Android & iOS) + 600+ workout videos</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> GST billing & staff roles</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'starter']) }}"
                       class="w-full py-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm text-center transition-colors">
                        Buy Starter Plan
                    </a>
                </div>

                {{-- PRO --}}
                <div class="p-8 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-2.5 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Pro Chain</span>
                            <span class="text-xs text-amber-400 font-semibold">Save 33%</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl font-bold text-white">₹12,000</span>
                            <span class="text-xs text-slate-400 line-through">₹18,000</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">per year + 18% GST &bull; Multi-branch chain</div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            For growing gym chains needing custom white-label mobile app and multi-location analytics.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Starter, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> White-label mobile app under your gym name</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Multi-branch consolidation reports</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Supplement shop POS & inventory</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Dedicated WhatsApp account manager</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'pro']) }}"
                       class="w-full py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm text-center transition-colors">
                        Buy Pro Plan
                    </a>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         TESTIMONIALS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-slate-900/40 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Customer Stories</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mt-2">
                    Trusted by gym founders and managers
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach([
                    [
                        'quote' => 'Connecting our Hikvision face terminals took less than 15 minutes. We stopped un-renewed members from walking in, recovering ₹45,000 in overdue fees in the very first month.',
                        'name' => 'Rajesh Sharma',
                        'role' => 'Owner, PowerFit Studio, Delhi',
                        'initials' => 'RS',
                    ],
                    [
                        'quote' => 'Automated WhatsApp renewal messages with direct UPI payment links are a massive time saver. 70% of our members pay before the expiry date now.',
                        'name' => 'Priya Nair',
                        'role' => 'Operations Manager, IronHouse Gym, Bengaluru',
                        'initials' => 'PN',
                    ],
                    [
                        'quote' => 'Managing our 3 branches from one dashboard gives us complete revenue clarity. Staff roles ensure receptionists only access what they need.',
                        'name' => 'Vikram Desai',
                        'role' => 'Founder, FitZone Club, Mumbai',
                        'initials' => 'VD',
                    ],
                ] as $review)
                    <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">"{{ $review['quote'] }}"</p>
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-800/80">
                            <div class="w-9 h-9 rounded-full bg-slate-800 text-amber-400 flex items-center justify-center font-bold text-xs">
                                {{ $review['initials'] }}
                            </div>
                            <div>
                                <div class="text-white font-semibold text-sm">{{ $review['name'] }}</div>
                                <div class="text-slate-400 text-xs">{{ $review['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FAQ SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Frequently Asked Questions</span>
                <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mt-2">
                    Everything you need to know
                </h2>
            </div>

            <div class="space-y-3" x-data="{ open: null }">
                @foreach([
                    ['q' => 'Is the free plan really free forever?', 'a' => 'Yes. The Free Forever plan has no expiration date. You can manage up to 75 members at zero cost with full member profile and package management. Upgrade only when you exceed 75 members or require automated biometric turnstile sync.'],
                    ['q' => 'Which biometric devices and brands are supported?', 'a' => 'We support all major biometric machines including eSSL (SilkBio, MB20, Identix, X990 series), Hikvision (Face MinMoe series), ZKTeco, Realtime, Mantra USB thumb readers, RFID smartcards, and turnstile gate relays.'],
                    ['q' => 'How does multi-branch isolation work?', 'a' => 'Each branch is scoped with separate staff permissions and financial registers. As a master administrator, you get consolidated metrics across all locations in real time.'],
                    ['q' => 'Can I import my existing member database?', 'a' => 'Yes. You can import existing member CSV files in bulk. Our onboarding team also provides free assisted data migration.'],
                ] as $i => $faq)
                    <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden">
                        <button class="w-full flex items-center justify-between gap-4 px-6 py-4 text-left font-semibold text-white hover:text-amber-400 transition-colors focus:outline-none"
                                @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="text-sm sm:text-base">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-slate-400 transition-transform duration-200"
                                 :class="open === {{ $i }} ? 'rotate-180 text-amber-400' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-4">
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FINAL CTA
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-slate-900/40 border-t border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white tracking-tight mb-4">
                Ready to upgrade your gym management?
            </h2>
            <p class="text-slate-400 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                Join hundreds of gyms automating their turnstiles, attendance, and fee collection with Gym Console.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-base hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20">
                    Get Started Free
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-700 transition-all">
                    Talk to Sales
                </a>
            </div>
        </div>
    </section>

</x-guest-layout>
