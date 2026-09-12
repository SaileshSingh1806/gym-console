<?php

namespace App\Models;

use App\Traits\BelongsToBranch;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CrmFeedback extends Model
{
    use BelongsToBranch, BelongsToTenant, HasFactory;

    protected $table = 'crm_feedback';

    protected $fillable = [
        'tenant_id',
        'branch_id',
        'member_name',
        'member_phone',
        'rating',
        'category',
        'comments',
        'status',
    ];
}
