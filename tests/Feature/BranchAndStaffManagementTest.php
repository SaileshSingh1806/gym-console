<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BranchAndStaffManagementTest extends TestCase
{
    use DatabaseTransactions;

    public function test_branch_and_staff_quota_restrictions_and_branch_crud(): void
    {
        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);

        $freePlan = Plan::where('slug', 'free-forever')->first();
        $starterPlan = Plan::where('slug', 'starter')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // 1. Register Gym on Free Forever Plan (1 Branch, 2 Staff)
        $registration = $tenantService->registerGym([
            'gym_name' => 'Apex Fitness Free',
            'email' => 'apexowner@testgym.com',
            'owner_name' => 'Apex Owner',
            'password' => 'password',
            'phone' => '9877777771',
        ], $freePlan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];

        // Free plan has staff_roles enabled
        $this->actingAs($owner)->get(route('app.staff.index'))->assertOk();

        // Add 1st staff member (Allowed)
        $this->actingAs($owner)->post(route('app.staff.store'), [
            'name' => 'Staff Member One',
            'email' => 'staff1@testgym.com',
            'phone' => '9877777772',
            'role' => 'receptionist',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');

        // Add 2nd staff member (Allowed - reaches limit of 2)
        $this->actingAs($owner)->post(route('app.staff.store'), [
            'name' => 'Staff Member Two',
            'email' => 'staff2@testgym.com',
            'phone' => '9877777773',
            'role' => 'trainer',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');

        // Add 3rd staff member (Blocked by Quota on Free plan)
        $this->actingAs($owner)->post(route('app.staff.store'), [
            'name' => 'Staff Member Three',
            'email' => 'staff3@testgym.com',
            'phone' => '9877777774',
            'role' => 'staff',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('error');

        // Try adding 2nd Branch on Free plan (Limit is 1 Branch)
        $this->actingAs($owner)->post(route('app.branches.store'), [
            'name' => 'Apex Branch Two',
            'city' => 'Mumbai',
        ])->assertRedirect()->assertSessionHas('error');

        // 2. Upgrade to Pro Plan (10 Branches, 25 Staff)
        $subscriptionService->activateSubscription($tenant, $proPlan, 'monthly', 'manual', 'TXN_PRO_TEST', 1000.0);
        $tenant->refresh();
        $owner->unsetRelation('tenant');
        $owner->refresh();

        // Now adding 2nd Branch succeeds on Pro Plan
        $this->actingAs($owner)->post(route('app.branches.store'), [
            'name' => 'Apex Branch Two',
            'code' => 'APEX02',
            'phone' => '9877777775',
            'city' => 'Mumbai',
            'address' => 'Andheri West',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'tenant_id' => $tenant->id,
            'name' => 'Apex Branch Two',
            'code' => 'APEX02',
        ]);

        $branch2 = Branch::where('tenant_id', $tenant->id)->where('code', 'APEX02')->first();

        // Update Branch Two
        $this->actingAs($owner)->post(route('app.branches.update', $branch2->id), [
            'name' => 'Apex Premier Andheri',
            'code' => 'APEX02',
            'city' => 'Mumbai North',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertDatabaseHas('branches', [
            'id' => $branch2->id,
            'name' => 'Apex Premier Andheri',
        ]);

        // Switch Active Branch
        $this->actingAs($owner)->post(route('app.branches.switch', $branch2->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertEquals($branch2->id, session('active_branch_id'));

        // Delete Branch Two (succeeds because it's not primary and gym has 2 branches)
        $this->actingAs($owner)->delete(route('app.branches.delete', $branch2->id))
            ->assertRedirect()
            ->assertSessionHas('success');

        // Add 3rd staff member now succeeds on Pro Plan
        $this->actingAs($owner)->post(route('app.staff.store'), [
            'name' => 'Staff Member Three',
            'email' => 'staff3@testgym.com',
            'phone' => '9877777774',
            'role' => 'staff',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');
    }

    public function test_branch_switching_is_blocked_when_downgraded_to_free_plan(): void
    {
        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);

        $freePlan = Plan::where('slug', 'free-forever')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // 1. Register Gym on Pro Plan (Multi-branch)
        $registration = $tenantService->registerGym([
            'gym_name' => 'Apex Fitness Multi',
            'email' => 'multigym@testgym.com',
            'owner_name' => 'Multi Owner',
            'password' => 'password',
            'phone' => '9877777780',
        ], $proPlan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];
        $branch1 = $tenant->mainBranch;

        // Create Branch Two on Pro Plan
        $this->actingAs($owner)->post(route('app.branches.store'), [
            'name' => 'Apex Branch Two',
            'code' => 'APEX02',
            'phone' => '9877777781',
            'city' => 'Mumbai',
            'status' => 'ACTIVE',
        ])->assertRedirect()->assertSessionHas('success');

        $branch2 = Branch::where('tenant_id', $tenant->id)->where('code', 'APEX02')->first();

        // On Pro Plan, switching to Branch Two is allowed
        $this->actingAs($owner)->post(route('app.branches.switch', $branch2->id))
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertEquals($branch2->id, session('active_branch_id'));

        // 2. Downgrade to Free Plan (1 Branch allowed)
        $subscriptionService->activateSubscription($tenant, $freePlan, 'monthly', 'free', 'FREE_DOWNGRADE', 0.0);
        $tenant->refresh();
        $owner->unsetRelation('tenant');
        $owner->refresh();

        // On Free Plan, attempting to switch to Branch Two MUST be blocked
        $this->actingAs($owner)->post(route('app.branches.switch', $branch2->id))
            ->assertRedirect()
            ->assertSessionHas('error');

        // Middleware automatically resets active branch to main allowed branch
        session(['active_branch_id' => $branch2->id]);
        $this->actingAs($owner)->get(route('app.dashboard'))->assertOk();
        $this->assertEquals($branch1->id, session('active_branch_id'));
    }
}
