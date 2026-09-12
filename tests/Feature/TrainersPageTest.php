<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Tests\TestCase;

class TrainersPageTest extends TestCase
{
    public function test_trainers_page_renders_successfully(): void
    {
        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Demo Gym',
            'slug' => 'demo-trainers',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($tenant);
        $user = User::where('tenant_id', $tenant->id)->first() ?? User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
        ]);

        $response = $this->actingAs($user)->get('/app/trainers');

        $response->assertStatus(200);
        $response->assertSee('Fitness Trainers', false);
        $response->assertSee('Add Trainer');
    }
}
