<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Member;
use App\Models\MemberAgreement;
use App\Models\MembershipPlan;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantAgreementTemplate;
use App\Models\User;
use App\Services\MemberAgreementService;
use App\Services\TenantService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;

class MemberAgreementTest extends TestCase
{
    use DatabaseTransactions;

    protected User $ownerA;

    protected User $ownerB;

    protected Tenant $tenantA;

    protected Tenant $tenantB;

    protected Branch $branchA;

    protected Member $memberA;

    protected MemberAgreementService $agreementService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agreementService = app(MemberAgreementService::class);
        $plan = Plan::where('slug', 'pro')->first() ?? Plan::first();
        $tenantService = app(TenantService::class);

        // Setup Tenant A
        $randA = Str::random(5);
        $regA = $tenantService->registerGym([
            'gym_name' => "Titan Fitness {$randA}",
            'gym_slug' => "titan-fitness-{$randA}",
            'owner_name' => "Titan Owner {$randA}",
            'email' => "titan_{$randA}@gymdeskhub.test",
            'phone' => '98' . rand(10000000, 99999999),
            'password' => 'Secret123!',
            'plan_id' => $plan->id,
        ]);
        $this->tenantA = $regA['tenant'];
        $this->ownerA = $regA['user'];
        $this->branchA = $this->tenantA->mainBranch;

        // Create Member in Tenant A
        $this->memberA = Member::create([
            'tenant_id' => $this->tenantA->id,
            'branch_id' => $this->branchA->id,
            'member_code' => 'TITAN001',
            'first_name' => 'Adwait',
            'last_name' => 'Rajput',
            'phone' => '9876543210',
            'email' => 'adwait@test.com',
            'gender' => 'male',
            'status' => 'ACTIVE',
        ]);

