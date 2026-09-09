<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DietPlan extends Model
{
    use BelongsToTenant, HasFactory;

    protected $fillable = [
        'tenant_id',
        'member_id',
        'trainer_id',
        'title',
        'daily_calories',
        'protein_grams',
        'carbs_grams',
        'fat_grams',
        'start_date',
        'end_date',
        'is_template',
        'guidelines',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_template' => 'boolean',
        'daily_calories' => 'integer',
        'protein_grams' => 'integer',
        'carbs_grams' => 'integer',
        'fat_grams' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(Trainer::class);
    }

    public function meals(): HasMany
    {
        return $this->hasMany(DietMeal::class)->orderBy('sort_order');
    }
}
