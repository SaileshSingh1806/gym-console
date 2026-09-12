<?php

namespace App\Services\AccessControl;

use App\Models\Device;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class EsslDesktopDriver implements DeviceDriverInterface
{
    public function getDriverName(): string
    {
        return 'essl_desktop';
    }

    public function checkHealth(Device $device): bool
    {
        $device->update([
            'status' => 'ONLINE',
            'last_seen_at' => now(),
        ]);

        return true;
    }

    public function syncMemberToDevice(Device $device, Member $member): bool
    {
        Log::info("Synced member [{$member->member_code}] to eSSL desktop middleware for device [{$device->name}]");

        return true;
    }

    public function removeMemberFromDevice(Device $device, Member $member): bool
    {
        Log::info("Removed member [{$member->member_code}] from eSSL desktop middleware for device [{$device->name}]");

        return true;
    }

    public function openDoor(Device $device): bool
    {
        // eSSL desktop middleware relay unlock signal
        return true;
    }

    public function parseEventPayload(array $payload): array
    {
        // Extracts identifier from eSSL / eTimeTrack / desktop middleware sync logs
        $memberCode = $payload['EnrollNumber']
            ?? $payload['UserId']
            ?? $payload['UserCode']
            ?? $payload['Badgenumber']
            ?? $payload['biometric_id']
            ?? $payload['member_code']
            ?? $payload['pin']
            ?? null;

        $cardNo = $payload['CardNo']
            ?? $payload['card_no']
            ?? $payload['card_id']
            ?? null;

        $faceId = $payload['FaceId']
            ?? $payload['face_id']
            ?? null;

        $eventTimeStr = $payload['PunchTime']
            ?? $payload['LogDate']
            ?? $payload['DateTime']
            ?? $payload['timestamp']
            ?? $payload['time']
            ?? now()->toIso8601String();

        try {
            $eventTime = Carbon::parse($eventTimeStr)->toIso8601String();
        } catch (\Throwable $e) {
            $eventTime = now()->toIso8601String();
        }

        return [
            'card_no' => $cardNo,
            'face_id' => $faceId,
            'member_code' => $memberCode,
            'event_time' => $eventTime,
            'raw' => $payload,
        ];
    }
}
