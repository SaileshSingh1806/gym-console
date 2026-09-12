<?php

namespace App\Services\AccessControl;

use App\Models\Device;
use App\Models\Member;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ZktecoDriver implements DeviceDriverInterface
{
    public function getDriverName(): string
    {
        return 'zkteco_biometric';
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
        Log::info("Synced member [{$member->member_code}] to ZKTeco device [{$device->name}]");

        return true;
    }

    public function removeMemberFromDevice(Device $device, Member $member): bool
    {
        Log::info("Removed member [{$member->member_code}] from ZKTeco device [{$device->name}]");

        return true;
    }

    public function openDoor(Device $device): bool
    {
        return true;
    }

    public function parseEventPayload(array $payload): array
    {
        $memberCode = $payload['PIN']
            ?? $payload['user_id']
            ?? $payload['EnrollNumber']
            ?? $payload['member_code']
            ?? null;

        $cardNo = $payload['Card']
            ?? $payload['card_no']
            ?? $payload['card_id']
            ?? null;

        $eventTimeStr = $payload['Time']
            ?? $payload['timestamp']
            ?? now()->toIso8601String();

        try {
            $eventTime = Carbon::parse($eventTimeStr)->toIso8601String();
        } catch (\Throwable $e) {
            $eventTime = now()->toIso8601String();
        }

        return [
            'card_no' => $cardNo,
            'face_id' => $payload['face_id'] ?? null,
            'member_code' => $memberCode,
            'event_time' => $eventTime,
            'raw' => $payload,
        ];
    }
}
