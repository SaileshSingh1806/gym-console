<x-app-layout header="Personal Training">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $currentTab = request()->get('tab', $tab ?? 'packages');
    @endphp

    <div class="space-y-4" x-data="{
        activeTab: '{{ $currentTab }}',
        sessionsSubTab: 'dashboard',
        calendarMonth: '{{ now()->format('F Y') }}',
        showAssignModal: false,
        showScheduleModal: false,
        showPlanModal: false,
        showTrainerModal: false,
        showTrainerEditModal: false,
        trainerEditPhotoPreview: null,
        planEditMode: false,
        planForm: {
            id: null,
            name: '',
            sort_order: 0,
            description: '',
            total_sessions: 12,
            validity_days: 30,
            default_price: 5000,
            trainer_commission_percent: '',
            sac_code: '',
            gst_rate: '',
            price_includes_gst: false,
            notes: '',
            is_active: true,
            includes_gate_pass: false,
            show_on_mobile_app: false,
            branch_id: ''
        },
        assignForm: {
            member_id: '',
            pt_plan_id: '',
            trainer_id: '',
            package_name: '',
            total_sessions: 12,
            validity_days: 30,
            start_date: '{{ now()->toDateString() }}',
            price: 5000,
            discount: 0,
            paid_amount: 5000,
            payment_method: 'upi',
            transaction_reference: '',
            notes: ''
        },
        scheduleForm: {
            member_id: '',
            trainer_id: '',
            member_pt_package_id: '',
            session_date: '{{ now()->toDateString() }}',
            start_time: '{{ now()->format('H:i') }}',
            duration_minutes: 60,
            focus_area: '',
            notes: ''
        },
        trainerForm: {
            id: null,
            full_name: '',
            first_name: '',
            last_name: '',
            phone: '',
            email: '',
            specialization: '',
            certification: '',
            salary: 0,
            salary_type: 'Fixed Monthly',
            salary_pay_day: '1st of every month',
            joining_date: '',
            status: 'ACTIVE',
            is_featured: false,
            bio: '',
            photo_url: null,
        },
        trainerAssignedMemberIds: [],
        trainerMemberFilter: 'all',
        trainerMemberSearch: '',
        membersList: {{ Js::from($members) }},
        plansData: {{ Js::from($plans) }},
        activePackagesData: {{ Js::from($activeMemberPackages) }},

        get assignedCount() {
            return this.trainerAssignedMemberIds.length;
        },
        get unassignedCount() {
            return Math.max(0, this.membersList.length - this.trainerAssignedMemberIds.length);
        },
        get filteredMembers() {
            let list = this.membersList;
            if (this.trainerMemberFilter === 'assigned') {
                list = list.filter(m => this.trainerAssignedMemberIds.includes(m.id));
            } else if (this.trainerMemberFilter === 'unassigned') {
                list = list.filter(m => !this.trainerAssignedMemberIds.includes(m.id));
            }
            if (this.trainerMemberSearch && this.trainerMemberSearch.trim() !== '') {
                const q = this.trainerMemberSearch.toLowerCase().trim();
                list = list.filter(m => {
                    const name = ((m.first_name || '') + ' ' + (m.last_name || '')).toLowerCase();
                    const phone = (m.phone || '').toLowerCase();
                    const code = (m.member_code || '').toLowerCase();
                    return name.includes(q) || phone.includes(q) || code.includes(q);
                });
            }
            return list;
        },
        isMemberAssigned(id) {
            return this.trainerAssignedMemberIds.includes(id);
        },
        toggleMember(id) {
            if (this.trainerAssignedMemberIds.includes(id)) {
                this.trainerAssignedMemberIds = this.trainerAssignedMemberIds.filter(x => x !== id);
            } else {
                this.trainerAssignedMemberIds.push(id);
            }
        },
        isAllVisibleSelected() {
            const visibleIds = this.filteredMembers.map(m => m.id);
            if (visibleIds.length === 0) return false;
            return visibleIds.every(id => this.trainerAssignedMemberIds.includes(id));
        },
        toggleAllVisible() {
            const visibleIds = this.filteredMembers.map(m => m.id);
            if (visibleIds.length === 0) return;
            const allSelected = visibleIds.every(id => this.trainerAssignedMemberIds.includes(id));
            if (allSelected) {
                this.trainerAssignedMemberIds = this.trainerAssignedMemberIds.filter(id => !visibleIds.includes(id));
            } else {
                const newSet = new Set([...this.trainerAssignedMemberIds, ...visibleIds]);
                this.trainerAssignedMemberIds = Array.from(newSet);
            }
        },
        previewTrainerPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.trainerEditPhotoPreview = URL.createObjectURL(file);
            }
        },
        openEditTrainer(trainer) {
            this.trainerForm = {
                id: trainer.id,
                full_name: trainer.full_name || (trainer.first_name + (trainer.last_name ? ' ' + trainer.last_name : '')),
                first_name: trainer.first_name || '',
                last_name: trainer.last_name || '',
                phone: trainer.phone || '',
                email: trainer.email || '',
                specialization: trainer.specialization || '',
                certification: trainer.certification || '',
                salary: trainer.salary ? parseFloat(trainer.salary) : 0,
                salary_type: trainer.salary_type || 'Fixed Monthly',
                salary_pay_day: trainer.salary_pay_day || '1st of every month',
                joining_date: trainer.joining_date ? trainer.joining_date.substring(0, 10) : '',
                status: trainer.status || 'ACTIVE',
                is_featured: Boolean(trainer.is_featured),
                bio: trainer.bio || '',
                photo_url: trainer.photo_path ? ('/storage/' + trainer.photo_path) : null,
            };
            this.trainerEditPhotoPreview = null;
            
            if (trainer.assigned_members && Array.isArray(trainer.assigned_members)) {
                this.trainerAssignedMemberIds = trainer.assigned_members.map(m => m.id);
            } else {
                this.trainerAssignedMemberIds = this.membersList.filter(m => m.trainer_id === trainer.id).map(m => m.id);
            }
            
            this.trainerMemberFilter = 'all';
            this.trainerMemberSearch = '';
            this.showTrainerEditModal = true;
        },
        onPlanSelect() {
            const selected = this.plansData.find(p => p.id == this.assignForm.pt_plan_id);
            if (selected) {
                this.assignForm.package_name = selected.name;
                this.assignForm.total_sessions = selected.total_sessions;
                this.assignForm.validity_days = selected.validity_days;
                this.assignForm.price = parseFloat(selected.default_price);
                this.assignForm.paid_amount = parseFloat(selected.default_price);
            }
        },
        onMemberSelectForSession() {
            const pkg = this.activePackagesData.find(p => p.member_id == this.scheduleForm.member_id);
            if (pkg) {
                this.scheduleForm.member_pt_package_id = pkg.id;
                if (pkg.trainer_id) {
                    this.scheduleForm.trainer_id = pkg.trainer_id;
                }
            }
        },
        openAddPlan() {
            this.planEditMode = false;
            this.planForm = {
                id: null,
                name: '',
                sort_order: 0,
                description: '',
                total_sessions: 12,
                validity_days: 30,
                default_price: 5000,
                trainer_commission_percent: '',
                sac_code: '',
                gst_rate: '',
                price_includes_gst: false,
                notes: '',
                is_active: true,
                includes_gate_pass: false,
                show_on_mobile_app: false,
                branch_id: ''
            };
            this.showPlanModal = true;
        },
        openEditPlan(plan) {
            this.planEditMode = true;
            this.planForm = {
                id: plan.id,
                name: plan.name,
                sort_order: plan.sort_order || 0,
                description: plan.description || '',
                total_sessions: plan.total_sessions,
                validity_days: plan.validity_days,
                default_price: parseFloat(plan.default_price),
                trainer_commission_percent: (plan.trainer_commission_percent !== null && plan.trainer_commission_percent !== undefined) ? parseFloat(plan.trainer_commission_percent) : '',
                sac_code: plan.sac_code || '',
                gst_rate: (plan.gst_rate !== null && plan.gst_rate !== undefined && parseFloat(plan.gst_rate) > 0) ? parseFloat(plan.gst_rate) : '',
                price_includes_gst: Boolean(plan.price_includes_gst),
                notes: plan.notes || '',
                is_active: Boolean(plan.is_active),
                includes_gate_pass: Boolean(plan.includes_gate_pass),
                show_on_mobile_app: Boolean(plan.show_on_mobile_app),
                branch_id: plan.branch_id || ''
            };
            this.showPlanModal = true;
        }
    }">

        <!-- Top Navigation Bar & Action Buttons -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-800 pb-3">
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <button @click="activeTab = 'packages'" 
                        :class="activeTab === 'packages' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>PT Packages</span>
                    <span class="px-1 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $packages->total() }}</span>
                </button>

                <!-- PT Sessions Tab -->
                <button @click="activeTab = 'sessions'" 
                        :class="activeTab === 'sessions' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>PT Sessions</span>
                    <span class="px-1 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $todayScheduledCount + $todayCompletedCount }}</span>
                </button>

                <!-- Plan Catalog Tab -->
                <button @click="activeTab = 'plans'" 
                        :class="activeTab === 'plans' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    <span>Plan Catalog</span>
                    <span class="px-1 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $plans->count() }}</span>
                </button>

                <!-- Coaches & Trainers Tab -->
                <button @click="activeTab = 'trainers'" 
                        :class="activeTab === 'trainers' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white hover:bg-slate-800'"
                        class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all flex items-center gap-1.5 shrink-0 border border-slate-800">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    <span>Coaches & Trainers</span>
                    <span class="px-1 py-0.2 rounded-full text-[10px] bg-slate-950/60 font-black">{{ $trainers->count() }}</span>
                </button>
            </div>

            <!-- Single Action CTA in Top Bar -->
            <div class="flex items-center gap-2">
                <template x-if="activeTab === 'packages'">
                    <button @click="showAssignModal = true" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Assign PT Package</span>
                    </button>
                </template>

                <template x-if="activeTab === 'sessions'">
                    <button @click="showScheduleModal = true" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 shadow transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Schedule Session</span>
                    </button>
                </template>

                <template x-if="activeTab === 'plans'">
                    <button @click="openAddPlan()" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>New PT Plan</span>
                    </button>
                </template>

                <template x-if="activeTab === 'trainers'">
                    <button @click="showTrainerModal = true" class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-1.5 shadow transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Trainer</span>
                    </button>
                </template>
            </div>
        </div>

        <!-- ==================== TAB 1: PT PACKAGES ==================== -->
        <div x-show="activeTab === 'packages'" class="space-y-4">
            <!-- 3 KPI Metric Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <!-- ACTIVE -->
                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-400">ACTIVE</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-white tracking-tight">{{ number_format($activePackagesCount) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Active personal training packages</div>
                    </div>
                </div>

                <!-- EXPIRING <= 7D -->
                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400">EXPIRING &le;7D</span>
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 text-amber-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-white tracking-tight">{{ number_format($expiring7DaysCount) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Packages ending within 7 days</div>
                    </div>
                </div>

                <!-- REVENUE (MTD) -->
                <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 flex flex-col justify-between">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-400">REVENUE (MTD)</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div>
                        <div class="text-xl font-bold text-white tracking-tight">{{ $currency }}{{ number_format($revenueMtd, 2) }}</div>
                        <div class="text-[11px] text-slate-400 mt-0.5">Month-to-date PT collections</div>
                    </div>
                </div>
            </div>

            <!-- Filter Toolbar -->
            <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                <form action="{{ route('app.pt.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                    <input type="hidden" name="tab" value="packages">

                    <!-- Status Filter -->
                    <div class="w-36">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Status</label>
                        <select name="status" onchange="this.form.submit()" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Statuses</option>
                            <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                            <option value="EXPIRING" {{ request('status') === 'EXPIRING' ? 'selected' : '' }}>Expiring &le;7D</option>
                            <option value="COMPLETED" {{ request('status') === 'COMPLETED' ? 'selected' : '' }}>Completed</option>
                            <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                            <option value="CANCELLED" {{ request('status') === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Trainer Filter -->
                    <div class="w-40">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Trainer</label>
                        <select name="trainer_id" onchange="this.form.submit()" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="all">All trainers</option>
                            @foreach($trainers as $tr)
                                <option value="{{ $tr->id }}" {{ request('trainer_id') == $tr->id ? 'selected' : '' }}>{{ $tr->full_name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Member / Package -->
                    <div class="flex-grow min-w-[200px]">
                        <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Search</label>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search member, phone or package..." class="w-full pl-8 pr-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                            <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                    </div>

                    <!-- Checkbox: Only expiring this week -->
                    <div class="flex items-center gap-1.5 pt-4">
                        <label class="flex items-center gap-1.5 text-xs font-medium text-slate-300 cursor-pointer">
                            <input type="checkbox" name="only_expiring" value="1" {{ request('only_expiring') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-0">
                            <span>Only expiring this week</span>
                        </label>
                    </div>

                    @if(request('search') || request('status') || request('trainer_id') || request('only_expiring'))
                        <div class="pt-4">
                            <a href="{{ route('app.pt.index', ['tab' => 'packages']) }}" class="px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">
                                Clear
                            </a>
                        </div>
                    @endif
                </form>
            </div>

            <!-- Packages Table -->
            <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3">Member</th>
                                <th class="py-2.5 px-3">Trainer</th>
                                <th class="py-2.5 px-3">Package</th>
                                <th class="py-2.5 px-3">Used</th>
                                <th class="py-2.5 px-3">Expiry</th>
                                <th class="py-2.5 px-3">Price</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @forelse($packages as $pkg)
                                @php
                                    $percentUsed = $pkg->total_sessions > 0 ? min(100, round(($pkg->used_sessions / $pkg->total_sessions) * 100)) : 0;
                                    $daysLeft = $pkg->days_remaining;
                                    $isExpired = $pkg->end_date < now()->toDateString();
                                @endphp
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <!-- Member -->
                                    <td class="py-2.5 px-3">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-indigo-500/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30 shrink-0">
                                                {{ substr($pkg->member?->first_name ?? 'M', 0, 1) }}{{ substr($pkg->member?->last_name ?? '', 0, 1) }}
                                            </div>
                                            <div>
                                                <a href="{{ $pkg->member ? route('app.members.show', $pkg->member->id) : '#' }}" class="font-bold text-white hover:text-indigo-400 transition-colors block">
                                                    {{ $pkg->member?->full_name ?? 'Deleted Member' }}
                                                </a>
                                                <div class="text-[10px] text-slate-400 flex items-center gap-1.5">
                                                    <span>{{ $pkg->member?->phone ?? 'N/A' }}</span>
                                                    @if($pkg->member?->branch)
                                                        <span class="px-1 py-0.2 rounded text-[9px] bg-slate-800 text-slate-300">{{ $pkg->member->branch->name }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Trainer -->
                                    <td class="py-2.5 px-3">
                                        @if($pkg->trainer)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-amber-500/10 text-amber-400 font-bold text-[10px] flex items-center justify-center border border-amber-500/20 shrink-0">
                                                    {{ substr($pkg->trainer->first_name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <span class="font-semibold text-slate-200 block">{{ $pkg->trainer->full_name }}</span>
                                                    <span class="text-[10px] text-amber-400/80">{{ $pkg->trainer->specialization ?? 'Coach' }}</span>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-slate-500 italic text-[11px]">Unassigned</span>
                                        @endif
                                    </td>

                                    <!-- Package -->
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $pkg->package_name }}</div>
                                        <div class="text-[10px] text-slate-400 flex items-center gap-1 mt-0.5">
                                            <span>{{ $pkg->total_sessions }} sessions</span>
                                            @if($pkg->ptPlan?->includes_gate_pass)
                                                <span class="px-1 py-0.2 rounded bg-emerald-500/10 text-emerald-400 text-[9px] font-bold">Gate Pass</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Used Progress Bar -->
                                    <td class="py-2.5 px-3 min-w-[120px]">
                                        <div class="flex items-center justify-between text-[10px] font-bold mb-1">
                                            <span class="text-white">{{ $pkg->used_sessions }}/{{ $pkg->total_sessions }}</span>
                                            <span class="text-slate-400">{{ $pkg->remaining_sessions }} left</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-950 overflow-hidden border border-slate-800">
                                            <div class="h-full rounded-full transition-all duration-300 {{ $percentUsed >= 100 ? 'bg-indigo-500' : 'bg-emerald-500' }}" style="width: {{ $percentUsed }}%"></div>
                                        </div>
                                    </td>

                                    <!-- Expiry -->
                                    <td class="py-2.5 px-3">
                                        <div class="text-slate-200 font-semibold">{{ $pkg->end_date->format('d M Y') }}</div>
                                        <div class="mt-0.5">
                                            @if($pkg->status === 'COMPLETED')
                                                <span class="text-[10px] text-indigo-400">Finished</span>
                                            @elseif($isExpired)
                                                <span class="text-[10px] text-rose-400 font-semibold">Expired</span>
                                            @elseif($daysLeft <= 7)
                                                <span class="px-1 py-0.2 rounded bg-amber-500/10 text-amber-400 text-[10px] font-bold border border-amber-500/20">{{ $daysLeft }}d left</span>
                                            @else
                                                <span class="text-[10px] text-slate-400">{{ $daysLeft }}d left</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Price -->
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $currency }}{{ number_format($pkg->final_amount, 2) }}</div>
                                        <div class="text-[10px]">
                                            @if($pkg->paid_amount >= $pkg->final_amount)
                                                <span class="text-emerald-400 font-semibold">Paid</span>
                                            @else
                                                <span class="text-amber-400 font-semibold">Due: {{ $currency }}{{ number_format($pkg->final_amount - $pkg->paid_amount, 2) }}</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2.5 px-3">
                                        @if($pkg->status === 'ACTIVE')
                                            @if($isExpired)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-rose-500/10 text-rose-400 border border-rose-500/20">Expired</span>
                                            @elseif($daysLeft <= 7)
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/10 text-amber-400 border border-amber-500/20">Expiring</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Active</span>
                                            @endif
                                        @elseif($pkg->status === 'COMPLETED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">Completed</span>
                                        @elseif($pkg->status === 'CANCELLED')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">Cancelled</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400">{{ $pkg->status }}</span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($pkg->status === 'ACTIVE' && !$isExpired && $pkg->remaining_sessions > 0)
                                                <form action="{{ route('app.pt-packages.log-session', $pkg->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" title="Log 1 session completed" class="px-2 py-1 rounded bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 text-[10px] font-bold border border-emerald-500/30 flex items-center gap-1 transition-all">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                        <span>Log</span>
                                                    </button>
                                                </form>
                                            @endif

                                            @if($pkg->status === 'ACTIVE')
                                                <form action="{{ route('app.pt-packages.cancel', $pkg->id) }}" method="POST" 
                                                      data-confirm="Are you sure you want to cancel this PT package for {{ addslashes($pkg->member->full_name ?? 'this member') }}?" 
                                                      data-confirm-title="Cancel Personal Training Package" 
                                                      data-confirm-btn="Yes, Cancel Package" 
                                                      data-confirm-type="warning" 
                                                      class="inline">
                                                    @csrf
                                                    <button type="submit" title="Cancel package" class="p-1 rounded text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 transition-colors cursor-pointer">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-slate-500">
                                        <div class="w-10 h-10 rounded-xl bg-slate-950 flex items-center justify-center mx-auto mb-2 border border-slate-800 text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        </div>
                                        <p class="font-bold text-white text-xs">No PT packages found</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Assign a personal training package to get started.</p>
                                        <button @click="showAssignModal = true" class="mt-3 px-3 py-1.5 rounded-lg bg-indigo-600 text-white font-bold text-xs hover:bg-indigo-500 transition-colors">
                                            Assign PT Package
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($packages->hasPages())
                    <div class="p-3 border-t border-slate-800 bg-slate-950/40">
                        {{ $packages->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- ==================== TAB 2: PT SESSIONS ==================== -->
        <div x-show="activeTab === 'sessions'" class="space-y-4">
            <!-- Header with Sub-tabs (Dashboard, Scheduled, Calendar, Session Log) -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-bold text-white tracking-tight">PT Sessions</h3>
                    <p class="text-[11px] text-slate-400">Schedule, track, and manage personal training sessions</p>
                </div>

                <!-- Subtab Selector -->
                <div class="flex items-center gap-1 p-0.5 rounded-lg bg-slate-900 border border-slate-800 text-xs font-bold">
                    <button @click="sessionsSubTab = 'dashboard'" :class="sessionsSubTab === 'dashboard' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">
                        Dashboard
                    </button>
                    <button @click="sessionsSubTab = 'scheduled'" :class="sessionsSubTab === 'scheduled' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">
                        Scheduled
                    </button>
                    <button @click="sessionsSubTab = 'calendar'" :class="sessionsSubTab === 'calendar' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">
                        Calendar
                    </button>
                    <button @click="sessionsSubTab = 'logs'" :class="sessionsSubTab === 'logs' ? 'bg-indigo-600 text-white shadow' : 'text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded transition-colors">
                        Session Log
                    </button>
                </div>
            </div>

            <!-- ================= SUBTAB 1: DASHBOARD ================= -->
            <div x-show="sessionsSubTab === 'dashboard'" class="space-y-4">
                <!-- 5 Session KPI Cards -->
                <div class="grid grid-cols-2 md:grid-cols-5 gap-2.5">
                    <div class="p-3 rounded-xl bg-slate-900 border border-emerald-500/20">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-emerald-400">ACTIVE PACKAGES</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $activePackagesCount }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-blue-500/20">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-blue-400">TODAY SCHEDULED</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $todayScheduledCount }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-purple-500/20">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-purple-400">TODAY COMPLETED</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $todayCompletedCount }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-amber-500/20">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-amber-400">THIS MONTH</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $thisMonthSessionsCount }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-rose-500/20 col-span-2 md:col-span-1">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-rose-400">NO-SHOWS (MONTH)</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $noShowsMonthCount }}</div>
                    </div>
                </div>

                <!-- Upcoming Sessions vs Recently Completed -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <!-- Left: Upcoming Sessions -->
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-white">
                            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                            <span>Upcoming Sessions</span>
                        </div>

                        <div class="space-y-2">
                            @forelse($upcomingSessions as $sess)
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-500/10 text-blue-400 font-bold text-xs flex items-center justify-center border border-blue-500/20 shrink-0">
                                            {{ substr($sess->member?->first_name ?? 'M', 0, 1) }}{{ substr($sess->member?->last_name ?? '', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-xs">{{ $sess->member?->full_name ?? 'Member' }}</div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $sess->trainer?->full_name ?? 'Trainer' }} &bull; <span class="text-slate-300">{{ $sess->focus_area ?? 'PT Session' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-blue-400">{{ \Carbon\Carbon::parse($sess->start_time)->format('g:i A') }}</div>
                                        <div class="text-[10px] text-slate-400 mb-1">{{ $sess->session_date->format('d M Y') }}</div>
                                        <form action="{{ route('app.pt-sessions.complete', $sess->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2 py-0.5 rounded bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-400 text-[9px] font-bold border border-emerald-500/30 transition-all">
                                                Mark Done
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center bg-slate-900/60 border border-slate-800 rounded-xl text-slate-500 text-xs">
                                    <p class="text-slate-400 font-semibold">No upcoming sessions</p>
                                    <button @click="showScheduleModal = true" class="mt-1 text-indigo-400 hover:text-indigo-300 text-xs font-bold underline">
                                        Schedule a session
                                    </button>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <!-- Right: Recently Completed -->
                    <div class="space-y-2.5">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-white">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <span>Recently Completed</span>
                        </div>

                        <div class="space-y-2">
                            @forelse($recentCompletedSessions as $sess)
                                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-colors flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-slate-800 text-slate-300 font-bold text-xs flex items-center justify-center border border-slate-700 shrink-0">
                                            {{ substr($sess->member?->first_name ?? 'M', 0, 1) }}{{ substr($sess->member?->last_name ?? '', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-white text-xs">{{ $sess->member?->full_name ?? 'Member' }}</div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $sess->trainer?->full_name ?? 'Trainer' }} &bull; {{ $sess->memberPtPackage?->package_name ?? '1 Month' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-xs font-bold text-white">{{ $sess->completed_at ? $sess->completed_at->format('g:i A') : \Carbon\Carbon::parse($sess->start_time)->format('g:i A') }}</div>
                                        <div class="text-[10px] text-slate-400 mb-0.5">{{ $sess->session_date->format('d M Y') }}</div>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 uppercase">done</span>
                                    </div>
                                </div>
                            @empty
                                <div class="p-8 text-center bg-slate-900/60 border border-slate-800 rounded-xl text-slate-500 text-xs">
                                    <p class="text-slate-400 font-semibold">No recently completed sessions yet.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>

            <!-- ================= SUBTAB 2: CALENDAR ================= -->
            <div x-show="sessionsSubTab === 'calendar'" class="space-y-3">
                <!-- Month Navigator Header -->
                <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-900 border border-slate-800">
                    <span class="text-xs font-bold text-white">{{ now()->format('F Y') }}</span>
                    <span class="text-[10px] text-slate-400">Current Month Schedule</span>
                </div>

                @php
                    $startOfMonth = now()->startOfMonth();
                    $endOfMonth = now()->endOfMonth();
                    $startDayOfWeek = $startOfMonth->dayOfWeek;
                    $daysInMonth = $startOfMonth->daysInMonth;
                    $prevMonthDays = now()->subMonth()->daysInMonth;
                @endphp

                <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
                    <!-- Week Day Headers -->
                    <div class="grid grid-cols-7 border-b border-slate-800 bg-slate-950/60 text-center text-[9px] font-bold text-slate-400 uppercase tracking-wider py-1.5">
                        <div>SUN</div>
                        <div>MON</div>
                        <div>TUE</div>
                        <div>WED</div>
                        <div>THU</div>
                        <div>FRI</div>
                        <div>SAT</div>
                    </div>

                    <!-- Day Cells -->
                    <div class="grid grid-cols-7 divide-x divide-y divide-slate-800/60 text-xs">
                        {{-- Previous Month Leading Days --}}
                        @for($i = $startDayOfWeek - 1; $i >= 0; $i--)
                            @php $dayNum = $prevMonthDays - $i; @endphp
                            <div class="p-1.5 bg-slate-950/30 text-slate-600 min-h-[60px] font-bold text-[10px]">
                                <span>{{ $dayNum }}</span>
                            </div>
                        @endfor

                        {{-- Current Month Days --}}
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $cellDate = now()->setDay($d)->toDateString();
                                $daySessions = $allSessions->where('session_date', $cellDate);
                                $isToday = $cellDate === now()->toDateString();
                            @endphp
                            <div class="p-1.5 min-h-[60px] {{ $isToday ? 'bg-indigo-950/20 ring-1 ring-inset ring-indigo-500/30' : 'bg-slate-900/40' }} hover:bg-slate-800/30 transition-colors flex flex-col justify-between">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-[10px] font-bold {{ $isToday ? 'text-indigo-400 font-black' : 'text-slate-300' }}">{{ $d }}</span>
                                    @if($isToday)
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400"></span>
                                    @endif
                                </div>

                                <div class="space-y-0.5">
                                    @foreach($daySessions as $sess)
                                        <div class="px-1.5 py-0.5 rounded bg-teal-950/80 border border-teal-500/30 text-teal-300 text-[9px] font-bold truncate flex items-center gap-1">
                                            <span>{{ \Carbon\Carbon::parse($sess->start_time)->format('H:i') }}</span>
                                            <span class="truncate">{{ $sess->member?->first_name ?? 'Member' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endfor

                        {{-- Next Month Trailing Days --}}
                        @php
                            $totalCells = $startDayOfWeek + $daysInMonth;
                            $trailingDays = (7 - ($totalCells % 7)) % 7;
                        @endphp
                        @for($t = 1; $t <= $trailingDays; $t++)
                            <div class="p-1.5 bg-slate-950/30 text-slate-600 min-h-[60px] font-bold text-[10px]">
                                <span>{{ $t }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>

            <!-- ================= SUBTAB 3 & 4: SESSION LOG ================= -->
            <div x-show="sessionsSubTab === 'logs' || sessionsSubTab === 'scheduled'" class="space-y-3.5">
                <!-- 3 Stat KPI Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-emerald-400">SESSIONS</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $allSessions->total() }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-blue-400">UNIQUE MEMBERS</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $allSessions->pluck('member_id')->unique()->count() }}</div>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-900 border border-slate-800">
                        <div class="text-[9px] font-bold uppercase tracking-wider text-amber-400">UNIQUE TRAINERS</div>
                        <div class="text-xl font-bold text-white mt-1">{{ $allSessions->pluck('trainer_id')->filter()->unique()->count() }}</div>
                    </div>
                </div>

                <!-- Session Log Filter Toolbar -->
                <div class="p-3 rounded-xl bg-slate-900 border border-slate-800 shadow-sm">
                    <form action="{{ route('app.pt.index') }}" method="GET" class="flex flex-wrap items-center gap-2.5">
                        <input type="hidden" name="tab" value="sessions">

                        <div class="w-32">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">From</label>
                            <input type="date" name="from_date" value="{{ request('from_date', now()->startOfMonth()->toDateString()) }}" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div class="w-32">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">To</label>
                            <input type="date" name="to_date" value="{{ request('to_date', now()->toDateString()) }}" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div class="w-36">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Trainer</label>
                            <select name="trainer_id" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="all">All</option>
                                @foreach($trainers as $tr)
                                    <option value="{{ $tr->id }}" {{ request('trainer_id') == $tr->id ? 'selected' : '' }}>{{ $tr->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-28">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Source</label>
                            <select name="source" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="all">All</option>
                                <option value="manual">Manual</option>
                                <option value="qr">QR / App</option>
                            </select>
                        </div>

                        <div class="flex-grow min-w-[180px]">
                            <label class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-0.5">Member</label>
                            <input type="text" name="member_search" value="{{ request('member_search') }}" placeholder="name / phone / code" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>

                        <div class="pt-4">
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition-all">
                                Apply
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Session Log Table -->
                <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                                <tr>
                                    <th class="py-2.5 px-3">Date / Time</th>
                                    <th class="py-2.5 px-3">Member</th>
                                    <th class="py-2.5 px-3">Trainer</th>
                                    <th class="py-2.5 px-3">Package</th>
                                    <th class="py-2.5 px-3">Remaining</th>
                                    <th class="py-2.5 px-3">Source</th>
                                    <th class="py-2.5 px-3">Commission</th>
                                    <th class="py-2.5 px-3">Note</th>
                                    <th class="py-2.5 px-3 text-right">Logged By</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-800/60 font-medium">
                                @forelse($allSessions as $sess)
                                    <tr class="hover:bg-slate-800/40 transition-colors">
                                        <!-- DATE / TIME -->
                                        <td class="py-2.5 px-3 font-semibold text-white">
                                            <div>{{ $sess->session_date->format('M d, Y') }}</div>
                                            <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($sess->start_time)->format('g:i A') }}</div>
                                        </td>

                                        <!-- MEMBER -->
                                        <td class="py-2.5 px-3">
                                            <div class="font-bold text-white">{{ $sess->member?->full_name }}</div>
                                            <div class="text-[10px] text-slate-400">
                                                {{ $sess->member?->member_code ? $sess->member->member_code . ' - ' : '' }}{{ $sess->member?->phone }}
                                            </div>
                                        </td>

                                        <!-- TRAINER -->
                                        <td class="py-2.5 px-3 text-slate-200">
                                            {{ $sess->trainer?->full_name ?? 'Unassigned' }}
                                        </td>

                                        <!-- PACKAGE -->
                                        <td class="py-2.5 px-3">
                                            <span class="text-indigo-400 font-semibold">{{ $sess->memberPtPackage?->package_name ?? '1 Month' }}</span>
                                        </td>

                                        <!-- REMAINING -->
                                        <td class="py-2.5 px-3 text-slate-300 font-bold">
                                            @if($sess->memberPtPackage)
                                                {{ $sess->memberPtPackage->remaining_sessions }} / {{ $sess->memberPtPackage->total_sessions }}
                                            @else
                                                -
                                            @endif
                                        </td>

                                        <!-- SOURCE -->
                                        <td class="py-2.5 px-3">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-950 text-indigo-300 border border-slate-800">manual</span>
                                        </td>

                                        <!-- COMMISSION -->
                                        <td class="py-2.5 px-3 text-slate-300">
                                            @if($sess->memberPtPackage && $sess->memberPtPackage->ptPlan?->trainer_commission_percent)
                                                @php
                                                    $commPerSess = ($sess->memberPtPackage->final_amount * ($sess->memberPtPackage->ptPlan->trainer_commission_percent / 100)) / max(1, $sess->memberPtPackage->total_sessions);
                                                @endphp
                                                <span class="text-amber-400 font-bold">{{ $currency }}{{ number_format($commPerSess, 2) }}</span>
                                            @else
                                                <span class="text-slate-500">-</span>
                                            @endif
                                        </td>

                                        <!-- NOTE -->
                                        <td class="py-2.5 px-3 text-slate-400">
                                            {{ strtolower($sess->status) }}
                                        </td>

                                        <!-- LOGGED BY -->
                                        <td class="py-2.5 px-3 text-right text-slate-400">
                                            {{ auth()->user()->name ?? 'Admin' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-6 text-center text-slate-500 text-xs">
                                            No session log records found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 3: PT PLAN CATALOG ==================== -->
        <div x-show="activeTab === 'plans'" class="space-y-4">
            <!-- Header -->
            <div>
                <h3 class="text-sm font-bold text-white tracking-tight">PT Plan Catalog</h3>
                <p class="text-[11px] text-slate-400">Reusable personal training templates with sessions, validity, pricing, and trainer commission.</p>
            </div>

            <!-- Plans Table -->
            <div class="rounded-xl bg-slate-900 border border-slate-800 overflow-hidden shadow">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-950/60 text-slate-400 uppercase tracking-wider text-[10px] font-bold border-b border-slate-800">
                            <tr>
                                <th class="py-2.5 px-3 w-10 text-center">#</th>
                                <th class="py-2.5 px-3">Name</th>
                                <th class="py-2.5 px-3">Sessions</th>
                                <th class="py-2.5 px-3">Validity</th>
                                <th class="py-2.5 px-3">Price</th>
                                <th class="py-2.5 px-3">Commission</th>
                                <th class="py-2.5 px-3">GST</th>
                                <th class="py-2.5 px-3">Sold</th>
                                <th class="py-2.5 px-3">Status</th>
                                <th class="py-2.5 px-3 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800/60 font-medium">
                            @forelse($plans as $index => $plan)
                                <tr class="hover:bg-slate-800/40 transition-colors">
                                    <!-- # -->
                                    <td class="py-2.5 px-3 text-center font-bold text-slate-500">
                                        {{ $index + 1 }}
                                    </td>

                                    <!-- Name -->
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $plan->name }}</div>
                                        @if($plan->description)
                                            <div class="text-[10px] text-slate-400 line-clamp-1 mt-0.5">{{ $plan->description }}</div>
                                        @endif
                                        <div class="flex items-center gap-1 mt-0.5">
                                            @if($plan->includes_gate_pass)
                                                <span class="px-1 py-0.2 rounded bg-emerald-500/15 text-emerald-400 text-[9px] font-bold border border-emerald-500/20">Gate Pass</span>
                                            @endif
                                            @if($plan->show_on_mobile_app)
                                                <span class="px-1 py-0.2 rounded bg-indigo-500/15 text-indigo-400 text-[9px] font-bold border border-indigo-500/20">Mobile App</span>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- Sessions -->
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded bg-slate-950 border border-slate-800 font-bold text-white text-xs">
                                            {{ $plan->total_sessions }}
                                        </span>
                                    </td>

                                    <!-- Validity -->
                                    <td class="py-2.5 px-3 font-semibold text-slate-300">
                                        {{ $plan->validity_days }} Days
                                    </td>

                                    <!-- Price -->
                                    <td class="py-2.5 px-3">
                                        <div class="font-bold text-white">{{ $currency }}{{ number_format($plan->default_price, 2) }}</div>
                                        @if($plan->gst_rate > 0)
                                            <div class="text-[10px] text-slate-400">
                                                {{ $plan->price_includes_gst ? 'Incl. GST' : '+ ' . number_format($plan->gst_rate, 0) . '% GST' }}
                                            </div>
                                        @endif
                                    </td>

                                    <!-- Commission -->
                                    <td class="py-2.5 px-3">
                                        @if($plan->trainer_commission_percent !== null && (float)$plan->trainer_commission_percent > 0)
                                            <span class="font-bold text-amber-400">{{ number_format($plan->trainer_commission_percent, 2) }}%</span>
                                        @else
                                            <span class="text-slate-500">-</span>
                                        @endif
                                    </td>

                                    <!-- GST -->
                                    <td class="py-2.5 px-3 text-slate-300">
                                        @if($plan->gst_rate > 0)
                                            <div>SAC {{ $plan->sac_code ?? '999723' }}</div>
                                            <div class="text-[10px] text-slate-400">{{ number_format($plan->gst_rate, 0) }}% Rate</div>
                                        @else
                                            <span class="text-slate-500">Exempt / -</span>
                                        @endif
                                    </td>

                                    <!-- Sold -->
                                    <td class="py-2.5 px-3">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-500/10 text-indigo-400 border border-indigo-500/20">
                                            {{ $plan->member_pt_packages_count ?? 0 }} sold
                                        </span>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-2.5 px-3">
                                        @if($plan->is_active)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                                <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Actions -->
                                    <td class="py-2.5 px-3 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- Edit Button -->
                                            <button @click="openEditPlan({{ Js::from($plan) }})" title="Edit PT Plan" class="p-1 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                            </button>

                                            <!-- Toggle Power Button -->
                                            <form action="{{ route('app.pt-plans.toggle', $plan->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" title="{{ $plan->is_active ? 'Deactivate' : 'Activate' }}" class="p-1 rounded {{ $plan->is_active ? 'bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 border border-emerald-500/20' : 'bg-slate-800 text-slate-500 hover:text-slate-300' }} transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                                </button>
                                            </form>

                                            <!-- Delete Button -->
                                            <form action="{{ route('app.pt-plans.delete', $plan->id) }}" method="POST" 
                                                  data-confirm="Are you sure you want to delete the personal training plan '{{ addslashes($plan->name) }}'?" 
                                                  data-confirm-title="Delete PT Plan" 
                                                  data-confirm-btn="Yes, Delete Plan" 
                                                  data-confirm-type="danger" 
                                                  class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" title="Delete PT plan" class="p-1 rounded bg-slate-800 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="py-8 text-center text-slate-500">
                                        <div class="w-10 h-10 rounded-xl bg-slate-950 flex items-center justify-center mx-auto mb-2 border border-slate-800 text-slate-600">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                                        </div>
                                        <p class="font-bold text-white text-xs">No PT plans defined yet</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Create predefined PT packages to standardize pricing and assign them easily.</p>
                                        <button @click="openAddPlan()" class="mt-3 px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition-colors">
                                            Create First PT Plan
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ==================== TAB 4: COACHES & TRAINERS ==================== -->
        <div x-show="activeTab === 'trainers'" class="space-y-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight">All Trainers</h3>
                        <p class="text-[11px] text-slate-400">Manage certified coaches, personal trainers, assigned clients, and compensation</p>
                    </div>
                </div>
                <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-slate-900 text-slate-300 border border-slate-800">{{ $trainers->count() }} {{ Str::plural('trainer', $trainers->count()) }}</span>
            </div>

            <!-- Trainer Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5">
                @forelse($trainers as $trainer)
                    <div class="p-3.5 rounded-xl bg-slate-900 border border-slate-800 hover:border-slate-700 transition-all shadow flex flex-col justify-between">
                        <div>
                            <!-- Top Row: Avatar & Name & Badges -->
                            <div class="flex items-start gap-3 mb-3">
                                <div class="relative shrink-0">
                                    @if($trainer->photo_path)
                                        <img src="{{ Storage::url($trainer->photo_path) }}" alt="{{ $trainer->full_name }}" class="w-10 h-10 rounded-xl object-cover border border-slate-700 shadow">
                                    @else
                                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 text-indigo-400 font-bold text-xs flex items-center justify-center border border-indigo-500/30 shadow">
                                            {{ substr($trainer->first_name, 0, 1) }}{{ substr($trainer->last_name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full {{ $trainer->status === 'ACTIVE' ? 'bg-emerald-500 ring-1 ring-slate-900' : 'bg-slate-600 ring-1 ring-slate-900' }}"></span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <h4 class="font-bold text-white text-xs truncate">{{ $trainer->full_name }}</h4>
                                        @if($trainer->status === 'ACTIVE')
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 shrink-0"></span>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-400 font-medium truncate">{{ $trainer->specialization ?? 'Personal Trainer' }}</p>

                                    <!-- Badges -->
                                    <div class="flex flex-wrap items-center gap-1 mt-1.5">
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-800 text-slate-300 border border-slate-700">
                                            Staff
                                        </span>

                                        <a href="{{ route('app.staff.index', ['search' => $trainer->phone]) }}" class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors">
                                            Staff Record
                                        </a>

                                        @if($trainer->is_featured)
                                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/15 text-amber-400 border border-amber-500/30">
                                                ★ Featured
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- 2 Stats Columns -->
                            <div class="grid grid-cols-2 gap-1.5 p-2 rounded-lg bg-slate-950/70 border border-slate-800/80 mb-2.5 text-center">
                                <div class="border-r border-slate-800/80 pr-1">
                                    <div class="text-sm font-bold text-white">{{ $trainer->assigned_members_count ?? $trainer->assignedMembers->count() }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">CLIENTS</div>
                                </div>
                                <div class="pl-1">
                                    <div class="text-sm font-bold text-white">{{ $trainer->today_sessions_count ?? 0 }}</div>
                                    <div class="text-[8px] font-bold text-slate-400 uppercase tracking-wider">SESSIONS TODAY</div>
                                </div>
                            </div>

                            <!-- Contact Details -->
                            <div class="space-y-1 text-xs text-slate-400 mb-3 px-0.5">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span class="text-slate-200 font-medium text-[11px]">{{ $trainer->phone }}</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-3 h-3 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                    <span class="text-slate-400 truncate text-[11px]">{{ $trainer->email ?: '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons Row -->
                        <div class="flex items-center gap-1.5 pt-2.5 border-t border-slate-800">
                            <button @click="openEditTrainer({{ Js::from($trainer) }})" class="flex-1 py-1.5 px-2.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1 shadow transition-all">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                <span>Edit</span>
                            </button>

                            <form action="{{ route('app.trainers.delete', $trainer->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete coach / trainer '{{ addslashes($trainer->full_name) }}'? Assigned members will be unassigned." 
                                  data-confirm-title="Delete Trainer Profile" 
                                  data-confirm-btn="Yes, Delete Trainer" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" title="Delete Trainer" class="p-1.5 rounded-lg bg-slate-950 hover:bg-rose-500/20 text-slate-500 hover:text-rose-400 border border-slate-800 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="col-span-full p-8 text-center bg-slate-900 border border-slate-800 rounded-xl text-slate-500 text-xs">
                        <div class="w-10 h-10 rounded-xl bg-slate-950 flex items-center justify-center mx-auto mb-2 border border-slate-800 text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                        <p class="font-bold text-white text-xs">No trainers registered yet</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Add your fitness trainers to start assigning them to PT packages.</p>
                        <button @click="showTrainerModal = true" class="mt-3 px-3 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs hover:bg-amber-400 transition-colors">
                            Add Fitness Trainer
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== MODAL 1: ADD / EDIT PT PLAN (GST OPTIONAL) ==================== -->
        <div x-show="showPlanModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showPlanModal = false">
                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white" x-text="planEditMode ? 'Edit PT Plan' : 'Create PT Plan'"></h3>
                        <p class="text-[11px] text-slate-400">Define personal training packages with session counts, validity, and pricing.</p>
                    </div>
                    <button @click="showPlanModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form :action="planEditMode ? '{{ url('/app/pt-plans') }}/' + planForm.id : '{{ route('app.pt-plans.store') }}'" method="POST" class="space-y-3">
                    @csrf
                    
                    <!-- Row: Name & Sort Order -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Name *</label>
                            <input type="text" name="name" x-model="planForm.name" required placeholder="e.g. 12 Sessions - Elite Transformation" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Sort order</label>
                            <input type="number" name="sort_order" x-model="planForm.sort_order" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Description</label>
                        <textarea name="description" x-model="planForm.description" rows="2" placeholder="What's included, training style, etc." class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Row of 4: Total Sessions, Validity, Price, Commission -->
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Total sessions *</label>
                            <input type="number" name="total_sessions" x-model="planForm.total_sessions" required min="1" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Validity (days) *</label>
                            <input type="number" name="validity_days" x-model="planForm.validity_days" required min="1" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Default price ({{ $currency }}) *</label>
                            <input type="number" step="0.01" name="default_price" x-model="planForm.default_price" required min="0" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Trainer comm. (%)</label>
                            <input type="number" step="0.01" name="trainer_commission_percent" x-model="planForm.trainer_commission_percent" placeholder="Optional" min="0" max="100" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- GST / HSN-SAC Section (Fully Optional) -->
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] font-bold text-amber-400 uppercase tracking-wider">GST / HSN-SAC</span>
                            <span class="text-[10px] text-slate-500 uppercase font-semibold">Optional</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">SAC code (Optional)</label>
                                <input type="text" name="sac_code" x-model="planForm.sac_code" placeholder="e.g. 999723 (Optional)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">GST rate (%) (Optional)</label>
                                <input type="number" step="0.01" name="gst_rate" x-model="planForm.gst_rate" placeholder="0 (Optional)" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>
                        <label class="flex items-center gap-2 cursor-pointer pt-0.5">
                            <input type="checkbox" name="price_includes_gst" x-model="planForm.price_includes_gst" value="1" class="rounded border-slate-700 bg-slate-900 text-amber-500 focus:ring-0">
                            <span class="text-[11px] font-medium text-slate-300">Price already includes GST</span>
                        </label>
                    </div>

                    <!-- Notes (Internal) -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Notes (internal)</label>
                        <textarea name="notes" x-model="planForm.notes" rows="2" placeholder="Internal notes for front desk & admin..." class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <!-- 3 Feature Switches -->
                    <div class="space-y-1.5 pt-2 border-t border-slate-800">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" x-model="planForm.is_active" value="1" class="rounded border-slate-700 bg-slate-950 text-emerald-500 focus:ring-0">
                            <span class="text-xs font-semibold text-white">Active (available for sale)</span>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="includes_gate_pass" x-model="planForm.includes_gate_pass" value="1" class="rounded border-slate-700 bg-slate-950 text-indigo-500 focus:ring-0">
                            <div>
                                <span class="text-xs font-semibold text-white">Includes Gate Pass</span>
                                <span class="text-[10px] text-slate-400 block">Allows turnstile/biometric entry on personal training session days</span>
                            </div>
                        </label>

                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="show_on_mobile_app" x-model="planForm.show_on_mobile_app" value="1" class="rounded border-slate-700 bg-slate-950 text-amber-500 focus:ring-0">
                            <div>
                                <span class="text-xs font-semibold text-white">Show on Mobile App</span>
                                <span class="text-[10px] text-slate-400 block">Visible to members in the mobile application catalog</span>
                            </div>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showPlanModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow transition-all">
                            <span x-text="planEditMode ? 'Update PT Plan' : 'Save PT Plan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL 2: ASSIGN PT PACKAGE ==================== -->
        <div x-show="showAssignModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-lg w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showAssignModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white">Assign PT Package</h3>
                        <p class="text-[11px] text-slate-400">Sell or allocate personal training sessions to a gym member.</p>
                    </div>
                    <button @click="showAssignModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.pt-packages.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <!-- Member Selection -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Gym Member *</label>
                        <select name="member_id" x-model="assignForm.member_id" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Select Member --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone }}) {{ $m->member_code ? '- ' . $m->member_code : '' }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- PT Plan Template & Assigned Trainer -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">PT Plan Template</label>
                            <select name="pt_plan_id" x-model="assignForm.pt_plan_id" @change="onPlanSelect()" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Custom / Select Plan --</option>
                                @foreach($plans->where('is_active', true) as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->total_sessions }} sess / {{ $currency }}{{ number_format($p->default_price, 0) }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Assigned Trainer / Coach</label>
                            <select name="trainer_id" x-model="assignForm.trainer_id" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Select Coach --</option>
                                @foreach($trainers as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->full_name }} ({{ $tr->specialization ?? 'Coach' }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Package Name -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Package Name *</label>
                        <input type="text" name="package_name" x-model="assignForm.package_name" required placeholder="e.g. 12 Sessions PT Package" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <!-- Sessions, Validity, Start Date -->
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Total Sessions *</label>
                            <input type="number" name="total_sessions" x-model="assignForm.total_sessions" required min="1" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Validity (Days) *</label>
                            <input type="number" name="validity_days" x-model="assignForm.validity_days" required min="1" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" x-model="assignForm.start_date" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Pricing & Payment -->
                    <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800 space-y-2.5">
                        <div class="text-[11px] font-bold text-indigo-400 uppercase tracking-wider">Pricing & Collection</div>
                        <div class="grid grid-cols-3 gap-2.5">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Price ({{ $currency }}) *</label>
                                <input type="number" step="0.01" name="price" x-model="assignForm.price" required min="0" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Discount ({{ $currency }})</label>
                                <input type="number" step="0.01" name="discount" x-model="assignForm.discount" min="0" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Paid Now ({{ $currency }})</label>
                                <input type="number" step="0.01" name="paid_amount" x-model="assignForm.paid_amount" min="0" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 pt-0.5">
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Payment Method</label>
                                <select name="payment_method" x-model="assignForm.payment_method" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="upi">UPI / QR</option>
                                    <option value="cash">Cash</option>
                                    <option value="card">Card / POS</option>
                                    <option value="netbanking">Net Banking / Transfer</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-slate-400 mb-1">Transaction Ref / Txn ID</label>
                                <input type="text" name="transaction_reference" x-model="assignForm.transaction_reference" placeholder="e.g. UPI-9982348" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Notes / Goals</label>
                        <textarea name="notes" x-model="assignForm.notes" rows="2" placeholder="e.g. Weight loss focus, evening sessions preferred" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showAssignModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition-all">
                            Assign PT Package
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL 3: SCHEDULE PT SESSION ==================== -->
        <div x-show="showScheduleModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-md w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showScheduleModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white">Schedule PT Session</h3>
                        <p class="text-[11px] text-slate-400">Book a one-on-one training appointment for a member.</p>
                    </div>
                    <button @click="showScheduleModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.pt-sessions.store') }}" method="POST" class="space-y-3">
                    @csrf

                    <!-- Select Member -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Member *</label>
                        <select name="member_id" x-model="scheduleForm.member_id" @change="onMemberSelectForSession()" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Select Member --</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned Trainer & Linked PT Package -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Trainer / Coach</label>
                            <select name="trainer_id" x-model="scheduleForm.trainer_id" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Select Trainer --</option>
                                @foreach($trainers as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Linked PT Package</label>
                            <select name="member_pt_package_id" x-model="scheduleForm.member_pt_package_id" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="">-- Standalone / Package --</option>
                                @foreach($activeMemberPackages as $pkg)
                                    <option value="{{ $pkg->id }}">{{ $pkg->member?->full_name }} - {{ $pkg->package_name }} ({{ $pkg->remaining_sessions }} left)</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Date, Time, Duration -->
                    <div class="grid grid-cols-3 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Date *</label>
                            <input type="date" name="session_date" x-model="scheduleForm.session_date" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Time *</label>
                            <input type="time" name="start_time" x-model="scheduleForm.start_time" required class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Duration (min)</label>
                            <input type="number" name="duration_minutes" x-model="scheduleForm.duration_minutes" min="15" max="180" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Focus Area & Notes -->
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Focus Area / Workout Target</label>
                        <input type="text" name="focus_area" x-model="scheduleForm.focus_area" placeholder="e.g. Chest & Triceps, Hypertrophy, Leg Day" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Notes / Instructions</label>
                        <textarea name="notes" x-model="scheduleForm.notes" rows="2" placeholder="Instructions, warm-up routine, member goals..." class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showScheduleModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow transition-all">
                            Book Session
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL 4: ADD TRAINER ==================== -->
        <div x-show="showTrainerModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/80 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-xl w-full p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showTrainerModal = false">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white">Add Fitness Trainer</h3>
                        <p class="text-[11px] text-slate-400">Register a certified coach or personal trainer</p>
                    </div>
                    <button @click="showTrainerModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.trainers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="full_name" required placeholder="e.g. Aditya Sharma" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Email</label>
                            <input type="email" name="email" placeholder="trainer@example.com" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Specialization</label>
                            <input type="text" name="specialization" placeholder="e.g. Weight Training" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Certification</label>
                            <input type="text" name="certification" placeholder="e.g. ACE, NASM" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Salary ({{ $currency }})</label>
                            <input type="number" step="0.01" name="salary" placeholder="0.00" value="0.00" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Salary Type</label>
                            <select name="salary_type" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="Fixed Monthly">Fixed Monthly</option>
                                <option value="Per Session">Per Session</option>
                                <option value="Commission Based">Commission Based</option>
                                <option value="Hourly">Hourly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Salary Pay Day</label>
                            <select name="salary_pay_day" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="1st of every month">1st of every month</option>
                                <option value="5th of every month">5th of every month</option>
                                <option value="10th of every month">10th of every month</option>
                                <option value="15th of every month">15th of every month</option>
                                <option value="Last day of month">Last day of month</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Joining Date</label>
                            <input type="date" name="joining_date" value="{{ now()->toDateString() }}" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Status</label>
                            <select name="status" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                                <option value="SUSPENDED">Suspended</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-slate-300 mb-1">Profile Photo</label>
                            <input type="file" name="photo" accept="image/*" class="w-full px-2.5 py-1 rounded-lg bg-slate-950 border border-slate-800 text-slate-300 text-xs file:mr-2 file:py-0.5 file:px-2 file:rounded-md file:border-0 file:text-[11px] file:bg-amber-500 file:text-slate-950 file:font-bold">
                        </div>
                    </div>

                    <!-- Amber Banner: Featured Trainer -->
                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center gap-2.5">
                        <input type="checkbox" name="is_featured" id="is_featured_add" value="1" class="rounded border-amber-500/40 bg-slate-950 text-amber-500 focus:ring-0">
                        <label for="is_featured_add" class="cursor-pointer">
                            <span class="text-xs font-bold text-amber-300 block">★ Featured Trainer</span>
                            <span class="text-[10px] text-amber-400/80 block">Featured trainers are highlighted on the public website and client app</span>
                        </label>
                    </div>

                    <div>
                        <label class="block text-[11px] font-semibold text-slate-300 mb-1">Biography / Notes</label>
                        <textarea name="bio" rows="2" placeholder="Experience, certifications..." class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-amber-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showTrainerModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 text-slate-300 text-xs font-bold">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-amber-500 text-slate-950 font-bold text-xs shadow">Save Trainer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL 5: EDIT TRAINER & ASSIGN MEMBERS ==================== -->
        <div x-show="showTrainerEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/85 flex items-center justify-center p-3" x-cloak>
            <div class="bg-slate-900 border border-slate-800 rounded-2xl max-w-3xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-5 shadow-2xl space-y-4" @click.away="showTrainerEditModal = false">
                
                <!-- Modal Top Header -->
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-white tracking-tight">Edit Trainer</h3>
                        <p class="text-[11px] text-slate-400">Update profile, certifications, compensation, and manage assigned gym members</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'{{ url('/app/staff') }}?search=' + (trainerForm.phone || '')" target="_blank" class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold flex items-center gap-1 transition-colors border border-slate-700">
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            <span>Staff Record</span>
                        </a>
                        <button @click="showTrainerEditModal = false" class="p-1 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <form :action="'{{ url('/app/trainers') }}/' + trainerForm.id" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf

                    <!-- Profile Photo Row -->
                    <div class="flex items-center gap-3.5 p-3 rounded-xl bg-slate-950/60 border border-slate-800">
                        <div class="relative shrink-0">
                            <template x-if="trainerEditPhotoPreview">
                                <img :src="trainerEditPhotoPreview" class="w-12 h-12 rounded-xl object-cover border border-indigo-500 shadow">
                            </template>
                            <template x-if="!trainerEditPhotoPreview && trainerForm.photo_url">
                                <img :src="trainerForm.photo_url" class="w-12 h-12 rounded-xl object-cover border border-slate-700 shadow">
                            </template>
                            <template x-if="!trainerEditPhotoPreview && !trainerForm.photo_url">
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500/20 to-purple-500/20 text-indigo-400 font-bold text-sm flex items-center justify-center border border-indigo-500/30">
                                    <span x-text="(trainerForm.full_name ? trainerForm.full_name.substring(0, 2).toUpperCase() : 'TR')"></span>
                                </div>
                            </template>
                        </div>
                        <div>
                            <input type="file" id="trainer_photo_edit" name="photo" accept="image/*" class="hidden" @change="previewTrainerPhoto($event)">
                            <label for="trainer_photo_edit" class="cursor-pointer inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition-colors">
                                <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Change Photo</span>
                            </label>
                            <p class="text-[10px] text-slate-400 mt-0.5">JPG, PNG or WEBP up to 3MB</p>
                        </div>
                    </div>

                    <!-- Main Fields Grid -->
                    <div class="space-y-3">
                        <!-- Row 1: Full Name, Email, Phone -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Full Name *</label>
                                <input type="text" name="full_name" x-model="trainerForm.full_name" required placeholder="e.g. Aditya" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Email</label>
                                <input type="email" name="email" x-model="trainerForm.email" placeholder="e.g. aditya@gmail.com" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Phone *</label>
                                <input type="text" name="phone" x-model="trainerForm.phone" required placeholder="e.g. 9742836352" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Row 2: Specialization, Certification -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Specialization</label>
                                <input type="text" name="specialization" x-model="trainerForm.specialization" placeholder="e.g. Weight Training, Yoga, CrossFit" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Certification</label>
                                <input type="text" name="certification" x-model="trainerForm.certification" placeholder="e.g. ACE, NASM, ISSA" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Row 3: Salary, Salary Type, Salary Pay Day, Date of Joining -->
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Salary ({{ $currency }})</label>
                                <input type="number" step="0.01" name="salary" x-model="trainerForm.salary" placeholder="0.00" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Salary Type</label>
                                <select name="salary_type" x-model="trainerForm.salary_type" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="Fixed Monthly">Fixed Monthly</option>
                                    <option value="Per Session">Per Session</option>
                                    <option value="Commission Based">Commission Based</option>
                                    <option value="Hourly">Hourly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Salary Pay Day</label>
                                <select name="salary_pay_day" x-model="trainerForm.salary_pay_day" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="1st of every month">1st of every month</option>
                                    <option value="5th of every month">5th of every month</option>
                                    <option value="10th of every month">10th of every month</option>
                                    <option value="15th of every month">15th of every month</option>
                                    <option value="Last day of month">Last day of month</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Date of Joining</label>
                                <input type="date" name="joining_date" x-model="trainerForm.joining_date" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Row 4: Status & Amber Banner -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 items-center">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-300 mb-1">Status</label>
                                <select name="status" x-model="trainerForm.status" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-950 border border-slate-800 text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                    <option value="SUSPENDED">Suspended</option>
                                </select>
                            </div>

                            <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center gap-2.5 mt-2 sm:mt-0">
                                <input type="checkbox" name="is_featured" id="is_featured_edit" x-model="trainerForm.is_featured" value="1" class="rounded border-amber-500/40 bg-slate-950 text-amber-500 focus:ring-0">
                                <label for="is_featured_edit" class="cursor-pointer">
                                    <span class="text-xs font-bold text-amber-300 block">★ Featured Trainer</span>
                                    <span class="text-[10px] text-amber-400/80 block">Featured on public site & mobile app</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== BOTTOM SECTION: ASSIGN MEMBERS TO TRAINER ==================== -->
                    <div class="pt-4 border-t border-slate-800 space-y-3">
                        <input type="hidden" name="assigned_member_ids_submitted" value="1">

                        <!-- Section Header with Badges -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs font-bold text-white">
                                    Assign Members to <span class="text-indigo-400" x-text="trainerForm.full_name"></span>
                                </h4>
                                <p class="text-[10px] text-slate-400">Select members to assign them directly to this trainer</p>
                            </div>
                            <div class="flex items-center gap-1.5">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-500/20 text-indigo-300 border border-indigo-500/30">
                                    <span x-text="assignedCount"></span> Assigned
                                </span>
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-800 text-slate-400 border border-slate-700">
                                    <span x-text="unassignedCount"></span> Unassigned
                                </span>
                            </div>
                        </div>

                        <!-- Filter and Search Toolbar -->
                        <div class="flex flex-wrap items-center justify-between gap-2 p-2 rounded-xl bg-slate-950/70 border border-slate-800">
                            <!-- Filter Pills: All Members, Assigned, Unassigned -->
                            <div class="flex items-center gap-1 text-[11px] font-bold">
                                <button type="button" @click="trainerMemberFilter = 'all'" :class="trainerMemberFilter === 'all' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg border border-slate-800 transition-colors">
                                    All (<span x-text="membersList.length"></span>)
                                </button>
                                <button type="button" @click="trainerMemberFilter = 'assigned'" :class="trainerMemberFilter === 'assigned' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg border border-slate-800 transition-colors">
                                    Assigned (<span x-text="assignedCount"></span>)
                                </button>
                                <button type="button" @click="trainerMemberFilter = 'unassigned'" :class="trainerMemberFilter === 'unassigned' ? 'bg-indigo-600 text-white shadow' : 'bg-slate-900 text-slate-400 hover:text-white'" class="px-2.5 py-1 rounded-lg border border-slate-800 transition-colors">
                                    Unassigned (<span x-text="unassignedCount"></span>)
                                </button>
                            </div>

                            <!-- Search Members Input -->
                            <div class="relative w-full sm:w-56">
                                <input type="text" x-model="trainerMemberSearch" placeholder="Search members..." class="w-full pl-7 pr-2.5 py-1 rounded-lg bg-slate-900 border border-slate-800 text-white text-xs placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                <svg class="w-3.5 h-3.5 text-slate-500 absolute left-2 top-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <!-- Select All Checkbox -->
                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center gap-1.5 cursor-pointer text-[11px] font-semibold text-slate-300">
                                <input type="checkbox" :checked="isAllVisibleSelected()" @change="toggleAllVisible()" class="rounded border-slate-700 bg-slate-950 text-indigo-600 focus:ring-0">
                                <span>Select all visible</span>
                            </label>
                            <span class="text-[10px] text-slate-500"><span x-text="filteredMembers.length"></span> members</span>
                        </div>

                        <!-- Member Cards Grid -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-2 max-h-60 overflow-y-auto pr-1">
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="toggleMember(m.id)" :class="isMemberAssigned(m.id) ? 'bg-indigo-950/30 border-indigo-500/40 ring-1 ring-indigo-500/30' : 'bg-slate-950/60 border-slate-800 hover:border-slate-700'" class="p-2 rounded-xl border transition-all cursor-pointer flex items-center justify-between gap-2 select-none">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <input type="checkbox" name="assigned_member_ids[]" :value="m.id" :checked="isMemberAssigned(m.id)" @click.stop="toggleMember(m.id)" class="rounded border-slate-700 bg-slate-900 text-indigo-600 focus:ring-0 shrink-0">
                                        
                                        <div class="w-6 h-6 rounded-full bg-slate-800 text-slate-300 font-bold text-[10px] flex items-center justify-center border border-slate-700 shrink-0">
                                            <span x-text="((m.first_name || 'M').charAt(0) + (m.last_name || '').charAt(0)).toUpperCase()"></span>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-white text-[11px] truncate" x-text="(m.first_name || '') + ' ' + (m.last_name || '')"></div>
                                            <div class="text-[9px] text-slate-400 truncate" x-text="(m.member_code ? m.member_code + ' • ' : '') + (m.phone || 'No phone')"></div>
                                        </div>
                                    </div>

                                    <template x-if="isMemberAssigned(m.id)">
                                        <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/15 text-emerald-400 border border-emerald-500/20 shrink-0">
                                            ✓
                                        </span>
                                    </template>
                                </div>
                            </template>

                            <template x-if="filteredMembers.length === 0">
                                <div class="col-span-full py-6 text-center text-slate-500 text-xs">
                                    No members found matching filter or search.
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Bottom Update Button Row -->
                    <div class="flex items-center justify-end gap-2.5 pt-3 border-t border-slate-800">
                        <button type="button" @click="showTrainerEditModal = false" class="px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-4 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow flex items-center gap-1.5 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Update Trainer</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
