<x-guest-layout title="Platform Features — {{ ($platformSettings['app_name'] ?? 'Gym Console') }}">

    {{-- Hero Section --}}
    <section class="relative pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                <span>The Complete Gym Operating System</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-bold text-white tracking-tight leading-[1.15] max-w-4xl mx-auto mb-6">
                Engineered for speed, reliability, and scale
            </h1>

            <p class="text-base sm:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
                Explore every tool built into Gym Console — from sub-second biometric turnstile synchronization to automated WhatsApp UPI fee collections and branded mobile apps.
            </p>
        </div>
    </section>

    {{-- Comprehensive Features Deep Dive --}}
    <section class="py-12 pb-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-20 lg:space-y-28">

            {{-- 1. Biometrics & Hardware Access Control --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                        <span>Access & Attendance</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight mb-4">
                        Biometric Attendance and Access Control
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Eliminate proxy attendance, member card sharing, and unpaid walk-ins. Gym Console connects directly with Hikvision facial terminals, eSSL fingerprint readers, and automated turnstile gates for sub-second, tamper-proof access.
                    </p>

                    <div class="space-y-4 text-sm text-slate-300">
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Multi-Device Compatibility:</strong> Sub-second face recognition (Hikvision MinMoe), fingerprint scanners (eSSL/ZKTeco), RFID smart cards, and digital QR codes.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Instant Expiry Gating:</strong> The turnstile gate locks instantly if a member's plan has expired or fees are pending — zero manual confrontation needed.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Anti-Passback Security:</strong> Prevents multiple people from entering on a single member's credentials without an exit punch.</p>
                        </div>
                    </div>
                </div>

                {{-- Live Biometric Visual Card --}}
                <div class="p-6 sm:p-7 rounded-3xl bg-[#0c121e]/90 border border-slate-800/80 shadow-2xl relative overflow-hidden group hover:border-amber-500/30 transition-all">
                    <div class="flex items-center justify-between border-b border-slate-800/80 pb-4 mb-4">
                        <div class="flex items-center gap-2.5 text-xs font-bold text-white uppercase tracking-wider">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            <span>Live Turnstile & Face Log</span>
                        </div>
                        <span class="text-xs text-amber-400 font-mono px-2.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20">0.2s Sync</span>
                    </div>

                    <div class="space-y-3 font-mono text-xs">
                        <div class="p-3.5 rounded-xl bg-[#080d17] border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 font-bold text-[11px]">ACCESS GRANTED</span>
                                <div>
                                    <span class="text-white font-sans font-semibold block text-sm">Rohit Sharma</span>
                                    <span class="text-slate-500 text-[11px]">Gold Annual &bull; MEM-089</span>
                                </div>
                            </div>
                            <span class="text-slate-400 font-sans text-xs">Turnstile Gate 1</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-rose-950/20 border border-rose-500/30 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded bg-rose-500/20 text-rose-400 font-bold text-[11px]">ACCESS DENIED</span>
                                <div>
                                    <span class="text-white font-sans font-semibold block text-sm">Ananya Sen</span>
                                    <span class="text-rose-400 text-[11px]">Plan Expired &bull; Locked</span>
                                </div>
                            </div>
                            <span class="text-rose-400 font-sans text-xs font-semibold">Turnstile Locked</span>
                        </div>

                        <div class="p-3.5 rounded-xl bg-[#080d17] border border-slate-800/80 flex items-center justify-between hover:border-slate-700 transition-colors">
                            <div class="flex items-center gap-3">
                                <span class="px-2 py-0.5 rounded bg-emerald-500/15 text-emerald-400 font-bold text-[11px]">ACCESS GRANTED</span>
                                <div>
                                    <span class="text-white font-sans font-semibold block text-sm">Karan Gill</span>
                                    <span class="text-slate-500 text-[11px]">Personal Training &bull; MEM-104</span>
                                </div>
                            </div>
                            <span class="text-slate-400 font-sans text-xs">Hikvision Face 1</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Multi-Branch Enterprise Consolidation --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-400"></span>
                        <span>Chain Operations</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight mb-4">
                        Multi-Branch Control from One Master Dashboard
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Expand your fitness brand across cities without losing visibility. Control 1, 5, or 20+ branches with consolidated financial statements, member roaming, and strict role permissions for receptionists and floor managers.
                    </p>

                    <div class="space-y-4 text-sm text-slate-300">
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Consolidated Analytics:</strong> Compare branch-by-branch revenues, membership counts, and daily attendance logs side-by-side.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Multi-Location Roaming:</strong> Enable VIP members to work out at any branch while revenue automatically credits to their home branch.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Isolated Staff Permissions:</strong> Front desk receptionists and trainers only see members and finances of their specific assigned facility.</p>
                        </div>
                    </div>
                </div>

                {{-- Multi-Branch Visual Card --}}
                <div class="p-6 sm:p-7 rounded-3xl bg-[#0c121e]/90 border border-slate-800/80 shadow-2xl lg:order-1 relative group hover:border-blue-500/30 transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-5">
                        <div class="text-xs font-bold text-white uppercase tracking-wider flex items-center gap-2">
                            <svg class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>Branch Performance Monitor</span>
                        </div>
                        <span class="text-xs text-slate-400 font-medium">All Branches Live</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                        <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/80">
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>Downtown Branch</span>
                                <span class="text-emerald-400 font-semibold">+18% MoM</span>
                            </div>
                            <div class="text-2xl font-extrabold text-white mt-1.5">₹4,85,000</div>
                            <div class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>420 Active Members</span>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/80">
                            <div class="flex items-center justify-between text-xs text-slate-400">
                                <span>Westside Studio</span>
                                <span class="text-emerald-400 font-semibold">+12% MoM</span>
                            </div>
                            <div class="text-2xl font-extrabold text-white mt-1.5">₹3,20,000</div>
                            <div class="text-xs text-slate-400 mt-2 flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                <span>290 Active Members</span>
                            </div>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-900/60 border border-slate-800/60 flex items-center justify-between text-xs">
                        <span class="text-slate-400">Consolidated Monthly Collection</span>
                        <span class="text-amber-400 font-extrabold text-sm font-mono">₹8,05,000</span>
                    </div>
                </div>
            </div>

            {{-- 3. WhatsApp Automation & GST Billing --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                        <span>Fee Automation & Billing</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight mb-4">
                        Automated WhatsApp Reminders &amp; GST Invoicing
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Never chase fee renewals manually again. Automatically deliver branded WhatsApp payment alerts with direct UPI pay links 7 days, 3 days, and on expiry day. Payments settle straight to your gym's account.
                    </p>

                    <div class="space-y-4 text-sm text-slate-300">
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">1-Click UPI Payment Links:</strong> Members tap the link inside WhatsApp, open GPay/PhonePe/Paytm, and pay immediately without front-desk friction.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">GST-Compliant Tax Invoices:</strong> Automated PDF invoices generated with SAC codes, gym GSTIN, tax breakdown, and instant WhatsApp delivery.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">POS & Expense Tracker:</strong> Log supplement shop inventory, personal training add-ons, and daily operational expenses in 1 dashboard.</p>
                        </div>
                    </div>
                </div>

                {{-- WhatsApp Simulation Card --}}
                <div class="p-6 sm:p-7 rounded-3xl bg-[#0c121e]/90 border border-slate-800/80 shadow-2xl relative group hover:border-emerald-500/30 transition-all">
                    <div class="flex items-center justify-between pb-4 border-b border-slate-800/80 mb-5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.77-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.006c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.274.072.376-.043c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.043.073.043.419-.101.824z"/></svg>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-white">WhatsApp Automated Reminder</div>
                                <div class="text-[11px] text-emerald-400 font-semibold">Delivered &bull; Active Notification</div>
                            </div>
                        </div>
                        <span class="text-xs text-slate-500">10:30 AM</span>
                    </div>

                    {{-- Chat bubble --}}
                    <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/80 mb-4 text-xs leading-relaxed">
                        <p class="text-slate-200">
                            "Hello <strong class="text-white">Sameer</strong>, your Gold Annual Membership at <strong class="text-amber-400">GymConsole</strong> expires in 3 days (21 Oct).
                        </p>
                        <p class="text-slate-300 mt-2">
                            To ensure continuous turnstile biometric access, click below to renew with 1-click UPI:
                        </p>
                        <div class="mt-3.5 p-2.5 rounded-xl bg-emerald-500/10 border border-emerald-500/25 flex items-center justify-between">
                            <span class="text-emerald-400 font-bold font-mono">₹14,999 (Inclusive GST)</span>
                            <span class="text-xs font-bold text-emerald-300 underline">Pay with UPI &rarr;</span>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-[#080d17] border border-slate-800/80 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-white">Invoice #INV-2026-0891</div>
                            <div class="text-[11px] text-slate-400">Instant GST Tax Receipt Attached</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-emerald-500/15 text-emerald-400 text-xs font-bold border border-emerald-500/30">Auto Paid</span>
                    </div>
                </div>
            </div>

            {{-- 4. Mobile Apps, Workout & Diet Builder --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/20 text-orange-400 text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                        <span>Member Experience</span>
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-extrabold text-white leading-tight mb-4">
                        Mobile Member App &amp; 600+ Exercise Library
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Provide members with a modern Android and iOS portal. Let them track workout splits with animated form videos, log calorie and macro targets, and view attendance history right on their smartphone.
                    </p>

                    <div class="space-y-4 text-sm text-slate-300">
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">600+ Exercise Library:</strong> Step-by-step form guides, targeted muscle groups, set counters, and rest interval timers.</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Personalized Diet & Macros:</strong> Calculate BMR/TDEE and assign customized daily macronutrient splits (Protein, Carbs, Fats).</p>
                        </div>
                        <div class="flex items-start gap-3.5 p-3 rounded-xl bg-slate-900/60 border border-slate-800/60">
                            <div class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-400 flex items-center justify-center shrink-0 mt-0.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <p><strong class="text-white">Digital QR Check-in:</strong> In facilities without hardware turnstiles, members scan dynamic QR codes to verify entrance.</p>
                        </div>
                    </div>
                </div>

                {{-- Member App Preview Card --}}
                <div class="p-6 sm:p-7 rounded-3xl bg-[#0c121e]/90 border border-slate-800/80 shadow-2xl lg:order-1 relative group hover:border-orange-500/30 transition-all">
                    <div class="p-4 rounded-2xl bg-[#080d17] border border-slate-800/80 mb-4">
                        <div class="flex items-center justify-between text-xs mb-1">
                            <span class="text-amber-400 font-bold uppercase tracking-wider">Today's Assigned Workout</span>
                            <span class="text-slate-400">Day 1 &bull; 45 Mins</span>
                        </div>
                        <div class="text-base font-bold text-white mt-1">Chest &amp; Triceps Hypertrophy</div>
                        <div class="text-xs text-slate-400 mt-1">Bench Press &bull; Incline DB Press &bull; Dips</div>
                    </div>

                    <div class="grid grid-cols-3 gap-2.5 text-center text-xs mb-4">
                        <div class="p-3 rounded-xl bg-[#080d17] border border-slate-800/80">
                            <div class="text-slate-400 text-[11px] font-medium">Protein</div>
                            <div class="font-bold text-white text-base mt-1">165g</div>
                            <div class="text-[10px] text-emerald-400 mt-0.5">Target Met</div>
                        </div>
                        <div class="p-3 rounded-xl bg-[#080d17] border border-slate-800/80">
                            <div class="text-slate-400 text-[11px] font-medium">Carbs</div>
                            <div class="font-bold text-white text-base mt-1">220g</div>
                            <div class="text-[10px] text-slate-400 mt-0.5">75% Done</div>
                        </div>
                        <div class="p-3 rounded-xl bg-[#080d17] border border-slate-800/80">
                            <div class="text-slate-400 text-[11px] font-medium">Fats</div>
                            <div class="font-bold text-white text-base mt-1">55g</div>
                            <div class="text-[10px] text-amber-400 mt-0.5">Within Limit</div>
                        </div>
                    </div>

                    <div class="p-3 rounded-xl bg-slate-900/60 border border-slate-800/60 flex items-center justify-between text-xs">
                        <span class="text-slate-300 font-medium">Monthly Attendance</span>
                        <span class="text-emerald-400 font-bold font-mono">22 / 26 Days (84%)</span>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="py-16 sm:py-20 bg-slate-900/40 border-t border-slate-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Experience Gym Console in Action</h2>
            <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto mb-8">
                Start with our Free Forever tier or buy a high-performance license with full hardware support.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-base hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20">
                    Start Free Forever
                </a>
                <a href="{{ route('pricing') }}" class="w-full sm:w-auto px-7 py-3.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-semibold text-base hover:bg-slate-700 transition-all">
                    Explore Pricing Plans
                </a>
            </div>
        </div>
    </section>
</x-guest-layout>
