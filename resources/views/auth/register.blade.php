<x-guest-layout title="Create Gym Account - {{ $platformSettings['app_name'] ?? 'Gym Console' }}">
    <div class="py-12 sm:py-16 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative" x-data="{ 
        planId: '{{ $selectedPlan?->id ?? 1 }}', 
        cycle: 'monthly',
        showPassword: false,
        showConfirmPassword: false
    }">
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
            <div class="w-[600px] h-[600px] bg-amber-500/10 rounded-full blur-3xl -top-32 left-1/2 -translate-x-1/2"></div>
        </div>

        <!-- Header -->
        <div class="text-center mb-10 relative z-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-full bg-amber-500/10 border border-amber-500/20 text-amber-400 text-xs font-bold uppercase tracking-wider mb-3">
                <span>⚡ Instant Account Provisioning &bull; Razorpay Secure Billing</span>
            </div>
            <h1 class="text-3xl sm:text-4xl font-black text-white tracking-tight">Launch Your Gym Management Console</h1>
            <p class="text-sm text-slate-400 mt-2 max-w-xl mx-auto">Select your plan, set up your gym tenant workspace, and start managing your fitness center seamlessly.</p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-3 mb-8 relative z-10 text-xs font-bold">
            <div class="flex items-center gap-2 text-amber-400">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-black">1</span>
                <span>Select Plan & Details</span>
            </div>
            <div class="w-12 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-black">2</span>
                <span>Payment & Activation</span>
            </div>
        </div>

        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-400 text-xs mb-8">
                <div class="font-bold mb-1 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-6">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-8 relative z-10">
            @csrf

            <!-- Plan Selection Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-sm font-extrabold text-white uppercase tracking-wider">1. Choose SaaS Subscription Plan</h2>
                        <p class="text-xs text-slate-400">All plans include biometric access control, member management, and diet planner</p>
                    </div>

                    <!-- Billing Cycle Switcher -->
                    <div class="flex items-center p-1 rounded-2xl bg-slate-900 border border-slate-800 text-xs font-bold self-start sm:self-auto">
                        <button type="button" @click="cycle = 'monthly'" 
                                :class="cycle === 'monthly' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'" 
                                class="px-4 py-2 rounded-xl transition-all cursor-pointer">
                            Monthly Billing
                        </button>
                        <button type="button" @click="cycle = 'yearly'" 
                                :class="cycle === 'yearly' ? 'bg-amber-500 text-slate-950 shadow-md' : 'text-slate-400 hover:text-white'" 
                                class="px-4 py-2 rounded-xl transition-all cursor-pointer flex items-center gap-1.5">
                            <span>Annual Billing</span>
                            <span class="px-1.5 py-0.2 rounded-md bg-emerald-500/20 text-emerald-400 text-[9px] font-black uppercase">SAVE 15%</span>
                        </button>
                        <input type="hidden" name="billing_cycle" :value="cycle">
                    </div>
                </div>

                <!-- Plans Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    @foreach($plans as $plan)
                        <div @click="planId = '{{ $plan->id }}'"
                             :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-slate-900 shadow-2xl shadow-amber-500/10 ring-1 ring-amber-500' : 'border-slate-800 bg-slate-900/60 hover:border-slate-700'"
                             class="p-6 rounded-3xl border-2 cursor-pointer transition-all relative flex flex-col justify-between group">
                            
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="planId" class="hidden">
                            
                            @if($plan->is_popular)
                                <div class="absolute -top-3 right-5 px-3 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-black text-[10px] tracking-wider uppercase shadow-md">
                                    MOST POPULAR
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-black text-lg text-white group-hover:text-amber-400 transition-colors">{{ $plan->name }}</h3>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all"
                                         :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-amber-500' : 'border-slate-700'">
                                        <div class="w-2 h-2 rounded-full bg-slate-950" x-show="planId == '{{ $plan->id }}'"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-3xl font-black text-white" x-show="cycle === 'monthly'">
                                        ₹{{ number_format($plan->price_monthly, 0) }}
                                        <span class="text-xs font-semibold text-slate-400">/ month</span>
                                    </div>
                                    <div class="text-3xl font-black text-white" x-show="cycle === 'yearly'">
                                        ₹{{ number_format($plan->price_yearly, 0) }}
                                        <span class="text-xs font-semibold text-slate-400">/ year</span>
                                    </div>
                                    <span class="text-[11px] text-slate-400 block mt-1">{{ $plan->description ?? 'Full operational suite' }}</span>
                                </div>

                                <div class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800/80 pt-4 mb-4">
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
                                        class="w-full py-2.5 rounded-xl text-xs transition-all">
                                    <span x-text="planId == '{{ $plan->id }}' ? '✓ Plan Selected' : 'Select Plan'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Gym & Account Details Section -->
            <div class="p-8 sm:p-10 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-sm font-extrabold text-white uppercase tracking-wider">2. Gym Business & Owner Details</h2>
                    <p class="text-xs text-slate-400 mt-0.5">These credentials will be used to log in to your gym console dashboard</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Gym Business Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="gym_name" value="{{ old('gym_name') }}" placeholder="e.g. Titan Strength & Fitness" required 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Primary Branch Name
                        </label>
                        <input type="text" name="branch_name" value="{{ old('branch_name') }}" placeholder="e.g. Main Flagship Branch" 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Owner Full Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="e.g. John Doe" required 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Owner Email Address <span class="text-amber-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="owner@yourgym.com" required 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Phone / WhatsApp Number
                        </label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+91 98765 43210" 
                               class="w-full px-4 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Account Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="8" placeholder="••••••••••••" 
                                   class="w-full pl-4 pr-10 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3.5 top-3 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showPassword">👁️</span>
                                <span x-show="showPassword">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 uppercase tracking-wider mb-2">
                            Confirm Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8" placeholder="••••••••••••" 
                                   class="w-full pl-4 pr-10 py-3 rounded-2xl bg-slate-950 border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none text-xs transition-all">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3.5 top-3 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showConfirmPassword">👁️</span>
                                <span x-show="showConfirmPassword">🙈</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-800/80 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-500">
                        By proceeding, you agree to our Terms of Service & Privacy Policy.
                    </p>
                    <button type="submit" class="w-full sm:w-auto px-8 py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-black text-xs shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                        <span>Proceed to Payment & Checkout</span>
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
