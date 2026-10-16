<?php

namespace App\Jobs;

use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignRecipient;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Services\WhatsApp\WhatsAppCampaignService;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkWhatsAppCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600;
    public int $tries = 3;

    public function __construct(public int $campaignId)
    {
    }

    public function handle(): void
    {
        $campaign = WhatsappCampaign::with('template')->find($this->campaignId);

        if (! $campaign) {
            Log::warning("WhatsApp Campaign ID {$this->campaignId} not found.");
            return;
        }

        // If campaign was paused or cancelled while queued, abort gracefully
        if ($campaign->status !== 'running') {
            Log::info("Campaign #{$campaign->id} is in status '{$campaign->status}', halting job execution.");
            return;
        }

        $template = $campaign->template;
        if (! $template || ! $template->isApproved()) {
            $campaign->update(['status' => 'failed']);
            Log::error("Campaign #{$campaign->id} failed: Template is not approved by Meta.");
            return;
        }

        $api = new WhatsAppCloudApiService($campaign->tenant);
        if (! $api->isConfigured()) {
            $campaign->update(['status' => 'failed']);
            Log::error("Campaign #{$campaign->id} failed: WhatsApp Cloud API is not configured for tenant #{$campaign->tenant_id}.");
            return;
        }

        $ratePerMsg = $template->category === 'MARKETING'
            ? (float) config('services.whatsapp.marketing_rate', 0.85)
            : (float) config('services.whatsapp.utility_rate', 0.35);

        // Fetch recipients in manageable batches of 25
        $batchSize = 25;

        while (true) {
            // Re-check campaign status before each batch in case admin paused/cancelled it mid-flight
            $freshStatus = WhatsappCampaign::where('id', $campaign->id)->value('status');
            if ($freshStatus !== 'running') {
                Log::info("Campaign #{$campaign->id} was {$freshStatus} mid-flight. Stopping batch runner.");
                return;
            }

            $recipients = WhatsappCampaignRecipient::with('contact')
                ->where('campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->limit($batchSize)
                ->get();

            if ($recipients->isEmpty()) {
                break;
            }

            foreach ($recipients as $recipient) {
                // Check contact opt-out status (in case customer replied STOP just now)
                if ($recipient->contact?->consent_status === 'opted_out') {
                    $recipient->update([
                        'status' => 'failed',
                        'error_code' => 'OPTED_OUT',
                        'error_message' => 'Recipient previously opted out of WhatsApp messages.',
                        'failed_at' => now(),
                    ]);
                    $campaign->increment('failed_count');
                    continue;
                }

                // Prepare variables from parameters
                $params = $recipient->parameters ?? [];
                $variables = [];
                if (! empty($params)) {
                    foreach ($params as $k => $v) {
                        $variables[] = $v;
                    }
                } else {
                    $variables[] = $recipient->contact?->name ?: 'Member';
                }

                $components = WhatsAppCampaignService::buildTemplateComponents($template, $variables);

                // Mark queued before dispatch
                $recipient->update(['status' => 'queued']);

                // Dispatch via Cloud API
                $result = $api->sendTemplateMessage(
                    to: $recipient->phone,
                    templateName: $template->name,
                    languageCode: $template->language ?: 'en',
                    components: $components
                );

                if ($result['success'] ?? false) {
                    $wamid = $result['wamid'] ?? null;

                    $recipient->update([
                        'status' => 'sent',
                        'wamid' => $wamid,
                        'sent_at' => now(),
                    ]);

                    $campaign->increment('sent_count');
                    $campaign->increment('actual_cost', $ratePerMsg);

                    // Track in conversations and messages for Inbox visibility
                    $this->recordOutboundMessage($campaign, $recipient, $template, $wamid);
                } else {
                    $errorCode = $result['error_code'] ?? 'UNKNOWN_ERROR';
                    $errorMessage = $result['error_message'] ?? 'Sending failed';

                    $recipient->update([
                        'status' => 'failed',
                        'error_code' => $errorCode,
                        'error_message' => $errorMessage,
                        'failed_at' => now(),
                    ]);

                    $campaign->increment('failed_count');
                }

                // Rate limit: 60ms delay prevents bursting Meta WABA rate limits (~16 msgs/sec)
                usleep(60000);
            }
        }

        // Finalize campaign when all recipients processed
        $remaining = WhatsappCampaignRecipient::where('campaign_id', $campaign->id)
            ->whereIn('status', ['pending', 'queued'])
            ->count();

        if ($remaining === 0) {
            $campaign->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);
            Log::info("Campaign #{$campaign->id} completed successfully.");
        }
    }

    /**
     * Record the sent campaign message into WhatsApp inbox conversation.
     */
    protected function recordOutboundMessage(WhatsappCampaign $campaign, WhatsappCampaignRecipient $recipient, $template, ?string $wamid): void
    {
        try {
            $conversation = WhatsappConversation::firstOrCreate(
                [
                    'tenant_id' => $campaign->tenant_id,
                    'contact_id' => $recipient->contact_id,
                ],
                [
                    'phone' => $recipient->phone,
                    'status' => 'open',
                ]
            );

            $bodyPreview = "Campaign: {$template->name}";

            WhatsappMessage::create([
                'tenant_id' => $campaign->tenant_id,
                'conversation_id' => $conversation->id,
                'contact_id' => $recipient->contact_id,
                'campaign_id' => $campaign->id,
                'direction' => 'outbound',
                'type' => 'template',
                'message_body' => $template->body_text,
                'wamid' => $wamid,
                'status' => 'sent',
                'sent_at' => now(),
            ]);

            $conversation->update([
                'last_message_at' => now(),
                'last_message_preview' => $bodyPreview,
            ]);
        } catch (Exception $e) {
            Log::warning("Failed to record conversation message for recipient #{$recipient->id}: {$e->getMessage()}");
        }
    }
}

