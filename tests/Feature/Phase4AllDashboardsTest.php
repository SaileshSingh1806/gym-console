<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantService;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class Phase4AllDashboardsTest extends TestCase
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

    protected User $trainerA;

    protected User $staffA;

    protected User $memberUserA;

    protected Plan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionSeeder)->run();
        $this->proPlan = Plan::where('slug', 'pro')->first();

        $tenantService = app(TenantService::class);
        $subService = app(SubscriptionService::class);

        // Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_dash@gymconsole.com'],
            ['name' => 'Super Admin Dash', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Setup Tenant A (Active Pro Plan)
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Titan Gym {$randA}",
            'email' => "owner_titan_{$randA}@gym.com",
            'owner_name' => 'Titan Owner',
            'password' => 'password',
        ], $this->proPlan);
        $this->tenantA = $regA['tenant'];
        $this->ownerA = $regA['owner'];
        $this->branchA1 = $regA['branch'];

        $subService->activateSubscription(
            $this->tenantA,
            $this->proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Add 2nd Branch to Tenant A
        $this->branchA2 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Titan North Branch',
            'code' => 'TITAN-N',
            'is_main' => false,
            'status' => 'ACTIVE',
        ]);

        // Setup Tenant B (Active Pro Plan)
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Spartan Gym {$randB}",
            'email' => "owner_spartan_{$randB}@gym.com",
            'owner_name' => 'Spartan Owner',
            'password' => 'password',
        ], $this->proPlan);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['owner'];
        $this->branchB = $regB['branch'];

        $subService->activateSubscription(
            $this->tenantB,
            $this->proPlan,
            'yearly',
            'stripe',
            'ch_'.Str::random(10),
            499.00
        );

        // Roles & Staff under Tenant A
        $this->managerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Manager Mike',
            'email' => "manager_titan_{$randA}@gym.com",
            'role' => 'gym_manager',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->receptionistA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Receptionist Rita',
            'email' => "receptionist_titan_{$randA}@gym.com",
            'role' => 'receptionist',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->accountantA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Accountant Andy',
            'email' => "accountant_titan_{$randA}@gym.com",
            'role' => 'accountant',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->trainerA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Trainer Tom',
            'email' => "trainer_titan_{$randA}@gym.com",
            'role' => 'trainer',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->staffA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Staff Sam',
            'email' => "staff_titan_{$randA}@gym.com",
            'role' => 'staff',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->memberUserA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Member Matt',
            'email' => "member_titan_{$randA}@gym.com",
            'role' => 'member',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
    }

    // =========================================================================
    // 1. SUPER ADMIN PLATFORM DASHBOARD (/admin/dashboard)
    // =========================================================================

    public function test_super_admin_dashboard_loads_and_displays_platform_kpis(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertViewIs('admin.dashboard');

        // Check required view data variables
        $response->assertViewHasAll([
            'metrics',
            'totalMembers',
            'totalUsers',
            'totalStaffCount',
            'superAdminCount',
            'totalTicketsCount',
            'urgentTicketsCount',
            'totalBranchesCount',
            'activeSubscriptionsCount',
            'expiringSubscriptionsCount',
            'openTicketsCount',
            'recentGyms',
            'recentInvoices',
            'expiringSubscriptions',
            'openSupportTickets',
            'recentPayments',
            'plans',
        ]);

        $viewMetrics = $response->viewData('metrics');
        $this->assertIsArray($viewMetrics);
        $this->assertArrayHasKey('total_gyms', $viewMetrics);
        $this->assertArrayHasKey('active_gyms', $viewMetrics);
        $this->assertArrayHasKey('arr', $viewMetrics);
        $this->assertArrayHasKey('total_revenue', $viewMetrics);
    }

    public function test_super_admin_dashboard_calculations_match_database(): void
    {
        $expectedGyms = Tenant::count();
        $expectedActiveGyms = Tenant::where('status', 'ACTIVE')->count();
        $expectedUsers = User::count();
        $expectedBranches = Branch::withoutGlobalScopes()->count();

        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));
        $response->assertOk();

        $this->assertEquals($expectedGyms, $response->viewData('metrics')['total_gyms']);
        $this->assertEquals($expectedActiveGyms, $response->viewData('metrics')['active_gyms']);
        $this->assertEquals($expectedUsers, $response->viewData('totalUsers'));
        $this->assertEquals($expectedBranches, $response->viewData('totalBranchesCount'));
    }

    public function test_super_admin_dashboard_unauthorized_access_blocked(): void
    {
        $respOwner = $this->actingAs($this->ownerA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respOwner->status());

        $respStaff = $this->actingAs($this->staffA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respStaff->status());

        $respMember = $this->actingAs($this->memberUserA)->get(route('admin.dashboard'));
        $this->assertEquals(403, $respMember->status());
    }

    // =========================================================================
    // 2. GYM OPERATIONS DASHBOARD (/app/dashboard)
    // =========================================================================

    public function test_gym_operations_dashboard_loads_with_correct_metrics(): void
    {
        $mPlan = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Monthly Fitness',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 100.00,
            'is_active' => true,
        ]);

        $member1 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'TITAN-001',
            'first_name' => 'Active',
            'last_name' => 'Member1',
            'phone' => '9811111111',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membership1 = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member1->id,
            'membership_plan_id' => $mPlan->id,
            'price' => 100.00,
            'final_amount' => 100.00,
            'paid_amount' => 100.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $payment1 = MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member1->id,
            'membership_id' => $membership1->id,
            'amount' => 100.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-TITAN-1001',
        ]);

        $cat = ExpenseCategory::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Gym Supplies',
        ]);

        $expense1 = Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat->id,
            'title' => 'Cleaning Supplies',
            'amount' => 30.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member1->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subHour(),
            'status' => 'PRESENT',
            'method' => 'manual',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.dashboard'));
        $response->assertOk();
        $response->assertViewIs('app.dashboard');

        $metrics = $response->viewData('metrics');
        $this->assertEquals(1, $metrics['total_members']);
        $this->assertEquals(1, $metrics['active_members']);
        $this->assertEquals(100.00, $metrics['today_revenue']);
        $this->assertEquals(100.00, $metrics['month_revenue']);
        $this->assertEquals(30.00, $metrics['today_expense']);
        $this->assertEquals(30.00, $metrics['month_expense']);
        $this->assertEquals(70.00, $metrics['net_profit']); // 100 - 30 = 70
    }

    public function test_gym_operations_dashboard_branch_scoping(): void
    {
        // Add member in branch A1
        $memberA1 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'TITAN-A1',
            'first_name' => 'Branch1',
            'last_name' => 'Member',
            'phone' => '9811111112',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Add member in branch A2
        $memberA2 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'member_code' => 'TITAN-A2',
            'first_name' => 'Branch2',
            'last_name' => 'Member',
            'phone' => '9811111113',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Switch to Branch A1
        $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchA1->id));

        $respA1 = $this->actingAs($this->ownerA)->withSession(['active_branch_id' => $this->branchA1->id])->get(route('app.dashboard'));
        $respA1->assertOk();
        $metricsA1 = $respA1->viewData('metrics');
        $this->assertEquals(1, $metricsA1['total_members'], 'Dashboard metrics should scope to branch A1');

        // Switch to Branch A2
        $this->actingAs($this->ownerA)->post(route('app.branches.switch', $this->branchA2->id));

        $respA2 = $this->actingAs($this->ownerA)->withSession(['active_branch_id' => $this->branchA2->id])->get(route('app.dashboard'));
        $respA2->assertOk();
        $metricsA2 = $respA2->viewData('metrics');
        $this->assertEquals(1, $metricsA2['total_members'], 'Dashboard metrics should scope to branch A2');
    }

    public function test_gym_operations_dashboard_tenant_isolation(): void
    {
        // Tenant A has 1 member
        Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'TITAN-ISO-A',
            'first_name' => 'TitanOnly',
            'last_name' => 'User',
            'phone' => '9811111114',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Tenant B has 0 members currently
        $respB = $this->actingAs($this->ownerB)->get(route('app.dashboard'));
        $respB->assertOk();
        $metricsB = $respB->viewData('metrics');
        $this->assertEquals(0, $metricsB['total_members'], 'Tenant B dashboard must not count Tenant A members');
    }

    public function test_gym_operations_dashboard_expiring_due_birthday_action_tiles(): void
    {
        // 1. Expiring member (end date in 3 days)
        $mPlan = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Short Plan',
            'duration_type' => 'days',
            'duration_value' => 30,
            'price' => 50.00,
            'is_active' => true,
        ]);

        $expiringMember = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'TITAN-EXP',
            'first_name' => 'Expiring',
            'last_name' => 'Member',
            'phone' => '9811111115',
            'join_date' => now()->subMonth()->toDateString(),
            'status' => 'ACTIVE',
            'dob' => now()->format('1990-m-d'), // Birthday today!
        ]);

        Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $expiringMember->id,
            'membership_plan_id' => $mPlan->id,
            'price' => 50.00,
            'final_amount' => 50.00,
            'paid_amount' => 20.00, // Due fee: 30.00
            'start_date' => now()->subDays(27)->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.dashboard'));
        $response->assertOk();

        $metrics = $response->viewData('metrics');
        $this->assertNotEmpty($metrics['expiring_members_list']);
        $this->assertNotEmpty($metrics['due_members_list']);
        $this->assertNotEmpty($metrics['birthday_members_list']);
    }

    // =========================================================================
    // 3. CRM & GROWTH DASHBOARD (/app/crm & /app/crm/dashboard)
    // =========================================================================

    public function test_crm_dashboard_loads_and_calculates_pipeline_funnel(): void
    {
        // Create Leads in various stages
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'New Lead 1',
            'phone' => '9822222201',
            'source' => 'website',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
            'estimated_value' => 120.00,
        ]);

        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Contacted Lead 2',
            'phone' => '9822222202',
            'source' => 'facebook',
            'stage' => 'CONTACTED',
            'status' => 'IN_PROGRESS',
            'estimated_value' => 200.00,
        ]);

        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Paid Lead 3',
            'phone' => '9822222203',
            'source' => 'walk_in',
            'stage' => 'PAID',
            'status' => 'CONVERTED',
            'estimated_value' => 350.00,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.crm.dashboard'));
        $response->assertOk();
        $response->assertViewIs('app.crm.dashboard');

        $this->assertEquals(3, $response->viewData('totalLeads'));
        $this->assertEquals(1, $response->viewData('pipelineStages')['new_lead']);
        $this->assertEquals(1, $response->viewData('pipelineStages')['contacted']);
        $this->assertEquals(1, $response->viewData('pipelineStages')['paid']);
        $this->assertEquals(350.00, $response->viewData('monthlyMrr'));
        $this->assertEquals(33.3, $response->viewData('conversionRate')); // 1 out of 3 = 33.3%
    }

    public function test_crm_dashboard_tenant_isolation(): void
    {
        // Lead in Tenant A
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Titan Secret Lead',
            'phone' => '9822222204',
            'source' => 'google',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        // Tenant B visits CRM Dashboard
        $respB = $this->actingAs($this->ownerB)->get(route('app.crm.dashboard'));
        $respB->assertOk();
        $this->assertEquals(0, $respB->viewData('totalLeads'), 'Tenant B CRM Dashboard must not show Tenant A leads');
    }

    public function test_crm_reports_page_loads_and_displays_analytics(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('app.crm.reports'));
        $response->assertOk();
        $response->assertViewIs('app.crm.reports');
    }

    // =========================================================================
    // 4. FINANCIAL BALANCE SHEET & EXPENSE DASHBOARD
    // =========================================================================

    public function test_balance_sheet_dashboard_loads_and_calculates_finances(): void
    {
        $mPlan = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Quarterly Plan',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price' => 300.00,
            'is_active' => true,
        ]);

        $member = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'TITAN-FIN-01',
            'first_name' => 'Finance',
            'last_name' => 'Member',
            'phone' => '9833333301',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membership = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_plan_id' => $mPlan->id,
            'price' => 300.00,
            'final_amount' => 300.00,
            'paid_amount' => 200.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonths(3)->toDateString(),
            'status' => 'ACTIVE',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_id' => $membership->id,
            'amount' => 200.00,
            'payment_method' => 'upi',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-FIN-2001',
        ]);

        $cat = ExpenseCategory::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Rent & Electricity',
        ]);

        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat->id,
            'title' => 'Gym Rent',
            'amount' => 80.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet'));
        $response->assertOk();
        $response->assertViewIs('app.finance.balance_sheet');

        $this->assertEquals(200.00, $response->viewData('totalIncome'));
        $this->assertEquals(80.00, $response->viewData('totalExpenses'));
        $this->assertEquals(120.00, $response->viewData('netProfit')); // 200 - 80 = 120
    }

    public function test_balance_sheet_date_filtering(): void
    {
        // Filter by 'today'
        $respToday = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', ['period' => 'today']));
        $respToday->assertOk();

        // Filter by 'this_month'
        $respMonth = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', ['period' => 'this_month']));
        $respMonth->assertOk();

        // Filter by 'this_year'
        $respYear = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', ['period' => 'this_year']));
        $respYear->assertOk();

        // Filter by custom range
        $respCustom = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', [
            'period' => 'custom',
            'start_date' => now()->subDays(7)->toDateString(),
            'end_date' => now()->toDateString(),
        ]));
        $respCustom->assertOk();
    }

    public function test_balance_sheet_pdf_export(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet.pdf'));
        $response->assertOk();
    }

    public function test_expense_report_dashboard_loads(): void
    {
        $response = $this->actingAs($this->ownerA)->get(route('app.finance.expense-report'));
        $response->assertOk();
    }

    // =========================================================================
    // 5. EMPTY STATES ON ALL DASHBOARDS
    // =========================================================================

    public function test_dashboards_empty_state_behavior(): void
    {
        // Tenant B has fresh setup with 0 members, 0 payments, 0 expenses, 0 leads
        // 1. Gym Operations Dashboard Empty State
        $respApp = $this->actingAs($this->ownerB)->get(route('app.dashboard'));
        $respApp->assertOk();
        $metricsB = $respApp->viewData('metrics');
        $this->assertEquals(0, $metricsB['total_members']);
        $this->assertEquals(0.0, $metricsB['today_revenue']);
        $this->assertEquals(0.0, $metricsB['today_expense']);
        $this->assertEquals(0.0, $metricsB['net_profit']);
        $this->assertEmpty($metricsB['expiring_members_list']);
        $this->assertEmpty($metricsB['due_members_list']);
        $this->assertEmpty($metricsB['birthday_members_list']);

        // 2. CRM Dashboard Empty State
        $respCrm = $this->actingAs($this->ownerB)->get(route('app.crm.dashboard'));
        $respCrm->assertOk();
        $this->assertEquals(0, $respCrm->viewData('totalLeads'));
        $this->assertEquals(0.0, $respCrm->viewData('conversionRate'));
        $this->assertEquals(0.0, $respCrm->viewData('monthlyMrr'));

        // 3. Balance Sheet Empty State
        $respFin = $this->actingAs($this->ownerB)->get(route('app.finance.balance-sheet'));
        $respFin->assertOk();
        $this->assertEquals(0.0, $respFin->viewData('totalIncome'));
        $this->assertEquals(0.0, $respFin->viewData('totalExpenses'));
        $this->assertEquals(0.0, $respFin->viewData('netProfit'));
    }

    // =========================================================================
    // 6. UNDERLYING DATA REFLECTION ON DASHBOARDS
    // =========================================================================

    public function test_dashboard_metrics_reflect_live_underlying_data_changes(): void
    {
        // Check initial revenue on Tenant A
        $initialResp = $this->actingAs($this->ownerA)->get(route('app.dashboard'));
        $initialRevenue = $initialResp->viewData('metrics')['month_revenue'];

        // Create new member & payment under Tenant A
        $newMember = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'LIVE-001',
            'first_name' => 'Live',
            'last_name' => 'Test',
            'phone' => '9844444401',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $newMember->id,
            'amount' => 500.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-LIVE-500',
        ]);

        // Re-fetch dashboard and verify updated revenue
        $updatedResp = $this->actingAs($this->ownerA)->get(route('app.dashboard'));
        $updatedRevenue = $updatedResp->viewData('metrics')['month_revenue'];

        $this->assertEquals($initialRevenue + 500.00, $updatedRevenue);
    }

    // =========================================================================
    // 7. DASHBOARD FILTER TILE NAVIGATION & LIST FILTERING
    // =========================================================================

    public function test_dashboard_filter_tile_navigation_targets_exist_and_filter(): void
    {
        // 1. Expiring filter link
        $respExpiring = $this->actingAs($this->ownerA)->get(route('app.members.index', ['filter' => 'expiring']));
        $respExpiring->assertOk();

        // 2. Due filter link
        $respDue = $this->actingAs($this->ownerA)->get(route('app.members.index', ['filter' => 'due']));
        $respDue->assertOk();

        // 3. Birthday filter link
        $respBday = $this->actingAs($this->ownerA)->get(route('app.members.index', ['filter' => 'birthday']));
        $respBday->assertOk();

        // 4. Churn filter link
        $respChurn = $this->actingAs($this->ownerA)->get(route('app.members.index', ['filter' => 'churn']));
        $respChurn->assertOk();

        // 5. Active status filter link
        $respActive = $this->actingAs($this->ownerA)->get(route('app.members.index', ['status' => 'ACTIVE']));
        $respActive->assertOk();

        // 6. Expired status filter link
        $respExpired = $this->actingAs($this->ownerA)->get(route('app.members.index', ['status' => 'EXPIRED']));
        $respExpired->assertOk();
    }
}
