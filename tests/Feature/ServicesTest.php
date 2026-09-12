<?php

namespace Tests\Feature;

use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\Member;
use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class ServicesTest extends TestCase
{
    public function test_services_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->get('/app/services');

        $response->assertStatus(200);
        $response->assertSee('Services');
        $response->assertSee('Add New Service');
    }

    public function test_can_create_and_update_service(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);

        $response = $this->actingAs($user)->post('/app/services', [
            'name' => 'Aromatherapy Sauna',
            'amount' => 450,
            'duration_minutes' => 45,
            'timeslot_availability' => '9 AM - 8 PM',
            'description' => 'Aromatic herbal steam sauna',
            'status' => 'active',
            'is_visible_in_portal' => 1,
            'is_locker_service' => 0,
            'is_session_countable' => 1,
            'session_count' => 3,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('gym_services', [
            'tenant_id' => $tenant->id,
            'name' => 'Aromatherapy Sauna',
            'amount' => 450,
            'session_count' => 3,
        ]);

        $service = GymService::where('name', 'Aromatherapy Sauna')->first();

        // Update service
        $updateResponse = $this->actingAs($user)->post("/app/services/{$service->id}", [
            'name' => 'Aromatherapy Sauna Deluxe',
            'amount' => 500,
            'duration_minutes' => 60,
            'timeslot_availability' => '9 AM - 8 PM',
            'description' => 'Updated aroma sauna',
            'status' => 'active',
            'is_visible_in_portal' => 1,
            'is_locker_service' => 0,
            'is_session_countable' => 1,
            'session_count' => 4,
        ]);

        $updateResponse->assertRedirect();
        $this->assertDatabaseHas('gym_services', [
            'id' => $service->id,
            'name' => 'Aromatherapy Sauna Deluxe',
            'amount' => 500,
        ]);
    }

    public function test_can_book_service_and_deduct_sessions(): void
    {
        $tenant = Tenant::first() ?? Tenant::factory()->create();
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::factory()->create(['tenant_id' => $tenant->id]);
        $member = Member::where('tenant_id', $tenant->id)->first() ?? Member::factory()->create(['tenant_id' => $tenant->id]);

        $service = GymService::create([
            'tenant_id' => $tenant->id,
            'name' => 'Hydrotherapy Bath',
            'amount' => 800,
            'duration_minutes' => 60,
            'status' => 'active',
            'is_session_countable' => true,
            'session_count' => 2,
        ]);

        // Book service
        $bookingResponse = $this->actingAs($user)->post('/app/services/bookings', [
            'member_id' => $member->id,
            'gym_service_id' => $service->id,
            'booking_date' => now()->toDateString(),
            'amount_paid' => 800,
            'locker_number' => 'L-42',
        ]);

        $bookingResponse->assertRedirect();
        $this->assertDatabaseHas('gym_service_bookings', [
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'gym_service_id' => $service->id,
            'total_sessions' => 2,
            'sessions_left' => 2,
            'locker_number' => 'L-42',
        ]);

        $booking = GymServiceBooking::where('gym_service_id', $service->id)->where('member_id', $member->id)->first();

        // Deduct 1 session
        $deductResponse = $this->actingAs($user)->post("/app/services/bookings/{$booking->id}/deduct");
        $deductResponse->assertRedirect();

        $booking->refresh();
        $this->assertEquals(1, $booking->sessions_left);

        // Deduct remaining session
        $this->actingAs($user)->post("/app/services/bookings/{$booking->id}/deduct");
        $booking->refresh();
        $this->assertEquals(0, $booking->sessions_left);
        $this->assertEquals('completed', $booking->status);
    }
}

