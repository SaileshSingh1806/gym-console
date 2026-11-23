<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\MemberAgreement;
use App\Services\MemberAgreementService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PublicAgreementController extends Controller
{
    public function __construct(
        protected MemberAgreementService $agreementService
    ) {}

    /**
     * Show the personalized digital agreement experience.
     */
    public function show(string $token): View
    {
        $agreement = MemberAgreement::with(['tenant.mainBranch', 'member.activeMembership.plan', 'member.branch', 'template'])
            ->where('token', $token)
            ->firstOrFail();

        $this->ensureAccessible($agreement);

        $tenant = $agreement->tenant;
        $member = $agreement->member;
        $template = $agreement->template ?? $this->agreementService->getOrCreateActiveTemplate($tenant);
        $activeMembership = $member->activeMembership ?? $member->memberships()->latest('id')->first();
        $maskedPhone = $this->agreementService->maskPhoneNumber($member->phone);

        // Determine starting step
        $currentStep = 2; // Profile, health declaration, terms, signature, completed.
        if ($agreement->isCompleted()) {
            $currentStep = 6;
        } elseif ($agreement->signed_at) {
            $currentStep = 6;
        } elseif ($agreement->terms_accepted_at) {
            $currentStep = 5;
        } elseif ($agreement->health_declaration_accepted_at) {
            $currentStep = 4;
        } elseif ($agreement->profile_confirmed_at) {
            $currentStep = 3;

        }

        return view('public.agreements.show', compact(
            'agreement',
            'tenant',
            'member',
            'template',
            'activeMembership',
            'maskedPhone',
            'currentStep'
        ));
    }

    /**
     * Confirm existing member profile details card.
     */
    public function confirmProfile(Request $request, string $token): JsonResponse
    {
        $agreement = MemberAgreement::where('token', $token)->firstOrFail();

        $this->ensureEditable($agreement);
        $request->validate(['profile_confirmed' => 'required|accepted']);

        $this->agreementService->confirmProfile($agreement);

        return response()->json([
            'success' => true,
            'message' => 'Profile details confirmed.',
            'next_step' => 3,
        ]);
    }

    /**
     * Submit Health Declaration (PAR-Q).
     */
    public function submitHealth(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'answers' => 'required|array',
            'declaration_confirmed' => 'required|accepted',
        ]);

        $agreement = MemberAgreement::where('token', $token)->firstOrFail();

        $this->ensureEditable($agreement);
        abort_unless($agreement->profile_confirmed_at, 422, 'Confirm your profile first.');
        $questions = $agreement->template?->health_questions ?? \App\Models\TenantAgreementTemplate::getDefaultHealthQuestions();
        $rules = [];
        foreach ($questions as $question) {
            $rules['answers.'.$question['id'].'.answer'] = 'required|in:yes,no';
            $rules['answers.'.$question['id'].'.explanation'] = 'nullable|string|max:2000';
        }
        $validated = $request->validate($rules);
        $this->agreementService->submitHealthDeclaration($agreement, $validated['answers'] ?? []);

        return response()->json([
            'success' => true,
            'message' => 'Health declaration submitted successfully.',
            'has_conditions' => $agreement->has_medical_conditions,
            'next_step' => 4,
        ]);
    }

    /**
     * Complete Electronic Signing.
     */
    public function submitSignature(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'signature_data' => 'required|string|max:500000',
            'terms_confirmed' => 'required|accepted',
            'terrace_confirmed' => 'nullable',
        ]);

        $agreement = MemberAgreement::where('token', $token)->firstOrFail();

        $this->ensureEditable($agreement);
        abort_unless($agreement->profile_confirmed_at && $agreement->health_declaration_accepted_at, 422, 'Confirm your profile and complete the health declaration before signing.');
        if ($agreement->terrace_rules_included) {
            $request->validate(['terrace_confirmed' => 'required|accepted']);
        }

        $sigData = (string) $request->input('signature_data');
        if (! preg_match('/^data:image\/png;base64,[A-Za-z0-9+\/=]+$/', $sigData)) {
            return response()->json(['success' => false, 'message' => 'Invalid signature format provided.'], 422);
        }

        $agreement = $this->agreementService->acceptTermsAndSign(
            $agreement,
            $sigData,
            $request->ip(),
            $request->userAgent()
        );

        return response()->json([
            'success' => true,
            'message' => 'Membership agreement and declaration successfully signed!',
            'reference' => $agreement->signature_reference,
            'signed_at' => $agreement->signed_at?->format('d M Y, h:i A'),
            'status' => $agreement->status,
            'download_url' => route('public.agreement.download', ['token' => $agreement->token]),
            'next_step' => 6,
        ]);
    }

    /**
     * Download or view the official signed agreement document.
     */
    public function download(string $token): Response
    {
        $agreement = MemberAgreement::with(['tenant.mainBranch', 'member.activeMembership.plan', 'template'])
            ->where('token', $token)
            ->firstOrFail();

        $this->ensureAccessible($agreement);

        $tenant = $agreement->tenant;
        $member = $agreement->member;
        $template = $agreement->template ?? $this->agreementService->getOrCreateActiveTemplate($tenant);
        $activeMembership = $member->activeMembership ?? $member->memberships()->latest('id')->first();
        $branch = $member->branch ?? $tenant->mainBranch;

        $html = view('app.agreements.document', compact(
            'agreement',
            'tenant',
            'member',
            'template',
            'activeMembership',
            'branch'
        ))->render();

        return response($html, 200, [
            'Content-Type' => 'text/html; charset=UTF-8',
            'Content-Disposition' => "inline; filename=\"Agreement_{$member->member_code}.html\"",
        ]);
    }

    /**
     * Member declines agreement with an optional note.
     */
    public function decline(Request $request, string $token): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $agreement = MemberAgreement::where('token', $token)->firstOrFail();
        $this->ensureEditable($agreement);
        $agreement->status = MemberAgreement::STATUS_DECLINED;
        $agreement->declined_at = now();
        $agreement->declined_reason = $request->input('reason');
        $agreement->recordAuditEvent('agreement_declined', ['reason' => $request->input('reason')]);
        $agreement->save();

        return response()->json([
            'success' => true,
            'message' => 'You have declined the agreement. Our gym management has been notified.',
        ]);
    }
    private function ensureAccessible(MemberAgreement $agreement): void
    {
        abort_if($agreement->token_expires_at?->isPast(), 410, 'This agreement link has expired. Ask your gym for a new link.');
        abort_if(in_array($agreement->status, [MemberAgreement::STATUS_EXPIRED, MemberAgreement::STATUS_DECLINED], true), 410, 'This agreement link is no longer active.');
    }

    private function ensureEditable(MemberAgreement $agreement): void
    {
        $this->ensureAccessible($agreement);
        abort_if($agreement->signed_at || $agreement->isCompleted(), 409, 'This agreement has already been signed.');
    }

}
