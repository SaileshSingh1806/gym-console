<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\User;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FinanceAndReportsTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected $tenant;

    protected Branch $branch;

    protected function setUp(): void
    {
        parent::setUp();

        $plan = Plan::where('slug', 'pro')->first() ?? Plan::first();
        $tenantService = app(TenantService::class);
        $registration = $tenantService->registerGym([
            'gym_name' => 'Report & Finance Test Gym',
            'email' => 'finance_test@gym.com',
            'owner_name' => 'Finance Tester',
            'password' => 'password',
        ], $plan);

        $this->tenant = $registration['tenant'];
        $this->user = $registration['owner'];
        $this->branch = $registration['branch'];
    }

    public function test_balance_sheet_renders_with_periods(): void
    {
        // Add sample payment and expense
        $member = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_code' => 'MEM-001',
            'first_name' => 'Finance',
            'last_name' => 'Member',
            'phone' => '9123456780',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_id' => $member->id,
            'invoice_number' => 'INV-001',
            'amount' => 5000.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        $category = ExpenseCategory::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Electricity',
        ]);

        Expense::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'expense_category_id' => $category->id,
            'title' => 'Power Bill Sept',
            'amount' => 1200.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'upi',
        ]);

        // Uncategorized expense (category_id = null)
        Expense::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'expense_category_id' => null,
            'title' => 'Gym Rent',
            'amount' => 125000.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'bank_transfer',
        ]);

        // Test month view
        $response = $this->actingAs($this->user)->get(route('app.finance.balance-sheet', ['period' => 'month']));
        $response->assertOk();
        $response->assertSee('Power Bill Sept');
        $response->assertSee('Gym Rent');
        $response->assertSee('125,000.00');
        $response->assertSee('Financial Overview');

        // Test quarter and year views
        $this->actingAs($this->user)->get(route('app.finance.balance-sheet', ['period' => 'quarter']))->assertOk();
        $this->actingAs($this->user)->get(route('app.finance.balance-sheet', ['period' => 'year']))->assertOk();

        // Test PDF statement view
        $pdfResponse = $this->actingAs($this->user)->get(route('app.finance.balance-sheet.pdf', ['period' => 'month']));
        $pdfResponse->assertOk();
        $pdfResponse->assertSee('INCOME AND EXPENDITURE ACCOUNT');
        $pdfResponse->assertSee('Gym Rent');
        $pdfResponse->assertSee('Membership Subscription');
        $pdfResponse->assertSee('Total Income (B)');
    }

    public function test_expense_report_renders_with_member_metrics(): void
    {
        $plan = MembershipPlan::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Gold Annual',
            'price' => 12000.00,
            'duration_months' => 12,
        ]);

        $member = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_code' => 'MEM-002',
            'first_name' => 'Report',
            'last_name' => 'Member 1',
            'phone' => '9888877771',
            'gender' => 'male',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        Membership::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch->id,
            'member_id' => $member->id,
            'membership_plan_id' => $plan->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addDays(5),
            'status' => 'ACTIVE',
            'price' => 12000.00,
            'final_amount' => 12000.00,
        ]);

        $response = $this->actingAs($this->user)->get(route('app.finance.expense-report'));
        $response->assertOk();
        $response->assertSee('Comprehensive membership');
        $response->assertSee('Gold Annual');
        $response->assertSee('Report Member 1');

        // Test alias routes
        $this->actingAs($this->user)->get(route('app.finance.member-report'))->assertOk();
        $this->actingAs($this->user)->get(route('app.reports.index'))->assertOk();

        // Test PDF statement view
        $pdfResponse = $this->actingAs($this->user)->get(route('app.finance.member-report.pdf'));
        $pdfResponse->assertOk();
        $pdfResponse->assertSee('Key Metrics');
        $pdfResponse->assertSee('Attendance Summary');
        $pdfResponse->assertSee('Membership Breakdown');
        $pdfResponse->assertSee('Demographics');
        $pdfResponse->assertSee('Age Groups');
        $pdfResponse->assertSee('Gold Annual');
        $pdfResponse->assertSee('Report Member 1');
    }

    public function test_expenses_crud_lifecycle(): void
    {
        // 1. Create Category
        $catResponse = $this->actingAs($this->user)->post(route('app.expenses.categories.store'), [
            'name' => 'Equipment Maintenance',
        ]);
        $catResponse->assertRedirect();
        $this->assertDatabaseHas('expense_categories', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Equipment Maintenance',
        ]);

        $category = ExpenseCategory::where('tenant_id', $this->tenant->id)->first();

        // 2. Create Expense
        $expResponse = $this->actingAs($this->user)->post(route('app.expenses.store'), [
            'title' => 'Treadmill Belt Replacement',
            'amount' => 3500.00,
            'expense_date' => now()->toDateString(),
            'expense_category_id' => $category->id,
            'payment_method' => 'bank_transfer',
            'branch_id' => $this->branch->id,
            'notes' => 'Replaced by Technogym service engineer',
        ]);
        $expResponse->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'tenant_id' => $this->tenant->id,
            'title' => 'Treadmill Belt Replacement',
            'amount' => 3500.00,
        ]);

        $expense = Expense::where('tenant_id', $this->tenant->id)->first();

        // 3. View Expenses Index
        $indexResponse = $this->actingAs($this->user)->get(route('app.expenses.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Treadmill Belt Replacement');
        $indexResponse->assertSee('Equipment Maintenance');

        // 4. Delete Expense
        $delResponse = $this->actingAs($this->user)->delete(route('app.expenses.delete', $expense->id));
        $delResponse->assertRedirect();
        $this->assertDatabaseMissing('expenses', [
            'id' => $expense->id,
        ]);
    }
}
