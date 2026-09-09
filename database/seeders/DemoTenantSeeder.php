<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\GymClass;
use App\Models\InventoryItem;
use App\Models\Lead;
use App\Models\Member;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Trainer;
use App\Models\User;
use App\Services\MemberPaymentService;
use App\Services\MembershipService;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use App\Services\TenantService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoTenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin
        User::updateOrCreate(
            ['email' => 'admin@gymconsole.com'],
            [
                'name' => 'SaaS Super Admin',
                'role' => 'super_admin',
                'status' => 'ACTIVE',
                'password' => Hash::make('password'),
            ]
        );

        $enterprisePlan = Plan::where('slug', 'enterprise')->first();
        $starterPlan = Plan::where('slug', 'starter')->first();

        $tenantService = app(TenantService::class);
        $subscriptionService = app(SubscriptionService::class);
        $membershipService = app(MembershipService::class);
        $paymentService = app(MemberPaymentService::class);

        // 2. Create PowerHouse Fitness Club (Enterprise)
        $registration1 = $tenantService->registerGym([
            'gym_name' => 'PowerHouse Fitness Club',
            'email' => 'owner@powerhouse.com',
            'phone' => '+1 555-0199',
            'owner_name' => 'Marcus Vance',
            'password' => 'password',
            'branch_name' => 'Downtown Flagship',
            'address' => '100 Fitness Boulevard',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10001',
            'currency' => 'USD',
        ], $enterprisePlan);

        $tenant1 = $registration1['tenant'];
        $branch1 = $registration1['branch'];
        $owner1 = $registration1['owner'];

        // Activate Enterprise Subscription
        $subscriptionService->activateSubscription(
            $tenant1,
            $enterprisePlan,
            'monthly',
            'stripe',
            'ch_'.Str::random(16),
            149.00
        );

        // Add 2nd Branch to PowerHouse
        TenantContext::setTenant($tenant1);
        $branch2 = Branch::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Uptown Express Studio',
            'code' => 'UPTOWN',
            'phone' => '+1 555-0188',
            'email' => 'uptown@powerhouse.com',
            'address' => '450 Lexington Ave',
            'city' => 'New York',
            'state' => 'NY',
            'postal_code' => '10017',
            'is_main' => false,
            'status' => 'ACTIVE',
        ]);

        // Create Membership Plans
        $planMonthly = MembershipPlan::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Monthly All-Access',
            'description' => 'Unlimited gym floor and locker room access',
            'duration_type' => 'months',
            'duration_value' => 1,
            'price' => 59.00,
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        $planQuarterly = MembershipPlan::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Quarterly VIP',
            'description' => '3 months gym access + 2 personal training sessions',
            'duration_type' => 'months',
            'duration_value' => 3,
            'price' => 149.00,
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        $planAnnual = MembershipPlan::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Annual Elite Black',
            'description' => '12 months unlimited multi-branch access + sauna & classes',
            'duration_type' => 'years',
            'duration_value' => 1,
            'price' => 499.00,
            'tax_rate' => 0.00,
            'is_active' => true,
        ]);

        // Create Hikvision Devices
        $device1 = Device::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Main Turnstile Entry',
            'model' => 'Hikvision DS-K1T341AMF',
            'type' => 'hikvision_facial',
            'serial_number' => 'HKV-FACE-001',
            'ip_address' => '192.168.1.101',
            'port' => 80,
            'direction' => 'in',
            'status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        $device2 = Device::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Main Turnstile Exit',
            'model' => 'Hikvision DS-K1T341AMF',
            'type' => 'hikvision_facial',
            'serial_number' => 'HKV-FACE-002',
            'ip_address' => '192.168.1.102',
            'port' => 80,
            'direction' => 'out',
            'status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        // Create Trainer
        $trainer = Trainer::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'first_name' => 'Alex',
            'last_name' => 'Stone',
            'email' => 'alex.stone@powerhouse.com',
            'phone' => '+1 555-0344',
            'specialization' => 'Hypertrophy & Strength Conditioning',
            'hourly_rate' => 45.00,
            'status' => 'ACTIVE',
            'bio' => 'Certified CSCS and competitive powerlifter with 8+ years coaching experience.',
        ]);

        // Create Demo Members
        $demoMembers = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'email' => 'john.doe@example.com', 'phone' => '+1 555-0101', 'gender' => 'male'],
            ['first_name' => 'Sarah', 'last_name' => 'Connor', 'email' => 'sarah.c@example.com', 'phone' => '+1 555-0102', 'gender' => 'female'],
            ['first_name' => 'Michael', 'last_name' => 'Bisping', 'email' => 'michael.b@example.com', 'phone' => '+1 555-0103', 'gender' => 'male'],
            ['first_name' => 'Emma', 'last_name' => 'Watson', 'email' => 'emma.w@example.com', 'phone' => '+1 555-0104', 'gender' => 'female'],
            ['first_name' => 'David', 'last_name' => 'Goggins', 'email' => 'david.g@example.com', 'phone' => '+1 555-0105', 'gender' => 'male'],
            ['first_name' => 'Jessica', 'last_name' => 'Alba', 'email' => 'jessica.a@example.com', 'phone' => '+1 555-0106', 'gender' => 'female'],
        ];

        foreach ($demoMembers as $idx => $m) {
            $member = Member::create([
                'tenant_id' => $tenant1->id,
                'branch_id' => $branch1->id,
                'member_code' => 'MEM-'.(1001 + $idx),
                'first_name' => $m['first_name'],
                'last_name' => $m['last_name'],
                'email' => $m['email'],
                'phone' => $m['phone'],
                'gender' => $m['gender'],
                'dob' => '1995-05-15',
                'join_date' => now()->subMonths(2)->toDateString(),
                'status' => 'ACTIVE',
                'qr_code_token' => Str::random(32),
            ]);

            // Assign Membership
            $membership = $membershipService->assignMembership($member, $planMonthly);

            // Record Payment
            $paymentService->recordPayment($member, $membership->final_amount, 'cash', $membership, 'RCP-INIT-'.$idx);

            // Seed Attendance for today
            Attendance::create([
                'tenant_id' => $tenant1->id,
                'branch_id' => $branch1->id,
                'member_id' => $member->id,
                'device_id' => $device1->id,
                'date' => now()->toDateString(),
                'check_in' => now()->subHours(rand(1, 4)),
                'check_out' => $idx % 2 === 0 ? now()->subMinutes(rand(10, 50)) : null,
                'method' => 'facial',
                'status' => 'PRESENT',
            ]);
        }

        // Classes
        $class = GymClass::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'HIIT Functional Conditioning',
            'description' => 'High intensity interval training designed to burn fat and boost stamina.',
            'capacity' => 20,
            'fee' => 0.00,
            'is_active' => true,
        ]);

        // Leads
        Lead::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'name' => 'Robert Johnson',
            'phone' => '+1 555-0988',
            'email' => 'robert.j@example.com',
            'source' => 'walk_in',
            'status' => 'NEW',
            'follow_up_date' => now()->addDays(2)->toDateString(),
            'notes' => 'Interested in annual membership and evening classes.',
        ]);

        // Expenses
        $cat = ExpenseCategory::create([
            'tenant_id' => $tenant1->id,
            'name' => 'Utilities & Equipment Maintenance',
        ]);

        Expense::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'expense_category_id' => $cat->id,
            'title' => 'Treadmill Belt Maintenance & Lubrication',
            'amount' => 120.00,
            'expense_date' => now()->toDateString(),
            'payment_method' => 'card',
        ]);

        // Inventory
        InventoryItem::create([
            'tenant_id' => $tenant1->id,
            'branch_id' => $branch1->id,
            'sku' => 'SUP-WHEY-01',
            'name' => '100% Gold Standard Whey 5lb (Double Rich Chocolate)',
            'category' => 'Supplements',
            'cost_price' => 45.00,
            'selling_price' => 69.99,
            'stock_quantity' => 24,
            'reorder_threshold' => 5,
        ]);

        // 3. Create Iron Core Gym (Starter Plan)
        $tenantService->registerGym([
            'gym_name' => 'Iron Core Gym',
            'email' => 'owner@ironcore.com',
            'phone' => '+1 555-0277',
            'owner_name' => 'Elena Rostova',
            'password' => 'password',
            'branch_name' => 'Iron Core Central',
            'city' => 'Chicago',
            'state' => 'IL',
        ], $starterPlan);

        TenantContext::reset();
    }
}
