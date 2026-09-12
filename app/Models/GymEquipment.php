<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GymEquipment extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $table = 'gym_equipment';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'category',
        'brand',
        'model_number',
        'serial_number',
        'location',
        'purchase_date',
        'purchase_cost',
        'warranty_expiry_date',
        'maintenance_interval_days',
        'last_service_date',
        'next_service_date',
        'status',
        'vendor_name',
        'vendor_contact',
        'notes',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry_date' => 'date',
        'last_service_date' => 'date',
        'next_service_date' => 'date',
        'purchase_cost' => 'decimal:2',
        'maintenance_interval_days' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(EquipmentMaintenanceLog::class, 'gym_equipment_id')->orderBy('service_date', 'desc');
    }

    public function getDaysUntilMaintenanceAttribute(): ?int
    {
        if (! $this->next_service_date) {
            return null;
        }

        $nextDate = Carbon::parse($this->next_service_date)->startOfDay();
        $today = Carbon::today();

        return (int) $today->diffInDays($nextDate, false);
    }

    public function getIsOverdueAttribute(): bool
    {
        if (! $this->next_service_date) {
            return false;
        }

        $nextDate = Carbon::parse($this->next_service_date)->startOfDay();

        return $nextDate->isBefore(Carbon::today());
    }

    public function getIsDueSoonAttribute(): bool
    {
        if (! $this->next_service_date || $this->is_overdue) {
            return false;
        }

        $days = $this->days_until_maintenance;

        return $days !== null && $days >= 0 && $days <= 7;
    }
}
