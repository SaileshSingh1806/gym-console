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
    public function autoCheckoutStaleCheckins(?int $tenantId = null): int
    {
        $cutoff = now()->subMinutes(60);

        $query = Attendance::withoutGlobalScopes()
            ->whereNull('check_out')
            ->where('check_in', '<=', $cutoff);

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        $records = $query->get();
        $count = 0;

        foreach ($records as $record) {
            $checkInTime = Carbon::parse($record->check_in);
            $record->update([
                'check_out' => $checkInTime->copy()->addMinutes(60),
                'status' => 'AUTO_CHECKOUT',
            ]);
            $count++;
        }

        return $count;
    }

    public function checkIn(Member $member, string $method = 'manual', ?Device $device = null, ?Carbon $time = null): Attendance
    {
        $time = $time ?? now();
        $today = $time->toDateString();

        $validMethods = ['manual', 'qr', 'biometric', 'facial'];
        $normalizedMethod = in_array(strtolower($method), $validMethods, true) ? strtolower($method) : (str_contains(strtolower($method), 'qr') ? 'qr' : 'manual');

        return DB::transaction(function () use ($member, $normalizedMethod, $device, $time, $today) {
            $existing = Attendance::withoutGlobalScopes()
                ->where('tenant_id', $member->tenant_id)
                ->where('member_id', $member->id)
                ->where('date', $today)
                ->whereNull('check_out')
                ->latest('id')
                ->first();

            if ($existing) {
                $checkInTime = Carbon::parse($existing->check_in);
                // If member checked in more than 60 minutes ago, auto-checkout previous session and allow new check-in!
                if ($checkInTime->diffInMinutes($time) >= 60) {
                    $existing->update([
                        'check_out' => $checkInTime->copy()->addMinutes(60),
                        'status' => 'AUTO_CHECKOUT',
                    ]);
                } else {
                    // Already inside within 60 minutes, return active record
                    return $existing;
                }
            }

            $attendance = Attendance::create([
                'tenant_id' => $member->tenant_id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'device_id' => $device?->id,
                'date' => $today,
                'check_in' => $time,
                'method' => $normalizedMethod,
                'status' => 'PRESENT',
            ]);

            ActivityLog::log('member_checkin', "Member {$member->full_name} checked in via {$normalizedMethod}", $attendance);

            return $attendance;
        });
    }

    public function checkOut(Member $member, ?Carbon $time = null): ?Attendance
    {
        $time = $time ?? now();
        $today = $time->toDateString();

        return DB::transaction(function () use ($member, $time, $today) {
            $attendance = Attendance::withoutGlobalScopes()
                ->where('tenant_id', $member->tenant_id)
                ->where('member_id', $member->id)
                ->whereNull('check_out')
                ->latest('id')
                ->first();

            if (! $attendance) {
                $attendance = Attendance::withoutGlobalScopes()
                    ->where('tenant_id', $member->tenant_id)
                    ->where('member_id', $member->id)
                    ->where('date', $today)
                    ->latest('id')
                    ->first();
            }

            if ($attendance) {
                $attendance->update(['check_out' => $time]);
                ActivityLog::log('member_checkout', "Member {$member->full_name} checked out", $attendance);
            }

            return $attendance;
        });
    }

    public function checkOutById(int|Attendance $attendance, ?Carbon $time = null): ?Attendance
    {
        $time = $time ?? now();

        return DB::transaction(function () use ($attendance, $time) {
            $att = $attendance instanceof Attendance
                ? $attendance
                : Attendance::withoutGlobalScopes()->findOrFail($attendance);

            $att->update(['check_out' => $time]);

            ActivityLog::log('member_checkout', "Member {$att->member?->full_name} checked out", $att);

            return $att;
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

    public function getTodaySummary(?int $branchId = null, ?int $tenantId = null): array
    {
        $this->autoCheckoutStaleCheckins($tenantId);

        $query = Attendance::where('date', now()->toDateString());

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

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
