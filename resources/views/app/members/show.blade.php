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
        showEnrollClassModal: false,
        selectedScheduleId: '',
        enrollBookingDate: '{{ now()->format('Y-m-d') }}',
        openEnrollClass(scheduleId = '') {
            this.selectedScheduleId = scheduleId;
            this.showEnrollClassModal = true;
        },
        showBookServiceModal: false,
        selectedServiceId: '',
        selectedServiceAmount: 0,
        selectedServiceIsLocker: false,
        selectedServiceIsCountable: false,
        selectedServiceSessionCount: 1,
        serviceBookingDate: '{{ now()->format('Y-m-d') }}',
        serviceBookingTime: '',
        serviceAmountPaid: 0,
        serviceLockerNumber: '',
        serviceNotes: '',
        openBookService(serviceId = '', serviceAmount = 0, isLocker = false, isCountable = false, sessionCount = 1) {
            this.selectedServiceId = serviceId;
            this.selectedServiceAmount = serviceAmount;
            this.serviceAmountPaid = serviceAmount;
            this.selectedServiceIsLocker = isLocker;
            this.selectedServiceIsCountable = isCountable;
            this.selectedServiceSessionCount = sessionCount;
            this.serviceBookingDate = '{{ now()->format('Y-m-d') }}';
            this.serviceBookingTime = '';
            this.serviceLockerNumber = '';
            this.serviceNotes = '';
            this.showBookServiceModal = true;
        },
        updateServiceSelection(e) {
            const opt = e.target.selectedOptions[0];
            if (opt && opt.dataset.price !== undefined) {
                this.selectedServiceAmount = opt.dataset.price;
                this.serviceAmountPaid = opt.dataset.price;
                this.selectedServiceIsLocker = opt.dataset.locker === '1';
                this.selectedServiceIsCountable = opt.dataset.countable === '1';
                this.selectedServiceSessionCount = opt.dataset.sessions || 1;
            } else {
                this.selectedServiceAmount = 0;
                this.serviceAmountPaid = 0;
                this.selectedServiceIsLocker = false;
                this.selectedServiceIsCountable = false;
                this.selectedServiceSessionCount = 1;
            }
        },

        // Workout Routine State
        showAddWorkoutModal: false,
        workoutTitle: '',
        workoutGoal: 'General Fitness',
        workoutLevel: 'beginner',
        workoutTrainerId: '{{ $assignedTrainer?->id ?? '' }}',
        workoutStartDate: '{{ now()->format('Y-m-d') }}',
        workoutEndDate: '',
        workoutNotes: '',
        workoutExercises: [
            { day: 'Day 1', exercise_name: 'Barbell Bench Press', sets: 4, reps: '8-10', weight: '60 kg', rest_seconds: 90, notes: 'Warm up thoroughly' },
            { day: 'Day 1', exercise_name: 'Incline Dumbbell Press', sets: 3, reps: '10-12', weight: '22 kg', rest_seconds: 60, notes: 'Focus on upper chest squeeze' },
            { day: 'Day 1', exercise_name: 'Tricep Cable Pushdown', sets: 3, reps: '12-15', weight: '25 kg', rest_seconds: 45, notes: '' }
        ],
        addWorkoutExercise() {
            this.workoutExercises.push({ day: 'Day ' + (this.workoutExercises.length + 1), exercise_name: '', sets: 3, reps: '10-12', weight: '', rest_seconds: 60, notes: '' });
        },
        removeWorkoutExercise(idx) {
            this.workoutExercises.splice(idx, 1);
        },
        openCreateWorkout() {
            this.workoutTitle = '';
            this.workoutGoal = 'General Fitness';
            this.workoutLevel = 'beginner';
            this.workoutTrainerId = '{{ $assignedTrainer?->id ?? '' }}';
            this.workoutStartDate = '{{ now()->format('Y-m-d') }}';
            this.workoutEndDate = '';
            this.workoutNotes = '';
            this.workoutExercises = [
                { day: 'Day 1', exercise_name: 'Barbell Bench Press', sets: 4, reps: '8-10', weight: '60 kg', rest_seconds: 90, notes: 'Warm up thoroughly' },
                { day: 'Day 1', exercise_name: 'Incline Dumbbell Press', sets: 3, reps: '10-12', weight: '22 kg', rest_seconds: 60, notes: 'Focus on upper chest squeeze' },
                { day: 'Day 1', exercise_name: 'Tricep Cable Pushdown', sets: 3, reps: '12-15', weight: '25 kg', rest_seconds: 45, notes: '' }
            ];
            this.showAddWorkoutModal = true;
        },
        loadWorkoutPreset(presetType) {
            if (presetType === 'push') {
                this.workoutTitle = 'Push Day (Chest, Shoulders, Triceps)';
                this.workoutGoal = 'Hypertrophy';
                this.workoutLevel = 'intermediate';
                this.workoutExercises = [
                    { day: 'Day 1', exercise_name: 'Barbell Flat Bench Press', sets: 4, reps: '8-10', weight: '', rest_seconds: 90, notes: 'Heavy compound press' },
                    { day: 'Day 1', exercise_name: 'Incline Dumbbell Press', sets: 3, reps: '10-12', weight: '', rest_seconds: 60, notes: 'Upper chest hypertrophy' },
                    { day: 'Day 1', exercise_name: 'Dumbbell Lateral Raises', sets: 4, reps: '12-15', weight: '', rest_seconds: 45, notes: 'Strict form, no swinging' },
                    { day: 'Day 1', exercise_name: 'Overhead Dumbbell Extension', sets: 3, reps: '10-12', weight: '', rest_seconds: 60, notes: 'Full stretch on triceps' }
                ];
            } else if (presetType === 'pull') {
                this.workoutTitle = 'Pull Day (Back & Biceps)';
                this.workoutGoal = 'Hypertrophy';
                this.workoutLevel = 'intermediate';
                this.workoutExercises = [
                    { day: 'Day 2', exercise_name: 'Lat Pulldowns / Pull-ups', sets: 4, reps: '8-10', weight: '', rest_seconds: 90, notes: 'Engage lats first' },
                    { day: 'Day 2', exercise_name: 'Barbell Bent-Over Rows', sets: 4, reps: '8-10', weight: '', rest_seconds: 90, notes: 'Keep back neutral' },
                    { day: 'Day 2', exercise_name: 'Face Pulls', sets: 3, reps: '15', weight: '', rest_seconds: 45, notes: 'Rear delt and posture' },
                    { day: 'Day 2', exercise_name: 'Barbell Bicep Curls', sets: 3, reps: '10-12', weight: '', rest_seconds: 60, notes: 'Controlled negative' }
                ];
            } else if (presetType === 'legs') {
                this.workoutTitle = 'Leg Day (Quads, Hamstrings, Calves)';
                this.workoutGoal = 'Strength';
                this.workoutLevel = 'intermediate';
                this.workoutExercises = [
                    { day: 'Day 3', exercise_name: 'Barbell Back Squats', sets: 4, reps: '6-8', weight: '', rest_seconds: 120, notes: 'Hit parallel depth' },
                    { day: 'Day 3', exercise_name: 'Romanian Deadlifts (RDL)', sets: 4, reps: '8-10', weight: '', rest_seconds: 90, notes: 'Hinge at the hips' },
                    { day: 'Day 3', exercise_name: 'Leg Press', sets: 3, reps: '12-15', weight: '', rest_seconds: 60, notes: 'Continuous tension' },
                    { day: 'Day 3', exercise_name: 'Standing Calf Raises', sets: 4, reps: '15-20', weight: '', rest_seconds: 45, notes: 'Pause at top and bottom' }
                ];
            }
        },
        loadWorkoutTemplate(tpl) {
            this.workoutTitle = tpl.title;
            this.workoutGoal = tpl.goal || 'General Fitness';
            this.workoutLevel = tpl.level || 'beginner';
            this.workoutTrainerId = tpl.trainer_id || '{{ $assignedTrainer?->id ?? '' }}';
            this.workoutNotes = tpl.notes || '';
            this.workoutStartDate = '{{ now()->format('Y-m-d') }}';
            this.workoutEndDate = '';
            if (tpl.exercises && tpl.exercises.length) {
                this.workoutExercises = tpl.exercises.map(e => ({
                    day: e.day || 'Day 1',
                    exercise_name: e.exercise_name,
                    sets: e.sets || 3,
                    reps: e.reps || '10-12',
                    weight: e.weight || '',
                    rest_seconds: e.rest_seconds || 60,
                    notes: e.notes || ''
                }));
            } else {
                this.workoutExercises = [{ day: 'Day 1', exercise_name: '', sets: 3, reps: '10-12', weight: '', rest_seconds: 60, notes: '' }];
            }
            this.showAddWorkoutModal = true;
        },

        // Diet Plan State
        showAddDietModal: false,
        showAiDietModal: false,
        dietTitle: '',
        dietCalories: 2000,
        dietProtein: 140,
        dietCarbs: 220,
        dietFat: 60,
        dietStartDate: '{{ now()->format('Y-m-d') }}',
        dietEndDate: '',
        dietGuidelines: '',
        dietTrainerId: '{{ $assignedTrainer?->id ?? '' }}',
        dietMeals: [
            { meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Power Breakfast', items_description: '3 Whole Eggs / Tofu Scramble + 2 Multigrain Toast + 1 Banana', calories: 450 },
            { meal_type: 'lunch', recommended_time: '13:30', meal_name: 'High Protein Lunch', items_description: '150g Grilled Chicken / Paneer + Brown Rice + Green Salad', calories: 650 },
            { meal_type: 'evening_snack', recommended_time: '17:30', meal_name: 'Pre-Workout Fuel', items_description: '1 Scoop Whey Protein + 1 Apple + Handful of Almonds', calories: 300 },
            { meal_type: 'dinner', recommended_time: '20:30', meal_name: 'Clean Dinner', items_description: 'Steamed Veggies + Yellow Dal + 2 Chapatis', calories: 500 }
        ],
        addDietMeal() {
            this.dietMeals.push({ meal_type: 'lunch', recommended_time: '13:00', meal_name: 'Meal', items_description: '', calories: 500 });
        },
        removeDietMeal(idx) {
            this.dietMeals.splice(idx, 1);
        },
        openCreateDiet() {
            this.dietTitle = '';
            this.dietCalories = 2000;
            this.dietProtein = 140;
            this.dietCarbs = 220;
            this.dietFat = 60;
            this.dietStartDate = '{{ now()->format('Y-m-d') }}';
            this.dietEndDate = '';
            this.dietGuidelines = 'Drink at least 3-4 liters of water daily. Avoid refined sugars and processed junk food.';
            this.dietTrainerId = '{{ $assignedTrainer?->id ?? '' }}';
            this.dietMeals = [
                { meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Power Breakfast', items_description: '3 Whole Eggs / Tofu Scramble + 2 Multigrain Toast + 1 Banana', calories: 450 },
                { meal_type: 'lunch', recommended_time: '13:30', meal_name: 'High Protein Lunch', items_description: '150g Grilled Chicken / Paneer + Brown Rice + Green Salad', calories: 650 },
                { meal_type: 'evening_snack', recommended_time: '17:30', meal_name: 'Pre-Workout Fuel', items_description: '1 Scoop Whey Protein + 1 Apple + Handful of Almonds', calories: 300 },
                { meal_type: 'dinner', recommended_time: '20:30', meal_name: 'Clean Dinner', items_description: 'Steamed Veggies + Yellow Dal + 2 Chapatis', calories: 500 }
            ];
            this.showAddDietModal = true;
        },
        loadDietTemplate(tpl) {
            this.dietTitle = tpl.title;
            this.dietCalories = tpl.daily_calories || 2000;
            this.dietProtein = tpl.protein_grams || 140;
            this.dietCarbs = tpl.carbs_grams || 220;
            this.dietFat = tpl.fat_grams || 60;
            this.dietStartDate = '{{ now()->format('Y-m-d') }}';
            this.dietEndDate = '';
            this.dietGuidelines = tpl.guidelines || '';
            this.dietTrainerId = tpl.trainer_id || '{{ $assignedTrainer?->id ?? '' }}';
            if (tpl.meals && tpl.meals.length) {
                this.dietMeals = tpl.meals.map(m => ({
                    meal_type: m.meal_type || 'breakfast',
                    recommended_time: m.recommended_time ? m.recommended_time.substring(0, 5) : '',
                    meal_name: m.meal_name,
                    items_description: m.items_description || '',
                    calories: m.calories || 0
                }));
            } else {
                this.dietMeals = [{ meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Breakfast', items_description: '', calories: 400 }];
            }
            this.showAddDietModal = true;
        },

        // AI Diet Generator State
        aiDietGoal: 'weight_loss',
        aiDietPreference: 'vegetarian',
        aiDietCalories: 2000,
        aiDietMealsPerDay: 4,
        aiDietLoading: false,
        aiDietError: null,
        async generateAiDiet() {
            this.aiDietLoading = true;
            this.aiDietError = null;
            try {
                const res = await fetch('{{ route('app.diets.generate-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        member_id: {{ $member->id }},
                        goal: this.aiDietGoal,
                        diet_preference: this.aiDietPreference,
                        daily_calories: this.aiDietCalories,
                        meals_per_day: this.aiDietMealsPerDay,
                        auto_save: true
                    })
                });
                const data = await res.json();
                if (data.success) {
                    window.location.reload();
                } else {
                    this.aiDietError = data.message || 'Failed to generate AI diet.';
                }
            } catch (err) {
                this.aiDietError = 'Error generating AI diet plan. Please try again.';
            } finally {
                this.aiDietLoading = false;
            }
        },
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
    }" class="space-y-4 sm:space-y-6 pb-32 sm:pb-16 max-w-7xl mx-auto">

        <!-- Top Bar Navigation -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2.5 sm:gap-3">
            <a href="{{ route('app.members.index') }}" 
               class="px-3.5 sm:px-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 hover:text-slate-900 dark:text-slate-300 dark:hover:text-white hover:bg-slate-50 dark:hover:bg-slate-800 text-xs font-bold flex items-center gap-2 transition-all shadow-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Members</span>
            </a>

            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-slate-400 font-semibold">
                <span class="w-2 h-2 rounded-full shrink-0 {{ $member->status === 'ACTIVE' ? 'bg-emerald-400' : ($member->status === 'SUSPENDED' ? 'bg-rose-400' : 'bg-orange-400') }}"></span>
                <span>Branch: <strong class="text-slate-900 dark:text-white">{{ $member->branch->name ?? 'Main Branch' }}</strong></span>
            </div>
        </div>

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div class="p-3.5 sm:p-4 rounded-2xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="leading-relaxed">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 sm:p-4 rounded-2xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3 shadow-sm">
                <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="leading-relaxed">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Top Header Profile Banner -->
        <div class="p-3.5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 sm:gap-6">
            <!-- Left Info Block -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 w-full lg:w-auto">
                <div class="min-w-0 w-full sm:w-auto">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white tracking-tight truncate">{{ $member->full_name }}</h1>
                        <span class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold shrink-0">
                            {{ $member->member_code }}
                        </span>
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2 sm:mt-2.5">
                        <!-- Status Badge -->
                        @if($member->status === 'ACTIVE')
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                <span>Active</span>
                            </span>
                        @elseif($member->status === 'SUSPENDED')
                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                <span>Frozen</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-500/15 border border-orange-200 dark:border-orange-500/30 text-orange-700 dark:text-orange-400 font-extrabold text-[11px] flex items-center gap-1.5 shadow-sm shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 dark:bg-orange-400"></span>
                                <span>{{ $member->status }}</span>
                            </span>
                        @endif

                        <!-- ID Badge -->
                        <span class="p-1 rounded-md bg-blue-50 dark:bg-blue-500/15 border border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-400 text-xs font-bold shrink-0" title="Digital ID Card">
                            💳
                        </span>

                        <!-- Plan Badge -->
                        @if($activeMembership && $activeMembership->plan)
                            <span class="px-2.5 sm:px-3 py-1 rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-bold text-xs flex items-center gap-1.5 shadow-sm shrink-0">
                                <span>👑</span>
                                <span>{{ $activeMembership->plan->name }}</span>
                            </span>
                        @endif

                        <!-- Phone -->
                        <a href="tel:{{ $member->phone }}" class="px-2.5 sm:px-3 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 border border-slate-200 text-slate-700 hover:text-slate-900 dark:bg-slate-800 dark:hover:bg-slate-700 dark:border-slate-700/60 dark:text-slate-300 dark:hover:text-white font-semibold text-xs flex items-center gap-1.5 transition-colors shadow-sm shrink-0">
                            <span>📞</span>
                            <span>{{ $member->phone }}</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Metrics & Actions Block -->
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5 sm:gap-3 w-full lg:w-auto justify-start lg:justify-end">
                <!-- Metrics Grid on Mobile -->
                <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center sm:gap-3 shrink-0">
                    <!-- Metric 1: Days Left -->
                    <div class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                        <p class="text-base sm:text-xl font-black {{ ($daysLeft !== null && $daysLeft < 7) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $daysLeft !== null ? max(0, $daysLeft) : '0' }}
                        </p>
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mt-0.5">DAYS LEFT</span>
                    </div>

                    <!-- Metric 2: Months Active -->
                    <div class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                        <p class="text-base sm:text-xl font-black text-slate-900 dark:text-white">{{ $monthsActive }}</p>
                        <span class="text-[9px] sm:text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mt-0.5">MONTHS ACTIVE</span>
                    </div>
                </div>

                <!-- Actions Buttons Grid on Mobile -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
                    <!-- Actions: Add Payment -->
                    <button type="button" 
                            @click="showAddPaymentModal = true" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Payment</span>
                    </button>

                    <!-- Actions: Edit -->
                    <button type="button" 
                            @click="showEditModal = true" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit</span>
                    </button>

                    <!-- Actions: WhatsApp Menu -->
                    <div class="relative w-full sm:w-auto" @click.away="showWhatsappMenu = false">
                        <button type="button" 
                                @click="showWhatsappMenu = !showWhatsappMenu" 
                                class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-emerald-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-emerald-400 dark:border-slate-700 font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>WhatsApp</span>
                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="showWhatsappMenu" 
                             x-transition 
                             class="absolute right-0 sm:right-0 mt-2 w-56 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl py-2 z-50 text-xs">
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

                    <!-- Actions: Delete -->
                    <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                          data-confirm="Are you sure you want to permanently delete member '{{ addslashes($member->full_name) }}'? All membership records, workout history, and payments will be removed." 
                          data-confirm-title="Delete Gym Member" 
                          data-confirm-btn="Yes, Delete Member" 
                          data-confirm-type="danger" 
                          class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 dark:text-rose-400 dark:border-rose-500/30 font-bold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-sm">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Main Layout: Left Sidebar Profile & Right Content Tabs -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6 items-start">
            
            <!-- ========================================================================= -->
            <!-- LEFT COLUMN: MEMBER PROFILE, MEMBERSHIP, FITNESS & GOALS, CONTACT & SYSTEM -->
            <!-- ========================================================================= -->
            <div class="lg:col-span-4 space-y-4 sm:space-y-6">

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
            <div class="lg:col-span-8 space-y-4 sm:space-y-6 min-w-0">

                <!-- Scrollable Tab Header Bar -->
                <div class="flex items-center gap-1.5 overflow-x-auto p-1.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-bold no-scrollbar shadow-sm -mx-3 px-3 sm:mx-0 sm:px-1.5">
                    <button type="button" 
                            @click="activeTab = 'subscriptions'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'subscriptions' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Subscriptions
                    </button>

                    <button type="button" 
                            @click="activeTab = 'pt_packages'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'pt_packages' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        PT Packages
                    </button>

                    <button type="button" 
                            @click="activeTab = 'services'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'services' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Services
                    </button>

                    <button type="button" 
                            @click="activeTab = 'invoices'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'invoices' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Invoices
                    </button>

                    <button type="button" 
                            @click="activeTab = 'workouts'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'workouts' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Workout Plans
                    </button>

                    <button type="button" 
                            @click="activeTab = 'diets'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'diets' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Diet Plans
                    </button>

                    <button type="button" 
                            @click="activeTab = 'classes'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'classes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Classes
                    </button>

                    <button type="button" 
                            @click="activeTab = 'attendance'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'attendance' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Attendance
                    </button>

                    <button type="button" 
                            @click="activeTab = 'measurements'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'measurements' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Measurements
                    </button>

                    <button type="button" 
                            @click="activeTab = 'audit_trail'"
                            class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                            :class="activeTab === 'audit_trail' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                        Audit Trail <span class="px-1.5 py-0.5 rounded-full bg-slate-100 dark:bg-slate-800 text-[10px] ml-1">{{ $auditLogs->count() }}</span>
                    </button>
                </div>

                <!-- TAB 1: SUBSCRIPTIONS HISTORY -->
                <div x-show="activeTab === 'subscriptions'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden space-y-4 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h2 class="text-base font-black text-slate-900 dark:text-white">Subscription History</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">All current and previous gym membership packages</p>
                        </div>

                        <button type="button" 
                                @click="showAddSubscriptionModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Subscription</span>
                        </button>
                    </div>

                    <!-- Subscription Table -->
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="w-full text-left text-xs min-w-[620px]">
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
                <div x-show="activeTab === 'pt_packages'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Personal Training</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Assigned personal training packages, sessions and dedicated coaching</p>
                        </div>
                        <button type="button" 
                                @click="showAddPtModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
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
                                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold shrink-0">
                                            🏋️
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs">{{ $assignedTrainer->full_name }}</h4>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400">{{ $assignedTrainer->specialization ?? 'Personal Trainer' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                        Assigned Coach
                                    </span>
                                </div>
                            @endif

                            @foreach($ptPackages as $pt)
                                <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 shadow-sm">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-base shrink-0">
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
                                    <div class="flex items-center justify-between sm:justify-end gap-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-slate-200 dark:border-slate-800">
                                        <div class="text-left sm:text-right">
                                            <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-semibold">TOTAL / PAID</span>
                                            <span class="font-bold text-slate-900 dark:text-white text-xs">{{ $currency }}{{ number_format($pt['total'] ?? 0, 2) }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400 text-[11px] block font-semibold">{{ $currency }}{{ number_format($pt['paid'] ?? 0, 2) }}</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                            {{ $pt['status'] ?? 'Active' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- TAB 3: SERVICES -->
                <div x-show="activeTab === 'services'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>✨</span>
                                <span>Gym Amenities & Services</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Manage spa, sauna, locker rental, and booked services for {{ $member->first_name }}</p>
                        </div>
                        <button type="button" 
                                @click="openBookService()" 
                                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Book Service</span>
                        </button>
                    </div>

                    @php
                        $memberServiceBookings = $member->serviceBookings ?? \App\Models\GymServiceBooking::with('service')
                            ->where('member_id', $member->id)
                            ->latest()
                            ->get();
                    @endphp

                    @if($memberServiceBookings->isEmpty())
                        <div class="p-6 sm:p-8 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🧖
                            </div>
                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">No Services Booked Yet</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                {{ $member->first_name }} has not booked any gym amenities, spa/sauna, or locker services yet.
                            </p>
                            <button type="button" 
                                    @click="openBookService()" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Book A Service Now</span>
                            </button>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            @foreach($memberServiceBookings as $booking)
                                @php
                                    $svc = $booking->service;
                                    $svcNameLower = strtolower($svc?->name ?? '');
                                    $icon = match(true) {
                                        str_contains($svcNameLower, 'steam') || str_contains($svcNameLower, 'sauna') => '🧖',
                                        str_contains($svcNameLower, 'massage') || str_contains($svcNameLower, 'spa') => '💆',
                                        str_contains($svcNameLower, 'locker') => '🔒',
                                        str_contains($svcNameLower, 'pool') || str_contains($svcNameLower, 'swim') => '🏊',
                                        str_contains($svcNameLower, 'physio') || str_contains($svcNameLower, 'therapy') => '🩺',
                                        str_contains($svcNameLower, 'diet') || str_contains($svcNameLower, 'nutrition') => '🥗',
                                        str_contains($svcNameLower, 'towel') || str_contains($svcNameLower, 'laundry') => '🧺',
                                        default => '✨',
                                    };
                                @endphp
                                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-4 shadow-sm hover:border-indigo-500/40 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg shrink-0 border border-indigo-200 dark:border-indigo-500/20">
                                                {{ $icon }}
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $svc?->name ?? 'Gym Service' }}</h4>
                                                <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
                                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-bold text-[10px]">{{ $currency }}{{ number_format($booking->amount_paid, 0) }}</span>
                                                    @if($booking->locker_number)
                                                        <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold text-[10px]">Locker: {{ $booking->locker_number }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-xs font-semibold text-slate-700 dark:text-slate-300 space-y-0.5">
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400">
                                                        Booked: <strong class="text-slate-700 dark:text-slate-300">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : $booking->created_at->format('d M Y') }}</strong>
                                                        @if($booking->booking_time)
                                                            <span>• {{ $booking->booking_time }}</span>
                                                        @endif
                                                    </p>
                                                    @if($svc?->is_session_countable)
                                                        <p class="text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">
                                                            Sessions: {{ $booking->sessions_left }} / {{ $booking->total_sessions }} remaining
                                                        </p>
                                                    @endif
                                                    @if($booking->notes)
                                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 italic mt-0.5">"{{ $booking->notes }}"</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        @if($booking->status === 'active')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black text-[10px] border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                                Active
                                            </span>
                                        @elseif($booking->status === 'completed')
                                            <span class="px-2.5 py-1 rounded-full bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-black text-[10px] border border-slate-200 dark:border-slate-700 shrink-0">
                                                Completed
                                            </span>
                                        @elseif($booking->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 font-black text-[10px] border border-amber-200 dark:border-amber-500/20 shrink-0">
                                                Pending
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-black text-[10px] border border-rose-200 dark:border-rose-500/20 shrink-0">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="flex items-center justify-between pt-3 border-t border-slate-200/80 dark:border-slate-800/80">
                                        <div class="flex items-center gap-2">
                                            @if($booking->status === 'active' && $booking->sessions_left > 0)
                                                <form action="{{ route('app.services.bookings.deduct', $booking->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] flex items-center gap-1 shadow-sm transition-all cursor-pointer">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Deduct Session</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('app.services.bookings.status', $booking->id) }}" method="POST" class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500">
                                                    <option value="active" {{ $booking->status === 'active' ? 'selected' : '' }}>Active</option>
                                                    <option value="completed" {{ $booking->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                    <option value="pending" {{ $booking->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="cancelled" {{ $booking->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                                </select>
                                            </form>
                                        </div>

                                        <form action="{{ route('app.services.bookings.delete', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this service booking record?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-700 text-xs font-bold hover:underline cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Available Amenities / Services Grid for Quick Booking -->
                    @if(isset($availableServices) && $availableServices->isNotEmpty())
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">Available Gym Amenities & Add-on Services</h4>
                                <a href="{{ route('app.services.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-bold">Manage All Services →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($availableServices as $svc)
                                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-3 shadow-xs">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-bold text-slate-900 dark:text-white text-xs">{{ $svc->name }}</h5>
                                                <span class="text-xs font-black text-amber-600 dark:text-amber-400 shrink-0">{{ $currency }}{{ number_format($svc->amount, 0) }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                {{ $svc->description ?? 'Gym amenity service available for members.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-[10px] text-slate-500 dark:text-slate-400 font-semibold">
                                                <span>⏱ {{ $svc->duration_minutes }} mins</span>
                                                <span>•</span>
                                                <span>{{ $svc->timeslot_availability ?? 'By Appointment' }}</span>
                                                @if($svc->is_session_countable)
                                                    <span>•</span>
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $svc->session_count }} sessions</span>
                                                @endif
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="openBookService('{{ $svc->id }}', {{ (float)$svc->amount }}, {{ $svc->is_locker_service ? 'true' : 'false' }}, {{ $svc->is_session_countable ? 'true' : 'false' }}, {{ (int)$svc->session_count }})" 
                                                class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-[11px] font-bold transition-all text-center cursor-pointer">
                                            + Book Service
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 4: INVOICES & PAYMENT TRAIL -->
                <div x-show="activeTab === 'invoices'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>💳</span>
                                <span>Payment & Invoice Trail</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Grouped by subscription package with full payment receipts and invoices</p>
                        </div>
                        <button type="button" 
                                @click="showAddPaymentModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Collect Payment</span>
                        </button>
                    </div>

                    <!-- Subscription Payment Cards Trail -->
                    <div class="space-y-4 sm:space-y-6">
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
                                <div class="p-3.5 sm:p-4 bg-slate-100/80 dark:bg-slate-900/90 border-b border-slate-200 dark:border-slate-800/80 flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-sm shrink-0">
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
                                    <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-3 text-right flex-wrap lg:flex-nowrap">
                                        <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 shadow-sm text-center sm:text-right">
                                            <span class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">PLAN PRICE</span>
                                            <span class="text-xs font-black text-slate-900 dark:text-white">{{ $currency }}{{ number_format($planPrice, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 shadow-sm text-center sm:text-right">
                                            <span class="text-[9px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">DISCOUNT</span>
                                            <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $currency }}{{ number_format($discount, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/20 shadow-sm text-center sm:text-right">
                                            <span class="text-[9px] font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">RECEIVED</span>
                                            <span class="text-xs font-black text-emerald-700 dark:text-emerald-400">{{ $currency }}{{ number_format($received, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg {{ $due > 0 ? 'bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-500/30' : 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80' }} shadow-sm text-center sm:text-right">
                                            <span class="text-[9px] font-extrabold {{ $due > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-slate-400' }} uppercase tracking-wider block">DUE</span>
                                            <span class="text-xs font-black {{ $due > 0 ? 'text-amber-700 dark:text-amber-400' : 'text-slate-600 dark:text-slate-400' }}">{{ $currency }}{{ number_format($due, 2) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Payment Records Table for this Subscription -->
                                <div class="overflow-x-auto">
                                    <table class="w-full text-left text-xs min-w-[580px]">
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
                                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
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
                            <div class="p-6 sm:p-8 text-center bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs">
                                No membership subscription or payment records found for this member yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- TAB 5: WORKOUT PLANS -->
                <div x-show="activeTab === 'workouts'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>🏋️</span>
                                <span>Assigned Workout Routines & Splits</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Personalized exercise programs, splits, and training regimens for {{ $member->full_name }}</p>
                        </div>
                        <button type="button" 
                                @click="openCreateWorkout()" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Create Workout Routine</span>
                        </button>
                    </div>

                    <!-- Member's Assigned Workout Routines -->
                    @if($member->workoutPlans->isNotEmpty())
                        <div class="grid grid-cols-1 {{ $member->workoutPlans->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-4">
                            @foreach($member->workoutPlans as $wp)
                                @php
                                    $formattedGoal = ucwords(str_replace(['_', '-'], ' ', $wp->goal ?? 'General Fitness'));
                                    $formattedLevel = ucfirst(str_replace(['_', '-'], ' ', $wp->level ?? 'All Levels'));

                                    $whatsappWorkoutText = "🏋️ *Workout Routine from " . (auth()->user()->tenant->name ?? 'Gym') . "*\n";
                                    $whatsappWorkoutText .= "*Routine:* " . $wp->title . "\n";
                                    if ($wp->goal) $whatsappWorkoutText .= "*Goal:* " . $formattedGoal . " | *Level:* " . $formattedLevel . "\n";
                                    $whatsappWorkoutText .= "---------------------------\n";
                                    foreach ($wp->exercises as $exIdx => $ex) {
                                        $dayLabel = $ex->day ? "[" . ucwords(str_replace('_', ' ', $ex->day)) . "] " : '';
                                        $whatsappWorkoutText .= ($exIdx + 1) . ". *" . $dayLabel . $ex->exercise_name . "*\n";
                                        $whatsappWorkoutText .= "   Sets: " . $ex->sets . " × " . $ex->reps . ($ex->weight ? " (" . $ex->weight . ")" : "") . ($ex->rest_seconds ? " | Rest: " . $ex->rest_seconds . "s" : "") . "\n";
                                    }
                                    if ($wp->notes) {
                                        $whatsappWorkoutText .= "---------------------------\n*Notes:* " . $wp->notes . "\n";
                                    }
                                    $whatsappWorkoutText .= "\nKeep pushing your limits! 💪";
                                @endphp

                                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm hover:border-indigo-500/40 transition-all">
                                    <div>
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3 mb-2.5">
                                            <div>
                                                <h4 class="font-black text-slate-900 dark:text-white text-base leading-tight">{{ $wp->title }}</h4>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-slate-500 dark:text-slate-400 flex-wrap">
                                                    <span class="font-semibold text-slate-700 dark:text-slate-300">Level: {{ $formattedLevel }}</span>
                                                    @if($wp->trainer)
                                                        <span>• Coach: <strong class="text-slate-800 dark:text-slate-200">{{ $wp->trainer->full_name }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($wp->goal)
                                                <span class="self-start px-2.5 py-1 rounded-xl text-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold uppercase tracking-wider shrink-0">
                                                    {{ $formattedGoal }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($wp->start_date)
                                            <div class="text-[10px] text-slate-400 dark:text-slate-500 mb-3 font-mono">
                                                Active from {{ $wp->start_date->format('d M Y') }} {{ $wp->end_date ? 'to ' . $wp->end_date->format('d M Y') : '' }}
                                            </div>
                                        @endif

                                        <!-- Exercises List -->
                                        <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3 mt-2">
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                                                Prescribed Exercises ({{ $wp->exercises->count() }})
                                            </span>
                                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                                @forelse($wp->exercises as $ex)
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 text-xs">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold shrink-0">
                                                                    {{ ucwords(str_replace('_', ' ', $ex->day ?? 'Day 1')) }}
                                                                </span>
                                                                <span class="truncate">{{ $ex->exercise_name }}</span>
                                                            </div>
                                                            @if($ex->notes)
                                                                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">{{ $ex->notes }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center text-right shrink-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                                                            <span class="text-amber-600 dark:text-amber-400 font-bold font-mono text-xs">{{ $ex->sets }} × {{ $ex->reps }}</span>
                                                            @if($ex->weight)
                                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">{{ $ex->weight }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">No exercises added to this routine yet.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        @if($wp->notes)
                                            <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 text-[11px] text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                                                <span class="font-bold text-indigo-700 dark:text-indigo-400 block mb-0.5">Trainer Advice:</span>
                                                {{ $wp->notes }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3 mt-4 flex flex-wrap items-center justify-between gap-2">
                                        @if($member->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode($whatsappWorkoutText) }}" 
                                               target="_blank" 
                                               class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5 transition-colors">
                                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                <span>Share on WhatsApp</span>
                                            </a>
                                        @else
                                            <div></div>
                                        @endif

                                        <form action="{{ route('app.workouts.delete', $wp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this workout routine?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete Routine</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🏋️
                            </div>
                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">No Workout Routine Assigned</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                {{ $member->first_name }} does not have a customized workout plan assigned yet. Create a tailored split or assign one of the pre-made templates below.
                            </p>
                            <button type="button" 
                                    @click="openCreateWorkout()" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Create Custom Routine</span>
                            </button>
                        </div>
                    @endif

                    <!-- Pre-made Workout Templates Quick-Assign -->
                    @if(isset($templateWorkoutPlans) && $templateWorkoutPlans->isNotEmpty())
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">📋 Quick Assign from Workout Templates</h4>
                                <a href="{{ route('app.workouts.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-bold">Manage All Workouts →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($templateWorkoutPlans as $tpl)
                                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-3 shadow-xs hover:border-indigo-500/30 transition-all">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-bold text-slate-900 dark:text-white text-xs leading-snug">{{ $tpl->title }}</h5>
                                                @if($tpl->goal)
                                                    <span class="px-2 py-0.5 rounded text-[9px] bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold shrink-0">{{ ucwords(str_replace(['_', '-'], ' ', $tpl->goal)) }}</span>
                                                @endif
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                {{ $tpl->notes ?? 'Standard workout split template.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-[10px] text-slate-500 dark:text-slate-400 font-semibold">
                                                <span class="capitalize">Level: {{ ucfirst(str_replace(['_', '-'], ' ', $tpl->level ?? 'All')) }}</span>
                                                <span>•</span>
                                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">{{ $tpl->exercises->count() }} exercises</span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="loadWorkoutTemplate({{ Js::from($tpl) }})" 
                                                class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-[11px] font-bold transition-all text-center cursor-pointer">
                                            + Assign to {{ $member->first_name }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 6: DIET PLANS -->
                <div x-show="activeTab === 'diets'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>🥗</span>
                                <span>Assigned Diet & Nutrition Charts</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Meal timings, calorie distribution, and macro targets prescribed for {{ $member->full_name }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                            <button type="button" 
                                    @click="showAiDietModal = true" 
                                    class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                                <span>✨</span>
                                <span>AI Diet Generator</span>
                            </button>
                            <button type="button" 
                                    @click="openCreateDiet()" 
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-emerald-600/20 transition-all cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Create Diet Plan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Member's Assigned Diet Plans -->
                    @if($member->dietPlans->isNotEmpty())
                        <div class="grid grid-cols-1 {{ $member->dietPlans->count() > 1 ? 'lg:grid-cols-2' : '' }} gap-4">
                            @foreach($member->dietPlans as $dp)
                                @php
                                    $protein = (int) ($dp->protein_grams ?? 0);
                                    $carbs = (int) ($dp->carbs_grams ?? 0);
                                    $fat = (int) ($dp->fat_grams ?? 0);
                                    $totalMacros = max(1, $protein + $carbs + $fat);
                                    $pPct = round(($protein / $totalMacros) * 100);
                                    $cPct = round(($carbs / $totalMacros) * 100);
                                    $fPct = max(0, 100 - $pPct - $cPct);

                                    $whatsappDietText = "🥗 *Diet & Nutrition Chart from " . (auth()->user()->tenant->name ?? 'Gym') . "*\n";
                                    $whatsappDietText .= "*Plan:* " . $dp->title . "\n";
                                    $whatsappDietText .= "*Target Calories:* " . ($dp->daily_calories ?? 2000) . " kcal\n";
                                    $whatsappDietText .= "*Daily Macros:* P: " . $protein . "g | C: " . $carbs . "g | F: " . $fat . "g\n";
                                    $whatsappDietText .= "---------------------------\n*MEAL SCHEDULE:*\n";
                                    foreach ($dp->meals as $m) {
                                        $whatsappDietText .= "• *" . strtoupper(str_replace('_', ' ', $m->meal_type)) . ($m->recommended_time ? " (" . substr($m->recommended_time, 0, 5) . ")" : "") . "*: " . $m->meal_name . "\n";
                                        if ($m->items_description) $whatsappDietText .= "  Items: " . $m->items_description . "\n";
                                    }
                                    if ($dp->guidelines) {
                                        $whatsappDietText .= "---------------------------\n*Guidelines:* " . $dp->guidelines . "\n";
                                    }
                                    $whatsappDietText .= "\nStay consistent and fueled! 🥗";
                                @endphp

                                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between shadow-sm hover:border-indigo-500/40 transition-all">
                                    <div>
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3 mb-2.5">
                                            <div>
                                                <h4 class="font-black text-slate-900 dark:text-white text-base leading-tight">{{ $dp->title }}</h4>
                                                @if($dp->trainer)
                                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Nutritionist / Coach: <strong class="text-slate-800 dark:text-slate-200">{{ $dp->trainer->full_name }}</strong></p>
                                                @endif
                                            </div>
                                            <div class="self-start px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-black shrink-0 inline-flex items-center gap-1">
                                                <span>🔥</span>
                                                <span>{{ number_format($dp->daily_calories ?? 2000) }} kcal</span>
                                            </div>
                                        </div>

                                        @if($dp->start_date)
                                            <div class="text-[10px] text-slate-400 dark:text-slate-500 mb-3 font-mono">
                                                Active from {{ $dp->start_date->format('d M Y') }} {{ $dp->end_date ? 'to ' . $dp->end_date->format('d M Y') : '' }}
                                            </div>
                                        @endif

                                        <!-- Macro Target Distribution -->
                                        <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 mb-3 space-y-2">
                                            <div class="flex items-center justify-between text-[11px] font-bold">
                                                <span class="text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Daily Macro Targets</span>
                                                <div class="flex items-center gap-2.5 text-xs font-mono">
                                                    <span class="text-rose-600 dark:text-rose-400"><strong>{{ $protein }}g</strong> <span class="text-[10px] text-slate-400">Protein</span></span>
                                                    <span class="text-amber-600 dark:text-amber-400"><strong>{{ $carbs }}g</strong> <span class="text-[10px] text-slate-400">Carbs</span></span>
                                                    <span class="text-cyan-600 dark:text-cyan-400"><strong>{{ $fat }}g</strong> <span class="text-[10px] text-slate-400">Fats</span></span>
                                                </div>
                                            </div>

                                            <div class="h-2 w-full rounded-full bg-slate-100 dark:bg-slate-800 flex overflow-hidden">
                                                <div class="bg-rose-500 h-full" style="width: {{ $pPct }}%" title="Protein: {{ $pPct }}%"></div>
                                                <div class="bg-amber-400 h-full" style="width: {{ $cPct }}%" title="Carbs: {{ $cPct }}%"></div>
                                                <div class="bg-cyan-400 h-full" style="width: {{ $fPct }}%" title="Fats: {{ $fPct }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Meals List -->
                                        <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3">
                                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">
                                                Meal Schedule ({{ $dp->meals->count() }})
                                            </span>
                                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                                @forelse($dp->meals as $m)
                                                    <div class="p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800/80 text-xs">
                                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1.5 sm:gap-2">
                                                            <div class="min-w-0 flex-1">
                                                                <div class="font-bold text-slate-900 dark:text-white flex items-center gap-1.5 flex-wrap">
                                                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 uppercase font-mono font-bold">{{ str_replace('_', ' ', $m->meal_type) }}</span>
                                                                    <span>{{ $m->meal_name }}</span>
                                                                </div>
                                                                @if($m->items_description)
                                                                    <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ $m->items_description }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center text-right shrink-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-slate-100 dark:border-slate-800">
                                                                @if($m->recommended_time)
                                                                    <span class="text-[10px] font-mono text-slate-500 dark:text-slate-400 block">⏰ {{ substr($m->recommended_time, 0, 5) }}</span>
                                                                @endif
                                                                @if($m->calories)
                                                                    <span class="text-[10px] font-bold text-emerald-600 dark:text-emerald-400 block">{{ $m->calories }} kcal</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-slate-400 dark:text-slate-500 italic">No scheduled meals added yet.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        @if($dp->guidelines)
                                            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 text-[11px] text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                                                <span class="font-bold text-emerald-700 dark:text-emerald-400 block mb-0.5">Hydration & Guidelines:</span>
                                                {{ $dp->guidelines }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="border-t border-slate-200 dark:border-slate-800/80 pt-3 mt-4 flex flex-wrap items-center justify-between gap-2">
                                        @if($member->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode($whatsappDietText) }}" 
                                               target="_blank" 
                                               class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5 transition-colors">
                                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                <span>Share on WhatsApp</span>
                                            </a>
                                        @else
                                            <div></div>
                                        @endif

                                        <form action="{{ route('app.diets.delete', $dp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this diet plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete Diet</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 dark:bg-emerald-600/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl border border-emerald-200 dark:border-emerald-500/20">
                                🥗
                            </div>
                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">No Diet Chart Assigned</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                {{ $member->first_name }} does not have a nutrition plan or calorie chart assigned yet. Generate one instantly with AI or build a custom meal plan.
                            </p>
                            <div class="flex items-center justify-center gap-2 pt-1 flex-wrap">
                                <button type="button" 
                                        @click="showAiDietModal = true" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                    <span>✨</span>
                                    <span>AI Diet Generator</span>
                                </button>
                                <button type="button" 
                                        @click="openCreateDiet()" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Create Custom Diet</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Pre-made Diet Templates Quick-Assign -->
                    @if(isset($templateDietPlans) && $templateDietPlans->isNotEmpty())
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">📋 Quick Assign from Diet Templates</h4>
                                <a href="{{ route('app.diets.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-bold">Manage All Diets →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($templateDietPlans as $tpl)
                                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-3 shadow-xs hover:border-emerald-500/30 transition-all">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-bold text-slate-900 dark:text-white text-xs leading-snug">{{ $tpl->title }}</h5>
                                                <span class="px-2 py-0.5 rounded text-[9px] bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-bold shrink-0">🔥 {{ $tpl->daily_calories ?? 2000 }} kcal</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1 line-clamp-2">
                                                {{ $tpl->guidelines ?? 'Standard nutritional diet chart.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-[10px] text-slate-500 dark:text-slate-400 font-semibold font-mono">
                                                <span class="text-rose-600 dark:text-rose-400">{{ $tpl->protein_grams ?? 0 }}g P</span>
                                                <span>•</span>
                                                <span class="text-amber-600 dark:text-amber-400">{{ $tpl->carbs_grams ?? 0 }}g C</span>
                                                <span>•</span>
                                                <span class="text-cyan-600 dark:text-cyan-400">{{ $tpl->fat_grams ?? 0 }}g F</span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="loadDietTemplate({{ Js::from($tpl) }})" 
                                                class="w-full py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white dark:bg-emerald-600/20 dark:hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 dark:hover:text-white text-[11px] font-bold transition-all text-center cursor-pointer">
                                            + Assign to {{ $member->first_name }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 7: CLASSES -->
                <div x-show="activeTab === 'classes'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Enrolled Fitness & Studio Classes</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Classes and group session schedules enrolled by this member</p>
                        </div>
                        <button type="button" 
                                @click="openEnrollClass('')" 
                                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>+ Enroll in Class</span>
                        </button>
                    </div>

                    <!-- Enrolled Classes List -->
                    @if($member->classBookings->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4">
                            @foreach($member->classBookings as $booking)
                                @php
                                    $sch = $booking->schedule;
                                    $gc = $sch?->gymClass;
                                    $classType = strtolower($gc?->class_type ?? '');
                                    $icon = match(true) {
                                        str_contains($classType, 'yoga') => '🧘',
                                        str_contains($classType, 'hiit') || str_contains($classType, 'bootcamp') => '🔥',
                                        str_contains($classType, 'zumba') || str_contains($classType, 'dance') => '💃',
                                        str_contains($classType, 'spin') || str_contains($classType, 'cycl') => '🚴',
                                        str_contains($classType, 'pilates') => '🤸',
                                        str_contains($classType, 'crossfit') || str_contains($classType, 'strength') => '🏋️',
                                        str_contains($classType, 'box') || str_contains($classType, 'fight') => '🥊',
                                        default => '⚡',
                                    };
                                @endphp
                                <div class="p-3.5 sm:p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-4 shadow-sm hover:border-indigo-500/40 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg shrink-0 border border-indigo-200 dark:border-indigo-500/20">
                                                {{ $icon }}
                                            </div>
                                            <div>
                                                <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">{{ $gc?->name ?? 'Group Class' }}</h4>
                                                <div class="flex flex-wrap items-center gap-2 mt-1 text-[11px] text-slate-500 dark:text-slate-400 font-semibold">
                                                    <span class="px-2 py-0.5 rounded bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold">{{ $gc?->class_type ?? 'Fitness' }}</span>
                                                    @if($gc?->duration_minutes)
                                                        <span>• {{ $gc->duration_minutes }} mins</span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-xs font-semibold text-slate-700 dark:text-slate-300">
                                                    @if($sch)
                                                        <p class="flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>{{ ucfirst($sch->day_of_week) }} • {{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('g:i A') }}</span>
                                                        </p>
                                                    @endif
                                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                                        Trainer: <span class="text-slate-700 dark:text-slate-300 font-bold">{{ $sch?->trainer?->full_name ?? ($gc?->instructor?->full_name ?? 'Gym Instructor') }}</span>
                                                    </p>
                                                    <p class="text-[10px] text-slate-400 dark:text-slate-500 mt-0.5">
                                                        Booked on: {{ $booking->booking_date ? $booking->booking_date->format('d M Y') : $booking->created_at->format('d M Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        @if($booking->status === 'BOOKED')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-black text-[10px] border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                                Enrolled / Booked
                                            </span>
                                        @elseif($booking->status === 'ATTENDED')
                                            <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 font-black text-[10px] border border-blue-200 dark:border-blue-500/20 shrink-0">
                                                Attended
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-black text-[10px] border border-rose-200 dark:border-rose-500/20 shrink-0">
                                                {{ $booking->status }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="flex items-center justify-between pt-3 border-t border-slate-200/80 dark:border-slate-800/80">
                                        <form action="{{ route('app.classes.booking-status', $booking->id) }}" method="POST" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-[11px] font-bold text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500">
                                                <option value="BOOKED" {{ $booking->status === 'BOOKED' ? 'selected' : '' }}>Enrolled</option>
                                                <option value="ATTENDED" {{ $booking->status === 'ATTENDED' ? 'selected' : '' }}>Attended</option>
                                                <option value="CANCELLED" {{ $booking->status === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                                                <option value="NO_SHOW" {{ $booking->status === 'NO_SHOW' ? 'selected' : '' }}>No Show</option>
                                            </select>
                                        </form>

                                        <form action="{{ route('app.classes.booking.delete', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this class enrollment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-700 text-xs font-bold hover:underline cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🧘
                            </div>
                            <h4 class="font-extrabold text-slate-900 dark:text-white text-sm">No Class Enrollments Yet</h4>
                            <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto">
                                {{ $member->first_name }} has not been enrolled into any group fitness or studio classes yet.
                            </p>
                            <button type="button" 
                                    @click="openEnrollClass('')" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Enroll In A Class Now</span>
                            </button>
                        </div>
                    @endif

                    <!-- Available Classes In The Gym -->
                    @if(isset($allGymClasses) && $allGymClasses->isNotEmpty())
                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs uppercase tracking-wider">Available Gym Classes & Schedules</h4>
                                <a href="{{ route('app.classes.index') }}" class="text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline font-bold">Manage All Classes →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($allGymClasses as $gc)
                                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col justify-between gap-3 shadow-xs">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-bold text-slate-900 dark:text-white text-xs">{{ $gc->name }}</h5>
                                                <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-bold text-[10px] shrink-0">{{ $gc->class_type }}</span>
                                            </div>
                                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-1">
                                                Instructor: <span class="text-slate-700 dark:text-slate-300 font-semibold">{{ $gc->instructor?->full_name ?? 'Gym Trainer' }}</span>
                                            </p>
                                            @if($gc->schedules->isNotEmpty())
                                                <div class="mt-2 space-y-1">
                                                    @foreach($gc->schedules as $sch)
                                                        <div class="flex items-center justify-between text-[11px] text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 px-2.5 py-1 rounded-lg border border-slate-200 dark:border-slate-800/60">
                                                            <span class="font-medium truncate mr-2">{{ ucfirst($sch->day_of_week) }} ({{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }})</span>
                                                            <button type="button" 
                                                                    @click="openEnrollClass('{{ $sch->id }}')" 
                                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline font-bold text-[10px] cursor-pointer shrink-0">
                                                                + Enroll
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="mt-2">
                                                    <button type="button" 
                                                            @click="openEnrollClass('')" 
                                                            class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-[11px] font-bold transition-all text-center cursor-pointer">
                                                        + Enroll in Class
                                                    </button>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 8: ATTENDANCE HISTORY -->
                <div x-show="activeTab === 'attendance'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Recent Attendance Logs (Last 30 Check-ins)</h3>
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="w-full text-left text-xs min-w-[420px]">
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
                <div x-show="activeTab === 'measurements'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-slate-200 dark:border-slate-800 pb-4">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                                <span>📏</span>
                                <span>Member Body Measurements</span>
                            </h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Track body composition and physical transformation over time</p>
                        </div>
                        <button type="button" 
                                @click="showAddMeasurementModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center justify-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Log New Measurement</span>
                        </button>
                    </div>

                    @if(empty($latestMeasurement))
                        <!-- Clean Empty State when no measurements logged -->
                        <div class="text-center py-10 sm:py-12 px-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
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
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
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

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3">
                                <!-- Metric 1: Weight -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">WEIGHT</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['weight'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">kg</span></p>
                                </div>

                                <!-- Metric 2: Body Fat -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">BODY FAT</span>
                                    <p class="text-base sm:text-lg font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $latestMeasurement['body_fat'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">%</span></p>
                                </div>

                                <!-- Metric 3: Chest -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">CHEST</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['chest'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 4: Shoulders -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">SHOULDERS</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['shoulders'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 5: Waist -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">WAIST</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['waist'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 6: Hips -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">HIPS</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['hips'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 7: Left Bicep -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">LEFT BICEP</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['bicep_left'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 8: Right Bicep -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">RIGHT BICEP</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['bicep_right'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 9: Left Thigh -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">LEFT THIGH</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['thigh_left'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 10: Right Thigh -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">RIGHT THIGH</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['thigh_right'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 11: Calves -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">CALVES</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['calves'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>

                                <!-- Metric 12: Neck -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center shadow-sm">
                                    <span class="text-[10px] font-extrabold text-slate-500 dark:text-slate-400 uppercase tracking-wider block">NECK</span>
                                    <p class="text-base sm:text-lg font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['neck'] ?? '—' }} <span class="text-xs font-normal text-slate-500 dark:text-slate-400">in</span></p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Measurement History Table -->
                    <div class="space-y-3 pt-2">
                        <h4 class="text-xs font-black text-slate-800 dark:text-slate-300 uppercase tracking-wider">Measurement History</h4>
                        
                        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[550px]">
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
                <div x-show="activeTab === 'audit_trail'" class="rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm p-4 sm:p-6 space-y-4 sm:space-y-6">
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
                            <div class="p-3.5 sm:p-4 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex items-start justify-between gap-3 sm:gap-4 shadow-sm">
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
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-bold text-slate-900 dark:text-white text-xs">{{ ucwords(str_replace('_', ' ', $log->action)) }}</span>
                                            <span class="text-[10px] font-medium text-slate-500 dark:text-slate-400">• By {{ $log->user->name ?? 'System' }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">{{ $log->description }}</p>
                                    </div>
                                </div>

                                <div class="text-right shrink-0">
                                    <span class="text-[11px] font-bold text-slate-700 dark:text-slate-300 block">{{ $log->created_at->format('d M Y') }}</span>
                                    <span class="text-[10px] font-mono text-slate-400 dark:text-slate-500 block">{{ $log->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 sm:p-8 text-center bg-slate-50 dark:bg-slate-950 rounded-2xl border border-slate-200 dark:border-slate-800 text-slate-500 dark:text-slate-400 text-xs">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddPaymentModal = false" class="w-full max-w-md rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-4 sm:p-6 space-y-4 text-slate-900 dark:text-slate-200 max-h-[90vh] overflow-y-auto">
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

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddPaymentModal = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black shadow-lg shadow-emerald-500/20 cursor-pointer text-center">Record Payment</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- MODAL 2: ADD SUBSCRIPTION -->
        <!-- ========================================================================= -->
        <div x-show="showAddSubscriptionModal" 
             x-transition 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddSubscriptionModal = false" class="w-full max-w-lg rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-4 sm:p-6 space-y-4 text-slate-900 dark:text-slate-200 max-h-[90vh] overflow-y-auto">
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-bold text-slate-700 dark:text-slate-300 mb-1">Discount ({{ $currency }})</label>
                            <input type="number" name="discount" x-model="newSubDiscount" min="0" placeholder="0" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddSubscriptionModal = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black shadow-lg shadow-indigo-600/30 cursor-pointer text-center">Assign Subscription</button>
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
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 dark:bg-black/80 backdrop-blur-md flex items-center justify-center p-3 sm:p-4" 
             x-cloak>
            <div @click.away="showEditModal = false" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-4xl w-full p-4 sm:p-8 shadow-2xl space-y-6 my-4 sm:my-8 max-h-[92vh] overflow-y-auto text-slate-900 dark:text-slate-200">
                
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
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-4 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showEditModal = false" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold transition-all cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-7 py-2.5 rounded-xl bg-gradient-to-r from-amber-500 to-amber-400 hover:from-amber-400 hover:to-amber-300 text-slate-950 font-black shadow-lg shadow-amber-500/20 transition-all flex items-center justify-center gap-2 cursor-pointer">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/70 dark:bg-black/85 backdrop-blur-md" 
             style="display: none;">
            <div @click.away="closeEditWebcam()" class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl max-w-lg w-full p-4 sm:p-6 shadow-2xl space-y-4 text-slate-900 dark:text-white max-h-[90vh] overflow-y-auto">
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

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                    <button type="button" @click="closeEditWebcam()" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:text-white dark:border-slate-700 text-xs font-bold transition-all text-center">
                        Cancel
                    </button>
                    <button type="button" @click="takeEditSnapshot()" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 transition-all cursor-pointer">
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
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-900/60 dark:bg-black/80 backdrop-blur-sm" 
             style="display: none;">
            <div @click.away="showAddMeasurementModal = false" class="w-full max-w-xl rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-2xl p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto text-slate-900 dark:text-slate-200">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white flex items-center gap-2">
                        <span>📏</span>
                        <span>Log Body Measurements</span>
                    </h3>
                    <button type="button" @click="showAddMeasurementModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.store-measurement', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
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

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddMeasurementModal = false" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:text-slate-300 font-bold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black shadow-lg shadow-indigo-600/30 cursor-pointer text-center">Save Measurements</button>
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
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 dark:bg-black/85 backdrop-blur-md p-2.5 sm:p-6" 
             style="display: none;">
            
            <div class="min-h-full flex items-center justify-center py-2 sm:py-6">
                <div @click.away="showInvoiceModal = false" class="w-full max-w-3xl space-y-2.5 sm:space-y-3">
                    
                    <!-- Modal Window Title Header -->
                    <div class="flex items-center justify-between px-3.5 sm:px-5 py-2.5 sm:py-3 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl">
                        <div class="flex items-center gap-2 text-slate-900 dark:text-white font-black text-xs sm:text-sm">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Tax Invoice &amp; Receipt Preview</span>
                        </div>

                        <div class="flex items-center gap-1.5 sm:gap-2">
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
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                        <button type="button" 
                                @click="showInvoiceModal = false" 
                                class="px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs shadow-sm transition-all cursor-pointer">
                            Close
                        </button>

                        <button type="button" 
                                onclick="window.print()" 
                                class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs flex items-center gap-1.5 shadow-lg shadow-emerald-500/20 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print</span>
                        </button>

                        <a :href="'/app/invoices/' + selectedInvoice.id" 
                           target="_blank" 
                           class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs flex items-center gap-1.5 shadow-lg shadow-indigo-600/30 transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download PDF</span>
                        </a>
                    </div>

                    <!-- Printable Invoice Sheet (A4 Proportion) -->
                    @php
                        $tenantObj = auth()->user()->tenant ?? ($tenant ?? null);
                        $tenantSettings = $tenantObj->settings ?? [];
                        $invSettings = $tenantSettings['invoice'] ?? [];
                        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenantObj->name ?? 'ARMOUR 24-7 GYM');
                        $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : ($member->branch->address ?? ($tenantObj->address ?? '6th Floor, Shalin Square, Nr Hathijan Circle, Ahmedabad - 382445'));
                        $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenantObj->phone ?? '+91 83065 30583');
                        $gymEmail = !empty($invSettings['email']) ? $invSettings['email'] : ($tenantObj->email ?? 'management@armour247gym.com');
                        $gymWeb = !empty($invSettings['website']) ? $invSettings['website'] : 'www.armour247gym.com';
                        $invoiceTitle = !empty($invSettings['title']) ? $invSettings['title'] : 'TAX INVOICE';
                        $statusText = !empty($invSettings['status_text']) ? $invSettings['status_text'] : 'PAID IN FULL';
                        $verificationText = !empty($invSettings['verification_text']) ? $invSettings['verification_text'] : 'Verified & Recorded';

                        // Terms
                        $defaultTerms = [
                            "Fees once paid are strictly non-refundable and non-transferable under any circumstances.",
                            "Membership is non-transferable and valid exclusively for the registered individual and specified tenure.",
                            "Members are required to carry clean training footwear, workout towel, and follow gym etiquette at all times.",
                            "Management reserves the right to adjust facility operating hours and enforce safety protocols.",
                            "Any unpaid dues must be cleared on or before the specified due date to maintain uninterrupted facility access."
                        ];
                        if (!empty($invSettings['terms'])) {
                            $rawTerms = array_filter(array_map('trim', explode("\n", $invSettings['terms'])));
                            $termsList = array_map(function($t) {
                                return preg_replace('/^\s*\d+[\.\)]\s*/', '', $t);
                            }, $rawTerms);
                        } else {
                            $termsList = $defaultTerms;
                        }

                        $thankYouText = !empty($invSettings['thank_you']) ? $invSettings['thank_you'] : ("Thank you for training with " . $gymName . "!");
                        $helpline = !empty($invSettings['helpline']) ? $invSettings['helpline'] : $gymPhone;
                        $footerEmail = !empty($invSettings['footer_email']) ? $invSettings['footer_email'] : $gymEmail;
                        $footerWeb = !empty($invSettings['footer_web']) ? $invSettings['footer_web'] : $gymWeb;
                        $tagline = !empty(trim($invSettings['tagline'] ?? '')) ? $invSettings['tagline'] : "BIGGER SPACE | BIGGER FACILITIES | STRONGER YOU";
                        $sealText = !empty($invSettings['seal_text']) ? $invSettings['seal_text'] : "Authorized Signature / Seal";
                        $sealUrl = $invSettings['seal_url'] ?? null;
                        $signatureUrl = $invSettings['signature_url'] ?? null;
                        $signatoryName = $invSettings['signatory_name'] ?? null;
                    @endphp

                    <div class="invoice-card relative w-full max-w-3xl bg-white text-slate-900 rounded-2xl shadow-xl p-4 sm:p-6 lg:p-7 border border-slate-200 flex flex-col justify-between min-h-0 sm:min-h-[1040px] print:min-h-[275mm] mx-auto">
                        
                        <!-- Faded Center Background Logo Watermark -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="" class="w-[320px] sm:w-[400px] h-[320px] sm:h-[400px] object-contain grayscale">
                            @else
                                <div class="text-center font-black tracking-widest text-6xl sm:text-8xl uppercase text-slate-900 rotate-[-12deg]">
                                    {{ $gymName }}
                                </div>
                            @endif
                        </div>

                        <div class="relative z-10 flex flex-col justify-between h-full min-h-[inherit] flex-1">
                            
                            <!-- MAIN TOP CONTENT: Sections 1 through 5 -->
                            <div class="space-y-3 sm:space-y-3.5">
                                <!-- 1. Header Section: Brand & Invoice Meta -->
                                <div class="flex flex-col sm:flex-row items-start justify-between gap-3 pt-1 border-b border-slate-200 pb-3">
                                    <div class="flex items-start gap-3.5 max-w-lg">
                                        @if(!empty($logoUrl))
                                            <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="h-14 w-14 sm:h-16 sm:w-16 object-contain rounded-xl bg-slate-950 p-1 sm:p-1.5 border border-slate-800 shadow-sm shrink-0">
                                        @else
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-xl bg-slate-950 text-white flex flex-col items-center justify-center font-black text-xs tracking-tight shadow-sm shrink-0 p-1 text-center">
                                                <span class="text-amber-400 text-xl">🏋️</span>
                                                <span class="text-[8px] sm:text-[9px] uppercase leading-none font-bold mt-0.5">GYM</span>
                                            </div>
                                        @endif
                                        <div>
                                            <h2 class="text-xl sm:text-2xl lg:text-3xl font-black text-slate-950 tracking-tight uppercase leading-tight">{{ $gymName }}</h2>
                                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-snug font-medium">{{ $gymAddress }}</p>
                                            <p class="text-xs sm:text-sm text-slate-600 mt-0.5 leading-snug font-medium">
                                                <span>Phone: <strong class="text-slate-800 font-semibold">{{ $gymPhone }}</strong></span>
                                                @if(!empty($gymEmail))
                                                    <span class="mx-1 text-slate-400">|</span>
                                                    <span>Email: <strong class="text-slate-800 font-semibold">{{ $gymEmail }}</strong></span>
                                                @endif
                                            </p>
                                            @if(!empty($gymWeb))
                                                <p class="text-xs sm:text-sm text-slate-600 leading-snug font-medium">
                                                    <span>Website: <strong class="text-slate-800 font-semibold">{{ $gymWeb }}</strong></span>
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto">
                                        <span class="inline-block px-5 py-1.5 rounded-xl bg-slate-950 text-white font-black text-xs sm:text-sm tracking-wider uppercase shadow-xs">
                                            {{ $invoiceTitle }}
                                        </span>
                                        <p class="text-xs sm:text-sm text-slate-600 mt-1.5 font-medium">Invoice No: <span class="text-slate-950 font-bold font-mono" x-text="selectedInvoice.receipt_no"></span></p>
                                        <p class="text-xs sm:text-sm text-slate-600 mt-0.5 font-medium" x-text="'Date: ' + selectedInvoice.date"></p>
                                        <span class="inline-block mt-1 px-3.5 py-0.5 rounded-full border border-emerald-400 bg-emerald-50/60 text-emerald-600 text-[10px] sm:text-xs font-bold uppercase tracking-wider">
                                            {{ $statusText }}
                                        </span>
                                    </div>
                                </div>

                                <!-- 2. Two Cards Row: Member & Plan Details -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <!-- Card 1: Member Details -->
                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                            <span>MEMBER DETAILS (BILLED TO)</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Full Name:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8 truncate">{{ $member->full_name }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Member ID:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->member_code }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Phone No:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8">{{ $member->phone }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Payment Mode:</span>
                                            <span class="font-bold uppercase text-slate-900 col-span-7 sm:col-span-8" x-text="selectedInvoice.method || 'UPI'"></span>
                                        </div>
                                    </div>

                                    <!-- Card 2: Membership & Plan Details -->
                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                            <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Package Duration:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8" x-text="selectedInvoice.plan_duration || '12 Months'"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Start Date:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8" x-text="selectedInvoice.start_date || selectedInvoice.date"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Expiry Date:</span>
                                            <span class="font-bold text-slate-900 col-span-7 sm:col-span-8" x-text="selectedInvoice.end_date || '-'"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-5 sm:col-span-4">Status:</span>
                                            <span class="font-bold text-emerald-600 col-span-7 sm:col-span-8">Active Membership</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Table -->
                                <div class="w-full overflow-x-auto">
                                    <table class="w-full min-w-[340px] text-left border-collapse">
                                        <thead>
                                            <tr class="bg-slate-950 text-white uppercase text-[11px] sm:text-xs font-black tracking-wider">
                                                <th class="py-2.5 px-3 rounded-l-lg w-8 sm:w-10 text-center">#</th>
                                                <th class="py-2.5 px-3">SERVICE / ITEM DESCRIPTION</th>
                                                <th class="py-2.5 px-3 text-center">DURATION</th>
                                                <th class="py-2.5 px-3 text-right">RATE (INR)</th>
                                                <th class="py-2.5 px-3 text-right rounded-r-lg">AMOUNT (INR)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-slate-100">
                                            <tr class="text-slate-900">
                                                <td class="py-3 px-3 text-center text-slate-500 font-medium text-xs">1</td>
                                                <td class="py-3 px-3">
                                                    <span class="font-bold text-slate-950 text-xs sm:text-sm block" x-text="selectedInvoice.plan_name"></span>
                                                </td>
                                                <td class="py-3 px-3 text-center font-medium text-slate-700 text-xs" x-text="selectedInvoice.plan_duration || '12 Months'"></td>
                                                <td class="py-3 px-3 text-right font-medium text-slate-700 text-xs">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                                <td class="py-3 px-3 text-right font-bold text-slate-950 text-xs sm:text-sm">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 4. Bottom Split: Verification & Summary -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                                        <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                                            PAYMENT DETAIL &amp; VERIFICATION
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Mode:</span>
                                            <span class="font-bold text-slate-900 col-span-6 sm:col-span-7 uppercase" x-text="selectedInvoice.method || 'UPI'"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Status:</span>
                                            <span class="font-bold text-emerald-600 col-span-6 sm:col-span-7">Completed</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-slate-500 font-normal col-span-6 sm:col-span-5">Payment Verification:</span>
                                            <span class="font-bold text-emerald-600 col-span-6 sm:col-span-7">{{ $verificationText }}</span>
                                        </div>
                                    </div>

                                    <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-600 font-normal">Subtotal</span>
                                            <span class="font-medium text-slate-900">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-500 font-normal">Taxes &amp; Gym Surcharge</span>
                                            <span class="font-normal text-slate-600">{{ $currency }}0 (Inclusive)</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-1 border-t border-slate-200">
                                            <span class="text-xs sm:text-sm font-bold text-slate-950">Total Amount</span>
                                            <span class="text-xs sm:text-sm font-bold text-slate-950">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-slate-600 font-normal">Amount Received</span>
                                            <span class="font-bold text-emerald-600">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="mt-1 p-2 rounded-lg bg-emerald-50/80 border border-emerald-200/70 flex items-center justify-between text-emerald-950">
                                            <span class="font-bold text-xs uppercase tracking-wider text-emerald-950">Balance Due</span>
                                            <span class="font-black text-xs sm:text-sm text-emerald-600">{{ $currency }}0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Terms & Conditions -->
                                <div class="p-3 sm:p-3.5 rounded-2xl bg-slate-50/90 border border-slate-100/90 text-xs text-slate-700 leading-snug space-y-1">
                                    <div class="text-xs font-black text-slate-900 uppercase tracking-wider mb-1">
                                        TERMS &amp; CONDITIONS
                                    </div>
                                    <ol class="list-decimal pl-4 space-y-0.5 text-[10.5px] sm:text-[11px] text-slate-600 leading-relaxed font-normal">
                                        @foreach($termsList as $term)
                                            <li>{{ $term }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>

                            <!-- FOOTER: Sections 6 & 7 (Anchored at the bottom with generous whitespace after Terms) -->
                            <div class="mt-auto pt-6 sm:pt-8 space-y-2.5 sm:space-y-3">
                                <!-- 6. Footer Helpline & Seal -->
                                <div class="pt-2.5 sm:pt-3 border-t border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3">
                                    <div class="text-left space-y-0.5 sm:space-y-1">
                                        <p class="font-bold text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                                        <div class="text-[10px] sm:text-xs text-slate-600 flex flex-wrap items-center gap-x-2 gap-y-0.5 font-normal mt-0.5">
                                            @if(!empty($helpline))<span>Official Helpline: <strong class="text-slate-800 font-medium">{{ $helpline }}</strong></span>@endif
                                            @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                                            @if(!empty($footerEmail))<span>Email: <strong class="text-slate-800 font-medium">{{ $footerEmail }}</strong></span>@endif
                                            @if(!empty($footerWeb))<span>|</span><span>Web: <strong class="text-slate-800 font-medium">{{ $footerWeb }}</strong></span>@endif
                                        </div>
                                    </div>

                                    <!-- Right: Official Seal / Stamp & Signature -->
                                    <div class="flex flex-col items-center justify-center shrink-0 min-w-[140px] sm:min-w-[160px] text-center">
                                        <div class="relative flex items-center justify-center w-24 h-18 sm:w-28 sm:h-20 select-none mx-auto">
                                            @if(!empty($sealUrl))
                                                <img src="{{ $sealUrl }}" alt="Stamp" class="h-14 w-14 sm:h-16 sm:w-16 object-contain drop-shadow-xs">
                                            @else
                                                <svg class="w-14 h-14 sm:w-16 sm:h-16 text-slate-700 drop-shadow-xs" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <circle cx="70" cy="70" r="65" stroke="currentColor" stroke-width="2.5" />
                                                    <circle cx="70" cy="70" r="58" stroke="currentColor" stroke-width="1.2" stroke-dasharray="3 2" />
                                                    <path id="mem-seal-top" d="M 24 70 A 46 46 0 0 1 116 70" fill="none" />
                                                    <text font-size="10.5" font-weight="900" fill="currentColor" letter-spacing="1.5">
                                                        <textPath href="#mem-seal-top" startOffset="50%" text-anchor="middle">
                                                            {{ strtoupper(substr($gymName, 0, 16)) }}
                                                        </textPath>
                                                    </text>
                                                    <path id="mem-seal-bottom" d="M 116 70 A 46 46 0 0 1 24 70" fill="none" />
                                                    <text font-size="9" font-weight="800" fill="currentColor" letter-spacing="1.5">
                                                        <textPath href="#mem-seal-bottom" startOffset="50%" text-anchor="middle">
                                                            ★ VERIFIED ★
                                                        </textPath>
                                                    </text>
                                                    <line x1="32" y1="58" x2="108" y2="58" stroke="currentColor" stroke-width="1.5" />
                                                    <text x="70" y="72" font-size="9.5" font-weight="900" fill="currentColor" text-anchor="middle" letter-spacing="1.5">
                                                        AHMEDABAD
                                                    </text>
                                                    <line x1="32" y1="78" x2="108" y2="78" stroke="currentColor" stroke-width="1.5" />
                                                </svg>
                                            @endif

                                            @if(!empty($signatureUrl))
                                                <img src="{{ $signatureUrl }}" alt="Signature" 
                                                     class="absolute inset-0 m-auto h-12 w-24 sm:h-14 sm:w-28 object-contain mix-blend-multiply rotate-[-6deg] drop-shadow-xs pointer-events-none z-10">
                                            @endif
                                        </div>
                                        <span class="text-[10px] sm:text-xs font-semibold text-slate-600 mt-1 text-center block w-full">{{ $sealText }}</span>
                                        @if(!empty($signatoryName))
                                            <span class="text-[9px] sm:text-[10px] font-semibold text-slate-500 text-center block w-full">{{ $signatoryName }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- 7. Tagline -->
                                <div class="pt-2 sm:pt-2.5 border-t border-slate-200 text-center">
                                    <p class="text-[11px] sm:text-xs font-black tracking-widest text-slate-950 uppercase">{{ !empty(trim($tagline)) ? $tagline : 'BIGGER SPACE | BIGGER FACILITIES | STRONGER YOU' }}</p>
                                </div>
                            </div>

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
                <div @click.away="showAddPtModal = false" class="w-full max-w-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl shadow-2xl p-4 sm:p-8 space-y-6 text-xs text-slate-900 dark:text-slate-200 relative max-h-[92vh] overflow-y-auto">
                    
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

        <!-- ENROLL IN CLASS MODAL -->
        <div x-show="showEnrollClassModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div @click.away="showEnrollClassModal = false" 
                 class="w-full max-w-lg rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-5 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                            🧘
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Enroll in Group / Studio Class</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Enroll {{ $member->full_name }} into a class schedule</p>
                        </div>
                    </div>
                    <button type="button" @click="showEnrollClassModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.members.enroll-class', $member->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Class / Schedule Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Select Class & Schedule *</label>
                        <select name="class_schedule_id" 
                                x-model="selectedScheduleId" 
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                            <option value="">-- Choose Class Schedule --</option>
                            @if(isset($availableClassSchedules) && $availableClassSchedules->isNotEmpty())
                                @foreach($availableClassSchedules as $sch)
                                    <option value="{{ $sch->id }}">
                                        {{ $sch->gymClass->name }} — {{ ucfirst($sch->day_of_week) }} ({{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('g:i A') }}) [{{ $sch->gymClass->class_type }}]
                                    </option>
                                @endforeach
                            @endif
                            @if(isset($allGymClasses))
                                @foreach($allGymClasses as $gc)
                                    @if($gc->schedules->isEmpty())
                                        <option value="class_{{ $gc->id }}">
                                            {{ $gc->name }} (General / All Days) [{{ $gc->class_type }}]
                                        </option>
                                    @endif
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Booking / Enrollment Date -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Enrollment / Start Date</label>
                        <input type="date" 
                               name="booking_date" 
                               x-model="enrollBookingDate" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                    </div>

                    <!-- Initial Status -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Enrollment Status</label>
                        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                            <option value="BOOKED">Booked / Enrolled (Active)</option>
                            <option value="ATTENDED">Attended</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" 
                                @click="showEnrollClassModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Enrollment</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- BOOK SERVICE MODAL -->
        <div x-show="showBookServiceModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-3 sm:p-4">
            <div @click.away="showBookServiceModal = false" 
                 class="w-full max-w-lg rounded-2xl sm:rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 space-y-5 shadow-2xl relative max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                            ✨
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Book Service for {{ $member->full_name }}</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Enroll member into an add-on gym service or assign a locker</p>
                        </div>
                    </div>
                    <button type="button" @click="showBookServiceModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.services.bookings.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">
                    
                    <!-- Service Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Select Service / Amenity *</label>
                        <select name="gym_service_id" 
                                x-model="selectedServiceId" 
                                @change="updateServiceSelection($event)"
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                            <option value="">-- Choose Service --</option>
                            @if(isset($availableServices) && $availableServices->isNotEmpty())
                                @foreach($availableServices as $s)
                                    <option value="{{ $s->id }}" 
                                            data-price="{{ $s->amount }}"
                                            data-locker="{{ $s->is_locker_service ? '1' : '0' }}"
                                            data-countable="{{ $s->is_session_countable ? '1' : '0' }}"
                                            data-sessions="{{ $s->session_count }}">
                                        {{ $s->name }} ({{ $currency }}{{ number_format($s->amount, 0) }} - {{ $s->is_session_countable ? $s->session_count . ' sessions' : 'single' }})
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Booking Date & Time -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Booking Date *</label>
                            <input type="date" 
                                   name="booking_date" 
                                   x-model="serviceBookingDate" 
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Time (Optional)</label>
                            <input type="time" 
                                   name="booking_time" 
                                   x-model="serviceBookingTime" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <!-- Amount Paid & Locker Number -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Amount Paid ({{ $currency }})</label>
                            <input type="number" 
                                   step="1" 
                                   min="0"
                                   name="amount_paid" 
                                   x-model="serviceAmountPaid" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono font-bold text-xs focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Locker Number (If Applicable)</label>
                            <input type="text" 
                                   name="locker_number" 
                                   x-model="serviceLockerNumber" 
                                   placeholder="e.g. L-102"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Notes / Special Instructions</label>
                        <textarea name="notes" 
                                  x-model="serviceNotes" 
                                  rows="2" 
                                  placeholder="Optional booking notes..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" 
                                @click="showBookServiceModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Confirm Service Booking</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CREATE / ASSIGN WORKOUT ROUTINE MODAL      -->
        <!-- ========================================== -->
        <div x-show="showAddWorkoutModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto">
            <div @click.away="showAddWorkoutModal = false" 
                 class="w-full max-w-3xl my-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-bold border border-indigo-200 dark:border-indigo-500/30">
                            🏋️
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Assign Workout Routine</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Custom workout split and exercises for <strong class="text-slate-800 dark:text-slate-200">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddWorkoutModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.workouts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">

                    <!-- Quick Preset Split Buttons -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">⚡ Quick Split Presets:</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="loadWorkoutPreset('push')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Push Day
                            </button>
                            <button type="button" @click="loadWorkoutPreset('pull')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Pull Day
                            </button>
                            <button type="button" @click="loadWorkoutPreset('legs')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Leg Day
                            </button>
                        </div>
                    </div>

                    <!-- Title & Goal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Routine Title *</label>
                            <input type="text" 
                                   name="title" 
                                   x-model="workoutTitle" 
                                   required 
                                   placeholder="e.g. 4-Day Hypertrophy Split" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Primary Goal</label>
                            <select name="goal" 
                                    x-model="workoutGoal" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                                <option value="General Fitness">General Fitness</option>
                                <option value="Hypertrophy">Hypertrophy / Muscle Building</option>
                                <option value="Strength">Strength & Power</option>
                                <option value="Fat Loss">Fat Loss & Conditioning</option>
                                <option value="Endurance">Endurance & Stamina</option>
                                <option value="Mobility">Mobility & Posture</option>
                            </select>
                        </div>
                    </div>

                    <!-- Level & Coach -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Experience Level</label>
                            <select name="level" 
                                    x-model="workoutLevel" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assigned Trainer / Coach</label>
                            <select name="trainer_id" 
                                    x-model="workoutTrainerId" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                                <option value="">-- No Specific Coach --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Start & End Date -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   x-model="workoutStartDate" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">End Date (Optional)</label>
                            <input type="date" 
                                   name="end_date" 
                                   x-model="workoutEndDate" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <!-- Prescribed Exercises Table -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Prescribed Exercises List</label>
                            <button type="button" 
                                    @click="addWorkoutExercise()" 
                                    class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Exercise</span>
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(ex, idx) in workoutExercises" :key="idx">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 relative">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-3">
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][day]'" 
                                                   x-model="ex.day" 
                                                   placeholder="Day (e.g. Day 1)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div class="sm:col-span-9 flex items-center gap-2">
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][exercise_name]'" 
                                                   x-model="ex.exercise_name" 
                                                   required 
                                                   placeholder="Exercise Name (e.g. Barbell Bench Press)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                            <button type="button" 
                                                    @click="removeWorkoutExercise(idx)" 
                                                    class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 transition-colors shrink-0 cursor-pointer" 
                                                    title="Remove Exercise">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Sets</label>
                                            <input type="number" 
                                                   :name="'exercises[' + idx + '][sets]'" 
                                                   x-model="ex.sets" 
                                                   min="1" 
                                                   class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Reps</label>
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][reps]'" 
                                                   x-model="ex.reps" 
                                                   placeholder="e.g. 8-10" 
                                                   class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Weight / Load</label>
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][weight]'" 
                                                   x-model="ex.weight" 
                                                   placeholder="e.g. 50 kg" 
                                                   class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Rest (Sec)</label>
                                            <input type="number" 
                                                   :name="'exercises[' + idx + '][rest_seconds]'" 
                                                   x-model="ex.rest_seconds" 
                                                   placeholder="60" 
                                                   class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                    </div>

                                    <div>
                                        <input type="text" 
                                               :name="'exercises[' + idx + '][notes]'" 
                                               x-model="ex.notes" 
                                               placeholder="Execution cues / tempo (optional)..." 
                                               class="w-full px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-indigo-500">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Coach Notes & Warmup Instructions</label>
                        <textarea name="notes" 
                                  x-model="workoutNotes" 
                                  rows="2" 
                                  placeholder="Warm-up thoroughly, focus on progressive overload, drink enough water..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" 
                                @click="showAddWorkoutModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save & Assign Routine</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- CREATE / ASSIGN DIET PLAN MODAL            -->
        <!-- ========================================== -->
        <div x-show="showAddDietModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto">
            <div @click.away="showAddDietModal = false" 
                 class="w-full max-w-3xl my-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-4 sm:p-6 shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-bold border border-emerald-200 dark:border-emerald-500/30">
                            🥗
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Assign Diet & Nutrition Chart</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Meal schedule & macros for <strong class="text-slate-800 dark:text-slate-200">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddDietModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.diets.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">

                    <!-- Title & Calories -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Diet Title *</label>
                            <input type="text" 
                                   name="title" 
                                   x-model="dietTitle" 
                                   required 
                                   placeholder="e.g. Lean Muscle Hypertrophy Diet" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Calories (kcal)</label>
                            <input type="number" 
                                   step="50" 
                                   name="daily_calories" 
                                   x-model="dietCalories" 
                                   placeholder="2000" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <!-- Macro Splits -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 space-y-2">
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">Target Daily Macronutrients</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase">Protein (g)</label>
                                <input type="number" 
                                       name="protein_grams" 
                                       x-model="dietProtein" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-rose-600 dark:text-rose-400 focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase">Carbs (g)</label>
                                <input type="number" 
                                       name="carbs_grams" 
                                       x-model="dietCarbs" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-amber-600 dark:text-amber-400 focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-cyan-600 dark:text-cyan-400 uppercase">Fat (g)</label>
                                <input type="number" 
                                       name="fat_grams" 
                                       x-model="dietFat" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-cyan-600 dark:text-cyan-400 focus:outline-none focus:border-indigo-500">
                            </div>
                        </div>
                    </div>

                    <!-- Coach & Dates -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Nutritionist / Trainer</label>
                            <select name="trainer_id" 
                                    x-model="dietTrainerId" 
                                    class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                                <option value="">-- None / Self --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   x-model="dietStartDate" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">End Date (Optional)</label>
                            <input type="date" 
                                   name="end_date" 
                                   x-model="dietEndDate" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                    </div>

                    <!-- Scheduled Meals List -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">Scheduled Meals & Portions</label>
                            <button type="button" 
                                    @click="addDietMeal()" 
                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white text-xs font-bold transition-all flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Meal</span>
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(meal, idx) in dietMeals" :key="idx">
                                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 relative">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-4">
                                            <select :name="'meals[' + idx + '][meal_type]'" 
                                                    x-model="meal.meal_type" 
                                                    class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                                <option value="breakfast">Breakfast</option>
                                                <option value="morning_snack">Mid-Morning Snack</option>
                                                <option value="lunch">Lunch</option>
                                                <option value="evening_snack">Evening Snack</option>
                                                <option value="post_workout">Post-Workout Fuel</option>
                                                <option value="dinner">Dinner</option>
                                            </select>
                                        </div>
                                        <div class="sm:col-span-3">
                                            <input type="time" 
                                                   :name="'meals[' + idx + '][recommended_time]'" 
                                                   x-model="meal.recommended_time" 
                                                   placeholder="Time" 
                                                   class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div class="sm:col-span-5 flex items-center gap-2">
                                            <input type="text" 
                                                   :name="'meals[' + idx + '][meal_name]'" 
                                                   x-model="meal.meal_name" 
                                                   required 
                                                   placeholder="Meal Title (e.g. Oats & Eggs)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                            <button type="button" 
                                                    @click="removeDietMeal(idx)" 
                                                    class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 transition-colors shrink-0 cursor-pointer" 
                                                    title="Remove Meal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-9">
                                            <input type="text" 
                                                   :name="'meals[' + idx + '][items_description]'" 
                                                   x-model="meal.items_description" 
                                                   placeholder="Food items & quantities (e.g. 3 Boiled Eggs + 2 Brown Bread + 1 Apple)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-indigo-500">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <input type="number" 
                                                   :name="'meals[' + idx + '][calories]'" 
                                                   x-model="meal.calories" 
                                                   placeholder="Calories (kcal)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-mono font-bold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-indigo-500">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Hydration & Dietary Advice</label>
                        <textarea name="guidelines" 
                                  x-model="dietGuidelines" 
                                  rows="2" 
                                  placeholder="Drink 3-4 liters of water daily, avoid added refined sugar..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-600 text-xs focus:border-indigo-500 focus:outline-none shadow-sm"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" 
                                @click="showAddDietModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-black text-xs shadow-lg shadow-emerald-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Save & Assign Diet Plan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================== -->
        <!-- AI DIET GENERATOR MODAL                    -->
        <!-- ========================================== -->
        <div x-show="showAiDietModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-4 bg-slate-950/80 backdrop-blur-xs overflow-y-auto">
            <div @click.away="showAiDietModal = false" 
                 class="w-full max-w-lg my-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-2xl space-y-4">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-purple-500 to-indigo-600 text-white flex items-center justify-center text-lg font-bold shadow-md shadow-indigo-500/20">
                            ✨
                        </div>
                        <div>
                            <h3 class="font-black text-slate-900 dark:text-white text-base">Instant AI Diet Generator</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Scientific nutrition plan for <strong class="text-slate-800 dark:text-slate-200">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAiDietModal = false" class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Error Alert -->
                <div x-show="aiDietError" x-cloak class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold" x-text="aiDietError"></div>

                <div class="space-y-3.5">
                    <!-- Goal -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Primary Fitness Goal</label>
                        <select x-model="aiDietGoal" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                            <option value="weight_loss">Fat Loss & Calorie Deficit</option>
                            <option value="muscle_gain">Lean Muscle Hypertrophy & Bulking</option>
                            <option value="maintenance">Body Recomposition & Maintenance</option>
                            <option value="endurance">Cardio & Athletic Endurance</option>
                        </select>
                    </div>

                    <!-- Diet Preference -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dietary Preference</label>
                        <select x-model="aiDietPreference" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                            <option value="vegetarian">Vegetarian (Indian / Standard)</option>
                            <option value="non_vegetarian">High-Protein Non-Vegetarian (Chicken/Eggs/Fish)</option>
                            <option value="eggetarian">Eggetarian</option>
                            <option value="vegan">100% Plant-Based Vegan</option>
                            <option value="keto">Keto (Low Carb / High Fat)</option>
                        </select>
                    </div>

                    <!-- Daily Calories -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Daily Target (kcal)</label>
                            <input type="number" 
                                   step="50" 
                                   x-model="aiDietCalories" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-mono font-bold focus:border-indigo-500 focus:outline-none shadow-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Meals Per Day</label>
                            <select x-model="aiDietMealsPerDay" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-semibold focus:border-indigo-500 focus:outline-none shadow-sm">
                                <option value="3">3 Meals (Breakfast, Lunch, Dinner)</option>
                                <option value="4" selected>4 Meals (+ Evening Snack)</option>
                                <option value="5">5 Meals (+ Pre/Post Workout)</option>
                                <option value="6">6 Small Frequent Meals</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" 
                            @click="showAiDietModal = false" 
                            :disabled="aiDietLoading"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 dark:border-slate-700 font-bold text-xs transition-colors cursor-pointer text-center">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="generateAiDiet()" 
                            :disabled="aiDietLoading"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-500 hover:to-indigo-500 text-white font-black text-xs shadow-lg shadow-indigo-600/20 transition-all cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50">
                        <template x-if="aiDietLoading">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </template>
                        <template x-if="!aiDietLoading">
                            <span>✨</span>
                        </template>
                        <span x-text="aiDietLoading ? 'Generating AI Diet...' : 'Generate & Assign Plan'"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>