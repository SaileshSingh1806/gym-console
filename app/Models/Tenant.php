<?php

namespace App\Models;

use App\Services\FeatureGateService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'logo_url',
        'currency',
        'timezone',
        'status',
        'trial_ends_at',
        'settings',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'settings' => 'array',
    ];

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function mainBranch(): HasOne
    {
        return $this->hasOne(Branch::class)->where('is_main', true);
    }

    public function allowedBranches()
    {
        return app(FeatureGateService::class)->getAllowedBranches($this);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function platformInvoices(): HasMany
    {
        return $this->hasMany(PlatformInvoice::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', ['ACTIVE', 'TRIAL', 'GRACE_PERIOD'])
            ->latestOfMany();
    }

    public function latestSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function membershipPlans(): HasMany
    {
        return $this->hasMany(MembershipPlan::class);
    }

    public function devices(): HasMany
    {
        return $this->hasMany(Device::class);
    }

    public function getCurrencySymbolAttribute(): string
    {
        return match (strtoupper($this->currency ?? 'INR')) {
            'INR' => '₹',
            'EUR' => '€',
            'GBP' => '£',
            'AED' => 'د.إ',
            'SAR' => '﷼',
            'CAD' => 'CA$',
            'AUD' => 'AU$',
            'SGD' => 'SG$',
            default => '$',
        };
    }

    public function getGstNumberAttribute(): ?string
    {
        return $this->settings['gst_number'] ?? null;
    }

    public function getGstRateAttribute(): float
    {
        return (float) ($this->settings['gst_rate'] ?? 18.0);
    }

    public function getIsGstRegisteredAttribute(): bool
    {
        return ! empty($this->settings['gst_registered']);
    }

    public function getAddressAttribute(): ?string
    {
        return $this->settings['address'] ?? ($this->mainBranch?->address ?? null);
    }

    public function getMemberIdFormatAttribute(): string
    {
        return $this->settings['member_id_format'] ?? 'coded';
    }

    public function getMemberIdPrefixAttribute(): string
    {
        return $this->settings['member_id_prefix'] ?? 'GYM';
    }

    public function getMemberIdPaddingAttribute(): int
    {
        return (int) ($this->settings['member_id_padding'] ?? 4);
    }

    public function generateNextMemberCode(): string
    {
        $format = $this->member_id_format;
        $count = $this->members()->count() + 1;

        if ($format === 'numeric') {
            return (string) (1000 + $count);
        }

        $prefix = strtoupper(trim($this->member_id_prefix ?: 'GYM'));
        $year = now()->format('y');
        $padding = max(3, min(8, $this->member_id_padding ?: 4));
        $sequence = str_pad((string) $count, $padding, '0', STR_PAD_LEFT);

        return "{$prefix}{$year}{$sequence}";
    }

    public function isSubscriptionActive(): bool
    {
        if (in_array($this->status, ['PENDING_PAYMENT', 'SUSPENDED', 'CANCELLED', 'INACTIVE'])) {
            return false;
        }

        if ($this->status === 'TRIAL') {
            return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
        }

        if ($this->status === 'ACTIVE') {
            $activeSub = $this->activeSubscription;
            if (! $activeSub) {
                return true;
            }

            return $activeSub->ends_at === null || $activeSub->ends_at->isFuture();
        }

        $activeSub = $this->activeSubscription;
        if (! $activeSub) {
            return false;
        }

        if ($activeSub->status === 'TRIAL') {
            return $activeSub->trial_ends_at !== null && $activeSub->trial_ends_at->isFuture();
        }

        if ($activeSub->status === 'ACTIVE') {
            return $activeSub->ends_at === null || $activeSub->ends_at->isFuture();
        }

        if ($activeSub->status === 'GRACE_PERIOD') {
            return $activeSub->grace_period_ends_at !== null && $activeSub->grace_period_ends_at->isFuture();
        }

        return false;
    }

    public function isTrialExpired(): bool
    {
        if ($this->status === 'TRIAL' && $this->trial_ends_at && $this->trial_ends_at->isPast()) {
            return true;
        }

        $sub = $this->activeSubscription ?? $this->latestSubscription;
        if ($sub && $sub->status === 'TRIAL' && $sub->trial_ends_at && $sub->trial_ends_at->isPast()) {
            return true;
        }

        return false;
    }

    public function isSubscriptionExpired(): bool
    {
        return ! $this->isSubscriptionActive();
    }

    public function hasFeature(string $featureCode): bool
    {
        $sub = $this->activeSubscription;
        if (! $sub || ! $sub->plan) {
            return false;
        }

        return $sub->plan->hasFeature($featureCode);
    }
}
