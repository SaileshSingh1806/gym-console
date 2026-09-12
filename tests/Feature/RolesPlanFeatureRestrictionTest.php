<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RolesPlanFeatureRestrictionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_roles_permission_matrix_only_shows_and_allows_active_plan_features(): void
    {
        (new PermissionSeeder)->run();

        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);

        $freePlan = Plan::where('slug', 'free-forever')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // 1. Register Gym on Free Forever Plan
        $registration = $tenantService->registerGym([
            'gym_name' => 'Role Scope Gym',
            'email' => 'rolescope@testgym.com',
            'owner_name' => 'Scope Owner',
            'password' => 'password',
            'phone' => '9877777790',
        ], $freePlan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];

        // Get roles page for Free plan
        $response = $this->actingAs($owner)->get(route('app.roles.index'));
        $response->assertOk();

        // Free plan includes: members, memberships, attendance, payments, workouts, diets, services, staff
        $response->assertSee('members.view');
        $response->assertSee('attendance.view');
        $response->assertSee('workouts.view');
        $response->assertSee('diets.view');
        $response->assertSee('services.view');

        // Free plan does NOT include: crm, devices (hikvision_iot), classes, personal training, inventory
        $response->assertDontSee('devices.view');
        $response->assertDontSee('crm.view');
        $response->assertDontSee('classes.view');
        $response->assertDontSee('pt.view');
        $response->assertDontSee('inventory.view');

        // Verify User::hasPermission enforces plan features
        $this->assertFalse($owner->hasPermission('devices.view'));
        $this->assertFalse($owner->hasPermission('crm.view'));
        $this->assertTrue($owner->hasPermission('members.view'));

        // 2. Upgrade to Pro Plan
        $subscriptionService->activateSubscription($tenant, $proPlan, 'monthly', 'manual', 'TXN_PRO_ROLES', 1000.0);
        $tenant->refresh();
        $owner->unsetRelation('tenant');
        $owner->refresh();

        // Get roles page for Pro plan
        $responsePro = $this->actingAs($owner)->get(route('app.roles.index'));
        $responsePro->assertOk();

        // Pro plan displays ALL features in the matrix
        $responsePro->assertSee('devices.view');
        $responsePro->assertSee('crm.view');
        $responsePro->assertSee('classes.view');
        $responsePro->assertSee('pt.view');
        $responsePro->assertSee('inventory.view');

        // Verify owner now has permissions for all pro features
        $this->assertTrue($owner->hasPermission('devices.view'));
        $this->assertTrue($owner->hasPermission('crm.view'));

        // Test updating permission matrix
        $managerRole = Role::where('tenant_id', $tenant->id)->where('name', 'gym_manager')->first();
        $devPerm = Permission::where('name', 'devices.view')->first();

        $this->actingAs($owner)->post(route('app.roles.matrix.update'), [
            'matrix' => [
                $managerRole->id => [$devPerm->id],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $managerRole->refresh();
        $this->assertTrue($managerRole->permissions->contains('id', $devPerm->id));
    }
}

