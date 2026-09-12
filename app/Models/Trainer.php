<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Trainer extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'certification',
        'hourly_rate',
        'salary',
        'salary_type',
        'salary_pay_day',
        'joining_date',
        'status',
        'is_featured',
        'bio',
        'photo_path',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'salary' => 'decimal:2',
        'is_featured' => 'boolean',
        'joining_date' => 'date',
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function classSchedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function dietPlans(): HasMany
    {
        return $this->hasMany(DietPlan::class);
    }

    public function memberPtPackages(): HasMany
    {
        return $this->hasMany(MemberPtPackage::class);
    }

    public function assignedMembers(): HasMany
    {
        return $this->hasMany(Member::class);
    }

    public function ptSessions(): HasMany
    {
        return $this->hasMany(PtSession::class);
    }
}
