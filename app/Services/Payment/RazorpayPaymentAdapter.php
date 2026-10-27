<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Setting;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Str;

class RazorpayPaymentAdapter implements SaaSPaymentGatewayInterface
{
    protected string $keyId;

    protected string $keySecret;

    protected string $webhookSecret;

    public function __construct()
    {
        $this->keyId = Setting::getGlobal('razorpay_key_id', config('services.razorpay.key', env('RAZORPAY_KEY', 'rzp_test_samplekey123')));
        $this->keySecret = Setting::getGlobal('razorpay_key_secret', config('services.razorpay.secret', env('RAZORPAY_SECRET', 'sample_secret_key')));
        $this->webhookSecret = Setting::getGlobal('razorpay_webhook_secret', env('RAZORPAY_WEBHOOK_SECRET', ''));
    }

    public function getName(): string
    {
        return 'razorpay';
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $billingCycle, array $options = []): array
    {
        $amount = $billingCycle === 'yearly' ? (float) $plan->price_yearly : (float) $plan->price_monthly;
        if (isset($options['discount_amount']) && $options['discount_amount'] > 0) {
            $amount = max(0, $amount - (float) $options['discount_amount']);
        }

        // Razorpay expects amount in paise (multiply by 100)
        $amountInPaise = (int) round($amount * 100);
        $orderId = 'order_'.Str::random(14);
        $receipt = 'RCP-'.strtoupper(Str::random(8));

        return [
            'gateway' => 'razorpay',
            'key_id' => $this->keyId,
            'order_id' => $orderId,
            'receipt' => $receipt,
            'amount' => $amount,
            'amount_paise' => $amountInPaise,
            'currency' => $tenant->currency ?? 'INR',
            'name' => Setting::getGlobal('app_name', config('app.name', 'Gym Console')),
            'description' => "Subscription to {$plan->name} ({$billingCycle})",
            'prefill' => [
                'name' => $tenant->users()->first()?->name ?? $tenant->name,
                'email' => $tenant->email,
                'contact' => $tenant->phone ?? '',
            ],
            'theme' => [
                'color' => '#f59e0b',
            ],
        ];
    }

    public function verifyWebhook(array $payload, array $headers): array
    {
        $signature = $headers['x-razorpay-signature'] ?? null;
        $event = $payload['event'] ?? 'payment.captured';
        $paymentEntity = $payload['payload']['payment']['entity'] ?? [];

        return [
            'valid' => true,
            'event_type' => $event,
            'transaction_id' => $paymentEntity['id'] ?? ('pay_'.Str::random(14)),
            'order_id' => $paymentEntity['order_id'] ?? null,
            'amount' => isset($paymentEntity['amount']) ? ($paymentEntity['amount'] / 100) : 0,
            'currency' => $paymentEntity['currency'] ?? 'INR',
            'status' => in_array($event, ['payment.captured', 'order.paid']) ? 'SUCCESS' : 'FAILED',
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        return true;
    }

    /**
     * Verify a Razorpay Payment ID directly with Razorpay API.
     *
     * @param string $paymentId
     * @return array
     */
    public function verifyPayment(string $paymentId): array
    {
        $cleanId = trim($paymentId);
        if (!str_starts_with($cleanId, 'pay_')) {
            $cleanId = 'pay_'.$cleanId;
        }

        $keyId = $this->keyId;
        $keySecret = $this->keySecret;

        if (empty($keyId) || empty($keySecret) || str_contains($keyId, 'samplekey')) {
            return [
                'valid' => false,
                'message' => 'Razorpay API credentials are not configured in settings.',
            ];
        }

        try {
            // First attempt with primary configured credentials
            $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                ->timeout(10)
                ->withBasicAuth($keyId, $keySecret)
                ->get("https://api.razorpay.com/v1/payments/{$cleanId}");

            // If not successful and env keys differ, try env keys
            if (! $response->successful()) {
                $envKey = env('RAZORPAY_KEY');
                $envSecret = env('RAZORPAY_SECRET');
                if ($envKey && $envKey !== $keyId && $envSecret) {
                    $response = \Illuminate\Support\Facades\Http::withoutVerifying()
                        ->timeout(10)
                        ->withBasicAuth($envKey, $envSecret)
                        ->get("https://api.razorpay.com/v1/payments/{$cleanId}");
                }
            }

            if (! $response->successful()) {
                $err = $response->json();
                $description = $err['error']['description'] ?? 'Invalid Payment ID or payment not found on Razorpay.';

                return [
                    'valid' => false,
                    'message' => $description,
                ];
            }

            $data = $response->json();
            $status = $data['status'] ?? '';

            if ($status !== 'captured') {
                return [
                    'valid' => false,
                    'message' => "Payment status is '{$status}', not captured.",
                ];
            }

            return [
                'valid' => true,
                'payment_id' => $cleanId,
                'amount' => isset($data['amount']) ? ((float) $data['amount'] / 100) : 0,
                'currency' => $data['currency'] ?? 'INR',
                'raw' => $data,
            ];
        } catch (\Throwable $e) {
            return [
                'valid' => false,
                'message' => 'Unable to verify payment with Razorpay: '.$e->getMessage(),
            ];
        }
    }
}
