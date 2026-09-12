<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    protected Tenant $tenant;

    protected Branch $branch;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Demo Gym',
            'slug' => 'demo-services',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->branch = Branch::where('tenant_id', $this->tenant->id)->first() ?? Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Main',
            'code' => 'MAIN',
        ]);

        $this->user = User::where('tenant_id', $this->tenant->id)->first() ?? User::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'name' => 'Admin User',
            'email' => 'admin_services@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);
    }

    public function test_services_page_renders_successfully(): void
    {
        $response = $this->actingAs($this->user)->get('/app/services');

        $response->assertStatus(200);
        $response->assertSee('Services');
        $response->assertSee('Add New Service');
    }

    public function test_can_create_and_update_service(): void
    {
        // Create
        $response = $this->actingAs($this->user)->post('/app/services', [
            'name' => 'Steam Bath & Sauna Deluxe',
            'amount' => 499.00,
            'duration_minutes' => 45,
            'timeslot_availability' => '07:00 AM - 09:00 PM',
            'description' => 'Therapeutic heat session',
            'is_visible_in_portal' => '1',
            'is_session_countable' => '1',
            'session_count' => 5,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gym_services', [
            'name' => 'Steam Bath & Sauna Deluxe',
            'amount' => 499.00,
            'duration_minutes' => 45,
        ]);

        $service = GymService::where('name', 'Steam Bath & Sauna Deluxe')->first();

        // Update
        $updateResponse = $this->actingAs($this->user)->post("/app/services/{$service->id}", [
            'name' => 'Steam Bath & Sauna Premium',
            'amount' => 599.00,
            'duration_minutes' => 60,
            'timeslot_availability' => '06:00 AM - 10:00 PM',
            'description' => 'Upgraded aroma heat session',
            'is_visible_in_portal' => '1',
            'is_session_countable' => '1',
            'session_count' => 10,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('gym_services', [
            'id' => $service->id,
            'name' => 'Steam Bath & Sauna Premium',
            'amount' => 599.00,
        ]);
    }

    public function test_can_book_service_and_deduct_sessions(): void
    {
        $member = Member::where('tenant_id', $this->tenant->id)->first() ?? Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_code' => 'MEM001',
            'first_name' => 'Test',
            'last_name' => 'Client',
            'phone' => '9998881111',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $service = GymService::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Locker Rental Platinum',
            'amount' => 1200.00,
            'duration_minutes' => 60,
            'is_locker_service' => true,
            'is_session_countable' => true,
            'session_count' => 10,
        ]);

        // Book
        $bookResp = $this->actingAs($this->user)->post('/app/services/bookings', [
            'member_id' => $member->id,
            'gym_service_id' => $service->id,
            'amount_paid' => 1200.00,
            'payment_method' => 'Cash',
            'locker_number' => 'L-42',
            'notes' => '10 session locker card',
        ]);

        $bookResp->assertRedirect();
        $this->assertDatabaseHas('gym_service_bookings', [
            'member_id' => $member->id,
            'gym_service_id' => $service->id,
            'locker_number' => 'L-42',
            'sessions_left' => 10,
        ]);

        $booking = GymServiceBooking::where('member_id', $member->id)->where('gym_service_id', $service->id)->first();

        // Deduct 1 session
        $deductResp = $this->actingAs($this->user)->post("/app/services/bookings/{$booking->id}/deduct");
        $deductResp->assertRedirect();

        $booking->refresh();
        $this->assertEquals(9, $booking->sessions_left);
    }
}
