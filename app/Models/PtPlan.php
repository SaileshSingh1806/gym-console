<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PtPlan extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'name',
        'sort_order',
        'description',
        'total_sessions',
        'validity_days',
        'default_price',
        'trainer_commission_percent',
        'sac_code',
        'gst_rate',
        'price_includes_gst',
        'notes',
        'is_active',
        'includes_gate_pass',
        'show_on_mobile_app',
    ];

    protected $casts = [
        'default_price' => 'decimal:2',
        'trainer_commission_percent' => 'decimal:2',
        'gst_rate' => 'decimal:2',
        'price_includes_gst' => 'boolean',
        'is_active' => 'boolean',
        'includes_gate_pass' => 'boolean',
        'show_on_mobile_app' => 'boolean',
    ];

    public function memberPtPackages(): HasMany
    {
        return $this->hasMany(MemberPtPackage::class);
    }
}
