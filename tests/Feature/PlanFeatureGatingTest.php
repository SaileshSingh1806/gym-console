<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class PlanFeatureGatingTest extends TestCase
{
    use DatabaseTransactions;

    public function test_tenant_can_only_access_features_included_in_their_plan(): void
    {
        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);

        $freePlan = Plan::where('slug', 'free-forever')->first();
        $starterPlan = Plan::where('slug', 'starter')->first();
        $proPlan = Plan::where('slug', 'pro')->first();

        // 1. Create a gym on Free Forever Plan
        $registration = $tenantService->registerGym([
            'gym_name' => 'Free Tier Gym',
            'email' => 'freeowner@gym.com',
            'owner_name' => 'Free Owner',
            'password' => 'password',
            'phone' => '9888888881',
        ], $freePlan);

        $tenant = $registration['tenant'];
        $owner = $registration['owner'];

        // Free plan has members_management and workout_plans
        $this->actingAs($owner)->get(route('app.members.index'))->assertOk();
        $this->actingAs($owner)->get(route('app.workouts.index'))->assertOk();

        // Free plan does NOT have crm_leads, personal_training, group_classes, hikvision_iot
        $this->actingAs($owner)->get(route('app.crm.index'))
            ->assertRedirect(route('app.subscription.index'))
            ->assertSessionHas('error');

        $this->actingAs($owner)->get(route('app.pt.index'))
            ->assertRedirect(route('app.subscription.index'))
            ->assertSessionHas('error');

        $this->actingAs($owner)->get(route('app.classes.index'))
            ->assertRedirect(route('app.subscription.index'))
            ->assertSessionHas('error');

        $this->actingAs($owner)->get(route('app.devices.index'))
            ->assertRedirect(route('app.subscription.index'))
            ->assertSessionHas('error');

        // Verify sidebar on Free plan does not render CRM or Personal Training links
        $dashboardResponse = $this->actingAs($owner)->get(route('app.dashboard'));
        $dashboardResponse->assertOk();
        $dashboardResponse->assertSee('MEMBERS');
        $dashboardResponse->assertSee('WORKOUT PLANS');
        $dashboardResponse->assertDontSee('CRM & LEADS');
        $dashboardResponse->assertDontSee('PERSONAL TRAINING');
        $dashboardResponse->assertDontSee('HIKVISION IOT');

        // 2. Upgrade Gym to Starter Plan
        $subscriptionService->activateSubscription($tenant, $starterPlan, 'monthly', 'manual', 'TXN_TEST_1', 999.0);
        $tenant->refresh();
        $owner->unsetRelation('tenant');
        $owner->refresh();

        // Starter plan HAS crm_leads, personal_training, group_classes, staff_roles
        $this->actingAs($owner)->get(route('app.crm.index'))->assertOk();
        $this->actingAs($owner)->get(route('app.pt.index'))->assertOk();
        $this->actingAs($owner)->get(route('app.classes.index'))->assertOk();
        $this->actingAs($owner)->get(route('app.staff.index'))->assertOk();

        // Starter plan does NOT have hikvision_iot (Pro only)
        $this->actingAs($owner)->get(route('app.devices.index'))
            ->assertRedirect(route('app.subscription.index'))
            ->assertSessionHas('error');

        // 3. Upgrade Gym to Pro Plan
        $subscriptionService->activateSubscription($tenant, $proPlan, 'monthly', 'manual', 'TXN_TEST_2', 1999.0);
        $tenant->refresh();
        $owner->unsetRelation('tenant');
        $owner->refresh();

        // Pro plan HAS hikvision_iot
        $this->actingAs($owner)->get(route('app.devices.index'))->assertOk();
    }
}
