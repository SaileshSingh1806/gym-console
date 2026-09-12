<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymClass extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'class_type',
        'instructor_id',
        'thumbnail_path',
        'description',
        'capacity',
        'fee',
        'validity_days',
        'total_sessions',
        'cancellation_hours',
        'sac_code',
        'gst_rate',
        'price_includes_gst',
        'duration_minutes',
        'room_location',
        'is_active',
        'is_featured',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'fee' => 'decimal:2',
        'validity_days' => 'integer',
        'total_sessions' => 'integer',
        'cancellation_hours' => 'integer',
        'gst_rate' => 'decimal:2',
        'price_includes_gst' => 'boolean',
        'duration_minutes' => 'integer',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Trainer::class, 'instructor_id');
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
