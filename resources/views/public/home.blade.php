<x-guest-layout title="Gym Console - Modern Multi-Tenant Gym Management SaaS">
    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-16 pb-24 lg:pt-24 lg:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-semibold mb-8">
                    <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                    Multi-Tenant SaaS • Hikvision Biometrics • Flutter Mobile App
                </div>
                <h1 class="text-4xl sm:text-6xl font-extrabold tracking-tight text-white leading-tight">
                    Scale Your Gym Empire with <span class="text-transparent bg-clip-text bg-gradient-to-r from-amber-400 via-orange-400 to-amber-200">Ultimate Precision</span>
                </h1>
                <p class="mt-6 text-lg text-slate-400 leading-relaxed">
                    The all-in-one multi-branch fitness management platform. Automated memberships, real-time Hikvision turnstile biometric access control, workout & diet builders, financial POS, and mobile apps.
                </p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:brightness-110 shadow-xl shadow-orange-500/20 transition-all text-center">
                        Start 14-Day Free Trial
                    </a>
                    <a href="#plans" class="w-full sm:w-auto px-8 py-4 rounded-xl bg-slate-900 border border-slate-800 text-slate-200 font-semibold hover:bg-slate-800 transition-all text-center">
                        View SaaS Pricing
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Key Feature Highlights Grid -->
    <section class="py-20 bg-slate-900/50 border-y border-slate-800/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs uppercase font-bold tracking-widest text-amber-400">Engineered for Fitness Businesses</h2>
                <p class="mt-2 text-3xl font-bold text-white">Everything needed to run 1 or 50 gym branches</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <!-- Card 1 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Multi-Tenant & Multi-Branch</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Each gym business has strictly isolated data, role-based staff permissions, and unified management across multiple facility branches.</p>
                </div>

                <!-- Card 2 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-orange-500/10 text-orange-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 004 11a7.96 7.96 0 001.328 4.417m11.536-1.077a9 9 0 01-1.364 3.79"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Hikvision & IoT Biometrics</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">Automatic turnstile gate unlocking, facial recognition scanners, RFID check-in, and instant anti-passback access denial for expired members.</p>
                </div>

                <!-- Card 3 -->
                <div class="p-8 rounded-2xl bg-slate-950/70 border border-slate-800 hover:border-amber-500/40 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Flutter Mobile API (/api/v1)</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">High-performance Laravel REST API with Sanctum tokens. Members can generate dynamic turnstile QR codes, check routines, and log workouts.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Grid -->
    <section id="plans" class="py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16">
                <h2 class="text-xs uppercase font-bold tracking-widest text-amber-400">Transparent SaaS Pricing</h2>
                <p class="mt-2 text-3xl font-bold text-white">Choose the plan that fits your gym's growth</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                @foreach($plans as $plan)
                    <div class="relative flex flex-col p-8 rounded-3xl bg-slate-900/80 border {{ $plan->is_popular ? 'border-amber-500 shadow-2xl shadow-amber-500/10' : 'border-slate-800' }}">
                        @if($plan->is_popular)
                            <span class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold text-xs uppercase tracking-wider shadow-md">Most Popular</span>
                        @endif

                        <h3 class="text-2xl font-bold text-white">{{ $plan->name }}</h3>
                        <p class="mt-2 text-sm text-slate-400 min-h-[40px]">{{ $plan->description }}</p>

                        <div class="my-6">
                            <div class="flex items-baseline gap-1">
                                <span class="text-4xl font-extrabold text-white">₹{{ number_format($plan->price_monthly, 0) }}</span>
                                <span class="text-slate-400 text-sm">/ month</span>
                            </div>
                            <span class="text-xs text-amber-400/80">or ₹{{ number_format($plan->price_yearly, 0) }}/yr (Billed annually)</span>
                        </div>

                        <!-- Quota overview -->
                        <div class="p-4 rounded-xl bg-slate-950/60 border border-slate-800 mb-6 space-y-2 text-xs text-slate-300">
                            <div class="flex justify-between">
                                <span class="text-slate-400">Members:</span>
                                <span class="font-bold text-white">{{ $plan->member_limit === -1 ? 'Unlimited' : $plan->member_limit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Branches:</span>
                                <span class="font-bold text-white">{{ $plan->branch_limit }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-400">Staff Accounts:</span>
                                <span class="font-bold text-white">{{ $plan->staff_limit }}</span>
                            </div>
                        </div>

                        <!-- Feature list -->
                        <ul class="space-y-3 text-sm text-slate-300 flex-grow mb-8">
                            @foreach($plan->features as $feature)
                                <li class="flex items-center gap-2.5">
                                    <svg class="w-4 h-4 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $feature->name }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ route('register', ['plan' => $plan->slug]) }}" class="w-full py-3.5 rounded-xl text-center font-bold transition-all {{ $plan->is_popular ? 'bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 hover:brightness-110 shadow-lg shadow-orange-500/20' : 'bg-slate-800 hover:bg-slate-700 text-white' }}">
                            Select {{ $plan->name }}
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
</x-guest-layout>

