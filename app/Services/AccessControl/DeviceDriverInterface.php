<?php

namespace App\Services\AccessControl;

use App\Models\Device;
use App\Models\Member;

interface DeviceDriverInterface
{
    public function getDriverName(): string;

    /**
     * Test connection/ping to device
     */
    public function checkHealth(Device $device): bool;

    /**
     * Push member credentials (face token / card ID) to the biometric device
     */
    public function syncMemberToDevice(Device $device, Member $member): bool;

    /**
     * Remove member permissions from physical device
     */
    public function removeMemberFromDevice(Device $device, Member $member): bool;

    /**
     * Remotely trigger door/turnstile unlock
     */
    public function openDoor(Device $device): bool;

    /**
     * Parse incoming webhook payload from physical device
     */
    public function parseEventPayload(array $payload): array;
}
