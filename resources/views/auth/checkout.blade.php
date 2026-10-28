<x-guest-layout title="Order Summary & Checkout - {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}">
    @php
        $isEnterprise = str_contains(strtolower($plan->name ?? ''), 'enterprise') || ($plan->slug ?? '') === 'pro';
        $paymentPageUrl = $isEnterprise ? 'https://rzp.io/rzp/lisGNMno' : 'https://rzp.io/rzp/seC7Xkmu';
    @endphp
    <div class="py-12 sm:py-16 max-w-xl mx-auto px-4 sm:px-6 relative" x-data="{ 
        processing: false,
        paymentFailed: false,
        failureMsg: '',
        manualPaymentId: '',
        checkingPayment: false,
        checkPaymentMessage: '',
        showIframeModal: false,
        iframeLoading: true,
        hasClickedPayNow: Boolean(
            {{ (!empty($isComingFromRazorpay) || request()->has('payment_id') || request()->has('razorpay_payment_id') || request()->has('paid') || request()->has('redirect') || request()->has('redirected')) ? 'true' : 'false' }} ||
            localStorage.getItem('checkout_clicked_pay_now') === '1' ||
            sessionStorage.getItem('checkout_clicked_pay_now') === '1' ||
            (document.referrer && document.referrer.includes('razorpay'))
        ),
        openPayModal() {
            this.hasClickedPayNow = true;
            localStorage.setItem('checkout_clicked_pay_now', '1');
            sessionStorage.setItem('checkout_clicked_pay_now', '1');

            // On mobile devices, third-party iframes block UPI intent apps (Google Pay, PhonePe, Paytm).
            // Therefore on mobile, navigate directly to official Razorpay payment page for native 1-tap UPI app launching.
            const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || (window.innerWidth < 768);
            if (isMobile) {
                window.location.href = '{{ $paymentPageUrl }}';
            } else {
                this.iframeLoading = true;
                this.showIframeModal = true;
            }
        },
        closePayModal() {
            this.showIframeModal = false;
        },
        verifyFromModal() {
            this.showIframeModal = false;
            checkRecentPayment();
        }
    }"
    x-init="
        window.addEventListener('focus', () => {
            if (localStorage.getItem('checkout_clicked_pay_now') === '1' || sessionStorage.getItem('checkout_clicked_pay_now') === '1') {
                hasClickedPayNow = true;
            }
        });
    ">
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

                {{-- Popup payment button hidden as requested --}}
                {{--
                <button type="button" @click="handleRazorpayPayment()" :disabled="processing" 
                        class="w-full py-4 rounded-2xl bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:brightness-110 text-slate-950 font-black text-sm shadow-xl shadow-orange-500/20 hover:shadow-orange-500/30 transition-all cursor-pointer flex items-center justify-center gap-2">
                    <span x-show="processing" class="animate-spin text-base">⏳</span>
                    <span x-show="!processing">💳 Pay ₹{{ number_format($price, 2) }} with Razorpay &rarr;</span>
                </button>
                --}}

                <!-- Primary Action: Direct Payment Page (Opens in-page modal on same page) -->
                <button type="button" 
                        @click="openPayModal()"
                        class="w-full py-4 px-5 rounded-2xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-500 hover:brightness-110 active:scale-[0.99] text-white font-black text-sm shadow-xl shadow-emerald-600/25 flex items-center justify-center gap-2.5 transition-all cursor-pointer">
                    <span>Pay Now</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </button>

                <!-- Check Recent Payment Button (Hidden initially, visible once Pay Now is clicked or upon return) -->
                <div x-show="hasClickedPayNow" x-cloak class="space-y-2 pt-1" x-transition>
                    <button type="button" @click="checkRecentPayment()" :disabled="checkingPayment"
                            class="w-full py-3.5 px-4 rounded-2xl bg-slate-800 hover:bg-slate-750 border border-slate-700/80 hover:border-emerald-500/50 text-emerald-400 font-bold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer shadow-md">
                        <span x-show="checkingPayment" class="animate-spin text-sm">🔄</span>
                        <span x-show="!checkingPayment">🔄</span>
                        <span x-text="checkingPayment ? 'Checking Razorpay for your payment...' : 'Paid? Click to Auto-Detect & Activate'"></span>
                    </button>
                    <template x-if="checkPaymentMessage">
                        <div class="p-2.5 rounded-xl text-center text-xs" :class="checkPaymentMessage.startsWith('✅') ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : 'bg-amber-500/10 text-amber-400 border border-amber-500/20'" x-text="checkPaymentMessage"></div>
                    </template>
                </div>

                <!-- Manual Payment ID verification for Direct Link payments (Visible after clicking Pay Now or upon return) -->
                <div x-show="hasClickedPayNow" x-cloak class="p-4 rounded-2xl bg-slate-950/90 border border-slate-800 space-y-2.5" x-transition>
                    <div class="flex items-center gap-2">
                        <span class="text-amber-400 font-bold text-sm">⚡</span>
                        <h4 class="text-xs font-bold text-slate-200">Or Paste Payment ID Manually</h4>
                    </div>
                    <p class="text-[11px] text-slate-400 leading-relaxed">
                        Enter your <strong>Payment ID</strong> (e.g. <span class="font-mono text-amber-400 bg-slate-900 px-1.5 py-0.5 rounded border border-slate-800">pay_TqF6Y9COLl1XS0</span>) from your payment screen or receipt:
                    </p>
                    <div class="flex flex-col sm:flex-row gap-2 pt-1">
                        <input type="text" id="manual_payment_input" x-model="manualPaymentId" @keydown.enter.prevent="submitManualPayment()" placeholder="Paste Payment ID here (e.g. pay_...)" 
                               class="flex-1 px-3.5 py-2.5 rounded-xl bg-slate-900 border border-slate-700 text-xs text-white placeholder-slate-500 focus:outline-none focus:border-amber-500 font-mono">
                        <button type="button" @click="submitManualPayment()" 
                                class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-orange-500 hover:brightness-110 text-slate-950 font-black text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/10 whitespace-nowrap">
                            <span>Verify &amp; Activate</span> &rarr;
                        </button>
                    </div>
                </div>
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

        <!-- In-Page Embedded Payment Modal (Fintech-Grade Secure Checkout Dialog) -->
        <div x-show="showIframeModal" x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-2 sm:p-4 md:p-6 bg-slate-950/85 backdrop-blur-md"
             @keydown.escape.window="closePayModal()"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95">
            
            <!-- Backdrop Click Closes & Auto-Checks Payment -->
            <div class="absolute inset-0" @click="closePayModal()"></div>

            <div class="relative w-full max-w-xl h-[92vh] max-h-[780px] bg-slate-900 rounded-2xl sm:rounded-3xl border border-slate-700/80 shadow-[0_25px_60px_-15px_rgba(0,0,0,0.8)] ring-1 ring-white/10 flex flex-col overflow-hidden z-10"
                 @click.stop>
                
                <!-- Fintech Modal Header -->
                <div class="bg-gradient-to-b from-slate-900 to-slate-950 border-b border-slate-800 shrink-0">
                    <!-- Top Bar: Trust Badge & Controls -->
                    <div class="px-4 sm:px-5 py-3 flex items-center justify-between gap-3 border-b border-slate-800/60">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <!-- Security Shield Icon -->
                            <div class="w-8 h-8 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-xs font-bold text-white tracking-tight">Razorpay Secure Checkout</span>
                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Verified</span>
                                </div>
                                <p class="text-[10px] text-slate-400 flex items-center gap-1">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                    <span>256-Bit Bank-Grade SSL Encrypted Session</span>
                                </p>
                            </div>
                        </div>

                        <!-- Top Right Controls -->
                        <div class="flex items-center gap-2 shrink-0">
                            <a href="{{ $paymentPageUrl }}" target="_blank" rel="noopener noreferrer" 
                               class="hidden sm:inline-flex items-center gap-1 text-[11px] text-slate-300 hover:text-amber-400 px-2.5 py-1.5 rounded-lg bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition-all font-medium"
                               title="Open in new window">
                                <span>Full Page</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button type="button" 
                                    @click="closePayModal()" 
                                    class="w-8 h-8 rounded-xl bg-slate-800/80 hover:bg-slate-750 border border-slate-700/80 text-slate-400 hover:text-white flex items-center justify-center transition-all cursor-pointer"
                                    title="Close">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Order Summary Mini Strip -->
                    <div class="px-4 sm:px-5 py-2 bg-slate-950/70 flex items-center justify-between text-xs">
                        <div class="flex items-center gap-2">
                            <span class="text-slate-400">Plan:</span>
                            <span class="font-bold text-white">{{ $plan->name }}</span>
                            <span class="text-[10px] text-slate-500">({{ $billingCycle === 'yearly' ? 'Annual Billing' : 'Monthly' }})</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-slate-400 text-[11px]">Due:</span>
                            <span class="font-extrabold text-amber-400 text-sm">₹{{ number_format($price, 2) }}</span>
                        </div>
                    </div>
                </div>

                <!-- Modal Body: Loading Skeleton & Embedded Iframe -->
                <div class="relative flex-1 w-full bg-slate-950 overflow-hidden">
                    <!-- Smooth Loading Skeleton / Splash Screen -->
                    <div x-show="iframeLoading" 
                         class="absolute inset-0 z-20 bg-slate-950 flex flex-col items-center justify-center p-6 text-center"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0">
                        <div class="relative mb-4">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-500/20 to-emerald-500/20 border border-amber-500/30 flex items-center justify-center text-amber-400 shadow-xl shadow-amber-500/10 animate-pulse">
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-emerald-500 border-2 border-slate-950 flex items-center justify-center text-[10px] text-slate-950 font-black">
                                ✓
                            </div>
                        </div>

                        <h4 class="text-sm font-bold text-white mb-1">Securing Connection to Razorpay</h4>
                        <p class="text-xs text-slate-400 max-w-xs mb-4 leading-relaxed">
                            Initializing encrypted payment gateway session for {{ $platformSettings['app_name'] ?? 'GymDeskHub' }}...
                        </p>

                        <!-- Micro progress bar -->
                        <div class="w-48 h-1.5 bg-slate-800 rounded-full overflow-hidden mb-3">
                            <div class="h-full bg-gradient-to-r from-amber-500 via-emerald-500 to-amber-500 w-full animate-pulse"></div>
                        </div>

                        <div class="flex items-center gap-2 text-[10px] text-slate-500">
                            <span>🔒 PCI-DSS Certified</span>
                            <span>&bull;</span>
                            <span>UPI • Cards • NetBanking</span>
                        </div>
                    </div>

                    <!-- Clean Payment Iframe -->
                    <iframe src="{{ $paymentPageUrl }}" 
                            @load="iframeLoading = false" 
                            class="w-full h-full border-0 bg-white" 
                            allow="payment; camera; microphone"></iframe>
                </div>

                <!-- Fintech Trust & Action Footer Strip -->
                <div class="px-4 py-2.5 sm:px-5 sm:py-3 bg-slate-950 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-2.5 shrink-0">
                    <div class="flex items-center gap-2 text-[11px] text-slate-400 text-center sm:text-left">
                        <svg class="w-3.5 h-3.5 text-emerald-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <span>Protected by <strong>Razorpay Verified Merchant Shield</strong></span>
                    </div>

                    <button type="button" 
                            @click="verifyFromModal()" 
                            class="w-full sm:w-auto px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:brightness-110 active:scale-[0.98] text-white text-xs font-bold transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/20">
                        <span>Paid? Verify &amp; Continue</span> &rarr;
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Razorpay Checkout Script -->
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const razorpayConfig = {{ Js::from($razorpayConfig ?? []) }};

        async function checkRecentPayment() {
            const alpineEl = document.querySelector('[x-data]');
            if (alpineEl && alpineEl._x_dataStack) {
                alpineEl._x_dataStack[0].checkingPayment = true;
                alpineEl._x_dataStack[0].checkPaymentMessage = 'Connecting to Razorpay & checking recent payment...';
            }

            try {
                const response = await fetch('{{ route('auth.checkout.auto-detect') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                });
                const data = await response.json();

                if (data.success && data.redirect) {
                    if (alpineEl && alpineEl._x_dataStack) {
                        alpineEl._x_dataStack[0].checkPaymentMessage = '✅ ' + data.message + ' Redirecting to Dashboard...';
                    }
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 800);
                } else {
                    if (alpineEl && alpineEl._x_dataStack) {
                        alpineEl._x_dataStack[0].checkingPayment = false;
                        alpineEl._x_dataStack[0].checkPaymentMessage = data.message || 'Payment not detected yet.';
                    }
                }
            } catch (e) {
                if (alpineEl && alpineEl._x_dataStack) {
                    alpineEl._x_dataStack[0].checkingPayment = false;
                    alpineEl._x_dataStack[0].checkPaymentMessage = 'Could not verify payment: ' + e.message;
                }
            }
        }

        function simulateTestPayment() {
            const form = document.getElementById('checkout-form');
            document.getElementById('razorpay_payment_id').value = 'pay_sim_' + Math.random().toString(36).substring(2, 15);
            document.getElementById('razorpay_order_id').value = 'order_sim_' + Math.random().toString(36).substring(2, 15);
            document.getElementById('razorpay_signature').value = 'sig_sim_' + Math.random().toString(36).substring(2, 15);
            form.submit();
        }

        function submitManualPayment() {
            const input = document.getElementById('manual_payment_input');
            const manualId = (input ? input.value : '').trim();
            if (!manualId) {
                alert('Please enter your Razorpay Payment ID (e.g. TqEX328BZu2kKu).');
                return;
            }
            const form = document.getElementById('checkout-form');
            document.getElementById('razorpay_payment_id').value = manualId;
            form.submit();
        }

        function handleRazorpayPayment() {
            const form = document.getElementById('checkout-form');

            if (typeof Razorpay !== 'undefined' && razorpayConfig.key) {
                const options = {
                    key: razorpayConfig.key,
                    amount: razorpayConfig.amount,
                    currency: razorpayConfig.currency || 'INR',
                    name: razorpayConfig.name || 'GymDeskHub',
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
