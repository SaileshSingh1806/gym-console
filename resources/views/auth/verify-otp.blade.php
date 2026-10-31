<x-guest-layout title="Verify Email - {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    <div class="py-10 sm:py-16 max-w-lg mx-auto px-4 sm:px-6 relative" 
         x-data="{
             otp: '{{ old('otp', '') }}',
             remainingSeconds: {{ $remainingSeconds ?? 0 }},
             resending: false,
             timerInterval: null,
             init() {
                 if (this.remainingSeconds > 0) {
                     this.timerInterval = setInterval(() => {
                         if (this.remainingSeconds > 0) {
                             this.remainingSeconds--;
                         } else {
                             clearInterval(this.timerInterval);
                         }
                     }, 1000);
                 }
             }
         }">
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
            <div class="w-[450px] sm:w-[500px] h-[450px] sm:h-[500px] bg-amber-500/10 rounded-full blur-3xl -top-24 left-1/2 -translate-x-1/2"></div>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-2 sm:gap-3 mb-8 relative z-10 text-xs font-bold">
            <div class="flex items-center gap-1.5 text-emerald-400">
                <span class="w-5 h-5 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 flex items-center justify-center text-[10px] font-black">✓</span>
                <span class="hidden xs:inline">Details</span>
            </div>
            <div class="w-6 sm:w-10 h-0.5 bg-emerald-500/40"></div>
            <div class="flex items-center gap-1.5 text-amber-400">
                <span class="w-5 h-5 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-[10px] font-black">2</span>
                <span>Verify Email</span>
            </div>
            <div class="w-6 sm:w-10 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-1.5 text-slate-500">
                <span class="w-5 h-5 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-[10px] font-black">3</span>
                <span class="hidden xs:inline">Activation</span>
            </div>
        </div>

        <!-- Card Container -->
        <div class="p-6 sm:p-9 rounded-3xl bg-[#0c1220]/95 border border-slate-800/90 shadow-2xl shadow-black/80 backdrop-blur-xl relative z-10 space-y-6">
            
            <!-- Header Icon & Title -->
            <div class="text-center space-y-2">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center text-2xl shadow-lg shadow-amber-500/10">
                    ✉️
                </div>
                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">Check Your Inbox</h1>
                <p class="text-xs sm:text-sm text-slate-400 max-w-sm mx-auto leading-relaxed">
                    We've sent a 6-digit verification code to:
                    <span class="block font-bold text-amber-400 font-mono mt-1 text-sm sm:text-base break-all">{{ $email }}</span>
                </p>
            </div>

            <!-- Flash & Error Alerts -->
            @if (session('success'))
                <div class="p-3.5 rounded-2xl bg-emerald-500/10 border border-emerald-500/25 text-emerald-400 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if (session('warning'))
                <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/25 text-amber-400 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>{{ session('warning') }}</span>
                </div>
            @endif

            @if ($errors->has('otp'))
                <div class="p-3.5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ $errors->first('otp') }}</span>
                </div>
            @endif

            <!-- OTP Submission Form -->
            <form action="{{ route('register.verify-otp.post') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label for="otp_input" class="block text-center text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                        Enter 6-Digit Verification Code
                    </label>
                    <input type="text" 
                           id="otp_input"
                           name="otp" 
                           x-model="otp"
                           maxlength="6" 
                           inputmode="numeric" 
                           pattern="[0-9]*" 
                           autocomplete="one-time-code"
                           autofocus
                           required
                           placeholder="••••••"
                           class="w-full text-center text-3xl sm:text-4xl tracking-[0.5em] font-black font-mono py-3.5 px-4 rounded-2xl bg-slate-950 border border-slate-800 text-amber-400 placeholder-slate-700 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/30 focus:outline-none transition-all">
                </div>

                <button type="submit" 
                        class="w-full py-3.5 sm:py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-black text-sm shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span>Verify &amp; Continue</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>
            </form>

            <!-- Resend Section & Actions -->
            <div class="pt-4 border-t border-slate-800/80 flex flex-col items-center gap-3 text-xs">
                <div class="text-slate-400 text-center">
                    <span>Didn't receive the email? Check your spam folder or </span>
                    <form action="{{ route('register.resend-otp') }}" method="POST" class="inline" @submit="resending = true">
                        @csrf
                        <template x-if="remainingSeconds > 0">
                            <span class="text-slate-500 font-semibold">
                                Resend available in <span x-text="remainingSeconds" class="text-amber-400 font-bold font-mono"></span>s
                            </span>
                        </template>
                        <template x-if="remainingSeconds <= 0">
                            <button type="submit" 
                                    :disabled="resending"
                                    class="text-amber-400 hover:text-amber-300 font-bold underline cursor-pointer disabled:opacity-50">
                                <span x-show="!resending">Resend Code</span>
                                <span x-show="resending">Sending...</span>
                            </button>
                        </template>
                    </form>
                </div>

                <div class="mt-2">
                    <a href="{{ route('register') }}" class="text-slate-400 hover:text-white transition-colors flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>Change Email / Edit Details</span>
                    </a>
                </div>
            </div>

        </div>

        <div class="mt-8 text-center text-xs text-slate-500 relative z-10">
            <span>Need help? Contact our team at </span>
            <a href="mailto:{{ $platformSettings['support_email'] ?? 'support@gymdeskhub.com' }}" class="text-slate-400 underline hover:text-amber-400">{{ $platformSettings['support_email'] ?? 'support@gymdeskhub.com' }}</a>
        </div>
    </div>
</x-guest-layout>

