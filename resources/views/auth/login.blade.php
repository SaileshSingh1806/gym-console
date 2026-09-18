<x-guest-layout title="Sign In - {{ $platformSettings['app_name'] ?? 'Gym Console' }}">
    <div class="py-16 sm:py-24 flex items-center justify-center px-4 sm:px-6 relative">
        <!-- Subtle Ambient Background Glow -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
            <div class="w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl -top-20 -left-20"></div>
            <div class="w-[400px] h-[400px] bg-orange-600/10 rounded-full blur-3xl -bottom-20 -right-20"></div>
        </div>

        <div class="w-full max-w-md p-8 sm:p-10 rounded-3xl bg-slate-900/90 backdrop-blur-xl border border-slate-800 shadow-2xl shadow-black/60 relative z-10" x-data="{ showPassword: false }">
            <!-- Brand Logo & Header -->
            <div class="text-center mb-8">
                @if(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="w-16 h-16 rounded-2xl object-contain bg-slate-950 p-2 border border-slate-800 mx-auto mb-4 shadow-xl">
                @else
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-500 text-slate-950 font-black flex items-center justify-center mx-auto mb-4 shadow-lg shadow-orange-500/20">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                @endif
                <h2 class="text-2xl font-black text-white tracking-tight">Sign in to {{ $platformSettings['app_name'] ?? 'Gym Console' }}</h2>
                <p class="text-xs text-slate-400 mt-1.5 font-medium">Enter your gym staff, owner, or platform administrator credentials</p>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs mb-6 flex items-start gap-2.5">
                    <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">Email Address</label>
                    <div class="relative">
                        <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="name@yourgym.com" 
                               class="w-full pl-10 pr-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider">Password</label>
                    </div>
                    <div class="relative">
                        <input :type="showPassword ? 'text' : 'password'" name="password" required placeholder="••••••••••••" 
                               class="w-full pl-10 pr-10 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                        <svg class="w-4 h-4 text-slate-500 absolute left-3.5 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-3 text-slate-400 hover:text-white text-xs cursor-pointer">
                            <span x-show="!showPassword">👁️</span>
                            <span x-show="showPassword">🙈</span>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between py-1.5">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0 cursor-pointer">
                        <span>Remember me on this device</span>
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-extrabold text-xs shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span>Sign In to Console</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <div class="mt-8 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
                <span>Don't have a gym account yet?</span>
                <a href="{{ route('register') }}" class="text-amber-400 font-bold hover:underline ml-1">Create gym account &rarr;</a>
            </div>
        </div>
    </div>
</x-guest-layout>
