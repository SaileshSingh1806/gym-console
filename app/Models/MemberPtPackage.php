<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MemberPtPackage extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_id',
        'trainer_id',
        'pt_plan_id',
        'package_name',
        'total_sessions',
        'used_sessions',
        'start_date',
        'end_date',
        'price',
        'discount',
        'final_amount',
        'paid_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'price' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function ptPlan(): BelongsTo
    {
        return $this->belongsTo(PtPlan::class);
    }

    public function ptSessions(): HasMany
    {
        return $this->hasMany(PtSession::class);
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->end_date < Carbon::today()) {
            return 0;
        }

        return (int) Carbon::today()->diffInDays($this->end_date, false);
    }

    public function getRemainingSessionsAttribute(): int
    {
        return max(0, $this->total_sessions - $this->used_sessions);
    }
}
