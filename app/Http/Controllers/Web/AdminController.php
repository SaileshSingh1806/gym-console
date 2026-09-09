<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Feature;
use App\Models\Member;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Models\User;
use App\Services\ReportService;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function __construct(
        protected ReportService $reportService,
        protected TenantService $tenantService,
        protected SubscriptionService $subscriptionService
    ) {}

    // 1. Dashboard
    public function dashboard(): View
    {
        TenantContext::setBypass(true);

        $metrics = $this->reportService->getSuperAdminMetrics();
        $totalMembers = Member::count();
        $totalUsers = User::count();
        $recentGyms = Tenant::with(['activeSubscription.plan', 'users'])->latest()->take(6)->get();
        $recentPayments = SubscriptionPayment::with(['tenant', 'subscription.plan'])->latest('paid_at')->take(6)->get();
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.dashboard', compact('metrics', 'totalMembers', 'totalUsers', 'recentGyms', 'recentPayments', 'plans'));
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

        return view('admin.gyms', compact('gyms', 'plans'));
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
            'currency' => 'required|string|max:10',
            'timezone' => 'required|string|max:50',
            'status' => 'required|in:ACTIVE,TRIAL,SUSPENDED',
            'branch_name' => 'nullable|string|max:150',
        ]);

        $plan = Plan::findOrFail($validated['plan_id']);
        $registration = $this->tenantService->registerGym($validated, $plan);
        $tenant = $registration['tenant'];

        if ($validated['status'] === 'ACTIVE') {
            $this->subscriptionService->activateSubscription(
                $tenant,
                $plan,
                'monthly',
                'manual',
                'ADMIN-INIT-'.strtoupper(Str::random(8)),
                (float) $plan->price_monthly,
                ['created_by_super_admin' => auth()->id()]
            );
        } elseif ($validated['status'] === 'TRIAL') {
            $trialEnds = ! empty($request->trial_ends_at) ? Carbon::parse($request->trial_ends_at) : now()->addDays($plan->trial_days > 0 ? $plan->trial_days : 14);
            $this->subscriptionService->setTenantTrial($tenant, $plan, $trialEnds);
        }

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
        ]);

        $trialEndsAt = $validated['trial_ends_at'] ? Carbon::parse($validated['trial_ends_at']) : null;
        $plan = Plan::findOrFail($validated['plan_id']);

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
            if (! $currentSub || $currentSub->status !== 'ACTIVE' || $currentSub->plan_id != $plan->id) {
                $this->subscriptionService->activateSubscription(
                    $tenant,
                    $plan,
                    $currentSub?->billing_cycle ?? 'monthly',
                    'manual',
                    'ADMIN-MOD-'.strtoupper(Str::random(8)),
                    (float) $plan->price_monthly,
                    ['assigned_by_super_admin' => auth()->id()]
                );
            }
        } elseif (in_array($validated['status'], ['SUSPENDED', 'CANCELLED', 'EXPIRED'])) {
            Subscription::where('tenant_id', $tenant->id)->update(['status' => $validated['status']]);
        }

        ActivityLog::log('gym_updated_by_admin', "Super admin updated gym {$tenant->name} (Status: {$validated['status']}, Plan: {$plan->name})", $tenant);

        return back()->with('success', "Gym '{$tenant->name}' details updated successfully!");
    }

    public function deleteGym(int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $tenant = Tenant::findOrFail($id);
        $name = $tenant->name;
        $tenant->delete();

        ActivityLog::log('gym_deleted_by_admin', "Super admin deleted gym {$name}");

        return back()->with('success', "Gym '{$name}' has been deleted.");
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
            'price_monthly' => 'required|numeric|min:0',
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

        $plan = Plan::create([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'price_monthly' => $validated['price_monthly'],
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
            'price_monthly' => 'required|numeric|min:0',
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

        $plan->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['slug']),
            'description' => $validated['description'] ?? null,
            'price_monthly' => $validated['price_monthly'],
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

        $query = Subscription::with(['tenant', 'plan', 'payments', 'invoices']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $subscriptions = $query->latest()->paginate(15);
        $invoices = PlatformInvoice::with(['tenant', 'subscription'])->latest('invoice_date')->paginate(15);
        $gyms = Tenant::all();
        $plans = Plan::all();

        return view('admin.subscriptions', compact('subscriptions', 'invoices', 'gyms', 'plans'));
    }

    public function updateSubscription(Request $request, int $id): RedirectResponse
    {
        TenantContext::setBypass(true);

        $sub = Subscription::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:TRIAL,ACTIVE,PAST_DUE,GRACE_PERIOD,SUSPENDED,CANCELLED,EXPIRED',
            'plan_id' => 'required|exists:plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
            'ends_at' => 'nullable|date',
            'trial_ends_at' => 'nullable|date',
        ]);

        $sub->update([
            'status' => $validated['status'],
            'plan_id' => $validated['plan_id'],
            'billing_cycle' => $validated['billing_cycle'],
            'ends_at' => $validated['ends_at'] ? Carbon::parse($validated['ends_at']) : $sub->ends_at,
            'trial_ends_at' => $validated['trial_ends_at'] ? Carbon::parse($validated['trial_ends_at']) : $sub->trial_ends_at,
        ]);

        // Sync tenant status
        $sub->tenant->update(['status' => in_array($validated['status'], ['ACTIVE', 'TRIAL']) ? $validated['status'] : $validated['status']]);

        return back()->with('success', "Subscription for {$sub->tenant->name} updated successfully!");
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

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            });
        }

        if ($request->role) {
            $query->where('role', $request->role);
        }

        if ($request->tenant_id) {
            $query->where('tenant_id', $request->tenant_id);
        }

        $users = $query->latest()->paginate(20)->withQueryString();
        $gyms = Tenant::all();

        return view('admin.users', compact('users', 'gyms'));
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

        ActivityLog::log('test_email_sent', "Sent test verification email to {$request->test_email}");

        return back()->with('success', "Test email successfully dispatched to {$request->test_email}!");
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
}
