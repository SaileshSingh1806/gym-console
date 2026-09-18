<?php

namespace App\Services;

use App\Mail\GymOwnerWelcomeMail;
use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
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

            $resolvedStatus = in_array($status, ['ACTIVE', 'TRIAL', 'SUSPENDED', 'PENDING_PAYMENT']) ? $status : 'PENDING_PAYMENT';
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
            } elseif ($resolvedStatus === 'PENDING_PAYMENT') {
                $subscription = Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $plan->id,
                    'billing_cycle' => $resolvedCycle,
                    'status' => 'PENDING',
                    'starts_at' => now(),
                    'ends_at' => null,
                    'gateway_name' => 'razorpay',
                ]);
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

    public function purgeTenantPermanently(int $tenantId): bool
    {
        return DB::transaction(function () use ($tenantId) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $userIds = DB::table('users')->where('tenant_id', $tenantId)->where('role', '!=', 'super_admin')->pluck('id')->toArray();
            $userEmails = DB::table('users')->where('tenant_id', $tenantId)->where('role', '!=', 'super_admin')->pluck('email')->toArray();
            $roleIds = DB::table('roles')->where('tenant_id', $tenantId)->pluck('id')->toArray();
            $branchIds = DB::table('branches')->where('tenant_id', $tenantId)->pluck('id')->toArray();
            $workoutPlanIds = DB::table('workout_plans')->where('tenant_id', $tenantId)->pluck('id')->toArray();
            $dietPlanIds = DB::table('diet_plans')->where('tenant_id', $tenantId)->pluck('id')->toArray();
            $supportTicketIds = Schema::hasTable('support_tickets')
                ? DB::table('support_tickets')->where('tenant_id', $tenantId)->pluck('id')->toArray()
                : [];
            $equipmentIds = Schema::hasTable('gym_equipment')
                ? DB::table('gym_equipment')->where('tenant_id', $tenantId)->pluck('id')->toArray()
                : [];
            $inventoryItemIds = Schema::hasTable('inventory_items')
                ? DB::table('inventory_items')->where('tenant_id', $tenantId)->pluck('id')->toArray()
                : [];

            // 1. Pivot and child relationships
            if (! empty($roleIds)) {
                if (Schema::hasTable('permission_role')) {
                    DB::table('permission_role')->whereIn('role_id', $roleIds)->delete();
                }
                if (Schema::hasTable('role_user')) {
                    DB::table('role_user')->whereIn('role_id', $roleIds)->delete();
                }
            }

            if (! empty($userIds)) {
                if (Schema::hasTable('branch_user')) {
                    DB::table('branch_user')->whereIn('user_id', $userIds)->delete();
                }
                if (Schema::hasTable('role_user')) {
                    DB::table('role_user')->whereIn('user_id', $userIds)->delete();
                }
                if (Schema::hasTable('personal_access_tokens')) {
                    DB::table('personal_access_tokens')->where('tokenable_type', 'App\\Models\\User')->whereIn('tokenable_id', $userIds)->delete();
                }
                if (Schema::hasTable('sessions')) {
                    DB::table('sessions')->whereIn('user_id', $userIds)->delete();
                }
            }

            if (! empty($userEmails) && Schema::hasTable('password_reset_tokens')) {
                DB::table('password_reset_tokens')->whereIn('email', $userEmails)->delete();
            }

            if (! empty($branchIds) && Schema::hasTable('branch_user')) {
                DB::table('branch_user')->whereIn('branch_id', $branchIds)->delete();
            }

            if (! empty($workoutPlanIds) && Schema::hasTable('workout_exercises')) {
                DB::table('workout_exercises')->whereIn('workout_plan_id', $workoutPlanIds)->delete();
            }

            if (! empty($dietPlanIds) && Schema::hasTable('diet_meals')) {
                DB::table('diet_meals')->whereIn('diet_plan_id', $dietPlanIds)->delete();
            }

            if (! empty($supportTicketIds) && Schema::hasTable('support_ticket_replies')) {
                DB::table('support_ticket_replies')->whereIn('support_ticket_id', $supportTicketIds)->delete();
            }

            if (! empty($equipmentIds) && Schema::hasTable('equipment_maintenance_logs')) {
                DB::table('equipment_maintenance_logs')->whereIn('gym_equipment_id', $equipmentIds)->delete();
            }

            if (! empty($inventoryItemIds) && Schema::hasTable('inventory_logs')) {
                DB::table('inventory_logs')->whereIn('inventory_item_id', $inventoryItemIds)->delete();
            }

            // 2. Purge across all database tables having a 'tenant_id' column
            $dbName = DB::getDatabaseName();
            $tablesWithTenantId = DB::select(
                "SELECT TABLE_NAME FROM information_schema.columns WHERE TABLE_SCHEMA = ? AND COLUMN_NAME = 'tenant_id' AND TABLE_NAME != 'tenants'",
                [$dbName]
            );

            foreach ($tablesWithTenantId as $row) {
                $tableName = $row->TABLE_NAME;
                if (Schema::hasTable($tableName)) {
                    DB::table($tableName)->where('tenant_id', $tenantId)->delete();
                }
            }

            // 3. Delete non-super_admin users of this tenant
            DB::table('users')->where('tenant_id', $tenantId)->where('role', '!=', 'super_admin')->delete();

            // 4. Clean activity logs where tenant was the subject
            if (Schema::hasTable('activity_logs')) {
                DB::table('activity_logs')->where('subject_type', 'App\\Models\\Tenant')->where('subject_id', $tenantId)->delete();
            }

            // 5. Delete tenant record permanently
            DB::table('tenants')->where('id', $tenantId)->delete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return true;
        });
    }
}
