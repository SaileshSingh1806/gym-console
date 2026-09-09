<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Attendance;
use App\Models\Device;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function checkIn(Member $member, string $method = 'manual', ?Device $device = null, ?Carbon $time = null): Attendance
    {
        $time = $time ?? now();
        $today = $time->toDateString();

        return DB::transaction(function () use ($member, $method, $device, $time, $today) {
            $existing = Attendance::where('tenant_id', $member->tenant_id)
                ->where('member_id', $member->id)
                ->where('date', $today)
                ->first();

            if ($existing) {
                // Already checked in today, update if no check in time
                if (! $existing->check_in) {
                    $existing->update(['check_in' => $time]);
                }

                return $existing;
            }

            $attendance = Attendance::create([
                'tenant_id' => $member->tenant_id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'device_id' => $device?->id,
                'date' => $today,
                'check_in' => $time,
                'method' => $method,
                'status' => 'PRESENT',
            ]);

            ActivityLog::log('member_checkin', "Member {$member->full_name} checked in via {$method}", $attendance);

            return $attendance;
        });
    }

    public function checkOut(Member $member, ?Carbon $time = null): ?Attendance
    {
        $time = $time ?? now();
        $today = $time->toDateString();

        return DB::transaction(function () use ($member, $time, $today) {
            $attendance = Attendance::where('tenant_id', $member->tenant_id)
                ->where('member_id', $member->id)
                ->where('date', $today)
                ->first();

            if ($attendance) {
                $attendance->update(['check_out' => $time]);
                ActivityLog::log('member_checkout', "Member {$member->full_name} checked out", $attendance);
            }

            return $attendance;
        });
    }

    public function recordBiometricAttendance(Member $member, Device $device, Carbon $eventTime, string $eventType): Attendance
    {
        $method = in_array($device->type, ['hikvision_facial']) ? 'facial' : 'biometric';

        if ($eventType === 'EXIT') {
            $att = $this->checkOut($member, $eventTime);

            return $att ?? $this->checkIn($member, $method, $device, $eventTime);
        }

        return $this->checkIn($member, $method, $device, $eventTime);
    }

    public function getTodaySummary(?int $branchId = null): array
    {
        $query = Attendance::where('date', now()->toDateString());

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $totalCheckedIn = (clone $query)->count();
        $currentlyInside = (clone $query)->whereNull('check_out')->count();
        $checkedOut = (clone $query)->whereNotNull('check_out')->count();

        return [
            'total_present' => $totalCheckedIn,
            'currently_inside' => $currentlyInside,
            'checked_out' => $checkedOut,
        ];
    }
}
