<x-app-layout header="Gym Members Management">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $plansJson = $membershipPlans->mapWithKeys(function ($p) {
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'plan_type' => $p->plan_type ?? 'single',
                'max_members' => (int) ($p->max_members ?? 1),
                'price' => (float) $p->price,
                'duration_type' => $p->duration_type,
                'duration_value' => $p->duration_value,
                'tax_rate' => (float) $p->tax_rate,
            ]];
        });

        $totalMembers = $members->total();
        $activeCount = \App\Models\Member::where('status', 'ACTIVE')->count();
        $pendingFeesCount = \App\Models\Membership::where('status', 'ACTIVE')
            ->whereRaw('final_amount > paid_amount')
            ->count();
        $expiringSoonCount = \App\Models\Membership::where('status', 'ACTIVE')
            ->whereBetween('end_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();

        $membersMap = $members->getCollection()->mapWithKeys(function ($m) {
            $meta = is_array($m->metadata) ? $m->metadata : [];
            $membership = $m->activeMembership ?? $m->memberships()->latest()->first();
            return [(string) $m->id => [
                'id' => $m->id,
                'member_code' => (string) ($m->member_code ?? ''),
                'first_name' => (string) ($m->first_name ?? ''),
                'last_name' => (string) ($m->last_name ?? ''),
                'phone' => (string) ($m->phone ?? ''),
                'alternate_phone' => (string) ($meta['alternate_phone'] ?? ''),
                'email' => (string) ($m->email ?? ''),
                'gender' => (string) ($m->gender ?? 'male'),
                'dob' => $m->dob ? $m->dob->format('Y-m-d') : '',
                'blood_group' => (string) ($meta['blood_group'] ?? ''),
                'city' => (string) ($meta['city'] ?? ''),
                'address' => (string) ($m->address ?? ''),
                'height' => (string) ($meta['height'] ?? ''),
                'height_unit' => (string) ($meta['height_unit'] ?? 'cm'),
                'weight' => (string) ($meta['weight'] ?? ''),
                'target_weight' => (string) ($meta['target_weight'] ?? ''),
                'fitness_goal' => (string) ($meta['fitness_goal'] ?? ''),
                'medical_history' => (string) ($meta['medical_history'] ?? ''),
                'emergency_contact_name' => (string) ($m->emergency_contact_name ?? ''),
                'emergency_contact_phone' => (string) ($m->emergency_contact_phone ?? ''),
                'emergency_relation' => (string) ($meta['emergency_relation'] ?? ''),
                'branch_id' => $m->branch_id ?? '',
                'status' => (string) ($m->status ?? 'ACTIVE'),
                'membership_plan_id' => $membership?->membership_plan_id ?? '',
                'notes' => (string) ($m->notes ?? ''),
                'photo_path' => (string) ($m->photo_path ?? ''),
                'membership' => $membership ? [
                    'final_amount' => (float) ($membership->final_amount ?? 0),
                    'paid_amount' => (float) ($membership->paid_amount ?? 0),
                    'plan_name' => $membership->plan?->name ?? 'Active Membership',
                ] : null,
            ]];
        });
    @endphp

    <script>
        window.__MEMBERSHIP_PLANS__ = {{ Js::from($plansJson) }};
        window.__MEMBERS_DATA__ = {{ Js::from($membersMap) }};
    </script>

    <div x-data="{ 
        showAddModal: false, 
        showEditModal: false, 
        showCollectFeeModal: false,
        plans: window.__MEMBERSHIP_PLANS__ || {},
        membersData: window.__MEMBERS_DATA__ || {},
        
        // Add Member Form State
        selectedPlanId: '',
        discountAmount: 0,
        paidNowAmount: 0,
        paymentMethod: 'cash',
        get planPrice() {
            return this.selectedPlanId && this.plans[this.selectedPlanId] ? this.plans[this.selectedPlanId].price : 0;
        },
        get finalFee() {
            return Math.max(0, this.planPrice - Number(this.discountAmount || 0));
        },
        get remainingBalance() {
            return Math.max(0, this.finalFee - Number(this.paidNowAmount || 0));
        },
        onPlanChange() {
            this.paidNowAmount = this.finalFee;
        },

        // Edit Member Form State
        editMember: {
            id: null,
            member_code: '',
            first_name: '',
            last_name: '',
            phone: '',
            alternate_phone: '',
            email: '',
            gender: 'male',
            dob: '',
            blood_group: '',
            city: '',
            address: '',
            height: '',
            height_unit: 'cm',
            weight: '',
            target_weight: '',
            fitness_goal: '',
            medical_history: '',
            emergency_contact_name: '',
            emergency_contact_phone: '',
            emergency_relation: '',
            branch_id: '',
            status: 'ACTIVE',
            membership_plan_id: '',
            notes: '',
            photo_path: ''
        },
        editPhotoPreview: '',
        editCapturedPhotoData: '',
        editRemovePhoto: false,
        editShowCameraModal: false,
        editCameraStream: null,
        editCameraActive: false,
        editCameraError: null,

        openEditModal(memberId) {
            let member = null;
            if (this.membersData) {
                if (Array.isArray(this.membersData)) {
                    member = this.membersData.find(m => m && m.id == memberId);
                } else if (typeof this.membersData === 'object') {
                    member = this.membersData[String(memberId)] || this.membersData[memberId];
                }
            }
            if (!member) {
                member = { id: memberId };
            }
            this.editMember = {
                id: member.id || memberId,
                member_code: member.member_code || '',
                first_name: member.first_name || '',
                last_name: member.last_name || '',
                phone: member.phone || '',
                alternate_phone: member.alternate_phone || '',
                email: member.email || '',
                gender: member.gender || 'male',
                dob: member.dob || '',
                blood_group: member.blood_group || '',
                city: member.city || '',
                address: member.address || '',
                height: member.height || '',
                height_unit: member.height_unit || 'cm',
                weight: member.weight || '',
                target_weight: member.target_weight || '',
                fitness_goal: member.fitness_goal || '',
                medical_history: member.medical_history || '',
                emergency_contact_name: member.emergency_contact_name || '',
                emergency_contact_phone: member.emergency_contact_phone || '',
                emergency_relation: member.emergency_relation || '',
                branch_id: member.branch_id || '',
                status: member.status || 'ACTIVE',
                membership_plan_id: member.membership_plan_id || '',
                notes: member.notes || '',
                photo_path: member.photo_path || ''
            };
            this.editPhotoPreview = member.photo_path ? ('/storage/' + member.photo_path) : '';
            this.editCapturedPhotoData = '';
            this.editRemovePhoto = false;
            this.showEditModal = true;
        },
        triggerEditFileInput() {
            this.$refs.editPhotoInput.click();
        },
        onEditPhotoSelected(e) {
            const file = e.target.files[0];
            if (file) {
                this.editPhotoPreview = URL.createObjectURL(file);
                this.editCapturedPhotoData = '';
                this.editRemovePhoto = false;
            }
        },
        async openEditWebcam() {
            this.editShowCameraModal = true;
            this.editCameraError = null;
            this.editCameraActive = false;
            try {
                this.editCameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
                });
                this.$nextTick(() => {
                    if (this.$refs.editCameraVideo) {
                        this.$refs.editCameraVideo.srcObject = this.editCameraStream;
                        this.$refs.editCameraVideo.play();
                        this.editCameraActive = true;
                    }
                });
            } catch (err) {
                this.editCameraError = 'Camera access denied or unavailable on this device.';
            }
        },
        closeEditWebcam() {
            if (this.editCameraStream) {
                this.editCameraStream.getTracks().forEach(track => track.stop());
                this.editCameraStream = null;
            }
            this.editCameraActive = false;
            this.editShowCameraModal = false;
        },
        takeEditSnapshot() {
            const video = this.$refs.editCameraVideo;
            const canvas = this.$refs.editCameraCanvas;
            if (video && canvas) {
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                const dataUrl = canvas.toDataURL('image/jpeg', 0.9);
                this.editPhotoPreview = dataUrl;
                this.editCapturedPhotoData = dataUrl;
                this.editRemovePhoto = false;
                this.closeEditWebcam();
            }
        },
        removeEditPhoto() {
            this.editPhotoPreview = '';
            this.editCapturedPhotoData = '';
            this.editRemovePhoto = true;
            if (this.$refs.editPhotoInput) {
                this.$refs.editPhotoInput.value = '';
            }
        },

        // Collect Fee Modal State
        feeMember: {
            id: null,
            name: '',
            plan_name: '',
            final_amount: 0,
            paid_amount: 0,
            remaining_balance: 0,
        },
        collectAmount: 0,
        collectMethod: 'cash',
        collectRef: '',
        collectNotes: '',
        openCollectFeeModal(memberId) {
            const member = this.membersData[String(memberId)] || {};
            const membership = member.membership || {};
            const finalAmt = Number(membership.final_amount || 0);
            const paidAmt = Number(membership.paid_amount || 0);
            const remBalance = Math.max(0, finalAmt - paidAmt);
            this.feeMember = {
                id: member.id,
                name: (member.first_name || '') + ' ' + (member.last_name || ''),
                plan_name: membership.plan_name || 'Active Membership',
                final_amount: finalAmt,
                paid_amount: paidAmt,
                remaining_balance: remBalance,
            };
            this.collectAmount = remBalance > 0 ? remBalance : '';
            this.collectMethod = 'upi';
            this.collectRef = '';
            this.collectNotes = '';
            this.showCollectFeeModal = true;
        }
    }" class="space-y-6">

        <!-- Top Metrics Header -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium">Total Enrolled</span>
                    <p class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-0.5 sm:mt-1">{{ $totalMembers }}</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-500/20 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium">Active Members</span>
                    <p class="text-xl sm:text-2xl font-black text-emerald-600 dark:text-emerald-400 mt-0.5 sm:mt-1">{{ $activeCount }}</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            <a href="{{ route('app.members.index', ['filter' => 'due']) }}" 
               class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between hover:border-amber-500/40 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all group cursor-pointer shadow-sm">
                <div>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium">Remaining Due</span>
                    <p class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-0.5 sm:mt-1">{{ $pendingFeesCount }} <span class="text-[10px] sm:text-xs font-normal text-slate-500 dark:text-slate-400">due</span></p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-500/20 group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" 
               class="p-3 sm:p-4 rounded-xl sm:rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between hover:border-rose-500/40 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-all group cursor-pointer shadow-sm">
                <div>
                    <span class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 font-medium">Expiring Soon</span>
                    <p class="text-xl sm:text-2xl font-black text-rose-600 dark:text-rose-400 mt-0.5 sm:mt-1">{{ $expiringSoonCount }} <span class="text-[10px] sm:text-xs font-normal text-slate-500 dark:text-slate-400">in 7d</span></p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-2xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200 dark:border-rose-500/20 group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </a>
        </div>

        <!-- Quick Filter Status Tabs -->
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 text-xs font-bold no-scrollbar -mx-3 px-3 sm:mx-0 sm:px-0">
            <a href="{{ route('app.members.index') }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ !request('status') && !request('filter') ? 'bg-indigo-600 text-white border-indigo-500 shadow-md shadow-indigo-600/20' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm' }}">
                <span>All Members</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('status') && !request('filter') ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $totalMembers }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'ACTIVE']) }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ request('status') === 'ACTIVE' && !request('filter') ? 'bg-emerald-500 text-slate-950 border-emerald-400 font-extrabold shadow-md shadow-emerald-500/20' : 'bg-white dark:bg-slate-900 text-emerald-600 dark:text-emerald-400 border-slate-200 dark:border-slate-800 hover:bg-emerald-50 dark:hover:bg-emerald-500/10 shadow-sm' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                <span>Active</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('status') === 'ACTIVE' && !request('filter') ? 'bg-slate-950/30 text-slate-950 font-black' : 'bg-emerald-100 dark:bg-emerald-500/20 text-emerald-800 dark:text-emerald-300' }}">{{ $activeCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'due']) }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ request('filter') === 'due' ? 'bg-amber-500 text-slate-950 border-amber-400 font-extrabold shadow-md shadow-amber-500/20' : 'bg-white dark:bg-slate-900 text-amber-600 dark:text-amber-400 border-slate-200 dark:border-slate-800 hover:bg-amber-50 dark:hover:bg-amber-500/10 shadow-sm' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span>Payment Due</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('filter') === 'due' ? 'bg-slate-950/30 text-slate-950 font-black' : 'bg-amber-100 dark:bg-amber-500/20 text-amber-800 dark:text-amber-300' }}">{{ $pendingFeesCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'EXPIRED']) }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ request('status') === 'EXPIRED' ? 'bg-rose-500 text-white border-rose-400 font-extrabold shadow-md shadow-rose-500/20' : 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 shadow-sm' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>Expired</span>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ request('filter') === 'expiring' ? 'bg-rose-500 text-white border-rose-400 font-extrabold shadow-md shadow-rose-500/20' : 'bg-white dark:bg-slate-900 text-rose-600 dark:text-rose-400 border-slate-200 dark:border-slate-800 hover:bg-rose-50 dark:hover:bg-rose-500/10 shadow-sm' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>Expiring Soon</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ request('filter') === 'expiring' ? 'bg-white/20 text-white' : 'bg-rose-100 dark:bg-rose-500/20 text-rose-800 dark:text-rose-300' }}">{{ $expiringSoonCount }}</span>
            </a>

            <a href="{{ route('app.members.index', ['status' => 'INACTIVE']) }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ request('status') === 'INACTIVE' ? 'bg-slate-700 text-white border-slate-600 font-extrabold shadow-md' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm' }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                <span>Inactive</span>
            </a>
        </div>

        <!-- Action Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3">
            <form action="{{ route('app.members.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 flex-grow max-w-3xl">
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                <div class="relative flex-grow">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, phone, email, code..." class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                    <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                
                <div class="flex items-center gap-2">
                    <select name="status" class="flex-1 sm:flex-none px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none shadow-sm">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                        <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                        <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer shadow-sm shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Search</span>
                    </button>
                    @if(request('status') || request('filter') || request('search'))
                        <a href="{{ route('app.members.index') }}" class="text-xs text-rose-500 dark:text-rose-400 hover:underline shrink-0 ml-1">Clear</a>
                    @endif
                </div>
            </form>

            <a href="{{ route('app.members.create') }}" class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs flex items-center gap-2 shadow-lg shadow-amber-500/20 transition-all shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Enroll New Member</span>
            </a>
        </div>

        <!-- Members List: Mobile Cards (block md:hidden) & Desktop Table (hidden md:block) -->
        <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden">
            
            <!-- Mobile Cards View -->
            <div class="block md:hidden divide-y divide-slate-100 dark:divide-slate-800/60">
                @forelse($members as $member)
                    @php
                        $membership = $member->activeMembership ?? $member->memberships()->latest()->first();
                        $finalAmt = (float) ($membership?->final_amount ?? 0);
                        $paidAmt = (float) ($membership?->paid_amount ?? 0);
                        $remainingAmt = max(0, $finalAmt - $paidAmt);
                        $daysLeft = $membership ? (int) now()->startOfDay()->diffInDays($membership->end_date->startOfDay(), false) : null;
                        $statusBadge = match($member->status) {
                            'ACTIVE' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                            'SUSPENDED' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                            'EXPIRED' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                            default => 'bg-slate-100 dark:bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                        };
                    @endphp
                    <div class="p-4 space-y-3">
                        <!-- Top Row: Member Header + Status -->
                        <div class="flex items-start justify-between gap-2">
                            <a href="{{ route('app.members.show', $member->id) }}" class="flex items-center gap-2.5 min-w-0 group">
                                <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-500/10 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center font-black text-amber-600 dark:text-amber-400 text-sm shadow-sm shrink-0">
                                    {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <div class="font-bold text-slate-900 dark:text-white text-sm truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                        {{ $member->full_name }}
                                    </div>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span class="text-[11px] text-amber-600 dark:text-amber-400 font-mono font-bold">{{ $member->member_code }}</span>
                                        <span class="text-slate-300 dark:text-slate-700">•</span>
                                        <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $member->branch?->name ?? 'Main Gym' }}</span>
                                    </div>
                                </div>
                            </a>

                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold border {{ $statusBadge }} shrink-0">
                                {{ $member->status }}
                            </span>
                        </div>

                        <!-- Info Grid -->
                        <div class="grid grid-cols-2 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800/80 text-xs">
                            <!-- Contact -->
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-medium">Contact</span>
                                <div class="flex items-center gap-1.5">
                                    <a href="tel:{{ $member->phone }}" class="font-semibold text-slate-900 dark:text-white hover:text-amber-600 dark:hover:text-amber-400">
                                        {{ $member->phone }}
                                    </a>
                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" title="WhatsApp" class="text-emerald-600 dark:text-emerald-400">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    </a>
                                </div>
                            </div>

                            <!-- Plan & Expiry -->
                            <div class="space-y-0.5">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-medium">Plan</span>
                                @if($membership)
                                    <div class="font-bold text-slate-900 dark:text-white truncate">
                                        {{ $membership->plan?->name ?? 'Active Plan' }}
                                    </div>
                                    <div class="text-[10px] {{ $daysLeft !== null && $daysLeft < 7 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-slate-500 dark:text-slate-400' }}">
                                        @if($daysLeft !== null && $daysLeft >= 0)
                                            {{ $daysLeft }}d left
                                        @elseif($daysLeft !== null && $daysLeft < 0)
                                            Expired
                                        @else
                                            No end date
                                        @endif
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] italic">No active plan</span>
                                @endif
                            </div>

                            <!-- Fee & Due Status (Spans 2 cols) -->
                            <div class="col-span-2 pt-1 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-medium">Fee Balance</span>
                                @if($membership)
                                    @if($remainingAmt > 0)
                                        <div class="flex items-center gap-1.5">
                                            <span class="text-xs font-black text-amber-600 dark:text-amber-400">{{ $currency }}{{ number_format($remainingAmt, 0) }} Due</span>
                                            <span class="text-[10px] text-slate-400 font-mono">({{ $currency }}{{ number_format($paidAmt, 0) }}/{{ number_format($finalAmt, 0) }})</span>
                                        </div>
                                    @else
                                        <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                            Paid in Full ({{ $currency }}{{ number_format($finalAmt, 0) }})
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400 text-[11px]">—</span>
                                @endif
                            </div>
                        </div>

                        <!-- Actions Bar -->
                        <div class="flex items-center gap-2 pt-1">
                            @if($membership && $remainingAmt > 0)
                                <button type="button" @click="openCollectFeeModal({{ $member->id }})" class="flex-1 py-2 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/20 transition-all cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                    <span>Collect {{ $currency }}{{ number_format($remainingAmt, 0) }}</span>
                                </button>
                            @endif

                            <a href="{{ route('app.members.show', $member->id) }}" class="{{ ($membership && $remainingAmt > 0) ? 'px-3' : 'flex-1' }} py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <span>Profile</span>
                            </a>

                            <button type="button" @click="openEditModal({{ $member->id }})" class="p-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 shadow-sm transition-colors cursor-pointer" title="Edit Member">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>

                            <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to remove member '{{ addslashes($member->full_name) }}'?" 
                                  data-confirm-title="Remove Gym Member" 
                                  data-confirm-btn="Yes, Delete" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 shadow-sm transition-colors cursor-pointer" title="Delete Member">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-slate-500 dark:text-slate-400 space-y-3">
                        <p class="text-sm">No gym members found matching your search or filters.</p>
                        <a href="{{ route('app.members.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/20">Enroll First Member</a>
                    </div>
                @endforelse
            </div>

            <!-- Desktop Table View (hidden md:block) -->
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left text-xs min-w-[750px]">
                    <thead class="bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-3.5 font-semibold">Member</th>
                            <th class="py-3 px-3.5 font-semibold">Contact</th>
                            <th class="py-3 px-3.5 font-semibold">Branch</th>
                            <th class="py-3 px-3.5 font-semibold">Plan & Validity</th>
                            <th class="py-3 px-3.5 font-semibold">Fee & Balance</th>
                            <th class="py-3 px-2.5 font-semibold text-center">Status</th>
                            <th class="py-3 px-3.5 font-semibold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                        @forelse($members as $member)
                            @php
                                $membership = $member->activeMembership ?? $member->memberships()->latest()->first();
                                $finalAmt = (float) ($membership?->final_amount ?? 0);
                                $paidAmt = (float) ($membership?->paid_amount ?? 0);
                                $remainingAmt = max(0, $finalAmt - $paidAmt);
                                $daysLeft = $membership ? (int) now()->startOfDay()->diffInDays($membership->end_date->startOfDay(), false) : null;
                            @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                <!-- Member info with ID and Joined cleanly stacked -->
                                <td class="py-3 px-3.5">
                                    <a href="{{ route('app.members.show', $member->id) }}" class="flex items-center gap-2.5 group">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-gradient-to-tr dark:from-amber-500/20 dark:to-orange-500/20 border border-amber-200 dark:border-amber-500/30 flex items-center justify-center font-bold text-amber-600 dark:text-amber-400 text-xs shadow-sm shrink-0 group-hover:scale-105 transition-transform">
                                            {{ strtoupper(substr($member->first_name, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs block leading-tight truncate group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">{{ $member->full_name }}</span>
                                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-mono font-medium block leading-tight mt-0.5">{{ $member->member_code }}</span>
                                            <span class="text-[9px] text-slate-500 dark:text-slate-400 block leading-tight mt-0.5">Joined: {{ $member->join_date ? $member->join_date->format('d M Y') : 'N/A' }}</span>
                                        </div>
                                    </a>
                                </td>

                                <!-- Contact -->
                                <td class="py-3 px-3.5">
                                    <div class="font-medium text-slate-900 dark:text-white flex items-center gap-1.5 leading-tight text-xs">
                                        <span>{{ $member->phone }}</span>
                                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" title="Send WhatsApp Message" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 dark:hover:text-emerald-300">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        </a>
                                    </div>
                                    <div class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5 truncate max-w-[150px]">{{ $member->email ?? 'No email' }}</div>
                                </td>

                                <!-- Branch -->
                                <td class="py-3 px-3 text-slate-600 dark:text-slate-400">
                                    <span class="truncate block max-w-[120px]">{{ $member->branch?->name ?? 'Main Gym' }}</span>
                                </td>

                                <!-- Plan & Validity -->
                                <td class="py-3 px-3.5">
                                    @if($membership)
                                        <div class="font-bold text-slate-900 dark:text-white leading-tight flex items-center gap-1.5">
                                            <span>{{ $membership->plan?->name ?? 'Custom Plan' }}</span>
                                            @if($daysLeft !== null)
                                                @if($daysLeft > 7)
                                                    <span class="px-1.5 py-0.2 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 text-[9px] font-bold border border-emerald-200 dark:border-emerald-500/20">{{ $daysLeft }}d left</span>
                                                @elseif($daysLeft >= 0)
                                                    <span class="px-1.5 py-0.2 rounded bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 text-[9px] font-bold border border-amber-200 dark:border-amber-500/20">{{ $daysLeft }}d left</span>
                                                @else
                                                    <span class="px-1.5 py-0.2 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 text-[9px] font-bold border border-rose-200 dark:border-rose-500/20">Expired</span>
                                                @endif
                                            @endif
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 leading-tight mt-0.5 font-mono">
                                            {{ $membership->start_date ? $membership->start_date->format('d M') : '—' }} to {{ $membership->end_date ? $membership->end_date->format('d M Y') : '—' }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">No active package</span>
                                    @endif
                                </td>

                                <!-- Fee & Balance with Live Indicators -->
                                <td class="py-3 px-3.5">
                                    @if($membership)
                                        <div class="font-semibold text-slate-900 dark:text-white leading-tight text-xs">
                                            {{ $currency }}{{ number_format($paidAmt, 0) }} <span class="text-[10px] text-slate-400 font-normal">/ {{ $currency }}{{ number_format($finalAmt, 0) }}</span>
                                        </div>
                                        @if($remainingAmt > 0)
                                            <div class="text-[10px] text-amber-600 dark:text-amber-400 font-black leading-tight mt-0.5 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                <span>{{ $currency }}{{ number_format($remainingAmt, 0) }} Due</span>
                                            </div>
                                        @else
                                            <div class="text-[10px] text-emerald-600 dark:text-emerald-400 font-bold leading-tight mt-0.5">
                                                ✓ Paid Full
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">—</span>
                                    @endif
                                </td>

                                <!-- Status -->
                                <td class="py-3 px-2.5 text-center">
                                    @php
                                        $statusBadge = match($member->status) {
                                            'ACTIVE' => 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-500/20',
                                            'SUSPENDED' => 'bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 border-rose-200 dark:border-rose-500/20',
                                            'EXPIRED' => 'bg-orange-50 dark:bg-orange-500/10 text-orange-700 dark:text-orange-400 border-orange-200 dark:border-orange-500/20',
                                            default => 'bg-slate-100 dark:bg-slate-500/10 text-slate-700 dark:text-slate-400 border-slate-200 dark:border-slate-500/20',
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded-full text-[9px] font-extrabold border {{ $statusBadge }} inline-block">
                                        {{ $member->status }}
                                    </span>
                                </td>

                                <!-- Actions Column -->
                                <td class="py-3 px-3.5 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        @if($membership && $remainingAmt > 0)
                                            <button type="button" @click="openCollectFeeModal({{ $member->id }})" title="Collect Remaining Fees" class="py-1 px-2.5 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 border border-amber-200 dark:bg-amber-500/10 dark:hover:bg-amber-500/20 dark:text-amber-400 dark:border-amber-500/30 transition-all flex items-center gap-1 text-[11px] font-bold shadow-sm shrink-0 cursor-pointer">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                                <span>Collect {{ $currency }}{{ number_format($remainingAmt, 0) }}</span>
                                            </button>
                                        @endif

                                        <a href="{{ route('app.members.show', $member->id) }}" title="View Member Profile" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white dark:border-slate-700/50 transition-colors shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        </a>

                                        <button type="button" @click="openEditModal({{ $member->id }})" title="Edit Member" class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white dark:border-slate-700/50 transition-colors shadow-sm cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>

                                        <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                                              data-confirm="Are you sure you want to remove member '{{ addslashes($member->full_name) }}'? This will permanently delete their profile and membership history." 
                                              data-confirm-title="Remove Gym Member" 
                                              data-confirm-btn="Yes, Delete Member" 
                                              data-confirm-type="danger" 
                                              class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" title="Delete Member" class="p-1.5 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-500 hover:text-rose-600 border border-slate-200 dark:bg-slate-800 dark:hover:bg-rose-500/20 dark:text-slate-400 dark:hover:text-rose-400 dark:border-slate-700/50 transition-colors cursor-pointer shadow-sm">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-500 dark:text-slate-400">
                                    <p class="text-sm">No gym members found matching your search or filters.</p>
                                    <a href="{{ route('app.members.create') }}" class="mt-3 inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-500 text-slate-950 font-bold text-xs shadow-md shadow-amber-500/20">Enroll First Member</a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($members->hasPages())
                <div class="p-3 sm:p-4 border-t border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/40">
                    {{ $members->links() }}
                </div>
            @endif
        </div>

        <!-- 1. ENROLL NEW MEMBER MODAL (With Live Fee & Remaining Balance Calculator) -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-2xl w-full p-4 sm:p-6 shadow-2xl space-y-5 text-slate-900 dark:text-slate-200 max-h-[90vh] overflow-y-auto" @click.away="showAddModal = false">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Enroll New Gym Member</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Add member details, choose package, and track fee payment</p>
                    </div>
                    <button @click="showAddModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">✕</button>
                </div>

                <form action="{{ route('app.members.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">First Name *</label>
                            <input type="text" name="first_name" required placeholder="e.g. Rahul" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Last Name *</label>
                            <input type="text" name="last_name" required placeholder="e.g. Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Gender</label>
                            <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Date of Birth</label>
                            <input type="date" name="dob" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Assigned Branch</label>
                            <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Membership Package & Billing Box -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">Membership Plan & Fee Setup</label>
                            <a href="{{ route('app.memberships.index') }}" class="text-[11px] text-slate-500 dark:text-slate-400 hover:text-amber-600 dark:hover:text-amber-400 font-medium">+ Manage Packages ↗</a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Select Package / Plan</label>
                                <select name="membership_plan_id" x-model="selectedPlanId" @change="onPlanChange()" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    <option value="">-- No plan initially (Assign Later) --</option>
                                    @foreach($membershipPlans as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->name }} — {{ $currency }}{{ number_format($mp->price, 2) }} ({{ $mp->duration_value }} {{ $mp->duration_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-slate-700 dark:text-slate-300 mb-1">Membership Start Date</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <template x-if="selectedPlanId">
                            <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-[11px] text-slate-600 dark:text-slate-400 mb-1">Package Price</label>
                                        <div class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold font-mono shadow-sm">
                                            {{ $currency }}<span x-text="planPrice.toFixed(2)"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-600 dark:text-slate-400 mb-1">Discount ({{ $currency }})</label>
                                        <input type="number" step="0.01" name="discount" x-model="discountAmount" @input="onPlanChange()" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-600 dark:text-slate-400 mb-1">Final Payable Fee</label>
                                        <div class="px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-emerald-600 dark:text-emerald-400 text-xs font-bold font-mono shadow-sm">
                                            {{ $currency }}<span x-text="finalFee.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">Initial Payment Paid Now ({{ $currency }}) *</label>
                                        <input type="number" step="0.01" name="initial_payment_amount" x-model="paidNowAmount" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-amber-500/50 text-slate-900 dark:text-white text-xs font-bold focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <!-- Remaining Fees Live Preview -->
                                    <div>
                                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Remaining / Due Balance</label>
                                        <div class="px-3 py-2 rounded-xl border text-xs font-extrabold flex items-center justify-between shadow-sm" :class="remainingBalance > 0 ? 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400'">
                                            <span>{{ $currency }}<span x-text="remainingBalance.toFixed(2)"></span></span>
                                            <span class="text-[10px]" x-text="remainingBalance > 0 ? 'PENDING DUE' : 'FULLY PAID'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-[11px] text-slate-600 dark:text-slate-400 mb-1">Payment Method</label>
                                        <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                            <option value="cash">💵 Cash Payment</option>
                                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                                            <option value="card">💳 Credit / Debit Card</option>
                                            <option value="netbanking">🏦 Net Banking</option>
                                            <option value="cheque">📄 Cheque</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-[11px] text-slate-600 dark:text-slate-400 mb-1">Transaction Ref / Note</label>
                                        <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Cash receipt" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Address / Locality</label>
                            <input type="text" name="address" placeholder="e.g. Ring Road, Hathijan" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" placeholder="Emergency phone" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-lg shadow-amber-500/20 transition-all cursor-pointer">Enroll & Save Member</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- 2. EDIT MEMBER MODAL (Matching Add Member UI & Photo Management) -->
        <div x-show="showEditModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4" 
             x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-4xl w-full p-4 sm:p-8 shadow-2xl space-y-6 my-4 sm:my-8 max-h-[92vh] overflow-y-auto text-slate-900 dark:text-slate-200" @click.away="showEditModal = false">
                
                <!-- Modal Top Bar -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold border border-indigo-200 dark:border-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Member Details</h3>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold border border-slate-200 dark:border-slate-700" x-text="editMember.member_code"></span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Update personal details, profile picture, body metrics, and membership status</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
                </div>

                <form :action="'/app/members/' + editMember.id" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
                    @csrf
                    <input type="hidden" name="photo_data" :value="editCapturedPhotoData">
                    <input type="hidden" name="remove_photo" :value="editRemovePhoto ? '1' : '0'">

                    <!-- 1. PROFILE PHOTO MANAGEMENT CARD -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2.5 text-slate-900 dark:text-slate-200 font-extrabold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Profile Photo</span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="relative group shrink-0">
                                <div class="w-24 h-24 rounded-2xl bg-slate-100 dark:bg-slate-800 border-2 border-indigo-500/30 overflow-hidden flex items-center justify-center shadow-lg relative">
                                    <template x-if="editPhotoPreview">
                                        <img :src="editPhotoPreview" alt="Profile preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!editPhotoPreview">
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-slate-100 dark:bg-gradient-to-br dark:from-slate-800 dark:to-slate-900 text-slate-400 dark:text-slate-500">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="text-[9px] font-bold mt-1 text-slate-400 dark:text-slate-500">No Photo</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center space-y-2 text-center sm:text-left grow">
                                <span class="text-xs font-bold text-slate-800 dark:text-slate-300">Upload or capture a new member photo</span>
                                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                                    <!-- Hidden File Input -->
                                    <input type="file" 
                                           name="photo" 
                                           x-ref="editPhotoInput" 
                                           accept="image/png,image/jpeg,image/webp,image/gif" 
                                           class="hidden" 
                                           @change="onEditPhotoSelected($event)">

                                    <button type="button" 
                                            @click="triggerEditFileInput()"
                                            class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-white dark:border-slate-700 text-xs font-bold flex items-center gap-2 transition-all cursor-pointer shadow-sm">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Upload Photo</span>
                                    </button>

                                    <button type="button" 
                                            @click="openEditWebcam()"
                                            class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-600/20 dark:hover:bg-indigo-600/30 dark:text-indigo-300 dark:border-indigo-500/30 text-xs font-bold flex items-center gap-2 transition-all cursor-pointer shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Capture Camera</span>
                                    </button>

                                    <template x-if="editPhotoPreview">
                                        <button type="button" 
                                                @click="removeEditPhoto()"
                                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 text-xs font-bold transition-all cursor-pointer shadow-sm">
                                            Remove Photo
                                        </button>
                                    </template>
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Supported formats: JPG, PNG, WEBP, GIF. Max file size: 5MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. PERSONAL INFORMATION SECTION -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2.5 text-slate-900 dark:text-slate-200 font-extrabold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Personal Information</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" x-model="editMember.first_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" x-model="editMember.last_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">+91</span>
                                    <input type="tel" name="phone" x-model="editMember.phone" required class="w-full pl-11 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" x-model="editMember.alternate_phone" placeholder="Optional secondary contact" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                <input type="email" name="email" x-model="editMember.email" placeholder="member@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Gender</label>
                                <select name="gender" x-model="editMember.gender" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Date of Birth</label>
                                <input type="date" name="dob" x-model="editMember.dob" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Blood Group</label>
                                <select name="blood_group" x-model="editMember.blood_group" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}">{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                                <input type="text" name="city" x-model="editMember.city" placeholder="e.g. Ahmedabad" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Full Address</label>
                                <input type="text" name="address" x-model="editMember.address" placeholder="Street address, apartment, locality" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 3. PHYSICAL & FITNESS METRICS SECTION -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2.5 text-slate-900 dark:text-slate-200 font-extrabold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Physical & Fitness Metrics</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Height with Unit Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="font-bold text-slate-700 dark:text-slate-300">Height</label>
                                    <div class="flex rounded-lg overflow-hidden border border-slate-300 dark:border-slate-700 p-0.5 bg-slate-100 dark:bg-slate-900">
                                        <button type="button" 
                                                @click="editMember.height_unit = 'cm'" 
                                                :class="editMember.height_unit === 'cm' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-[10px] font-bold rounded transition-colors">cm</button>
                                        <button type="button" 
                                                @click="editMember.height_unit = 'ft'" 
                                                :class="editMember.height_unit === 'ft' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-[10px] font-bold rounded transition-colors">ft</button>
                                    </div>
                                    <input type="hidden" name="height_unit" :value="editMember.height_unit">
                                </div>
                                <input type="text" 
                                       name="height" 
                                       x-model="editMember.height" 
                                       :placeholder="editMember.height_unit === 'cm' ? 'e.g. 175' : 'e.g. 5.9'" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Current Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" x-model="editMember.weight" placeholder="e.g. 72.5" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Target Weight (kg)</label>
                                <input type="number" step="0.1" name="target_weight" x-model="editMember.target_weight" placeholder="e.g. 68.0" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Primary Fitness Goal</label>
                                <select name="fitness_goal" x-model="editMember.fitness_goal" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">Select Primary Goal</option>
                                    <option value="Weight Loss">🔥 Weight Loss & Fat Burn</option>
                                    <option value="Muscle Building">💪 Muscle Building & Hypertrophy</option>
                                    <option value="General Fitness">⚡ General Fitness & Stamina</option>
                                    <option value="Endurance Training">🏃 Endurance & Cardiovascular</option>
                                    <option value="Rehabilitation">🩹 Injury Rehab / Physical Therapy</option>
                                    <option value="Athletic Performance">🏆 Athletic Performance & Sports</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Medical History / Health Notes</label>
                                <input type="text" name="medical_history" x-model="editMember.medical_history" placeholder="e.g. Asthma, Knee surgery, High BP" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 4. EMERGENCY CONTACT DETAILS -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2.5 text-slate-900 dark:text-slate-200 font-extrabold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Emergency Contact Information</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Contact Person Name</label>
                                <input type="text" name="emergency_contact_name" x-model="editMember.emergency_contact_name" placeholder="e.g. Robert Doe" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Emergency Phone Number</label>
                                <input type="tel" name="emergency_contact_phone" x-model="editMember.emergency_contact_phone" placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Relationship</label>
                                <input type="text" name="emergency_relation" x-model="editMember.emergency_relation" placeholder="e.g. Spouse / Parent / Sibling" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>
                    </div>

                    <!-- 5. ACCOUNT STATUS & SYSTEM SETTINGS -->
                    <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-4">
                        <div class="flex items-center gap-2.5 text-slate-900 dark:text-slate-200 font-extrabold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Membership & Account Status</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Member Status <span class="text-rose-500">*</span></label>
                                <select name="status" x-model="editMember.status" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none font-bold shadow-sm">
                                    <option value="ACTIVE" class="text-emerald-600 dark:text-emerald-400">🟢 Active</option>
                                    <option value="INACTIVE" class="text-slate-500 dark:text-slate-400">⚪ Inactive</option>
                                    <option value="SUSPENDED" class="text-rose-600 dark:text-rose-400">🔴 Suspended / Frozen</option>
                                    <option value="EXPIRED" class="text-orange-600 dark:text-orange-400">🟠 Expired</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Branch</label>
                                <select name="branch_id" x-model="editMember.branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Package Plan</label>
                                <select name="membership_plan_id" x-model="editMember.membership_plan_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">-- Keep Current Plan --</option>
                                    @foreach($membershipPlans as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->name }} ({{ $currency }}{{ number_format($mp->price, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Internal Notes</label>
                            <textarea name="notes" x-model="editMember.notes" rows="2" placeholder="Optional notes regarding medical history or special arrangements" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-7 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save Changes</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- EMBEDDED CAMERA SNAPSHOT MODAL FOR EDIT (IN MEMBERS LIST) -->
        <div x-show="editShowCameraModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/70 dark:bg-black/85 backdrop-blur-md" 
             style="display: none;">
            <div @click.away="closeEditWebcam()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 text-slate-900 dark:text-white">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Take Live Member Photo</span>
                    </h3>
                    <button type="button" @click="closeEditWebcam()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <template x-if="editCameraError">
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs">
                        <span x-text="editCameraError"></span>
                    </div>
                </template>

                <div class="relative rounded-2xl overflow-hidden bg-black aspect-video flex items-center justify-center border border-slate-200 dark:border-slate-800 shadow-inner">
                    <video x-ref="editCameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas x-ref="editCameraCanvas" class="hidden"></canvas>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="closeEditWebcam()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white dark:border-slate-700 text-xs font-bold transition-all">
                        Cancel
                    </button>
                    <button type="button" @click="takeEditSnapshot()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Capture Photo</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. COLLECT REMAINING FEES MODAL -->
        <div x-show="showCollectFeeModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-lg w-full p-4 sm:p-6 shadow-2xl space-y-5 text-slate-900 dark:text-slate-200 max-h-[90vh] overflow-y-auto" @click.away="showCollectFeeModal = false">
                <div class="flex justify-between items-center border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-white">Collect Fee Payment</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Receive remaining balance or instalment payment</p>
                    </div>
                    <button @click="showCollectFeeModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-lg">✕</button>
                </div>

                <!-- Summary Box -->
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Member:</span>
                        <span class="font-bold text-slate-900 dark:text-white" x-text="feeMember.name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Plan:</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400" x-text="feeMember.plan_name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-200 dark:border-slate-800">
                        <span class="text-slate-500 dark:text-slate-400">Total Fee:</span>
                        <span class="font-mono text-slate-700 dark:text-slate-300">{{ $currency }}<span x-text="feeMember.final_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-slate-500 dark:text-slate-400">Already Paid:</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $currency }}<span x-text="feeMember.paid_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-slate-200 dark:border-slate-800">
                        <span class="font-bold text-amber-600 dark:text-amber-400">Remaining Balance:</span>
                        <span class="font-mono font-black text-amber-600 dark:text-amber-400 text-sm">{{ $currency }}<span x-text="feeMember.remaining_balance.toFixed(2)"></span></span>
                    </div>
                </div>

                <form :action="'/app/members/' + feeMember.id + '/collect-fee'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-amber-600 dark:text-amber-400 mb-1">Amount to Collect ({{ $currency }}) *</label>
                        <input type="number" step="0.01" name="amount" x-model="collectAmount" required min="0.01" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-950 border border-amber-500/60 text-slate-900 dark:text-white font-mono font-bold text-sm focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="collectMethod" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                            <option value="cash">💵 Cash Payment</option>
                            <option value="card">💳 Credit / Debit Card</option>
                            <option value="netbanking">🏦 Net Banking</option>
                            <option value="cheque">📄 Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Transaction Reference / UTR Number</label>
                        <input type="text" name="transaction_reference" x-model="collectRef" placeholder="e.g. UPI-123456789" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Payment Notes / Receipt Remarks</label>
                        <input type="text" name="notes" x-model="collectNotes" placeholder="e.g. Second instalment remaining fee cleared" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showCollectFeeModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs shadow-lg shadow-emerald-500/20 flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Record Payment Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
