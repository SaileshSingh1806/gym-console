<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminTenantDeleteTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_delete_gym_requires_exact_name_confirmation_and_cascades_all_data(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Admin',
            'email' => 'admin_test_del@gymconsole.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $plan = Plan::first();
        $tenantService = app(TenantService::class);

        // Register a test gym
        $registration = $tenantService->registerGym([
            'gym_name' => 'Iron Fortress Gym',
            'email' => 'owner_iron@fortress.com',
            'owner_name' => 'Iron Owner',
            'password' => 'password',
            'phone' => '9876543210',
        ], $plan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];
        $branch = $tenant->branches()->first();

        // Create a member
        $member = Member::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john_del@example.com',
            'phone' => '9998887771',
            'status' => 'ACTIVE',
            'member_code' => 'MEM-DEL-01',
            'join_date' => now()->toDateString(),
        ]);

        // 1. Attempt delete with wrong name -> should fail and keep gym
        $failResponse = $this->actingAs($superAdmin)->delete(route('admin.gyms.delete', $tenant->id), [
            'confirm_gym_name' => 'Wrong Name',
        ]);

        $failResponse->assertRedirect();
        $failResponse->assertSessionHas('error');
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);
        $this->assertDatabaseHas('members', ['id' => $member->id]);
        $this->assertDatabaseHas('users', ['id' => $owner->id]);

        // 2. Attempt delete with correct gym name -> should delete gym and all child records
        $successResponse = $this->actingAs($superAdmin)->delete(route('admin.gyms.delete', $tenant->id), [
            'confirm_gym_name' => 'Iron Fortress Gym',
        ]);

        $successResponse->assertRedirect();
        $successResponse->assertSessionHas('success');

        // Verify total deletion of tenant and its child records
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
        $this->assertDatabaseMissing('members', ['id' => $member->id]);
        $this->assertDatabaseMissing('users', ['id' => $owner->id]);
        $this->assertDatabaseMissing('branches', ['tenant_id' => $tenant->id]);
    }
}
