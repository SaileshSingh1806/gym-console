<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
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
            $request->session()->regenerate();

            $user = Auth::user();

            if ($user->isSuperAdmin()) {
                return redirect()->intended(route('admin.dashboard'));
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

        $registration = $this->tenantService->registerGym($validated, $plan);
        $owner = $registration['owner'];
        $tenant = $registration['tenant'];

        Auth::login($owner);

        // Store pending checkout details in session
        session([
            'checkout_plan_id' => $plan->id,
            'checkout_billing_cycle' => $validated['billing_cycle'],
        ]);

        return redirect()->route('auth.checkout');
    }

    public function showCheckout(): View
    {
        $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id'));
        $billingCycle = session('checkout_billing_cycle', 'monthly');
        $plan = Plan::findOrFail($planId);

        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        return view('auth.checkout', compact('plan', 'billingCycle', 'price'));
    }

    public function processCheckout(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $tenant = $user->tenant;

        $planId = session('checkout_plan_id', Plan::where('slug', 'starter')->value('id'));
        $billingCycle = session('checkout_billing_cycle', 'monthly');
        $plan = Plan::findOrFail($planId);

        $gateway = $request->input('gateway', 'stripe');
        $price = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;

        $transactionId = 'TXN-'.strtoupper(Str::random(12));

        $this->subscriptionService->activateSubscription(
            $tenant,
            $plan,
            $billingCycle,
            $gateway,
            $transactionId,
            (float) $price,
            ['checkout_source' => 'web_registration', 'verified_at' => now()->toIso8601String()]
        );

        session()->forget(['checkout_plan_id', 'checkout_billing_cycle']);

        return redirect()->route('app.dashboard')->with('success', 'Congratulations! Your subscription has been activated successfully.');
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
