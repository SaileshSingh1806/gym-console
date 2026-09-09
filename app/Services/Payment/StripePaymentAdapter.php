<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Str;

class StripePaymentAdapter implements SaaSPaymentGatewayInterface
{
    protected string $secretKey;

    protected string $webhookSecret;

    public function __construct()
    {
        $this->secretKey = config('services.stripe.secret', env('STRIPE_SECRET', ''));
        $this->webhookSecret = config('services.stripe.webhook_secret', env('STRIPE_WEBHOOK_SECRET', ''));
    }

    public function getName(): string
    {
        return 'stripe';
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $billingCycle, array $options = []): array
    {
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $sessionId = 'cs_test_'.Str::random(24);

        return [
            'gateway' => 'stripe',
            'session_id' => $sessionId,
            'transaction_id' => 'tx_'.Str::random(16),
            'amount' => $amount,
            'currency' => $tenant->currency ?? 'USD',
            'checkout_url' => "https://checkout.stripe.com/pay/{$sessionId}",
        ];
    }

    public function verifyWebhook(array $payload, array $headers): array
    {
        // Simulated signature verification for webhook events
        $signature = $headers['stripe-signature'] ?? null;
        $eventType = $payload['type'] ?? 'payment_intent.succeeded';

        return [
            'valid' => true,
            'event_type' => $eventType,
            'transaction_id' => $payload['data']['object']['id'] ?? ('tx_'.Str::random(12)),
            'amount' => isset($payload['data']['object']['amount']) ? ($payload['data']['object']['amount'] / 100) : 0,
            'status' => $eventType === 'payment_intent.succeeded' ? 'SUCCESS' : 'FAILED',
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        return true;
    }
}
