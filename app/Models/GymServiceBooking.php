<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GymServiceBooking extends Model
{
    use BelongsToTenant, HasFactory;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'member_id',
        'gym_service_id',
        'booking_date',
        'booking_time',
        'amount_paid',
        'total_sessions',
        'sessions_left',
        'locker_number',
        'status',
        'notes',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'amount_paid' => 'float',
        'total_sessions' => 'integer',
        'sessions_left' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(GymService::class, 'gym_service_id');
    }
}