        // Setup Tenant B for isolation testing
        $randB = Str::random(5);
        $regB = $tenantService->registerGym([
            'gym_name' => "Iron Gym {$randB}",
            'gym_slug' => "iron-gym-{$randB}",
            'owner_name' => "Iron Owner {$randB}",
            'email' => "iron_{$randB}@gymdeskhub.test",
            'phone' => '97' . rand(10000000, 99999999),
            'password' => 'Secret123!',
            'plan_id' => $plan->id,
        ]);
        $this->tenantB = $regB['tenant'];
        $this->ownerB = $regB['user'];
    }

    public function test_can_generate_agreement_for_member_with_secure_token(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA, true, 'whatsapp');

        $this->assertNotNull($agreement);
        $this->assertEquals($this->tenantA->id, $agreement->tenant_id);
        $this->assertEquals($this->memberA->id, $agreement->member_id);
        $this->assertNotEmpty($agreement->token);
        $this->assertEquals(hash('sha256', $agreement->token), $agreement->token_hash);
        $this->assertEquals(MemberAgreement::STATUS_LINK_SENT, $agreement->status);
        $this->assertNotNull($agreement->link_sent_at);
        $this->assertStringContainsString('/agreements/' . $agreement->token, $agreement->share_url);
        $this->assertStringContainsString('whatsapp', $agreement->whatsapp_share_url);
    }

    public function test_public_agreement_shows_member_details_and_masked_phone(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);

        $response = $this->get(route('public.agreement.show', $agreement->token));

        $response->assertStatus(200);
        $response->assertSee('Titan Fitness');
        $response->assertSee('Adwait Rajput');
        $response->assertSee('TITAN001');
        // Masked phone format +91 98***3210
        $response->assertSee($this->agreementService->maskPhoneNumber($this->memberA->phone));
        $response->assertDontSee('Mobile OTP Verification');
    }

    public function test_otp_endpoints_are_unavailable(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $this->postJson('/agreements/'.$agreement->token.'/otp/send')->assertStatus(404);
        $this->postJson('/agreements/'.$agreement->token.'/otp/verify')->assertStatus(404);
    }

    public function test_profile_requires_explicit_confirmation(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $this->postJson(route('public.agreement.profile.confirm', $agreement->token))
            ->assertStatus(422)->assertJsonValidationErrors('profile_confirmed');
        $this->assertNull($agreement->fresh()->profile_confirmed_at);
    }

    public function test_expired_agreement_cannot_be_viewed_or_confirmed(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update(['token_expires_at' => now()->subMinute()]);
        $this->get(route('public.agreement.show', $agreement->token))->assertStatus(410);
        $this->postJson(route('public.agreement.profile.confirm', $agreement->token), ['profile_confirmed' => true])->assertStatus(410);
    }

    public function test_signature_requires_completed_profile_and_health_declaration(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $this->postJson(route('public.agreement.sign.submit', $agreement->token), [
            'signature_data' => 'data:image/png;base64,AAAA', 'terms_confirmed' => true,
        ])->assertStatus(422);
        $this->assertNull($agreement->fresh()->signed_at);
    }

    public function test_signed_agreement_cannot_be_changed(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update(['signed_at' => now(), 'status' => MemberAgreement::STATUS_COMPLETED]);
        $this->postJson(route('public.agreement.profile.confirm', $agreement->token), ['profile_confirmed' => true])->assertStatus(409);
        $this->postJson(route('public.agreement.decline', $agreement->token))->assertStatus(409);
    }

    public function test_uploaded_photo_is_shown_on_member_profile(): void
    {
        $this->memberA->update(['photo_path' => 'members/test-photo.jpg']);
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $this->get(route('public.agreement.show', $agreement->token))
            ->assertStatus(200)->assertSee(asset('storage/members/test-photo.jpg'));
    }

    public function test_health_submission_requires_every_question_to_be_answered(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update(['profile_confirmed_at' => now()]);
        $this->postJson(route('public.agreement.health.submit', $agreement->token), [
            'answers' => ['heart_condition' => ['answer' => 'no']],
            'declaration_confirmed' => true,
        ])->assertStatus(422)->assertJsonValidationErrors('answers.injuries_surgery.answer');
        $this->assertNull($agreement->fresh()->health_declaration_accepted_at);
    }

    public function test_profile_confirmation_proceeds_to_health_declaration(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);

        $response = $this->postJson(route('public.agreement.profile.confirm', $agreement->token), ['profile_confirmed' => true]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'next_step' => 3]);

        $agreement->refresh();
        $this->assertNotNull($agreement->profile_confirmed_at);
    }

    public function test_health_declaration_submission_and_medical_flagging(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update([
            'profile_confirmed_at' => now(),
        ]);

        // Submit PAR-Q with a medical disclosure
        $answers = [];
        foreach (TenantAgreementTemplate::getDefaultHealthQuestions() as $question) {
            $answers[$question['id']] = ['answer' => 'no', 'explanation' => null];
        }
        $answers['heart_condition'] = ['answer' => 'yes', 'explanation' => 'Mild hypertension on medication'];

        $response = $this->postJson(route('public.agreement.health.submit', $agreement->token), [
            'answers' => $answers,
            'declaration_confirmed' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true, 'has_conditions' => true]);

        $agreement->refresh();
        $this->assertTrue((bool) $agreement->has_medical_conditions);
        $this->assertEquals(MemberAgreement::STATUS_MEDICAL_REVIEW, $agreement->status);
        $this->assertNotNull($agreement->health_declaration_accepted_at);
        $this->assertCount(7, $agreement->health_declaration_answers);
    }

    public function test_electronic_signature_submission_seals_document_and_audit_hash(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update([
            'profile_confirmed_at' => now(),
            'health_declaration_accepted_at' => now(),
        ]);

        $fakeSignature = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';

        $response = $this->postJson(route('public.agreement.sign.submit', $agreement->token), [
            'signature_data' => $fakeSignature,
            'terms_confirmed' => 1,
            'terrace_confirmed' => 1,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $agreement->refresh();
        $this->assertEquals(MemberAgreement::STATUS_COMPLETED, $agreement->status);
        $this->assertNotNull($agreement->signed_at);
        $this->assertNotNull($agreement->signature_reference);
        $this->assertNotNull($agreement->signature_hash);
        $this->assertNotEmpty($agreement->signature_data);

        // Check audit trail contains cryptographic signature event
        $events = collect($agreement->audit_trail)->pluck('event')->toArray();
        $this->assertContains('agreement_signed', $events);
        $this->assertNull($agreement->otp_verified_at);
    }

    public function test_terrace_clauses_conditionally_included_based_on_setting(): void
    {
        // 1. When terrace is enabled
        $templateWithTerrace = $this->agreementService->publishNewTemplateVersion($this->tenantA, [
            'title' => 'Titan Membership Agreement',
            'open_terrace_enabled' => true,
        ], $this->ownerA);

        $this->assertTrue((bool) $templateWithTerrace->open_terrace_enabled);
        $this->assertTrue(collect($templateWithTerrace->terms_sections)->contains('key', 'open_terrace_rules'));

        // 2. When terrace is disabled
        $templateNoTerrace = $this->agreementService->publishNewTemplateVersion($this->tenantA, [
            'title' => 'Titan Standard Agreement',
            'open_terrace_enabled' => false,
        ], $this->ownerA);

        $this->assertFalse((bool) $templateNoTerrace->open_terrace_enabled);
        $this->assertFalse(collect($templateNoTerrace->terms_sections)->contains('key', 'open_terrace_rules'));
    }

    public function test_gym_owner_can_publish_new_immutable_version_without_modifying_old_agreements(): void
    {
        // Member A creates an agreement under v1.0
        $agreementV1 = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $v1Number = $agreementV1->version;

        // Owner publishes v2.0
        $this->actingAs($this->ownerA);
        $publishResponse = $this->post(route('app.agreements.settings.save'), [
            'title' => 'Titan Fitness Revised Terms',
            'open_terrace_enabled' => 1,
        ]);
        $publishResponse->assertRedirect(route('app.agreements.settings'));

        // Old agreement remains securely locked to v1
        $agreementV1->refresh();
        $this->assertEquals($v1Number, $agreementV1->version);

        // Active template in gym is now the new version
        $activeTemplate = $this->agreementService->getOrCreateActiveTemplate($this->tenantA);
        $this->assertEquals($v1Number + 1, $activeTemplate->version);
    }

    public function test_tenant_isolation_prevents_cross_tenant_agreement_access(): void
    {
        $agreementA = $this->agreementService->getOrCreateAgreementForMember($this->memberA);

        // Owner B tries to access Tenant A's agreement PDF and Audit
        $this->actingAs($this->ownerB);

        $responsePdf = $this->get(route('app.agreements.pdf', $agreementA->id));
        $responsePdf->assertStatus(404);

        $responseAudit = $this->get(route('app.agreements.audit', $agreementA->id));
        $responseAudit->assertStatus(404);
    }

    public function test_signed_agreement_pdf_document_renders_cleanly(): void
    {
        $agreement = $this->agreementService->getOrCreateAgreementForMember($this->memberA);
        $agreement->update([
            'status' => MemberAgreement::STATUS_COMPLETED,
            'signed_at' => now(),
            'signature_reference' => 'GDH-SIG-TEST-001',
            'signature_hash' => hash('sha256', 'test_signature_payload'),
            'signature_data' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==',
        ]);

        $this->actingAs($this->ownerA);

        $response = $this->get(route('app.agreements.pdf', $agreement->id));

        $response->assertStatus(200);
        $response->assertSee('Titan Fitness');
        $response->assertSee('Adwait Rajput');
        $response->assertSee('TITAN001');
        $response->assertSee('GDH-SIG-TEST-001');
        $response->assertSee('Digital Signature Verification');
    }
}
