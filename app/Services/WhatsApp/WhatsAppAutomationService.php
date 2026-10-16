<?php

namespace App\Services\WhatsApp;

use App\Models\Lead;
use App\Models\Member;
use App\Models\MemberPayment;
use App\Models\Membership;
use App\Models\Tenant;
use App\Models\WhatsappAutomation;
use App\Models\WhatsappAutomationLog;
use App\Models\WhatsappContact;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class WhatsAppAutomationService
{
    /**
     * Run all active automations for all tenants (called via scheduler/cron).
     */
    public static function runAll(): array
    {
        $tenants = Tenant::where('status', 'ACTIVE')->get();
        $summary = [
            'tenants_processed' => $tenants->count(),
            'messages_dispatched' => 0,
            'skipped_count' => 0,
            'errors' => [],
        ];

        foreach ($tenants as $tenant) {
            $automations = WhatsappAutomation::with('template')
                ->where('tenant_id', $tenant->id)
                ->where('is_active', true)
                ->get();

            $api = new WhatsAppCloudApiService($tenant);

            foreach ($automations as $automation) {
                try {
                    $result = self::processAutomation($automation, $api);
                    $summary['messages_dispatched'] += $result['sent'];
                    $summary['skipped_count'] += $result['skipped'];
                } catch (Exception $e) {
                    $summary['errors'][] = "Automation #{$automation->id} ({$automation->name}): {$e->getMessage()}";
                    Log::error("Automation error #{$automation->id}: {$e->getMessage()}");
                }
            }
        }

        return $summary;
    }

    /**
     * Process an individual automation trigger.
     */
    public static function processAutomation(WhatsappAutomation $automation, ?WhatsAppCloudApiService $api = null): array
    {
        if (! $automation->is_active || ! $automation->template || ! $automation->template->isApproved()) {
            return ['sent' => 0, 'skipped' => 0];
        }

        $api = $api ?? new WhatsAppCloudApiService($automation->tenant);
        if (! $api->isConfigured()) {
            return ['sent' => 0, 'skipped' => 0];
        }

        $sent = 0;
        $skipped = 0;

        switch ($automation->trigger_event) {
            case 'member_created':
                $items = self::getTargetsForNewMembers($automation);
                break;
            case 'membership_expiring':
                $items = self::getTargetsForExpiringMemberships($automation);
                break;
            case 'membership_expired':
                $items = self::getTargetsForExpiredMemberships($automation);
                break;
            case 'payment_received':
                $items = self::getTargetsForPayments($automation);
                break;
            case 'member_birthday':
                $items = self::getTargetsForBirthdays($automation);
                break;
            case 'lead_created':
                $items = self::getTargetsForNewLeads($automation);
                break;
            default:
                $items = [];
        }

        foreach ($items as $item) {
            $contact = $item['contact'];
            $entityType = $item['entity_type'];
            $entityId = $item['entity_id'];
            $variables = $item['variables'] ?? [];

            // 1. Consent Policy Check
            if ($contact->consent_status === 'opted_out') {
                self::logSkip($automation, $contact, $entityType, $entityId, 'Contact opted out of WhatsApp messages');
                $skipped++;
                continue;
            }

            if ($automation->message_category === 'MARKETING' && $contact->consent_status !== 'opted_in') {
                self::logSkip($automation, $contact, $entityType, $entityId, 'Marketing message requires documented opt-in consent');
                $skipped++;
                continue;
            }

            // 2. Deduplication check: Has this automation fired for this entity today or previously?
            $alreadySent = WhatsappAutomationLog::where('automation_id', $automation->id)
                ->where('contact_id', $contact->id)
                ->where('entity_type', $entityType)
                ->where('entity_id', $entityId)
                ->where('status', 'sent')
                ->exists();

            if ($alreadySent) {
                $skipped++;
                continue;
            }

            // 3. Dispatch template message via Meta Graph API
            $components = WhatsAppCampaignService::buildTemplateComponents($automation->template, $variables);

            $result = $api->sendTemplateMessage(
                to: $contact->phone,
                templateName: $automation->template->name,
                languageCode: $automation->template->language ?: 'en',
                components: $components
            );

            if ($result['success'] ?? false) {
                $wamid = $result['wamid'] ?? null;

                WhatsappAutomationLog::create([
                    'tenant_id' => $automation->tenant_id,
                    'automation_id' => $automation->id,
                    'contact_id' => $contact->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'wamid' => $wamid,
                    'status' => 'sent',
                    'executed_at' => now(),
                ]);

                // Record in conversation
                self::recordMessage($automation, $contact, $wamid);

                $sent++;
            } else {
                WhatsappAutomationLog::create([
                    'tenant_id' => $automation->tenant_id,
                    'automation_id' => $automation->id,
                    'contact_id' => $contact->id,
                    'entity_type' => $entityType,
                    'entity_id' => $entityId,
                    'status' => 'failed',
                    'error_message' => $result['error_message'] ?? 'API dispatch failed',
                    'executed_at' => now(),
                ]);
            }
        }

        $automation->update(['last_run_at' => now()]);

        return ['sent' => $sent, 'skipped' => $skipped];
    }

    /**
     * Target 1: New Member Welcome Messages (created in last 24h).
     */
    protected static function getTargetsForNewMembers(WhatsappAutomation $automation): array
    {
        $members = Member::where('tenant_id', $automation->tenant_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $targets = [];
        foreach ($members as $member) {
            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($member->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $member->full_name, 'type' => 'member', 'member_id' => $member->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'Member',
                'entity_id' => $member->id,
                'variables' => [
                    $member->full_name,
                    $automation->tenant->name ?? 'ARMOUR 24-7 GYM',
                ],
            ];
        }

        return $targets;
    }

    /**
     * Target 2: Expiring Memberships (e.g. 3 days before end_date).
     */
    protected static function getTargetsForExpiringMemberships(WhatsappAutomation $automation): array
    {
        $offset = $automation->days_offset; // e.g. -3 for 3 days before
        $targetDate = now()->addDays(abs($offset))->toDateString();

        $memberships = Membership::with('member', 'plan')
            ->where('tenant_id', $automation->tenant_id)
            ->where('status', 'ACTIVE')
            ->whereDate('end_date', $targetDate)
            ->get();

        $targets = [];
        foreach ($memberships as $ms) {
            if (! $ms->member || empty($ms->member->phone)) {
                continue;
            }

            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($ms->member->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $ms->member->full_name, 'type' => 'member', 'member_id' => $ms->member->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'Membership',
                'entity_id' => $ms->id,
                'variables' => [
                    $ms->member->full_name,
                    $ms->plan?->name ?? 'Gym Membership',
                    Carbon::parse($ms->end_date)->format('d M Y'),
                ],
            ];
        }

        return $targets;
    }

    /**
     * Target 3: Expired Memberships (Renewal Follow-up).
     */
    protected static function getTargetsForExpiredMemberships(WhatsappAutomation $automation): array
    {
        $offset = $automation->days_offset; // e.g. +1 for 1 day after expiry
        $targetDate = now()->subDays(max(1, abs($offset)))->toDateString();

        $memberships = Membership::with('member', 'plan')
            ->where('tenant_id', $automation->tenant_id)
            ->whereDate('end_date', $targetDate)
            ->get();

        $targets = [];
        foreach ($memberships as $ms) {
            if (! $ms->member || empty($ms->member->phone)) {
                continue;
            }

            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($ms->member->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $ms->member->full_name, 'type' => 'member', 'member_id' => $ms->member->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'Membership',
                'entity_id' => $ms->id,
                'variables' => [
                    $ms->member->full_name,
                    $ms->plan?->name ?? 'Gym Membership',
                ],
            ];
        }

        return $targets;
    }

    /**
     * Target 4: Payment Confirmations.
     */
    protected static function getTargetsForPayments(WhatsappAutomation $automation): array
    {
        $payments = MemberPayment::with('member')
            ->where('tenant_id', $automation->tenant_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $targets = [];
        foreach ($payments as $payment) {
            if (! $payment->member || empty($payment->member->phone)) {
                continue;
            }

            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($payment->member->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $payment->member->full_name, 'type' => 'member', 'member_id' => $payment->member->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'MemberPayment',
                'entity_id' => $payment->id,
                'variables' => [
                    $payment->member->full_name,
                    '₹'.number_format($payment->amount, 2),
                    $payment->invoice_number ?: (string) $payment->id,
                ],
            ];
        }

        return $targets;
    }

    /**
     * Target 5: Member Birthday Wishes (today).
     */
    protected static function getTargetsForBirthdays(WhatsappAutomation $automation): array
    {
        $todayMonth = now()->format('m');
        $todayDay = now()->format('d');

        $members = Member::where('tenant_id', $automation->tenant_id)
            ->whereNotNull('dob')
            ->whereMonth('dob', $todayMonth)
            ->whereDay('dob', $todayDay)
            ->get();

        $targets = [];
        foreach ($members as $member) {
            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($member->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $member->full_name, 'type' => 'member', 'member_id' => $member->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'Member',
                'entity_id' => $member->id,
                'variables' => [
                    $member->full_name,
                    $automation->tenant->name ?? 'ARMOUR 24-7 GYM',
                ],
            ];
        }

        return $targets;
    }

    /**
     * Target 6: New Lead Follow-ups.
     */
    protected static function getTargetsForNewLeads(WhatsappAutomation $automation): array
    {
        $leads = Lead::where('tenant_id', $automation->tenant_id)
            ->where('created_at', '>=', now()->subHours(24))
            ->get();

        $targets = [];
        foreach ($leads as $lead) {
            $phone = WhatsAppContactService::normalizeIndianPhoneNumber($lead->phone);
            if (! $phone) {
                continue;
            }

            $contact = WhatsappContact::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'phone' => $phone],
                ['name' => $lead->name, 'type' => 'lead', 'lead_id' => $lead->id, 'consent_status' => 'opted_in']
            );

            $targets[] = [
                'contact' => $contact,
                'entity_type' => 'Lead',
                'entity_id' => $lead->id,
                'variables' => [
                    $lead->name ?: 'Fitness Enthusiast',
                    $automation->tenant->name ?? 'ARMOUR 24-7 GYM',
                ],
            ];
        }

        return $targets;
    }

    protected static function logSkip(WhatsappAutomation $automation, WhatsappContact $contact, string $entityType, $entityId, string $reason): void
    {
        WhatsappAutomationLog::create([
            'tenant_id' => $automation->tenant_id,
            'automation_id' => $automation->id,
            'contact_id' => $contact->id,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'status' => 'skipped',
            'skip_reason' => $reason,
            'executed_at' => now(),
        ]);
    }

    protected static function recordMessage(WhatsappAutomation $automation, WhatsappContact $contact, ?string $wamid): void
    {
        try {
            $conversation = WhatsappConversation::firstOrCreate(
                ['tenant_id' => $automation->tenant_id, 'contact_id' => $contact->id],
                ['phone' => $contact->phone, 'status' => 'open']
            );

            WhatsappMessage::create([
                'tenant_id' => $automation->tenant_id,
                'conversation_id' => $conversation->id,
                'contact_id' => $contact->id,
                'direction' => 'outbound',
                'type' => 'template',
                'message_body' => "Automation: {$automation->name}",
                'wamid' => $wamid,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => "Automation: {$automation->name}",
            ]);
        } catch (Exception $e) {
            Log::warning("Could not record automated message in conversation: {$e->getMessage()}");
        }
    }
}

