<x-app-layout header="Add Member">
    @php
        $currency = auth()->user()->tenant?->currency_symbol ?? '₹';
        $activeBranchId = \App\Services\TenantContext::branchId();
        $membershipPlans = ($membershipPlans ?? collect())->filter(fn ($p) => (bool) $p->is_active);
        $plansJson = $membershipPlans->mapWithKeys(function ($p) {
            return [$p->id => [
                'id' => $p->id,
                'name' => $p->name,
                'plan_type' => $p->plan_type ?? 'single',
                'max_members' => (int) ($p->max_members ?? ($p->plan_type === 'duo' ? 2 : ($p->plan_type === 'family' ? 4 : 1))),
                'price' => (float) $p->price,
                'duration_type' => $p->duration_type,
                'duration_value' => $p->duration_value,
                'tax_rate' => (float) $p->tax_rate,
            ]];
        });

        $lead = $lead ?? null;
        $leadFirstName = '';
        $leadLastName = '';
        $leadPhone = '';
        if ($lead) {
            $parts = explode(' ', trim($lead->name ?? ''), 2);
            $leadFirstName = $parts[0] ?? '';
            $leadLastName = $parts[1] ?? ($parts[0] ?? '');
            $rawPhone = trim($lead->phone ?? '');
            $leadPhone = preg_replace('/^\+91|^91/', '', $rawPhone);
        }
        $preselectedPlanId = old('membership_plan_id', $lead?->membership_plan_id ?? request('plan_id', ''));
    @endphp

    <script>
        window.__MEMBERSHIP_PLANS__ = {{ Js::from($plansJson) }};
    </script>

    <div x-data="{
        mode: 'new', // 'new' | 'existing'
        
        // Plans & Financials
        plans: window.__MEMBERSHIP_PLANS__ || {},
        selectedPlanId: '{{ $preselectedPlanId }}',
        discountAmount: 0,
        paidNowAmount: 0,
        paymentMethod: 'cash',

        init() {
            if (this.selectedPlanId) {
                this.$nextTick(() => {
                    this.onPlanChange();
                });
            }
        },
        
        // Split Payment State (New Member)
        isSplitPayment: false,
        splitMethod1: 'cash',
        splitAmount1: '',
        splitRef1: '',
        splitMethod2: 'upi',
        splitAmount2: '',
        splitRef2: '',

        toggleSplitPayment() {
            this.isSplitPayment = !this.isSplitPayment;
            if (this.isSplitPayment) {
                const total = Number(this.paidNowAmount || this.finalFee || 0);
                const half = Math.round((total / 2) * 100) / 100;
                this.splitAmount1 = half > 0 ? half : '';
                this.splitAmount2 = (total - half) > 0 ? (total - half) : '';
                this.splitMethod1 = this.splitMethod1 || 'cash';
                this.splitMethod2 = this.splitMethod2 || 'upi';
                this.updateSplitFromFirst();
            } else {
                this.paidNowAmount = this.finalFee;
            }
        },

        updateSplitFromFirst() {
            const total = Number(this.finalFee || this.paidNowAmount || 0);
            const a1 = Number(this.splitAmount1 || 0);
            if (total > 0 && a1 >= 0) {
                const rem = Math.max(0, Math.round((total - a1) * 100) / 100);
                this.splitAmount2 = rem > 0 ? rem : 0;
            }
            this.syncSplitTotal();
        },

        updateSplitFromSecond() {
            this.syncSplitTotal();
        },

        syncSplitTotal() {
            const a1 = Number(this.splitAmount1 || 0);
            const a2 = Number(this.splitAmount2 || 0);
            this.paidNowAmount = Math.round((a1 + a2) * 100) / 100;
        },
        
        get selectedPlan() {
            return this.selectedPlanId && this.plans[this.selectedPlanId] ? this.plans[this.selectedPlanId] : null;
        },
        get planType() {
            return this.selectedPlan ? (this.selectedPlan.plan_type || 'single') : 'single';
        },
        get maxMembers() {
            return this.selectedPlan ? Number(this.selectedPlan.max_members || 1) : 1;
        },
        get isDuo() {
            return (this.planType === 'duo' || this.maxMembers === 2) && this.maxMembers < 3;
        },
        get isFamily() {
            return (this.planType === 'family' || this.maxMembers === 4) && this.maxMembers < 5 && this.planType !== 'group';
        },
        get isGroup() {
            return this.planType === 'group' || this.maxMembers > 2;
        },
        get planPrice() {
            return this.selectedPlan ? this.selectedPlan.price : 0;
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
        onDiscountChange() {
            this.paidNowAmount = this.finalFee;
            if (this.isSplitPayment) {
                this.updateSplitFromFirst();
            }
        },

        // Height Unit Toggle
        heightUnit: 'cm',
        
        // Photo & Webcam
        photoPreview: null,
        capturedPhotoData: '',
        showCameraModal: false,
        cameraStream: null,
        cameraActive: false,
        cameraError: null,

        triggerFileInput() {
            this.$refs.photoInput.click();
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
        async onPhotoSelected(e) {
            const file = e.target.files[0];
            if (file) {
                this.photoPreview = URL.createObjectURL(file);
                const compressed = await this.compressClientSide(file, 1200, 0.85);
                if (compressed) {
                    this.capturedPhotoData = compressed;
                }
            }
        },
        async openWebcam() {
            this.showCameraModal = true;
            this.cameraError = null;
            this.cameraActive = false;
            try {
                this.cameraStream = await navigator.mediaDevices.getUserMedia({
                    video: { width: { ideal: 640 }, height: { ideal: 480 }, facingMode: 'user' }
                });
                this.$nextTick(() => {
                    if (this.$refs.cameraVideo) {
                        this.$refs.cameraVideo.srcObject = this.cameraStream;
                        this.$refs.cameraVideo.play();
                        this.cameraActive = true;
                    }
                });
            } catch (err) {
                this.cameraError = 'Camera access denied or unavailable on this device.';
            }
        },
        closeWebcam() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
            this.cameraActive = false;
            this.showCameraModal = false;
        },
        takeSnapshot() {
            const video = this.$refs.cameraVideo;
            const canvas = this.$refs.cameraCanvas;
            if (video && canvas) {
                canvas.width = video.videoWidth || 640;
                canvas.height = video.videoHeight || 480;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                let dataUrl = canvas.toDataURL('image/webp', 0.85);
                if (!dataUrl.startsWith('data:image/webp')) {
                    dataUrl = canvas.toDataURL('image/jpeg', 0.88);
                }
                this.photoPreview = dataUrl;
                this.capturedPhotoData = dataUrl;
                this.closeWebcam();
            }
        },

        // Dynamic Identification Documents
        idDocs: [
            { type: 'Aadhaar Card', number: '', frontFileName: '', backFileName: '' }
        ],
        addIdDoc() {
            this.idDocs.push({ type: 'Aadhaar Card', number: '', frontFileName: '', backFileName: '' });
        },
        removeIdDoc(index) {
            if (this.idDocs.length > 1) {
                this.idDocs.splice(index, 1);
            } else {
                this.idDocs = [{ type: 'Aadhaar Card', number: '', frontFileName: '', backFileName: '' }];
            }
        },
        onDocFileChange(e, index, side) {
            const file = e.target.files[0];
            if (file) {
                if (side === 'front') {
                    this.idDocs[index].frontFileName = file.name;
                } else {
                    this.idDocs[index].backFileName = file.name;
                }
            }
        },

        // Assign Existing Member Lookup State
        searchPhone: '',
        lookupLoading: false,
        lookupCompleted: false,
        existingMember: null,
        existingSelectedPlanId: '',
        existingDiscountAmount: 0,
        existingPaidNowAmount: 0,
        existingPaymentMethod: 'cash',
        
        // Split Payment State (Existing Member)
        existingIsSplitPayment: false,
        existingSplitMethod1: 'cash',
        existingSplitAmount1: '',
        existingSplitRef1: '',
        existingSplitMethod2: 'upi',
        existingSplitAmount2: '',
        existingSplitRef2: '',

        toggleExistingSplitPayment() {
            this.existingIsSplitPayment = !this.existingIsSplitPayment;
            if (this.existingIsSplitPayment) {
                const total = Number(this.existingPaidNowAmount || this.existingFinalFee || 0);
                const half = Math.round((total / 2) * 100) / 100;
                this.existingSplitAmount1 = half > 0 ? half : '';
                this.existingSplitAmount2 = (total - half) > 0 ? (total - half) : '';
                this.existingSplitMethod1 = this.existingSplitMethod1 || 'cash';
                this.existingSplitMethod2 = this.existingSplitMethod2 || 'upi';
                this.updateExistingSplitFromFirst();
            } else {
                this.existingPaidNowAmount = this.existingFinalFee;
            }
        },

        updateExistingSplitFromFirst() {
            const total = Number(this.existingFinalFee || this.existingPaidNowAmount || 0);
            const a1 = Number(this.existingSplitAmount1 || 0);
            if (total > 0 && a1 >= 0) {
                const rem = Math.max(0, Math.round((total - a1) * 100) / 100);
                this.existingSplitAmount2 = rem > 0 ? rem : 0;
            }
            this.syncExistingSplitTotal();
        },

        updateExistingSplitFromSecond() {
            this.syncExistingSplitTotal();
        },

        syncExistingSplitTotal() {
            const a1 = Number(this.existingSplitAmount1 || 0);
            const a2 = Number(this.existingSplitAmount2 || 0);
            this.existingPaidNowAmount = Math.round((a1 + a2) * 100) / 100;
        },
        
        get existingPlanPrice() {
            return this.existingSelectedPlanId && this.plans[this.existingSelectedPlanId] ? this.plans[this.existingSelectedPlanId].price : 0;
        },
        get existingFinalFee() {
            return Math.max(0, this.existingPlanPrice - Number(this.existingDiscountAmount || 0));
        },
        onExistingPlanChange() {
            this.existingPaidNowAmount = this.existingFinalFee;
        },
        onExistingDiscountChange() {
            this.existingPaidNowAmount = this.existingFinalFee;
            if (this.existingIsSplitPayment) {
                this.updateExistingSplitFromFirst();
            }
        },

        async searchMemberByPhone() {
            if (!this.searchPhone || this.searchPhone.length < 4) {
                return;
            }
            this.lookupLoading = true;
            this.lookupCompleted = false;
            this.existingMember = null;
            try {
                const res = await fetch('{{ route('app.members.lookup-phone') }}?phone=' + encodeURIComponent(this.searchPhone));
                const data = await res.json();
                this.lookupCompleted = true;
                if (data.found && data.member) {
                    this.existingMember = data.member;
                }
            } catch (err) {
                console.error(err);
                this.lookupCompleted = true;
            } finally {
                this.lookupLoading = false;
            }
        }
    }" class="space-y-6 pb-20 max-w-6xl mx-auto">

        <!-- Top Header & Navigation Breadcrumb -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('app.members.index') }}" 
                   class="p-2 sm:p-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-all shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                </a>
                <div class="min-w-0">
                    <div class="flex items-center gap-2 text-xs font-semibold text-fg-muted dark:text-fg-muted mb-0.5 truncate">
                        <a href="{{ route('app.members.index') }}" class="hover:text-fg dark:hover:text-fg transition-colors">Members</a>
                        <span>/</span>
                        <span class="text-amber-600 dark:text-amber-400">Add Member</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight">Add Member</h1>
                </div>
            </div>

            <a href="{{ route('app.members.index') }}" 
               class="self-start sm:self-auto px-4 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised text-xs font-semibold transition-all">
                Cancel
            </a>
        </div>

        <!-- Feedback Messages -->
        @if (session('error'))
            <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-300 text-xs space-y-1">
                <p class="font-semibold flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Please correct the following errors:
                </p>
                <ul class="list-disc list-inside text-rose-600 dark:text-rose-400/90 pl-1 space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Lead Conversion Banner -->
        @if($lead)
            <div class="p-4 sm:p-5 rounded-xl bg-indigo-50/90 dark:bg-indigo-950/40 border border-indigo-200 dark:border-indigo-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-start sm:items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center font-semibold text-lg shrink-0">
                        👤
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-semibold text-indigo-950 dark:text-indigo-200">Converting CRM Lead: {{ $lead->name }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-200/80 dark:bg-indigo-900/80 text-indigo-900 dark:text-indigo-200 font-mono font-semibold">Lead #{{ $lead->id }}</span>
                            @if($lead->membershipPlan)
                                <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-300 font-semibold border border-amber-300/80 dark:border-amber-700/60">
                                    Inquired Plan: {{ $lead->membershipPlan->name }} ({{ $currency }}{{ number_format($lead->membershipPlan->price, 2) }})
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">
                            Prospect details have been pre-filled. You can change the package, enter health info, snap webcam photo, or record split payment before saving.
                        </p>
                    </div>
                </div>
                <a href="{{ route('app.leads.index') }}" class="px-3 py-1.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white transition-colors shrink-0">
                    Cancel Conversion
                </a>
            </div>
        @endif

        <!-- Mode Selection Dual Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
            <!-- Option 1: Register New Member -->
            <button type="button" 
                    @click="mode = 'new'"
                    class="p-4 sm:p-5 rounded-xl border text-left transition-all relative overflow-hidden flex items-start gap-3 sm:gap-4 cursor-pointer"
                    :class="mode === 'new' ? 'bg-gradient-to-br from-indigo-50/80 to-white dark:from-indigo-950/60 dark:to-slate-900 border-indigo-500 shadow-xl shadow-indigo-500/10 ring-2 ring-indigo-500/30' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 shadow-sm opacity-80 hover:opacity-100'">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0 transition-all"
                     :class="mode === 'new' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                </div>
                <div class="flex-grow pr-6 min-w-0">
                    <h3 class="text-sm font-semibold text-fg dark:text-white truncate">Register New Member</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 leading-relaxed">Create a new member account and add to your gym</p>
                </div>
                <div x-show="mode === 'new'" class="absolute top-3.5 right-3.5 sm:top-4 sm:right-4 w-5 h-5 rounded-full bg-indigo-500 text-white flex items-center justify-center text-xs font-semibold">
                    ✓
                </div>
            </button>

            <!-- Option 2: Assign Existing Member -->
            <button type="button" 
                    @click="mode = 'existing'"
                    class="p-4 sm:p-5 rounded-xl border text-left transition-all relative overflow-hidden flex items-start gap-3 sm:gap-4 cursor-pointer"
                    :class="mode === 'existing' ? 'bg-gradient-to-br from-amber-50/80 to-white dark:from-amber-950/40 dark:to-slate-900 border-amber-500 shadow-xl shadow-amber-500/10 ring-2 ring-amber-500/30' : 'bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 shadow-sm opacity-80 hover:opacity-100'">
                <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl flex items-center justify-center shrink-0 transition-all"
                     :class="mode === 'existing' ? 'bg-amber-500 text-slate-950 shadow-lg shadow-amber-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400'">
                    <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
                <div class="flex-grow pr-6 min-w-0">
                    <h3 class="text-sm font-semibold text-fg dark:text-white truncate">Assign Existing Member</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 leading-relaxed">Add an existing member to your gym using their phone number</p>
                </div>
                <div x-show="mode === 'existing'" class="absolute top-3.5 right-3.5 sm:top-4 sm:right-4 w-5 h-5 rounded-full bg-amber-500 text-slate-950 flex items-center justify-center text-xs font-semibold">
                    ✓
                </div>
            </button>
        </div>

        <!-- ========================================================================= -->
        <!-- MODE 1: REGISTER NEW MEMBER FORM -->
        <!-- ========================================================================= -->
        <form x-show="mode === 'new'" 
              action="{{ route('app.members.store') }}" 
              method="POST" 
              enctype="multipart/form-data" 
              class="space-y-4 sm:space-y-6">
            @csrf
            <input type="hidden" name="mode" value="new">
            <input type="hidden" name="lead_id" value="{{ $lead?->id ?? request('lead_id', '') }}">
            <input type="hidden" name="photo_data" :value="capturedPhotoData">

            <!-- 1. PERSONAL INFORMATION SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Personal Information</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Basic contact and identification details</p>
                    </div>
                </div>

                <!-- Avatar & Photo Actions (Upload / Webcam Capture) -->
                <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 p-4 rounded-xl bg-raised dark:bg-slate-950/40 border border-line dark:border-line">
                    <div class="relative group">
                        <div class="w-24 h-24 rounded-full bg-raised dark:bg-raised border-2 border-line dark:border-line overflow-hidden flex items-center justify-center">
                            <template x-if="photoPreview">
                                <img :src="photoPreview" alt="Profile preview" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!photoPreview">
                                <svg class="w-12 h-12 text-fg-muted dark:text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </template>
                        </div>
                    </div>

                    <div class="flex flex-col justify-center space-y-2 text-center sm:text-left">
                        <span class="text-xs font-semibold text-fg dark:text-fg-muted">Member Photo</span>
                        <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2.5">
                            <!-- Hidden File Input -->
                            <input type="file" 
                                   name="photo" 
                                   x-ref="photoInput" 
                                   accept="image/png,image/jpeg,image/webp,image/gif" 
                                   class="hidden" 
                                   @change="onPhotoSelected($event)">

                            <button type="button" 
                                    @click="triggerFileInput()"
                                    class="px-3.5 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-white dark:border-line text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                <span>Upload</span>
                            </button>

                            <button type="button" 
                                    @click="openWebcam()"
                                    class="px-3.5 py-2 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 dark:bg-indigo-600/20 dark:hover:bg-indigo-600/30 dark:text-indigo-400 dark:border-indigo-500/30 text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>Capture</span>
                            </button>

                            <template x-if="photoPreview">
                                <button type="button" 
                                        @click="photoPreview = null; capturedPhotoData = ''; $refs.photoInput.value = ''"
                                        class="px-3 py-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 text-xs font-semibold transition-all cursor-pointer">
                                    Remove
                                </button>
                            </template>
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">JPG, PNG or WEBP. Max 10MB.</p>
                        @error('photo')
                            <p class="text-rose-500 font-semibold text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('photo_data')
                            <p class="text-rose-500 font-semibold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Personal Info Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Member ID / Code -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">
                                Member ID / Code
                            </label>
                            <span class="text-xs text-amber-500 font-semibold">Auto or Custom</span>
                        </div>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-semibold text-fg-muted dark:text-fg-muted">#</span>
                            <input type="text" 
                                   name="member_code" 
                                   value="{{ old('member_code', $nextMemberCode ?? '') }}" 
                                   placeholder="e.g. {{ $nextMemberCode ?? 'MEM001' }}" 
                                   class="w-full pl-8 pr-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono font-semibold placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                        </div>
                        @error('member_code')
                            <p class="text-rose-500 font-semibold text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- First Name -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="first_name" 
                               value="{{ old('first_name', $leadFirstName ?: request('first_name', '')) }}" 
                               required 
                               placeholder="e.g. John" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="last_name" 
                               value="{{ old('last_name', $leadLastName ?: request('last_name', '')) }}" 
                               required 
                               placeholder="e.g. Doe" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Phone Number -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                        <div class="relative flex items-center">
                            <span class="absolute left-3 text-xs font-semibold text-fg-muted dark:text-fg-muted">+91</span>
                            <input type="tel" 
                                   name="phone" 
                                   value="{{ old('phone', $leadPhone ?: request('phone', '')) }}" 
                                   required 
                                   placeholder="9876543210" 
                                   class="w-full pl-12 pr-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <!-- Alternate Phone -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Alternate Phone</label>
                        <input type="tel" 
                               name="alternate_phone" 
                               value="{{ old('alternate_phone', $lead?->alternate_phone ?? request('alternate_phone', '')) }}" 
                               placeholder="Optional alternate number" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Email Address -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Email Address</label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email', $lead?->email ?? request('email', '')) }}" 
                               placeholder="john.doe@example.com" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Gender -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Gender</label>
                        <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="">Select Gender</option>
                            <option value="male" {{ strtolower(old('gender', $lead?->gender ?? request('gender', 'male'))) === 'male' ? 'selected' : '' }}>Male</option>
                            <option value="female" {{ strtolower(old('gender', $lead?->gender ?? request('gender', ''))) === 'female' ? 'selected' : '' }}>Female</option>
                            <option value="other" {{ strtolower(old('gender', $lead?->gender ?? request('gender', ''))) === 'other' ? 'selected' : '' }}>Other</option>
                        </select>
                    </div>

                    <!-- Date of Birth -->
                    <x-dob-picker name="dob" :value="old('dob')" />

                    <!-- Blood Group -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Blood Group</label>
                        <select name="blood_group" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            <option value="">Select Blood Group</option>
                            @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                <option value="{{ $bg }}" {{ old('blood_group') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- City -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">City</label>
                        <input type="text" 
                               name="city" 
                               value="{{ old('city', $lead?->city ?? request('city', '')) }}" 
                               placeholder="e.g. Mumbai" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>

                    <!-- Address (Spans 3 cols on large screens) -->
                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Address</label>
                        <input type="text" 
                               name="address" 
                               value="{{ old('address') }}" 
                               placeholder="House / Flat No, Street, Landmark, Area" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                    </div>
                </div>

                <!-- WhatsApp Notifications Subsection -->
                <div class="pt-4 border-t border-line dark:border-line">
                    <div class="flex items-center gap-2.5 mb-2">
                        <div class="w-6 h-6 rounded-md bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold text-xs">
                            💬
                        </div>
                        <span class="text-xs font-semibold text-fg dark:text-white">WhatsApp Notifications</span>
                    </div>
                    <label class="flex items-start gap-3 p-3.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line hover:border-line dark:hover:border-line cursor-pointer transition-colors">
                        <input type="checkbox" 
                               name="whatsapp_disabled" 
                               value="1" 
                               {{ old('whatsapp_disabled') ? 'checked' : '' }} 
                               class="mt-0.5 rounded bg-surface dark:bg-surface border-line dark:border-line text-indigo-600 focus:ring-indigo-500 w-4 h-4">
                        <div>
                            <span class="text-xs font-semibold text-fg dark:text-fg">Notifications Disabled</span>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">No WhatsApp reminders will be sent (OTP not affected)</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- 2. MEMBERSHIP DETAILS SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Membership Details</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Gym branch, plan selection, trainer and access settings</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <!-- Branch -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Branch <span class="text-rose-500">*</span></label>
                        <select name="branch_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="">Select Branch</option>
                            @foreach($branches as $b)
                                <option value="{{ $b->id }}" {{ old('branch_id', $activeBranchId) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Membership Plan -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Membership Plan</label>
                            <template x-if="selectedPlan">
                                <span class="px-2 py-0.5 rounded text-xs font-semibold"
                                      :class="isDuo ? 'bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30' : (maxMembers > 2 ? 'bg-purple-50 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30' : 'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-700 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-500/30')"
                                      x-text="isDuo ? '👥 Duo (2 People)' : (maxMembers > 2 ? ('👥 ' + (planType === 'group' ? 'Group' : 'Family') + ' (' + maxMembers + ' People)') : '👤 Single (1 Person)')"></span>
                            </template>
                        </div>
                        <select name="membership_plan_id" 
                                x-model="selectedPlanId" 
                                @change="onPlanChange()" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="">Select Plan</option>
                            @foreach($membershipPlans as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} 
                                    ({{ strtoupper($p->plan_type ?? 'SINGLE') }} - {{ $p->max_members ?? 1 }} {{ ($p->max_members ?? 1) > 1 ? 'Persons' : 'Person' }}) - 
                                    {{ $currency }}{{ number_format($p->price, 0) }} / {{ $p->duration_value }} {{ $p->duration_type }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Join Date -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Join Date <span class="text-rose-500">*</span></label>
                        <input type="date" 
                               name="join_date" 
                               value="{{ old('join_date', now()->toDateString()) }}" 
                               onclick="this.showPicker && this.showPicker()"
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none cursor-pointer">
                    </div>

                    <!-- Lead Source -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Lead Source</label>
                        @php
                            $selectedLeadSource = old('lead_source', $lead ? ucwords(str_replace('_', ' ', $lead->source)) : '');
                        @endphp
                        <select name="lead_source" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="">Select Lead Source</option>
                            @foreach(['Walk-in', 'Website', 'Instagram', 'Facebook', 'Referral', 'Google', 'Campaign', 'Other'] as $source)
                                <option value="{{ $source }}" {{ strtolower($selectedLeadSource) === strtolower($source) ? 'selected' : '' }}>{{ $source }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Assigned Trainer -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Assigned Trainer</label>
                        <select name="trainer_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="">Select Trainer</option>
                            @foreach($trainers as $trainer)
                                <option value="{{ $trainer->id }}" {{ old('trainer_id') == $trainer->id ? 'selected' : '' }}>{{ $trainer->full_name }} ({{ $trainer->specialization ?? 'General' }})</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Sales Representative -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Sales Representative</label>
                        <select name="sales_rep_id" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                            <option value="">Select Sales Rep</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}" {{ old('sales_rep_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Locker Number -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Locker Number</label>
                        <input type="text" 
                               name="locker_number" 
                               value="{{ old('locker_number') }}" 
                               placeholder="e.g. L-104" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                    </div>

                    <!-- Max Entries Per Day -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Max Entries Per Day</label>
                        <input type="number" 
                               name="max_daily_entries" 
                               value="{{ old('max_daily_entries') }}" 
                               min="0" 
                               placeholder="Leave empty to use global setting. 0 = unlimited." 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">
                    </div>
                </div>

                <!-- Plan Pricing & Initial Payment Calculation Box (Appears when plan selected) -->
                <div x-show="selectedPlanId" x-transition class="p-4 rounded-xl bg-raised dark:bg-slate-950/80 border border-amber-300 dark:border-amber-500/30 space-y-4">
                    <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                        <span class="text-xs font-semibold text-fg dark:text-fg-muted">Plan Fee Details</span>
                        <div class="text-xs text-amber-600 dark:text-amber-400 font-semibold">
                            Standard Fee: {{ $currency }}<span x-text="planPrice.toFixed(2)"></span>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Discount Amount ({{ $currency }})</label>
                            <input type="number" 
                                   name="discount" 
                                   x-model="discountAmount" 
                                   @input="onDiscountChange()"
                                   min="0" 
                                   step="any" 
                                   placeholder="0" 
                                   class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Net Payable Fee ({{ $currency }})</label>
                            <div class="px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-emerald-600 dark:text-emerald-400 font-semibold text-xs">
                                {{ $currency }}<span x-text="finalFee.toFixed(2)"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Split Payment Toggle -->
                    <div class="flex items-center justify-between border-t border-line dark:border-line pt-3">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">
                            Payment Breakdown
                        </label>
                        <button type="button" 
                                @click="toggleSplitPayment()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors cursor-pointer border"
                                :class="isSplitPayment ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white dark:bg-slate-900 hover:bg-slate-100 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                            <span x-text="isSplitPayment ? '✓ Split Payment Active' : 'Split Payment (2 Methods)'"></span>
                        </button>
                    </div>

                    <input type="hidden" name="is_split_payment" :value="isSplitPayment ? '1' : '0'">

                    <!-- Standard Single Payment Mode -->
                    <div x-show="!isSplitPayment" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Paid Now Amount ({{ $currency }})</label>
                            <input type="number" 
                                   name="initial_payment_amount" 
                                   x-model="paidNowAmount" 
                                   min="0" 
                                   step="any" 
                                   placeholder="0" 
                                   class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs tabular-nums focus:border-accent focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="card">Card (POS)</option>
                                <option value="netbanking">Net Banking</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Dual / Split Payment Box -->
                    <div x-show="isSplitPayment" class="p-4 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-fg-muted dark:text-fg-muted">Split into 2 Payment Modes</span>
                            <span class="text-fg-muted dark:text-fg-muted">
                                Total Paid: <strong class="text-fg dark:text-white font-semibold tabular-nums">{{ $currency }}<span x-text="Number(paidNowAmount || 0).toFixed(2)"></span></strong>
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Part 1 -->
                            <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 1</span>
                                    <span class="text-xs text-fg-muted">First part</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <select name="split_method_1" 
                                            x-model="splitMethod1" 
                                            class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI / QR</option>
                                        <option value="card">Card / POS</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                    <input type="number" 
                                           name="split_amount_1" 
                                           x-model="splitAmount1" 
                                           @input="updateSplitFromFirst()" 
                                           step="any" 
                                           min="0" 
                                           placeholder="Amount" 
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium tabular-nums text-xs focus:border-accent focus:outline-none">
                                </div>
                                <input type="text" 
                                       name="split_ref_1" 
                                       x-model="splitRef1" 
                                       placeholder="Ref / Txn ID (Optional)" 
                                       class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                <input type="hidden" name="split_cash_amount" :value="splitMethod1 === 'cash' ? splitAmount1 : (splitMethod2 === 'cash' ? splitAmount2 : 0)">
                            </div>

                            <!-- Part 2 (Auto-calculated, Editable) -->
                            <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 2</span>
                                    <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Auto-remaining (Editable)</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    <select name="split_method_2" 
                                            x-model="splitMethod2" 
                                            class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                        <option value="upi">UPI / QR</option>
                                        <option value="card">Card / POS</option>
                                        <option value="cash">Cash</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                    <input type="number" 
                                           name="split_amount_2" 
                                           x-model="splitAmount2" 
                                           @input="updateSplitFromSecond()" 
                                           step="any" 
                                           min="0" 
                                           placeholder="Remaining" 
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium tabular-nums text-xs focus:border-accent focus:outline-none">
                                </div>
                                <input type="text" 
                                       name="split_ref_2" 
                                       x-model="splitRef2" 
                                       placeholder="Ref / Txn ID (Optional)" 
                                       class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                <input type="hidden" name="split_online_amount" :value="splitMethod2 !== 'cash' ? splitAmount2 : (splitMethod1 !== 'cash' ? splitAmount1 : 0)">
                                <input type="hidden" name="split_online_method" :value="splitMethod2 !== 'cash' ? splitMethod2 : (splitMethod1 !== 'cash' ? splitMethod1 : 'upi')">
                                <input type="hidden" name="split_online_reference" :value="splitRef2 || splitRef1 || ''">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4 pt-1">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Transaction Ref / Cheque No</label>
                            <input type="text" 
                                   name="transaction_reference" 
                                   placeholder="e.g. UPI Ref / Txn ID" 
                                   class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                        </div>

                        <div class="flex items-center justify-between p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                            <div>
                                <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">Remaining Due Balance</span>
                                <p class="text-sm font-semibold mt-0.5" :class="remainingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'">
                                    {{ $currency }}<span x-text="remainingBalance.toFixed(2)"></span>
                                </p>
                            </div>
                            <span class="px-2 py-1 rounded-md text-xs font-semibold" 
                                  :class="remainingBalance > 0 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 border border-amber-200 dark:border-amber-500/30' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30'"
                                  x-text="remainingBalance > 0 ? 'Partial / Pending' : 'Fully Paid'"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 2.1 DUO PLAN: 2ND PERSON DETAILS (Appears when Duo Plan selected) -->
            <div x-show="isDuo" x-transition class="p-4 sm:p-6 rounded-xl border border-indigo-200 dark:border-indigo-500/40 space-y-4 sm:space-y-6">
                <div class="flex items-center justify-between border-b border-indigo-200 dark:border-indigo-500/20 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-semibold shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-fg dark:text-white">Duo Partner Details (Member #2)</h2>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-indigo-50 dark:bg-indigo-500/20 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30">2 Persons Total</span>
                            </div>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Primary member is Member #1. Enter the details for the 2nd person included in this Duo plan.</p>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" 
                               name="group_members[0][first_name]" 
                               :required="isDuo"
                               placeholder="e.g. Rahul" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Last Name</label>
                        <input type="text" 
                               name="group_members[0][last_name]" 
                               placeholder="e.g. Sharma" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                        <input type="tel" 
                               name="group_members[0][phone]" 
                               :required="isDuo"
                               placeholder="e.g. 9876543210" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Email Address</label>
                        <input type="email" 
                               name="group_members[0][email]" 
                               placeholder="partner@example.com" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Gender</label>
                        <select name="group_members[0][gender]" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <x-dob-picker name="group_members[0][dob]" />

                    <div class="sm:col-span-2 lg:col-span-3">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Relationship with Primary Member</label>
                        <input type="text" 
                               name="group_members[0][relation]" 
                               value="Duo Partner"
                               placeholder="e.g. Spouse / Friend / Workout Partner / Colleague" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                    </div>
                </div>
            </div>

            <!-- 2.2 FAMILY / GROUP PLAN: ADDITIONAL MEMBERS (Appears when Multi-Member Plan selected) -->
            <div x-show="maxMembers > 2" x-transition class="p-4 sm:p-6 rounded-xl border border-purple-200 dark:border-purple-500/40 space-y-4 sm:space-y-6">
                <div class="flex items-center justify-between border-b border-purple-200 dark:border-purple-500/20 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-400 flex items-center justify-center font-semibold shrink-0">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="text-base font-semibold text-fg dark:text-white" x-text="(planType === 'group' ? 'Group Members' : 'Family Members') + ' Details (' + maxMembers + ' People Total)'"></h2>
                                <span class="px-2 py-0.5 rounded text-xs font-semibold bg-purple-50 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30" x-text="'1 Primary + ' + (maxMembers - 1) + ' Additional'"></span>
                            </div>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Primary member is Member #1 (entered above). Enter details for the remaining members below.</p>
                        </div>
                    </div>
                </div>

                <div class="space-y-4 sm:space-y-6">
                    @foreach([
                        ['idx' => 0, 'label' => 'Member #2', 'default_rel' => 'Partner / Member'],
                        ['idx' => 1, 'label' => 'Member #3', 'default_rel' => 'Member / Friend'],
                        ['idx' => 2, 'label' => 'Member #4', 'default_rel' => 'Member / Friend'],
                        ['idx' => 3, 'label' => 'Member #5', 'default_rel' => 'Member / Friend'],
                        ['idx' => 4, 'label' => 'Member #6', 'default_rel' => 'Member / Friend'],
                        ['idx' => 5, 'label' => 'Member #7', 'default_rel' => 'Member / Friend'],
                        ['idx' => 6, 'label' => 'Member #8', 'default_rel' => 'Member / Friend'],
                        ['idx' => 7, 'label' => 'Member #9', 'default_rel' => 'Member / Friend'],
                        ['idx' => 8, 'label' => 'Member #10', 'default_rel' => 'Member / Friend'],
                    ] as $slot)
                        <div x-show="maxMembers >= {{ $slot['idx'] + 2 }}" class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between border-b border-line dark:border-line pb-2">
                                <span class="text-xs font-semibold text-purple-700 dark:text-purple-300 flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    <span>{{ $slot['label'] }}</span>
                                </span>
                                <span class="text-xs text-fg-muted font-semibold" x-text="'Slot ' + ({{ $slot['idx'] }} + 2) + ' of ' + maxMembers"></span>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">First Name <span class="text-rose-500">*</span></label>
                                    <input type="text" 
                                           name="group_members[{{ $slot['idx'] }}][first_name]" 
                                           :required="maxMembers >= {{ $slot['idx'] + 2 }} && {{ $slot['idx'] }} === 0"
                                           placeholder="First name" 
                                           class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-purple-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Last Name</label>
                                    <input type="text" 
                                           name="group_members[{{ $slot['idx'] }}][last_name]" 
                                           placeholder="Last name" 
                                           class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-purple-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Phone Number</label>
                                    <input type="tel" 
                                           name="group_members[{{ $slot['idx'] }}][phone]" 
                                           placeholder="Phone (or primary phone)" 
                                           class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-purple-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Gender</label>
                                    <select name="group_members[{{ $slot['idx'] }}][gender]" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-purple-500 focus:outline-none">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Relationship</label>
                                    <input type="text" 
                                           name="group_members[{{ $slot['idx'] }}][relation]" 
                                           value="{{ $slot['default_rel'] }}"
                                           placeholder="e.g. {{ $slot['default_rel'] }}" 
                                           class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-purple-500 focus:outline-none">
                                </div>
                                <div>
                                    <x-dob-picker name="group_members[{{ $slot['idx'] }}][dob]" />
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Email</label>
                                    <input type="email" 
                                           name="group_members[{{ $slot['idx'] }}][email]" 
                                           placeholder="Optional email" 
                                           class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-purple-500 focus:outline-none">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- 3. FITNESS INFORMATION SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Fitness Information</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Body metrics, target goals, and medical history</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <!-- Current Weight -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Current Weight (kg)</label>
                        <input type="number" 
                               name="weight" 
                               value="{{ old('weight') }}" 
                               step="0.1" 
                               placeholder="e.g. 75.5" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Target Weight -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Target Weight (kg)</label>
                        <input type="number" 
                               name="target_weight" 
                               value="{{ old('target_weight') }}" 
                               step="0.1" 
                               placeholder="e.g. 68.0" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Height with Unit Toggle -->
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Height</label>
                            <div class="flex items-center gap-1 bg-raised dark:bg-surface p-0.5 rounded-lg border border-line dark:border-line text-xs">
                                <button type="button" 
                                        @click="heightUnit = 'cm'"
                                        class="px-1.5 py-0.5 rounded font-semibold transition-all"
                                        :class="heightUnit === 'cm' ? 'bg-emerald-500 text-slate-950' : 'text-slate-500 dark:text-slate-400'">cm</button>
                                <button type="button" 
                                        @click="heightUnit = 'ft'"
                                        class="px-1.5 py-0.5 rounded font-semibold transition-all"
                                        :class="heightUnit === 'ft' ? 'bg-emerald-500 text-slate-950' : 'text-slate-500 dark:text-slate-400'">ft.in</button>
                            </div>
                        </div>
                        <input type="hidden" name="height_unit" :value="heightUnit">
                        <input type="text" 
                               name="height" 
                               value="{{ old('height') }}" 
                               :placeholder="heightUnit === 'cm' ? 'e.g. 175' : 'e.g. 5.9'" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                    </div>

                    <!-- Fitness Goal -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Fitness Goal</label>
                        <select name="fitness_goal" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">
                            <option value="">Select Goal</option>
                            @foreach(['General Fitness', 'Weight Loss', 'Muscle Gain', 'Endurance', 'Bodybuilding', 'Rehabilitation', 'Athletic Performance', 'Other'] as $goal)
                                <option value="{{ $goal }}" {{ old('fitness_goal') === $goal ? 'selected' : '' }}>{{ $goal }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Medical History / Health Conditions (Full Width) -->
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Health Conditions / Medical History</label>
                        <textarea name="medical_history" 
                                  rows="2" 
                                  placeholder="Any medical conditions, injuries, allergies, past surgeries, or physical limitations..." 
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 focus:outline-none">{{ old('medical_history') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- 4. EMERGENCY CONTACT SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 sm:space-y-6">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 dark:bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Emergency Contact</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Whom to notify in case of an emergency</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- Contact Name -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Contact Name</label>
                        <input type="text" 
                               name="emergency_contact_name" 
                               value="{{ old('emergency_contact_name') }}" 
                               placeholder="e.g. Jane Doe" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <!-- Contact Phone -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Contact Phone</label>
                        <input type="tel" 
                               name="emergency_contact_phone" 
                               value="{{ old('emergency_contact_phone') }}" 
                               placeholder="e.g. 9876543211" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                    </div>

                    <!-- Relation -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Relation</label>
                        <select name="emergency_relation" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-rose-500 focus:ring-1 focus:ring-rose-500 focus:outline-none">
                            <option value="">Select Relation</option>
                            @foreach(['Spouse', 'Parent', 'Sibling', 'Child', 'Friend', 'Guardian', 'Other'] as $rel)
                                <option value="{{ $rel }}" {{ old('emergency_relation') === $rel ? 'selected' : '' }}>{{ $rel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- 5. IDENTIFICATION DOCUMENTS SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4 sm:space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-semibold shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-semibold text-fg dark:text-white">Identification Documents</h2>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">KYC verification, government IDs and document attachments</p>
                        </div>
                    </div>

                    <button type="button" 
                            @click="addIdDoc()" 
                            class="self-start sm:self-auto px-3 py-1.5 rounded-xl bg-cyan-50 hover:bg-cyan-100 text-cyan-700 border border-cyan-200 dark:bg-cyan-500/15 dark:hover:bg-cyan-500/25 dark:text-cyan-400 dark:border-cyan-500/30 text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add ID</span>
                    </button>
                </div>

                <!-- Dynamic ID Rows -->
                <div class="space-y-3">
                    <template x-for="(doc, index) in idDocs" :key="index">
                        <div class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line flex flex-col lg:flex-row items-stretch lg:items-center gap-3">
                            <!-- ID Type Dropdown -->
                            <div class="w-full lg:w-48">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">ID Type</label>
                                <select :name="`id_documents[${index}][type]`" 
                                        x-model="doc.type" 
                                        class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-cyan-500 focus:outline-none">
                                    <option value="Aadhaar Card">Aadhaar Card</option>
                                    <option value="Driving License">Driving License</option>
                                    <option value="Passport">Passport</option>
                                    <option value="Voter ID">Voter ID</option>
                                    <option value="PAN Card">PAN Card</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>

                            <!-- ID Number -->
                            <div class="flex-grow">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">ID Number</label>
                                <input type="text" 
                                       :name="`id_documents[${index}][number]`" 
                                       x-model="doc.number" 
                                       placeholder="e.g. 1234 5678 9012" 
                                       class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-cyan-500 focus:outline-none">
                            </div>

                            <!-- Front File Upload -->
                            <div class="w-full lg:w-40">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Front Document</label>
                                <label class="px-3 py-2 rounded-xl bg-surface hover:bg-raised border border-line text-fg-muted dark:bg-surface dark:hover:bg-raised dark:border-line dark:text-fg-muted text-xs font-semibold flex items-center justify-between gap-1.5 cursor-pointer transition-colors overflow-hidden">
                                    <span class="truncate" x-text="doc.frontFileName || 'Upload Front'"></span>
                                    <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <input type="file" 
                                           :name="`id_documents[${index}][front]`" 
                                           accept="image/*,application/pdf" 
                                           class="hidden" 
                                           @change="onDocFileChange($event, index, 'front')">
                                </label>
                            </div>

                            <!-- Back File Upload -->
                            <div class="w-full lg:w-40">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Back Document</label>
                                <label class="px-3 py-2 rounded-xl bg-surface hover:bg-raised border border-line text-fg-muted dark:bg-surface dark:hover:bg-raised dark:border-line dark:text-fg-muted text-xs font-semibold flex items-center justify-between gap-1.5 cursor-pointer transition-colors overflow-hidden">
                                    <span class="truncate" x-text="doc.backFileName || 'Upload Back'"></span>
                                    <svg class="w-3.5 h-3.5 text-fg-muted dark:text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                    <input type="file" 
                                           :name="`id_documents[${index}][back]`" 
                                           accept="image/*,application/pdf" 
                                           class="hidden" 
                                           @change="onDocFileChange($event, index, 'back')">
                                </label>
                            </div>

                            <!-- Delete Row Button -->
                            <div class="flex items-end justify-end pt-1">
                                <button type="button" 
                                        @click="removeIdDoc(index)" 
                                        title="Remove Document" 
                                        class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- 6. ADDITIONAL NOTES SECTION -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Additional Notes</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Internal remarks visible only to gym administrators</p>
                    </div>
                </div>

                <textarea name="notes" 
                          rows="3" 
                          placeholder="Any internal notes about the member (visible to staff only)..." 
                          class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:ring-1 focus:ring-amber-500 focus:outline-none">{{ old('notes') }}</textarea>
            </div>

            <!-- Bottom Actions Bar -->
            <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                <a href="{{ route('app.members.index') }}" 
                   class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold text-center transition-all">
                    Cancel
                </a>

                <button type="submit" 
                        class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    <span>Add Member</span>
                </button>
            </div>
        </form>

        <!-- ========================================================================= -->
        <!-- MODE 2: ASSIGN EXISTING MEMBER BY PHONE -->
        <!-- ========================================================================= -->
        <div x-show="mode === 'existing'" class="space-y-4 sm:space-y-6">
            <!-- Search Phone Input Card -->
            <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-4">
                    <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-semibold text-fg dark:text-white">Search Member by Phone</h2>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Enter the member's registered phone number to find their existing account</p>
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <div class="relative flex items-center flex-grow w-full">
                        <span class="absolute left-3 text-xs font-semibold text-fg-muted dark:text-fg-muted">+91</span>
                        <input type="tel" 
                               x-model="searchPhone" 
                               @keydown.enter.prevent="searchMemberByPhone()" 
                               placeholder="Enter 10-digit phone number..." 
                               class="w-full pl-12 pr-4 py-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                    </div>

                    <button type="button" 
                            @click="searchMemberByPhone()" 
                            :disabled="lookupLoading || !searchPhone" 
                            class="w-full sm:w-auto px-6 py-3 rounded-xl bg-amber-500 hover:bg-amber-400 disabled:opacity-50 text-slate-950 font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <template x-if="lookupLoading">
                            <svg class="w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                        </template>
                        <template x-if="!lookupLoading">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </template>
                        <span>Search Member</span>
                    </button>
                </div>
            </div>

            <!-- When Member Found -->
            <template x-if="lookupCompleted && existingMember">
                <form action="{{ route('app.members.store') }}" method="POST" class="space-y-4 sm:space-y-6">
                    @csrf
                    <input type="hidden" name="mode" value="existing">
                    <input type="hidden" name="existing_member_id" :value="existingMember.id">

                    <!-- Member Profile Summary Card -->
                    <div class="p-4 sm:p-6 rounded-xl border border-emerald-200 dark:border-emerald-500/40 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-4">
                            <div class="flex items-center gap-3 sm:gap-4 min-w-0">
                                <div class="w-12 h-12 sm:w-14 sm:h-14 rounded-full bg-raised dark:bg-raised border-2 border-emerald-500/50 overflow-hidden flex items-center justify-center shrink-0">
                                    <template x-if="existingMember.photo_path">
                                        <img :src="'/storage/' + existingMember.photo_path" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!existingMember.photo_path">
                                        <span class="text-base sm:text-lg font-semibold text-emerald-600 dark:text-emerald-400" x-text="existingMember.first_name[0] + (existingMember.last_name ? existingMember.last_name[0] : '')"></span>
                                    </template>
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <h3 class="text-sm sm:text-base font-semibold text-fg dark:text-white truncate" x-text="existingMember.full_name"></h3>
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold shrink-0"
                                              :class="existingMember.status === 'ACTIVE' ? 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400'"
                                              x-text="existingMember.status"></span>
                                    </div>
                                    <p class="text-xs sm:text-xs text-fg-muted dark:text-fg-muted mt-0.5 flex flex-wrap items-center gap-x-3 gap-y-1">
                                        <span>📞 <strong class="text-fg dark:text-fg" x-text="existingMember.phone"></strong></span>
                                        <span x-show="existingMember.email">✉️ <span class="text-fg-muted dark:text-fg-muted" x-text="existingMember.email"></span></span>
                                        <span>📍 <span class="text-fg-muted dark:text-fg-muted" x-text="existingMember.branch_name"></span></span>
                                    </p>
                                </div>
                            </div>
                            <span class="self-start sm:self-auto px-3 py-1 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/30 text-xs font-semibold">
                                Account Found
                            </span>
                        </div>

                        <!-- Active Membership info if present -->
                        <div x-show="existingMember.active_plan" class="p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg-muted dark:text-fg-muted flex items-center justify-between">
                            <span>Current Plan: <strong class="text-fg dark:text-white" x-text="existingMember.active_plan"></strong></span>
                            <span class="text-fg-muted dark:text-fg-muted">Valid Till: <strong class="text-amber-600 dark:text-amber-400" x-text="existingMember.active_plan_end"></strong></span>
                        </div>
                    </div>

                    <!-- Assign Branch & Plan Details -->
                    <div class="p-4 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                        <h3 class="text-sm font-semibold text-fg dark:text-white border-b border-line dark:border-line pb-3">Assign to Branch & New Plan</h3>

                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Target Branch -->
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Gym Branch <span class="text-rose-500">*</span></label>
                                <select name="branch_id" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $activeBranchId == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Membership Plan -->
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">New Membership Plan</label>
                                <select name="membership_plan_id" 
                                        x-model="existingSelectedPlanId" 
                                        @change="onExistingPlanChange()" 
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                    <option value="">Select Plan (Optional)</option>
                                    @foreach($membershipPlans as $p)
                                        <option value="{{ $p->id }}">
                                            {{ $p->name }} ({{ strtoupper($p->plan_type ?? 'SINGLE') }} - {{ $p->max_members ?? 1 }} {{ ($p->max_members ?? 1) > 1 ? 'Persons' : 'Person' }}) - {{ $currency }}{{ number_format($p->price, 0) }} / {{ $p->duration_value }} {{ $p->duration_type }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Membership Start Date</label>
                                <input type="date" 
                                       name="start_date" 
                                       value="{{ now()->toDateString() }}" 
                                       onclick="this.showPicker && this.showPicker()"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none cursor-pointer">
                            </div>
                        </div>

                        <!-- Payment Fields if plan selected -->
                        <div x-show="existingSelectedPlanId" class="pt-3 space-y-3 border-t border-line dark:border-line">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Discount Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           name="discount" 
                                           x-model="existingDiscountAmount" 
                                           @input="onExistingDiscountChange()"
                                           min="0" 
                                           placeholder="0" 
                                           class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-amber-500 focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Net Payable Fee ({{ $currency }})</label>
                                    <div class="px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-emerald-600 dark:text-emerald-400 font-semibold text-xs">
                                        {{ $currency }}<span x-text="existingFinalFee.toFixed(2)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Existing Member Split Toggle -->
                            <div class="flex items-center justify-between border-t border-line dark:border-line pt-2">
                                <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Payment Breakdown</label>
                                <button type="button" 
                                        @click="toggleExistingSplitPayment()" 
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-medium transition-colors cursor-pointer border"
                                        :class="existingIsSplitPayment ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-50 dark:bg-slate-950 hover:bg-slate-100 text-slate-700 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                                    <span x-text="existingIsSplitPayment ? '✓ Split Payment Active' : 'Split Payment (2 Methods)'"></span>
                                </button>
                            </div>

                            <input type="hidden" name="is_split_payment" :value="existingIsSplitPayment ? '1' : '0'">

                            <!-- Standard Single Mode -->
                            <div x-show="!existingIsSplitPayment" class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Paid Now Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           name="initial_payment_amount" 
                                           x-model="existingPaidNowAmount" 
                                           min="0" 
                                           placeholder="0" 
                                           class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs tabular-nums focus:border-accent focus:outline-none">
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method</label>
                                    <select name="payment_method" x-model="existingPaymentMethod" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI / QR Code</option>
                                        <option value="card">Card (POS)</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                        <option value="bank_transfer">Bank Transfer</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Split Mode -->
                            <div x-show="existingIsSplitPayment" class="p-3.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-3">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-semibold text-fg-muted dark:text-fg-muted">Split into 2 Payment Modes</span>
                                    <span class="text-fg-muted dark:text-fg-muted">
                                        Total Paid: <strong class="text-fg dark:text-white font-semibold tabular-nums">{{ $currency }}<span x-text="Number(existingPaidNowAmount || 0).toFixed(2)"></span></strong>
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Part 1 -->
                                    <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 1</span>
                                            <span class="text-xs text-fg-muted">First part</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <select name="split_method_1" 
                                                    x-model="existingSplitMethod1" 
                                                    class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                                <option value="cash">Cash</option>
                                                <option value="upi">UPI / QR</option>
                                                <option value="card">Card / POS</option>
                                                <option value="netbanking">Net Banking</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                            <input type="number" 
                                                   name="split_amount_1" 
                                                   x-model="existingSplitAmount1" 
                                                   @input="updateExistingSplitFromFirst()" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="Amount" 
                                                   class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium tabular-nums text-xs focus:border-accent focus:outline-none">
                                        </div>
                                        <input type="text" 
                                               name="split_ref_1" 
                                               x-model="existingSplitRef1" 
                                               placeholder="Ref / Txn ID (Optional)" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                        <input type="hidden" name="split_cash_amount" :value="existingSplitMethod1 === 'cash' ? existingSplitAmount1 : (existingSplitMethod2 === 'cash' ? existingSplitAmount2 : 0)">
                                    </div>

                                    <!-- Part 2 (Auto-calculated, Editable) -->
                                    <div class="p-3 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line space-y-2">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="font-medium text-fg-muted dark:text-fg-muted">Payment 2</span>
                                            <span class="text-xs text-indigo-600 dark:text-indigo-400 font-medium">Auto-remaining (Editable)</span>
                                        </div>
                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            <select name="split_method_2" 
                                                    x-model="existingSplitMethod2" 
                                                    class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-medium focus:border-accent focus:outline-none">
                                                <option value="upi">UPI / QR</option>
                                                <option value="card">Card / POS</option>
                                                <option value="cash">Cash</option>
                                                <option value="netbanking">Net Banking</option>
                                                <option value="cheque">Cheque</option>
                                                <option value="bank_transfer">Bank Transfer</option>
                                            </select>
                                            <input type="number" 
                                                   name="split_amount_2" 
                                                   x-model="existingSplitAmount2" 
                                                   @input="updateExistingSplitFromSecond()" 
                                                   step="any" 
                                                   min="0" 
                                                   placeholder="Remaining" 
                                                   class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium tabular-nums text-xs focus:border-accent focus:outline-none">
                                        </div>
                                        <input type="text" 
                                               name="split_ref_2" 
                                               x-model="existingSplitRef2" 
                                               placeholder="Ref / Txn ID (Optional)" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-accent focus:outline-none">
                                        <input type="hidden" name="split_online_amount" :value="existingSplitMethod2 !== 'cash' ? existingSplitAmount2 : (existingSplitMethod1 !== 'cash' ? existingSplitAmount1 : 0)">
                                        <input type="hidden" name="split_online_method" :value="existingSplitMethod2 !== 'cash' ? existingSplitMethod2 : (existingSplitMethod1 !== 'cash' ? existingSplitMethod1 : 'upi')">
                                        <input type="hidden" name="split_online_reference" :value="existingSplitRef2 || existingSplitRef1 || ''">
                                    </div>
                                </div>
                            </div>

                                            <select name="split_online_method" 
                                                    x-model="existingSplitOnlineMethod" 
                                                    class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-amber-500 focus:outline-none">
                                                <option value="upi">UPI / QR Code</option>
                                                <option value="card">Card (POS)</option>
                                                <option value="netbanking">Net Banking</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </div>
                                        <input type="text" 
                                               name="split_online_reference" 
                                               x-model="existingSplitOnlineRef" 
                                               placeholder="UPI Ref / Txn ID (Optional)" 
                                               class="w-full px-3 py-1.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Assign Member -->
                    <div class="flex flex-col-reverse sm:flex-row sm:items-center justify-between gap-3 p-4 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
                        <button type="button" @click="existingMember = null; lookupCompleted = false;" class="w-full sm:w-auto text-xs text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white font-semibold transition-colors py-2 text-center">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Assign Member to Gym</span>
                        </button>
                    </div>
                </form>
            </template>

            <!-- When Member Not Found -->
            <template x-if="lookupCompleted && !existingMember">
                <div class="p-6 sm:p-8 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-center space-y-4">
                    <div class="w-12 h-12 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400 flex items-center justify-center mx-auto text-xl font-semibold">
                        🔍
                    </div>
                    <div class="max-w-md mx-auto">
                        <h3 class="text-sm font-semibold text-fg dark:text-white">No Member Found</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">No existing member was found matching phone number <strong class="text-amber-600 dark:text-amber-400" x-text="searchPhone"></strong>.</p>
                    </div>
                    <button type="button" 
                            @click="mode = 'new'" 
                            class="px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs inline-flex items-center gap-2 transition-all cursor-pointer ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Register as New Member</span>
                    </button>
                </div>
            </template>
        </div>

        <!-- ========================================================================= -->
        <!-- WEBCAM CAPTURE MODAL POPUP -->
        <!-- ========================================================================= -->
        <div x-show="showCameraModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 dark:bg-black/85 backdrop-blur-sm overflow-y-auto"
             style="display: none;">
            <div @click.away="closeWebcam()" class="w-full max-w-lg rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden space-y-4 p-4 sm:p-5 text-fg dark:text-white max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                        <h3 class="text-sm font-semibold text-fg dark:text-white">Capture Member Photo</h3>
                    </div>
                    <button type="button" @click="closeWebcam()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-base">✕</button>
                </div>

                <!-- Video Preview Box -->
                <div class="relative aspect-video rounded-xl bg-black border border-line dark:border-line overflow-hidden flex items-center justify-center">
                    <video x-ref="cameraVideo" autoplay playsinline class="w-full h-full object-cover"></video>
                    <canvas x-ref="cameraCanvas" class="hidden"></canvas>
                </div>

                <!-- Modal Actions -->
                <div class="flex items-center justify-between pt-2">
                    <button type="button" 
                            @click="closeWebcam()" 
                            class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line text-xs font-semibold transition-all">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="takeSnapshot()" 
                            class="px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Capture Photo</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>