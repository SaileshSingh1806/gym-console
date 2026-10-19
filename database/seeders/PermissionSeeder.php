<?php

namespace Database\Seeders;

use App\Models\Feature;
use App\Models\Permission;
use App\Models\Plan;
use App\Models\Role;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public static function getPermissionsGrouped(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard & Visibility',
                'permissions' => [
                    ['name' => 'dashboard.view', 'display_name' => 'View Dashboard', 'description' => 'Access the main gym operations dashboard'],
                    ['name' => 'dashboard.financials', 'display_name' => 'View Financial & Revenue Stats', 'description' => 'See collections, billed amounts, dues balance & financial comparison charts on dashboard'],
                    ['name' => 'dashboard.member_stats', 'display_name' => 'View Member Stats & Alerts', 'description' => 'See active, expired, churn risk, expiring soon & dormant counts'],
                    ['name' => 'dashboard.attendance_stats', 'display_name' => 'View Attendance & Occupancy', 'description' => 'See live check-ins count, today attendance & peak time charts'],
                ],
            ],
            'members' => [
                'label' => 'Members Management',
                'permissions' => [
                    ['name' => 'members.view', 'display_name' => 'View Members', 'description' => 'View member directory, profiles & activity logs'],
                    ['name' => 'members.create', 'display_name' => 'Add / Enroll Member', 'description' => 'Enroll and register new gym members'],
                    ['name' => 'members.edit', 'display_name' => 'Edit Member', 'description' => 'Edit member contact details, photo & status'],
                    ['name' => 'members.delete', 'display_name' => 'Delete Member', 'description' => 'Archive or delete members'],
                    ['name' => 'members.freeze', 'display_name' => 'Freeze / Suspend Member', 'description' => 'Toggle membership freeze & unfreeze status'],
                    ['name' => 'members.measurements', 'display_name' => 'Record Measurements', 'description' => 'Log body weight, chest, arms & fat % stats'],
                ],
            ],
            'billing' => [
                'label' => 'Memberships & Plans Catalog',
                'permissions' => [
                    ['name' => 'memberships.view', 'display_name' => 'View Memberships', 'description' => 'Inspect active plans and member subscriptions'],
                    ['name' => 'memberships.create', 'display_name' => 'Assign Membership', 'description' => 'Assign/enroll members to membership plans'],
                    ['name' => 'memberships.delete', 'display_name' => 'Cancel Membership', 'description' => 'Terminate or remove member subscriptions'],
                    ['name' => 'plans.view', 'display_name' => 'View Plans Catalog', 'description' => 'Browse membership plan tiers and pricing'],
                    ['name' => 'plans.create', 'display_name' => 'Create Membership Plan', 'description' => 'Add new membership plans to catalog'],
                    ['name' => 'plans.edit', 'display_name' => 'Edit Membership Plan', 'description' => 'Modify plan durations, prices, tax & terms'],
                    ['name' => 'plans.delete', 'display_name' => 'Delete Membership Plan', 'description' => 'Remove membership plans from catalog'],
                ],
            ],
            'payments' => [
                'label' => 'Payments, POS & Billing',
                'permissions' => [
                    ['name' => 'payments.view', 'display_name' => 'View Payments & Receipts', 'description' => 'Inspect payment records and transaction logs'],
                    ['name' => 'payments.record', 'display_name' => 'Collect / Record Payment', 'description' => 'Accept cash, POS, card, or UPI fee collections'],
                    ['name' => 'payments.refund', 'display_name' => 'Refund / Reverse Payment', 'description' => 'Reverse or cancel recorded transactions'],
                    ['name' => 'payments.invoice_view', 'display_name' => 'View & Print Tax Invoices', 'description' => 'Generate, inspect, and print official tax invoices'],
                ],
            ],
            'attendance' => [
                'label' => 'Attendance & Check-in',
                'permissions' => [
                    ['name' => 'attendance.view', 'display_name' => 'View Attendance', 'description' => 'View real-time and historical check-in logs'],
                    ['name' => 'attendance.checkin', 'display_name' => 'Manual Check-in / Check-out', 'description' => 'Check in / check out members manually at reception'],
                ],
            ],
            'training' => [
                'label' => 'Training & Group Classes',
                'permissions' => [
                    ['name' => 'classes.view', 'display_name' => 'View Classes & Schedule', 'description' => 'View group fitness class schedule and studio timings'],
                    ['name' => 'classes.create', 'display_name' => 'Create Group Class', 'description' => 'Add new class types and timetable slots'],
                    ['name' => 'classes.edit', 'display_name' => 'Edit Group Class', 'description' => 'Modify class timings, room, and instructors'],
                    ['name' => 'classes.delete', 'display_name' => 'Delete Group Class', 'description' => 'Remove class schedule or booking slot'],
                    ['name' => 'classes.book', 'display_name' => 'Enroll / Book Member', 'description' => 'Book members into group workout classes'],
                ],
            ],
            'trainers' => [
                'label' => 'Coaches & Trainers Management',
                'permissions' => [
                    ['name' => 'trainers.view', 'display_name' => 'View Trainers', 'description' => 'View certified coaching staff directory'],
                    ['name' => 'trainers.create', 'display_name' => 'Add Trainer', 'description' => 'Register new fitness trainers'],
                    ['name' => 'trainers.edit', 'display_name' => 'Edit Trainer Details', 'description' => 'Update trainer details, specialty & compensation'],
                    ['name' => 'trainers.assign', 'display_name' => 'Assign Members to Trainer', 'description' => 'Allocate or reassign gym members to a fitness trainer'],
                    ['name' => 'trainers.delete', 'display_name' => 'Delete Trainer', 'description' => 'Remove trainer from gym directory'],
                ],
            ],
            'personal_training' => [
                'label' => 'Personal Training (PT)',
                'permissions' => [
                    ['name' => 'pt.view', 'display_name' => 'View PT Packages & Sessions', 'description' => 'View active packages, session counts & calendar'],
                    ['name' => 'pt.create', 'display_name' => 'Assign PT Package', 'description' => 'Sell and assign 1-on-1 personal training packages'],
                    ['name' => 'pt.sessions_manage', 'display_name' => 'Schedule & Log PT Sessions', 'description' => 'Schedule appointments and log completed sessions'],
                    ['name' => 'pt.delete', 'display_name' => 'Cancel PT Package', 'description' => 'Cancel or delete personal training packages'],
                    ['name' => 'pt.plans.manage', 'display_name' => 'Manage PT Plans Catalog', 'description' => 'Create, edit & delete PT plan tiers'],
                ],
            ],
            'fitness' => [
                'label' => 'Workouts, Diets & Extra Services',
                'permissions' => [
                    ['name' => 'workouts.view', 'display_name' => 'View Workout Plans', 'description' => 'Inspect exercise routines and workout splits'],
                    ['name' => 'workouts.create', 'display_name' => 'Create Workout Plan', 'description' => 'Create and assign workout routines to members'],
                    ['name' => 'workouts.edit', 'display_name' => 'Edit Workout Plan', 'description' => 'Modify sets, reps, weight & exercise instructions'],
                    ['name' => 'workouts.delete', 'display_name' => 'Delete Workout Plan', 'description' => 'Remove workout routines'],
                    ['name' => 'diets.view', 'display_name' => 'View Diet Plans', 'description' => 'View nutrition charts and diet plans'],
                    ['name' => 'diets.create', 'display_name' => 'Create & AI-Generate Diet', 'description' => 'Create meal timetables and generate AI diets'],
                    ['name' => 'diets.edit', 'display_name' => 'Edit Diet Plan', 'description' => 'Update target calories, macros & meal details'],
                    ['name' => 'diets.delete', 'display_name' => 'Delete Diet Plan', 'description' => 'Remove diet plans'],
                    ['name' => 'services.view', 'display_name' => 'View Extra Services', 'description' => 'View extra gym services and active bookings'],
                    ['name' => 'services.create', 'display_name' => 'Create Gym Service', 'description' => 'Add new services (Locker, Sauna, Steam, Physio)'],
                    ['name' => 'services.edit', 'display_name' => 'Edit Gym Service', 'description' => 'Update service pricing, duration & visibility'],
                    ['name' => 'services.delete', 'display_name' => 'Delete Gym Service', 'description' => 'Remove gym service catalog items'],
                    ['name' => 'services.book', 'display_name' => 'Book Service & Allocate Locker', 'description' => 'Record amenity bookings & allocate locker numbers'],
                ],
            ],
            'crm' => [
                'label' => 'CRM, Leads & Growth',
                'permissions' => [
                    ['name' => 'crm.view', 'display_name' => 'Access CRM Suite', 'description' => 'Access CRM dashboard and sales pipeline'],
                    ['name' => 'crm.leads.view', 'display_name' => 'View Leads & Enquiries', 'description' => 'Browse lead directory and follow-up status'],
                    ['name' => 'crm.leads.create', 'display_name' => 'Create Lead / Enquiry', 'description' => 'Add new walk-in or digital prospect inquiries'],
                    ['name' => 'crm.leads.edit', 'display_name' => 'Edit & Follow Up Lead', 'description' => 'Update stage, record call notes & assign staff'],
                    ['name' => 'crm.leads.delete', 'display_name' => 'Delete Lead', 'description' => 'Remove leads from CRM pipeline'],
                    ['name' => 'crm.trials', 'display_name' => 'Manage Free Trials', 'description' => 'Book trial visits and convert to member'],
                    ['name' => 'crm.reports', 'display_name' => 'View CRM Reports', 'description' => 'Inspect sales conversion analytics and reports'],
                ],
            ],
            'finance' => [
                'label' => 'Reports & Financial Management',
                'permissions' => [
                    ['name' => 'reports.member_report', 'display_name' => 'View Member Report', 'description' => 'Inspect member report and PDF export'],
                    ['name' => 'reports.balance_sheet', 'display_name' => 'View Financial Balance Sheet', 'description' => 'Access balance sheet, P&L & revenue statements'],
                    ['name' => 'expenses.view', 'display_name' => 'View Expenses', 'description' => 'Inspect expense logs and vendor bills'],
                    ['name' => 'expenses.create', 'display_name' => 'Record Expense / Bill', 'description' => 'Record expenses, bills, rent & staff payouts'],
                    ['name' => 'expenses.delete', 'display_name' => 'Delete Expense', 'description' => 'Remove expense records'],
                ],
            ],
            'inventory' => [
                'label' => 'Inventory & Equipment Maintenance',
                'permissions' => [
                    ['name' => 'inventory.view', 'display_name' => 'View Inventory & Equipment', 'description' => 'Inspect stock items and machine status'],
                    ['name' => 'inventory.create', 'display_name' => 'Add Inventory Item', 'description' => 'Register new supplement/merchandise products'],
                    ['name' => 'inventory.edit', 'display_name' => 'Adjust Stock & Edit Item', 'description' => 'Adjust stock quantities and update product details'],
                    ['name' => 'inventory.delete', 'display_name' => 'Delete Inventory Item', 'description' => 'Remove products from stock inventory'],
                    ['name' => 'equipment.maintenance', 'display_name' => 'Equipment Maintenance', 'description' => 'Log machine service, AC repair & technician notes'],
                ],
            ],
            'devices' => [
                'label' => 'Hikvision IoT Devices',
                'permissions' => [
                    ['name' => 'devices.view', 'display_name' => 'View Devices & Access Logs', 'description' => 'Monitor connected facial/turnstile devices and logs'],
                    ['name' => 'devices.manage', 'display_name' => 'Configure Hardware', 'description' => 'Add, test, and sync biometric hardware'],
                ],
            ],
            'whatsapp' => [
                'label' => 'WhatsApp Marketing & Automation',
                'permissions' => [
                    ['name' => 'whatsapp.view', 'display_name' => 'Access WhatsApp Dashboard', 'description' => 'View campaign analytics, metrics, delivery reports & message dispatch counts'],
                    ['name' => 'whatsapp.contacts', 'display_name' => 'Manage Contacts & Consent', 'description' => 'Create, edit, import CSV contacts, and manage WhatsApp marketing consent & opt-outs'],
                    ['name' => 'whatsapp.campaigns', 'display_name' => 'Create & Send Campaigns', 'description' => 'Launch bulk broadcasts, preview campaigns, schedule and view broadcast history'],
                    ['name' => 'whatsapp.templates', 'display_name' => 'Manage Templates', 'description' => 'Create, inspect, preview, and sync Meta message templates'],
                    ['name' => 'whatsapp.inbox', 'display_name' => 'WhatsApp Live Inbox', 'description' => 'Read and reply to 2-way customer WhatsApp messages in 24h service window'],
                    ['name' => 'whatsapp.settings', 'display_name' => 'WhatsApp API & Automation Config', 'description' => 'Configure Cloud API credentials, webhooks, and trigger event automations'],
                ],
            ],
            'administration' => [
                'label' => 'Administration & Team Roles',
                'permissions' => [
                    ['name' => 'staff.view', 'display_name' => 'View Staff Directory', 'description' => 'View staff directory and profiles'],
                    ['name' => 'staff.create', 'display_name' => 'Create Staff Account', 'description' => 'Add staff login accounts and initial roles'],
                    ['name' => 'staff.edit', 'display_name' => 'Edit Staff Account', 'description' => 'Update staff credentials and role assignments'],
                    ['name' => 'staff.delete', 'display_name' => 'Delete Staff Account', 'description' => 'Remove staff user accounts'],
                    ['name' => 'roles.manage', 'display_name' => 'Manage Roles & Permissions', 'description' => 'Customize permissions matrix for all roles'],
                    ['name' => 'settings.manage', 'display_name' => 'Manage Gym Settings', 'description' => 'Update gym branding, tax, GSTIN & location info'],
                ],
            ],
        ];
    }

    public static function getPermissionFeatureMap(): array
    {
        return [
            // Dashboard
            'dashboard.view' => null,
            'dashboard.financials' => 'reports_finance',
            'dashboard.member_stats' => 'members_management',
            'dashboard.attendance_stats' => 'attendance_checkin',

            // Members
            'members.view' => 'members_management',
            'members.create' => 'members_management',
            'members.edit' => 'members_management',
            'members.delete' => 'members_management',
            'members.freeze' => 'members_management',
            'members.measurements' => 'members_management',

            // Memberships & Plans
            'memberships.view' => 'memberships_billing',
            'memberships.create' => 'memberships_billing',
            'memberships.delete' => 'memberships_billing',
            'plans.view' => 'memberships_billing',
            'plans.create' => 'memberships_billing',
            'plans.edit' => 'memberships_billing',
            'plans.delete' => 'memberships_billing',

            // Payments
            'payments.view' => 'payments_pos',
            'payments.record' => 'payments_pos',
            'payments.refund' => 'payments_pos',
            'payments.invoice_view' => 'payments_pos',

            // Attendance
            'attendance.view' => 'attendance_checkin',
            'attendance.checkin' => 'attendance_checkin',

            // Training & Group Classes
            'classes.view' => 'group_classes',
            'classes.create' => 'group_classes',
            'classes.edit' => 'group_classes',
            'classes.delete' => 'group_classes',
            'classes.book' => 'group_classes',

            // Trainers
            'trainers.view' => 'trainers_management',
            'trainers.create' => 'trainers_management',
            'trainers.edit' => 'trainers_management',
            'trainers.assign' => 'trainers_management',
            'trainers.delete' => 'trainers_management',

            // Personal Training
            'pt.view' => 'personal_training',
            'pt.create' => 'personal_training',
            'pt.sessions_manage' => 'personal_training',
            'pt.delete' => 'personal_training',
            'pt.plans.manage' => 'personal_training',

            // Fitness & Services
            'workouts.view' => 'workout_plans',
            'workouts.create' => 'workout_plans',
            'workouts.edit' => 'workout_plans',
            'workouts.delete' => 'workout_plans',
            'diets.view' => 'diet_nutrition',
            'diets.create' => 'diet_nutrition',
            'diets.edit' => 'diet_nutrition',
            'diets.delete' => 'diet_nutrition',
            'services.view' => 'gym_services',
            'services.create' => 'gym_services',
            'services.edit' => 'gym_services',
            'services.delete' => 'gym_services',
            'services.book' => 'gym_services',

            // CRM & Growth
            'crm.view' => 'crm_leads',
            'crm.leads.view' => 'crm_leads',
            'crm.leads.create' => 'crm_leads',
            'crm.leads.edit' => 'crm_leads',
            'crm.leads.delete' => 'crm_leads',
            'crm.trials' => 'crm_leads',
            'crm.reports' => 'crm_leads',

            // WhatsApp Marketing & Automation
            'whatsapp.view' => 'whatsapp_marketing',
            'whatsapp.contacts' => 'whatsapp_marketing',
            'whatsapp.campaigns' => 'whatsapp_marketing',
            'whatsapp.templates' => 'whatsapp_marketing',
            'whatsapp.inbox' => 'whatsapp_marketing',
            'whatsapp.settings' => 'whatsapp_marketing',

            // Reports & Finance
            'reports.member_report' => 'reports_finance',
            'reports.balance_sheet' => 'reports_finance',
            'expenses.view' => 'reports_finance',
            'expenses.create' => 'reports_finance',
            'expenses.delete' => 'reports_finance',

            // Inventory & Maintenance
            'inventory.view' => 'inventory_stock',
            'inventory.create' => 'inventory_stock',
            'inventory.edit' => 'inventory_stock',
            'inventory.delete' => 'inventory_stock',
            'equipment.maintenance' => 'equipment_maintenance',

            // Devices IoT
            'devices.view' => 'hikvision_iot',
            'devices.manage' => 'hikvision_iot',

            // Administration
            'staff.view' => 'staff_roles',
            'staff.create' => 'staff_roles',
            'staff.edit' => 'staff_roles',
            'staff.delete' => 'staff_roles',
            'roles.manage' => 'staff_roles',
            'settings.manage' => null,
        ];
    }

    public static function getDefaultRolePermissions(): array
    {
        return [
            'gym_manager' => [
                'dashboard.view', 'dashboard.financials', 'dashboard.member_stats', 'dashboard.attendance_stats',
                'members.view', 'members.create', 'members.edit', 'members.freeze', 'members.measurements',
                'memberships.view', 'memberships.create', 'plans.view', 'plans.create', 'plans.edit',
                'payments.view', 'payments.record', 'payments.invoice_view',
                'attendance.view', 'attendance.checkin',
                'classes.view', 'classes.create', 'classes.edit', 'classes.book',
                'trainers.view', 'trainers.create', 'trainers.edit', 'trainers.assign',
                'pt.view', 'pt.create', 'pt.sessions_manage', 'pt.plans.manage',
                'workouts.view', 'workouts.create', 'workouts.edit',
                'diets.view', 'diets.create', 'diets.edit',
                'services.view', 'services.create', 'services.edit', 'services.book',
                'crm.view', 'crm.leads.view', 'crm.leads.create', 'crm.leads.edit', 'crm.trials', 'crm.reports',
                'whatsapp.view', 'whatsapp.contacts', 'whatsapp.campaigns', 'whatsapp.templates', 'whatsapp.inbox', 'whatsapp.settings',
                'reports.member_report',
                'inventory.view', 'inventory.create', 'inventory.edit', 'equipment.maintenance',
                'devices.view',
                'staff.view',
            ],
            'receptionist' => [
                'dashboard.view', 'dashboard.member_stats', 'dashboard.attendance_stats',
                'members.view', 'members.create', 'members.edit', 'members.measurements',
                'memberships.view', 'memberships.create', 'plans.view',
                'payments.view', 'payments.record', 'payments.invoice_view',
                'attendance.view', 'attendance.checkin',
                'classes.view', 'classes.book', 'trainers.view', 'trainers.assign',
                'pt.view', 'pt.create', 'pt.sessions_manage',
                'services.view', 'services.book',
                'crm.view', 'crm.leads.view', 'crm.leads.create', 'crm.leads.edit', 'crm.trials',
                'whatsapp.view', 'whatsapp.contacts', 'whatsapp.inbox',
                'inventory.view',
            ],
            'trainer' => [
                'dashboard.view', 'dashboard.member_stats', 'dashboard.attendance_stats',
                'members.view', 'members.measurements',
                'classes.view', 'trainers.view',
                'pt.view', 'pt.sessions_manage',
                'workouts.view', 'workouts.create', 'workouts.edit',
                'diets.view', 'diets.create', 'diets.edit',
                'attendance.view',
            ],
            'accountant' => [
                'dashboard.view', 'dashboard.financials',
                'members.view',
                'memberships.view', 'plans.view',
                'payments.view', 'payments.record', 'payments.refund', 'payments.invoice_view',
                'reports.member_report', 'reports.balance_sheet',
                'expenses.view', 'expenses.create', 'expenses.delete',
                'inventory.view',
            ],
            'staff' => [
                'dashboard.view', 'dashboard.attendance_stats',
                'members.view',
                'attendance.view', 'attendance.checkin',
                'classes.view',
                'inventory.view',
            ],
        ];
    }

    protected static bool $syncedThisRequest = false;

    public static function getDeclaredPermissionCount(): int
    {
        $count = 0;
        foreach (self::getPermissionsGrouped() as $group) {
            $count += count($group['permissions']);
        }

        return $count;
    }

    /**
     * Ensure all declared permissions and default role mappings exist in database.
     * Runs fast with lightweight count check so it can be called safely without overhead.
     */
    public static function ensureSynced(): void
    {
        if (self::$syncedThisRequest) {
            return;
        }

        $declaredCount = self::getDeclaredPermissionCount();
        $dbCount = Permission::count();

        if ($dbCount < $declaredCount) {
            self::sync();
        }

        self::$syncedThisRequest = true;
    }

    /**
     * Auto-sync all declared permissions and default role bindings for all gyms/tenants.
     * Automatically inserts new permissions into the permissions table,
     * links default permissions to existing system roles without overriding custom tweaks,
     * and guarantees that the features table and plan mappings are up to date.
     */
    public static function sync(bool $forceRoleDefaults = false): void
    {
        $groups = self::getPermissionsGrouped();
        $allPermMap = [];

        foreach ($groups as $groupKey => $groupData) {
            foreach ($groupData['permissions'] as $p) {
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

        // Keep existing role assignments and attach newly introduced default permissions to system roles
        $tenants = Tenant::all();
        $defaultMappings = self::getDefaultRolePermissions();

        foreach ($tenants as $tenant) {
            self::syncForTenant($tenant, $allPermMap, $defaultMappings, $forceRoleDefaults);
        }

        // Ensure SaaS feature existence & plan mapping
        self::syncFeatures();

        self::$syncedThisRequest = true;
    }

    /**
     * Synchronize permissions and default roles for a specific tenant.
     */
    public static function syncForTenant(Tenant $tenant, ?array $allPermMap = null, ?array $defaultMappings = null, bool $force = false): void
    {
        if ($allPermMap === null) {
            $allPermMap = Permission::pluck('id', 'name')->toArray();
        }
        if ($defaultMappings === null) {
            $defaultMappings = self::getDefaultRolePermissions();
        }

        foreach ($defaultMappings as $roleSlug => $permNames) {
            $role = Role::withoutGlobalScopes()->where('tenant_id', $tenant->id)->where('name', $roleSlug)->first();
            if (! $role) {
                $role = Role::withoutGlobalScopes()->create([
                    'tenant_id' => $tenant->id,
                    'name' => $roleSlug,
                    'display_name' => match ($roleSlug) {
                        'gym_manager' => 'Gym Manager',
                        'receptionist' => 'Receptionist',
                        'trainer' => 'Fitness Trainer',
                        'accountant' => 'Accountant',
                        'staff' => 'General Staff',
                        default => ucfirst($roleSlug),
                    },
                    'is_system' => true,
                ]);

                $permIds = [];
                foreach ($permNames as $pName) {
                    if (isset($allPermMap[$pName])) {
                        $permIds[] = $allPermMap[$pName];
                    }
                }
                $role->permissions()->sync($permIds);
            } else {
                // If role already exists, attach any newly added default permissions without overriding customizations
                $existingPermIds = $role->permissions()->pluck('permissions.id')->toArray();
                $newPermIds = [];
                foreach ($permNames as $pName) {
                    if (isset($allPermMap[$pName]) && ! in_array($allPermMap[$pName], $existingPermIds)) {
                        $newPermIds[] = $allPermMap[$pName];
                    }
                }
                if (! empty($newPermIds)) {
                    $role->permissions()->syncWithoutDetaching($newPermIds);
                }
            }
        }
    }

    /**
     * Ensure the whatsapp_marketing feature exists in features table and is attached to plans.
     */
    public static function syncFeatures(): void
    {
        $whatsappFeature = Feature::firstOrCreate(
            ['code' => 'whatsapp_marketing'],
            [
                'name' => 'WhatsApp Marketing & Automation',
                'description' => 'Official Meta Cloud API bulk campaigns, 2-way inbox, automated member alerts & templates',
                'type' => 'boolean',
            ]
        );

        // Ensure Starter, Pro and all active plans have whatsapp_marketing attached
        $plans = Plan::all();
        foreach ($plans as $plan) {
            if (! $plan->features()->where('features.id', $whatsappFeature->id)->exists()) {
                $plan->features()->attach($whatsappFeature->id, ['value' => '1']);
            }
        }
    }

    public function run(): void
    {
        self::sync(true);
    }
}
