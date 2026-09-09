<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Member;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MembershipService
{
    public function __construct(
        protected FeatureGateService $featureGateService
    ) {}

    public function createMember(Tenant $tenant, array $data): Member
    {
        $this->featureGateService->ensureWithinQuota($tenant, 'members');

        return DB::transaction(function () use ($tenant, $data) {
            $memberCode = $data['member_code'] ?? ('MEM-'.strtoupper(Str::random(6)));

            $member = Member::create([
                'tenant_id' => $tenant->id,
                'branch_id' => $data['branch_id'] ?? TenantContext::branchId(),
                'member_code' => $memberCode,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'],
                'gender' => $data['gender'] ?? null,
                'dob' => $data['dob'] ?? null,
                'photo_path' => $data['photo_path'] ?? null,
                'address' => $data['address'] ?? null,
                'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
                'join_date' => $data['join_date'] ?? now()->toDateString(),
                'status' => 'ACTIVE',
                'notes' => $data['notes'] ?? null,
                'metadata' => $data['metadata'] ?? null,
                'qr_code_token' => Str::random(32),
            ]);

            ActivityLog::log('member_created', "Registered new member {$member->full_name} ({$member->member_code})", $member);

            return $member;
        });
    }

    public function assignMembership(Member $member, MembershipPlan $plan, array $options = []): Membership
    {
        return DB::transaction(function () use ($member, $plan, $options) {
            $startDate = isset($options['start_date']) ? Carbon::parse($options['start_date']) : Carbon::today();

            $endDate = match ($plan->duration_type) {
                'days' => $startDate->copy()->addDays($plan->duration_value),
                'months' => $startDate->copy()->addMonths($plan->duration_value),
                'years' => $startDate->copy()->addYears($plan->duration_value),
                default => $startDate->copy()->addMonth(),
            };

            $price = (float) $plan->price;
            $discount = (float) ($options['discount'] ?? 0.00);
            $taxRate = (float) $plan->tax_rate;
            $taxableAmount = max(0, $price - $discount);
            $tax = ($taxableAmount * $taxRate) / 100;
            $finalAmount = $taxableAmount + $tax;

            $membership = Membership::create([
                'tenant_id' => $member->tenant_id,
                'branch_id' => $member->branch_id,
                'member_id' => $member->id,
                'membership_plan_id' => $plan->id,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'price' => $price,
                'discount' => $discount,
                'tax' => $tax,
                'final_amount' => $finalAmount,
                'paid_amount' => 0.00,
                'status' => 'ACTIVE',
                'notes' => $options['notes'] ?? null,
            ]);

            $member->update(['status' => 'ACTIVE']);

            ActivityLog::log('membership_assigned', "Assigned plan '{$plan->name}' to member {$member->full_name}", $membership);

            return $membership;
        });
    }

    public function renewMembership(Membership $oldMembership, ?MembershipPlan $newPlan = null, array $options = []): Membership
    {
        $member = $oldMembership->member;
        $plan = $newPlan ?? $oldMembership->plan;

        $startDate = $oldMembership->end_date->isFuture() ? $oldMembership->end_date->addDay() : Carbon::today();
        $options['start_date'] = $startDate->toDateString();

        return $this->assignMembership($member, $plan, $options);
    }
}
