<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Models\WhatsappCampaign;
use App\Models\WhatsappContact;
use App\Models\WhatsappConversation;
use App\Models\WhatsappMessage;
use App\Models\WhatsappTemplate;
use App\Services\WhatsApp\WhatsAppCampaignService;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use App\Services\WhatsApp\WhatsAppContactService;
use App\Services\WhatsApp\WhatsAppWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppCloudApiTest extends TestCase
{
    protected Tenant $tenant;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::first() ?? Tenant::create([
            'name' => 'ARMOUR 24-7 GYM',
            'slug' => 'armour-gym',
            'status' => 'ACTIVE',
        ]);

        $this->user = User::first() ?? User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'gym_owner',
        ]);
    }

    public function test_phone_number_normalization_handles_various_indian_formats(): void
    {
        // 10 digits clean
        $this->assertEquals('+919876543210', WhatsAppContactService::normalizeIndianPhoneNumber('9876543210'));

        // With leading 0
        $this->assertEquals('+919876543210', WhatsAppContactService::normalizeIndianPhoneNumber('09876543210'));

        // With country code 91
        $this->assertEquals('+919876543210', WhatsAppContactService::normalizeIndianPhoneNumber('919876543210'));

        // With +91 and spaces/dashes
        $this->assertEquals('+919876543210', WhatsAppContactService::normalizeIndianPhoneNumber('+91 98765-43210'));

        // Invalid numbers (starting with 1-5 or wrong length)
        $this->assertNull(WhatsAppContactService::normalizeIndianPhoneNumber('1234567890'));
        $this->assertNull(WhatsAppContactService::normalizeIndianPhoneNumber('98765'));
        $this->assertNull(WhatsAppContactService::normalizeIndianPhoneNumber(''));
    }

    public function test_meta_api_connection_success_with_mock(): void
    {
        Config::set('services.whatsapp.phone_number_id', '123456789');
        Config::set('services.whatsapp.access_token', 'mock_token');

        Http::fake([
            'https://graph.facebook.com/v22.0/123456789*' => Http::response([
                'id' => '123456789',
                'verified_name' => 'ARMOUR 24-7 GYM',
                'display_phone_number' => '+91 98765 43210',
                'quality_rating' => 'GREEN',
                'code_verification_status' => 'VERIFIED',
            ], 200),
        ]);

        $api = new WhatsAppCloudApiService($this->tenant);
        $result = $api->testConnection();

        $this->assertTrue($result['success']);
        $this->assertEquals('ARMOUR 24-7 GYM', $result['data']['verified_name']);
    }

    public function test_webhook_verification_challenge_succeeds_with_valid_token(): void
    {
        Config::set('services.whatsapp.webhook_verify_token', 'test_verify_token_123');

        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=test_verify_token_123&hub_challenge=1158201444');

        $response->assertStatus(200);
        $this->assertEquals('1158201444', $response->getContent());
    }

    public function test_webhook_verification_challenge_fails_with_invalid_token(): void
    {
        Config::set('services.whatsapp.webhook_verify_token', 'test_verify_token_123');

        $response = $this->get('/api/whatsapp/webhook?hub_mode=subscribe&hub_verify_token=wrong_token&hub_challenge=1158201444');

        $response->assertStatus(403);
    }

    public function test_webhook_incoming_message_creates_contact_and_conversation(): void
    {
        $payload = [
            'object' => 'whatsapp_business_account',
            'entry' => [
                [
                    'id' => 'WABA_123',
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messaging_product' => 'whatsapp',
                                'metadata' => [
                                    'display_phone_number' => '919876543210',
                                    'phone_number_id' => 'PHONE_123',
                                ],
                                'contacts' => [
                                    [
                                        'profile' => ['name' => 'Vikram Patel'],
                                        'wa_id' => '919876500001',
                                    ],
                                ],
                                'messages' => [
                                    [
                                        'from' => '919876500001',
                                        'id' => 'wamid.HBgLM...',
                                        'timestamp' => (string) time(),
                                        'text' => ['body' => 'Hi, what are your gym timings?'],
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        WhatsAppWebhookService::processPayload($payload);

        $contact = WhatsappContact::where('phone', '+919876500001')->first();
        $this->assertNotNull($contact);
        $this->assertEquals('Vikram Patel', $contact->name);

        $conversation = WhatsappConversation::where('contact_id', $contact->id)->first();
        $this->assertNotNull($conversation);
        $this->assertEquals(1, $conversation->unread_count);
        $this->assertTrue($conversation->isCustomerServiceWindowOpen());

        $message = WhatsappMessage::where('conversation_id', $conversation->id)->first();
        $this->assertNotNull($message);
        $this->assertEquals('Hi, what are your gym timings?', $message->message_body);
        $this->assertEquals('inbound', $message->direction);
    }

    public function test_webhook_inbound_stop_keyword_automatically_opts_out_contact(): void
    {
        $contact = WhatsappContact::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Aarav Mehta',
            'phone' => '+919876500002',
            'type' => 'external',
            'consent_status' => 'opted_in',
        ]);

        $payload = [
            'entry' => [
                [
                    'changes' => [
                        [
                            'field' => 'messages',
                            'value' => [
                                'messages' => [
                                    [
                                        'from' => '919876500002',
                                        'id' => 'wamid.STOP_123',
                                        'timestamp' => (string) time(),
                                        'text' => ['body' => 'STOP'],
                                        'type' => 'text',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        WhatsAppWebhookService::processPayload($payload);

        $contact->refresh();
        $this->assertEquals('opted_out', $contact->consent_status);
        $this->assertTrue($contact->isOptedOut());
    }

    public function test_marketing_campaign_only_targets_opted_in_contacts(): void
    {
        // 1. Opted In Contact
        $optedIn = WhatsappContact::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Opted In Member',
            'phone' => '+919876500003',
            'type' => 'member',
            'consent_status' => 'opted_in',
            'status' => 'active',
        ]);

        // 2. Unknown Consent Contact
        $unknown = WhatsappContact::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Unknown Member',
            'phone' => '+919876500004',
            'type' => 'member',
            'consent_status' => 'unknown',
            'status' => 'active',
        ]);

        // 3. Opted Out Contact
        $optedOut = WhatsappContact::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Opted Out Lead',
            'phone' => '+919876500005',
            'type' => 'lead',
            'consent_status' => 'opted_out',
            'status' => 'active',
        ]);

        $template = WhatsappTemplate::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'marketing_promo_test',
            'language' => 'en',
            'category' => 'MARKETING',
            'status' => 'APPROVED',
            'body_text' => 'Special gym discount offer!',
        ]);

        $query = WhatsAppCampaignService::buildAudienceQuery([], $this->tenant);
        $recipients = $query->pluck('id')->toArray();

        // Must include ONLY opted_in contact
        $this->assertContains($optedIn->id, $recipients);
        $this->assertNotContains($unknown->id, $recipients);
        $this->assertNotContains($optedOut->id, $recipients);
    }
}

