<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        // Define SaaS Features
        $features = [
            ['code' => 'members', 'name' => 'Member Management', 'description' => 'Create and manage gym member profiles', 'type' => 'boolean'],
            ['code' => 'memberships', 'name' => 'Membership Billing', 'description' => 'Configurable plans, recurring memberships, receipts', 'type' => 'boolean'],
            ['code' => 'attendance', 'name' => 'Attendance Tracking', 'description' => 'Manual and QR code attendance tracking', 'type' => 'boolean'],
            ['code' => 'payments', 'name' => 'Payment & Invoicing', 'description' => 'Record cash, POS, UPI payments and generate invoices', 'type' => 'boolean'],
            ['code' => 'basic_reports', 'name' => 'Basic Reports', 'description' => 'Daily attendance and revenue summary', 'type' => 'boolean'],
            ['code' => 'trainers', 'name' => 'Trainer Management', 'description' => 'Trainer profiles and assigned client rosters', 'type' => 'boolean'],
            ['code' => 'classes', 'name' => 'Classes & Booking', 'description' => 'Group class schedules and member bookings', 'type' => 'boolean'],
            ['code' => 'workout_plans', 'name' => 'Workout Routines', 'description' => 'Custom workout plan builder and exercise library', 'type' => 'boolean'],
            ['code' => 'diet_plans', 'name' => 'Diet & Nutrition', 'description' => 'Custom meal plans and calorie target designer', 'type' => 'boolean'],
            ['code' => 'leads', 'name' => 'Leads CRM', 'description' => 'Prospect inquiry tracking and conversion pipeline', 'type' => 'boolean'],
            ['code' => 'expenses', 'name' => 'Expense Tracking', 'description' => 'Gym operational expense categorization', 'type' => 'boolean'],
            ['code' => 'inventory', 'name' => 'Inventory & POS', 'description' => 'Supplement and merchandise stock management', 'type' => 'boolean'],
            ['code' => 'advanced_reports', 'name' => 'Advanced Financial Analytics', 'description' => 'Revenue forecasting, churn, and retention analysis', 'type' => 'boolean'],
            ['code' => 'multiple_branches', 'name' => 'Multi-Branch Support', 'description' => 'Manage multiple gym locations under one account', 'type' => 'boolean'],
            ['code' => 'hikvision_integration', 'name' => 'Hikvision & Biometric Access', 'description' => 'Integration with Hikvision face scanners, turnstiles, and RFID readers', 'type' => 'boolean'],
            ['code' => 'api_access', 'name' => 'Flutter Mobile API', 'description' => 'REST API access for custom and mobile applications', 'type' => 'boolean'],
            ['code' => 'custom_branding', 'name' => 'Custom Branding', 'description' => 'Custom gym logo, receipts, and portal styling', 'type' => 'boolean'],
        ];

        $createdFeatures = [];
        foreach ($features as $f) {
            $createdFeatures[$f['code']] = Feature::updateOrCreate(
                ['code' => $f['code']],
                ['name' => $f['name'], 'description' => $f['description'], 'type' => $f['type']]
            );
        }

        // 1. STARTER PLAN
        $starter = Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'Starter',
                'description' => 'Essential tools for boutique studios and single-location gyms starting out.',
                'price_monthly' => 1499.00,
                'price_yearly' => 14990.00,
                'trial_days' => 14,
                'member_limit' => 100,
                'branch_limit' => 1,
                'staff_limit' => 3,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ]
        );

        $starterFeatures = ['members', 'memberships', 'attendance', 'payments', 'basic_reports'];
        $starter->features()->sync(
            collect($starterFeatures)->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );

        // 2. PROFESSIONAL PLAN
        $professional = Plan::updateOrCreate(
            ['slug' => 'professional'],
            [
                'name' => 'Professional',
                'description' => 'Comprehensive management suite for growing fitness centers and clubs.',
                'price_monthly' => 3499.00,
                'price_yearly' => 34990.00,
                'trial_days' => 14,
                'member_limit' => 500,
                'branch_limit' => 2,
                'staff_limit' => 10,
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ]
        );

        $proFeatures = [
            'members', 'memberships', 'attendance', 'payments', 'basic_reports',
            'trainers', 'classes', 'workout_plans', 'diet_plans', 'leads', 'expenses', 'inventory', 'advanced_reports', 'api_access',
        ];
        $professional->features()->sync(
            collect($proFeatures)->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );

        // 3. ENTERPRISE PLAN
        $enterprise = Plan::updateOrCreate(
            ['slug' => 'enterprise'],
            [
                'name' => 'Enterprise',
                'description' => 'Unlimited multi-branch scaling, Hikvision biometric IoT turnstiles, and custom branding.',
                'price_monthly' => 7999.00,
                'price_yearly' => 79990.00,
                'trial_days' => 14,
                'member_limit' => -1, // Unlimited
                'branch_limit' => 10,
                'staff_limit' => 50,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ]
        );

        // All features enabled
        $enterprise->features()->sync(
            collect(array_keys($createdFeatures))->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );
    }
}
