<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentMaintenanceLog extends Model
{
    use BelongsToTenant, HasFactory;

    protected $table = 'equipment_maintenance_logs';

    protected $fillable = [
        'tenant_id',
        'gym_equipment_id',
        'maintenance_type',
        'service_date',
        'technician_name',
        'technician_contact',
        'cost',
        'status_after_service',
        'work_summary',
        'replaced_parts',
        'next_service_date',
        'receipt_path',
    ];

    protected $casts = [
        'service_date' => 'date',
        'next_service_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(GymEquipment::class, 'gym_equipment_id');
    }
}
