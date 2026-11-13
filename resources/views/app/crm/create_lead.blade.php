<x-app-layout header="Add New Lead">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $plans = $membershipPlans ?? collect();
    @endphp

    <div class="space-y-6 pb-24 max-w-6xl mx-auto" x-data="{
        stage: '{{ old('stage', 'NEW_LEAD') }}',
        estimatedValue: '{{ old('estimated_value', '') }}',
        remarks: '{{ old('remarks', '') }}',
        followUpDate: '{{ old('follow_up_date', '') }}',
        hasTrial: {{ old('trial_date') ? 'true' : 'false' }},
        selectedPlanId: '{{ old('membership_plan_id', '') }}',
        plans: {{ Js::from($plans) }},

        selectPlan(plan) {
            this.selectedPlanId = plan.id;
            this.estimatedValue = plan.price || 0;
            if (!this.remarks) {
                this.remarks = 'Interested in ' + plan.name;
            }
        },

        onPlanSelectChange(e) {
            const planId = e.target.value;
            this.selectedPlanId = planId;
            const plan = this.plans.find(p => p.id == planId);
            if (plan) {
                this.estimatedValue = plan.price || 0;
                if (!this.remarks) {
                    this.remarks = 'Interested in ' + plan.name;
                }
            }
        },

        setQuickDate(days) {
            const d = new Date();
            d.setDate(d.getDate() + days);
            const yyyy = d.getFullYear();
            const mm = String(d.getMonth() + 1).padStart(2, '0');
            const dd = String(d.getDate()).padStart(2, '0');
            this.followUpDate = `${yyyy}-${mm}-${dd}`;
        }
    }">

        <!-- ==================== TOP BREADCRUMB & HEADER ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('app.leads.index') }}" 
                   class="p-2 sm:p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 transition-all shadow-sm shrink-0"
                   title="Back to Leads">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 dark:text-slate-400 mb-0.5 truncate">
                        <a href="{{ route('app.crm.index') }}" class="hover:text-slate-900 dark:hover:text-slate-200 transition-colors">CRM &amp; Sales</a>
                        <span>/</span>
                        <a href="{{ route('app.leads.index') }}" class="hover:text-slate-900 dark:hover:text-slate-200 transition-colors">Leads</a>
                        <span>/</span>
                        <span class="text-indigo-600 dark:text-indigo-400">Add Lead</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">Add New Lead</h1>
                </div>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('app.leads.index') }}" 
                   class="self-start sm:self-auto px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold transition-all shadow-sm">
                    Cancel
                </a>
                <button type="submit" form="leadCreateForm"
                        class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/25 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    <span>Save Lead</span>
                </button>
            </div>
        </div>

        <!-- ==================== ERROR ALERTS ==================== -->
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs space-y-1 shadow-sm">
                <p class="font-bold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Please correct the following errors:</span>
                </p>
                <ul class="list-disc list-inside text-rose-600 dark:text-rose-400/90 pl-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="leadCreateForm" action="{{ route('app.leads.store') }}" method="POST" class="space-y-6">
            @csrf

            <!-- ========================================================================= -->
            <!-- CARD 1: CONTACT INFORMATION (ADD MEMBER STYLE) -->
            <!-- ========================================================================= -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Contact &amp; Personal Information</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Prospect name, contact numbers, and location details</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- First Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="first_name" 
                               value="{{ old('first_name') }}" 
                               required 
                               placeholder="e.g. Rahul" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        @error('first_name')
                            <p class="text-rose-500 font-bold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="last_name" 
                               value="{{ old('last_name') }}" 
                               required 
                               placeholder="e.g. Sharma" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        @error('last_name')
                            <p class="text-rose-500 font-bold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">+91</span>
                            <input type="tel" 
                                   name="phone" 
                                   value="{{ old('phone') }}" 
                                   required 
                                   placeholder="9876543210" 
                                   class="w-full pl-12 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        </div>
                        @error('phone')
                            <p class="text-rose-500 font-bold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alternate Phone -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alternate Phone</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">+91</span>
                            <input type="tel" 
                                   name="alternate_phone" 
                                   value="{{ old('alternate_phone') }}" 
                                   placeholder="Optional alternate number" 
                                   class="w-full pl-12 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        </div>
                        @error('alternate_phone')
                            <p class="text-rose-500 font-bold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               placeholder="rahul.sharma@example.com" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        @error('email')
                            <p class="text-rose-500 font-bold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Gender</label>
                        <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm transition-all">
                            <option value="">Select Gender</option>
                            <option value="male" {{ old('gender', 'male') === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ old('gender') === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">City / Locality</label>
                        <input type="text" 
                               name="city" 
                               value="{{ old('city') }}" 
                               placeholder="e.g. Mumbai" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                    </div>

                    <!-- Instagram Handle -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Instagram Handle</label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">@</span>
                            <input type="text" 
                                   name="instagram_handle" 
                                   value="{{ old('instagram_handle') }}" 
                                   placeholder="username" 
                                   class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        </div>
                    </div>

                    <!-- Persons / Member Count -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">Number of Persons</label>
                        <input type="number" 
                               min="1" 
                               max="50" 
                               name="member_count" 
                               value="{{ old('member_count', 1) }}" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- CARD 2: PIPELINE STAGE & ATTRIBUTION -->
            <!-- ========================================================================= -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Lead Acquisition &amp; Pipeline Status</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Marketing channel attribution and current sales funnel position</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Lead Source -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Lead Source <span class="text-rose-500">*</span>
                        </label>
                        <select name="source" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-all">
                            <option value="walk_in" {{ old('source', 'walk_in') == 'walk_in' ? 'selected' : '' }}>🚶 Walk-in Front Desk</option>
                            <option value="instagram" {{ old('source') == 'instagram' ? 'selected' : '' }}>📸 Instagram DM / Ad</option>
                            <option value="facebook" {{ old('source') == 'facebook' ? 'selected' : '' }}>👥 Facebook Campaign</option>
                            <option value="google" {{ old('source') == 'google' ? 'selected' : '' }}>📍 Google Maps / Search</option>
                            <option value="whatsapp" {{ old('source') == 'whatsapp' ? 'selected' : '' }}>💬 WhatsApp Inquiry</option>
                            <option value="phone" {{ old('source') == 'phone' ? 'selected' : '' }}>📞 Direct Phone Call</option>
                            <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>🤝 Member Referral</option>
                            <option value="website" {{ old('source') == 'website' ? 'selected' : '' }}>🌐 Website Inquiry</option>
                            <option value="advertising" {{ old('source') == 'advertising' ? 'selected' : '' }}>📢 Outdoor / Banner Ad</option>
                            <option value="other" {{ old('source') == 'other' ? 'selected' : '' }}>✨ Other Source</option>
                        </select>
                    </div>

                    <!-- Pipeline Stage -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Pipeline Stage
                        </label>
                        <select name="stage" x-model="stage" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-all">
                            <option value="NEW_LEAD">🔵 New Lead (Initial Inquiry)</option>
                            <option value="CONTACTED">💬 Contacted / Qualified</option>
                            <option value="DEMO_BOOKED">📅 Demo / Trial Booked</option>
                            <option value="PROPOSAL_SENT">📄 Quotation / Proposal Sent</option>
                            <option value="NEGOTIATION">🤝 In Negotiation / Follow-up</option>
                            <option value="TRIAL">⚡ On Trial Session</option>
                            <option value="PAID">🟢 Converted / Won (Paid)</option>
                            <option value="LOST">🔴 Closed / Lost</option>
                        </select>
                    </div>

                    <!-- Assigned Staff -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Assigned Representative
                        </label>
                        @php
                            $currentUser = auth()->user();
                            $defaultAssignedStaffId = old('assigned_to_user_id');
                            if (!$defaultAssignedStaffId && $currentUser && !$currentUser->isGymOwner() && !$currentUser->isSuperAdmin() && $currentUser->isStaff()) {
                                $defaultAssignedStaffId = $currentUser->id;
                            }
                        @endphp
                        <select name="assigned_to_user_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-all">
                            <option value="">Unassigned (Front Desk Queue)</option>
                            @forelse($staffMembers as $staff)
                                <option value="{{ $staff->id }}" {{ $defaultAssignedStaffId == $staff->id ? 'selected' : '' }}>
                                    {{ $staff->name }} {{ ($currentUser && $currentUser->id === $staff->id) ? '(You)' : '' }}
                                </option>
                            @empty
                                @if($currentUser && !$currentUser->isGymOwner() && !$currentUser->isSuperAdmin() && $currentUser->isStaff())
                                    <option value="{{ $currentUser->id }}" selected>{{ $currentUser->name }} (You)</option>
                                @else
                                    <option value="" disabled class="text-slate-400">No staff members found</option>
                                @endif
                            @endforelse
                        </select>
                    </div>

                    <!-- Gym Branch -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Assigned Branch
                        </label>
                        @php
                            $defaultBranchId = old('branch_id');
                            if (!$defaultBranchId && $branches->count() === 1) {
                                $defaultBranchId = $branches->first()->id;
                            }
                        @endphp
                        <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-all">
                            @if($branches->count() === 1)
                                <option value="{{ $branches->first()->id }}" selected>
                                    {{ $branches->first()->name }}
                                </option>
                            @elseif($branches->count() > 1)
                                <option value="">Select Branch</option>
                                @foreach($branches as $branch)
                                    <option value="{{ $branch->id }}" {{ $defaultBranchId == $branch->id ? 'selected' : '' }}>
                                        {{ $branch->name }}
                                    </option>
                                @endforeach
                            @else
                                <option value="">{{ $tenant?->name ?? 'Primary Gym Facility' }}</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Dynamic Converted / Won Notice -->
                <div x-show="stage === 'PAID'" x-cloak class="p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs space-y-1">
                    <div class="flex items-center gap-2 font-black">
                        <svg class="w-4 h-4 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        <span>Lead Marked as Won / Converted</span>
                    </div>
                    <p class="text-[11px] text-emerald-700 dark:text-emerald-300/80">Saving as Converted will credit the deal value to your gym revenue metrics and allow instant member conversion.</p>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- CARD 3: MEMBERSHIP PLAN INTEREST & ESTIMATED VALUE -->
            <!-- ========================================================================= -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Package Interest &amp; Deal Value</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Target membership package inquired by the prospect and projected revenue</p>
                    </div>
                </div>

                <!-- Plans Quick-Select & Dropdown -->
                <input type="hidden" name="membership_plan_id" :value="selectedPlanId">

                @if($plans->isNotEmpty())
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                                Select Interested Membership Package <span class="text-[10px] text-slate-400 font-normal">(Auto-fills deal value &amp; recommended tenure)</span>
                            </label>
                            <select x-model="selectedPlanId" 
                                    @change="onPlanSelectChange($event)"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                                <option value="">-- Choose From All Active Membership Packages --</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}">{{ $plan->name }} ({{ $plan->duration_value }} {{ ucfirst($plan->duration_type ?? 'Months') }}) • {{ $currency }}{{ number_format($plan->price, 2) }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                            @foreach($plans as $plan)
                                <button type="button" 
                                        @click="selectPlan({{ Js::from($plan) }})"
                                        class="p-3.5 rounded-xl border text-left transition-all relative flex flex-col justify-between cursor-pointer"
                                        :class="selectedPlanId === {{ $plan->id }} ? 'bg-indigo-50/80 dark:bg-indigo-950/40 border-indigo-500 ring-2 ring-indigo-500/20 shadow-sm' : 'bg-slate-50 dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'">
                                    <div>
                                        <div class="flex items-center justify-between gap-1 mb-1">
                                            <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $plan->name }}</h4>
                                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold shrink-0">
                                                {{ $plan->duration_value }} {{ ucfirst(substr($plan->duration_type ?? 'Months', 0, 1)) }}
                                            </span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 line-clamp-1">
                                            {{ $plan->description ?? 'Standard gym membership package' }}
                                        </p>
                                    </div>
                                    <div class="mt-3 pt-2 border-t border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                                        <span class="text-xs font-black text-slate-900 dark:text-white font-mono">
                                            {{ $currency }}{{ number_format($plan->price, 2) }}
                                        </span>
                                        <span class="text-[10px] font-bold text-indigo-600 dark:text-indigo-400" x-show="selectedPlanId === {{ $plan->id }}" x-cloak>
                                            ✓ Selected
                                        </span>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Estimated Deal Value -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Estimated Deal Value ({{ $currency }})
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">{{ $currency }}</span>
                            <input type="number" 
                                   step="any" 
                                   min="0" 
                                   name="estimated_value" 
                                   x-model="estimatedValue" 
                                   placeholder="0.00" 
                                   class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono font-bold text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                        </div>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">Projected revenue value used for pipeline forecasting and sales metrics.</p>
                    </div>

                    <!-- Quick Remarks / Interest Tag -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Package Remarks / Focus Area
                        </label>
                        <input type="text" 
                               name="remarks" 
                               x-model="remarks" 
                               placeholder="e.g. Inquired about 1-year annual membership with personal trainer" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- CARD 4: FOLLOW-UP SCHEDULE & DEMO / TRIAL BOOKING -->
            <!-- ========================================================================= -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Follow-up Schedule &amp; Next Action</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Set task reminders and schedule gym trial appointments</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Next Action Type -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                            Next Action Type
                        </label>
                        <select name="next_action" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-medium focus:border-indigo-500 focus:outline-none shadow-sm cursor-pointer transition-all">
                            <option value="">Select Next Action</option>
                            <option value="Follow-up Phone Call" {{ old('next_action') == 'Follow-up Phone Call' ? 'selected' : '' }}>📞 Follow-up Phone Call</option>
                            <option value="Send WhatsApp Quotation" {{ old('next_action') == 'Send WhatsApp Quotation' ? 'selected' : '' }}>💬 Send WhatsApp Quotation</option>
                            <option value="Free Trial Session" {{ old('next_action') == 'Free Trial Session' ? 'selected' : '' }}>⚡ Free Trial Session</option>
                            <option value="Facility Tour & Meeting" {{ old('next_action') == 'Facility Tour & Meeting' ? 'selected' : '' }}>🏢 Facility Tour &amp; Meeting</option>
                            <option value="Offer Special Discount" {{ old('next_action') == 'Offer Special Discount' ? 'selected' : '' }}>🎁 Offer Special Discount</option>
                            <option value="Body Composition Assessment" {{ old('next_action') == 'Body Composition Assessment' ? 'selected' : '' }}>📊 Body Composition Assessment</option>
                        </select>
                    </div>

                    <!-- Follow-up Date (with quick-select buttons) -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Follow-up Reminder Date
                            </label>
                            <div class="flex items-center gap-1">
                                <button type="button" @click="setQuickDate(0)" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">Today</button>
                                <button type="button" @click="setQuickDate(1)" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">Tomorrow</button>
                                <button type="button" @click="setQuickDate(3)" class="text-[10px] font-bold px-2 py-0.5 rounded bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 transition-colors">+3 Days</button>
                            </div>
                        </div>
                        <input type="date" 
                               name="follow_up_date" 
                               x-model="followUpDate" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all">
                    </div>
                </div>

                <!-- Trial Booking Optional Toggle -->
                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <input type="checkbox" 
                                   id="toggleTrialSchedule" 
                                   x-model="hasTrial" 
                                   class="w-4 h-4 rounded text-indigo-600 focus:ring-0 focus:outline-none bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700">
                            <label for="toggleTrialSchedule" class="text-xs font-bold text-slate-900 dark:text-white cursor-pointer">
                                Schedule Free Workout Trial / Demo Session
                            </label>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">
                            CRM Trial Pipeline
                        </span>
                    </div>

                    <div x-show="hasTrial" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2 border-t border-slate-200/80 dark:border-slate-800">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Trial Date</label>
                            <input type="date" 
                                   name="trial_date" 
                                   value="{{ old('trial_date') }}" 
                                   class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1">Trial Time Slot</label>
                            <input type="text" 
                                   name="trial_time" 
                                   value="{{ old('trial_time', '10:00 AM') }}" 
                                   placeholder="e.g. 10:00 AM or 06:30 PM" 
                                   class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                            <input type="hidden" name="trial_status" value="upcoming">
                        </div>
                    </div>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- CARD 5: DISCUSSION & CLIENT REQUIREMENTS NOTES -->
            <!-- ========================================================================= -->
            <div class="p-4 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-black text-slate-900 dark:text-white">Discussion &amp; Requirements Notes</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Detailed conversation logs, fitness targets, medical flags, and budget preferences</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1.5">
                        Client Conversation History &amp; Requirement Notes
                    </label>
                    <textarea name="notes" 
                              rows="4" 
                              placeholder="Record client fitness goals (weight loss, muscle gain), medical history or injuries, previous gym experience, preferred workout timings, and budget notes..." 
                              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm transition-all resize-y">{{ old('notes') }}</textarea>
                </div>
            </div>

            <!-- ========================================================================= -->
            <!-- BOTTOM STICKY ACTION BAR -->
            <!-- ========================================================================= -->
            <div class="fixed bottom-0 left-0 right-0 z-40 bg-white/90 dark:bg-slate-900/90 backdrop-blur-md border-t border-slate-200 dark:border-slate-800 p-3 sm:p-4 shadow-xl">
                <div class="max-w-6xl mx-auto flex items-center justify-between gap-4">
                    <a href="{{ route('app.leads.index') }}" 
                       class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-200 font-bold text-xs transition-colors">
                        Cancel
                    </a>

                    <div class="flex items-center gap-3">
                        <button type="submit" 
                                class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save &amp; Add to Pipeline</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>