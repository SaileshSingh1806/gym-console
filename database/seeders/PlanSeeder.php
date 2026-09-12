<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public static function getSystemFeatures(): array
    {
        return [
            // 1. Gym Operations
            [
                'code' => 'members_management',
                'name' => 'Member Directory & Profiles',
                'description' => 'Enroll members, KYC, body measurements & transformation records',
                'category' => 'Gym Operations',
                'type' => 'boolean',
            ],
            [
                'code' => 'memberships_billing',
                'name' => 'Memberships & Plan Catalog',
                'description' => 'Configurable plans, recurring packages, admission fees & discounts',
                'category' => 'Gym Operations',
                'type' => 'boolean',
            ],
            [
                'code' => 'attendance_checkin',
                'name' => 'Daily Attendance & Check-in',
                'description' => 'Real-time check-in logs, receptionist desk check-in & reports',
                'category' => 'Gym Operations',
                'type' => 'boolean',
            ],
            [
                'code' => 'payments_pos',
                'name' => 'Payments, POS & PDF Invoices',
                'description' => 'Record cash, POS, UPI payments, GST billing & print PDF tax receipts',
                'category' => 'Gym Operations',
                'type' => 'boolean',
            ],

            // 2. Fitness & Coaching
            [
                'code' => 'group_classes',
                'name' => 'Training & Group Classes',
                'description' => 'Yoga, Zumba, CrossFit class calendars, schedules & slot bookings',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],
            [
                'code' => 'trainers_management',
                'name' => 'Trainer Directory & Staff',
                'description' => 'Certified trainers, trainer profiles & member assignments',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],
            [
                'code' => 'personal_training',
                'name' => 'Personal Training (PT) Packages',
                'description' => 'PT packages, 1-on-1 session tracking & appointment booking',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],
            [
                'code' => 'workout_plans',
                'name' => 'Workout Plans & Exercise Builder',
                'description' => 'Custom workout routines, exercise libraries & day-wise workouts',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],
            [
                'code' => 'diet_nutrition',
                'name' => 'Diet Plans & Nutrition Charts',
                'description' => 'Meal planning, caloric macros & customized diet charts',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],
            [
                'code' => 'gym_services',
                'name' => 'Gym Services & Locker Rentals',
                'description' => 'Spa, sauna, steam bath services & monthly locker rentals',
                'category' => 'Fitness & Coaching',
                'type' => 'boolean',
            ],

            // 3. Business & Growth
            [
                'code' => 'crm_leads',
                'name' => 'CRM Leads & Trial Follow-ups',
                'description' => 'Lead pipeline, walk-in inquiries, trial booking & conversion tracker',
                'category' => 'Business & Growth',
                'type' => 'boolean',
            ],
            [
                'code' => 'reports_finance',
                'name' => 'Reports & Financial Balance Sheet',
                'description' => 'Member reports, revenue analytics, expense tracking & P&L balance sheet',
                'category' => 'Business & Growth',
                'type' => 'boolean',
            ],
            [
                'code' => 'inventory_stock',
                'name' => 'Inventory & Supplements POS',
                'description' => 'Supplements stock, merchandise inventory & stock replenishment logs',
                'category' => 'Business & Growth',
                'type' => 'boolean',
            ],
            [
                'code' => 'equipment_maintenance',
                'name' => 'Equipment & AC Maintenance',
                'description' => 'Gym machines & AC service schedules, repair logs & technician records',
                'category' => 'Business & Growth',
                'type' => 'boolean',
            ],
            [
                'code' => 'hikvision_iot',
                'name' => 'Hikvision IoT Biometrics & Gates',
                'description' => 'Facial recognition terminals, fingerprint readers & turnstile door control',
                'category' => 'Business & Growth',
                'type' => 'boolean',
            ],

            // 4. Administration
            [
                'code' => 'staff_roles',
                'name' => 'Staff Accounts & Roles Matrix',
                'description' => 'Staff profiles, custom roles & granular permission access control',
                'category' => 'Administration',
                'type' => 'boolean',
            ],
            [
                'code' => 'custom_branding',
                'name' => 'Custom Gym Logo & Theme Branding',
                'description' => 'Gym owner custom logo, invoice receipts branding & theme customization',
                'category' => 'Administration',
                'type' => 'boolean',
            ],
        ];
    }

    public function run(): void
    {
        $features = self::getSystemFeatures();
        $createdFeatures = [];

        foreach ($features as $f) {
            $createdFeatures[$f['code']] = Feature::updateOrCreate(
                ['code' => $f['code']],
                [
                    'name' => $f['name'],
                    'description' => $f['description'],
                    'type' => $f['type'],
                ]
            );
        }

        // 1. FREE FOREVER PLAN
        $freeForever = Plan::updateOrCreate(
            ['slug' => 'free-forever'],
            [
                'name' => 'FREE FOREVER',
                'description' => 'Free forever, not a trial. Perfect for new single-location gym startups.',
                'price_monthly' => 0.00,
                'price_yearly' => 0.00,
                'trial_days' => 0,
                'member_limit' => 75,
                'branch_limit' => 1,
                'staff_limit' => 2,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 1,
            ]
        );

        $freeFeatures = [
            'members_management',
            'memberships_billing',
            'attendance_checkin',
            'payments_pos',
            'workout_plans',
            'diet_nutrition',
            'gym_services',
            'staff_roles',
            'custom_branding',
        ];

        $freeForever->features()->sync(
            collect($freeFeatures)->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );

        // 2. STARTER PLAN (MOST POPULAR)
        $starter = Plan::updateOrCreate(
            ['slug' => 'starter'],
            [
                'name' => 'STARTER',
                'description' => 'Launch offer — only ₹500/month (billed annually). Complete operational suite.',
                'price_monthly' => 500.00,
                'price_yearly' => 6000.00,
                'trial_days' => 0,
                'member_limit' => -1, // Unlimited
                'branch_limit' => 1,
                'staff_limit' => 5,
                'is_active' => true,
                'is_popular' => true,
                'sort_order' => 2,
            ]
        );

        $starterFeatures = [
            'members_management',
            'memberships_billing',
            'attendance_checkin',
            'payments_pos',
            'group_classes',
            'trainers_management',
            'personal_training',
            'workout_plans',
            'diet_nutrition',
            'gym_services',
            'crm_leads',
            'reports_finance',
            'inventory_stock',
            'staff_roles',
            'custom_branding',
        ];

        $starter->features()->sync(
            collect($starterFeatures)->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );

        // 3. PRO PLAN
        $pro = Plan::updateOrCreate(
            ['slug' => 'pro'],
            [
                'name' => 'PRO & ENTERPRISE',
                'description' => 'Unrestricted access to every feature including IoT Biometrics & Equipment Maintenance.',
                'price_monthly' => 1000.00,
                'price_yearly' => 12000.00,
                'trial_days' => 0,
                'member_limit' => -1, // Unlimited
                'branch_limit' => 10,
                'staff_limit' => 25,
                'is_active' => true,
                'is_popular' => false,
                'sort_order' => 3,
            ]
        );

        // All features enabled for PRO
        $pro->features()->sync(
            collect(array_keys($createdFeatures))->mapWithKeys(fn ($code) => [$createdFeatures[$code]->id => ['value' => '1']])
        );
    }
}
