<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member' => [
                'id' => $this->member->id,
                'name' => $this->member->full_name,
                'member_code' => $this->member->member_code,
            ],
            'device' => $this->device ? [
                'id' => $this->device->id,
                'name' => $this->device->name,
            ] : null,
            'date' => $this->date?->toDateString(),
            'check_in' => $this->check_in?->toIso8601String(),
            'check_out' => $this->check_out?->toIso8601String(),
            'method' => $this->method,
            'status' => $this->status,
        ];
    }
}
