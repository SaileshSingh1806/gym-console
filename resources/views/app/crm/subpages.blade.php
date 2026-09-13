<x-app-layout header="{{ $title ?? 'CRM' }}">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6" x-data="{
        showAddModal: false,
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

        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        <!-- ==================== HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>{{ $title }}</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $subtitle }}</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                @if($section === 'enquiries')
                    <button type="button" @click="showAddModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Enquiry / Walk-In</span>
                    </button>
                @endif

                <a href="{{ route('app.crm.dashboard') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>CRM Dashboard</span>
                </a>
            </div>
        </div>

        <!-- ==================== CONTENT SECTION: ENQUIRIES ==================== -->
        @if($section === 'enquiries')
            <!-- Search & Filter Bar -->
            <form method="GET" action="{{ route('app.crm.enquiries') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-wrap items-center gap-2.5">
                <div class="relative flex-1 min-w-[220px]">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search enquiry by name, phone, notes..." class="w-full pl-9 pr-3.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                    <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>

                <select name="source" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                    <option value="all" {{ request('source') === 'all' || !request('source') ? 'selected' : '' }}>All Channels</option>
                    <option value="walk_in" {{ request('source') === 'walk_in' ? 'selected' : '' }}>Direct Walk-In</option>
                    <option value="phone" {{ request('source') === 'phone' ? 'selected' : '' }}>Phone Call</option>
                    <option value="whatsapp" {{ request('source') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="website" {{ request('source') === 'website' ? 'selected' : '' }}>Website Form</option>
                    <option value="instagram" {{ request('source') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                    <option value="facebook" {{ request('source') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                    <option value="google" {{ request('source') === 'google' ? 'selected' : '' }}>Google Search/Maps</option>
                    <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                    <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Other</option>
                </select>

                @if(request('search') || (request('source') && request('source') !== 'all'))
                    <a href="{{ route('app.crm.enquiries') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-bold transition-all">
                        Reset
                    </a>
                @endif
            </form>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Prospect Name</th>
                                <th class="py-3 px-4">Contact</th>
                                <th class="py-3 px-4">Channel / Source</th>
                                <th class="py-3 px-4">Pipeline Stage</th>
                                <th class="py-3 px-4">Remarks / Needs</th>
                                <th class="py-3 px-4">Received On</th>
                                <th class="py-3 px-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($enquiries as $enq)
                                @php
                                    $st = strtoupper($enq->effective_stage);
                                    $stageStyle = match($st) {
                                        'PAID', 'CONVERTED' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20',
                                        'TRIAL', 'TRIAL_SCHEDULED' => 'bg-cyan-500/10 text-cyan-400 border-cyan-500/20',
                                        'DEMO_BOOKED' => 'bg-purple-500/10 text-purple-400 border-purple-500/20',
                                        'PROPOSAL_SENT', 'NEGOTIATION' => 'bg-amber-500/10 text-amber-400 border-amber-500/20',
                                        'CONTACTED' => 'bg-blue-500/10 text-blue-400 border-blue-500/20',
                                        'LOST' => 'bg-rose-500/10 text-rose-400 border-rose-500/20',
                                        default => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/20',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-bold text-white">
                                        <div>{{ $enq->name }}</div>
                                        @if($enq->assignedTo)
                                            <div class="text-[10px] text-slate-400 font-normal">Assigned: {{ $enq->assignedTo->name }}</div>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="text-slate-200 font-mono">{{ $enq->phone ?: '--' }}</div>
                                        <div class="text-[10px] text-slate-500">{{ $enq->email }}</div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 uppercase">
                                            {{ str_replace('_', ' ', $enq->source ?: 'walk_in') }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="{{ route('app.leads.stage', $enq->id) }}" method="POST" class="inline-block">
                                            @csrf
                                            <select name="stage" @change="handleStageChange($event, {{ $enq->id }}, '{{ addslashes($enq->name) }}', '{{ $st }}', {{ (float) ($enq->estimated_value ?? 0) }})" class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold border cursor-pointer focus:outline-none {{ $stageStyle }}">
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
                                    <td class="py-3 px-4 text-slate-400">{{ $enq->remarks ?: ($enq->notes ?: 'General Enquiry') }}</td>
                                    <td class="py-3 px-4 text-slate-400 font-mono">{{ $enq->created_at ? $enq->created_at->format('d M Y') : '' }}</td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($enq->phone)
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $enq->phone) }}?text=Hello%20{{ urlencode($enq->name) }},%20greeting%20from%20{{ urlencode($tenant->name ?? 'Gym Console') }}" target="_blank" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] inline-flex items-center gap-1 transition-all shadow-sm">
                                                    <span>WhatsApp</span>
                                                </a>
                                            @endif
                                            <a href="{{ route('app.leads.edit', $enq->id) }}" title="Edit Lead" class="p-1 rounded-lg bg-slate-950 hover:bg-indigo-500/20 text-slate-400 hover:text-indigo-400 border border-slate-800 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-8 text-center text-slate-500">No incoming prospect enquiries found. Click "+ Add Enquiry / Walk-In" to record one.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                    {{ $enquiries->links() }}
                </div>
            </div>

            <!-- ==================== MODAL: ADD DIRECT ENQUIRY / WALK-IN ==================== -->
            <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-xl w-full p-6 shadow-2xl space-y-5" @click.away="showAddModal = false">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold">
                                📝
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-white tracking-tight">Record Direct Enquiry / Walk-In</h3>
                                <p class="text-[11px] text-slate-400">Add an incoming prospect directly into CRM</p>
                            </div>
                        </div>
                        <button type="button" @click="showAddModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                    </div>

                    <form action="{{ route('app.crm.enquiries.store') }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                            <!-- Prospect Name -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Prospect Name <span class="text-rose-400">*</span></label>
                                <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <!-- Phone -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Contact Phone <span class="text-rose-400">*</span></label>
                                <input type="text" name="phone" required placeholder="e.g. +91 9876543210" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs font-mono focus:border-indigo-500 focus:outline-none">
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Email Address</label>
                                <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <!-- Channel / Source -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Inquiry Channel / Source</label>
                                <select name="source" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="walk_in" selected>Direct Walk-In</option>
                                    <option value="phone">Phone Call</option>
                                    <option value="whatsapp">WhatsApp Message</option>
                                    <option value="website">Website Form</option>
                                    <option value="instagram">Instagram DM</option>
                                    <option value="facebook">Facebook Ad / Page</option>
                                    <option value="google">Google Maps / Search</option>
                                    <option value="referral">Member Referral</option>
                                    <option value="other">Other / Event</option>
                                </select>
                            </div>

                            <!-- Initial Stage -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Initial Pipeline Stage</label>
                                <select name="stage" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="NEW_LEAD" selected>New Lead</option>
                                    <option value="CONTACTED">Contacted</option>
                                    <option value="DEMO_BOOKED">Demo Booked</option>
                                    <option value="PROPOSAL_SENT">Proposal Sent</option>
                                    <option value="NEGOTIATION">Negotiation</option>
                                    <option value="TRIAL">Trial / Demo</option>
                                    <option value="PAID">Paid / Converted</option>
                                </select>
                            </div>

                            <!-- Assigned Staff -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Assign to Staff</label>
                                <select name="assigned_to_user_id" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="">Unassigned</option>
                                    @foreach($staffMembers ?? [] as $stf)
                                        <option value="{{ $stf->id }}">{{ $stf->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Follow-up Date -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Next Follow-up Date</label>
                                <input type="date" name="follow_up_date" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>

                            <!-- Estimated Value -->
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Estimated Value ({{ $currency }})</label>
                                <input type="number" step="0.01" min="0" name="estimated_value" placeholder="e.g. 3500" class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Remarks / Notes -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-300 mb-1">Inquiry Remarks / Member Goals</label>
                            <textarea name="remarks" rows="2" placeholder="e.g. Interested in 6-month weight loss membership + personal training..." class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                            <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold transition-all">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Save Enquiry</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ==================== MODAL: RECORD CONVERSION AMOUNT ==================== -->
            <div x-show="showPaidModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
                <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-5" @click.away="cancelPaidModal()">
                    <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                        <div class="flex items-center gap-2.5">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-white tracking-tight">Record Conversion Amount</h3>
                                <p class="text-[11px] text-slate-400">Mark prospect as converted & won</p>
                            </div>
                        </div>
                        <button type="button" @click="cancelPaidModal()" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                    </div>

                    <div class="space-y-4">
                        <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80">
                            <div class="text-[11px] text-slate-400 font-medium">Prospect Name</div>
                            <div class="text-sm font-bold text-white mt-0.5" x-text="paidModalData.leadName"></div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-emerald-400 mb-1.5">
                                Conversion Amount ({{ $currency }}) <span class="text-rose-400">*</span>
                            </label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-2.5 font-bold text-slate-400 text-sm">{{ $currency }}</span>
                                <input type="number" step="0.01" min="0" x-model="paidModalData.paidAmount" placeholder="e.g. 5000" autofocus class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-950 border border-emerald-500/50 text-white font-bold text-sm focus:border-emerald-400 focus:outline-none transition-all">
                            </div>
                            <p class="text-[10.5px] text-slate-400 mt-1.5">This conversion value will be added to your CRM Won Revenue & Conversion metrics.</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="cancelPaidModal()" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold transition-all">
                            Cancel
                        </button>
                        <button type="button" @click="confirmPaidModal()" class="px-5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/25 flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Conversion Amount</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        <!-- ==================== CONTENT SECTION: CONVERSIONS ==================== -->
        @if($section === 'conversions')
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Converted Members</span>
                        <div class="text-2xl font-black text-emerald-400 mt-1">{{ $paidLeads->total() }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center font-bold">
                        ✓
                    </div>
                </div>

                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800 flex items-center justify-between">
                    <div>
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Value Won</span>
                        <div class="text-2xl font-black text-amber-400 mt-1">{{ $currency }}{{ number_format($totalRevenue) }}</div>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/20 flex items-center justify-center font-bold">
                        {{ $currency }}
                    </div>
                </div>
            </div>

            <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                            <tr>
                                <th class="py-3 px-4">Member Name</th>
                                <th class="py-3 px-4">Phone</th>
                                <th class="py-3 px-4">Acquisition Source</th>
                                <th class="py-3 px-4">Conversion Value</th>
                                <th class="py-3 px-4">Date Won</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 text-slate-300">
                            @forelse($paidLeads as $lead)
                                <tr class="hover:bg-slate-800/30">
                                    <td class="py-3 px-4 font-bold text-white">{{ $lead->name }}</td>
                                    <td class="py-3 px-4 text-slate-400 font-mono">{{ $lead->phone }}</td>
                                    <td class="py-3 px-4 capitalize">{{ str_replace('_', ' ', $lead->source) }}</td>
                                    <td class="py-3 px-4 font-extrabold text-amber-400">{{ $currency }}{{ number_format($lead->estimated_value ?? 0) }}</td>
                                    <td class="py-3 px-4 font-mono text-slate-400">{{ $lead->created_at ? $lead->created_at->format('d M Y') : '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-500">No conversions recorded yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <!-- ==================== CONTENT SECTION: REPORTS ==================== -->
        @if($section === 'reports')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total Leads Acquired</div>
                    <div class="text-2xl font-black text-white mt-1">{{ $totalLeads }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Won / Paid Conversions</div>
                    <div class="text-2xl font-black text-emerald-400 mt-1">{{ $paidCount }}</div>
                </div>
                <div class="p-4 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Active Pipeline Leads</div>
                    <div class="text-2xl font-black text-indigo-400 mt-1">{{ $activePipeline }}</div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>

