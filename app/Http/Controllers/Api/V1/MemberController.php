<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MemberResource;
use App\Models\Member;
use App\Services\MembershipService;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MemberController extends Controller
{
    public function __construct(
        protected MembershipService $membershipService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $query = Member::with(['branch', 'activeMembership.plan']);

        if ($request->search) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%")
                    ->orWhere('member_code', 'like', "%{$s}%");
            });
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sortBy = $request->get('sort', 'member_id_desc');
        if ($sortBy === 'member_id_asc') {
            $query->orderByRaw("CASE WHEN member_code REGEXP '[0-9]+' THEN CAST(REGEXP_REPLACE(member_code, '[^0-9]', '') AS UNSIGNED) ELSE 999999 END ASC, id ASC");
        } elseif ($sortBy === 'name_asc') {
            $query->orderBy('first_name', 'asc')->orderBy('last_name', 'asc');
        } elseif ($sortBy === 'join_date_desc') {
            $query->orderBy('join_date', 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderByRaw("CASE WHEN member_code REGEXP '[0-9]+' THEN CAST(REGEXP_REPLACE(member_code, '[^0-9]', '') AS UNSIGNED) ELSE 0 END DESC, id DESC");
        }

        $members = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => MemberResource::collection($members),
            'meta' => [
                'current_page' => $members->currentPage(),
                'last_page' => $members->lastPage(),
                'per_page' => $members->perPage(),
                'total' => $members->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $member = Member::with(['branch', 'memberships.plan', 'payments', 'attendance', 'workoutPlans', 'dietPlans'])->findOrFail($id);

        if ($request->user()->role === 'member' && $member->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view another member\'s profile.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new MemberResource($member),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'phone' => 'required|string|max:30',
            'email' => 'nullable|email|max:150',
            'gender' => 'nullable|in:male,female,other',
            'dob' => 'nullable|date',
            'address' => 'nullable|string|max:500',
            'branch_id' => 'nullable|exists:branches,id',
            'emergency_contact_name' => 'nullable|string|max:100',
            'emergency_contact_phone' => 'nullable|string|max:30',
        ]);

        $tenant = TenantContext::getTenant() ?? $request->user()->tenant;
        $member = $this->membershipService->createMember($tenant, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Member enrolled successfully',
            'data' => new MemberResource($member),
        ], 201);
    }
}
