<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'plan_id',
        'min_amount',
        'max_discount_amount',
        'usage_limit',
        'used_count',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_amount' => 'decimal:2',
        'max_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(SubscriptionPayment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(PlatformInvoice::class);
    }

    /**
     * Check if the coupon is valid for a given plan and order amount.
     */
    public function isValid(?Plan $plan = null, float $amount = 0.0): array
    {
        if (! $this->is_active) {
            return ['valid' => false, 'message' => 'This promo coupon is inactive or disabled.'];
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return ['valid' => false, 'message' => 'This promo coupon offer has not started yet.'];
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'This promo coupon has expired.'];
        }

        if ($this->usage_limit !== null && $this->used_count >= $this->usage_limit) {
            return ['valid' => false, 'message' => 'This promo coupon has reached its maximum redemption limit.'];
        }

        if ($this->plan_id !== null && $plan !== null && $this->plan_id !== $plan->id) {
            return ['valid' => false, 'message' => "This coupon is only applicable to the {$this->plan?->name} plan."];
        }

        if ($this->min_amount > 0 && $amount > 0 && $amount < (float) $this->min_amount) {
            return ['valid' => false, 'message' => 'Minimum order amount to apply this coupon is ₹'.number_format($this->min_amount, 2).'.'];
        }

        return ['valid' => true, 'message' => 'Coupon code applied successfully!'];
    }

    /**
     * Calculate discount amount for a given order total.
     */
    public function calculateDiscount(float $amount): float
    {
        if ($amount <= 0) {
            return 0.0;
        }

        if ($this->discount_type === 'percentage') {
            $discount = ($amount * (float) $this->discount_value) / 100.0;
            if ($this->max_discount_amount !== null && (float) $this->max_discount_amount > 0) {
                $discount = min($discount, (float) $this->max_discount_amount);
            }

            return round(min($discount, $amount), 2);
        }

        // Fixed amount discount
        return round(min((float) $this->discount_value, $amount), 2);
    }

    /**
     * Increment used counter.
     */
    public function incrementUsage(): void
    {
        $this->increment('used_count');
    }
}
