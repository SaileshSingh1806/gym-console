<x-guest-layout title="Create Gym Account - {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    <div class="py-8 sm:py-14 max-w-5xl mx-auto px-3.5 sm:px-6 lg:px-8 relative" x-data="{ 
        planId: '{{ $selectedPlan?->id ?? 1 }}', 
        showPassword: false,
        showConfirmPassword: false
    }">
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
            <div class="w-[500px] sm:w-[600px] h-[500px] sm:h-[600px] bg-amber-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2"></div>
        </div>

        <!-- Header -->
        <div class="text-center mb-8 sm:mb-10 relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-[11px] sm:text-xs font-bold uppercase tracking-wider mb-3">
                <span>⚡ Instant Account Setup &bull; Annual Plan</span>
            </div>
            <h1 class="text-2xl sm:text-3xl md:text-4xl font-black text-white tracking-tight">Launch Your Gym Management Console</h1>
            <p class="text-xs sm:text-sm text-slate-400 mt-2 max-w-xl mx-auto px-2">Select your annual plan, set up your gym workspace, and start managing your fitness center seamlessly.</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-2 sm:gap-3 mb-6 sm:mb-8 relative z-10 text-xs font-bold">
            <div class="flex items-center gap-2 text-amber-400">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-black">1</span>
                <span>Select Plan &amp; Details</span>
            </div>
            <div class="w-6 sm:w-10 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-black">2</span>
                <span>Verify Email</span>
            </div>
            <div class="w-6 sm:w-10 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-black">3</span>
                <span>Activation</span>
            </div>
        </div>

        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs mb-6 sm:mb-8">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-6 sm:space-y-8 relative z-10">
            @csrf
            <!-- Strict Annual Billing Input -->
            <input type="hidden" name="billing_cycle" value="yearly">

            <!-- Plan Selection Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <div>
                        <h2 class="text-xs sm:text-sm font-extrabold text-white uppercase tracking-wider">1. Choose Subscription Plan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">All annual plans include biometric access control, member management, and diet planner</p>
                    </div>

                    <!-- Annual Billing Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Annual Billing</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-400 text-[10px] font-black uppercase">Best Value</span>
                    </div>
                </div>

                <!-- Plans Grid (Fully Responsive on Mobile/Tablet/Desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 sm:gap-5">
                    @foreach($plans as $plan)
                        <div @click="planId = '{{ $plan->id }}'"
                             :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-slate-900 shadow-xl shadow-amber-500/10 ring-1 ring-amber-500' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700'"
                             class="p-5 sm:p-6 rounded-2xl sm:rounded-3xl border-2 cursor-pointer transition-all relative flex flex-col justify-between group">
                            
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="planId" class="hidden">
                            
                            @if($plan->is_popular)
                                <div class="absolute -top-3 right-4 sm:right-5 px-3 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-[9px] sm:text-[10px] tracking-wider uppercase shadow-md">
                                    MOST POPULAR
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-black text-base sm:text-lg text-white group-hover:text-amber-400 transition-colors">{{ $plan->name }}</h3>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                         :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-amber-500' : 'border-slate-700'">
                                        <div class="w-2 h-2 rounded-full bg-slate-950" x-show="planId == '{{ $plan->id }}'"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-2xl sm:text-3xl font-black text-white">
                                        ₹{{ number_format($plan->price_yearly, 0) }}
                                        <span class="text-xs font-semibold text-slate-400">/ year</span>
                                    </div>
                                    @if($plan->price_yearly > 0)
                                        <span class="text-[11px] text-emerald-400 font-semibold block mt-1">
                                            ≈ ₹{{ number_format(round($plan->price_yearly / 12), 0) }} / month (Billed Annually)
                                        </span>
                                    @else
                                        <span class="text-[11px] text-emerald-400 font-semibold block mt-1">100% Free Forever</span>
                                    @endif
                                    <span class="text-[11px] text-slate-400 block mt-1 leading-relaxed">{{ $plan->description ?? 'Full operational suite' }}</span>
                                </div>

                                <div class="space-y-2 text-xs text-slate-300 border-t border-slate-800/80 pt-3.5 mb-4">
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->member_limit === -1 ? 'Unlimited' : number_format($plan->member_limit) }}</strong> Active Members</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->branch_limit }}</strong> Branch Location{{ $plan->branch_limit > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-amber-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->staff_limit }}</strong> Staff / Trainer Accounts</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="button" @click="planId = '{{ $plan->id }}'" 
                                        :class="planId == '{{ $plan->id }}' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-800 text-slate-300 font-bold'" 
                                        class="w-full py-2.5 rounded-xl text-xs transition-all cursor-pointer">
                                    <span x-text="planId == '{{ $plan->id }}' ? '✓ Plan Selected' : 'Select Plan'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Gym & Account Details Section (Responsive Form) -->
            <div class="p-5 sm:p-8 md:p-10 rounded-2xl sm:rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-xs sm:text-sm font-extrabold text-white uppercase tracking-wider">2. Gym Business &amp; Owner Details</h2>
                    <p class="text-xs text-slate-400 mt-0.5">These credentials will be used to log in to your GymDeskHub dashboard</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Gym Business Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="gym_name" value="{{ old('gym_name', $pending['gym_name'] ?? '') }}" placeholder="e.g. Titan Strength & Fitness" required 
                               class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Primary Branch Name
                        </label>
                        <input type="text" name="branch_name" value="{{ old('branch_name', $pending['branch_name'] ?? '') }}" placeholder="e.g. Main Flagship Branch" 
                               class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Owner Full Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $pending['owner_name'] ?? '') }}" placeholder="e.g. John Doe" required 
                               class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Owner Email Address <span class="text-amber-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $pending['email'] ?? '') }}" placeholder="owner@yourgym.com" required 
                               class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Phone / WhatsApp Number
                        </label>
                        <input type="text" name="phone" value="{{ old('phone', $pending['phone'] ?? '') }}" placeholder="+91 98765 43210" 
                               class="w-full px-3.5 sm:px-4 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Account Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="8" placeholder="••••••••••••" 
                                   class="w-full pl-3.5 sm:pl-4 pr-10 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-2.5 sm:top-3 p-1 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showPassword">👁️</span>
                                <span x-show="showPassword">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-1.5 sm:mb-2">
                            Confirm Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8" placeholder="••••••••••••" 
                                   class="w-full pl-3.5 sm:pl-4 pr-10 py-2.5 sm:py-3 rounded-xl sm:rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs sm:text-sm transition-all font-medium">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-2.5 sm:top-3 p-1 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showConfirmPassword">👁️</span>
                                <span x-show="showConfirmPassword">🙈</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500 text-center sm:text-left">
                        By proceeding, you agree to our Terms of Service &amp; Privacy Policy.
                    </p>
                    <button type="submit" class="w-full sm:w-auto px-6 sm:px-8 py-3.5 sm:py-4 rounded-xl sm:rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-black text-xs sm:text-sm shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                        <span>Verify Email &amp; Continue</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-8 text-center text-xs text-slate-400 relative z-10">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" class="text-amber-400 font-bold hover:underline ml-1">Sign in here &rarr;</a>
        </div>
    </div>
</x-guest-layout>
