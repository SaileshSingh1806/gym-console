<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymEquipment;
use App\Models\InventoryItem;
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

class Phase5AllReportsTest extends TestCase
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

    protected User $accountantA;

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
            ['email' => 'superadmin_rep@gymconsole.com'],
            ['name' => 'Super Admin Reports', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Setup Tenant A (Active Pro Plan)
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Apex Gym {$randA}",
            'email' => "owner_apex_{$randA}@gym.com",
            'owner_name' => 'Apex Owner',
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

        // Branch 2 for Tenant A
        $this->branchA2 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Apex East Studio',
            'code' => 'APEX-E',
            'is_main' => false,
            'status' => 'ACTIVE',
        ]);

        // Setup Tenant B
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Vanguard Gym {$randB}",
            'email' => "owner_vanguard_{$randB}@gym.com",
            'owner_name' => 'Vanguard Owner',
            'password' => 'password',
        ], $this->proPlan);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['owner'];
        $this->branchB = $regB['branch'];

        // Users
        $this->accountantA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Accountant Aaron',
            'email' => "acc_apex_{$randA}@gym.com",
            'role' => 'accountant',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->staffA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Staff Simon',
            'email' => "staff_apex_{$randA}@gym.com",
            'role' => 'staff',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);

        $this->memberUserA = User::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Member Mark',
            'email' => "member_apex_{$randA}@gym.com",
            'role' => 'member',
            'status' => 'ACTIVE',
            'password' => Hash::make('password'),
        ]);
    }

    // =========================================================================
    // 1. FINANCIAL BALANCE SHEET & P&L REPORT
    // =========================================================================

    public function test_balance_sheet_report_loads_and_calculates_correctly(): void
    {
        $mPlan = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'VIP Annual',
            'duration_type' => 'years',
            'duration_value' => 1,
            'price' => 1200.00,
            'is_active' => true,
        ]);

        $member = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-REP-01',
            'first_name' => 'Arthur',
            'last_name' => 'Dent',
            'phone' => '9855555501',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membership = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_plan_id' => $mPlan->id,
            'price' => 1200.00,
            'final_amount' => 1200.00,
            'paid_amount' => 1200.00,
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->addYear()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_id' => $membership->id,
            'amount' => 1200.00,
            'payment_method' => 'card',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-APEX-1200',
        ]);

        $cat = ExpenseCategory::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Equipment Leasing',
        ]);

        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat->id,
            'title' => 'Treadmills Lease',
            'amount' => 400.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        // 1. Web View
        $response = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet'));
        $response->assertOk();
        $response->assertViewIs('app.finance.balance_sheet');
        $this->assertEquals(1200.00, $response->viewData('totalIncome'));
        $this->assertEquals(400.00, $response->viewData('totalExpenses'));
        $this->assertEquals(800.00, $response->viewData('netProfit'));

        // 2. PDF View
        $pdfResp = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet.pdf'));
        $pdfResp->assertOk();
        $pdfResp->assertViewIs('app.finance.balance_sheet_pdf');
        $this->assertEquals(1200.00, $pdfResp->viewData('totalIncome'));
        $this->assertEquals(400.00, $pdfResp->viewData('totalExpenses'));
        $this->assertEquals(800.00, $pdfResp->viewData('netProfit'));
    }

    public function test_balance_sheet_report_branch_filtering(): void
    {
        $member1 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-BF-01',
            'first_name' => 'Branch1',
            'last_name' => 'Member',
            'phone' => '9855555511',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $member2 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'member_code' => 'APEX-BF-02',
            'first_name' => 'Branch2',
            'last_name' => 'Member',
            'phone' => '9855555512',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // Branch A1: Income 600, Expense 100
        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member1->id,
            'amount' => 600.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-APEX-B1',
        ]);
        $cat = ExpenseCategory::create(['tenant_id' => $this->tenantA->id, 'name' => 'General']);
        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat->id,
            'title' => 'Branch 1 Water',
            'amount' => 100.00,
            'expense_date' => now()->toDateString(),
        ]);

        // Branch A2: Income 900, Expense 200
        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'member_id' => $member2->id,
            'amount' => 900.00,
            'payment_method' => 'cash',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-APEX-B2',
        ]);
        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA2->id,
            'expense_category_id' => $cat->id,
            'title' => 'Branch 2 Water',
            'amount' => 200.00,
            'expense_date' => now()->toDateString(),
        ]);

        // Request filtered to Branch A1
        $respB1 = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', ['branch_id' => $this->branchA1->id]));
        $respB1->assertOk();
        $this->assertEquals(600.00, $respB1->viewData('totalIncome'));
        $this->assertEquals(100.00, $respB1->viewData('totalExpenses'));
        $this->assertEquals(500.00, $respB1->viewData('netProfit'));

        // Request filtered to Branch A2
        $respB2 = $this->actingAs($this->ownerA)->get(route('app.finance.balance-sheet', ['branch_id' => $this->branchA2->id]));
        $respB2->assertOk();
        $this->assertEquals(900.00, $respB2->viewData('totalIncome'));
        $this->assertEquals(200.00, $respB2->viewData('totalExpenses'));
        $this->assertEquals(700.00, $respB2->viewData('netProfit'));
    }

    // =========================================================================
    // 2. MEMBER REPORT (DEMOGRAPHICS, ATTENDANCE & PLANS)
    // =========================================================================

    public function test_member_report_loads_and_generates_pdf(): void
    {
        Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-MEM-M',
            'first_name' => 'Male',
            'last_name' => 'Member',
            'gender' => 'male',
            'phone' => '9855555502',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-MEM-F',
            'first_name' => 'Female',
            'last_name' => 'Member',
            'gender' => 'female',
            'phone' => '9855555503',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // 1. Web View
        $resp = $this->actingAs($this->ownerA)->get(route('app.finance.member-report'));
        $resp->assertOk();
        $resp->assertViewIs('app.finance.expense_report');
        $this->assertEquals(2, $resp->viewData('totalMembers'));
        $this->assertEquals(1, $resp->viewData('maleCount'));
        $this->assertEquals(1, $resp->viewData('femaleCount'));

        // 2. PDF View
        $respPdf = $this->actingAs($this->ownerA)->get(route('app.finance.member-report.pdf'));
        $respPdf->assertOk();
        $respPdf->assertViewIs('app.finance.member_report_pdf');
        $this->assertEquals(2, $respPdf->viewData('totalMembers'));
        $this->assertEquals(1, $respPdf->viewData('maleCount'));
        $this->assertEquals(1, $respPdf->viewData('femaleCount'));
    }

    // =========================================================================
    // 3. CRM CONVERSION & LEAD PERFORMANCE REPORT & CSV EXPORT
    // =========================================================================

    public function test_crm_reports_page_and_csv_export(): void
    {
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Report Lead Alpha',
            'phone' => '9866666601',
            'email' => 'alpha_lead@report.com',
            'source' => 'instagram',
            'stage' => 'PAID',
            'status' => 'CONVERTED',
            'estimated_value' => 300.00,
        ]);

        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Report Lead Beta',
            'phone' => '9866666602',
            'email' => 'beta_lead@report.com',
            'source' => 'walk_in',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
            'estimated_value' => 150.00,
        ]);

        // 1. Web View
        $response = $this->actingAs($this->ownerA)->get(route('app.crm.reports'));
        $response->assertOk();
        $response->assertViewIs('app.crm.reports');
        $this->assertEquals(2, $response->viewData('totalLeads'));
        $this->assertEquals(1, $response->viewData('convertedCount'));
        $this->assertEquals(50.0, $response->viewData('conversionRate'));
        $this->assertEquals(300.00, $response->viewData('totalConversionValue'));

        // 2. CSV Export
        $csvResponse = $this->actingAs($this->ownerA)->get(route('app.crm.reports', ['export' => 'csv']));
        $csvResponse->assertOk();
        $this->assertStringContainsString('text/csv', (string) $csvResponse->headers->get('content-type'));

        ob_start();
        $csvResponse->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('Report Lead Alpha', $csvContent);
        $this->assertStringContainsString('Report Lead Beta', $csvContent);
        $this->assertStringContainsString('alpha_lead@report.com', $csvContent);
    }

    public function test_crm_leads_csv_export(): void
    {
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Export Lead One',
            'phone' => '9866666603',
            'source' => 'facebook',
            'stage' => 'CONTACTED',
            'status' => 'IN_PROGRESS',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.leads.index', ['export' => 'csv']));
        $response->assertOk();

        ob_start();
        $response->sendContent();
        $csvContent = ob_get_clean();

        $this->assertStringContainsString('Export Lead One', $csvContent);
        $this->assertStringContainsString('9866666603', $csvContent);
    }

    // =========================================================================
    // 4. MEMBER LEDGER & INVOICE / RECEIPT REPORT
    // =========================================================================

    public function test_member_ledger_profile_report_and_invoice(): void
    {
        $mPlan = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Gold Monthly',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 150.00,
            'is_active' => true,
        ]);

        $member = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-LEDGER-01',
            'first_name' => 'Ledger',
            'last_name' => 'Hero',
            'phone' => '9877777701',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $membership = Membership::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_plan_id' => $mPlan->id,
            'price' => 150.00,
            'final_amount' => 150.00,
            'paid_amount' => 150.00,
            'start_date' => now()->toDateString(),
            'end_date' => now()->addMonth()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $payment = MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'membership_id' => $membership->id,
            'amount' => 150.00,
            'payment_method' => 'upi',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-APEX-LEDGER-150',
        ]);

        // 1. Member Ledger Profile View
        $respShow = $this->actingAs($this->ownerA)->get(route('app.members.show', $member->id));
        $respShow->assertOk();
        $respShow->assertSee('INV-APEX-LEDGER-150');
        $respShow->assertSee('Gold Monthly');

        // 2. Invoice / Receipt Printable View
        $respInv = $this->actingAs($this->ownerA)->get(route('app.invoices.show', $payment->id));
        $respInv->assertOk();
        $respInv->assertSee('INV-APEX-LEDGER-150');
        $respInv->assertSee('150.00');
    }

    // =========================================================================
    // 5. ATTENDANCE & ACCESS LOG REPORT
    // =========================================================================

    public function test_attendance_history_report(): void
    {
        $member = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-ATT-01',
            'first_name' => 'Attendance',
            'last_name' => 'Tester',
            'phone' => '9888888801',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        Attendance::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'date' => now()->toDateString(),
            'check_in' => now()->subHours(2),
            'check_out' => now()->subHour(),
            'method' => 'manual',
            'status' => 'PRESENT',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.attendance.index', ['date' => now()->toDateString()]));
        $response->assertOk();
        $response->assertSee('Attendance Tester');
        $response->assertSee('APEX-ATT-01');
    }

    // =========================================================================
    // 6. INVENTORY STOCK VALUATION & EQUIPMENT MAINTENANCE REPORT
    // =========================================================================

    public function test_inventory_and_equipment_report(): void
    {
        InventoryItem::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'sku' => 'APEX-PROT-01',
            'name' => 'Apex Hydro Whey 2kg',
            'cost_price' => 30.00,
            'selling_price' => 50.00,
            'stock_quantity' => 15,
        ]);

        GymEquipment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Apex Lat Pulldown Machine',
            'status' => 'OPERATIONAL',
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.inventory.index'));
        $response->assertOk();
        $response->assertSee('Apex Hydro Whey 2kg');
        $response->assertSee('Apex Lat Pulldown Machine');
    }

    // =========================================================================
    // 7. SUPER ADMIN PLATFORM AUDIT & SUBSCRIPTION REPORTS
    // =========================================================================

    public function test_super_admin_platform_reports(): void
    {
        // 1. Audit / Activity Logs Report
        $respLogs = $this->actingAs($this->superAdmin)->get(route('admin.logs'));
        $respLogs->assertOk();

        // 2. Subscriptions & Platform Invoices Report
        $respSubs = $this->actingAs($this->superAdmin)->get(route('admin.subscriptions'));
        $respSubs->assertOk();

        // 3. Gym Businesses Audit Directory
        $respGyms = $this->actingAs($this->superAdmin)->get(route('admin.gyms'));
        $respGyms->assertOk();
    }

    // =========================================================================
    // 8. CROSS-TENANT ISOLATION ON ALL REPORTS & EXPORTS
    // =========================================================================

    public function test_cross_tenant_isolation_on_all_reports_and_exports(): void
    {
        // Lead in Tenant A
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Tenant A Super Secret Lead',
            'phone' => '9899999901',
            'source' => 'website',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        // Member in Tenant A
        Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'APEX-ISO-99',
            'first_name' => 'Secret',
            'last_name' => 'MemberA',
            'phone' => '9899999902',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // 1. Tenant B CRM Report
        $respCrmB = $this->actingAs($this->ownerB)->get(route('app.crm.reports'));
        $respCrmB->assertOk();
        $this->assertEquals(0, $respCrmB->viewData('totalLeads'), 'Tenant B CRM report should have 0 leads');

        // 2. Tenant B CSV Export
        $csvRespB = $this->actingAs($this->ownerB)->get(route('app.crm.reports', ['export' => 'csv']));
        $csvRespB->assertOk();
        ob_start();
        $csvRespB->sendContent();
        $csvB = ob_get_clean();
        $this->assertStringNotContainsString('Tenant A Super Secret Lead', $csvB);

        // 3. Tenant B Member Report
        $respMemB = $this->actingAs($this->ownerB)->get(route('app.finance.member-report'));
        $respMemB->assertOk();
        $this->assertEquals(0, $respMemB->viewData('totalMembers'), 'Tenant B Member report should have 0 members');
    }
}
