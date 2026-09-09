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

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
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

    public function show(int $id): JsonResponse
    {
        $membership = Membership::with(['member', 'plan', 'payments'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new MembershipResource($membership),
        ]);
    }
}
