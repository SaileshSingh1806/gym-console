<?php

namespace App\Services\AccessControl;

use App\Models\Device;
use App\Models\Member;

class GenericDeviceDriver implements DeviceDriverInterface
{
    public function getDriverName(): string
    {
        return 'generic';
    }

    public function checkHealth(Device $device): bool
    {
        $device->update(['status' => 'ONLINE', 'last_seen_at' => now()]);

        return true;
    }

    public function syncMemberToDevice(Device $device, Member $member): bool
    {
        return true;
    }

    public function removeMemberFromDevice(Device $device, Member $member): bool
    {
        return true;
    }

    public function openDoor(Device $device): bool
    {
        return true;
    }

    public function parseEventPayload(array $payload): array
    {
        return [
            'card_no' => $payload['card_id'] ?? null,
            'face_id' => $payload['face_id'] ?? null,
            'member_code' => $payload['member_code'] ?? null,
            'event_time' => $payload['timestamp'] ?? now()->toIso8601String(),
            'raw' => $payload,
        ];
    }
}
