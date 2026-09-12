<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'phone',
        'email',
        'instagram_handle',
        'city',
        'member_count',
        'source',
        'status',
        'stage',
        'follow_up_date',
        'assigned_to_user_id',
        'notes',
        'remarks',
        'estimated_value',
        'next_action',
        'trial_date',
        'trial_time',
        'trial_status',
    ];

    protected $casts = [
        'follow_up_date' => 'date',
        'trial_date' => 'date',
        'estimated_value' => 'decimal:2',
        'member_count' => 'integer',
    ];

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }

    public function trials()
    {
        return $this->hasMany(LeadTrial::class);
    }

    public function getEffectiveStageAttribute(): string
    {
        return $this->stage ?: ($this->status ?: 'NEW_LEAD');
    }

    public function getFormattedStageAttribute(): string
    {
        return match (strtoupper(str_replace(' ', '_', $this->effective_stage))) {
            'NEW_LEAD', 'NEW' => 'New Lead',
            'CONTACTED' => 'Contacted',
            'DEMO_BOOKED' => 'Demo Booked',
            'PROPOSAL_SENT' => 'Proposal Sent',
            'NEGOTIATION' => 'Negotiation',
            'TRIAL', 'TRIAL_SCHEDULED' => 'Trial',
            'PAID', 'CONVERTED' => 'Paid',
            'LOST' => 'Lost',
            default => ucwords(str_replace('_', ' ', strtolower($this->effective_stage))),
        };
    }
}
