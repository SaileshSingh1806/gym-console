<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CrmFeedback extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $table = 'crm_feedback';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_id',
        'user_id',
        'member_name',
        'member_phone',
        'rating',
        'category',
        'comments',
        'is_anonymous',
        'admin_notes',
        'resolved_at',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_anonymous' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
