<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsappContactConsent extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'contact_id',
        'consent_status',
        'source',
        'wording_version',
        'ip_address',
        'user_agent',
        'evidence_file_path',
        'notes',
        'created_by_user_id',
        'recorded_at',
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'contact_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_user_id');
    }
}

