<x-guest-layout 
    title="GymDeskHub — Gym Management Software & Biometric Access Control"
    meta-description="Cloud gym management software for Indian gyms. Connects Hikvision turnstiles, sends automated WhatsApp fee reminders with UPI links, and handles GST billing."
    meta-keywords="gym management software, gym software india, biometric turnstile gym, gym whatsapp billing, gym membership software"
>

    {{-- ══════════════════════════════════════════════════════════════
         HERO SECTION
    ══════════════════════════════════════════════════════════════ --}}
    <section class="relative pt-12 pb-20 sm:pt-20 sm:pb-28 lg:pt-24 lg:pb-32 overflow-hidden">
        {{-- Subtle ambient glow --}}
        <div class="pointer-events-none absolute top-0 left-1/2 -translate-x-1/2 w-full max-w-6xl h-[550px] bg-gradient-to-b from-amber-500/[0.10] via-amber-500/[0.02] to-transparent blur-3xl -z-10"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-4xl mx-auto">
                {{-- Status Pill --}}
                <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-slate-900/90 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6 sm:mb-8 shadow-sm">
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>Biometric Turnstile Sync &bull; Automated WhatsApp Billing</span>
                </div>

                {{-- Headline --}}
                <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white leading-[1.12] mb-6">
                    Stop chasing gym fees. <br class="hidden sm:inline">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-300 via-amber-400 to-amber-500">Lock the turnstile</span>
                    when plans expire.
                </h1>

                {{-- Subheadline --}}
                <p class="text-base sm:text-lg lg:text-xl text-slate-300 leading-relaxed max-w-3xl mx-auto font-normal mb-10">
                    GymDeskHub connects your biometric gates to member billing. When a plan expires, the door stays locked and an automated WhatsApp reminder with a UPI payment link goes out.
                </p>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-base transition-all shadow-lg shadow-amber-500/20 active:scale-[0.98]">
                        <span>Start Free (Up to 75 Members)</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="{{ route('contact') }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 rounded-xl bg-slate-900 border border-slate-700/80 text-slate-200 font-semibold text-base hover:bg-slate-800 hover:text-white transition-all">
                        <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771z"/></svg>
                        <span>Talk to Hardware Specialist</span>
                    </a>
                </div>

                {{-- Trust Points --}}
                <div class="mt-12 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-xs sm:text-sm text-slate-400 font-medium">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Free forever up to 75 members</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>No credit card needed</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Works with Hikvision &amp; eSSL</span>
                    </div>
                </div>
            </div>

            {{-- ══════════════════════════════════════════════════════════
                 PRODUCT DASHBOARD PREVIEW
            ══════════════════════════════════════════════════════════ --}}
            <div class="mt-16 sm:mt-20 max-w-6xl mx-auto">
                <div class="rounded-3xl bg-slate-900/90 p-2 sm:p-3 border border-slate-800 shadow-2xl ring-1 ring-white/10">
                    
                    {{-- App Header Bar --}}
                    <div class="flex items-center justify-between px-4 py-3 bg-[#080d17] rounded-2xl border border-slate-800/80 mb-3">
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div class="w-3 h-3 rounded-full bg-rose-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                            </div>
                            <div class="hidden sm:flex items-center gap-2 px-3 py-1 rounded-lg bg-slate-900 border border-slate-800 text-xs font-mono text-slate-300">
                                <span class="text-amber-400 font-bold">GymDeskHub</span>
                                <span class="text-slate-600">/</span>
                                <span>Main Branch</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 font-semibold border border-emerald-500/30">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span> 2 Turnstiles Connected
                            </span>
                            <span class="text-slate-400 font-mono hidden md:inline">Sync: 42ms</span>
                        </div>
                    </div>

                    {{-- Main Mockup Grid --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-3.5">
                        {{-- Metrics (Left / 4 cols) --}}
                        <div class="md:col-span-4 space-y-3.5">
                            <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/90 shadow-sm">
                                <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                                    <span>This Month's Collections</span>
                                    <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">+18.4%</span>
                                </div>
                                <div class="text-2xl sm:text-3xl font-extrabold text-white mt-1.5 tracking-tight">₹5,42,800</div>
                                <div class="text-xs text-slate-400 mt-2">
                                    <span class="text-emerald-400 font-semibold">₹84,500</span> collected via WhatsApp UPI links this week
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/90 shadow-sm">
                                <div class="flex items-center justify-between text-xs text-slate-400 font-medium">
                                    <span>Active Members</span>
                                    <span class="text-white font-mono font-semibold">468 / 500</span>
                                </div>
                                <div class="w-full bg-slate-800 rounded-full h-2.5 mt-3 overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-400 to-emerald-400 h-2.5 rounded-full" style="width: 93%"></div>
                                </div>
                                <div class="flex items-center justify-between text-[11px] text-slate-400 mt-2.5">
                                    <span>184 Checked in today</span>
                                    <span class="text-amber-400 font-semibold">12 Expiries this week</span>
                                </div>
                            </div>

                            <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/90 shadow-sm">
                                <div class="text-xs text-slate-400 font-medium">WhatsApp Reminders</div>
                                <div class="text-sm font-bold text-white mt-1">16 Expiry alerts sent today</div>
                                <div class="text-xs text-emerald-400 mt-1 flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Each alert includes a direct UPI link</span>
                                </div>
                            </div>
                        </div>

                        {{-- Live Biometric Logs (Right / 8 cols) --}}
                        <div class="md:col-span-8 p-4 rounded-2xl bg-[#080d17] border border-slate-800/90 shadow-sm flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between pb-3 border-b border-slate-800/80 mb-3">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-ping"></div>
                                        <span class="text-xs font-bold text-white uppercase tracking-wider">Live Turnstile Activity</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 font-mono">Real-time gate events</span>
                                </div>

                                <div class="space-y-2.5 text-xs">
                                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs">RS</div>
                                            <div>
                                                <div class="font-bold text-white">Rohit Sharma</div>
                                                <div class="text-[11px] text-slate-400">12-Month Plan &bull; Valid till Dec 2026</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-emerald-400 font-bold text-xs">GATE OPEN</div>
                                            <div class="text-[10px] text-slate-400 font-mono">Face ID &bull; 0.19s</div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl bg-rose-500/[0.06] border border-rose-500/30 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-rose-500/20 text-rose-400 flex items-center justify-center font-bold text-xs">AS</div>
                                            <div>
                                                <div class="font-bold text-white">Ananya Sen</div>
                                                <div class="text-[11px] text-rose-400 font-medium">Plan expired yesterday &bull; WhatsApp link sent</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-rose-400 font-bold text-xs">GATE LOCKED</div>
                                            <div class="text-[10px] text-slate-400 font-mono">Automatic lockout</div>
                                        </div>
                                    </div>

                                    <div class="p-3 rounded-xl bg-slate-900/90 border border-slate-800/80 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs">KG</div>
                                            <div>
                                                <div class="font-bold text-white">Karan Gill</div>
                                                <div class="text-[11px] text-slate-400">6-Month Plan &bull; Valid till Feb 2027</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-emerald-400 font-bold text-xs">GATE OPEN</div>
                                            <div class="text-[10px] text-slate-400 font-mono">Fingerprint &bull; 0.22s</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center justify-between text-xs text-slate-400">
                                <span>Hardware: Hikvision, eSSL, ZKTeco, Realtime, Mantra</span>
                                <a href="{{ route('features') }}" class="text-amber-400 hover:text-amber-300 font-semibold inline-flex items-center gap-1">
                                    Hardware setup guide &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         HARDWARE BRAND INTEGRATIONS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="border-y border-slate-800 bg-[#0a0e18] py-6 overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                <div class="text-center md:text-left">
                    <span class="text-xs uppercase font-bold tracking-wider text-slate-400">Works with your existing machines</span>
                    <p class="text-xs text-slate-500 mt-0.5">Runs on a local Windows PC. No static IP or cloud server needed at the gym.</p>
                </div>
                <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6 text-slate-300 font-bold text-xs sm:text-sm tracking-wide">
                    <span class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200">Hikvision MinMoe</span>
                    <span class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200">eSSL SilkBio &amp; MB20</span>
                    <span class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200">ZKTeco Face &amp; RFID</span>
                    <span class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200">Realtime</span>
                    <span class="px-3.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-slate-200">Tripod Turnstiles</span>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         CORE FEATURES
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">What It Does</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mt-2 mb-4">
                    Six tools every gym actually needs
                </h2>
                <p class="text-slate-400 text-base sm:text-lg">
                    No complicated setups. We built the exact features Indian gym owners asked for.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

                {{-- Feature 1: Biometric Turnstiles --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-amber-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-6 border border-amber-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11m0 0a8.003 8.003 0 0015.357 2m-1.357 5H18"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-amber-300 transition-colors">Biometric Gate Control</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Connect Face ID, fingerprint, or card readers to your turnstile. If a member hasn't paid, the gate stays locked.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Stops unpaid members without receptionist confrontation</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Gates keep opening even if gym internet disconnects</li>
                    </ul>
                </div>

                {{-- Feature 2: WhatsApp Auto Recovery --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-emerald-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-6 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-300 transition-colors">WhatsApp Fee Reminders</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Sends reminders 3 days before expiry, on the due date, and when overdue. Members tap the link to pay via GPay, PhonePe, or Paytm.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Money goes directly to your bank account with 0% cut</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> WhatsApp sends digital tax receipts automatically</li>
                    </ul>
                </div>

                {{-- Feature 3: Member Mobile App --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-blue-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-6 border border-blue-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-blue-300 transition-colors">Member Mobile Portal</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Members open your gym link on their phone and log in with their mobile number. No app store download required.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> QR code check-in via phone camera</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Monthly attendance streaks and badges to keep them coming</li>
                    </ul>
                </div>

                {{-- Feature 4: POS & Billing --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-purple-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-purple-500/10 text-purple-400 flex items-center justify-center mb-6 border border-purple-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-purple-300 transition-colors">GST Billing &amp; Split Payments</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Print or WhatsApp GST invoices. Accept split payments like part cash and part UPI in a single receipt.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Clean receipts with public verification QR links</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Front-desk supplement store billing with barcode scan</li>
                    </ul>
                </div>

                {{-- Feature 5: Multi-Branch Control --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-amber-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-6 border border-amber-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-amber-300 transition-colors">Multi-Branch Management</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Manage 1 branch or 20 branches under one owner account. Staff only see their own branch, while you see the full picture.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Compare collections and expenses across locations</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Support roaming memberships across branches</li>
                    </ul>
                </div>

                {{-- Feature 6: Workouts & Diet --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 hover:border-emerald-500/40 transition-all duration-300 group shadow-xl">
                    <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-6 border border-emerald-500/20 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2 group-hover:text-emerald-300 transition-colors">Workouts &amp; Diet Plans</h3>
                    <p class="text-sm text-slate-400 leading-relaxed mb-4">
                        Assign exercise routines and meal plans directly to member phones. Track personal training bookings and trainer commissions.
                    </p>
                    <ul class="space-y-2 text-xs text-slate-400">
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Log weight, chest, and waist measurements over time</li>
                        <li class="flex items-center gap-2"><svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Automatic commission calculation for personal trainers</li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         PRICING
    ══════════════════════════════════════════════════════════════ --}}
    <section id="plans" class="py-20 lg:py-28 bg-[#070b14] border-t border-slate-800/80" x-data="{ billingCycle: 'yearly' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Pricing</span>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mt-2 mb-4">
                    Simple pricing in rupees
                </h2>
                <p class="text-slate-400 text-base sm:text-lg mb-8">
                    Free up to 75 members. Upgrade when you need biometric turnstile sync. No per-member fees.
                </p>

                {{-- Billing Switcher --}}
                <div class="inline-flex items-center gap-2 p-1.5 rounded-2xl bg-slate-900 border border-slate-800">
                    <button type="button"
                            @click="billingCycle = 'monthly'"
                            :class="billingCycle === 'monthly' ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                            class="px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all">
                        Monthly
                    </button>
                    <button type="button"
                            @click="billingCycle = 'yearly'"
                            :class="billingCycle === 'yearly' ? 'bg-amber-500 text-slate-950 font-bold shadow-md' : 'text-slate-400 hover:text-white'"
                            class="px-5 py-2 rounded-xl text-xs sm:text-sm font-semibold transition-all flex items-center gap-1.5">
                        <span>Yearly</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-extrabold tracking-wide">SAVE 33%</span>
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 lg:gap-8 items-stretch max-w-6xl mx-auto">

                {{-- FREE FOREVER --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 flex flex-col justify-between shadow-xl">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Free Forever</span>
                        </div>
                        <div class="text-4xl font-extrabold text-white">₹0</div>
                        <div class="text-xs text-slate-400 mt-1 font-medium">Free forever &bull; Up to 75 members</div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            For personal trainers and small gyms just getting started.
                        </p>
                        <ul class="space-y-3 text-xs text-slate-300 my-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Up to 75 active members</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Membership plans and tax invoices</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Manual attendance and QR check-in</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Member portal on phone</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'free_forever']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center transition-colors">
                        Get Started Free
                    </a>
                </div>

                {{-- STARTER (HIGHLIGHTED) --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border-2 border-amber-500/80 flex flex-col justify-between relative shadow-2xl shadow-amber-500/10">
                    <div class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-amber-500 text-slate-950 text-[11px] font-black uppercase tracking-wider shadow-md">
                        Most Popular
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-amber-500/10 text-amber-400 text-xs font-bold uppercase tracking-wider">Starter</span>
                            <span class="text-xs text-amber-400 font-semibold" x-show="billingCycle === 'yearly'">Save ₹3,000/yr</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-extrabold text-white" x-text="billingCycle === 'yearly' ? '₹6,000' : '₹749'">₹6,000</span>
                            <span class="text-xs text-slate-400" x-text="billingCycle === 'yearly' ? '/year' : '/month'">/year</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">1 branch &bull; <strong>Unlimited Members</strong></div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            For gyms that want biometric turnstiles and WhatsApp fee recovery.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Free, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Unlimited members</strong></li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Biometric turnstile sync</strong> (Hikvision, eSSL, ZKTeco)</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>WhatsApp fee reminders</strong> + UPI pay links</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> GST billing, split payments, and front-desk POS</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'starter']) }}"
                       class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-sm text-center transition-all shadow-lg shadow-amber-500/20">
                        Choose Starter Plan
                    </a>
                </div>

                {{-- PRO CHAIN --}}
                <div class="p-8 rounded-3xl bg-[#0c121e] border border-slate-800 flex flex-col justify-between shadow-xl">
                    <div>
                        <div class="flex items-center justify-between mb-4">
                            <span class="px-3 py-1 rounded-md bg-slate-800 text-slate-300 text-xs font-bold uppercase tracking-wider">Pro Chain</span>
                            <span class="text-xs text-amber-400 font-semibold" x-show="billingCycle === 'yearly'">Save ₹6,000/yr</span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="text-4xl sm:text-5xl font-extrabold text-white" x-text="billingCycle === 'yearly' ? '₹12,000' : '₹1,499'">₹12,000</span>
                            <span class="text-xs text-slate-400" x-text="billingCycle === 'yearly' ? '/year' : '/month'">/year</span>
                        </div>
                        <div class="text-xs text-slate-400 mt-1">Multi-branch &bull; <strong>Central Command</strong></div>
                        <p class="text-xs text-slate-400 mt-3 pb-6 border-b border-slate-800 leading-relaxed">
                            For gym owners running multiple locations who need consolidated revenue tracking.
                        </p>
                        <div class="text-xs font-semibold text-white my-3">Everything in Starter, plus:</div>
                        <ul class="space-y-3 text-xs text-slate-300 mb-6">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Multi-branch owner dashboard</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Supplement store POS with barcode stock tracking</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Personal trainer payroll &amp; commission calculations</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Direct phone and WhatsApp onboarding support</li>
                        </ul>
                    </div>
                    <a href="{{ route('register', ['plan' => 'pro']) }}"
                       class="w-full py-3.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-sm text-center transition-colors">
                        Choose Pro Chain
                    </a>
                </div>

            </div>

            {{-- Guarantee --}}
            <div class="mt-12 text-center text-xs text-slate-400 flex flex-wrap items-center justify-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-semibold">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    <span>7-Day Full Money-Back Guarantee</span>
                </span>
                <span>•</span>
                <span>Immediate account activation</span>
                <span>•</span>
                <span>Pay securely via UPI, Cards, or NetBanking</span>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         TESTIMONIALS
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-[#090d16]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Gym Owners</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mt-2">
                    What gym founders say
                </h2>
                <p class="text-slate-400 text-sm mt-2">Over 40,000 member check-ins processed daily.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach([
                    [
                        'quote' => 'Setting up our Hikvision face terminals took under 15 minutes. Unpaid members can no longer walk past reception, and we recovered ₹45,000 in overdue fees in our first month.',
                        'name' => 'Rajesh Sharma',
                        'role' => 'Owner, PowerFit Studio, Delhi NCR',
                        'initials' => 'RS',
                    ],
                    [
                        'quote' => 'The WhatsApp reminders with UPI payment links saved us hours of daily phone calls. About 70% of our members now pay before their plan expires.',
                        'name' => 'Priya Nair',
                        'role' => 'Operations Manager, IronHouse Gym, Bengaluru',
                        'initials' => 'PN',
                    ],
                    [
                        'quote' => 'I run 3 branches. Seeing all collections in one place on my phone means I no longer need to call branch managers every evening for numbers.',
                        'name' => 'Vikram Desai',
                        'role' => 'Founder, FitZone Club, Mumbai',
                        'initials' => 'VD',
                    ],
                ] as $review)
                    <div class="p-7 rounded-3xl bg-[#0c121e] border border-slate-800 flex flex-col justify-between shadow-lg">
                        <div class="flex items-center gap-1 text-amber-400 mb-4">
                            @for($i=0; $i<5; $i++)
                                <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            @endfor
                        </div>
                        <p class="text-slate-300 text-sm leading-relaxed mb-6">"{{ $review['quote'] }}"</p>
                        <div class="flex items-center gap-3 pt-4 border-t border-slate-800/80">
                            <div class="w-10 h-10 rounded-full bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs border border-amber-500/20">
                                {{ $review['initials'] }}
                            </div>
                            <div>
                                <div class="text-white font-bold text-sm">{{ $review['name'] }}</div>
                                <div class="text-slate-400 text-xs">{{ $review['role'] }}</div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         FAQ
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 lg:py-28 bg-[#070b14] border-t border-slate-800/80">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-14">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-400">Questions &amp; Answers</span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-white tracking-tight mt-2">
                    Common questions
                </h2>
            </div>

            <div class="space-y-3" x-data="{ open: null }">
                @foreach([
                    ['q' => 'Is the Free Forever plan really free with no time limit?', 'a' => 'Yes. The Free Forever plan has no expiration date. You can manage up to 75 active members at zero cost. Upgrade only when you exceed 75 members or want biometric turnstile sync.'],
                    ['q' => 'Which biometric machines and turnstiles work with this?', 'a' => 'Hikvision (Face MinMoe series), eSSL (SilkBio, MB20, Identix, X990), ZKTeco, Realtime, Mantra USB thumb scanners, RFID cards, and standard tripod turnstiles.'],
                    ['q' => 'How do WhatsApp fee reminders work?', 'a' => 'The software tracks expiry dates. It sends an alert on WhatsApp 3 days before expiry, on the due date, and when overdue. Each message has your UPI link so members can pay via GPay, PhonePe, or Paytm. The payment goes directly to your bank account.'],
                    ['q' => 'Can I import my members from Excel?', 'a' => 'Yes. Upload your Excel or CSV file in one click. If you need help, our support team will import your data for you at no charge.'],
                    ['q' => 'Do my members have to install an app from the Play Store?', 'a' => 'No. They open your gym link on their mobile browser, log in with their mobile number, and can see their attendance, diet, workout routine, and check in via camera QR.'],
                ] as $i => $faq)
                    <div class="rounded-2xl bg-[#0c121e] border border-slate-800 overflow-hidden">
                        <button class="w-full flex items-center justify-between gap-4 px-6 py-4.5 text-left font-semibold text-white hover:text-amber-400 transition-colors focus:outline-none"
                                @click="open = open === {{ $i }} ? null : {{ $i }}">
                            <span class="text-sm sm:text-base">{{ $faq['q'] }}</span>
                            <svg class="w-5 h-5 shrink-0 text-slate-400 transition-transform duration-200"
                                 :class="open === {{ $i }} ? 'rotate-180 text-amber-400' : ''"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse x-cloak class="px-6 pb-5 pt-1 border-t border-slate-800/40">
                            <p class="text-slate-400 text-sm leading-relaxed">{{ $faq['a'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════════════════════════════
         BOTTOM CTA
    ══════════════════════════════════════════════════════════════ --}}
    <section class="py-20 bg-gradient-to-b from-[#090d16] to-[#0c121e] border-t border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-white tracking-tight mb-4">
                Ready to try it at your gym?
            </h2>
            <p class="text-slate-400 text-base sm:text-lg mb-8 max-w-2xl mx-auto">
                Set up takes less than two minutes. No credit card required.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-extrabold text-base transition-all shadow-xl shadow-amber-500/25">
                    Start Free (Up to 75 Members) &rarr;
                </a>
                <a href="{{ route('contact') }}"
                   class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-800 border border-slate-700 text-slate-200 font-semibold text-base hover:bg-slate-700 transition-all">
                    Talk to Hardware Specialist
                </a>
            </div>
        </div>
    </section>

</x-guest-layout>
