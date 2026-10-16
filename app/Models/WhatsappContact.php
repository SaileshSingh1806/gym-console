<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappContact extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_id',
        'lead_id',
        'name',
        'phone',
        'city',
        'type',
        'source',
        'tags',
        'consent_status',
        'consent_updated_at',
        'custom_attributes',
        'status',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
        'custom_attributes' => 'array',
        'consent_updated_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function consents(): HasMany
    {
        return $this->hasMany(WhatsappContactConsent::class, 'contact_id')->latest('recorded_at');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(WhatsappConversation::class, 'contact_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'contact_id');
    }

    public function campaignRecipients(): HasMany
    {
        return $this->hasMany(WhatsappCampaignRecipient::class, 'contact_id');
    }

    public function isOptedIn(): bool
    {
        return $this->consent_status === 'opted_in';
    }

    public function isOptedOut(): bool
    {
        return $this->consent_status === 'opted_out';
    }

    /**
     * Scope for marketing eligible contacts (strictly opted in).
     */
    public function scopeMarketingEligible($query)
    {
        return $query->where('consent_status', 'opted_in')->where('status', 'active');
    }
}

