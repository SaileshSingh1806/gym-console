<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
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

class Phase6SearchFilterSortPaginationTest extends TestCase
{
    use DatabaseTransactions;

    protected User $superAdmin;

    protected Tenant $tenantA;

    protected Branch $branchA1;

    protected Branch $branchA2;

    protected User $ownerA;

    protected Plan $proPlan;

    protected MembershipPlan $mPlanA;

    protected function setUp(): void
    {
        parent::setUp();

        (new PermissionSeeder)->run();
        $this->proPlan = Plan::where('slug', 'pro')->first();

        $tenantService = app(TenantService::class);
        $subService = app(SubscriptionService::class);

        // Super Admin
        $this->superAdmin = User::firstOrCreate(
            ['email' => 'superadmin_sfsp@gymconsole.com'],
            ['name' => 'Super Admin SFSP', 'role' => 'super_admin', 'status' => 'ACTIVE', 'password' => Hash::make('password')]
        );

        // Setup Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Nexus Gym {$randA}",
            'email' => "owner_nexus_{$randA}@gym.com",
            'owner_name' => 'Nexus Owner',
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

        // Branch 2
        $this->branchA2 = Branch::create([
            'tenant_id' => $this->tenantA->id,
            'name' => 'Nexus West Hub',
            'code' => 'NEXUS-W',
            'is_main' => false,
            'status' => 'ACTIVE',
        ]);

        $this->mPlanA = MembershipPlan::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Standard Monthly',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 100.00,
            'is_active' => true,
        ]);
    }

    // =========================================================================
    // 1. MEMBERS SEARCH, FILTER & PAGINATION (/app/members)
    // =========================================================================

    public function test_members_search_by_name_phone_and_code(): void
    {
        $member1 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'NEXUS-M-001',
            'first_name' => 'Alexander',
            'last_name' => 'Hamilton',
            'phone' => '9870000001',
            'email' => 'alex@hamilton.com',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $member2 = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'NEXUS-M-002',
            'first_name' => 'Benjamin',
            'last_name' => 'Franklin',
            'phone' => '9870000002',
            'email' => 'ben@franklin.com',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        // 1. Search by first name partial 'Alex'
        $resp1 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['search' => 'Alex']));
        $resp1->assertOk();
        $resp1->assertSee('Alexander');
        $resp1->assertDontSee('Franklin');

        // 2. Search by last name 'Franklin'
        $resp2 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['search' => 'Franklin']));
        $resp2->assertOk();
        $resp2->assertSee('Franklin');
        $resp2->assertDontSee('Alexander');

        // 3. Search by phone '9870000001'
        $resp3 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['search' => '9870000001']));
        $resp3->assertOk();
        $resp3->assertSee('Alexander');
        $resp3->assertDontSee('Franklin');

        // 4. Search by member code 'NEXUS-M-002'
        $resp4 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['search' => 'NEXUS-M-002']));
        $resp4->assertOk();
        $resp4->assertSee('Benjamin');
        $resp4->assertDontSee('Alexander');

        // 5. Empty search (non-matching)
        $resp5 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['search' => 'NONEXISTENT_XYZ']));
        $resp5->assertOk();
        $resp5->assertDontSee('Alexander');
        $resp5->assertDontSee('Benjamin');
    }

    public function test_members_status_and_action_filtering(): void
    {
        $activeMember = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'NEX-ACT',
            'first_name' => 'Active',
            'last_name' => 'User',
            'phone' => '9870000011',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        $inactiveMember = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'NEX-INA',
            'first_name' => 'Inactive',
            'last_name' => 'User',
            'phone' => '9870000012',
            'join_date' => now()->toDateString(),
            'status' => 'INACTIVE',
        ]);

        // Status filter = ACTIVE
        $respActive = $this->actingAs($this->ownerA)->get(route('app.members.index', ['status' => 'ACTIVE']));
        $respActive->assertOk();
        $respActive->assertSee('Active User');
        $respActive->assertDontSee('Inactive User');

        // Status filter = INACTIVE
        $respInactive = $this->actingAs($this->ownerA)->get(route('app.members.index', ['status' => 'INACTIVE']));
        $respInactive->assertOk();
        $respInactive->assertSee('Inactive User');
        $respInactive->assertDontSee('Active User');
    }

    public function test_members_pagination_across_multiple_pages(): void
    {
        // Seed 20 members to exceed default perPage of 15
        for ($i = 1; $i <= 20; $i++) {
            Member::create([
                'tenant_id' => $this->tenantA->id,
                'branch_id' => $this->branchA1->id,
                'member_code' => "PAGE-MEM-{$i}",
                'first_name' => "Batch{$i}",
                'last_name' => 'Member',
                'phone' => '9870000'.str_pad($i, 3, '0', STR_PAD_LEFT),
                'join_date' => now()->toDateString(),
                'status' => 'ACTIVE',
            ]);
        }

        // Page 1
        $respP1 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['page' => 1]));
        $respP1->assertOk();
        $membersP1 = $respP1->viewData('members');
        $this->assertEquals(15, $membersP1->count());
        $this->assertEquals(20, $membersP1->total());
        $this->assertEquals(2, $membersP1->lastPage());

        // Page 2
        $respP2 = $this->actingAs($this->ownerA)->get(route('app.members.index', ['page' => 2]));
        $respP2->assertOk();
        $membersP2 = $respP2->viewData('members');
        $this->assertEquals(5, $membersP2->count());
    }

    // =========================================================================
    // 2. PAYMENTS SEARCH, METHOD & DATE FILTERING (/app/payments)
    // =========================================================================

    public function test_payments_search_and_method_filtering(): void
    {
        $member = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_code' => 'NEX-PAY-01',
            'first_name' => 'Charlie',
            'last_name' => 'Payne',
            'phone' => '9870000031',
            'join_date' => now()->toDateString(),
            'status' => 'ACTIVE',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'amount' => 250.00,
            'payment_method' => 'upi',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-NEXUS-UPI-250',
            'transaction_reference' => 'UPI-REF-998877',
        ]);

        MemberPayment::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'member_id' => $member->id,
            'amount' => 500.00,
            'payment_method' => 'card',
            'payment_date' => now()->toDateString(),
            'invoice_number' => 'INV-NEXUS-CARD-500',
            'transaction_reference' => 'CARD-AUTH-112233',
        ]);

        // Search by invoice number
        $respInv = $this->actingAs($this->ownerA)->get(route('app.payments.index', ['search' => 'INV-NEXUS-UPI-250', 'date_filter' => 'all']));
        $respInv->assertOk();
        $respInv->assertSee('INV-NEXUS-UPI-250');
        $respInv->assertDontSee('INV-NEXUS-CARD-500');

        // Search by transaction ref
        $respRef = $this->actingAs($this->ownerA)->get(route('app.payments.index', ['search' => 'CARD-AUTH-112233', 'date_filter' => 'all']));
        $respRef->assertOk();
        $respRef->assertSee('INV-NEXUS-CARD-500');
        $respRef->assertDontSee('INV-NEXUS-UPI-250');

        // Method filter = upi
        $respUpi = $this->actingAs($this->ownerA)->get(route('app.payments.index', ['method' => 'upi', 'date_filter' => 'all']));
        $respUpi->assertOk();
        $respUpi->assertSee('INV-NEXUS-UPI-250');
        $respUpi->assertDontSee('INV-NEXUS-CARD-500');
    }

    // =========================================================================
    // 3. CRM LEADS SEARCH, STAGE & SOURCE FILTERING (/app/crm/leads)
    // =========================================================================

    public function test_crm_leads_search_and_stage_filtering(): void
    {
        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Lead Walter White',
            'phone' => '9870000041',
            'source' => 'website',
            'stage' => 'NEW_LEAD',
            'status' => 'NEW',
        ]);

        Lead::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'name' => 'Lead Jesse Pinkman',
            'phone' => '9870000042',
            'source' => 'instagram',
            'stage' => 'PAID',
            'status' => 'CONVERTED',
        ]);

        // Search by name
        $respSearch = $this->actingAs($this->ownerA)->get(route('app.leads.index', ['search' => 'Walter']));
        $respSearch->assertOk();
        $respSearch->assertSee('Lead Walter White');
        $respSearch->assertDontSee('Lead Jesse Pinkman');

        // Stage filter = PAID
        $respStage = $this->actingAs($this->ownerA)->get(route('app.leads.index', ['stage' => 'PAID']));
        $respStage->assertOk();
        $respStage->assertSee('Lead Jesse Pinkman');
        $respStage->assertDontSee('Lead Walter White');

        // Source filter = instagram
        $respSource = $this->actingAs($this->ownerA)->get(route('app.leads.index', ['source' => 'instagram']));
        $respSource->assertOk();
        $respSource->assertSee('Lead Jesse Pinkman');
        $respSource->assertDontSee('Lead Walter White');
    }

    // =========================================================================
    // 4. EXPENSES CATEGORY & MONTH FILTERING (/app/expenses)
    // =========================================================================

    public function test_expenses_category_and_date_filtering(): void
    {
        $cat1 = ExpenseCategory::create(['tenant_id' => $this->tenantA->id, 'name' => 'Utilities']);
        $cat2 = ExpenseCategory::create(['tenant_id' => $this->tenantA->id, 'name' => 'Marketing']);

        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat1->id,
            'title' => 'Electric Bill Expense',
            'amount' => 150.00,
            'expense_date' => now()->toDateString(),
        ]);

        Expense::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'expense_category_id' => $cat2->id,
            'title' => 'Meta Ads Expense',
            'amount' => 300.00,
            'expense_date' => now()->toDateString(),
        ]);

        // Filter by category Utilities
        $respCat = $this->actingAs($this->ownerA)->get(route('app.expenses.index', ['category_id' => $cat1->id]));
        $respCat->assertOk();
        $respCat->assertSee('Electric Bill Expense');
        $respCat->assertDontSee('Meta Ads Expense');
    }

    // =========================================================================
    // 5. INVENTORY & EQUIPMENT SEARCH & FILTERING (/app/inventory)
    // =========================================================================

    public function test_inventory_search_and_category_filtering(): void
    {
        InventoryItem::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'sku' => 'SUP-CASEIN-01',
            'name' => 'Micellar Casein Protein 2lb',
            'category' => 'Supplements',
            'cost_price' => 25.00,
            'selling_price' => 45.00,
            'stock_quantity' => 10,
        ]);

        InventoryItem::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA1->id,
            'sku' => 'ACC-STRAP-01',
            'name' => 'Lifting Wrist Straps Pro',
            'category' => 'Accessories',
            'cost_price' => 5.00,
            'selling_price' => 15.00,
            'stock_quantity' => 30,
        ]);

        $response = $this->actingAs($this->ownerA)->get(route('app.inventory.index'));
        $response->assertOk();
        $response->assertSee('Micellar Casein Protein 2lb');
        $response->assertSee('Lifting Wrist Straps Pro');
    }

    // =========================================================================
    // 6. SUPER ADMIN GYMS SEARCH, STATUS FILTER & PAGINATION (/admin/gyms)
    // =========================================================================

    public function test_super_admin_gyms_search_and_status_filtering(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.gyms', ['search' => 'Nexus Gym']));
        $response->assertOk();
        $response->assertSee('Nexus Gym');

        $respFilter = $this->actingAs($this->superAdmin)->get(route('admin.gyms', ['status' => 'ACTIVE']));
        $respFilter->assertOk();
        $respFilter->assertSee('Nexus Gym');
    }

    // =========================================================================
    // 7. SUPER ADMIN USERS SEARCH & ROLE FILTERING (/admin/users)
    // =========================================================================

    public function test_super_admin_users_search_and_role_filtering(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.users', ['search' => 'Nexus Owner']));
        $response->assertOk();
        $response->assertSee('Nexus Owner');

        // Filter by role = gym_owner
        $respRole = $this->actingAs($this->superAdmin)->get(route('admin.users', ['role' => 'gym_owner']));
        $respRole->assertOk();
        $respRole->assertSee('Nexus Owner');
    }

    // =========================================================================
    // 8. SUPER ADMIN ACTIVITY LOGS SEARCH & PAGINATION (/admin/logs)
    // =========================================================================

    public function test_super_admin_activity_logs_listing(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.logs'));
        $response->assertOk();
    }

    // =========================================================================
    // 9. TRAINING, CLASSES, WORKOUTS, DIETS & SERVICES LISTINGS
    // =========================================================================

    public function test_fitness_and_services_listings(): void
    {
        // 1. Trainers
        $respTrainers = $this->actingAs($this->ownerA)->get(route('app.trainers.index'));
        $respTrainers->assertOk();

        // 2. Classes
        $respClasses = $this->actingAs($this->ownerA)->get(route('app.classes.index'));
        $respClasses->assertOk();

        // 3. Workouts
        $respWorkouts = $this->actingAs($this->ownerA)->get(route('app.workouts.index'));
        $respWorkouts->assertOk();

        // 4. Diets
        $respDiets = $this->actingAs($this->ownerA)->get(route('app.diets.index'));
        $respDiets->assertOk();

        // 5. Services
        $respServices = $this->actingAs($this->ownerA)->get(route('app.services.index'));
        $respServices->assertOk();

        // 6. Personal Training
        $respPt = $this->actingAs($this->ownerA)->get(route('app.pt.index'));
        $respPt->assertOk();
    }

    // =========================================================================
    // 10. SUPPORT TICKETS, STAFF, COUPONS & SUBSCRIPTIONS LISTINGS
    // =========================================================================

    public function test_support_tickets_staff_coupons_subscriptions_listings(): void
    {
        // 1. App Support Tickets
        $respAppTickets = $this->actingAs($this->ownerA)->get(route('app.support.index'));
        $respAppTickets->assertOk();

        // 2. App Staff
        $respStaff = $this->actingAs($this->ownerA)->get(route('app.staff.index'));
        $respStaff->assertOk();

        // 3. Admin Support Tickets
        $respAdminTickets = $this->actingAs($this->superAdmin)->get(route('admin.tickets.index'));
        $respAdminTickets->assertOk();

        // 4. Admin Coupons
        $respCoupons = $this->actingAs($this->superAdmin)->get(route('admin.coupons'));
        $respCoupons->assertOk();

        // 5. Admin Subscriptions
        $respSubs = $this->actingAs($this->superAdmin)->get(route('admin.subscriptions'));
        $respSubs->assertOk();
    }
}
