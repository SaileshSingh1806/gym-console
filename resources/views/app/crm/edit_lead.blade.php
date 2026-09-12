<x-app-layout header="Edit Lead">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $initialStage = old('stage', $lead->stage ?: ($lead->status ?: 'NEW_LEAD'));
        if (in_array(strtoupper($initialStage), ['CONVERTED', 'PAID'])) {
            $initialStage = 'PAID';
        }
    @endphp

    <div class="max-w-7xl mx-auto space-y-6" x-data="{
        stage: '{{ $initialStage }}',
        paidAmount: '{{ old('estimated_value', (float)($lead->estimated_value ?? 0) > 0 ? (float)$lead->estimated_value : '') }}'
    }">

        <!-- ==================== TOP ACTION HEADER ==================== -->
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-indigo-500/15 text-indigo-400 border border-indigo-500/20 flex items-center justify-center shadow-inner">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-black text-white tracking-tight flex items-center gap-2">
                        <span>Edit Lead: {{ $lead->name }}</span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">CRM Leads Pipeline</p>
                </div>
            </div>

            <a href="{{ route('app.leads.index') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white text-xs font-bold border border-slate-800 flex items-center gap-2 transition-all shadow-sm">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                <span>Back to List</span>
            </a>
        </div>

        <!-- ==================== ERROR ALERTS ==================== -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-400">
                <div class="flex items-center gap-2 font-bold text-xs mb-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Please correct the following errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs space-y-0.5 ml-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ==================== MAIN FORM CARD ==================== -->
        <div class="rounded-2xl bg-slate-900 border border-slate-800 shadow-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-800/80 bg-slate-950/40">
                <h2 class="text-sm font-bold text-white tracking-wide">Edit Lead</h2>
            </div>

            <form action="{{ route('app.leads.update', $lead->id) }}" method="POST" class="p-6 sm:p-8 space-y-8">
                @csrf

                <!-- SECTION 1: Contact Information -->
                <div class="space-y-4">
                    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Contact Information</h3>

                    <!-- Row 1: Name, Phone, Email -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">
                                Name <span class="text-rose-400">*</span>
                            </label>
                            <input type="text" name="name" required value="{{ old('name', $lead->name) }}" placeholder="" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Phone</label>
                            <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}" placeholder="" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Email</label>
                            <input type="email" name="email" value="{{ old('email', $lead->email) }}" placeholder="" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>
                    </div>

                    <!-- Row 2: Instagram Handle, City, Member Count -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Instagram Handle</label>
                            <input type="text" name="instagram_handle" value="{{ old('instagram_handle', $lead->instagram_handle) }}" placeholder="@username" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">City</label>
                            <input type="text" name="city" value="{{ old('city', $lead->city) }}" placeholder="" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Member Count</label>
                            <input type="number" min="1" name="member_count" value="{{ old('member_count', $lead->member_count ?? 1) }}" placeholder="1" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: Lead Details -->
                <div class="space-y-4 pt-4 border-t border-slate-800/80">
                    <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Lead Details</h3>

                    <!-- Row 1: Source, Stage, Assigned To -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Source</label>
                            @php
                                $selectedSource = old('source', $lead->source);
                            @endphp
                            <select name="source" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-all">
                                <option value="">Select Source</option>
                                <option value="website" {{ $selectedSource == 'website' ? 'selected' : '' }}>Website</option>
                                <option value="walk_in" {{ $selectedSource == 'walk_in' ? 'selected' : '' }}>Walk In</option>
                                <option value="facebook" {{ $selectedSource == 'facebook' ? 'selected' : '' }}>Facebook</option>
                                <option value="instagram" {{ $selectedSource == 'instagram' ? 'selected' : '' }}>Instagram</option>
                                <option value="google" {{ $selectedSource == 'google' ? 'selected' : '' }}>Google Maps</option>
                                <option value="referral" {{ $selectedSource == 'referral' ? 'selected' : '' }}>Referral</option>
                                <option value="whatsapp" {{ $selectedSource == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                                <option value="phone" {{ $selectedSource == 'phone' ? 'selected' : '' }}>Phone Call</option>
                                <option value="advertising" {{ $selectedSource == 'advertising' ? 'selected' : '' }}>Advertising</option>
                                <option value="other" {{ $selectedSource == 'other' ? 'selected' : '' }}>Other</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Stage</label>
                            <select name="stage" x-model="stage" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-all">
                                <option value="NEW_LEAD">Default Stage (New Lead)</option>
                                <option value="CONTACTED">Contacted</option>
                                <option value="DEMO_BOOKED">Demo Booked</option>
                                <option value="PROPOSAL_SENT">Proposal Sent</option>
                                <option value="NEGOTIATION">Negotiation</option>
                                <option value="TRIAL">Trial</option>
                                <option value="PAID">Paid</option>
                                <option value="LOST">Lost</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Assigned To</label>
                            @php
                                $selectedStaff = old('assigned_to_user_id', $lead->assigned_to_user_id);
                            @endphp
                            <select name="assigned_to_user_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-all">
                                <option value="">Unassigned</option>
                                @foreach($staffMembers as $staff)
                                    <option value="{{ $staff->id }}" {{ $selectedStaff == $staff->id ? 'selected' : '' }}>
                                        {{ $staff->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- DYNAMIC PAID AMOUNT BOX (shown when stage is PAID) -->
                    <div x-show="stage === 'PAID'" x-transition class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-2">
                        <div class="flex items-center gap-2 text-emerald-400 font-bold text-xs">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>Conversion Amount ({{ $currency }}) <span class="text-rose-400">*</span></span>
                        </div>
                        <div class="relative">
                            <span class="absolute left-3.5 top-2.5 font-bold text-slate-400 text-sm">{{ $currency }}</span>
                            <input type="number" step="0.01" min="0" name="estimated_value" x-model="paidAmount" placeholder="e.g. 5000" class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-950 border border-emerald-500/50 text-white font-bold text-sm focus:border-emerald-400 focus:outline-none transition-all">
                        </div>
                        <p class="text-[11px] text-slate-400">This conversion amount will be added to your CRM Won Revenue & Conversion Value metrics.</p>
                    </div>

                    <!-- Hidden input to preserve estimated_value if not in PAID stage -->
                    <template x-if="stage !== 'PAID'">
                        <input type="hidden" name="estimated_value" :value="paidAmount">
                    </template>

                    <!-- Row 2: Next Action, Next Action Date, Branch -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Next Action</label>
                            <input type="text" name="next_action" value="{{ old('next_action', $lead->next_action) }}" placeholder="e.g. Follow up call" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Next Action Date</label>
                            <input type="date" name="follow_up_date" value="{{ old('follow_up_date', $lead->follow_up_date ? $lead->follow_up_date->format('Y-m-d') : '') }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-300 mb-1.5">Branch</label>
                            @php
                                $selectedBranch = old('branch_id', $lead->branch_id);
                            @endphp
                            <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none transition-all">
                                <option value="">No Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Row 3: Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-300 mb-1.5">Notes</label>
                        <textarea name="notes" rows="4" placeholder="" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all resize-y">{{ old('notes', $lead->notes) }}</textarea>
                    </div>
                </div>

                <!-- ==================== FORM ACTIONS ==================== -->
                <div class="pt-6 border-t border-slate-800/80 flex items-center justify-end gap-4">
                    <a href="{{ route('app.leads.index') }}" class="px-4 py-2 text-xs font-semibold text-slate-400 hover:text-white transition-all">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold shadow-lg shadow-emerald-600/25 flex items-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span>Save Changes</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
