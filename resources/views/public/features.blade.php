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
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-4">
                        Hardware Integration
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight mb-4">
                        Biometric Turnstiles &amp; Face Recognition Access
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Eliminate proxy attendance, member card sharing, and unauthorized entry. Gym Console features a universal biometric integration engine compatible with eSSL, Hikvision, Realtime, ZKTeco, Mantra, facial scanners, thumb readers, and turnstile gates.
                    </p>

                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Multi-Brand Hardware Support:</strong> Sub-second face recognition (Hikvision/eSSL), fingerprint thumb scans (eSSL/ZKTeco/Realtime/Mantra), RFID cards, and QR scan check-in.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Instant Expiry Gating:</strong> Automatically denies gate unlock if membership is expired or payment is overdue.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Anti-Passback Enforcement:</strong> Prevents multiple entries on a single credential without an exit event.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3 mb-4">
                        <div class="flex items-center gap-2 text-xs font-bold text-white uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span> Live Biometric Stream
                        </div>
                        <span class="text-xs text-slate-400 font-mono">0.2s Latency</span>
                    </div>

                    <div class="space-y-2.5 text-xs font-mono">
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">[PASS]</span>
                                <span class="text-white">Rohit Sharma (MEM-089)</span>
                            </div>
                            <span class="text-slate-400">Turnstile #1 &bull; 0.28s</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-rose-400 font-bold">[DENIED]</span>
                                <span class="text-white">Ananya Sen (Expired)</span>
                            </div>
                            <span class="text-rose-400 font-sans">Gate Locked</span>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800/80 flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="text-emerald-400 font-bold">[PASS]</span>
                                <span class="text-white">Karan Gill (MEM-104)</span>
                            </div>
                            <span class="text-slate-400">Face Scan #1 &bull; 0.31s</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. Multi-Branch Enterprise Consolidation --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold uppercase tracking-wider mb-4">
                        Chain Management
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight mb-4">
                        Multi-Branch Control from One Master Dashboard
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Expand your fitness business across locations without losing visibility. Manage 1, 5, or 50 branches with aggregated revenues, cross-facility roaming, and isolated staff permissions.
                    </p>

                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Consolidated Analytics:</strong> Compare branch revenue, active memberships, and footfall side-by-side.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Multi-Location Roaming:</strong> Allow VIP members to train across branches while keeping billing centralized.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-blue-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Branch-Scoped Staff:</strong> Receptionists and trainers only access data for their assigned facility.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl lg:order-1">
                    <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4">Branch Performance Overview</div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="text-xs text-slate-400">Downtown Club</div>
                            <div class="text-xl font-bold text-white mt-1">₹4,85,000</div>
                            <div class="text-xs text-emerald-400 mt-1">420 Active Members</div>
                        </div>
                        <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                            <div class="text-xs text-slate-400">Westside Studio</div>
                            <div class="text-xl font-bold text-white mt-1">₹3,20,000</div>
                            <div class="text-xs text-emerald-400 mt-1">290 Active Members</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. WhatsApp Automation & GST Billing --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-4">
                        Payments & Messaging
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight mb-4">
                        Automated WhatsApp Reminders &amp; GST Invoicing
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Never chase payments manually again. Send automated WhatsApp payment reminders with instant UPI payment links 7 days, 3 days, and on the day of expiry.
                    </p>

                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">1-Click UPI Payment Links:</strong> Members receive reminders with dynamic UPI links and pay in seconds.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">GST-Compliant Tax Invoices:</strong> Instant PDF invoices generated with HSN/SAC codes and tax breakdown.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">POS & Expense Tracker:</strong> Record supplement store sales, PT packages, and operational overheads.</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl space-y-4">
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 text-xs leading-relaxed">
                        <div class="font-bold text-xs text-emerald-400 mb-1">Automated WhatsApp Reminder Preview</div>
                        <p class="text-slate-300">"Hi Sameer, your Gold Plan at Gym Console expires in 3 days. Tap here to renew with 1-click UPI: <span class="text-amber-400 underline">pay.gymconsole.in/r/98x7</span>"</p>
                    </div>
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 flex items-center justify-between">
                        <div>
                            <div class="text-xs font-bold text-white">Tax Invoice #INV-2026-0891</div>
                            <div class="text-[11px] text-slate-400">GST 18% Included &bull; Paid via UPI</div>
                        </div>
                        <span class="px-2.5 py-1 rounded-md bg-emerald-500/10 text-emerald-400 text-xs font-bold border border-emerald-500/20">Paid ₹9,440</span>
                    </div>
                </div>
            </div>

            {{-- 4. Mobile Apps, Workout & Diet Builder --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
                <div class="lg:order-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md bg-orange-500/10 border border-orange-500/20 text-orange-400 text-xs font-bold uppercase tracking-wider mb-4">
                        Member Experience
                    </div>
                    <h2 class="text-2xl sm:text-4xl font-bold text-white leading-tight mb-4">
                        Mobile Member App &amp; 600+ Exercise Library
                    </h2>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed mb-6">
                        Equip your members with a native Android & iOS app. Let them view customized workout routines with animated videos, track daily calories/macros, and generate turnstile QR codes.
                    </p>

                    <div class="space-y-3 text-sm text-slate-300">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">600+ Exercise Video Library:</strong> Form guides, muscle group tagging, sets, reps, and rest timers.</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Diet & Nutrition Calculator:</strong> Calculate BMR/TDEE and assign customized macro goals (Proteins, Carbs, Fats).</p>
                        </div>
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-orange-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <p><strong class="text-white">Branded White-Label App:</strong> Publish the member app under your gym’s exact logo and name on Play Store & App Store (Pro Plan).</p>
                        </div>
                    </div>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800 shadow-xl lg:order-1">
                    <div class="p-4 rounded-xl bg-slate-950 border border-slate-800 mb-4">
                        <div class="text-xs font-bold text-amber-400 uppercase tracking-wider">Assigned Workout Routine</div>
                        <div class="text-sm font-bold text-white mt-1">Chest &amp; Triceps Hypertrophy</div>
                        <div class="text-xs text-slate-400 mt-0.5">5 Exercises &bull; 18 Sets &bull; 45 Mins</div>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-center text-xs">
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                            <div class="text-slate-400 text-[11px]">Protein</div>
                            <div class="font-bold text-white mt-1">165g</div>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                            <div class="text-slate-400 text-[11px]">Carbs</div>
                            <div class="font-bold text-white mt-1">220g</div>
                        </div>
                        <div class="p-3 rounded-lg bg-slate-950 border border-slate-800">
                            <div class="text-slate-400 text-[11px]">Fats</div>
                            <div class="font-bold text-white mt-1">55g</div>
                        </div>
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
