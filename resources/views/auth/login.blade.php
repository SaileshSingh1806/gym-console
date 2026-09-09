<x-guest-layout title="Sign In - Gym Console">
    <div class="py-16 flex items-center justify-center px-4">
        <div class="w-full max-w-md p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl shadow-black/50">
            <div class="text-center mb-8">
                @if(!empty($platformSettings['logo_url']))
                    <img src="{{ $platformSettings['logo_url'] }}" alt="{{ $platformSettings['app_name'] ?? 'Gym Console' }}" class="w-14 h-14 rounded-2xl object-contain bg-slate-950 p-1.5 border border-slate-800 mx-auto mb-4 shadow-lg">
                @else
                    <div class="w-12 h-12 rounded-xl bg-amber-500 text-slate-950 font-bold flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                @endif
                <h2 class="text-2xl font-bold text-white">Sign in to {{ $platformSettings['app_name'] ?? 'Gym Console' }}</h2>
                <p class="text-sm text-slate-400 mt-1">Enter your gym staff, trainer, or owner credentials</p>
            </div>

            @if ($errors->any())
                <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm mb-6">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <input type="email" name="email" value="{{ old('email', 'owner@powerhouse.com') }}" required autofocus class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Password</label>
                    </div>
                    <input type="password" name="password" value="password" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                </div>

                <div class="flex items-center justify-between py-1">
                    <label class="flex items-center gap-2 text-xs text-slate-400 cursor-pointer">
                        <input type="checkbox" name="remember" class="rounded bg-slate-950 border-slate-800 text-amber-500 focus:ring-0">
                        Remember me
                    </label>
                </div>

                <button type="submit" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:brightness-110 shadow-lg shadow-orange-500/20 transition-all text-sm">
                    Sign In
                </button>
            </form>

            <div class="mt-6 pt-6 border-t border-slate-800/80 text-center text-xs text-slate-400">
                <span>Demo Accounts:</span><br>
                <span class="text-amber-400">owner@powerhouse.com</span> (Enterprise) •
                <span class="text-amber-400">admin@gymconsole.com</span> (Super Admin)<br>
                Password: <code class="text-white">password</code>
            </div>

            <div class="mt-4 text-center text-sm text-slate-400">
                Don't have a gym account yet? <a href="{{ route('register') }}" class="text-amber-400 font-semibold hover:underline">Start free trial</a>
            </div>
        </div>
    </div>
</x-guest-layout>

