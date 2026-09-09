<x-guest-layout title="Checkout & Activate SaaS Subscription - Gym Console">
    <div class="py-16 max-w-xl mx-auto px-4">
        <div class="p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl">
            <div class="text-center mb-8">
                <span class="text-xs uppercase font-bold tracking-widest text-amber-400">Step 2: Payment & Verification</span>
                <h1 class="text-2xl font-bold text-white mt-1">Activate {{ $plan->name }} Plan</h1>
                <p class="text-xs text-slate-400 mt-1">Your 14-day free trial will start immediately. Cancel anytime.</p>
            </div>

            <!-- Order Summary Card -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800 mb-6 space-y-3">
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">Plan Name:</span>
                    <span class="font-bold text-white">{{ $plan->name }} ({{ ucfirst($billingCycle) }})</span>
                </div>
                <div class="flex justify-between items-center text-sm">
                    <span class="text-slate-400">Trial Period:</span>
                    <span class="font-bold text-emerald-400">14 Days Free</span>
                </div>
                <div class="flex justify-between items-center text-sm border-t border-slate-800 pt-3">
                    <span class="text-slate-300 font-semibold">Total Due Today:</span>
                    <span class="text-xl font-extrabold text-amber-400">${{ number_format($price, 2) }}</span>
                </div>
            </div>

            <form action="{{ route('auth.checkout.process') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-2">Select Payment Method</label>
                    <div class="space-y-3">
                        <label class="flex items-center justify-between p-4 rounded-xl bg-slate-950 border border-amber-500/40 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="gateway" value="stripe" checked class="text-amber-500 focus:ring-0">
                                <div>
                                    <span class="font-semibold text-white text-sm block">Credit / Debit Card (Stripe)</span>
                                    <span class="text-xs text-slate-400">Instant verification & webhook auto-provisioning</span>
                                </div>
                            </div>
                            <span class="text-xs font-bold text-amber-400">Simulated</span>
                        </label>

                        <label class="flex items-center justify-between p-4 rounded-xl bg-slate-950 border border-slate-800 cursor-pointer">
                            <div class="flex items-center gap-3">
                                <input type="radio" name="gateway" value="manual" class="text-amber-500 focus:ring-0">
                                <div>
                                    <span class="font-semibold text-white text-sm block">Direct Bank Transfer / Wire</span>
                                    <span class="text-xs text-slate-400">Manual verification by Super Admin</span>
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <button type="submit" class="w-full py-4 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 text-slate-950 font-bold hover:brightness-110 shadow-lg shadow-orange-500/20 transition-all text-sm">
                    Complete Payment & Open Gym Dashboard →
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>

