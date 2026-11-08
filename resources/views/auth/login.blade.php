<x-guest-layout :hide-nav="true" :hide-footer="true" title="Sign In — {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    <div class="min-h-screen flex flex-col justify-between items-center py-6 sm:py-10 px-4 relative bg-[#090d16] text-slate-100 selection:bg-amber-500/25 selection:text-amber-200">

        <!-- Top Header Navigation -->
        <header class="w-full max-w-[420px] flex items-center justify-between text-xs py-2">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-slate-400 hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to website</span>
            </a>
            <a href="{{ route('contact') }}" class="text-slate-400 hover:text-white transition-colors">
                Need help?
            </a>
        </header>

        <!-- Centered Login Card -->
        <main class="w-full max-w-[420px] my-auto py-6" x-data="{ 
            showPassword: false, 
            submitting: false
        }">
            <div class="p-7 sm:p-8 rounded-2xl bg-[#0c121e] border border-slate-800 shadow-xl space-y-6">
                
                <!-- Logo & Heading -->
                <div class="text-center space-y-2">
                    <a href="{{ route('home') }}" class="inline-flex items-center justify-center mb-1 group focus:outline-none">
                        <img src="{{ asset('images/gymdeskhub_horizontal_dark.png') }}"
                             alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}"
                             class="h-9 sm:h-10 w-auto object-contain transition-opacity group-hover:opacity-90">
                    </a>

                    <div>
                        <h1 class="text-2xl font-bold text-white tracking-tight">
                            Sign In
                        </h1>
                        <p class="text-xs sm:text-sm text-slate-400 mt-1">
                            Enter your credentials to access your account
                        </p>
                    </div>
                </div>

                <!-- Error Alert -->
                @if ($errors->any())
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-300 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span class="font-medium leading-relaxed">{{ $errors->first() }}</span>
                    </div>
                @endif

                @if (session('status'))
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-300 text-xs flex items-start gap-2.5">
                        <svg class="w-4 h-4 shrink-0 mt-0.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="font-medium leading-relaxed">{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Credentials Form -->
                <form action="{{ route('login.post') }}" method="POST" @submit="submitting = true" class="space-y-4">
                    @csrf

                    <!-- Identifier Input -->
                    <div class="space-y-1.5">
                        <label for="email" class="block text-xs font-medium text-slate-300">
                            Email, Mobile or Member ID
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
                                   placeholder="name@example.com or mobile number" 
                                   class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:border-amber-500 transition-colors">
                        </div>
                    </div>

                    <!-- Password Input -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-xs font-medium text-slate-300">
                                Password
                            </label>
                            <a href="{{ route('contact') }}" class="text-xs text-amber-400 hover:underline transition-colors">
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
                                   class="w-full pl-10 pr-10 py-2.5 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 text-xs sm:text-sm focus:outline-none focus:border-amber-500 transition-colors">
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
                            <span>Keep me signed in</span>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" 
                                :disabled="submitting"
                                class="w-full py-2.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-sm transition-colors cursor-pointer flex items-center justify-center gap-2">
                            <span x-show="submitting" class="animate-spin text-sm">⏳</span>
                            <span x-show="!submitting">Sign In</span>
                        </button>
                    </div>
                </form>

                <!-- Help / Registration Notice -->
                <div class="pt-4 border-t border-slate-800 text-center">
                    <p class="text-xs text-slate-400">
                        Don't have an account yet? 
                        <a href="{{ route('register') }}" class="text-amber-400 font-semibold hover:underline ml-1">
                            Get started free &rarr;
                        </a>
                    </p>
                </div>
            </div>
        </main>

        <!-- Minimal Footer -->
        <footer class="w-full max-w-[420px] text-center text-[11px] text-slate-500 py-3">
            <span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' ' . ($platformSettings['app_name'] ?? 'GymDeskHub')) }}</span>
        </footer>
    </div>
</x-guest-layout>
