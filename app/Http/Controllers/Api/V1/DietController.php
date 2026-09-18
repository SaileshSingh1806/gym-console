<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\DietPlanResource;
use App\Models\DietPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DietController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = DietPlan::with(['trainer', 'meals']);

        if ($request->user()->role === 'member') {
            $query->where(function ($q) use ($request) {
                $q->where('is_template', true)
                    ->orWhereHas('member', fn ($m) => $m->where('user_id', $request->user()->id));
            });
        } elseif ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        $plans = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => DietPlanResource::collection($plans),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $plan = DietPlan::with(['trainer', 'meals'])->findOrFail($id);

        if ($request->user()->role === 'member' && ! $plan->is_template && $plan->member?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view another member\'s diet plan.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new DietPlanResource($plan),
        ]);
    }
}
