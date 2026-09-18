<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\ClassBooking;
use App\Models\ClassSchedule;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class ClassesPageTest extends TestCase
{
    public function test_classes_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Test Gym',
            'slug' => 'test-gym-classes',
            'status' => 'ACTIVE',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);
        $this->attachProSubscription($tenant);
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gym Owner',
            'email' => 'owner_classes@gym.com',
            'password' => bcrypt('password'),
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($user)->get('/app/classes');

        $response->assertStatus(200);
    }

    public function test_member_can_be_enrolled_in_class_and_displayed_on_profile(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Test Gym',
            'slug' => 'test-gym-classes-2',
            'status' => 'ACTIVE',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ]);
        $this->attachProSubscription($tenant);
        $user = User::where('tenant_id', $tenant->id)->first();
        $branch = Branch::where('tenant_id', $tenant->id)->first() ?? Branch::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Branch',
            'slug' => 'main-branch',
            'is_main' => true,
        ]);

        $member = Member::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'member_code' => 'MEM-TEST-'.uniqid(),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '9988'.rand(100000, 999999),
            'status' => 'ACTIVE',
            'join_date' => now()->toDateString(),
        ]);

        $gymClass = GymClass::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'name' => 'Power Pilates Flow',
            'class_type' => 'Pilates',
            'fee' => 0,
            'is_active' => true,
            'duration_minutes' => 45,
        ]);

        $schedule = ClassSchedule::create([
            'tenant_id' => $tenant->id,
            'branch_id' => $branch->id,
            'gym_class_id' => $gymClass->id,
            'day_of_week' => 'monday',
            'start_time' => '07:00:00',
            'end_time' => '07:45:00',
            'is_active' => true,
        ]);

        // 1. View member profile before enrollment
        $showResp = $this->actingAs($user)->get(route('app.members.show', $member->id));
        $showResp->assertStatus(200);
        $showResp->assertDontSee('Morning Yoga & Mobility'); // Dummy removed

        // 2. Enroll member into class
        $enrollResp = $this->actingAs($user)->post(route('app.members.enroll-class', $member->id), [
            'class_schedule_id' => $schedule->id,
            'booking_date' => now()->toDateString(),
            'status' => 'BOOKED',
        ]);
        $enrollResp->assertRedirect();
        $enrollResp->assertSessionHas('success');

        // 3. Verify member profile shows the enrolled class
        $showRespAfter = $this->actingAs($user)->get(route('app.members.show', $member->id));
        $showRespAfter->assertStatus(200);
        $showRespAfter->assertSee('Power Pilates Flow');
        $showRespAfter->assertSee('Pilates');
        $showRespAfter->assertSee('Enrolled / Booked');

        // 4. Delete / Un-enroll
        $booking = ClassBooking::where('member_id', $member->id)->where('class_schedule_id', $schedule->id)->first();
        $this->assertNotNull($booking);

        $deleteResp = $this->actingAs($user)->delete(route('app.classes.booking.delete', $booking->id));
        $deleteResp->assertRedirect();
        $this->assertDatabaseMissing('class_bookings', ['id' => $booking->id]);
    }
}
