<?php

namespace App\Services;

use App\Models\Tenant;

class FeatureGateService
{
    public function hasFeature(Tenant $tenant, string $featureCode): bool
    {
        $subscription = $tenant->activeSubscription;

        if (! $subscription || ! $subscription->plan) {
            return false;
        }

        return $subscription->plan->hasFeature($featureCode);
    }

    public function checkQuota(Tenant $tenant, string $resourceType): array
    {
        $subscription = $tenant->activeSubscription;
        $plan = $subscription?->plan;

        if (! $plan) {
            return ['allowed' => false, 'current' => 0, 'limit' => 0, 'remaining' => 0];
        }

        $limit = match ($resourceType) {
            'members' => $plan->member_limit,
            'branches' => $plan->branch_limit,
            'staff' => $plan->staff_limit,
            default => -1,
        };

        if ($limit === -1) {
            return ['allowed' => true, 'current' => 0, 'limit' => -1, 'remaining' => 999999];
        }

        $current = match ($resourceType) {
            'members' => $tenant->members()->count(),
            'branches' => $tenant->branches()->count(),
            'staff' => $tenant->users()->where('role', '!=', 'super_admin')->count(),
            default => 0,
        };

        $allowed = $current < $limit;
        $remaining = max(0, $limit - $current);

        return [
            'allowed' => $allowed,
            'current' => $current,
            'limit' => $limit,
            'remaining' => $remaining,
        ];
    }

    public function ensureWithinQuota(Tenant $tenant, string $resourceType): void
    {
        $quota = $this->checkQuota($tenant, $resourceType);

        if (! $quota['allowed']) {
            throw new \Exception("You have reached the maximum limit of {$quota['limit']} {$resourceType} for your current plan. Please upgrade your subscription to add more.");
        }
    }
}
