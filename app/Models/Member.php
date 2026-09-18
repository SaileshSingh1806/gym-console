<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'trainer_id',
        'user_id',
        'member_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'gender',
        'dob',
        'photo_path',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'join_date',
        'status',
        'notes',
        'qr_code_token',
        'metadata',
    ];

    protected $casts = [
        'dob' => 'date',
        'join_date' => 'date',
        'metadata' => 'array',
    ];

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getNameAttribute(): string
    {
        return $this->full_name ?: ($this->phone ?? 'Member');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTrainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'trainer_id');
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class);
    }

    public function memberPtPackages(): HasMany
    {
        return $this->hasMany(MemberPtPackage::class);
    }

    public function activeMembership(): HasOne
    {
        return $this->hasOne(Membership::class)
            ->where('status', 'ACTIVE')
            ->where('start_date', '<=', now()->toDateString())
            ->where('end_date', '>=', now()->toDateString())
            ->latestOfMany();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MemberPayment::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function attendances(): HasMany
    {
        return $this->attendance();
    }

    public function workoutPlans(): HasMany
    {
        return $this->hasMany(WorkoutPlan::class);
    }

    public function activeWorkoutPlan(): HasOne
    {
        return $this->hasOne(WorkoutPlan::class)->latestOfMany();
    }

    public function dietPlans(): HasMany
    {
        return $this->hasMany(DietPlan::class);
    }

    public function activeDietPlan(): HasOne
    {
        return $this->hasOne(DietPlan::class)->latestOfMany();
    }

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    public function classBookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class);
    }

    public function isMembershipActive(): bool
    {
        return $this->status === 'ACTIVE' && $this->activeMembership()->exists();
    }
}
