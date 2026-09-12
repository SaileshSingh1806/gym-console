<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('system:purge-dummy-data', function () {
    $this->info('Purging all dummy gym and tenant data...');

    DB::statement('SET FOREIGN_KEY_CHECKS=0;');

    $tables = [
        'access_logs',
        'activity_logs',
        'attendances',
        'branch_user',
        'branches',
        'class_bookings',
        'class_schedules',
        'devices',
        'diet_meals',
        'diet_plans',
        'equipment_maintenance_logs',
        'expense_categories',
        'expenses',
        'gym_classes',
        'gym_equipment',
        'gym_service_bookings',
        'gym_services',
        'inventory_logs',
        'inventory_items',
        'lead_trials',
        'leads',
        'member_payments',
        'member_pt_packages',
        'members',
        'membership_plans',
        'memberships',
        'permission_role',
        'permissions',
        'plan_features',
        'features',
        'plans',
        'platform_invoices',
        'pt_plans',
        'pt_sessions',
        'role_user',
        'roles',
        'subscription_payments',
        'subscriptions',
        'tenants',
        'trainers',
        'workout_exercises',
        'workout_plans',
        'users',
    ];

    foreach ($tables as $table) {
        if (Schema::hasTable($table)) {
            DB::table($table)->truncate();
        }
    }

    DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    $this->info('Re-seeding clean system configurations...');
    Artisan::call('db:seed', ['--class' => 'PermissionSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'PlanSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'SuperAdminSeeder', '--force' => true]);
    Artisan::call('db:seed', ['--class' => 'CouponSeeder', '--force' => true]);

    $this->info('System reset complete! Only Super Admin account exists.');
})->purpose('Purge all tenant/dummy data and keep only Super Admin login & clean SaaS system configs');
