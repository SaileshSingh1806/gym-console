<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Members
            ['name' => 'members.view', 'display_name' => 'View Members', 'group' => 'members', 'description' => 'View member lists and profiles'],
            ['name' => 'members.create', 'display_name' => 'Create Members', 'group' => 'members', 'description' => 'Enroll new gym members'],
            ['name' => 'members.edit', 'display_name' => 'Edit Members', 'group' => 'members', 'description' => 'Modify member details'],
            ['name' => 'members.delete', 'display_name' => 'Delete Members', 'group' => 'members', 'description' => 'Archive or delete members'],

            // Memberships & Payments
            ['name' => 'memberships.manage', 'display_name' => 'Manage Memberships', 'group' => 'billing', 'description' => 'Assign and renew membership plans'],
            ['name' => 'payments.record', 'display_name' => 'Record Payments', 'group' => 'billing', 'description' => 'Accept cash/card/online member payments'],
            ['name' => 'payments.view', 'display_name' => 'View Invoices & Payments', 'group' => 'billing', 'description' => 'Inspect financial transactions'],

            // Attendance
            ['name' => 'attendance.checkin', 'display_name' => 'Manual Check-in', 'group' => 'attendance', 'description' => 'Check in and out members at reception'],
            ['name' => 'attendance.view', 'display_name' => 'View Attendance Logs', 'group' => 'attendance', 'description' => 'View real-time and historical attendance'],

            // Trainers & Classes
            ['name' => 'trainers.manage', 'display_name' => 'Manage Trainers', 'group' => 'trainers', 'description' => 'Add and configure trainer staff'],
            ['name' => 'classes.manage', 'display_name' => 'Manage Classes', 'group' => 'classes', 'description' => 'Schedule group fitness classes'],
            ['name' => 'classes.book', 'display_name' => 'Book Classes', 'group' => 'classes', 'description' => 'Book members into sessions'],

            // Workouts & Diets
            ['name' => 'workouts.manage', 'display_name' => 'Manage Workout Plans', 'group' => 'fitness', 'description' => 'Create and assign workout routines'],
            ['name' => 'diets.manage', 'display_name' => 'Manage Diet Plans', 'group' => 'fitness', 'description' => 'Create and assign meal plans'],

            // Leads & Expenses & Inventory
            ['name' => 'leads.manage', 'display_name' => 'Manage CRM Leads', 'group' => 'crm', 'description' => 'Follow up on prospect inquiries'],
            ['name' => 'expenses.manage', 'display_name' => 'Manage Expenses', 'group' => 'finance', 'description' => 'Record operational costs and receipts'],
            ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory', 'group' => 'inventory', 'description' => 'Manage store merchandise and supplements'],

            // Devices & IoT
            ['name' => 'devices.manage', 'display_name' => 'Manage Access Hardware', 'group' => 'devices', 'description' => 'Configure Hikvision turnstiles and biometric readers'],
            ['name' => 'devices.logs', 'display_name' => 'View Access Logs', 'group' => 'devices', 'description' => 'Inspect door entry and denied logs'],

            // Settings & Reports
            ['name' => 'reports.view', 'display_name' => 'View Business Reports', 'group' => 'reports', 'description' => 'Access revenue and attendance analytics'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Gym Settings', 'group' => 'settings', 'description' => 'Update gym profile, taxes, and branding'],
        ];

        foreach ($permissions as $p) {
            Permission::updateOrCreate(['name' => $p['name']], $p);
        }
    }
}
