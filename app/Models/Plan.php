<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price_monthly',
        'price_yearly',
        'trial_days',
        'member_limit',
        'branch_limit',
        'staff_limit',
        'is_active',
        'is_popular',
        'sort_order',
    ];

    protected $casts = [
        'price_monthly' => 'decimal:2',
        'price_yearly' => 'decimal:2',
        'is_active' => 'boolean',
        'is_popular' => 'boolean',
        'member_limit' => 'integer',
        'branch_limit' => 'integer',
        'staff_limit' => 'integer',
        'trial_days' => 'integer',
        'sort_order' => 'integer',
    ];

    public function features(): BelongsToMany
    {
        return $this->belongsToMany(Feature::class, 'plan_features')
            ->withPivot('value')
            ->withTimestamps();
    }

    public function planFeatures(): HasMany
    {
        return $this->hasMany(PlanFeature::class);
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    protected ?array $cachedFeatures = null;

    public function hasFeature(string $featureCode): bool
    {
        if ($this->cachedFeatures === null) {
            $this->loadFeaturesCache();
        }

        $val = $this->cachedFeatures[$featureCode] ?? null;

        return $val === '1' || $val === 'true' || $val === true || $val === 1;
    }

    public function getFeatureValue(string $featureCode, $default = null)
    {
        if ($this->cachedFeatures === null) {
            $this->loadFeaturesCache();
        }

        return $this->cachedFeatures[$featureCode] ?? $default;
    }

    protected function loadFeaturesCache(): void
    {
        $this->cachedFeatures = $this->features()
            ->get(['features.code', 'plan_features.value'])
            ->pluck('pivot.value', 'code')
            ->toArray();
    }
}
