<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappAutomation extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'trigger_event',
        'template_id',
        'is_active',
        'days_offset',
        'send_time',
        'message_category',
        'parameter_mappings',
        'config',
        'last_run_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'days_offset' => 'integer',
        'parameter_mappings' => 'array',
        'config' => 'array',
        'last_run_at' => 'datetime',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(WhatsappTemplate::class, 'template_id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(WhatsappAutomationLog::class, 'automation_id');
    }
}

