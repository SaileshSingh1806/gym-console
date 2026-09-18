<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Models\Member;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Attendance::with(['member', 'device']);

        if ($request->user()->role === 'member') {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        } elseif ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->date) {
            $query->where('date', $request->date);
        }

        $attendance = $query->latest('check_in')->paginate($request->per_page ?? 20);

        return response()->json([
            'success' => true,
            'data' => AttendanceResource::collection($attendance),
            'meta' => [
                'current_page' => $attendance->currentPage(),
                'last_page' => $attendance->lastPage(),
                'per_page' => $attendance->perPage(),
                'total' => $attendance->total(),
            ],
        ]);
    }

    public function qrScan(Request $request): JsonResponse
    {
        $request->validate([
            'qr_token' => 'required|string',
            'action' => 'nullable|in:check_in,check_out',
        ]);

        $member = Member::where('qr_code_token', $request->qr_token)->first();

        if (! $member) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid QR Code or member not found.',
            ], 404);
        }

        if (! $member->isMembershipActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Access Denied: Member does not have an active membership.',
            ], 403);
        }

        if ($request->action === 'check_out') {
            $record = $this->attendanceService->checkOut($member);

            return response()->json([
                'success' => true,
                'message' => "Goodbye {$member->first_name}, check-out recorded!",
                'data' => $record ? new AttendanceResource($record) : null,
            ]);
        }

        $record = $this->attendanceService->checkIn($member, 'qr');

        return response()->json([
            'success' => true,
            'message' => "Welcome {$member->first_name}, check-in recorded!",
            'data' => new AttendanceResource($record),
        ]);
    }

    public function todaySummary(): JsonResponse
    {
        $summary = $this->attendanceService->getTodaySummary();

        return response()->json([
            'success' => true,
            'data' => $summary,
        ]);
    }
}
