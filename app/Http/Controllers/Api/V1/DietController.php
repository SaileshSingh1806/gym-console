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

        if ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        $plans = $query->latest()->get();

        return response()->json([
            'success' => true,
            'data' => DietPlanResource::collection($plans),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $plan = DietPlan::with(['trainer', 'meals'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => new DietPlanResource($plan),
        ]);
    }
}
