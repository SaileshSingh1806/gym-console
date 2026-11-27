<x-guest-layout 
     :hide-nav="true" :hide-footer="true" title="Create an account — GymDeskHub"
    meta-description="Create your GymDeskHub workspace and choose a subscription plan."
>
    <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
    .gdh-register { font-family:'Plus Jakarta Sans',system-ui,sans-serif; color:#f1f3f5; background:#101113; min-height:100dvh; font-size:14px; color-scheme:dark; }
    .gdh-register :focus-visible { outline:2px solid #f5a623; outline-offset:3px; }
    .gdh-register input:not([type=radio]):not([type=hidden]) { min-height:46px; font-size:14px; font-weight:400; }
    .gdh-register input::placeholder { color:#8795a5; }
    .gdh-register .register-plan:focus-within { outline:2px solid #f5a623; outline-offset:2px; }
    .gdh-register .register-plan strong { font-weight:600; }
    @media(max-width:640px) { .gdh-register input:not([type=radio]):not([type=hidden]) { font-size:16px; } }
    </style>
    <div class="gdh-register">
    <header class="max-w-6xl mx-auto px-5 sm:px-8 flex items-center justify-between gap-4 min-h-[72px] border-b border-[#292c30]">
        <a href="{{ route('home') }}" aria-label="Back to website"><img src="{{ asset('images/gymdeskhub-official-dark.svg') }}" alt="{{ $platformSettings['app_name'] ?? 'GymDeskHub' }}" class="h-8 w-auto object-contain max-w-[180px]"></a>
        <a href="{{ route('contact') }}" class="text-sm text-[#f5a623] hover:underline">Contact support</a>
    </header>
    <div class="py-8 sm:py-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative" x-data="{ 
        planId: '{{ (int) old('plan_id', $selectedPlan?->id ?? 1) }}', 
        showPassword: false,
        showConfirmPassword: false
    }">

        <!-- Header -->
        <div class="mb-6 sm:mb-8 relative z-10">
            <h1 class="text-2xl sm:text-3xl font-semibold text-white tracking-tight">
                Create your gym workspace
            </h1>
            <p class="text-sm text-slate-400 mt-2 max-w-xl leading-relaxed">
                Choose a subscription plan and enter your gym and account details.
            </p>
        </div>

        <!-- Step Indicator -->
        <div class="flex flex-wrap items-center gap-2 sm:gap-4 mb-6 sm:mb-8 relative z-10 text-xs font-semibold">
            <div class="flex items-center gap-2 text-[#f5a623]">
                <span class="w-6 h-6 rounded-full bg-[#f5a623] text-[#151618] flex items-center justify-center text-xs font-medium">1</span>
                <span>Account details</span>
            </div>
            <div class="w-6 sm:w-12 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-medium">2</span>
                <span>Verify email</span>
            </div>
            <div class="w-6 sm:w-12 h-0.5 bg-slate-800"></div>
            <div class="flex items-center gap-2 text-slate-500">
                <span class="w-6 h-6 rounded-full bg-slate-800 text-slate-400 flex items-center justify-center text-xs font-medium">3</span>
                <span>Workspace</span>
            </div>
        </div>

        <!-- Error Alerts -->
        @if ($errors->any())
            <div class="p-4 sm:p-5 rounded-lg bg-rose-500/10 border border-rose-500/25 text-rose-300 text-xs sm:text-sm mb-6 sm:mb-8">
                <div class="font-medium mb-1 flex items-center gap-2 text-rose-400">
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

        <form action="{{ route('register.post') }}" method="POST" class="space-y-6 relative z-10">
            @csrf
            <!-- Strict Annual Billing Input -->
            <input type="hidden" name="billing_cycle" value="yearly">

            <!-- Plan Selection Section -->
            <div class="space-y-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-2 border-b border-[#292c30]">
                    <div>
                        <h2 class="text-sm sm:text-base font-semibold text-white">1. Choose a plan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Upgrade or change plans anytime from your billing settings</p>
                    </div>

                    <span class="text-xs text-slate-400">Billed annually</span>
                </div>

                <!-- Plans Grid (Fully Responsive) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-stretch">
                    @foreach($plans as $plan)
                        <div @click="planId = '{{ $plan->id }}'"
                             :class="planId == '{{ $plan->id }}' ? 'border-[#f5a623] bg-[#282219]' : 'border-[#292c30] bg-[#151618] hover:border-slate-700'"
                             class="register-plan p-4 rounded-lg border cursor-pointer transition-colors relative flex flex-col justify-between group">
                            
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="planId" class="sr-only" :aria-label="'Select ' + $el.value">
                            
                            @if($plan->is_popular)
                                <div class="text-xs text-[#f5a623] font-medium mb-2">
                                    Popular
                                </div>
                            @endif

                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-lg text-white group-hover:text-[#f5a623] transition-colors">{{ $plan->name }}</h3>
                                    <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all shrink-0"
                                         :class="planId == '{{ $plan->id }}' ? 'border-[#f5a623] bg-[#f5a623]' : 'border-slate-700'">
                                        <div class="w-2 h-2 rounded-full bg-slate-950" x-show="planId == '{{ $plan->id }}'"></div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <div class="text-2xl font-semibold text-white">
                                        ₹{{ number_format($plan->price_yearly, 0) }}
                                        <span class="text-xs font-semibold text-slate-400">/ year</span>
                                    </div>
                                    @if($plan->price_yearly > 0)
                                        <span class="text-xs text-[#f5a623] font-semibold block mt-1">
                                            Equivalent to ₹{{ number_format(round($plan->price_yearly / 12), 0) }} / month
                                        </span>
                                    @else
                                        <span class="text-xs text-[#a1a8b0] font-semibold block mt-1">Free plan</span>
                                    @endif
                                    <span class="text-xs text-slate-400 block mt-1.5 leading-relaxed">{{ $plan->description ?? '' }}</span>
                                </div>

                                <div class="space-y-1.5 text-sm text-slate-300 border-t border-[#292c30] pt-3 mb-3">
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#a1a8b0] font-medium">✓</span>
                                        <span><strong>{{ $plan->member_limit === -1 ? 'Unlimited' : number_format($plan->member_limit) }}</strong> Active Members</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#a1a8b0] font-medium">✓</span>
                                        <span><strong>{{ $plan->branch_limit }}</strong> Branch Location{{ $plan->branch_limit > 1 ? 's' : '' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span class="text-[#a1a8b0] font-medium">✓</span>
                                        <span><strong>{{ $plan->staff_limit }}</strong> Staff / Trainer Logins</span>
                                    </div>
                                </div>
                            </div>

                            <div class="pt-2">
                                <button type="button" @click="planId = '{{ $plan->id }}'" 
                                        :class="planId == '{{ $plan->id }}' ? 'bg-[#f5a623] text-[#151618] font-medium' : 'bg-slate-800 text-slate-300 font-semibold hover:bg-slate-700'" 
                                        class="w-full py-2 rounded-md text-sm transition-colors cursor-pointer">
                                    <span x-text="planId == '{{ $plan->id }}' ? 'Selected' : 'Select plan'"></span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Gym & Account Details Section (Responsive Matte Form) -->
            <div class="p-4 sm:p-6 rounded-lg bg-[#151618] border border-[#292c30] space-y-5">
                <div class="border-b border-[#292c30] pb-4">
                    <h2 class="text-sm sm:text-base font-semibold text-white">2. Gym and account details</h2>
                    <p class="text-xs text-slate-400 mt-0.5">Use these details to sign in to your workspace.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="register-gym_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Gym or business name <span class="text-[#f5a623]">*</span></label>
                        <input type="text" name="gym_name" id="register-gym_name" value="{{ old('gym_name', $pending['gym_name'] ?? '') }}" placeholder="e.g. Apex Fitness Studio" required 
                               class="w-full px-3 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                    </div>

                    <div>
                        <label for="register-branch_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Primary branch (optional)</label>
                        <input type="text" name="branch_name" id="register-branch_name" value="{{ old('branch_name', $pending['branch_name'] ?? '') }}" placeholder="e.g. Flagship Center" 
                               class="w-full px-3 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                    </div>

                    <div>
                        <label for="register-owner_name" class="block text-xs font-semibold text-slate-300 mb-1.5">Full name <span class="text-[#f5a623]">*</span></label>
                        <input type="text" name="owner_name" id="register-owner_name" value="{{ old('owner_name', $pending['owner_name'] ?? '') }}" placeholder="e.g. Vikram Rathore" required 
                               class="w-full px-3 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                    </div>

                    <div>
                        <label for="register-email" class="block text-xs font-semibold text-slate-300 mb-1.5">Email address <span class="text-[#f5a623]">*</span></label>
                        <input type="email" name="email" id="register-email" value="{{ old('email', $pending['email'] ?? '') }}" placeholder="owner@yourgym.com" required 
                               class="w-full px-3 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                    </div>

                    <div class="sm:col-span-2">
                        <label for="register-phone" class="block text-xs font-semibold text-slate-300 mb-1.5">Phone / WhatsApp (optional)</label>
                        <input type="tel" name="phone" id="register-phone" value="{{ old('phone', $pending['phone'] ?? '') }}" placeholder="+91 98765 43210" 
                               class="w-full px-3 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                    </div>

                    <div>
                        <label for="register-password" class="block text-xs font-semibold text-slate-300 mb-1.5">Password <span class="text-[#f5a623]">*</span></label>
                        <div class="relative">
                            <input type="password" autocomplete="new-password" :type="showPassword ? 'text' : 'password'" name="password" id="register-password" required minlength="8" placeholder="At least 8 characters" 
                                   class="w-full pl-3 pr-16 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                            <button type="button" @click="showPassword = !showPassword" class="absolute right-2 inset-y-1 px-2 text-slate-400 hover:text-white text-xs cursor-pointer" :aria-pressed="showPassword" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <span x-text="showPassword ? 'Hide' : 'Show'">Show</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="register-password_confirmation" class="block text-xs font-semibold text-slate-300 mb-1.5">Confirm password <span class="text-[#f5a623]">*</span></label>
                        <div class="relative">
                            <input type="password" autocomplete="new-password" :type="showConfirmPassword ? 'text' : 'password'" name="password_confirmation" id="register-password_confirmation" required minlength="8" placeholder="Repeat your password" 
                                   class="w-full pl-3 pr-16 py-2.5 rounded-md bg-[#101113] border border-[#292c30] text-white placeholder-slate-500 focus:border-[#f5a623] focus:outline-none text-sm transition-colors font-normal">
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword" class="absolute right-2 inset-y-1 px-2 text-slate-400 hover:text-white text-xs cursor-pointer" :aria-pressed="showConfirmPassword" :aria-label="showConfirmPassword ? 'Hide password' : 'Show password'">
                                <span x-text="showConfirmPassword ? 'Hide' : 'Show'">Show</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="pt-5 border-t border-[#292c30] flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-xs text-slate-400 text-center sm:text-left">
                        By signing up, you agree to our <a href="{{ route('terms') }}" class="text-[#f5a623] hover:underline">Terms of Service</a> &amp; <a href="{{ route('privacy') }}" class="text-[#f5a623] hover:underline">Privacy Policy</a>.
                    </p>
                    <button type="submit" 
                            class="w-full sm:w-auto px-5 py-3 rounded-md bg-[#f5a623] hover:bg-[#ffb942] text-[#151618] font-medium text-sm transition-colors cursor-pointer flex items-center justify-center gap-2">
                        <span>Create account</span>
                    </button>
                </div>
            </div>
        </form>

        <div class="mt-8 text-center text-xs text-slate-400 relative z-10">
            <span>Already have an account?</span>
            <a href="{{ route('login') }}" class="text-[#f5a623] font-medium hover:underline ml-1">Sign in</a>
        </div>
    </div>
    <footer class="max-w-6xl mx-auto px-5 sm:px-8 py-5 border-t border-[#292c30] flex justify-between gap-4 text-xs text-slate-400"><span>{{ $platformSettings['footer_copyright'] ?? ('© ' . date('Y') . ' GymDeskHub') }}</span><a href="{{ route('home') }}" class="text-[#f5a623] hover:underline">Back to website</a></footer>
    </div>
</x-guest-layout>
