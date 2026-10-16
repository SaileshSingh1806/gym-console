<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\WhatsappWebhookEvent;
use App\Services\WhatsApp\WhatsAppWebhookService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    /**
     * Handle Meta Webhook Verification Challenge (GET /api/whatsapp/webhook).
     */
    public function verify(Request $request): Response|JsonResponse
    {
        $verification = WhatsAppWebhookService::verifyChallenge($request);

        if ($verification['success']) {
            return response($verification['challenge'], 200)
                ->header('Content-Type', 'text/plain');
        }

        return response()->json([
            'status' => 'error',
            'message' => $verification['message'],
        ], 403);
    }

    /**
     * Handle Incoming Meta Webhook Events (POST /api/whatsapp/webhook).
     */
    public function handle(Request $request): JsonResponse
    {
        // 1. Signature Verification with App Secret
        if (! WhatsAppWebhookService::verifySignature($request)) {
            Log::warning('WhatsApp Webhook rejected: Invalid X-Hub-Signature-256 header.');

            return response()->json(['error' => 'Invalid webhook signature.'], 401);
        }

        $payload = $request->all();

        // 2. Generate Event ID for Idempotency
        $eventId = md5($request->getContent());

        // Check if event already processed
        $existing = WhatsappWebhookEvent::where('event_id', $eventId)->first();
        if ($existing && $existing->processed) {
            return response()->json(['status' => 'already_processed'], 200);
        }

        $webhookEvent = WhatsappWebhookEvent::create([
            'event_id' => $eventId,
            'event_type' => $payload['entry'][0]['changes'][0]['field'] ?? 'messages',
            'payload' => $payload,
            'processed' => false,
        ]);

        try {
            // Process payload
            WhatsAppWebhookService::processPayload($payload);

            $webhookEvent->update([
                'processed' => true,
                'processed_at' => now(),
            ]);

            return response()->json(['status' => 'success'], 200);
        } catch (Exception $e) {
            Log::error("WhatsApp Webhook processing failed: {$e->getMessage()}");

            $webhookEvent->update([
                'error' => $e->getMessage(),
            ]);

            // Always return HTTP 200 to Meta so it does not disable the webhook subscription
            return response()->json(['status' => 'logged_with_error'], 200);
        }
    }
}

