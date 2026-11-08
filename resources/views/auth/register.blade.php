<x-guest-layout 
    title="Start Free Forever — Create Your Gym Account | GymDeskHub"
    meta-description="Setup your gym membership plans, staff permissions, and biometric integration in under 2 minutes with GymDeskHub."
>
    <div class="py-8 sm:py-14 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative bg-[#090d16] overflow-x-hidden" x-data="{ 
        planId: '{{ $selectedPlan?->id ?? 1 }}', 
        showPassword: false,
        showConfirmPassword: false
    }">

        <!-- Header -->
        <div class="text-center mb-8 sm:mb-12 relative z-10">
            {{-- Category Pill Badge --}}
            <div class="inline-flex items-center gap-2 sm:gap-2.5 px-3.5 sm:px-4 py-1.5 rounded-full bg-slate-900 border border-slate-700 text-slate-200 text-xs sm:text-sm font-medium mb-4 max-w-full flex-wrap sm:flex-nowrap justify-center text-center">
                <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 font-bold text-[10px] uppercase tracking-wider shrink-0">INSTANT SETUP</span>
                <span class="text-slate-300 font-medium">Annual Plans &bull; 7-Day Money-Back Guarantee</span>
            </div>

            <h1 class="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white tracking-tight">
                Create Your Gym Workspace
            </h1>
            <p class="text-xs sm:text-sm md:text-base text-slate-300 mt-2 max-w-xl mx-auto leading-relaxed">
                Choose your annual tier, enter your facility details, and manage your gym operations on autopilot.
            </p>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-2 sm:gap-4 mb-8 sm:mb-10 relative z-10 text-xs font-semibold">
            <div class="flex items-center gap-2 text-amber-400">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-bold">1</span>
                <span>Select Plan &amp; Details</span>
            </div>
            <div class="w-6 sm:w-12 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-bold">2</span>
                <span>Verify Email</span>
            </div>
            <div class="w-6 sm:w-12 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-bold">3</span>
                <span>Live Console</span>
            </div>
        </div>

        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="p-4 sm:p-5 rounded-2xl bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs sm:text-sm mb-6 sm:mb-8">
                <div class="font-bold mb-1 flex items-center gap-2 text-rose-400">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    <span>Please review the form:</span>
                </div>
                <ul class="list-disc list-inside space-y-0.5 pl-5 text-xs text-rose-300">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" class="space-y-8 sm:space-y-10 relative z-10">
            @csrf
            <!-- Strict Annual Billing Input -->
            <input type="hidden" name="billing_cycle" value="yearly">

            <!-- Plan Selection Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-slate-800">
                    <div>
                        <h2 class="text-sm sm:text-base font-extrabold text-white">1. Select Your Annual Plan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Upgrade or change plans anytime from your billing settings</p>
                    </div>

                    <!-- Annual Billing Badge -->
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl bg-slate-900 border border-slate-800 text-slate-300 text-xs font-semibold self-start sm:self-auto">
                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                        <span>Annual Billing</span>
                        <span class="px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 text-[10px] font-bold">Best Value</span>
                    </div>
                </div>

                <!-- Plans Grid (Fully Responsive) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 items-stretch">
                    @foreach($plans as $plan)
                        <div @click="planId = '{{ $plan->id }}'"
                             :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-[#0f172a] shadow-xl' : 'border-slate-800 bg-[#0c121e] hover:border-slate-700'"
                             class="p-6 rounded-3xl border-2 cursor-pointer transition-all relative flex flex-col justify-between group">
                            
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="planId" class="hidden">
                            
                            @if($plan->is_popular)
                                <div class="absolute -top-3.5 right-6 px-3.5 py-0.5 rounded-full bg-amber-500 text-slate-950 font-black text-[10px] tracking-wider uppercase shadow-md">
                                    MOST POPULAR
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-extrabold text-lg text-white group-hover:text-amber-400 transition-colors">{{ $plan->name }}</h3>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                         :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-amber-500' : 'border-slate-700'">
                                        <div class="w-2 h-2 rounded-full bg-slate-950" x-show="planId == '{{ $plan->id }}'"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-3xl font-extrabold text-white">
                                        ₹{{ number_format($plan->price_yearly, 0) }}
                                        <span class="text-xs font-semibold text-slate-400">/ year</span>
                                    </div>
                                    @if($plan->price_yearly > 0)
                                        <span class="text-xs text-amber-400 font-semibold block mt-1">
                                            Equivalent to ₹{{ number_format(round($plan->price_yearly / 12), 0) }} / month
                                        </span>
                                    @else
                                        <span class="text-xs text-emerald-400 font-semibold block mt-1">Free Forever &bull; No Expiry</span>
                                    @endif
                                    <span class="text-xs text-slate-400 block mt-1.5 leading-relaxed">{{ $plan->description ?? 'Full operational suite' }}</span>
                                </div>

                                <div class="space-y-2.5 text-xs text-slate-300 border-t border-slate-800 pt-3.5 mb-5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-emerald-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->member_limit === -1 ? 'Unlimited' : number_format($plan->member_limit) }}</strong> Active Members</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-emerald-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->branch_limit }}</strong> Branch Location{{ $plan->branch_limit > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-emerald-400 font-bold">✓</span>
                                        <span><strong>{{ $plan->staff_limit }}</strong> Staff / Trainer Logins</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="button" @click="planId = '{{ $plan->id }}'" 
                                        :class="planId == '{{ $plan->id }}' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-800 text-slate-300 font-semibold hover:bg-slate-700'" 
                                        class="w-full py-3 rounded-xl text-xs transition-colors cursor-pointer">
                                    <span x-text="planId == '{{ $plan->id }}' ? '✓ Selected Plan' : 'Select This Plan'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Gym & Account Details Section (Responsive Matte Form) -->
            <div class="p-6 sm:p-9 rounded-3xl bg-[#0c121e] border border-slate-800 shadow-xl space-y-6">
                <div class="border-b border-slate-800 pb-4">
                    <h2 class="text-sm sm:text-base font-extrabold text-white">2. Gym Business &amp; Owner Credentials</h2>
                    <p class="text-xs text-slate-400 mt-0.5">You will use this email and password to log in to your owner dashboard</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Gym / Business Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="gym_name" value="{{ old('gym_name', $pending['gym_name'] ?? '') }}" placeholder="e.g. Apex Fitness Studio" required 
                               class="w-full px-4 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Primary Branch Location
                        </label>
                        <input type="text" name="branch_name" value="{{ old('branch_name', $pending['branch_name'] ?? '') }}" placeholder="e.g. Flagship Center" 
                               class="w-full px-4 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Owner Full Name <span class="text-amber-400">*</span>
                        </label>
                        <input type="text" name="owner_name" value="{{ old('owner_name', $pending['owner_name'] ?? '') }}" placeholder="e.g. Vikram Rathore" required 
                               class="w-full px-4 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Owner Email Address <span class="text-amber-400">*</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $pending['email'] ?? '') }}" placeholder="owner@yourgym.com" required 
                               class="w-full px-4 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Phone / WhatsApp Number (For account notifications)
                        </label>
                        <input type="tel" name="phone" value="{{ old('phone', $pending['phone'] ?? '') }}" placeholder="+91 98765 43210" 
                               class="w-full px-4 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Create Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" required minlength="8" placeholder="At least 8 characters" 
                                   class="w-full pl-4 pr-10 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-3 top-3 p-1 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showPassword">👁️</span>
                                <span x-show="showPassword">🙈</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">
                            Confirm Password <span class="text-amber-400">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" required minlength="8" placeholder="Repeat your password" 
                                   class="w-full pl-4 pr-10 py-3 rounded-xl bg-[#090d16] border border-slate-800 text-white placeholder-slate-500 focus:border-amber-500 focus:outline-none text-xs sm:text-sm transition-colors font-medium">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-3 top-3 p-1 text-slate-400 hover:text-white text-xs cursor-pointer">
                                <span x-show="!showConfirmPassword">👁️</span>
                                <span x-show="showConfirmPassword">🙈</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-5 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-400 text-center sm:text-left">
                        By signing up, you agree to our <a href="{{ route('terms') }}" class="text-amber-400 hover:underline">Terms of Service</a> &amp; <a href="{{ route('privacy') }}" class="text-amber-400 hover:underline">Privacy Policy</a>.
                    </p>
                    <button type="submit" 
                            class="w-full sm:w-auto px-8 py-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-sm transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <span>Continue &amp; Create Account &rarr;</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-8 text-center text-xs text-slate-400 relative z-10">
            <span>Already have a GymDeskHub account?</span>
            <a href="{{ route('login') }}" class="text-amber-400 font-bold hover:underline ml-1">Sign in here &rarr;</a>
        </div>
    </div>
</x-guest-layout>
