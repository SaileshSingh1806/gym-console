<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Exception;

class SubscriptionPaymentController extends Controller
{
    /**
     * AJAX Endpoint: Verify Razorpay Payment and Activate 1-Year SaaS Subscription.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $request->validate([
            'razorpay_payment_id' => 'required|string',
            'plan_id' => 'nullable|integer',
        ]);

        $paymentId = $request->input('razorpay_payment_id');
        $user = auth()->user();
        $tenant = $user?->tenant;

        if (!$tenant) {
            return response()->json([
                'success' => false,
                'message' => 'No active gym tenant profile found for this account.',
            ], 404);
        }

        $keyId = Setting::getGlobal('razorpay_key_id', config('services.razorpay.key', env('RAZORPAY_KEY')));
        $keySecret = Setting::getGlobal('razorpay_key_secret', config('services.razorpay.secret', env('RAZORPAY_SECRET')));

        try {
            $adapter = new \App\Services\Payment\RazorpayPaymentAdapter();
            $verification = $adapter->verifyPayment($paymentId);

            if (! $verification['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment verification failed: '.$verification['message'],
                ], 422);
            }

            $paymentId = $verification['payment_id'];

            // Prevent duplicate redemption across different tenants
            $alreadyUsed = \App\Models\SubscriptionPayment::where('transaction_id', $paymentId)
                ->where('status', 'SUCCESS')
                ->where('tenant_id', '!=', $tenant->id)
                ->exists();

            if ($alreadyUsed) {
                return response()->json([
                    'success' => false,
                    'message' => 'This Razorpay Payment ID has already been redeemed by another gym account.',
                ], 422);
            }

            // 2. Database Logic: Update Authenticated User's Gym Subscription
            $subscription = $tenant->activeSubscription ?? $tenant->subscriptions()->latest()->first();

            // Calculate new expiry date using Carbon (+1 year)
            $baseDate = ($subscription && $subscription->ends_at && Carbon::parse($subscription->ends_at)->isFuture())
                ? Carbon::parse($subscription->ends_at)
                : Carbon::now();

            $newExpiryDate = $baseDate->copy()->addYear();

            if ($subscription) {
                $subscription->status = 'ACTIVE';
                $subscription->ends_at = $newExpiryDate;
                $subscription->gateway_name = 'razorpay';
                $subscription->gateway_subscription_id = $paymentId;
                $subscription->billing_cycle = 'yearly';
                $subscription->save();
            } else {
                $planId = $request->input('plan_id', \App\Models\Plan::where('slug', 'pro')->value('id') ?? \App\Models\Plan::first()?->id);
                $subscription = Subscription::create([
                    'tenant_id' => $tenant->id,
                    'plan_id' => $planId,
                    'billing_cycle' => 'yearly',
                    'status' => 'ACTIVE',
                    'starts_at' => now(),
                    'ends_at' => $newExpiryDate,
                    'gateway_name' => 'razorpay',
                    'gateway_subscription_id' => $paymentId,
                ]);
            }

            // Also update tenant status and timestamp
            $tenant->status = 'active';
            $tenant->save();

            return response()->json([
                'success' => true,
                'message' => 'Payment verified successfully! Your SaaS yearly subscription is active until ' . $newExpiryDate->format('d M Y') . '.',
                'payment_id' => $paymentId,
                'expires_at' => $newExpiryDate->toFormattedDateString(),
            ]);

        } catch (Exception $e) {
            report($e);
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during payment verification: ' . $e->getMessage(),
            ], 500);
        }
    }
}

