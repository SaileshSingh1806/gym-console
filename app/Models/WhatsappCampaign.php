<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappCampaign extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'created_by_user_id',
        'template_id',
        'name',
        'target_audience',
        'filters',
        'status',
        'total_recipients',
        'sent_count',
        'delivered_count',
        'read_count',
        'failed_count',
        'estimated_cost',
        'actual_cost',
        'scheduled_at',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }

    public function recipients(): HasMany
    {
        return $this->hasMany(WhatsappCampaignRecipient::class, 'campaign_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'campaign_id');
    }

    public function canBePaused(): bool
    {
        return in_array($this->status, ['running', 'scheduled']);
    }

    public function canBeResumed(): bool
    {
        return $this->status === 'paused';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['draft', 'scheduled', 'paused', 'running']);
    }
}

