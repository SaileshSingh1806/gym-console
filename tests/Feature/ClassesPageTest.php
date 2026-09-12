<?php

namespace Tests\Feature;

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
}
