<x-guest-layout :hide-nav="true" :hide-footer="true" title="Set Your Member Portal Password — {{ $tenant->name ?? 'GymDeskHub' }}">
    <div class="min-h-screen flex flex-col justify-between items-center py-8 sm:py-12 px-4 relative overflow-hidden bg-[#080c14] text-slate-100 selection:bg-emerald-500/25 selection:text-emerald-200">
        
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 bg-subtle-grid opacity-20 pointer-events-none"></div>
        <div class="absolute w-[500px] h-[500px] bg-emerald-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2 pointer-events-none"></div>

        <!-- Top Header Navigation -->
        <header class="w-full max-w-md flex items-center justify-between relative z-10 text-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>GymDeskHub</span>
            </a>

            <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition-colors font-medium">
                Back to Login &rarr;
            </a>
        </header>

        <!-- Centered Card -->
        <main class="w-full max-w-[420px] my-auto py-6 relative z-10" x-data="{ showPass: false, showConfirm: false, submitting: false }">
            <div class="p-6 sm:p-9 rounded-3xl bg-[#0c1220]/95 border border-slate-800/90 shadow-2xl shadow-black/80 backdrop-blur-xl space-y-6">
                
                <!-- Gym Branding -->
                <div class="text-center space-y-3">
                    @if(!empty($tenant->logo_url))
                        <img src="{{ asset('storage/' . $tenant->logo_url) }}" alt="{{ $tenant->name }}" class="h-14 sm:h-16 w-auto max-w-[200px] mx-auto object-contain drop-shadow-md">
                    @else
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-emerald-500 to-teal-600 flex items-center justify-center text-slate-950 font-black text-2xl mx-auto shadow-lg shadow-emerald-500/20">
                            {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                        </div>
                    @endif

                    <div>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[11px] font-black uppercase tracking-wider">
                            🔐 Member Portal Setup
                        </span>
                        <h1 class="text-2xl font-black text-white tracking-tight mt-2">
                            Set Your Password
                        </h1>
                        <p class="text-xs text-slate-400 mt-1">
                            Welcome, <strong class="text-white">{{ $member->full_name }}</strong>! Choose a confidential password that only you will know.
                        </p>
                    </div>
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed">{{ $errors->first() }}</span>
                    </div>
                @endif

                <!-- Password Form -->
                <form action="{{ route('portal.setup.save', ['token' => $token]) }}" method="POST" @submit="submitting = true" class="space-y-4 pt-1">
                    @csrf

                    <!-- New Password Input -->
                    <div class="space-y-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-300">
                            New Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPass ? 'text' : 'password'" 
                                   id="password"
                                   name="password" 
                                   required 
                                   minlength="6"
                                   placeholder="Create your new password (min. 6 characters)" 
                                   class="w-full pl-10 pr-10 py-3 rounded-xl bg-[#080d17] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 transition-all font-medium">
                            <button type="button" 
                                    @click="showPass = !showPass" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                                    title="Toggle password visibility">
                                <svg x-show="!showPass" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Confirm Password Input -->
                    <div class="space-y-1.5">
                        <label for="password_confirmation" class="block text-xs font-semibold text-slate-300">
                            Confirm Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <input :type="showConfirm ? 'text' : 'password'" 
                                   id="password_confirmation"
                                   name="password_confirmation" 
                                   required 
                                   minlength="6"
                                   placeholder="Re-enter your new password" 
                                   class="w-full pl-10 pr-10 py-3 rounded-xl bg-[#080d17] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/30 transition-all font-medium">
                            <button type="button" 
                                    @click="showConfirm = !showConfirm" 
                                    class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 transition-colors cursor-pointer"
                                    title="Toggle password visibility">
                                <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Security Note -->
                    <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 text-[11px] text-slate-400 space-y-1">
                        <div class="flex items-center gap-1.5 text-emerald-400 font-semibold">
                            <span>🛡️ Privacy Protected</span>
                        </div>
                        <p>After saving, only you will be able to access your Member Portal using this password.</p>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" 
                            :disabled="submitting"
                            class="w-full py-3.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:brightness-110 active:scale-[0.99] text-white font-black text-xs shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-2 mt-2">
                        <span x-show="submitting" class="animate-spin text-sm">⏳</span>
                        <span x-show="!submitting">Save Password &amp; Open Portal &rarr;</span>
                    </button>
                </form>

                <!-- Back Link -->
                <div class="pt-3 border-t border-slate-800/80 text-center">
                    <a href="{{ route('login') }}" class="text-xs text-slate-400 hover:text-white transition-colors">
                        Already know your password? <span class="text-emerald-400 underline font-semibold">Sign in here</span>
                    </a>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="w-full max-w-md text-center text-[11px] text-slate-500 relative z-10 pt-2">
            <span>Powered by <strong>GymDeskHub</strong></span>
        </footer>

    </div>
</x-guest-layout>

