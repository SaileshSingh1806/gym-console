<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Services\AccessControl\AccessControlService;
use App\Services\TenantContext;
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
            $device = Device::withoutGlobalScopes()->where('device_secret', $secret)->first();
        } elseif ($serial) {
            $device = Device::withoutGlobalScopes()->where('serial_number', $serial)->first();
        }

        if (! $device) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized device or unknown device identifier.',
            ], 401);
        }

        if ($device->tenant) {
            TenantContext::setTenant($device->tenant);
        }
        if ($device->branch) {
            TenantContext::setBranch($device->branch);
        }

        $result = $this->accessControlService->processDeviceEvent($device, $request->all());

        return response()->json([
            'success' => true,
            'result' => $result,
        ]);
    }
}
