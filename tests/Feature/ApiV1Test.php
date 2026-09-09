<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiV1Test extends TestCase
{
    use DatabaseTransactions;

    public function test_api_v1_login_and_fetch_profile(): void
    {
        $plan = Plan::firstOrCreate(
            ['slug' => 'test-pro'],
            ['name' => 'Pro Test', 'price_monthly' => 1499, 'price_yearly' => 14990, 'trial_days' => 14, 'member_limit' => 500, 'branch_limit' => 2, 'staff_limit' => 10]
        );

        $tenantService = app(TenantService::class);
        $registration = $tenantService->registerGym([
            'gym_name' => 'API Test Gym',
            'owner_name' => 'API Owner',
            'email' => 'api_owner@gymconsole.com',
            'password' => 'secret123',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
        ], $plan);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'api_owner@gymconsole.com',
            'password' => 'secret123',
            'device_name' => 'Flutter Test Device',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'token',
                'user' => ['id', 'name', 'email', 'role', 'tenant'],
            ],
        ]);

        $token = $response->json('data.token');

        // Fetch members list with bearer token
        $membersResponse = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/members');

        $membersResponse->assertStatus(200);
        $membersResponse->assertJsonStructure([
            'success',
            'data',
        ]);
    }
}
