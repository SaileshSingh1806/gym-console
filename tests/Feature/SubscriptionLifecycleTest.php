<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\User;
use App\Services\FeatureGateService;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class SubscriptionLifecycleTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tenant_registration_starts_trial_and_payment_activates_subscription(): void
    {
        $plan = Plan::where('slug', 'starter')->first();
        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);
        $featureGateService = app(FeatureGateService::class);
        $rand = Str::random(6);

        $registration = $tenantService->registerGym([
            'gym_name' => "Titan Gym {$rand}",
            'email' => "titan_{$rand}@gym.com",
            'owner_name' => 'Titan Owner',
            'password' => 'password',
        ], $plan);

        $tenant = $registration['tenant'];

        // Initial state is TRIAL
        $this->assertEquals('TRIAL', $tenant->status);
        $this->assertTrue($tenant->isSubscriptionActive());

        // Check feature and quota enforcement
        $this->assertTrue($featureGateService->hasFeature($tenant, 'members'));
        $this->assertFalse($featureGateService->hasFeature($tenant, 'hikvision_integration')); // Starter doesn't have Hikvision

        // Activate via Payment
        $subscription = $subscriptionService->activateSubscription(
            $tenant,
            $plan,
            'monthly',
            'stripe',
            "txn_{$rand}",
            29.00
        );

        $this->assertEquals('ACTIVE', $subscription->status);
        $this->assertEquals('ACTIVE', $tenant->fresh()->status);

        // Verify Platform Invoice generated
        $invoice = PlatformInvoice::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(29.00, (float) $invoice->total);
        $this->assertEquals('PAID', $invoice->status);
    }

    public function test_expired_trial_is_blocked_and_can_be_renewed(): void
    {
        $plan = Plan::where('slug', 'starter')->first();
        $tenantService = app(TenantService::class);
        $rand = Str::random(6);

        $registration = $tenantService->registerGym([
            'gym_name' => "Expired Gym {$rand}",
            'email' => "expired_{$rand}@gym.com",
            'owner_name' => 'Expired Owner',
            'password' => 'password',
        ], $plan);

        $tenant = $registration['tenant'];
        $user = $registration['owner'];

        // Expire the trial manually
        $tenant->update([
            'status' => 'TRIAL',
            'trial_ends_at' => now()->subDay(),
        ]);
        $tenant->activeSubscription->update([
            'status' => 'TRIAL',
            'trial_ends_at' => now()->subDay(),
        ]);

        $this->assertTrue($tenant->fresh()->isTrialExpired());
        $this->assertFalse($tenant->fresh()->isSubscriptionActive());

        // Accessing protected gym routes redirects to subscription index
        $response = $this->actingAs($user)->get(route('app.dashboard'));
        $response->assertRedirect(route('app.subscription.index'));

        // Accessing subscription page is allowed
        $subPageResponse = $this->actingAs($user)->get(route('app.subscription.index'));
        $subPageResponse->assertOk();
        $subPageResponse->assertSee('Your Free Trial / SaaS Subscription Has Expired!');

        // Renew subscription
        $renewResponse = $this->actingAs($user)->post(route('app.subscription.upgrade'), [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);
        $renewResponse->assertRedirect(route('app.subscription.index'));

        $this->assertTrue($tenant->fresh()->isSubscriptionActive());
        $this->assertEquals('ACTIVE', $tenant->fresh()->status);
    }

    public function test_super_admin_can_manage_coupons_and_user_gets_discount(): void
    {
        $admin = User::where('role', 'super_admin')->first();
        $plan = Plan::where('slug', 'professional')->first();
        $rand = strtoupper(Str::random(4));
        $code = "TEST{$rand}";

        // 1. Super Admin creates a coupon
        $createResponse = $this->actingAs($admin)->post(route('admin.coupons.store'), [
            'code' => $code,
            'name' => 'Special 25% Off Test',
            'discount_type' => 'percentage',
            'discount_value' => 25.00,
            'plan_id' => $plan->id,
            'min_amount' => 100.00,
            'is_active' => '1',
        ]);
        $createResponse->assertRedirect();

        $coupon = Coupon::where('code', $code)->first();
        $this->assertNotNull($coupon);
        $this->assertEquals(25.00, (float) $coupon->discount_value);

        // 2. Gym Owner validates coupon
        $tenantService = app(TenantService::class);
        $registration = $tenantService->registerGym([
            'gym_name' => "Coupon Gym {$rand}",
            'email' => "coupongym_{$rand}@gym.com",
            'owner_name' => 'Coupon Owner',
            'password' => 'password',
        ], $plan);

        $user = $registration['owner'];

        $valResponse = $this->actingAs($user)->postJson(route('app.coupon.validate'), [
            'code' => $code,
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
        ]);
        $valResponse->assertOk();
        $valResponse->assertJson(['valid' => true, 'code' => $code]);

        // 3. Gym Owner upgrades with coupon
        $originalPrice = (float) $plan->price_monthly;
        $expectedDiscount = ($originalPrice * 25.0) / 100.0;
        $expectedPaid = $originalPrice - $expectedDiscount;

        $upgradeResponse = $this->actingAs($user)->post(route('app.subscription.upgrade'), [
            'plan_id' => $plan->id,
            'billing_cycle' => 'monthly',
            'coupon_code' => $code,
        ]);
        $upgradeResponse->assertRedirect(route('app.subscription.index'));

        // Verify Platform Invoice has coupon and discount recorded
        $invoice = PlatformInvoice::where('tenant_id', $registration['tenant']->id)->latest()->first();
        $this->assertNotNull($invoice);
        $this->assertEquals($coupon->id, $invoice->coupon_id);
        $this->assertEquals($expectedDiscount, (float) $invoice->discount);
        $this->assertEquals($expectedPaid, (float) $invoice->total);

        // Verify coupon usage count incremented
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }
}
