<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminPlatformUsersFilterTest extends TestCase
{
    use DatabaseTransactions;

    public function test_super_admin_can_filter_users_by_role_and_view_all_roles(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin.filter@gymconsole.test',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $tenant = Tenant::first() ?? Tenant::create([
            'name' => 'Filter Test Gym',
            'slug' => 'filtergym',
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);

        $owner = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Gym Owner Filter Test',
            'email' => 'owner.filter@test.com',
            'password' => bcrypt('password123'),
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        $trainer = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Trainer Filter Test',
            'email' => 'trainer.filter@test.com',
            'password' => bcrypt('password123'),
            'role' => 'trainer',
            'status' => 'ACTIVE',
        ]);

        // 1. Initial page load (no role query param) -> defaults to gym_owner
        $responseDefault = $this->actingAs($superAdmin)->get(route('admin.users'));
        $responseDefault->assertStatus(200);
        $responseDefault->assertSee('Gym Owner Filter Test');
        $responseDefault->assertDontSee('Trainer Filter Test');

        // 2. All Roles view (role='') -> should see both Gym Owner and Trainer
        $responseAll = $this->actingAs($superAdmin)->get(route('admin.users', ['role' => '']));
        $responseAll->assertStatus(200);
        $responseAll->assertSee('Gym Owner Filter Test');
        $responseAll->assertSee('Trainer Filter Test');

        // 3. Filter by gym_owner -> should only see Gym Owner, not Trainer
        $responseOwner = $this->actingAs($superAdmin)->get(route('admin.users', ['role' => 'gym_owner']));
        $responseOwner->assertStatus(200);
        $responseOwner->assertSee('Gym Owner Filter Test');
        $responseOwner->assertDontSee('Trainer Filter Test');

        // 4. Filter by trainer -> should only see Trainer, not Gym Owner
        $responseTrainer = $this->actingAs($superAdmin)->get(route('admin.users', ['role' => 'trainer']));
        $responseTrainer->assertStatus(200);
        $responseTrainer->assertSee('Trainer Filter Test');
        $responseTrainer->assertDontSee('Gym Owner Filter Test');
    }

    public function test_super_admin_subscriptions_page_loads_with_plan_details(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin.subtest@gymconsole.test',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.subscriptions'));
        $response->assertStatus(200);
        $response->assertSee('Tenant SaaS Subscriptions');
        $response->assertSee('Subscribed Plan & Inclusions', false);
        $response->assertSee('Generated SaaS Platform Invoices');
    }

    public function test_super_admin_can_modify_subscription_and_sync_invoice_amount(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin.modifytest@gymconsole.test',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $tenant = Tenant::create([
            'name' => 'Modify Sub Test Gym',
            'slug' => 'modifysubgym',
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);

        $planA = Plan::create([
            'name' => 'Starter Plan Test',
            'slug' => 'starter-test-'.uniqid(),
            'price_monthly' => 500,
            'price_yearly' => 6000,
            'is_active' => true,
        ]);

        $planB = Plan::create([
            'name' => 'Pro Plan Test',
            'slug' => 'pro-test-'.uniqid(),
            'price_monthly' => 1000,
            'price_yearly' => 12000,
            'is_active' => true,
        ]);

        $subscription = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $planA->id,
            'billing_cycle' => 'yearly',
            'status' => 'ACTIVE',
            'starts_at' => now(),
            'ends_at' => now()->addYear(),
        ]);

        $invoice = PlatformInvoice::create([
            'tenant_id' => $tenant->id,
            'subscription_id' => $subscription->id,
            'invoice_number' => 'INV-TEST-001',
            'subtotal' => 6000,
            'discount' => 0,
            'tax' => 0,
            'total' => 6000,
            'currency' => 'INR',
            'status' => 'PAID',
            'invoice_date' => now()->toDateString(),
        ]);

        // Modify subscription to Plan B (12,000 yearly)
        $response = $this->actingAs($superAdmin)->post(route('admin.subscriptions.update', $subscription->id), [
            'status' => 'ACTIVE',
            'plan_id' => $planB->id,
            'billing_cycle' => 'yearly',
            'ends_at' => now()->addYear()->toDateString(),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $subscription->refresh();
        $invoice->refresh();

        $this->assertEquals($planB->id, $subscription->plan_id);
        $this->assertEquals(12000, (float) $invoice->total);
    }

    public function test_super_admin_gyms_page_loads_successfully_with_metrics(): void
    {
        $superAdmin = User::where('role', 'super_admin')->first() ?? User::create([
            'name' => 'Super Admin Test',
            'email' => 'superadmin.gymstest@gymconsole.test',
            'password' => bcrypt('password123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($superAdmin)->get(route('admin.gyms'));
        $response->assertStatus(200);
        $response->assertSee('Gym Businesses & Tenant Control');
        $response->assertSee('Total Platform Members');
        $response->assertSee('Contracted SaaS Value');
    }
}
