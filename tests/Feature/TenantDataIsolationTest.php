<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DietPlan;
use App\Models\GymClass;
use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantDataIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $ownerA;

    protected User $ownerB;

    protected $tenantA;

    protected $tenantB;

    protected Branch $branchA;

    protected Branch $branchB;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::where('slug', 'pro')->first() ?? Plan::first();
        $tenantService = app(TenantService::class);

        // Setup Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Alpha Gym {$randA}",
            'email' => "alpha_{$randA}@gym.com",
            'owner_name' => 'Alpha Owner',
            'password' => 'password',
        ], $plan);
        $this->tenantA = $regA['tenant'];
        $this->ownerA = $regA['owner'];
        $this->branchA = $regA['branch'];

        // Setup Tenant B
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Beta Gym {$randB}",
            'email' => "beta_{$randB}@gym.com",
            'owner_name' => 'Beta Owner',
            'password' => 'password',
        ], $plan);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['owner'];
        $this->branchB = $regB['branch'];
    }

    public function test_tenant_b_cannot_see_or_access_tenant_a_members(): void
    {
        // Create member under Tenant A
        $memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => 'ALPHA-001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '9876543210',
            'email' => 'john.doe@alphagym.com',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Tenant B visits members listing
        $response = $this->actingAs($this->ownerB)->get(route('app.members.index'));
        $response->assertOk();
        $response->assertDontSee('ALPHA-001');
        $response->assertDontSee('john.doe@alphagym.com');

        // Tenant B attempts direct access to Tenant A's member details
        $showResponse = $this->actingAs($this->ownerB)->get(route('app.members.show', $memberA->id));
        $showResponse->assertNotFound();

        // Tenant B attempts to update Tenant A's member
        $updateResponse = $this->actingAs($this->ownerB)->post(route('app.members.update', $memberA->id), [
            'first_name' => 'Hacked Name',
            'phone' => '1111111111',
        ]);
        $updateResponse->assertNotFound();
        $this->assertEquals('John', $memberA->fresh()->first_name);

        // Tenant B attempts to delete Tenant A's member
        $deleteResponse = $this->actingAs($this->ownerB)->delete(route('app.members.delete', $memberA->id));
        $deleteResponse->assertNotFound();
        $this->assertNull($memberA->fresh()->deleted_at);
    }

    public function test_tenant_b_cannot_see_or_modify_tenant_a_crm_leads(): void
    {
        // Create lead under Tenant A
        $leadA = Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Alpha Secret Lead',
            'phone' => '9988776655',
            'email' => 'secretlead@alpha.com',
            'source' => 'facebook',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        // Tenant B visits CRM Leads
        $response = $this->actingAs($this->ownerB)->get(route('app.leads.index'));
        $response->assertOk();
        $response->assertDontSee('Alpha Secret Lead');
        $response->assertDontSee('secretlead@alpha.com');

        // Tenant B attempts to update lead stage
        $stageResponse = $this->actingAs($this->ownerB)->post(route('app.leads.stage', $leadA->id), [
            'stage' => 'LOST',
        ]);
        $stageResponse->assertNotFound();
        $this->assertEquals('NEW_LEAD', $leadA->fresh()->stage);

        // Tenant B attempts to delete Tenant A's lead
        $deleteResponse = $this->actingAs($this->ownerB)->delete(route('app.leads.delete', $leadA->id));
        $deleteResponse->assertNotFound();
        $this->assertDatabaseHas('leads', ['id' => $leadA->id]);
    }

    public function test_tenant_b_cannot_see_or_modify_tenant_a_gym_services_and_bookings(): void
    {
        $serviceA = GymService::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha Private Sauna VIP',
            'amount' => 1500.00,
            'duration_minutes' => 45,
            'status' => 'active',
            'is_visible_in_portal' => true,
        ]);

        $memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => 'ALPHA-SVC-01',
            'first_name' => 'Sauna',
            'last_name' => 'VIP',
            'phone' => '9876543211',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $bookingA = GymServiceBooking::create([
            'tenant_id' => $this->tenantA->id,
            'member_id' => $memberA->id,
            'gym_service_id' => $serviceA->id,
            'booking_date' => now()->toDateString(),
            'amount_paid' => 1500.00,
            'total_sessions' => 5,
            'sessions_left' => 5,
            'status' => 'active',
        ]);

        // Tenant B visits Services page
        $response = $this->actingAs($this->ownerB)->get(route('app.services.index'));
        $response->assertOk();
        $response->assertDontSee('Alpha Private Sauna VIP');

        // Tenant B attempts to update Tenant A's service
        $updateResponse = $this->actingAs($this->ownerB)->post(route('app.services.update', $serviceA->id), [
            'name' => 'Hacked Service',
            'amount' => 1.00,
        ]);
        $updateResponse->assertNotFound();
        $this->assertEquals('Alpha Private Sauna VIP', $serviceA->fresh()->name);

        // Tenant B attempts to deduct session from Tenant A's booking
        $deductResponse = $this->actingAs($this->ownerB)->post(route('app.services.bookings.deduct', $bookingA->id));
        $deductResponse->assertNotFound();
        $this->assertEquals(5, $bookingA->fresh()->sessions_left);

        // Tenant B attempts to delete Tenant A's service
        $deleteResponse = $this->actingAs($this->ownerB)->delete(route('app.services.delete', $serviceA->id));
        $deleteResponse->assertNotFound();
        $this->assertDatabaseHas('gym_services', ['id' => $serviceA->id]);
    }

    public function test_tenant_b_cannot_see_tenant_a_payments_and_invoices(): void
    {
        $mPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha Gold Annual',
            'duration_type' => 'months',
            'duration_value' => 12,
            'price' => 12000.00,
        ]);

        $memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => 'ALPHA-PAY-01',
            'first_name' => 'Rich',
            'last_name' => 'Member',
            'phone' => '9876543212',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membershipA = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $memberA->id,
            'membership_plan_id' => $mPlanA->id,
            'price' => 12000.00,
            'final_amount' => 12000.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'ACTIVE',
            'paid_amount' => 12000.00,
        ]);

        $paymentA = MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'member_id' => $memberA->id,
            'membership_id' => $membershipA->id,
            'amount' => 12000.00,
            'payment_method' => 'cash',
            'status' => 'PAID',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-ALPHA-9999',
        ]);

        // Tenant B visits Payments index
        $response = $this->actingAs($this->ownerB)->get(route('app.payments.index'));
        $response->assertOk();
        $response->assertDontSee('INV-ALPHA-9999');

        // Tenant B attempts to view Tenant A's invoice directly
        $invoiceResponse = $this->actingAs($this->ownerB)->get(route('app.invoices.show', $paymentA->id));
        $invoiceResponse->assertNotFound();
    }

    public function test_tenant_b_cannot_see_tenant_a_workouts_diets_trainers_and_classes(): void
    {
        $trainerA = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'first_name' => 'Master Trainer',
            'last_name' => 'Bob',
            'phone' => '9876543213',
            'specialization' => 'Powerlifting',
            'status' => 'ACTIVE',
        ]);

        $classA = GymClass::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Alpha Extreme CrossFit',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $dietA = DietPlan::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Alpha 4000kcal Beast Diet',
            'daily_calories' => 4000,
            'protein_grams' => 250,
            'carbs_grams' => 400,
            'fat_grams' => 100,
            'is_template' => true,
        ]);

        $workoutA = WorkoutPlan::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Alpha Olympia Workout Routine',
            'days_per_week' => 6,
            'level' => 'advanced',
            'is_template' => true,
        ]);

        // Tenant B views Trainers
        $tResponse = $this->actingAs($this->ownerB)->get(route('app.trainers.index'));
        $tResponse->assertOk();
        $tResponse->assertDontSee('Master Trainer Bob');

        // Tenant B views Classes
        $cResponse = $this->actingAs($this->ownerB)->get(route('app.classes.index'));
        $cResponse->assertOk();
        $cResponse->assertDontSee('Alpha Extreme CrossFit');

        // Tenant B views Diets
        $dResponse = $this->actingAs($this->ownerB)->get(route('app.diets.index'));
        $dResponse->assertOk();
        $dResponse->assertDontSee('Alpha 4000kcal Beast Diet');

        // Tenant B views Workouts
        $wResponse = $this->actingAs($this->ownerB)->get(route('app.workouts.index'));
        $wResponse->assertOk();
        $wResponse->assertDontSee('Alpha Olympia Workout Routine');
    }

    public function test_api_v1_endpoints_enforce_strict_tenant_data_isolation(): void
    {
        $memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => 'ALPHA-API-01',
            'first_name' => 'API Alpha',
            'last_name' => 'User',
            'phone' => '9876543214',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $tokenB = $this->ownerB->createToken('test_token_b')->plainTextToken;

        // Tenant B calls API /api/v1/members
        $apiListResponse = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson('/api/v1/members');
        $apiListResponse->assertOk();
        $apiListResponse->assertJsonMissing(['member_code' => 'ALPHA-API-01']);

        // Tenant B calls API /api/v1/members/{id} for Tenant A's member
        $apiShowResponse = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/v1/members/{$memberA->id}");
        $apiShowResponse->assertNotFound();
    }
}
