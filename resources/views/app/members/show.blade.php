<x-app-layout header="Member Profile">
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

        $finalAmt = (float) ($activeMembership?->final_amount ?? 0);
        $paidAmt = (float) ($activeMembership?->paid_amount ?? 0);
        $remainingAmt = max(0, $finalAmt - $paidAmt);

        $measurementsList = $member->metadata['measurements'] ?? [];
        if (is_array($measurementsList)) {
            usort($measurementsList, function ($a, $b) {
                return strcmp($b['date'] ?? '', $a['date'] ?? '');
            });
        } else {
            $measurementsList = [];
        }
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
        showCancelMembershipModal: false,
        cancelSubId: null,
        cancelSubPlanName: '',
        openCancelMembershipModal(subId, planName) {
            this.cancelSubId = subId;
            this.cancelSubPlanName = planName;
            this.showCancelMembershipModal = true;
        },
        showAddPtModal: false,
        showEditModal: {{ request()->boolean('edit') ? 'true' : 'false' }},
        editPlanId: '{{ $activeMembership?->membership_plan_id ?? '' }}',
        editStartDate: '{{ $activeMembership?->start_date ? $activeMembership->start_date->format('Y-m-d') : '' }}',
        editEndDate: '{{ $activeMembership?->end_date ? $activeMembership->end_date->format('Y-m-d') : '' }}',
        onEditPlanChange() {
            if (!this.editPlanId) return;
            const plans = window.__MEMBERSHIP_PLANS__ || {};
            const plan = plans[this.editPlanId];
            if (!plan) return;

            if (!this.editStartDate) {
                this.editStartDate = new Date().toISOString().split('T')[0];
            }

            const start = new Date(this.editStartDate);
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

            this.editEndDate = end.toISOString().split('T')[0];
        },
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
            { meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Power Breakfast', items_description: '3 Whole Eggs / Tofu Scramble + 2 Multigrain Toast + 1 Banana', calories: 450, protein_grams: 30, carbs_grams: 45, fat_grams: 15 },
            { meal_type: 'lunch', recommended_time: '13:30', meal_name: 'High Protein Lunch', items_description: '150g Grilled Chicken / Paneer + Brown Rice + Green Salad', calories: 650, protein_grams: 45, carbs_grams: 70, fat_grams: 18 },
            { meal_type: 'evening_snack', recommended_time: '17:30', meal_name: 'Pre-Workout Fuel', items_description: '1 Scoop Whey Protein + 1 Apple + Handful of Almonds', calories: 300, protein_grams: 28, carbs_grams: 30, fat_grams: 8 },
            { meal_type: 'dinner', recommended_time: '20:30', meal_name: 'Clean Dinner', items_description: 'Steamed Veggies + Yellow Dal + 2 Chapatis', calories: 500, protein_grams: 25, carbs_grams: 65, fat_grams: 12 }
        ],
        addDietMeal() {
            this.dietMeals.push({ meal_type: 'lunch', recommended_time: '13:00', meal_name: 'Meal', items_description: '', calories: 500, protein_grams: 30, carbs_grams: 55, fat_grams: 15 });
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
                { meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Power Breakfast', items_description: '3 Whole Eggs / Tofu Scramble + 2 Multigrain Toast + 1 Banana', calories: 450, protein_grams: 30, carbs_grams: 45, fat_grams: 15 },
                { meal_type: 'lunch', recommended_time: '13:30', meal_name: 'High Protein Lunch', items_description: '150g Grilled Chicken / Paneer + Brown Rice + Green Salad', calories: 650, protein_grams: 45, carbs_grams: 70, fat_grams: 18 },
                { meal_type: 'evening_snack', recommended_time: '17:30', meal_name: 'Pre-Workout Fuel', items_description: '1 Scoop Whey Protein + 1 Apple + Handful of Almonds', calories: 300, protein_grams: 28, carbs_grams: 30, fat_grams: 8 },
                { meal_type: 'dinner', recommended_time: '20:30', meal_name: 'Clean Dinner', items_description: 'Steamed Veggies + Yellow Dal + 2 Chapatis', calories: 500, protein_grams: 25, carbs_grams: 65, fat_grams: 12 }
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
                    calories: m.calories || 0,
                    protein_grams: m.protein_grams || 0,
                    carbs_grams: m.carbs_grams || 0,
                    fat_grams: m.fat_grams || 0
                }));
            } else {
                this.dietMeals = [{ meal_type: 'breakfast', recommended_time: '08:00', meal_name: 'Breakfast', items_description: '', calories: 400, protein_grams: 25, carbs_grams: 40, fat_grams: 12 }];
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
        getInvoiceModeText(inv) {
            if (!inv) return 'Cash';
            const notes = inv.notes || '';
            const isSplit = (inv.method && inv.method.toLowerCase() === 'split') || notes.includes('Split Payment') || notes.includes('Split:');
            if (!isSplit) {
                const m = (inv.method || 'Cash').toUpperCase();
                return m === 'UPI' ? 'UPI' : (m === 'CASH' ? 'Cash' : (m === 'CARD' ? 'Card' : (m === 'CHEQUE' ? 'Cheque' : (m === 'BANK_TRANSFER' ? 'Bank Transfer' : inv.method))));
            }
            let raw = notes;
            if (raw.includes('Split Payment:')) raw = raw.split('Split Payment:').pop();
            else if (raw.includes('Split:')) raw = raw.split('Split:').pop();
            if (raw.includes('|')) raw = raw.split('|')[0];
            const parts = raw.split('+');
            const methods = [];
            parts.forEach(p => {
                p = p.trim();
                const match = p.match(/^([A-Za-z\s_]+)/);
                if (match) {
                    let m = match[1].trim().toUpperCase();
                    methods.push(m === 'UPI' ? 'UPI' : (m === 'CASH' ? 'Cash' : (m === 'CARD' ? 'Card' : (m === 'CHEQUE' ? 'Cheque' : (m === 'BANK_TRANSFER' ? 'Bank Transfer' : match[1].trim())))));
                }
            });
            return methods.length >= 2 ? (methods[0] + ' & ' + methods[1]) : (methods[0] || 'Cash & UPI');
        },
        getInvoiceSplitItems(inv) {
            if (!inv) return [];
            const notes = inv.notes || '';
            const isSplit = (inv.method && inv.method.toLowerCase() === 'split') || notes.includes('Split Payment') || notes.includes('Split:');
            if (!isSplit) return [];
            let raw = notes;
            if (raw.includes('Split Payment:')) raw = raw.split('Split Payment:').pop();
            else if (raw.includes('Split:')) raw = raw.split('Split:').pop();
            if (raw.includes('|')) raw = raw.split('|')[0];
            const parts = raw.split('+');
            const items = [];
            parts.forEach(p => {
                p = p.trim();
                if (!p) return;
                const m = p.match(/^([A-Za-z\s_]+?)\s*([^\d\s]*\s*[\d,]+(?:\.\d+)?)\s*(\(.*?\))?$/);
                if (m) {
                    let name = m[1].trim().toUpperCase();
                    name = name === 'UPI' ? 'UPI' : (name === 'CASH' ? 'Cash' : (name === 'CARD' ? 'Card' : (name === 'CHEQUE' ? 'Cheque' : (name === 'BANK_TRANSFER' ? 'Bank Transfer' : m[1].trim()))));
                    items.push({ method: name, amount: m[2].trim(), ref: (m[3] || '').trim() });
                }
            });
            return items;
        },
        printInvoice(id) {
            const invoiceId = id || this.selectedInvoice.id;
            if (!invoiceId) return;
            let frame = document.getElementById('tax-invoice-silent-frame');
            if (!frame) {
                frame = document.createElement('iframe');
                frame.id = 'tax-invoice-silent-frame';
                frame.style.position = 'fixed';
                frame.style.right = '0';
                frame.style.bottom = '0';
                frame.style.width = '0';
                frame.style.height = '0';
                frame.style.border = '0';
                frame.style.visibility = 'hidden';
                document.body.appendChild(frame);
            }
            frame.src = '/app/invoices/' + invoiceId + '?print=1';
        },

        // Edit Member Photo & Webcam State
        editPhotoPreview: '{{ $member->avatar_url ?? "" }}',
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
        paymentNotes: '',

        // Split Payment State
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
                const total = Number(this.paymentAmount || 0);
                const half = Math.round((total / 2) * 100) / 100;
                this.splitAmount1 = half > 0 ? half : '';
                this.splitAmount2 = (total - half) > 0 ? (total - half) : '';
                this.splitMethod1 = this.splitMethod1 || 'cash';
                this.splitMethod2 = this.splitMethod2 || 'upi';
                this.updateSplitFromFirst();
            }
        },

        updateSplitFromFirst() {
            const total = Number(this.paymentAmount || 0);
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
            this.paymentAmount = Math.round((a1 + a2) * 100) / 100;
        },

        portalLinkLoading: false,
        generatePortalLink() {
            this.portalLinkLoading = true;
            fetch('{{ route('app.members.generate-portal-link', $member->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.portalLinkLoading = false;
                if (data.success && data.whatsapp_url) {
                    navigator.clipboard.writeText(data.setup_url).catch(() => {});
                    window.open(data.whatsapp_url, '_blank');
                } else {
                    alert(data.message || 'Error generating link');
                }
            })
            .catch(() => {
                this.portalLinkLoading = false;
                alert('Network error generating link');
            });
        },

        // Member Direct Password Modal State & Methods
        showPasswordModal: false,
        passwordInput: '',
        showPasswordText: false,
        passwordSendWhatsapp: true,
        passwordSubmitting: false,
        passwordSuccessMsg: '',
        passwordErrorMsg: '',
        submitMemberPassword() {
            if (!this.passwordInput || this.passwordInput.length < 4) {
                this.passwordErrorMsg = 'Password must be at least 4 characters long.';
                return;
            }
            this.passwordSubmitting = true;
            this.passwordErrorMsg = '';
            this.passwordSuccessMsg = '';
            fetch('{{ route('app.members.update-password', $member->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    password: this.passwordInput,
                    send_whatsapp: this.passwordSendWhatsapp
                })
            })
            .then(res => res.json())
            .then(data => {
                this.passwordSubmitting = false;
                if (data.success) {
                    this.passwordSuccessMsg = data.message;
                    if (this.passwordSendWhatsapp && data.whatsapp_url) {
                        setTimeout(() => {
                            window.open(data.whatsapp_url, '_blank');
                        }, 500);
                    }
                } else {
                    this.passwordErrorMsg = data.message || 'Error updating password';
                }
            })
            .catch(() => {
                this.passwordSubmitting = false;
                this.passwordErrorMsg = 'Network error updating password';
            });
        }
    }" class="space-y-4 sm:space-y-6 pb-32 sm:pb-16 max-w-7xl mx-auto">

        <!-- Top Bar Navigation -->
        <div class="flex items-center justify-between gap-2 sm:gap-3 min-w-0">
            <a href="{{ route('app.members.index') }}" 
               class="shrink-0 whitespace-nowrap px-2.5 sm:px-4 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted hover:text-fg dark:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised text-xs font-semibold flex items-center gap-2 transition-all">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                <span>Back to Members</span>
            </a>

            <div class="flex min-w-0 items-center justify-end gap-1.5 sm:gap-2 text-right text-xs text-fg-muted dark:text-fg-muted font-semibold">
                <span class="w-2 h-2 rounded-full shrink-0 {{ $member->status === 'ACTIVE' ? 'bg-emerald-400' : ($member->status === 'SUSPENDED' ? 'bg-rose-400' : 'bg-orange-400') }}"></span>
                <span class="min-w-0 break-words">Branch: <strong class="text-fg dark:text-white">{{ $member->branch->name ?? 'Main Branch' }}</strong></span>
            </div>
        </div>

        <!-- Feedback Alerts -->
        @if (session('success'))
            <div class="p-3.5 sm:p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span class="leading-relaxed">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('agreement_share_url'))
            <div class="p-4 sm:p-5 rounded-xl bg-indigo-50 dark:bg-indigo-950/50 border-2 border-indigo-500/50 text-fg dark:text-white flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-semibold text-fg dark:text-white flex items-center gap-2">
                            <span>Digital Member Agreement & Health Declaration Link Ready</span>
                            <span class="text-xs bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 font-semibold px-2 py-0.5 rounded-full">Secure 256-Bit</span>
                        </h4>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Share this direct link with {{ session('agreement_member_name', $member->full_name) }} to complete their health declaration and digital signing.</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 w-full md:w-auto shrink-0">
                    <button type="button" @click="navigator.clipboard.writeText('{{ session('agreement_share_url') }}'); alert('Agreement link copied to clipboard!');" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line hover:bg-raised text-fg dark:text-fg font-semibold text-xs cursor-pointer transition-colors">
                        <svg class="w-4 h-4 text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy Link</span>
                    </button>
                    @if(session('agreement_whatsapp_url'))
                    <a href="{{ session('agreement_whatsapp_url') }}" target="_blank" class="flex-1 md:flex-initial inline-flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition-all hover:scale-[1.02]">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Share on WhatsApp</span>
                    </a>
                    @endif
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-3.5 sm:p-4 rounded-xl bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0 text-rose-600 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="leading-relaxed">{{ session('error') }}</span>
            </div>
        @endif

        <!-- Top Header Profile Banner -->
        <div class="p-3.5 sm:p-6 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 sm:gap-6">
            <!-- Left Info Block -->
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4 w-full lg:w-auto">
                <div class="min-w-0 w-full sm:w-auto">
                    <div class="flex flex-wrap items-center gap-2 sm:gap-2.5">
                        <h1 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight truncate">{{ $member->full_name }}</h1>
                        <span class="px-2 py-0.5 rounded-md bg-raised dark:bg-raised border border-line dark:border-line text-amber-600 dark:text-amber-400 text-xs font-mono font-semibold shrink-0">
                            {{ $member->member_code }}
                        </span>
                        @if($member->lead_id)
                            <a href="{{ route('app.leads.edit', $member->lead_id) }}" title="Converted from CRM Lead #{{ $member->lead_id }}" class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 text-xs font-semibold shrink-0 hover:bg-indigo-100 dark:hover:bg-indigo-500/25 transition-all">
                                CRM Lead #{{ $member->lead_id }} ↗
                            </a>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-1.5 sm:gap-2 mt-2 sm:mt-2.5">
                        <!-- Status Badge -->
                        @if($member->status === 'ACTIVE')
                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                <span>Active</span>
                            </span>
                        @elseif($member->status === 'SUSPENDED')
                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-semibold text-xs flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 dark:bg-rose-400"></span>
                                <span>Frozen</span>
                            </span>
                        @else
                            <span class="px-2.5 py-1 rounded-full bg-orange-50 dark:bg-orange-500/15 border border-orange-200 dark:border-orange-500/30 text-orange-700 dark:text-orange-400 font-semibold text-xs flex items-center gap-1.5 shrink-0">
                                <span class="w-1.5 h-1.5 rounded-full bg-orange-500 dark:bg-orange-400"></span>
                                <span>{{ $member->status }}</span>
                            </span>
                        @endif

                        <!-- ID Badge -->
                        <span class="p-1 rounded-md bg-blue-50 dark:bg-blue-500/15 border border-blue-200 dark:border-blue-500/30 text-blue-700 dark:text-blue-400 text-xs font-semibold shrink-0" title="Digital ID Card">
                            💳
                        </span>

                        <!-- Plan Badge -->
                        @if($activeMembership && $activeMembership->plan)
                            <span class="px-2.5 sm:px-3 py-1 rounded-lg bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-semibold text-xs flex items-center gap-1.5 shrink-0">
                                <span>👑</span>
                                <span>{{ $activeMembership->plan->name }}</span>
                            </span>
                        @endif

                        <!-- Phone -->
                        <a href="tel:{{ $member->phone }}" class="px-2.5 sm:px-3 py-1 rounded-lg bg-raised hover:bg-slate-200 border border-line text-fg-muted hover:text-fg dark:bg-raised dark:hover:bg-slate-700 dark:border-line dark:text-fg-muted dark:hover:text-white font-semibold text-xs flex items-center gap-1.5 transition-colors shrink-0">
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
                    <div class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                        <p class="text-base sm:text-xl font-black {{ ($daysLeft !== null && $daysLeft < 7) ? 'text-rose-600 dark:text-rose-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                            {{ $daysLeft !== null ? max(0, $daysLeft) : '0' }}
                        </p>
                        <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted block mt-0.5">DAYS LEFT</span>
                    </div>

                    <!-- Metric 2: Months Active -->
                    <div class="px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                        <p class="text-base sm:text-xl font-semibold text-fg dark:text-white">{{ $monthsActive }}</p>
                        <span class="text-xs sm:text-xs font-semibold text-fg-muted dark:text-fg-muted block mt-0.5">MONTHS ACTIVE</span>
                    </div>
                </div>

                <!-- Actions Buttons Grid on Mobile -->
                <div class="grid grid-cols-2 sm:flex sm:flex-wrap items-center gap-2 w-full sm:w-auto">
                    <!-- Actions: Add Payment -->
                    <button type="button" 
                            @click="showAddPaymentModal = true" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Payment</span>
                    </button>

                    <!-- Actions: Edit -->
                    <button type="button" 
                            @click="showEditModal = true" 
                            class="w-full sm:w-auto px-3 sm:px-4 py-2 sm:py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        <span>Edit</span>
                    </button>



                    <!-- Actions: WhatsApp Menu -->
                    <div class="relative w-full sm:w-auto" @click.away="showWhatsappMenu = false">
                        <button type="button" 
                                @click="showWhatsappMenu = !showWhatsappMenu" 
                                class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-emerald-700 border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-emerald-400 dark:border-line font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>WhatsApp</span>
                            <svg class="w-3.5 h-3.5 text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>

                        <div x-show="showWhatsappMenu" 
                             x-transition 
                             class="absolute right-0 sm:right-0 mt-2 w-56 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line py-2 z-50 text-xs">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Hello ' . $member->first_name . ', greeting from ' . (auth()->user()->tenant->name ?? 'Gym') . '!') }}" 
                                target="_blank" 
                                class="px-4 py-2.5 text-fg-muted hover:text-fg hover:bg-raised dark:text-fg-muted dark:hover:text-white dark:hover:bg-raised flex items-center gap-2 transition-colors">
                                <span>💬</span>
                                <span>Direct Chat</span>
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Dear ' . $member->first_name . ', your membership expires on ' . ($activeMembership ? $activeMembership->end_date->format('d M Y') : 'soon') . '. Please renew to continue your workout routine without interruption.') }}" 
                                target="_blank" 
                                class="px-4 py-2.5 text-fg-muted hover:text-fg hover:bg-raised dark:text-fg-muted dark:hover:text-white dark:hover:bg-raised flex items-center gap-2 transition-colors">
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

                            <button type="button" 
                                    @click="generatePortalLink()" 
                                    class="w-full text-left px-4 py-2.5 text-emerald-700 hover:bg-emerald-50 dark:text-emerald-400 dark:hover:bg-emerald-500/10 flex items-center gap-2 transition-colors cursor-pointer border-t border-line dark:border-line font-semibold">
                                <span>📲</span>
                                <span x-show="!portalLinkLoading">Send Portal Setup Link</span>
                                <span x-show="portalLinkLoading">Generating Link...</span>
                            </button>

                            <button type="button" 
                                    @click="showPasswordModal = true; showWhatsappMenu = false; passwordInput = ''; passwordSuccessMsg = ''; passwordErrorMsg = '';" 
                                    class="w-full text-left px-4 py-2.5 text-amber-700 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-500/10 flex items-center gap-2 transition-colors cursor-pointer border-t border-line dark:border-line font-semibold">
                                <span>🔑</span>
                                <span>Set Portal Password</span>
                            </button>
                        </div>
                    </div>

                    <!-- Actions: Send Portal Invite Button -->
                    <button type="button" 
                            @click="generatePortalLink()" 
                            title="Send secure setup link to member on WhatsApp so they can set their password"
                            class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span x-show="!portalLinkLoading">Send Portal Link</span>
                        <span x-show="portalLinkLoading">Sending...</span>
                    </button>

                    <!-- Actions: Set Direct Password Button -->
                    <button type="button" 
                            @click="showPasswordModal = true; passwordInput = ''; passwordSuccessMsg = ''; passwordErrorMsg = '';" 
                            title="Set or reset portal login password for this member"
                            class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 border border-amber-500/30 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                        <span>Set Password</span>
                    </button>

                    <!-- Actions: Delete -->
                    <form action="{{ route('app.members.delete', $member->id) }}" method="POST" 
                          data-confirm="Are you sure you want to permanently delete member '{{ addslashes($member->full_name) }}'? All membership records, workout history, and payments will be removed." 
                          data-confirm-title="Delete Gym Member" 
                          data-confirm-btn="Yes, Delete Member" 
                          data-confirm-type="danger" 
                          class="w-full sm:w-auto">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full sm:w-auto px-3 py-2 sm:py-2.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/15 dark:hover:bg-rose-500/25 dark:text-rose-400 dark:border-rose-500/30 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
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
                <div class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden">
                    <!-- Photo Header -->
                    <div class="relative aspect-[4/3] bg-raised dark:bg-surface flex items-center justify-center overflow-hidden">
                        @if($member->avatar_url)
                            <img src="{{ $member->avatar_url }}" alt="{{ $member->full_name }}" loading="lazy" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full bg-raised flex flex-col items-center justify-center text-fg-muted p-6 text-center">
                                <div class="w-20 h-20 rounded-full bg-surface dark:bg-raised border-2 border-line dark:border-line flex items-center justify-center text-2xl font-semibold text-fg dark:text-white mb-2">
                                    {{ strtoupper(substr($member->first_name, 0, 1)) }}{{ strtoupper(substr($member->last_name, 0, 1)) }}
                                </div>
                                <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">No Photo Uploaded</span>
                            </div>
                        @endif

                        <!-- Floating Name & Age Pill Overlay -->
                        <div class="absolute bottom-0 inset-x-0 p-4 flex items-end justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-white leading-tight">{{ $member->full_name }}</h3>
                                <div class="flex items-center gap-2 mt-1">
                                    @if($member->dob)
                                        <span class="px-2 py-0.5 rounded-full bg-raised border border-line text-xs font-semibold text-fg-muted">
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
                    <div class="p-3 bg-raised dark:bg-slate-950/70 border-t border-line dark:border-line flex items-center gap-2 text-xs text-fg-muted dark:text-fg-muted font-semibold">
                        <span class="w-2 h-2 rounded-full {{ ($lastAttendance && $lastAttendance->check_in && $lastAttendance->check_in->isToday()) ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400 dark:bg-slate-500' }}"></span>
                        <span>
                            @if($lastAttendance && $lastAttendance->check_in)
                                Last seen: <strong class="text-fg dark:text-fg">{{ $lastAttendance->check_in->isToday() ? 'Today at ' . $lastAttendance->check_in->format('g:i A') : $lastAttendance->check_in->format('d M, g:i A') }}</strong>
                            @else
                                Last seen: <span class="text-fg-muted dark:text-fg-muted">No recent check-in</span>
                            @endif
                        </span>
                    </div>
                </div>

                <!-- 2. MEMBERSHIP INFO CARD -->
                <div class="p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold text-xs">
                            💳
                        </div>
                        <h3 class="text-xs font-semibold text-fg dark:text-white">MEMBERSHIP INFO</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Gender</span>
                            <span class="font-semibold text-fg dark:text-white">{{ ucfirst($member->gender ?? 'Not Specified') }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Current Plan</span>
                            <span class="font-semibold text-amber-600 dark:text-amber-400">{{ $activeMembership?->plan?->name ?? 'None' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Category</span>
                            <span class="font-semibold text-fg-muted dark:text-fg">{{ $member->metadata['lead_source'] ?? 'General' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Assigned Trainer</span>
                            <span class="font-semibold text-fg-muted dark:text-fg">{{ $assignedTrainer?->full_name ?? 'None' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Locker Number</span>
                            <span class="font-semibold text-fg-muted dark:text-fg">{{ $member->metadata['locker_number'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Join Date</span>
                            <span class="font-semibold text-fg dark:text-white">{{ $member->join_date ? $member->join_date->format('d/m/Y') : 'N/A' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Expiry</span>
                            <span class="font-semibold text-fg dark:text-white">{{ $activeMembership ? $activeMembership->end_date->format('d/m/Y') : '—' }}</span>
                        </div>
                    </div>

                    <!-- Freeze Account Action -->
                    <div class="pt-2 border-t border-line dark:border-line">
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
                <div class="p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-3">
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-semibold text-xs">
                            🏋️
                        </div>
                        <h3 class="text-xs font-semibold text-fg dark:text-white">FITNESS & GOALS</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Current Weight</span>
                            <span class="font-semibold text-fg dark:text-white">
                                {{ !empty($member->metadata['weight']) ? $member->metadata['weight'] . ' kg' : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Target Weight</span>
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                {{ !empty($member->metadata['target_weight']) ? $member->metadata['target_weight'] . ' kg' : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Height</span>
                            <span class="font-semibold text-fg dark:text-white">
                                {{ !empty($member->metadata['height']) ? $member->metadata['height'] . ' ' . ($member->metadata['height_unit'] ?? 'cm') : 'Not Set' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Fitness Goal</span>
                            <span class="px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-500/15 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-300 font-semibold text-xs">
                                {{ $member->metadata['fitness_goal'] ?? 'General Fitness' }}
                            </span>
                        </div>

                        <!-- Medical History Section -->
                        <div class="pt-2 border-t border-line dark:border-line">
                            <span class="text-fg-muted dark:text-fg-muted font-medium block mb-1">Health Conditions / Medical History</span>
                            <p class="text-fg-muted dark:text-fg-muted bg-raised dark:bg-surface p-2.5 rounded-xl border border-line dark:border-line text-xs leading-relaxed">
                                {{ $member->metadata['medical_history'] ?? 'No medical conditions or physical limitations reported.' }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 4. CONTACT & SYSTEM CARD -->
                <div class="p-5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line space-y-4">
                    <div class="flex items-center gap-2.5 border-b border-line dark:border-line pb-3">
                        <div class="w-7 h-7 rounded-lg bg-cyan-50 dark:bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-semibold text-xs">
                            📱
                        </div>
                        <h3 class="text-xs font-semibold text-fg dark:text-white">CONTACT & SYSTEM</h3>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Phone Number</span>
                            <span class="font-semibold text-fg dark:text-white flex items-center gap-1.5">
                                {{ $member->phone }}
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}" target="_blank" class="text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 text-xs" title="WhatsApp Chat">💬</a>
                            </span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Alternate Phone</span>
                            <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $member->metadata['alternate_phone'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Email Address</span>
                            <span class="font-semibold text-fg-muted dark:text-fg-muted truncate max-w-[170px]" title="{{ $member->email }}">{{ $member->email ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">City</span>
                            <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $member->metadata['city'] ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Address</span>
                            <span class="font-semibold text-fg-muted dark:text-fg-muted truncate max-w-[170px]" title="{{ $member->address }}">{{ $member->address ?? 'Not Set' }}</span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-fg-muted dark:text-fg-muted font-medium">Blood Group</span>
                            <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-500/10 border border-rose-200 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 font-semibold text-xs">
                                {{ $member->metadata['blood_group'] ?? 'Not Set' }}
                            </span>
                        </div>

                        <!-- Emergency Contact -->
                        <div class="pt-2 border-t border-line dark:border-line">
                            <span class="text-fg-muted dark:text-fg-muted font-medium block mb-1">Emergency Contact</span>
                            @if(!empty($member->emergency_contact_name))
                                <div class="p-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-1">
                                    <div class="flex items-center justify-between font-semibold text-fg dark:text-white text-xs">
                                        <span>{{ $member->emergency_contact_name }}</span>
                                        <span class="text-xs text-rose-600 dark:text-rose-400 font-semibold">{{ $member->metadata['emergency_relation'] ?? 'Contact' }}</span>
                                    </div>
                                    @if($member->emergency_contact_phone)
                                        <div class="text-xs text-fg-muted dark:text-fg-muted font-mono">
                                            📞 {{ $member->emergency_contact_phone }}
                                        </div>
                                    @endif
                                </div>
                            @else
                                <span class="text-fg-muted dark:text-fg-muted italic text-xs">No emergency contact provided</span>
                            @endif
                        </div>

                        <!-- WhatsApp & System Rules -->
                        <div class="pt-2 border-t border-line dark:border-line space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted font-medium">WhatsApp Reminders</span>
                                @if(!empty($member->metadata['whatsapp_disabled']))
                                    <span class="px-2 py-0.5 rounded bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-semibold text-xs">Disabled</span>
                                @else
                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">Enabled</span>
                                @endif
                            </div>

                            <div class="flex items-center justify-between">
                                <span class="text-fg-muted dark:text-fg-muted font-medium">Max Daily Check-ins</span>
                                <span class="font-semibold text-fg dark:text-white">{{ !empty($member->metadata['max_daily_entries']) ? $member->metadata['max_daily_entries'] . ' per day' : 'Unlimited' }}</span>
                            </div>

                            <!-- KYC Identification Documents -->
                            <div>
                                <span class="text-fg-muted dark:text-fg-muted font-medium block mb-1">KYC Documents</span>
                                @if(!empty($member->metadata['id_documents']) && count($member->metadata['id_documents']) > 0)
                                    <div class="space-y-1.5">
                                        @foreach($member->metadata['id_documents'] as $doc)
                                            <div class="p-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line flex items-center justify-between text-xs">
                                                <div>
                                                    <span class="font-semibold text-fg dark:text-white block">{{ $doc['type'] ?? 'ID' }}</span>
                                                    <span class="text-fg-muted dark:text-fg-muted font-mono text-xs">{{ $doc['number'] ?? 'No number' }}</span>
                                                </div>
                                                <div class="flex items-center gap-1.5">
                                                    @if(!empty($doc['front_path']))
                                                        <a href="/storage/{{ $doc['front_path'] }}" target="_blank" class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-raised hover:bg-slate-300 dark:hover:bg-slate-700 text-cyan-700 dark:text-cyan-400 text-xs font-semibold">Front</a>
                                                    @endif
                                                    @if(!empty($doc['back_path']))
                                                        <a href="/storage/{{ $doc['back_path'] }}" target="_blank" class="px-1.5 py-0.5 rounded bg-slate-200 dark:bg-raised hover:bg-slate-300 dark:hover:bg-slate-700 text-cyan-700 dark:text-cyan-400 text-xs font-semibold">Back</a>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-fg-muted dark:text-fg-muted italic text-xs">No ID documents uploaded</span>
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

                <style>
                    .no-scrollbar::-webkit-scrollbar {
                        display: none !important;
                        width: 0 !important;
                        height: 0 !important;
                    }
                    .no-scrollbar {
                        -ms-overflow-style: none !important;
                        scrollbar-width: none !important;
                    }
                </style>

                <!-- Scrollable Tab Header Bar with Clean Arrows & Wheel Navigation -->
                <div class="p-1.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center gap-1.5 -mx-3 px-3 sm:mx-0 sm:px-1.5"
                     x-data="{
                         canScrollLeft: false,
                         canScrollRight: false,
                         checkScroll() {
                             const el = this.$refs.tabContainer;
                             if (!el) return;
                             this.canScrollLeft = el.scrollLeft > 6;
                             this.canScrollRight = el.scrollLeft + el.clientWidth < el.scrollWidth - 6;
                         },
                         scrollNav(dir) {
                             const el = this.$refs.tabContainer;
                             if (!el) return;
                             el.scrollBy({ left: dir * 260, behavior: 'smooth' });
                             setTimeout(() => this.checkScroll(), 300);
                         },
                         scrollWheel(e) {
                             const el = this.$refs.tabContainer;
                             if (!el) return;
                             const delta = e.deltaY !== 0 ? e.deltaY : e.deltaX;
                             if (delta !== 0) {
                                 el.scrollLeft += delta * 1.2;
                                 this.checkScroll();
                             }
                         }
                     }"
                     x-init="
                         $nextTick(() => { checkScroll(); });
                         window.addEventListener('resize', () => checkScroll());
                     ">

                    <!-- Left Navigation Arrow -->
                    <button type="button" 
                            x-show="canScrollLeft" 
                            x-cloak
                            @click="scrollNav(-1)"
                            class="shrink-0 w-8 h-8 rounded-xl bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-750 text-fg-muted dark:text-fg flex items-center justify-center transition-all cursor-pointer"
                            title="Scroll left">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                    </button>

                    <!-- Tabs Container -->
                    <div x-ref="tabContainer"
                         @scroll.passive="checkScroll()"
                         @wheel.prevent="scrollWheel($event)"
                         class="flex items-center gap-1.5 overflow-x-auto text-xs font-semibold no-scrollbar flex-1 min-w-0"
                         style="scrollbar-width: none !important; -ms-overflow-style: none !important;">

                        <button type="button" 
                                @click="activeTab = 'subscriptions'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'subscriptions' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Subscriptions
                        </button>

                        <button type="button" 
                                @click="activeTab = 'pt_packages'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'pt_packages' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            PT Packages
                        </button>

                        <button type="button" 
                                @click="activeTab = 'services'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'services' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Services
                        </button>

                        <button type="button" 
                                @click="activeTab = 'invoices'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'invoices' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Invoices &amp; Receipts
                        </button>

                        <button type="button" 
                                @click="activeTab = 'agreement'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer flex items-center gap-1.5"
                                :class="activeTab === 'agreement' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            <span>Agreement &amp; Health</span>
                            @if(isset($latestAgreement))
                                @if($latestAgreement->isCompleted())
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                @elseif($latestAgreement->status === 'medical_review')
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                @else
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                @endif
                            @endif
                        </button>

                        <button type="button" 
                                @click="activeTab = 'workouts'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'workouts' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Workout Plans
                        </button>

                        <button type="button" 
                                @click="activeTab = 'diets'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'diets' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Diet Plans
                        </button>

                        <button type="button" 
                                @click="activeTab = 'classes'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'classes' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Classes
                        </button>

                        <button type="button" 
                                @click="activeTab = 'attendance'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'attendance' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Attendance
                        </button>

                        <button type="button" 
                                @click="activeTab = 'measurements'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'measurements' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Measurements
                        </button>

                        <button type="button" 
                                @click="activeTab = 'audit_trail'; $el.scrollIntoView({ behavior: 'smooth', inline: 'nearest', block: 'nearest' });"
                                class="shrink-0 px-3.5 sm:px-4 py-2 sm:py-2.5 rounded-xl transition-all whitespace-nowrap cursor-pointer"
                                :class="activeTab === 'audit_trail' ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30' : 'text-slate-600 hover:text-slate-900 hover:bg-slate-100 dark:text-slate-400 dark:hover:text-white dark:hover:bg-slate-800'">
                            Audit Trail <span class="px-1.5 py-0.5 rounded-full bg-raised dark:bg-raised text-xs ml-1">{{ $auditLogs->count() }}</span>
                        </button>
                    </div>

                    <!-- Right Navigation Arrow -->
                    <button type="button" 
                            x-show="canScrollRight" 
                            x-cloak
                            @click="scrollNav(1)"
                            class="shrink-0 w-8 h-8 rounded-xl bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-750 text-fg-muted dark:text-fg flex items-center justify-center transition-all cursor-pointer"
                            title="Scroll right">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <!-- TAB 1: SUBSCRIPTIONS HISTORY -->
                <div x-show="activeTab === 'subscriptions'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden space-y-4 p-4 sm:p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-4">
                        <div>
                            <h2 class="text-base font-semibold text-fg dark:text-white">Subscription History</h2>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">All current and previous gym membership packages</p>
                        </div>

                        @if(auth()->user()->isGymOwner() || auth()->user()->hasPermission('memberships.create') || auth()->user()->hasPermission('memberships.edit') || auth()->user()->hasPermission('plans.create') || auth()->user()->hasPermission('plans.edit') || auth()->user()->hasPermission('members.edit') || auth()->user()->hasPermission('members.create'))
                        <button type="button" 
                                @click="showAddSubscriptionModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add Subscription</span>
                        </button>
                        @endif
                    </div>

                    <!-- Subscription Table -->
                    <div class="overflow-x-auto no-scrollbar -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="w-full text-left text-xs min-w-[560px] sm:min-w-full">
                            <thead class="text-xs text-fg-muted dark:text-fg-muted bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line">
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
                                @forelse($member->memberships()->latest()->get() as $sub)
                                    @php
                                        $subDaysLeft = (int) now()->startOfDay()->diffInDays($sub->end_date->startOfDay(), false);
                                        $subDue = max(0, (float)$sub->final_amount - (float)$sub->paid_amount);
                                        $subPayments = $sub->payments()->latest()->get();
                                    @endphp
                                    <tbody x-data="{ showPayments: false }" class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                        <tr class="hover:bg-raised dark:hover:bg-raised transition-colors">
                                            <!-- Plan -->
                                            <td class="py-3.5 px-3 font-semibold text-fg dark:text-white">
                                                <span>{{ $sub->plan->name ?? 'Custom Plan' }}</span>
                                            </td>

                                            <!-- Period -->
                                            <td class="py-3.5 px-3">
                                                <div class="font-medium text-fg dark:text-fg">
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
                                            <td class="py-3.5 px-3 font-semibold text-fg dark:text-white">
                                                {{ $currency }}{{ number_format($sub->final_amount, 2) }}
                                            </td>

                                            <!-- Discount -->
                                            <td class="py-3.5 px-3 text-fg-muted dark:text-fg-muted">
                                                {{ $sub->discount_amount > 0 ? $currency . number_format($sub->discount_amount, 2) : '—' }}
                                            </td>

                                            <!-- Paid -->
                                            <td class="py-3.5 px-3 font-semibold text-emerald-600 dark:text-emerald-400">
                                                {{ $currency }}{{ number_format($sub->paid_amount, 2) }}
                                            </td>

                                            <!-- Status -->
                                            <td class="py-3.5 px-3">
                                                @if($sub->status === 'ACTIVE' && !$sub->isExpired())
                                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                                                        Active
                                                    </span>
                                                @elseif($sub->status === 'CANCELLED')
                                                    <span class="px-2.5 py-0.5 rounded-full bg-rose-50 dark:bg-rose-500/15 border border-rose-200 dark:border-rose-500/30 text-rose-700 dark:text-rose-400 font-semibold text-xs">
                                                        Cancelled
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-semibold text-xs">
                                                        Expired
                                                    </span>
                                                @endif
                                            </td>

                                            <!-- Actions -->
                                            <td class="py-3.5 px-3 text-right">
                                                <div class="flex items-center justify-end gap-2">
                                                    @if($subPayments->count() > 0)
                                                        <button type="button" 
                                                                @click="showPayments = !showPayments"
                                                                title="Toggle Payment Installments"
                                                                class="px-2.5 py-1.5 rounded-lg bg-raised hover:bg-slate-200 text-fg-muted hover:text-fg border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white dark:border-line text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1">
                                                            <span x-text="showPayments ? 'Hide' : 'Payments ({{ $subPayments->count() }})'"></span>
                                                            <svg class="w-3.5 h-3.5 transition-transform" :class="showPayments ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                                        </button>
                                                    @endif

                                                    @if($sub->status === 'ACTIVE' && (auth()->user()->isGymOwner() || auth()->user()->isSuperAdmin() || auth()->user()->hasPermission('memberships.edit')))
                                                        <button type="button" 
                                                                @click="openCancelMembershipModal({{ $sub->id }}, '{{ addslashes($sub->plan->name ?? 'Membership Plan') }}')"
                                                                title="Cancel / Void this membership subscription" 
                                                                class="px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-200 dark:bg-rose-500/10 dark:hover:bg-rose-500/20 dark:text-rose-400 dark:border-rose-500/20 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                                            <span>Cancel</span>
                                                        </button>
                                                    @endif

                                                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode('Subscription: ' . ($sub->plan->name ?? '') . ' | Period: ' . $sub->start_date->format('d/m/Y') . ' - ' . $sub->end_date->format('d/m/Y') . ' | Total: ' . $currency . number_format($sub->final_amount, 2)) }}" 
                                                       target="_blank" 
                                                       title="Send via WhatsApp" 
                                                       class="p-1.5 rounded-lg bg-raised hover:bg-slate-200 text-emerald-600 border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-emerald-400 dark:border-line transition-colors">
                                                        💬
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Expandable Payments Timeline Row -->
                                        @if($subPayments->count() > 0)
                                            <tr x-show="showPayments" x-cloak class="bg-raised dark:bg-slate-950/70">
                                                <td colspan="7" class="p-3 sm:p-4">
                                                    <div class="rounded-xl border border-line dark:border-line bg-surface dark:bg-surface p-3 sm:p-4 space-y-3">
                                                        <div class="flex items-center justify-between flex-wrap gap-2 text-xs border-b border-line dark:border-line pb-2.5">
                                                            <div class="flex items-center gap-2">
                                                                <span class="font-semibold text-fg dark:text-white">Fee Installments / Receipts for {{ $sub->plan->name ?? 'Package' }}</span>
                                                                <span class="text-fg-muted">• Total Package: {{ $currency }}{{ number_format($sub->final_amount, 2) }}</span>
                                                                <span class="text-emerald-600 dark:text-emerald-400 font-semibold">• Received: {{ $currency }}{{ number_format($sub->paid_amount, 2) }}</span>
                                                            </div>
                                                            @if($subDue > 0)
                                                                <button type="button" 
                                                                        @click="showAddPaymentModal = true" 
                                                                        class="px-2.5 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold text-xs flex items-center gap-1 cursor-pointer">
                                                                    <span>Collect Remaining Fee</span>
                                                                    <span>({{ $currency }}{{ number_format($subDue, 2) }})</span>
                                                                </button>
                                                            @else
                                                                <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                                                                    Package Fully Paid
                                                                </span>
                                                            @endif
                                                        </div>

                                                        <div class="space-y-1.5">
                                                            @foreach($subPayments as $p)
                                                                @php
                                                                    $pReceipt = $p->receipt_number ?? ('INV-' . str_pad($p->id, 4, '0', STR_PAD_LEFT));
                                                                    $pDate = $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y');
                                                                @endphp
                                                                <div class="flex items-center justify-between p-2.5 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-xs">
                                                                    <div class="flex items-center gap-2.5 flex-wrap">
                                                                        <span class="font-mono font-semibold text-amber-600 dark:text-amber-400 text-xs">{{ $pReceipt }}</span>
                                                                        <span class="text-fg-muted">•</span>
                                                                        <span class="font-semibold text-fg dark:text-fg">{{ $pDate }}</span>
                                                                        <span class="text-fg-muted">•</span>
                                                                        <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-raised font-mono text-xs font-semibold text-fg-muted dark:text-fg-muted">{{ $p->payment_method }}</span>
                                                                        @if(!empty($p->notes) && !str_contains(strtolower($p->notes), 'crm') && !str_contains(strtolower($p->notes), 'conversion') && !str_contains(strtolower($p->notes), 'lead'))
                                                                            <span class="text-fg-muted">•</span>
                                                                            <span class="text-fg-muted dark:text-fg-muted text-xs truncate max-w-[200px]">{{ $p->notes }}</span>
                                                                        @endif
                                                                    </div>
                                                                    <div class="flex items-center gap-2">
                                                                        <span class="font-semibold text-emerald-600 dark:text-emerald-400 font-mono text-sm">{{ $currency }}{{ number_format($p->amount, 2) }}</span>
                                                                        <button type="button" 
                                                                                @click="openInvoice({
                                                                                    id: {{ $p->id }},
                                                                                    receipt_no: '{{ $pReceipt }}',
                                                                                    date: '{{ $pDate }}',
                                                                                    amount: '{{ number_format($p->amount, 2) }}',
                                                                                    method: '{{ strtoupper($p->payment_method) }}',
                                                                                    ref: '{{ $p->transaction_reference ?? '' }}',
                                                                                    plan_name: '{{ $sub->plan->name ?? 'Gym Membership' }}',
                                                                                    plan_duration: '{{ $sub->plan ? ($sub->plan->duration_value . ' ' . $sub->plan->duration_type) : '' }}',
                                                                                    start_date: '{{ $sub->start_date->format('d M Y') }}',
                                                                                    end_date: '{{ $sub->end_date->format('d M Y') }}',
                                                                                    total: '{{ number_format($sub->final_amount, 2) }}',
                                                                                    paid: '{{ number_format($p->amount, 2) }}',
                                                                                    notes: '{{ addslashes($p->notes ?? '') }}',
                                                                                    whatsapp_url: '{{ $p->whatsapp_share_url }}'
                                                                                })"
                                                                                title="View Receipt"
                                                                                class="p-1.5 rounded-lg bg-surface dark:bg-raised border border-line dark:border-line text-fg-muted dark:text-fg-muted hover:text-white hover:bg-slate-700 transition-colors cursor-pointer">
                                                                            👁️
                                                                        </button>
                                                                        <a href="{{ $p->whatsapp_share_url }}" target="_blank" title="Share Receipt on WhatsApp" class="p-1.5 rounded-lg bg-surface dark:bg-raised border border-line dark:border-line text-emerald-600 dark:text-emerald-400 hover:bg-emerald-50 dark:hover:bg-emerald-500/20 transition-colors">
                                                                            💬
                                                                        </a>
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                @empty
                                    <tbody class="text-fg-muted dark:text-fg-muted">
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-fg-muted dark:text-fg-muted text-xs">
                                                No subscriptions found for this member. Click "+ Add Subscription" to assign one.
                                            </td>
                                        </tr>
                                    </tbody>
                                @endforelse
                        </table>
                    </div>
                </div>

                <!-- TAB 2: PT PACKAGES -->
                <div x-show="activeTab === 'pt_packages'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white">Personal Training</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Assigned personal training packages, sessions and dedicated coaching</p>
                        </div>
                        <button type="button" 
                                @click="showAddPtModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Add PT Package</span>
                        </button>
                    </div>

                    @php
                        $ptPackages = $member->metadata['pt_packages'] ?? [];
                    @endphp

                    @if(empty($ptPackages) && !$assignedTrainer)
                        <div class="py-12 text-center text-xs text-fg-muted dark:text-fg-muted rounded-xl bg-raised dark:bg-surface border border-line dark:border-line">
                            No PT packages for this member yet.
                        </div>
                    @else
                        <div class="space-y-3">
                            @if($assignedTrainer && empty($ptPackages))
                                <div class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold shrink-0">
                                            🏋️
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-fg dark:text-white text-xs">{{ $assignedTrainer->full_name }}</h4>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted">{{ $assignedTrainer->specialization ?? 'Personal Trainer' }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                        Assigned Coach
                                    </span>
                                </div>
                            @endif

                            @foreach($ptPackages as $pt)
                                <div class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-base shrink-0">
                                            🏋️
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-fg dark:text-white text-xs">{{ $pt['package_name'] ?? 'Personal Training Package' }}</h4>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">
                                                Trainer: <strong class="text-fg dark:text-white">{{ $pt['trainer_name'] ?? 'Assigned Trainer' }}</strong> • {{ $pt['sessions'] ?? 12 }} Sessions • Valid {{ $pt['validity_days'] ?? 30 }} days
                                            </p>
                                            <p class="text-xs font-mono text-fg-muted dark:text-fg-muted mt-0.5">
                                                {{ !empty($pt['start_date']) ? date('d M Y', strtotime($pt['start_date'])) : '' }} — {{ !empty($pt['end_date']) ? date('d M Y', strtotime($pt['end_date'])) : '' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between sm:justify-end gap-4 pt-2 sm:pt-0 border-t sm:border-t-0 border-line dark:border-line">
                                        <div class="text-left sm:text-right">
                                            <span class="text-xs text-fg-muted dark:text-fg-muted block font-semibold">TOTAL / PAID</span>
                                            <span class="font-semibold text-fg dark:text-white text-xs">{{ $currency }}{{ number_format($pt['total'] ?? 0, 2) }}</span>
                                            <span class="text-emerald-600 dark:text-emerald-400 text-xs block font-semibold">{{ $currency }}{{ number_format($pt['paid'] ?? 0, 2) }}</span>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                            {{ $pt['status'] ?? 'Active' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- TAB 3: SERVICES -->
                <div x-show="activeTab === 'services'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span>✨</span>
                                <span>Gym Amenities & Services</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Manage spa, sauna, locker rental, and booked services for {{ $member->first_name }}</p>
                        </div>
                        <button type="button" 
                                @click="openBookService()" 
                                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
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
                        <div class="p-6 sm:p-8 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🧖
                            </div>
                            <h4 class="font-semibold text-fg dark:text-white text-sm">No Services Booked Yet</h4>
                            <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                                {{ $member->first_name }} has not booked any gym amenities, spa/sauna, or locker services yet.
                            </p>
                            <button type="button" 
                                    @click="openBookService()" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer ops-primary">
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
                                <div class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-4 hover:border-indigo-500/40 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-lg shrink-0 border border-indigo-200 dark:border-indigo-500/20">
                                                {{ $icon }}
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-fg dark:text-white text-sm">{{ $svc?->name ?? 'Gym Service' }}</h4>
                                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-fg-muted dark:text-fg-muted font-semibold">
                                                    <span class="px-2 py-0.5 rounded bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">{{ $currency }}{{ number_format($booking->amount_paid, 0) }}</span>
                                                    @if($booking->locker_number)
                                                        <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-semibold text-xs">Locker: {{ $booking->locker_number }}</span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-xs font-semibold text-fg-muted dark:text-fg-muted space-y-0.5">
                                                    <p class="text-xs text-fg-muted dark:text-fg-muted">
                                                        Booked: <strong class="text-fg-muted dark:text-fg-muted">{{ $booking->booking_date ? \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') : $booking->created_at->format('d M Y') }}</strong>
                                                        @if($booking->booking_time)
                                                            <span>• {{ $booking->booking_time }}</span>
                                                        @endif
                                                    </p>
                                                    @if($svc?->is_session_countable)
                                                        <p class="text-xs text-indigo-600 dark:text-indigo-400 font-semibold">
                                                            Sessions: {{ $booking->sessions_left }} / {{ $booking->total_sessions }} remaining
                                                        </p>
                                                    @endif
                                                    @if($booking->notes)
                                                        <p class="text-xs text-fg-muted dark:text-fg-muted italic mt-0.5">"{{ $booking->notes }}"</p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        @if($booking->status === 'active')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                                Active
                                            </span>
                                        @elseif($booking->status === 'completed')
                                            <span class="px-2.5 py-1 rounded-full bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-semibold text-xs border border-line dark:border-line shrink-0">
                                                Completed
                                            </span>
                                        @elseif($booking->status === 'pending')
                                            <span class="px-2.5 py-1 rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-400 font-semibold text-xs border border-amber-200 dark:border-amber-500/20 shrink-0">
                                                Pending
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-semibold text-xs border border-rose-200 dark:border-rose-500/20 shrink-0">
                                                {{ ucfirst($booking->status) }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="flex items-center justify-between pt-3 border-t border-line dark:border-line">
                                        <div class="flex items-center gap-2">
                                            @if($booking->status === 'active' && $booking->sessions_left > 0)
                                                <form action="{{ route('app.services.bookings.deduct', $booking->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center gap-1 transition-all cursor-pointer ops-primary">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                        <span>Deduct Session</span>
                                                    </button>
                                                </form>
                                            @endif

                                            <form action="{{ route('app.services.bookings.status', $booking->id) }}" method="POST" class="flex items-center gap-1.5">
                                                @csrf
                                                <select name="status" onchange="this.form.submit()" class="px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg-muted dark:text-fg-muted focus:outline-none focus:border-accent">
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
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-700 text-xs font-semibold hover:underline cursor-pointer flex items-center gap-1">
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
                        <div class="pt-4 border-t border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-fg dark:text-white text-xs">Available Gym Amenities & Add-on Services</h4>
                                <a href="{{ route('app.services.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Manage All Services →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($availableServices as $svc)
                                    <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-3">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-semibold text-fg dark:text-white text-xs">{{ $svc->name }}</h5>
                                                <span class="text-xs font-semibold text-amber-600 dark:text-amber-400 shrink-0">{{ $currency }}{{ number_format($svc->amount, 0) }}</span>
                                            </div>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 line-clamp-2">
                                                {{ $svc->description ?? 'Gym amenity service available for members.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-xs text-fg-muted dark:text-fg-muted font-semibold">
                                                <span>⏱ {{ $svc->duration_minutes }} mins</span>
                                                <span>•</span>
                                                <span>{{ $svc->timeslot_availability ?? 'By Appointment' }}</span>
                                                @if($svc->is_session_countable)
                                                    <span>•</span>
                                                    <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $svc->session_count }} sessions</span>
                                                @endif
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="openBookService('{{ $svc->id }}', {{ (float)$svc->amount }}, {{ $svc->is_locker_service ? 'true' : 'false' }}, {{ $svc->is_session_countable ? 'true' : 'false' }}, {{ (int)$svc->session_count }})" 
                                                class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-xs font-semibold transition-all text-center cursor-pointer">
                                            + Book Service
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 4: INVOICES & PAYMENT TRAIL -->
                <div x-show="activeTab === 'invoices'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span>💳</span>
                                <span>Payment & Invoice Trail</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Grouped by subscription package with full payment receipts and invoices</p>
                        </div>
                        <button type="button" 
                                @click="showAddPaymentModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
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
                            <div class="rounded-xl bg-raised dark:bg-surface border border-line dark:border-line overflow-hidden">
                                
                                <!-- Card Header Summary Banner -->
                                <div class="p-3.5 sm:p-4 bg-raised dark:bg-slate-900/90 border-b border-line dark:border-line flex flex-col lg:flex-row lg:items-center justify-between gap-3 sm:gap-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 border border-indigo-200 dark:border-indigo-500/30 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-semibold text-sm shrink-0">
                                            🏋️
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <h4 class="font-semibold text-fg dark:text-white text-sm">
                                                    {{ $sub->plan->name ?? 'Membership Subscription' }}
                                                    @if($sub->plan)
                                                        <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">({{ $sub->plan->duration_value }} {{ $sub->plan->duration_type }})</span>
                                                    @endif
                                                </h4>
                                                <span class="px-2.5 py-0.5 rounded-full bg-surface dark:bg-raised border border-line dark:border-line text-fg-muted dark:text-fg-muted font-mono text-xs font-semibold">
                                                    {{ $sub->start_date->format('d/m/Y') }} - {{ $sub->end_date->format('d/m/Y') }}
                                                </span>
                                                @if($isFullyPaid)
                                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                                                        Fully Paid
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full bg-amber-50 dark:bg-amber-500/15 border border-amber-200 dark:border-amber-500/30 text-amber-700 dark:text-amber-400 font-semibold text-xs">
                                                        Due: {{ $currency }}{{ number_format($due, 2) }}
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Right Financial Statistics -->
                                    <div class="grid grid-cols-2 sm:flex sm:items-center gap-2 sm:gap-3 text-right flex-wrap lg:flex-nowrap">
                                        <div class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-center sm:text-right">
                                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">PLAN PRICE</span>
                                            <span class="text-xs font-semibold text-fg dark:text-white">{{ $currency }}{{ number_format($planPrice, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-center sm:text-right">
                                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">DISCOUNT</span>
                                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">{{ $currency }}{{ number_format($discount, 2) }}</span>
                                        </div>

                                        <div class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-500/20 text-center sm:text-right">
                                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400 block">RECEIVED</span>
                                            <span class="text-xs font-semibold text-emerald-700 dark:text-emerald-400">{{ $currency }}{{ number_format($received, 2) }}</span>
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
                                        <thead class="text-xs text-fg-muted dark:text-fg-muted bg-raised dark:bg-surface border-b border-line dark:border-line">
                                            <tr>
                                                <th class="py-2.5 px-4 font-semibold">Receipt No</th>
                                                <th class="py-2.5 px-4 font-semibold">Date</th>
                                                <th class="py-2.5 px-4 font-semibold">Payment Type</th>
                                                <th class="py-2.5 px-4 font-semibold">Method</th>
                                                <th class="py-2.5 px-4 font-semibold">Amount</th>
                                                <th class="py-2.5 px-4 font-semibold text-right">Receipt &amp; Share</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                            @forelse($sub->payments as $p)
                                                @php
                                                    $receiptNum = $p->receipt_number ?? ('INV-' . str_pad($p->id, 4, '0', STR_PAD_LEFT));
                                                    $payDateFormatted = $p->payment_date ? $p->payment_date->format('d M Y') : $p->created_at->format('d M Y');
                                                @endphp
                                                <tr class="hover:bg-surface dark:hover:bg-slate-900/40 transition-colors">
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
                                                                    paid: '{{ number_format($p->amount, 2) }}',
                                                                    notes: '{{ addslashes($p->notes ?? '') }}',
                                                                    whatsapp_url: '{{ $p->whatsapp_share_url }}'
                                                                })"
                                                                class="inline-flex items-center gap-1.5 font-mono font-semibold text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 hover:underline cursor-pointer">
                                                            <svg class="w-3.5 h-3.5 text-fg-muted shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                                            <span>{{ $receiptNum }}</span>
                                                        </button>
                                                    </td>

                                                    <!-- Date -->
                                                    <td class="py-3 px-4 font-medium text-fg dark:text-fg">
                                                        {{ $payDateFormatted }}
                                                    </td>

                                                    <!-- Type -->
                                                    <td class="py-3 px-4">
                                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">
                                                            Fee Installment
                                                        </span>
                                                    </td>

                                                    <!-- Method -->
                                                    <td class="py-3 px-4">
                                                        <span class="px-2 py-0.5 rounded bg-slate-200 dark:bg-raised text-fg-muted dark:text-fg-muted font-semibold text-xs">
                                                            {{ $p->payment_method }}
                                                        </span>
                                                    </td>

                                                    <!-- Amount -->
                                                    <td class="py-3 px-4 font-semibold text-emerald-600 dark:text-emerald-400 text-sm">
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
                                                                        paid: '{{ number_format($p->amount, 2) }}',
                                                                        notes: '{{ addslashes($p->notes ?? '') }}',
                                                                        whatsapp_url: '{{ $p->whatsapp_share_url }}'
                                                                    })"
                                                                    title="View Payment Receipt" 
                                                                    class="p-1.5 rounded-lg bg-surface hover:bg-raised text-fg-muted hover:text-fg border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white dark:border-line transition-colors cursor-pointer">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                            </button>

                                                            <a href="{{ route('app.invoices.show', $p->id) }}" 
                                                               target="_blank" 
                                                               title="Open Printable Receipt Page" 
                                                               class="p-1.5 rounded-lg bg-surface hover:bg-raised text-cyan-600 dark:text-cyan-400 hover:text-cyan-700 dark:hover:text-cyan-300 border border-line dark:border-line transition-colors">
                                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                                            </a>

                                                            <a href="{{ $p->whatsapp_share_url }}" 
                                                               target="_blank" 
                                                               title="Share Receipt on WhatsApp" 
                                                               class="p-1.5 rounded-lg bg-surface hover:bg-emerald-50 text-emerald-600 border border-line dark:bg-raised dark:hover:bg-emerald-500/20 dark:text-emerald-400 dark:border-line transition-colors">
                                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                            </a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="py-4 px-4 text-center text-fg-muted dark:text-fg-muted text-xs">
                                                        No payment records logged for this subscription package.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 sm:p-8 text-center bg-raised dark:bg-surface rounded-xl border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
                                No membership subscription or payment records found for this member yet.
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- TAB: DIGITAL AGREEMENT & HEALTH DECLARATION -->
                <div x-show="activeTab === 'agreement'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-5">
                    @php
                        $ag = $latestAgreement ?? $member->latestAgreement;
                    @endphp

                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span class="p-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </span>
                                <span>Digital Member Agreement & Health Declaration</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Legally binding membership contract, PAR-Q medical disclosure, and electronic signature</p>
                        </div>

                        <div class="flex items-center gap-2">
                            @if($ag)
                                <a href="{{ route('app.agreements.pdf', $ag->id) }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-surface dark:bg-surface text-white dark:text-fg font-semibold text-xs flex items-center gap-1.5 hover:scale-[1.01] transition-transform">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    <span>Official Document (PDF)</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($ag)
                        <!-- Agreement Status Overview Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-1">
                                <span class="text-xs font-semibold text-fg-muted">Status</span>
                                <div>
                                    @if($ag->isCompleted())
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Signed &amp; Active
                                        </span>
                                    @elseif($ag->status === 'medical_review')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                            Medical Review Needed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                            Pending Member Signature
                                        </span>
                                    @endif
                                </div>
                                <div class="text-xs text-fg-muted pt-1">Version: <strong class="text-fg dark:text-fg">v{{ $ag->version }}</strong> ({{ $ag->terrace_rules_included ? 'Terrace rules included' : 'Standard rules' }})</div>
                            </div>

                            <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-1">
                                <span class="text-xs font-semibold text-fg-muted">Health Declaration (PAR-Q)</span>
                                <div>
                                    @if($ag->has_medical_conditions)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border border-rose-200">
                                            ⚠️ Medical Disclosures Flagged
                                        </span>
                                    @elseif($ag->health_declaration_accepted_at)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded text-xs font-semibold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40">
                                            ✓ Clear (All Negative)
                                        </span>
                                    @else
                                        <span class="text-xs text-fg-muted italic">Not submitted yet</span>
                                    @endif
                                </div>
                                <div class="text-xs text-fg-muted pt-1">
                                    {{ $ag->health_declaration_accepted_at ? 'Accepted ' . $ag->health_declaration_accepted_at->format('d M Y') : 'Awaiting member input' }}
                                </div>
                            </div>

                            <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-1">
                                <span class="text-xs font-semibold text-fg-muted">Electronic Signature</span>
                                <div>
                                    @if($ag->signed_at)
                                        <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400">Signed on {{ $ag->signed_at->format('d M Y, h:i A') }}</span>
                                    @else
                                        <span class="text-xs text-amber-600 dark:text-amber-400 font-semibold">Unsigned</span>
                                    @endif
                                </div>
                                @if($ag->signature_reference)
                                    <div class="text-xs font-mono text-fg-muted pt-1 truncate">Ref: {{ $ag->signature_reference }}</div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Bar for Unsigned / Pending -->
                        @if(!$ag->isCompleted())
                            <div class="p-4 rounded-xl bg-indigo-50/70 dark:bg-indigo-950/40 border border-indigo-200/60 dark:border-indigo-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="text-xs font-semibold text-indigo-950 dark:text-indigo-200">Direct Member Signing Link</div>
                                    <div class="text-xs text-indigo-800/80 dark:text-indigo-300">Opens the member profile, health declaration and signing steps.</div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="navigator.clipboard.writeText('{{ $ag->share_url }}'); alert('Link copied to clipboard!');" class="px-3.5 py-2 rounded-xl bg-surface dark:bg-surface border border-indigo-200 dark:border-indigo-800 text-fg dark:text-fg text-xs font-semibold hover:bg-indigo-50 transition-colors">
                                        Copy Link
                                    </button>
                                    <a href="{{ $ag->whatsapp_share_url }}" target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold transition-colors flex items-center gap-1.5">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                        <span>Send on WhatsApp</span>
                                    </a>
                                </div>
                            </div>
                        @endif

                        <!-- Health Declaration Details -->
                        @if(!empty($ag->health_declaration_answers))
                            <div class="space-y-3 pt-2">
                                <h4 class="text-xs font-semibold text-fg dark:text-fg">Submitted PAR-Q Medical Questionnaire</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                                    @php
                                        $healthQuestions = collect($ag->template?->health_questions ?? \App\Models\TenantAgreementTemplate::getDefaultHealthQuestions())->keyBy('id');
                                    @endphp
                                    @foreach($ag->health_declaration_answers as $questionId => $qAns)
                                        @php
                                            $questionText = $qAns['question'] ?? ($healthQuestions->get($questionId)['question'] ?? 'Health question');
                                            $disclosure = $qAns['explanation'] ?? $qAns['details'] ?? null;
                                        @endphp
                                        <div class="p-3 rounded-xl border text-xs {{ ($qAns['answer'] ?? '') === 'yes' ? 'bg-rose-50/80 border-rose-200 dark:bg-rose-950/30 dark:border-rose-900' : 'bg-slate-50 border-slate-200/70 dark:bg-slate-950 dark:border-slate-800' }}">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="font-semibold text-fg dark:text-fg">{{ $loop->iteration }}. {{ $questionText }}</div>
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase shrink-0 {{ ($qAns['answer'] ?? '') === 'yes' ? 'bg-rose-600 text-white' : 'bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300' }}">
                                                    {{ $qAns['answer'] ?? 'no' }}
                                                </span>
                                            </div>
                                            @if(($qAns['answer'] ?? '') === 'yes' && !empty($disclosure))
                                                <div class="mt-2 text-xs text-rose-900 dark:text-rose-300 bg-white/70 dark:bg-surface p-2 rounded-lg">
                                                    <strong>Details:</strong> {{ $disclosure }}
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Signature & Verification Certificate -->
                        @if($ag->isCompleted())
                            <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-3">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                    <h4 class="text-xs font-semibold text-fg dark:text-fg flex items-center gap-1.5">
                                        <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                        <span>Cryptographic Audit Trail (SHA-256)</span>
                                    </h4>
                                    <span class="text-xs font-mono text-fg-muted">Ref: {{ $ag->signature_reference }}</span>
                                </div>
                                <div class="text-xs font-mono p-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg-muted dark:text-fg-muted break-all">
                                    {{ $ag->signature_hash }}
                                </div>
                                <div class="flex items-center justify-between text-xs text-fg-muted pt-1">
                                    <span>IP: {{ $ag->signer_ip ?? 'Recorded' }}</span>
                                    <span>Signed: {{ $ag->signed_at?->format('d M Y, h:i:s A T') }}</span>
                                </div>
                            </div>
                        @endif

                    @else
                        <div class="py-12 text-center text-fg-muted">
                            <p class="text-xs">No digital agreement generated yet for this member.</p>
                            <form action="{{ route('app.agreements.generate', $member->id) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-accent text-accent-on text-xs font-semibold ops-primary">
                                    Generate Agreement Now
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
                <div x-show="activeTab === 'workouts'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span>🏋️</span>
                                <span>Assigned Workout Routines & Splits</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Personalized exercise programs, splits, and training regimens for {{ $member->full_name }}</p>
                        </div>
                        <button type="button" 
                                @click="openCreateWorkout()" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
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
                                        if (!empty($ex->video_url)) {
                                            $whatsappWorkoutText .= "   ▶️ Video Demo: " . $ex->video_url . "\n";
                                        }
                                    }
                                    if ($wp->notes) {
                                        $whatsappWorkoutText .= "---------------------------\n*Notes:* " . $wp->notes . "\n";
                                    }
                                    $whatsappWorkoutText .= "\nKeep pushing your limits! 💪";
                                @endphp

                                <div class="p-4 sm:p-5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between hover:border-indigo-500/40 transition-all">
                                    <div>
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3 mb-2.5">
                                            <div>
                                                <h4 class="font-semibold text-fg dark:text-white text-base leading-tight">{{ $wp->title }}</h4>
                                                <div class="flex items-center gap-2 mt-1 text-xs text-fg-muted dark:text-fg-muted flex-wrap">
                                                    <span class="font-semibold text-fg-muted dark:text-fg-muted">Level: {{ $formattedLevel }}</span>
                                                    @if($wp->trainer)
                                                        <span>• Coach: <strong class="text-fg dark:text-fg">{{ $wp->trainer->full_name }}</strong></span>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($wp->goal)
                                                <span class="self-start px-2.5 py-1 rounded-xl text-xs bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-semibold shrink-0">
                                                    {{ $formattedGoal }}
                                                </span>
                                            @endif
                                        </div>

                                        @if($wp->start_date)
                                            <div class="text-xs text-fg-muted dark:text-fg-muted mb-3 font-mono">
                                                Active from {{ $wp->start_date->format('d M Y') }} {{ $wp->end_date ? 'to ' . $wp->end_date->format('d M Y') : '' }}
                                            </div>
                                        @endif

                                        <!-- Exercises List -->
                                        <div class="border-t border-line dark:border-line pt-3 mt-2">
                                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-2">
                                                Prescribed Exercises ({{ $wp->exercises->count() }})
                                            </span>
                                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                                @forelse($wp->exercises as $ex)
                                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-3 p-2.5 sm:p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs">
                                                        <div class="min-w-0 flex-1">
                                                            <div class="font-semibold text-fg dark:text-white flex items-center gap-2 flex-wrap">
                                                                <span class="text-xs px-2 py-0.5 rounded-md bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted font-mono font-semibold shrink-0">
                                                                    {{ ucwords(str_replace('_', ' ', $ex->day ?? 'Day 1')) }}
                                                                </span>
                                                                <span class="truncate">{{ $ex->exercise_name }}</span>
                                                                @if(!empty($ex->video_url))
                                                                    <a href="{{ $ex->video_url }}" target="_blank" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 border border-indigo-500/20 shrink-0 transition-colors" title="Watch Form Demo Video">
                                                                        <span>▶️</span>
                                                                        <span class="hidden sm:inline">Demo</span>
                                                                    </a>
                                                                @endif
                                                            </div>
                                                            @if($ex->notes)
                                                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5 truncate">{{ $ex->notes }}</p>
                                                            @endif
                                                        </div>
                                                        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center text-right shrink-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-line dark:border-line">
                                                            <span class="text-amber-600 dark:text-amber-400 font-semibold font-mono text-xs">{{ $ex->sets }} × {{ $ex->reps }}</span>
                                                            @if($ex->weight)
                                                                <span class="text-xs text-fg-muted dark:text-fg-muted font-mono">{{ $ex->weight }}</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-fg-muted dark:text-fg-muted italic">No exercises added to this routine yet.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        @if($wp->notes)
                                            <div class="p-3 rounded-xl bg-indigo-50/50 dark:bg-indigo-950/20 border border-indigo-100 dark:border-indigo-900/30 text-xs text-fg-muted dark:text-fg-muted mt-3 leading-relaxed">
                                                <span class="font-semibold text-indigo-700 dark:text-indigo-400 block mb-0.5">Trainer Advice:</span>
                                                {{ $wp->notes }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="border-t border-line dark:border-line pt-3 mt-4 flex flex-wrap items-center justify-between gap-2">
                                        @if($member->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode($whatsappWorkoutText) }}" 
                                               target="_blank" 
                                               class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-xs font-semibold border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5 transition-colors">
                                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                <span>Share on WhatsApp</span>
                                            </a>
                                        @else
                                            <div></div>
                                        @endif

                                        <form action="{{ route('app.workouts.delete', $wp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this workout routine?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete Routine</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🏋️
                            </div>
                            <h4 class="font-semibold text-fg dark:text-white text-sm">No Workout Routine Assigned</h4>
                            <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                                {{ $member->first_name }} does not have a customized workout plan assigned yet. Create a tailored split or assign one of the pre-made templates below.
                            </p>
                            <button type="button" 
                                    @click="openCreateWorkout()" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer ops-primary">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Create Custom Routine</span>
                            </button>
                        </div>
                    @endif

                    <!-- Pre-made Workout Templates Quick-Assign -->
                    @if(isset($templateWorkoutPlans) && $templateWorkoutPlans->isNotEmpty())
                        <div class="pt-4 border-t border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-fg dark:text-white text-xs">📋 Quick Assign from Workout Templates</h4>
                                <a href="{{ route('app.workouts.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Manage All Workouts →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($templateWorkoutPlans as $tpl)
                                    <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-3 hover:border-indigo-500/30 transition-all">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-semibold text-fg dark:text-white text-xs leading-snug">{{ $tpl->title }}</h5>
                                                @if($tpl->goal)
                                                    <span class="px-2 py-0.5 rounded text-xs bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-semibold shrink-0">{{ ucwords(str_replace(['_', '-'], ' ', $tpl->goal)) }}</span>
                                                @endif
                                            </div>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 line-clamp-2">
                                                {{ $tpl->notes ?? 'Standard workout split template.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-xs text-fg-muted dark:text-fg-muted font-semibold">
                                                <span class="capitalize">Level: {{ ucfirst(str_replace(['_', '-'], ' ', $tpl->level ?? 'All')) }}</span>
                                                <span>•</span>
                                                <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $tpl->exercises->count() }} exercises</span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="loadWorkoutTemplate({{ Js::from($tpl) }})" 
                                                class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-xs font-semibold transition-all text-center cursor-pointer">
                                            + Assign to {{ $member->first_name }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 6: DIET PLANS -->
                <div x-show="activeTab === 'diets'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span>🥗</span>
                                <span>Assigned Diet & Nutrition Charts</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Meal timings, calorie distribution, and macro targets prescribed for {{ $member->full_name }}</p>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap">
                            <button type="button" 
                                    @click="showAiDietModal = true" 
                                    class="w-full sm:w-auto px-3.5 py-2.5 rounded-xl text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
                                <span>✨</span>
                                <span>AI Diet Generator</span>
                            </button>
                            <button type="button" 
                                    @click="openCreateDiet()" 
                                    class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
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

                                <div class="p-4 sm:p-5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between hover:border-indigo-500/40 transition-all">
                                    <div>
                                        <!-- Header -->
                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2 sm:gap-3 mb-2.5">
                                            <div>
                                                <h4 class="font-semibold text-fg dark:text-white text-base leading-tight">{{ $dp->title }}</h4>
                                                @if($dp->trainer)
                                                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Nutritionist / Coach: <strong class="text-fg dark:text-fg">{{ $dp->trainer->full_name }}</strong></p>
                                                @endif
                                            </div>
                                            <div class="self-start px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-semibold shrink-0 inline-flex items-center gap-1">
                                                <span>🔥</span>
                                                <span>{{ number_format($dp->daily_calories ?? 2000) }} kcal</span>
                                            </div>
                                        </div>

                                        @if($dp->start_date)
                                            <div class="text-xs text-fg-muted dark:text-fg-muted mb-3 font-mono">
                                                Active from {{ $dp->start_date->format('d M Y') }} {{ $dp->end_date ? 'to ' . $dp->end_date->format('d M Y') : '' }}
                                            </div>
                                        @endif

                                        <!-- Macro Target Distribution -->
                                        <div class="p-3.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line mb-3 space-y-2">
                                            <div class="flex items-center justify-between text-xs font-semibold">
                                                <span class="text-fg-muted dark:text-fg-muted text-xs">Daily Macro Targets</span>
                                                <div class="flex items-center gap-2.5 text-xs font-mono">
                                                    <span class="text-rose-600 dark:text-rose-400"><strong>{{ $protein }}g</strong> <span class="text-xs text-fg-muted">Protein</span></span>
                                                    <span class="text-amber-600 dark:text-amber-400"><strong>{{ $carbs }}g</strong> <span class="text-xs text-fg-muted">Carbs</span></span>
                                                    <span class="text-cyan-600 dark:text-cyan-400"><strong>{{ $fat }}g</strong> <span class="text-xs text-fg-muted">Fats</span></span>
                                                </div>
                                            </div>

                                            <div class="h-2 w-full rounded-full bg-raised dark:bg-raised flex overflow-hidden">
                                                <div class="bg-rose-500 h-full" style="width: {{ $pPct }}%" title="Protein: {{ $pPct }}%"></div>
                                                <div class="bg-amber-400 h-full" style="width: {{ $cPct }}%" title="Carbs: {{ $cPct }}%"></div>
                                                <div class="bg-cyan-400 h-full" style="width: {{ $fPct }}%" title="Fats: {{ $fPct }}%"></div>
                                            </div>
                                        </div>

                                        <!-- Meals List -->
                                        <div class="border-t border-line dark:border-line pt-3">
                                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-2">
                                                Meal Schedule ({{ $dp->meals->count() }})
                                            </span>
                                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                                @forelse($dp->meals as $m)
                                                    <div class="p-2.5 sm:p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs">
                                                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-1.5 sm:gap-2">
                                                            <div class="min-w-0 flex-1">
                                                                <div class="font-semibold text-fg dark:text-white flex items-center gap-1.5 flex-wrap">
                                                                    <span class="text-xs px-2 py-0.5 rounded-md bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 font-mono font-semibold">{{ str_replace('_', ' ', $m->meal_type) }}</span>
                                                                    <span>{{ $m->meal_name }}</span>
                                                                </div>
                                                                @if($m->items_description)
                                                                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 leading-relaxed whitespace-pre-line">{{ $m->items_description }}</p>
                                                                @endif
                                                            </div>
                                                            <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center text-right shrink-0 pt-1 sm:pt-0 border-t sm:border-t-0 border-line dark:border-line">
                                                                @if($m->recommended_time)
                                                                    <span class="text-xs font-mono text-fg-muted dark:text-fg-muted block">⏰ {{ substr($m->recommended_time, 0, 5) }}</span>
                                                                @endif
                                                                @if($m->calories)
                                                                    <span class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 block">{{ $m->calories }} kcal</span>
                                                                @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <span class="text-xs text-fg-muted dark:text-fg-muted italic">No scheduled meals added yet.</span>
                                                @endforelse
                                            </div>
                                        </div>

                                        @if($dp->guidelines)
                                            <div class="p-3 rounded-xl bg-emerald-50/50 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/30 text-xs text-fg-muted dark:text-fg-muted mt-3 leading-relaxed">
                                                <span class="font-semibold text-emerald-700 dark:text-emerald-400 block mb-0.5">Hydration & Guidelines:</span>
                                                {{ $dp->guidelines }}
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="border-t border-line dark:border-line pt-3 mt-4 flex flex-wrap items-center justify-between gap-2">
                                        @if($member->phone)
                                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $member->phone) }}?text={{ urlencode($whatsappDietText) }}" 
                                               target="_blank" 
                                               class="px-3.5 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-xs font-semibold border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5 transition-colors">
                                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                                <span>Share on WhatsApp</span>
                                            </a>
                                        @else
                                            <div></div>
                                        @endif

                                        <form action="{{ route('app.diets.delete', $dp->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this diet plan?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Delete Diet</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-xl bg-emerald-50 dark:bg-emerald-600/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl border border-emerald-200 dark:border-emerald-500/20">
                                🥗
                            </div>
                            <h4 class="font-semibold text-fg dark:text-white text-sm">No Diet Chart Assigned</h4>
                            <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                                {{ $member->first_name }} does not have a nutrition plan or calorie chart assigned yet. Generate one instantly with AI or build a custom meal plan.
                            </p>
                            <div class="flex items-center justify-center gap-2 pt-1 flex-wrap">
                                <button type="button" 
                                        @click="showAiDietModal = true" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-white font-semibold text-xs transition-all cursor-pointer">
                                    <span>✨</span>
                                    <span>AI Diet Generator</span>
                                </button>
                                <button type="button" 
                                        @click="openCreateDiet()" 
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition-all cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Create Custom Diet</span>
                                </button>
                            </div>
                        </div>
                    @endif

                    <!-- Pre-made Diet Templates Quick-Assign -->
                    @if(isset($templateDietPlans) && $templateDietPlans->isNotEmpty())
                        <div class="pt-4 border-t border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-fg dark:text-white text-xs">📋 Quick Assign from Diet Templates</h4>
                                <a href="{{ route('app.diets.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Manage All Diets →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($templateDietPlans as $tpl)
                                    <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-3 hover:border-emerald-500/30 transition-all">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-semibold text-fg dark:text-white text-xs leading-snug">{{ $tpl->title }}</h5>
                                                <span class="px-2 py-0.5 rounded text-xs bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 font-semibold shrink-0">🔥 {{ $tpl->daily_calories ?? 2000 }} kcal</span>
                                            </div>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 line-clamp-2">
                                                {{ $tpl->guidelines ?? 'Standard nutritional diet chart.' }}
                                            </p>
                                            <div class="flex items-center gap-2 mt-2 text-xs text-fg-muted dark:text-fg-muted font-semibold font-mono">
                                                <span class="text-rose-600 dark:text-rose-400">{{ $tpl->protein_grams ?? 0 }}g P</span>
                                                <span>•</span>
                                                <span class="text-amber-600 dark:text-amber-400">{{ $tpl->carbs_grams ?? 0 }}g C</span>
                                                <span>•</span>
                                                <span class="text-cyan-600 dark:text-cyan-400">{{ $tpl->fat_grams ?? 0 }}g F</span>
                                            </div>
                                        </div>

                                        <button type="button" 
                                                @click="loadDietTemplate({{ Js::from($tpl) }})" 
                                                class="w-full py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-600 hover:text-white dark:bg-emerald-600/20 dark:hover:bg-emerald-600 text-emerald-700 dark:text-emerald-300 dark:hover:text-white text-xs font-semibold transition-all text-center cursor-pointer">
                                            + Assign to {{ $member->first_name }}
                                        </button>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>

                <!-- TAB 7: CLASSES -->
                <div x-show="activeTab === 'classes'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white">Enrolled Fitness & Studio Classes</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Classes and group session schedules enrolled by this member</p>
                        </div>
                        <button type="button" 
                                @click="openEnrollClass('')" 
                                class="w-full sm:w-auto px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Enroll in Class</span>
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
                                <div class="p-3.5 sm:p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-4 hover:border-indigo-500/40 transition-all">
                                    <div class="flex items-start justify-between gap-3">
                                        <div class="flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-lg shrink-0 border border-indigo-200 dark:border-indigo-500/20">
                                                {{ $icon }}
                                            </div>
                                            <div>
                                                <h4 class="font-semibold text-fg dark:text-white text-sm">{{ $gc?->name ?? 'Group Class' }}</h4>
                                                <div class="flex flex-wrap items-center gap-2 mt-1 text-xs text-fg-muted dark:text-fg-muted font-semibold">
                                                    <span class="px-2 py-0.5 rounded bg-slate-200/80 dark:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold">{{ $gc?->class_type ?? 'Fitness' }}</span>
                                                    @if($gc?->duration_minutes)
                                                        <span>• {{ $gc->duration_minutes }} mins</span>
                                                    @endif
                                                </div>
                                                <div class="mt-2 text-xs font-semibold text-fg-muted dark:text-fg-muted">
                                                    @if($sch)
                                                        <p class="flex items-center gap-1.5">
                                                            <svg class="w-3.5 h-3.5 text-indigo-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                            <span>{{ ucfirst($sch->day_of_week) }} • {{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }} - {{ \Carbon\Carbon::parse($sch->end_time)->format('g:i A') }}</span>
                                                        </p>
                                                    @endif
                                                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">
                                                        Trainer: <span class="text-fg-muted dark:text-fg-muted font-semibold">{{ $sch?->trainer?->full_name ?? ($gc?->instructor?->full_name ?? 'Gym Instructor') }}</span>
                                                    </p>
                                                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">
                                                        Booked on: {{ $booking->booking_date ? $booking->booking_date->format('d M Y') : $booking->created_at->format('d M Y') }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Status Badge -->
                                        @if($booking->status === 'BOOKED')
                                            <span class="px-2.5 py-1 rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 font-semibold text-xs border border-emerald-200 dark:border-emerald-500/20 shrink-0">
                                                Enrolled / Booked
                                            </span>
                                        @elseif($booking->status === 'ATTENDED')
                                            <span class="px-2.5 py-1 rounded-full bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-400 font-semibold text-xs border border-blue-200 dark:border-blue-500/20 shrink-0">
                                                Attended
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-700 dark:text-rose-400 font-semibold text-xs border border-rose-200 dark:border-rose-500/20 shrink-0">
                                                {{ $booking->status }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Actions Footer -->
                                    <div class="flex items-center justify-between pt-3 border-t border-line dark:border-line">
                                        <form action="{{ route('app.classes.booking-status', $booking->id) }}" method="POST" class="flex items-center gap-1.5">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()" class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg-muted dark:text-fg-muted focus:outline-none focus:border-accent">
                                                <option value="BOOKED" {{ $booking->status === 'BOOKED' ? 'selected' : '' }}>Enrolled</option>
                                                <option value="ATTENDED" {{ $booking->status === 'ATTENDED' ? 'selected' : '' }}>Attended</option>
                                                <option value="CANCELLED" {{ $booking->status === 'CANCELLED' ? 'selected' : '' }}>Cancelled</option>
                                                <option value="NO_SHOW" {{ $booking->status === 'NO_SHOW' ? 'selected' : '' }}>No Show</option>
                                            </select>
                                        </form>

                                        <form action="{{ route('app.classes.booking.delete', $booking->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this class enrollment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 dark:text-rose-400 hover:text-rose-700 text-xs font-semibold hover:underline cursor-pointer flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                <span>Remove</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-6 sm:p-8 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center space-y-3">
                            <div class="w-14 h-14 mx-auto rounded-xl bg-indigo-50 dark:bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-2xl border border-indigo-200 dark:border-indigo-500/20">
                                🧘
                            </div>
                            <h4 class="font-semibold text-fg dark:text-white text-sm">No Class Enrollments Yet</h4>
                            <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                                {{ $member->first_name }} has not been enrolled into any group fitness or studio classes yet.
                            </p>
                            <button type="button" 
                                    @click="openEnrollClass('')" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer ops-primary">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Enroll In A Class Now</span>
                            </button>
                        </div>
                    @endif

                    <!-- Available Classes In The Gym -->
                    @if(isset($allGymClasses) && $allGymClasses->isNotEmpty())
                        <div class="pt-4 border-t border-line dark:border-line space-y-3">
                            <div class="flex items-center justify-between">
                                <h4 class="font-semibold text-fg dark:text-white text-xs">Available Gym Classes & Schedules</h4>
                                <a href="{{ route('app.classes.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline font-semibold">Manage All Classes →</a>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($allGymClasses as $gc)
                                    <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col justify-between gap-3">
                                        <div>
                                            <div class="flex items-start justify-between gap-2">
                                                <h5 class="font-semibold text-fg dark:text-white text-xs">{{ $gc->name }}</h5>
                                                <span class="px-2 py-0.5 rounded bg-indigo-50 dark:bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 font-semibold text-xs shrink-0">{{ $gc->class_type }}</span>
                                            </div>
                                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">
                                                Instructor: <span class="text-fg-muted dark:text-fg-muted font-semibold">{{ $gc->instructor?->full_name ?? 'Gym Trainer' }}</span>
                                            </p>
                                            @if($gc->schedules->isNotEmpty())
                                                <div class="mt-2 space-y-1">
                                                    @foreach($gc->schedules as $sch)
                                                        <div class="flex items-center justify-between text-xs text-fg-muted dark:text-fg-muted bg-surface dark:bg-surface px-2.5 py-1 rounded-lg border border-line dark:border-line">
                                                            <span class="font-medium truncate mr-2">{{ ucfirst($sch->day_of_week) }} ({{ \Carbon\Carbon::parse($sch->start_time)->format('g:i A') }})</span>
                                                            <button type="button" 
                                                                    @click="openEnrollClass('{{ $sch->id }}')" 
                                                                    class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold text-xs cursor-pointer shrink-0">
                                                                + Enroll
                                                            </button>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="mt-2">
                                                    <button type="button" 
                                                            @click="openEnrollClass('')" 
                                                            class="w-full py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-600 hover:text-white dark:bg-indigo-600/20 dark:hover:bg-indigo-600 text-indigo-700 dark:text-indigo-300 dark:hover:text-white text-xs font-semibold transition-all text-center cursor-pointer">
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
                <div x-show="activeTab === 'attendance'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4">
                    <h3 class="text-base font-semibold text-fg dark:text-white">Recent Attendance Logs (Last 30 Check-ins)</h3>
                    <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                        <table class="w-full text-left text-xs min-w-[420px]">
                            <thead class="text-xs text-fg-muted dark:text-fg-muted bg-raised dark:bg-slate-950/70 border-b border-line dark:border-line">
                                <tr>
                                    <th class="py-2.5 px-3">Date</th>
                                    <th class="py-2.5 px-3">Check-in Time</th>
                                    <th class="py-2.5 px-3">Check-out Time</th>
                                    <th class="py-2.5 px-3">Method</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                @forelse($member->attendance as $att)
                                    <tr class="hover:bg-raised dark:hover:bg-raised">
                                        <td class="py-3 px-3 font-semibold text-fg dark:text-white">{{ $att->date ? $att->date->format('d M Y') : ($att->check_in ? $att->check_in->format('d M Y') : '—') }}</td>
                                        <td class="py-3 px-3 font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $att->check_in ? $att->check_in->format('g:i A') : '—' }}</td>
                                        <td class="py-3 px-3 font-mono text-fg-muted dark:text-fg-muted">{{ $att->check_out ? $att->check_out->format('g:i A') : '—' }}</td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold">{{ $att->method ?? 'QR Code' }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-fg-muted dark:text-fg-muted text-xs">No attendance check-in records found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 9: MEASUREMENTS -->
                <div x-show="activeTab === 'measurements'" 
                     x-data="{
                         measurements: {{ Js::from(array_values($measurementsList)) }},
                         selectedIndex: 0,
                         get current() {
                             return this.measurements[this.selectedIndex] || {};
                         },
                         formatDate(dateStr) {
                             if (!dateStr) return '—';
                             try {
                                 const parts = dateStr.split('-');
                                 if (parts.length === 3) {
                                     const d = new Date(parts[0], parts[1] - 1, parts[2]);
                                     return d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                                 }
                                 return dateStr;
                             } catch(e) {
                                 return dateStr;
                             }
                         }
                     }"
                     class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4 border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <span>📏</span>
                                <span>Member Body Measurements</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Track body composition and physical transformation over time</p>
                        </div>
                        <button type="button" 
                                @click="showAddMeasurementModal = true" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer ops-primary">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            <span>Log New Measurement</span>
                        </button>
                    </div>

                    @if(empty($latestMeasurement))
                        <!-- Clean Empty State when no measurements logged -->
                        <div class="text-center py-10 sm:py-12 px-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-3">
                            <div class="w-14 h-14 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line flex items-center justify-center mx-auto text-2xl">
                                📏
                            </div>
                            <h4 class="text-sm font-semibold text-fg dark:text-white">No Measurements Logged Yet</h4>
                            <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto">
                                You haven't recorded any baseline body metrics or physical measurements for {{ $member->first_name }} yet.
                            </p>
                            <button type="button" 
                                    @click="showAddMeasurementModal = true" 
                                    class="px-4 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs inline-flex items-center gap-2 cursor-pointer ops-primary">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Log First Measurement</span>
                            </button>
                        </div>
                    @else
                        <!-- Measurements Metric Display -->
                        <div>
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-3 pb-3 border-b border-line dark:border-line">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-xs font-semibold text-fg dark:text-fg">
                                        <span x-text="selectedIndex === 0 ? 'Latest Recorded Metrics' : 'Historical Metrics'">Latest Recorded Metrics</span>
                                    </h4>
                                    <span x-show="selectedIndex === 0" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-400 border border-emerald-300 dark:border-emerald-800">
                                        Latest Record
                                    </span>
                                    <span x-show="selectedIndex > 0" class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-400 border border-amber-300 dark:border-amber-800" x-cloak>
                                        Past Record
                                    </span>
                                    <span class="text-xs font-medium text-fg-muted dark:text-fg-muted">
                                        As of <span class="font-semibold text-fg dark:text-fg" x-text="formatDate(current.date)">{{ !empty($latestMeasurement['date']) ? date('d M Y', strtotime($latestMeasurement['date'])) : '—' }}</span>
                                    </span>
                                </div>

                                <div class="flex items-center gap-2 self-start sm:self-auto">
                                    <label class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Select Date:</label>
                                    <select x-model.number="selectedIndex" 
                                            class="px-3 py-1.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-semibold text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                                        <template x-for="(m, idx) in measurements" :key="idx">
                                            <option :value="idx" x-text="formatDate(m.date) + (idx === 0 ? ' (Latest)' : '')"></option>
                                        </template>
                                    </select>
                                    <button type="button" 
                                            x-show="selectedIndex > 0" 
                                            @click="selectedIndex = 0" 
                                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline px-1 cursor-pointer"
                                            x-cloak>
                                        Reset to Latest
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-2 sm:gap-3">
                                <!-- Metric 1: Weight -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">WEIGHT</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.weight || '—'">{{ $latestMeasurement['weight'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">kg</span>
                                    </p>
                                </div>

                                <!-- Metric 2: Body Fat -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">BODY FAT</span>
                                    <p class="text-base sm:text-lg font-semibold text-indigo-600 dark:text-indigo-400 mt-1">
                                        <span x-text="current.body_fat || '—'">{{ $latestMeasurement['body_fat'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">%</span>
                                    </p>
                                </div>

                                <!-- Metric 3: Chest -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">CHEST</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.chest || '—'">{{ $latestMeasurement['chest'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 4: Shoulders -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">SHOULDERS</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.shoulders || '—'">{{ $latestMeasurement['shoulders'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 5: Waist -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">WAIST</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.waist || '—'">{{ $latestMeasurement['waist'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 6: Hips -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">HIPS</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.hips || '—'">{{ $latestMeasurement['hips'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 7: Left Bicep -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">LEFT BICEP</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.bicep_left || '—'">{{ $latestMeasurement['bicep_left'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 8: Right Bicep -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">RIGHT BICEP</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.bicep_right || '—'">{{ $latestMeasurement['bicep_right'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 9: Left Thigh -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">LEFT THIGH</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.thigh_left || '—'">{{ $latestMeasurement['thigh_left'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 10: Right Thigh -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">RIGHT THIGH</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.thigh_right || '—'">{{ $latestMeasurement['thigh_right'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 11: Calves -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">CALVES</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.calves || '—'">{{ $latestMeasurement['calves'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>

                                <!-- Metric 12: Neck -->
                                <div class="p-2.5 sm:p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">NECK</span>
                                    <p class="text-base sm:text-lg font-semibold text-fg dark:text-white mt-1">
                                        <span x-text="current.neck || '—'">{{ $latestMeasurement['neck'] ?? '—' }}</span> <span class="text-xs font-normal text-fg-muted dark:text-fg-muted">in</span>
                                    </p>
                                </div>
                            </div>

                            <div x-show="current.notes" class="mt-3 p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex items-start gap-2.5 text-xs text-fg-muted dark:text-fg-muted" x-cloak>
                                <span class="text-base">📝</span>
                                <div>
                                    <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">Trainer Remarks & Notes</span>
                                    <p class="mt-0.5 text-fg dark:text-fg font-medium" x-text="current.notes"></p>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Measurement History Table -->
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-semibold text-fg dark:text-fg-muted">Measurement History</h4>
                            <span class="text-xs text-fg-muted dark:text-fg-muted">Click any row to inspect full body metrics</span>
                        </div>
                        
                        <div class="overflow-x-auto -mx-4 sm:mx-0 px-4 sm:px-0">
                            <table class="w-full text-left text-xs min-w-[550px]">
                                <thead class="text-xs text-fg-muted dark:text-fg-muted bg-raised dark:bg-surface border-b border-line dark:border-line">
                                    <tr>
                                        <th class="py-2.5 px-3 font-semibold">Date</th>
                                        <th class="py-2.5 px-3 font-semibold">Weight</th>
                                        <th class="py-2.5 px-3 font-semibold">Chest</th>
                                        <th class="py-2.5 px-3 font-semibold">Waist</th>
                                        <th class="py-2.5 px-3 font-semibold">Arms (L/R)</th>
                                        <th class="py-2.5 px-3 font-semibold">Body Fat</th>
                                        <th class="py-2.5 px-3 font-semibold">Logged By</th>
                                        <th class="py-2.5 px-3 font-semibold text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line dark:divide-line text-fg-muted dark:text-fg-muted">
                                    @forelse($measurementsList as $idx => $m)
                                        <tr @click="selectedIndex = {{ $idx }}" 
                                            class="cursor-pointer transition-colors"
                                            :class="selectedIndex === {{ $idx }} ? 'bg-indigo-50/80 dark:bg-indigo-950/40 ring-1 ring-inset ring-indigo-500/40 font-medium' : 'hover:bg-slate-50 dark:hover:bg-slate-950/50'">
                                            <td class="py-3 px-3 font-semibold text-fg dark:text-white">
                                                <div class="flex items-center gap-2">
                                                    <span>{{ date('d M Y', strtotime($m['date'])) }}</span>
                                                    @if($loop->first)
                                                        <span class="px-1.5 py-0.5 text-xs font-semibold rounded bg-emerald-100 text-emerald-800 dark:bg-emerald-900/60 dark:text-emerald-300">Latest</span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="py-3 px-3 font-semibold">{{ $m['weight'] ? $m['weight'] . ' kg' : '—' }}</td>
                                            <td class="py-3 px-3">{{ $m['chest'] ? $m['chest'] . '"' : '—' }}</td>
                                            <td class="py-3 px-3">{{ $m['waist'] ? $m['waist'] . '"' : '—' }}</td>
                                            <td class="py-3 px-3">{{ ($m['bicep_left'] ?? '—') . '" / ' . ($m['bicep_right'] ?? '—') . '"' }}</td>
                                            <td class="py-3 px-3 text-indigo-600 dark:text-indigo-400 font-semibold">{{ $m['body_fat'] ? $m['body_fat'] . '%' : '—' }}</td>
                                            <td class="py-3 px-3 text-fg-muted dark:text-fg-muted text-xs">{{ $m['logged_by'] ?? 'Staff' }}</td>
                                            <td class="py-3 px-3 text-right">
                                                <button type="button" 
                                                        @click.stop="selectedIndex = {{ $idx }}"
                                                        class="px-2.5 py-1 rounded-lg text-xs font-semibold transition-all cursor-pointer"
                                                        :class="selectedIndex === {{ $idx }} ? 'bg-indigo-600 text-white shadow-sm' : 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300'">
                                                    <span x-text="selectedIndex === {{ $idx }} ? 'Viewing' : 'Inspect'"></span>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="py-6 text-center text-fg-muted dark:text-fg-muted text-xs">
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
                <div x-show="activeTab === 'audit_trail'" class="rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 sm:space-y-6">
                    <div class="flex items-center justify-between border-b border-line dark:border-line pb-4">
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                                <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <span>Transaction Audit Trail</span>
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Complete log of payments, renewals, freezes and status updates for this member</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-lg bg-raised dark:bg-raised border border-line dark:border-line text-xs font-semibold text-fg-muted dark:text-fg-muted">
                            {{ $auditLogs->count() }} Records
                        </span>
                    </div>

                    <!-- Audit Trail List -->
                    <div class="space-y-2.5">
                        @forelse($auditLogs as $log)
                            @php
                                $isPayment = str_contains($log->action, 'payment') || str_contains($log->action, 'fee');
                                $isMember = str_contains($log->action, 'member') || str_contains($log->action, 'enrolled');
                                $isSub = str_contains($log->action, 'subscription') || str_contains($log->action, 'membership');
                                $isFreeze = str_contains($log->action, 'freeze');
                            @endphp
                            <div class="p-3 sm:p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex items-start justify-between gap-3 sm:gap-4 hover:border-line dark:hover:border-line transition-colors">
                                <div class="flex items-start gap-3 min-w-0">
                                    <!-- Simple Category Icon Box -->
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 border
                                        {{ $isPayment ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-500' : ($isSub ? 'bg-amber-500/10 border-amber-500/20 text-amber-500' : ($isFreeze ? 'bg-cyan-500/10 border-cyan-500/20 text-cyan-500' : 'bg-blue-500/10 border-blue-500/20 text-blue-500')) }}">
                                        @if($isPayment)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        @elseif($isSub)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                                        @elseif($isFreeze)
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        @endif
                                    </div>

                                    <!-- Content -->
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="font-semibold text-fg dark:text-white text-xs">
                                                @if($isPayment)
                                                    Payment Collected
                                                @elseif($isSub)
                                                    Subscription Update
                                                @elseif($isFreeze)
                                                    Membership Freeze
                                                @else
                                                    Member Updated
                                                @endif
                                            </span>
                                            <span class="text-xs text-fg-muted dark:text-fg-muted">
                                                • By {{ $log->user->name ?? 'System' }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1 leading-relaxed">
                                            {{ $log->description }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Date & Time -->
                                <div class="text-right shrink-0">
                                    <span class="text-xs font-semibold text-fg dark:text-fg block">{{ $log->created_at->format('d M Y') }}</span>
                                    <span class="text-xs font-mono text-fg-muted dark:text-fg-muted block mt-0.5">{{ $log->created_at->format('h:i A') }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 sm:p-8 text-center bg-raised dark:bg-surface rounded-xl border border-line dark:border-line text-fg-muted dark:text-fg-muted text-xs">
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
            <div @click.away="showAddPaymentModal = false" class="w-full max-w-md rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden p-4 sm:p-6 space-y-4 text-fg dark:text-fg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <h3 class="text-base font-semibold text-fg dark:text-white">Record Member Payment</h3>
                    <button type="button" @click="showAddPaymentModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.collect-fee', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf

                    <!-- Split Mode Toggle -->
                    <div class="flex items-center justify-between pb-1">
                        <span class="font-semibold text-fg-muted dark:text-fg-muted">Payment Breakdown</span>
                        <button type="button" 
                                @click="toggleSplitPayment()" 
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-medium transition-colors cursor-pointer border"
                                :class="isSplitPayment ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:text-slate-300 border-slate-300 dark:border-slate-700'">
                            <span x-text="isSplitPayment ? '✓ Split Payment Active' : 'Split Payment (2 Methods)'"></span>
                        </button>
                    </div>

                    <input type="hidden" name="is_split_payment" :value="isSplitPayment ? '1' : '0'">

                    <!-- Single Mode -->
                    <div x-show="!isSplitPayment" class="space-y-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Amount ({{ $currency }}) *</label>
                            <input type="number" 
                                   name="amount" 
                                   x-model="paymentAmount" 
                                   step="0.01" 
                                   min="0.01" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-semibold text-sm focus:border-accent focus:outline-none">
                        </div>

                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method</label>
                            <select name="payment_method" x-model="paymentMethod" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="card">Card (POS)</option>
                                <option value="netbanking">Net Banking</option>
                                <option value="cheque">Cheque</option>
                                <option value="bank_transfer">Bank Transfer</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Transaction Ref / Cheque No</label>
                            <input type="text" 
                                   name="transaction_reference" 
                                   placeholder="e.g. UPI Ref / Txn ID" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Split Mode -->
                    <div x-show="isSplitPayment" class="p-3.5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-3">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-semibold text-fg-muted dark:text-fg-muted">Split into 2 Payment Modes</span>
                            <span class="text-fg-muted dark:text-fg-muted text-xs">
                                Total Paid: <strong class="text-fg dark:text-white tabular-nums font-semibold">{{ $currency }}<span x-text="Number(paymentAmount || 0).toFixed(2)"></span></strong>
                            </span>
                        </div>

                        <div class="space-y-3">
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
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white tabular-nums text-xs font-medium focus:border-accent focus:outline-none">
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
                                           class="w-full px-3 py-2 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white tabular-nums text-xs font-medium focus:border-accent focus:outline-none">
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

                    <div>
                        <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Notes / Remarks</label>
                        <textarea name="notes" rows="2" placeholder="Fee receipt note..." class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-emerald-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddPaymentModal = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:text-fg-muted font-semibold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold cursor-pointer text-center">Record Payment</button>
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
            <div @click.away="showAddSubscriptionModal = false" class="w-full max-w-lg rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden p-4 sm:p-6 space-y-4 text-fg dark:text-fg max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <h3 class="text-base font-semibold text-fg dark:text-white">Add Membership Subscription</h3>
                    <button type="button" @click="showAddSubscriptionModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.add-subscription', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Membership Plan *</label>
                        <select name="membership_plan_id" 
                                x-model="newSubPlanId" 
                                @change="onNewSubPlanChange()" 
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                            <option value="">Select Plan</option>
                            @foreach($membershipPlans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} ({{ $currency }}{{ number_format($p->price, 0) }} / {{ $p->duration_value }} {{ $p->duration_type }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Start Date *</label>
                            <input type="date" name="start_date" value="{{ now()->toDateString() }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Discount ({{ $currency }})</label>
                            <input type="number" name="discount" x-model="newSubDiscount" min="0" placeholder="0" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Paid Now ({{ $currency }})</label>
                            <input type="number" name="initial_payment_amount" x-model="newSubPaidNow" min="0" placeholder="0" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Payment Method</label>
                            <select name="payment_method" x-model="newSubPaymentMethod" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                <option value="cash">Cash</option>
                                <option value="upi">UPI / QR Code</option>
                                <option value="card">Card (POS)</option>
                                <option value="netbanking">Net Banking</option>
                                <option value="cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddSubscriptionModal = false" class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:text-fg-muted font-semibold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold cursor-pointer text-center ops-primary">Assign Subscription</button>
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
            <div @click.away="showEditModal = false" class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-4xl w-full p-4 sm:p-8 space-y-6 my-4 sm:my-8 max-h-[92vh] overflow-y-auto text-fg dark:text-fg">
                
                <!-- Modal Top Bar -->
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold border border-indigo-200 dark:border-indigo-500/20">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-semibold text-fg dark:text-white">Edit Member Details</h3>
                                <span class="px-2 py-0.5 rounded-md bg-raised dark:bg-raised text-amber-600 dark:text-amber-400 text-xs font-mono font-semibold border border-line dark:border-line">
                                    {{ $member->member_code }}
                                </span>
                            </div>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Update personal details, profile picture, body metrics, and membership status</p>
                        </div>
                    </div>
                    <button type="button" @click="showEditModal = false" class="w-9 h-9 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted hover:text-fg dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
                </div>

                <form action="{{ route('app.members.update', $member->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6 text-xs">
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
                                       value="{{ old('member_code', $member->member_code) }}" 
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
                                       value="{{ old('join_date', $member->join_date ? $member->join_date->format('Y-m-d') : '') }}" 
                                       onclick="this.showPicker && this.showPicker()" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none cursor-pointer">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">First Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="first_name" value="{{ $member->first_name }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Last Name <span class="text-rose-500">*</span></label>
                                <input type="text" name="last_name" value="{{ $member->last_name }}" required class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Phone Number <span class="text-rose-500">*</span></label>
                                <div class="relative flex items-center">
                                    <span class="absolute left-3 text-xs font-semibold text-fg-muted dark:text-fg-muted">+91</span>
                                    <input type="tel" name="phone" value="{{ $member->phone }}" required class="w-full pl-11 pr-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                                </div>
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Alternate Phone</label>
                                <input type="tel" name="alternate_phone" value="{{ $member->metadata['alternate_phone'] ?? '' }}" placeholder="Optional secondary contact" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Email Address</label>
                                <input type="email" name="email" value="{{ $member->email }}" placeholder="member@example.com" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:ring-1 focus:ring-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Gender</label>
                                <select name="gender" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ strtolower($member->gender ?? '') === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ strtolower($member->gender ?? '') === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ strtolower($member->gender ?? '') === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div>
                                <x-dob-picker name="dob" :value="$member->dob ? $member->dob->format('Y-m-d') : ''" />
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Blood Group</label>
                                <select name="blood_group" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    <option value="">Select Blood Group</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bg)
                                        <option value="{{ $bg }}" {{ ($member->metadata['blood_group'] ?? '') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">City</label>
                                <input type="text" name="city" value="{{ $member->metadata['city'] ?? '' }}" placeholder="e.g. Ahmedabad" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Full Address</label>
                                <input type="text" name="address" value="{{ $member->address }}" placeholder="Street address, apartment, locality" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
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
                                                @click="editHeightUnit = 'cm'" 
                                                :class="editHeightUnit === 'cm' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-xs font-semibold rounded transition-colors">cm</button>
                                        <button type="button" 
                                                @click="editHeightUnit = 'ft'" 
                                                :class="editHeightUnit === 'ft' ? 'bg-indigo-600 text-white' : 'text-slate-600 hover:text-slate-900 dark:text-slate-400 dark:hover:text-slate-200'"
                                                class="px-2 py-0.5 text-xs font-semibold rounded transition-colors">ft</button>
                                    </div>
                                    <input type="hidden" name="height_unit" :value="editHeightUnit">
                                </div>
                                <input type="text" 
                                       name="height" 
                                       value="{{ $member->metadata['height'] ?? '' }}" 
                                       :placeholder="editHeightUnit === 'cm' ? 'e.g. 175' : 'e.g. 5.9'" 
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Current Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" value="{{ $member->metadata['weight'] ?? ($latestMeasurement['weight'] ?? '') }}" placeholder="e.g. 72.5" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Target Weight (kg)</label>
                                <input type="number" step="0.1" name="target_weight" value="{{ $member->metadata['target_weight'] ?? '' }}" placeholder="e.g. 68.0" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Primary Fitness Goal</label>
                                <select name="fitness_goal" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
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
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Medical History / Health Notes</label>
                                <input type="text" name="medical_history" value="{{ $member->metadata['medical_history'] ?? '' }}" placeholder="e.g. Asthma, Knee surgery, High BP" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
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
                                <input type="text" name="emergency_contact_name" value="{{ $member->emergency_contact_name }}" placeholder="e.g. Robert Doe" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Emergency Phone Number</label>
                                <input type="tel" name="emergency_contact_phone" value="{{ $member->emergency_contact_phone }}" placeholder="e.g. 9876543210" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
                            </div>
                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Relationship</label>
                                <input type="text" name="emergency_relation" value="{{ $member->metadata['emergency_relation'] ?? '' }}" placeholder="e.g. Spouse / Parent / Sibling" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">
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
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none font-semibold">
                                    <option value="ACTIVE" class="text-emerald-600 dark:text-emerald-400" {{ $member->status === 'ACTIVE' ? 'selected' : '' }}>🟢 Active</option>
                                    <option value="INACTIVE" class="text-fg-muted dark:text-fg-muted" {{ $member->status === 'INACTIVE' ? 'selected' : '' }}>⚪ Inactive</option>
                                    <option value="SUSPENDED" class="text-rose-600 dark:text-rose-400" {{ $member->status === 'SUSPENDED' ? 'selected' : '' }}>🔴 Suspended / Frozen</option>
                                    <option value="EXPIRED" class="text-orange-600 dark:text-orange-400" {{ $member->status === 'EXPIRED' ? 'selected' : '' }}>🟠 Expired</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Branch</label>
                                <select name="branch_id" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                                    @foreach($branches as $b)
                                        <option value="{{ $b->id }}" {{ $member->branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
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
                                    <select name="membership_plan_id" x-model="editPlanId" @change="onEditPlanChange()" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-amber-500 focus:outline-none text-xs font-semibold">
                                        <option value="">-- Keep Current Plan --</option>
                                        @foreach($membershipPlans as $mp)
                                            <option value="{{ $mp->id }}" {{ $activeMembership?->membership_plan_id == $mp->id ? 'selected' : '' }}>{{ $mp->name }} ({{ $currency }}{{ number_format($mp->price, 0) }}) - {{ $mp->duration_value }} {{ $mp->duration_type }}</option>
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
                                           x-model="editStartDate" 
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
                                           x-model="editEndDate" 
                                           onclick="this.showPicker && this.showPicker()" 
                                           class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-amber-500 focus:outline-none text-xs cursor-pointer">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Internal Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional notes regarding medical history or special arrangements" class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none">{{ $member->notes }}</textarea>
                        </div>
                    </div>

                    <!-- Modal Actions Footer -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-4 border-t border-line dark:border-line">
                        <button type="button" @click="showEditModal = false" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold transition-all cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" class="w-full sm:w-auto px-7 py-2.5 rounded-xl text-slate-950 font-semibold transition-all flex items-center justify-center gap-2 cursor-pointer">
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
            <div @click.away="closeEditWebcam()" class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl max-w-lg w-full p-4 sm:p-6 space-y-4 text-fg dark:text-white max-h-[90vh] overflow-y-auto">
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

                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                    <button type="button" @click="closeEditWebcam()" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:text-fg-muted dark:hover:text-white dark:border-line text-xs font-semibold transition-all text-center">
                        Cancel
                    </button>
                    <button type="button" @click="takeEditSnapshot()" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center justify-center gap-2 transition-all cursor-pointer ops-primary">
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
            <div @click.away="showAddMeasurementModal = false" class="w-full max-w-xl rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line overflow-hidden p-4 sm:p-6 space-y-4 max-h-[90vh] overflow-y-auto text-fg dark:text-fg">
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <h3 class="text-base font-semibold text-fg dark:text-white flex items-center gap-2">
                        <span>📏</span>
                        <span>Log Body Measurements</span>
                    </h3>
                    <button type="button" @click="showAddMeasurementModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white">✕</button>
                </div>

                <form action="{{ route('app.members.store-measurement', $member->id) }}" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Measurement Date *</label>
                            <input type="date" name="date" value="{{ date('Y-m-d') }}" required class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Weight (kg)</label>
                            <input type="number" step="0.1" name="weight" placeholder="e.g. 72.5" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Body Fat (%)</label>
                            <input type="number" step="0.1" name="body_fat" placeholder="e.g. 15.5" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Chest (in)</label>
                            <input type="number" step="0.1" name="chest" placeholder="e.g. 38" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Shoulders (in)</label>
                            <input type="number" step="0.1" name="shoulders" placeholder="e.g. 44" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Waist (in)</label>
                            <input type="number" step="0.1" name="waist" placeholder="e.g. 32" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Hips (in)</label>
                            <input type="number" step="0.1" name="hips" placeholder="e.g. 36" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Left Bicep (in)</label>
                            <input type="number" step="0.1" name="bicep_left" placeholder="e.g. 14" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Right Bicep (in)</label>
                            <input type="number" step="0.1" name="bicep_right" placeholder="e.g. 14.2" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Left Thigh (in)</label>
                            <input type="number" step="0.1" name="thigh_left" placeholder="e.g. 22" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Right Thigh (in)</label>
                            <input type="number" step="0.1" name="thigh_right" placeholder="e.g. 22" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Calves (in)</label>
                            <input type="number" step="0.1" name="calves" placeholder="e.g. 15" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Neck (in)</label>
                            <input type="number" step="0.1" name="neck" placeholder="e.g. 15.5" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-fg-muted dark:text-fg-muted mb-1">Notes / Trainer Remarks</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Body fat dropped by 1.2%, improved quad definition..." class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between gap-2.5 pt-2">
                        <button type="button" @click="showAddMeasurementModal = false" class="w-full sm:w-auto px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:text-fg-muted font-semibold transition-all text-center">Cancel</button>
                        <button type="submit" class="w-full sm:w-auto px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold cursor-pointer text-center ops-primary">Save Measurements</button>
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
                    <div class="flex items-center justify-between px-3.5 sm:px-5 py-2.5 sm:py-3 bg-surface dark:bg-surface border border-line dark:border-line rounded-xl">
                        <div class="flex items-center gap-2 text-fg dark:text-white font-semibold text-xs sm:text-sm">
                            <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                            <span>Tax Invoice &amp; Receipt Preview</span>
                        </div>

                        <div class="flex items-center gap-1.5 sm:gap-2">
                            <a :href="'/app/invoices/' + selectedInvoice.id" 
                               target="_blank" 
                               title="Open in new tab"
                               class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-raised dark:text-fg-muted dark:hover:text-white dark:hover:bg-raised transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                            <button type="button" 
                                    @click="showInvoiceModal = false" 
                                    title="Close"
                                    class="p-1.5 rounded-lg text-fg-muted hover:text-fg hover:bg-raised dark:text-fg-muted dark:hover:text-white dark:hover:bg-raised transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>

                    <!-- Action Bar Toolbar (Close, Print, Download PDF) -->
                    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3">
                        <button type="button" 
                                @click="showInvoiceModal = false" 
                                class="px-3.5 sm:px-5 py-1.5 sm:py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-all cursor-pointer">
                            Close
                        </button>

                        <button type="button" 
                                @click="printInvoice(selectedInvoice.id)" 
                                class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-surface hover:bg-raised text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                            <span>Print</span>
                        </button>

                        <a :href="selectedInvoice.whatsapp_url" 
                           target="_blank" 
                           class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs flex items-center gap-1.5 transition-all cursor-pointer">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Share on WhatsApp</span>
                        </a>

                        <a :href="'/app/invoices/' + selectedInvoice.id" 
                           target="_blank" 
                           class="px-4 sm:px-6 py-1.5 sm:py-2 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs flex items-center gap-1.5 transition-all ops-primary">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download PDF</span>
                        </a>
                    </div>

                    <!-- Printable Invoice Sheet (A4 Proportion) -->
                    @php
                        $tenantObj = auth()->user()->tenant ?? ($tenant ?? null);
                        $tenantSettings = $tenantObj->settings ?? [];
                        $invSettings = $tenantSettings['invoice'] ?? [];
                        $gymName = !empty($invSettings['gym_name']) ? $invSettings['gym_name'] : ($tenantObj->name ?? 'Gym');
                        $gymAddress = !empty($invSettings['address']) ? $invSettings['address'] : (!empty($tenantSettings['address']) ? $tenantSettings['address'] : ($member->branch->address ?? ($tenantObj->mainBranch?->address ?? ($tenantObj->address ?? ''))));
                        $gymPhone = !empty($invSettings['phone']) ? $invSettings['phone'] : ($tenantObj->phone ?? '');
                        $gymEmail = !empty($invSettings['email']) ? $invSettings['email'] : ($tenantObj->email ?? '');
                        $gymWeb = !empty($invSettings['website']) ? $invSettings['website'] : ($tenantObj->website ?? '');
                        $invoiceTitle = !empty($invSettings['title']) ? $invSettings['title'] : 'TAX INVOICE';
                        $statusText = !empty($invSettings['status_text']) ? $invSettings['status_text'] : 'PAID IN FULL';
                        $verificationText = !empty($invSettings['verification_text']) ? $invSettings['verification_text'] : 'Verified & Recorded';

                        // Terms
                        $defaultTerms = [
                            "Fees once paid are strictly non-refundable and non-transferable under any circumstances.",
                            "Membership is non-transferable and valid exclusively for the registered individual and specified tenure.",
                            "Members are required to carry clean training footwear, workout towel, and follow gym etiquette at all times.",
                            "Management reserves the right to adjust facility operating hours and enforce safety protocols.",
                            "Any unpaid dues must be cleared on or before the specified due date to maintain uninterrupted facility access.",
                            "All memberships and personal training sessions must be utilized strictly within the stipulated validity period; unutilized sessions or duration will automatically lapse upon expiry.",
                            "Members confirm they are medically fit for physical exercise, and management accepts no liability for any pre-existing health conditions, injuries, or emergencies during workouts.",
                            "Members are solely responsible for their personal belongings, cash, and valuables, and management accepts no liability for any loss, theft, or damage within the facility or lockers.",
                            "Gym equipment, weights, and accessories must be handled with care and re-racked after use, and any deliberate misuse or damage to facility property will be charged to the member.",
                            "Management strictly enforces zero tolerance against misconduct, harassment, or rule violations, which will result in immediate termination of membership without refund."
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
                        $tagline = !empty(trim($invSettings['tagline'] ?? '')) ? $invSettings['tagline'] : '';
                        $sealText = !empty($invSettings['seal_text']) ? $invSettings['seal_text'] : "Authorized Signature / Seal";
                        $sealUrl = $invSettings['seal_url'] ?? null;
                        $signatureUrl = $invSettings['signature_url'] ?? null;
                        $signatoryName = $invSettings['signatory_name'] ?? null;
                    @endphp

                    <div class="invoice-card relative w-full max-w-3xl bg-surface text-fg rounded-xl p-4 sm:p-6 lg:p-7 border border-line flex flex-col justify-between min-h-0 sm:min-h-[1040px] print:min-h-0 mx-auto">
                        
                        <!-- Faded Center Background Logo Watermark -->
                        <div class="absolute inset-0 flex items-center justify-center pointer-events-none opacity-[0.035] select-none z-0">
                            @if(!empty($logoUrl))
                                <img src="{{ $logoUrl }}" alt="" class="w-[320px] sm:w-[400px] h-[320px] sm:h-[400px] object-contain grayscale">
                            @else
                                <div class="text-center font-semibold text-6xl sm:text-8xl text-fg rotate-[-12deg]">
                                    {{ $gymName }}
                                </div>
                            @endif
                        </div>

                        <div class="relative z-10 flex flex-col justify-between h-full min-h-[inherit] flex-1">
                            
                            <!-- MAIN TOP CONTENT: Sections 1 through 5 -->
                            <div class="space-y-3 sm:space-y-3.5">
                                <!-- 1. Header Section: Brand & Invoice Meta -->
                                <div class="flex flex-col sm:flex-row items-start justify-between gap-4 pt-1 border-b border-line pb-3.5">
                                    <div class="flex items-start gap-3.5 max-w-lg min-w-0">
                                        @if(!empty($logoUrl))
                                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl p-1.5 border border-line flex items-center justify-center shrink-0">
                                                <img src="{{ $logoUrl }}" alt="{{ $gymName }}" class="w-full h-full object-contain rounded-xl">
                                            </div>
                                        @else
                                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-xl text-white flex flex-col items-center justify-center font-semibold text-xs tracking-tight shrink-0 p-1 text-center border border-line">
                                                <span class="text-amber-400 text-2xl">🏋️</span>
                                                <span class="text-xs sm:text-xs leading-none font-semibold mt-1 text-fg-muted">GYM</span>
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <h2 class="text-xl sm:text-2xl lg:text-[26px] font-semibold text-slate-950 tracking-tight leading-tight">{{ $gymName }}</h2>
                                            <p class="text-xs text-fg-muted mt-1 leading-snug font-medium line-clamp-2">{{ $gymAddress }}</p>
                                            <div class="flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-fg-muted mt-1.5 font-medium">
                                                <span class="inline-flex items-center gap-1">
                                                    <span class="text-fg-muted">Phone:</span>
                                                    <strong class="text-fg font-semibold">{{ $gymPhone }}</strong>
                                                </span>
                                                @if(!empty($gymEmail))
                                                    <span class="text-fg-muted">|</span>
                                                    <span class="inline-flex items-center gap-1 truncate max-w-[220px]">
                                                        <span class="text-fg-muted">Email:</span>
                                                        <strong class="text-fg font-semibold truncate">{{ $gymEmail }}</strong>
                                                    </span>
                                                @endif
                                            </div>
                                            @if(!empty($gymWeb))
                                                <div class="text-xs text-fg-muted mt-0.5 font-medium">
                                                    <span>Website: <strong class="text-fg font-semibold">{{ $gymWeb }}</strong></span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="sm:text-right flex flex-col items-start sm:items-end shrink-0 w-full sm:w-auto self-start">
                                        <!-- Tax Invoice Badge -->
                                        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-surface text-white">
                                            <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                            <span class="font-semibold text-xs sm:text-sm">{{ $invoiceTitle }}</span>
                                        </div>

                                        <!-- Clean Short Invoice Number -->
                                        <div class="mt-2.5 flex items-center gap-2 sm:justify-end">
                                            <span class="text-xs font-semibold text-fg-muted">Invoice No:</span>
                                            <span class="px-2.5 py-0.5 rounded-md bg-raised border border-line text-slate-950 font-semibold font-mono text-xs sm:text-sm" x-text="selectedInvoice.receipt_no">
                                            </span>
                                        </div>

                                        <!-- Date -->
                                        <div class="mt-1 flex items-center gap-2 sm:justify-end text-xs">
                                            <span class="text-fg-muted font-medium">Date:</span>
                                            <span class="font-semibold text-fg" x-text="selectedInvoice.date"></span>
                                        </div>

                                        <!-- Status -->
                                        <div class="mt-1.5">
                                            <span class="inline-flex items-center gap-1.5 px-3 py-0.5 rounded-full border border-emerald-400 bg-emerald-50 text-emerald-700 text-xs sm:text-xs font-semibold">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                {{ $statusText }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 2. Two Cards Row: Member & Plan Details -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <!-- Card 1: Member Details -->
                                    <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-fg mb-1">
                                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                            <span>MEMBER DETAILS (BILLED TO)</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Full Name:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8 truncate">{{ $member->full_name }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Member ID:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8">{{ $member->member_code }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Phone No:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8">{{ $member->phone }}</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Payment Mode:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="getInvoiceModeText(selectedInvoice)"></span>
                                        </div>
                                    </div>

                                    <!-- Card 2: Membership & Plan Details -->
                                    <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                        <div class="flex items-center gap-1.5 text-xs font-semibold text-fg mb-1">
                                            <span class="w-2.5 h-2.5 rounded-full bg-red-500 inline-block shrink-0"></span>
                                            <span>MEMBERSHIP &amp; PLAN DETAILS</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Package Duration:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.plan_duration || '12 Months'"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Start Date:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.start_date || selectedInvoice.date"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Expiry Date:</span>
                                            <span class="font-semibold text-fg col-span-7 sm:col-span-8" x-text="selectedInvoice.end_date || '-'"></span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-5 sm:col-span-4">Status:</span>
                                            <span class="font-semibold text-emerald-600 col-span-7 sm:col-span-8">Active Membership</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 3. Table -->
                                <div class="w-full overflow-x-auto">
                                    <table class="w-full min-w-[340px] text-left border-collapse">
                                        <thead>
                                            <tr class="bg-surface text-white text-xs sm:text-xs font-semibold">
                                                <th class="py-2.5 px-3 rounded-l-lg w-8 sm:w-10 text-center">#</th>
                                                <th class="py-2.5 px-3">SERVICE / ITEM DESCRIPTION</th>
                                                <th class="py-2.5 px-3 text-center">DURATION</th>
                                                <th class="py-2.5 px-3 text-right">RATE (INR)</th>
                                                <th class="py-2.5 px-3 text-right rounded-r-lg">AMOUNT (INR)</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-line">
                                            <tr class="text-fg">
                                                <td class="py-3 px-3 text-center text-fg-muted font-medium text-xs">1</td>
                                                <td class="py-3 px-3">
                                                    <span class="font-semibold text-slate-950 text-xs sm:text-sm block" x-text="selectedInvoice.plan_name"></span>
                                                </td>
                                                <td class="py-3 px-3 text-center font-medium text-fg-muted text-xs" x-text="selectedInvoice.plan_duration || '12 Months'"></td>
                                                <td class="py-3 px-3 text-right font-medium text-fg-muted text-xs">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                                <td class="py-3 px-3 text-right font-semibold text-slate-950 text-xs sm:text-sm">{{ $currency }}<span x-text="selectedInvoice.amount"></span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 4. Bottom Split: Verification & Summary -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                                    <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                        <div class="text-xs font-semibold text-fg mb-1">
                                            PAYMENT DETAIL &amp; VERIFICATION
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Mode:</span>
                                            <span class="font-semibold text-fg col-span-6 sm:col-span-7" x-text="getInvoiceModeText(selectedInvoice)"></span>
                                        </div>
                                        <template x-for="item in getInvoiceSplitItems(selectedInvoice)" :key="item.method">
                                            <div class="grid grid-cols-12 gap-1 text-xs py-0.5">
                                                <span class="text-fg-muted font-normal col-span-6 sm:col-span-5" x-text="item.method + ':'"></span>
                                                <span class="font-semibold text-fg col-span-6 sm:col-span-7">
                                                    <span x-text="item.amount"></span>
                                                    <span class="text-xs font-normal text-fg-muted ml-1" x-show="item.ref" x-text="item.ref"></span>
                                                </span>
                                            </div>
                                        </template>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Status:</span>
                                            <span class="font-semibold text-emerald-600 col-span-6 sm:col-span-7">Completed</span>
                                        </div>
                                        <div class="grid grid-cols-12 gap-1 text-xs">
                                            <span class="text-fg-muted font-normal col-span-6 sm:col-span-5">Payment Verification:</span>
                                            <span class="font-semibold text-emerald-600 col-span-6 sm:col-span-7">{{ $verificationText }}</span>
                                        </div>
                                    </div>

                                    <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line space-y-1">
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-fg-muted font-normal">Subtotal</span>
                                            <span class="font-medium text-fg">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-fg-muted font-normal">Taxes &amp; Gym Surcharge</span>
                                            <span class="font-normal text-fg-muted">{{ $currency }}0 (Inclusive)</span>
                                        </div>
                                        <div class="flex items-center justify-between pt-1 border-t border-line">
                                            <span class="text-xs sm:text-sm font-semibold text-slate-950">Total Amount</span>
                                            <span class="text-xs sm:text-sm font-semibold text-slate-950">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="flex items-center justify-between text-xs">
                                            <span class="text-fg-muted font-normal">Amount Received</span>
                                            <span class="font-semibold text-emerald-600">{{ $currency }}<span x-text="selectedInvoice.amount"></span></span>
                                        </div>
                                        <div class="mt-1 p-2 rounded-lg bg-emerald-50/80 border border-emerald-200/70 flex items-center justify-between text-emerald-950">
                                            <span class="font-semibold text-xs text-emerald-950">Balance Due</span>
                                            <span class="font-semibold text-xs sm:text-sm text-emerald-600">{{ $currency }}0</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- 5. Terms & Conditions -->
                                <div class="p-3 sm:p-3.5 rounded-xl bg-raised border border-line text-xs text-fg-muted leading-snug space-y-1">
                                    <div class="text-xs font-semibold text-fg mb-1">
                                        TERMS &amp; CONDITIONS
                                    </div>
                                    <ol class="list-decimal pl-4 space-y-0.5 text-xs sm:text-xs text-fg-muted leading-relaxed font-normal">
                                        @foreach($termsList as $term)
                                            <li>{{ $term }}</li>
                                        @endforeach
                                    </ol>
                                </div>
                            </div>

                            <!-- FOOTER: Sections 6 & 7 (Anchored at the bottom with generous whitespace after Terms) -->
                            <div class="mt-auto pt-6 sm:pt-8 space-y-2.5 sm:space-y-3">
                                <!-- 6. Footer Helpline & Seal -->
                                <div class="pt-2.5 sm:pt-3 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-2.5 sm:gap-3">
                                    <div class="text-left space-y-0.5 sm:space-y-1">
                                        <p class="font-semibold text-xs sm:text-sm text-slate-950">{{ $thankYouText }}</p>
                                        <div class="text-xs sm:text-xs text-fg-muted flex flex-wrap items-center gap-x-2 gap-y-0.5 font-normal mt-0.5">
                                            @if(!empty($helpline))<span>Official Helpline: <strong class="text-fg font-medium">{{ $helpline }}</strong></span>@endif
                                            @if(!empty($helpline) && !empty($footerEmail))<span>|</span>@endif
                                            @if(!empty($footerEmail))<span>Email: <strong class="text-fg font-medium">{{ $footerEmail }}</strong></span>@endif
                                            @if(!empty($footerWeb))<span>|</span><span>Web: <strong class="text-fg font-medium">{{ $footerWeb }}</strong></span>@endif
                                        </div>
                                    </div>

                                    <!-- Right: Official Seal / Stamp & Signature -->
                                    <div class="flex flex-col items-center justify-center shrink-0 min-w-[140px] sm:min-w-[160px] text-center">
                                        <div class="relative flex items-center justify-center w-24 h-18 sm:w-28 sm:h-20 select-none mx-auto">
                                            @if(!empty($sealUrl))
                                                <img src="{{ $sealUrl }}" alt="Stamp" class="h-14 w-14 sm:h-16 sm:w-16 object-contain drop-shadow-xs">
                                            @else
                                                <svg class="w-14 h-14 sm:w-16 sm:h-16 text-fg-muted drop-shadow-xs" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
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
                                        <span class="text-xs sm:text-xs font-semibold text-fg-muted mt-1 text-center block w-full">{{ $sealText }}</span>
                                        @if(!empty($signatoryName))
                                            <span class="text-xs sm:text-xs font-semibold text-fg-muted text-center block w-full">{{ $signatoryName }}</span>
                                        @endif
                                    </div>
                                </div>

                                <!-- 7. Tagline -->
                                @if(!empty(trim($tagline)))
                                <div class="pt-2 sm:pt-2.5 border-t border-line text-center">
                                    <p class="text-xs sm:text-xs font-semibold text-slate-950">{{ $tagline }}</p>
                                </div>
                                @endif
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
                <div @click.away="showAddPtModal = false" class="w-full max-w-3xl bg-surface dark:bg-surface border border-line dark:border-line rounded-xl sm:rounded-xl p-4 sm:p-8 space-y-6 text-xs text-fg dark:text-fg relative max-h-[92vh] overflow-y-auto">
                    
                    <!-- Top-Right Close Button -->
                    <button type="button" 
                            @click="showAddPtModal = false" 
                            class="absolute top-4 sm:top-6 right-4 sm:right-6 p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors z-20 cursor-pointer" 
                            title="Close modal">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>

                    <form action="{{ route('app.members.add-pt-package', $member->id) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Header Section: Gym Info & INVOICE -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 border-b border-line dark:border-line pb-5 pr-10">
                            <div>
                                <h2 class="text-base font-semibold text-fg dark:text-white">{{ auth()->user()->tenant->name ?? 'POWERFIT GYM' }}</h2>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">{{ $member->branch->address ?? (auth()->user()->tenant->address ?? '123 Fitness Street, Health City') }}</p>
                                <p class="text-xs text-fg-muted dark:text-fg-muted">Ph: {{ auth()->user()->tenant->phone ?? '080 6940 9814' }}</p>
                                <p class="text-xs font-mono text-fg-muted dark:text-fg-muted">{{ auth()->user()->tenant->metadata['gstin'] ?? 'GSTIN: 27AAACM3025E1ZZ' }}</p>
                            </div>

                            <div class="text-left sm:text-right">
                                <h1 class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400">INVOICE</h1>
                                <div class="flex items-center gap-2 mt-2 sm:justify-end">
                                    <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">Date</span>
                                    <input type="date" 
                                           name="start_date" 
                                           x-model="ptStartDate" 
                                           class="px-3 py-1 rounded-lg bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <!-- BILL TO Card -->
                        <div>
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-1.5">BILL TO</span>
                            <div class="p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-wrap items-center justify-between gap-2">
                                <div>
                                    <h3 class="font-semibold text-fg dark:text-white text-sm">{{ $member->full_name }} ({{ $member->member_code }})</h3>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-500/15 border border-emerald-200 dark:border-emerald-500/30 text-emerald-700 dark:text-emerald-400 font-semibold text-xs">Active</span>
                                        <span class="text-fg-muted dark:text-fg-muted font-mono text-xs">{{ $member->phone }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Item Description Table -->
                        <div class="space-y-2">
                            <!-- Table Header -->
                            <div class="hidden sm:flex items-center gap-3 text-xs font-semibold text-fg-muted dark:text-fg-muted border-b border-line dark:border-line pb-2 px-1">
                                <div class="w-6 shrink-0">#</div>
                                <div class="w-44 shrink-0">ITEM TYPE</div>
                                <div class="flex-1 min-w-0">DESCRIPTION</div>
                                <div class="w-32 shrink-0 text-right">AMOUNT ({{ $currency }})</div>
                            </div>

                            <!-- Line Item Row 1 -->
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-start gap-3 p-3.5 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line">
                                <div class="hidden sm:block w-6 shrink-0 font-semibold text-fg-muted dark:text-fg-muted pt-2.5">1</div>
                                
                                <div class="w-full sm:w-44 shrink-0">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Item Type</label>
                                    <select class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line font-semibold text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                        <option value="pt" selected>Personal Training</option>
                                    </select>
                                </div>

                                <div class="flex-1 min-w-0 space-y-2.5">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Description</label>
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
                                                class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-medium text-xs focus:border-accent focus:outline-none">
                                            <option value="">— Select Personal Training —</option>
                                            <option value="1 Month PT (12 Sessions)">1 Month PT (12 Sessions)</option>
                                            <option value="3 Month PT (36 Sessions)">3 Month PT (36 Sessions)</option>
                                            <option value="6 Month PT (72 Sessions)">6 Month PT (72 Sessions)</option>
                                            <option value="Custom PT Coaching">Custom PT Coaching</option>
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Trainer <span class="text-rose-500 font-semibold">*</span></span>
                                        <select name="trainer_id" 
                                                x-model="ptTrainerId" 
                                                required
                                                class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-accent focus:outline-none">
                                            <option value="">Select Coach / Trainer (Mandatory) *</option>
                                            @foreach($trainers as $t)
                                                <option value="{{ $t->id }}">{{ $t->full_name }} ({{ $t->specialization ?? 'Trainer' }})</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Sessions</span>
                                            <input type="number" 
                                                   name="sessions" 
                                                   x-model="ptSessions" 
                                                   min="1" 
                                                   class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0">Validity</span>
                                            <input type="number" 
                                                   name="validity_days" 
                                                   x-model="ptValidityDays" 
                                                   min="1" 
                                                   class="w-20 px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                            <span class="text-fg-muted dark:text-fg-muted text-xs">days</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-3 pt-0.5 flex-wrap">
                                        <div class="flex items-center gap-2">
                                            <span class="text-fg-muted dark:text-fg-muted font-semibold text-xs shrink-0 w-16">Start</span>
                                            <input type="date" 
                                                   name="start_date_display" 
                                                   x-model="ptStartDate" 
                                                   class="px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-xs focus:border-accent focus:outline-none">
                                        </div>
                                        <span class="text-fg-muted dark:text-fg-muted font-mono text-xs">→ Ends: <strong class="text-fg dark:text-white" x-text="ptEndDate"></strong> (<span x-text="ptValidityDays"></span> days)</span>
                                    </div>
                                </div>

                                <div class="w-full sm:w-32 shrink-0 text-left sm:text-right">
                                    <label class="block sm:hidden text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Amount ({{ $currency }})</label>
                                    <input type="number" 
                                           step="0.01" 
                                           name="amount" 
                                           x-model="ptAmount" 
                                           required 
                                           @input="ptCollectedAmount = ptTotal" 
                                           placeholder="0.00" 
                                           class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono font-semibold text-xs text-left sm:text-right focus:border-accent focus:outline-none">
                                </div>
                            </div>

                            <!-- Add Line Item Button -->
                            <div class="pt-1">
                                <button type="button" 
                                        class="px-3.5 py-1.5 rounded-lg border border-dashed border-indigo-400 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 hover:border-indigo-500 text-xs font-semibold flex items-center gap-1.5 transition-colors cursor-pointer">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                    <span>Add Line Item</span>
                                </button>
                            </div>
                        </div>

                        <!-- Financial Breakdown Summary -->
                        <div class="flex justify-end pt-1">
                            <div class="w-full sm:max-w-xs space-y-2 border-t border-line dark:border-line pt-3 text-xs">
                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Subtotal</span>
                                    <span class="font-semibold text-fg dark:text-white">{{ $currency }}<span x-text="Number(ptAmount || 0).toFixed(2)"></span></span>
                                </div>

                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Discount</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="discount" 
                                               x-model="ptDiscount" 
                                               @input="ptCollectedAmount = ptTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-right text-xs focus:border-accent focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between text-fg-muted dark:text-fg-muted">
                                    <span>Tax</span>
                                    <div class="flex items-center gap-1">
                                        <span>{{ $currency }}</span>
                                        <input type="number" 
                                               step="0.01" 
                                               name="tax" 
                                               x-model="ptTax" 
                                               @input="ptCollectedAmount = ptTotal" 
                                               class="w-24 px-2 py-0.5 rounded bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-right text-xs focus:border-accent focus:outline-none">
                                    </div>
                                </div>

                                <div class="flex items-center justify-between font-semibold text-sm text-fg dark:text-white pt-2 border-t border-line dark:border-line">
                                    <span>TOTAL</span>
                                    <span class="text-base text-indigo-600 dark:text-indigo-400 font-semibold">{{ $currency }}<span x-text="ptTotal.toFixed(2)"></span></span>
                                </div>
                            </div>
                        </div>

                        <!-- AMOUNT TO COLLECT -->
                        <div class="p-4 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-3">
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">AMOUNT TO COLLECT</span>
                            <div class="flex items-center gap-3">
                                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-surface dark:bg-surface border border-emerald-500/40 text-emerald-600 dark:text-emerald-400 font-semibold text-base">
                                    <span>{{ $currency }}</span>
                                    <input type="number" 
                                           step="0.01" 
                                           name="collected_amount" 
                                           x-model="ptCollectedAmount" 
                                           class="w-28 bg-transparent text-emerald-600 dark:text-emerald-400 font-mono font-semibold focus:outline-none">
                                </div>
                                <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold">of {{ $currency }}<span x-text="ptTotal.toFixed(2)"></span></span>
                            </div>

                            <label class="flex items-center gap-2 text-xs text-fg-muted dark:text-fg-muted font-semibold cursor-pointer pt-1">
                                <input type="checkbox" checked class="w-4 h-4 rounded border-line dark:border-line bg-surface dark:bg-surface text-indigo-600 focus:ring-0">
                                <span><strong>Activate package now:</strong> Member can start personal training immediately despite pending dues</span>
                            </label>
                        </div>

                        <!-- PAYMENT METHOD -->
                        <div class="space-y-2">
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">PAYMENT METHOD</span>
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-2.5">
                                <div class="sm:col-span-4">
                                    <select name="payment_method" 
                                            x-model="ptPaymentMethod" 
                                            class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-semibold text-xs focus:border-accent focus:outline-none">
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
                                           class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-mono focus:border-accent focus:outline-none">
                                </div>

                                <div class="sm:col-span-4">
                                    <input type="text" 
                                           name="transaction_reference" 
                                           x-model="ptRef" 
                                           placeholder="Ref / Txn ID (Optional)" 
                                           class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs font-mono focus:border-accent focus:outline-none">
                                </div>

                                <div class="sm:col-span-1">
                                    <button type="button" 
                                            class="w-full h-full py-2 px-2 rounded-xl border border-dashed border-indigo-400 dark:border-indigo-500/40 text-indigo-600 dark:text-indigo-400 text-xs font-semibold whitespace-nowrap hover:bg-raised dark:hover:bg-raised transition-colors flex items-center justify-center cursor-pointer">
                                        + Split
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block mb-1">Notes</span>
                            <textarea name="notes" 
                                      x-model="ptNotes" 
                                      rows="2" 
                                      placeholder="Additional notes..." 
                                      class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                        </div>

                        <!-- Footer Actions -->
                        <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-between border-t border-line dark:border-line pt-4 gap-3">
                            <p class="text-xs text-fg-muted dark:text-fg-muted italic text-center sm:text-left">Thank you for your membership!</p>
                            <div class="flex items-center gap-3 justify-end">
                                <button type="button" 
                                        @click="showAddPtModal = false" 
                                        class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                                    Cancel
                                </button>
                                <button type="submit" 
                                        class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-semibold text-xs flex items-center justify-center gap-1.5 transition-all cursor-pointer">
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
                 class="w-full max-w-lg rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-lg">
                            🧘
                        </div>
                        <div>
                            <h3 class="font-semibold text-fg dark:text-white text-base">Enroll in Group / Studio Class</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Enroll {{ $member->full_name }} into a class schedule</p>
                        </div>
                    </div>
                    <button type="button" @click="showEnrollClassModal = false" class="p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.members.enroll-class', $member->id) }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <!-- Class / Schedule Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Select Class & Schedule *</label>
                        <select name="class_schedule_id" 
                                x-model="selectedScheduleId" 
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
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
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Enrollment / Start Date</label>
                        <input type="date" 
                               name="booking_date" 
                               x-model="enrollBookingDate" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                    </div>

                    <!-- Initial Status -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Enrollment Status</label>
                        <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                            <option value="BOOKED">Booked / Enrolled (Active)</option>
                            <option value="ATTENDED">Attended</option>
                        </select>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" 
                                @click="showEnrollClassModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 ops-primary">
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
                 class="w-full max-w-lg rounded-xl sm:rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-5 relative max-h-[90vh] overflow-y-auto">
                
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-100 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center font-semibold text-lg">
                            ✨
                        </div>
                        <div>
                            <h3 class="font-semibold text-fg dark:text-white text-base">Book Service for {{ $member->full_name }}</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Enroll member into an add-on gym service or assign a locker</p>
                        </div>
                    </div>
                    <button type="button" @click="showBookServiceModal = false" class="p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.services.bookings.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">
                    
                    <!-- Service Selection -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Select Service / Amenity *</label>
                        <select name="gym_service_id" 
                                x-model="selectedServiceId" 
                                @change="updateServiceSelection($event)"
                                required 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
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
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Booking Date *</label>
                            <input type="date" 
                                   name="booking_date" 
                                   x-model="serviceBookingDate" 
                                   required
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Time (Optional)</label>
                            <input type="time" 
                                   name="booking_time" 
                                   x-model="serviceBookingTime" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Amount Paid & Locker Number -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Amount Paid ({{ $currency }})</label>
                            <input type="number" 
                                   step="1" 
                                   min="0"
                                   name="amount_paid" 
                                   x-model="serviceAmountPaid" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono font-semibold text-xs focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Locker Number (If Applicable)</label>
                            <input type="text" 
                                   name="locker_number" 
                                   x-model="serviceLockerNumber" 
                                   placeholder="e.g. L-102"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Notes / Special Instructions</label>
                        <textarea name="notes" 
                                  x-model="serviceNotes" 
                                  rows="2" 
                                  placeholder="Optional booking notes..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" 
                                @click="showBookServiceModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 ops-primary">
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
                 class="w-full max-w-3xl my-8 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 flex items-center justify-center text-lg font-semibold border border-indigo-200 dark:border-indigo-500/30">
                            🏋️
                        </div>
                        <div>
                            <h3 class="font-semibold text-fg dark:text-white text-base">Assign Workout Routine</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Custom workout split and exercises for <strong class="text-fg dark:text-fg">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddWorkoutModal = false" class="p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.workouts.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">

                    <!-- Quick Preset Split Buttons -->
                    <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted">⚡ Quick Split Presets:</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" @click="loadWorkoutPreset('push')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-semibold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Push Day
                            </button>
                            <button type="button" @click="loadWorkoutPreset('pull')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-semibold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Pull Day
                            </button>
                            <button type="button" @click="loadWorkoutPreset('legs')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-semibold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Leg Day
                            </button>
                        </div>
                    </div>

                    <!-- Title & Goal -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Routine Title *</label>
                            <input type="text" 
                                   name="title" 
                                   x-model="workoutTitle" 
                                   required 
                                   placeholder="e.g. 4-Day Hypertrophy Split" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Primary Goal</label>
                            <select name="goal" 
                                    x-model="workoutGoal" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
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
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Experience Level</label>
                            <select name="level" 
                                    x-model="workoutLevel" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Assigned Trainer / Coach</label>
                            <select name="trainer_id" 
                                    x-model="workoutTrainerId" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
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
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   x-model="workoutStartDate" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">End Date (Optional)</label>
                            <input type="date" 
                                   name="end_date" 
                                   x-model="workoutEndDate" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Prescribed Exercises Table -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Prescribed Exercises List</label>
                            <button type="button" 
                                    @click="addWorkoutExercise()" 
                                    class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-600 hover:text-white text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Exercise</span>
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(ex, idx) in workoutExercises" :key="idx">
                                <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-2 relative">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-3">
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][day]'" 
                                                   x-model="ex.day" 
                                                   placeholder="Day (e.g. Day 1)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                        <div class="sm:col-span-9 flex items-center gap-2">
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][exercise_name]'" 
                                                   x-model="ex.exercise_name" 
                                                   required 
                                                   placeholder="Exercise Name (e.g. Barbell Bench Press)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
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
                                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Sets</label>
                                            <input type="number" 
                                                   :name="'exercises[' + idx + '][sets]'" 
                                                   x-model="ex.sets" 
                                                   min="1" 
                                                   class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Reps</label>
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][reps]'" 
                                                   x-model="ex.reps" 
                                                   placeholder="e.g. 8-10" 
                                                   class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Weight / Load</label>
                                            <input type="text" 
                                                   :name="'exercises[' + idx + '][weight]'" 
                                                   x-model="ex.weight" 
                                                   placeholder="e.g. 50 kg" 
                                                   class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Rest (Sec)</label>
                                            <input type="number" 
                                                   :name="'exercises[' + idx + '][rest_seconds]'" 
                                                   x-model="ex.rest_seconds" 
                                                   placeholder="60" 
                                                   class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                    </div>

                                    <div>
                                        <input type="text" 
                                               :name="'exercises[' + idx + '][notes]'" 
                                               x-model="ex.notes" 
                                               placeholder="Execution cues / tempo (optional)..." 
                                               class="w-full px-2.5 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg-muted dark:text-fg-muted placeholder-fg-subtle dark:placeholder-fg-subtle focus:outline-none focus:border-accent">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Coach Notes & Warmup Instructions</label>
                        <textarea name="notes" 
                                  x-model="workoutNotes" 
                                  rows="2" 
                                  placeholder="Warm-up thoroughly, focus on progressive overload, drink enough water..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" 
                                @click="showAddWorkoutModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on font-semibold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 ops-primary">
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
                 class="w-full max-w-3xl my-8 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-4 sm:p-6 space-y-4 max-h-[92vh] overflow-y-auto">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-semibold border border-emerald-200 dark:border-emerald-500/30">
                            🥗
                        </div>
                        <div>
                            <h3 class="font-semibold text-fg dark:text-white text-base">Assign Diet & Nutrition Chart</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Meal schedule & macros for <strong class="text-fg dark:text-fg">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAddDietModal = false" class="p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form action="{{ route('app.diets.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="member_id" value="{{ $member->id }}">

                    <!-- Title & Calories -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Diet Title *</label>
                            <input type="text" 
                                   name="title" 
                                   x-model="dietTitle" 
                                   required 
                                   placeholder="e.g. Lean Muscle Hypertrophy Diet" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Daily Calories (kcal)</label>
                            <input type="number" 
                                   step="50" 
                                   name="daily_calories" 
                                   x-model="dietCalories" 
                                   placeholder="2000" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-mono font-semibold focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Macro Splits -->
                    <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-2">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">Target Daily Macronutrients</label>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-rose-600 dark:text-rose-400">Protein (g)</label>
                                <input type="number" 
                                       name="protein_grams" 
                                       x-model="dietProtein" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-rose-600 dark:text-rose-400 focus:outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-amber-600 dark:text-amber-400">Carbs (g)</label>
                                <input type="number" 
                                       name="carbs_grams" 
                                       x-model="dietCarbs" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-amber-600 dark:text-amber-400 focus:outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-cyan-600 dark:text-cyan-400">Fat (g)</label>
                                <input type="number" 
                                       name="fat_grams" 
                                       x-model="dietFat" 
                                       class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-cyan-600 dark:text-cyan-400 focus:outline-none focus:border-accent">
                            </div>
                        </div>
                    </div>

                    <!-- Coach & Dates -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Nutritionist / Trainer</label>
                            <select name="trainer_id" 
                                    x-model="dietTrainerId" 
                                    class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                                <option value="">-- None / Self --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Start Date</label>
                            <input type="date" 
                                   name="start_date" 
                                   x-model="dietStartDate" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">End Date (Optional)</label>
                            <input type="date" 
                                   name="end_date" 
                                   x-model="dietEndDate" 
                                   class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                        </div>
                    </div>

                    <!-- Scheduled Meals List -->
                    <div class="pt-2">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-semibold text-fg-muted dark:text-fg-muted">Scheduled Meals & Portions</label>
                            <button type="button" 
                                    @click="addDietMeal()" 
                                    class="px-2.5 py-1 rounded-lg bg-emerald-50 dark:bg-emerald-600/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-600 hover:text-white text-xs font-semibold transition-all flex items-center gap-1 cursor-pointer">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Meal</span>
                            </button>
                        </div>

                        <div class="space-y-2.5">
                            <template x-for="(meal, idx) in dietMeals" :key="idx">
                                <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line space-y-2 relative">
                                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                        <div class="sm:col-span-4">
                                            <select :name="'meals[' + idx + '][meal_type]'" 
                                                    x-model="meal.meal_type" 
                                                    class="w-full px-2 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
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
                                                   class="w-full px-2 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono text-fg dark:text-white focus:outline-none focus:border-accent">
                                        </div>
                                        <div class="sm:col-span-5 flex items-center gap-2">
                                            <input type="text" 
                                                   :name="'meals[' + idx + '][meal_name]'" 
                                                   x-model="meal.meal_name" 
                                                   required 
                                                   placeholder="Meal Title (e.g. Oats & Eggs)" 
                                                   class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold text-fg dark:text-white focus:outline-none focus:border-accent">
                                            <button type="button" 
                                                    @click="removeDietMeal(idx)" 
                                                    class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 transition-colors shrink-0 cursor-pointer" 
                                                    title="Remove Meal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="space-y-2">
                                        <div>
                                            <textarea rows="2" 
                                                      :name="'meals[' + idx + '][items_description]'" 
                                                      x-model="meal.items_description" 
                                                      placeholder="Food items & quantities (e.g. 3 Boiled Eggs, 2 Toast, 1 Glass Milk, 1 Apple)" 
                                                      class="w-full px-2.5 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:outline-none focus:border-accent leading-relaxed"></textarea>
                                        </div>
                                        <div class="grid grid-cols-4 gap-1.5">
                                            <div>
                                                <input type="number" 
                                                       :name="'meals[' + idx + '][calories]'" 
                                                       x-model="meal.calories" 
                                                       placeholder="Kcal (450)" 
                                                       class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-emerald-600 dark:text-emerald-400 focus:outline-none focus:border-accent text-center" title="Calories">
                                                <span class="text-xs text-fg-muted block text-center mt-0.5 font-semibold">Kcal</span>
                                            </div>
                                            <div>
                                                <input type="number" 
                                                       :name="'meals[' + idx + '][protein_grams]'" 
                                                       x-model="meal.protein_grams" 
                                                       placeholder="Prot (30)" 
                                                       class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-sky-600 dark:text-sky-400 focus:outline-none focus:border-accent text-center" title="Protein">
                                                <span class="text-xs text-sky-500 block text-center mt-0.5 font-semibold">Protein (g)</span>
                                            </div>
                                            <div>
                                                <input type="number" 
                                                       :name="'meals[' + idx + '][carbs_grams]'" 
                                                       x-model="meal.carbs_grams" 
                                                       placeholder="Carb (45)" 
                                                       class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-amber-600 dark:text-amber-400 focus:outline-none focus:border-accent text-center" title="Carbs">
                                                <span class="text-xs text-amber-500 block text-center mt-0.5 font-semibold">Carbs (g)</span>
                                            </div>
                                            <div>
                                                <input type="number" 
                                                       :name="'meals[' + idx + '][fat_grams]'" 
                                                       x-model="meal.fat_grams" 
                                                       placeholder="Fat (12)" 
                                                       class="w-full px-2 py-1 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-mono font-semibold text-rose-600 dark:text-rose-400 focus:outline-none focus:border-accent text-center" title="Fat">
                                                <span class="text-xs text-rose-500 block text-center mt-0.5 font-semibold">Fat (g)</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Hydration & Dietary Advice</label>
                        <textarea name="guidelines" 
                                  x-model="dietGuidelines" 
                                  rows="2" 
                                  placeholder="Drink 3-4 liters of water daily, avoid added refined sugar..." 
                                  class="w-full px-3.5 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle text-xs focus:border-accent focus:outline-none"></textarea>
                    </div>

                    <!-- Modal Actions -->
                    <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                        <button type="button" 
                                @click="showAddDietModal = false" 
                                class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5">
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
                 class="w-full max-w-lg my-8 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line p-5 sm:p-6 space-y-4">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-line dark:border-line pb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl text-white flex items-center justify-center text-lg font-semibold">
                            ✨
                        </div>
                        <div>
                            <h3 class="font-semibold text-fg dark:text-white text-base">Instant AI Diet Generator</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">Scientific nutrition plan for <strong class="text-fg dark:text-fg">{{ $member->full_name }}</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showAiDietModal = false" class="p-2 rounded-xl text-fg-muted hover:text-fg-muted dark:hover:text-white hover:bg-raised dark:hover:bg-raised transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Error Alert -->
                <div x-show="aiDietError" x-cloak class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs font-semibold" x-text="aiDietError"></div>

                <div class="space-y-3.5">
                    <!-- Goal -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Primary Fitness Goal</label>
                        <select x-model="aiDietGoal" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                            <option value="weight_loss">Fat Loss & Calorie Deficit</option>
                            <option value="muscle_gain">Lean Muscle Hypertrophy & Bulking</option>
                            <option value="maintenance">Body Recomposition & Maintenance</option>
                            <option value="endurance">Cardio & Athletic Endurance</option>
                        </select>
                    </div>

                    <!-- Diet Preference -->
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Dietary Preference</label>
                        <select x-model="aiDietPreference" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
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
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Daily Target (kcal)</label>
                            <input type="number" 
                                   step="50" 
                                   x-model="aiDietCalories" 
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-mono font-semibold focus:border-accent focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Meals Per Day</label>
                            <select x-model="aiDietMealsPerDay" class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs font-semibold focus:border-accent focus:outline-none">
                                <option value="3">3 Meals (Breakfast, Lunch, Dinner)</option>
                                <option value="4" selected>4 Meals (+ Evening Snack)</option>
                                <option value="5">5 Meals (+ Pre/Post Workout)</option>
                                <option value="6">6 Small Frequent Meals</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Modal Actions -->
                <div class="flex flex-col-reverse sm:flex-row items-stretch sm:items-center justify-end gap-2.5 pt-3 border-t border-line dark:border-line">
                    <button type="button" 
                            @click="showAiDietModal = false" 
                            :disabled="aiDietLoading"
                            class="w-full sm:w-auto px-4 py-2.5 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted border border-line dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted dark:border-line font-semibold text-xs transition-colors cursor-pointer text-center">
                        Cancel
                    </button>
                    <button type="button" 
                            @click="generateAiDiet()" 
                            :disabled="aiDietLoading"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl text-white font-semibold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 disabled:opacity-50">
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

        <!-- ========================================================================= -->
        <!-- MODAL: CANCEL MEMBERSHIP SUBSCRIPTION -->
        <!-- ========================================================================= -->
        <div x-show="showCancelMembershipModal" 
             class="fixed inset-0 z-[100] overflow-y-auto bg-slate-950/80 backdrop-blur-sm flex items-center justify-center p-4" 
             x-cloak>
            <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl max-w-md w-full p-6 space-y-4 text-left" 
                 @click.away="showCancelMembershipModal = false">
                <div class="flex items-center gap-3 border-b border-line dark:border-line pb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 flex items-center justify-center font-semibold shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white">Cancel Membership</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Cancel package &amp; remove from active dashboard fees</p>
                    </div>
                </div>

                <form action="{{ route('app.members.cancel-membership', $member->id) }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="membership_id" :value="cancelSubId">

                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400 text-xs">
                        <span>Cancelling package for <strong>{{ $member->full_name }}</strong>: <span class="font-semibold underline" x-text="cancelSubPlanName"></span>. This will set member status to <strong>Inactive</strong>.</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">
                            Reason for Cancellation <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" 
                               name="reason" 
                               required 
                               placeholder="e.g. Package transferred to husband Minesh Gajjar (ARM045)" 
                               class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white text-xs focus:border-rose-500 focus:outline-none">
                    </div>

                    <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" name="void_fee" value="1" checked class="mt-0.5 rounded border-line text-rose-600 focus:ring-rose-500">
                            <div class="text-xs">
                                <span class="font-semibold text-fg dark:text-white block">Void / Transfer Fee from Dashboard</span>
                                <span class="text-fg-muted dark:text-fg-muted text-xs block mt-0.5">Check this so this package's fee is NOT counted or double-counted in your gym dashboard revenue.</span>
                            </div>
                        </label>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-line dark:border-line">
                        <button type="button" @click="showCancelMembershipModal = false" class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted text-xs font-semibold transition-all cursor-pointer">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-xs font-semibold transition-all cursor-pointer">Confirm Cancellation</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Set / Reset Member Password Modal -->
        <div x-show="showPasswordModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/70 backdrop-blur-sm"
             @keydown.escape.window="showPasswordModal = false">
            <div class="relative w-full max-w-md bg-surface dark:bg-surface rounded-xl border border-line dark:border-line p-6 sm:p-7 space-y-5"
                 @click.away="showPasswordModal = false">
                
                <!-- Header -->
                <div class="flex items-center justify-between pb-3 border-b border-line dark:border-line">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-semibold">
                            🔑
                        </div>
                        <div>
                            <h3 class="text-base font-semibold text-fg dark:text-white leading-tight">
                                Set Portal Login Password
                            </h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted">
                                Set or change member's personal login password
                            </p>
                        </div>
                    </div>
                    <button type="button" @click="showPasswordModal = false" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-lg cursor-pointer">
                        &times;
                    </button>
                </div>

                <!-- Member Info Badge -->
                <div class="p-3 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line flex items-center justify-between text-xs">
                    <div>
                        <span class="font-semibold text-fg dark:text-white block">{{ $member->full_name }}</span>
                        <span class="text-xs text-fg-muted dark:text-fg-muted font-mono mt-0.5 block">
                            ID: {{ $member->member_code }} &bull; Phone: {{ $member->phone }}
                        </span>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                        Active Account
                    </span>
                </div>

                <!-- Success Alert -->
                <template x-if="passwordSuccessMsg">
                    <div class="p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-800 dark:text-emerald-300 text-xs flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span>✅</span>
                            <span class="font-semibold" x-text="passwordSuccessMsg"></span>
                        </div>
                    </div>
                </template>

                <!-- Error Alert -->
                <template x-if="passwordErrorMsg">
                    <div class="p-3.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-800 dark:text-rose-300 text-xs flex items-center gap-2">
                        <span>⚠️</span>
                        <span class="font-semibold" x-text="passwordErrorMsg"></span>
                    </div>
                </template>

                <!-- Form Content -->
                <div class="space-y-4">
                    <!-- Password Field -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted">
                            New Password <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <input :type="showPasswordText ? 'text' : 'password'" 
                                   x-model="passwordInput" 
                                   required 
                                   placeholder="Enter new password (min. 4 characters)"
                                   class="w-full pl-3.5 pr-10 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-white placeholder-fg-subtle text-xs focus:border-amber-500 focus:outline-none font-mono">
                            <button type="button" 
                                    @click="showPasswordText = !showPasswordText" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-fg-muted hover:text-fg-muted dark:hover:text-fg cursor-pointer">
                                <span x-show="!showPasswordText" class="text-xs">👁️</span>
                                <span x-show="showPasswordText" class="text-xs">🙈</span>
                            </button>
                        </div>
                    </div>

                    <!-- Quick Preset Buttons -->
                    <div class="space-y-1.5">
                        <span class="text-xs font-semibold text-fg-muted dark:text-fg-muted block">
                            Quick 1-Click Presets:
                        </span>
                        <div class="flex flex-wrap gap-2">
                            <button type="button" 
                                    @click="passwordInput = '123456'"
                                    class="px-2.5 py-1.5 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg dark:text-fg text-xs font-semibold border border-line dark:border-line transition-colors flex items-center gap-1 cursor-pointer">
                                <span>Default:</span>
                                <span class="font-mono text-amber-600 dark:text-amber-400 font-semibold">123456</span>
                            </button>
                            <button type="button" 
                                    @click="passwordInput = '{{ preg_replace('/[^0-9]/', '', $member->phone ?? '') }}'"
                                    class="px-2.5 py-1.5 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg dark:text-fg text-xs font-semibold border border-line dark:border-line transition-colors flex items-center gap-1 cursor-pointer">
                                <span>Mobile:</span>
                                <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ substr(preg_replace('/[^0-9]/', '', $member->phone ?? ''), -10) }}</span>
                            </button>
                            <button type="button" 
                                    @click="passwordInput = Math.floor(100000 + Math.random() * 900000).toString()"
                                    class="px-2.5 py-1.5 rounded-lg bg-raised hover:bg-slate-200 dark:bg-raised dark:hover:bg-slate-700 text-fg dark:text-fg text-xs font-semibold border border-line dark:border-line transition-colors flex items-center gap-1 cursor-pointer">
                                <span>🎲 Random 6-Digit</span>
                            </button>
                        </div>
                    </div>

                    <!-- WhatsApp Notification Toggle -->
                    <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line">
                        <label class="flex items-start gap-2.5 cursor-pointer">
                            <input type="checkbox" x-model="passwordSendWhatsapp" class="mt-0.5 rounded border-line text-emerald-600 focus:ring-emerald-500">
                            <div class="text-xs">
                                <span class="font-semibold text-fg dark:text-white flex items-center gap-1.5">
                                    <span>Send Credentials to Member on WhatsApp</span>
                                    <span class="text-emerald-600 text-xs font-semibold bg-emerald-500/10 px-1.5 py-0.5 rounded">Auto</span>
                                </span>
                                <span class="text-fg-muted dark:text-fg-muted text-xs block mt-0.5">
                                    Opens WhatsApp chat with login link, member ID, and their new password pre-written.
                                </span>
                            </div>
                        </label>
                    </div>

                    <!-- Footer Action Buttons -->
                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-line dark:border-line">
                        <button type="button" 
                                @click="showPasswordModal = false" 
                                class="px-4 py-2 rounded-xl bg-raised hover:bg-slate-200 text-fg-muted dark:bg-raised dark:hover:bg-slate-700 dark:text-fg-muted text-xs font-semibold transition-all cursor-pointer">
                            Close
                        </button>
                        <button type="button" 
                                @click="submitMemberPassword()" 
                                :disabled="passwordSubmitting || !passwordInput"
                                class="px-5 py-2 rounded-xl text-slate-950 text-xs font-semibold transition-all flex items-center gap-1.5 cursor-pointer disabled:opacity-50">
                            <span x-show="passwordSubmitting" class="animate-spin text-xs">⏳</span>
                            <span x-show="!passwordSubmitting">Save &amp; Update Password</span>
                            <span x-show="passwordSubmitting">Saving...</span>
                        </button>
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>