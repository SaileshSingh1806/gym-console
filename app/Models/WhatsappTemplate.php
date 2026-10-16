<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsappTemplate extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'waba_template_id',
        'name',
        'language',
        'category',
        'status',
        'header_type',
        'header_content',
        'body_text',
        'footer_text',
        'buttons',
        'variables',
        'meta_raw_data',
        'rejection_reason',
    ];

    protected $casts = [
        'buttons' => 'array',
        'variables' => 'array',
        'meta_raw_data' => 'array',
    ];

    public function campaigns(): HasMany
    {
        return $this->hasMany(WhatsappCampaign::class, 'template_id');
    }

    public function automations(): HasMany
    {
        return $this->hasMany(WhatsappAutomation::class, 'template_id');
    }

    public function isApproved(): bool
    {
        return strtoupper($this->status) === 'APPROVED';
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'APPROVED');
    }
}

