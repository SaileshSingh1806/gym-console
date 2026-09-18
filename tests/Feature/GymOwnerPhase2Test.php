<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\DietPlan;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\GymEquipment;
use App\Models\GymService;
use App\Models\GymServiceBooking;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GymOwnerPhase2Test extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;

    protected Branch $branchA;

    protected User $ownerA;

    protected Tenant $tenantB;

    protected Branch $branchB;

    protected User $ownerB;

    protected Plan $unlimitedPlan;

    protected MembershipPlan $membershipPlanA;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(PermissionSeeder::class);

        // Get an unlimited pro plan
        $this->unlimitedPlan = Plan::where('slug', 'pro')->first() ?? Plan::create([
            'name' => 'Pro Unlimited',
            'slug' => 'pro',
            'price_monthly' => 4999,
            'price_yearly' => 49990,
            'trial_days' => 14,
            'member_limit' => -1,
            'branch_limit' => 10,
            'staff_limit' => 50,
            'is_active' => true,
        ]);

        // Setup Test Gym A
        $this->tenantA = Tenant::create([
            'name' => 'Test Gym A',
            'slug' => 'test-gym-a',
            'email' => 'contact@testgyma.com',
            'phone' => '9876543210',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'status' => 'ACTIVE',
        ]);

        $this->branchA = $this->tenantA->branches()->create([
            'name' => 'Main Branch A',
            'code' => 'MAIN-A',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->ownerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'GymConsole Owner 1',
            'email' => 'gymconsole.owner1@yopmail.com',
            'phone' => '9876543210',
            'password' => Hash::make('GymConsole@123'),
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        // Attach active subscription to Gym A
        TenantContext::setTenant($this->tenantA);
        app(SubscriptionService::class)->activateSubscription(
            $this->tenantA,
            $this->unlimitedPlan,
            'yearly',
            'manual',
            'TXN-A-INIT',
            49990
        );

        // Setup Test Gym B (For Tenant Isolation Verification)
        $this->tenantB = Tenant::create([
            'name' => 'Test Gym B',
            'slug' => 'test-gym-b',
            'email' => 'contact@testgymb.com',
            'phone' => '9123456780',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'status' => 'ACTIVE',
        ]);

        $this->branchB = $this->tenantB->branches()->create([
            'name' => 'Main Branch B',
            'code' => 'MAIN-B',
            'is_main' => true,
            'is_active' => true,
        ]);

        $this->ownerB = User::create([
            'tenant_id' => $this->tenantB->id,
            'name' => 'GymConsole Owner 2',
            'email' => 'gymconsole.owner2@yopmail.com',
            'phone' => '9123456780',
            'password' => Hash::make('GymConsole@123'),
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        app(SubscriptionService::class)->activateSubscription(
            $this->tenantB,
            $this->unlimitedPlan,
            'yearly',
            'manual',
            'TXN-B-INIT',
            49990
        );

        // Setup a default membership plan for Gym A
        $this->membershipPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Gold Annual Membership',
            'description' => 'Full access to gym & classes',
            'price' => 12000,
            'duration_months' => 12,
            'duration_days' => 365,
            'is_active' => true,
        ]);
    }

    private function createTestMember(array $attributes = []): Member
    {
        static $counter = 100;
        $counter++;
        $tenantId = $attributes['tenant_id'] ?? $this->tenantA->id;
        $branchId = $attributes['branch_id'] ?? $this->branchA->id;

        return Member::create(array_merge([
            'tenant_id' => $tenantId,
            'branch_id' => $branchId,
            'member_code' => 'MEM-'.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'first_name' => 'GymConsole',
            'last_name' => 'User '.$counter,
            'email' => "gymconsole.user{$counter}@yopmail.com",
            'phone' => '98765'.str_pad((string) $counter, 5, '0', STR_PAD_LEFT),
            'gender' => 'male',
            'status' => 'ACTIVE',
            'join_date' => now()->toDateString(),
        ], $attributes));
    }

    /**
     * 1. Dashboard & Gym Operations Hub
     */
    public function test_gym_owner_can_access_dashboard_and_view_kpis(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('app.dashboard'));

        $response->assertOk();
        $response->assertViewIs('app.dashboard');
        $response->assertSee('Test Gym A');
        $response->assertSee('ACTIVE');
    }

    /**
     * 2. Member Management System (Full CRUD, Filters, Fee Collection, Freeze, Renew, PT, Measurements)
     */
    public function test_gym_owner_can_create_member_and_enroll_plan(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('app.members.store'), [
            'first_name' => 'GymConsole',
            'last_name' => 'User 1',
            'email' => 'gymconsole.user1@yopmail.com',
            'phone' => '9876540001',
            'gender' => 'male',
            'dob' => '1995-05-15',
            'branch_id' => $this->branchA->id,
            'membership_plan_id' => $this->membershipPlanA->id,
            'start_date' => now()->toDateString(),
            'initial_payment_amount' => 10000, // Partial payment (2000 due)
            'payment_method' => 'cash',
        ]);

        $response->assertRedirect(route('app.members.index'));
        $response->assertSessionHas('success');

        $member = Member::where('email', 'gymconsole.user1@yopmail.com')->first();
        $this->assertNotNull($member);
        $this->assertEquals($this->tenantA->id, $member->tenant_id);
        $this->assertEquals('ACTIVE', $member->status);

        // Verify active membership and payment record created
        $this->assertDatabaseHas('memberships', [
            'member_id' => $member->id,
            'membership_plan_id' => $this->membershipPlanA->id,
            'final_amount' => 12000,
            'paid_amount' => 10000,
        ]);

        $this->assertDatabaseHas('member_payments', [
            'member_id' => $member->id,
            'amount' => 10000,
            'payment_method' => 'cash',
        ]);
    }

    public function test_gym_owner_can_lookup_member_phone(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 2',
            'email' => 'gymconsole.user2@yopmail.com',
            'phone' => '9876540002',
            'gender' => 'female',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.members.lookup-phone', ['phone' => '9876540002']));
        $response->assertOk();
        $response->assertJsonPath('found', true);
        $response->assertJsonPath('member.first_name', 'GymConsole');
    }

    public function test_gym_owner_can_update_member_and_collect_pending_fee(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 3',
            'email' => 'gymconsole.user3@yopmail.com',
            'phone' => '9876540003',
        ]);

        $membership = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $member->id,
            'membership_plan_id' => $this->membershipPlanA->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'price' => 12000,
            'final_amount' => 12000,
            'paid_amount' => 8000,
            'status' => 'ACTIVE',
        ]);

        // 1. Update Member Details
        $updateResponse = $this->actingAs($this->ownerA)->post(route('app.members.update', $member->id), [
            'first_name' => 'GymConsole',
            'last_name' => 'User 3 Updated',
            'email' => 'gymconsole.user3@yopmail.com',
            'phone' => '9876540003',
            'gender' => 'male',
            'status' => 'ACTIVE',
            'branch_id' => $this->branchA->id,
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertEquals('User 3 Updated', $member->fresh()->last_name);

        // 2. Collect Remaining Fee of 4000
        $feeResponse = $this->actingAs($this->ownerA)->post(route('app.members.collect-fee', $member->id), [
            'amount' => 4000,
            'payment_method' => 'upi',
            'payment_date' => now()->toDateString(),
            'notes' => 'Settled remaining annual balance',
        ]);
        $feeResponse->assertSessionHas('success');

        $this->assertEquals(12000, $membership->fresh()->paid_amount);
        $this->assertDatabaseHas('member_payments', [
            'member_id' => $member->id,
            'amount' => 4000,
            'payment_method' => 'upi',
        ]);
    }

    public function test_gym_owner_can_freeze_and_unfreeze_member(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 4',
            'email' => 'gymconsole.user4@yopmail.com',
            'phone' => '9876540004',
        ]);

        // 1. Freeze
        $freezeResponse = $this->actingAs($this->ownerA)->post(route('app.members.freeze', $member->id), [
            'reason' => 'Medical recovery for 2 weeks',
        ]);
        $freezeResponse->assertSessionHas('success');
        $this->assertEquals('SUSPENDED', $member->fresh()->status);

        // 2. Unfreeze
        $unfreezeResponse = $this->actingAs($this->ownerA)->post(route('app.members.freeze', $member->id));
        $unfreezeResponse->assertSessionHas('success');
        $this->assertEquals('ACTIVE', $member->fresh()->status);
    }

    public function test_gym_owner_can_record_member_body_measurements(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 5',
            'email' => 'gymconsole.user5@yopmail.com',
            'phone' => '9876540005',
        ]);

        $response = $this->actingAs($this->ownerA)->post(route('app.members.store-measurement', $member->id), [
            'date' => now()->toDateString(),
            'weight' => 78.5,
            'height' => 178,
            'chest' => 40,
            'waist' => 32,
            'arms' => 15.5,
            'body_fat' => 14.5,
            'notes' => 'Month 1 initial progress measurement',
        ]);

        $response->assertSessionHas('success');
        $this->assertNotEmpty($member->fresh()->metadata['measurements'] ?? []);
    }

    /**
     * 3. Memberships & Plans Catalog CRUD
     */
    public function test_gym_owner_can_manage_membership_plans(): void
    {
        // 1. Create Plan
        $createResponse = $this->actingAs($this->ownerA)->post(route('app.membership-plans.store'), [
            'name' => 'Quarterly Kickstart',
            'description' => '3 Months access',
            'plan_type' => 'single',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price' => 4500,
            'is_active' => 1,
        ]);
        $createResponse->assertSessionHas('success');

        $plan = MembershipPlan::where('name', 'Quarterly Kickstart')->first();
        $this->assertNotNull($plan);

        // 2. Update Plan
        $updateResponse = $this->actingAs($this->ownerA)->post(route('app.membership-plans.update', $plan->id), [
            'name' => 'Quarterly Kickstart Pro',
            'plan_type' => 'single',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price' => 4999,
            'is_active' => 1,
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertEquals(4999, $plan->fresh()->price);

        // 3. Delete Plan
        $deleteResponse = $this->actingAs($this->ownerA)->delete(route('app.membership-plans.delete', $plan->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('membership_plans', ['id' => $plan->id]);
    }

    /**
     * 4. Payments & POS Billing System
     */
    public function test_gym_owner_can_record_standalone_pos_payment_and_reverse(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 6',
            'email' => 'gymconsole.user6@yopmail.com',
            'phone' => '9876540006',
        ]);

        // 1. Record POS Product / Supplement Payment
        $payResponse = $this->actingAs($this->ownerA)->post(route('app.payments.store'), [
            'member_id' => $member->id,
            'branch_id' => $this->branchA->id,
            'amount' => 3500,
            'collected_amount' => 3500,
            'payment_method' => 'card',
            'payment_date' => now()->toDateString(),
            'notes' => 'Whey Protein 2kg purchase',
        ]);
        $payResponse->assertSessionHas('success');

        $payment = MemberPayment::where('member_id', $member->id)->first();
        $this->assertNotNull($payment);

        // 2. View Invoice
        $invoiceResponse = $this->actingAs($this->ownerA)->get(route('app.invoices.show', $payment->id));
        $invoiceResponse->assertOk();

        // 3. Reverse Payment
        $reverseResponse = $this->actingAs($this->ownerA)->post(route('app.payments.reverse', $payment->id), [
            'reason' => 'Customer requested item return',
        ]);
        $reverseResponse->assertSessionHas('success');
        $this->assertStringContainsString('[REVERSED]', $payment->fresh()->notes);
    }

    /**
     * 5. Attendance & Check-in System
     */
    public function test_gym_owner_can_manage_attendance_checkin_and_checkout(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 7',
            'email' => 'gymconsole.user7@yopmail.com',
            'phone' => '9876540007',
        ]);

        // 1. Manual Reception Check-in
        $checkinResponse = $this->actingAs($this->ownerA)->post(route('app.attendance.store'), [
            'member_id' => $member->id,
            'branch_id' => $this->branchA->id,
            'method' => 'manual',
        ]);
        $checkinResponse->assertSessionHas('success');

        $attendance = Attendance::where('member_id', $member->id)->where('date', now()->toDateString())->first();
        $this->assertNotNull($attendance);
        $this->assertNull($attendance->check_out);

        // 2. Checkout
        $checkoutResponse = $this->actingAs($this->ownerA)->post(route('app.attendance.checkout', $attendance->id));
        $checkoutResponse->assertSessionHas('success');
        $this->assertNotNull($attendance->fresh()->check_out);
    }

    /**
     * 6. Personal Training (PT Plans, Package Assignment & Session Logging)
     */
    public function test_gym_owner_can_manage_personal_training(): void
    {
        $trainer = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'first_name' => 'GymConsole',
            'last_name' => 'Trainer 1',
            'phone' => '9988771101',
            'email' => 'gymconsole.trainer1@yopmail.com',
            'specialization' => 'Bodybuilding & Hypertrophy',
            'status' => 'ACTIVE',
        ]);

        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 8',
            'email' => 'gymconsole.user8@yopmail.com',
            'phone' => '9876540008',
        ]);

        // 1. Create PT Plan
        $ptPlanResponse = $this->actingAs($this->ownerA)->post(route('app.pt-plans.store'), [
            'name' => '12-Session PT Mastery',
            'total_sessions' => 12,
            'default_price' => 15000,
            'validity_days' => 60,
        ]);
        $ptPlanResponse->assertSessionHas('success');
        $ptPlan = PtPlan::where('name', '12-Session PT Mastery')->first();

        // 2. Assign PT Package to Member
        $assignResponse = $this->actingAs($this->ownerA)->post(route('app.pt-packages.store'), [
            'member_id' => $member->id,
            'trainer_id' => $trainer->id,
            'pt_plan_id' => $ptPlan->id,
            'package_name' => '12-Session PT Mastery',
            'total_sessions' => 12,
            'validity_days' => 60,
            'start_date' => now()->toDateString(),
            'price' => 15000,
            'paid_amount' => 15000,
            'payment_method' => 'upi',
        ]);
        $assignResponse->assertSessionHas('success');

        $package = MemberPtPackage::where('member_id', $member->id)->first();
        $this->assertNotNull($package);
        $this->assertEquals(12, $package->remaining_sessions);

        // 3. Log a Completed PT Session
        $logSessionResponse = $this->actingAs($this->ownerA)->post(route('app.pt-packages.log-session', $package->id), [
            'count' => 1,
        ]);
        $logSessionResponse->assertSessionHas('success');

        // Sessions remaining should decrement from 12 to 11
        $this->assertEquals(11, $package->fresh()->remaining_sessions);
        $this->assertDatabaseHas('pt_sessions', [
            'member_pt_package_id' => $package->id,
            'status' => 'COMPLETED',
        ]);
    }

    /**
     * 7. Trainers Management System
     */
    public function test_gym_owner_can_manage_trainers(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('app.trainers.store'), [
            'first_name' => 'GymConsole',
            'last_name' => 'Trainer 2',
            'phone' => '9988771102',
            'email' => 'gymconsole.trainer2@yopmail.com',
            'specialization' => 'Yoga & Flexibility',
            'branch_id' => $this->branchA->id,
            'salary' => 25000,
            'status' => 'ACTIVE',
        ]);

        $response->assertSessionHas('success');
        $trainer = Trainer::where('email', 'gymconsole.trainer2@yopmail.com')->first();
        $this->assertNotNull($trainer);
        $this->assertEquals('ACTIVE', $trainer->status);
    }

    /**
     * 8. Group Classes & Scheduling
     */
    public function test_gym_owner_can_manage_group_classes_and_bookings(): void
    {
        $trainer = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'first_name' => 'Coach',
            'last_name' => 'Sam',
            'phone' => '9988771199',
            'status' => 'ACTIVE',
        ]);

        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 9',
            'email' => 'gymconsole.user9@yopmail.com',
            'phone' => '9876540009',
        ]);

        // 1. Create Class
        $classResponse = $this->actingAs($this->ownerA)->post(route('app.classes.store'), [
            'name' => 'Morning HIIT Blast',
            'class_type' => 'Group Fitness',
            'trainer_id' => $trainer->id,
            'branch_id' => $this->branchA->id,
            'capacity' => 25,
            'duration_minutes' => 45,
            'schedules' => [
                ['day_of_week' => 'monday', 'start_time' => '06:30', 'end_time' => '07:15'],
            ],
        ]);
        $classResponse->assertSessionHas('success');

        $class = GymClass::where('name', 'Morning HIIT Blast')->first();
        $this->assertNotNull($class);

        $schedule = ClassSchedule::where('gym_class_id', $class->id)->first();
        $this->assertNotNull($schedule);

        // 2. Book Member into Class Schedule
        $bookResponse = $this->actingAs($this->ownerA)->post(route('app.classes.book', $schedule->id), [
            'member_id' => $member->id,
            'booking_date' => now()->next(1)->toDateString(),
        ]);
        $bookResponse->assertSessionHas('success');

        $this->assertDatabaseHas('class_bookings', [
            'member_id' => $member->id,
            'class_schedule_id' => $schedule->id,
            'status' => 'booked',
        ]);
    }

    /**
     * 9. Workouts Management
     */
    public function test_gym_owner_can_create_and_delete_workout_plans(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('app.workouts.store'), [
            'title' => 'Hypertrophy Upper Body',
            'level' => 'intermediate',
            'goal' => 'muscle_gain',
            'notes' => 'Targeted upper body push pull protocol',
            'exercises' => [
                ['exercise_name' => 'Incline DB Press', 'day' => 'day_1', 'sets' => 4, 'reps' => '10', 'rest_seconds' => 90],
                ['exercise_name' => 'Barbell Row', 'day' => 'day_1', 'sets' => 4, 'reps' => '12', 'rest_seconds' => 90],
            ],
        ]);

        $response->assertSessionHas('success');
        $workout = WorkoutPlan::where('title', 'Hypertrophy Upper Body')->first();
        $this->assertNotNull($workout);

        $deleteResponse = $this->actingAs($this->ownerA)->delete(route('app.workouts.delete', $workout->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('workout_plans', ['id' => $workout->id]);
    }

    /**
     * 10. Diets & Nutrition System
     */
    public function test_gym_owner_can_seed_and_create_diet_plans(): void
    {
        // 1. Seed Starter Diets
        $seedResponse = $this->actingAs($this->ownerA)->post(route('app.diets.seed'));
        $seedResponse->assertSessionHas('success');
        $this->assertGreaterThan(0, DietPlan::where('tenant_id', $this->tenantA->id)->count());

        // 2. Create Custom Diet Plan
        $dietResponse = $this->actingAs($this->ownerA)->post(route('app.diets.store'), [
            'title' => 'High Protein Shred Diet',
            'daily_calories' => 2400,
            'protein_grams' => 180,
            'carbs_grams' => 220,
            'fat_grams' => 60,
            'meals' => [
                ['meal_type' => 'breakfast', 'meal_name' => 'Oatmeal & 4 Egg Whites', 'items_description' => 'Oatmeal, 4 boiled egg whites, banana', 'calories' => 500],
            ],
        ]);
        $dietResponse->assertSessionHas('success');
        $this->assertDatabaseHas('diet_plans', ['title' => 'High Protein Shred Diet']);
    }

    /**
     * 11. Gym Services & Extra Bookings
     */
    public function test_gym_owner_can_manage_services_and_session_bookings(): void
    {
        $member = $this->createTestMember([
            'first_name' => 'GymConsole',
            'last_name' => 'User 10',
            'email' => 'gymconsole.user10@yopmail.com',
            'phone' => '9876540010',
        ]);

        // 1. Create Service
        $serviceResponse = $this->actingAs($this->ownerA)->post(route('app.services.store'), [
            'name' => 'Cryotherapy Ice Bath Recovery',
            'amount' => 1500,
            'is_session_countable' => 1,
            'session_count' => 5,
            'branch_id' => $this->branchA->id,
        ]);
        $serviceResponse->assertSessionHas('success');

        $service = GymService::where('name', 'Cryotherapy Ice Bath Recovery')->first();
        $this->assertNotNull($service);

        // 2. Book Service Session
        $bookResponse = $this->actingAs($this->ownerA)->post(route('app.services.bookings.store'), [
            'member_id' => $member->id,
            'gym_service_id' => $service->id,
            'amount_paid' => 7500,
        ]);
        $bookResponse->assertSessionHas('success');

        $booking = GymServiceBooking::where('member_id', $member->id)->first();
        $this->assertNotNull($booking);
        $this->assertEquals(5, $booking->sessions_left);

        // 3. Deduct a Session
        $deductResponse = $this->actingAs($this->ownerA)->post(route('app.services.bookings.deduct', $booking->id));
        $deductResponse->assertSessionHas('success');
        $this->assertEquals(4, $booking->fresh()->sessions_left);
    }

    /**
     * 12. CRM, Leads, Trials & Enquiries
     */
    public function test_gym_owner_can_manage_crm_leads_trials_and_enquiries(): void
    {
        // 1. Create Lead
        $leadResponse = $this->actingAs($this->ownerA)->post(route('app.leads.store'), [
            'name' => 'Alex Prospect',
            'phone' => '9876599999',
            'email' => 'alex.prospect@yopmail.com',
            'branch_id' => $this->branchA->id,
            'stage' => 'NEW_LEAD',
            'source' => 'instagram',
            'notes' => 'Inquired about annual transformation packages',
        ]);
        $leadResponse->assertSessionHas('success');

        $lead = Lead::where('phone', '9876599999')->first();
        $this->assertNotNull($lead);

        // 2. Update Lead Stage
        $stageResponse = $this->actingAs($this->ownerA)->post(route('app.leads.stage', $lead->id), [
            'stage' => 'TRIAL_SCHEDULED',
        ]);
        $stageResponse->assertSessionHas('success');
        $this->assertEquals('TRIAL_SCHEDULED', $lead->fresh()->stage);

        // 3. Book Free Trial Visit
        $trialResponse = $this->actingAs($this->ownerA)->post(route('app.crm.trials.store'), [
            'lead_id' => $lead->id,
            'trial_date' => now()->addDay()->toDateString(),
            'notes' => 'Leg day trial scheduled at 6 PM',
        ]);
        $trialResponse->assertSessionHas('success');
        $this->assertDatabaseHas('lead_trials', ['lead_id' => $lead->id]);

        // 4. Record Quick Enquiry
        $enquiryResponse = $this->actingAs($this->ownerA)->post(route('app.crm.enquiries.store'), [
            'name' => 'Walkin Guest',
            'phone' => '9888877777',
            'branch_id' => $this->branchA->id,
            'interest' => 'Zumba classes',
        ]);
        $enquiryResponse->assertSessionHas('success');
    }

    /**
     * 13. Reports & Financial Balance Sheet
     */
    public function test_gym_owner_can_view_finance_reports_and_record_expenses(): void
    {
        // 1. Create Expense Category & Expense
        $catResponse = $this->actingAs($this->ownerA)->post(route('app.expenses.categories.store'), [
            'name' => 'Utilities & Electricity',
        ]);
        $catResponse->assertSessionHas('success');
        $cat = ExpenseCategory::where('name', 'Utilities & Electricity')->first();

        $expResponse = $this->actingAs($this->ownerA)->post(route('app.expenses.store'), [
            'title' => 'Gym Floor Electricity Bill',
            'amount' => 15000,
            'expense_category_id' => $cat->id,
            'branch_id' => $this->branchA->id,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);
        $expResponse->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', ['title' => 'Gym Floor Electricity Bill', 'amount' => 15000]);

        // 2. View Financial Balance Sheet
        $balanceSheetResponse = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet'));
        $balanceSheetResponse->assertOk();

        // 3. View Member Financial Report
        $memberReportResponse = $this->actingAs($this->ownerA)->get(route('app.finance.member-report'));
        $memberReportResponse->assertOk();
    }

    /**
     * 14. Inventory & Equipment Maintenance
     */
    public function test_gym_owner_can_manage_inventory_stock_and_equipment_maintenance(): void
    {
        // 1. Create Inventory Item
        $itemResponse = $this->actingAs($this->ownerA)->post(route('app.inventory.items.store'), [
            'name' => 'Gym Console Shaker Bottle',
            'sku' => 'SHK-700ML',
            'category' => 'Apparel',
            'cost_price' => 200,
            'selling_price' => 450,
            'stock_quantity' => 50,
            'reorder_threshold' => 10,
            'branch_id' => $this->branchA->id,
        ]);
        $itemResponse->assertSessionHas('success');
        $item = InventoryItem::where('sku', 'SHK-700ML')->first();

        // 2. Adjust Stock
        $adjustResponse = $this->actingAs($this->ownerA)->post(route('app.inventory.items.adjust', $item->id), [
            'type' => 'OUT',
            'quantity' => 5,
            'notes' => 'Sold 5 bottles at reception',
        ]);
        $adjustResponse->assertSessionHas('success');
        $this->assertEquals(45, $item->fresh()->stock_quantity);

        // 3. Create Gym Equipment Machine
        $equipResponse = $this->actingAs($this->ownerA)->post(route('app.inventory.equipment.store'), [
            'name' => 'Commercial Treadmill Pro T9',
            'serial_number' => 'TM-2026-009',
            'category' => 'Cardio',
            'maintenance_interval_days' => 90,
            'branch_id' => $this->branchA->id,
            'status' => 'OPERATIONAL',
        ]);
        $equipResponse->assertSessionHas('success');
        $equip = GymEquipment::where('serial_number', 'TM-2026-009')->first();

        // 4. Log Equipment Maintenance Service
        $maintResponse = $this->actingAs($this->ownerA)->post(route('app.inventory.equipment.maintenance.store', $equip->id), [
            'maintenance_type' => 'Routine Service',
            'service_date' => now()->toDateString(),
            'cost' => 2500,
            'technician_name' => 'Technician Raj',
            'status_after_service' => 'OPERATIONAL',
            'work_summary' => 'Motor belt replacement and belt lubrication',
        ]);
        $maintResponse->assertSessionHas('success');
        $this->assertDatabaseHas('equipment_maintenance_logs', ['gym_equipment_id' => $equip->id, 'cost' => 2500]);
    }

    /**
     * 15. Biometric Devices
     */
    public function test_gym_owner_can_manage_biometric_devices(): void
    {
        $response = $this->actingAs($this->ownerA)->post(route('app.devices.store'), [
            'name' => 'Turnstile Entry Face Scanner',
            'type' => 'hikvision_facial',
            'direction' => 'in',
            'ip_address' => '192.168.1.150',
            'port' => 8000,
            'username' => 'admin',
            'password' => 'Hikvision@123',
            'branch_id' => $this->branchA->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('devices', ['name' => 'Turnstile Entry Face Scanner', 'ip_address' => '192.168.1.150']);
    }

    /**
     * 16. Staff & Role-Based Permissions
     */
    public function test_gym_owner_can_manage_staff_and_permission_matrix(): void
    {
        // 1. Create Staff User
        $staffResponse = $this->actingAs($this->ownerA)->post(route('app.staff.store'), [
            'name' => 'GymConsole Staff 2',
            'email' => 'gymconsole.staff2@yopmail.com',
            'phone' => '9988772202',
            'role' => 'receptionist',
            'status' => 'ACTIVE',
            'branch_id' => $this->branchA->id,
            'password' => 'GymConsole@123',
        ]);
        $staffResponse->assertSessionHas('success');
        $staff = User::where('email', 'gymconsole.staff2@yopmail.com')->first();
        $this->assertNotNull($staff);

        // 2. Toggle Status
        $toggleResponse = $this->actingAs($this->ownerA)->post(route('app.staff.toggle-status', $staff->id));
        $toggleResponse->assertSessionHas('success');
        $this->assertEquals('SUSPENDED', $staff->fresh()->status);

        // 3. Reset Staff Password
        $resetResponse = $this->actingAs($this->ownerA)->post(route('app.staff.reset-password', $staff->id), [
            'password' => 'GymConsole@New123',
        ]);
        $resetResponse->assertSessionHas('success');
    }

    /**
     * 17. Multi-Branch Operations & Switching
     */
    public function test_gym_owner_can_create_and_switch_branches(): void
    {
        $branchResponse = $this->actingAs($this->ownerA)->post(route('app.branches.store'), [
            'name' => 'Downtown Express Branch',
            'code' => 'DWN-EXP',
            'city' => 'Mumbai',
            'phone' => '9876500000',
        ]);
        $branchResponse->assertSessionHas('success');

        $newBranch = Branch::where('code', 'DWN-EXP')->first();
        $this->assertNotNull($newBranch);

        // Switch active session branch
        $switchResponse = $this->actingAs($this->ownerA)->post(route('app.branches.switch', $newBranch->id));
        $switchResponse->assertRedirect();
        $this->assertEquals($newBranch->id, session('active_branch_id'));
    }

    /**
     * 18. Support Tickets Helpdesk
     */
    public function test_gym_owner_can_create_and_close_support_tickets(): void
    {
        $createResponse = $this->actingAs($this->ownerA)->post(route('app.support.store'), [
            'subject' => 'Inquiry regarding WhatsApp automated reminder integration',
            'category' => 'feature_request',
            'priority' => 'medium',
            'message' => 'Is WhatsApp API automation available for due fee alerts?',
        ]);
        $createResponse->assertSessionHas('success');

        $ticket = SupportTicket::where('tenant_id', $this->tenantA->id)->first();
        $this->assertNotNull($ticket);

        // Close ticket
        $closeResponse = $this->actingAs($this->ownerA)->post(route('app.support.close', $ticket->id));
        $closeResponse->assertSessionHas('success');
        $this->assertEquals('closed', $ticket->fresh()->status);
    }

    /**
     * 19. Tenant Isolation Guard Verification (Crucial)
     */
    public function test_gym_owner_cannot_access_or_modify_other_tenant_data(): void
    {
        // Create confidential member in Gym B
        $memberB = $this->createTestMember([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'first_name' => 'Confidential',
            'last_name' => 'Member B',
            'email' => 'confidential.b@yopmail.com',
            'phone' => '9111111111',
        ]);

        // 1. Gym Owner A cannot view Member B profile
        $viewResponse = $this->actingAs($this->ownerA)->get(route('app.members.show', $memberB->id));
        $viewResponse->assertNotFound();

        // 2. Gym Owner A cannot update Member B
        $updateResponse = $this->actingAs($this->ownerA)->post(route('app.members.update', $memberB->id), [
            'first_name' => 'Hacked Name',
        ]);
        $updateResponse->assertNotFound();
        $this->assertEquals('Confidential', $memberB->fresh()->first_name);

        // 3. Gym Owner A cannot delete Member B
        $deleteResponse = $this->actingAs($this->ownerA)->delete(route('app.members.delete', $memberB->id));
        $deleteResponse->assertNotFound();
        $this->assertDatabaseHas('members', ['id' => $memberB->id]);

        // 4. Gym Owner A cannot switch to Gym B's branch
        $switchBranchResponse = $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchB->id));
        $this->assertNotEquals($this->branchB->id, session('active_branch_id'));
    }
}
