<x-guest-layout title="About Us — {{ ($platformSettings['app_name'] ?? 'Gym Console') }}">

    {{-- Hero Section --}}
    <section class="relative pt-12 pb-16 sm:pt-20 sm:pb-24 lg:pt-24 lg:pb-28">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-slate-800/80 border border-slate-700/80 text-slate-300 text-xs sm:text-sm font-medium mb-6">
                <span class="inline-block w-2 h-2 rounded-full bg-amber-400"></span>
                <span>Our Mission & Vision</span>
            </div>

            <h1 class="text-4xl sm:text-6xl font-bold text-white tracking-tight leading-[1.15] max-w-4xl mx-auto mb-6">
                Built by fitness founders, engineered for scale
            </h1>

            <p class="text-base sm:text-lg text-slate-400 max-w-3xl mx-auto leading-relaxed">
                Gym Console was built to replace clunky desktop software, fragmented paper logs, and unreliable offline turnstiles with a clean, dependable cloud operating system.
            </p>
        </div>
    </section>

    {{-- Stats Banner --}}
    <section class="border-y border-slate-800 bg-slate-900/40 py-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1">500+</div>
                    <div class="text-xs sm:text-sm font-medium text-slate-400">Gym Branches Powered</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1">100,000+</div>
                    <div class="text-xs sm:text-sm font-medium text-slate-400">Active Gym Members</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1">5M+</div>
                    <div class="text-xs sm:text-sm font-medium text-slate-400">Biometric Check-ins</div>
                </div>
                <div>
                    <div class="text-3xl sm:text-4xl font-bold text-white mb-1">99.98%</div>
                    <div class="text-xs sm:text-sm font-medium text-slate-400">Platform Uptime</div>
                </div>
            </div>
        </div>
    </section>

    {{-- The Problem & Our Story --}}
    <section class="py-16 lg:py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <div>
                    <span class="text-xs uppercase font-bold tracking-wider text-amber-400">The Problem We Solve</span>
                    <h2 class="mt-2 text-2xl sm:text-4xl font-bold text-white leading-tight mb-6">
                        Why legacy gym systems fail modern operators
                    </h2>
                    <div class="space-y-4 text-slate-300 text-sm sm:text-base leading-relaxed">
                        <p>
                            For years, gym owners have struggled with fragmented tools: paper registers that lose track of expired memberships, manual WhatsApp messages sent one by one, turnstile gates that require USB drives for offline sync, and error-prone Excel spreadsheets.
                        </p>
                        <p>
                            We built <strong class="text-white">Gym Console</strong> to eliminate this chaos. By combining universal biometric hardware integration (eSSL, Hikvision, Realtime, ZKTeco), automated WhatsApp fee collections, multi-branch synchronization, and native mobile apps into one unified platform, gym owners regain total control over their business.
                        </p>
                    </div>

                    <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-white font-bold text-base mb-1">Zero Revenue Leakage</div>
                            <p class="text-xs text-slate-400">Expired members are automatically blocked at the turnstile gate until payment is cleared.</p>
                        </div>
                        <div class="p-5 rounded-xl bg-slate-900 border border-slate-800">
                            <div class="text-white font-bold text-base mb-1">100% Cloud Synced</div>
                            <p class="text-xs text-slate-400">Review member attendance and branch collections live from any smartphone or laptop.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-2xl bg-slate-900 border border-slate-800 p-6 sm:p-8 shadow-xl space-y-6">
                    <h3 class="text-lg font-bold text-white">
                        Our Guiding Principles
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800/80">
                            <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">1</div>
                            <div>
                                <div class="text-sm font-bold text-white">Hardware Reliability First</div>
                                <div class="text-xs text-slate-400 mt-1">Biometric turnstiles, fingerprint readers, and Hikvision facial gates work seamlessly with sub-second response.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800/80">
                            <div class="w-7 h-7 rounded-lg bg-blue-500/10 text-blue-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">2</div>
                            <div>
                                <div class="text-sm font-bold text-white">Strict Tenant Isolation</div>
                                <div class="text-xs text-slate-400 mt-1">Every gym’s data, finances, and member records are isolated at the database level.</div>
                            </div>
                        </div>
                        <div class="flex items-start gap-4 p-4 rounded-xl bg-slate-950 border border-slate-800/80">
                            <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">3</div>
                            <div>
                                <div class="text-sm font-bold text-white">Simplicity & Speed</div>
                                <div class="text-xs text-slate-400 mt-1">No complex onboarding. Staff and trainers can learn the entire system in under 10 minutes.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- Architectural Highlights Grid --}}
    <section class="py-16 lg:py-24 bg-slate-900/40 border-y border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-14">
                <span class="text-xs uppercase font-bold tracking-wider text-amber-400">Engineering & Reliability</span>
                <h2 class="mt-2 text-2xl sm:text-4xl font-bold text-white">Built for high concurrency and uptime</h2>
                <p class="mt-3 text-slate-400 text-sm sm:text-base">Under the hood, Gym Console is powered by modern, battle-tested software architecture.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Universal Biometric Engine</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">Direct hardware integration layer for eSSL desktop middleware, Hikvision facial terminals, Realtime, ZKTeco, and turnstile relays.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-blue-500/10 text-blue-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Multi-Tenant Scoping</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">Database-level multi-tenant scoping ensures each gym’s financial statements, member lists, and audit logs remain strictly segregated.</p>
                </div>

                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-4">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-2">Native Mobile REST API</h3>
                    <p class="text-slate-400 text-xs sm:text-sm leading-relaxed">High-performance REST API powering native Android and iOS member apps with QR authentication, push notifications, and routine tracking.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA Banner --}}
    <section class="py-16 sm:py-20">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl font-bold text-white tracking-tight mb-4">Ready to elevate your gym operations?</h2>
            <p class="text-slate-400 text-base sm:text-lg max-w-2xl mx-auto mb-8">
                Get started with our Free Forever tier or talk to our hardware specialists.
            </p>
            <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-amber-500 text-slate-950 font-bold text-base hover:bg-amber-400 transition-all shadow-lg shadow-amber-500/20">
                    Start Free Account
                </a>
                <a href="{{ route('contact') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl bg-slate-800 border border-slate-700 text-white font-semibold text-base hover:bg-slate-700 transition-all">
                    Contact Sales & Support
                </a>
            </div>
        </div>
    </section>
</x-guest-layout>
