<x-app-layout header="Gym Members Management">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $membershipPlans = ($membershipPlans ?? collect())->filter(fn ($p) => (bool) $p->is_active);
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

    @endphp

    <script>
        window.__MEMBERSHIP_PLANS__ = {{ Js::from($plansJson) }};
    </script>

    <div x-data="{ 
        showAddModal: false, 
        showEditModal: false, 
        showCollectFeeModal: false,
        plans: window.__MEMBERSHIP_PLANS__ || {},
        membersData: JSON.parse(document.getElementById('member-results-data').textContent),
        
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
                join_date: member.join_date || '',
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
                membership_start_date: member.membership_start_date || '',
                membership_end_date: member.membership_end_date || '',
                notes: member.notes || '',
                photo_path: member.photo_path || ''
            };
            this.editPhotoPreview = member.photo_path ? (member.photo_path.startsWith('http') ? member.photo_path : ('/storage/' + member.photo_path)) : '';
            this.editCapturedPhotoData = '';
            this.editRemovePhoto = false;
            this.showEditModal = true;
        },
        onEditPlanChange() {
            if (!this.editMember.membership_plan_id) return;
            const plans = window.__MEMBERSHIP_PLANS__ || {};
            const plan = plans[this.editMember.membership_plan_id];
            if (!plan) return;

            if (!this.editMember.membership_start_date) {
                this.editMember.membership_start_date = new Date().toISOString().split('T')[0];
            }

            const start = new Date(this.editMember.membership_start_date);
            if (isNaN(start.getTime())) return;

            const end = new Date(start);
            const val = parseInt(plan.duration_value) || 1;
            const type = (plan.duration_type || 'months').toLowerCase();

            if (type === 'days') {
                end.setDate(end.getDate() + val);
            } else if (type === 'years') {
                end.setFullYear(end.getFullYear() + val);
            } else {
                end.setMonth(end.getMonth() + val);
            }

            this.editMember.membership_end_date = end.toISOString().split('T')[0];
        },
        triggerEditFileInput() {
            this.$refs.editPhotoInput.click();
        },
        async compressClientSide(file, maxDim = 1200, quality = 0.85) {
            return new Promise((resolve) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let w = img.width;
                        let h = img.height;
                        if (w > maxDim || h > maxDim) {
                            if (w > h) {
                                h = Math.round((h * maxDim) / w);
                                w = maxDim;
                            } else {
                                w = Math.round((w * maxDim) / h);
                                h = maxDim;
                            }
                        }
                        const canvas = document.createElement('canvas');
                        canvas.width = w;
                        canvas.height = h;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, w, h);
                        let dataUrl = canvas.toDataURL('image/webp', quality);
                        if (!dataUrl.startsWith('data:image/webp')) {
                            dataUrl = canvas.toDataURL('image/jpeg', quality);
                        }
                        resolve(dataUrl);
                    };
                    img.onerror = () => resolve(null);
                    img.src = e.target.result;
                };
                reader.onerror = () => resolve(null);
                reader.readAsDataURL(file);
            });
        },
        async onEditPhotoSelected(e) {
            const file = e.target.files[0];
            if (file) {
                this.editPhotoPreview = URL.createObjectURL(file);
                this.editRemovePhoto = false;
                const compressed = await this.compressClientSide(file, 1200, 0.85);
                if (compressed) {
                    this.editCapturedPhotoData = compressed;
                }
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
                let dataUrl = canvas.toDataURL('image/webp', 0.85);
                if (!dataUrl.startsWith('data:image/webp')) {
                    dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                }
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
    }" id="members-page" class="space-y-6">

        <!-- Top Metrics Header -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-2.5 sm:gap-4">
            <div class="p-3 sm:p-4 rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-medium">Total Enrolled</span>
                    <p class="text-xl sm:text-2xl font-semibold text-fg dark:text-white mt-0.5 sm:mt-1">{{ $totalMembers }}</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-500/20 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>

            <div class="p-3 sm:p-4 rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                <div>
                    <span class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-medium">Active Members</span>
                    <p class="text-xl sm:text-2xl font-semibold text-emerald-600 dark:text-emerald-400 mt-0.5 sm:mt-1">{{ $activeCount }}</p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
            </div>

            <a href="{{ route('app.members.index', ['filter' => 'due']) }}" 
               class="p-3 sm:p-4 rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between hover:border-amber-500/40 hover:bg-raised dark:hover:bg-raised transition-all group cursor-pointer">
                <div>
                    <span class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-medium">Remaining Due</span>
                    <p class="text-xl sm:text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-0.5 sm:mt-1">{{ $pendingFeesCount }} <span class="text-xs sm:text-xs font-normal text-fg-muted dark:text-fg-muted">due</span></p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-xl bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center border border-amber-200 dark:border-amber-500/20 group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                </div>
            </a>

            <a href="{{ route('app.members.index', ['filter' => 'expiring']) }}" 
               class="p-3 sm:p-4 rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-between hover:border-rose-500/40 hover:bg-raised dark:hover:bg-raised transition-all group cursor-pointer">
                <div>
                    <span class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted font-medium">Expiring Soon</span>
                    <p class="text-xl sm:text-2xl font-semibold text-rose-600 dark:text-rose-400 mt-0.5 sm:mt-1">{{ $expiringSoonCount }} <span class="text-xs sm:text-xs font-normal text-fg-muted dark:text-fg-muted">in 7d</span></p>
                </div>
                <div class="w-9 h-9 sm:w-11 sm:h-11 rounded-xl sm:rounded-xl bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center border border-rose-200 dark:border-rose-500/20 group-hover:scale-105 transition-transform shrink-0">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </a>
        </div>

        <!-- Quick Filter Status Tabs -->
        <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 text-xs font-semibold no-scrollbar -mx-3 px-3 sm:mx-0 sm:px-0">
            <a href="{{ route('app.members.index') }}" 
               class="shrink-0 px-3.5 py-2 rounded-xl border transition-all flex items-center gap-1.5 whitespace-nowrap {{ !request('status') && !request('filter') ? 'bg-accent text-accent-on border-accent' : 'bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 border-slate-200 dark:border-slate-800 hover:text-slate-900 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 shadow-sm' }}">
                <span>All Members</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ !request('status') && !request('filter') ? 'bg-black/10 text-accent-on font-semibold' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300' }}">{{ $totalMembers }}</span>
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
            <form id="member-search-form" action="{{ route('app.members.index') }}" method="GET" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-3 flex-grow max-w-4xl">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                @if(request('date_filter'))
                    <input type="hidden" name="date_filter" value="{{ request('date_filter') }}">
                @endif
                <div class="relative flex-grow">
                    <input type="search" id="member-search-input" name="search" autocomplete="off" maxlength="100" value="{{ request('search') }}" placeholder="Search name, phone, email, code..." class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                    <svg class="w-4 h-4 text-fg-muted dark:text-fg-muted absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                
                <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                    <!-- Sort Selection -->
                    <select name="sort" class="flex-1 sm:flex-none px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-amber-500 focus:outline-none cursor-pointer">
                        <option value="member_id_desc" {{ request('sort', 'member_id_desc') === 'member_id_desc' ? 'selected' : '' }}>Member ID: Latest First</option>
                        <option value="member_id_asc" {{ request('sort') === 'member_id_asc' ? 'selected' : '' }}>Member ID: Old First</option>
                        <option value="join_date_desc" {{ request('sort') === 'join_date_desc' ? 'selected' : '' }}>Join Date: Newest First</option>
                        <option value="name_asc" {{ request('sort') === 'name_asc' ? 'selected' : '' }}>Name: A to Z</option>
                    </select>

                    <select name="status" class="flex-1 sm:flex-none px-3 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        <option value="">All Statuses</option>
                        <option value="ACTIVE" {{ request('status') === 'ACTIVE' ? 'selected' : '' }}>Active</option>
                        <option value="INACTIVE" {{ request('status') === 'INACTIVE' ? 'selected' : '' }}>Inactive</option>
                        <option value="SUSPENDED" {{ request('status') === 'SUSPENDED' ? 'selected' : '' }}>Suspended</option>
                        <option value="EXPIRED" {{ request('status') === 'EXPIRED' ? 'selected' : '' }}>Expired</option>
                    </select>

                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <span>Search</span>
                    </button>
                    @if(request('status') || request('filter') || request('search') || (request('sort') && request('sort') !== 'member_id_desc'))
                        <a href="{{ route('app.members.index') }}" class="text-xs text-rose-500 dark:text-rose-400 hover:underline shrink-0 ml-1">Clear</a>
                    @endif
                </div>
            </form>

            <div class="flex items-center gap-2 w-full sm:w-auto shrink-0">
                @if(auth()->user()?->isGymOwner() || auth()->user()?->isSuperAdmin())
                    <a id="member-export-link" data-export-url="{{ route('app.members.export') }}" href="{{ route('app.members.export', request()->all()) }}" 
                       class="flex-1 sm:flex-none justify-center px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg border border-line dark:border-line text-xs font-semibold flex items-center gap-2 transition-colors cursor-pointer"
                       title="Export members to CSV with active filters">
                        <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                        <span>Export CSV</span>
                    </a>

                    <a href="{{ route('app.members.import') }}" 
                       class="flex-1 sm:flex-none justify-center px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg border border-line dark:border-line text-xs font-semibold flex items-center gap-2 transition-colors cursor-pointer"
                       title="Bulk import members from CSV">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        <span>Import CSV</span>
                    </a>
                @endif

                <a href="{{ route('app.members.create') }}" class="flex-1 sm:flex-none justify-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs flex items-center gap-2 transition-all shrink-0 cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Enroll Member</span>
                </a>
            </div>
        </div>

        <p id="member-search-status" role="status" aria-live="polite" class="text-xs text-fg-muted">Enter at least 3 characters to search members.</p>
        <div id="member-results" aria-busy="false">
            @include('app.members.partials.results')
        </div>

        <!-- 1. ENROLL NEW MEMBER MODAL (With Live Fee & Remaining Balance Calculator) -->
        <div x-show="showAddModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-2xl w-full p-4 sm:p-6 space-y-5 text-fg dark:text-fg max-h-[90vh] overflow-y-auto" @click.away="showAddModal = false">
                <div class="flex justify-between items-center border-b border-line dark:border-line pb-3">
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white">Enroll New Gym Member</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Add member details, choose package, and track fee payment</p>
                    </div>
                    <button @click="showAddModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-lg">✕</button>
                </div>

                <form action="{{ route('app.members.store') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Personal Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">First Name *</label>
                            <input type="text" name="first_name" required placeholder="e.g. Rahul" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Last Name *</label>
                            <input type="text" name="last_name" required placeholder="e.g. Sharma" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Phone Number (WhatsApp) *</label>
                            <input type="text" name="phone" required placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Email Address</label>
                            <input type="email" name="email" placeholder="rahul@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Gender</label>
                            <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div>
                            <x-dob-picker name="dob" />
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Assigned Branch</label>
                            <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                @foreach($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Membership Package & Billing Box -->
                    <div class="p-4 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line space-y-3">
                        <div class="flex justify-between items-center">
                            <label class="text-xs font-semibold text-amber-600 dark:text-amber-400">Membership Plan & Fee Setup</label>
                            <a href="{{ route('app.memberships.index') }}" class="text-xs text-fg-muted dark:text-fg-muted hover:text-amber-600 dark:hover:text-amber-400 font-medium">+ Manage Packages ↗</a>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Select Package / Plan</label>
                                <select name="membership_plan_id" x-model="selectedPlanId" @change="onPlanChange()" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    <option value="">-- No plan initially (Assign Later) --</option>
                                    @foreach($membershipPlans as $mp)
                                        <option value="{{ $mp->id }}">{{ $mp->name }} — {{ $currency }}{{ number_format($mp->price, 2) }} ({{ $mp->duration_value }} {{ $mp->duration_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Membership Start Date</label>
                                <input type="date" name="start_date" value="{{ date('Y-m-d') }}" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>

                        <template x-if="selectedPlanId">
                            <div class="space-y-3 pt-2 border-t border-line dark:border-line">
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Package Price</label>
                                        <div class="px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold font-mono">
                                            {{ $currency }}<span x-text="planPrice.toFixed(2)"></span>
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Discount ({{ $currency }})</label>
                                        <input type="number" step="0.01" name="discount" x-model="discountAmount" @input="onPlanChange()" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <div>
                                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Final Payable Fee</label>
                                        <div class="px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-emerald-600 dark:text-emerald-400 text-xs font-semibold font-mono">
                                            {{ $currency }}<span x-text="finalFee.toFixed(2)"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                    <div>
                                        <label class="block text-xs font-semibold text-amber-600 dark:text-amber-400 mb-1">Initial Payment Paid Now ({{ $currency }}) *</label>
                                        <input type="number" step="0.01" name="initial_payment_amount" x-model="paidNowAmount" placeholder="0.00" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-amber-500/50 text-fg dark:text-white text-xs font-semibold focus:border-amber-500 focus:outline-none font-mono">
                                    </div>

                                    <!-- Remaining Fees Live Preview -->
                                    <div>
                                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Remaining / Due Balance</label>
                                        <div class="px-3 py-2 rounded-xl border text-xs font-semibold flex items-center justify-between" :class="remainingBalance > 0 ? 'bg-amber-50 dark:bg-amber-500/10 border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400' : 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400'">
                                            <span>{{ $currency }}<span x-text="remainingBalance.toFixed(2)"></span></span>
                                            <span class="text-xs" x-text="remainingBalance > 0 ? 'PENDING DUE' : 'FULLY PAID'"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Payment Method</label>
                                        <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                            <option value="cash">💵 Cash Payment</option>
                                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                                            <option value="card">💳 Credit / Debit Card</option>
                                            <option value="netbanking">🏦 Net Banking</option>
                                            <option value="cheque">📄 Cheque</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Transaction Ref / Note</label>
                                        <input type="text" name="transaction_reference" placeholder="e.g. UPI Ref / Cash receipt" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Address / Locality</label>
                            <input type="text" name="address" placeholder="e.g. Ring Road, Hathijan" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Emergency Contact Phone</label>
                            <input type="text" name="emergency_contact_phone" placeholder="Emergency phone" class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs transition-all cursor-pointer">Enroll & Save Member</button>
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
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-4xl w-full p-4 sm:p-8 space-y-6 my-4 sm:my-8 max-h-[92vh] overflow-y-auto text-fg dark:text-fg" @click.away="showEditModal = false">
                
                <!-- Modal Top Bar -->
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold border border-indigo-200 dark:border-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-fg dark:text-white">Edit Member Details</h3>
                                <span class="px-2 py-0.5 rounded-md bg-raised dark:bg-raised text-amber-600 dark:text-amber-400 text-xs font-mono font-semibold border border-line dark:border-line" x-text="editMember.member_code"></span>
                            </div>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Update personal details, profile picture, body metrics, and membership status</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted hover:text-fg dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
                </div>

                <form :action="'/app/members/' + editMember.id" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
                    @csrf
                    <input type="hidden" name="photo_data" :value="editCapturedPhotoData">
                    <input type="hidden" name="remove_photo" :value="editRemovePhoto ? '1' : '0'">

                    <!-- 1. PROFILE PHOTO MANAGEMENT CARD -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex items-center gap-2.5 text-fg dark:text-fg font-semibold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            <span>Profile Photo</span>
                        </div>

                        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                            <div class="relative group shrink-0">
                                <div class="w-24 h-24 rounded-xl bg-raised dark:bg-raised border-2 border-indigo-500/30 overflow-hidden flex items-center justify-center relative">
                                    <template x-if="editPhotoPreview">
                                        <img :src="editPhotoPreview" alt="Profile preview" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!editPhotoPreview">
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-raised text-fg-muted dark:text-fg-muted">
                                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            <span class="text-xs font-semibold mt-1 text-fg-muted dark:text-fg-muted">No Photo</span>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <div class="flex flex-col justify-center space-y-2 text-center sm:text-left grow">
                                <span class="text-xs font-semibold text-fg dark:text-fg-muted">Upload or capture a new member photo</span>
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
                                            class="px-3.5 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-white dark:border-line text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        <span>Upload Photo</span>
                                    </button>

                                    <button type="button" 
                                            @click="openEditWebcam()"
                                            class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-600/20 dark:hover:bg-indigo-600/30 dark:text-indigo-300 dark:border-indigo-500/30 text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                        <span>Capture Camera</span>
                                    </button>

                                    <template x-if="editPhotoPreview">
                                        <button type="button" 
                                                @click="removeEditPhoto()"
                                                class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 text-xs font-semibold transition-all cursor-pointer">
                                            Remove Photo
                                        </button>
                                    </template>
                                </div>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Supported formats: JPG, PNG, WEBP, GIF. Max file size: 5MB.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 2. PERSONAL INFORMATION SECTION -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex items-center gap-2.5 text-fg dark:text-fg font-semibold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            <span>Personal Information</span>
                        </div>

                        <!-- Member ID & Join Date -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pb-3 border-b border-line dark:border-line">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Member ID / Code</label>
                                <input type="text" 
                                       name="member_code" 
                                       x-model="editMember.member_code" 
                                       placeholder="e.g. ARM0001" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-amber-600 dark:text-amber-400 font-mono font-semibold placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5 flex items-center justify-between">
                                    <span>Join Date</span>
                                    <span class="text-xs text-fg-muted font-normal">Click to pick date</span>
                                </label>
                                <input type="date" 
                                       name="join_date" 
                                       x-model="editMember.join_date" 
                                       onclick="this.showPicker && this.showPicker()" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" x-model="editMember.first_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" x-model="editMember.last_name" required class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-xs font-semibold text-fg-muted dark:text-fg-muted">+91</span>
                                    <input type="tel" name="phone" x-model="editMember.phone" required class="w-full pl-11 pr-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" x-model="editMember.alternate_phone" placeholder="Optional secondary contact" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Email Address</label>
                                <input type="email" name="email" x-model="editMember.email" placeholder="member@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Gender</label>
                                <select name="gender" x-model="editMember.gender" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    <option value="male">Male</option>
                                    <option value="female">Female</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div>
                                <x-dob-picker name="dob" model="editMember.dob" />
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Blood Group</label>
                                <select name="blood_group" x-model="editMember.blood_group" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}">{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">City</label>
                                <input type="text" name="city" x-model="editMember.city" placeholder="e.g. Ahmedabad" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Full Address</label>
                                <input type="text" name="address" x-model="editMember.address" placeholder="Street address, apartment, locality" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 3. PHYSICAL & FITNESS METRICS SECTION -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex items-center gap-2.5 text-fg dark:text-fg font-semibold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            <span>Physical & Fitness Metrics</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <!-- Height with Unit Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-1.5">
                                    <label class="font-semibold text-fg-muted dark:text-fg-muted">Height</label>
                                    <div class="flex rounded-lg overflow-hidden border border-line dark:border-line p-0.5 bg-raised dark:bg-surface">
                                        <button type="button" 
                                                @click="editMember.height_unit = 'cm'" 
                                                :class="editMember.height_unit === 'cm' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-xs font-semibold rounded transition-colors">cm</button>
                                        <button type="button" 
                                                @click="editMember.height_unit = 'ft'" 
                                                :class="editMember.height_unit === 'ft' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-xs font-semibold rounded transition-colors">ft</button>
                                    </div>
                                    <input type="hidden" name="height_unit" :value="editMember.height_unit">
                                </div>
                                <input type="text" 
                                       name="height" 
                                       x-model="editMember.height" 
                                       :placeholder="editMember.height_unit === 'cm' ? 'e.g. 175' : 'e.g. 5.9'" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Current Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" x-model="editMember.weight" placeholder="e.g. 72.5" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Target Weight (kg)</label>
                                <input type="number" step="0.1" name="target_weight" x-model="editMember.target_weight" placeholder="e.g. 68.0" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Primary Fitness Goal</label>
                                <select name="fitness_goal" x-model="editMember.fitness_goal" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
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
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Medical History / Health Notes</label>
                                <input type="text" name="medical_history" x-model="editMember.medical_history" placeholder="e.g. Asthma, Knee surgery, High BP" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 4. EMERGENCY CONTACT DETAILS -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex items-center gap-2.5 text-fg dark:text-fg font-semibold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span>Emergency Contact Information</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Contact Person Name</label>
                                <input type="text" name="emergency_contact_name" x-model="editMember.emergency_contact_name" placeholder="e.g. Robert Doe" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Emergency Phone Number</label>
                                <input type="tel" name="emergency_contact_phone" x-model="editMember.emergency_contact_phone" placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Relationship</label>
                                <input type="text" name="emergency_relation" x-model="editMember.emergency_relation" placeholder="e.g. Spouse / Parent / Sibling" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                        </div>
                    </div>

                    <!-- 5. ACCOUNT STATUS & SYSTEM SETTINGS -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex items-center gap-2.5 text-fg dark:text-fg font-semibold text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Membership & Account Status</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Member Status <span class="text-rose-500">*</span></label>
                                <select name="status" x-model="editMember.status" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none font-semibold">
                                    <option value="ACTIVE" class="text-emerald-600 dark:text-emerald-400">🟢 Active</option>
                                    <option value="INACTIVE" class="text-fg-muted dark:text-fg-muted">⚪ Inactive</option>
                                    <option value="SUSPENDED" class="text-rose-600 dark:text-rose-400">🔴 Suspended / Frozen</option>
                                    <option value="EXPIRED" class="text-orange-600 dark:text-orange-400">🟠 Expired</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Branch</label>
                                <select name="branch_id" x-model="editMember.branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Membership Plan & Validity Setup -->
                        <div class="p-4 rounded-xl bg-surface dark:bg-slate-900/90 border border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between">
                                <label class="text-xs font-semibold text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    <span>Plan & Validity Period</span>
                                </label>
                                <span class="text-xs text-fg-muted">Edit package or validity dates anytime</span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                <div>
                                    <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Package Plan</label>
                                    <select name="membership_plan_id" x-model="editMember.membership_plan_id" @change="onEditPlanChange()" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-amber-500 focus:outline-none text-xs font-semibold">
                                        <option value="">-- Keep Current Plan --</option>
                                        @foreach($membershipPlans as $mp)
                                            <option value="{{ $mp->id }}">{{ $mp->name }} ({{ $currency }}{{ number_format($mp->price, 0) }}) - {{ $mp->duration_value }} {{ $mp->duration_type }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div>
                                    <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5 flex items-center justify-between">
                                        <span>Start Date</span>
                                        <span class="text-xs text-fg-muted font-normal">Click to pick</span>
                                    </label>
                                    <input type="date" 
                                           name="membership_start_date" 
                                           x-model="editMember.membership_start_date" 
                                           @change="onEditPlanChange()" 
                                           onclick="this.showPicker && this.showPicker()" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-amber-500 focus:outline-none text-xs cursor-pointer">
                                </div>

                                <div>
                                    <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5 flex items-center justify-between">
                                        <span>End Date (Expiry)</span>
                                        <span class="text-xs text-fg-muted font-normal">Click to pick</span>
                                    </label>
                                    <input type="date" 
                                           name="membership_end_date" 
                                           x-model="editMember.membership_end_date" 
                                           onclick="this.showPicker && this.showPicker()" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-amber-500 focus:outline-none text-xs cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Internal Notes</label>
                            <textarea name="notes" x-model="editMember.notes" rows="2" placeholder="Optional notes regarding medical history or special arrangements" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none"></textarea>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-line dark:border-line">
                        <button type="button" @click="showEditModal = false" class="px-5 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold transition-all cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" class="px-7 py-2.5 rounded-xl text-slate-950 font-semibold transition-all flex items-center gap-2 cursor-pointer">
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
            <div @click.away="closeEditWebcam()" class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl max-w-lg w-full p-6 space-y-4 text-fg dark:text-white">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <h3 class="text-sm font-semibold text-fg dark:text-white flex items-center gap-2">
                        <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Take Live Member Photo</span>
                    </h3>
                    <button type="button" @click="closeEditWebcam()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white">✕</button>
                </div>

                <template x-if="editCameraError">
                    <div class="p-3.5 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs">
                        <span x-text="editCameraError"></span>
                    </div>
                </template>

                <div class="relative rounded-xl overflow-hidden bg-black aspect-video flex items-center justify-center border border-line dark:border-line">
                    <video x-ref="editCameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas x-ref="editCameraCanvas" class="hidden"></canvas>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" @click="closeEditWebcam()" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:text-fg-muted dark:hover:text-white dark:border-line text-xs font-semibold transition-all">
                        Cancel
                    </button>
                    <button type="button" @click="takeEditSnapshot()" class="px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-2 transition-all ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Capture Photo</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- 3. COLLECT REMAINING FEES MODAL -->
        <div x-show="showCollectFeeModal" class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-3 sm:p-4" x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-lg w-full p-4 sm:p-6 space-y-5 text-fg dark:text-fg max-h-[90vh] overflow-y-auto" @click.away="showCollectFeeModal = false">
                <div class="flex justify-between items-center border-b border-line dark:border-line pb-3">
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white">Collect Fee Payment</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Receive remaining balance or instalment payment</p>
                    </div>
                    <button @click="showCollectFeeModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-lg">✕</button>
                </div>

                <!-- Summary Box -->
                <div class="p-4 rounded-xl bg-raised dark:bg-slate-950/80 border border-line dark:border-line space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-fg-muted dark:text-fg-muted">Member:</span>
                        <span class="font-semibold text-fg dark:text-white" x-text="feeMember.name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-fg-muted dark:text-fg-muted">Plan:</span>
                        <span class="font-semibold text-amber-600 dark:text-amber-400" x-text="feeMember.plan_name"></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-line dark:border-line">
                        <span class="text-fg-muted dark:text-fg-muted">Total Fee:</span>
                        <span class="font-mono text-fg-muted dark:text-fg-muted">{{ $currency }}<span x-text="feeMember.final_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-fg-muted dark:text-fg-muted">Already Paid:</span>
                        <span class="font-mono text-emerald-600 dark:text-emerald-400">{{ $currency }}<span x-text="feeMember.paid_amount.toFixed(2)"></span></span>
                    </div>
                    <div class="flex justify-between items-center text-xs pt-1 border-t border-line dark:border-line">
                        <span class="font-semibold text-amber-600 dark:text-amber-400">Remaining Balance:</span>
                        <span class="font-mono font-semibold text-amber-600 dark:text-amber-400 text-sm">{{ $currency }}<span x-text="feeMember.remaining_balance.toFixed(2)"></span></span>
                    </div>
                </div>

                <form :action="'/app/members/' + feeMember.id + '/collect-fee'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-amber-600 dark:text-amber-400 mb-1">Amount to Collect ({{ $currency }}) *</label>
                        <input type="number" step="0.01" name="amount" x-model="collectAmount" required min="0.01" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-amber-500/60 text-fg dark:text-white font-mono font-semibold text-sm focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method *</label>
                        <select name="payment_method" x-model="collectMethod" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                            <option value="upi">📱 UPI / GPay / PhonePe / Paytm</option>
                            <option value="cash">💵 Cash Payment</option>
                            <option value="card">💳 Credit / Debit Card</option>
                            <option value="netbanking">🏦 Net Banking</option>
                            <option value="cheque">📄 Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Transaction Reference / UTR Number</label>
                        <input type="text" name="transaction_reference" x-model="collectRef" placeholder="e.g. UPI-123456789" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Notes / Receipt Remarks</label>
                        <input type="text" name="notes" x-model="collectNotes" placeholder="e.g. Second instalment remaining fee cleared" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-3 pt-3 border-t border-line dark:border-line">
                        <button type="button" @click="showCollectFeeModal = false" class="px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold transition-colors">Cancel</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Record Payment Receipt
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('member-search-form');
            const input = document.getElementById('member-search-input');
            const results = document.getElementById('member-results');
            const status = document.getElementById('member-search-status');
            const root = document.getElementById('members-page');
            if (!form || !input || !results || !status || !root) return;

            let timer, pending, version = 0;
            let applied = new URL(location.href);
            let composing = false;

            const buildUrl = () => {
                const url = new URL(form.action, location.href);
                for (const [key, value] of new FormData(form)) {
                    const clean = String(value).trim();
                    if (clean) url.searchParams.set(key, clean);
                }
                return url;
            };

            const cancel = () => {
                clearTimeout(timer);
                pending?.abort();
                version++;
                results.setAttribute('aria-busy', 'false');
            };

            async function load(url, force = false) {
                cancel();
                if (!force && url.searchParams.toString() === applied.searchParams.toString()) {
                    status.textContent = 'Results are up to date.';
                    return;
                }
                const current = version;
                const controller = new AbortController();
                pending = controller;
                status.textContent = 'Searching members…';
                results.setAttribute('aria-busy', 'true');
                try {
                    const response = await fetch(url, {
                        headers: {'X-Member-Results': '1', 'Accept': 'text/html'},
                        credentials: 'same-origin', cache: 'no-store', signal: controller.signal
                    });
                    if (!response.ok || response.redirected) throw new Error('Search failed');
                    const html = await response.text();
                    if (current !== version) return;
                    const fragment = new DOMParser().parseFromString(html, 'text/html');
                    const data = fragment.getElementById('member-results-data');
                    if (!data || !window.Alpine) throw new Error('Invalid search response');
                    const members = JSON.parse(data.textContent);
                    window.Alpine.$data(root).membersData = members;
                    results.replaceChildren(...Array.from(fragment.body.childNodes).map(node => document.importNode(node, true)));
                    applied = url;
                    history.replaceState(history.state, '', url);
                    const exportLink = document.getElementById('member-export-link');
                    if (exportLink) {
                        const exportUrl = new URL(exportLink.dataset.exportUrl, location.href);
                        exportUrl.search = url.search;
                        exportUrl.searchParams.delete('page');
                        exportLink.href = exportUrl.href;
                    }
                    status.textContent = Object.keys(members).length ? 'Results updated.' : 'No matching members.';
                } catch (error) {
                    if (error.name !== 'AbortError' && current === version) status.textContent = 'Unable to refresh results. Press Search to retry.';
                } finally {
                    if (current === version) {
                        pending = null;
                        results.setAttribute('aria-busy', 'false');
                    }
                }
            }

            function schedule(immediate = false) {
                cancel();
                const term = input.value.trim();
                if (term.length > 0 && term.length < 3) {
                    status.textContent = 'Enter at least 3 characters. Showing previous results.';
                    return;
                }
                const url = buildUrl();
                if (immediate) load(url, true);
                else timer = setTimeout(() => load(url), 350);
            }

            input.addEventListener('compositionstart', () => { composing = true; cancel(); });
            input.addEventListener('compositionend', () => { composing = false; schedule(); });
            input.addEventListener('input', () => { if (!composing) schedule(); });
            form.addEventListener('submit', event => { event.preventDefault(); schedule(true); });
            form.querySelectorAll('select').forEach(select => select.addEventListener('change', () => schedule(true)));
            results.addEventListener('click', event => {
                const link = event.target.closest('[data-member-pagination] a');
                if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                event.preventDefault();
                load(new URL(link.href, location.href), true);
            });
        });
    </script>
</x-app-layout>
