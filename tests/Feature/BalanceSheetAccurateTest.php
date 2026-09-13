<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BalanceSheetAccurateTest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected Tenant $tenant;

    protected Branch $branch1;

    protected Branch $branch2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Finance Test Gym '.rand(1000, 9999),
            'slug' => 'fingym'.rand(1000, 9999),
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);
        $this->attachProSubscription($this->tenant);

        $this->branch1 = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Branch Alpha',
            'code' => 'ALPHA',
            'is_main' => true,
        ]);

        $this->branch2 = Branch::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Branch Beta',
            'code' => 'BETA',
            'is_main' => false,
        ]);

        $this->user = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Finance Admin',
            'email' => 'fin_admin@gym.com',
            'password' => bcrypt('password123'),
            'role' => 'gym_owner',
            'is_owner' => true,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_balance_sheet_has_no_fake_pos_sales_and_scopes_to_branch(): void
    {
        // Member on Branch Alpha
        $memberAlpha = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'first_name' => 'Alpha',
            'last_name' => 'User',
            'phone' => '9111111111',
            'member_code' => 'ALPH01',
            'status' => 'ACTIVE',
            'join_date' => now()->toDateString(),
        ]);

        // Member on Branch Beta
        $memberBeta = Member::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'first_name' => 'Beta',
            'last_name' => 'User',
            'phone' => '9222222222',
            'member_code' => 'BETA01',
            'status' => 'ACTIVE',
            'join_date' => now()->toDateString(),
        ]);

        // Alpha payment: 5000
        MemberPayment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch1->id,
            'member_id' => $memberAlpha->id,
            'invoice_number' => 'INV-TEST-001',
            'amount' => 5000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // Beta payment: 3000
        MemberPayment::create([
            'tenant_id' => $this->tenant->id,
            'branch_id' => $this->branch2->id,
            'member_id' => $memberBeta->id,
            'invoice_number' => 'INV-TEST-002',
            'amount' => 3000,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ]);

        // Check Branch Alpha view
        $responseAlpha = $this->actingAs($this->user)->get(route('app.finance.balance-sheet', [
            'branch_id' => $this->branch1->id,
        ]));

        $responseAlpha->assertStatus(200);
        $responseAlpha->assertViewHas('posSales', 0.0);
        $responseAlpha->assertViewHas('membershipIncome', 5000.0);
        $responseAlpha->assertViewHas('totalIncome', 5000.0);

        // Check Branch Beta view
        $responseBeta = $this->actingAs($this->user)->get(route('app.finance.balance-sheet', [
            'branch_id' => $this->branch2->id,
        ]));

        $responseBeta->assertStatus(200);
        $responseBeta->assertViewHas('posSales', 0.0);
        $responseBeta->assertViewHas('membershipIncome', 3000.0);
        $responseBeta->assertViewHas('totalIncome', 3000.0);

        // Check All Branches view
        $responseAll = $this->actingAs($this->user)->get(route('app.finance.balance-sheet', [
            'branch_id' => 'all',
        ]));

        $responseAll->assertStatus(200);
        $responseAll->assertViewHas('posSales', 0.0);
        $responseAll->assertViewHas('membershipIncome', 8000.0);
        $responseAll->assertViewHas('totalIncome', 8000.0);
    }
}
