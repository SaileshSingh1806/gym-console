<?php

namespace Tests\Feature;

use App\Models\Device;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Services\AccessControl\AccessControlService;
use App\Services\MembershipService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class HikvisionAccessControlTest extends TestCase
{
    use DatabaseTransactions;

    public function test_hikvision_event_grants_access_and_records_attendance(): void
    {
        $plan = Plan::where('slug', 'enterprise')->first();
        $tenantService = app(TenantService::class);
        $membershipService = app(MembershipService::class);
        $accessControlService = app(AccessControlService::class);
        $rand = Str::random(6);

        $registration = $tenantService->registerGym([
            'gym_name' => "Apex Fitness {$rand}",
            'email' => "apex_{$rand}@gym.com",
            'owner_name' => 'Apex Owner',
            'password' => 'password',
        ], $plan);

        $tenant = $registration['tenant'];
        $branch = $registration['branch'];
        TenantContext::setTenant($tenant);
        TenantContext::setBranch($branch);

        $device = Device::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Front Turnstile',
            'type' => 'hikvision_facial',
            'serial_number' => "HKV-APEX-{$rand}",
            'direction' => 'in',
            'status' => 'ONLINE',
        ]);

        $mPlan = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Monthly',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 50.00,
        ]);

        $member = Member::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'member_code' => "APEX-{$rand}",
            'first_name' => 'Arnold',
            'last_name' => 'Schwarzenegger',
            'phone' => '99999999',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membershipService->assignMembership($member, $mPlan);

        // Simulate incoming Hikvision event webhook
        $payload = [
            'member_code' => "APEX-{$rand}",
            'dateTime' => now()->toIso8601String(),
        ];

        $result = $accessControlService->processDeviceEvent($device, $payload);

        $this->assertTrue($result['granted']);

        // Assert Access Log is GRANTED
        $this->assertDatabaseHas('access_logs', [
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'device_id' => $device->id,
            'access_status' => 'GRANTED',
        ]);

        // Assert Attendance was created
        $this->assertDatabaseHas('attendance', [
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'status' => 'PRESENT',
        ]);
    }
}
