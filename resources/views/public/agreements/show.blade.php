<x-agreement-public :tenant="$tenant">
    @php
        $questions = $template->health_questions ?? \App\Models\TenantAgreementTemplate::getDefaultHealthQuestions();
        $flowConfig = [
            'step' => $currentStep, 'csrf' => csrf_token(),
            'profileUrl' => route('public.agreement.profile.confirm', $agreement->token),
            'healthUrl' => route('public.agreement.health.submit', $agreement->token),
            'signUrl' => route('public.agreement.sign.submit', $agreement->token),
            'downloadUrl' => route('public.agreement.download', $agreement->token),
            'answers' => $agreement->health_declaration_answers ?? [],
            'questionIds' => array_column($questions, 'id'),
            'reference' => $agreement->signature_reference,
            'medicalReview' => $agreement->status === \App\Models\MemberAgreement::STATUS_MEDICAL_REVIEW,
            'terrace' => (bool) $agreement->terrace_rules_included,
        ];
    @endphp
    <div x-data="agreementFlow({{ \Illuminate\Support\Js::from($flowConfig) }})">
        <div class="pageintro"><div><p class="eyebrow">Membership documentation</p><h1>Member agreement</h1><p class="muted">Review your details, complete your health declaration and sign your membership terms.</p></div><span class="eyebrow">Version {{ $agreement->version }}</span></div>
        <article class="sheet">
            <header class="sheethead"><div class="member">
                @if($member->avatar_url)
                    <div x-data="{ photoFailed: false }"><img x-show="!photoFailed" x-on:error="photoFailed = true" class="portrait" src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}"><span x-cloak x-show="photoFailed" class="portrait initials">{{ mb_substr($member->first_name, 0, 1) }}</span></div>
                @else<span class="portrait initials" aria-label="Member initials">{{ mb_substr($member->first_name, 0, 1) }}{{ mb_substr($member->last_name ?? '', 0, 1) }}</span>@endif
                <div><p class="eyebrow">Member profile</p><h2>{{ $member->full_name }}</h2><div class="membermeta"><span>{{ $member->member_code }}</span><span>{{ $member->branch?->name ?? $tenant->name }}</span></div></div>
            </div></header>
            <nav class="steps" aria-label="Agreement progress" x-show="step < 6">
                @foreach([2 => 'Profile', 3 => 'Health', 4 => 'Terms', 5 => 'Signature'] as $number => $label)
                    <div class="step" :class="{ active: step === {{ $number }} }" :aria-current="step === {{ $number }} ? 'step' : null"><span>0{{ $number - 1 }}</span>{{ $label }}</div>
                @endforeach
            </nav>
            <div class="document">
                <noscript><p class="alert">Please enable JavaScript to complete this agreement.</p></noscript>
                <div x-cloak x-show="error" class="alert" role="alert" x-text="error"></div>
                <section x-show="step === 2">
                    <div class="sectionintro"><p class="eyebrow">01 / Member details</p><h2>Check your profile</h2><p>Please confirm these details belong to you.</p></div>
                    <dl class="facts"><div><dt>Full name</dt><dd>{{ $member->full_name }}</dd></div><div><dt>Member ID</dt><dd>{{ $member->member_code }}</dd></div><div><dt>Mobile number</dt><dd>{{ $maskedPhone }}</dd></div><div><dt>Membership</dt><dd>{{ $activeMembership?->plan?->name ?? 'Not assigned' }}</dd></div><div><dt>Branch</dt><dd>{{ $member->branch?->name ?? $tenant->name }}</dd></div><div><dt>Valid until</dt><dd>{{ $activeMembership?->end_date?->format('d M Y') ?? 'Not set' }}</dd></div>
                        <div><dt>Date of birth</dt><dd>{{ $member->dob?->format('d M Y') ?? 'Not provided' }}</dd></div>
                        <div><dt>Email</dt><dd>{{ $member->email ?: 'Not provided' }}</dd></div>
                        <div><dt>Residential address</dt><dd>{{ $member->address ?: 'Not provided' }}</dd></div>
                        <div><dt>Emergency contact</dt><dd>{{ $member->emergency_contact_name ?: 'Not provided' }}@if($member->emergency_contact_phone)<br>{{ $member->emergency_contact_phone }}@endif</dd></div>
                    </dl>
                    <p class="notice">If anything is incorrect, contact the gym reception before continuing.</p>
                    <label class="check"><input type="checkbox" x-model="profileConfirmed"><span>I confirm that this is my profile and the details above are correct.</span></label>
                    <div class="actions"><span class="muted">Next: Health declaration</span><button class="primary" :disabled="loading || !profileConfirmed" @click="confirmProfile()" x-text="loading ? 'Saving...' : 'Confirm & continue'"></button></div>
                </section>
                <section x-cloak x-show="step === 3">
                    <div class="sectionintro"><p class="eyebrow">02 / Health declaration</p><h2>Your health information</h2><p>Answer each question. Include any details your trainer should know when you select Yes.</p></div>
                    <div class="health-progress" aria-live="polite">
                        <div><strong>Health questionnaire</strong><span x-text="answeredCount() + ' of ' + config.questionIds.length + ' answered'"></span></div>
                        <progress :value="answeredCount()" :max="config.questionIds.length" aria-label="Questions answered"></progress>
                    </div>
                    @foreach($questions as $question)
                        <fieldset class="question" :class="{ answered: ['yes', 'no'].includes(answers[{{ \Illuminate\Support\Js::from($question['id']) }}]?.answer) }"><legend><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ $question['question'] }}</legend>
                            <div class="answers">@foreach(['yes' => 'Yes', 'no' => 'No'] as $value => $label)<label class="answer"><input type="radio" name="health_{{ $question['id'] }}" value="{{ $value }}" :checked="answers[{{ \Illuminate\Support\Js::from($question['id']) }}]?.answer === '{{ $value }}'" @change="setAnswer({{ \Illuminate\Support\Js::from($question['id']) }}, '{{ $value }}')">{{ $label }}</label>@endforeach</div>
                            <div x-cloak x-show="answers[{{ \Illuminate\Support\Js::from($question['id']) }}]?.answer === 'yes'"><label class="muted" for="details_{{ $question['id'] }}">Additional details (optional)</label><textarea id="details_{{ $question['id'] }}" maxlength="2000" :value="answers[{{ \Illuminate\Support\Js::from($question['id']) }}]?.explanation || ''" @input="answers[{{ \Illuminate\Support\Js::from($question['id']) }}].explanation = $event.target.value" placeholder="Condition, medication or relevant advice"></textarea></div>
                        </fieldset>
                    @endforeach
                    <p class="notice">Your answers help gym staff review your training needs. Tell your trainer if your health changes.</p>
                    <label class="check declaration"><input type="checkbox" x-model="healthConfirmed"><span>I confirm that the health information I have provided is accurate to the best of my knowledge.</span></label>
                    <div class="actions health-actions"><span class="save-hint" x-text="allAnswered() ? (healthConfirmed ? 'Ready to continue' : 'Confirm your declaration to continue') : (config.questionIds.length - answeredCount()) + ' questions remaining'"></span><button class="secondary" :disabled="loading" @click="go(2)">Back</button><button class="primary" :disabled="loading || !allAnswered() || !healthConfirmed" @click="submitHealth()" x-text="loading ? 'Saving...' : 'Continue to terms'"></button></div>
                </section>
                <section x-cloak x-show="step === 4" :class="{ 'reader-large': largeText }">
                    <div class="sectionintro"><p class="eyebrow">03 / Membership terms</p><h2>Read your agreement</h2><p>These terms apply to your membership at {{ $tenant->name }}.</p></div>
                    <div class="reading-tools">
                        <details class="browser-translation-help">
                            <summary>Translate this page</summary>
                            <p>Use your browser's Translate option and choose your preferred language.</p>
                            <ul>
                                <li><strong>Chrome on mobile:</strong> Open the browser menu and tap Translate.</li>
                                <li><strong>Chrome or Edge on desktop:</strong> Right-click the page and choose Translate.</li>
                                <li><strong>Safari on iPhone:</strong> Open the page menu in the address bar and look for Translate.</li>
                            </ul>
                            <p>Options and languages depend on your browser. If Translate is unavailable, open this link in Chrome or Edge.</p>
                        </details>
                        <div class="reading-size" role="group" aria-label="Reading text size"><button type="button" :aria-pressed="!largeText" @click="largeText = false" aria-label="Standard text size">A</button><button type="button" :aria-pressed="largeText" @click="largeText = true" aria-label="Larger text size">A+</button></div>
                    </div>
                    <details class="reader-index"><summary>Jump to a section ({{ count($template->terms_sections ?? []) }})</summary>
                    <nav class="terms-index" aria-label="Agreement sections">
                        <span class="eyebrow">In this agreement</span>
                        @foreach($template->terms_sections ?? [] as $section)
                            <a href="#agreement-section-{{ $loop->iteration }}"><span>{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>{{ mb_convert_case($section['title'], MB_CASE_TITLE) }}</a>
                        @endforeach
                    </nav>
                    </details>
                    @foreach($template->terms_sections ?? [] as $section)
                        <section class="terms" id="agreement-section-{{ $loop->iteration }}">
                            <header class="terms-heading"><span class="terms-number">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span><h3 dir="auto">{{ mb_convert_case($section['title'], MB_CASE_TITLE) }}</h3>

                            </header>
                            <ul class="terms-clauses">
                                @foreach($section['clauses'] ?? [] as $clause)
                                    <li dir="auto" @class(['policy-emphasis' => str_contains(strtolower($clause), 'fees are non-refundable')])>{{ $clause }}</li>
                                @endforeach
                            </ul>
                        </section>
                    @endforeach
                    <p class="notice">Translations are provided for reading assistance and may contain errors. If a term is unclear, ask gym staff before signing.</p>
                    
                    <div id="terms-acceptance-card" class="terms-acceptance-card" :class="{ 'has-error': termsValidationError }">
                        <label class="check declaration" :class="{ 'highlight-checkbox': termsValidationError && !termsConfirmed }">
                            <input type="checkbox" x-model="termsConfirmed" @change="termsValidationError = ''">
                            <span>I confirm that I have read, understood, and accept the membership terms and conditions above.</span>
                        </label>
                        @if($agreement->terrace_rules_included)
                            <label class="check declaration terrace-check" :class="{ 'highlight-checkbox': termsValidationError && !terraceConfirmed }" style="margin-top:14px">
                                <input type="checkbox" x-model="terraceConfirmed" @change="termsValidationError = ''">
                                <span>I acknowledge and accept the open terrace workout area safety rules and access restrictions.</span>
                            </label>
                        @endif
                        <div x-cloak x-show="termsValidationError" class="terms-error-message" role="alert">
                            <svg class="error-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span x-text="termsValidationError"></span>
                        </div>
                    </div>

                    <div class="actions terms-actions">
                        <button type="button" class="secondary" @click="go(3)">
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            <span>Back</span>
                        </button>
                        <button type="button" class="primary continue-to-sign-btn" :class="{ 'is-ready': termsConfirmed && (!config.terrace || terraceConfirmed) }" @click="proceedToSignature()">
                            <span>Continue to signature</span>
                            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </section>
                <section x-cloak x-show="step === 5">
                    <div class="sectionintro"><p class="eyebrow">04 / Signature</p><h2>Sign your agreement</h2><p>By signing, you confirm your health declaration and acceptance of the membership terms.</p></div>
                    <p><strong>{{ $member->full_name }}</strong><br><span class="muted">Member {{ $member->member_code }}</span></p>
                    <canvas class="signature" x-ref="signature" width="1200" height="380" aria-label="Draw your signature using a finger, stylus or mouse" @pointerdown="startDraw($event)" @pointermove="draw($event)" @pointerup="stopDraw($event)" @pointercancel="stopDraw($event)" @lostpointercapture="drawing = false"></canvas>
                    <div class="signaturetools"><span>Sign above using your finger or mouse</span><button class="textbutton" :disabled="loading" @click="clearSignature()">Clear signature</button></div>
                    <div class="actions"><button class="secondary" :disabled="loading" @click="go(4)">Back</button><button class="primary" :disabled="loading || !hasSignature" @click="sign()" x-text="loading ? 'Submitting...' : 'Sign & submit'"></button></div>
                </section>
                <section x-cloak x-show="step === 6" class="complete"><p class="eyebrow">Membership records</p><h2>Agreement signed</h2><p class="muted" style="margin-top:12px">A copy of your signed agreement is available below.</p><p class="notice" x-show="medicalReview">Your health declaration is awaiting review by the gym team.</p><div class="receipt"><p class="eyebrow">Agreement reference</p><p x-text="reference"></p></div><div class="signed-actions"><a class="primary" :href="config.downloadUrl" target="_blank" rel="noopener noreferrer">View / save A4 PDF</a></div><p class="muted" style="margin-top:10px;font-size:12px">Open the signed document, then select Print / Save A4 PDF. Choose Save as PDF in your device’s print options to download a copy.</p></section>
            </div>
        </article>
    </div>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('agreementFlow', (config) => ({
                config, largeText: false, step: config.step, answers: { ...config.answers }, reference: config.reference,
                medicalReview: config.medicalReview, profileConfirmed: false, healthConfirmed: false,
                termsConfirmed: false, terraceConfirmed: false, termsValidationError: '', loading: false, error: '', hasSignature: false, drawing: false,
                go(step) { this.step = step; this.error = ''; this.termsValidationError = ''; window.scrollTo({ top: 0, behavior: 'auto' }); },
                proceedToSignature() {
                    this.termsValidationError = '';
                    if (!this.termsConfirmed) {
                        this.termsValidationError = 'Please confirm you have read and accepted the membership terms by checking the box above.';
                        const el = document.getElementById('terms-acceptance-card');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    if (this.config.terrace && !this.terraceConfirmed) {
                        this.termsValidationError = 'Please confirm you accept the open terrace access and safety rules by checking the box above.';
                        const el = document.getElementById('terms-acceptance-card');
                        if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        return;
                    }
                    this.go(5);
                },
                setAnswer(id, answer) {
                    this.answers = { ...this.answers, [id]: { answer, explanation: this.answers[id]?.explanation || '' } };
                    this.error = '';
                },
                answeredCount() { return config.questionIds.filter(id => ['yes', 'no'].includes(this.answers[id]?.answer)).length; },
                healthPayload() {
                    return Object.fromEntries(config.questionIds.map(id => [id, {
                        answer: this.answers[id]?.answer,
                        explanation: this.answers[id]?.answer === 'yes' ? (this.answers[id]?.explanation || '') : null
                    }]));
                },
                allAnswered() { return config.questionIds.every(id => ['yes', 'no'].includes(this.answers[id]?.answer)); },
                async post(url, payload) {
                    if (this.loading) return null;
                    this.loading = true; this.error = '';
                    try {
                        const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': config.csrf }, body: JSON.stringify(payload) });
                        const data = await response.json();
                        if (!response.ok || !data.success) throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Unable to save. Please try again.');
                        return data;
                    } catch (error) { this.error = error instanceof SyntaxError ? 'Your session may have expired. Refresh the page and try again.' : error.message; return null; }
                    finally { this.loading = false; }
                },
                async confirmProfile() { if (!this.profileConfirmed) return; const result = await this.post(config.profileUrl, { profile_confirmed: this.profileConfirmed }); if (result) this.go(3); },
                async submitHealth() { if (!this.allAnswered() || !this.healthConfirmed) return; const result = await this.post(config.healthUrl, { answers: this.healthPayload(), declaration_confirmed: this.healthConfirmed }); if (result) { this.medicalReview = result.has_conditions; this.go(4); } },
                position(event) { const canvas = this.$refs.signature, rect = canvas.getBoundingClientRect(); return { x: (event.clientX - rect.left) * canvas.width / rect.width, y: (event.clientY - rect.top) * canvas.height / rect.height }; },
                startDraw(event) { if (this.loading) return; event.preventDefault(); const canvas = this.$refs.signature; canvas.setPointerCapture(event.pointerId); const ctx = canvas.getContext('2d'), point = this.position(event); ctx.strokeStyle = '#252b33'; ctx.lineWidth = 4; ctx.lineCap = 'round'; ctx.lineJoin = 'round'; ctx.beginPath(); ctx.moveTo(point.x, point.y); this.drawing = true; },
                draw(event) { if (!this.drawing) return; event.preventDefault(); const point = this.position(event), ctx = this.$refs.signature.getContext('2d'); ctx.lineTo(point.x, point.y); ctx.stroke(); this.hasSignature = true; },
                stopDraw(event) { this.drawing = false; if (this.$refs.signature.hasPointerCapture(event.pointerId)) this.$refs.signature.releasePointerCapture(event.pointerId); },
                clearSignature() { const canvas = this.$refs.signature; canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height); this.hasSignature = false; },
                async sign() {
                    if (!this.hasSignature) return;
                    if (!this.termsConfirmed || (config.terrace && !this.terraceConfirmed)) { this.go(4); return; }
                    const result = await this.post(config.signUrl, { signature_data: this.$refs.signature.toDataURL('image/png'), terms_confirmed: this.termsConfirmed, terrace_confirmed: this.terraceConfirmed });
                    if (result) { this.reference = result.reference; config.downloadUrl = result.download_url; this.medicalReview = result.status === 'MEDICAL_REVIEW_REQUIRED'; this.go(6); }
                }
            }));
        });
    </script>
</x-agreement-public>

