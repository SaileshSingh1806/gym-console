<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\SupportTicket;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\PlanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminPhase1Test extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;

    protected Plan $standardPlan;

    protected Plan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PlanSeeder::class);
        $this->seed(PermissionSeeder::class);

        $this->superAdmin = User::factory()->create([
            'name' => 'GymConsole Super Admin 1',
            'email' => 'gymconsole.superadmin1@yopmail.com',
            'password' => Hash::make('GymConsole@123'),
            'role' => 'super_admin',
            'status' => 'ACTIVE',
            'tenant_id' => null,
        ]);

        $this->standardPlan = Plan::where('slug', 'starter')->first() ?? Plan::create([
            'name' => 'Starter Tier',
            'slug' => 'starter',
            'price_monthly' => 1999,
            'price_yearly' => 19990,
            'trial_days' => 14,
            'member_limit' => 200,
            'branch_limit' => 1,
            'staff_limit' => 5,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $this->proPlan = Plan::where('slug', 'pro')->first() ?? Plan::create([
            'name' => 'Pro Tier',
            'slug' => 'pro',
            'price_monthly' => 3999,
            'price_yearly' => 39990,
            'trial_days' => 14,
            'member_limit' => 1000,
            'branch_limit' => 3,
            'staff_limit' => 15,
            'is_active' => true,
            'sort_order' => 2,
        ]);
    }

    protected function createTestTenant(string $name = 'Test Fitness Gym', string $status = 'ACTIVE'): Tenant
    {
        $slug = Str::slug($name).'-'.Str::random(5);
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'email' => 'contact@'.Str::slug($name).'.com',
            'phone' => '9876543210',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'status' => $status,
        ]);

        $tenant->branches()->create([
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_main' => true,
            'is_active' => true,
        ]);

        return $tenant;
    }

    /**
     * 1. Authentication & Role Access Verification
     */
    public function test_super_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->post(route('login.post'), [
            'email' => 'gymconsole.superadmin1@yopmail.com',
            'password' => 'GymConsole@123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);
    }

    public function test_non_super_admin_is_forbidden_from_admin_console(): void
    {
        $tenant = $this->createTestTenant('Forbidden Gym');
        $gymOwner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        $response = $this->actingAs($gymOwner)->get(route('admin.dashboard'));
        $response->assertStatus(403);
    }

    public function test_guest_is_redirected_to_login_from_admin_console(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }

    /**
     * 2. Dashboard Telemetry & Metrics
     */
    public function test_super_admin_dashboard_renders_successfully(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Platform Metrics');
    }

    /**
     * 3. Gym Tenants Management (CRUD + Impersonation + Cascade Delete)
     */
    public function test_super_admin_can_view_gyms_listing_with_search_and_filters(): void
    {
        $tenantA = $this->createTestTenant('Iron Fitness Hub', 'ACTIVE');
        $tenantB = $this->createTestTenant('Metro Muscle Gym', 'TRIAL');

        $response = $this->actingAs($this->superAdmin)->get(route('admin.gyms', ['search' => 'Iron']));
        $response->assertOk();
        $response->assertSee('Iron Fitness Hub');
        $response->assertDontSee('Metro Muscle Gym');

        $statusFilterResponse = $this->actingAs($this->superAdmin)->get(route('admin.gyms', ['status' => 'TRIAL']));
        $statusFilterResponse->assertOk();
        $statusFilterResponse->assertSee('Metro Muscle Gym');
    }

    public function test_super_admin_cannot_create_gym_with_invalid_data(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.gyms.store'), [
            'gym_name' => '',
            'owner_name' => '',
            'email' => 'invalid-email',
            'plan_id' => 999999,
        ]);

        $response->assertSessionHasErrors(['gym_name', 'owner_name', 'email', 'plan_id', 'currency', 'timezone', 'status']);
    }

    public function test_super_admin_can_create_new_gym_tenant_successfully(): void
    {
        $payload = [
            'gym_name' => 'Gold Power Gym',
            'owner_name' => 'GymConsole Owner 1',
            'email' => 'gymconsole.owner1@yopmail.com',
            'phone' => '9876543210',
            'password' => 'GymConsole@123',
            'plan_id' => $this->proPlan->id,
            'billing_cycle' => 'yearly',
            'currency' => 'INR',
            'timezone' => 'Asia/Kolkata',
            'status' => 'ACTIVE',
            'branch_name' => 'Main Branch',
        ];

        $response = $this->actingAs($this->superAdmin)->post(route('admin.gyms.store'), $payload);
        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'name' => 'Gold Power Gym',
            'currency' => 'INR',
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'gymconsole.owner1@yopmail.com',
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        $this->assertDatabaseHas('subscriptions', [
            'plan_id' => $this->proPlan->id,
            'status' => 'ACTIVE',
        ]);
    }

    public function test_super_admin_can_update_existing_gym(): void
    {
        $tenant = $this->createTestTenant('Old Gym Name');

        $response = $this->actingAs($this->superAdmin)->post(route('admin.gyms.update', $tenant->id), [
            'name' => 'Updated Elite Gym',
            'slug' => 'updated-elite-gym',
            'email' => 'updated@elitegym.com',
            'phone' => '9123456780',
            'currency' => 'USD',
            'timezone' => 'UTC',
            'status' => 'ACTIVE',
            'plan_id' => $this->proPlan->id,
            'billing_cycle' => 'monthly',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'name' => 'Updated Elite Gym',
            'slug' => 'updated-elite-gym',
            'currency' => 'USD',
        ]);
    }

    public function test_super_admin_can_impersonate_gym_owner_and_leave_impersonation(): void
    {
        $tenant = $this->createTestTenant('Impersonation Target Gym');
        $owner = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'gym_owner',
            'status' => 'ACTIVE',
        ]);

        // Start Impersonation
        $impersonateResponse = $this->actingAs($this->superAdmin)->post(route('admin.gyms.impersonate', $tenant->id));
        $impersonateResponse->assertRedirect(route('app.dashboard'));
        $this->assertAuthenticatedAs($owner);
        $this->assertEquals($this->superAdmin->id, session('admin_impersonate_user_id'));

        // Leave Impersonation
        $leaveResponse = $this->post(route('admin.leave-impersonation'));
        $leaveResponse->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($this->superAdmin);
        $this->assertNull(session('admin_impersonate_user_id'));
    }

    public function test_super_admin_delete_gym_requires_exact_name_confirmation(): void
    {
        $tenant = $this->createTestTenant('Protected Gym');

        // Wrong confirmation
        $failResponse = $this->actingAs($this->superAdmin)->delete(route('admin.gyms.delete', $tenant->id), [
            'confirm_gym_name' => 'Wrong Name',
        ]);
        $failResponse->assertSessionHas('error');
        $this->assertDatabaseHas('tenants', ['id' => $tenant->id]);

        // Correct confirmation
        $successResponse = $this->actingAs($this->superAdmin)->delete(route('admin.gyms.delete', $tenant->id), [
            'confirm_gym_name' => 'Protected Gym',
        ]);
        $successResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('tenants', ['id' => $tenant->id]);
    }

    /**
     * 4. SaaS Plans & Features Management
     */
    public function test_super_admin_can_create_update_and_delete_saas_plan(): void
    {
        // 1. Create Plan
        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.plans.store'), [
            'name' => 'Enterprise Elite',
            'slug' => 'enterprise-elite',
            'description' => 'Unlimited multi-branch gym plan',
            'price_monthly' => 5999,
            'price_yearly' => 59990,
            'trial_days' => 30,
            'member_limit' => -1,
            'branch_limit' => 10,
            'staff_limit' => 50,
            'is_active' => 1,
            'is_popular' => 1,
            'sort_order' => 5,
        ]);
        $createResponse->assertSessionHas('success');

        $plan = Plan::where('slug', 'enterprise-elite')->first();
        $this->assertNotNull($plan);

        // 2. Update Plan
        $updateResponse = $this->actingAs($this->superAdmin)->post(route('admin.plans.update', $plan->id), [
            'name' => 'Enterprise Elite Plus',
            'slug' => 'enterprise-elite-plus',
            'price_monthly' => 6999,
            'price_yearly' => 69990,
            'trial_days' => 30,
            'member_limit' => -1,
            'branch_limit' => 20,
            'staff_limit' => 100,
            'is_active' => 1,
            'sort_order' => 6,
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('plans', ['name' => 'Enterprise Elite Plus']);

        // 3. Delete Plan
        $deleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.plans.delete', $plan->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('plans', ['id' => $plan->id]);
    }

    public function test_super_admin_can_create_new_feature_toggle(): void
    {
        $response = $this->actingAs($this->superAdmin)->post(route('admin.features.store'), [
            'name' => 'AI Automated Workout Coach',
            'code' => 'ai_workout_coach',
            'description' => 'Generates custom workout plans via LLM',
            'type' => 'boolean',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('features', ['code' => 'ai_workout_coach']);
    }

    /**
     * 5. Promo Coupons & Discount Management
     */
    public function test_super_admin_can_manage_promo_coupons(): void
    {
        // 1. Validation: Percentage cannot exceed 100%
        $invalidResponse = $this->actingAs($this->superAdmin)->post(route('admin.coupons.store'), [
            'code' => 'INVALID150',
            'name' => '150 Percent Off',
            'discount_type' => 'percentage',
            'discount_value' => 150,
        ]);
        $invalidResponse->assertSessionHas('error');

        // 2. Create Valid Coupon
        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.coupons.store'), [
            'code' => 'fitblast50',
            'name' => '50% Welcome Discount',
            'discount_type' => 'percentage',
            'discount_value' => 50,
            'plan_id' => $this->proPlan->id,
            'is_active' => 1,
        ]);
        $createResponse->assertSessionHas('success');

        $coupon = Coupon::where('code', 'FITBLAST50')->first();
        $this->assertNotNull($coupon);
        $this->assertTrue($coupon->is_active);

        // 3. Toggle Status
        $toggleResponse = $this->actingAs($this->superAdmin)->post(route('admin.coupons.toggle', $coupon->id));
        $toggleResponse->assertSessionHas('success');
        $this->assertFalse($coupon->fresh()->is_active);

        // 4. Update Coupon
        $updateResponse = $this->actingAs($this->superAdmin)->post(route('admin.coupons.update', $coupon->id), [
            'code' => 'FITBLAST60',
            'name' => '60% Mega Discount',
            'discount_type' => 'percentage',
            'discount_value' => 60,
            'is_active' => 1,
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('coupons', ['code' => 'FITBLAST60', 'discount_value' => 60]);

        // 5. Delete Coupon
        $deleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.coupons.delete', $coupon->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('coupons', ['id' => $coupon->id]);
    }

    /**
     * 6. Subscriptions & Manual Offline Payments
     */
    public function test_super_admin_can_view_and_update_subscriptions(): void
    {
        $tenant = $this->createTestTenant('Subscription Target Gym');
        $sub = Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $this->standardPlan->id,
            'status' => 'TRIAL',
            'billing_cycle' => 'monthly',
            'starts_at' => now(),
            'ends_at' => now()->addMonth(),
        ]);

        $response = $this->actingAs($this->superAdmin)->post(route('admin.subscriptions.update', $sub->id), [
            'status' => 'ACTIVE',
            'plan_id' => $this->proPlan->id,
            'billing_cycle' => 'yearly',
            'ends_at' => now()->addYear()->toDateString(),
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('subscriptions', [
            'id' => $sub->id,
            'plan_id' => $this->proPlan->id,
            'status' => 'ACTIVE',
            'billing_cycle' => 'yearly',
        ]);
    }

    public function test_super_admin_can_record_manual_offline_payment_for_tenant(): void
    {
        $tenant = $this->createTestTenant('Manual Payment Target Gym', 'TRIAL');

        $response = $this->actingAs($this->superAdmin)->post(route('admin.subscriptions.manual-payment'), [
            'tenant_id' => $tenant->id,
            'plan_id' => $this->proPlan->id,
            'amount' => 39990,
            'billing_cycle' => 'yearly',
            'payment_method' => 'bank_transfer',
            'transaction_id' => 'NEFT-123456789',
            'notes' => 'Offline direct bank transfer verified by Super Admin',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals('ACTIVE', $tenant->fresh()->status);
        $this->assertDatabaseHas('subscription_payments', [
            'tenant_id' => $tenant->id,
            'amount' => 39990,
            'transaction_id' => 'NEFT-123456789',
        ]);
    }

    /**
     * 7. Support Tickets Helpdesk
     */
    public function test_super_admin_can_manage_support_tickets(): void
    {
        $tenant = $this->createTestTenant('Helpdesk Gym');
        $owner = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'gym_owner']);

        $ticket = SupportTicket::create([
            'tenant_id' => $tenant->id,
            'user_id' => $owner->id,
            'ticket_number' => 'TICK-TEST-001',
            'subject' => 'Need help with biometric device IP config',
            'category' => 'technical',
            'priority' => 'high',
            'status' => 'open',
            'message' => 'Our Hikvision device is not receiving events.',
            'last_reply_at' => now(),
        ]);

        // 1. View Index & Show
        $indexResponse = $this->actingAs($this->superAdmin)->get(route('admin.tickets.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('TICK-TEST-001');

        $showResponse = $this->actingAs($this->superAdmin)->get(route('admin.tickets.show', $ticket->id));
        $showResponse->assertOk();
        $showResponse->assertSee('Need help with biometric device IP config');

        // 2. Post Super Admin Reply
        $replyResponse = $this->actingAs($this->superAdmin)->post(route('admin.tickets.reply', $ticket->id), [
            'message' => 'Please verify port 8000 and ensure device webhook URL is configured.',
            'status' => 'answered',
        ]);
        $replyResponse->assertSessionHas('success');
        $this->assertDatabaseHas('support_ticket_replies', [
            'support_ticket_id' => $ticket->id,
            'user_id' => $this->superAdmin->id,
            'is_admin_reply' => true,
        ]);
        $this->assertEquals('answered', $ticket->fresh()->status);

        // 3. Update Status
        $statusResponse = $this->actingAs($this->superAdmin)->post(route('admin.tickets.status', $ticket->id), [
            'status' => 'resolved',
            'priority' => 'medium',
        ]);
        $statusResponse->assertSessionHas('success');
        $this->assertEquals('resolved', $ticket->fresh()->status);

        // 4. Delete Ticket
        $deleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.tickets.delete', $ticket->id));
        $deleteResponse->assertRedirect(route('admin.tickets.index'));
        $this->assertSoftDeleted('support_tickets', ['id' => $ticket->id]);
    }

    /**
     * 8. Platform Users Management
     */
    public function test_super_admin_can_manage_platform_users(): void
    {
        $tenant = $this->createTestTenant('Staff Management Gym');

        // 1. Create User
        $createResponse = $this->actingAs($this->superAdmin)->post(route('admin.users.store'), [
            'name' => 'GymConsole Staff 1',
            'email' => 'gymconsole.staff1@yopmail.com',
            'phone' => '9988776655',
            'role' => 'gym_manager',
            'status' => 'ACTIVE',
            'tenant_id' => $tenant->id,
            'password' => 'GymConsole@123',
        ]);
        $createResponse->assertSessionHas('success');

        $user = User::where('email', 'gymconsole.staff1@yopmail.com')->first();
        $this->assertNotNull($user);

        // 2. Update User
        $updateResponse = $this->actingAs($this->superAdmin)->post(route('admin.users.update', $user->id), [
            'name' => 'GymConsole Staff 1 Updated',
            'email' => 'gymconsole.staff1@yopmail.com',
            'role' => 'gym_manager',
            'status' => 'SUSPENDED',
            'tenant_id' => $tenant->id,
        ]);
        $updateResponse->assertSessionHas('success');
        $this->assertEquals('SUSPENDED', $user->fresh()->status);

        // 3. Self-deletion prevention
        $selfDeleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.users.delete', $this->superAdmin->id));
        $selfDeleteResponse->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->superAdmin->id]);

        // 4. Delete user
        $deleteResponse = $this->actingAs($this->superAdmin)->delete(route('admin.users.delete', $user->id));
        $deleteResponse->assertSessionHas('success');
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    /**
     * 9. System Activity Logs
     */
    public function test_super_admin_can_view_and_clear_activity_logs(): void
    {
        ActivityLog::log('test_action', 'Test log message for super admin');

        $viewResponse = $this->actingAs($this->superAdmin)->get(route('admin.logs'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('Test log message for super admin');

        $clearResponse = $this->actingAs($this->superAdmin)->post(route('admin.logs.clear'));
        $clearResponse->assertSessionHas('success');
        $this->assertEquals(0, ActivityLog::count());
    }

    /**
     * 10. Global Platform Settings (General, SMTP, Payment, SEO, AI)
     */
    public function test_super_admin_can_update_global_platform_settings(): void
    {
        // 1. General Branding Settings
        $generalResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.general'), [
            'app_name' => 'Gym Console Pro SaaS',
            'app_tagline' => 'The Premier Gym Operations Cloud',
            'support_email' => 'support@gymconsole.com',
            'support_phone' => '+91 99999 88888',
            'default_currency' => 'INR',
            'default_timezone' => 'Asia/Kolkata',
            'footer_copyright' => '© 2026 Gym Console. All rights reserved.',
        ]);
        $generalResponse->assertSessionHas('success');
        $this->assertEquals('Gym Console Pro SaaS', Setting::getGlobal('app_name'));

        // 2. Email / SMTP Settings
        $emailResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.email'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.mailtrap.io',
            'mail_port' => 2525,
            'mail_username' => 'testuser',
            'mail_password' => 'testpass',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@gymconsole.com',
            'mail_from_name' => 'Gym Console System',
        ]);
        $emailResponse->assertSessionHas('success');
        $this->assertEquals('smtp.mailtrap.io', Setting::getGlobal('mail_host'));

        // 3. Payment Gateway Settings
        $paymentResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.payment'), [
            'razorpay_enabled' => 1,
            'razorpay_mode' => 'sandbox',
            'razorpay_key_id' => 'rzp_test_phase1key',
            'razorpay_key_secret' => 'rzp_sec_phase1secret',
            'stripe_enabled' => 0,
        ]);
        $paymentResponse->assertSessionHas('success');
        $this->assertEquals('rzp_test_phase1key', Setting::getGlobal('razorpay_key_id'));

        // 4. SEO Settings
        $seoResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.seo'), [
            'meta_title' => 'Gym Console - Best Gym Software',
            'meta_description' => 'Comprehensive gym management software with biometric access control.',
            'meta_keywords' => 'gym, software, fitness, saas',
        ]);
        $seoResponse->assertSessionHas('success');
        $this->assertEquals('Gym Console - Best Gym Software', Setting::getGlobal('meta_title'));

        // 5. AI Settings
        $aiResponse = $this->actingAs($this->superAdmin)->post(route('admin.settings.ai'), [
            'gemini_api_key' => 'AIzaSy_test_key_sample',
            'gemini_model' => 'gemini-1.5-pro',
            'gemini_enabled' => 1,
        ]);
        $aiResponse->assertSessionHas('success');
        $this->assertEquals('gemini-1.5-pro', Setting::getGlobal('gemini_model'));
    }
}
