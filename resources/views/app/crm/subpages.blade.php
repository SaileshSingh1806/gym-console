<x-app-layout :header="$title ?? 'CRM'">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $stats = $enquiryStats ?? ['total' => 0, 'new' => 0, 'today' => 0, 'converted' => 0];
    @endphp

    @push('styles')
    <style>
        .enquiry-workspace { --eq-surface:rgb(var(--c-surface)); --eq-line:rgb(var(--c-line)); --eq-muted:rgb(var(--c-fg-muted)); --eq-text:rgb(var(--c-fg)); --eq-soft:rgb(var(--c-raised)); }
        .enquiry-heading { display:flex; justify-content:space-between; align-items:center; gap:20px; padding:4px 0; }
        .enquiry-kicker { color:var(--eq-muted); font-size:11px; letter-spacing:.1em; text-transform:uppercase; margin-bottom:7px; font-weight:600; }
        .enquiry-heading h1 { font-size:28px; line-height:1.3; letter-spacing:-.8px; font-weight:650; color:var(--eq-text); }
        .enquiry-heading p:last-child { font-size:13px; color:var(--eq-muted); margin-top:8px; }
        .enquiry-add { display:inline-flex; align-items:center; justify-content:center; gap:10px; min-height:44px; padding:11px 18px; border-radius:7px; font-size:13px; font-weight:600; white-space:nowrap; }
        .enquiry-summary { display:grid; grid-template-columns:repeat(4,minmax(0,1fr)); border:1px solid var(--eq-line); border-radius:10px; background:var(--eq-surface); overflow:hidden; }
        .enquiry-summary>div { padding:20px 24px; border-right:1px solid var(--eq-line); }
        .enquiry-summary>div:last-child { border:0; }
        .enquiry-summary dt { font-size:12px; color:var(--eq-muted); }
        .enquiry-summary dd { font-size:30px; line-height:1.3; font-weight:600; letter-spacing:-1px; font-variant-numeric:tabular-nums; color:var(--eq-text); margin-top:9px; }
        .enquiry-summary p { font-size:11px; color:var(--eq-muted); margin-top:5px; }
        .enquiry-workspace .enquiry-filters { border-radius:9px; padding:16px; gap:12px; box-shadow:none; }
        .enquiry-filters input,.enquiry-filters select,.enquiry-filters button,.enquiry-filters>a { min-height:42px; font-size:12px; }
        .enquiry-filters>div>svg { top:13px; }
        .enquiry-panel { border:1px solid var(--eq-line); border-radius:10px; overflow:hidden; background:var(--eq-surface); }
        .enquiry-list-heading { padding:18px 20px; display:flex; align-items:center; justify-content:space-between; gap:14px; border-bottom:1px solid var(--eq-line); }
        .enquiry-list-heading h2 { font-size:15px; font-weight:600; color:var(--eq-text); }
        .enquiry-list-heading p { font-size:12px; color:var(--eq-muted); margin-top:4px; }
        .enquiry-list-heading>span { color:var(--eq-muted); font-size:12px; white-space:nowrap; }
        .enquiry-panel table { min-width:1100px; }
        .enquiry-panel th { font-size:10px; letter-spacing:.06em; background:var(--eq-soft); padding:13px 18px; }
        .enquiry-panel td { padding:18px; font-size:12px; vertical-align:middle; }
        .enquiry-panel td:first-child { min-width:170px; font-size:13px; }
        .enquiry-panel td select { min-height:38px; max-width:150px; }
        .enquiry-panel td:last-child a { min-height:38px; min-width:38px; display:inline-flex; align-items:center; justify-content:center; box-shadow:none; }
        .enquiry-workspace :focus-visible { outline:2px solid var(--theme-accent,rgb(var(--c-accent))); outline-offset:3px; }
        .enquiry-workspace .fixed input,.enquiry-workspace .fixed select { min-height:42px; font-size:13px; }
        .enquiry-workspace .fixed button { min-height:40px; }
        @media(max-width:760px) { .enquiry-heading { align-items:flex-start; flex-direction:column; gap:16px; } .enquiry-heading h1 { font-size:24px; } .enquiry-add { width:100%; } .enquiry-summary { grid-template-columns:repeat(2,minmax(0,1fr)); } .enquiry-summary>div { padding:16px; border-bottom:1px solid var(--eq-line); } .enquiry-summary>div:nth-child(2) { border-right:0; } .enquiry-summary>div:nth-child(3) { border-bottom:0; } .enquiry-summary dd { font-size:26px; } .enquiry-filters>div { flex-basis:100%; min-width:0; } .enquiry-filters select { flex:1; min-width:120px; } .enquiry-list-heading { align-items:flex-start; flex-direction:column; gap:6px; } }

        .enquiry-modal .enquiry-dialog { max-width:720px; max-height:calc(100dvh - 48px); border-radius:12px; color:var(--eq-text); background:var(--eq-surface); }
        .enquiry-dialog-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; padding:24px 28px; border-bottom:1px solid var(--eq-line); flex-shrink:0; }
        .enquiry-dialog-header h2 { font-size:24px; font-weight:600; line-height:1.3; letter-spacing:-.6px; }
        .enquiry-dialog-header #enquiry-dialog-description { font-size:13px; color:var(--eq-muted); margin-top:6px; }
        .enquiry-dialog-close { width:40px; height:40px; display:grid; place-items:center; flex-shrink:0; color:var(--eq-muted); border-radius:7px; }
        .enquiry-dialog-close:hover { background:var(--eq-soft); color:var(--eq-text); }
        .enquiry-form-body { padding:0 28px; overflow-y:auto; overscroll-behavior:contain; flex:1; }
        .enquiry-form-section { padding:22px 0; border-bottom:1px solid var(--eq-line); }
        .enquiry-form-section:last-child { border-bottom:0; }
        .enquiry-section-heading { margin-bottom:17px; }
        .enquiry-section-heading h3 { font-size:14px; font-weight:600; }
        .enquiry-section-heading p { margin-top:4px; font-size:12px; color:var(--eq-muted); }
        .enquiry-form-body label { font-size:12px; font-weight:500; margin-bottom:7px; display:block; }
        .enquiry-workspace .enquiry-form-body input:not([type=hidden]),.enquiry-workspace .enquiry-form-body select,.enquiry-workspace .enquiry-form-body textarea { min-height:44px; font-size:13px; border-radius:7px; border-color:var(--eq-line); background:var(--eq-soft); color:var(--eq-text); }
        .enquiry-form-body textarea { resize:vertical; }
        .enquiry-date-shortcuts { display:flex; flex-wrap:wrap; gap:6px; margin-top:8px; }
        .enquiry-workspace .enquiry-date-shortcuts button { min-height:32px; padding:5px 11px; font-size:11px; border:1px solid var(--eq-line); border-radius:6px; }
        .enquiry-dialog-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 28px; background:var(--eq-surface); border-top:1px solid var(--eq-line); flex-shrink:0; }
        .enquiry-workspace .enquiry-dialog-footer button { min-height:44px; font-size:13px; padding:10px 20px; }
        @media(max-width:640px) { .enquiry-workspace .enquiry-modal { padding:8px; } .enquiry-modal .enquiry-dialog { max-height:calc(100dvh - 16px); border-radius:10px; } .enquiry-dialog-header { padding:20px; } .enquiry-form-body { padding:0 20px; } .enquiry-dialog-footer { padding:14px 20px; } .enquiry-dialog-footer button { flex:1; justify-content:center; } }
    </style>
    @endpush

    <div class="crm-enquiries-scope {{ ($section ?? '') === 'enquiries' ? 'enquiry-workspace' : '' }} space-y-6" x-data="{
        showAddModal: {{ $errors->any() ? 'true' : 'false' }},
        followUpDate: '{{ old('follow_up_date', '') }}',
        selectedPlanId: '{{ old('membership_plan_id', '') }}',
        estimatedValue: '{{ old('estimated_value', '') }}',
        onPlanChange(event) {
            const selectedOpt = event.target.options[event.target.selectedIndex];
            const price = selectedOpt ? selectedOpt.getAttribute('data-price') : '';
            this.selectedPlanId = event.target.value;
            this.estimatedValue = price || '';
        },
        setQuickFollowUp(days) {
            const d = new Date();
            d.setDate(d.getDate() + days);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            this.followUpDate = `${yyyy}-${mm}-${dd}`;
        },
        showPaidModal: false,
        paidModalData: {
            leadId: '',
            leadName: '',
            previousStage: '',
            paidAmount: '',
            formElement: null,
            selectElement: null
        },
        handleStageChange(event, leadId, leadName, currentStage, currentAmount) {
            const newStage = event.target.value;
            if (newStage === 'PAID') {
                this.paidModalData = {
                    leadId: leadId,
                    leadName: leadName,
                    previousStage: currentStage,
                    paidAmount: (currentAmount && currentAmount > 0) ? currentAmount : '',
                    formElement: event.target.form,
                    selectElement: event.target
                };
                this.showPaidModal = true;
            } else {
                event.target.form.submit();
            }
        },
        cancelPaidModal() {
            if (this.paidModalData.selectElement) {
                this.paidModalData.selectElement.value = this.paidModalData.previousStage;
            }
            this.showPaidModal = false;
        },
        confirmPaidModal() {
            if (this.paidModalData.formElement) {
                let input = this.paidModalData.formElement.querySelector('input[name=\'paid_amount\']');
                if (!input) {
                    input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'paid_amount';
                    this.paidModalData.formElement.appendChild(input);
                }
                input.value = this.paidModalData.paidAmount || 0;
                this.paidModalData.formElement.submit();
            }
        }
    }">

        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white p-1 cursor-pointer" aria-label="Dismiss">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 flex items-start justify-between gap-3">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 rounded-lg bg-rose-100 dark:bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0 mt-0.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div class="text-xs space-y-1">
                        <p class="font-semibold">Please check the enquiry details and try again.</p>
                        <ul class="list-disc list-inside space-y-0.5 text-rose-600 dark:text-rose-300">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-rose-400 hover:text-rose-600 dark:hover:text-white p-1 cursor-pointer" aria-label="Dismiss">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if(($section ?? '') === 'enquiries')
            <header class="enquiry-heading">
                <div><p class="enquiry-kicker">Member acquisition</p><h1>Enquiries &amp; walk-ins</h1><p>Keep every conversation moving. Track prospects, plan follow-ups and manage new enquiries.</p></div>
                <button type="button" @click="showAddModal = true" class="enquiry-add bg-indigo-600 hover:bg-indigo-500 text-white"><span aria-hidden="true">+</span> Add enquiry</button>
            </header>
        @else
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-fg dark:text-white tracking-tight">{{ $title ?? 'CRM' }}</h2>
                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">{{ $subtitle ?? '' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if(($section ?? '') === 'enquiries')
                    <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Enquiry / Walk-In</span>
                    </button>
                @else
                    <a href="{{ route('app.crm.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white text-xs font-medium border border-line dark:border-line flex items-center gap-1.5 transition-colors">
                        <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>CRM Dashboard</span>
                    </a>
                @endif
            </div>
        </div>

        @endif

        <!-- ENQUIRIES SECTION -->
        @if(($section ?? '') === 'enquiries')
            <dl class="enquiry-summary" aria-label="Enquiry overview">
                @foreach([
                    ['key' => 'total', 'label' => 'Total enquiries', 'detail' => 'Across all sources'],
                    ['key' => 'new', 'label' => 'New leads', 'detail' => 'Ready for first contact'],
                    ['key' => 'today', 'label' => 'Received today', 'detail' => 'New enquiries today'],
                    ['key' => 'converted', 'label' => 'Converted', 'detail' => 'Paid or converted leads'],
                ] as $metric)
                    <div><dt>{{ $metric['label'] }}</dt><dd>{{ number_format((int) ($stats[$metric['key']] ?? 0)) }}</dd><p>{{ $metric['detail'] }}</p></div>
                @endforeach
            </dl>

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('app.crm.enquiries') }}" class="enquiry-filters p-3.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" aria-label="Search enquiries" value="{{ request('search') }}" placeholder="Search by name, phone, email, or notes..." class="w-full pl-9 pr-3.5 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none transition-colors">
                    <svg class="w-4 h-4 text-fg-muted dark:text-fg-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="source" aria-label="Filter by source" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs focus:border-accent focus:outline-none">
                    <option value="all" {{ request('source') === 'all' || !request('source') ? 'selected' : '' }}>All Sources</option>
                    <option value="walk_in" {{ request('source') === 'walk_in' ? 'selected' : '' }}>Walk-In</option>
                    <option value="phone" {{ request('source') === 'phone' ? 'selected' : '' }}>Phone Call</option>
                    <option value="whatsapp" {{ request('source') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="website" {{ request('source') === 'website' ? 'selected' : '' }}>Website</option>
                    <option value="instagram" {{ request('source') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="facebook" {{ request('source') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="google" {{ request('source') === 'google' ? 'selected' : '' }}>Google</option>
                    <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                    <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Other</option>
                </select>

                <select name="stage" aria-label="Filter by stage" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs focus:border-accent focus:outline-none">
                    <option value="all" {{ request('stage') === 'all' || !request('stage') ? 'selected' : '' }}>All Stages</option>
                    <option value="NEW_LEAD" {{ request('stage') === 'NEW_LEAD' ? 'selected' : '' }}>New Lead</option>
                    <option value="CONTACTED" {{ request('stage') === 'CONTACTED' ? 'selected' : '' }}>Contacted</option>
                    <option value="DEMO_BOOKED" {{ request('stage') === 'DEMO_BOOKED' ? 'selected' : '' }}>Demo Booked</option>
                    <option value="PROPOSAL_SENT" {{ request('stage') === 'PROPOSAL_SENT' ? 'selected' : '' }}>Proposal Sent</option>
                    <option value="NEGOTIATION" {{ request('stage') === 'NEGOTIATION' ? 'selected' : '' }}>Negotiation</option>
                    <option value="TRIAL" {{ request('stage') === 'TRIAL' ? 'selected' : '' }}>Trial</option>
                    <option value="PAID" {{ request('stage') === 'PAID' ? 'selected' : '' }}>Paid / Converted</option>
                    <option value="LOST" {{ request('stage') === 'LOST' ? 'selected' : '' }}>Lost</option>
                </select>

                <button type="submit" class="px-3.5 py-2 rounded-lg bg-surface hover:bg-raised dark:bg-raised dark:hover:bg-slate-700 text-white text-xs font-medium transition-colors cursor-pointer">
                    Filter
                </button>

                @if(request('search') || (request('source') && request('source') !== 'all') || (request('stage') && request('stage') !== 'all'))
                    <a href="{{ route('app.crm.enquiries') }}" class="px-3 py-2 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-medium transition-colors">Reset</a>
                @endif
            </form>

            <!-- Enquiries Table -->
            <div class="enquiry-panel">
                <div class="enquiry-list-heading"><div><h2>Enquiry register</h2><p>Contact details, next follow-up and stage in one place.</p></div><span>{{ $enquiries->total() }} {{ $enquiries->total() === 1 ? 'result' : 'results' }}</span></div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs font-semibold">
                            <tr>
                                <th class="py-3.5 px-4">Prospect</th>
                                <th class="py-3.5 px-4">Contact</th>
                                <th class="py-3.5 px-4">Source</th>
                                <th class="py-3.5 px-4">Stage</th>
                                <th class="py-3.5 px-4">Follow-Up &amp; Value</th>
                                <th class="py-3.5 px-4">Notes</th>
                                <th class="py-3.5 px-4">Received</th>
                                <th class="py-3.5 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                            @forelse($enquiries ?? [] as $enq)
                                @php
                                    $st = strtoupper($enq->effective_stage);
                                    $stageStyle = match($st) {
                                        'PAID', 'CONVERTED' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                        'TRIAL', 'TRIAL_SCHEDULED' => 'bg-cyan-50 dark:bg-cyan-500/10 text-cyan-700 dark:text-cyan-400 border-cyan-200 dark:border-cyan-500/20',
                                        'DEMO_BOOKED' => 'bg-purple-50 dark:bg-purple-500/10 text-purple-700 dark:text-purple-400 border-purple-200 dark:border-purple-500/20',
                                        'PROPOSAL_SENT', 'NEGOTIATION' => 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-500/20',
                                        'CONTACTED' => 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 border-blue-200 dark:border-blue-500/20',
                                        'LOST' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                                        default => 'bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-400 border-indigo-200 dark:border-indigo-500/20',
                                    };
                                    $sourceLabel = match(strtolower((string) ($enq->source ?: 'walk_in'))) {
                                        'walk_in' => 'Walk-In',
                                        'phone' => 'Phone Call',
                                        'whatsapp' => 'WhatsApp',
                                        'website' => 'Website',
                                        'instagram' => 'Instagram',
                                        'facebook' => 'Facebook',
                                        'google' => 'Google',
                                        'referral' => 'Referral',
                                        default => ucwords(str_replace('_', ' ', (string) ($enq->source ?: 'Other'))),
                                    };
                                    $noteText = trim((string) ($enq->remarks ?: ($enq->notes ?: '')));
                                    $followUp = $enq->follow_up_date ?? $enq->next_follow_up_at;
                                @endphp
                                <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-fg dark:text-white">{{ $enq->name }}</div>
                                        @if($enq->assignedTo)
                                            <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Owner: {{ $enq->assignedTo->name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($enq->phone)
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $enq->phone) }}" class="text-fg dark:text-fg hover:text-indigo-600 dark:hover:text-indigo-400 font-medium tabular-nums">{{ $enq->phone }}</a>
                                        @else
                                            <span class="text-fg-muted">--</span>
                                        @endif
                                        @if($enq->email)
                                            <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5 truncate max-w-[180px]">{{ $enq->email }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-xs font-medium bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted border border-line dark:border-line">
                                            {{ $sourceLabel }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <form action="{{ route('app.leads.stage', $enq->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <select name="stage" @change="handleStageChange($event, {{ $enq->id }}, '{{ addslashes($enq->name) }}', '{{ $st }}', {{ (float) ($enq->estimated_value ?? 0) }})" class="px-2.5 py-1 rounded-md text-[11px] font-semibold border cursor-pointer focus:outline-none {{ $stageStyle }}">
                                                <option value="NEW_LEAD" {{ in_array($st, ['NEW_LEAD', 'NEW']) ? 'selected' : '' }}>New Lead</option>
                                                <option value="CONTACTED" {{ $st === 'CONTACTED' ? 'selected' : '' }}>Contacted</option>
                                                <option value="DEMO_BOOKED" {{ $st === 'DEMO_BOOKED' ? 'selected' : '' }}>Demo Booked</option>
                                                <option value="PROPOSAL_SENT" {{ $st === 'PROPOSAL_SENT' ? 'selected' : '' }}>Proposal Sent</option>
                                                <option value="NEGOTIATION" {{ $st === 'NEGOTIATION' ? 'selected' : '' }}>Negotiation</option>
                                                <option value="TRIAL" {{ in_array($st, ['TRIAL', 'TRIAL_SCHEDULED']) ? 'selected' : '' }}>Trial</option>
                                                <option value="PAID" {{ in_array($st, ['PAID', 'CONVERTED']) ? 'selected' : '' }}>Paid</option>
                                                <option value="LOST" {{ $st === 'LOST' ? 'selected' : '' }}>Lost</option>
                                            </select>
                                        </form>
                                    </td>
                                    <td class="py-3.5 px-4 tabular-nums">
                                        @if($followUp)
                                            <div class="text-fg dark:text-fg font-medium">{{ \Illuminate\Support\Carbon::parse($followUp)->format('d M Y') }}</div>
                                        @else
                                            <div class="text-fg-muted">No follow-up</div>
                                        @endif
                                        @if($enq->membershipPlan)
                                            <div class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 mt-0.5 truncate max-w-[170px]" title="{{ $enq->membershipPlan->name }}">
                                                {{ $enq->membershipPlan->name }}
                                            </div>
                                        @endif
                                        @if((float) ($enq->estimated_value ?? 0) > 0)
                                            <div class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">{{ $currency }}{{ number_format((float) $enq->estimated_value, 0) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-fg-muted dark:text-fg-muted max-w-[220px]">
                                        <span class="line-clamp-2">{{ $noteText !== '' ? $noteText : '—' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-fg-muted dark:text-fg-muted tabular-nums whitespace-nowrap">{{ $enq->created_at ? $enq->created_at->format('d M Y') : '—' }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($enq->phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enq->phone) }}?text=Hello%20{{ urlencode($enq->name) }}" target="_blank" rel="noopener noreferrer" title="Message on WhatsApp" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-xs inline-flex items-center gap-1.5 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 2c-5.516 0-9.969 4.453-9.969 9.969 0 1.766.469 3.484 1.344 5l-1.406 5.156 5.281-1.375c1.469.797 3.109 1.219 4.75 1.219 5.516 0 9.969-4.453 9.969-9.969s-4.453-10-9.969-10zm5.813 14.125c-.25.703-1.438 1.344-2 1.422-.516.078-1.172.109-1.891-.125-.438-.141-.984-.328-1.688-.641-2.969-1.281-4.906-4.266-5.047-4.453-.156-.203-1.203-1.609-1.203-3.063s.766-2.172 1.031-2.469c.281-.297.609-.375.813-.375s.406 0 .578.016c.188.016.438-.078.688.531.25.609.859 2.094.938 2.25.078.156.125.344.031.547-.109.203-.156.328-.313.5-.156.172-.328.391-.469.531-.156.156-.328.328-.141.641.188.313.844 1.391 1.813 2.25 1.25 1.109 2.297 1.453 2.609 1.609.313.156.484.125.672-.078.188-.203.781-.906.984-1.219.203-.313.406-.266.688-.156.281.109 1.797.844 2.109 1 .313.156.516.234.594.359.078.125.078.734-.172 1.438z"/></svg>
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('app.leads.edit', $enq->id) }}" title="Edit Enquiry" aria-label="Edit enquiry for {{ $enq->name }}" class="p-1.5 rounded-lg bg-raised dark:bg-raised hover:bg-indigo-50 dark:hover:bg-indigo-500/20 text-fg-muted dark:text-fg-muted hover:text-indigo-600 dark:hover:text-indigo-400 border border-line dark:border-line transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <div class="text-sm font-medium text-fg-muted dark:text-fg-muted">No enquiries match your current filters</div>
                                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Click &ldquo;Add enquiry&rdquo; to record a new prospect.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($enquiries) && method_exists($enquiries, 'links'))
                    <div class="p-3 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                        {{ $enquiries->links() }}
                    </div>
                @endif
            </div>

            <!-- Modal: Add Enquiry / Walk-In -->
            <div x-show="showAddModal" @keydown.escape.window="if (showAddModal) { showAddModal = false; }" class="enquiry-modal fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div role="dialog" aria-modal="true" aria-labelledby="enquiry-dialog-title" aria-describedby="enquiry-dialog-description" class="enquiry-dialog bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full flex flex-col overflow-hidden my-auto" @click.away="showAddModal = false">
                    <header class="enquiry-dialog-header">
                        <div><p class="enquiry-kicker">New prospect</p><h2 id="enquiry-dialog-title">Add enquiry</h2><p id="enquiry-dialog-description">Record the conversation and decide what happens next.</p></div>
                        <button type="button" @click="showAddModal = false" class="enquiry-dialog-close" aria-label="Close enquiry form"><svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-width="1.5" d="m6 6 12 12M6 18 18 6"/></svg></button>
                    </header>

                    <form action="{{ route('app.crm.enquiries.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                        @csrf
                        <div class="enquiry-form-body">
                            <!-- Section 1: Prospect Contact -->
                            <div class="enquiry-form-section">
                                <div class="enquiry-section-heading"><h3>Contact details</h3><p>Start with their name and phone number.</p></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="sm:col-span-2">
                                        <label for="enquiry-name" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Full Name <span class="text-rose-500">*</span></label>
                                        <input type="text" autocomplete="name" id="enquiry-name" name="name" value="{{ old('name') }}" required placeholder="Rahul Sharma" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="enquiry-phone" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Phone Number <span class="text-rose-500">*</span></label>
                                        <input type="tel" inputmode="tel" autocomplete="tel" id="enquiry-phone" name="phone" value="{{ old('phone') }}" required placeholder="+91 9876543210" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs tabular-nums placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                                    </div>
                                    <div>
                                        <label for="enquiry-email" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Email Address</label>
                                        <input type="email" autocomplete="email" id="enquiry-email" name="email" value="{{ old('email') }}" placeholder="rahul@example.com" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Source & Ownership -->
                            <div class="enquiry-form-section">
                                <div class="enquiry-section-heading"><h3>Enquiry details</h3><p>Choose the source, current stage and person responsible.</p></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label for="enquiry-source" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Source</label>
                                        <select id="enquiry-source" name="source" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                            @php $oldSource = old('source', 'walk_in'); @endphp
                                            <option value="walk_in" {{ $oldSource === 'walk_in' ? 'selected' : '' }}>Walk-In</option>
                                            <option value="phone" {{ $oldSource === 'phone' ? 'selected' : '' }}>Phone Call</option>
                                            <option value="whatsapp" {{ $oldSource === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                            <option value="website" {{ $oldSource === 'website' ? 'selected' : '' }}>Website</option>
                                            <option value="instagram" {{ $oldSource === 'instagram' ? 'selected' : '' }}>Instagram</option>
                                            <option value="facebook" {{ $oldSource === 'facebook' ? 'selected' : '' }}>Facebook</option>
                                            <option value="google" {{ $oldSource === 'google' ? 'selected' : '' }}>Google</option>
                                            <option value="referral" {{ $oldSource === 'referral' ? 'selected' : '' }}>Referral</option>
                                            <option value="other" {{ $oldSource === 'other' ? 'selected' : '' }}>Other</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="enquiry-stage" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Stage</label>
                                        <select id="enquiry-stage" name="stage" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                            @php $oldStage = old('stage', 'NEW_LEAD'); @endphp
                                            <option value="NEW_LEAD" {{ $oldStage === 'NEW_LEAD' ? 'selected' : '' }}>New Lead</option>
                                            <option value="CONTACTED" {{ $oldStage === 'CONTACTED' ? 'selected' : '' }}>Contacted</option>
                                            <option value="DEMO_BOOKED" {{ $oldStage === 'DEMO_BOOKED' ? 'selected' : '' }}>Demo Booked</option>
                                            <option value="PROPOSAL_SENT" {{ $oldStage === 'PROPOSAL_SENT' ? 'selected' : '' }}>Proposal Sent</option>
                                            <option value="NEGOTIATION" {{ $oldStage === 'NEGOTIATION' ? 'selected' : '' }}>Negotiation</option>
                                            <option value="TRIAL" {{ $oldStage === 'TRIAL' ? 'selected' : '' }}>Trial</option>
                                            <option value="PAID" {{ $oldStage === 'PAID' ? 'selected' : '' }}>Paid / Converted</option>
                                        </select>
                                    </div>

                                    @if(($branches ?? collect())->count() > 0)
                                        <div>
                                            <label for="enquiry-branch_id" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Branch</label>
                                            <select id="enquiry-branch_id" name="branch_id" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                                <option value="">All / Primary Branch</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="{{ ($branches ?? collect())->count() > 0 ? '' : 'sm:col-span-2' }}">
                                        <label for="enquiry-assigned_to_user_id" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Assign to Staff</label>
                                        @php
                                            $cUser = auth()->user();
                                            $defEnqStaffId = old('assigned_to_user_id') ?: (($cUser && !$cUser->isGymOwner() && !$cUser->isSuperAdmin() && $cUser->isStaff()) ? $cUser->id : null);
                                        @endphp
                                        <select id="enquiry-assigned_to_user_id" name="assigned_to_user_id" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                            <option value="">Unassigned</option>
                                            @foreach($staffMembers ?? [] as $stf)
                                                <option value="{{ $stf->id }}" {{ (string) $defEnqStaffId === (string) $stf->id ? 'selected' : '' }}>
                                                    {{ $stf->name }} {{ ($cUser && $cUser->id === $stf->id) ? '(You)' : '' }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Section 3: Follow-Up & Pitched Membership -->
                            <div class="enquiry-form-section">
                                <div class="enquiry-section-heading"><h3>Plan the next step</h3><p>Set a follow-up date and select a membership if discussed.</p></div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="enquiry-follow_up_date" class="text-xs font-medium text-fg-muted dark:text-fg-muted">Next Follow-Up Date</label>

                                        </div>
                                        <input type="date" id="enquiry-follow_up_date" name="follow_up_date" x-model="followUpDate" class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs tabular-nums focus:border-accent focus:outline-none">
                                            <div class="enquiry-date-shortcuts">
                                                <button type="button" @click="setQuickFollowUp(0)" class="px-2 py-0.5 rounded bg-raised hover:bg-indigo-50 dark:bg-raised dark:hover:bg-indigo-500/20 text-xs font-medium text-fg-muted dark:text-fg-muted hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">Today</button>
                                                <button type="button" @click="setQuickFollowUp(1)" class="px-2 py-0.5 rounded bg-raised hover:bg-indigo-50 dark:bg-raised dark:hover:bg-indigo-500/20 text-xs font-medium text-fg-muted dark:text-fg-muted hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">Tomorrow</button>
                                                <button type="button" @click="setQuickFollowUp(3)" class="px-2 py-0.5 rounded bg-raised hover:bg-indigo-50 dark:bg-raised dark:hover:bg-indigo-500/20 text-xs font-medium text-fg-muted dark:text-fg-muted hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">+3 Days</button>
                                            </div>
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label for="enquiry-membership_plan_id" class="block text-xs font-medium text-fg-muted dark:text-fg-muted">
                                                Membership discussed
                                            </label>
                                            <span x-show="estimatedValue && estimatedValue > 0" x-cloak class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                                                {{ $currency }}<span x-text="Number(estimatedValue).toLocaleString('en-IN')"></span>
                                            </span>
                                        </div>
                                        <select id="enquiry-membership_plan_id" name="membership_plan_id" 
                                                x-model="selectedPlanId" 
                                                @change="onPlanChange($event)" 
                                                class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none cursor-pointer">
                                            <option value="" data-price="">-- Select Membership discussed --</option>
                                            @foreach($membershipPlans ?? [] as $plan)
                                                <option value="{{ $plan->id }}" data-price="{{ $plan->price }}" {{ (string) old('membership_plan_id') === (string) $plan->id ? 'selected' : '' }}>
                                                    {{ $plan->name }} ({{ $plan->duration_value }} {{ ucfirst($plan->duration_type ?? 'Months') }}) • {{ $currency }}{{ number_format($plan->price) }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input type="hidden" name="estimated_value" :value="estimatedValue">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 4: Notes / Goal -->
                            <div class="enquiry-form-section">
                                <label for="enquiry-remarks" class="block text-xs font-medium text-fg-muted dark:text-fg-muted mb-1">Notes / Fitness Goal</label>
                                <textarea id="enquiry-remarks" name="remarks" rows="3" placeholder="Interested in 6-month membership, weight loss, or personal training..." class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="enquiry-dialog-footer">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-medium transition-colors cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer ops-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Save enquiry</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal: Record Conversion Amount -->
            <div x-show="showPaidModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl max-w-md w-full p-6 space-y-5" @click.away="cancelPaidModal()">
                    <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-fg dark:text-white tracking-tight">Record Conversion Amount</h3>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Mark enquiry as converted</p>
                            </div>
                        </div>
                        <button type="button" @click="cancelPaidModal()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white p-1 cursor-pointer" aria-label="Close">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line">
                            <div class="text-xs text-fg-muted dark:text-fg-muted font-medium">Prospect Name</div>
                            <div class="text-sm font-semibold text-fg dark:text-white mt-0.5" x-text="paidModalData.leadName"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 dark:text-emerald-400 mb-1.5">
                                Conversion Amount ({{ $currency }}) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 font-semibold text-fg-muted text-sm">{{ $currency }}</span>
                                <input type="number" step="0.01" min="0" x-model="paidModalData.paidAmount" placeholder="5000" autofocus class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-emerald-500/50 text-fg dark:text-white font-semibold tabular-nums text-sm focus:border-emerald-500 focus:outline-none transition-colors">
                            </div>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1.5">This amount will be added to your CRM won revenue and conversion metrics.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="cancelPaidModal()" class="px-4 py-2 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-medium transition-colors cursor-pointer">Cancel</button>
                        <button type="button" @click="confirmPaidModal()" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Conversion</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- CONVERSIONS SECTION -->
        @if(($section ?? '') === 'conversions')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Converted Members</span>
                        <div class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ isset($paidLeads) && method_exists($paidLeads, 'total') ? $paidLeads->total() : (is_countable($paidLeads ?? []) ? count($paidLeads ?? []) : 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-center font-semibold">&#10003;</div>
                </div>
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                    <div>
                        <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Total Value Won</span>
                        <div class="text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-1">{{ $currency }}{{ number_format($totalRevenue ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 flex items-center justify-center font-semibold">{{ $currency }}</div>
                </div>
            </div>

            <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs font-semibold">
                            <tr>
                                <th class="py-3 px-4">Member Name</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Acquisition Source</th>
                                <th class="py-3 px-4">Conversion Value</th>
                                <th class="py-3 px-4">Date Won</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                            @forelse($paidLeads ?? [] as $lead)
                                <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                    <td class="py-3 px-4 font-semibold text-fg dark:text-white">{{ $lead->name }}</td>
                                    <td class="py-3 px-4 text-fg-muted dark:text-fg-muted font-mono">{{ $lead->phone }}</td>
                                    <td class="py-3 px-4 capitalize">{{ str_replace('_', ' ', $lead->source ?? 'Direct') }}</td>
                                    <td class="py-3 px-4 font-semibold text-amber-600 dark:text-amber-400">{{ $currency }}{{ number_format($lead->estimated_value ?? 0) }}</td>
                                    <td class="py-3 px-4 font-mono text-fg-muted dark:text-fg-muted">{{ $lead->created_at ? $lead->created_at->format('d M Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-fg-muted dark:text-fg-muted">No conversions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($paidLeads) && method_exists($paidLeads, 'links'))
                    <div class="p-3 border-t border-line dark:border-line bg-raised dark:bg-slate-950/40">
                        {{ $paidLeads->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- REPORTS SECTION -->
        @if(($section ?? '') === 'reports')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                    <div class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Total Leads Acquired</div>
                    <div class="text-2xl font-semibold text-fg dark:text-white mt-1">{{ $totalLeads ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                    <div class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Won / Paid Conversions</div>
                    <div class="text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-1">{{ $paidCount ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                    <div class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Active Pipeline Leads</div>
                    <div class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400 mt-1">{{ $activePipeline ?? 0 }}</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

