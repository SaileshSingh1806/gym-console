<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Setting;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Database\Seeders\PlanSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
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

            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
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

    public function showCheckout(): View
    {
        $user = Auth::user();
        $tenant = $user?->tenant;

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
        $orderId = 'order_'.Str::random(14);

        $razorpayConfig = [
            'key' => $razorpayKeyId,
            'amount' => $amountInPaise,
            'currency' => $tenant?->currency ?? 'INR',
            'name' => Setting::getGlobal('app_name', 'Gym Console'),
            'description' => "Subscription to {$plan->name} ({$billingCycle})",
            'order_id' => $orderId,
            'prefill' => [
                'name' => $user?->name ?? 'Gym Owner',
                'email' => $user?->email ?? '',
                'contact' => $user?->phone ?? ($tenant?->phone ?? ''),
            ],
            'theme' => [
                'color' => '#f59e0b',
            ],
        ];

        return view('auth.checkout', compact('plan', 'billingCycle', 'price', 'razorpayConfig', 'razorpayEnabled', 'stripeEnabled', 'tenant', 'user'));
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

        $transactionId = match ($gateway) {
            'razorpay' => $request->input('razorpay_payment_id') ?: ('pay_'.Str::random(14)),
            'stripe' => $request->input('stripe_payment_id') ?: ('ch_'.Str::random(14)),
            default => 'TXN-'.strtoupper(Str::random(12)),
        };

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

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        TenantContext::reset();

        return redirect()->route('login');
    }
}
