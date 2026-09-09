<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\PlatformInvoice;
use App\Models\Subscription;
use App\Models\SubscriptionPayment;
use App\Models\Tenant;
use App\Services\Payment\ManualPaymentAdapter;
use App\Services\Payment\RazorpayPaymentAdapter;
use App\Services\Payment\SaaSPaymentGatewayInterface;
use App\Services\Payment\StripePaymentAdapter;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SubscriptionService
{
    public function getGateway(string $gatewayName = 'manual'): SaaSPaymentGatewayInterface
    {
        return match ($gatewayName) {
            'stripe' => new StripePaymentAdapter,
            'razorpay' => new RazorpayPaymentAdapter,
            default => new ManualPaymentAdapter,
        };
    }

    public function startTrial(Tenant $tenant, Plan $plan): Subscription
    {
        return $this->setTenantTrial($tenant, $plan);
    }

    public function setTenantTrial(Tenant $tenant, Plan $plan, ?Carbon $trialEndsAt = null): Subscription
    {
        return DB::transaction(function () use ($tenant, $plan, $trialEndsAt) {
            $trialDays = $plan->trial_days > 0 ? $plan->trial_days : 14;
            $endsAt = $trialEndsAt ?? now()->addDays($trialDays);

            Subscription::where('tenant_id', $tenant->id)->update(['status' => 'CANCELLED']);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => 'monthly',
                'status' => 'TRIAL',
                'trial_ends_at' => $endsAt,
                'starts_at' => now(),
                'ends_at' => $endsAt,
                'gateway_name' => 'trial',
            ]);

            $tenant->update([
                'status' => 'TRIAL',
                'trial_ends_at' => $endsAt,
            ]);

            ActivityLog::log('trial_updated', "Set {$plan->name} trial until {$endsAt->toDateString()}", $subscription);

            return $subscription;
        });
    }

    public function activateSubscription(
        Tenant $tenant,
        Plan $plan,
        string $billingCycle,
        string $gatewayName,
        string $transactionId,
        float $amount,
        ?array $gatewayResponse = null,
        ?Coupon $coupon = null,
        float $discountAmount = 0.0
    ): Subscription {
        return DB::transaction(function () use ($tenant, $plan, $billingCycle, $gatewayName, $transactionId, $amount, $gatewayResponse, $coupon, $discountAmount) {
            $durationMonths = $billingCycle === 'yearly' ? 12 : 1;
            $startsAt = now();
            $endsAt = now()->addMonths($durationMonths);

            // Deactivate existing subscriptions
            Subscription::where('tenant_id', $tenant->id)->update(['status' => 'CANCELLED']);

            $subscription = Subscription::create([
                'tenant_id' => $tenant->id,
                'plan_id' => $plan->id,
                'billing_cycle' => $billingCycle,
                'status' => 'ACTIVE',
                'trial_ends_at' => null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'gateway_name' => $gatewayName,
                'gateway_subscription_id' => $transactionId,
            ]);

            $payment = SubscriptionPayment::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $tenant->id,
                'coupon_id' => $coupon?->id,
                'transaction_id' => $transactionId,
                'amount' => $amount,
                'discount_amount' => $discountAmount,
                'currency' => $tenant->currency ?? 'INR',
                'gateway' => $gatewayName,
                'payment_method' => $gatewayName,
                'status' => 'SUCCESS',
                'paid_at' => now(),
                'gateway_response' => $gatewayResponse,
            ]);

            if ($coupon) {
                $coupon->incrementUsage();
            }

            // Create SaaS Platform Invoice
            $invoiceNumber = 'INV-SAAS-'.strtoupper(Str::random(6)).'-'.date('Y');
            $subtotal = $amount + $discountAmount;

            PlatformInvoice::create([
                'tenant_id' => $tenant->id,
                'coupon_id' => $coupon?->id,
                'subscription_id' => $subscription->id,
                'subscription_payment_id' => $payment->id,
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'discount' => $discountAmount,
                'tax' => 0.00,
                'total' => $amount,
                'currency' => $tenant->currency ?? 'INR',
                'status' => 'PAID',
                'invoice_date' => now()->toDateString(),
                'paid_at' => now(),
            ]);

            $tenant->update([
                'status' => 'ACTIVE',
                'trial_ends_at' => null,
            ]);

            $currencySymbol = $tenant->currency_symbol ?? '₹';
            ActivityLog::log('subscription_activated', "Activated {$plan->name} ({$billingCycle}) subscription for {$currencySymbol}{$amount}".($coupon ? " (Coupon: {$coupon->code} saved {$currencySymbol}{$discountAmount})" : ''), $subscription);

            return $subscription;
        });
    }

    public function handlePaymentFailed(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $gracePeriodEndsAt = now()->addDays(3);

            $subscription->update([
                'status' => 'GRACE_PERIOD',
                'grace_period_ends_at' => $gracePeriodEndsAt,
            ]);

            $subscription->tenant->update([
                'status' => 'GRACE_PERIOD',
            ]);

            ActivityLog::log('subscription_past_due', 'Subscription entered 3-day grace period due to failed payment', $subscription);
        });
    }

    public function cancelSubscription(Subscription $subscription): void
    {
        DB::transaction(function () use ($subscription) {
            $subscription->update([
                'status' => 'CANCELLED',
                'cancelled_at' => now(),
            ]);

            ActivityLog::log('subscription_cancelled', 'Subscription cancelled by user', $subscription);
        });
    }
}
