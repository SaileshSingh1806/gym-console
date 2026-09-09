<x-guest-layout title="Register Gym - Gym Console SaaS">
    <div class="py-12 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold text-white">Create Your Gym SaaS Account</h1>
            <p class="text-sm text-slate-400 mt-2">Step 1: Choose SaaS Plan & Fill Gym Details • Step 2: Checkout & Verification</p>
        </div>

        @if ($errors->any())
            <div class="p-4 rounded-xl bg-red-500/10 border border-red-500/20 text-red-400 text-sm mb-6">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST" x-data="{ planId: '{{ $selectedPlan->id }}', cycle: 'monthly' }">
            @csrf

            <!-- Plan Selection Row -->
            <div class="mb-8">
                <label class="block text-xs font-bold text-amber-400 uppercase tracking-widest mb-4">Select SaaS Subscription Plan</label>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    @foreach($plans as $plan)
                        <div @click="planId = '{{ $plan->id }}'"
                             :class="planId == '{{ $plan->id }}' ? 'border-amber-500 bg-slate-900 shadow-lg shadow-amber-500/10' : 'border-slate-800 bg-slate-950/60 opacity-80'"
                             class="p-5 rounded-2xl border-2 cursor-pointer transition-all hover:border-slate-700 relative">
                            <input type="radio" name="plan_id" value="{{ $plan->id }}" x-model="planId" class="hidden">
                            <div class="flex justify-between items-start mb-2">
                                <span class="font-bold text-lg text-white">{{ $plan->name }}</span>
                                <span class="text-xs px-2 py-0.5 rounded bg-amber-400/10 text-amber-400 border border-amber-400/20">14-day trial</span>
                            </div>
                            <div class="text-2xl font-extrabold text-amber-400 mb-2">
                                <span x-show="cycle === 'monthly'">${{ number_format($plan->price_monthly, 0) }}<span class="text-xs text-slate-400">/mo</span></span>
                                <span x-show="cycle === 'yearly'">${{ number_format($plan->price_yearly, 0) }}<span class="text-xs text-slate-400">/yr</span></span>
                            </div>
                            <div class="text-xs text-slate-400 space-y-1">
                                <div>• {{ $plan->member_limit === -1 ? 'Unlimited' : $plan->member_limit }} Members</div>
                                <div>• {{ $plan->branch_limit }} Branch Location(s)</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Billing Cycle Switch -->
            <div class="mb-8 flex items-center justify-center gap-4 p-3 rounded-2xl bg-slate-900 border border-slate-800 w-fit mx-auto">
                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold" :class="cycle === 'monthly' ? 'text-amber-400' : 'text-slate-400'">
                    <input type="radio" name="billing_cycle" value="monthly" x-model="cycle" class="text-amber-500 focus:ring-0">
                    Monthly Billing
                </label>
                <span class="text-slate-600">|</span>
                <label class="flex items-center gap-2 cursor-pointer text-sm font-semibold" :class="cycle === 'yearly' ? 'text-amber-400' : 'text-slate-400'">
                    <input type="radio" name="billing_cycle" value="yearly" x-model="cycle" class="text-amber-500 focus:ring-0">
                    Annual Billing (Save 15%)
                </label>
            </div>

            <!-- Gym and Owner Details -->
            <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 space-y-6">
                <h3 class="text-lg font-bold text-white border-b border-slate-800 pb-3">Gym & Account Credentials</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Gym Business Name *</label>
                        <input type="text" name="gym_name" value="{{ old('gym_name') }}" placeholder="e.g. Titan Strength Gym" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Primary Branch Name</label>
                        <input type="text" name="branch_name" value="{{ old('branch_name') }}" placeholder="e.g. Central City Branch" class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Owner Full Name *</label>
                        <input type="text" name="owner_name" value="{{ old('owner_name') }}" placeholder="John Doe" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Owner Email Address *</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="owner@gym.com" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" placeholder="+1 555-0100" class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password *</label>
                        <input type="password" name="password" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full px-4 py-3 rounded-xl bg-slate-950 border border-slate-800 text-white focus:border-amber-500 focus:outline-none text-sm">
                    </div>
                </div>

                <div class="pt-4">
                    <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:brightness-110 shadow-xl shadow-orange-500/20 transition-all">
                        Continue to Checkout & Activate Trial →
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-guest-layout>

