<?php

namespace App\Services;

use App\Mail\GymOwnerWelcomeMail;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantService
{
    public function __construct(
        protected SubscriptionService $subscriptionService,
        protected FeatureGateService $featureGateService
    ) {}

    public function registerGym(array $data, Plan $plan, string $status = 'TRIAL', ?Carbon $trialEndsAt = null, string $billingCycle = 'yearly'): array
    {
        return DB::transaction(function () use ($data, $plan, $status, $trialEndsAt, $billingCycle) {
            $slug = Str::slug($data['gym_name']);
            if (Tenant::where('slug', $slug)->exists()) {
                $slug .= '-'.Str::lower(Str::random(4));
            }

            $resolvedStatus = in_array($status, ['ACTIVE', 'TRIAL', 'SUSPENDED']) ? $status : 'TRIAL';
            $resolvedCycle = in_array(strtolower($billingCycle), ['monthly', 'yearly']) ? strtolower($billingCycle) : (isset($data['billing_cycle']) && in_array(strtolower($data['billing_cycle']), ['monthly', 'yearly']) ? strtolower($data['billing_cycle']) : 'yearly');

            $tenant = Tenant::create([
                'name' => $data['gym_name'],
                'slug' => $slug,
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'currency' => $data['currency'] ?? 'INR',
                'timezone' => $data['timezone'] ?? 'Asia/Kolkata',
                'status' => $resolvedStatus,
                'trial_ends_at' => $resolvedStatus === 'TRIAL' ? ($trialEndsAt ?? now()->addDays($plan->trial_days > 0 ? $plan->trial_days : 14)) : null,
            ]);

            // Set context for child records
            TenantContext::setTenant($tenant);

            // Create Main Branch
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name' => $data['branch_name'] ?? ($data['gym_name'].' Main Branch'),
                'code' => 'MAIN',
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'],
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'is_main' => true,
                'status' => 'ACTIVE',
            ]);

            TenantContext::setBranch($branch);

            // Create Gym Owner User
            $owner = User::create([
                'tenant_id' => $tenant->id,
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'role' => 'gym_owner',
                'status' => 'ACTIVE',
                'password' => Hash::make($data['password']),
            ]);

            // Attach to branch
            $owner->branches()->attach($branch->id);

            // Seed default roles for this tenant
            $this->createDefaultTenantRoles($tenant);

            // Start trial or activate subscription based on chosen status
            if ($resolvedStatus === 'ACTIVE') {
                $planPrice = $resolvedCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
                $subscription = $this->subscriptionService->activateSubscription(
                    $tenant,
                    $plan,
                    $resolvedCycle,
                    'manual',
                    'ADMIN-INIT-'.strtoupper(Str::random(8)),
                    $planPrice,
                    ['created_by_super_admin' => auth()->id() ?? null],
                    sendEmail: false
                );
            } elseif ($resolvedStatus === 'TRIAL') {
                $subscription = $this->subscriptionService->setTenantTrial($tenant, $plan, $trialEndsAt);
            } else {
                $subscription = $this->subscriptionService->setTenantTrial($tenant, $plan, $trialEndsAt);
                $subscription->update(['status' => $resolvedStatus]);
            }

            ActivityLog::log('tenant_registered', "New Gym '{$tenant->name}' registered with owner '{$owner->name}' (Status: {$resolvedStatus}, Plan: {$plan->name}, Cycle: {$resolvedCycle})", $tenant);

            // Send SINGLE welcome email in background (instant non-blocking queue execution)
            AsyncMailService::dispatch($tenant, $owner->email, new GymOwnerWelcomeMail($tenant, $owner, $plan, $data['password'] ?? null));

            return [
                'tenant' => $tenant,
                'branch' => $branch,
                'owner' => $owner,
                'subscription' => $subscription,
            ];
        });
    }

    public function createBranch(Tenant $tenant, array $data): Branch
    {
        $this->featureGateService->ensureWithinQuota($tenant, 'branches');

        return DB::transaction(function () use ($tenant, $data) {
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name' => $data['name'],
                'code' => $data['code'] ?? strtoupper(Str::random(4)),
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'state' => $data['state'] ?? null,
                'postal_code' => $data['postal_code'] ?? null,
                'is_main' => false,
                'status' => 'ACTIVE',
            ]);

            ActivityLog::log('branch_created', "Created branch '{$branch->name}'", $branch);

            return $branch;
        });
    }

    public function createDefaultTenantRoles(Tenant $tenant): void
    {
        $roles = [
            ['name' => 'gym_manager', 'display_name' => 'Gym Manager', 'description' => 'Branch manager with operational access'],
            ['name' => 'receptionist', 'display_name' => 'Receptionist', 'description' => 'Front desk reception and attendance'],
            ['name' => 'trainer', 'display_name' => 'Fitness Trainer', 'description' => 'Personal trainer and class coach'],
            ['name' => 'accountant', 'display_name' => 'Accountant', 'description' => 'Financial records and expense manager'],
            ['name' => 'staff', 'display_name' => 'General Staff', 'description' => 'Basic staff access'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(
                ['tenant_id' => $tenant->id, 'name' => $r['name']],
                ['display_name' => $r['display_name'], 'description' => $r['description'], 'is_system' => true]
            );
        }
    }
}
