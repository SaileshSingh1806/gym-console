<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DietPlanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'daily_calories' => $this->daily_calories,
            'protein_grams' => $this->protein_grams,
            'carbs_grams' => $this->carbs_grams,
            'fat_grams' => $this->fat_grams,
            'guidelines' => $this->guidelines,
            'trainer' => $this->trainer ? $this->trainer->full_name : null,
            'meals' => $this->meals->map(fn ($m) => [
                'id' => $m->id,
                'meal_type' => $m->meal_type,
                'recommended_time' => $m->recommended_time,
                'meal_name' => $m->meal_name,
                'items_description' => $m->items_description,
                'calories' => $m->calories,
            ]),
        ];
    }
}
