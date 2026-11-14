<x-app-layout header="{{ $title ?? 'CRM' }}">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $stats = $enquiryStats ?? ['total' => 0, 'new' => 0, 'today' => 0, 'converted' => 0];
    @endphp

    <style>
        .crm-enquiries-scope {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
    </style>

    <div class="crm-enquiries-scope space-y-6" x-data="{
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
            <div class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-100 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 cursor-pointer" aria-label="Dismiss">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 flex items-start justify-between gap-3 shadow-sm">
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

        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-slate-900 dark:text-white tracking-tight">{{ $title ?? 'CRM' }}</h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">{{ $subtitle ?? '' }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if(($section ?? '') === 'enquiries')
                    <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Enquiry / Walk-In</span>
                    </button>
                @else
                    <a href="{{ route('app.crm.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white text-xs font-medium border border-slate-200 dark:border-slate-800 flex items-center gap-1.5 transition-colors shadow-sm">
                        <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>CRM Dashboard</span>
                    </a>
                @endif
            </div>
        </div>

        <!-- ENQUIRIES SECTION -->
        @if(($section ?? '') === 'enquiries')
            <!-- KPI Strip -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5">
                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Enquiries</span>
                        <span class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((int) ($stats['total'] ?? 0)) }}</div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">New Leads</span>
                        <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((int) ($stats['new'] ?? 0)) }}</div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Received Today</span>
                        <span class="w-7 h-7 rounded-lg bg-amber-50 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((int) ($stats['today'] ?? 0)) }}</div>
                </div>

                <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Converted</span>
                        <span class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                    </div>
                    <div class="mt-2 text-2xl font-semibold text-slate-900 dark:text-white tabular-nums">{{ number_format((int) ($stats['converted'] ?? 0)) }}</div>
                </div>
            </div>

            <!-- Filter Bar -->
            <form method="GET" action="{{ route('app.crm.enquiries') }}" class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center gap-2.5 shadow-sm">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email, or notes..." class="w-full pl-9 pr-3.5 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-colors">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="source" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
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

                <select name="stage" onchange="this.form.submit()" class="px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
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

                <button type="submit" class="px-3.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white text-xs font-medium transition-colors cursor-pointer">
                    Filter
                </button>

                @if(request('search') || (request('source') && request('source') !== 'all') || (request('stage') && request('stage') !== 'all'))
                    <a href="{{ route('app.crm.enquiries') }}" class="px-3 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-xs font-medium transition-colors">Reset</a>
                @endif
            </form>

            <!-- Enquiries Table -->
            <div class="rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[11px] font-semibold">
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
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
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
                                <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3.5 px-4">
                                        <div class="font-semibold text-slate-900 dark:text-white">{{ $enq->name }}</div>
                                        @if($enq->assignedTo)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Owner: {{ $enq->assignedTo->name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        @if($enq->phone)
                                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $enq->phone) }}" class="text-slate-800 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 font-medium tabular-nums">{{ $enq->phone }}</a>
                                        @else
                                            <span class="text-slate-400">--</span>
                                        @endif
                                        @if($enq->email)
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate max-w-[180px]">{{ $enq->email }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
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
                                            <div class="text-slate-800 dark:text-slate-200 font-medium">{{ \Illuminate\Support\Carbon::parse($followUp)->format('d M Y') }}</div>
                                        @else
                                            <div class="text-slate-400">No follow-up</div>
                                        @endif
                                        @if($enq->membershipPlan)
                                            <div class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 mt-0.5 truncate max-w-[170px]" title="{{ $enq->membershipPlan->name }}">
                                                🎯 {{ $enq->membershipPlan->name }}
                                            </div>
                                        @endif
                                        @if((float) ($enq->estimated_value ?? 0) > 0)
                                            <div class="text-[11px] text-emerald-600 dark:text-emerald-400 font-bold mt-0.5">{{ $currency }}{{ number_format((float) $enq->estimated_value, 0) }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 max-w-[220px]">
                                        <span class="line-clamp-2">{{ $noteText !== '' ? $noteText : '—' }}</span>
                                    </td>
                                    <td class="py-3.5 px-4 text-slate-500 dark:text-slate-400 tabular-nums whitespace-nowrap">{{ $enq->created_at ? $enq->created_at->format('d M Y') : '—' }}</td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($enq->phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enq->phone) }}?text=Hello%20{{ urlencode($enq->name) }}" target="_blank" title="Message on WhatsApp" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-medium text-[11px] inline-flex items-center gap-1.5 transition-colors shadow-sm">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 2c-5.516 0-9.969 4.453-9.969 9.969 0 1.766.469 3.484 1.344 5l-1.406 5.156 5.281-1.375c1.469.797 3.109 1.219 4.75 1.219 5.516 0 9.969-4.453 9.969-9.969s-4.453-10-9.969-10zm5.813 14.125c-.25.703-1.438 1.344-2 1.422-.516.078-1.172.109-1.891-.125-.438-.141-.984-.328-1.688-.641-2.969-1.281-4.906-4.266-5.047-4.453-.156-.203-1.203-1.609-1.203-3.063s.766-2.172 1.031-2.469c.281-.297.609-.375.813-.375s.406 0 .578.016c.188.016.438-.078.688.531.25.609.859 2.094.938 2.25.078.156.125.344.031.547-.109.203-.156.328-.313.5-.156.172-.328.391-.469.531-.156.156-.328.328-.141.641.188.313.844 1.391 1.813 2.25 1.25 1.109 2.297 1.453 2.609 1.609.313.156.484.125.672-.078.188-.203.781-.906.984-1.219.203-.313.406-.266.688-.156.281.109 1.797.844 2.109 1 .313.156.516.234.594.359.078.125.078.734-.172 1.438z"/></svg>
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('app.leads.edit', $enq->id) }}" title="Edit Enquiry" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-indigo-50 dark:hover:bg-indigo-500/20 text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 border border-slate-200 dark:border-slate-700 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center">
                                        <div class="text-sm font-medium text-slate-600 dark:text-slate-300">No enquiries match your current filters</div>
                                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-1">Click &ldquo;Add Enquiry / Walk-In&rdquo; to record a new prospect.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($enquiries) && method_exists($enquiries, 'links'))
                    <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                        {{ $enquiries->links() }}
                    </div>
                @endif
            </div>

            <!-- Modal: Add Enquiry / Walk-In -->
            <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-2xl w-full max-h-[88vh] shadow-2xl flex flex-col overflow-hidden my-auto" @click.away="showAddModal = false">
                    <div class="shrink-0 flex items-center justify-between px-6 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-200/80 dark:border-indigo-500/20 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white tracking-tight">Add Enquiry / Walk-In</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Enter prospect contact details, source, and follow-up schedule.</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1.5 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer" aria-label="Close modal">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    <form action="{{ route('app.crm.enquiries.store') }}" method="POST" class="flex flex-col flex-1 min-h-0">
                        @csrf
                        <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                            <!-- Section 1: Prospect Contact -->
                            <div class="space-y-2.5">
                                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Prospect Contact</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Full Name <span class="text-rose-500">*</span></label>
                                        <input type="text" name="name" value="{{ old('name') }}" required placeholder="Rahul Sharma" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Phone Number <span class="text-rose-500">*</span></label>
                                        <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+91 9876543210" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs tabular-nums placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                                        <input type="email" name="email" value="{{ old('email') }}" placeholder="rahul@example.com" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>

                            <!-- Section 2: Source & Ownership -->
                            <div class="space-y-2.5 pt-3.5 border-t border-slate-200 dark:border-slate-800">
                                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Source &amp; Ownership</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Source</label>
                                        <select name="source" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
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
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Stage</label>
                                        <select name="stage" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
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
                                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Branch</label>
                                            <select name="branch_id" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                                <option value="">All / Primary Branch</option>
                                                @foreach($branches as $branch)
                                                    <option value="{{ $branch->id }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="{{ ($branches ?? collect())->count() > 0 ? '' : 'sm:col-span-2' }}">
                                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Assign to Staff</label>
                                        @php
                                            $cUser = auth()->user();
                                            $defEnqStaffId = old('assigned_to_user_id') ?: (($cUser && !$cUser->isGymOwner() && !$cUser->isSuperAdmin() && $cUser->isStaff()) ? $cUser->id : null);
                                        @endphp
                                        <select name="assigned_to_user_id" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
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
                            <div class="space-y-2.5 pt-3.5 border-t border-slate-200 dark:border-slate-800">
                                <h4 class="text-[11px] font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400">Follow-Up &amp; Pitched Membership</h4>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="text-xs font-medium text-slate-700 dark:text-slate-300">Next Follow-Up Date</label>
                                            <div class="flex items-center gap-1">
                                                <button type="button" @click="setQuickFollowUp(0)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-indigo-500/20 text-[10px] font-medium text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">Today</button>
                                                <button type="button" @click="setQuickFollowUp(1)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-indigo-500/20 text-[10px] font-medium text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">Tomorrow</button>
                                                <button type="button" @click="setQuickFollowUp(3)" class="px-2 py-0.5 rounded bg-slate-100 hover:bg-indigo-50 dark:bg-slate-800 dark:hover:bg-indigo-500/20 text-[10px] font-medium text-slate-600 dark:text-slate-300 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">+3 Days</button>
                                            </div>
                                        </div>
                                        <input type="date" name="follow_up_date" x-model="followUpDate" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs tabular-nums focus:border-indigo-500 focus:outline-none">
                                    </div>
                                    <div>
                                        <div class="flex items-center justify-between mb-1">
                                            <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">
                                                Pitched Membership Plan
                                            </label>
                                            <span x-show="estimatedValue && estimatedValue > 0" x-cloak class="text-[11px] font-bold text-emerald-600 dark:text-emerald-400">
                                                {{ $currency }}<span x-text="Number(estimatedValue).toLocaleString('en-IN')"></span>
                                            </span>
                                        </div>
                                        <select name="membership_plan_id" 
                                                x-model="selectedPlanId" 
                                                @change="onPlanChange($event)" 
                                                class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none cursor-pointer">
                                            <option value="" data-price="">-- Select Pitched Membership Plan --</option>
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
                            <div class="pt-3.5 border-t border-slate-200 dark:border-slate-800">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300 mb-1">Notes / Fitness Goal</label>
                                <textarea name="remarks" rows="2" placeholder="Interested in 6-month membership, weight loss, or personal training..." class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">{{ old('remarks') }}</textarea>
                            </div>
                        </div>

                        <div class="shrink-0 flex items-center justify-end gap-2.5 px-6 py-3.5 border-t border-slate-200 dark:border-slate-800 bg-slate-50/70 dark:bg-slate-950/50">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors cursor-pointer">Cancel</button>
                            <button type="submit" class="px-5 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Save Enquiry</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal: Record Conversion Amount -->
            <div x-show="showPaidModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/75 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5" @click.away="cancelPaidModal()">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-semibold text-slate-900 dark:text-white tracking-tight">Record Conversion Amount</h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">Mark enquiry as converted</p>
                            </div>
                        </div>
                        <button type="button" @click="cancelPaidModal()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white p-1 cursor-pointer" aria-label="Close">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80">
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 font-medium">Prospect Name</div>
                            <div class="text-sm font-semibold text-slate-900 dark:text-white mt-0.5" x-text="paidModalData.leadName"></div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-emerald-600 dark:text-emerald-400 mb-1.5">
                                Conversion Amount ({{ $currency }}) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 font-semibold text-slate-400 text-sm">{{ $currency }}</span>
                                <input type="number" step="0.01" min="0" x-model="paidModalData.paidAmount" placeholder="5000" autofocus class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-emerald-500/50 text-slate-900 dark:text-white font-semibold tabular-nums text-sm focus:border-emerald-500 focus:outline-none transition-colors">
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1.5">This amount will be added to your CRM won revenue and conversion metrics.</p>
                        </div>
                    </div>
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="cancelPaidModal()" class="px-4 py-2 rounded-lg bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-medium transition-colors cursor-pointer">Cancel</button>
                        <button type="button" @click="confirmPaidModal()" class="px-5 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm flex items-center gap-1.5 transition-colors cursor-pointer">
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
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Converted Members</span>
                        <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ isset($paidLeads) && method_exists($paidLeads, 'total') ? $paidLeads->total() : (is_countable($paidLeads ?? []) ? count($paidLeads ?? []) : 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 flex items-center justify-center font-bold">&#10003;</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                    <div>
                        <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Value Won</span>
                        <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $currency }}{{ number_format($totalRevenue ?? 0) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-500/20 flex items-center justify-center font-bold">{{ $currency }}</div>
                </div>
            </div>

            <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Member Name</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Acquisition Source</th>
                                <th class="py-3 px-4">Conversion Value</th>
                                <th class="py-3 px-4">Date Won</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                            @forelse($paidLeads ?? [] as $lead)
                                <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                    <td class="py-3 px-4 font-bold text-slate-900 dark:text-white">{{ $lead->name }}</td>
                                    <td class="py-3 px-4 text-slate-600 dark:text-slate-400 font-mono">{{ $lead->phone }}</td>
                                    <td class="py-3 px-4 capitalize">{{ str_replace('_', ' ', $lead->source ?? 'Direct') }}</td>
                                    <td class="py-3 px-4 font-extrabold text-amber-600 dark:text-amber-400">{{ $currency }}{{ number_format($lead->estimated_value ?? 0) }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-500 dark:text-slate-400">{{ $lead->created_at ? $lead->created_at->format('d M Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400 dark:text-slate-500">No conversions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if(isset($paidLeads) && method_exists($paidLeads, 'links'))
                    <div class="p-3 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                        {{ $paidLeads->links() }}
                    </div>
                @endif
            </div>
        @endif

        <!-- REPORTS SECTION -->
        @if(($section ?? '') === 'reports')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Leads Acquired</div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $totalLeads ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Won / Paid Conversions</div>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $paidCount ?? 0 }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                    <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Pipeline Leads</div>
                    <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $activePipeline ?? 0 }}</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

