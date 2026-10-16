<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappCampaignRecipient extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'campaign_id',
        'contact_id',
        'phone',
        'parameters',
        'status',
        'wamid',
        'error_code',
        'error_message',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'parameters' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class, 'campaign_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'contact_id');
    }
}

