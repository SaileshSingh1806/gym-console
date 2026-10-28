<x-guest-layout :hide-nav="true" :hide-footer="true" title="Sign In — {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    <div class="min-h-screen flex flex-col justify-between items-center py-6 sm:py-10 px-4 relative overflow-hidden bg-[#080c14] text-slate-100 selection:bg-amber-500/25 selection:text-amber-200">
        
        <!-- Ambient Background Glow & Subtle Grid -->
        <div class="absolute inset-0 bg-subtle-grid opacity-20 pointer-events-none"></div>
        <div class="absolute w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2 pointer-events-none"></div>

        <!-- Top Header Navigation -->
        <header class="w-full max-w-md flex items-center justify-between relative z-10 text-xs">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to website</span>
            </a>

            <a href="{{ route('register') }}" class="text-slate-400 hover:text-amber-400 transition-colors font-medium">
                Need an account? <span class="font-bold text-amber-400 hover:underline">Register &rarr;</span>
            </a>
        </header>

        <!-- Centered Card -->
        <main class="w-full max-w-[420px] my-auto py-6 relative z-10" x-data="{ showPassword: false, submitting: false }">
            <div class="p-7 sm:p-9 rounded-3xl bg-[#0c1220]/95 border border-slate-800/90 shadow-2xl shadow-black/80 backdrop-blur-xl space-y-6">
                
                <!-- Logo & Heading -->
                <div class="text-center space-y-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-1 group focus:outline-none">
                        @if(!empty($platformSettings['logo_url']))
                            <img src="{{ $platformSettings['logo_url'] }}"
                                 alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                                 class="h-14 sm:h-16 w-auto max-w-[200px] rounded-2xl object-contain border border-slate-800 bg-[#0f1624] p-2 shadow-lg group-hover:scale-105 transition-transform">
                        @else
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-tr from-amber-500 to-amber-400 text-slate-950 flex items-center justify-center font-black shadow-lg shadow-amber-500/20 group-hover:scale-105 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                        @endif
                    </a>

                    <div>
                        <h1 class="text-2xl font-black text-white tracking-tight">
                            Sign in to your account
                        </h1>
                        <p class="text-xs text-slate-400 mt-1">
                            Enter your credentials to access your console
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

                @if (session('status'))
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Credentials Form -->
                <form action="{{ route('login.post') }}" method="POST" @submit="submitting = true" class="space-y-4 pt-1">
                    @csrf

                    <!-- Identifier Input -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-semibold text-slate-300">
                            Email, Mobile Number, or Member ID
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                            </div>
                            <input type="text" 
                                   id="email"
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required 
                                   autofocus 
                                   placeholder="name@example.com or phone" 
                                   class="w-full pl-10 pr-4 py-3 rounded-xl bg-[#080d17] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all font-medium">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-xs font-semibold text-slate-300">
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
                                   id="password"
                                   name="password" 
                                   required 
                                   placeholder="Enter your password" 
                                   class="w-full pl-10 pr-10 py-3 rounded-xl bg-[#080d17] border border-slate-700/80 text-white placeholder-slate-500 text-xs focus:outline-none focus:border-amber-500 focus:ring-1 focus:ring-amber-500/30 transition-all font-medium">
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
                    <div class="flex items-center justify-between pt-0.5">
                        <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-none">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-0 cursor-pointer">
                            <span>Remember me</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="submitting"
                                class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-400 hover:to-amber-500 active:scale-[0.99] text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 transition-all cursor-pointer flex items-center justify-center gap-2">
                            <span x-show="submitting" class="animate-spin text-sm">⏳</span>
                            <span x-show="!submitting" class="flex items-center gap-2">
                                <span>Sign In to Console</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Help / Trial Notice -->
                <div class="pt-4 border-t border-slate-800/80 text-center">
                    <p class="text-xs text-slate-400">
                        Don't have a gym workspace yet? 
                        <a href="{{ route('register') }}" class="text-amber-400 font-bold hover:underline ml-1">
                            Start free trial &rarr;
                        </a>
                    </p>
                </div>
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="w-full max-w-md flex flex-col sm:flex-row items-center justify-between gap-2 text-[11px] text-slate-500 relative z-10 pt-2">
            <span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'GymDeskHub')) }}</span>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1">🔒 SSL Secured</span>
                <span>•</span>
                <a href="{{ route('contact') }}" class="hover:text-slate-400 transition-colors">Support</a>
            </div>
        </footer>

    </div>
</x-guest-layout>
