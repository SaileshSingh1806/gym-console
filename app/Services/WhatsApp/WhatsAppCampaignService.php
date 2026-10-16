<?php

namespace App\Services\WhatsApp;

use App\Jobs\SendBulkWhatsAppCampaignJob;
use App\Models\Tenant;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignRecipient;
use App\Models\WhatsappContact;
use App\Models\WhatsappTemplate;
use App\Services\TenantContext;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppCampaignService
{
    /**
     * Build an Eloquent query for audience matching the campaign filters.
     */
    public static function buildAudienceQuery(array $filters = [], ?Tenant $tenant = null): Builder
    {
        $tenantId = $tenant?->id ?? TenantContext::tenantId();

        $query = WhatsappContact::where('tenant_id', $tenantId)
            ->where('status', 'active');

        // STRICT MARKETING COMPLIANCE: Only contacts with documented opt-in consent can receive campaigns
        $query->where('consent_status', 'opted_in');

        // Target audience type
        if (! empty($filters['type']) && in_array($filters['type'], ['member', 'lead', 'external'])) {
            $query->where('type', $filters['type']);
        }

        // City filter
        if (! empty($filters['city'])) {
            $query->where('city', 'like', "%{$filters['city']}%");
        }

        // Tags filter
        if (! empty($filters['tags']) && is_array($filters['tags'])) {
            $query->where(function ($q) use ($filters) {
                foreach ($filters['tags'] as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        // Source filter
        if (! empty($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        return $query;
    }

    /**
     * Calculate recipient count and estimated Meta conversation charges.
     */
    public static function estimateAudienceAndCost(WhatsappTemplate $template, array $filters = [], ?Tenant $tenant = null): array
    {
        $query = self::buildAudienceQuery($filters, $tenant);
        $count = $query->count();

        // Meta Pricing for India (Configurable)
        $ratePerMsg = $template->category === 'MARKETING'
            ? (float) config('services.whatsapp.marketing_rate', 0.85)
            : (float) config('services.whatsapp.utility_rate', 0.35);

        $currency = config('services.whatsapp.currency', 'INR');
        $estimatedCost = round($count * $ratePerMsg, 2);

        return [
            'count' => $count,
            'rate_per_message' => $ratePerMsg,
            'currency' => $currency,
            'estimated_cost' => $estimatedCost,
        ];
    }

    /**
     * Send a single live test message to an authorized staff/tester mobile number.
     */
    public static function sendTestMessage(WhatsappTemplate $template, string $testPhone, array $sampleVariables = []): array
    {
        $api = new WhatsAppCloudApiService;
        if (! $api->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Cloud API credentials are not configured.',
            ];
        }

        $components = self::buildTemplateComponents($template, $sampleVariables);

        return $api->sendTemplateMessage(
            to: $testPhone,
            templateName: $template->name,
            languageCode: $template->language ?: 'en',
            components: $components
        );
    }

    /**
     * Create and populate a new campaign.
     */
    public static function createCampaign(
        string $name,
        WhatsappTemplate $template,
        array $filters = [],
        ?string $scheduledAt = null,
        ?Tenant $tenant = null,
        ?int $userId = null
    ): WhatsappCampaign {
        if (! $template->isApproved()) {
            throw new Exception("Campaign cannot be launched with template '{$template->name}' because its Meta approval status is '{$template->status}'.");
        }

        $tenantId = $tenant?->id ?? TenantContext::tenantId();
        $estimate = self::estimateAudienceAndCost($template, $filters, $tenant);

        if ($estimate['count'] === 0) {
            throw new Exception('No eligible opted-in contacts found matching the selected audience criteria.');
        }

        $campaign = DB::transaction(function () use ($name, $template, $filters, $scheduledAt, $tenantId, $userId, $estimate) {
            $campaign = WhatsappCampaign::create([
                'tenant_id' => $tenantId,
                'created_by_user_id' => $userId,
                'template_id' => $template->id,
                'name' => $name,
                'target_audience' => $filters['type'] ?? 'all_opted_in',
                'filters' => $filters,
                'status' => $scheduledAt ? 'scheduled' : 'draft',
                'total_recipients' => $estimate['count'],
                'estimated_cost' => $estimate['estimated_cost'],
                'scheduled_at' => $scheduledAt ? \Carbon\Carbon::parse($scheduledAt) : null,
            ]);

            // Populate recipients batch by batch to prevent memory limits
            $audienceQuery = self::buildAudienceQuery($filters);

            $audienceQuery->chunk(500, function ($contacts) use ($campaign, $tenantId) {
                $recipientRows = [];
                foreach ($contacts as $contact) {
                    $recipientRows[] = [
                        'tenant_id' => $tenantId,
                        'campaign_id' => $campaign->id,
                        'contact_id' => $contact->id,
                        'phone' => $contact->phone,
                        'parameters' => json_encode([
                            '1' => $contact->name ?: 'Valued Member',
                        ]),
                        'status' => 'pending',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                WhatsappCampaignRecipient::insert($recipientRows);
            });

            return $campaign;
        });

        return $campaign;
    }

    /**
     * Launch or schedule a campaign.
     */
    public static function launchCampaign(WhatsappCampaign $campaign): void
    {
        if ($campaign->status === 'running') {
            throw new Exception('Campaign is already running.');
        }

        $campaign->update([
            'status' => 'running',
            'started_at' => $campaign->started_at ?? now(),
        ]);

        // Dispatch queued background processing
        SendBulkWhatsAppCampaignJob::dispatch($campaign->id);
    }

    /**
     * Pause a currently running campaign.
     */
    public static function pauseCampaign(WhatsappCampaign $campaign): void
    {
        if (! $campaign->canBePaused()) {
            throw new Exception('Campaign cannot be paused in its current state.');
        }

        $campaign->update(['status' => 'paused']);
    }

    /**
     * Resume a paused campaign.
     */
    public static function resumeCampaign(WhatsappCampaign $campaign): void
    {
        if (! $campaign->canBeResumed()) {
            throw new Exception('Only paused campaigns can be resumed.');
        }

        $campaign->update(['status' => 'running']);
        SendBulkWhatsAppCampaignJob::dispatch($campaign->id);
    }

    /**
     * Cancel a pending or running campaign.
     */
    public static function cancelCampaign(WhatsappCampaign $campaign): void
    {
        if (! $campaign->canBeCancelled()) {
            throw new Exception('Completed or already failed campaigns cannot be cancelled.');
        }

        $campaign->update([
            'status' => 'cancelled',
            'completed_at' => now(),
        ]);
    }

    /**
     * Helper to build Meta template components array.
     */
    public static function buildTemplateComponents(WhatsappTemplate $template, array $variables = []): array
    {
        $components = [];

        // Body component
        if (! empty($variables)) {
            $parameters = [];
            foreach ($variables as $val) {
                $parameters[] = [
                    'type' => 'text',
                    'text' => (string) $val,
                ];
            }

            $components[] = [
                'type' => 'body',
                'parameters' => $parameters,
            ];
        }

        return $components;
    }
}

