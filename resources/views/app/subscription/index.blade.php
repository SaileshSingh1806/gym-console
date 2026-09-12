<x-app-layout header="SaaS Subscription & Billing Management">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $isExpired = $tenant->isSubscriptionExpired();
        $isTrial = $tenant->status === 'TRIAL';
        $daysLeft = ($subscription?->trial_ends_at && $subscription->trial_ends_at->isFuture()) 
            ? (int) ceil(now()->diffInDays($subscription->trial_ends_at, false)) 
            : 0;
    @endphp

    <div class="space-y-8" x-data="{ 
        billingCycle: 'monthly', 
        couponCode: '',
        appliedCoupon: null,
        couponMessage: '',
        couponError: '',
        isApplying: false,
        async applyCoupon() {
            if (!this.couponCode.trim()) {
                this.couponError = 'Please enter a coupon code.';
                this.appliedCoupon = null;
                return;
            }
            this.isApplying = true;
            this.couponError = '';
            this.couponMessage = '';

            try {
                let response = await fetch('{{ route('app.coupon.validate') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        code: this.couponCode
                    })
                });

                let data = await response.json();
                if (response.ok && data.valid) {
                    this.appliedCoupon = data;
                    this.couponMessage = data.message;
                    this.couponError = '';
                } else {
                    this.appliedCoupon = null;
                    this.couponError = data.message || 'Invalid coupon code.';
                }
            } catch (err) {
                this.appliedCoupon = null;
                this.couponError = 'Failed to validate coupon. Please try again.';
            } finally {
                this.isApplying = false;
            }
        },
        removeCoupon() {
            this.appliedCoupon = null;
            this.couponCode = '';
            this.couponMessage = '';
            this.couponError = '';
        },
        getPlanPricing(planId, priceMonthly, priceYearly) {
            let basePrice = this.billingCycle === 'yearly' ? parseFloat(priceYearly) : parseFloat(priceMonthly);
            
            if (!this.appliedCoupon) {
                return {
                    basePrice: basePrice,
                    finalPrice: basePrice,
                    discountAmount: 0,
                    hasDiscount: false,
                    reason: ''
                };
            }

            let c = this.appliedCoupon;
            
            // Plan restriction check
            if (c.plan_id && c.plan_id != planId) {
                return {
                    basePrice: basePrice,
                    finalPrice: basePrice,
                    discountAmount: 0,
                    hasDiscount: false,
                    reason: 'Coupon only applicable to ' + (c.plan_name || 'selected') + ' plan'
                };
            }

            // Min order amount check
            if (c.min_amount > 0 && basePrice < c.min_amount) {
                return {
                    basePrice: basePrice,
                    finalPrice: basePrice,
                    discountAmount: 0,
                    hasDiscount: false,
                    reason: 'Min. order ₹' + Number(c.min_amount).toLocaleString('en-IN') + ' required'
                };
            }

            // Calculate discount
            let discount = 0;
            if (c.discount_type === 'percentage') {
                discount = (basePrice * c.discount_value) / 100;
                if (c.max_discount_amount && c.max_discount_amount > 0) {
                    discount = Math.min(discount, c.max_discount_amount);
                }
            } else {
                discount = Math.min(c.discount_value, basePrice);
            }

            discount = Math.round(discount * 100) / 100;
            let finalPrice = Math.max(0, Math.round((basePrice - discount) * 100) / 100);

            return {
                basePrice: basePrice,
                finalPrice: finalPrice,
                discountAmount: discount,
                hasDiscount: discount > 0,
                discountType: c.discount_type,
                discountValue: c.discount_value,
                reason: ''
            };
        }
    }">

        <!-- Expired Trial Alert Notice -->
        @if($isExpired)
            <div class="p-6 rounded-3xl bg-gradient-to-r from-red-950/80 via-slate-900 to-red-950/80 border-2 border-red-500/50 shadow-2xl relative overflow-hidden">
                <div class="absolute -right-8 -top-8 w-40 h-40 bg-red-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 relative z-10">
                    <div class="space-y-2">
                        <div class="flex items-center gap-3">
                            <span class="p-2.5 rounded-2xl bg-red-500/20 text-red-400 border border-red-500/30">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            </span>
                            <div>
                                <h2 class="text-xl font-extrabold text-white tracking-tight">Your Free Trial / SaaS Subscription Has Expired!</h2>
                                <p class="text-xs text-red-300">
                                    Access to members enrollment, attendance, and gym operations is currently locked.
                                </p>
                            </div>
                        </div>
                        <p class="text-xs text-slate-300 pl-1">
                            Trial ended on: <span class="font-bold text-amber-400">{{ $subscription?->trial_ends_at ? $subscription->trial_ends_at->format('F d, Y (h:i A)') : 'Recently' }}</span>. Please select a SaaS plan below and click <strong>"Renew / Activate Plan"</strong> to instantly restore your dashboard.
                        </p>
                    </div>

                    <a href="#available-plans" class="px-6 py-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-400 hover:to-orange-400 text-slate-950 font-extrabold text-xs shadow-xl shadow-amber-500/20 flex items-center gap-2 whitespace-nowrap transition-all">
                        <span>⚡ Choose Plan & Renew Now</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                </div>
            </div>
        @elseif($isTrial)
            <!-- Active Trial Banner -->
            <div class="p-5 rounded-2xl bg-gradient-to-r from-amber-500/10 via-slate-900 to-amber-500/10 border border-amber-500/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-3">
                    <span class="p-2 rounded-xl bg-amber-500/20 text-amber-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-white">Free Trial Active ({{ $subscription?->plan->name ?? 'Starter' }} Tier)</h3>
                        <p class="text-xs text-slate-400">
                            You have <span class="font-bold text-amber-400">{{ $daysLeft }} days left</span> in your trial (Expires on {{ $subscription?->trial_ends_at ? $subscription->trial_ends_at->format('M d, Y') : 'N/A' }}). You can upgrade to a full paid plan anytime.
                        </p>
                    </div>
                </div>
                <a href="#available-plans" class="px-4 py-2 rounded-xl bg-amber-500/20 text-amber-400 hover:bg-amber-500 hover:text-slate-950 text-xs font-bold border border-amber-500/30 transition-all whitespace-nowrap">
                    Upgrade to Paid Plan &rarr;
                </a>
            </div>
        @endif

        <!-- Active Subscription Summary Card -->
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-xl flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h2 class="text-2xl font-bold text-white">{{ $subscription?->plan->name ?? 'Free Trial' }} Plan</h2>
                    @if($isExpired)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-400 border border-red-500/20">
                            🔴 EXPIRED
                        </span>
                    @elseif($isTrial)
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                            🟡 FREE TRIAL ({{ $daysLeft }}d left)
                        </span>
                    @else
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                            🟢 ACTIVE (Paid)
                        </span>
                    @endif
                </div>
                <p class="text-xs text-slate-400">
                    Billing Cycle: <span class="capitalize font-semibold text-slate-200">{{ $subscription?->billing_cycle ?? 'Monthly' }}</span> •
                    @if($isExpired)
                        <span class="text-red-400 font-semibold">Expired on: {{ $subscription?->trial_ends_at ? $subscription->trial_ends_at->format('F d, Y') : ($subscription?->ends_at ? $subscription->ends_at->format('F d, Y') : 'N/A') }}</span>
                    @else
                        Next Renewal Date: <span class="font-semibold text-amber-400">{{ $subscription?->ends_at ? $subscription->ends_at->format('F d, Y') : ($subscription?->trial_ends_at ? $subscription->trial_ends_at->format('F d, Y') : 'N/A') }}</span>
                    @endif
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="#available-plans" class="px-6 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold text-xs hover:brightness-110 shadow-lg shadow-orange-500/20">
                    {{ $isExpired ? '⚡ Renew / Choose Plan' : 'Change / Upgrade Plan' }}
                </a>
            </div>
        </div>

        <!-- Available Plans Section -->
        <div id="available-plans" class="space-y-6 pt-2">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-800 pb-4">
                <div>
                    <h3 class="text-lg font-bold text-white">Select SaaS Plan & {{ $isExpired ? 'Renew Access' : 'Upgrade' }}</h3>
                    <p class="text-xs text-slate-400">Choose the right tier for your gym capacity. Switch or renew instantly.</p>
                </div>

                <!-- Monthly / Annual Toggle -->
                <div class="inline-flex p-1 rounded-xl bg-slate-950 border border-slate-800 text-xs">
                    <button type="button" @click="billingCycle = 'monthly'" :class="billingCycle === 'monthly' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'" class="px-4 py-1.5 rounded-lg transition-all">
                        Monthly Billing
                    </button>
                    <button type="button" @click="billingCycle = 'yearly'" :class="billingCycle === 'yearly' ? 'bg-amber-500 text-slate-950 font-bold' : 'text-slate-400 hover:text-white'" class="px-4 py-1.5 rounded-lg transition-all flex items-center gap-1.5">
                        <span>Yearly Billing</span>
                        <span class="px-1.5 py-0.2 rounded text-[9px] font-extrabold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Save 20%</span>
                    </button>
                </div>
            </div>

            <!-- Global Promo Coupon Input Banner -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="p-2.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/20">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                    </span>
                    <div>
                        <h4 class="text-xs font-bold text-white">Have a Promo or Discount Coupon Code?</h4>
                    </div>
                </div>

                <div class="w-full md:w-auto">
                    <div class="flex items-center gap-2">
                        <input type="text" x-model="couponCode" @keydown.enter.prevent="applyCoupon()" placeholder="ENTER CODE" class="w-40 px-3 py-2 rounded-xl bg-slate-900 border border-slate-800 text-white font-mono uppercase text-xs focus:border-amber-500 focus:outline-none">
                        <button type="button" @click="applyCoupon()" :disabled="isApplying" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs disabled:opacity-50 transition-colors">
                            <span x-show="!isApplying">Apply</span>
                            <span x-show="isApplying" x-cloak>Checking...</span>
                        </button>
                        <button type="button" x-show="appliedCoupon" @click="removeCoupon()" class="px-3 py-2 rounded-xl bg-slate-800 text-slate-400 hover:text-white text-xs" title="Remove coupon" x-cloak>
                            ✕
                        </button>
                    </div>
                    <div x-show="couponMessage" class="text-[11px] text-emerald-400 font-bold mt-1.5 flex items-center gap-1" x-cloak>
                        <span>✓</span> <span x-text="couponMessage"></span>
                    </div>
                    <div x-show="couponError" class="text-[11px] text-rose-400 font-bold mt-1.5" x-text="couponError" x-cloak></div>
                </div>
            </div>

            <!-- Pricing Plan Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                @foreach($plans as $p)
                    @php
                        $isCurrentPlan = $subscription?->plan_id == $p->id;
                    @endphp
                    <div class="rounded-3xl bg-slate-900 border {{ $isCurrentPlan ? 'border-amber-500 ring-1 ring-amber-500/50' : 'border-slate-800 hover:border-slate-700' }} p-6 flex flex-col justify-between relative shadow-xl transition-all">
                        @if($p->is_popular)
                            <div class="absolute -top-3 left-1/2 -translate-x-1/2 px-3 py-0.5 rounded-full bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 text-[10px] font-extrabold uppercase tracking-wider shadow-md">
                                Most Popular
                            </div>
                        @endif

                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-lg font-bold text-white">{{ $p->name }}</h4>
                                @if($isCurrentPlan)
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                        Current Plan
                                    </span>
                                @endif
                            </div>
                            <p class="text-xs text-slate-400 mb-6 min-h-[36px]">{{ $p->description ?? 'All the essential tools to manage your gym members & attendance.' }}</p>

                            <!-- Dynamic Price Display with Live Coupon Strikethrough -->
                            <div class="mb-6 p-4 rounded-2xl bg-slate-950/60 border border-slate-800/80 transition-all">
                                <!-- When Coupon Discount is Active for this plan -->
                                <template x-if="getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).hasDiscount">
                                    <div>
                                        <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                            <del class="text-sm font-bold text-slate-500 line-through" x-text="'{{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).basePrice).toLocaleString('en-IN')"></del>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500/20 text-emerald-400 border border-emerald-500/30" x-text="getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).discountType === 'percentage' ? (getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).discountValue + '% OFF') : ('SAVE {{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).discountAmount).toLocaleString('en-IN'))"></span>
                                        </div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-3xl font-extrabold text-emerald-400" x-text="'{{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).finalPrice).toLocaleString('en-IN')"></span>
                                            <span class="text-xs text-slate-400" x-text="billingCycle === 'yearly' ? '/ year' : '/ month'"></span>
                                        </div>
                                        <span class="text-[10px] text-emerald-400 font-semibold block mt-1.5 flex items-center gap-1">
                                            <span>✨</span> You save <strong x-text="'{{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).discountAmount).toLocaleString('en-IN')"></strong> with coupon <span class="font-mono uppercase font-bold" x-text="appliedCoupon ? appliedCoupon.code : ''"></span>
                                        </span>
                                    </div>
                                </template>

                                <!-- Normal Price when no coupon discount applies -->
                                <template x-if="!getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).hasDiscount">
                                    <div>
                                        <div class="flex items-baseline gap-1">
                                            <span class="text-3xl font-extrabold" :class="billingCycle === 'yearly' ? 'text-amber-400' : 'text-white'" x-text="'{{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).basePrice).toLocaleString('en-IN')"></span>
                                            <span class="text-xs text-slate-400" x-text="billingCycle === 'yearly' ? '/ year' : '/ month'"></span>
                                        </div>
                                        <span class="text-[10px] block mt-0.5" :class="billingCycle === 'yearly' ? 'text-emerald-400' : 'text-slate-500'" x-text="billingCycle === 'yearly' ? '{{ $currency }}' + Math.round(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).basePrice/12).toLocaleString('en-IN') + '/mo billed annually' : 'Billed monthly'"></span>
                                        
                                        <!-- If coupon is entered but this plan is not eligible -->
                                        <span x-show="appliedCoupon && getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).reason" class="text-[10px] text-amber-400/90 block mt-1 font-medium" x-text="'⚠️ ' + getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).reason"></span>
                                    </div>
                                </template>
                            </div>

                            <!-- Plan Features Quotas -->
                            <div class="space-y-2.5 text-xs text-slate-300 mb-8 border-t border-slate-800/80 pt-4">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>{{ $p->member_limit === -1 ? 'Unlimited' : number_format($p->member_limit) }}</strong> Active Members</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>{{ $p->branch_limit }}</strong> Branch Location(s)</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-emerald-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span><strong>{{ $p->staff_limit === -1 ? 'Unlimited' : $p->staff_limit }}</strong> Staff / Trainers</span>
                                </div>
                                @foreach($p->features as $f)
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $f->name }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- 1-Click Renew / Activate Form -->
                        <form action="{{ route('app.subscription.upgrade') }}" method="POST" class="pt-2">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $p->id }}">
                            <input type="hidden" name="billing_cycle" :value="billingCycle">
                            <input type="hidden" name="coupon_code" :value="couponCode">
                            
                            <button type="submit" class="w-full py-3 rounded-xl font-bold text-xs shadow-lg transition-all flex items-center justify-center gap-2 {{ $isCurrentPlan && $isExpired ? 'bg-gradient-to-r from-red-500 via-amber-500 to-orange-500 text-slate-950 hover:brightness-110 shadow-red-500/20 animate-pulse' : ($isCurrentPlan ? 'bg-amber-500 text-slate-950 hover:bg-amber-400 shadow-amber-500/20' : 'bg-slate-800 text-white hover:bg-amber-500 hover:text-slate-950') }}">
                                <template x-if="getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).hasDiscount">
                                    <span x-text="'⚡ Pay ' + '{{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).finalPrice).toLocaleString('en-IN') + ' (Save {{ $currency }}' + Number(getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).discountAmount).toLocaleString('en-IN') + ')'"></span>
                                </template>
                                <template x-if="!getPlanPricing({{ $p->id }}, {{ (float)$p->price_monthly }}, {{ (float)$p->price_yearly }}).hasDiscount">
                                    <span>
                                        @if($isExpired)
                                            ⚡ Renew on {{ $p->name }}
                                        @elseif($isCurrentPlan)
                                            🔄 Renew / Extend Plan
                                        @else
                                            Switch to {{ $p->name }} &rarr;
                                        @endif
                                    </span>
                                </template>
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Plan Resource Quotas -->
        <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider mb-4">Current Plan Resource Limits</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">Enrolled Gym Members</span>
                    <span class="text-2xl font-bold text-white">{{ $quotas['members']['current'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $quotas['members']['limit'] === -1 ? 'Unlimited' : $quotas['members']['limit'] }}</span></span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">Gym Branch Locations</span>
                    <span class="text-2xl font-bold text-white">{{ $quotas['branches']['current'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $quotas['branches']['limit'] }}</span></span>
                </div>
                <div class="p-4 rounded-xl bg-slate-950 border border-slate-800">
                    <span class="text-xs text-slate-400 block mb-1">Staff / Trainer Logins</span>
                    <span class="text-2xl font-bold text-white">{{ $quotas['staff']['current'] }} <span class="text-xs text-slate-400 font-normal">/ {{ $quotas['staff']['limit'] }}</span></span>
                </div>
            </div>
        </div>

        <!-- SaaS Invoices & Receipts -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden">
            <div class="p-4 border-b border-slate-800 bg-slate-950/40">
                <h3 class="text-xs font-bold text-white uppercase tracking-wider">SaaS Platform Billing History & Receipts</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/60 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4">Invoice #</th>
                            <th class="py-3 px-4">Date</th>
                            <th class="py-3 px-4">Subtotal</th>
                            <th class="py-3 px-4">Discount</th>
                            <th class="py-3 px-4">Paid Total</th>
                            <th class="py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($invoices as $inv)
                            <tr class="hover:bg-slate-800/30">
                                <td class="py-3 px-4 font-mono font-bold text-amber-400">{{ $inv->invoice_number }}</td>
                                <td class="py-3 px-4 text-slate-400">{{ $inv->invoice_date->format('M d, Y') }}</td>
                                <td class="py-3 px-4 text-slate-400">
                                    {{ $currency }}{{ number_format($inv->subtotal, 2) }}
                                </td>
                                <td class="py-3 px-4 text-rose-400 font-semibold">
                                    {{ $inv->discount > 0 ? '-'.$currency.number_format($inv->discount, 2) : '—' }}
                                </td>
                                <td class="py-3 px-4 font-bold text-white">
                                    {{ $currency }}{{ number_format($inv->total, 2) }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        {{ $inv->status }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-6 text-center text-slate-500">No previous platform invoices.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
