<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappMessage extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'conversation_id',
        'contact_id',
        'campaign_id',
        'direction',
        'type',
        'message_body',
        'media_url',
        'media_mime_type',
        'media_caption',
        'wamid',
        'status',
        'error_code',
        'error_message',
        'meta_raw_payload',
        'sent_at',
        'delivered_at',
        'read_at',
        'failed_at',
    ];

    protected $casts = [
        'meta_raw_payload' => 'array',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(WhatsappConversation::class, 'conversation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'contact_id');
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(WhatsappCampaign::class, 'campaign_id');
    }

    public function isInbound(): bool
    {
        return $this->direction === 'inbound';
    }

    public function isOutbound(): bool
    {
        return $this->direction === 'outbound';
    }
}

