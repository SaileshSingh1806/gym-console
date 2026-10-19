<x-guest-layout :hide-nav="true" :hide-footer="true" title="Sign In — {{ $platformSettings['app_name'] ?? 'Gym Console' }}">
    <div class="min-h-screen flex flex-col lg:grid lg:grid-cols-12 bg-[#080c14] text-slate-100 selection:bg-amber-500/25 selection:text-amber-200">
        
        <!-- ═══════════════════════════════════════════════════
             LEFT BRAND & VALUE PROPOSITION COLUMN (Desktop)
        ═══════════════════════════════════════════════════ -->
        <aside class="hidden lg:flex lg:col-span-5 xl:col-span-5 flex-col justify-between p-10 xl:p-14 bg-gradient-to-b from-[#0c121e] via-[#090d16] to-[#060911] border-r border-slate-800/80 relative overflow-hidden">
            <!-- Subtle architectural background grid -->
            <div class="absolute inset-0 bg-subtle-grid opacity-25 pointer-events-none"></div>

            <!-- Top Brand Lockup -->
            <div class="relative z-10">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group focus:outline-none">
                    @if(!empty($platformSettings['logo_url']))
                        <img src="{{ $platformSettings['logo_url'] }}"
                             alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}"
                             class="h-10 w-10 rounded-xl object-contain border border-slate-800 bg-[#0f1624] p-1.5 shadow-md">
                    @else
                        <div class="w-10 h-10 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    @endif
                    <div>
                        <span class="text-lg font-bold tracking-tight text-white block leading-none">
                            {{ $platformSettings['app_name'] ?? 'Gym Console' }}
                        </span>
                        <span class="text-[10px] font-semibold text-slate-400 uppercase tracking-widest mt-1 block">
                            Enterprise Operating System
                        </span>
                    </div>
                </a>
            </div>

            <!-- Core Value & Features Showcase -->
            <div class="relative z-10 my-auto py-12">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-800/80 border border-slate-700/60 text-slate-300 text-xs font-semibold mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Multi-Tenant Operations Engine</span>
                </div>

                <h1 class="text-3xl xl:text-4xl font-extrabold text-white tracking-tight leading-snug">
                    Designed for fitness clubs that demand reliability and speed.
                </h1>

                <p class="text-sm text-slate-400 mt-4 leading-relaxed">
                    Manage biometric access gates, automated WhatsApp fee renewals, digital invoices, and member subscriptions — all from a single unified control room.
                </p>

                <!-- Value Proposition Highlights -->
                <div class="mt-8 space-y-4">
                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-800/90 border border-slate-700/80 text-amber-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-200">Biometric Attendance and Access</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Real-time turnstile verification, offline buffer logs, and anti-passback security.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-800/90 border border-slate-700/80 text-emerald-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/>
                            </svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-200">Automated WhatsApp &amp; UPI Reminders</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Automated membership expiry alerts, dynamic UPI payment links &amp; instant receipts.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-800/90 border border-slate-700/80 text-blue-400 flex items-center justify-center shrink-0 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <h4 class="text-xs font-bold text-slate-200">CRM, Leads & Instant Billing</h4>
                            <p class="text-[11px] text-slate-400 mt-0.5">Prospect lead pipeline, trial schedules, PT allocations, and GST invoices.</p>
                        </div>
                    </div>
                </div>

                <!-- Verified Gym Quote Card -->
                <div class="mt-8 p-4 rounded-xl bg-slate-900/60 border border-slate-800 text-xs text-slate-300">
                    <p class="text-slate-300 leading-relaxed italic">
                        "Automated renewal notifications and turnstile biometric gates eliminated front-desk queues completely across our club."
                    </p>
                    <div class="mt-3 flex items-center justify-between text-[11px] text-slate-400 font-semibold border-t border-slate-800/80 pt-2.5">
                        <span>Armour 24-7 Gym Operations</span>
                        <span class="text-emerald-400 font-bold flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Verified Tenant
                        </span>
                    </div>
                </div>
            </div>

            <!-- Bottom Security Badges -->
            <div class="relative z-10 pt-6 border-t border-slate-800/60 flex items-center justify-between text-[11px] text-slate-500 font-medium">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    256-Bit SSL Encryption
                </span>
                <span>•</span>
                <span>99.98% Service Uptime</span>
                <span>•</span>
                <span>Isolated Tenant Data</span>
            </div>
        </aside>

        <!-- ═══════════════════════════════════════════════════
             RIGHT AUTHENTICATION FORM COLUMN
        ═══════════════════════════════════════════════════ -->
        <main class="lg:col-span-7 xl:col-span-7 flex flex-col justify-between p-6 sm:p-10 lg:p-14 relative bg-[#080c14]">
            
            <!-- Top Navigation Bar inside Auth Shell -->
            <div class="flex items-center justify-between w-full max-w-md mx-auto lg:max-w-none">
                <!-- Back Link -->
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-400 hover:text-white transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Website</span>
                </a>

                <!-- Register Workspace CTA -->
                <div class="text-xs text-slate-400">
                    <span class="hidden sm:inline">New gym owner?</span>
                    <a href="{{ route('register') }}" class="font-bold text-amber-400 hover:text-amber-300 ml-1.5 transition-colors">
                        Create gym account &rarr;
                    </a>
                </div>
            </div>

            <!-- Centered Sign-In Box -->
            <div class="w-full max-w-md mx-auto my-auto py-8 sm:py-12" x-data="{ showPassword: false }">
                
                <!-- Mobile Only Logo Header -->
                <div class="lg:hidden text-center mb-6">
                    @if(!empty($platformSettings['logo_url']))
                        <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="h-12 w-12 rounded-xl object-contain border border-slate-800 bg-[#0f1624] p-1.5 shadow-md mx-auto mb-3">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 flex items-center justify-center font-black shadow-md mx-auto mb-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    @endif
                </div>

                <!-- Heading -->
                <div class="mb-8">
                    <h2 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">Sign in to console</h2>
                    <p class="text-xs sm:text-sm text-slate-400 mt-2 font-normal">
                        Access your club dashboard, staff accounts, or member portal.
                    </p>
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs mb-6 flex items-start gap-3">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed">{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs mb-6 flex items-start gap-3">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Credentials Form -->
                <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                    @csrf

                    <!-- Identifier Input -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                            Email, Mobile Number, or Member ID
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input type="text" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   placeholder="owner@gym.com or 9876543210" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#0b111e] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all font-medium">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-slate-300">
                                Password
                            </label>
                            <a href="{{ route('contact') }}" class="text-[11px] font-medium text-slate-400 hover:text-amber-400 transition-colors">
                                Forgot password?
                            </a>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" 
                                   name="password" 
                                   required 
                                   placeholder="Enter account password" 
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[#0b111e] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all font-medium">
                            <button type="button" 
                                    @click="showPassword = !showPassword" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                                    title="Toggle password visibility">
                                <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-0 cursor-pointer">
                            <span>Remember this device for 30 days</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-xs shadow-sm transition-all cursor-pointer flex items-center justify-center gap-2">
                            <span>Sign In to Console</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </form>

                <!-- Help / Support Notice -->
                <div class="mt-8 pt-6 border-t border-slate-800/80 text-center">
                    <p class="text-xs text-slate-400">
                        Don't have a gym workspace yet? 
                        <a href="{{ route('register') }}" class="text-amber-400 font-bold hover:underline ml-1">
                            Start 14-day free trial &rarr;
                        </a>
                    </p>
                    <p class="text-[11px] text-slate-500 mt-3">
                        Super Admins, Gym Owners, Trainers &amp; Members all sign in with their assigned credentials.
                    </p>
                </div>
            </div>

            <!-- Bottom Legal & Protection Footer -->
            <div class="w-full max-w-md mx-auto lg:max-w-none pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500 border-t border-slate-800/60">
                <span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'Gym Console') . '. All rights reserved.') }}</span>
                <div class="flex items-center gap-3">
                    <a href="{{ route('about') }}" class="hover:text-slate-400 transition-colors">Privacy</a>
                    <span>•</span>
                    <a href="{{ route('contact') }}" class="hover:text-slate-400 transition-colors">Support Desk</a>
                </div>
            </div>
        </main>

    </div>
</x-guest-layout>
