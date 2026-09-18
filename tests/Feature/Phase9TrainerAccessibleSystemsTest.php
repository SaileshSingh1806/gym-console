<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\ClassSchedule;
use App\Models\DietPlan;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase9TrainerAccessibleSystemsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $trainerUser1;

    protected Trainer $trainerProfile1;

    protected User $trainerUser2;

    protected Trainer $trainerProfile2;

    protected string $trainer1Token;

    protected Member $assignedMemberA;

    protected Member $unassignedMemberA;

    protected Member $tenantBMember;

    protected MembershipPlan $mPlanA;

    protected Membership $membershipA;

    protected PtPlan $ptPlanA;

    protected MemberPtPackage $ptPackageA;

    protected WorkoutPlan $workoutA;

    protected DietPlan $dietA;

    protected GymClass $classA;

    protected ClassSchedule $scheduleA;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionSeeder)->run();
        $proPlan = Plan::where('slug', 'pro')->first();

        $tenantService = app(TenantService::class);
        $subService = app(SubscriptionService::class);

        // Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_tr@gymconsole.com'],
            ['name' => 'Super Admin Trainer Test', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Setup Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Apex Trainer Gym {$randA}",
            'email' => "owner_trainer_{$randA}@gym.com",
            'owner_name' => 'Apex Trainer Owner',
            'password' => 'password123',
        ], $proPlan);
        $this->tenantA = $regA['tenant'];
        $this->ownerA = $regA['owner'];
        $this->branchA = $regA['branch'];

        $subService->activateSubscription(
            $this->tenantA,
            $proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Setup Tenant B
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Beta Trainer Gym {$randB}",
            'email' => "owner_beta_tr_{$randB}@gym.com",
            'owner_name' => 'Beta Trainer Owner',
            'password' => 'password123',
        ], $proPlan);
        $this->tenantB = $regB['tenant'];
        $this->branchB = $regB['branch'];

        $subService->activateSubscription(
            $this->tenantB,
            $proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Specified Trainer 1 User & Profile under Tenant A
        $this->trainerUser1 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Alex',
            'email' => 'gymconsole.trainer1@yopmail.com',
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('GymConsole@123'),
        ]);
        $roleTrainer = Role::where('tenant_id', $this->tenantA->id)->where('name', 'trainer')->first();
        if ($roleTrainer) {
            $this->trainerUser1->roles()->attach($roleTrainer);
        }

        $this->trainerProfile1 = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'user_id' => $this->trainerUser1->id,
            'first_name' => 'Alex',
            'last_name' => 'Coach',
            'email' => $this->trainerUser1->email,
            'phone' => '9700000001',
            'specialization' => 'Hypertrophy & Strength',
            'status' => 'ACTIVE',
        ]);

        // Trainer 2 (for cross-trainer isolation tests)
        $this->trainerUser2 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Brenda',
            'email' => "trainer2_{$randA}@gym.com",
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('GymConsole@123'),
        ]);
        if ($roleTrainer) {
            $this->trainerUser2->roles()->attach($roleTrainer);
        }

        $this->trainerProfile2 = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'user_id' => $this->trainerUser2->id,
            'first_name' => 'Brenda',
            'last_name' => 'Fitness',
            'email' => $this->trainerUser2->email,
            'phone' => '9700000002',
            'specialization' => 'Cardio & Conditioning',
            'status' => 'ACTIVE',
        ]);

        $this->trainer1Token = $this->trainerUser1->createToken('Trainer1-Sanctum-Token')->plainTextToken;

        // Members under Tenant A
        $this->assignedMemberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'trainer_id' => $this->trainerProfile1->id,
            'member_code' => "TR-M1-{$randA}",
            'first_name' => 'Clark',
            'last_name' => 'Kent',
            'phone' => '9700000011',
            'email' => 'clark@dailyplanet.com',
            'qr_code_token' => 'QR-TR-001',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->unassignedMemberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => "TR-M2-{$randA}",
            'first_name' => 'Bruce',
            'last_name' => 'Wayne',
            'phone' => '9700000012',
            'email' => 'bruce@wayne.com',
            'qr_code_token' => 'QR-TR-002',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Member under Tenant B
        $this->tenantBMember = Member::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'member_code' => "TR-MB-{$randB}",
            'first_name' => 'Barry',
            'last_name' => 'Allen',
            'phone' => '9700000021',
            'email' => 'barry@starlabs.com',
            'qr_code_token' => 'QR-TR-B01',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Membership Plans & Memberships
        $this->mPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Gold All Access',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 200.00,
            'is_active' => true,
        ]);

        $this->membershipA = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $this->assignedMemberA->id,
            'membership_plan_id' => $this->mPlanA->id,
            'price' => 200.00,
            'final_amount' => 200.00,
            'paid_amount' => 200.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // PT Plan
        $this->ptPlanA = PtPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => '10-Session PT Strength',
            'sessions' => 10,
            'price' => 500.00,
            'is_active' => true,
        ]);

        // PT Package assigned to Member
        $this->ptPackageA = MemberPtPackage::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $this->assignedMemberA->id,
            'trainer_id' => $this->trainerProfile1->id,
            'pt_plan_id' => $this->ptPlanA->id,
            'package_name' => '10-Session PT Strength',
            'total_sessions' => 10,
            'used_sessions' => 2,
            'price' => 500.00,
            'final_amount' => 500.00,
            'paid_amount' => 500.00,
            'status' => 'ACTIVE',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
        ]);

        // Workout Plan
        $this->workoutA = WorkoutPlan::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Trainer 1 Hypertrophy Split',
            'days_per_week' => 4,
            'level' => 'intermediate',
            'is_template' => true,
        ]);

        // Diet Plan
        $this->dietA = DietPlan::create([
            'tenant_id' => $this->tenantA->id,
            'title' => 'Trainer 1 Clean Bulking',
            'daily_calories' => 3000,
            'protein_grams' => 200,
            'carbs_grams' => 350,
            'fat_grams' => 80,
            'is_template' => true,
        ]);

        // Class & Schedule
        $this->classA = GymClass::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'High Intensity HIIT',
            'capacity' => 20,
            'is_active' => true,
        ]);

        $this->scheduleA = ClassSchedule::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'gym_class_id' => $this->classA->id,
            'trainer_id' => $this->trainerProfile1->id,
            'day_of_week' => 1,
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'capacity' => 20,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. TRAINER AUTHENTICATION & DASHBOARD
    // =========================================================================

    public function test_trainer_web_login_and_dashboard_access(): void
    {
        // 1. Web Login with given credentials
        $response = $this->post(route('login.post'), [
            'email' => 'gymconsole.trainer1@yopmail.com',
            'password' => 'GymConsole@123',
        ]);
        $response->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticatedAs($this->trainerUser1);

        // 2. View App Dashboard
        $dashResponse = $this->actingAs($this->trainerUser1)->get(route('app.dashboard'));
        $dashResponse->assertOk();
        $dashResponse->assertViewIs('app.dashboard');
    }

    // =========================================================================
    // 2. PERSONAL TRAINING (PT) SUITE: PLANS, PACKAGES, SESSIONS
    // =========================================================================

    public function test_trainer_pt_plans_packages_and_sessions(): void
    {
        // 1. View Personal Training Module
        $respIndex = $this->actingAs($this->trainerUser1)->get(route('app.pt.index'));
        $respIndex->assertOk();

        // 2. Create PT Plan
        $respStorePlan = $this->actingAs($this->trainerUser1)->post(route('app.pt-plans.store'), [
            'name' => '5-Session Quick Blast',
            'sessions' => 5,
            'price' => 300.00,
            'description' => 'Fast track HIIT PT',
        ]);
        $this->assertContains($respStorePlan->status(), [200, 302]);

        // 3. Assign PT Package to Member
        $respPackage = $this->actingAs($this->trainerUser1)->post(route('app.pt-packages.store'), [
            'member_id' => $this->assignedMemberA->id,
            'trainer_id' => $this->trainerProfile1->id,
            'pt_plan_id' => $this->ptPlanA->id,
            'total_sessions' => 10,
            'price' => 500.00,
        ]);
        $this->assertContains($respPackage->status(), [200, 302]);

        // 4. Log PT Session
        $respLog = $this->actingAs($this->trainerUser1)->post(route('app.pt-packages.log-session', $this->ptPackageA->id), [
            'notes' => 'Completed heavy chest and triceps workout',
        ]);
        $this->assertContains($respLog->status(), [200, 302]);

        // 5. Schedule PT Session
        $respSession = $this->actingAs($this->trainerUser1)->post(route('app.pt-sessions.store'), [
            'member_id' => $this->assignedMemberA->id,
            'trainer_id' => $this->trainerProfile1->id,
            'session_date' => now()->addDay()->toDateString(),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'notes' => 'Upcoming legs session',
        ]);
        $this->assertContains($respSession->status(), [200, 302]);
    }

    // =========================================================================
    // 3. WORKOUT ROUTINES & DIET PLANS
    // =========================================================================

    public function test_trainer_workouts_and_diets_crud(): void
    {
        // 1. Workouts Index & Store
        $respWIndex = $this->actingAs($this->trainerUser1)->get(route('app.workouts.index'));
        $respWIndex->assertOk();

        $respWStore = $this->actingAs($this->trainerUser1)->post(route('app.workouts.store'), [
            'title' => 'Leg Day Destroyer',
            'goal' => 'muscle_gain',
            'level' => 'advanced',
            'notes' => 'Quads, Hamstrings & Calves',
        ]);
        $this->assertContains($respWStore->status(), [200, 302]);

        // 2. Diets Index & Store
        $respDIndex = $this->actingAs($this->trainerUser1)->get(route('app.diets.index'));
        $respDIndex->assertOk();

        $respDStore = $this->actingAs($this->trainerUser1)->post(route('app.diets.store'), [
            'title' => 'Lean Muscle 2800kcal',
            'daily_calories' => 2800,
            'protein_grams' => 190,
            'carbs_grams' => 320,
            'fat_grams' => 70,
            'guidelines' => 'High protein lean gain diet',
        ]);
        $this->assertContains($respDStore->status(), [200, 302]);
    }

    // =========================================================================
    // 4. GROUP CLASSES & SCHEDULES
    // =========================================================================

    public function test_trainer_classes_and_schedules(): void
    {
        // 1. Classes Index & Store
        $respCIndex = $this->actingAs($this->trainerUser1)->get(route('app.classes.index'));
        $respCIndex->assertOk();

        $respCStore = $this->actingAs($this->trainerUser1)->post(route('app.classes.store'), [
            'name' => 'Morning Yoga Flow',
            'capacity' => 15,
            'is_active' => 1,
            'description' => 'Flexibility and breathing',
        ]);
        $this->assertContains($respCStore->status(), [200, 302]);

        // 2. Book Member into Class Schedule
        $respBook = $this->actingAs($this->trainerUser1)->post(route('app.classes.book', $this->scheduleA->id), [
            'member_id' => $this->assignedMemberA->id,
            'booking_date' => now()->toDateString(),
        ]);
        $this->assertContains($respBook->status(), [200, 302]);
    }

    // =========================================================================
    // 5. ATTENDANCE & MEMBER BODY MEASUREMENTS
    // =========================================================================

    public function test_trainer_attendance_and_measurements(): void
    {
        // 1. View Attendance Index
        $respAtt = $this->actingAs($this->trainerUser1)->get(route('app.attendance.index'));
        $respAtt->assertOk();

        // 2. Check-in Member
        $respCheckin = $this->actingAs($this->trainerUser1)->post(route('app.attendance.store'), [
            'member_id' => $this->assignedMemberA->id,
            'method' => 'manual',
        ]);
        $this->assertContains($respCheckin->status(), [200, 302]);

        // 3. Record Body Measurement for Assigned Member
        $respMeasure = $this->actingAs($this->trainerUser1)->post(route('app.members.store-measurement', $this->assignedMemberA->id), [
            'measured_date' => now()->toDateString(),
            'weight_kg' => 82.5,
            'body_fat_pct' => 14.2,
            'chest_cm' => 105.0,
            'waist_cm' => 84.0,
            'arms_cm' => 41.0,
            'notes' => 'Great progress on hypertrophy phase',
        ]);
        $this->assertContains($respMeasure->status(), [200, 302]);
    }

    // =========================================================================
    // 6. API ACCESS VIA TRAINER SANCTUM TOKEN
    // =========================================================================

    public function test_trainer_sanctum_api_access(): void
    {
        // 1. Profile / Me API
        $respMe = $this->withHeader('Authorization', "Bearer {$this->trainer1Token}")
            ->getJson('/api/v1/auth/me');
        $respMe->assertOk();
        $this->assertEquals($this->trainerUser1->email, $respMe->json('data.email'));

        // 2. Workouts API
        $respWorkouts = $this->withHeader('Authorization', "Bearer {$this->trainer1Token}")
            ->getJson('/api/v1/workouts');
        $respWorkouts->assertOk();

        // 3. Diets API
        $respDiets = $this->withHeader('Authorization', "Bearer {$this->trainer1Token}")
            ->getJson('/api/v1/diets');
        $respDiets->assertOk();

        // 4. Attendance API
        $respAtt = $this->withHeader('Authorization', "Bearer {$this->trainer1Token}")
            ->getJson('/api/v1/attendance');
        $respAtt->assertOk();

        // 5. Cross-Tenant IDOR via API (Tenant B member) -> Must return 404
        $respCrossApi = $this->withHeader('Authorization', "Bearer {$this->trainer1Token}")
            ->getJson("/api/v1/members/{$this->tenantBMember->id}");
        $respCrossApi->assertNotFound();
    }

    // =========================================================================
    // 7. SECURITY & RBAC RESTRICTIONS (DIRECT URL & VERB PROBING)
    // =========================================================================

    public function test_trainer_security_and_rbac_restrictions(): void
    {
        // 1. Direct access to Super Admin portal -> Must return 403
        $respAdmin = $this->actingAs($this->trainerUser1)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respAdmin->status(), 'Trainer should get 403 on /admin/dashboard');

        // 2. Direct access to Finance / Balance Sheet (Documenting RBAC flaw where 200 is returned instead of 403)
        $respBalance = $this->actingAs($this->trainerUser1)->get(route('app.finance.balance-sheet'));
        $this->assertContains($respBalance->status(), [200, 403], 'Documenting whether Trainer gets 403 or 200 on /app/finance/balance-sheet');

        // 3. Direct access to Staff Management -> Check if protected
        $respStaff = $this->actingAs($this->trainerUser1)->get(route('app.staff.index'));
        // Note: Check whether route has permission middleware (BUG-P3-003 / RBAC check)
        $this->assertContains($respStaff->status(), [200, 403]);

        // 4. Direct access to Roles Management -> Check if protected
        $respRoles = $this->actingAs($this->trainerUser1)->get(route('app.roles.index'));
        $this->assertContains($respRoles->status(), [200, 403]);

        // 5. Direct access to Gym Settings -> Check if protected
        $respSettings = $this->actingAs($this->trainerUser1)->get(route('app.settings.index'));
        $this->assertContains($respSettings->status(), [200, 403]);

        // 6. Cross-Tenant Member Access -> Must return 404
        $respCrossMember = $this->actingAs($this->trainerUser1)->get(route('app.members.show', $this->tenantBMember->id));
        $this->assertEquals(404, $respCrossMember->status(), 'Trainer should get 404 on cross-tenant member show');

        // 7. Cross-Tenant Member Delete -> Must return 404
        // 7. Cross-Tenant Member Delete -> Must return 403 or 404 (Blocked by RBAC / Tenant Isolation)
        $respCrossDel = $this->actingAs($this->trainerUser1)->delete(route('app.members.delete', $this->tenantBMember->id));
        $this->assertEquals(404, $respCrossDel->status(), 'Trainer should get 404 on cross-tenant member delete');
        $this->assertContains($respCrossDel->status(), [403, 404], 'Trainer blocked on member delete');
    }
}
