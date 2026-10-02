<x-guest-layout title="All Features — Gym Console | The Complete Gym Operating System">
    {{-- Hero Section --}}
    <section class="relative overflow-hidden bg-grid pt-16 pb-20 sm:pt-24 sm:pb-28 lg:pt-32 lg:pb-36">
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[900px] h-[450px] bg-gradient-to-b from-amber-500/15 via-orange-500/5 to-transparent rounded-full blur-3xl pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-amber-500/10 border border-amber-500/25 text-amber-300 text-xs sm:text-sm font-bold mb-8 backdrop-blur-md">
                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                <span>The Complete Gym Operating System</span>
            </div>

            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-black text-white tracking-tight leading-[1.15] max-w-5xl mx-auto">
                Engineered for Peak Performance, <br class="hidden sm:inline" />
                <span class="text-gradient">Built for Modern Gyms</span>
            </h1>

            <p class="mt-6 sm:mt-8 text-base sm:text-lg lg:text-xl text-slate-300 max-w-3xl mx-auto leading-relaxed">
                From sub-second biometric turnstiles to automated WhatsApp UPI renewals and branded mobile apps — explore every tool built to automate your operations and grow your revenue.
            </p>
        </div>
    </section>

    {{-- Comprehensive Features Deep Dive --}}
    <section class="py-16 pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-24">

            {{-- 1. Biometrics & Hardware Access Control --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
                        ⚡ Hardware Integration
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                        Biometric Turnstile & <br><span class="text-gradient">Face Recognition Access</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-base leading-relaxed">
                        Eliminate proxy attendance, member card sharing, and unauthorized entry. Gym Console features a direct hardware abstraction driver designed for Hikvision terminals and turnstiles.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Hikvision DS-K1T343EWX Driver:</strong> Sub-second face recognition & RFID check-in with live event webhooks.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Instant Expiry Gating:</strong> Automatically denies gate unlock if membership is expired or payment is pending.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-amber-500/20 text-amber-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Anti-Passback Enforcement:</strong> Prevents multiple entries on a single tap or member credential.</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl relative">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-4 mb-6">
                        <div class="flex items-center gap-3">
                            <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                            <span class="text-xs font-bold text-slate-300 uppercase tracking-wider">Live Biometric Feed</span>
                        </div>
                        <span class="text-xs font-semibold text-slate-500">Hikvision ISAPI v2.0</span>
                    </div>

                    <div class="space-y-3 text-xs font-mono">
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400">● [PASS]</span>
                                <span class="text-white">Rohit Sharma (MEM-089)</span>
                            </div>
                            <span class="text-slate-500">Turnstile #1 • 0.28s</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-rose-400">● [DENIED]</span>
                                <span class="text-white">Ananya Sen (Expired)</span>
                            </div>
                            <span class="text-rose-400/80">Turnstile #2 • Blocked</span>
                        </div>
                        <div class="p-3 rounded-xl bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400">● [PASS]</span>
                                <span class="text-white">Karan Gill (MEM-104)</span>
                            </div>
                            <span class="text-slate-500">Face Scan #1 • 0.31s</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Multi-Branch Enterprise Consolidation --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center lg:flex-row-reverse">
                <div class="lg:order-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/10 border border-orange-500/20 text-orange-400 text-xs font-bold uppercase tracking-wider mb-4">
                        🏢 Chain Management
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                        Multi-Branch Control from <br><span class="text-gradient">One Master Dashboard</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-base leading-relaxed">
                        Expand your fitness business across cities without losing visibility. Manage 1, 5, or 50 branches with aggregated revenues, cross-facility roaming, and isolated staff permissions.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Consolidated Analytics:</strong> Compare branch-wise revenue, active memberships, and footfall side-by-side.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Multi-Location Roaming:</strong> Allow VIP members to train at any branch while keeping billing centralized.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-orange-500/20 text-orange-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Branch-Scoped Staff:</strong> Receptionists and trainers only access data for their assigned facility.</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl lg:order-1">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Branch Performance Overview</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                            <div class="text-xs text-slate-400">Branch 01 — Downtown</div>
                            <div class="text-xl font-black text-white mt-1">₹4,85,000</div>
                            <div class="text-xs text-emerald-400 mt-1">▲ 420 Members</div>
                        </div>
                        <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800">
                            <div class="text-xs text-slate-400">Branch 02 — Westside</div>
                            <div class="text-xl font-black text-white mt-1">₹3,20,000</div>
                            <div class="text-xs text-emerald-400 mt-1">▲ 290 Members</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. WhatsApp Automation & GST Billing --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-4">
                        💬 Payments & Messaging
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                        Automated WhatsApp Reminders <br><span class="text-gradient">& GST Invoicing</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-base leading-relaxed">
                        Never chase payments manually again. Send automated WhatsApp payment reminders with instant UPI payment links 7 days, 3 days, and on the day of expiry.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">1-Click UPI Payment Links:</strong> Members receive reminders with dynamic UPI QR codes and pay in seconds.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">GST-Compliant Tax Invoices:</strong> Instant PDF invoices generated with HSN/SAC codes and tax breakdown.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">POS & Expense Tracker:</strong> Record supplement store sales, PT packages, and operational overheads.</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl space-y-4">
                    <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs leading-relaxed">
                        <div class="font-bold text-sm text-emerald-400 mb-1">📱 Automated WhatsApp Trigger</div>
                        "Hi Sameer, your Gold Plan at Gym Console expires in 3 days. Tap here to renew with 1-click UPI: <span class="underline font-mono">pay.gymconsole.in/r/98x7</span>"
                    </div>
                    <div class="p-4 rounded-2xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-white">Invoice #INV-2026-0891</div>
                            <div class="text-[11px] text-slate-500">GST 18% Included • Paid via UPI</div>
                        </div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-400 text-xs font-bold">Paid ₹9,440</span>
                    </div>
                </div>
            </div>

            {{-- 4. Mobile Apps, Workout & Diet Builder --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center lg:flex-row-reverse">
                <div class="lg:order-2">
                    <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-wider mb-4">
                        📱 Member Experience
                    </span>
                    <h2 class="text-3xl sm:text-4xl font-black text-white leading-tight">
                        Native Mobile Apps, <br><span class="text-gradient">600+ Exercises & Diet Plans</span>
                    </h2>
                    <p class="mt-4 text-slate-300 text-base leading-relaxed">
                        Equip your members with a native Android & iOS app. Let them view customized workout routines with animated videos, track daily calories/macros, and generate turnstile QR codes.
                    </p>

                    <div class="mt-6 space-y-3">
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">600+ Exercise Video Library:</strong> Form guides, muscle group tagging, sets, reps, and rest timers.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Diet & Nutrition Calculator:</strong> Calculate BMR/TDEE and assign customized macro goals (Proteins, Carbs, Fats).</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <span class="w-5 h-5 rounded-full bg-blue-500/20 text-blue-400 flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">✓</span>
                            <p class="text-sm text-slate-300"><strong class="text-white">Branded White-Label App:</strong> Publish the member app under your gym’s exact logo and name on Play Store & App Store (Pro Plan).</p>
                        </div>
                    </div>
                </div>

                <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl lg:order-1 text-center">
                    <div class="inline-flex p-4 rounded-2xl bg-slate-950 border border-slate-800 mb-4">
                        <div class="text-left space-y-2">
                            <div class="text-xs font-bold text-amber-400 uppercase">Today's Workout Split</div>
                            <div class="text-sm font-black text-white">Chest & Triceps Hypertrophy</div>
                            <div class="text-xs text-slate-400">5 Exercises • 18 Sets • 45 Mins</div>
                        </div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="text-slate-500">Protein</div>
                            <div class="font-bold text-white mt-0.5">165g</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="text-slate-500">Carbs</div>
                            <div class="font-bold text-white mt-0.5">220g</div>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="text-slate-500">Fats</div>
                            <div class="font-bold text-white mt-0.5">55g</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {{-- Bottom CTA --}}
    <section class="py-20 bg-slate-900/50 border-t border-slate-800/80">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">Experience Gym Console in Action</h2>
            <p class="mt-4 text-slate-300 text-base sm:text-lg max-w-2xl mx-auto">
                Start with our Free Forever tier or buy a high-performance license with full hardware support.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-base hover:brightness-110 shadow-xl shadow-amber-500/20 transition-all">
                    Start 14-Day Free Trial
                </a>
                <a href="{{ route('pricing') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-950 border border-slate-800 text-white font-bold text-base hover:bg-slate-900 transition-all">
                    Explore Pricing Plans
                </a>
            </div>
        </div>
    </section>
</x-guest-layout>
