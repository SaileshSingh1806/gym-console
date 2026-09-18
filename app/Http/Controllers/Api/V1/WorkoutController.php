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
            'data' => WorkoutPlanResource::collection($plans),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $plan = WorkoutPlan::with(['trainer', 'exercises'])->findOrFail($id);

        if ($request->user()->role === 'member' && ! $plan->is_template && $plan->member?->user_id !== $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to view another member\'s workout plan.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => new WorkoutPlanResource($plan),
        ]);
    }
}
