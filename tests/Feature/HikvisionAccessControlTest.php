<?php

namespace Tests\Feature;

use App\Http\Controllers\Web\AppController;
use App\Models\Device;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Services\AccessControl\AccessControlService;
use App\Services\MembershipService;
use App\Services\SubscriptionService;
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
        $plan = Plan::where('slug', 'pro')->first();
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

    public function test_essl_desktop_middleware_event_grants_access_and_records_attendance(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $tenantService = app(TenantService::class);
        $membershipService = app(MembershipService::class);
        $accessControlService = app(AccessControlService::class);
        $rand = Str::random(6);

        $registration = $tenantService->registerGym([
            'gym_name' => "eSSL PowerGym {$rand}",
            'email' => "essl_{$rand}@gym.com",
            'owner_name' => 'eSSL Owner',
            'password' => 'password',
        ], $plan);

        $tenant = $registration['tenant'];
        $branch = $registration['branch'];
        TenantContext::setTenant($tenant);
        TenantContext::setBranch($branch);

        $device = Device::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'eSSL SilkBio Reception',
            'type' => 'essl_desktop',
            'serial_number' => "ESSL-{$rand}",
            'direction' => 'in',
            'status' => 'ONLINE',
        ]);

        $mPlan = MembershipPlan::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gold Annual',
            'duration_type' => 'months',
            'duration_value' => 12,
            'price' => 500.00,
        ]);

        $member = Member::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'member_code' => "ESSL-MEM-{$rand}",
            'first_name' => 'Ronnie',
            'last_name' => 'Coleman',
            'phone' => '88888888',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membershipService->assignMembership($member, $mPlan);

        // Simulate incoming eSSL desktop middleware punch payload
        $payload = [
            'EnrollNumber' => "ESSL-MEM-{$rand}",
            'PunchTime' => now()->format('Y-m-d H:i:s'),
            'DeviceId' => "ESSL-{$rand}",
        ];

        $result = $accessControlService->processDeviceEvent($device, $payload);

        $this->assertTrue($result['granted']);
        $this->assertEquals('Ronnie Coleman', $result['member']);

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

    public function test_device_crud_in_settings(): void
    {
        $plan = Plan::where('slug', 'pro')->first();
        $tenantService = app(TenantService::class);
        $rand = Str::random(6);

        $registration = $tenantService->registerGym([
            'gym_name' => "Device Settings Gym {$rand}",
            'email' => "devset_{$rand}@gym.com",
            'owner_name' => 'Settings Owner',
            'password' => 'password',
        ], $plan);

        $tenant = $registration['tenant'];
        $user = $registration['owner'];
        $branch = $registration['branch'];

        $subscriptionService = app(SubscriptionService::class);
        $subscriptionService->activateSubscription($tenant, $plan, 'monthly', 'manual', 'TXN_DEV_TEST', 1000.0);
        $tenant->refresh();
        $user->unsetRelation('tenant');
        $user->refresh();
        TenantContext::setTenant($tenant);
        TenantContext::setBranch($branch);

        $this->actingAs($user);

        // 1. Verify Controller settings method returns view with devices
        $view = app(AppController::class)->settings(request());
        $this->assertEquals('app.settings.index', $view->name());
        $this->assertArrayHasKey('devices', $view->getData());

        // 2. Create device
        $device = Device::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Front Desk eSSL K90',
            'model' => 'eSSL K90',
            'type' => 'essl_desktop',
            'direction' => 'both',
            'ip_address' => '192.168.1.150',
            'port' => 4370,
            'serial_number' => "K90-{$rand}",
            'status' => 'ONLINE',
        ]);

        $this->assertDatabaseHas('devices', [
            'tenant_id' => $tenant->id,
            'name' => 'Front Desk eSSL K90',
            'type' => 'essl_desktop',
            'serial_number' => "K90-{$rand}",
        ]);

        // 3. Ping test device
        $testResponse = $this->post(route('app.devices.test', $device->id));
        $testResponse->assertRedirect(route('app.settings.index', ['tab' => 'devices']));

        // 4. Delete device
        $deleteResponse = $this->delete(route('app.devices.delete', $device->id));
        $deleteResponse->assertRedirect(route('app.settings.index', ['tab' => 'devices']));
        $this->assertDatabaseMissing('devices', [
            'id' => $device->id,
        ]);
    }
}
