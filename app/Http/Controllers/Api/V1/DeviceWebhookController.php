<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\AccessControl\AccessControlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceWebhookController extends Controller
{
    public function __construct(
        protected AccessControlService $accessControlService
    ) {}

    public function handleEvent(Request $request): JsonResponse
    {
        // Identify device via Secret Token header or serial number
        $secret = $request->header('X-Device-Secret') ?? $request->input('device_secret');
        $serial = $request->header('X-Device-Serial') ?? $request->input('serial_number');

        $device = null;

        if ($secret) {
            $device = Device::where('device_secret', $secret)->first();
        } elseif ($serial) {
            $device = Device::where('serial_number', $serial)->first();
        }

        if (! $device) {
            // Fallback: look for device specified in route or payload for demo/local simulator
            $deviceId = $request->input('device_id');
            if ($deviceId) {
                $device = Device::find($deviceId);
            }
        }

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized device or unknown device identifier.',
            ], 401);
        }

        $result = $this->accessControlService->processDeviceEvent($device, $request->all());

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
}
