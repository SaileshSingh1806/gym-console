<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Branch;
use App\Models\Lead;
use App\Models\LeadTrial;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\PtPlan;
use App\Models\PtSession;
use App\Models\Role;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use App\Services\SubscriptionService;
use App\Services\TenantContext;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class ComprehensiveTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure all system plans and permissions exist
        (new PlanSeeder)->run();
        (new PermissionSeeder)->run();

        // 2. Locate or create the Test Gym Tenant (Tenant 54 / Armour 24-7 Gym or create/update)
        $tenant = Tenant::find(54);
        if (! $tenant) {
            $tenant = Tenant::first() ?? Tenant::create([
                'name' => 'Armour 24-7 Gym',
                'slug' => 'armour-24-7-gym',
                'email' => 'armour247gym@gmail.com',
                'phone' => '9974995179',
                'currency' => 'INR',
                'timezone' => 'Asia/Kolkata',
                'status' => 'ACTIVE',
            ]);
        }

        TenantContext::setTenant($tenant);

        // Ensure active PRO subscription with all features enabled (CRM, PT, Biometrics, etc.)
        $proPlan = Plan::where('slug', 'pro')->first() ?? Plan::where('slug', 'starter')->first();
        if ($proPlan) {
            app(SubscriptionService::class)->activateSubscription(
                $tenant,
                $proPlan,
                'yearly',
                'manual',
                'tx_test_'.Str::random(10),
                12000.00
            );
        }

        // 3. Ensure Main Branch exists
        $branch = Branch::where('tenant_id', $tenant->id)->first();
        if (! $branch) {
            $branch = Branch::create([
                'tenant_id' => $tenant->id,
                'name' => 'Main Flagship Branch',
                'code' => 'MAIN',
                'phone' => $tenant->phone ?? '9974995179',
                'email' => $tenant->email ?? 'branch@armour247.com',
                'address' => 'Plot 42, Fitness Avenue, Sector 18',
                'city' => 'Gurugram',
                'state' => 'Haryana',
                'postal_code' => '122001',
                'is_main' => true,
                'status' => 'ACTIVE',
            ]);
        }

        // 4. Create / Update Membership Plans
        $planMonthly = MembershipPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '1 Month Basic Fitness'],
            [
                'branch_id' => $branch->id,
                'description' => 'Unlimited Gym Access + Cardio & Strength Floor',
                'duration_type' => 'months',
                'duration_value' => 1,
                'price' => 2000.00,
                'tax_rate' => 0.00,
                'is_active' => true,
                'plan_type' => 'single',
            ]
        );

        $planQuarterly = MembershipPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '3 Months Standard Power'],
            [
                'branch_id' => $branch->id,
                'description' => '3 Months Gym Access + Locker & Steam Bath',
                'duration_type' => 'months',
                'duration_value' => 3,
                'price' => 5000.00,
                'tax_rate' => 0.00,
                'is_active' => true,
                'plan_type' => 'single',
            ]
        );

        $planHalfYearly = MembershipPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '6 Months Pro Transformation'],
            [
                'branch_id' => $branch->id,
                'description' => '6 Months Access + Diet Consultation + 2 Free PT Sessions',
                'duration_type' => 'months',
                'duration_value' => 6,
                'price' => 9000.00,
                'tax_rate' => 0.00,
                'is_active' => true,
                'plan_type' => 'single',
            ]
        );

        $planAnnual = MembershipPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '12 Months Annual VIP Black'],
            [
                'branch_id' => $branch->id,
                'description' => '1 Year All-Inclusive VIP Access + Nutrition + Group Classes',
                'duration_type' => 'years',
                'duration_value' => 1,
                'price' => 15000.00,
                'tax_rate' => 0.00,
                'is_active' => true,
                'plan_type' => 'single',
            ]
        );

        // 5. Create / Update PT Plans
        $ptPlan12 = PtPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '12 Sessions Starter PT'],
            [
                'branch_id' => $branch->id,
                'description' => '12 One-on-One personal training sessions with certified trainer',
                'total_sessions' => 12,
                'validity_days' => 30,
                'default_price' => 6000.00,
                'trainer_commission_percent' => 20.00,
                'is_active' => true,
            ]
        );

        $ptPlan24 = PtPlan::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => '24 Sessions Elite PT Transformation'],
            [
                'branch_id' => $branch->id,
                'description' => '24 Intensive personal training sessions + custom workout & diet tracking',
                'total_sessions' => 24,
                'validity_days' => 60,
                'default_price' => 11000.00,
                'trainer_commission_percent' => 25.00,
                'is_active' => true,
            ]
        );

        // 6. Setup Roles & Permissions for Staff
        // A. Sales Manager Role
        $crmPermissions = Permission::whereIn('name', ['crm.view', 'crm.manage', 'crm.trials'])->pluck('id')->toArray();
        $salesRole = Role::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'sales_manager'],
            [
                'display_name' => 'Sales Manager',
                'description' => 'Solely handles CRM leads, trials, follow-ups, and prospect inquiries',
                'is_system' => false,
            ]
        );
        $salesRole->permissions()->sync($crmPermissions);

        // B. Receptionist Role (ONLY Member Management Permissions)
        $memberOnlyPermissions = Permission::whereIn('name', [
            'members.view',
            'members.create',
            'members.edit',
            'members.delete',
        ])->pluck('id')->toArray();

        $receptionistRole = Role::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'receptionist'],
            [
                'display_name' => 'Receptionist',
                'description' => 'Handles member check-in, onboarding, and member directory only',
                'is_system' => true,
            ]
        );
        $receptionistRole->permissions()->sync($memberOnlyPermissions);

        // C. Trainer Role
        $trainerPermissions = Permission::whereIn('name', [
            'members.view',
            'classes.view',
            'trainers.view',
            'pt.view',
            'pt.sessions_manage',
            'workouts.view',
            'workouts.manage',
            'diets.view',
            'diets.manage',
            'attendance.view',
        ])->pluck('id')->toArray();

        $trainerRole = Role::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'trainer'],
            [
                'display_name' => 'Fitness Trainer',
                'description' => 'Handles personal training, workouts, diets, and attendance',
                'is_system' => true,
            ]
        );
        $trainerRole->permissions()->sync($trainerPermissions);

        // D. General Staff Role
        $staffRole = Role::updateOrCreate(
            ['tenant_id' => $tenant->id, 'name' => 'staff'],
            [
                'display_name' => 'General Staff / Housekeeping',
                'description' => 'General support staff and facility maintenance',
                'is_system' => true,
            ]
        );
        $staffRole->permissions()->sync([]);

        // 7. Seed Staff Accounts with Salary Set
        // ----------------------------------------------------
        // Staff 1: General Fitness Trainer
        $userTrainerGeneral = User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'trainer.general@armour247.com'],
            [
                'name' => 'Vikram Rathi',
                'phone' => '9876500001',
                'role' => 'trainer',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-TRN-01',
                    'designation' => 'General Fitness Trainer',
                    'department' => 'Fitness & Training',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 25000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
                ],
            ]
        );

        $userTrainerGeneral->roles()->syncWithoutDetaching([$trainerRole->id]);

        $trainerGeneral = Trainer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $userTrainerGeneral->id],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Vikram',
                'last_name' => 'Rathi',
                'email' => 'trainer.general@armour247.com',
                'phone' => '9876500001',
                'specialization' => 'General Fitness & Conditioning',
                'certification' => 'ACE Certified Personal Trainer',
                'salary' => 25000.00,
                'salary_type' => 'Fixed Monthly',
                'salary_pay_day' => '1st of every month',
                'joining_date' => Carbon::now()->subMonths(6)->toDateString(),
                'status' => 'ACTIVE',
                'is_featured' => true,
                'bio' => 'Passionate about functional fitness, cardio conditioning, and helping beginners start their gym journey.',
            ]
        );

        // Staff 2: PT Specialist Trainer
        $userTrainerPt = User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'trainer.pt@armour247.com'],
            [
                'name' => 'Rohit Verma',
                'phone' => '9876500002',
                'role' => 'trainer',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-TRN-02',
                    'designation' => 'Senior PT & Strength Coach',
                    'department' => 'Personal Training',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 40000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(8)->toDateString(),
                ],
            ]
        );
        $userTrainerPt->roles()->syncWithoutDetaching([$trainerRole->id]);

        $trainerPt = Trainer::updateOrCreate(
            ['tenant_id' => $tenant->id, 'user_id' => $userTrainerPt->id],
            [
                'branch_id' => $branch->id,
                'first_name' => 'Rohit',
                'last_name' => 'Verma',
                'email' => 'trainer.pt@armour247.com',
                'phone' => '9876500002',
                'specialization' => 'Strength & Conditioning / PT Specialist',
                'certification' => 'ISSA Master Trainer, K11 Certified',
                'salary' => 40000.00,
                'salary_type' => 'Fixed Monthly',
                'salary_pay_day' => '1st of every month',
                'joining_date' => Carbon::now()->subMonths(8)->toDateString(),
                'status' => 'ACTIVE',
                'is_featured' => true,
                'bio' => 'Specializes in hypertrophy, body transformation, powerlifting, and customized 1-on-1 coaching.',
            ]
        );

        // Staff 3: Receptionist (Members Management Only)
        $userReceptionist = User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'receptionist@armour247.com'],
            [
                'name' => 'Pooja Sharma',
                'phone' => '9876500003',
                'role' => 'receptionist',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-REC-01',
                    'designation' => 'Front Desk Executive / Receptionist',
                    'department' => 'Administration & Front Desk',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 22000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(4)->toDateString(),
                ],
            ]
        );
        $userReceptionist->roles()->syncWithoutDetaching([$receptionistRole->id]);

        // Staff 4: Sales Manager (CRM & Leads Only)
        $userSalesManager = User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'sales@armour247.com'],
            [
                'name' => 'Amit Khanna',
                'phone' => '9876500004',
                'role' => 'sales_manager',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-SLS-01',
                    'designation' => 'Sales & Growth Manager',
                    'department' => 'Sales & Business Development',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 35000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(5)->toDateString(),
                ],
            ]
        );
        $userSalesManager->roles()->syncWithoutDetaching([$salesRole->id]);

        // Staff 5, 6, 7: Housekeeping / Saaf Safai Wale
        User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'ramesh.cleaning@armour247.com'],
            [
                'name' => 'Ramesh Kumar',
                'phone' => '9876500005',
                'role' => 'staff',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-HKP-01',
                    'designation' => 'Housekeeping Supervisor',
                    'department' => 'Housekeeping & Sanitization',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 15000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(10)->toDateString(),
                ],
            ]
        );

        User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'sunita.cleaning@armour247.com'],
            [
                'name' => 'Sunita Devi',
                'phone' => '9876500006',
                'role' => 'staff',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-HKP-02',
                    'designation' => 'Cleaning & Hygiene Staff',
                    'department' => 'Housekeeping & Sanitization',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 14000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(7)->toDateString(),
                ],
            ]
        );

        User::updateOrCreate(
            ['tenant_id' => $tenant->id, 'email' => 'manoj.maintenance@armour247.com'],
            [
                'name' => 'Manoj Singh',
                'phone' => '9876500007',
                'role' => 'staff',
                'status' => 'ACTIVE',
                'password' => Hash::make('password123'),
                'metadata' => [
                    'employee_id' => 'EMP-HKP-03',
                    'designation' => 'Facility Maintenance & Cleaning',
                    'department' => 'Housekeeping & Maintenance',
                    'employee_category' => 'Full-Time',
                    'monthly_salary' => 16000,
                    'payout_type' => 'Fixed Monthly',
                    'salary_pay_day' => '1st of every month',
                    'joining_date' => Carbon::now()->subMonths(9)->toDateString(),
                ],
            ]
        );

        // 8. Seed 50 Comprehensive Realistic Members
        // ----------------------------------------------------
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow();

        $memberProfiles = [
            // --- Group 1: 10 Expired Members (joined 2-4 months ago, expired) ---
            ['first' => 'Aarav', 'last' => 'Patel', 'gender' => 'male', 'phone' => '9811000001', 'dob' => '1996-03-12', 'scenario' => 'expired', 'months_ago' => 4, 'expired_days_ago' => 30, 'payment' => 'full', 'pt' => false],
            ['first' => 'Neha', 'last' => 'Gupta', 'gender' => 'female', 'phone' => '9811000002', 'dob' => '1999-07-25', 'scenario' => 'expired', 'months_ago' => 3, 'expired_days_ago' => 15, 'payment' => 'full', 'pt' => false],
            ['first' => 'Karan', 'last' => 'Malhotra', 'gender' => 'male', 'phone' => '9811000003', 'dob' => '1994-11-04', 'scenario' => 'expired', 'months_ago' => 4, 'expired_days_ago' => 45, 'payment' => 'full', 'pt' => true],
            ['first' => 'Priya', 'last' => 'Sood', 'gender' => 'female', 'phone' => '9811000004', 'dob' => '1998-05-18', 'scenario' => 'expired', 'months_ago' => 3, 'expired_days_ago' => 20, 'payment' => 'full', 'pt' => false],
            ['first' => 'Dev', 'last' => 'Singhania', 'gender' => 'male', 'phone' => '9811000005', 'dob' => '1992-08-30', 'scenario' => 'expired', 'months_ago' => 4, 'expired_days_ago' => 25, 'payment' => 'full', 'pt' => false],
            ['first' => 'Ananya', 'last' => 'Iyer', 'gender' => 'female', 'phone' => '9811000006', 'dob' => '2001-01-14', 'scenario' => 'expired', 'months_ago' => 3, 'expired_days_ago' => 10, 'payment' => 'full', 'pt' => false],
            ['first' => 'Rohan', 'last' => 'Chawla', 'gender' => 'male', 'phone' => '9811000007', 'dob' => '1995-10-22', 'scenario' => 'expired', 'months_ago' => 4, 'expired_days_ago' => 50, 'payment' => 'full', 'pt' => false],
            ['first' => 'Simran', 'last' => 'Kaur', 'gender' => 'female', 'phone' => '9811000008', 'dob' => '1997-04-09', 'scenario' => 'expired', 'months_ago' => 3, 'expired_days_ago' => 18, 'payment' => 'full', 'pt' => true],
            ['first' => 'Kabir', 'last' => 'Mehta', 'gender' => 'male', 'phone' => '9811000009', 'dob' => '1993-12-05', 'scenario' => 'expired', 'months_ago' => 4, 'expired_days_ago' => 35, 'payment' => 'full', 'pt' => false],
            ['first' => 'Divya', 'last' => 'Nair', 'gender' => 'female', 'phone' => '9811000010', 'dob' => '2000-06-29', 'scenario' => 'expired', 'months_ago' => 3, 'expired_days_ago' => 12, 'payment' => 'full', 'pt' => false],

            // --- Group 2: 3 Expiring TODAY ---
            ['first' => 'Siddharth', 'last' => 'Rao', 'gender' => 'male', 'phone' => '9811000011', 'dob' => '1995-02-14', 'scenario' => 'expires_today', 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Tanvi', 'last' => 'Bhatia', 'gender' => 'female', 'phone' => '9811000012', 'dob' => '1999-09-02', 'scenario' => 'expires_today', 'months_ago' => 3, 'payment' => 'full', 'pt' => true],
            ['first' => 'Aditya', 'last' => 'Joshi', 'gender' => 'male', 'phone' => '9811000013', 'dob' => '1992-06-19', 'scenario' => 'expires_today', 'months_ago' => 6, 'payment' => 'full', 'pt' => false],

            // --- Group 3: 3 Expiring TOMORROW ---
            ['first' => 'Manish', 'last' => 'Aggarwal', 'gender' => 'male', 'phone' => '9811000014', 'dob' => '1994-08-11', 'scenario' => 'expires_tomorrow', 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Rhea', 'last' => 'Sen', 'gender' => 'female', 'phone' => '9811000015', 'dob' => '2001-03-24', 'scenario' => 'expires_tomorrow', 'months_ago' => 3, 'payment' => 'full', 'pt' => true],
            ['first' => 'Kunal', 'last' => 'Chopra', 'gender' => 'male', 'phone' => '9811000016', 'dob' => '1997-12-30', 'scenario' => 'expires_tomorrow', 'months_ago' => 1, 'payment' => 'full', 'pt' => false],

            // --- Group 4: 8 Expiring Soon (in 3 to 7 days) ---
            ['first' => 'Gaurav', 'last' => 'Bansal', 'gender' => 'male', 'phone' => '9811000017', 'dob' => '1996-01-19', 'scenario' => 'expires_soon', 'days_left' => 3, 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Kavita', 'last' => 'Mishra', 'gender' => 'female', 'phone' => '9811000018', 'dob' => '1998-11-15', 'scenario' => 'expires_soon', 'days_left' => 4, 'months_ago' => 3, 'payment' => 'full', 'pt' => true],
            ['first' => 'Deepak', 'last' => 'Verma', 'gender' => 'male', 'phone' => '9811000019', 'dob' => '1993-04-28', 'scenario' => 'expires_soon', 'days_left' => 5, 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Shreya', 'last' => 'Roy', 'gender' => 'female', 'phone' => '9811000020', 'dob' => '2000-08-07', 'scenario' => 'expires_soon', 'days_left' => 6, 'months_ago' => 6, 'payment' => 'full', 'pt' => false],
            ['first' => 'Nikhil', 'last' => 'Kapoor', 'gender' => 'male', 'phone' => '9811000021', 'dob' => '1995-05-21', 'scenario' => 'expires_soon', 'days_left' => 3, 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Meera', 'last' => 'Deshmukh', 'gender' => 'female', 'phone' => '9811000022', 'dob' => '1997-09-05', 'scenario' => 'expires_soon', 'days_left' => 4, 'months_ago' => 3, 'payment' => 'full', 'pt' => true],
            ['first' => 'Arjun', 'last' => 'Reddy', 'gender' => 'male', 'phone' => '9811000023', 'dob' => '1994-07-13', 'scenario' => 'expires_soon', 'days_left' => 7, 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Ishita', 'last' => 'Saxena', 'gender' => 'female', 'phone' => '9811000024', 'dob' => '2001-10-02', 'scenario' => 'expires_soon', 'days_left' => 5, 'months_ago' => 6, 'payment' => 'full', 'pt' => false],

            // --- Group 5: 3 Birthday TODAY (September 18th) ---
            ['first' => 'Rahul', 'last' => 'Bajaj', 'gender' => 'male', 'phone' => '9811000025', 'dob' => '1998-'.$today->format('m-d'), 'scenario' => 'birthday_today', 'months_ago' => 2, 'payment' => 'full', 'pt' => true],
            ['first' => 'Sneha', 'last' => 'Kulkarni', 'gender' => 'female', 'phone' => '9811000026', 'dob' => '2001-'.$today->format('m-d'), 'scenario' => 'birthday_today', 'months_ago' => 1, 'payment' => 'full', 'pt' => false],
            ['first' => 'Harsh', 'last' => 'Vardhan', 'gender' => 'male', 'phone' => '9811000027', 'dob' => '1995-'.$today->format('m-d'), 'scenario' => 'birthday_today', 'months_ago' => 3, 'payment' => 'full', 'pt' => true],

            // --- Group 6: 4 Birthday this week / upcoming ---
            ['first' => 'Alok', 'last' => 'Tiwari', 'gender' => 'male', 'phone' => '9811000028', 'dob' => '1997-'.$tomorrow->format('m-d'), 'scenario' => 'birthday_upcoming', 'months_ago' => 2, 'payment' => 'full', 'pt' => false],
            ['first' => 'Pooja', 'last' => 'Pandey', 'gender' => 'female', 'phone' => '9811000029', 'dob' => '1999-'.$today->copy()->addDays(2)->format('m-d'), 'scenario' => 'birthday_upcoming', 'months_ago' => 1, 'payment' => 'full', 'pt' => true],
            ['first' => 'Vishal', 'last' => 'Yadav', 'gender' => 'male', 'phone' => '9811000030', 'dob' => '1994-'.$today->copy()->addDays(4)->format('m-d'), 'scenario' => 'birthday_upcoming', 'months_ago' => 3, 'payment' => 'full', 'pt' => false],
            ['first' => 'Kritika', 'last' => 'Soni', 'gender' => 'female', 'phone' => '9811000031', 'dob' => '2000-'.$today->copy()->addDays(6)->format('m-d'), 'scenario' => 'birthday_upcoming', 'months_ago' => 2, 'payment' => 'full', 'pt' => true],

            // --- Group 7: 12 Half / Partial Payment Members (Remaining Dues) ---
            ['first' => 'Sameer', 'last' => 'Garg', 'gender' => 'male', 'phone' => '9811000032', 'dob' => '1993-06-17', 'scenario' => 'active_partial', 'months_ago' => 2, 'payment' => 'half', 'pt' => false],
            ['first' => 'Pallavi', 'last' => 'Jain', 'gender' => 'female', 'phone' => '9811000033', 'dob' => '1998-02-28', 'scenario' => 'active_partial', 'months_ago' => 1, 'payment' => 'half', 'pt' => true],
            ['first' => 'Akash', 'last' => 'Tripathi', 'gender' => 'male', 'phone' => '9811000034', 'dob' => '1996-08-04', 'scenario' => 'active_partial', 'months_ago' => 3, 'payment' => 'half', 'pt' => false],
            ['first' => 'Nikita', 'last' => 'Dhawan', 'gender' => 'female', 'phone' => '9811000035', 'dob' => '2001-11-19', 'scenario' => 'active_partial', 'months_ago' => 2, 'payment' => 'half', 'pt' => true],
            ['first' => 'Yash', 'last' => 'Singhal', 'gender' => 'male', 'phone' => '9811000036', 'dob' => '1995-04-10', 'scenario' => 'active_partial', 'months_ago' => 1, 'payment' => 'half', 'pt' => false],
            ['first' => 'Ritu', 'last' => 'Kumari', 'gender' => 'female', 'phone' => '9811000037', 'dob' => '1997-12-08', 'scenario' => 'active_partial', 'months_ago' => 3, 'payment' => 'half', 'pt' => true],
            ['first' => 'Pranav', 'last' => 'Dixit', 'gender' => 'male', 'phone' => '9811000038', 'dob' => '1994-09-29', 'scenario' => 'active_partial', 'months_ago' => 2, 'payment' => 'half', 'pt' => false],
            ['first' => 'Swati', 'last' => 'Rawat', 'gender' => 'female', 'phone' => '9811000039', 'dob' => '2000-03-03', 'scenario' => 'active_partial', 'months_ago' => 1, 'payment' => 'half', 'pt' => true],
            ['first' => 'Varun', 'last' => 'Kaushik', 'gender' => 'male', 'phone' => '9811000040', 'dob' => '1992-07-21', 'scenario' => 'active_partial', 'months_ago' => 3, 'payment' => 'half', 'pt' => false],
            ['first' => 'Ankita', 'last' => 'Dubey', 'gender' => 'female', 'phone' => '9811000041', 'dob' => '1999-05-14', 'scenario' => 'active_partial', 'months_ago' => 2, 'payment' => 'half', 'pt' => true],
            ['first' => 'Mohit', 'last' => 'Chauhan', 'gender' => 'male', 'phone' => '9811000042', 'dob' => '1996-10-31', 'scenario' => 'active_partial', 'months_ago' => 1, 'payment' => 'half', 'pt' => false],
            ['first' => 'Sonam', 'last' => 'Chopra', 'gender' => 'female', 'phone' => '9811000043', 'dob' => '1998-01-26', 'scenario' => 'active_partial', 'months_ago' => 3, 'payment' => 'half', 'pt' => true],

            // --- Group 8: 7 Long-Term Active Members (Fully Paid, 6-12 Months Plan) ---
            ['first' => 'Kishore', 'last' => 'Kumar', 'gender' => 'male', 'phone' => '9811000044', 'dob' => '1991-05-02', 'scenario' => 'active_full', 'months_ago' => 3, 'plan' => 'annual', 'payment' => 'full', 'pt' => false],
            ['first' => 'Monika', 'last' => 'Bhardwaj', 'gender' => 'female', 'phone' => '9811000045', 'dob' => '1996-12-16', 'scenario' => 'active_full', 'months_ago' => 2, 'plan' => 'annual', 'payment' => 'full', 'pt' => true],
            ['first' => 'Girish', 'last' => 'Soni', 'gender' => 'male', 'phone' => '9811000046', 'dob' => '1993-08-22', 'scenario' => 'active_full', 'months_ago' => 1, 'plan' => 'half_yearly', 'payment' => 'full', 'pt' => false],
            ['first' => 'Bhavna', 'last' => 'Taneja', 'gender' => 'female', 'phone' => '9811000047', 'dob' => '2000-02-11', 'scenario' => 'active_full', 'months_ago' => 3, 'plan' => 'half_yearly', 'payment' => 'full', 'pt' => false],
            ['first' => 'Ayush', 'last' => 'Madaan', 'gender' => 'male', 'phone' => '9811000048', 'dob' => '1997-06-08', 'scenario' => 'active_full', 'months_ago' => 2, 'plan' => 'annual', 'payment' => 'full', 'pt' => true],
            ['first' => 'Deepika', 'last' => 'Rana', 'gender' => 'female', 'phone' => '9811000049', 'dob' => '1999-10-18', 'scenario' => 'active_full', 'months_ago' => 1, 'plan' => 'quarterly', 'payment' => 'full', 'pt' => false],
            ['first' => 'Naveen', 'last' => 'Sehgal', 'gender' => 'male', 'phone' => '9811000050', 'dob' => '1994-03-27', 'scenario' => 'active_full', 'months_ago' => 3, 'plan' => 'annual', 'payment' => 'full', 'pt' => true],
        ];

        foreach ($memberProfiles as $index => $p) {
            $memberCode = 'MEM-'.str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT);
            $joinDate = $today->copy()->subMonths($p['months_ago'] ?? 2)->subDays(rand(1, 20));

            // Select Plan based on scenario
            $plan = match ($p['plan'] ?? null) {
                'annual' => $planAnnual,
                'half_yearly' => $planHalfYearly,
                'quarterly' => $planQuarterly,
                default => ($p['scenario'] === 'expired' ? $planMonthly : (rand(0, 1) ? $planQuarterly : $planMonthly)),
            };

            // Calculate Start & End Date
            $startDate = $joinDate->copy();
            $status = 'ACTIVE';

            if ($p['scenario'] === 'expired') {
                $daysAgo = $p['expired_days_ago'] ?? 20;
                $endDate = $today->copy()->subDays($daysAgo);
                $startDate = $endDate->copy()->subMonth();
                $status = 'EXPIRED';
            } elseif ($p['scenario'] === 'expires_today') {
                $endDate = $today->copy();
                $startDate = $endDate->copy()->subMonths($plan->duration_value);
                $status = 'ACTIVE';
            } elseif ($p['scenario'] === 'expires_tomorrow') {
                $endDate = $tomorrow->copy();
                $startDate = $endDate->copy()->subMonths($plan->duration_value);
                $status = 'ACTIVE';
            } elseif ($p['scenario'] === 'expires_soon') {
                $daysLeft = $p['days_left'] ?? 4;
                $endDate = $today->copy()->addDays($daysLeft);
                $startDate = $endDate->copy()->subMonths($plan->duration_value);
                $status = 'ACTIVE';
            } else {
                $endDate = $startDate->copy()->addMonths($plan->duration_value);
                if ($endDate->isPast()) {
                    $endDate = $today->copy()->addMonths(rand(2, 8));
                }
                $status = 'ACTIVE';
            }

            // Assign Trainer if PT
            $assignedTrainerId = null;
            if (! empty($p['pt'])) {
                $assignedTrainerId = ($index % 2 === 0) ? $trainerPt->id : $trainerGeneral->id;
            }

            // Create Member Record
            $member = Member::updateOrCreate(
                ['tenant_id' => $tenant->id, 'member_code' => $memberCode],
                [
                    'branch_id' => $branch->id,
                    'trainer_id' => $assignedTrainerId,
                    'first_name' => $p['first'],
                    'last_name' => $p['last'],
                    'email' => strtolower($p['first'].'.'.$p['last'].'@example.com'),
                    'phone' => $p['phone'],
                    'gender' => $p['gender'],
                    'dob' => $p['dob'],
                    'address' => 'House #'.rand(10, 200).', Sector '.rand(10, 60).', Gurugram',
                    'emergency_contact_name' => $p['last'].' Family',
                    'emergency_contact_phone' => '98765'.rand(10000, 99999),
                    'join_date' => $joinDate->toDateString(),
                    'status' => $status,
                    'notes' => 'Test Profile: '.ucfirst(str_replace('_', ' ', $p['scenario'])).' | Payment: '.ucfirst($p['payment']),
                ]
            );

            // Calculate Payment & Pricing
            $finalAmount = (float) $plan->price;
            $paidAmount = $finalAmount;
            if ($p['payment'] === 'half') {
                $paidAmount = round($finalAmount / 2, 2);
            }

            // Create Membership
            $membership = Membership::updateOrCreate(
                ['tenant_id' => $tenant->id, 'member_id' => $member->id],
                [
                    'branch_id' => $branch->id,
                    'membership_plan_id' => $plan->id,
                    'start_date' => $startDate->toDateString(),
                    'end_date' => $endDate->toDateString(),
                    'price' => $plan->price,
                    'discount' => 0.00,
                    'tax' => 0.00,
                    'final_amount' => $finalAmount,
                    'paid_amount' => $paidAmount,
                    'status' => $status,
                    'notes' => 'Plan: '.$plan->name.($p['payment'] === 'half' ? ' (Half Paid - Due Pending: ₹'.($finalAmount - $paidAmount).')' : ' (Full Paid)'),
                ]
            );

            // Record Payment Transaction in MemberPayments
            if ($paidAmount > 0) {
                $paymentMethod = match ($index % 4) {
                    0 => 'upi',
                    1 => 'cash',
                    2 => 'card',
                    default => 'bank_transfer',
                };

                MemberPayment::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'membership_id' => $membership->id],
                    [
                        'branch_id' => $branch->id,
                        'invoice_number' => 'INV-2026-'.str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT),
                        'amount' => $paidAmount,
                        'payment_method' => $paymentMethod,
                        'transaction_reference' => 'TXN-'.strtoupper(Str::random(8)),
                        'payment_date' => $startDate->toDateString(),
                        'received_by_user_id' => $userReceptionist->id,
                        'notes' => $p['payment'] === 'half' ? 'First installment (50% paid)' : 'Full payment cleared on enrollment',
                    ]
                );
            }

            // If PT is enabled, assign MemberPtPackage + log some PT sessions
            if (! empty($p['pt'])) {
                $ptPlan = ($index % 3 === 0) ? $ptPlan24 : $ptPlan12;
                $usedSessions = rand(2, $ptPlan->total_sessions - 2);

                $ptPackage = MemberPtPackage::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'member_id' => $member->id],
                    [
                        'branch_id' => $branch->id,
                        'trainer_id' => $assignedTrainerId,
                        'pt_plan_id' => $ptPlan->id,
                        'package_name' => $ptPlan->name,
                        'total_sessions' => $ptPlan->total_sessions,
                        'used_sessions' => $usedSessions,
                        'start_date' => $startDate->toDateString(),
                        'end_date' => $startDate->copy()->addDays($ptPlan->validity_days)->toDateString(),
                        'price' => $ptPlan->default_price,
                        'discount' => 0.00,
                        'final_amount' => $ptPlan->default_price,
                        'paid_amount' => $ptPlan->default_price,
                        'status' => 'ACTIVE',
                        'notes' => 'Personal training with coach '.($assignedTrainerId === $trainerPt->id ? 'Rohit Verma' : 'Vikram Rathi'),
                    ]
                );

                // Create a couple of recent PT session logs
                PtSession::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'session_date' => $today->copy()->subDays(2)->toDateString()],
                    [
                        'branch_id' => $branch->id,
                        'member_pt_package_id' => $ptPackage->id,
                        'trainer_id' => $assignedTrainerId,
                        'start_time' => '07:00:00',
                        'end_time' => '08:00:00',
                        'duration_minutes' => 60,
                        'status' => 'COMPLETED',
                        'focus_area' => 'Legs & Cardio',
                        'notes' => 'Legs workout & cardio assessment',
                        'completed_at' => $today->copy()->subDays(2)->setTime(8, 0),
                    ]
                );

                PtSession::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'session_date' => $today->toDateString()],
                    [
                        'branch_id' => $branch->id,
                        'member_pt_package_id' => $ptPackage->id,
                        'trainer_id' => $assignedTrainerId,
                        'start_time' => '10:00:00',
                        'end_time' => '11:00:00',
                        'duration_minutes' => 60,
                        'status' => 'SCHEDULED',
                        'focus_area' => 'Upper Body Hypertrophy',
                        'notes' => 'Upper body compound movements',
                    ]
                );
            }

            // Seed realistic attendance records for active members
            if ($status === 'ACTIVE') {
                $isDormantScenario = in_array($index, [44, 45, 46, 47, 48, 49]); // 6 dormant members (inactive >14 days)
                $isChurnScenario = in_array($index, [10, 13, 16, 20]); // 4 churn risk members (expiring <=7 days & inactive >7 days)

                if ($isDormantScenario) {
                    // Inactive for 20+ days (Dormant Member)
                    Attendance::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'date' => $today->copy()->subDays(22)->toDateString()],
                        [
                            'branch_id' => $branch->id,
                            'check_in' => $today->copy()->subDays(22)->setTime(8, 15),
                            'check_out' => $today->copy()->subDays(22)->setTime(9, 30),
                            'method' => 'manual',
                            'status' => 'PRESENT',
                        ]
                    );
                } elseif ($isChurnScenario) {
                    // Inactive for 9 days & expiring soon (Churn Risk Member)
                    Attendance::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'date' => $today->copy()->subDays(9)->toDateString()],
                        [
                            'branch_id' => $branch->id,
                            'check_in' => $today->copy()->subDays(9)->setTime(7, 30),
                            'check_out' => $today->copy()->subDays(9)->setTime(8, 45),
                            'method' => 'manual',
                            'status' => 'PRESENT',
                        ]
                    );
                } else {
                    // Regular Active Member (Attended today, 2 days ago, 4 days ago)
                    Attendance::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'date' => $today->toDateString()],
                        [
                            'branch_id' => $branch->id,
                            'check_in' => $today->copy()->setTime(6, 45),
                            'check_out' => $today->copy()->setTime(8, 0),
                            'method' => 'qr',
                            'status' => 'PRESENT',
                        ]
                    );
                    Attendance::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'date' => $today->copy()->subDays(2)->toDateString()],
                        [
                            'branch_id' => $branch->id,
                            'check_in' => $today->copy()->subDays(2)->setTime(7, 0),
                            'check_out' => $today->copy()->subDays(2)->setTime(8, 15),
                            'method' => 'qr',
                            'status' => 'PRESENT',
                        ]
                    );
                    Attendance::updateOrCreate(
                        ['tenant_id' => $tenant->id, 'member_id' => $member->id, 'date' => $today->copy()->subDays(4)->toDateString()],
                        [
                            'branch_id' => $branch->id,
                            'check_in' => $today->copy()->subDays(4)->setTime(6, 30),
                            'check_out' => $today->copy()->subDays(4)->setTime(7, 45),
                            'method' => 'qr',
                            'status' => 'PRESENT',
                        ]
                    );
                }
            }
        }

        // 9. Seed 12 CRM Leads for the Sales Manager
        // ----------------------------------------------------
        $crmLeadsData = [
            ['name' => 'Kunal Kapoor', 'phone' => '9899111001', 'email' => 'kunal@prospect.com', 'stage' => 'NEW_LEAD', 'source' => 'instagram', 'val' => 5000],
            ['name' => 'Mehak Oberoi', 'phone' => '9899111002', 'email' => 'mehak@prospect.com', 'stage' => 'CONTACTED', 'source' => 'walk_in', 'val' => 9000],
            ['name' => 'Aman Deep', 'phone' => '9899111003', 'email' => 'aman@prospect.com', 'stage' => 'DEMO_BOOKED', 'source' => 'google', 'val' => 15000],
            ['name' => 'Ritika Sethi', 'phone' => '9899111004', 'email' => 'ritika@prospect.com', 'stage' => 'TRIAL', 'source' => 'facebook', 'val' => 6000],
            ['name' => 'Deepak Rawal', 'phone' => '9899111005', 'email' => 'deepak@prospect.com', 'stage' => 'PROPOSAL_SENT', 'source' => 'referral', 'val' => 12000],
            ['name' => 'Pooja Vashisht', 'phone' => '9899111006', 'email' => 'pooja.v@prospect.com', 'stage' => 'NEGOTIATION', 'source' => 'website', 'val' => 9000],
            ['name' => 'Saurabh Juneja', 'phone' => '9899111007', 'email' => 'saurabh@prospect.com', 'stage' => 'PAID', 'source' => 'walk_in', 'val' => 15000],
            ['name' => 'Neha Grover', 'phone' => '9899111008', 'email' => 'neha.g@prospect.com', 'stage' => 'LOST', 'source' => 'phone', 'val' => 2000],
        ];

        foreach ($crmLeadsData as $ld) {
            $lead = Lead::updateOrCreate(
                ['tenant_id' => $tenant->id, 'phone' => $ld['phone']],
                [
                    'branch_id' => $branch->id,
                    'assigned_to_user_id' => $userSalesManager->id,
                    'name' => $ld['name'],
                    'email' => $ld['email'],
                    'stage' => $ld['stage'],
                    'source' => $ld['source'],
                    'estimated_value' => $ld['val'],
                    'remarks' => 'Inquiry for '.$ld['source'].' channel, assigned to Sales Manager Amit Khanna',
                ]
            );

            if (in_array($ld['stage'], ['DEMO_BOOKED', 'TRIAL'])) {
                LeadTrial::updateOrCreate(
                    ['tenant_id' => $tenant->id, 'lead_id' => $lead->id],
                    [
                        'branch_id' => $branch->id,
                        'assigned_to_user_id' => $userTrainerGeneral->id,
                        'trial_date' => $today->copy()->addDays(rand(0, 3))->toDateString(),
                        'trial_time' => '11:00 AM',
                        'status' => 'SCHEDULED',
                        'notes' => '1-Day trial workout with general coach',
                    ]
                );
            }
        }
    }
}
