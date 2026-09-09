<?php

namespace App\Services\Payment;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;

interface SaaSPaymentGatewayInterface
{
    public function getName(): string;

    /**
     * Initialize a checkout session or charge intent
     */
    public function createCheckoutSession(Tenant $tenant, Plan $plan, string $billingCycle, array $options = []): array;

    /**
     * Verify payment signature/webhook payload
     */
    public function verifyWebhook(array $payload, array $headers): array;

    /**
     * Cancel an active gateway subscription
     */
    public function cancelSubscription(Subscription $subscription): bool;
}
