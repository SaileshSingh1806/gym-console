<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Mail\TestDiagnosticMail;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Member;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use App\Services\AiDietPlannerService;
use App\Services\ReportService;
use App\Services\SubscriptionService;
use App\Services\SupportTicketService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(['auth', 'role:super_admin'], except: ['leaveImpersonation']),
        ];
    }

    public function __construct(
        protected ReportService $reportService,
        protected TenantService $tenantService,
        protected SubscriptionService $subscriptionService
    ) {}

    // 1. Dashboard
    public function dashboard(): View
    {
        TenantContext::setBypass(true);

        // Deduplicate: Ensure each active tenant has only 1 latest ACTIVE subscription
        $activeTenants = Tenant::where('status', 'ACTIVE')->get();
        foreach ($activeTenants as $t) {
            $tActiveSubs = Subscription::where('tenant_id', $t->id)->where('status', 'ACTIVE')->latest('id')->get();
            if ($tActiveSubs->count() > 1) {
                $keepId = $tActiveSubs->first()->id;
                Subscription::where('tenant_id', $t->id)
                    ->where('status', 'ACTIVE')
                    ->where('id', '!=', $keepId)
                    ->update(['status' => 'CANCELLED']);
            }
        }

        // Auto-sync active subscriptions with their platform invoices to keep metrics consistent
        $activeSubs = Subscription::with(['plan', 'invoices'])
            ->where('status', 'ACTIVE')
            ->whereIn('tenant_id', Tenant::where('status', 'ACTIVE')->pluck('id'))
            ->get()
            ->unique('tenant_id');

        foreach ($activeSubs as $activeSub) {
            if ($activeSub->plan) {
                $planPrice = (float) ($activeSub->plan->price_yearly ?: ($activeSub->plan->price_monthly * 12));
                $latestInv = $activeSub->invoices()->latest('invoice_date')->first()
                    ?? PlatformInvoice::where('tenant_id', $activeSub->tenant_id)->latest('invoice_date')->first();
                if ($latestInv && $latestInv->total != $planPrice && $planPrice > 0) {
                    $latestInv->update([
                        'subscription_id' => $activeSub->id,
                        'subtotal' => $planPrice,
                        'total' => $planPrice,
                        'status' => 'PAID',
                    ]);
                }
            }
        }

        $metrics = $this->reportService->getSuperAdminMetrics();
        $totalMembers = Member::count();
        $totalUsers = User::count();
        $totalStaffCount = User::whereNotNull('tenant_id')->where('role', '!=', 'super_admin')->count();
        $superAdminCount = User::where('role', 'super_admin')->count();
        $totalTicketsCount = SupportTicket::withoutGlobalScopes()->count();
        $urgentTicketsCount = SupportTicket::withoutGlobalScopes()->whereIn('status', ['open', 'in_progress', 'pending'])->where('priority', 'urgent')->count();
        $totalBranchesCount = Branch::count();
        $activeSubscriptionsCount = $metrics['active_subscriptions_count'] ?? $activeSubs->count();
        $expiringSubscriptionsCount = Subscription::where('status', 'ACTIVE')
            ->whereBetween('ends_at', [now(), now()->addDays(7)])
            ->count();
        $openTicketsCount = SupportTicket::withoutGlobalScopes()->whereIn('status', ['open', 'in_progress', 'pending'])->count();
        $recentGyms = Tenant::with(['activeSubscription.plan', 'users', 'branches'])->latest()->take(6)->get();
        $recentInvoices = PlatformInvoice::with(['tenant.users', 'subscription.plan'])->latest('invoice_date')->take(6)->get();
        $expiringSubscriptions = Subscription::with(['tenant.users', 'plan'])->where('status', 'ACTIVE')->whereBetween('ends_at', [now(), now()->addDays(30)])->orderBy('ends_at')->take(5)->get();
        $openSupportTickets = SupportTicket::withoutGlobalScopes()->whereIn('status', ['open', 'in_progress', 'pending'])->with('tenant')->latest()->take(5)->get();
        $recentPayments = SubscriptionPayment::with(['tenant', 'subscription.plan'])->latest('paid_at')->take(6)->get();
        $plans = Plan::where('is_active', true)->withCount(['subscriptions' => fn ($q) => $q->where('status', 'ACTIVE')])->orderBy('sort_order')->get();

        return view('admin.dashboard', compact(
            'metrics',
            'totalMembers',
            'totalUsers',
            'totalStaffCount',
            'superAdminCount',
            'totalTicketsCount',
            'urgentTicketsCount',
            'totalBranchesCount',
            'activeSubscriptionsCount',
            'expiringSubscriptionsCount',
            'openTicketsCount',
            'recentGyms',
            'recentInvoices',
            'expiringSubscriptions',
            'openSupportTickets',
            'recentPayments',
            'plans'
        ));
    }

    // 2. Gyms / Tenants Management
    public function gyms(Request $request): View
    {
        TenantContext::setBypass(true);

        $query = Tenant::with(['branches', 'activeSubscription.plan', 'users'])->withCount(['members', 'users']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('slug', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->plan_id) {
            $query->whereHas('activeSubscription', function ($q) use ($request) {
                $q->where('plan_id', $request->plan_id);
            });
        }

        $gyms = $query->latest()->paginate(15)->withQueryString();
        $plans = Plan::where('is_active', true)->get();

        $gymStats = [
            'totalGymsCount' => Tenant::count(),
            'totalBranchesCount' => Branch::count(),
            'totalStaffCount' => User::whereNotNull('tenant_id')->where('role', '!=', 'super_admin')->count(),
            'activeGymsCount' => Tenant::where('status', 'ACTIVE')->count(),
            'trialGymsCount' => Tenant::where('status', 'TRIAL')->count(),
            'totalPlatformMembersCount' => Member::count(),
            'totalPlatformRevenue' => (float) Subscription::where('status', 'ACTIVE')->with('plan')->get()->sum(fn ($s) => $s->billing_cycle === 'yearly' ? ($s->plan?->price_yearly ?? 0) : ($s->plan?->price_monthly ?? 0)),
        ];

        return view('admin.gyms', compact('gyms', 'plans', 'gymStats'));
    }

    public function storeGym(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'gym_name' => 'required|string|max:150',
            'owner_name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'password' => 'required|string|min:6',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'nullable|in:monthly,yearly',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:ACTIVE,TRIAL,SUSPENDED',
            'branch_name' => 'nullable|string|max:150',
            'trial_ends_at' => 'nullable|date',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $trialEnds = ! empty($request->trial_ends_at) ? Carbon::parse($request->trial_ends_at) : null;
        $billingCycle = $validated['billing_cycle'] ?? 'yearly';

        $registration = $this->tenantService->registerGym($validated, $plan, $validated['status'], $trialEnds, $billingCycle);
        $tenant = $registration['tenant'];

        return back()->with('success', "Gym '{$tenant->name}' created successfully with owner account!");
    }

    public function updateGym(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $tenant = Tenant::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'slug' => 'required|string|max:150|unique:tenants,slug,'.$tenant->id,
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:TRIAL,ACTIVE,PAST_DUE,GRACE_PERIOD,SUSPENDED,CANCELLED,EXPIRED',
            'trial_ends_at' => 'nullable|date',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'nullable|in:monthly,yearly',
        ]);

        $trialEndsAt = $validated['trial_ends_at'] ? Carbon::parse($validated['trial_ends_at']) : null;
        $trialEndsAt = ! empty($validated['trial_ends_at']) ? Carbon::parse($validated['trial_ends_at']) : null;
        $plan = Plan::findOrFail($validated['plan_id']);
        $billingCycle = $validated['billing_cycle'] ?? ($tenant->activeSubscription?->billing_cycle ?? 'yearly');

        $tenant->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'currency' => $validated['currency'],
            'timezone' => $validated['timezone'],
            'status' => $validated['status'],
            'trial_ends_at' => $validated['status'] === 'TRIAL' ? ($trialEndsAt ?? now()->addDays($plan->trial_days > 0 ? $plan->trial_days : 14)) : null,
        ]);

        if ($validated['status'] === 'TRIAL') {
            $this->subscriptionService->setTenantTrial($tenant, $plan, $tenant->trial_ends_at);
        } elseif ($validated['status'] === 'ACTIVE') {
            $currentSub = $tenant->activeSubscription;
            $price = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
            if (! $currentSub || $currentSub->status !== 'ACTIVE' || $currentSub->plan_id != $plan->id || $currentSub->billing_cycle !== $billingCycle) {
                $this->subscriptionService->activateSubscription(
                    $tenant,
                    $plan,
                    $billingCycle,
                    'manual',
                    'ADMIN-MOD-'.strtoupper(Str::random(8)),
                    $price,
                    ['assigned_by_super_admin' => auth()->id()],
                    sendEmail: false
                );
            }
        } elseif (in_array($validated['status'], ['SUSPENDED', 'CANCELLED', 'EXPIRED'])) {
            Subscription::where('tenant_id', $tenant->id)->update(['status' => $validated['status']]);
        }

        ActivityLog::log('gym_updated_by_admin', "Super admin updated gym {$tenant->name} (Status: {$validated['status']}, Plan: {$plan->name}, Cycle: {$billingCycle})", $tenant);

        return back()->with('success', "Gym '{$tenant->name}' details updated successfully!");
    }

    public function deleteGym(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $tenant = Tenant::findOrFail($id);
        $name = $tenant->name;
        $tenantId = $tenant->id;

        // Verify confirmation input matches gym name or slug
        $confirmation = trim((string) $request->input('confirm_gym_name', ''));
        if (strtolower($confirmation) !== strtolower($name) && strtolower($confirmation) !== strtolower($tenant->slug)) {
            return back()->with('error', "Deletion cancelled. The typed confirmation name ('{$confirmation}') did not match the gym name '{$name}'.");
        }

        $this->tenantService->purgeTenantPermanently($tenantId);

        ActivityLog::log('gym_deleted_by_admin', "Super admin permanently deleted gym {$name} (ID: {$tenantId}) and all associated records across all tables.");

        return back()->with('success', "Gym '{$name}' and all associated tenant data across all tables have been permanently deleted.");
    }

    public function impersonateGym(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $tenant = Tenant::findOrFail($id);
        $owner = $tenant->users()->where('role', 'gym_owner')->first() ?? $tenant->users()->first();

        if (! $owner) {
            return back()->with('error', 'No owner account found for this gym to impersonate.');
        }

        session(['admin_impersonate_user_id' => Auth::id()]);
        Auth::login($owner);

        return redirect()->route('app.dashboard')->with('warning', "You are now logged in as {$owner->name} ({$tenant->name}).");
    }

    public function leaveImpersonation(): RedirectResponse
    {
        $adminId = session('admin_impersonate_user_id');

        if ($adminId) {
            $admin = User::find($adminId);
            if ($admin && $admin->isSuperAdmin()) {
                session()->forget('admin_impersonate_user_id');
                Auth::login($admin);

                return redirect()->route('admin.dashboard')->with('success', 'Returned to Super Admin Console.');
            }
        }

        return redirect()->route('admin.dashboard');
    }

    // 3. Plans & Features Management
    public function plans(): View
    {
        TenantContext::setBypass(true);

        $plans = Plan::with('features')->orderBy('sort_order')->get();
        $features = Feature::all();

        return view('admin.plans', compact('plans', 'features'));
    }

    public function storePlan(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:plans,slug',
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'member_limit' => 'required|integer',
            'branch_limit' => 'required|integer|min:1',
            'staff_limit' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'features' => 'nullable|array',
        ]);

        $monthlyPrice = isset($validated['price_monthly']) && $validated['price_monthly'] !== ''
            ? (float) $validated['price_monthly']
            : round((float) $validated['price_yearly'] / 12, 2);

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'price_monthly' => $monthlyPrice,
            'price_yearly' => $validated['price_yearly'],
            'trial_days' => $validated['trial_days'],
            'member_limit' => $validated['member_limit'],
            'branch_limit' => $validated['branch_limit'],
            'staff_limit' => $validated['staff_limit'],
            'is_active' => $request->boolean('is_active', true),
            'is_popular' => $request->boolean('is_popular', false),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        if (! empty($validated['features'])) {
            $featureSync = [];
            foreach ($validated['features'] as $fId) {
                $featureSync[$fId] = ['value' => '1'];
            }
            $plan->features()->sync($featureSync);
        }

        return back()->with('success', "SaaS Plan '{$plan->name}' created successfully!");
    }

    public function updatePlan(Request $request, int $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'slug' => 'required|string|max:100|unique:plans,slug,'.$plan->id,
            'description' => 'nullable|string|max:500',
            'price_monthly' => 'nullable|numeric|min:0',
            'price_yearly' => 'required|numeric|min:0',
            'trial_days' => 'required|integer|min:0',
            'member_limit' => 'required|integer',
            'branch_limit' => 'required|integer|min:1',
            'staff_limit' => 'required|integer|min:1',
            'is_active' => 'nullable|boolean',
            'is_popular' => 'nullable|boolean',
            'sort_order' => 'nullable|integer',
            'features' => 'nullable|array',
        ]);

        $monthlyPrice = isset($validated['price_monthly']) && $validated['price_monthly'] !== ''
            ? (float) $validated['price_monthly']
            : round((float) $validated['price_yearly'] / 12, 2);

        $plan->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'price_monthly' => $monthlyPrice,
            'price_yearly' => $validated['price_yearly'],
            'trial_days' => $validated['trial_days'],
            'member_limit' => $validated['member_limit'],
            'branch_limit' => $validated['branch_limit'],
            'staff_limit' => $validated['staff_limit'],
            'is_active' => $request->boolean('is_active'),
            'is_popular' => $request->boolean('is_popular'),
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        $featureSync = [];
        if (! empty($validated['features'])) {
            foreach ($validated['features'] as $fId) {
                $featureSync[$fId] = ['value' => '1'];
            }
        }
        $plan->features()->sync($featureSync);

        return back()->with('success', "Plan '{$plan->name}' updated successfully!");
    }

    public function deletePlan(int $id): RedirectResponse
    {
        $plan = Plan::findOrFail($id);

        if ($plan->subscriptions()->where('status', 'ACTIVE')->exists()) {
            return back()->with('error', 'Cannot delete a plan with active subscriptions.');
        }

        $plan->delete();

        return back()->with('success', 'Plan deleted successfully.');
    }

    public function storeFeature(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:100|unique:features,code',
            'description' => 'nullable|string|max:300',
            'type' => 'required|in:boolean,limit',
        ]);

        $validated['code'] = Str::slug($validated['code'], '_');
        Feature::create($validated);

        return back()->with('success', "Feature '{$validated['name']}' created successfully!");
    }

    // 4. Subscriptions & Payments Management
    public function subscriptions(Request $request): View
    {
        TenantContext::setBypass(true);

        // Deduplicate: Ensure each tenant only has 1 ACTIVE subscription (cancel older duplicates if any)
        $tenants = Tenant::all();
        foreach ($tenants as $t) {
            $activeSubs = Subscription::where('tenant_id', $t->id)->where('status', 'ACTIVE')->latest('id')->get();
            if ($activeSubs->count() > 1) {
                $keepId = $activeSubs->first()->id;
                Subscription::where('tenant_id', $t->id)
                    ->where('status', 'ACTIVE')
                    ->where('id', '!=', $keepId)
                    ->update(['status' => 'CANCELLED']);
            }
        }

        $query = Subscription::with(['tenant.users', 'plan.features', 'payments', 'invoices']);

        if ($request->status && $request->status !== 'all') {
            $query->where('status', $request->status);
        } elseif (! $request->has('status')) {
            $query->whereIn('status', ['ACTIVE', 'TRIAL', 'PAST_DUE', 'GRACE_PERIOD']);
        }

        $subscriptions = $query->latest()->paginate(15);
        $invoices = PlatformInvoice::with(['tenant.users', 'subscription.plan', 'payment'])->latest('invoice_date')->paginate(15);
        $gyms = Tenant::all();
        $plans = Plan::with('features')->get();

        return view('admin.subscriptions', compact('subscriptions', 'invoices', 'gyms', 'plans'));
    }

    public function updateSubscription(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $sub = Subscription::with(['tenant', 'plan', 'invoices', 'payments'])->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:TRIAL,ACTIVE,PAST_DUE,GRACE_PERIOD,SUSPENDED,CANCELLED,EXPIRED',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'ends_at' => 'nullable|date',
            'trial_ends_at' => 'nullable|date',
        ]);

        $newPlan = Plan::findOrFail($validated['plan_id']);
        $newPrice = (float) ($validated['billing_cycle'] === 'yearly' ? $newPlan->price_yearly : $newPlan->price_monthly);

        $endsAt = ! empty($validated['ends_at']) ? Carbon::parse($validated['ends_at']) : $sub->ends_at;
        $trialEndsAt = ! empty($validated['trial_ends_at']) ? Carbon::parse($validated['trial_ends_at']) : $sub->trial_ends_at;

        $sub->update([
            'status' => $validated['status'],
            'plan_id' => $validated['plan_id'],
            'billing_cycle' => $validated['billing_cycle'],
            'ends_at' => $endsAt,
            'trial_ends_at' => $trialEndsAt,
        ]);

        // Sync invoices & payments to match the updated subscription plan & amount
        if ($validated['status'] === 'ACTIVE' && $newPrice > 0) {
            $latestInvoice = $sub->invoices()->latest('invoice_date')->first()
                ?? PlatformInvoice::where('tenant_id', $sub->tenant_id)->latest('invoice_date')->first();

            if ($latestInvoice) {
                $latestInvoice->update([
                    'subscription_id' => $sub->id,
                    'subtotal' => $newPrice,
                    'total' => $newPrice,
                    'status' => 'PAID',
                ]);
            } else {
                PlatformInvoice::create([
                    'tenant_id' => $sub->tenant_id,
                    'subscription_id' => $sub->id,
                    'invoice_number' => 'INV-SAAS-'.strtoupper(Str::random(6)).'-'.date('Y'),
                    'subtotal' => $newPrice,
                    'discount' => 0.00,
                    'tax' => 0.00,
                    'total' => $newPrice,
                    'currency' => $sub->tenant?->currency ?? 'INR',
                    'status' => 'PAID',
                    'invoice_date' => now()->toDateString(),
                    'paid_at' => now(),
                ]);
            }

            $latestPayment = $sub->payments()->latest('paid_at')->first();
            if ($latestPayment) {
                $latestPayment->update([
                    'amount' => $newPrice,
                    'status' => 'SUCCESS',
                ]);
            }
        }

        // Sync tenant status
        $sub->tenant->update(['status' => in_array($validated['status'], ['ACTIVE', 'TRIAL']) ? $validated['status'] : $validated['status']]);

        return back()->with('success', "Subscription for {$sub->tenant->name} updated successfully to {$newPlan->name} ({$validated['billing_cycle']})!");
    }

    public function recordManualPayment(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'amount' => 'required|numeric|min:1',
            'billing_cycle' => 'required|in:monthly,yearly',
            'transaction_id' => 'nullable|string|max:100',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        $tenant = Tenant::findOrFail($validated['tenant_id']);
        $plan = Plan::findOrFail($validated['plan_id']);
        $txId = $validated['transaction_id'] ?? ('MAN-'.strtoupper(Str::random(10)));

        $this->subscriptionService->activateSubscription(
            $tenant,
            $plan,
            $validated['billing_cycle'],
            $validated['payment_method'],
            $txId,
            (float) $validated['amount'],
            ['notes' => $validated['notes'] ?? 'Manual payment recorded by Super Admin']
        );

        return back()->with('success', "Manual payment of \${$validated['amount']} recorded and subscription activated for {$tenant->name}!");
    }

    // 5. Users Management across all Gyms
    public function users(Request $request): View
    {
        TenantContext::setBypass(true);

        $query = User::with('tenant');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        // Default to 'gym_owner' on initial load; if user explicitly submits empty string ('') or 'all', show all roles
        $selectedRole = $request->has('role') ? $request->role : 'gym_owner';
        if (! empty($selectedRole) && $selectedRole !== 'all') {
            $query->where('role', $selectedRole);
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        $gyms = Tenant::all();

        return view('admin.users', compact('users', 'gyms', 'selectedRole'));
    }

    public function storeUser(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            'tenant_id' => 'nullable|exists:tenants,id',
            'password' => 'required|string|min:6',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', "User '{$validated['name']}' created successfully!");
    }

    public function updateUser(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'phone' => 'nullable|string|max:30',
            'role' => 'required|string',
            'status' => 'required|in:ACTIVE,INACTIVE,SUSPENDED',
            'tenant_id' => 'nullable|exists:tenants,id',
            'password' => 'nullable|string|min:6',
        ]);

        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', "User '{$user->name}' updated successfully!");
    }

    public function deleteUser(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own Super Admin account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    // 6. System Activity Logs
    public function logs(Request $request): View
    {
        TenantContext::setBypass(true);

        $query = ActivityLog::with(['tenant', 'user']);

        if ($request->action) {
            $query->where('action', 'like', "%{$request->action}%");
        }

        if ($request->tenant_id) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $logs = $query->latest()->paginate(30)->withQueryString();
        $gyms = Tenant::all();

        return view('admin.logs', compact('logs', 'gyms'));
    }

    public function clearLogs(): RedirectResponse
    {
        TenantContext::setBypass(true);
        ActivityLog::truncate();

        return back()->with('success', 'System activity logs cleared successfully.');
    }

    // 7. SaaS Promo Coupons & Discount Management
    public function coupons(Request $request): View
    {
        TenantContext::setBypass(true);

        $query = Coupon::with('plan')->withCount(['payments', 'invoices']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('code', 'like', "%{$s}%")
                    ->orWhere('name', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }

        $coupons = $query->latest()->paginate(15)->withQueryString();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        $stats = [
            'total_coupons' => Coupon::count(),
            'active_coupons' => Coupon::where('is_active', true)->count(),
            'total_used' => Coupon::sum('used_count'),
            'total_discount_given' => (float) PlatformInvoice::sum('discount'),
        ];

        return view('admin.coupons', compact('coupons', 'plans', 'stats'));
    }

    public function storeCoupon(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:coupons,code',
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'plan_id' => 'nullable|exists:plans,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active', true);

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->with('error', 'Percentage discount value cannot exceed 100%.')->withInput();
        }

        $coupon = Coupon::create($validated);

        ActivityLog::log('coupon_created', "Created promo coupon code '{$coupon->code}' ({$coupon->discount_value}".($coupon->discount_type === 'percentage' ? '%' : ' ₹').')', $coupon);

        return back()->with('success', "Coupon code '{$coupon->code}' created successfully!");
    }

    public function updateCoupon(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $coupon = Coupon::findOrFail($id);

        $validated = $request->validate([
            'code' => 'required|string|max:30|unique:coupons,code,'.$coupon->id,
            'name' => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|numeric|min:0.01',
            'plan_id' => 'nullable|exists:plans,id',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount_amount' => 'nullable|numeric|min:0',
            'usage_limit' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper(trim($validated['code']));
        $validated['is_active'] = $request->boolean('is_active');

        if ($validated['discount_type'] === 'percentage' && $validated['discount_value'] > 100) {
            return back()->with('error', 'Percentage discount value cannot exceed 100%.')->withInput();
        }

        $coupon->update($validated);

        ActivityLog::log('coupon_updated', "Updated promo coupon code '{$coupon->code}'", $coupon);

        return back()->with('success', "Coupon code '{$coupon->code}' updated successfully!");
    }

    public function deleteCoupon(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $coupon = Coupon::findOrFail($id);
        $code = $coupon->code;

        $coupon->delete();

        ActivityLog::log('coupon_deleted', "Deleted promo coupon '{$code}'");

        return back()->with('success', "Coupon '{$code}' deleted successfully.");
    }

    public function toggleCouponStatus(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $coupon = Coupon::findOrFail($id);
        $coupon->is_active = ! $coupon->is_active;
        $coupon->save();

        $status = $coupon->is_active ? 'Activated' : 'Disabled';
        ActivityLog::log('coupon_status_toggled', "Coupon '{$coupon->code}' {$status}", $coupon);

        return back()->with('success', "Coupon '{$coupon->code}' {$status} successfully.");
    }

    // 8. Global Platform Settings (General, Logo/Favicon, Email/SMTP, Razorpay, Site SEO)
    public function settings(Request $request): View
    {
        TenantContext::setBypass(true);

        $settings = Setting::getAllGlobal();

        // Fill standard defaults if not set yet
        $defaults = [
            'app_name' => 'Gym Console',
            'app_tagline' => 'Next-Gen Multi-Tenant Gym Management SaaS Platform',
            'logo_url' => null,
            'favicon_url' => null,
            'support_email' => 'support@gymconsole.com',
            'support_phone' => '+91 98765 43210',
            'default_currency' => 'INR',
            'default_timezone' => 'Asia/Kolkata',
            'footer_copyright' => '© '.date('Y').' Gym Console SaaS. All rights reserved.',
            // Email / SMTP
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.gmail.com',
            'mail_port' => '587',
            'mail_username' => '',
            'mail_password' => '',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@gymconsole.com',
            'mail_from_name' => 'Gym Console Platform',
            // Payments
            'razorpay_enabled' => true,
            'razorpay_mode' => 'sandbox',
            'razorpay_key_id' => 'rzp_test_samplekey123',
            'razorpay_key_secret' => 'sample_secret_key',
            'razorpay_webhook_secret' => '',
            'stripe_enabled' => true,
            'stripe_key' => '',
            'stripe_secret' => '',
            'stripe_webhook_secret' => '',
            // SEO
            'meta_title' => 'Gym Console - Multi-Tenant Gym Management SaaS',
            'meta_description' => 'Complete gym membership management, biometric access control with Hikvision, POS billing, and multi-tenant SaaS dashboard.',
            'meta_keywords' => 'gym software, gym management saas, gym attendance system, biometric access control, gym billing software in rupees',
            'og_image_url' => null,
            'google_analytics_id' => '',
            'facebook_pixel_id' => '',
            'custom_header_scripts' => '',
            'custom_footer_scripts' => '',
            // AI & Google Gemini
            'gemini_api_key' => '',
            'gemini_model' => 'gemini-1.5-flash',
            'gemini_enabled' => true,
        ];

        $settings = array_merge($defaults, $settings);

        return view('admin.settings', compact('settings'));
    }

    public function updateGeneralSettings(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'app_tagline' => 'nullable|string|max:200',
            'logo' => 'nullable|image|max:5120',
            'logo_url' => 'nullable|url|max:500',
            'favicon' => 'nullable|mimes:ico,png,svg,jpg,jpeg|max:2048',
            'favicon_url' => 'nullable|url|max:500',
            'support_email' => 'required|email|max:150',
            'support_phone' => 'nullable|string|max:50',
            'default_currency' => 'required|string|max:10',
            'default_timezone' => 'required|string|max:50',
            'footer_copyright' => 'nullable|string|max:250',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('uploads/branding', 'public');
            $validated['logo_url'] = asset('storage/'.$path);
        }

        if ($request->hasFile('favicon')) {
            $path = $request->file('favicon')->store('uploads/branding', 'public');
            $validated['favicon_url'] = asset('storage/'.$path);
        }

        unset($validated['logo'], $validated['favicon']);

        Setting::setManyGlobal($validated);

        ActivityLog::log('general_settings_updated', 'Updated general platform branding & contact settings');

        return back()->with('success', 'General branding & platform settings updated successfully!');
    }

    public function updateEmailSettings(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'mail_mailer' => 'required|string|in:smtp,sendmail,log',
            'mail_host' => 'required|string|max:150',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:150',
            'mail_password' => 'nullable|string|max:150',
            'mail_encryption' => 'required|string|in:tls,ssl,none',
            'mail_from_address' => 'required|email|max:150',
            'mail_from_name' => 'required|string|max:100',
        ]);

        Setting::setManyGlobal($validated);

        ActivityLog::log('email_settings_updated', "Updated platform SMTP email configuration ({$validated['mail_host']}:{$validated['mail_port']})");

        return back()->with('success', 'Email / SMTP configuration saved successfully!');
    }

    public function sendTestEmail(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $request->validate([
            'test_email' => 'required|email',
        ]);

        try {
            Mail::to($request->test_email)->send(new TestDiagnosticMail($request->test_email));
            ActivityLog::log('test_email_sent', "Sent test verification email to {$request->test_email}");

            return back()->with('success', "Test email successfully dispatched to {$request->test_email}! Check your inbox (or storage/logs/laravel.log if using log driver).");
        } catch (\Throwable $e) {
            Log::error("Failed to send test email to {$request->test_email}: ".$e->getMessage());

            return back()->with('error', 'Could not send test email: '.$e->getMessage());
        }
    }

    public function updatePaymentSettings(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'razorpay_enabled' => 'nullable|boolean',
            'razorpay_mode' => 'required|in:sandbox,live',
            'razorpay_key_id' => 'nullable|string|max:150',
            'razorpay_key_secret' => 'nullable|string|max:150',
            'razorpay_webhook_secret' => 'nullable|string|max:150',
            'stripe_enabled' => 'nullable|boolean',
            'stripe_key' => 'nullable|string|max:150',
            'stripe_secret' => 'nullable|string|max:150',
            'stripe_webhook_secret' => 'nullable|string|max:150',
        ]);

        $validated['razorpay_enabled'] = $request->boolean('razorpay_enabled');
        $validated['stripe_enabled'] = $request->boolean('stripe_enabled');

        Setting::setManyGlobal($validated);

        ActivityLog::log('payment_gateways_updated', 'Updated Razorpay and Stripe payment gateway credentials');

        return back()->with('success', 'Payment gateway settings (Razorpay & Stripe) updated successfully!');
    }

    public function updateSeoSettings(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'meta_title' => 'required|string|max:150',
            'meta_description' => 'required|string|max:500',
            'meta_keywords' => 'nullable|string|max:500',
            'og_image' => 'nullable|image|max:2048',
            'og_image_url' => 'nullable|url|max:500',
            'google_analytics_id' => 'nullable|string|max:50',
            'facebook_pixel_id' => 'nullable|string|max:50',
            'custom_header_scripts' => 'nullable|string',
            'custom_footer_scripts' => 'nullable|string',
        ]);

        if ($request->hasFile('og_image')) {
            $path = $request->file('og_image')->store('uploads/seo', 'public');
            $validated['og_image_url'] = asset('storage/'.$path);
        }

        unset($validated['og_image']);

        Setting::setManyGlobal($validated);

        ActivityLog::log('seo_settings_updated', 'Updated site SEO meta tags, OpenGraph & Analytics scripts');

        return back()->with('success', 'Site SEO & Analytics configuration updated successfully!');
    }

    public function updateAiSettings(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);

        $validated = $request->validate([
            'gemini_api_key' => 'nullable|string|max:200',
            'gemini_model' => 'required|string|max:100',
            'gemini_enabled' => 'nullable',
        ]);

        $validated['gemini_enabled'] = $request->boolean('gemini_enabled');

        Setting::setManyGlobal($validated);

        ActivityLog::log('ai_settings_updated', "Updated Google Gemini AI platform settings (Model: {$validated['gemini_model']})");

        return back()->with('success', 'Google Gemini AI settings saved successfully!')->with('activeTab', 'ai');
    }

    public function testAdminGeminiConnection(Request $request, AiDietPlannerService $aiDietService): JsonResponse
    {
        TenantContext::setBypass(true);

        $apiKey = trim((string) ($request->input('gemini_api_key') ?: (Setting::getGlobal('gemini_api_key') ?: config('services.gemini.api_key'))));
        $model = trim((string) ($request->input('gemini_model') ?: (Setting::getGlobal('gemini_model') ?: config('services.gemini.model', 'gemini-2.5-flash'))));

        $result = $aiDietService->testConnection($apiKey, $model);

        return response()->json($result);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        TenantContext::setBypass(true);
        /** @var User $user */
        $user = Auth::user();

        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ], [
            'current_password.current_password' => 'The provided current password does not match your account password.',
            'password.different' => 'The new password must be different from your current password.',
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        ActivityLog::log('super_admin_password_updated', "Super Admin {$user->name} ({$user->email}) updated their master login password");

        return back()->with('success', 'Super Admin master password changed successfully!')->with('activeTab', 'security');
    }

    /**
     * Display all support tickets across all gym tenants for Super Admin.
     */
    public function supportTickets(Request $request): View
    {
        TenantContext::setBypass(true);

        $query = SupportTicket::withoutGlobalScopes()
            ->with(['tenant', 'user', 'lastReplyBy', 'latestReply']);

        if ($status = $request->get('status')) {
            if ($status !== 'all') {
                $query->where('status', $status);
            }
        }

        if ($priority = $request->get('priority')) {
            if ($priority !== 'all') {
                $query->where('priority', $priority);
            }
        }

        if ($tenantId = $request->get('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('ticket_number', 'like', "%{$search}%")
                    ->orWhere('subject', 'like', "%{$search}%")
                    ->orWhereHas('tenant', function ($tq) use ($search) {
                        $tq->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($uq) use ($search) {
                        $uq->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        $tickets = $query->latest('last_reply_at')->latest('id')->paginate(20);

        $counts = [
            'total' => SupportTicket::withoutGlobalScopes()->count(),
            'open' => SupportTicket::withoutGlobalScopes()->where('status', 'open')->count(),
            'in_progress' => SupportTicket::withoutGlobalScopes()->where('status', 'in_progress')->count(),
            'answered' => SupportTicket::withoutGlobalScopes()->where('status', 'answered')->count(),
            'resolved' => SupportTicket::withoutGlobalScopes()->whereIn('status', ['resolved', 'closed'])->count(),
            'urgent' => SupportTicket::withoutGlobalScopes()->where('priority', 'urgent')->whereNotIn('status', ['resolved', 'closed'])->count(),
        ];

        $gyms = Tenant::orderBy('name')->get();

        return view('admin.tickets.index', compact('tickets', 'counts', 'gyms'));
    }

    /**
     * Show ticket details and thread for Super Admin.
     */
    public function showSupportTicket(int $id): View
    {
        TenantContext::setBypass(true);

        $ticket = SupportTicket::withoutGlobalScopes()
            ->with(['tenant.activeSubscription.plan', 'user', 'replies.user'])
            ->findOrFail($id);

        return view('admin.tickets.show', compact('ticket'));
    }

    /**
     * Super Admin reply to a support ticket.
     */
    public function replySupportTicket(Request $request, int $id, SupportTicketService $ticketService): RedirectResponse
    {
        TenantContext::setBypass(true);

        $ticket = SupportTicket::withoutGlobalScopes()->findOrFail($id);
        $user = auth()->user();

        $validated = $request->validate([
            'message' => 'required|string|max:5000',
            'status' => 'nullable|in:open,in_progress,answered,resolved,closed',
            'attachment' => 'nullable|file|mimes:jpeg,png,jpg,webp,pdf,doc,docx,zip|max:5120',
        ]);

        $ticketService->replyTicket(
            $ticket,
            $user,
            $validated['message'],
            $request->file('attachment'),
            true,
            $validated['status'] ?? 'answered'
        );

        return back()->with('success', 'Reply posted and email update sent to the gym owner!');
    }

    /**
     * Update support ticket status/priority.
     */
    public function updateTicketStatus(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $ticket = SupportTicket::withoutGlobalScopes()->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,answered,resolved,closed',
            'priority' => 'nullable|in:low,medium,high,urgent',
        ]);

        $updateData = ['status' => $validated['status']];

        if (! empty($validated['priority'])) {
            $updateData['priority'] = $validated['priority'];
        }

        if (in_array($validated['status'], ['resolved', 'closed'])) {
            $updateData['resolved_at'] = now();
        } else {
            $updateData['resolved_at'] = null;
        }

        $ticket->update($updateData);

        ActivityLog::log('support_ticket_status_updated', "Updated ticket #{$ticket->ticket_number} status to {$validated['status']}", $ticket);

        return back()->with('success', "Ticket status updated to '{$validated['status']}' successfully!");
    }

    /**
     * Delete a support ticket (Super Admin only).
     */
    public function deleteSupportTicket(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $ticket = SupportTicket::withoutGlobalScopes()->findOrFail($id);
        $number = $ticket->ticket_number;

        $ticket->replies()->delete();
        $ticket->delete();

        ActivityLog::log('support_ticket_deleted', "Deleted support ticket #{$number}");

        return redirect()->route('admin.tickets.index')->with('success', "Support ticket #{$number} deleted successfully.");
    }
}
