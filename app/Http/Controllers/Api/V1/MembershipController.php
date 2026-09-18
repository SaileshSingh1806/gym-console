<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\MembershipResource;
use App\Models\Membership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MembershipController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Membership::with(['member', 'plan']);

        if ($request->user()->role === 'member') {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        } elseif ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $memberships = $query->latest()->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => MembershipResource::collection($memberships),
            'meta' => [
                'current_page' => $memberships->currentPage(),
                'last_page' => $memberships->lastPage(),
                'per_page' => $memberships->perPage(),
                'total' => $memberships->total(),
            ],
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $membership = Membership::with(['member', 'plan', 'payments'])->findOrFail($id);

        if ($request->user()->role === 'member' && $membership->member?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view another member\'s membership.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new MembershipResource($membership),
        ]);
    }
}
