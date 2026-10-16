<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappAutomationLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'automation_id',
        'contact_id',
        'entity_type',
        'entity_id',
        'wamid',
        'status',
        'skip_reason',
        'error_message',
        'executed_at',
    ];

    protected $casts = [
        'executed_at' => 'datetime',
    ];

    public function automation(): BelongsTo
    {
        return $this->belongsTo(WhatsappAutomation::class, 'automation_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'contact_id');
    }
}

