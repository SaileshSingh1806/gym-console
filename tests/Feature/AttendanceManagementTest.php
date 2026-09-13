<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AttendanceManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Tenant $tenant;

    protected Branch $branch;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Fitness Attendance Gym',
            'slug' => 'attgym',
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->first() ?? Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_main' => true,
        ]);

        $this->user = User::where('tenant_id', $this->tenant->id)->first() ?? User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Manager User',
            'email' => 'manager_att@gym.com',
            'password' => bcrypt('password123'),
            'role' => 'gym_manager',
            'is_owner' => false,
            'status' => 'ACTIVE',
        ]);

        $this->member = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'first_name' => 'Test',
            'last_name' => 'AttMember',
            'member_code' => 'TEST'.rand(10000, 99999),
            'phone' => '99999'.rand(10000, 99999),
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);
    }

    public function test_attendance_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get(route('app.attendance.index'));
        $response->assertStatus(200);
        $response->assertSee('Gym Attendance & Live Access');
    }

    public function test_can_checkin_and_checkout_member(): void
    {
        // Check in
        // Check in 15 minutes ago
        $initialCheckIn = now()->subMinutes(15);
        $response = $this->actingAs($this->user)->post(route('app.attendance.store'), [
            'member_id' => $this->member->id,
            'action' => 'check_in',
        ]);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $att = Attendance::where('tenant_id', $this->tenant->id)
            ->where('member_id', $this->member->id)
            ->where('date', now()->toDateString())
            ->first();

        $this->assertNotNull($att);
        $this->assertNotNull($att->check_in);
        $this->assertNull($att->check_out);

        // Manually set check_in to 15 mins ago to verify it doesn't get overwritten on checkout
        $att->update(['check_in' => $initialCheckIn]);
        $originalCheckIn = $att->fresh()->check_in->toDateTimeString();

        // Check out via ID
        $checkoutResponse = $this->actingAs($this->user)->post(route('app.attendance.checkout', $att->id));
        $checkoutResponse->assertRedirect();
        $checkoutResponse->assertSessionHas('success');

        $att->refresh();
        $this->assertNotNull($att->check_out);
        // Check in must NOT change to current timestamp on checkout
        $this->assertEquals($originalCheckIn, $att->check_in->toDateTimeString());
        $this->assertNotEquals($att->check_in->toDateTimeString(), $att->check_out->toDateTimeString());
    }

    public function test_can_checkout_via_manual_form(): void
    {
        // Check in first
        $att = Attendance::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_id' => $this->member->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subMinutes(30),
            'method' => 'manual',
            'status' => 'PRESENT',
        ]);

        $response = $this->actingAs($this->user)->post(route('app.attendance.store'), [
            'member_id' => $this->member->id,
            'action' => 'check_out',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $att->refresh();
        $this->assertNotNull($att->check_out);
    }
}
