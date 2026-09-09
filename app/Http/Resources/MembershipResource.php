<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
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
            'plan' => [
                'id' => $this->plan?->id,
                'name' => $this->plan?->name,
            ],
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'price' => (float) $this->price,
            'discount' => (float) $this->discount,
            'tax' => (float) $this->tax,
            'final_amount' => (float) $this->final_amount,
            'paid_amount' => (float) $this->paid_amount,
            'remaining_balance' => (float) $this->remaining_balance,
            'status' => $this->status,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
