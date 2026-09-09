<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Device extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'model',
        'type',
        'serial_number',
        'ip_address',
        'port',
        'username',
        'password',
        'device_secret',
        'direction',
        'status',
        'last_seen_at',
        'configuration',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
        'configuration' => 'array',
        'password' => 'encrypted',
        'port' => 'integer',
    ];

    public function accessLogs(): HasMany
    {
        return $this->hasMany(AccessLog::class);
    }

    public function attendance(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }
}
