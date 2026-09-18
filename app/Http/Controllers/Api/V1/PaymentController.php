<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentResource;
use App\Models\MemberPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = MemberPayment::with(['member', 'receivedBy']);

        if ($request->user()->role === 'member') {
            $query->whereHas('member', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });
        } elseif ($request->member_id) {
            $query->where('member_id', $request->member_id);
        }

        $payments = $query->latest('payment_date')->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => PaymentResource::collection($payments),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }
}
