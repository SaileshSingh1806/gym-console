<x-guest-layout title="Order Summary & Checkout - {{ $platformSettings['app_name'] ?? 'Gym Console' }}">
    <div class="py-12 sm:py-16 max-w-xl mx-auto px-4 sm:px-6 relative" x-data="{ 
        processing: false,
        paymentFailed: false,
        failureMsg: ''
    }">
        <!-- Ambient Background Glow -->
        <div class="absolute inset-0 flex items-center justify-center pointer-events-none overflow-hidden">
            <div class="w-[500px] h-[500px] bg-amber-500/10 rounded-full blur-3xl -top-20 left-1/2 -translate-x-1/2"></div>
        </div>

        <!-- Step Indicator -->
        <div class="flex items-center justify-center gap-3 mb-8 relative z-10 text-xs font-bold">
            <div class="flex items-center gap-2 text-emerald-400">
                <span class="w-6 h-6 rounded-full bg-emerald-500 text-slate-950 flex items-center justify-center text-xs font-black">✓</span>
                <span>Plan & Details</span>
            </div>
            <div class="w-12 h-0.5 bg-amber-500"></div>
            <div class="flex items-center gap-2 text-amber-400">
                <span class="w-6 h-6 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-black">2</span>
                <span>Payment & Activation</span>
            </div>
        </div>

        <!-- Single Centered Order Summary & Payment Card -->
        <div class="p-6 sm:p-8 rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl shadow-black/60 relative z-10 space-y-6">
            <div class="border-b border-slate-800 pb-4 flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-black text-white tracking-tight">Order Summary</h1>
                    <p class="text-xs text-slate-400 mt-0.5">SaaS Platform Subscription Details</p>
                </div>
                <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-xs font-black uppercase tracking-wider">
                    {{ ucfirst($billingCycle) }}
                </span>
            </div>

            <!-- Selected Plan Inclusions -->
            <div class="p-5 rounded-2xl bg-slate-950 border border-slate-800/90 space-y-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-black text-white block">{{ $plan->name }} Plan</h2>
                        <span class="text-xs text-amber-400 font-bold">₹{{ number_format($price, 2) }} / {{ $billingCycle === 'yearly' ? 'year' : 'month' }}</span>
                    </div>
                    <span class="px-2.5 py-1 rounded-xl bg-slate-900 border border-slate-800 text-[11px] font-extrabold text-slate-300">
                        {{ $plan->slug === 'free-forever' ? 'Startup' : ($plan->slug === 'starter' ? 'Growth' : 'Scale') }}
                    </span>
                </div>

                <div class="border-t border-slate-800/80 pt-3 space-y-2.5 text-xs text-slate-300">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>Active Member Quota:</span>
                        </span>
                        <span class="font-bold text-white">{{ $plan->member_limit === -1 ? 'Unlimited' : number_format($plan->member_limit) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>Branch Locations:</span>
                        </span>
                        <span class="font-bold text-white">{{ $plan->branch_limit }} Branch{{ $plan->branch_limit > 1 ? 'es' : '' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-400 flex items-center gap-2">
                            <span class="text-amber-400 font-bold">✓</span>
                            <span>Staff & Trainer Accounts:</span>
                        </span>
                        <span class="font-bold text-white">{{ $plan->staff_limit }} Accounts</span>
                    </div>
                </div>
            </div>

            <!-- Pricing Breakdown -->
            <div class="space-y-3 text-xs text-slate-400 border-t border-slate-800 pt-4">
                <div class="flex justify-between items-center">
                    <span>Base Plan Price:</span>
                    <span class="text-slate-200 font-semibold">₹{{ number_format($price, 2) }}</span>
                </div>
                <div class="flex justify-between items-center text-slate-300 font-semibold">
                    <span>Gateway & Platform Fee:</span>
                    <span class="text-emerald-400 font-bold">₹0.00 (Free)</span>
                </div>
                <div class="flex justify-between items-center border-t border-slate-800 pt-3 text-sm">
                    <span class="font-extrabold text-white">Total Amount Due:</span>
                    <span class="text-2xl font-black text-amber-400">₹{{ number_format($price, 2) }}</span>
                </div>
                <p class="text-[10px] text-slate-500 mt-1 leading-relaxed">
                    Billed {{ $billingCycle === 'yearly' ? 'annually' : 'monthly' }}. Your subscription will activate immediately upon successful payment confirmation.
                </p>
            </div>

            <!-- Checkout Form & Pay Button -->
            <form id="checkout-form" action="{{ route('auth.checkout.process') }}" method="POST" class="space-y-4 pt-2">
                @csrf
                <input type="hidden" name="gateway" value="razorpay">
                <input type="hidden" name="razorpay_payment_id" id="razorpay_payment_id">
                <input type="hidden" name="razorpay_order_id" id="razorpay_order_id">
                <input type="hidden" name="razorpay_signature" id="razorpay_signature">

                <!-- Sandbox Notice if invalid API key in test environment -->
                <template x-if="paymentFailed">
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-xs text-slate-300 space-y-2">
                        <div class="font-bold text-amber-400 flex items-center gap-2">
                            <span>⚠️ Sandbox Testing Notice:</span>
                        </div>
                        <p class="text-[11px] text-slate-400">
                            <span x-text="failureMsg || 'Razorpay sandbox key verification.'"></span><br>
                            To process live UPI/Cards, configure your Razorpay Key ID in <strong>Super Admin &rarr; Settings &rarr; Payment Gateways</strong>.
                        </p>
                        <button type="button" @click="simulateTestPayment()" class="w-full py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all cursor-pointer">
                            ⚡ Complete Test Payment (Sandbox Simulator)
                        </button>
                    </div>
                </template>

                <button type="button" @click="handleRazorpayPayment()" :disabled="processing" 
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-black text-sm shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span x-show="processing" class="animate-spin text-base">⏳</span>
                    <span x-show="!processing">💳 Pay ₹{{ number_format($price, 2) }} with Razorpay &rarr;</span>
                </button>
            </form>

            <!-- Trust Signals & Supported Payments Strip -->
            <div class="border-t border-slate-800/80 pt-4 space-y-2 text-center">
                <div class="flex items-center justify-center gap-3 text-[11px] text-slate-400">
                    <span>🔒 256-bit Bank Grade SSL</span>
                    <span>&bull;</span>
                    <span>⚡ Instant Activation</span>
                </div>
                <p class="text-[10px] text-slate-500">
                    Supports UPI (GPay, PhonePe, Paytm), Debit/Credit Cards, NetBanking & Wallets
                </p>
            </div>
        </div>
    </div>

    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const razorpayConfig = {{ Js::from($razorpayConfig ?? []) }};

        function simulateTestPayment() {
            const form = document.getElementById('checkout-form');
            document.getElementById('razorpay_payment_id').value = 'pay_sim_' + Math.random().toString(36).substring(2, 15);
            document.getElementById('razorpay_order_id').value = 'order_sim_' + Math.random().toString(36).substring(2, 15);
            document.getElementById('razorpay_signature').value = 'sig_sim_' + Math.random().toString(36).substring(2, 15);
            form.submit();
        }

        function handleRazorpayPayment() {
            const form = document.getElementById('checkout-form');

            if (typeof Razorpay !== 'undefined' && razorpayConfig.key) {
                const options = {
                    key: razorpayConfig.key,
                    amount: razorpayConfig.amount,
                    currency: razorpayConfig.currency || 'INR',
                    name: razorpayConfig.name || 'Gym Console',
                    description: razorpayConfig.description || 'SaaS Subscription Plan',
                    image: '{{ $platformSettings['logo_url'] ?? '' }}',
                    prefill: {
                        name: razorpayConfig.prefill?.name || 'Gym Owner',
                        email: razorpayConfig.prefill?.email || '',
                        contact: razorpayConfig.prefill?.contact || ''
                    },
                    theme: {
                        color: '#f59e0b'
                    },
                    handler: function (response) {
                        document.getElementById('razorpay_payment_id').value = response.razorpay_payment_id || '';
                        document.getElementById('razorpay_order_id').value = response.razorpay_order_id || '';
                        document.getElementById('razorpay_signature').value = response.razorpay_signature || '';
                        form.submit();
                    },
                    modal: {
                        ondismiss: function() {
                            // User closed modal
                        }
                    }
                };

                try {
                    const rzp = new Razorpay(options);
                    rzp.on('payment.failed', function (response){
                        const alpineEl = document.querySelector('[x-data]');
                        if (alpineEl && alpineEl._x_dataStack) {
                            alpineEl._x_dataStack[0].paymentFailed = true;
                            alpineEl._x_dataStack[0].failureMsg = response.error ? (response.error.description || response.error.reason) : 'Payment failed.';
                        }
                    });
                    rzp.open();
                } catch(e) {
                    simulateTestPayment();
                }
            } else {
                simulateTestPayment();
            }
        }
    </script>
</x-guest-layout>
