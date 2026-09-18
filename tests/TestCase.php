<?php

namespace Tests;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (Plan::count() === 0) {
            (new PlanSeeder)->run();
        }
    }

    protected function attachProSubscription(Tenant $tenant): Subscription
    {
        $plan = Plan::where('slug', 'pro')->first();
        if (! $plan) {
            $plan = Plan::first();
            (new PlanSeeder)->run();
            $plan = Plan::where('slug', 'pro')->first();
        }

        return Subscription::firstOrCreate([
            'tenant_id' => $tenant->id,
            'status' => 'ACTIVE',
        ], [
            'plan_id' => $plan->id,
            'billing_cycle' => 'yearly',
            'starts_at' => now()->subDay(),
            'ends_at' => now()->addYear(),
            'gateway_name' => 'manual',
            'gateway_subscription_id' => 'TEST_SUB_'.$tenant->id,
        ]);
    }
}
