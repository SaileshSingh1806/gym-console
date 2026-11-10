<?php

namespace App\Models;

use Carbon\Carbon;
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
        'protein_grams',
        'carbs_grams',
        'fat_grams',
        'sort_order',
    ];

    protected $casts = [
        'calories' => 'integer',
        'protein_grams' => 'integer',
        'carbs_grams' => 'integer',
        'fat_grams' => 'integer',
        'sort_order' => 'integer',
    ];

    public function getItemsAttribute(): ?string
    {
        return $this->items_description;
    }

    public function getMealTimeAttribute(): ?string
    {
        return $this->recommended_time ? substr((string) $this->recommended_time, 0, 5) : null;
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(DietPlan::class, 'diet_plan_id');
    }

    public function setRecommendedTimeAttribute($value): void
    {
        if (empty($value)) {
            $this->attributes['recommended_time'] = null;

            return;
        }

        try {
            $this->attributes['recommended_time'] = Carbon::parse(trim($value))->format('H:i:s');
        } catch (\Throwable $e) {
            $cleaned = preg_replace('/[^0-9:]/', '', (string) $value);
            $this->attributes['recommended_time'] = ! empty($cleaned) ? substr($cleaned, 0, 8) : null;
        }
    }
}
