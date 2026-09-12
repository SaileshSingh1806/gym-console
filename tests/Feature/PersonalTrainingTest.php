<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\MemberPtPackage;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\PtSession;
use App\Models\Trainer;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class PersonalTrainingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setupGym()
    {
        $plan = Plan::where('slug', 'starter')->first() ?? Plan::create([
            'name' => 'Starter',
            'slug' => 'starter',
            'price_monthly' => 49.00,
            'price_yearly' => 490.00,
            'status' => 'ACTIVE',
        ]);
        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);
        $rand = Str::random(6);

        $gym = $tenantService->registerGym([
            'gym_name' => "PT Gym {$rand}",
            'email' => "pt_owner_{$rand}@gym.com",
            'owner_name' => 'PT Owner',
            'password' => 'password',
        ], $plan);

        $subscriptionService->activateSubscription(
            $gym['tenant'],
            $plan,
            'monthly',
            'manual',
            'TEST-TXN-'.$rand,
            49.00
        );

        TenantContext::setTenant($gym['tenant']);
        TenantContext::setBranch($gym['branch']);

        return $gym;
    }

    public function test_can_view_personal_training_index(): void
    {
        $gym = $this->setupGym();

        $response = $this->actingAs($gym['owner'])
            ->get(route('app.pt.index'));

        $response->assertStatus(200);
        $response->assertSee('Personal Training');
        $response->assertSee('PT Packages');
        $response->assertSee('Plan Catalog');
    }

    public function test_can_create_and_manage_pt_plans(): void
    {
        $gym = $this->setupGym();

        // 1. Create PT Plan
        $response = $this->actingAs($gym['owner'])
            ->post(route('app.pt-plans.store'), [
                'name' => '16 Sessions Pro Elite',
                'total_sessions' => 16,
                'validity_days' => 45,
                'default_price' => 7500.00,
                'trainer_commission_percent' => 20.00,
                'sac_code' => '999723',
                'gst_rate' => 18.00,
                'price_includes_gst' => 1,
                'is_active' => 1,
                'includes_gate_pass' => 1,
                'show_on_mobile_app' => 1,
                'description' => 'Elite personal coaching program',
            ]);

        $response->assertRedirect(route('app.pt.index', ['tab' => 'plans']));

        $plan = PtPlan::where('name', '16 Sessions Pro Elite')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(16, $plan->total_sessions);
        $this->assertTrue($plan->is_active);

        // 2. Toggle Status
        $toggleRes = $this->actingAs($gym['owner'])
            ->post(route('app.pt-plans.toggle', $plan->id));
        $toggleRes->assertRedirect(route('app.pt.index', ['tab' => 'plans']));
        $this->assertFalse($plan->fresh()->is_active);

        // 3. Update Plan
        $updateRes = $this->actingAs($gym['owner'])
            ->post(route('app.pt-plans.update', $plan->id), [
                'name' => '16 Sessions Pro Elite Updated',
                'total_sessions' => 16,
                'validity_days' => 50,
                'default_price' => 8000.00,
                'trainer_commission_percent' => 25.00,
                'sac_code' => '999723',
                'gst_rate' => 18.00,
                'is_active' => 1,
            ]);

        $updateRes->assertRedirect(route('app.pt.index', ['tab' => 'plans']));
        $this->assertEquals('16 Sessions Pro Elite Updated', $plan->fresh()->name);
        $this->assertEquals(8000.00, (float) $plan->fresh()->default_price);
    }

    public function test_can_assign_pt_package_and_log_sessions(): void
    {
        $gym = $this->setupGym();

        $member = Member::create([
            'tenant_id' => $gym['tenant']->id,
            'branch_id' => $gym['branch']->id,
            'member_code' => 'MEM-'.Str::random(5),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '9812345678',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $trainer = Trainer::create([
            'tenant_id' => $gym['tenant']->id,
            'branch_id' => $gym['branch']->id,
            'first_name' => 'Max',
            'last_name' => 'Power',
            'phone' => '9876540000',
            'specialization' => 'Hypertrophy',
            'hourly_rate' => 500,
            'status' => 'ACTIVE',
        ]);

        $plan = PtPlan::create([
            'tenant_id' => $gym['tenant']->id,
            'name' => '10 Sessions Sprint',
            'total_sessions' => 10,
            'validity_days' => 30,
            'default_price' => 4500.00,
            'is_active' => true,
        ]);

        // 1. Assign PT Package
        $response = $this->actingAs($gym['owner'])
            ->post(route('app.pt-packages.store'), [
                'member_id' => $member->id,
                'pt_plan_id' => $plan->id,
                'trainer_id' => $trainer->id,
                'package_name' => $plan->name,
                'total_sessions' => 10,
                'validity_days' => 30,
                'start_date' => now()->toDateString(),
                'price' => 4500.00,
                'discount' => 500.00,
                'paid_amount' => 4000.00,
                'payment_method' => 'upi',
            ]);

        $response->assertRedirect(route('app.pt.index', ['tab' => 'packages']));

        $package = MemberPtPackage::where('member_id', $member->id)->first();
        $this->assertNotNull($package);
        $this->assertEquals(10, $package->total_sessions);
        $this->assertEquals(0, $package->used_sessions);
        $this->assertEquals(4000.00, (float) $package->final_amount);
        $this->assertEquals(4000.00, (float) $package->paid_amount);
        $this->assertEquals('ACTIVE', $package->status);

        // 2. Log Session
        $logRes = $this->actingAs($gym['owner'])
            ->post(route('app.pt-packages.log-session', $package->id), ['count' => 1]);

        $logRes->assertSessionHas('success');
        $this->assertEquals(1, $package->fresh()->used_sessions);
        $this->assertEquals(9, $package->fresh()->remaining_sessions);

        // 3. Log Remaining Sessions
        $this->actingAs($gym['owner'])
            ->post(route('app.pt-packages.log-session', $package->id), ['count' => 9]);

        $this->assertEquals(10, $package->fresh()->used_sessions);
        $this->assertEquals('COMPLETED', $package->fresh()->status);
    }

    public function test_can_create_pt_plan_without_gst(): void
    {
        $gym = $this->setupGym();

        $response = $this->actingAs($gym['owner'])
            ->post(route('app.pt-plans.store'), [
                'name' => '10 Sessions No GST Plan',
                'total_sessions' => 10,
                'validity_days' => 30,
                'default_price' => 5000.00,
            ]);

        $response->assertRedirect(route('app.pt.index', ['tab' => 'plans']));

        $plan = PtPlan::where('name', '10 Sessions No GST Plan')->first();
        $this->assertNotNull($plan);
        $this->assertEquals(0.00, (float) $plan->gst_rate);
        $this->assertNull($plan->sac_code);
    }

    public function test_can_schedule_and_complete_pt_session(): void
    {
        $gym = $this->setupGym();

        $member = Member::create([
            'tenant_id' => $gym['tenant']->id,
            'branch_id' => $gym['branch']->id,
            'member_code' => 'MEM-'.Str::random(5),
            'first_name' => 'Subhasish',
            'last_name' => 'Pal',
            'phone' => '9811223344',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $trainer = Trainer::create([
            'tenant_id' => $gym['tenant']->id,
            'branch_id' => $gym['branch']->id,
            'first_name' => 'Divya',
            'last_name' => 'Iyer',
            'phone' => '9877665544',
            'specialization' => 'Functional Coaching',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($gym['owner'])
            ->post(route('app.pt-sessions.store'), [
                'member_id' => $member->id,
                'trainer_id' => $trainer->id,
                'session_date' => now()->toDateString(),
                'start_time' => '19:59',
                'duration_minutes' => 60,
                'focus_area' => '1 Month Transformation',
            ]);

        $response->assertRedirect(route('app.pt.index', ['tab' => 'sessions']));

        $session = PtSession::where('member_id', $member->id)->first();
        $this->assertNotNull($session);
        $this->assertEquals('SCHEDULED', $session->status);

        // Mark completed
        $compRes = $this->actingAs($gym['owner'])
            ->post(route('app.pt-sessions.complete', $session->id));

        $compRes->assertSessionHas('success');
        $this->assertEquals('COMPLETED', $session->fresh()->status);
        $this->assertNotNull($session->fresh()->completed_at);
    }

    public function test_can_view_trainers_management_index(): void
    {
        $gym = $this->setupGym();

        $trainer = Trainer::create([
            'tenant_id' => $gym['tenant']->id,
            'branch_id' => $gym['branch']->id,
            'first_name' => 'Aditya',
            'last_name' => 'Sharma',
            'phone' => '9876543210',
            'specialization' => 'Strength & Conditioning',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($gym['owner'])
            ->get(route('app.trainers.index'));

        $response->assertStatus(200);
        $response->assertSee('Fitness Trainers');
        $response->assertSee('Aditya Sharma');
        $response->assertSee('Total Coaches');
    }
}
