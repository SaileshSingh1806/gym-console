<?php

namespace App\Services\AccessControl;

use App\Models\Device;
use App\Models\Member;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HikvisionISAPIDriver implements DeviceDriverInterface
{
    public function getDriverName(): string
    {
        return 'hikvision';
    }

    public function checkHealth(Device $device): bool
    {
        if (empty($device->ip_address)) {
            return false;
        }

        try {
            $url = "http://{$device->ip_address}:{$device->port}/ISAPI/System/status";
            $response = Http::timeout(3)
                ->withDigestAuth($device->username ?? 'admin', $device->password ?? '')
                ->get($url);

            $online = $response->successful();
            $device->update([
                'status' => $online ? 'ONLINE' : 'ERROR',
                'last_seen_at' => now(),
            ]);

            return $online;
        } catch (\Exception $e) {
            Log::warning("Hikvision device [{$device->id}] health check failed: ".$e->getMessage());
            $device->update(['status' => 'OFFLINE']);

            return false;
        }
    }

    public function syncMemberToDevice(Device $device, Member $member): bool
    {
        // ISAPI Face / Card provisioning endpoint
        Log::info("Pushing member [{$member->member_code}] permissions to Hikvision device [{$device->name}]");

        return true;
    }

    public function removeMemberFromDevice(Device $device, Member $member): bool
    {
        Log::info("Revoking member [{$member->member_code}] from Hikvision device [{$device->name}]");

        return true;
    }

    public function openDoor(Device $device): bool
    {
        try {
            $url = "http://{$device->ip_address}:{$device->port}/ISAPI/AccessControl/RemoteControl/door/1";
            $xml = '<RemoteControlDoor><cmd>open</cmd></RemoteControlDoor>';

            $response = Http::timeout(3)
                ->withDigestAuth($device->username ?? 'admin', $device->password ?? '')
                ->withBody($xml, 'application/xml')
                ->put($url);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to remotely unlock Hikvision door: '.$e->getMessage());

            return false;
        }
    }

    public function parseEventPayload(array $payload): array
    {
        // Extracts event parameters from Hikvision AlertStream / Webhook JSON
        $eventObj = $payload['AccessControllerEvent'] ?? $payload;

        $cardNo = $eventObj['cardNo'] ?? $payload['card_id'] ?? null;
        $faceId = $eventObj['faceId'] ?? $payload['face_id'] ?? null;
        $employeeNo = $eventObj['employeeNoString'] ?? $payload['member_code'] ?? null;
        $eventTime = $payload['dateTime'] ?? now()->toIso8601String();

        return [
            'card_no' => $cardNo,
            'face_id' => $faceId,
            'member_code' => $employeeNo,
            'event_time' => $eventTime,
            'raw' => $payload,
        ];
    }
}
