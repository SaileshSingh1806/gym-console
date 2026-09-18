<x-app-layout header="Member Profile">
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

        $finalAmt = (float) ($activeMembership?->final_amount ?? 0);
        $paidAmt = (float) ($activeMembership?->paid_amount ?? 0);
        $remainingAmt = max(0, $finalAmt - $paidAmt);

        $measurementsList = $member->metadata['measurements'] ?? [];
        $latestMeasurement = !empty($measurementsList) ? $measurementsList[0] : null;
        $logoUrl = auth()->user()->tenant?->logo_url ?? ($platformSettings['logo_url'] ?? null);
    @endphp

    <script>
        window.__MEMBERSHIP_PLANS__ = {{ Js::from($plansJson) }};
    </script>

    <div x-data="{
        activeTab: 'subscriptions',
        showAddPaymentModal: false,
        showAddSubscriptionModal: false,
        showAddPtModal: false,
        showEditModal: {{ request()->boolean('edit') ? 'true' : 'false' }},
        showWhatsappMenu: false,
        showInvoiceModal: false,
        showAddMeasurementModal: false,
        selectedInvoice: {
            id: null,
            receipt_no: '',
            date: '',
            amount: '0.00',
            method: 'CASH',
            ref: '',
            plan_name: '',
            plan_duration: '',
            start_date: '',
            end_date: '',
            total: '0.00',
            paid: '0.00'
        },
        openInvoice(inv) {
            this.selectedInvoice = inv;
            this.showInvoiceModal = true;
        },

        // Edit Member Photo & Webcam State
        editPhotoPreview: '{{ $member->photo_path ? asset("storage/" . $member->photo_path) : "" }}',
        editCapturedPhotoData: '',
        editRemovePhoto: false,
        editShowCameraModal: false,
        editCameraStream: null,
        editCameraActive: false,
        editCameraError: null,
        editHeightUnit: '{{ $member->metadata["height_unit"] ?? "cm" }}',

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

        // Add Subscription State
        plans: window.__MEMBERSHIP_PLANS__ || {},
        newSubPlanId: '',
        newSubDiscount: 0,
        newSubPaidNow: 0,
        newSubPaymentMethod: 'cash',
        get newSubPrice() {
            return this.newSubPlanId && this.plans[this.newSubPlanId] ? this.plans[this.newSubPlanId].price : 0;
        },
        get newSubFinalFee() {
            return Math.max(0, this.newSubPrice - Number(this.newSubDiscount || 0));
        },
        onNewSubPlanChange() {
            this.newSubPaidNow = this.newSubFinalFee;
        },

        // PT Package Modal State
        ptTrainerId: '{{ $assignedTrainer?->id ?? '' }}',
        ptPackageName: '',
        ptSessions: 12,
        ptValidityDays: 30,
        ptStartDate: '{{ now()->format('Y-m-d') }}',
        ptAmount: 0,
        ptDiscount: 0,
        ptTax: 0,
        ptCollectedAmount: 0,
        ptPaymentMethod: 'cash',
        ptRef: '',
        ptNotes: '',
        get ptTotal() {
            return Math.max(0, Number(this.ptAmount || 0) - Number(this.ptDiscount || 0) + Number(this.ptTax || 0));
        },
        get ptEndDate() {
            if (!this.ptStartDate) return '';
            let d = new Date(this.ptStartDate);
            d.setDate(d.getDate() + Number(this.ptValidityDays || 30));
            return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        // Add Payment State
        paymentAmount: '{{ $remainingAmt > 0 ? $remainingAmt : $finalAmt }}',
        paymentMethod: 'cash',
        paymentRef: '',
        paymentNotes: ''
    }" class="space-y-6 pb-20 max-w-7xl mx-auto">

        <!-- Top Bar Navigation -->
        <div class="flex items-center justify-between">
            <a href="{{ route('app.members.index') }}" 
               class="px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Members</span>
            </a>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                <span class="w-2 h-2 rounded-full {{ $member->status === 'ACTIVE' ? 'bg-emerald-400' : ($member->status === 'SUSPENDED' ? 'bg-rose-400' : 'bg-orange-400') }}"></span>
                <span>Branch: <strong class="text-slate-900 dark:text-white">{{ $member->branch->name ?? 'Main Branch' }}</strong></span>
            </div>
        </div>

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <!-- Top Header Profile Banner -->
        <div class="p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
            <!-- Left Info Block -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                <div>
                    <div class="flex flex-wrap items-center gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $member->full_name }}</h1>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold">
                            {{ $member->member_code }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 mt-2.5">
                        <!-- Status Badge -->
                        @if($member->status === 'ACTIVE')
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                <span>Active</span>
                            </span>
                        @elseif($member->status === 'SUSPENDED')
                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                <span>Frozen</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-500/15 border border-orange-200 dark:border-orange-500/30 text-orange-700 dark:text-orange-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 dark:bg-orange-400"></span>
                                <span>{{ $member->status }}</span>
                            </span>
                        @endif

                        <!-- ID Badge -->
                        <span class="p-1 rounded-md bg-blue-50 dark:bg-blue-500/15 border border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-400 text-xs font-bold" title="Digital ID Card">
                            💳
                        </span>

                        <!-- Plan Badge -->
                        @if($activeMembership && $activeMembership->plan)
                            <span class="px-3 py-1 rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-bold text-xs flex items-center gap-1.5 shadow-sm">
                                <span>👑</span>
                                <span>{{ $activeMembership->plan->name }}</span>
                            </span>
                        @endif

                        <!-- Phone -->
                        <a href="tel:{{ $member->phone }}" class="px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700/60 dark:text-slate-300 dark:hover:text-white font-semibold text-xs flex items-center gap-1.5 transition-colors shadow-sm">
                            <span>📞</span>
                            <span>{{ $member->phone }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Metrics & Actions Block -->
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto justify-start lg:justify-end">
                <!-- Metric 1: Days Left -->
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center min-w-[90px] shadow-sm">
                    <p class="text-xl font-black {{ ($daysLeft !== null && $daysLeft < 7) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                        {{ $daysLeft !== null ? max(0, $daysLeft) : '0' }}
                    </p>
                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mt-0.5">DAYS LEFT</span>
                </div>

                <!-- Metric 2: Months Active -->
                <div class="px-4 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center min-w-[100px] shadow-sm">
                    <p class="text-xl font-black text-slate-900 dark:text-white">{{ $monthsActive }}</p>
                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mt-0.5">MONTHS ACTIVE</span>
                </div>

                <!-- Actions: Add Payment -->
                <button type="button" 
                        @click="showAddPaymentModal = true" 
                        class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Add Payment</span>
                </button>

                <!-- Actions: Edit -->
                <button type="button" 
                        @click="showEditModal = true" 
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    <span>Edit</span>
                </button>

                <!-- Actions: Delete -->
                <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                      data-confirm="Are you sure you want to permanently delete member '{{ addslashes($member->full_name) }}'? All membership records, workout history, and payments will be removed." 
                      data-confirm-title="Delete Gym Member" 
                      data-confirm-btn="Yes, Delete Member" 
                      data-confirm-type="danger" 
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3.5 py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 dark:text-rose-400 dark:border-rose-500/30 font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        <span>Delete</span>
                    </button>
                </form>

                <!-- Actions: WhatsApp Menu -->
                <div class="relative" @click.away="showWhatsappMenu = false">
                    <button type="button" 
                            @click="showWhatsappMenu = !showWhatsappMenu" 
                            class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-emerald-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-emerald-400 dark:border-slate-700 font-bold text-xs flex items-center gap-1.5 transition-all cursor-pointer shadow-sm">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>WhatsApp</span>
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>

                    <div x-show="showWhatsappMenu" 
                         x-transition 
                         class="absolute right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl py-2 z-50 text-xs">
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Hello ' . $member->first_name . ', greeting from ' . (auth()->user()->tenant->name ?? 'Gym') . '!') }}" 
                           target="_blank" 
                           class="px-4 py-2.5 text-slate-700 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800 flex items-center gap-2 transition-colors">
                            <span>💬</span>
                            <span>Direct Chat</span>
                        </a>
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Dear ' . $member->first_name . ', your membership expires on ' . ($activeMembership ? $activeMembership->end_date->format('d M Y') : 'soon') . '. Please renew to continue your workout routine without interruption.') }}" 
                           target="_blank" 
                           class="px-4 py-2.5 text-slate-700 hover:text-slate-900 hover:bg-slate-50 dark:text-slate-300 dark:hover:text-white dark:hover:bg-slate-800 flex items-center gap-2 transition-colors">
                            <span>⏳</span>
                            <span>Send Renewal Reminder</span>
                        </a>
                        @if($remainingAmt > 0)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Dear ' . $member->first_name . ', you have a pending fee balance of ' . $currency . number_format($remainingAmt, 0) . ' towards your membership at ' . (auth()->user()->tenant->name ?? 'Gym') . '. Kindly clear at the reception desk.') }}" 
                               target="_blank" 
                               class="px-4 py-2.5 text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10 flex items-center gap-2 transition-colors">
                                <span>💳</span>
                                <span>Send Fee Due Notice</span>
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Layout: Left Sidebar Profile & Right Content Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ========================================================================= -->
            <!-- LEFT COLUMN: MEMBER PROFILE, MEMBERSHIP, FITNESS & GOALS, CONTACT & SYSTEM -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-4 space-y-6">

                <!-- 1. PHOTO BANNER & IDENTITY CARD -->
                <div class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                    <!-- Photo Header -->
                    <div class="relative aspect-[4/3] bg-slate-100 dark:bg-slate-950 flex items-center justify-center overflow-hidden">
                        @if($member->photo_path)
                            <img src="/storage/{{ $member->photo_path }}" alt="{{ $member->full_name }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-slate-100 dark:bg-gradient-to-tr dark:from-slate-900 dark:via-indigo-950 dark:to-slate-900 flex flex-col items-center justify-center text-slate-400 p-6 text-center">
                                <div class="w-20 h-20 rounded-full bg-white dark:bg-slate-800 border-2 border-slate-200 dark:border-slate-700 flex items-center justify-center text-2xl font-black text-slate-800 dark:text-white shadow-inner mb-2">
                                    {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-bold text-slate-500 dark:text-slate-400">No Photo Uploaded</span>
                            </div>
                        @endif

                        <!-- Floating Name & Age Pill Overlay -->
                        <div class="absolute bottom-0 inset-x-0 bg-gradient-to-t from-slate-950 via-slate-950/80 to-transparent p-4 flex items-end justify-between">
                            <div>
                                <h3 class="text-lg font-black text-white leading-tight">{{ $member->full_name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($member->dob)
                                        <span class="px-2 py-0.5 rounded-full bg-slate-800/90 border border-slate-700/80 text-[11px] font-bold text-slate-300">
                                            {{ $member->dob->age }} yrs
                                        </span>
                                    @endif

                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-black {{ strtolower($member->gender) === 'female' ? 'bg-pink-500/20 text-pink-400 border border-pink-500/30' : 'bg-blue-500/20 text-blue-400 border border-blue-500/30' }}">
                                        @if(strtolower($member->gender) === 'female') ♀ Female @elseif(strtolower($member->gender) === 'male') ♂ Male @else ⚧ Other @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Last Seen Attendance Indicator -->
                    <div class="p-3 bg-slate-50 dark:bg-slate-950/70 border-t border-slate-200 dark:border-slate-800/80 flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                        <span class="w-2 h-2 rounded-full {{ ($lastAttendance && $lastAttendance->check_in && $lastAttendance->check_in->isToday()) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400 dark:bg-slate-500' }}"></span>
                        <span>
                            @if($lastAttendance && $lastAttendance->check_in)
                                Last seen: <strong class="text-slate-800 dark:text-slate-200">{{ $lastAttendance->check_in->isToday() ? 'Today at ' . $lastAttendance->check_in->format('g:i A') : $lastAttendance->check_in->format('d M, g:i A') }}</strong>
                            @else
                                Last seen: <span class="text-slate-400 dark:text-slate-500">No recent check-in</span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- 2. MEMBERSHIP INFO CARD -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-200 dark:border-slate-800/80 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            💳
                        </div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">MEMBERSHIP INFO</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Gender</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ ucfirst($member->gender ?? 'Not Specified') }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Current Plan</span>
                            <span class="font-bold text-amber-600 dark:text-amber-400">{{ $activeMembership?->plan?->name ?? 'None' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Category</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $member->metadata['lead_source'] ?? 'General' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Assigned Trainer</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $assignedTrainer?->full_name ?? 'None' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Locker Number</span>
                            <span class="font-bold text-slate-700 dark:text-slate-200">{{ $member->metadata['locker_number'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Join Date</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $member->join_date ? $member->join_date->format('d/m/Y') : 'N/A' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Expiry</span>
                            <span class="font-bold text-slate-900 dark:text-white">{{ $activeMembership ? $activeMembership->end_date->format('d/m/Y') : '—' }}</span>
                        </div>
                    </div>

                    <!-- Freeze Account Action -->
                    <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80">
                        <form action="{{ route('app.members.freeze', $member->id) }}" method="POST">
                            @csrf
                            <button type="submit" 
                                    class="w-full py-2.5 rounded-xl border text-xs font-bold flex items-center justify-center gap-2 transition-all cursor-pointer shadow-sm {{ $member->status === 'SUSPENDED' ? 'bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border-emerald-200 dark:bg-emerald-500/10 dark:hover:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-500/30' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 hover:text-slate-900 border-slate-200 dark:bg-slate-950 dark:hover:bg-slate-800 dark:text-slate-300 dark:hover:text-white dark:border-slate-800' }}">
                                <span>{{ $member->status === 'SUSPENDED' ? '☀️ Unfreeze / Reactivate Account' : '❄️ Freeze Account' }}</span>
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 3. FITNESS & GOALS CARD -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-200 dark:border-slate-800/80 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-xs">
                            🏋️
                        </div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">FITNESS & GOALS</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Current Weight</span>
                            <span class="font-bold text-slate-900 dark:text-white">
                                {{ !empty($member->metadata['weight']) ? $member->metadata['weight'] . ' kg' : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Target Weight</span>
                            <span class="font-bold text-emerald-600 dark:text-emerald-400">
                                {{ !empty($member->metadata['target_weight']) ? $member->metadata['target_weight'] . ' kg' : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Height</span>
                            <span class="font-bold text-slate-900 dark:text-white">
                                {{ !empty($member->metadata['height']) ? $member->metadata['height'] . ' ' . ($member->metadata['height_unit'] ?? 'cm') : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Fitness Goal</span>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 font-bold text-[11px]">
                                {{ $member->metadata['fitness_goal'] ?? 'General Fitness' }}
                            </span>
                        </div>

                        <!-- Medical History Section -->
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80">
                            <span class="text-slate-500 dark:text-slate-400 font-medium block mb-1">Health Conditions / Medical History</span>
                            <p class="text-slate-700 dark:text-slate-300 bg-slate-50 dark:bg-slate-950 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800 text-[11px] leading-relaxed">
                                {{ $member->metadata['medical_history'] ?? 'No medical conditions or physical limitations reported.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 4. CONTACT & SYSTEM CARD -->
                <div class="p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-slate-200 dark:border-slate-800/80 pb-3">
                        <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-xs">
                            📱
                        </div>
                        <h3 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">CONTACT & SYSTEM</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Phone Number</span>
                            <span class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                                {{ $member->phone }}
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 text-xs" title="WhatsApp Chat">💬</a>
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Alternate Phone</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $member->metadata['alternate_phone'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Email Address</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300 truncate max-w-[170px]" title="{{ $member->email }}">{{ $member->email ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">City</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300">{{ $member->metadata['city'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Address</span>
                            <span class="font-bold text-slate-700 dark:text-slate-300 truncate max-w-[170px]" title="{{ $member->address }}">{{ $member->address ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Blood Group</span>
                            <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 font-black text-[11px]">
                                {{ $member->metadata['blood_group'] ?? 'Not Set' }}
                            </span>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80">
                            <span class="text-slate-500 dark:text-slate-400 font-medium block mb-1">Emergency Contact</span>
                            @if(!empty($member->emergency_contact_name))
                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1 shadow-sm">
                                    <div class="flex items-center justify-between font-bold text-slate-900 dark:text-white text-xs">
                                        <span>{{ $member->emergency_contact_name }}</span>
                                        <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold uppercase">{{ $member->metadata['emergency_relation'] ?? 'Contact' }}</span>
                                    </div>
                                    @if($member->emergency_contact_phone)
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 font-mono">
                                            📞 {{ $member->emergency_contact_phone }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">No emergency contact provided</span>
                            @endif
                        </div>

                        <!-- WhatsApp & System Rules -->
                        <div class="pt-2 border-t border-slate-200 dark:border-slate-800/80 space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">WhatsApp Reminders</span>
                                @if(!empty($member->metadata['whatsapp_disabled']))
                                    <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-bold text-[10px]">Disabled</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">Enabled</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-slate-500 dark:text-slate-400 font-medium">Max Daily Check-ins</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ !empty($member->metadata['max_daily_entries']) ? $member->metadata['max_daily_entries'] . ' per day' : 'Unlimited' }}</span>
                            </div>

                            <!-- KYC Identification Documents -->
                            <div>
                                <span class="text-slate-500 dark:text-slate-400 font-medium block mb-1">KYC Documents</span>
                                @if(!empty($member->metadata['id_documents']) && count($member->metadata['id_documents']) > 0)
                                    <div class="space-y-1.5">
                                        @foreach($member->metadata['id_documents'] as $doc)
                                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between text-[11px] shadow-sm">
                                                <div>
                                                    <span class="font-bold text-slate-900 dark:text-white block">{{ $doc['type'] ?? 'ID' }}</span>
                                                    <span class="text-slate-500 dark:text-slate-400 font-mono text-[10px]">{{ $doc['number'] ?? 'No number' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    @if(!empty($doc['front_path']))
                                                        <a href="/storage/{{ $doc['front_path'] }}" target="_blank" class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-cyan-700 dark:text-cyan-400 text-[9px] font-bold">Front</a>
                                                    @endif
                                                    @if(!empty($doc['back_path']))
                                                        <a href="/storage/{{ $doc['back_path'] }}" target="_blank" class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-cyan-700 dark:text-cyan-400 text-[9px] font-bold">Back</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-slate-400 dark:text-slate-500 italic text-[11px]">No ID documents uploaded</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ========================================================================= -->
            <!-- RIGHT COLUMN: TAB NAVIGATION & TABBED CONTENT -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-8 space-y-6">

                <!-- Scrollable Tab Header Bar -->
                <div class="flex items-center gap-1.5 overflow-x-auto p-1.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold no-scrollbar shadow-sm">
                    <button type="button" 
                            @click="activeTab = 'subscriptions'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'subscriptions' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Subscriptions
                    </button>

                    <button type="button" 
                            @click="activeTab = 'pt_packages'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'pt_packages' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        PT Packages
                    </button>

                    <button type="button" 
                            @click="activeTab = 'services'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'services' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Services
                    </button>

                    <button type="button" 
                            @click="activeTab = 'invoices'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'invoices' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Invoices
                    </button>

                    <button type="button" 
                            @click="activeTab = 'workouts'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'workouts' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Workout Plans
                    </button>

                    <button type="button" 
                            @click="activeTab = 'diets'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'diets' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Diet Plans
                    </button>

                    <button type="button" 
                            @click="activeTab = 'classes'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'classes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Classes
                    </button>

                    <button type="button" 
                            @click="activeTab = 'attendance'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'attendance' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Attendance
                    </button>

                    <button type="button" 
                            @click="activeTab = 'measurements'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'measurements' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Measurements
                    </button>

                    <button type="button" 
                            @click="activeTab = 'audit_trail'"
                            class="px-4 py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'audit_trail' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Audit Trail <span class="px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] ml-1">{{ $auditLogs->count() }}</span>
                    </button>
                </div>

                <!-- TAB 1: SUBSCRIPTIONS HISTORY -->
                <div x-show="activeTab === 'subscriptions'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-4 p-5">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Subscription History</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">All current and previous gym membership packages</p>
                        </div>

                        <button type="button" 
                                @click="showAddSubscriptionModal = true" 
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Subscription</span>
                        </button>
                    </div>

                    <!-- Subscription Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-3 px-3 font-semibold">Plan</th>
                                    <th class="py-3 px-3 font-semibold">Period</th>
                                    <th class="py-3 px-3 font-semibold">Total</th>
                                    <th class="py-3 px-3 font-semibold">Discount</th>
                                    <th class="py-3 px-3 font-semibold">Paid</th>
                                    <th class="py-3 px-3 font-semibold">Status</th>
                                    <th class="py-3 px-3 font-semibold text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @forelse($member->memberships()->latest()->get() as $sub)
                                    @php
                                        $subDaysLeft = (int) now()->startOfDay()->diffInDays($sub->end_date->startOfDay(), false);
                                        $firstPayment = $sub->payments->first();
                                    @endphp
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors">
                                        <!-- Plan -->
                                        <td class="py-3.5 px-3 font-bold text-slate-900 dark:text-white">
                                            {{ $sub->plan->name ?? 'Custom Plan' }}
                                        </td>

                                        <!-- Period -->
                                        <td class="py-3.5 px-3">
                                            <div class="font-medium text-slate-800 dark:text-slate-200">
                                                {{ $sub->start_date->format('d/m/Y') }} - {{ $sub->end_date->format('d/m/Y') }}
                                            </div>
                                            <div class="text-[10px] mt-0.5 {{ $sub->isExpired() ? 'text-slate-400 dark:text-slate-500' : ($subDaysLeft <= 7 ? 'text-rose-600 dark:text-rose-400 font-bold' : 'text-emerald-600 dark:text-emerald-400 font-semibold') }}">
                                                @if($sub->isExpired())
                                                    Expired
                                                @elseif($subDaysLeft == 0)
                                                    Expires today
                                                @else
                                                    {{ $subDaysLeft }} days left
                                                @endif
                                            </div>
                                        </td>

                                        <!-- Total -->
                                        <td class="py-3.5 px-3 font-bold text-slate-900 dark:text-white">
                                            {{ $currency }}{{ number_format($sub->final_amount, 2) }}
                                        </td>

                                        <!-- Discount -->
                                        <td class="py-3.5 px-3 text-slate-500 dark:text-slate-400">
                                            {{ $sub->discount_amount > 0 ? $currency . number_format($sub->discount_amount, 2) : '—' }}
                                        </td>

                                        <!-- Paid -->
                                        <td class="py-3.5 px-3 font-bold text-emerald-600 dark:text-emerald-400">
                                            {{ $currency }}{{ number_format($sub->paid_amount, 2) }}
                                        </td>

                                        <!-- Status -->
                                        <td class="py-3.5 px-3">
                                            @if($sub->status === 'ACTIVE' && !$sub->isExpired())
                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px]">
                                                    Active
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold text-[10px]">
                                                    Expired
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Actions -->
                                        <td class="py-3.5 px-3 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($firstPayment)
                                                    <button type="button" 
                                                            @click="openInvoice({
                                                                id: {{ $firstPayment->id }},
                                                                receipt_no: '{{ $firstPayment->receipt_number ?? ('RCP' . str_pad($firstPayment->id, 8, '0', STR_PAD_LEFT)) }}',
                                                                date: '{{ $firstPayment->payment_date ? $firstPayment->payment_date->format('d M Y') : $firstPayment->created_at->format('d M Y') }}',
                                                                amount: '{{ number_format($firstPayment->amount, 2) }}',
                                                                method: '{{ strtoupper($firstPayment->payment_method) }}',
                                                                ref: '{{ $firstPayment->transaction_reference ?? '' }}',
                                                                plan_name: '{{ $sub->plan->name ?? 'Gym Membership' }}',
                                                                plan_duration: '{{ $sub->plan ? ($sub->plan->duration_value . ' ' . $sub->plan->duration_type) : '' }}',
                                                                start_date: '{{ $sub->start_date->format('d M Y') }}',
                                                                end_date: '{{ $sub->end_date->format('d M Y') }}',
                                                                total: '{{ number_format($sub->final_amount, 2) }}',
                                                                paid: '{{ number_format($firstPayment->amount, 2) }}'
                                                            })"
                                                            title="View / Print Tax Invoice Receipt" 
                                                            class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white dark:border-slate-700 transition-colors shadow-sm">
                                                        📄
                                                    </button>
                                                @endif
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Subscription: ' . ($sub->plan->name ?? '') . ' | Period: ' . $sub->start_date->format('d/m/Y') . ' - ' . $sub->end_date->format('d/m/Y') . ' | Total: ' . $currency . number_format($sub->final_amount, 2)) }}" 
                                                   target="_blank" 
                                                   title="Send via WhatsApp" 
                                                   class="p-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-emerald-600 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-emerald-400 dark:border-slate-700 transition-colors shadow-sm">
                                                    💬
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="py-8 text-center text-slate-500 dark:text-slate-400 text-xs">
                                            No subscriptions found for this member. Click "+ Add Subscription" to assign one.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 2: PT PACKAGES -->
                <div x-show="activeTab === 'pt_packages'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Personal Training</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Assigned personal training packages, sessions and dedicated coaching</p>
                        </div>
                        <button type="button" 
                                @click="showAddPtModal = true" 
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add PT Package</span>
                        </button>
                    </div>

                    @php
                        $ptPackages = $member->metadata['pt_packages'] ?? [];
                    @endphp

                    @if(empty($ptPackages) && !$assignedTrainer)
                        <div class="py-12 text-center text-xs text-slate-500 dark:text-slate-400 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                            No PT packages for this member yet.
                        </div>
                    @else
                        <div class="space-y-3">
                            @if($assignedTrainer && empty($ptPackages))
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold">
                                            🏋️
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs">{{ $assignedTrainer->full_name }}</h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $assignedTrainer->specialization ?? 'Personal Trainer' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200 dark:border-emerald-500/20">
                                        Assigned Coach
                                    </span>
                                </div>
                            @endif

                            @foreach($ptPackages as $pt)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-base">
                                            🏋️
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs">{{ $pt['package_name'] ?? 'Personal Training Package' }}</h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">
                                                Trainer: <strong class="text-slate-900 dark:text-white">{{ $pt['trainer_name'] ?? 'Assigned Trainer' }}</strong> • {{ $pt['sessions'] ?? 12 }} Sessions • Valid {{ $pt['validity_days'] ?? 30 }} days
                                            </p>
                                            <p class="text-[10px] font-mono text-slate-500 dark:text-slate-400 mt-0.5">
                                                {{ !empty($pt['start_date']) ? date('d M Y', strtotime($pt['start_date'])) : '' }} — {{ !empty($pt['end_date']) ? date('d M Y', strtotime($pt['end_date'])) : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <div class="text-right">
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-semibold">TOTAL / PAID</span>
                                            <span class="font-bold text-slate-900 dark:text-white text-xs">{{ $currency }}{{ number_format($pt['total'] ?? 0, 2) }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400 text-[11px] block font-semibold">{{ $currency }}{{ number_format($pt['paid'] ?? 0, 2) }}</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200 dark:border-emerald-500/20">
                                            {{ $pt['status'] ?? 'Active' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- TAB 3: SERVICES -->
                <div x-show="activeTab === 'services'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Gym Amenities & Services</h3>
                    </div>

                    @php
                        $memberServiceBookings = \App\Models\GymServiceBooking::with('service')
                            ->where('member_id', $member->id)
                            ->latest()
                            ->get();
                    @endphp

                    @if($memberServiceBookings->isEmpty())
                        <div class="py-10 text-center text-slate-500 dark:text-slate-400">
                            <svg class="w-10 h-10 mx-auto mb-3 text-slate-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                            <p class="text-xs font-semibold">No services booked yet</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-500 mt-1">Services booked for this member will appear here.</p>
                        </div>
                    @else
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                            @foreach($memberServiceBookings as $booking)
                                <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shadow-sm">
                                    <div>
                                        <span class="font-bold text-slate-900 dark:text-white block">{{ $booking->service?->name ?? 'Service' }}</span>
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">
                                            Booked: {{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : '—' }}
                                            @if($booking->service?->is_session_countable)
                                                · {{ $booking->sessions_left }}/{{ $booking->total_sessions }} sessions left
                                            @endif
                                        </span>
                                    </div>
                                    <span class="shrink-0 px-2 py-0.5 rounded text-[10px] font-bold {{ $booking->status === 'active' ? 'bg-emerald-50 dark:bg-emerald-500/15 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ ucfirst($booking->status) }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- TAB 4: INVOICES & PAYMENT TRAIL -->
                <div x-show="activeTab === 'invoices'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>💳</span>
                                <span>Payment & Invoice Trail</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Grouped by subscription package with full payment receipts and invoices</p>
                        </div>
                        <button type="button" 
                                @click="showAddPaymentModal = true" 
                                class="px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer self-start sm:self-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Collect Payment</span>
                        </button>
                    </div>

                    <!-- Subscription Payment Cards Trail -->
                    <div class="space-y-6">
                        @php
                            $allSubs = $member->memberships()->with(['plan', 'payments'])->latest()->get();
                        @endphp

                        @forelse($allSubs as $sub)
                            @php
                                $planPrice = (float) ($sub->plan?->price ?? $sub->final_amount);
                                $discount = (float) $sub->discount_amount;
                                $received = (float) $sub->paid_amount;
                                $due = max(0, (float)$sub->final_amount - $received);
                                $isFullyPaid = $due <= 0;
                            @endphp

                            <!-- Individual Subscription Card -->
                            <div class="rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
                                
                                <!-- Card Header Summary Banner -->
                                <div class="p-4 bg-slate-100/80 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-sm">
                                            🏋️
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-black text-slate-900 dark:text-white text-sm">
                                                    {{ $sub->plan->name ?? 'Membership Subscription' }}
                                                    @if($sub->plan)
                                                        <span class="text-xs font-normal text-slate-500 dark:text-slate-400">({{ $sub->plan->duration_value }} {{ $sub->plan->duration_type }})</span>
                                                    @endif
                                                </h4>
                                                <span class="px-2.5 py-0.5 rounded-full bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-300 font-mono text-[10px] font-bold shadow-sm">
                                                    {{ $sub->start_date->format('d/m/Y') }} - {{ $sub->end_date->format('d/m/Y') }}
                                                </span>
                                                @if($isFullyPaid)
                                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px]">
                                                        Fully Paid
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-black text-[10px]">
                                                        Due: {{ $currency }}{{ number_format($due, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Financial Statistics -->
                                    <div class="flex items-center gap-4 text-right flex-wrap md:flex-nowrap">
                                        <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                                            <span class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">PLAN PRICE</span>
                                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ $currency }}{{ number_format($planPrice, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                                            <span class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">DISCOUNT</span>
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $currency }}{{ number_format($discount, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/20 shadow-sm">
                                            <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">RECEIVED</span>
                                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-400">{{ $currency }}{{ number_format($received, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg {{ $due > 0 ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-500/30' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80' }} shadow-sm">
                                            <span class="text-[9px] font-extrabold {{ $due > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }} uppercase tracking-wider block">DUE</span>
                                            <span class="text-xs font-black {{ $due > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $currency }}{{ number_format($due, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Records Table for this Subscription -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs">
                                        <thead class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-100/50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800/80">
                                            <tr>
                                                <th class="py-2.5 px-4 font-semibold">Receipt ID</th>
                                                <th class="py-2.5 px-4 font-semibold">Date</th>
                                                <th class="py-2.5 px-4 font-semibold">Type</th>
                                                <th class="py-2.5 px-4 font-semibold">Method</th>
                                                <th class="py-2.5 px-4 font-semibold">Amount</th>
                                                <th class="py-2.5 px-4 font-semibold text-right">Invoice & Share</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                            @forelse($sub->payments as $p)
                                                @php
                                                    $receiptNum = $p->receipt_number ?? ('RCP' . str_pad($p->id, 8, '0', STR_PAD_LEFT));
                                                    $payDateFormatted = $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y');
                                                @endphp
                                                <tr class="hover:bg-white dark:hover:bg-slate-900/40 transition-colors">
                                                    <!-- Receipt ID (Clickable) -->
                                                    <td class="py-3 px-4">
                                                        <button type="button" 
                                                                @click="openInvoice({
                                                                    id: {{ $p->id }},
                                                                    receipt_no: '{{ $receiptNum }}',
                                                                    date: '{{ $payDateFormatted }}',
                                                                    amount: '{{ number_format($p->amount, 2) }}',
                                                                    method: '{{ strtoupper($p->payment_method) }}',
                                                                    ref: '{{ $p->transaction_reference ?? '' }}',
                                                                    plan_name: '{{ $sub->plan->name ?? 'Gym Membership' }}',
                                                                    plan_duration: '{{ $sub->plan ? ($sub->plan->duration_value . ' ' . $sub->plan->duration_type) : '' }}',
                                                                    start_date: '{{ $sub->start_date->format('d M Y') }}',
                                                                    end_date: '{{ $sub->end_date->format('d M Y') }}',
                                                                    total: '{{ number_format($sub->final_amount, 2) }}',
                                                                    paid: '{{ number_format($p->amount, 2) }}'
                                                                })"
                                                                class="inline-flex items-center gap-1.5 font-mono font-bold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 hover:underline cursor-pointer">
                                                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            <span>{{ $receiptNum }}</span>
                                                        </button>
                                                    </td>

                                                    <!-- Date -->
                                                    <td class="py-3 px-4 font-medium text-slate-800 dark:text-slate-200">
                                                        {{ $payDateFormatted }}
                                                    </td>

                                                    <!-- Type -->
                                                    <td class="py-3 px-4">
                                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">
                                                            Payment
                                                        </span>
                                                    </td>

                                                    <!-- Method -->
                                                    <td class="py-3 px-4">
                                                        <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold text-[10px] uppercase">
                                                            {{ $p->payment_method }}
                                                        </span>
                                                    </td>

                                                    <!-- Amount -->
                                                    <td class="py-3 px-4 font-black text-emerald-600 dark:text-emerald-400 text-sm">
                                                        {{ $currency }}{{ number_format($p->amount, 2) }}
                                                    </td>

                                                    <!-- Actions: Receipt View & WhatsApp -->
                                                    <td class="py-3 px-4 text-right">
                                                        <div class="flex items-center justify-end gap-2">
                                                            <button type="button" 
                                                                    @click="openInvoice({
                                                                        id: {{ $p->id }},
                                                                        receipt_no: '{{ $receiptNum }}',
                                                                        date: '{{ $payDateFormatted }}',
                                                                        amount: '{{ number_format($p->amount, 2) }}',
                                                                        method: '{{ strtoupper($p->payment_method) }}',
                                                                        ref: '{{ $p->transaction_reference ?? '' }}',
                                                                        plan_name: '{{ $sub->plan->name ?? 'Gym Membership' }}',
                                                                        plan_duration: '{{ $sub->plan ? ($sub->plan->duration_value . ' ' . $sub->plan->duration_type) : '' }}',
                                                                        start_date: '{{ $sub->start_date->format('d M Y') }}',
                                                                        end_date: '{{ $sub->end_date->format('d M Y') }}',
                                                                        total: '{{ number_format($sub->final_amount, 2) }}',
                                                                        paid: '{{ number_format($p->amount, 2) }}'
                                                                    })"
                                                                    title="View Tax Invoice" 
                                                                    class="p-1.5 rounded-lg bg-white hover:bg-slate-100 text-slate-700 hover:text-slate-900 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:hover:text-white dark:border-slate-700 transition-colors cursor-pointer shadow-sm">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            </button>

                                                            <a href="{{ route('app.invoices.show', $p->id) }}" 
                                                               target="_blank" 
                                                               title="Open Printable Invoice Page" 
                                                               class="p-1.5 rounded-lg bg-white hover:bg-slate-100 text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 border border-slate-200 dark:border-slate-700 transition-colors shadow-sm">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            </a>

                                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Fee Receipt from ' . (auth()->user()->tenant->name ?? 'Gym') . ': Receipt No: ' . $receiptNum . ' | Amount: ' . $currency . number_format($p->amount, 2) . ' | Method: ' . strtoupper($p->payment_method) . ' | Date: ' . $payDateFormatted . ' | Thank you for training with us!') }}" 
                                                               target="_blank" 
                                                               title="Share Receipt on WhatsApp" 
                                                               class="p-1.5 rounded-lg bg-white hover:bg-slate-100 text-emerald-600 dark:text-emerald-400 border border-slate-200 dark:border-slate-700 transition-colors shadow-sm">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-4 px-4 text-center text-slate-500 dark:text-slate-400 text-xs">
                                                        No payment records logged for this subscription package.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs">
                                No membership subscription or payment records found for this member yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 5: WORKOUT PLANS -->
                <div x-show="activeTab === 'workouts'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Assigned Workout Routines</h3>
                    <div class="space-y-3">
                        @forelse($member->workoutPlans as $wp)
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs">{{ $wp->title }}</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ $wp->description ?? 'Custom workout split' }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 dark:text-slate-400">No workout plan assigned yet. You can create one under Workouts.</p>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 6: DIET PLANS -->
                <div x-show="activeTab === 'diets'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Assigned Diet & Nutrition Charts</h3>
                    <div class="space-y-3">
                        @forelse($member->dietPlans as $dp)
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 shadow-sm">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs">{{ $dp->title }} ({{ $dp->daily_calories ?? 2000 }} kcal)</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">{{ $dp->description ?? 'Nutritional guidelines' }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-slate-500 dark:text-slate-400">No diet plan assigned yet. You can create one under Diets.</p>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 7: CLASSES -->
                <div x-show="activeTab === 'classes'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Enrolled Fitness & Studio Classes</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-base">
                                    🧘
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">Morning Yoga & Mobility</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Mon, Wed, Fri • 07:00 AM</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] border border-emerald-200 dark:border-emerald-500/20">
                                Enrolled
                            </span>
                        </div>

                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-600/20 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-base">
                                    🔥
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white">HIIT & Strength Bootcamp</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">Tue, Thu • 06:30 PM</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-[10px] border border-emerald-200 dark:border-emerald-500/20">
                                Enrolled
                            </span>
                        </div>
                    </div>
                </div>

                <!-- TAB 8: ATTENDANCE HISTORY -->
                <div x-show="activeTab === 'attendance'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Recent Attendance Logs (Last 30 Check-ins)</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800">
                                <tr>
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Check-in Time</th>
                                    <th class="py-2.5 px-3">Check-out Time</th>
                                    <th class="py-2.5 px-3">Method</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                @forelse($member->attendance as $att)
                                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30">
                                        <td class="py-3 px-3 font-semibold text-slate-900 dark:text-white">{{ $att->date ? $att->date->format('d M Y') : ($att->check_in ? $att->check_in->format('d M Y') : '—') }}</td>
                                        <td class="py-3 px-3 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $att->check_in ? $att->check_in->format('g:i A') : '—' }}</td>
                                        <td class="py-3 px-3 font-mono text-slate-500 dark:text-slate-400">{{ $att->check_out ? $att->check_out->format('g:i A') : '—' }}</td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold uppercase">{{ $att->method ?? 'QR Code' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-slate-500 dark:text-slate-400 text-xs">No attendance check-in records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 9: MEASUREMENTS -->
                <div x-show="activeTab === 'measurements'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>📏</span>
                                <span>Member Body Measurements</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track body composition and physical transformation over time</p>
                        </div>
                        <button type="button" 
                                @click="showAddMeasurementModal = true" 
                                class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer self-start sm:self-auto">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Log New Measurement</span>
                        </button>
                    </div>

                    @if(empty($latestMeasurement))
                        <!-- Clean Empty State when no measurements logged -->
                        <div class="text-center py-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="w-14 h-14 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center mx-auto text-2xl shadow-inner">
                                📏
                            </div>
                            <h4 class="text-sm font-bold text-slate-900 dark:text-white">No Measurements Logged Yet</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                You haven't recorded any baseline body metrics or physical measurements for {{ $member->first_name }} yet.
                            </p>
                            <button type="button" 
                                    @click="showAddMeasurementModal = true" 
                                    class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs inline-flex items-center gap-2 cursor-pointer shadow-lg shadow-indigo-600/30">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Log First Measurement</span>
                            </button>
                        </div>
                    @else
                        <!-- Latest Measurements Grid -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <h4 class="text-xs font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">Latest Recorded Metrics</h4>
                                @if(!empty($latestMeasurement['date']))
                                    <span class="text-[11px] font-medium text-slate-500 dark:text-slate-400">As of {{ date('d M Y', strtotime($latestMeasurement['date'])) }}</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                <!-- Metric 1: Weight -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">WEIGHT</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['weight'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">kg</span></p>
                                </div>

                                <!-- Metric 2: Body Fat -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">BODY FAT</span>
                                    <p class="text-lg font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $latestMeasurement['body_fat'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">%</span></p>
                                </div>

                                <!-- Metric 3: Chest -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">CHEST</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['chest'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 4: Shoulders -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">SHOULDERS</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['shoulders'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 5: Waist -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">WAIST</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['waist'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 6: Hips -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">HIPS</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['hips'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 7: Left Bicep -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">LEFT BICEP</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['bicep_left'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 8: Right Bicep -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">RIGHT BICEP</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['bicep_right'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 9: Left Thigh -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">LEFT THIGH</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['thigh_left'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 10: Right Thigh -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">RIGHT THIGH</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['thigh_right'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 11: Calves -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">CALVES</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['calves'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 12: Neck -->
                                <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">NECK</span>
                                    <p class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['neck'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Measurement History Table -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-xs font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">Measurement History</h4>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead class="text-[10px] text-slate-500 dark:text-slate-400 uppercase tracking-wider bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800">
                                    <tr>
                                        <th class="py-2.5 px-3 font-semibold">Date</th>
                                        <th class="py-2.5 px-3 font-semibold">Weight</th>
                                        <th class="py-2.5 px-3 font-semibold">Chest</th>
                                        <th class="py-2.5 px-3 font-semibold">Waist</th>
                                        <th class="py-2.5 px-3 font-semibold">Arms (L/R)</th>
                                        <th class="py-2.5 px-3 font-semibold">Body Fat</th>
                                        <th class="py-2.5 px-3 font-semibold">Logged By</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800/60 text-slate-700 dark:text-slate-300">
                                    @forelse($measurementsList as $m)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-950/50 transition-colors">
                                            <td class="py-3 px-3 font-bold text-slate-900 dark:text-white">{{ date('d M Y', strtotime($m['date'])) }}</td>
                                            <td class="py-3 px-3 font-semibold">{{ $m['weight'] ? $m['weight'] . ' kg' : '—' }}</td>
                                            <td class="py-3 px-3">{{ $m['chest'] ? $m['chest'] . '"' : '—' }}</td>
                                            <td class="py-3 px-3">{{ $m['waist'] ? $m['waist'] . '"' : '—' }}</td>
                                            <td class="py-3 px-3">{{ ($m['bicep_left'] ?? '—') . '" / ' . ($m['bicep_right'] ?? '—') . '"' }}</td>
                                            <td class="py-3 px-3 text-indigo-600 dark:text-indigo-400 font-bold">{{ $m['body_fat'] ? $m['body_fat'] . '%' : '—' }}</td>
                                            <td class="py-3 px-3 text-slate-500 dark:text-slate-400 text-[11px]">{{ $m['logged_by'] ?? 'Staff' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="py-6 text-center text-slate-500 dark:text-slate-400 text-xs">
                                                No measurement logs recorded yet. Click "+ Log New Measurement" to start tracking.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- TAB 10: AUDIT TRAIL -->
                <div x-show="activeTab === 'audit_trail'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-6 space-y-6">
                    <div class="border-b border-slate-200 dark:border-slate-800 pb-4">
                        <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                            <span>🕒</span>
                            <span>Transaction Audit Trail</span>
                        </h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Complete log of payments, renewals, freezes and status updates for this member</p>
                    </div>

                    <!-- Audit Trail Timeline / List -->
                    <div class="space-y-3">
                        @forelse($auditLogs as $log)
                            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-start justify-between gap-4 shadow-sm">
                                <div class="flex items-start gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 flex items-center justify-center text-xs shrink-0 mt-0.5 shadow-sm">
                                        @if(str_contains($log->action, 'payment') || str_contains($log->action, 'fee'))
                                            💳
                                        @elseif(str_contains($log->action, 'member_created') || str_contains($log->action, 'enrolled'))
                                            👤
                                        @elseif(str_contains($log->action, 'subscription') || str_contains($log->action, 'membership'))
                                            🏋️
                                        @elseif(str_contains($log->action, 'freeze'))
                                            ❄️
                                        @else
                                            📝
                                        @endif
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">• By {{ $log->user->name ?? 'System' }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1">{{ $log->description }}</p>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">{{ $log->created_at->format('d M Y') }}</span>
                                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 block">{{ $log->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs">
                                No audit log records recorded yet for this member.
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 1: ADD / RECORD PAYMENT -->
        <!-- ========================================================================= -->
        <div x-show="showAddPaymentModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddPaymentModal = false" class="w-full max-w-md rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-6 space-y-4 text-slate-900 dark:text-slate-200">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Record Member Payment</h3>
                    <button type="button" @click="showAddPaymentModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.collect-fee', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Amount ({{ $currency }}) *</label>
                        <input type="number" 
                               name="amount" 
                               x-model="paymentAmount" 
                               step="0.01" 
                               min="0.01" 
                               required 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-black text-sm focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                        <select name="payment_method" x-model="paymentMethod" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-emerald-500 focus:outline-none">
                            <option value="cash">Cash</option>
                            <option value="upi">UPI / QR Code</option>
                            <option value="card">Card (POS)</option>
                            <option value="netbanking">Net Banking</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Transaction Ref / Cheque No</label>
                        <input type="text" 
                               name="transaction_reference" 
                               placeholder="e.g. UPI Ref / Txn ID" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-emerald-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Fee receipt note..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="showAddPaymentModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black shadow-lg shadow-emerald-500/20 cursor-pointer">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: ADD SUBSCRIPTION -->
        <!-- ========================================================================= -->
        <div x-show="showAddSubscriptionModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddSubscriptionModal = false" class="w-full max-w-lg rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-6 space-y-4 text-slate-900 dark:text-slate-200">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Add Membership Subscription</h3>
                    <button type="button" @click="showAddSubscriptionModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.add-subscription', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Membership Plan *</label>
                        <select name="membership_plan_id" 
                                x-model="newSubPlanId" 
                                @change="onNewSubPlanChange()" 
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                            <option value="">Select Plan</option>
                            @foreach($membershipPlans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $currency }}{{ number_format($p->price, 0) }} / {{ $p->duration_value }} {{ $p->duration_type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Discount ({{ $currency }})</label>
                            <input type="number" name="discount" x-model="newSubDiscount" min="0" placeholder="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Paid Now ({{ $currency }})</label>
                            <input type="number" name="initial_payment_amount" x-model="newSubPaidNow" min="0" placeholder="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Payment Method</label>
                            <select name="payment_method" x-model="newSubPaymentMethod" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="card">Card (POS)</option>
                                <option value="netbanking">Net Banking</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="showAddSubscriptionModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black shadow-lg shadow-indigo-600/30 cursor-pointer">Assign Subscription</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 3/7: EDIT MEMBER DETAILS (Full Profile & Photos)                    -->
        <!-- ========================================================================= -->
        <div x-show="showEditModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-md flex items-center justify-center p-4" 
             x-cloak>
            <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl max-w-4xl w-full p-6 sm:p-8 shadow-2xl space-y-6 my-8 max-h-[92vh] overflow-y-auto text-slate-900 dark:text-slate-200">
                
                <!-- Modal Top Bar -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800/80 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold border border-indigo-200 dark:border-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-black text-slate-900 dark:text-white">Edit Member Details</h3>
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold border border-slate-200 dark:border-slate-700">
                                    {{ $member->member_code }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Update personal details, profile picture, body metrics, and membership status</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
                </div>

                <form action="{{ route('app.members.update', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
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
                                <input type="text" name="first_name" value="{{ $member->first_name }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" value="{{ $member->last_name }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-xs font-bold text-slate-400 dark:text-slate-500">+91</span>
                                    <input type="tel" name="phone" value="{{ $member->phone }}" required class="w-full pl-11 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" value="{{ $member->metadata['alternate_phone'] ?? '' }}" placeholder="Optional secondary contact" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Email Address</label>
                                <input type="email" name="email" value="{{ $member->email }}" placeholder="member@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Gender</label>
                                <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ strtolower($member->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ strtolower($member->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ strtolower($member->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Date of Birth</label>
                                <input type="date" name="dob" value="{{ $member->dob ? $member->dob->format('Y-m-d') : '' }}" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Blood Group</label>
                                <select name="blood_group" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}" {{ ($member->metadata['blood_group'] ?? '') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">City</label>
                                <input type="text" name="city" value="{{ $member->metadata['city'] ?? '' }}" placeholder="e.g. Ahmedabad" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Full Address</label>
                                <input type="text" name="address" value="{{ $member->address }}" placeholder="Street address, apartment, locality" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
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
                                                @click="editHeightUnit = 'cm'" 
                                                :class="editHeightUnit === 'cm' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-[10px] font-bold rounded transition-colors">cm</button>
                                        <button type="button" 
                                                @click="editHeightUnit = 'ft'" 
                                                :class="editHeightUnit === 'ft' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-[10px] font-bold rounded transition-colors">ft</button>
                                    </div>
                                    <input type="hidden" name="height_unit" :value="editHeightUnit">
                                </div>
                                <input type="text" 
                                       name="height" 
                                       value="{{ $member->metadata['height'] ?? '' }}" 
                                       :placeholder="editHeightUnit === 'cm' ? 'e.g. 175' : 'e.g. 5.9'" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Current Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" value="{{ $member->metadata['weight'] ?? ($latestMeasurement['weight'] ?? '') }}" placeholder="e.g. 72.5" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Target Weight (kg)</label>
                                <input type="number" step="0.1" name="target_weight" value="{{ $member->metadata['target_weight'] ?? '' }}" placeholder="e.g. 68.0" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Primary Fitness Goal</label>
                                <select name="fitness_goal" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">Select Primary Goal</option>
                                    @php
                                        $goals = [
                                            'Weight Loss' => '🔥 Weight Loss & Fat Burn',
                                            'Muscle Building' => '💪 Muscle Building & Hypertrophy',
                                            'General Fitness' => '⚡ General Fitness & Stamina',
                                            'Endurance Training' => '🏃 Endurance & Cardiovascular',
                                            'Rehabilitation' => '🩹 Injury Rehab / Physical Therapy',
                                            'Athletic Performance' => '🏆 Athletic Performance & Sports',
                                        ];
                                    @endphp
                                    @foreach($goals as $gVal => $gLabel)
                                        <option value="{{ $gVal }}" {{ ($member->metadata['fitness_goal'] ?? '') === $gVal ? 'selected' : '' }}>{{ $gLabel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Medical History / Health Notes</label>
                                <input type="text" name="medical_history" value="{{ $member->metadata['medical_history'] ?? '' }}" placeholder="e.g. Asthma, Knee surgery, High BP" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
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
                                <input type="text" name="emergency_contact_name" value="{{ $member->emergency_contact_name }}" placeholder="e.g. Robert Doe" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Emergency Phone Number</label>
                                <input type="tel" name="emergency_contact_phone" value="{{ $member->emergency_contact_phone }}" placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Relationship</label>
                                <input type="text" name="emergency_relation" value="{{ $member->metadata['emergency_relation'] ?? '' }}" placeholder="e.g. Spouse / Parent / Sibling" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">
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
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none font-bold shadow-sm">
                                    <option value="ACTIVE" class="text-emerald-600 dark:text-emerald-400" {{ $member->status === 'ACTIVE' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="INACTIVE" class="text-slate-500 dark:text-slate-400" {{ $member->status === 'INACTIVE' ? 'selected' : '' }}>⚪ Inactive</option>
                                    <option value="SUSPENDED" class="text-rose-600 dark:text-rose-400" {{ $member->status === 'SUSPENDED' ? 'selected' : '' }}>🔴 Suspended / Frozen</option>
                                    <option value="EXPIRED" class="text-orange-600 dark:text-orange-400" {{ $member->status === 'EXPIRED' ? 'selected' : '' }}>🟠 Expired</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Branch</label>
                                <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $member->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Package Plan</label>
                                <select name="membership_plan_id" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                                    <option value="">-- Keep Current Plan --</option>
                                    @foreach($membershipPlans as $mp)
                                        <option value="{{ $mp->id }}" {{ $activeMembership?->membership_plan_id == $mp->id ? 'selected' : '' }}>{{ $mp->name }} ({{ $currency }}{{ number_format($mp->price, 2) }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1.5">Internal Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes regarding medical history or special arrangements" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none shadow-sm">{{ $member->notes }}</textarea>
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

        <!-- EMBEDDED CAMERA SNAPSHOT MODAL FOR EDIT (IN MEMBER PROFILE) -->
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
                    <button type="button" @click="takeEditSnapshot()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Capture Photo</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 4: LOG BODY MEASUREMENTS -->
        <!-- ========================================================================= -->
        <div x-show="showAddMeasurementModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddMeasurementModal = false" class="w-full max-w-xl rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-6 space-y-4 max-h-[90vh] overflow-y-auto text-slate-900 dark:text-slate-200">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📏</span>
                        <span>Log Body Measurements</span>
                    </h3>
                    <button type="button" @click="showAddMeasurementModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.store-measurement', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Measurement Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" placeholder="e.g. 72.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Body Fat (%)</label>
                            <input type="number" step="0.1" name="body_fat" placeholder="e.g. 15.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Chest (in)</label>
                            <input type="number" step="0.1" name="chest" placeholder="e.g. 38" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Shoulders (in)</label>
                            <input type="number" step="0.1" name="shoulders" placeholder="e.g. 44" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Waist (in)</label>
                            <input type="number" step="0.1" name="waist" placeholder="e.g. 32" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Hips (in)</label>
                            <input type="number" step="0.1" name="hips" placeholder="e.g. 36" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Left Bicep (in)</label>
                            <input type="number" step="0.1" name="bicep_left" placeholder="e.g. 14" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Right Bicep (in)</label>
                            <input type="number" step="0.1" name="bicep_right" placeholder="e.g. 14.2" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Left Thigh (in)</label>
                            <input type="number" step="0.1" name="thigh_left" placeholder="e.g. 22" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Right Thigh (in)</label>
                            <input type="number" step="0.1" name="thigh_right" placeholder="e.g. 22" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Calves (in)</label>
                            <input type="number" step="0.1" name="calves" placeholder="e.g. 15" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Neck (in)</label>
                            <input type="number" step="0.1" name="neck" placeholder="e.g. 15.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Trainer Remarks</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Body fat dropped by 1.2%, improved quad definition..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                    </div>

                    <div class="flex items-center justify-between pt-2">
                        <button type="button" @click="showAddMeasurementModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black shadow-lg shadow-indigo-600/30 cursor-pointer">Save Measurements</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 5: TAX INVOICE & FEE RECEIPT PREVIEW -->
        <!-- ========================================================================= -->
        <div x-show="showInvoiceModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 dark:bg-black/85 backdrop-blur-md p-4 sm:p-6" 
             style="display: none;">
            
            <div class="min-h-full flex items-center justify-center py-6">
                <div @click.away="showInvoiceModal = false" class="w-full max-w-2xl space-y-3">
                    
                    <!-- Modal Window Title Header -->
                    <div class="flex items-center justify-between px-5 py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl">
                        <div class="flex items-center gap-2 text-slate-900 dark:text-white font-black text-xs">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Invoice Preview</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <a :href="'/app/invoices/' + selectedInvoice.id" 
                               target="_blank" 
                               title="Open in new tab"
                               class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button type="button" 
                                    @click="showInvoiceModal = false" 
                                    title="Close"
                                    class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Action Bar Toolbar (Close, Print, Download PDF) -->
                    <div class="flex items-center justify-center gap-3">
                        <button type="button" 
                                @click="showInvoiceModal = false" 
                                class="px-5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs shadow-sm transition-all cursor-pointer">
                            Close
                        </button>

                        <button type="button" 
                                onclick="window.print()" 
                                class="px-6 py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print</span>
                        </button>

                        <a :href="'/app/invoices/' + selectedInvoice.id" 
                           target="_blank" 
                           class="px-6 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download PDF</span>
                        </a>
                    </div>

                    <!-- Printable Invoice Sheet (A4 Proportion) -->
                    <div class="relative w-full min-h-[820px] bg-white text-slate-900 rounded-2xl shadow-2xl p-8 sm:p-12 overflow-hidden border border-slate-200 text-xs flex flex-col justify-between">
                        
                        <!-- Faded Background Logo Watermark -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.06] select-none">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="" class="w-72 h-72 object-contain grayscale">
                            @else
                                <div class="text-center font-black tracking-widest text-7xl uppercase text-slate-900 rotate-[-15deg]">
                                    {{ auth()->user()->tenant->name ?? 'GYM CONSOLE' }}
                                </div>
                            @endif
                        </div>

                        <div class="relative z-10 space-y-8">
                            
                            <!-- Header Section: Logo, Gym Info & INVOICE Meta -->
                            <div class="flex items-start justify-between border-b border-slate-200 pb-6">
                                <div class="flex items-center gap-3.5">
                                    @if(!empty($logoUrl))
                                        <img src="{{ $logoUrl }}" alt="{{ auth()->user()->tenant->name ?? 'Gym Logo' }}" class="h-16 w-auto max-w-[140px] object-contain shrink-0">
                                    @else
                                        <div class="w-14 h-14 rounded-2xl bg-slate-950 text-amber-400 flex items-center justify-center font-black text-2xl shadow-md shrink-0">
                                            🏋️
                                        </div>
                                    @endif
                                    <div>
                                        <h2 class="text-xl font-black text-slate-950 tracking-tight">{{ auth()->user()->tenant->name ?? 'PowerFit Gym' }}</h2>
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $member->branch->address ?? (auth()->user()->tenant->address ?? '123 Fitness Street, Health City') }}</p>
                                        @if(!empty(auth()->user()->tenant->phone) || !empty(auth()->user()->tenant->email))
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ auth()->user()->tenant->phone ?? '' }} @if(!empty(auth()->user()->tenant->phone) && !empty(auth()->user()->tenant->email)) • @endif {{ auth()->user()->tenant->email ?? '' }}</p>
                                        @endif
                                    </div>
                                </div>

                                <div class="text-right">
                                    <h1 class="text-2xl font-black text-slate-900 tracking-tight">INVOICE</h1>
                                    <p class="text-xs font-mono font-bold text-slate-600 mt-1" x-text="selectedInvoice.receipt_no"></p>
                                    <p class="text-xs text-slate-400 mt-0.5" x-text="selectedInvoice.date"></p>
                                </div>
                            </div>

                            <!-- Bill To & Plan Details (2 Columns) -->
                            <div class="grid grid-cols-2 gap-6 border-b border-slate-200 pb-6">
                                <!-- Bill To Column -->
                                <div>
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">BILL TO</span>
                                    <h3 class="text-sm font-black text-slate-950">{{ $member->full_name }}</h3>
                                    <p class="text-xs font-mono font-semibold text-slate-500 mt-0.5">{{ $member->member_code }}</p>
                                    <p class="text-xs text-slate-600 mt-0.5">{{ $member->phone }}</p>
                                    @if($member->email)
                                        <p class="text-xs text-slate-500 mt-0.5">{{ $member->email }}</p>
                                    @endif
                                </div>

                                <!-- Plan Details Column -->
                                <div>
                                    <span class="text-[10px] font-extrabold text-slate-400 uppercase tracking-wider block mb-1">PLAN DETAILS</span>
                                    <h3 class="text-sm font-black text-slate-950" x-text="selectedInvoice.plan_name"></h3>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        <span x-text="selectedInvoice.start_date"></span>
                                    </p>
                                    <p class="text-xs text-slate-600 mt-0.5">
                                        <span x-text="selectedInvoice.end_date"></span>
                                    </p>
                                </div>
                            </div>

                            <!-- Items & Amount Table -->
                            <div>
                                <table class="w-full text-left text-xs">
                                    <thead>
                                        <tr class="border-b-2 border-slate-200 text-slate-500 font-extrabold text-[10px] uppercase tracking-wider">
                                            <th class="py-3">DESCRIPTION</th>
                                            <th class="py-3 text-right">AMOUNT</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100">
                                        <tr>
                                            <td class="py-4">
                                                <span class="font-bold text-slate-900 block text-sm" x-text="selectedInvoice.plan_name"></span>
                                                <span class="text-slate-500 text-[11px] block mt-0.5" x-show="selectedInvoice.plan_duration" x-text="selectedInvoice.plan_duration"></span>
                                                <span class="text-slate-400 text-[10px] block mt-0.5">
                                                    Payment Method: <span class="font-semibold text-slate-600 uppercase" x-text="selectedInvoice.method"></span>
                                                    <span x-show="selectedInvoice.ref" x-text="' — Ref: ' + selectedInvoice.ref"></span>
                                                </span>
                                            </td>
                                            <td class="py-4 text-right font-black text-slate-900 text-sm">
                                                {{ $currency }}<span x-text="selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="border-t-2 border-slate-200 text-xs">
                                        <tr>
                                            <td class="py-3 font-bold text-slate-600">Total</td>
                                            <td class="py-3 text-right font-black text-slate-900 text-sm">
                                                {{ $currency }}<span x-text="selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="py-2 font-bold text-emerald-700">Paid</td>
                                            <td class="py-2 text-right font-black text-emerald-600 text-base">
                                                {{ $currency }}<span x-text="selectedInvoice.amount"></span>
                                            </td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                        </div>

                        <!-- Bottom Note / Thank You Footer -->
                        <div class="relative z-10 pt-8 border-t border-slate-100 text-center mt-auto">
                            <p class="text-xs font-semibold text-slate-500">Thank you for your membership!</p>
                            <p class="text-[10px] text-slate-400 mt-1">Generated by {{ auth()->user()->tenant->name ?? 'Gym Console' }} — All rights reserved.</p>
                        </div>

                    </div>

                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 6: ADD PERSONAL TRAINING (PT) PACKAGE -->
        <!-- ========================================================================= -->
        <div x-show="showAddPtModal" 
             x-transition:enter="ease-out duration-200"
             x-transition:leave="ease-in duration-150"
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 dark:bg-black/85 backdrop-blur-md p-3 sm:p-6" 
             style="display: none;">
            
            <div class="min-h-full flex items-center justify-center py-4 sm:py-6">
                <div @click.away="showAddPtModal = false" class="w-full max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl p-5 sm:p-8 space-y-6 text-xs text-slate-900 dark:text-slate-200 relative">
                    
                    <!-- Top-Right Close Button -->
                    <button type="button" 
                            @click="showAddPtModal = false" 
                            class="absolute top-4 sm:top-6 right-4 sm:right-6 p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors z-20 cursor-pointer" 
                            title="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <form action="{{ route('app.members.add-pt-package', $member->id) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Header Section: Gym Info & INVOICE -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-slate-200 dark:border-slate-800/80 pb-5 pr-10">
                            <div>
                                <h2 class="text-base font-black text-slate-900 dark:text-white tracking-wide uppercase">{{ auth()->user()->tenant->name ?? 'POWERFIT GYM' }}</h2>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $member->branch->address ?? (auth()->user()->tenant->address ?? '123 Fitness Street, Health City') }}</p>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Ph: {{ auth()->user()->tenant->phone ?? '080 6940 9814' }}</p>
                                <p class="text-[11px] font-mono text-slate-400 dark:text-slate-500">{{ auth()->user()->tenant->metadata['gstin'] ?? 'GSTIN: 27AAACM3025E1ZZ' }}</p>
                            </div>

                            <div class="text-left sm:text-right">
                                <h1 class="text-2xl font-black text-indigo-600 dark:text-indigo-400 tracking-wider uppercase">INVOICE</h1>
                                <div class="flex items-center gap-2 mt-2 sm:justify-end">
                                    <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">Date</span>
                                    <input type="date" 
                                           name="start_date" 
                                           x-model="ptStartDate" 
                                           class="px-3 py-1 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                </div>
                            </div>
                        </div>

                        <!-- BILL TO Card -->
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1.5">BILL TO</span>
                            <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-wrap items-center justify-between gap-2 shadow-sm">
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-sm">{{ $member->full_name }} ({{ $member->member_code }})</h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-black text-[10px]">Active</span>
                                        <span class="text-slate-500 dark:text-slate-400 font-mono text-xs">{{ $member->phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Description Table -->
                        <div class="space-y-2">
                            <!-- Table Header -->
                            <div class="hidden sm:flex items-center gap-3 text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider border-b border-slate-200 dark:border-slate-800 pb-2 px-1">
                                <div class="w-6 shrink-0">#</div>
                                <div class="w-44 shrink-0">ITEM TYPE</div>
                                <div class="flex-1 min-w-0">DESCRIPTION</div>
                                <div class="w-32 shrink-0 text-right">AMOUNT ({{ $currency }})</div>
                            </div>

                            <!-- Line Item Row 1 -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-3 p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 shadow-sm">
                                <div class="hidden sm:block w-6 shrink-0 font-bold text-slate-500 dark:text-slate-400 pt-2.5">1</div>
                                
                                <div class="w-full sm:w-44 shrink-0">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Item Type</label>
                                    <select class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 font-bold text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                        <option value="pt" selected>Personal Training</option>
                                    </select>
                                </div>

                                <div class="flex-1 min-w-0 space-y-2.5">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Description</label>
                                    <div>
                                        <select name="pt_package_name" 
                                                x-model="ptPackageName" 
                                                required 
                                                @change="
                                                    if (ptPackageName === '1 Month PT (12 Sessions)') { ptSessions = 12; ptValidityDays = 30; ptAmount = 5000; }
                                                    else if (ptPackageName === '3 Month PT (36 Sessions)') { ptSessions = 36; ptValidityDays = 90; ptAmount = 12000; }
                                                    else if (ptPackageName === '6 Month PT (72 Sessions)') { ptSessions = 72; ptValidityDays = 180; ptAmount = 22000; }
                                                    ptCollectedAmount = ptTotal;
                                                "
                                                class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-medium text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                            <option value="">— Select Personal Training —</option>
                                            <option value="1 Month PT (12 Sessions)">1 Month PT (12 Sessions)</option>
                                            <option value="3 Month PT (36 Sessions)">3 Month PT (36 Sessions)</option>
                                            <option value="6 Month PT (72 Sessions)">6 Month PT (72 Sessions)</option>
                                            <option value="Custom PT Coaching">Custom PT Coaching</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500 dark:text-slate-400 font-semibold text-xs shrink-0 w-16">Trainer</span>
                                        <select name="trainer_id" 
                                                x-model="ptTrainerId" 
                                                class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                            <option value="">Select Trainer</option>
                                            @foreach($trainers as $t)
                                                <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-500 dark:text-slate-400 font-semibold text-xs shrink-0 w-16">Sessions</span>
                                            <input type="number" 
                                                   name="sessions" 
                                                   x-model="ptSessions" 
                                                   min="1" 
                                                   class="w-full px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-500 dark:text-slate-400 font-semibold text-xs shrink-0">Validity</span>
                                            <input type="number" 
                                                   name="validity_days" 
                                                   x-model="ptValidityDays" 
                                                   min="1" 
                                                   class="w-20 px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                            <span class="text-slate-500 dark:text-slate-400 text-xs">days</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 pt-0.5 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-slate-500 dark:text-slate-400 font-semibold text-xs shrink-0 w-16">Start</span>
                                            <input type="date" 
                                                   name="start_date_display" 
                                                   x-model="ptStartDate" 
                                                   class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                        </div>
                                        <span class="text-slate-500 dark:text-slate-400 font-mono text-xs">→ Ends: <strong class="text-slate-900 dark:text-white" x-text="ptEndDate"></strong> (<span x-text="ptValidityDays"></span> days)</span>
                                    </div>
                                </div>

                                <div class="w-full sm:w-32 shrink-0 text-left sm:text-right">
                                    <label class="block sm:hidden text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-1">Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           step="0.01" 
                                           name="amount" 
                                           x-model="ptAmount" 
                                           required 
                                           @input="ptCollectedAmount = ptTotal" 
                                           placeholder="0.00" 
                                           class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono font-bold text-xs text-left sm:text-right focus:border-indigo-500 focus:outline-none shadow-sm">
                                </div>
                            </div>

                            <!-- Add Line Item Button -->
                            <div class="pt-1">
                                <button type="button" 
                                        class="px-3.5 py-1.5 rounded-lg border border-dashed border-indigo-400 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:border-indigo-500 text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Line Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Financial Breakdown Summary -->
                        <div class="flex justify-end pt-1">
                            <div class="w-full sm:max-w-xs space-y-2 border-t border-slate-200 dark:border-slate-800 pt-3 text-xs">
                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Subtotal</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $currency }}<span x-text="Number(ptAmount || 0).toFixed(2)"></span></span>
                                </div>

                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Discount</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="discount" 
                                               x-model="ptDiscount" 
                                               @input="ptCollectedAmount = ptTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-right text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-slate-600 dark:text-slate-400">
                                    <span>Tax</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="tax" 
                                               x-model="ptTax" 
                                               @input="ptCollectedAmount = ptTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-right text-xs focus:border-indigo-500 focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between font-black text-sm text-slate-900 dark:text-white pt-2 border-t border-slate-200 dark:border-slate-800">
                                    <span>TOTAL</span>
                                    <span class="text-base text-indigo-600 dark:text-indigo-400 font-bold">{{ $currency }}<span x-text="ptTotal.toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- AMOUNT TO COLLECT -->
                        <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3 shadow-sm">
                            <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">AMOUNT TO COLLECT</span>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 font-black text-base shadow-sm">
                                    <span>{{ $currency }}</span>
                                    <input type="number" 
                                           step="0.01" 
                                           name="collected_amount" 
                                           x-model="ptCollectedAmount" 
                                           class="w-28 bg-transparent text-emerald-600 dark:text-emerald-400 font-mono font-black focus:outline-none">
                                </div>
                                <span class="text-xs text-slate-500 dark:text-slate-400 font-semibold">of {{ $currency }}<span x-text="ptTotal.toFixed(2)"></span></span>
                            </div>

                            <label class="flex items-center gap-2 text-xs text-slate-700 dark:text-slate-300 font-semibold cursor-pointer pt-1">
                                <input type="checkbox" checked class="w-4 h-4 rounded border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-900 text-indigo-600 focus:ring-0">
                                <span><strong>Activate package now:</strong> Member can start personal training immediately despite pending dues</span>
                            </label>
                        </div>

                        <!-- PAYMENT METHOD -->
                        <div class="space-y-2">
                            <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">PAYMENT METHOD</span>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-4">
                                    <select name="payment_method" 
                                            x-model="ptPaymentMethod" 
                                            class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-bold text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                                        <option value="cash">Cash</option>
                                        <option value="upi">UPI / QR Code</option>
                                        <option value="card">Card / POS Machine</option>
                                        <option value="netbanking">Net Banking</option>
                                        <option value="cheque">Cheque</option>
                                    </select>
                                </div>

                                <div class="sm:col-span-3">
                                    <input type="number" 
                                           step="0.01" 
                                           x-model="ptCollectedAmount" 
                                           placeholder="0" 
                                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-mono focus:border-indigo-500 focus:outline-none shadow-sm">
                                </div>

                                <div class="sm:col-span-4">
                                    <input type="text" 
                                           name="transaction_reference" 
                                           x-model="ptRef" 
                                           placeholder="Ref / Txn ID (Optional)" 
                                           class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs font-mono focus:border-indigo-500 focus:outline-none shadow-sm">
                                </div>

                                <div class="sm:col-span-1">
                                    <button type="button" 
                                            class="w-full h-full py-2 px-2 rounded-xl border border-dashed border-indigo-400 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-400 text-xs font-bold whitespace-nowrap hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors flex items-center justify-center cursor-pointer">
                                        + Split
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-1">Notes</span>
                            <textarea name="notes" 
                                      x-model="ptNotes" 
                                      rows="2" 
                                      placeholder="Additional notes..." 
                                      class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between border-t border-slate-200 dark:border-slate-800 pt-4 gap-3">
                            <p class="text-xs text-slate-500 dark:text-slate-400 italic text-center sm:text-left">Thank you for your membership!</p>
                            <div class="flex items-center gap-3 justify-end">
                                <button type="button" 
                                        @click="showAddPtModal = false" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>
                                    <span>Record Payment</span>
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>