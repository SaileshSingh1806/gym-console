<?php

namespace App\Services\AccessControl;

use App\Models\AccessLog;
use App\Models\Device;
use App\Models\Member;
use App\Services\AttendanceService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccessControlService
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function getDriver(Device $device): DeviceDriverInterface
    {
        return match ($device->type) {
            'hikvision_facial', 'hikvision_turnstile' => new HikvisionISAPIDriver,
            'essl_desktop' => new EsslDesktopDriver,
            'zkteco_biometric' => new ZktecoDriver,
            default => new GenericDeviceDriver,
        };
    }

    public function processDeviceEvent(Device $device, array $payload): array
    {
        $driver = $this->getDriver($device);
        $parsed = $driver->parseEventPayload($payload);

        $memberCode = $parsed['member_code'] ?? null;
        $cardNo = $parsed['card_no'] ?? null;
        $eventTime = isset($parsed['event_time']) ? Carbon::parse($parsed['event_time']) : now();

        // Find member within tenant
        $member = null;
        if ($memberCode) {
            $member = Member::where('tenant_id', $device->tenant_id)->where('member_code', $memberCode)->first();
        }
        if (! $member && $cardNo) {
            $member = Member::where('tenant_id', $device->tenant_id)
                ->where(function ($q) use ($cardNo) {
                    $q->where('qr_code_token', $cardNo)
                        ->orWhereJsonContains('metadata->card_number', $cardNo);
                })->first();
        }

        return DB::transaction(function () use ($device, $member, $eventTime, $parsed, $driver) {
            if (! $member) {
                $log = AccessLog::create([
                    'tenant_id' => $device->tenant_id,
                    'branch_id' => $device->branch_id,
                    'device_id' => $device->id,
                    'member_id' => null,
                    'event_type' => 'DENIED',
                    'access_status' => 'DENIED_UNRECOGNIZED',
                    'event_time' => $eventTime,
                    'card_or_face_id' => $parsed['card_no'] ?? $parsed['face_id'] ?? null,
                    'raw_event_payload' => json_encode($parsed['raw']),
                    'metadata' => ['reason' => 'Unrecognized member credential'],
                ]);

                return ['granted' => false, 'reason' => 'Unrecognized credential', 'log_id' => $log->id];
            }

            // Check branch match
            if ($member->branch_id !== $device->branch_id) {
                $log = AccessLog::create([
                    'tenant_id' => $device->tenant_id,
                    'branch_id' => $device->branch_id,
                    'device_id' => $device->id,
                    'member_id' => $member->id,
                    'event_type' => 'DENIED',
                    'access_status' => 'DENIED_WRONG_BRANCH',
                    'event_time' => $eventTime,
                    'raw_event_payload' => json_encode($parsed['raw']),
                    'metadata' => ['reason' => 'Member not enrolled in this branch'],
                ]);

                return ['granted' => false, 'reason' => 'Wrong branch', 'member' => $member->full_name];
            }

            // Check member status
            if ($member->status !== 'ACTIVE') {
                $statusMap = [
                    'SUSPENDED' => 'DENIED_SUSPENDED',
                    'EXPIRED' => 'DENIED_EXPIRED',
                ];

                $log = AccessLog::create([
                    'tenant_id' => $device->tenant_id,
                    'branch_id' => $device->branch_id,
                    'device_id' => $device->id,
                    'member_id' => $member->id,
                    'event_type' => 'DENIED',
                    'access_status' => $statusMap[$member->status] ?? 'DENIED_INACTIVE',
                    'event_time' => $eventTime,
                    'raw_event_payload' => json_encode($parsed['raw']),
                    'metadata' => ['reason' => "Member status is {$member->status}"],
                ]);

                return ['granted' => false, 'reason' => "Member is {$member->status}", 'member' => $member->full_name];
            }

            // Check active membership contract
            if (! $member->isMembershipActive()) {
                $log = AccessLog::create([
                    'tenant_id' => $device->tenant_id,
                    'branch_id' => $device->branch_id,
                    'device_id' => $device->id,
                    'member_id' => $member->id,
                    'event_type' => 'DENIED',
                    'access_status' => 'DENIED_EXPIRED',
                    'event_time' => $eventTime,
                    'raw_event_payload' => json_encode($parsed['raw']),
                    'metadata' => ['reason' => 'No active membership plan found'],
                ]);

                return ['granted' => false, 'reason' => 'Membership expired', 'member' => $member->full_name];
            }

            // ACCESS GRANTED
            $eventType = $device->direction === 'out' ? 'EXIT' : 'ENTRY';

            $log = AccessLog::create([
                'tenant_id' => $device->tenant_id,
                'branch_id' => $device->branch_id,
                'device_id' => $device->id,
                'member_id' => $member->id,
                'event_type' => $eventType,
                'access_status' => 'GRANTED',
                'event_time' => $eventTime,
                'raw_event_payload' => json_encode($parsed['raw']),
            ]);

            // Auto-Attendance
            $attendance = $this->attendanceService->recordBiometricAttendance($member, $device, $eventTime, $eventType);

            // Trigger physical unlock if turnstile
            $driver->openDoor($device);

            return [
                'granted' => true,
                'member' => $member->full_name,
                'event_type' => $eventType,
                'log_id' => $log->id,
                'attendance_id' => $attendance->id,
            ];
        });
    }
}
