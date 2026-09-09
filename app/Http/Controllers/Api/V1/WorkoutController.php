<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\WorkoutPlanResource;
use App\Models\WorkoutPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WorkoutController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = WorkoutPlan::with(['trainer', 'exercises']);

        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        $plans = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => WorkoutPlanResource::collection($plans),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $plan = WorkoutPlan::with(['trainer', 'exercises'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new WorkoutPlanResource($plan),
        ]);
    }
}
