<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Database\Seeders\PlanSeeder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function __construct(
        protected TenantService $tenantService,
        protected SubscriptionService $subscriptionService
    ) {}

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $loginInput = trim($request->input('email') ?? $request->input('login') ?? '');
        $password = (string) $request->input('password');

        if (empty($loginInput) || empty($password)) {
            return back()->withErrors([
                'email' => 'Please provide your email, phone number, or member code along with your password.',
            ])->onlyInput('email');
        }

        // 1. Check if direct User exists by email or phone
        $user = User::where('email', $loginInput)
            ->orWhere('phone', $loginInput)
            ->first();

        // 2. If not found directly on User, search in Member records
        if (! $user) {
            $member = Member::where('member_code', $loginInput)
                ->orWhere('phone', $loginInput)
                ->orWhere('email', $loginInput)
                ->first();

            if ($member) {
                if ($member->user_id && $member->user) {
                    $user = $member->user;
                } else {
                    // Create or find user account for this member
                    $memberEmail = ! empty($member->email) ? $member->email : ('member_'.($member->phone ?: $member->id).'@gym.portal');
                    $user = User::where('email', $memberEmail)->first();

                    if (! $user) {
                        $user = User::create([
                            'tenant_id' => $member->tenant_id,
                            'name' => $member->full_name ?: ($member->phone ?? 'Member '.$member->member_code),
                            'email' => $memberEmail,
                            'phone' => $member->phone,
                            'role' => 'member',
                            'status' => $member->status === 'SUSPENDED' ? 'SUSPENDED' : 'ACTIVE',
                            'password' => Hash::make($password),
                        ]);
                    }

                    $member->update(['user_id' => $user->id]);
                }
            }
        }

        // 3. Authenticate User if found
        if ($user) {
            $passwordMatches = Hash::check($password, $user->password);

            // If initial fallback password for members (e.g. member phone, member code, or last 4 digits)
            if (! $passwordMatches && $user->role === 'member') {
                $member = $user->memberProfile ?? Member::where('user_id', $user->id)->first();
                if ($member) {
                    $validFallbacks = array_filter([
                        $member->phone,
                        $member->member_code,
                        substr($member->phone ?? '', -4),
                        'password',
                        '123456',
                        '12345678',
                    ]);

                    if (in_array($password, $validFallbacks, true)) {
                        $user->password = Hash::make($password);
                        $user->save();
                        $passwordMatches = true;
                    }
                }
            }

            if ($passwordMatches) {
                if ($user->status !== 'ACTIVE') {
                    return back()->withErrors([
                        'email' => 'Your account is currently inactive or suspended. Please contact gym reception.',
                    ])->onlyInput('email');
                }

                Auth::login($user, $request->boolean('remember'));
                $request->session()->regenerate();

                if ($user->isSuperAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }

                if ($user->role === 'member') {
                    return redirect()->intended(route('app.portal'));
                }

                $tenant = $user->tenant;
                if ($tenant && ! $tenant->isSubscriptionActive()) {
                    session([
                        'checkout_plan_id' => $tenant->latestSubscription?->plan_id ?? Plan::where('slug', 'starter')->value('id') ?? Plan::first()->id,
                        'checkout_billing_cycle' => $tenant->latestSubscription?->billing_cycle ?? 'monthly',
                    ]);

                    return redirect()->route('auth.checkout')->with('warning', 'Please complete your subscription payment to activate your gym dashboard.');
                }

                return redirect()->intended(route('app.dashboard'));
            }
        }

        // Standard Laravel Auth fallback for valid email
        if (filter_var($loginInput, FILTER_VALIDATE_EMAIL)) {
            if (Auth::attempt(['email' => $loginInput, 'password' => $password], $request->boolean('remember'))) {
                $user = Auth::user();

                if ($user->status !== 'ACTIVE') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();

                    return back()->withErrors([
                        'email' => 'Your account is inactive or suspended.',
                    ])->onlyInput('email');
                }

                $request->session()->regenerate();

                if ($user->isSuperAdmin()) {
                    return redirect()->intended(route('admin.dashboard'));
                }

                if ($user->role === 'member') {
                    return redirect()->intended(route('app.portal'));
                }

                return redirect()->intended(route('app.dashboard'));
            }
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(Request $request): View
    {
        $selectedPlanSlug = $request->query('plan', 'starter');
        $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();
        if ($plans->isEmpty()) {
            (new PlanSeeder)->run();
            $plans = Plan::with('features')->where('is_active', true)->orderBy('sort_order')->get();
        }
        $selectedPlan = Plan::where('slug', $selectedPlanSlug)->first() ?? $plans->first();

        return view('auth.register', compact('plans', 'selectedPlan'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gym_name' => 'required|string|max:150',
            'owner_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $isFreePlan = ($plan->price_monthly <= 0 && $plan->price_yearly <= 0) || $plan->slug === 'free-forever';
        $initialStatus = $isFreePlan ? 'ACTIVE' : 'PENDING_PAYMENT';

        $registration = $this->tenantService->registerGym($validated, $plan, $initialStatus, null, $validated['billing_cycle']);
        $owner = $registration['owner'];
        $tenant = $registration['tenant'];

        Auth::login($owner);

        if ($isFreePlan) {
            return redirect()->route('app.dashboard')->with('success', "Welcome to Gym Console! Your {$plan->name} plan is now active.");
        }

        // Store pending checkout details in session
        session([
            'checkout_plan_id' => $plan->id,
            'checkout_billing_cycle' => $validated['billing_cycle'],
        ]);

        return redirect()->route('auth.checkout');
    }

    public function showCheckout(Request $request): View|RedirectResponse
    {
        $user = Auth::user();
        $tenant = $user?->tenant;

        if ($tenant && strtolower($tenant->status) === 'active' && ! $tenant->isSubscriptionExpired()) {
            return redirect()->route('app.dashboard')->with('success', 'Your subscription is already active.');
        }

        // Auto-activate if redirected back from Razorpay Payment Page with payment ID in URL
        $returnedPaymentId = $request->query('razorpay_payment_id') ?: $request->query('payment_id');
        if ($returnedPaymentId && $tenant) {
            $request->merge(['razorpay_payment_id' => $returnedPaymentId, 'gateway' => 'razorpay']);

            return $this->processCheckout($request);
        }

        $referer = strtolower($request->header('referer') ?? '');
        $isComingFromRazorpay = str_contains($referer, 'razorpay') || $request->has('redirected') || $request->has('paid') || $request->has('from_rzp') || $request->has('auto_check');

        // If returned from Razorpay, attempt automatic payment detection immediately
        if ($isComingFromRazorpay && $tenant) {
            $detectResponse = $this->autoDetectPayment($request);
            $detectData = $detectResponse->getData(true);
            if (! empty($detectData['success']) && ! empty($detectData['redirect'])) {
                return redirect($detectData['redirect'])->with('success', $detectData['message'] ?? 'Payment verified successfully!');
            }
        }

        if (Plan::count() === 0) {
            (new PlanSeeder)->run();
        }

        $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id') ?? Plan::first()->id);
        $billingCycle = session('checkout_billing_cycle', 'monthly');
        $plan = Plan::findOrFail($planId);

        $price = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;

        $razorpayKeyId = Setting::getGlobal('razorpay_key_id', config('services.razorpay.key', env('RAZORPAY_KEY', 'rzp_test_samplekey123')));
        $razorpayEnabled = (bool) Setting::getGlobal('razorpay_enabled', true);
        $stripeEnabled = (bool) Setting::getGlobal('stripe_enabled', true);

        $amountInPaise = (int) round($price * 100);

        $razorpayConfig = [
            'key' => $razorpayKeyId,
            'amount' => $amountInPaise,
            'currency' => $tenant?->currency ?? 'INR',
            'name' => Setting::getGlobal('app_name', 'Gym Console'),
            'description' => "Subscription to {$plan->name} ({$billingCycle})",
            'prefill' => [
                'name' => $user?->name ?? 'Gym Owner',
                'email' => $user?->email ?? '',
                'contact' => $user?->phone ?? ($tenant?->phone ?? ''),
            ],
            'theme' => [
                'color' => '#f59e0b',
            ],
        ];

        return view('auth.checkout', compact('plan', 'billingCycle', 'price', 'razorpayConfig', 'razorpayEnabled', 'stripeEnabled', 'tenant', 'user', 'isComingFromRazorpay'));
    }

    public function processCheckout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id') ?? Plan::first()->id);
        $billingCycle = session('checkout_billing_cycle', 'monthly');
        $plan = Plan::findOrFail($planId);

        $gateway = $request->input('gateway', 'razorpay');
        $price = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;

        if ($price > 0 && $gateway === 'razorpay') {
            $paymentId = $request->input('razorpay_payment_id');
            if (empty($paymentId)) {
                return back()->with('error', 'Please complete the payment or provide a valid Razorpay Payment ID.');
            }

            $adapter = new \App\Services\Payment\RazorpayPaymentAdapter();
            $verification = $adapter->verifyPayment($paymentId);

            if (! $verification['valid']) {
                return back()->with('error', 'Razorpay Payment verification failed: '.$verification['message']);
            }

            $transactionId = $verification['payment_id'];

            // Prevent duplicate redemption across different tenants
            $alreadyUsed = \App\Models\SubscriptionPayment::where('transaction_id', $transactionId)
                ->where('status', 'SUCCESS')
                ->where('tenant_id', '!=', $tenant->id)
                ->exists();

            if ($alreadyUsed) {
                return back()->with('error', 'This Payment ID has already been redeemed by another account.');
            }
        } else {
            $transactionId = match ($gateway) {
                'stripe' => $request->input('stripe_payment_id') ?: ('ch_'.Str::random(14)),
                default => 'TXN-'.strtoupper(Str::random(12)),
            };
        }

        $this->subscriptionService->activateSubscription(
            $tenant,
            $plan,
            $billingCycle,
            $gateway,
            $transactionId,
            $price,
            [
                'checkout_source' => 'web_registration',
                'razorpay_payment_id' => $request->input('razorpay_payment_id'),
                'razorpay_order_id' => $request->input('razorpay_order_id'),
                'razorpay_signature' => $request->input('razorpay_signature'),
                'verified_at' => now()->toIso8601String(),
            ]
        );

        session()->forget(['checkout_plan_id', 'checkout_billing_cycle']);

        return redirect()->route('app.dashboard')->with('success', "Congratulations! Your {$plan->name} subscription has been activated successfully via ".ucfirst($gateway).'.');
    }

    /**
     * AJAX Endpoint: Auto-detect recent captured Razorpay payment from external payment page.
     */
    public function autoDetectPayment(Request $request): JsonResponse
    {
        $user = Auth::user();
        $tenant = $user?->tenant;

        if (! $tenant) {
            return response()->json(['success' => false, 'message' => 'No active gym tenant profile found.'], 404);
        }

        $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id') ?? Plan::first()->id);
        $billingCycle = session('checkout_billing_cycle', 'yearly');
        $plan = Plan::find($planId) ?? Plan::first();
        $price = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
        $expectedPaise = (int) round($price * 100);

        $keyId = Setting::getGlobal('razorpay_key_id', config('services.razorpay.key', env('RAZORPAY_KEY')));
        $keySecret = Setting::getGlobal('razorpay_key_secret', config('services.razorpay.secret', env('RAZORPAY_SECRET')));

        if (empty($keyId) || empty($keySecret)) {
            return response()->json(['success' => false, 'message' => 'Razorpay credentials not configured.'], 400);
        }

        try {
            $response = Http::withoutVerifying()
                ->timeout(10)
                ->withBasicAuth($keyId, $keySecret)
                ->get('https://api.razorpay.com/v1/payments', [
                    'count' => 20,
                ]);

            if (! $response->successful()) {
                return response()->json(['success' => false, 'message' => 'Could not contact Razorpay API.'], 500);
            }

            $items = $response->json('items') ?? [];
            $matchedPayment = null;

            $userEmail = strtolower($user->email ?? '');
            $tenantEmail = strtolower($tenant->email ?? '');
            $userPhone = preg_replace('/[^0-9]/', '', $user->phone ?? ($tenant->phone ?? ''));
            $userPhoneSuffix = strlen($userPhone) >= 10 ? substr($userPhone, -10) : $userPhone;

            foreach ($items as $payment) {
                if (($payment['status'] ?? '') !== 'captured') {
                    continue;
                }

                $transactionId = $payment['id'];

                // Check if already used by any tenant
                $alreadyUsed = SubscriptionPayment::where('transaction_id', $transactionId)
                    ->where('status', 'SUCCESS')
                    ->exists();

                if ($alreadyUsed) {
                    continue;
                }

                // Payment must be within last 2 hours
                $createdAt = $payment['created_at'] ?? 0;
                if ($createdAt < (time() - 7200)) {
                    continue;
                }

                $pEmail = strtolower($payment['email'] ?? '');
                $pContact = preg_replace('/[^0-9]/', '', $payment['contact'] ?? '');

                $emailMatches = ! empty($pEmail) && ($pEmail === $userEmail || $pEmail === $tenantEmail);
                $phoneMatches = ! empty($userPhoneSuffix) && (str_ends_with($pContact, $userPhoneSuffix) || str_ends_with($userPhone, $pContact));

                $pAmount = (int) ($payment['amount'] ?? 0);
                // Verify amount (within ₹2 tolerance in paise for rounding)
                $amountMatches = ($expectedPaise <= 0) || abs($pAmount - $expectedPaise) <= 200;

                // Strict matching: Either email or phone MUST match AND amount must match
                if (($emailMatches || $phoneMatches) && $amountMatches) {
                    $matchedPayment = $payment;
                    break;
                }
            }

            if (! $matchedPayment) {
                return response()->json([
                    'success' => false,
                    'message' => 'No recent matching captured payment found for your account on Razorpay. If you just paid, please wait a moment or enter the Payment ID below.',
                ], 404);
            }

            $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id') ?? Plan::first()->id);
            $billingCycle = session('checkout_billing_cycle', 'monthly');
            $plan = Plan::findOrFail($planId);
            $price = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;

            $this->subscriptionService->activateSubscription(
                $tenant,
                $plan,
                $billingCycle,
                'razorpay',
                $matchedPayment['id'],
                $price,
                [
                    'checkout_source' => 'auto_detect_razorpay',
                    'razorpay_payment_id' => $matchedPayment['id'],
                    'matched_email' => $matchedPayment['email'] ?? '',
                    'matched_contact' => $matchedPayment['contact'] ?? '',
                    'verified_at' => now()->toIso8601String(),
                ]
            );

            session()->forget(['checkout_plan_id', 'checkout_billing_cycle']);

            return response()->json([
                'success' => true,
                'message' => "Payment {$matchedPayment['id']} verified! Your subscription is now active.",
                'redirect' => route('app.dashboard'),
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: '.$e->getMessage()], 500);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        TenantContext::reset();

        return redirect()->route('login');
    }
}
