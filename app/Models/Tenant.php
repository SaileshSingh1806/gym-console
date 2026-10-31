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

        if ($format === 'numeric') {
            $existingNumeric = $this->members()
                ->pluck('member_code')
                ->map(fn ($c) => preg_replace('/\D/', '', (string) $c))
                ->filter(fn ($c) => is_numeric($c) && (int) $c > 0)
                ->map(fn ($c) => (int) $c);

            $maxNum = $existingNumeric->isNotEmpty() ? $existingNumeric->max() : 1000;
            $next = max($maxNum + 1, 1001);

            while ($this->members()->where('member_code', (string) $next)->exists()) {
                $next++;
            }

            return (string) $next;
        }

        $prefix = strtoupper(trim($this->member_id_prefix ?: 'GYM'));

        // Look at existing codes with this prefix to find the highest number
        $existingCodes = $this->members()
            ->where('member_code', 'LIKE', "{$prefix}%")
            ->pluck('member_code');

        $maxNum = 0;
        $detectedPadding = 0;

        foreach ($existingCodes as $rawCode) {
            $suffix = substr($rawCode, strlen($prefix));
            if (is_numeric($suffix)) {
                $num = (int) $suffix;
                if ($num > $maxNum) {
                    $maxNum = $num;
                }
                if ($detectedPadding === 0) {
                    $detectedPadding = strlen($suffix);
                }
            }
        }

        $nextNum = max($maxNum + 1, $this->members()->count() + 1);

        // Determine padding: prefer detected padding from existing members if available,
        // otherwise configured setting or default 4
        $configuredPadding = (int) ($this->settings['member_id_padding'] ?? 0);
        $padding = $detectedPadding > 0 ? $detectedPadding : ($configuredPadding > 0 ? $configuredPadding : 4);
        $padding = max($padding, strlen((string) $nextNum));

        $sequence = str_pad((string) $nextNum, $padding, '0', STR_PAD_LEFT);
        $code = "{$prefix}{$sequence}";

        // Ensure no collision with both padded and unpadded variations
        while (
            $this->members()->where('member_code', $code)->exists() ||
            $this->members()->where('member_code', $prefix . (int) $nextNum)->exists()
        ) {
            $nextNum++;
            $sequence = str_pad((string) $nextNum, $padding, '0', STR_PAD_LEFT);
            $code = "{$prefix}{$sequence}";
        }

        return $code;
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
