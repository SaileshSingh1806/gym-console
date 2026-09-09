<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Support\Str;

class ManualPaymentAdapter implements SaaSPaymentGatewayInterface
{
    public function getName(): string
    {
        return 'manual';
    }

    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $billingCycle, array $options = []): array
    {
        $amount = $billingCycle === 'yearly' ? $plan->price_yearly : $plan->price_monthly;
        $transactionId = 'MAN-'.strtoupper(Str::random(12));

        return [
            'gateway' => 'manual',
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'currency' => $tenant->currency ?? 'USD',
            'checkout_url' => null,
            'instructions' => 'Please transfer to Bank Account: XXXX-XXXX-XXXX and submit receipt.',
        ];
    }

    public function verifyWebhook(array $payload, array $headers): array
    {
        return [
            'valid' => true,
            'transaction_id' => $payload['transaction_id'] ?? null,
            'amount' => $payload['amount'] ?? 0,
            'status' => 'SUCCESS',
        ];
    }

    public function cancelSubscription(Subscription $subscription): bool
    {
        return true;
    }
}
