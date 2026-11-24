<?php

namespace App\Services;

use App\Models\Member;
use App\Models\MemberAgreement;
use App\Models\Tenant;
use App\Models\TenantAgreementTemplate;
use App\Models\User;
use App\Services\WhatsApp\WhatsAppCloudApiService;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MemberAgreementService
{
    /**
     * Get or create active agreement template for tenant.
     */
    public function getOrCreateActiveTemplate(Tenant $tenant): TenantAgreementTemplate
    {
        $template = TenantAgreementTemplate::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->latest('version')
            ->first();

        if ($template) {
            return $template;
        }

        $terraceEnabled = (bool) ($tenant->settings['open_terrace_enabled'] ?? false);

        return TenantAgreementTemplate::create([
            'tenant_id' => $tenant->id,
            'version' => 1,
            'title' => "{$tenant->name} — Membership Agreement & Health Declaration",
            'is_active' => true,
            'open_terrace_enabled' => $terraceEnabled,
            'terms_sections' => TenantAgreementTemplate::getDefaultTermsSections($terraceEnabled),
            'health_questions' => TenantAgreementTemplate::getDefaultHealthQuestions(),
            'published_at' => now(),
        ]);
    }

    /**
     * Publish a new immutable version of the agreement template.
     */
    public function publishNewTemplateVersion(Tenant $tenant, array $data, ?User $user = null): TenantAgreementTemplate
    {
        $latest = TenantAgreementTemplate::where('tenant_id', $tenant->id)->latest('version')->first();
        $nextVersion = $latest ? ($latest->version + 1) : 1;

        // Mark existing templates as inactive
        TenantAgreementTemplate::where('tenant_id', $tenant->id)->update(['is_active' => false]);

        $terraceEnabled = isset($data['open_terrace_enabled']) 
            ? (bool) $data['open_terrace_enabled'] 
            : (bool) ($tenant->settings['open_terrace_enabled'] ?? false);

        // Also persist open_terrace_enabled in tenant settings
        $settings = $tenant->settings ?? [];
        $settings['open_terrace_enabled'] = $terraceEnabled;
        $tenant->settings = $settings;
        $tenant->save();

        return TenantAgreementTemplate::create([
            'tenant_id' => $tenant->id,
            'version' => $nextVersion,
            'title' => $data['title'] ?? "{$tenant->name} — Membership Agreement & Health Declaration",
            'is_active' => true,
            'open_terrace_enabled' => $terraceEnabled,
            'terms_sections' => $data['terms_sections'] ?? TenantAgreementTemplate::getDefaultTermsSections($terraceEnabled),
            'health_questions' => $data['health_questions'] ?? TenantAgreementTemplate::getDefaultHealthQuestions(),
            'published_at' => now(),
            'published_by' => $user?->id ?? auth()->id(),
        ]);
    }

    /**
     * Get or create a personalized agreement for a member.
     */
    public function getOrCreateAgreementForMember(Member $member, bool $sendLink = false, ?string $channel = null): MemberAgreement
    {
        $tenant = $member->tenant ?? Tenant::findOrFail($member->tenant_id);
        $template = $this->getOrCreateActiveTemplate($tenant);

        // Check if member already has an active completed agreement
        $existingCompleted = MemberAgreement::where('tenant_id', $tenant->id)
            ->where('member_id', $member->id)
            ->where('status', MemberAgreement::STATUS_COMPLETED)
            ->latest('id')
            ->first();

        if ($existingCompleted) {
            return $existingCompleted;
        }

        // Check if member has an unexpired pending agreement
        $existingPending = MemberAgreement::where('tenant_id', $tenant->id)
            ->where('member_id', $member->id)
            ->whereIn('status', [MemberAgreement::STATUS_PENDING, MemberAgreement::STATUS_LINK_SENT, MemberAgreement::STATUS_MEDICAL_REVIEW])
            ->where(function ($q) {
                $q->whereNull('token_expires_at')->orWhere('token_expires_at', '>', now());
            })
            ->latest('id')
            ->first();

        if ($existingPending) {
            if ($sendLink && $channel) {
                $this->recordLinkSent($existingPending, $channel);
            }

            return $existingPending;
        }

        // Generate a new secure, random token (48 chars hex)
        $token = bin2hex(random_bytes(24));
        $tokenHash = hash('sha256', $token);

        $agreement = MemberAgreement::create([
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'agreement_template_id' => $template->id,
            'version' => $template->version,
            'token' => $token,
            'token_hash' => $tokenHash,
            'token_expires_at' => now()->addDays(30),
            'status' => $sendLink ? MemberAgreement::STATUS_LINK_SENT : MemberAgreement::STATUS_PENDING,
            'link_sent_at' => $sendLink ? now() : null,
            'link_sent_channel' => $sendLink ? ($channel ?? 'manual_copy') : null,
            'terrace_rules_included' => (bool) $template->open_terrace_enabled,
        ]);

        $agreement->recordAuditEvent('agreement_created', [
            'template_version' => $template->version,
            'channel' => $channel,
        ]);
        $agreement->save();

        return $agreement;
    }

    /**
     * Record link dispatched to member.
     */
    public function recordLinkSent(MemberAgreement $agreement, string $channel = 'whatsapp'): void
    {
        $agreement->link_sent_at = now();
        $agreement->link_sent_channel = $channel;
        if ($agreement->status === MemberAgreement::STATUS_PENDING) {
            $agreement->status = MemberAgreement::STATUS_LINK_SENT;
        }
        $agreement->recordAuditEvent('link_sent', ['channel' => $channel]);
        $agreement->save();

        // If WhatsApp Cloud API is configured for tenant, attempt automated sending
        if ($channel === 'whatsapp') {
            try {
                $tenant = $agreement->tenant;
                if (! empty($tenant->settings['whatsapp_phone_number_id']) && ! empty($tenant->settings['whatsapp_access_token'])) {
                    $api = new WhatsAppCloudApiService($tenant);
                    $phone = $agreement->member?->phone;
                    if ($phone) {
                        $gymName = $tenant->name;
                        $memberName = $agreement->member?->first_name ?? 'Member';
                        $url = $agreement->share_url;
                        $message = "Dear {$memberName},\n\nWelcome to {$gymName}!\n\nPlease complete your digital Membership Agreement and Health Declaration by clicking below:\n\n👉 {$url}\n\nIt takes less than 2 minutes on your mobile.\n\nWarm regards,\n{$gymName} Team";
                        $api->sendTextMessage($phone, $message);
                    }
                }
            } catch (Exception $e) {
                Log::info("WhatsApp automated agreement send skipped: {$e->getMessage()}");
            }
        }
    }

    /**
     * Member confirms existing membership profile card.
     */
    public function confirmProfile(MemberAgreement $agreement): void
    {
        $agreement->profile_confirmed_at = now();
        $agreement->recordAuditEvent('profile_confirmed');
        $agreement->save();
    }

    /**
     * Submit Health Declaration (PAR-Q).
     */
    public function submitHealthDeclaration(MemberAgreement $agreement, array $answers): void
    {
        $hasConditions = false;
        $formattedAnswers = [];

        foreach ($answers as $qId => $data) {
            $val = is_array($data) ? ($data['answer'] ?? 'no') : $data;
            $explanation = is_array($data) ? ($data['explanation'] ?? null) : null;
            $isYes = strtolower(trim((string) $val)) === 'yes';

            if ($isYes) {
                $hasConditions = true;
            }

            $formattedAnswers[$qId] = [
                'answer' => $isYes ? 'yes' : 'no',
                'explanation' => $isYes ? trim((string) $explanation) : null,
            ];
        }

        $agreement->health_declaration_answers = $formattedAnswers;
        $agreement->health_declaration_accepted_at = now();
        $agreement->has_medical_conditions = $hasConditions;

        if ($hasConditions && $agreement->status !== MemberAgreement::STATUS_COMPLETED) {
            $agreement->status = MemberAgreement::STATUS_MEDICAL_REVIEW;
        }

        $agreement->recordAuditEvent('health_declaration_submitted', [
            'has_medical_conditions' => $hasConditions,
        ]);
        $agreement->save();
    }

    /**
     * Sign agreement electronically with touch/drawn signature, audit hash, and IP evidence.
     */
    public function acceptTermsAndSign(
        MemberAgreement $agreement,
        string $signatureData,
        ?string $ip = null,
        ?string $userAgent = null
    ): MemberAgreement {
        $now = now();
        $tenant = $agreement->tenant;
        $member = $agreement->member;

        $agreement->terms_accepted_at = $now;

        if ($agreement->terrace_rules_included) {
            $agreement->terrace_declaration_accepted_at = $now;
        }

        $reference = MemberAgreement::generateSignatureReference($tenant->id, $member->id);

        // Cryptographic audit hash tying together agreement metadata, timestamps, signature, and member ID
        $auditPayload = implode('|', [
            $reference,
            $tenant->id,
            $member->id,
            $member->member_code,
            $agreement->version,
            $now->toIso8601String(),
            $ip ?? request()->ip(),
            hash('sha256', $signatureData),
        ]);
        $signatureHash = hash('sha256', $auditPayload);

        $agreement->signature_type = 'digital_drawn';
        $agreement->signature_data = $signatureData;
        $agreement->signature_provider = 'GymDeskHub Digital Signature Audit Engine';
        $agreement->signature_reference = $reference;
        $agreement->signature_hash = $signatureHash;
        $agreement->signed_at = $now;
        $agreement->signer_ip = $ip ?? request()->ip();
        $agreement->signer_user_agent = substr((string) ($userAgent ?? request()->userAgent()), 0, 500);

        // If medical review is needed, keep in review status; otherwise mark completed
        $agreement->status = $agreement->has_medical_conditions && ! $agreement->reviewed_at
            ? MemberAgreement::STATUS_MEDICAL_REVIEW
            : MemberAgreement::STATUS_COMPLETED;

        $agreement->recordAuditEvent('agreement_signed', [
            'reference' => $reference,
            'hash' => $signatureHash,
            'ip' => $agreement->signer_ip,
        ]);

        $agreement->save();

        // Generate and store final signed document
        $this->generateSignedDocument($agreement);

        return $agreement;
    }

    /**
     * Generate clean, official A4 printable signed agreement document & store in private storage.
     */
    public function generateSignedDocument(MemberAgreement $agreement): string
    {
        $tenant = $agreement->tenant;
        $member = $agreement->member;
        $template = $agreement->template ?? $this->getOrCreateActiveTemplate($tenant);

        $activeMembership = $member->activeMembership ?? $member->memberships()->latest('id')->first();
        $branch = $member->branch ?? $tenant->mainBranch;

        $dir = "agreements/tenant_{$tenant->id}";
        $filename = "agreement_{$member->member_code}_{$agreement->id}.html";
        $path = "{$dir}/{$filename}";

        $html = view('app.agreements.document', compact(
            'agreement',
            'tenant',
            'member',
            'template',
            'activeMembership',
            'branch'
        ))->render();

        Storage::disk('local')->put($path, $html);

        $agreement->pdf_path = $path;
        $agreement->save();

        return $path;
    }

    /**
     * Staff/Gym Owner approves medical review for member with health conditions.
     */
    public function approveMedicalReview(MemberAgreement $agreement, User $reviewer, ?string $notes = null): void
    {
        $agreement->reviewed_by_user_id = $reviewer->id;
        $agreement->reviewed_at = now();
        $agreement->medical_review_notes = $notes;

        if ($agreement->signed_at) {
            $agreement->status = MemberAgreement::STATUS_COMPLETED;
        }

        $agreement->recordAuditEvent('medical_review_approved', [
            'reviewer_id' => $reviewer->id,
            'reviewer_name' => $reviewer->name,
            'notes' => $notes,
        ]);
        $agreement->save();
    }

    /**
     * Get overview metrics for Gym Owner dashboard.
     */
    public function getDashboardStats(Tenant $tenant): array
    {
        $totalMembers = Member::where('tenant_id', $tenant->id)->count();

        $completedCount = MemberAgreement::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereIn('status', [MemberAgreement::STATUS_COMPLETED, 'completed', 'COMPLETED'])
                    ->orWhereNotNull('signed_at');
            })
            ->count();

        $medicalReviewCount = MemberAgreement::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereIn('status', [MemberAgreement::STATUS_MEDICAL_REVIEW, 'medical_review', 'MEDICAL_REVIEW_REQUIRED'])
                    ->orWhere('has_medical_conditions', true);
            })
            ->count();

        $pendingCount = MemberAgreement::where('tenant_id', $tenant->id)
            ->where(function ($q) {
                $q->whereIn('status', [MemberAgreement::STATUS_PENDING, MemberAgreement::STATUS_LINK_SENT, 'pending', 'link_sent', 'PENDING', 'LINK_SENT'])
                    ->whereNull('signed_at');
            })
            ->count();

        $totalAgreements = MemberAgreement::where('tenant_id', $tenant->id)->count();
        $notSentCount = max(0, $totalMembers - ($completedCount + $medicalReviewCount + $pendingCount));
        $completionPercentage = $totalAgreements > 0 ? round(($completedCount / $totalAgreements) * 100, 1) : 0;

        return [
            // Short keys (used by index.blade.php)
            'total' => $totalAgreements,
            'completed' => $completedCount,
            'pending' => $pendingCount,
            'medical_review' => $medicalReviewCount,
            'completion_rate' => $completionPercentage,

            // Long keys (backwards compatibility)
            'total_agreements' => $totalAgreements,
            'total_members' => $totalMembers,
            'completed_count' => $completedCount,
            'pending_count' => $pendingCount,
            'medical_review_count' => $medicalReviewCount,
            'not_sent_count' => $notSentCount,
            'completion_percentage' => $completionPercentage,
        ];
    }

    /**
     * Bulk generate agreements for already registered members.
     */
    public function bulkGenerateAgreements(Tenant $tenant, array $memberIds, bool $send = false, string $channel = 'whatsapp'): array
    {
        $members = Member::where('tenant_id', $tenant->id)
            ->whereIn('id', $memberIds)
            ->get();

        $created = 0;
        $alreadyActive = 0;

        foreach ($members as $m) {
            $agr = $this->getOrCreateAgreementForMember($m, $send, $channel);
            if ($agr->wasRecentlyCreated) {
                $created++;
            } else {
                $alreadyActive++;
            }
        }

        return [
            'total_processed' => $members->count(),
            'created' => $created,
            'already_active' => $alreadyActive,
        ];
    }

    /**
     * Mask mobile number for privacy (e.g. +91 ****** 43210).
     */
    public function maskPhoneNumber(?string $phone): string
    {
        $clean = preg_replace('/[^0-9]/', '', (string) $phone);
        if (strlen($clean) < 6) {
            return $phone ? (substr($phone, 0, 2) . '****') : '—';
        }

        $last4 = substr($clean, -4);
        $first2 = substr($clean, 0, 2);

        return "+91 {$first2}**** {$last4}";
    }
}
