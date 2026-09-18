<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Role;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use App\Services\TenantService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase3RbacAndTenantIsolationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected Branch $branchB;

    protected User $ownerA;

    protected User $ownerB;

    protected User $managerA;

    protected User $receptionistA;

    protected User $accountantA;

    protected User $trainerA1;

    protected User $trainerA2;

    protected User $staffA;

    protected User $memberUserA;

    protected Member $memberA;

    protected Member $memberB;

    protected MembershipPlan $mPlanA;

    protected MembershipPlan $mPlanB;

    protected MemberPayment $paymentA;

    protected Expense $expenseA;

    protected GymClass $classA;

    protected Trainer $trainerProfileA1;

    protected Trainer $trainerProfileA2;

    protected Lead $leadA;

    protected SupportTicket $ticketA;

    protected SupportTicket $ticketB;

    protected function setUp(): void
    {
        parent::setUp();

        // Ensure Permissions and Plan exist
        (new PermissionSeeder)->run();
        $proPlan = Plan::where('slug', 'pro')->first();

        $tenantService = app(TenantService::class);

        // Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_test@gymconsole.com'],
            ['name' => 'Super Admin Test', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Setup Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Tenant A Gym {$randA}",
            'email' => "owner_a_{$randA}@gym.com",
            'owner_name' => 'Owner A',
            'password' => 'password',
        ], $proPlan);
        $this->tenantA = $regA['tenant'];
        $this->ownerA = $regA['owner'];
        $this->branchA1 = $regA['branch'];

        // Add 2nd Branch to Tenant A
        $this->branchA2 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Tenant A Branch 2',
            'code' => 'BR2',
            'is_main' => false,
            'status' => 'ACTIVE',
        ]);

        // Setup Tenant B
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Tenant B Gym {$randB}",
            'email' => "owner_b_{$randB}@gym.com",
            'owner_name' => 'Owner B',
            'password' => 'password',
        ], $proPlan);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['owner'];
        $this->branchB = $regB['branch'];

        // Assign default roles for Tenant A
        $roleManager = Role::where('tenant_id', $this->tenantA->id)->where('name', 'gym_manager')->first();
        $roleReceptionist = Role::where('tenant_id', $this->tenantA->id)->where('name', 'receptionist')->first();
        $roleAccountant = Role::where('tenant_id', $this->tenantA->id)->where('name', 'accountant')->first();
        $roleTrainer = Role::where('tenant_id', $this->tenantA->id)->where('name', 'trainer')->first();
        $roleStaff = Role::where('tenant_id', $this->tenantA->id)->where('name', 'staff')->first();

        // Create Users for each Role under Tenant A
        $this->managerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Manager Alice',
            'email' => "manager_{$randA}@gym.com",
            'role' => 'gym_manager',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleManager) {
            $this->managerA->roles()->attach($roleManager);
        }

        $this->receptionistA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Receptionist Bob',
            'email' => "receptionist_{$randA}@gym.com",
            'role' => 'receptionist',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleReceptionist) {
            $this->receptionistA->roles()->attach($roleReceptionist);
        }

        $this->accountantA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Accountant Charlie',
            'email' => "accountant_{$randA}@gym.com",
            'role' => 'accountant',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleAccountant) {
            $this->accountantA->roles()->attach($roleAccountant);
        }

        $this->trainerA1 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Dave',
            'email' => "trainer1_{$randA}@gym.com",
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleTrainer) {
            $this->trainerA1->roles()->attach($roleTrainer);
        }
        $this->trainerProfileA1 = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->trainerA1->id,
            'first_name' => 'Trainer',
            'last_name' => 'Dave',
            'email' => $this->trainerA1->email,
            'phone' => '9800000004',
            'status' => 'ACTIVE',
        ]);

        $this->trainerA2 = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Eve',
            'email' => "trainer2_{$randA}@gym.com",
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleTrainer) {
            $this->trainerA2->roles()->attach($roleTrainer);
        }
        $this->trainerProfileA2 = Trainer::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->trainerA2->id,
            'first_name' => 'Trainer',
            'last_name' => 'Eve',
            'email' => $this->trainerA2->email,
            'phone' => '9800000005',
            'status' => 'ACTIVE',
        ]);

        $this->staffA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Staff Frank',
            'email' => "staff_{$randA}@gym.com",
            'role' => 'staff',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
        if ($roleStaff) {
            $this->staffA->roles()->attach($roleStaff);
        }

        $this->memberUserA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Member George',
            'email' => "member_{$randA}@gym.com",
            'role' => 'member',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        // Create Entities under Tenant A
        $this->memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'user_id' => $this->memberUserA->id,
            'member_code' => "MEM-A-{$randA}",
            'first_name' => 'George',
            'last_name' => 'MemberA',
            'phone' => '9800000001',
            'email' => $this->memberUserA->email,
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->mPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Gold Plan A',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 100.00,
            'is_active' => true,
        ]);

        $this->paymentA = MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'member_id' => $this->memberA->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'status' => 'PAID',
            'payment_date' => now()->toDateString(),
            'invoice_number' => "INV-A-{$randA}",
        ]);

        $catA = ExpenseCategory::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Rent & Bills',
        ]);

        $this->expenseA = Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $catA->id,
            'title' => 'Monthly Electricity',
            'amount' => 250.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'card',
        ]);

        $this->classA = GymClass::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Spinning Class A',
            'capacity' => 15,
            'is_active' => true,
        ]);

        $this->leadA = Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Lead Alpha',
            'phone' => '9800000002',
            'email' => 'lead_alpha@example.com',
            'source' => 'website',
            'status' => 'NEW',
        ]);

        $this->ticketA = SupportTicket::create([
            'tenant_id' => $this->tenantA->id,
            'user_id' => $this->ownerA->id,
            'ticket_number' => "TCK-A-{$randA}",
            'subject' => 'Issue in Tenant A',
            'message' => 'Help needed with settings',
            'priority' => 'high',
            'status' => 'open',
        ]);

        // Create Entities under Tenant B
        $this->memberB = Member::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'member_code' => "MEM-B-{$randB}",
            'first_name' => 'Helen',
            'last_name' => 'MemberB',
            'phone' => '9800000003',
            'email' => 'helen@tenantb.com',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $this->mPlanB = MembershipPlan::create([
            'tenant_id' => $this->tenantB->id,
            'branch_id' => $this->branchB->id,
            'name' => 'Gold Plan B',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 150.00,
            'is_active' => true,
        ]);

        $this->ticketB = SupportTicket::create([
            'tenant_id' => $this->tenantB->id,
            'user_id' => $this->ownerB->id,
            'ticket_number' => "TCK-B-{$randB}",
            'subject' => 'Issue in Tenant B',
            'message' => 'Help needed for Tenant B',
            'priority' => 'low',
            'status' => 'open',
        ]);
    }

    // ==========================================
    // 1. SUPER ADMIN ACCESS & CROSS-TENANT TESTS
    // ==========================================

    public function test_super_admin_can_access_admin_portal_routes(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.gyms'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.plans'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.coupons'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.users'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.tickets.index'));
        $response->assertOk();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.settings'));
        $response->assertOk();
    }

    public function test_non_super_admin_cannot_access_admin_portal(): void
    {
        // Owner A
        $respOwner = $this->actingAs($this->ownerA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respOwner->status(), 'Gym Owner should get 403 on /admin/dashboard');

        // Receptionist A
        $respRecep = $this->actingAs($this->receptionistA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respRecep->status(), 'Receptionist should get 403 on /admin/dashboard');

        // Customer / Member
        $respMem = $this->actingAs($this->memberUserA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respMem->status(), 'Member should get 403 on /admin/dashboard');
    }

    // ==========================================
    // 2. CROSS-TENANT DATA ISOLATION TESTS
    // ==========================================

    public function test_cross_tenant_isolation_members(): void
    {
        // Owner B attempts direct GET /app/members/{memberA_id}
        $resp = $this->actingAs($this->ownerB)->get(route('app.members.show', $this->memberA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 when attempting to access Member A');

        // Owner B attempts POST /app/members/{memberA_id} update
        $resp = $this->actingAs($this->ownerB)->post(route('app.members.update', $this->memberA->id), [
            'first_name' => 'Tampered Name',
        ]);
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 on update Member A');
        $this->assertEquals('George', $this->memberA->fresh()->first_name);

        // Owner B attempts DELETE /app/members/{memberA_id}
        $resp = $this->actingAs($this->ownerB)->delete(route('app.members.delete', $this->memberA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 on delete Member A');
        $this->assertNull($this->memberA->fresh()->deleted_at);
    }

    public function test_cross_tenant_isolation_membership_plans(): void
    {
        // Owner B attempts to delete Tenant A's membership plan
        $resp = $this->actingAs($this->ownerB)->delete(route('app.membership-plans.delete', $this->mPlanA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 deleting Tenant A membership plan');
        $this->assertDatabaseHas('membership_plans', ['id' => $this->mPlanA->id]);
    }

    public function test_cross_tenant_isolation_payments_invoices(): void
    {
        // Owner B attempts to view Tenant A invoice
        $resp = $this->actingAs($this->ownerB)->get(route('app.invoices.show', $this->paymentA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 viewing Tenant A invoice');

        // Owner B attempts to reverse Tenant A payment
        $resp = $this->actingAs($this->ownerB)->post(route('app.payments.reverse', $this->paymentA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 reversing Tenant A payment');
        $this->assertDatabaseHas('member_payments', ['id' => $this->paymentA->id]);
    }

    public function test_cross_tenant_isolation_support_tickets(): void
    {
        // Owner B attempts to view Tenant A's support ticket
        $resp = $this->actingAs($this->ownerB)->get(route('app.support.show', $this->ticketA->id));
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 viewing Tenant A support ticket');

        // Owner B attempts to reply to Tenant A's support ticket
        $resp = $this->actingAs($this->ownerB)->post(route('app.support.reply', $this->ticketA->id), [
            'message' => 'Hacked reply from Tenant B',
        ]);
        $this->assertEquals(404, $resp->status(), 'Owner B should get 404 replying to Tenant A ticket');
    }

    // ==========================================
    // 3. ROLE-BASED ACCESS CONTROL (RBAC) TESTS
    // ==========================================

    public function test_customer_member_role_cannot_access_staff_admin_pages(): void
    {
        // Customer / Member accessing /app/staff
        $resp = $this->actingAs($this->memberUserA)->get(route('app.staff.index'));
        $this->assertEquals(403, $resp->status(), 'Member should receive 403 on /app/staff');

        // Customer / Member accessing /app/roles
        $resp = $this->actingAs($this->memberUserA)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Member should receive 403 on /app/roles');

        // Customer / Member accessing /app/settings
        $resp = $this->actingAs($this->memberUserA)->get(route('app.settings.index'));
        $this->assertEquals(403, $resp->status(), 'Member should receive 403 on /app/settings');

        // Customer / Member accessing /app/finance/balance-sheet
        $resp = $this->actingAs($this->memberUserA)->get(route('app.finance.balance-sheet'));
        $this->assertEquals(403, $resp->status(), 'Member should receive 403 on /app/finance/balance-sheet');

        // Customer / Member accessing /app/members
        $resp = $this->actingAs($this->memberUserA)->get(route('app.members.index'));
        $this->assertEquals(403, $resp->status(), 'Member should receive 403 on /app/members');
    }

    public function test_staff_role_cannot_access_unauthorized_features_or_actions(): void
    {
        // Staff does NOT have roles.manage -> /app/roles
        $resp = $this->actingAs($this->staffA)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 on /app/roles');

        // Staff does NOT have staff.manage -> POST /app/staff
        $resp = $this->actingAs($this->staffA)->post(route('app.staff.store'), [
            'name' => 'Hacker Staff',
            'email' => 'hacker@staff.com',
            'role' => 'gym_owner',
            'password' => 'password',
        ]);
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 creating staff');

        // Staff does NOT have settings.manage -> POST /app/settings
        $resp = $this->actingAs($this->staffA)->post(route('app.settings.update'), [
            'gym_name' => 'Hacked Gym Name',
        ]);
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 modifying settings');

        // Staff does NOT have reports.balance_sheet -> /app/finance/balance-sheet
        $resp = $this->actingAs($this->staffA)->get(route('app.finance.balance-sheet'));
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 on balance sheet');

        // Staff does NOT have payments.view -> /app/payments
        $resp = $this->actingAs($this->staffA)->get(route('app.payments.index'));
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 on payments index');

        // Staff does NOT have members.delete -> DELETE /app/members/{id}
        $resp = $this->actingAs($this->staffA)->delete(route('app.members.delete', $this->memberA->id));
        $this->assertEquals(403, $resp->status(), 'Staff should receive 403 deleting a member');
    }

    public function test_trainer_role_cannot_access_unauthorized_features_or_actions(): void
    {
        // Trainer does NOT have payments.view -> /app/payments
        $resp = $this->actingAs($this->trainerA1)->get(route('app.payments.index'));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 on /app/payments');

        // Trainer does NOT have roles.manage -> /app/roles
        $resp = $this->actingAs($this->trainerA1)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 on /app/roles');

        // Trainer does NOT have reports.balance_sheet -> /app/finance/balance-sheet
        $resp = $this->actingAs($this->trainerA1)->get(route('app.finance.balance-sheet'));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 on balance sheet');

        // Trainer does NOT have crm.view -> /app/crm
        $resp = $this->actingAs($this->trainerA1)->get(route('app.crm.index'));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 on CRM dashboard');

        // Trainer does NOT have members.delete -> DELETE /app/members/{id}
        $resp = $this->actingAs($this->trainerA1)->delete(route('app.members.delete', $this->memberA->id));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 deleting a member');

        // Trainer does NOT have settings.manage -> /app/settings
        $resp = $this->actingAs($this->trainerA1)->get(route('app.settings.index'));
        $this->assertEquals(403, $resp->status(), 'Trainer should receive 403 on settings');
    }

    public function test_receptionist_role_cannot_access_unauthorized_features_or_actions(): void
    {
        // Receptionist does NOT have roles.manage -> /app/roles
        $resp = $this->actingAs($this->receptionistA)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 on /app/roles');

        // Receptionist does NOT have staff.manage -> /app/staff
        $resp = $this->actingAs($this->receptionistA)->get(route('app.staff.index'));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 on /app/staff');

        // Receptionist does NOT have reports.balance_sheet -> /app/finance/balance-sheet
        $resp = $this->actingAs($this->receptionistA)->get(route('app.finance.balance-sheet'));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 on balance sheet');

        // Receptionist does NOT have payments.refund -> POST /app/payments/{id}/reverse
        $resp = $this->actingAs($this->receptionistA)->post(route('app.payments.reverse', $this->paymentA->id));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 on payment reversal');

        // Receptionist does NOT have members.delete -> DELETE /app/members/{id}
        $resp = $this->actingAs($this->receptionistA)->delete(route('app.members.delete', $this->memberA->id));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 deleting a member');

        // Receptionist does NOT have settings.manage -> /app/settings
        $resp = $this->actingAs($this->receptionistA)->get(route('app.settings.index'));
        $this->assertEquals(403, $resp->status(), 'Receptionist should receive 403 on settings');
    }

    public function test_accountant_role_cannot_access_unauthorized_features_or_actions(): void
    {
        // Accountant does NOT have roles.manage -> /app/roles
        $resp = $this->actingAs($this->accountantA)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Accountant should receive 403 on /app/roles');

        // Accountant does NOT have staff.manage -> /app/staff
        $resp = $this->actingAs($this->accountantA)->get(route('app.staff.index'));
        $this->assertEquals(403, $resp->status(), 'Accountant should receive 403 on /app/staff');

        // Accountant does NOT have crm.view -> /app/crm
        $resp = $this->actingAs($this->accountantA)->get(route('app.crm.index'));
        $this->assertEquals(403, $resp->status(), 'Accountant should receive 403 on CRM');

        // Accountant does NOT have members.delete -> DELETE /app/members/{id}
        $resp = $this->actingAs($this->accountantA)->delete(route('app.members.delete', $this->memberA->id));
        $this->assertEquals(403, $resp->status(), 'Accountant should receive 403 deleting a member');

        // Accountant does NOT have settings.manage -> /app/settings
        $resp = $this->actingAs($this->accountantA)->get(route('app.settings.index'));
        $this->assertEquals(403, $resp->status(), 'Accountant should receive 403 on settings');
    }

    public function test_gym_manager_role_cannot_access_unauthorized_owner_actions(): void
    {
        // Gym Manager does NOT have roles.manage -> /app/roles
        $resp = $this->actingAs($this->managerA)->get(route('app.roles.index'));
        $this->assertEquals(403, $resp->status(), 'Manager should receive 403 on /app/roles');

        // Gym Manager does NOT have staff.manage -> POST /app/staff
        $resp = $this->actingAs($this->managerA)->post(route('app.staff.store'), [
            'name' => 'Manager Added Staff',
            'email' => 'm_staff@gym.com',
            'role' => 'staff',
            'password' => 'password',
        ]);
        $this->assertEquals(403, $resp->status(), 'Manager should receive 403 creating staff');

        // Gym Manager does NOT have settings.manage -> /app/settings
        $resp = $this->actingAs($this->managerA)->get(route('app.settings.index'));
        $this->assertEquals(403, $resp->status(), 'Manager should receive 403 on settings');

        // Gym Manager does NOT have reports.balance_sheet -> /app/finance/balance-sheet
        $resp = $this->actingAs($this->managerA)->get(route('app.finance.balance-sheet'));
        $this->assertEquals(403, $resp->status(), 'Manager should receive 403 on balance sheet');
    }

    // ==========================================
    // 4. BRANCH ISOLATION TESTS
    // ==========================================

    public function test_branch_switching_and_scoping(): void
    {
        // Switch to branch A1
        $resp1 = $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchA1->id));
        $resp1->assertRedirect();
        $this->assertEquals($this->branchA1->id, session('active_branch_id'));

        // Switch to branch A2
        $resp2 = $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchA2->id));
        $resp2->assertRedirect();
        $this->assertEquals($this->branchA2->id, session('active_branch_id'));

        // Attempt to switch to branch B (cross-tenant)
        $resp3 = $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchB->id));
        $this->assertTrue(in_array($resp3->status(), [403, 404, 302]));
        // Active branch in session should NOT become branch B
        $this->assertNotEquals($this->branchB->id, session('active_branch_id'));
    }

    // ==========================================
    // 5. API V1 RBAC & CROSS-TENANT ISOLATION
    // ==========================================

    public function test_api_v1_cross_tenant_isolation(): void
    {
        $tokenB = $this->ownerB->createToken('test_token_b')->plainTextToken;

        // Tenant B calls API /api/v1/members
        $apiListResponse = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson('/api/v1/members');
        $apiListResponse->assertOk();
        $apiListResponse->assertJsonMissing(['member_code' => $this->memberA->member_code]);

        // Tenant B calls API /api/v1/members/{id} for Tenant A's member
        $apiShowResponse = $this->withHeader('Authorization', "Bearer {$tokenB}")
            ->getJson("/api/v1/members/{$this->memberA->id}");
        $apiShowResponse->assertNotFound();
    }

    public function test_api_v1_role_enforcement(): void
    {
        // Member / Customer accessing member list via API
        $memberToken = $this->memberUserA->createToken('member_token')->plainTextToken;

        $resp = $this->withHeader('Authorization', "Bearer {$memberToken}")
            ->getJson('/api/v1/members');
        // Check if Member user is allowed to list all tenant members
        $this->assertEquals(403, $resp->status(), 'Member role should receive 403 on API /api/v1/members');
    }

    // ==========================================
    // 6. IDOR / CROSS-TENANT PARAMETER TAMPERING
    // ==========================================

    public function test_cannot_tamper_member_id_in_payments(): void
    {
        // Owner A attempts to record payment referencing Member B (from Tenant B)
        $resp = $this->actingAs($this->ownerA)->post(route('app.payments.store'), [
            'member_id' => $this->memberB->id,
            'amount' => 50.00,
            'payment_method' => 'cash',
        ]);
        // Should fail or reject cross-tenant member ID
        $this->assertDatabaseMissing('member_payments', [
            'member_id' => $this->memberB->id,
            'amount' => 50.00,
        ]);
    }

    public function test_cannot_tamper_plan_id_in_subscriptions(): void
    {
        // Owner A attempts to assign Membership Plan B (from Tenant B) to Member A
        $resp = $this->actingAs($this->ownerA)->post(route('app.members.add-subscription', $this->memberA->id), [
            'membership_plan_id' => $this->mPlanB->id,
            'start_date' => now()->toDateString(),
            'paid_amount' => 150.00,
            'payment_method' => 'cash',
        ]);
        // Should fail or reject cross-tenant plan
        $this->assertDatabaseMissing('memberships', [
            'member_id' => $this->memberA->id,
            'membership_plan_id' => $this->mPlanB->id,
        ]);
    }
}
