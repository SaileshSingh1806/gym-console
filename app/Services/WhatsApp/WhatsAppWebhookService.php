<?php

namespace App\Services\WhatsApp;

use App\Models\Tenant;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappCampaignRecipient;
use App\Models\WhatsappContact;
use App\Models\WhatsappContactConsent;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappWebhookEvent;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookService
{
    /**
     * Verify Webhook GET subscription challenge from Meta.
     */
    public static function verifyChallenge(Request $request): array
    {
        $mode = $request->query('hub_mode') ?? $request->query('hub.mode');
        $token = $request->query('hub_verify_token') ?? $request->query('hub.verify_token');
        $challenge = $request->query('hub_challenge') ?? $request->query('hub.challenge');

        $configuredToken = config('services.whatsapp.webhook_verify_token', 'armour_gym_secure_webhook_token_2026');

        if ($mode === 'subscribe' && $token === $configuredToken) {
            return [
                'success' => true,
                'challenge' => $challenge,
            ];
        }

        return [
            'success' => false,
            'message' => 'Verification token mismatch or invalid mode.',
        ];
    }

    /**
     * Verify the X-Hub-Signature-256 header using the Meta App Secret.
     */
    public static function verifySignature(Request $request, ?string $appSecret = null): bool
    {
        $secret = $appSecret ?: config('services.whatsapp.app_secret');
        if (empty($secret)) {
            // If app secret is not configured yet in development, allow requests with warning
            return true;
        }

        $signatureHeader = $request->header('X-Hub-Signature-256');
        if (empty($signatureHeader) || ! str_starts_with($signatureHeader, 'sha256=')) {
            return false;
        }

        $expectedHash = substr($signatureHeader, 7);
        $computedHash = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expectedHash, $computedHash);
    }

    /**
     * Process incoming Webhook payload from Meta.
     */
    public static function processPayload(array $payload): void
    {
        $entries = $payload['entry'] ?? [];

        foreach ($entries as $entry) {
            $changes = $entry['changes'] ?? [];

            foreach ($changes as $change) {
                $field = $change['field'] ?? '';
                $value = $change['value'] ?? [];

                if ($field !== 'messages') {
                    continue;
                }

                $metadata = $value['metadata'] ?? [];
                $phoneNumberId = $metadata['phone_number_id'] ?? null;

                // 1. Process Delivery Statuses (sent, delivered, read, failed)
                if (! empty($value['statuses'])) {
                    self::handleStatuses($value['statuses']);
                }

                // 2. Process Incoming Messages
                if (! empty($value['messages'])) {
                    self::handleIncomingMessages($value['messages'], $value['contacts'] ?? [], $phoneNumberId);
                }
            }
        }
    }

    /**
     * Handle delivery status updates from Meta (sent, delivered, read, failed).
     */
    protected static function handleStatuses(array $statuses): void
    {
        foreach ($statuses as $statusItem) {
            $wamid = $statusItem['id'] ?? null;
            $status = $statusItem['status'] ?? null; // 'sent', 'delivered', 'read', 'failed'
            $timestamp = isset($statusItem['timestamp']) ? date('Y-m-d H:i:s', (int) $statusItem['timestamp']) : now();

            if (! $wamid || ! $status) {
                continue;
            }

            // Find matching message
            $message = WhatsappMessage::where('wamid', $wamid)->first();
            if ($message) {
                $updateData = ['status' => $status];
                if ($status === 'sent') {
                    $updateData['sent_at'] = $timestamp;
                } elseif ($status === 'delivered') {
                    $updateData['delivered_at'] = $timestamp;
                } elseif ($status === 'read') {
                    $updateData['read_at'] = $timestamp;
                } elseif ($status === 'failed') {
                    $updateData['failed_at'] = $timestamp;
                    $errors = $statusItem['errors'][0] ?? [];
                    $updateData['error_code'] = (string) ($errors['code'] ?? 'UNKNOWN');
                    $updateData['error_message'] = $errors['title'] ?? $errors['message'] ?? 'Delivery failed';
                }

                $message->update($updateData);
            }

            // Update matching campaign recipient
            $recipient = WhatsappCampaignRecipient::where('wamid', $wamid)->first();
            if ($recipient) {
                $recipientUpdates = ['status' => $status];
                if ($status === 'sent') {
                    $recipientUpdates['sent_at'] = $timestamp;
                } elseif ($status === 'delivered') {
                    $recipientUpdates['delivered_at'] = $timestamp;
                    WhatsappCampaign::where('id', $recipient->campaign_id)->increment('delivered_count');
                } elseif ($status === 'read') {
                    $recipientUpdates['read_at'] = $timestamp;
                    WhatsappCampaign::where('id', $recipient->campaign_id)->increment('read_count');
                } elseif ($status === 'failed') {
                    $recipientUpdates['failed_at'] = $timestamp;
                    $errors = $statusItem['errors'][0] ?? [];
                    $recipientUpdates['error_code'] = (string) ($errors['code'] ?? 'UNKNOWN');
                    $recipientUpdates['error_message'] = $errors['title'] ?? $errors['message'] ?? 'Delivery failed';
                    WhatsappCampaign::where('id', $recipient->campaign_id)->increment('failed_count');
                }

                $recipient->update($recipientUpdates);
            }
        }
    }

    /**
     * Handle incoming messages (text, interactive, opt-out keywords).
     */
    protected static function handleIncomingMessages(array $messages, array $contactsMeta = [], ?string $phoneNumberId = null): void
    {
        // Resolve tenant
        $tenant = Tenant::first(); // Default fallback or resolve by phone number ID

        foreach ($messages as $msg) {
            $wamid = $msg['id'] ?? null;
            $fromRaw = $msg['from'] ?? null;
            $timestamp = isset($msg['timestamp']) ? date('Y-m-d H:i:s', (int) $msg['timestamp']) : now();
            $type = $msg['type'] ?? 'text';

            if (! $fromRaw) {
                continue;
            }

            $normalizedPhone = WhatsAppContactService::normalizeIndianPhoneNumber($fromRaw) ?: ('+'.$fromRaw);

            // Extract contact name from Meta contacts meta
            $senderName = 'WhatsApp User';
            if (! empty($contactsMeta)) {
                foreach ($contactsMeta as $cm) {
                    if (($cm['wa_id'] ?? '') === $fromRaw && ! empty($cm['profile']['name'])) {
                        $senderName = $cm['profile']['name'];
                        break;
                    }
                }
            }

            // Find or create contact
            $contact = WhatsappContact::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'phone' => $normalizedPhone,
                ],
                [
                    'name' => $senderName,
                    'source' => 'inbound_message',
                    'consent_status' => 'opted_in', // Customer initiated inbound conversation
                    'consent_updated_at' => now(),
                    'status' => 'active',
                ]
            );

            // Extract message body text
            $body = null;
            if ($type === 'text') {
                $body = $msg['text']['body'] ?? '';
            } elseif ($type === 'button') {
                $body = $msg['button']['text'] ?? '';
            } elseif ($type === 'interactive') {
                $interactiveType = $msg['interactive']['type'] ?? '';
                if ($interactiveType === 'button_reply') {
                    $body = $msg['interactive']['button_reply']['title'] ?? '';
                } elseif ($interactiveType === 'list_reply') {
                    $body = $msg['interactive']['list_reply']['title'] ?? '';
                }
            }

            // CRITICAL CHECK: Inbound STOP / Opt-Out Detection
            if (! empty($body) && preg_match('/\b(stop|unsubscribe|halt|cancel|optout)\b/i', trim($body))) {
                WhatsAppContactService::updateConsent(
                    contact: $contact,
                    newStatus: 'opted_out',
                    source: 'inbound_stop_keyword',
                    wordingVersion: 'Customer sent STOP via WhatsApp chat',
                    notes: "Automated opt-out on message: '{$body}'"
                );
                Log::info("Contact {$contact->phone} opted out via inbound keyword.");
            } elseif (! empty($body) && preg_match('/\b(start|yes|subscribe|unstop)\b/i', trim($body))) {
                WhatsAppContactService::updateConsent(
                    contact: $contact,
                    newStatus: 'opted_in',
                    source: 'inbound_start_keyword',
                    wordingVersion: 'Customer sent START/YES via WhatsApp chat',
                    notes: "Automated opt-in on message: '{$body}'"
                );
            }

            // Update or create Conversation
            $conversation = WhatsappConversation::firstOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'contact_id' => $contact->id,
                ],
                [
                    'phone' => $normalizedPhone,
                    'status' => 'open',
                ]
            );

            // Inbound message resets 24-hr customer service window and increments unread count
            $conversation->update([
                'last_message_at' => $timestamp,
                'last_message_preview' => substr($body ?: "[{$type} message]", 0, 150),
                'last_incoming_at' => now(),
                'unread_count' => DB::raw('unread_count + 1'),
            ]);

            // Save inbound message
            WhatsappMessage::create([
                'tenant_id' => $tenant->id,
                'conversation_id' => $conversation->id,
                'contact_id' => $contact->id,
                'direction' => 'inbound',
                'type' => $type,
                'message_body' => $body,
                'wamid' => $wamid,
                'status' => 'received',
                'meta_raw_payload' => $msg,
                'delivered_at' => $timestamp,
            ]);
        }
    }
}

