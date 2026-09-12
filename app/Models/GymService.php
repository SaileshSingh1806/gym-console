<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymService extends Model
{
    use BelongsToTenant, HasFactory;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'amount',
        'duration_minutes',
        'timeslot_availability',
        'description',
        'status',
        'is_visible_in_portal',
        'is_locker_service',
        'is_session_countable',
        'session_count',
    ];

    protected $casts = [
        'amount' => 'float',
        'duration_minutes' => 'integer',
        'is_visible_in_portal' => 'boolean',
        'is_locker_service' => 'boolean',
        'is_session_countable' => 'boolean',
        'session_count' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(GymServiceBooking::class);
    }
}
