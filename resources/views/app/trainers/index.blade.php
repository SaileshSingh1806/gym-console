<x-app-layout header="Fitness Trainers & Coaches">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $activeTrainersCount = $trainers->where('status', 'ACTIVE')->count();
        $totalAssignedMembers = $trainers->sum(function($t) {
            return $t->assigned_members_count ?? ($t->assignedMembers ? $t->assignedMembers->count() : 0);
        });
        $totalTodaySessions = $trainers->sum(function($t) {
            return $t->today_sessions_count ?? 0;
        });
    @endphp

    <div class="space-y-6" x-data="{
        showTrainerModal: false,
        showTrainerEditModal: false,
        showDeleteModal: false,
        trainerToDelete: { id: null, name: '' },
        trainerEditPhotoPreview: null,
        trainerAddPhotoPreview: null,
        statusFilter: 'all',
        searchQuery: '',
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
        previewAddTrainerPhoto(event) {
            const file = event.target.files[0];
            if (file) {
                this.trainerAddPhotoPreview = URL.createObjectURL(file);
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
            } else if (trainer.assigned_members_ids && Array.isArray(trainer.assigned_members_ids)) {
                this.trainerAssignedMemberIds = trainer.assigned_members_ids;
            } else {
                this.trainerAssignedMemberIds = this.membersList.filter(m => m.trainer_id === trainer.id).map(m => m.id);
            }
            
            this.trainerMemberFilter = 'all';
            this.trainerMemberSearch = '';
            this.showTrainerEditModal = true;
        },
        confirmDelete(id, name) {
            this.trainerToDelete = { id: id, name: name };
            this.showDeleteModal = true;
        },
        matchesFilter(trainer) {
            if (this.statusFilter === 'active' && trainer.status !== 'ACTIVE') return false;
            if (this.statusFilter === 'inactive' && trainer.status === 'ACTIVE') return false;
            if (this.statusFilter === 'featured' && !trainer.is_featured) return false;
            
            if (this.searchQuery && this.searchQuery.trim() !== '') {
                const q = this.searchQuery.toLowerCase().trim();
                const name = (trainer.full_name || (trainer.first_name + ' ' + (trainer.last_name || ''))).toLowerCase();
                const phone = (trainer.phone || '').toLowerCase();
                const email = (trainer.email || '').toLowerCase();
                const spec = (trainer.specialization || '').toLowerCase();
                return name.includes(q) || phone.includes(q) || email.includes(q) || spec.includes(q);
            }
            return true;
        }
    }">

        <!-- ==================== TOP ACTION BAR ==================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <div class="flex items-center gap-2.5">
                    <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight">Fitness Trainers &amp; Coaches</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20">
                        {{ $trainers->count() }} Total
                    </span>
                </div>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage certified coaches, personal trainers, and assigned gym members</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="showTrainerModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center gap-1.5 transition-all shadow-md shadow-indigo-600/25 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Trainer</span>
                </button>

                <a href="{{ route('app.staff.index', ['role' => 'trainer']) }}" class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white font-bold text-xs border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 flex items-center gap-1.5 transition-all shadow-sm">
                    <svg class="w-4 h-4 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    <span>Staff Roster</span>
                </a>
            </div>
        </div>

        <!-- ==================== METRICS KPI STATS BAR ==================== -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <!-- Total Trainers -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Coaches</span>
                    <div class="w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center border border-indigo-200 dark:border-indigo-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $trainers->count() }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Registered trainers</div>
            </div>

            <!-- Active Trainers -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Active Staff</span>
                    <div class="w-8 h-8 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/20">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 dark:bg-emerald-400 animate-pulse"></span>
                    </div>
                </div>
                <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $activeTrainersCount }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Ready for sessions</div>
            </div>

            <!-- Total Assigned Clients -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Assigned Clients</span>
                    <div class="w-8 h-8 rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $totalAssignedMembers }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Members under PT</div>
            </div>

            <!-- Today's Sessions -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm relative overflow-hidden group hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                <div class="flex items-center justify-between mb-1.5">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Today's Sessions</span>
                    <div class="w-8 h-8 rounded-xl bg-purple-50 dark:bg-purple-500/10 text-purple-600 dark:text-purple-400 flex items-center justify-center border border-purple-200 dark:border-purple-500/20">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                </div>
                <div class="text-2xl font-black text-purple-600 dark:text-purple-400 tracking-tight">{{ $totalTodaySessions }}</div>
                <div class="text-[10px] text-slate-500 dark:text-slate-400 font-medium mt-0.5">Scheduled for today</div>
            </div>
        </div>

        <!-- ==================== TOOLBAR: SEARCH & STATUS FILTER ==================== -->
        <div class="p-3.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5 text-xs font-bold">
                <button type="button" @click="statusFilter = 'all'" :class="statusFilter === 'all' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    All ({{ $trainers->count() }})
                </button>
                <button type="button" @click="statusFilter = 'active'" :class="statusFilter === 'active' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    Active ({{ $activeTrainersCount }})
                </button>
                <button type="button" @click="statusFilter = 'featured'" :class="statusFilter === 'featured' ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    ★ Featured ({{ $trainers->where('is_featured', true)->count() }})
                </button>
                <button type="button" @click="statusFilter = 'inactive'" :class="statusFilter === 'inactive' ? 'bg-slate-700 text-white' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-950 dark:text-slate-400 dark:hover:text-white dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl text-xs transition-all cursor-pointer">
                    Inactive ({{ $trainers->where('status', '!=', 'ACTIVE')->count() }})
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <input type="text" x-model="searchQuery" placeholder="Search by name, phone, spec..." class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none transition-all">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- ==================== TRAINER CARDS GRID ==================== -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse($trainers as $trainer)
                <div x-show="matchesFilter({{ Js::from($trainer) }})" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between space-y-3 shadow-sm hover:shadow-md group relative overflow-hidden">
                    @if($trainer->is_featured)
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-amber-500 to-amber-400"></div>
                    @endif

                    <div>
                        <!-- Header: Avatar + Name + Specialization -->
                        <div class="flex items-start gap-3 mb-3">
                            <div class="relative shrink-0">
                                @if($trainer->photo_path)
                                    <img src="{{ Storage::url($trainer->photo_path) }}" alt="{{ $trainer->full_name }}" class="w-11 h-11 rounded-xl object-cover border border-slate-200 dark:border-slate-700 shadow-sm">
                                @else
                                    <div class="w-11 h-11 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-black text-sm flex items-center justify-center border border-indigo-200 dark:border-indigo-500/20">
                                        {{ substr($trainer->first_name, 0, 1) }}{{ substr($trainer->last_name, 0, 1) }}
                                    </div>
                                @endif
                                <span class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 rounded-full {{ $trainer->status === 'ACTIVE' ? 'bg-emerald-500 ring-2 ring-white dark:ring-slate-900' : 'bg-slate-400 ring-2 ring-white dark:ring-slate-900' }}" title="{{ $trainer->status }}"></span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <h4 class="font-bold text-slate-900 dark:text-white text-sm truncate tracking-tight">{{ $trainer->full_name }}</h4>
                                <p class="text-xs text-indigo-600 dark:text-indigo-400 font-medium truncate mt-0.5">{{ $trainer->specialization ?? 'Personal Trainer' }}</p>
                                
                                <div class="flex items-center gap-1.5 mt-1.5">
                                    <a href="{{ route('app.staff.index', ['search' => $trainer->phone]) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20 transition-colors">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                        Staff
                                    </a>
                                    @if($trainer->is_featured)
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-black bg-amber-50 dark:bg-amber-500/15 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                                            ★ Featured
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- 2 Stats Counters -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 mb-3 text-center">
                            <div class="border-r border-slate-200 dark:border-slate-800 pr-1">
                                <div class="text-base font-black text-slate-900 dark:text-white">{{ $trainer->assigned_members_count ?? ($trainer->assignedMembers ? $trainer->assignedMembers->count() : 0) }}</div>
                                <div class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">Assigned</div>
                            </div>
                            <div class="pl-1">
                                <div class="text-base font-black text-slate-900 dark:text-white">{{ $trainer->today_sessions_count ?? 0 }}</div>
                                <div class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mt-0.5">Today</div>
                            </div>
                        </div>

                        <!-- Contact Details -->
                        <div class="space-y-1.5 text-xs text-slate-600 dark:text-slate-400 px-0.5">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <a href="tel:{{ $trainer->phone }}" class="text-slate-800 dark:text-slate-200 hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold truncate transition-colors">{{ $trainer->phone }}</a>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-slate-400 dark:text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                <span class="text-slate-500 dark:text-slate-400 truncate text-[11px]">{{ $trainer->email ?: 'No email registered' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button type="button" @click="openEditTrainer({{ Js::from($trainer) }})" class="flex-1 py-2 px-3 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            <span>Edit &amp; Assign</span>
                        </button>
                        <button type="button" @click="confirmDelete({{ $trainer->id }}, '{{ addslashes($trainer->full_name) }}')" title="Delete Trainer" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-950 hover:bg-rose-50 dark:hover:bg-rose-500/20 text-slate-500 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-800 hover:border-rose-200 dark:hover:border-rose-500/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-10 text-center bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-slate-100 dark:bg-slate-950 flex items-center justify-center mx-auto mb-3 border border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <h4 class="font-bold text-slate-900 dark:text-white text-base">No trainers registered yet</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 max-w-sm mx-auto">Register your certified fitness coaches to start scheduling workouts and assigning gym members.</p>
                    <button type="button" @click="showTrainerModal = true" class="mt-4 px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition-all shadow-md shadow-indigo-600/25 cursor-pointer">
                        + Add Fitness Trainer
                    </button>
                </div>
            @endforelse
        </div>

        <!-- ==================== MODAL 1: ADD TRAINER ==================== -->
        <div x-show="showTrainerModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-2xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 md:p-8 shadow-2xl space-y-6" @click.away="showTrainerModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Add Fitness Trainer</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Register a certified coach or personal trainer</p>
                    </div>
                    <button type="button" @click="showTrainerModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.trainers.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                            <input type="text" name="full_name" required placeholder="e.g. Aditya Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone Number *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                            <input type="email" name="email" placeholder="trainer@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Specialization</label>
                            <input type="text" name="specialization" placeholder="e.g. Weight Training, CrossFit" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Certification</label>
                            <input type="text" name="certification" placeholder="e.g. ACE, NASM, ISSA" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary ({{ $currency }})</label>
                            <input type="number" step="0.01" name="salary" placeholder="0.00" value="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary Type</label>
                            <select name="salary_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="Fixed Monthly">Fixed Monthly</option>
                                <option value="Per Session">Per Session</option>
                                <option value="Commission Based">Commission Based</option>
                                <option value="Hourly">Hourly</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary Pay Day</label>
                            <select name="salary_pay_day" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="1st of every month">1st of every month</option>
                                <option value="5th of every month">5th of every month</option>
                                <option value="10th of every month">10th of every month</option>
                                <option value="15th of every month">15th of every month</option>
                                <option value="Last day of month">Last day of month</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Date of Joining</label>
                            <input type="date" name="joining_date" value="{{ now()->toDateString() }}" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                            <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                                <option value="SUSPENDED">Suspended</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Profile Photo</label>
                            <input type="file" name="photo" accept="image/*" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-xs file:mr-2 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-xs file:bg-indigo-600 file:text-white file:font-bold">
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-center gap-3.5">
                        <input type="checkbox" name="is_featured" id="is_featured_add" value="1" class="rounded border-amber-300 dark:border-amber-500/40 bg-white dark:bg-slate-950 text-amber-500 focus:ring-0">
                        <label for="is_featured_add" class="cursor-pointer">
                            <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">★ Featured Trainer</span>
                            <span class="text-[11px] text-amber-700 dark:text-amber-400/80 block">Featured trainers are highlighted on the public website and client app</span>
                        </label>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showTrainerModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all cursor-pointer">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">Save Trainer</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL 2: EDIT TRAINER & ASSIGN MEMBERS ==================== -->
        <div x-show="showTrainerEditModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-4xl w-full max-h-[90vh] overflow-y-auto p-4 sm:p-6 md:p-8 shadow-2xl space-y-6" @click.away="showTrainerEditModal = false">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white tracking-tight">Edit Trainer &amp; Member Assignments</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Update profile, compensation details, and manage assigned gym members</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <a :href="'{{ url('/app/staff') }}?search=' + (trainerForm.phone || '')" target="_blank" class="px-3.5 py-1.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold flex items-center gap-1.5 transition-colors border border-slate-200 dark:border-slate-700">
                            <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                            <span>Staff Record</span>
                        </a>
                        <button type="button" @click="showTrainerEditModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </div>

                <form :action="'{{ url('/app/trainers') }}/' + trainerForm.id" method="POST" enctype="multipart/form-data" class="space-y-6">
                    @csrf

                    <div class="flex items-center gap-5 p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800">
                        <div class="relative shrink-0">
                            <template x-if="trainerEditPhotoPreview">
                                <img :src="trainerEditPhotoPreview" class="w-16 h-16 rounded-2xl object-cover border-2 border-indigo-500 shadow-md">
                            </template>
                            <template x-if="!trainerEditPhotoPreview && trainerForm.photo_url">
                                <img :src="trainerForm.photo_url" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-200 dark:border-slate-700 shadow-md">
                            </template>
                            <template x-if="!trainerEditPhotoPreview && !trainerForm.photo_url">
                                <div class="w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-black text-xl flex items-center justify-center border-2 border-indigo-200 dark:border-indigo-500/30">
                                    <span x-text="(trainerForm.full_name ? trainerForm.full_name.substring(0, 2).toUpperCase() : 'TR')"></span>
                                </div>
                            </template>
                        </div>
                        <div>
                            <input type="file" id="trainer_photo_edit" name="photo" accept="image/*" class="hidden" @change="previewTrainerPhoto($event)">
                            <label for="trainer_photo_edit" class="cursor-pointer inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-300 dark:border-slate-700 transition-colors shadow-sm">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Change Photo</span>
                            </label>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">JPG, PNG or GIF. Click photo or button to change.</p>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Full Name *</label>
                                <input type="text" name="full_name" x-model="trainerForm.full_name" required placeholder="e.g. Aditya" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Email</label>
                                <input type="email" name="email" x-model="trainerForm.email" placeholder="e.g. aditya@gmail.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Phone *</label>
                                <input type="text" name="phone" x-model="trainerForm.phone" required placeholder="e.g. 9090909090" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Specialization</label>
                                <input type="text" name="specialization" x-model="trainerForm.specialization" placeholder="e.g. Weight Training, Yoga, CrossFit" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Certification</label>
                                <input type="text" name="certification" x-model="trainerForm.certification" placeholder="e.g. ACE, NASM, ISSA" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary ({{ $currency }})</label>
                                <input type="number" step="0.01" name="salary" x-model="trainerForm.salary" placeholder="0.00" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary Type</label>
                                <select name="salary_type" x-model="trainerForm.salary_type" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="Fixed Monthly">Fixed Monthly</option>
                                    <option value="Per Session">Per Session</option>
                                    <option value="Commission Based">Commission Based</option>
                                    <option value="Hourly">Hourly</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Salary Pay Day</label>
                                <select name="salary_pay_day" x-model="trainerForm.salary_pay_day" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="1st of every month">1st of every month</option>
                                    <option value="5th of every month">5th of every month</option>
                                    <option value="10th of every month">10th of every month</option>
                                    <option value="15th of every month">15th of every month</option>
                                    <option value="Last day of month">Last day of month</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Date of Joining</label>
                                <input type="date" name="joining_date" x-model="trainerForm.joining_date" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Status</label>
                                <select name="status" x-model="trainerForm.status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                                    <option value="ACTIVE">Active</option>
                                    <option value="INACTIVE">Inactive</option>
                                    <option value="SUSPENDED">Suspended</option>
                                </select>
                            </div>
                        </div>

                        <div class="p-4 rounded-2xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-center gap-3.5">
                            <input type="checkbox" name="is_featured" id="is_featured_edit" x-model="trainerForm.is_featured" value="1" class="rounded border-amber-300 dark:border-amber-500/40 bg-white dark:bg-slate-950 text-amber-500 focus:ring-0">
                            <label for="is_featured_edit" class="cursor-pointer">
                                <span class="text-xs font-bold text-amber-800 dark:text-amber-300 block">★ Featured Trainer</span>
                                <span class="text-[11px] text-amber-700 dark:text-amber-400/80 block">Featured trainers are highlighted on the public website</span>
                            </label>
                        </div>
                    </div>

                    <!-- ==================== BOTTOM SECTION: ASSIGN MEMBERS ==================== -->
                    <div class="pt-6 border-t border-slate-200 dark:border-slate-800 space-y-4">
                        <input type="hidden" name="assigned_member_ids_submitted" value="1">

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <h4 class="text-sm font-bold text-slate-900 dark:text-white">
                                    Assign Members to <span class="text-indigo-600 dark:text-indigo-400" x-text="trainerForm.full_name"></span>
                                </h4>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">
                                    <span x-text="assignedCount"></span> Assigned
                                </span>
                                <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700">
                                    <span x-text="unassignedCount"></span> Unassigned
                                </span>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800">
                            <div class="flex items-center gap-1.5 text-xs font-bold">
                                <button type="button" @click="trainerMemberFilter = 'all'" :class="trainerMemberFilter === 'all' ? 'bg-indigo-600 text-white shadow' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl transition-colors cursor-pointer">
                                    All Members (<span x-text="membersList.length"></span>)
                                </button>
                                <button type="button" @click="trainerMemberFilter = 'assigned'" :class="trainerMemberFilter === 'assigned' ? 'bg-indigo-600 text-white shadow' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl transition-colors cursor-pointer">
                                    Assigned (<span x-text="assignedCount"></span>)
                                </button>
                                <button type="button" @click="trainerMemberFilter = 'unassigned'" :class="trainerMemberFilter === 'unassigned' ? 'bg-indigo-600 text-white shadow' : 'bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800'" class="px-3.5 py-1.5 rounded-xl transition-colors cursor-pointer">
                                    Unassigned (<span x-text="unassignedCount"></span>)
                                </button>
                            </div>

                            <div class="relative w-full sm:w-64">
                                <input type="text" x-model="trainerMemberSearch" placeholder="Search members..." class="w-full pl-9 pr-3.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            </div>
                        </div>

                        <div class="flex items-center justify-between px-1">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-slate-700 dark:text-slate-300">
                                <input type="checkbox" :checked="isAllVisibleSelected()" @change="toggleAllVisible()" class="rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-950 text-indigo-600 focus:ring-0">
                                <span>Select all on this page</span>
                            </label>
                            <span class="text-[11px] text-slate-500 dark:text-slate-400">Showing <span x-text="filteredMembers.length"></span> members</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-3 max-h-72 overflow-y-auto pr-1">
                            <template x-for="m in filteredMembers" :key="m.id">
                                <div @click="toggleMember(m.id)" :class="isMemberAssigned(m.id) ? 'bg-indigo-50 dark:bg-indigo-950/30 border-indigo-300 dark:border-indigo-500/40 ring-1 ring-indigo-500/30' : 'bg-white dark:bg-slate-950/60 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700'" class="p-3 rounded-2xl border transition-all cursor-pointer flex items-center justify-between gap-3 select-none shadow-sm">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <input type="checkbox" name="assigned_member_ids[]" :value="m.id" :checked="isMemberAssigned(m.id)" @click.stop="toggleMember(m.id)" class="rounded border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-slate-900 text-indigo-600 focus:ring-0 shrink-0">
                                        
                                        <div class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center border border-slate-200 dark:border-slate-700 shrink-0">
                                            <span x-text="((m.first_name || 'M').charAt(0) + (m.last_name || '').charAt(0)).toUpperCase()"></span>
                                        </div>

                                        <div class="min-w-0">
                                            <div class="font-bold text-slate-900 dark:text-white text-xs truncate" x-text="(m.first_name || '') + ' ' + (m.last_name || '')"></div>
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 truncate" x-text="(m.member_code ? m.member_code + ' • ' : '') + (m.phone || 'No phone')"></div>
                                        </div>
                                    </div>

                                    <template x-if="isMemberAssigned(m.id)">
                                        <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                            ✓ Assigned
                                        </span>
                                    </template>
                                </div>
                            </template>

                            <template x-if="filteredMembers.length === 0">
                                <div class="col-span-full py-8 text-center text-slate-500 text-xs">
                                    No members found matching your filter or search.
                                </div>
                            </template>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showTrainerEditModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-all cursor-pointer">Cancel</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-500/20 flex items-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                            <span>Update Trainer &amp; Assignments</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== CUSTOM DELETE CONFIRMATION MODAL ==================== -->
        <div x-show="showDeleteModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/70 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-md w-full max-h-[90vh] overflow-y-auto p-5 sm:p-7 shadow-2xl space-y-5 text-center" @click.away="showDeleteModal = false">
                <div class="w-14 h-14 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/25 text-rose-600 dark:text-rose-400 flex items-center justify-center mx-auto shadow-lg shadow-rose-500/10">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </div>

                <div class="space-y-2">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Delete Trainer Profile</h3>
                    <p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
                        Are you sure you want to remove <span class="font-bold text-slate-900 dark:text-white" x-text="trainerToDelete.name"></span>? Any assigned gym members will be unassigned from this coach.
                    </p>
                </div>

                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" @click="showDeleteModal = false" class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 font-bold text-xs transition-all cursor-pointer">
                        Cancel
                    </button>

                    <form :action="'{{ url('/app/trainers') }}/' + trainerToDelete.id" method="POST" class="flex-1 inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-bold text-xs shadow-lg shadow-rose-600/25 hover:shadow-rose-600/35 transition-all cursor-pointer">
                            Yes, Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>

