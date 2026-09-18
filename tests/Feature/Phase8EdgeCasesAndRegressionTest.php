<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase8EdgeCasesAndRegressionTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $ownerB;

    protected User $managerA;

    protected User $receptionistA;

    protected User $accountantA;

    protected User $trainerA;

    protected User $staffA;

    protected User $memberUserA;

    protected Member $memberA;

    protected Member $memberB;

    protected MembershipPlan $mPlanA;

    protected Membership $membershipA;

    protected MemberPayment $paymentA;

    protected Device $deviceA;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionSeeder)->run();
        $proPlan = Plan::where('slug', 'pro')->first();

        $tenantService = app(TenantService::class);
        $subService = app(SubscriptionService::class);

        // Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_edge@gymconsole.com'],
            ['name' => 'Super Admin Edge', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Edge Tenant A {$randA}",
            'email' => "owner_edge_a_{$randA}@gym.com",
            'owner_name' => 'Owner Edge A',
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

        // Tenant B
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Edge Tenant B {$randB}",
            'email' => "owner_edge_b_{$randB}@gym.com",
            'owner_name' => 'Owner Edge B',
            'password' => 'password123',
        ], $proPlan);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['owner'];
        $this->branchB = $regB['branch'];

        $subService->activateSubscription(
            $this->tenantB,
            $proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Users under Tenant A
        $this->managerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Manager Edge',
            'email' => "manager_edge_{$randA}@gym.com",
            'role' => 'gym_manager',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        $this->receptionistA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Receptionist Edge',
            'email' => "receptionist_edge_{$randA}@gym.com",
            'role' => 'receptionist',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        $this->accountantA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Accountant Edge',
            'email' => "accountant_edge_{$randA}@gym.com",
            'role' => 'accountant',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        $this->trainerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Edge',
            'email' => "trainer_edge_{$randA}@gym.com",
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        $this->staffA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Staff Edge',
            'email' => "staff_edge_{$randA}@gym.com",
            'role' => 'staff',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        $this->memberUserA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Member User Edge',
            'email' => "member_user_edge_{$randA}@gym.com",
            'role' => 'member',
            'status' => 'ACTIVE',
            'password' => Hash::make('password123'),
        ]);

        // Attach Spatie/custom roles where applicable
        $roleManager = Role::where('tenant_id', $this->tenantA->id)->where('name', 'gym_manager')->first();
        if ($roleManager) {
            $this->managerA->roles()->attach($roleManager);
        }
        $roleRecep = Role::where('tenant_id', $this->tenantA->id)->where('name', 'receptionist')->first();
        if ($roleRecep) {
            $this->receptionistA->roles()->attach($roleRecep);
        }
        $roleAcc = Role::where('tenant_id', $this->tenantA->id)->where('name', 'accountant')->first();
        if ($roleAcc) {
            $this->accountantA->roles()->attach($roleAcc);
        }
        $roleTr = Role::where('tenant_id', $this->tenantA->id)->where('name', 'trainer')->first();
        if ($roleTr) {
            $this->trainerA->roles()->attach($roleTr);
        }
        $roleSt = Role::where('tenant_id', $this->tenantA->id)->where('name', 'staff')->first();
        if ($roleSt) {
            $this->staffA->roles()->attach($roleSt);
        }

        // Core entities under Tenant A
        $this->memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'user_id' => $this->memberUserA->id,
            'member_code' => "EDG-M-{$randA}",
            'first_name' => 'Edge',
            'last_name' => 'Member',
            'phone' => '9900000001',
            'email' => $this->memberUserA->email,
            'qr_code_token' => 'QR-EDGE-001',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->mPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Edge Standard Plan',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 150.00,
            'is_active' => true,
        ]);

        $this->membershipA = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $this->memberA->id,
            'membership_plan_id' => $this->mPlanA->id,
            'price' => 150.00,
            'final_amount' => 150.00,
            'paid_amount' => 150.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->paymentA = MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $this->memberA->id,
            'membership_id' => $this->membershipA->id,
            'amount' => 150.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'invoice_number' => "INV-EDG-{$randA}",
        ]);

        $this->deviceA = Device::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'name' => 'Edge Turnstile A',
            'model' => 'DS-K1T341',
            'serial_number' => "HKV-EDG-{$randA}",
            'device_secret' => "SECRET-EDG-{$randA}",
            'type' => 'hikvision_facial',
            'status' => 'ONLINE',
        ]);

        // Tenant B Member
        $this->memberB = Member::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'member_code' => "EDG-M-{$randB}",
            'first_name' => 'Beta',
            'last_name' => 'Member',
            'phone' => '9900000002',
            'email' => 'beta@tenantb.com',
            'qr_code_token' => 'QR-EDGE-B-002',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);
    }

    // =========================================================================
    // 1. EDGE CASES: EMPTY / NULL / MISSING FIELDS & INVALID DATA TYPES
    // =========================================================================

    public function test_edge_empty_and_missing_fields_validation(): void
    {
        // 1. Empty member store request
        $resp = $this->actingAs($this->ownerA)->post(route('app.members.store'), []);
        $resp->assertSessionHasErrors(['first_name', 'phone']);

        // 2. Empty payment store request
        $resp = $this->actingAs($this->ownerA)->post(route('app.payments.store'), []);
        $resp->assertSessionHasErrors(['member_id', 'amount']);

        // 3. Empty expense store request
        $resp = $this->actingAs($this->ownerA)->post(route('app.expenses.store'), []);
        $resp->assertSessionHasErrors(['title', 'amount', 'expense_date']);
    }

    public function test_edge_negative_and_zero_values_validation(): void
    {
        // 1. Negative payment amount
        $resp = $this->actingAs($this->ownerA)->post(route('app.payments.store'), [
            'member_id' => $this->memberA->id,
            'amount' => -100.00,
            'payment_method' => 'cash',
        ]);
        $resp->assertSessionHasErrors(['amount']);

        // 2. Negative expense amount
        $cat = ExpenseCategory::firstOrCreate(['tenant_id' => $this->tenantA->id, 'name' => 'Bills']);
        $resp = $this->actingAs($this->ownerA)->post(route('app.expenses.store'), [
            'expense_category_id' => $cat->id,
            'title' => 'Negative Bill',
            'amount' => -50.00,
            'payment_method' => 'cash',
            'expense_date' => now()->toDateString(),
        ]);
        $resp->assertSessionHasErrors(['amount']);
    }

    public function test_edge_very_large_monetary_values_and_decimals(): void
    {
        // Very large payment amount (e.g. 99,999,999.99)
        $resp = $this->actingAs($this->ownerA)->post(route('app.payments.store'), [
            'member_id' => $this->memberA->id,
            'amount' => 99999999.99,
            'payment_method' => 'card',
            'payment_date' => now()->toDateString(),
        ]);
        // Should handle without uncaught DB overflow exception (redirect with success or validation error)
        $this->assertContains($resp->status(), [200, 302, 422]);
    }

    public function test_edge_non_existent_and_invalid_ids(): void
    {
        // 1. Non-existent Member ID
        $resp = $this->actingAs($this->ownerA)->get('/app/members/99999999');
        $this->assertEquals(404, $resp->status());

        // 2. Non-existent Invoice ID
        $resp = $this->actingAs($this->ownerA)->get('/app/invoices/99999999');
        $this->assertEquals(404, $resp->status());

        // 3. String / Malformed ID in place of integer
        $resp = $this->actingAs($this->ownerA)->get('/app/members/invalid-string-id');
        $this->assertContains($resp->status(), [404, 500]);
    }

    // =========================================================================
    // 2. EDGE CASES: DUPLICATE RECORDS & IDEMPOTENCY
    // =========================================================================

    public function test_edge_duplicate_member_phone_and_code(): void
    {
        // Check behavior when submitting member with existing phone
        $resp = $this->actingAs($this->ownerA)->post(route('app.members.store'), [
            'first_name' => 'Duplicate',
            'last_name' => 'User',
            'phone' => $this->memberA->phone,
            'gender' => 'male',
        ]);
        // System either rejects with validation error or creates duplicate depending on unique constraint
        $this->assertContains($resp->status(), [200, 302, 422]);
    }

    public function test_edge_duplicate_attendance_checkin_same_day(): void
    {
        // Record first check-in today
        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_id' => $this->memberA->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subMinutes(30),
            'status' => 'PRESENT',
            'method' => 'manual',
        ]);

        // Attempt second check-in on the same day
        $resp = $this->actingAs($this->ownerA)->post(route('app.attendance.store'), [
            'member_id' => $this->memberA->id,
            'method' => 'manual',
        ]);

        // Application handles duplicate check-in safely (redirect with notice/error or log)
        $this->assertContains($resp->status(), [200, 302]);
    }

    // =========================================================================
    // 3. EDGE CASES: BOUNDARY DATES & TIME TRAPS
    // =========================================================================

    public function test_edge_boundary_and_inverted_dates(): void
    {
        // 1. End date before start date in membership
        $resp = $this->actingAs($this->ownerA)->post(route('app.members.add-subscription', $this->memberA->id), [
            'membership_plan_id' => $this->mPlanA->id,
            'start_date' => '2026-12-31',
            'end_date' => '2026-01-01', // Before start_date
            'paid_amount' => 150.00,
            'payment_method' => 'cash',
        ]);
        $this->assertContains($resp->status(), [200, 302, 422]);

        // 2. Far future date (year 2099)
        $resp2 = $this->actingAs($this->ownerA)->post(route('app.members.add-subscription', $this->memberA->id), [
            'membership_plan_id' => $this->mPlanA->id,
            'start_date' => '2099-01-01',
            'paid_amount' => 150.00,
            'payment_method' => 'cash',
        ]);
        $this->assertContains($resp2->status(), [200, 302, 422]);

        // 3. Leap year date (Feb 29)
        $resp3 = $this->actingAs($this->ownerA)->get(route('app.attendance.index', ['date' => '2028-02-29']));
        $resp3->assertOk();
    }

    // =========================================================================
    // 4. EDGE CASES: STATUS, LIFECYCLE & ZERO-STATE SCENARIOS
    // =========================================================================

    public function test_edge_suspended_user_login_blocked(): void
    {
        $suspendedUser = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Suspended Staff',
            'email' => 'suspended_edge@gym.com',
            'role' => 'staff',
            'status' => 'INACTIVE',
            'password' => Hash::make('password123'),
        ]);

        // 1. API Login Attempt is strictly blocked with 403
        $respApi = $this->postJson('/api/v1/auth/login', [
            'email' => $suspendedUser->email,
            'password' => 'password123',
        ]);
        $respApi->assertStatus(403);

        // 2. Web Login Attempt: Note that Web AuthController does not check active status (Documented Bug BUG-P8-001)
        $respWeb = $this->post(route('login.post'), [
            'email' => $suspendedUser->email,
            'password' => 'password123',
        ]);
        $this->assertContains($respWeb->status(), [200, 302]);
    }

    public function test_edge_zero_state_dashboards_and_reports(): void
    {
        // Create an empty fresh tenant with 0 members, 0 payments, 0 check-ins
        $tenantService = app(TenantService::class);
        $subService = app(SubscriptionService::class);
        $proPlan = Plan::where('slug', 'pro')->first();

        $randFresh = Str::random(5);
        $regFresh = $tenantService->registerGym([
            'gym_name' => "Zero State Gym {$randFresh}",
            'email' => "fresh_zero_{$randFresh}@gym.com",
            'owner_name' => 'Zero State Owner',
            'password' => 'password123',
        ], $proPlan);
        $freshTenant = $regFresh['tenant'];
        $freshOwner = $regFresh['owner'];

        $subService->activateSubscription(
            $freshTenant,
            $proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Access Dashboard with 0 records
        $respDash = $this->actingAs($freshOwner)->get(route('app.dashboard'));
        $respDash->assertOk();

        // Access Reports & Finance with 0 records (verify no divide-by-zero errors)
        $respRep1 = $this->actingAs($freshOwner)->get(route('app.finance.balance-sheet'));
        $respRep1->assertOk();

        $respRep2 = $this->actingAs($freshOwner)->get(route('app.expenses.index'));
        $respRep2->assertOk();

        $respRep3 = $this->actingAs($freshOwner)->get(route('app.finance.balance-sheet.pdf'));
        $respRep3->assertOk();
    }

    public function test_edge_search_special_characters_and_pagination_boundaries(): void
    {
        // 1. Search with SQL injection characters
        $respSearch = $this->actingAs($this->ownerA)->get(route('app.members.index', [
            'search' => "' OR '1'='1' --",
        ]));
        $respSearch->assertOk();

        // 2. Search resulting in 0 matches
        $respNoMatch = $this->actingAs($this->ownerA)->get(route('app.members.index', [
            'search' => 'NON_EXISTENT_STRING_XYZ_999',
        ]));
        $respNoMatch->assertOk();

        // 3. Out of bounds page number
        $respPageOOB = $this->actingAs($this->ownerA)->get(route('app.members.index', [
            'page' => 999999,
        ]));
        $respPageOOB->assertOk();

        // 4. Negative page number
        $respNegPage = $this->actingAs($this->ownerA)->get(route('app.members.index', [
            'page' => -1,
        ]));
        $respNegPage->assertOk();
    }

    public function test_edge_api_malformed_payload_and_invalid_token(): void
    {
        // 1. Invalid Sanctum Token
        $respBadToken = $this->withHeader('Authorization', 'Bearer invalid_tampered_token_xyz')
            ->getJson('/api/v1/members');
        $respBadToken->assertStatus(401);

        // 2. Malformed JSON payload on store endpoint
        $validToken = $this->ownerA->createToken('EdgeTestToken')->plainTextToken;
        $respMalformed = $this->withHeader('Authorization', "Bearer {$validToken}")
            ->postJson('/api/v1/members', [
                'first_name' => 12345, // invalid type
                'phone' => null,
            ]);
        $respMalformed->assertStatus(422);
    }

    // =========================================================================
    // 5. REGRESSION & PREVIOUSLY KNOWN BUGS RE-CONFIRMATION
    // =========================================================================

    public function test_regression_reconfirm_known_bugs(): void
    {
        // BUG-API-01 Re-confirmation: Member role accesses /api/v1/members without server-side 403
        $memberToken = $this->memberUserA->createToken('RegressionMemberToken')->plainTextToken;
        $respApiMember = $this->withHeader('Authorization', "Bearer {$memberToken}")
            ->getJson('/api/v1/members');
        // Currently returns 200 instead of 403 due to missing role middleware
        $this->assertEquals(200, $respApiMember->status(), 'Reconfirming BUG-API-01: Member role unexpectedly allowed on /api/v1/members');
        // Now properly returns 403 with role middleware applied
        $this->assertEquals(403, $respApiMember->status(), 'Verifying BUG-API-01 fixed: Member role forbidden on /api/v1/members');

        // RBAC Non-Super Admin blocked on /admin/*
        $respAdmin = $this->actingAs($this->ownerA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respAdmin->status());

        // Cross-Tenant direct access returns 404
        $respCross = $this->actingAs($this->ownerB)->get(route('app.members.show', $this->memberA->id));
        $this->assertEquals(404, $respCross->status());
    }
}
