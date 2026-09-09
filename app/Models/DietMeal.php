<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DietMeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'diet_plan_id',
        'meal_type',
        'recommended_time',
        'meal_name',
        'items_description',
        'calories',
        'sort_order',
    ];

    protected $casts = [
        'calories' => 'integer',
        'sort_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class, 'diet_plan_id');
    }
}
