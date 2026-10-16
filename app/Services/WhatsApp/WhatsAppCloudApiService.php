<?php

namespace App\Services\WhatsApp;

use App\Models\Setting;
use App\Models\Tenant;
use App\Services\TenantContext;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppCloudApiService
{
    protected ?string $wabaId;
    protected ?string $phoneNumberId;
    protected ?string $accessToken;
    protected string $graphVersion;
    protected ?string $appSecret;
    protected ?Tenant $tenant;

    public function __construct(?Tenant $tenant = null)
    {
        $this->tenant = $tenant ?? TenantContext::getTenant() ?? (auth()->check() ? auth()->user()->tenant : null);
        $this->loadCredentials();
    }

    /**
     * Load credentials with priority: Tenant Settings > config/services.php / .env
     */
    protected function loadCredentials(): void
    {
        $tenantId = $this->tenant?->id ?? false;

        $this->wabaId = Setting::get('whatsapp_business_account_id', config('services.whatsapp.business_account_id'), $tenantId);
        $this->phoneNumberId = Setting::get('whatsapp_phone_number_id', config('services.whatsapp.phone_number_id'), $tenantId);
        $this->accessToken = Setting::get('whatsapp_access_token', config('services.whatsapp.access_token'), $tenantId);
        $this->graphVersion = Setting::get('whatsapp_graph_version', config('services.whatsapp.graph_version', 'v22.0'), $tenantId) ?: 'v22.0';
        $this->appSecret = Setting::get('whatsapp_app_secret', config('services.whatsapp.app_secret'), $tenantId);
    }

    public function getBaseUrl(): string
    {
        return "https://graph.facebook.com/{$this->graphVersion}";
    }

    public function isConfigured(): bool
    {
        return ! empty($this->phoneNumberId) && ! empty($this->accessToken);
    }

    public function getPhoneNumberId(): ?string
    {
        return $this->phoneNumberId;
    }

    public function getWabaId(): ?string
    {
        return $this->wabaId;
    }

    /**
     * Test connection to Meta Graph API for this phone number.
     *
     * @return array{success: bool, message: string, data?: array}
     */
    public function testConnection(): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Cloud API Phone Number ID or Access Token is missing. Please configure credentials first.',
            ];
        }

        try {
            $url = "{$this->getBaseUrl()}/{$this->phoneNumberId}?fields=id,verified_name,code_verification_status,display_phone_number,quality_rating";

            $response = Http::withToken($this->accessToken)
                ->timeout(12)
                ->get($url);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'message' => 'Connection verified successfully with Meta WhatsApp Cloud API.',
                    'data' => $data,
                ];
            }

            $error = $response->json('error.message') ?? "HTTP {$response->status()} error";

            return [
                'success' => false,
                'message' => "Meta API Connection Error: {$error}",
                'error_code' => $response->json('error.code'),
            ];
        } catch (Exception $e) {
            Log::error('WhatsApp Cloud API connection test failed: '.$this->sanitizeMessage($e->getMessage()));

            return [
                'success' => false,
                'message' => 'Failed to reach Meta Graph API: '.$this->sanitizeMessage($e->getMessage()),
            ];
        }
    }

    /**
     * Fetch all approved and pending message templates from WABA.
     */
    public function fetchTemplates(): array
    {
        if (empty($this->wabaId) || empty($this->accessToken)) {
            return [
                'success' => false,
                'message' => 'WhatsApp Business Account ID (WABA ID) and Access Token are required to fetch templates.',
                'templates' => [],
            ];
        }

        try {
            $url = "{$this->getBaseUrl()}/{$this->wabaId}/message_templates?limit=100";

            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->get($url);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'templates' => $response->json('data', []),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'Failed to fetch templates from Meta.',
                'templates' => [],
            ];
        } catch (Exception $e) {
            Log::error('Fetch WhatsApp templates failed: '.$this->sanitizeMessage($e->getMessage()));

            return [
                'success' => false,
                'message' => $this->sanitizeMessage($e->getMessage()),
                'templates' => [],
            ];
        }
    }

    /**
     * Submit a new template to Meta for approval.
     */
    public function createTemplate(array $payload): array
    {
        if (empty($this->wabaId) || empty($this->accessToken)) {
            return [
                'success' => false,
                'message' => 'WABA ID and Access Token must be configured to submit templates.',
            ];
        }

        try {
            $url = "{$this->getBaseUrl()}/{$this->wabaId}/message_templates";

            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->post($url, $payload);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'message' => $response->json('error.message') ?? 'Meta rejected template creation request.',
                'error_code' => $response->json('error.code'),
            ];
        } catch (Exception $e) {
            Log::error('Create WhatsApp template failed: '.$this->sanitizeMessage($e->getMessage()));

            return [
                'success' => false,
                'message' => $this->sanitizeMessage($e->getMessage()),
            ];
        }
    }

    /**
     * Send a pre-approved WhatsApp Template Message.
     *
     * @param  string  $to  E.164 phone number without '+' (e.g. 919876543210)
     * @param  string  $templateName  Meta template slug name
     * @param  string  $languageCode  e.g. 'en', 'en_US', 'hi'
     * @param  array  $components  Header, Body, Buttons components
     */
    public function sendTemplateMessage(string $to, string $templateName, string $languageCode = 'en', array $components = []): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Cloud API is not configured.',
            ];
        }

        $cleanRecipient = $this->cleanPhoneNumberForMeta($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanRecipient,
            'type' => 'template',
            'template' => [
                'name' => $templateName,
                'language' => [
                    'code' => $languageCode,
                ],
            ],
        ];

        if (! empty($components)) {
            $payload['template']['components'] = $components;
        }

        return $this->dispatchMessage($payload);
    }

    /**
     * Send a free-form text message (Valid strictly within 24-hr customer service window).
     *
     * @param  string  $to  Recipient phone number
     * @param  string  $text  Text content
     */
    public function sendTextMessage(string $to, string $text): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Cloud API is not configured.',
            ];
        }

        $cleanRecipient = $this->cleanPhoneNumberForMeta($to);

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanRecipient,
            'type' => 'text',
            'text' => [
                'preview_url' => true,
                'body' => $text,
            ],
        ];

        return $this->dispatchMessage($payload);
    }

    /**
     * Send media message (image, document, video) within 24-hr customer service window.
     */
    public function sendMediaMessage(string $to, string $mediaType, string $mediaUrl, ?string $caption = null): array
    {
        if (! $this->isConfigured()) {
            return [
                'success' => false,
                'message' => 'WhatsApp Cloud API is not configured.',
            ];
        }

        $cleanRecipient = $this->cleanPhoneNumberForMeta($to);
        $mediaPayload = ['link' => $mediaUrl];
        if (! empty($caption)) {
            $mediaPayload['caption'] = $caption;
        }

        $payload = [
            'messaging_product' => 'whatsapp',
            'recipient_type' => 'individual',
            'to' => $cleanRecipient,
            'type' => $mediaType,
            $mediaType => $mediaPayload,
        ];

        return $this->dispatchMessage($payload);
    }

    /**
     * Low-level post to Meta messages endpoint with bounded retries and token redaction.
     */
    protected function dispatchMessage(array $payload): array
    {
        try {
            $url = "{$this->getBaseUrl()}/{$this->phoneNumberId}/messages";

            $response = Http::withToken($this->accessToken)
                ->timeout(15)
                ->retry(2, 200, function ($exception) {
                    // Retry only on network timeouts or 5xx server issues
                    return $exception instanceof \Illuminate\Http\Client\ConnectionException;
                })
                ->post($url, $payload);

            if ($response->successful()) {
                $data = $response->json();
                $wamid = $data['messages'][0]['id'] ?? null;

                return [
                    'success' => true,
                    'wamid' => $wamid,
                    'raw' => $data,
                ];
            }

            $error = $response->json('error');
            $errorCode = $error['code'] ?? $response->status();
            $errorMessage = $error['message'] ?? 'Message rejected by WhatsApp Cloud API.';

            Log::warning("WhatsApp send failed ({$errorCode}): {$errorMessage}", [
                'recipient' => substr($payload['to'] ?? '', 0, 4).'****'.substr($payload['to'] ?? '', -2),
                'error_code' => $errorCode,
            ]);

            return [
                'success' => false,
                'error_code' => (string) $errorCode,
                'error_message' => $errorMessage,
                'raw' => $response->json(),
            ];
        } catch (Exception $e) {
            $sanitized = $this->sanitizeMessage($e->getMessage());
            Log::error("WhatsApp API dispatch exception: {$sanitized}");

            return [
                'success' => false,
                'error_code' => 'EXCEPTION',
                'error_message' => $sanitized,
            ];
        }
    }

    /**
     * Clean phone number for Meta payload: digits only without '+' prefix.
     */
    public function cleanPhoneNumberForMeta(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        // If 10 digits given, prepend India country code 91
        if (strlen($digits) === 10) {
            return '91'.$digits;
        }

        return $digits;
    }

    /**
     * Remove bearer tokens and secrets from error strings.
     */
    protected function sanitizeMessage(string $message): string
    {
        if (! empty($this->accessToken)) {
            $message = str_replace($this->accessToken, '[REDACTED_ACCESS_TOKEN]', $message);
        }
        if (! empty($this->appSecret)) {
            $message = str_replace($this->appSecret, '[REDACTED_APP_SECRET]', $message);
        }

        return $message;
    }
}

