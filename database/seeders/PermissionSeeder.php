<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public static function getPermissionsGrouped(): array
    {
        return [
            'members' => [
                'label' => 'Members',
                'permissions' => [
                    ['name' => 'members.view', 'display_name' => 'View Members', 'description' => 'View member directory & profiles'],
                    ['name' => 'members.create', 'display_name' => 'Create Member', 'description' => 'Enroll and add new gym members'],
                    ['name' => 'members.edit', 'display_name' => 'Edit Member', 'description' => 'Edit member details, phone & status'],
                    ['name' => 'members.delete', 'display_name' => 'Delete Member', 'description' => 'Archive or delete members'],
                ],
            ],
            'billing' => [
                'label' => 'Memberships & Billing',
                'permissions' => [
                    ['name' => 'memberships.view', 'display_name' => 'View Memberships', 'description' => 'Inspect active plans and member subscriptions'],
                    ['name' => 'memberships.manage', 'display_name' => 'Manage Plans', 'description' => 'Create and edit membership plan catalog'],
                    ['name' => 'payments.view', 'display_name' => 'View Payments & Invoices', 'description' => 'Inspect payment records and invoice receipts'],
                    ['name' => 'payments.record', 'display_name' => 'Collect / Record Payment', 'description' => 'Accept cash, POS, card, or UPI fee collections'],
                    ['name' => 'payments.refund', 'display_name' => 'Refund / Reverse Payment', 'description' => 'Reverse or cancel recorded transactions'],
                ],
            ],
            'attendance' => [
                'label' => 'Attendance',
                'permissions' => [
                    ['name' => 'attendance.view', 'display_name' => 'View Attendance', 'description' => 'View real-time and historical check-in logs'],
                    ['name' => 'attendance.checkin', 'display_name' => 'Manual Check-in', 'description' => 'Check in / check out members manually at reception'],
                ],
            ],
            'training' => [
                'label' => 'Training, Classes & Trainers',
                'permissions' => [
                    ['name' => 'classes.view', 'display_name' => 'View Classes', 'description' => 'View group fitness class schedule'],
                    ['name' => 'classes.manage', 'display_name' => 'Manage Classes', 'description' => 'Create, edit, and schedule group classes'],
                    ['name' => 'classes.book', 'display_name' => 'Book Classes', 'description' => 'Book members into group classes'],
                    ['name' => 'trainers.view', 'display_name' => 'View Trainers', 'description' => 'View certified coaching staff'],
                    ['name' => 'trainers.manage', 'display_name' => 'Manage Trainers', 'description' => 'Add/edit trainers and member assignments'],
                ],
            ],
            'personal_training' => [
                'label' => 'Personal Training (PT)',
                'permissions' => [
                    ['name' => 'pt.view', 'display_name' => 'View PT Packages & Sessions', 'description' => 'View active packages, session counts & calendar'],
                    ['name' => 'pt.assign', 'display_name' => 'Assign PT Packages', 'description' => 'Assign personal training packages to members'],
                    ['name' => 'pt.sessions_manage', 'display_name' => 'Schedule & Log PT Sessions', 'description' => 'Schedule appointments and log completed sessions'],
                ],
            ],
            'fitness' => [
                'label' => 'Workouts, Diets & Services',
                'permissions' => [
                    ['name' => 'workouts.view', 'display_name' => 'View Workout Plans', 'description' => 'Inspect exercise routines and workout plans'],
                    ['name' => 'workouts.manage', 'display_name' => 'Manage Workouts', 'description' => 'Create and assign workout routines'],
                    ['name' => 'diets.view', 'display_name' => 'View Diet Plans', 'description' => 'View nutrition charts and diet plans'],
                    ['name' => 'diets.manage', 'display_name' => 'Manage Diets', 'description' => 'Create and assign meal plans'],
                    ['name' => 'services.view', 'display_name' => 'View Gym Services', 'description' => 'View extra gym services and bookings'],
                    ['name' => 'services.manage', 'display_name' => 'Manage Services', 'description' => 'Create services and record session bookings'],
                ],
            ],
            'crm' => [
                'label' => 'CRM & Leads',
                'permissions' => [
                    ['name' => 'crm.view', 'display_name' => 'View CRM & Leads', 'description' => 'Access CRM dashboard and inquiries list'],
                    ['name' => 'crm.manage', 'display_name' => 'Manage Leads', 'description' => 'Create, edit, follow up, and assign leads'],
                    ['name' => 'crm.trials', 'display_name' => 'Manage Trials', 'description' => 'Book and record trial visits and conversions'],
                ],
            ],
            'finance' => [
                'label' => 'Report & Finance',
                'permissions' => [
                    ['name' => 'reports.member_report', 'display_name' => 'View Member Report', 'description' => 'Inspect member report and PDF export'],
                    ['name' => 'reports.balance_sheet', 'display_name' => 'View Balance Sheet', 'description' => 'Access financial balance sheet and P&L analytics'],
                    ['name' => 'expenses.manage', 'display_name' => 'Manage Expenses', 'description' => 'Record expenses, bills, and categories'],
                ],
            ],
            'inventory' => [
                'label' => 'Inventory & Maintenance',
                'permissions' => [
                    ['name' => 'inventory.view', 'display_name' => 'View Inventory & Equipment', 'description' => 'Inspect stock items and machine statuses'],
                    ['name' => 'inventory.manage', 'display_name' => 'Manage Inventory Stock', 'description' => 'Add products and adjust stock quantities'],
                    ['name' => 'equipment.maintenance', 'display_name' => 'Equipment Maintenance', 'description' => 'Log machine and AC service/repair records'],
                ],
            ],
            'devices' => [
                'label' => 'Hikvision IoT Devices',
                'permissions' => [
                    ['name' => 'devices.view', 'display_name' => 'View Devices & Access Logs', 'description' => 'Monitor connected facial/turnstile devices and logs'],
                    ['name' => 'devices.manage', 'display_name' => 'Configure Hardware', 'description' => 'Add and test biometric access hardware'],
                ],
            ],
            'administration' => [
                'label' => 'Administration & Settings',
                'permissions' => [
                    ['name' => 'staff.view', 'display_name' => 'View Staff', 'description' => 'View staff directory and profiles'],
                    ['name' => 'staff.manage', 'display_name' => 'Manage Staff', 'description' => 'Add/edit staff accounts and passwords'],
                    ['name' => 'roles.manage', 'display_name' => 'Manage Roles & Permissions', 'description' => 'Customize role permissions matrix'],
                    ['name' => 'settings.manage', 'display_name' => 'Manage Gym Settings', 'description' => 'Update gym branding, tax, and details'],
                ],
            ],
        ];
    }

    public static function getPermissionFeatureMap(): array
    {
        return [
            // Members
            'members.view' => 'members_management',
            'members.create' => 'members_management',
            'members.edit' => 'members_management',
            'members.delete' => 'members_management',

            // Memberships & Billing
            'memberships.view' => 'memberships_billing',
            'memberships.manage' => 'memberships_billing',
            'payments.view' => 'payments_pos',
            'payments.record' => 'payments_pos',
            'payments.refund' => 'payments_pos',

            // Attendance
            'attendance.view' => 'attendance_checkin',
            'attendance.checkin' => 'attendance_checkin',

            // Training & Coaches
            'classes.view' => 'group_classes',
            'classes.manage' => 'group_classes',
            'classes.book' => 'group_classes',
            'trainers.view' => 'trainers_management',
            'trainers.manage' => 'trainers_management',

            // Personal Training
            'pt.view' => 'personal_training',
            'pt.assign' => 'personal_training',
            'pt.sessions_manage' => 'personal_training',

            // Fitness & Services
            'workouts.view' => 'workout_plans',
            'workouts.manage' => 'workout_plans',
            'diets.view' => 'diet_nutrition',
            'diets.manage' => 'diet_nutrition',
            'services.view' => 'gym_services',
            'services.manage' => 'gym_services',

            // CRM & Growth
            'crm.view' => 'crm_leads',
            'crm.manage' => 'crm_leads',
            'crm.trials' => 'crm_leads',

            // Reports & Finance
            'reports.member_report' => 'reports_finance',
            'reports.balance_sheet' => 'reports_finance',
            'expenses.manage' => 'reports_finance',

            // Inventory & Maintenance
            'inventory.view' => 'inventory_stock',
            'inventory.manage' => 'inventory_stock',
            'equipment.maintenance' => 'equipment_maintenance',

            // Devices IoT
            'devices.view' => 'hikvision_iot',
            'devices.manage' => 'hikvision_iot',

            // Administration
            'staff.view' => 'staff_roles',
            'staff.manage' => 'staff_roles',
            'roles.manage' => 'staff_roles',
            'settings.manage' => null,
        ];
    }

    public static function getDefaultRolePermissions(): array
    {
        return [
            'gym_manager' => [
                'members.view', 'members.create', 'members.edit',
                'memberships.view', 'memberships.manage', 'payments.view', 'payments.record',
                'attendance.view', 'attendance.checkin',
                'classes.view', 'classes.manage', 'classes.book', 'trainers.view',
                'pt.view', 'pt.assign', 'pt.sessions_manage',
                'workouts.view', 'workouts.manage', 'diets.view', 'diets.manage', 'services.view', 'services.manage',
                'crm.view', 'crm.manage', 'crm.trials',
                'reports.member_report',
                'inventory.view', 'inventory.manage', 'equipment.maintenance',
                'devices.view',
                'staff.view',
            ],
            'receptionist' => [
                'members.view', 'members.create', 'members.edit',
                'memberships.view', 'payments.view', 'payments.record',
                'attendance.view', 'attendance.checkin',
                'classes.view', 'classes.book', 'trainers.view',
                'pt.view', 'pt.sessions_manage',
                'services.view',
                'crm.view', 'crm.manage', 'crm.trials',
                'inventory.view',
            ],
            'trainer' => [
                'members.view',
                'classes.view', 'trainers.view',
                'pt.view', 'pt.sessions_manage',
                'workouts.view', 'workouts.manage', 'diets.view', 'diets.manage',
                'attendance.view',
            ],
            'accountant' => [
                'members.view',
                'memberships.view', 'payments.view', 'payments.record', 'payments.refund',
                'reports.member_report', 'reports.balance_sheet', 'expenses.manage',
                'inventory.view',
            ],
            'staff' => [
                'members.view',
                'attendance.view',
                'classes.view',
                'inventory.view',
            ],
        ];
    }

    public function run(): void
    {
        $groups = self::getPermissionsGrouped();
        $validNames = [];
        $allPermMap = [];

        foreach ($groups as $groupKey => $groupData) {
            foreach ($groupData['permissions'] as $p) {
                $validNames[] = $p['name'];
                $perm = Permission::updateOrCreate(
                    ['name' => $p['name']],
                    [
                        'display_name' => $p['display_name'],
                        'group' => $groupKey,
                        'description' => $p['description'] ?? null,
                    ]
                );
                $allPermMap[$p['name']] = $perm->id;
            }
        }

        // Delete any obsolete permissions not in the current system list
        Permission::whereNotIn('name', $validNames)->delete();

        $defaultMappings = self::getDefaultRolePermissions();

        // Seed roles and sync default permissions for all tenants
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            foreach ($defaultMappings as $roleSlug => $permNames) {
                $role = Role::withoutGlobalScopes()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $roleSlug],
                    [
                        'display_name' => match ($roleSlug) {
                            'gym_manager' => 'Gym Manager',
                            'receptionist' => 'Receptionist',
                            'trainer' => 'Fitness Trainer',
                            'accountant' => 'Accountant',
                            'staff' => 'General Staff',
                            default => ucfirst($roleSlug),
                        },
                        'is_system' => true,
                    ]
                );

                $permIds = [];
                foreach ($permNames as $pName) {
                    if (isset($allPermMap[$pName])) {
                        $permIds[] = $allPermMap[$pName];
                    }
                }

                $role->permissions()->sync($permIds);
            }
        }
    }
}
