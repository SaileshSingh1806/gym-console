<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'billing_cycle',
        'status',
        'trial_ends_at',
        'starts_at',
        'ends_at',
        'grace_period_ends_at',
        'cancelled_at',
        'gateway_name',
        'gateway_subscription_id',
        'gateway_customer_id',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'grace_period_ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
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

    public function isActive(): bool
    {
        return in_array($this->status, ['ACTIVE', 'TRIAL', 'GRACE_PERIOD']);
    }

    public function isTrial(): bool
    {
        return $this->status === 'TRIAL' && ($this->trial_ends_at === null || $this->trial_ends_at->isFuture());
    }

    public function isGracePeriod(): bool
    {
        return $this->status === 'GRACE_PERIOD' && ($this->grace_period_ends_at === null || $this->grace_period_ends_at->isFuture());
    }
}
