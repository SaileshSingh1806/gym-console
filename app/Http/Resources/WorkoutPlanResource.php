<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WorkoutPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'goal' => $this->goal,
            'level' => $this->level,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'trainer' => $this->trainer ? $this->trainer->full_name : null,
            'exercises' => $this->exercises->map(fn ($ex) => [
                'id' => $ex->id,
                'day' => $ex->day,
                'exercise_name' => $ex->exercise_name,
                'sets' => $ex->sets,
                'tracking_type' => $ex->tracking_type ?? 'reps',
                'duration_seconds' => $ex->duration_seconds,
                'video_url' => $ex->video_url,
                'reps' => $ex->reps,
                'weight' => $ex->weight,
                'rest_seconds' => $ex->rest_seconds,
                'notes' => $ex->notes,
            ]),
        ];
    }
}
