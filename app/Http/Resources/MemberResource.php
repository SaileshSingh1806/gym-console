<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'member_code' => $this->member_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'dob' => $this->dob?->toDateString(),
            'photo_path' => $this->photo_path,
            'address' => $this->address,
            'emergency_contact' => [
                'name' => $this->emergency_contact_name,
                'phone' => $this->emergency_contact_phone,
            ],
            'join_date' => $this->join_date?->toDateString(),
            'status' => $this->status,
            'branch' => $this->branch ? [
                'id' => $this->branch->id,
                'name' => $this->branch->name,
                'code' => $this->branch->code,
            ] : null,
            'active_membership' => $this->activeMembership ? [
                'id' => $this->activeMembership->id,
                'plan_name' => $this->activeMembership->plan?->name,
                'start_date' => $this->activeMembership->start_date?->toDateString(),
                'end_date' => $this->activeMembership->end_date?->toDateString(),
                'status' => $this->activeMembership->status,
                'remaining_days' => max(0, (int) now()->diffInDays($this->activeMembership->end_date, false)),
            ] : null,
            'qr_code_token' => $this->qr_code_token,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
