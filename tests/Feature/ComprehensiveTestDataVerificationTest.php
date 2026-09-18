<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Member;
use App\Models\MemberPtPackage;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\Trainer;
use App\Models\User;
use Carbon\Carbon;
use Tests\TestCase;

class ComprehensiveTestDataVerificationTest extends TestCase
{
    public function test_seeded_data_completeness_and_rbac_boundaries(): void
    {
        $tenant = Tenant::find(54);
        $this->assertNotNull($tenant, 'Tenant 54 should exist.');

        // 1. Members count
        $totalMembers = Member::where('tenant_id', 54)->count();
        $this->assertEquals(50, $totalMembers, 'Should have exactly 50 members.');

        // 2. Expired members
        $expiredCount = Member::where('tenant_id', 54)->where('status', 'EXPIRED')->count();
        $this->assertGreaterThanOrEqual(10, $expiredCount, 'Should have at least 10 expired members.');

        // 3. Expiring Today
        $todayStr = Carbon::today()->toDateString();
        $expiringToday = Membership::where('tenant_id', 54)->where('end_date', $todayStr)->count();
        $this->assertGreaterThanOrEqual(3, $expiringToday, 'Should have at least 3 memberships expiring today.');

        // 4. Expiring Tomorrow
        $tomorrowStr = Carbon::tomorrow()->toDateString();
        $expiringTomorrow = Membership::where('tenant_id', 54)->where('end_date', $tomorrowStr)->count();
        $this->assertGreaterThanOrEqual(3, $expiringTomorrow, 'Should have at least 3 memberships expiring tomorrow.');

        // 5. Birthday Today
        $birthdayToday = Member::where('tenant_id', 54)->whereRaw("DATE_FORMAT(dob, '%m-%d') = DATE_FORMAT(NOW(), '%m-%d')")->count();
        $this->assertGreaterThanOrEqual(3, $birthdayToday, 'Should have at least 3 members with birthday today.');

        // 6. Half / Partial Payments
        $halfPaid = Membership::where('tenant_id', 54)->whereRaw('paid_amount < final_amount')->count();
        $this->assertGreaterThanOrEqual(12, $halfPaid, 'Should have at least 12 partial/half-paid memberships.');

        // 7. Full Payments
        $fullPaid = Membership::where('tenant_id', 54)->whereRaw('paid_amount >= final_amount')->count();
        $this->assertGreaterThanOrEqual(30, $fullPaid, 'Should have at least 30 fully-paid memberships.');

        // 8. Personal Training (PT) Packages
        $ptPackages = MemberPtPackage::where('tenant_id', 54)->count();
        $this->assertGreaterThanOrEqual(15, $ptPackages, 'Should have at least 15 members with PT packages.');

        // 9. Trainers
        $trainers = Trainer::where('tenant_id', 54)->get();
        $this->assertCount(2, $trainers, 'Should have 2 trainers (General & PT).');
        foreach ($trainers as $trainer) {
            $this->assertGreaterThan(0, (float) $trainer->salary, 'Trainer should have salary configured.');
        }

        // 10. Receptionist permissions
        $receptionist = User::where('tenant_id', 54)->where('email', 'receptionist@armour247.com')->first();
        $this->assertNotNull($receptionist, 'Receptionist user should exist.');
        $this->assertTrue($receptionist->hasPermission('members.view'), 'Receptionist should have members.view permission.');
        $this->assertTrue($receptionist->hasPermission('members.create'), 'Receptionist should have members.create permission.');
        $this->assertFalse($receptionist->hasPermission('crm.view'), 'Receptionist should NOT have crm.view permission.');
        $this->assertFalse($receptionist->hasPermission('reports.balance_sheet'), 'Receptionist should NOT have reports.balance_sheet permission.');
        $this->assertGreaterThan(0, $receptionist->metadata['monthly_salary'] ?? 0, 'Receptionist should have monthly salary set.');

        // 11. Sales Manager permissions
        $salesManager = User::where('tenant_id', 54)->where('email', 'sales@armour247.com')->first();
        $this->assertNotNull($salesManager, 'Sales Manager user should exist.');
        $this->assertTrue($salesManager->hasPermission('crm.view'), 'Sales Manager should have crm.view permission.');
        $this->assertTrue($salesManager->hasPermission('crm.manage'), 'Sales Manager should have crm.manage permission.');
        $this->assertFalse($salesManager->hasPermission('members.view'), 'Sales Manager should NOT have members.view permission.');
        $this->assertFalse($salesManager->hasPermission('payments.view'), 'Sales Manager should NOT have payments.view permission.');
        $this->assertFalse($salesManager->hasPermission('reports.balance_sheet'), 'Sales Manager should NOT have reports.balance_sheet permission.');
        $this->assertGreaterThan(0, $salesManager->metadata['monthly_salary'] ?? 0, 'Sales Manager should have monthly salary set.');

        // 12. Housekeeping / Maintenance Staff
        $cleaningUsers = User::where('tenant_id', 54)->where('email', 'like', '%.cleaning@armour247.com')->orWhere('email', 'like', '%.maintenance@armour247.com')->get();
        $this->assertGreaterThanOrEqual(3, $cleaningUsers->count(), 'Should have at least 3 housekeeping/maintenance staff.');
        foreach ($cleaningUsers as $cleaner) {
            $this->assertGreaterThan(0, $cleaner->metadata['monthly_salary'] ?? 0, 'Housekeeping staff should have monthly salary set.');
        }

        // 13. Attendance & Dormant Members
        $attendanceCount = Attendance::where('tenant_id', 54)->count();
        $this->assertGreaterThan(50, $attendanceCount, 'Should have attendance logs seeded for active members.');

        $dormantCount = Member::where('tenant_id', 54)
            ->where('status', 'ACTIVE')
            ->whereDoesntHave('attendances', function ($q) {
                $q->where('date', '>=', now()->subDays(14)->toDateString());
            })->count();
        $this->assertEquals(6, $dormantCount, 'Should have exactly 6 dormant active members instead of 40.');
    }
}
