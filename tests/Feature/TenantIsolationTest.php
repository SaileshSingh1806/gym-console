<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Plan;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tenants_cannot_access_other_tenants_members(): void
    {
        $plan = Plan::where('slug', 'starter')->first();
        $tenantService = app(TenantService::class);
        $rand = Str::random(6);

        // Create Gym A
        $gymA = $tenantService->registerGym([
            'gym_name' => "Gym Alpha {$rand}",
            'email' => "alpha_{$rand}@gym.com",
            'owner_name' => 'Alpha Owner',
            'password' => 'password',
        ], $plan);

        // Create Gym B
        $gymB = $tenantService->registerGym([
            'gym_name' => "Gym Beta {$rand}",
            'email' => "beta_{$rand}@gym.com",
            'owner_name' => 'Beta Owner',
            'password' => 'password',
        ], $plan);

        // Create member in Gym A
        TenantContext::setTenant($gymA['tenant']);
        TenantContext::setBranch($gymA['branch']);
        $memberA = Member::create([
            'tenant_id' => $gymA['tenant']->id,
            'branch_id' => $gymA['branch']->id,
            'member_code' => "ALPHA-{$rand}",
            'first_name' => 'Alice',
            'last_name' => 'Alpha',
            'phone' => '11111111',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Create member in Gym B
        TenantContext::setTenant($gymB['tenant']);
        TenantContext::setBranch($gymB['branch']);
        $memberB = Member::create([
            'tenant_id' => $gymB['tenant']->id,
            'branch_id' => $gymB['branch']->id,
            'member_code' => "BETA-{$rand}",
            'first_name' => 'Bob',
            'last_name' => 'Beta',
            'phone' => '22222222',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // When in Gym A context, only Member A is returned
        TenantContext::setTenant($gymA['tenant']);
        TenantContext::setBranch($gymA['branch']);
        $gymAMembers = Member::all();
        $this->assertTrue($gymAMembers->contains($memberA));
        $this->assertFalse($gymAMembers->contains($memberB));

        // When in Gym B context, only Member B is returned
        TenantContext::setTenant($gymB['tenant']);
        TenantContext::setBranch($gymB['branch']);
        $gymBMembers = Member::all();
        $this->assertTrue($gymBMembers->contains($memberB));
        $this->assertFalse($gymBMembers->contains($memberA));
    }
}
