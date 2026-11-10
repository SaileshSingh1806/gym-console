<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\RegistrationOtpMail;
use App\Models\Member;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\SubscriptionPayment;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Database\Seeders\PlanSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

        $digitsOnly = preg_replace('/[^0-9]/', '', $loginInput);
        $suffix10 = strlen($digitsOnly) >= 10 ? substr($digitsOnly, -10) : $digitsOnly;

        // 1. Check if direct User exists by email or phone
        $userQuery = User::where('email', $loginInput);
        if (! empty($digitsOnly)) {
            $userQuery->orWhere('phone', $loginInput)
                      ->orWhere('phone', 'like', "%{$suffix10}");
        }
        $user = $userQuery->first();

        // 2. If not found directly on User, search in Member records
        if (! $user) {
            $memberQuery = Member::where('member_code', $loginInput)
                ->orWhere('email', $loginInput);
            if (! empty($digitsOnly)) {
                $memberQuery->orWhere('phone', $loginInput)
                            ->orWhere('phone', 'like', "%{$suffix10}");
            }
            $member = $memberQuery->first();

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

                if ($user->role === 'trainer') {
                    return redirect()->intended(route('trainer.workspace'));
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

                if ($user->role === 'trainer') {
                    return redirect()->intended(route('trainer.workspace'));
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
        $pending = session('pending_registration', []);

        return view('auth.register', compact('plans', 'selectedPlan', 'pending'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gym_name' => 'required|string|max:150',
            'branch_name' => 'nullable|string|max:150',
            'owner_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $otp = (string) random_int(100000, 999999);

        session([
            'pending_registration' => $validated,
            'registration_otp' => $otp,
            'registration_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'registration_otp_resend_after' => now()->addSeconds(60)->timestamp,
            'registration_otp_attempts' => 0,
        ]);

        try {
            Mail::to($validated['email'])->send(new RegistrationOtpMail($otp, $validated['owner_name'], $validated['gym_name']));
        } catch (\Throwable $e) {
            Log::error('Registration OTP mail error: '.$e->getMessage());
        }

        return redirect()->route('register.verify-otp')->with('success', "A 6-digit verification code has been sent to {$validated['email']}.");
    }

    public function showVerifyOtp(Request $request): View|RedirectResponse
    {
        if (! session()->has('pending_registration') || ! session()->has('registration_otp')) {
            return redirect()->route('register')->with('warning', 'Please fill in your gym registration details first.');
        }

        $pending = session('pending_registration');
        $email = $pending['email'];
        $resendAfter = session('registration_otp_resend_after', 0);
        $remainingSeconds = max(0, $resendAfter - now()->timestamp);

        return view('auth.verify-otp', compact('email', 'pending', 'remainingSeconds'));
    }

    public function verifyOtp(Request $request): RedirectResponse
    {
        if (! session()->has('pending_registration') || ! session()->has('registration_otp')) {
            return redirect()->route('register')->with('error', 'Registration session expired. Please fill in your details again.');
        }

        $request->validate([
            'otp' => 'required|string|size:6',
        ], [
            'otp.required' => 'Please enter the 6-digit verification code.',
            'otp.size' => 'The verification code must be exactly 6 digits.',
        ]);

        $attempts = session('registration_otp_attempts', 0) + 1;
        session(['registration_otp_attempts' => $attempts]);

        if ($attempts > 5) {
            session()->forget(['registration_otp', 'registration_otp_expires_at']);

            return back()->withErrors(['otp' => 'Too many failed verification attempts. Please click "Resend Code" to receive a new code.']);
        }

        $expiresAt = session('registration_otp_expires_at', 0);
        if (now()->timestamp > $expiresAt) {
            return back()->withErrors(['otp' => 'This verification code has expired. Please click "Resend Code" to receive a new code.']);
        }

        $storedOtp = (string) session('registration_otp');
        $enteredOtp = trim((string) $request->input('otp'));

        if ($enteredOtp !== $storedOtp) {
            return back()->withErrors(['otp' => 'Incorrect verification code. Please check your email and try again.'])->onlyInput('otp');
        }

        $validated = session('pending_registration');

        // Check email uniqueness again to avoid race conditions
        if (User::where('email', $validated['email'])->exists()) {
            session()->forget(['pending_registration', 'registration_otp', 'registration_otp_expires_at', 'registration_otp_resend_after', 'registration_otp_attempts']);

            return redirect()->route('login')->withErrors(['email' => 'An account with this email already exists. Please log in.']);
        }

        $plan = Plan::findOrFail($validated['plan_id']);
        $isFreePlan = ($plan->price_monthly <= 0 && $plan->price_yearly <= 0) || $plan->slug === 'free-forever';
        $initialStatus = $isFreePlan ? 'ACTIVE' : 'PENDING_PAYMENT';

        $registration = $this->tenantService->registerGym($validated, $plan, $initialStatus, null, $validated['billing_cycle']);
        $owner = $registration['owner'];
        $tenant = $registration['tenant'];

        // Clear OTP & registration session data
        session()->forget([
            'pending_registration',
            'registration_otp',
            'registration_otp_expires_at',
            'registration_otp_resend_after',
            'registration_otp_attempts',
        ]);

        Auth::login($owner);

        if ($isFreePlan) {
            return redirect()->route('app.dashboard')->with('success', "Welcome to GymDeskHub! Your {$plan->name} plan is now active.");
        }

        // Store pending checkout details in session
        session([
            'checkout_plan_id' => $plan->id,
            'checkout_billing_cycle' => $validated['billing_cycle'],
        ]);

        return redirect()->route('auth.checkout')->with('success', 'Email verified successfully! Complete checkout to activate your gym console.');
    }

    public function resendOtp(Request $request): RedirectResponse
    {
        if (! session()->has('pending_registration')) {
            return redirect()->route('register')->with('warning', 'Please fill in your gym registration details first.');
        }

        $resendAfter = session('registration_otp_resend_after', 0);
        if (now()->timestamp < $resendAfter) {
            $wait = $resendAfter - now()->timestamp;

            return back()->with('warning', "Please wait {$wait} seconds before requesting a new code.");
        }

        $pending = session('pending_registration');
        $otp = (string) random_int(100000, 999999);

        session([
            'registration_otp' => $otp,
            'registration_otp_expires_at' => now()->addMinutes(10)->timestamp,
            'registration_otp_resend_after' => now()->addSeconds(60)->timestamp,
            'registration_otp_attempts' => 0,
        ]);

        try {
            Mail::to($pending['email'])->send(new RegistrationOtpMail($otp, $pending['owner_name'], $pending['gym_name']));
        } catch (\Throwable $e) {
            Log::error('Resend registration OTP mail error: '.$e->getMessage());
        }

        return back()->with('success', "A new 6-digit verification code has been sent to {$pending['email']}.");
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
            'name' => Setting::getGlobal('app_name', 'GymDeskHub'),
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

                // Payment must be within last 24 hours
                $createdAt = $payment['created_at'] ?? 0;
                if ($createdAt < (time() - 86400)) {
                    continue;
                }

                $pEmail = strtolower($payment['email'] ?? '');
                $pContact = preg_replace('/[^0-9]/', '', $payment['contact'] ?? '');
                $notes = $payment['notes'] ?? [];
                $noteEmail = strtolower($notes['email'] ?? '');
                $notePhone = preg_replace('/[^0-9]/', '', $notes['phone'] ?? ($notes['contact'] ?? ''));

                $emailMatches = (! empty($pEmail) && ($pEmail === $userEmail || $pEmail === $tenantEmail))
                    || (! empty($noteEmail) && ($noteEmail === $userEmail || $noteEmail === $tenantEmail));

                $phoneMatches = (! empty($userPhoneSuffix) && (str_ends_with($pContact, $userPhoneSuffix) || str_ends_with($userPhone, $pContact)))
                    || (! empty($userPhoneSuffix) && ! empty($notePhone) && (str_ends_with($notePhone, $userPhoneSuffix) || str_ends_with($userPhone, $notePhone)));

                $pAmount = (int) ($payment['amount'] ?? 0);
                // Verify amount (within ₹2 tolerance in paise, or any captured payment matching user's phone/email)
                $amountMatches = ($expectedPaise <= 0) || abs($pAmount - $expectedPaise) <= 200 || ($pAmount > 0 && ($emailMatches || $phoneMatches));

                // If either email or phone matches and amount is valid
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

    public function showMemberPasswordSetup(string $token): View|RedirectResponse
    {
        $hashedToken = hash('sha256', $token);
        $member = Member::where('metadata->portal_setup_token', $hashedToken)->first();

        if (! $member) {
            $member = Member::all()->first(function ($m) use ($hashedToken) {
                $meta = is_array($m->metadata) ? $m->metadata : json_decode($m->metadata ?? '', true);

                return ($meta['portal_setup_token'] ?? null) === $hashedToken;
            });
        }

        if (! $member) {
            return redirect()->route('login')->withErrors(['email' => 'This password setup link is invalid or has already been used. Please request a new link.']);
        }

        $meta = is_array($member->metadata) ? $member->metadata : json_decode($member->metadata ?? '', true);
        $expiresAt = isset($meta['portal_setup_expires_at']) ? Carbon::parse($meta['portal_setup_expires_at']) : null;
        if ($expiresAt && $expiresAt->isPast()) {
            return redirect()->route('login')->withErrors(['email' => 'This password setup link has expired. Please request a new link.']);
        }

        $tenant = $member->tenant;

        return view('auth.member_setup_password', compact('member', 'tenant', 'token'));
    }

    public function saveMemberPasswordSetup(string $token, Request $request): RedirectResponse
    {
        $request->validate([
            'password' => 'required|string|min:6|confirmed',
        ], [
            'password.confirmed' => 'Passwords do not match.',
            'password.min' => 'Password must be at least 6 characters long.',
        ]);

        $hashedToken = hash('sha256', $token);
        $member = Member::where('metadata->portal_setup_token', $hashedToken)->first();

        if (! $member) {
            $member = Member::all()->first(function ($m) use ($hashedToken) {
                $meta = is_array($m->metadata) ? $m->metadata : json_decode($m->metadata ?? '', true);

                return ($meta['portal_setup_token'] ?? null) === $hashedToken;
            });
        }

        if (! $member) {
            return redirect()->route('login')->withErrors(['email' => 'This setup link is invalid or expired.']);
        }

        $meta = is_array($member->metadata) ? $member->metadata : (json_decode($member->metadata ?? '', true) ?? []);
        $expiresAt = isset($meta['portal_setup_expires_at']) ? Carbon::parse($meta['portal_setup_expires_at']) : null;
        if ($expiresAt && $expiresAt->isPast()) {
            return redirect()->route('login')->withErrors(['email' => 'This setup link has expired.']);
        }

        $user = $member->user;
        if (! $user) {
            $memberEmail = ! empty($member->email) ? $member->email : ('member_'.($member->phone ?: $member->id).'@gym.portal');
            $user = User::where('email', $memberEmail)->first();
            if (! $user) {
                $user = User::create([
                    'tenant_id' => $member->tenant_id,
                    'name' => $member->full_name ?: ($member->phone ?? 'Member '.$member->member_code),
                    'email' => $memberEmail,
                    'phone' => $member->phone,
                    'role' => 'member',
                    'status' => 'ACTIVE',
                    'password' => Hash::make($request->password),
                ]);
            } else {
                $user->password = Hash::make($request->password);
                $user->role = 'member';
                $user->status = 'ACTIVE';
                $user->save();
            }
            $member->update(['user_id' => $user->id]);
        } else {
            $user->password = Hash::make($request->password);
            $user->status = 'ACTIVE';
            $user->save();
        }

        unset($meta['portal_setup_token'], $meta['portal_setup_expires_at']);
        $member->metadata = $meta;
        $member->save();

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('app.portal')->with('success', 'Your password has been set successfully! Welcome to your Member Portal.');
    }

    public function requestMemberSetupLink(Request $request): JsonResponse|RedirectResponse
    {
        $input = trim($request->input('identifier', $request->input('phone', '')));
        if (empty($input)) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Please enter your Mobile Number or Member ID.'], 422);
            }

            return back()->withErrors(['email' => 'Please enter your Mobile Number or Member ID.']);
        }

        $cleanDigits = preg_replace('/[^0-9]/', '', $input);
        $suffix10 = strlen($cleanDigits) >= 10 ? substr($cleanDigits, -10) : $cleanDigits;

        $member = Member::where(function ($q) use ($input, $suffix10) {
            $q->where('member_code', $input)
                ->orWhere('email', $input);
            if (! empty($suffix10)) {
                $q->orWhere('phone', $input)
                    ->orWhere('phone', 'like', "%{$suffix10}");
            }
        })->first();

        if (! $member) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'No active member profile found with this Mobile Number or Member ID. Please contact gym reception.'], 404);
            }

            return back()->withErrors(['email' => 'No active member profile found with this Mobile Number or Member ID. Please contact gym reception.']);
        }

        $token = Str::random(40);
        $meta = is_array($member->metadata) ? $member->metadata : (json_decode($member->metadata ?? '', true) ?? []);
        $meta['portal_setup_token'] = hash('sha256', $token);
        $meta['portal_setup_expires_at'] = now()->addDays(7)->toIso8601String();
        $member->metadata = $meta;
        $member->save();

        $setupUrl = route('portal.setup', ['token' => $token]);
        $gymName = $member->tenant?->name ?? 'GymDeskHub';
        $cleanPhone = preg_replace('/[^0-9]/', '', $member->phone ?? '');
        $waPhone = strlen($cleanPhone) === 10 ? ('91'.$cleanPhone) : $cleanPhone;
        $waMessage = "Hello {$member->first_name},\n\nHere is your secure link to set your password for your {$gymName} Member Portal:\n\n{$setupUrl}\n\nThis link is confidential and valid for 7 days.";
        $waUrl = 'https://wa.me/'.$waPhone.'?text='.urlencode($waMessage);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Password setup link generated for {$member->full_name}.",
                'setup_url' => $setupUrl,
                'whatsapp_url' => $waUrl,
                'member_phone' => $member->phone,
                'member_name' => $member->full_name,
            ]);
        }

        return redirect($waUrl);
    }
}
