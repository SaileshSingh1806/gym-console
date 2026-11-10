<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtSession extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_id',
        'trainer_id',
        'member_pt_package_id',
        'session_date',
        'start_time',
        'end_time',
        'duration_minutes',
        'status',
        'confirmation_token',
        'token_expires_at',
        'logged_by_user_id',
        'confirmed_by_member_id',
        'confirmed_at',
        'issue_reported_at',
        'issue_notes',
        'otp_code',
        'focus_area',
        'notes',
        'completed_at',
        'rating',
        'member_feedback',
    ];

    protected $casts = [
        'session_date' => 'date',
        'completed_at' => 'datetime',
        'token_expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'issue_reported_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class)->withTrashed();
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function memberPtPackage(): BelongsTo
    {
        return $this->belongsTo(MemberPtPackage::class);
    }

    public function loggedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'logged_by_user_id');
    }

    public function confirmedByMember(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'confirmed_by_member_id')->withTrashed();
    }

    public function isPending(): bool
    {
        return $this->status === 'PENDING';
    }

    public function isConfirmed(): bool
    {
        return in_array($this->status, ['CONFIRMED', 'COMPLETED']);
    }

    public function isIssueReported(): bool
    {
        return $this->status === 'ISSUE_REPORTED';
    }

    public function isExpired(): bool
    {
        return $this->token_expires_at && $this->token_expires_at->isPast();
    }
}
