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

    public function hasFeature(string $featureCode): bool
    {
        $feature = $this->features()->where('code', $featureCode)->first();
        if (! $feature) {
            return false;
        }

        return $feature->pivot->value === '1' || $feature->pivot->value === 'true';
    }

    public function getFeatureValue(string $featureCode, $default = null)
    {
        $feature = $this->features()->where('code', $featureCode)->first();

        return $feature ? $feature->pivot->value : $default;
    }
}
