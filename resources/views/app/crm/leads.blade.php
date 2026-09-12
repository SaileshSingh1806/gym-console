<x-app-layout header="CRM Leads">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
    @endphp

    <div class="space-y-6" x-data="{
        showAddLeadModal: false,
        showEditLeadModal: false,
        showImportModal: false,
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
        },
        editLeadData: {
            id: '',
            name: '',
            phone: '',
            email: '',
            source: 'website',
            stage: 'NEW_LEAD',
            assigned_to_user_id: '',
            follow_up_date: '',
            remarks: '',
            next_action: '',
            estimated_value: 0,
            notes: ''
        },
        openEditModal(lead) {
            this.editLeadData = {
                id: lead.id,
                name: lead.name || '',
                phone: lead.phone || '',
                email: lead.email || '',
                source: lead.source || 'website',
                stage: lead.stage || lead.status || 'NEW_LEAD',
                assigned_to_user_id: lead.assigned_to_user_id || '',
                follow_up_date: lead.follow_up_date ? lead.follow_up_date.substring(0, 10) : '',
                remarks: lead.remarks || '',
                next_action: lead.next_action || '',
                estimated_value: lead.estimated_value || 0,
                notes: lead.notes || ''
            };
            this.showEditLeadModal = true;
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

        <!-- ==================== TOP ACTION HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h2 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>CRM Leads</span>
                    </h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ $totalLeadsCount }} total leads registered in pipeline</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <button type="button" @click="showImportModal = true" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span>Import</span>
                </button>

                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv']) }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-3.5 h-3.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    <span>Export All</span>
                </a>

                <a href="{{ route('app.leads.create') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/25 flex items-center gap-1.5 transition-all">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Lead</span>
                </a>
            </div>
        </div>

        <!-- ==================== SEARCH & MULTI-FILTER TOOLBAR ==================== -->
        <form method="GET" action="{{ route('app.leads.index') }}" class="p-3.5 rounded-2xl bg-slate-900 border border-slate-800 flex flex-wrap items-center gap-2.5">
            <!-- Search -->
            <div class="relative flex-1 min-w-[220px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, phone, email..." class="w-full pl-9 pr-3.5 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                <svg class="w-4 h-4 text-slate-500 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>

            <!-- Stages Filter -->
            <select name="stage" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
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

            <!-- Sources Filter -->
            <select name="source" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                <option value="all" {{ request('source') === 'all' || !request('source') ? 'selected' : '' }}>All Sources</option>
                <option value="website" {{ request('source') === 'website' ? 'selected' : '' }}>Website</option>
                <option value="walk_in" {{ request('source') === 'walk_in' ? 'selected' : '' }}>Walk In</option>
                <option value="facebook" {{ request('source') === 'facebook' ? 'selected' : '' }}>Facebook</option>
                <option value="instagram" {{ request('source') === 'instagram' ? 'selected' : '' }}>Instagram</option>
                <option value="google" {{ request('source') === 'google' ? 'selected' : '' }}>Google</option>
                <option value="referral" {{ request('source') === 'referral' ? 'selected' : '' }}>Referral</option>
                <option value="expired_list" {{ request('source') === 'expired_list' ? 'selected' : '' }}>Expired List</option>
                <option value="whatsapp" {{ request('source') === 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                <option value="phone" {{ request('source') === 'phone' ? 'selected' : '' }}>Phone</option>
                <option value="other" {{ request('source') === 'other' ? 'selected' : '' }}>Other</option>
            </select>

            <!-- Staff Filter -->
            <select name="staff_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                <option value="all" {{ request('staff_id') === 'all' || !request('staff_id') ? 'selected' : '' }}>All Staff</option>
                @foreach($staffMembers as $stf)
                    <option value="{{ $stf->id }}" {{ request('staff_id') == $stf->id ? 'selected' : '' }}>{{ $stf->name }}</option>
                @endforeach
            </select>

            <!-- Branches Filter -->
            <select name="branch_id" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                <option value="all">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </select>

            <!-- Remarks Filter -->
            <select name="remarks_filter" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl bg-slate-950 border border-slate-800 text-slate-300 text-xs focus:border-indigo-500 focus:outline-none">
                <option value="all" {{ request('remarks_filter') === 'all' || !request('remarks_filter') ? 'selected' : '' }}>All Remarks</option>
                <option value="with_remarks" {{ request('remarks_filter') === 'with_remarks' ? 'selected' : '' }}>With Remarks</option>
                <option value="no_remarks" {{ request('remarks_filter') === 'no_remarks' ? 'selected' : '' }}>No Remarks</option>
            </select>

            @if(request('search') || request('stage') || request('source') || request('staff_id') || request('remarks_filter'))
                <a href="{{ route('app.leads.index') }}" class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white text-xs font-bold transition-all">
                    Reset
                </a>
            @endif
        </form>

        <!-- ==================== LEADS DATA TABLE ==================== -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 overflow-hidden shadow-md">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-950/70 border-b border-slate-800 text-slate-400 uppercase tracking-wider text-[10px] font-bold">
                        <tr>
                            <th class="py-3 px-3.5 w-8">
                                <input type="checkbox" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-0">
                            </th>
                            <th class="py-3 px-3.5">Name</th>
                            <th class="py-3 px-3.5">Stage</th>
                            <th class="py-3 px-3.5">Conversion Amount</th>
                            <th class="py-3 px-3.5">Source</th>
                            <th class="py-3 px-3.5">Assigned To</th>
                            <th class="py-3 px-3.5">Next Action</th>
                            <th class="py-3 px-3.5">Created</th>
                            <th class="py-3 px-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800/60 text-slate-300">
                        @forelse($leads as $lead)
                            @php
                                $st = strtoupper(str_replace(' ', '_', $lead->effective_stage));
                                $stageStyle = match($st) {
                                    'PAID', 'CONVERTED' => 'bg-emerald-500/15 text-emerald-400 border-emerald-500/30',
                                    'TRIAL', 'TRIAL_SCHEDULED' => 'bg-purple-500/15 text-purple-400 border-purple-500/30',
                                    'CONTACTED' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                                    'DEMO_BOOKED' => 'bg-amber-500/15 text-amber-400 border-amber-500/30',
                                    'NEGOTIATION' => 'bg-pink-500/15 text-pink-400 border-pink-500/30',
                                    'PROPOSAL_SENT' => 'bg-teal-500/15 text-teal-400 border-teal-500/30',
                                    'LOST' => 'bg-slate-800 text-slate-400 border-slate-700',
                                    default => 'bg-cyan-500/15 text-cyan-400 border-cyan-500/30',
                                };
                            @endphp
                            <tr class="hover:bg-slate-800/40 transition-colors">
                                <td class="py-3 px-3.5">
                                    <input type="checkbox" class="rounded bg-slate-900 border-slate-700 text-indigo-600 focus:ring-0">
                                </td>

                                <!-- Name & Phone & Remarks -->
                                <td class="py-3 px-3.5">
                                    <div class="font-bold text-white flex items-center gap-2">
                                        <span>{{ $lead->name }}</span>
                                        <span class="text-slate-400 font-normal font-mono text-[11px]">{{ $lead->phone }}</span>
                                    </div>
                                    @if($lead->remarks)
                                        <div class="text-[10.5px] text-slate-400 mt-0.5">{{ $lead->remarks }}</div>
                                    @endif
                                </td>

                                <!-- Stage Badge -->
                                <td class="py-3 px-3.5">
                                    <form action="{{ route('app.leads.stage', $lead->id) }}" method="POST" class="inline-block">
                                        @csrf
                                        <select name="stage" @change="handleStageChange($event, {{ $lead->id }}, '{{ addslashes($lead->name) }}', '{{ $st }}', {{ (float) ($lead->estimated_value ?? 0) }})" class="px-2 py-0.5 rounded-lg text-[10px] font-extrabold border cursor-pointer focus:outline-none {{ $stageStyle }}">
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

                                <!-- Conversion Amount -->
                                <td class="py-3 px-3.5 font-mono">
                                    @if($lead->estimated_value > 0)
                                        <span class="text-xs font-bold text-emerald-400 bg-emerald-500/10 px-2 py-0.5 rounded-lg border border-emerald-500/20">
                                            {{ $currency }}{{ number_format((float) $lead->estimated_value) }}
                                        </span>
                                    @else
                                        <span class="text-slate-600">--</span>
                                    @endif
                                </td>

                                <!-- Source -->
                                <td class="py-3 px-3.5">
                                    <span class="text-slate-300 font-medium capitalize">{{ str_replace('_', ' ', $lead->source ?? 'walk in') }}</span>
                                </td>

                                <!-- Assigned To -->
                                <td class="py-3 px-3.5">
                                    @if($lead->assignedTo)
                                        <span class="text-slate-200 font-medium">{{ $lead->assignedTo->name }}</span>
                                    @else
                                        <span class="text-slate-600 font-mono">--</span>
                                    @endif
                                </td>

                                <!-- Next Action -->
                                <td class="py-3 px-3.5">
                                    @if($lead->next_action || $lead->follow_up_date)
                                        <span class="text-slate-300">{{ $lead->next_action ?? $lead->follow_up_date->format('d M') }}</span>
                                    @else
                                        <span class="text-slate-600 font-mono">--</span>
                                    @endif
                                </td>

                                <!-- Created -->
                                <td class="py-3 px-3.5 text-slate-400 font-mono text-[11px]">
                                    {{ $lead->created_at ? $lead->created_at->format('d M y') : '--' }}
                                </td>

                                <!-- Actions -->
                                <td class="py-3 px-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- WhatsApp -->
                                        @if($lead->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $lead->phone) }}?text=Hello%20{{ urlencode($lead->name) }},%20greeting%20from%20{{ urlencode($tenant->name ?? 'Gym Console') }}" target="_blank" title="Send WhatsApp Message" class="p-1.5 rounded-lg bg-slate-950 hover:bg-emerald-500/20 text-slate-400 hover:text-emerald-400 border border-slate-800 transition-all">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.182-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766.001-3.187-2.575-5.771-5.764-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.299.045-.677.063-1.092-.069-.252-.08-.575-.187-.988-.365-1.739-.751-2.874-2.502-2.961-2.617-.087-.116-.708-.94-.708-1.793s.448-1.273.607-1.446c.159-.173.346-.217.462-.217l.332.007c.106.005.249-.04.39.298.144.347.491 1.2.534 1.287.043.087.072.188.014.304-.058.116-.087.188-.173.289l-.26.304c-.087.086-.177.18-.076.354.101.174.449.741.964 1.201.662.591 1.221.774 1.394.86s.275.072.376-.044c.101-.116.433-.506.549-.68.116-.173.231-.145.39-.087s1.011.477 1.184.564.289.13.332.202c.045.072.045.419-.099.824z"/></svg>
                                            </a>
                                        @endif

                                        <!-- Edit -->
                                        <a href="{{ route('app.leads.edit', $lead->id) }}" title="Edit Lead" class="p-1.5 rounded-lg bg-slate-950 hover:bg-indigo-500/20 text-slate-400 hover:text-indigo-400 border border-slate-800 transition-all">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </a>

                                        <!-- Delete -->
                                        <form action="{{ route('app.leads.delete', $lead->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete lead {{ addslashes($lead->name) }}?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Lead" class="p-1.5 rounded-lg bg-slate-950 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 border border-slate-800 transition-all cursor-pointer">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="py-10 text-center text-slate-500 text-xs">
                                    No prospect leads found matching the filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-3.5 border-t border-slate-800 bg-slate-950/40">
                {{ $leads->links() }}
            </div>
        </div>

        <!-- ==================== MODAL: ADD LEAD ==================== -->
        <div x-show="showAddLeadModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showAddLeadModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white tracking-tight flex items-center gap-2">
                        <span>+ Add New Lead</span>
                    </h3>
                    <button type="button" @click="showAddLeadModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <form action="{{ route('app.leads.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="name" required placeholder="e.g. Rahul Sharma" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required placeholder="9876543210" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Lead Stage</label>
                            <select name="stage" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="NEW_LEAD">New Lead</option>
                                <option value="CONTACTED">Contacted</option>
                                <option value="DEMO_BOOKED">Demo Booked</option>
                                <option value="PROPOSAL_SENT">Proposal Sent</option>
                                <option value="NEGOTIATION">Negotiation</option>
                                <option value="TRIAL">Trial</option>
                                <option value="PAID">Paid / Converted</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Lead Source</label>
                            <select name="source" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="website">Website</option>
                                <option value="walk_in">Walk In</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="google">Google</option>
                                <option value="referral">Referral</option>
                                <option value="expired_list">Expired List</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="phone">Phone Call</option>
                                <option value="advertising">Advertising</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Assigned Staff</label>
                            <select name="assigned_to_user_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Unassigned --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Conversion Amount ({{ $currency }})</label>
                            <input type="number" step="0.01" name="estimated_value" placeholder="e.g. 5000" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Remarks / Note</label>
                        <input type="text" name="remarks" placeholder="e.g. Call later, Interested in annual PT" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="showAddLeadModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25">Create Lead</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: EDIT LEAD ==================== -->
        <div x-show="showEditLeadModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showEditLeadModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white tracking-tight flex items-center gap-2">
                        <span>✏️ Edit Lead</span>
                    </h3>
                    <button type="button" @click="showEditLeadModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <form :action="'/app/leads/' + editLeadData.id" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Full Name *</label>
                        <input type="text" name="name" required x-model="editLeadData.name" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required x-model="editLeadData.phone" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" x-model="editLeadData.email" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Lead Stage</label>
                            <select name="stage" x-model="editLeadData.stage" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="NEW_LEAD">New Lead</option>
                                <option value="CONTACTED">Contacted</option>
                                <option value="DEMO_BOOKED">Demo Booked</option>
                                <option value="PROPOSAL_SENT">Proposal Sent</option>
                                <option value="NEGOTIATION">Negotiation</option>
                                <option value="TRIAL">Trial</option>
                                <option value="PAID">Paid / Converted</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Lead Source</label>
                            <select name="source" x-model="editLeadData.source" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="website">Website</option>
                                <option value="walk_in">Walk In</option>
                                <option value="facebook">Facebook</option>
                                <option value="instagram">Instagram</option>
                                <option value="google">Google</option>
                                <option value="referral">Referral</option>
                                <option value="expired_list">Expired List</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="phone">Phone Call</option>
                                <option value="advertising">Advertising</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Assigned Staff</label>
                            <select name="assigned_to_user_id" x-model="editLeadData.assigned_to_user_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Unassigned --</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-300 mb-1">Conversion Amount ({{ $currency }})</label>
                            <input type="number" step="0.01" name="estimated_value" x-model="editLeadData.estimated_value" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-300 mb-1">Remarks</label>
                        <input type="text" name="remarks" x-model="editLeadData.remarks" class="w-full px-3.5 py-2 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                        <button type="button" @click="showEditLeadModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: IMPORT LEADS ==================== -->
        <div x-show="showImportModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showImportModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <h3 class="text-base font-black text-white tracking-tight flex items-center gap-2">
                        <span>📤 Import Leads (CSV)</span>
                    </h3>
                    <button type="button" @click="showImportModal = false" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <div class="space-y-3 text-xs text-slate-300">
                    <p>Upload a standard CSV file with headers: <code class="text-indigo-400 font-mono">name, phone, email, source, remarks</code></p>
                    <div class="p-4 rounded-2xl border-2 border-dashed border-slate-700 bg-slate-950 text-center space-y-2">
                        <svg class="w-8 h-8 text-slate-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                        <input type="file" accept=".csv" class="text-xs text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white cursor-pointer">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-800">
                    <button type="button" @click="showImportModal = false" class="px-4 py-2 rounded-xl bg-slate-800 text-slate-300 hover:text-white text-xs font-bold">Close</button>
                </div>
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
                            <p class="text-[11px] text-slate-400">Mark lead as converted & won</p>
                        </div>
                    </div>
                    <button type="button" @click="cancelPaidModal()" class="text-slate-400 hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <div class="space-y-4">
                    <div class="p-3.5 rounded-xl bg-slate-950 border border-slate-800/80">
                        <div class="text-[11px] text-slate-400 font-medium">Lead Name</div>
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
                        <p class="text-[10.5px] text-slate-400 mt-1.5">This conversion amount will be added to your CRM Won Revenue & Conversion metrics.</p>
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
</x-app-layout>

