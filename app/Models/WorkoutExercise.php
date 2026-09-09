<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkoutExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'workout_plan_id',
        'day',
        'exercise_name',
        'sets',
        'reps',
        'weight',
        'rest_seconds',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'sets' => 'integer',
        'rest_seconds' => 'integer',
        'sort_order' => 'integer',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(WorkoutPlan::class, 'workout_plan_id');
    }
}
