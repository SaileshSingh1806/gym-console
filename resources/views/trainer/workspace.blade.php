<x-app-layout :header="$tenant->name ?? 'Trainer Workspace'">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $activeTab = $activeTab ?? 'today';
        $gymLogo = $tenant->logo_url;
        $resolvedLogo = $gymLogo ? (\Illuminate\Support\Str::startsWith($gymLogo, ['http://', 'https://', '/']) ? $gymLogo : asset('storage/' . $gymLogo)) : null;
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        memberSearch: '',
        sessionStatus: '{{ $sessionStatus ?? 'all' }}',
        
        // Member Detail Sheet
        selectedMember: null,
        showMemberSheet: false,
        
        // 2-Step QR Session Logger State
        showLoggerModal: false,
        loggerStep: 'form', // 'form' or 'qr'
        loggerSubmitting: false,
        loggerPollingInterval: null,
        loggerError: '',
        
        loggerForm: {
            member_id: '',
            member_pt_package_id: '',
            trainer_id: '{{ $trainer?->id ?? '' }}',
            duration_minutes: 60,
            focus_area: 'Strength Training',
            notes: ''
        },
        
        loggerSession: {
            id: null,
            token: '',
            confirmUrl: '',
            otpCode: '',
            memberName: '',
            trainerName: '{{ addslashes($trainer?->full_name ?? '') }}',
            packageName: '',
            remainingSessions: 0,
            duration: 60,
            status: 'PENDING',
            expiresAt: null
        },

        focusTags: ['Strength', 'HIIT & Cardio', 'Core & Mobility', 'Chest & Arms', 'Leg Day', 'Full Body'],

        // Measurement Logging State
        showMeasurementModal: false,
        measurementSubmitting: false,
        showMeasHistory: false,
        measurementForm: {
            date: '{{ now()->toDateString() }}',
            weight: '',
            body_fat: '',
            waist: '',
            chest: '',
            bicep: '',
            hips: '',
            thighs: '',
            shoulders: '',
            calves: '',
            neck: '',
            custom_metrics: [],
            notes: ''
        },

        // Workout / Diet Plan Assignment State
        showAssignModal: false,
        assignType: 'workout', // 'workout' or 'diet'
        planSubmitting: false,
        showWorkoutDetails: false,
        showDietDetails: false,

        workoutPresets: {{ Js::from($workoutPresets ?? []) }},
        dietPresets: {{ Js::from($dietPresets ?? []) }},

        // Dynamic Workout Builder
        workoutBuilder: {
            preset_id: 'ppl_4day',
            title: '4-Day Push / Pull / Legs Split',
            goal: 'muscle_gain',
            level: 'intermediate',
            exercises: [],
            newDay: 'day_1',
            newName: '',
            newSets: 3,
            newReps: '10-12',
            newRest: 60,
            newNotes: ''
        },

        // Dynamic Diet Builder
        dietBuilder: {
            preset_id: 'muscle_gain_2400',
            title: 'High Protein Muscle Gain (2,400 kcal)',
            daily_calories: 2400,
            protein_grams: 160,
            carbs_grams: 260,
            fat_grams: 65,
            meals: [],
            editingIndex: -1,
            newType: 'breakfast',
            newTime: '08:00',
            newName: 'Meal 1: Breakfast',
            newDesc: '',
            newCalories: '',
            newProtein: '',
            newCarbs: '',
            newFat: ''
        },

        get dietTotals() {
            const meals = this.dietBuilder?.meals || [];
            return meals.reduce((acc, m) => {
                acc.calories += Number(m.calories) || 0;
                acc.protein += Number(m.protein) || 0;
                acc.carbs += Number(m.carbs) || 0;
                acc.fat += Number(m.fat) || 0;
                return acc;
            }, { calories: 0, protein: 0, carbs: 0, fat: 0 });
        },

        assignedMembersList: {{ Js::from($assignedMembers->map(function($m) {
            $meta = $m->metadata ?? [];
            $meas = $meta['measurements'] ?? [];
            $latestWeight = !empty($meas) && isset($meas[0]['weight']) ? $meas[0]['weight'] : null;
            $activePkg = $m->memberPtPackages->first();
            $hasRenewalDue = $activePkg && $activePkg->remaining_sessions <= 2;

            return [
                'id' => $m->id,
                'name' => $m->full_name,
                'initials' => strtoupper(substr($m->first_name, 0, 1) . substr($m->last_name ?? '', 0, 1)),
                'phone' => !empty($m->phone) ? substr($m->phone, 0, 3) . ' •••• ' . substr($m->phone, -3) : '',
                'raw_phone' => preg_replace('/[^0-9]/', '', (string)$m->phone),
                'code' => $m->member_code,
                'branch' => $m->branch?->name ?? '',
                'has_renewal_due' => $hasRenewalDue,
                'latest_weight' => $latestWeight,
                'active_workout' => $m->activeWorkoutPlan ? [
                    'id' => $m->activeWorkoutPlan->id,
                    'title' => $m->activeWorkoutPlan->title,
                    'goal' => $m->activeWorkoutPlan->goal ?? 'general_fitness',
                    'level' => $m->activeWorkoutPlan->level ?? 'intermediate',
                    'exercises_count' => $m->activeWorkoutPlan->exercises ? $m->activeWorkoutPlan->exercises->count() : 0,
                    'exercises' => $m->activeWorkoutPlan->exercises ? $m->activeWorkoutPlan->exercises->map(fn($e) => [
                        'day' => $e->day,
                        'name' => $e->exercise_name,
                        'sets' => $e->sets,
                        'reps' => $e->reps,
                        'rest' => $e->rest_seconds,
                    ]) : [],
                ] : null,
                'active_diet' => $m->activeDietPlan ? [
                    'id' => $m->activeDietPlan->id,
                    'title' => $m->activeDietPlan->title,
                    'calories' => $m->activeDietPlan->daily_calories,
                    'protein' => $m->activeDietPlan->protein_grams,
                    'carbs' => $m->activeDietPlan->carbs_grams,
                    'fat' => $m->activeDietPlan->fat_grams,
                    'guidelines' => $m->activeDietPlan->guidelines,
                    'meals_count' => $m->activeDietPlan->meals ? $m->activeDietPlan->meals->count() : 0,
                    'meals' => $m->activeDietPlan->meals ? $m->activeDietPlan->meals->map(fn($ml) => [
                        'type' => $ml->meal_type,
                        'time' => $ml->recommended_time ? substr((string)$ml->recommended_time, 0, 5) : '',
                        'name' => $ml->meal_name,
                        'description' => $ml->items_description,
                        'calories' => $ml->calories,
                        'protein' => $ml->protein_grams,
                        'carbs' => $ml->carbs_grams,
                        'fat' => $ml->fat_grams,
                    ]) : [],
                ] : null,
                'measurements' => array_slice($meas, 0, 25),
                'active_packages' => $m->memberPtPackages->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->package_name,
                    'total' => $p->total_sessions,
                    'used' => $p->used_sessions,
                    'remaining' => $p->remaining_sessions,
                    'days_remaining' => $p->days_remaining,
                    'end_date' => $p->end_date ? $p->end_date->format('d M Y') : '',
                ]),
                'recent_sessions' => $m->ptSessions->map(fn($s) => [
                    'date' => $s->session_date ? $s->session_date->format('d M Y') : '',
                    'duration' => $s->duration_minutes,
                    'focus' => $s->focus_area ?? 'PT Workout',
                    'status' => $s->status,
                ])
            ];
        })) }},

        setTab(newTab) {
            this.tab = newTab;
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                if (newTab === 'today') {
                    url.searchParams.delete('tab');
                } else {
                    url.searchParams.set('tab', newTab);
                }
                window.history.replaceState(null, '', url.toString());
            }
        },

        openMemberDetail(member) {
            this.selectedMember = member;
            this.showMemberSheet = true;
        },

        openLogger(memberId = null, packageId = null) {
            this.loggerStep = 'form';
            this.loggerError = '';
            this.stopPolling();
            this.showMemberSheet = false;
            
            this.loggerForm.member_id = memberId ? String(memberId) : '';
            this.loggerForm.member_pt_package_id = packageId ? String(packageId) : '';
            this.loggerForm.trainer_id = '{{ $trainer?->id ?? '' }}';
            this.loggerForm.duration_minutes = 60;
            this.loggerForm.focus_area = 'Strength Training';
            this.loggerForm.notes = '';

            if (memberId && !packageId) {
                this.onMemberSelect();
            }

            this.showLoggerModal = true;
        },

        onMemberSelect() {
            const member = this.assignedMembersList.find(m => String(m.id) === String(this.loggerForm.member_id));
            if (member && member.active_packages && member.active_packages.length > 0) {
                const eligible = member.active_packages.find(p => p.remaining > 0);
                if (eligible) {
                    this.loggerForm.member_pt_package_id = String(eligible.id);
                } else {
                    this.loggerForm.member_pt_package_id = '';
                }
            } else {
                this.loggerForm.member_pt_package_id = '';
            }
        },

        async submitLogger() {
            if (!this.loggerForm.member_id || !this.loggerForm.member_pt_package_id) {
                this.loggerError = 'Please select an assigned client with an active package.';
                return;
            }

            this.loggerSubmitting = true;
            this.loggerError = '';

            try {
                const res = await fetch('{{ route('app.pt-sessions.log-with-qr') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.loggerForm)
                });

                const data = await res.json();
                if (data && data.success) {
                    this.loggerSession = {
                        id: data.session_id,
                        token: data.token,
                        confirmUrl: data.confirm_url,
                        otpCode: data.otp_code,
                        memberName: data.member_name,
                        trainerName: data.trainer_name,
                        packageName: data.package_name,
                        remainingSessions: data.remaining_sessions,
                        duration: data.duration_minutes,
                        status: 'PENDING',
                        expiresAt: data.expires_at
                    };
                    this.loggerStep = 'qr';
                    this.startPolling(data.session_id);
                } else {
                    this.loggerError = data.message || 'Could not generate session QR.';
                }
            } catch (err) {
                this.loggerError = 'Network error while generating QR. Please try again.';
            } finally {
                this.loggerSubmitting = false;
            }
        },

        showExistingQr(sessionId, token, confirmUrl, memberName, packageName, duration, otpCode, status = 'PENDING') {
            this.loggerSession = {
                id: sessionId,
                token: token,
                confirmUrl: confirmUrl,
                otpCode: otpCode,
                memberName: memberName,
                trainerName: '{{ addslashes($trainer?->full_name ?? '') }}',
                packageName: packageName,
                duration: duration,
                status: status,
                expiresAt: null
            };
            this.loggerStep = 'qr';
            this.showLoggerModal = true;
            if (status === 'PENDING') {
                this.startPolling(sessionId);
            }
        },

        startPolling(sessionId) {
            this.stopPolling();
            this.loggerPollingInterval = setInterval(async () => {
                if (!this.showLoggerModal || this.loggerStep !== 'qr') {
                    this.stopPolling();
                    return;
                }
                try {
                    const url = '{{ route('app.pt-sessions.qr-status', ['id' => '__ID__']) }}'.replace('__ID__', sessionId);
                    const res = await fetch(url, {
                        headers: { 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (data && (data.is_confirmed || data.is_issue_reported)) {
                        this.loggerSession.status = data.status;
                        this.stopPolling();
                    }
                } catch (e) {}
            }, 2500);
        },

        stopPolling() {
            if (this.loggerPollingInterval) {
                clearInterval(this.loggerPollingInterval);
                this.loggerPollingInterval = null;
            }
        },

        async refreshQr() {
            if (!this.loggerSession.id) return;
            this.loggerSubmitting = true;
            try {
                const url = '{{ route('app.pt-sessions.refresh-qr', ['id' => '__ID__']) }}'.replace('__ID__', this.loggerSession.id);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data && data.success) {
                    this.loggerSession.token = data.token;
                    this.loggerSession.confirmUrl = data.confirm_url;
                    this.loggerSession.otpCode = data.otp_code;
                    this.loggerSession.status = 'PENDING';
                    this.startPolling(this.loggerSession.id);
                }
            } catch (e) {
            } finally {
                this.loggerSubmitting = false;
            }
        },

        async cancelSession(sessionId) {
            if (!confirm('Cancel this pending session?')) return;
            try {
                const url = '{{ route('app.pt-sessions.cancel-pending', ['id' => '__ID__']) }}'.replace('__ID__', sessionId);
                await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                this.stopPolling();
                this.showLoggerModal = false;
                window.location.reload();
            } catch (e) {}
        },

        copyLink() {
            if (this.loggerSession.confirmUrl) {
                navigator.clipboard.writeText(this.loggerSession.confirmUrl);
                alert('Confirmation link copied to clipboard.');
            }
        },

        openMeasurementModal(member) {
            if (member) this.selectedMember = member;
            this.measurementForm = {
                date: new Date().toISOString().split('T')[0],
                weight: this.selectedMember?.latest_weight || '',
                body_fat: '',
                waist: '',
                chest: '',
                bicep: '',
                hips: '',
                thighs: '',
                shoulders: '',
                calves: '',
                neck: '',
                custom_metrics: [],
                notes: ''
            };
            this.showMeasurementModal = true;
        },

        addCustomMetric() {
            this.measurementForm.custom_metrics.push({ name: '', value: '' });
        },

        removeCustomMetric(idx) {
            this.measurementForm.custom_metrics.splice(idx, 1);
        },

        async submitMeasurement() {
            if (!this.selectedMember) return;
            this.measurementSubmitting = true;
            try {
                const url = '{{ route('trainer.members.store-measurement', ['id' => '__MEMBER_ID__']) }}'.replace('__MEMBER_ID__', this.selectedMember.id);
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.measurementForm)
                });
                const data = await res.json();
                if (data && data.success) {
                    this.selectedMember.measurements = data.measurements || [data.measurement, ...(this.selectedMember.measurements || [])];
                    if (data.measurement && data.measurement.weight) {
                        this.selectedMember.latest_weight = data.measurement.weight;
                    }
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) {
                        found.measurements = this.selectedMember.measurements;
                        if (this.selectedMember.latest_weight) found.latest_weight = this.selectedMember.latest_weight;
                    }
                    this.showMeasurementModal = false;
                    this.measurementForm = {
                        date: new Date().toISOString().split('T')[0],
                        weight: '',
                        body_fat: '',
                        waist: '',
                        chest: '',
                        bicep: '',
                        hips: '',
                        thighs: '',
                        shoulders: '',
                        calves: '',
                        neck: '',
                        custom_metrics: [],
                        notes: ''
                    };
                    alert('Measurements recorded successfully for ' + (data.measurement?.date || 'Today') + '!');
                } else {
                    alert(data.message || 'Failed to save measurement.');
                }
            } catch (e) {
                alert('Error submitting measurement.');
            } finally {
                this.measurementSubmitting = false;
            }
        },

        async deleteMeasurement(measId) {
            if (!this.selectedMember || !measId) return;
            if (!confirm('Are you sure you want to delete this measurement entry?')) return;
            try {
                const url = '{{ route('trainer.members.delete-measurement', ['id' => '__MEMBER_ID__', 'measurementId' => '__MEAS_ID__']) }}'
                    .replace('__MEMBER_ID__', this.selectedMember.id)
                    .replace('__MEAS_ID__', measId);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                const data = await res.json();
                if (data && data.success) {
                    this.selectedMember.measurements = data.measurements;
                    if (data.measurements && data.measurements.length > 0 && data.measurements[0].weight) {
                        this.selectedMember.latest_weight = data.measurements[0].weight;
                    } else if (!data.measurements || data.measurements.length === 0) {
                        this.selectedMember.latest_weight = null;
                    }
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) {
                        found.measurements = this.selectedMember.measurements;
                        found.latest_weight = this.selectedMember.latest_weight;
                    }
                    alert(data.message);
                } else {
                    alert(data.message || 'Failed to delete measurement.');
                }
            } catch (e) {
                alert('Error deleting measurement.');
            }
        },

        openWorkoutAssignModal() {
            this.assignType = 'workout';
            this.showAssignModal = true;
            if (this.selectedMember && this.selectedMember.active_workout) {
                const aw = this.selectedMember.active_workout;
                this.workoutBuilder.preset_id = 'custom';
                this.workoutBuilder.title = aw.title || 'Personalized Workout Routine';
                this.workoutBuilder.goal = aw.goal || 'muscle_gain';
                this.workoutBuilder.level = aw.level || 'intermediate';
                this.workoutBuilder.exercises = (aw.exercises || []).map(e => ({
                    day: e.day,
                    name: e.name,
                    sets: e.sets || 3,
                    reps: e.reps || '10-12',
                    rest: e.rest || 60,
                    notes: e.notes || ''
                }));
            } else if (!this.workoutBuilder.exercises || this.workoutBuilder.exercises.length === 0) {
                this.selectWorkoutPreset('ppl_4day');
            }
        },

        selectWorkoutPreset(presetId) {
            this.workoutBuilder.preset_id = presetId;
            if (presetId === 'custom') {
                this.workoutBuilder.title = 'Personalized Workout Routine';
                this.workoutBuilder.goal = 'general_fitness';
                this.workoutBuilder.level = 'intermediate';
                this.workoutBuilder.exercises = [];
            } else {
                const found = this.workoutPresets.find(p => p.id === presetId);
                if (found) {
                    this.workoutBuilder.title = found.title;
                    this.workoutBuilder.goal = found.goal;
                    this.workoutBuilder.level = found.level;
                    this.workoutBuilder.exercises = JSON.parse(JSON.stringify(found.exercises.map(e => ({
                        day: e.day,
                        name: e.exercise_name,
                        sets: e.sets,
                        reps: e.reps,
                        rest: e.rest_seconds,
                        notes: e.notes || ''
                    }))));
                }
            }
        },

        addExerciseToBuilder() {
            if (!this.workoutBuilder.newName.trim()) {
                alert('Please enter an exercise name.');
                return;
            }
            this.workoutBuilder.exercises.push({
                day: this.workoutBuilder.newDay || 'day_1',
                name: this.workoutBuilder.newName.trim(),
                sets: Number(this.workoutBuilder.newSets) || 3,
                reps: this.workoutBuilder.newReps.trim() || '10-12',
                rest: Number(this.workoutBuilder.newRest) || 60,
                notes: this.workoutBuilder.newNotes.trim()
            });
            this.workoutBuilder.newName = '';
            this.workoutBuilder.newNotes = '';
        },

        removeExerciseFromBuilder(index) {
            this.workoutBuilder.exercises.splice(index, 1);
        },

        openDietAssignModal() {
            this.assignType = 'diet';
            this.showAssignModal = true;
            if (this.selectedMember && this.selectedMember.active_diet) {
                const ad = this.selectedMember.active_diet;
                this.dietBuilder.preset_id = 'custom';
                this.dietBuilder.title = ad.title || 'Personalized Nutrition Plan';
                this.dietBuilder.daily_calories = ad.calories || 2000;
                this.dietBuilder.protein_grams = ad.protein || 140;
                this.dietBuilder.carbs_grams = ad.carbs || 200;
                this.dietBuilder.fat_grams = ad.fat || 50;
                this.dietBuilder.meals = (ad.meals || []).map(m => ({
                    type: m.type,
                    time: m.time || '08:00',
                    name: m.name,
                    description: m.description,
                    calories: m.calories || null,
                    protein: m.protein || null,
                    carbs: m.carbs || null,
                    fat: m.fat || null
                }));
            } else if (!this.dietBuilder.meals || this.dietBuilder.meals.length === 0) {
                this.selectDietPreset('muscle_gain_2400');
            }
            this.resetMealInputs();
        },

        selectDietPreset(presetId) {
            this.dietBuilder.preset_id = presetId;
            this.dietBuilder.editingIndex = -1;
            if (presetId === 'custom') {
                this.dietBuilder.title = 'Personalized Nutrition Plan';
                this.dietBuilder.daily_calories = 2000;
                this.dietBuilder.protein_grams = 130;
                this.dietBuilder.carbs_grams = 200;
                this.dietBuilder.fat_grams = 50;
                this.dietBuilder.meals = [];
            } else {
                const found = this.dietPresets.find(p => p.id === presetId);
                if (found) {
                    this.dietBuilder.title = found.title;
                    this.dietBuilder.daily_calories = found.daily_calories;
                    this.dietBuilder.protein_grams = found.protein_grams;
                    this.dietBuilder.carbs_grams = found.carbs_grams;
                    this.dietBuilder.fat_grams = found.fat_grams;
                    this.dietBuilder.meals = JSON.parse(JSON.stringify(found.meals.map(m => ({
                        type: m.meal_type,
                        time: m.recommended_time ? m.recommended_time.substring(0, 5) : '08:00',
                        name: m.meal_name,
                        description: m.items_description,
                        calories: m.calories || null,
                        protein: m.protein_grams || null,
                        carbs: m.carbs_grams || null,
                        fat: m.fat_grams || null
                    }))));
                }
            }
            this.resetMealInputs();
        },

        onMealTypeChange() {
            const labels = {
                breakfast: 'Meal 1: Breakfast',
                morning_snack: 'Meal 2: Morning Snack',
                lunch: 'Meal 3: Lunch',
                evening_snack: 'Meal 4: Evening Snack',
                dinner: 'Meal 5: Dinner',
                post_workout: 'Meal 6: Post-Workout'
            };
            const times = {
                breakfast: '08:00',
                morning_snack: '11:00',
                lunch: '13:30',
                evening_snack: '17:00',
                dinner: '20:30',
                post_workout: '22:00'
            };
            if (this.dietBuilder.editingIndex === -1) {
                this.dietBuilder.newName = labels[this.dietBuilder.newType] || 'Meal ' + ((this.dietBuilder.meals?.length || 0) + 1);
                this.dietBuilder.newTime = times[this.dietBuilder.newType] || '08:00';
            }
        },

        editMealInBuilder(index) {
            const m = this.dietBuilder.meals[index];
            if (!m) return;
            this.dietBuilder.editingIndex = index;
            this.dietBuilder.newType = m.type || 'breakfast';
            this.dietBuilder.newTime = m.time || '08:00';
            this.dietBuilder.newName = m.name || '';
            this.dietBuilder.newDesc = m.description || '';
            this.dietBuilder.newCalories = m.calories ?? '';
            this.dietBuilder.newProtein = m.protein ?? '';
            this.dietBuilder.newCarbs = m.carbs ?? '';
            this.dietBuilder.newFat = m.fat ?? '';
        },

        cancelEditMeal() {
            this.resetMealInputs();
        },

        resetMealInputs() {
            this.dietBuilder.editingIndex = -1;
            const nextIdx = (this.dietBuilder.meals?.length || 0) + 1;
            const labels = {
                1: 'Meal 1: Breakfast',
                2: 'Meal 2: Morning Snack',
                3: 'Meal 3: Lunch',
                4: 'Meal 4: Evening Snack',
                5: 'Meal 5: Dinner',
                6: 'Meal 6: Post-Workout'
            };
            const types = {
                1: 'breakfast',
                2: 'morning_snack',
                3: 'lunch',
                4: 'evening_snack',
                5: 'dinner',
                6: 'post_workout'
            };
            const times = {
                1: '08:00',
                2: '11:00',
                3: '13:30',
                4: '17:00',
                5: '20:30',
                6: '22:00'
            };
            this.dietBuilder.newType = types[nextIdx] || 'breakfast';
            this.dietBuilder.newTime = times[nextIdx] || '08:00';
            this.dietBuilder.newName = labels[nextIdx] || ('Meal ' + nextIdx);
            this.dietBuilder.newDesc = '';
            this.dietBuilder.newCalories = '';
            this.dietBuilder.newProtein = '';
            this.dietBuilder.newCarbs = '';
            this.dietBuilder.newFat = '';
        },

        saveMealToBuilder() {
            const desc = (this.dietBuilder.newDesc || '').trim();
            if (!desc) {
                alert('Please enter food items for this meal (e.g. 4 Boiled Eggs, 4 Wheat Bread, 1 Glass Milk).');
                return;
            }
            const name = (this.dietBuilder.newName || '').trim() || ('Meal ' + (this.dietBuilder.editingIndex !== -1 ? (this.dietBuilder.editingIndex + 1) : ((this.dietBuilder.meals?.length || 0) + 1)));

            const mealData = {
                type: this.dietBuilder.newType || 'breakfast',
                time: this.dietBuilder.newTime || '08:00',
                name: name,
                description: desc,
                calories: this.dietBuilder.newCalories !== '' ? Number(this.dietBuilder.newCalories) : null,
                protein: this.dietBuilder.newProtein !== '' ? Number(this.dietBuilder.newProtein) : null,
                carbs: this.dietBuilder.newCarbs !== '' ? Number(this.dietBuilder.newCarbs) : null,
                fat: this.dietBuilder.newFat !== '' ? Number(this.dietBuilder.newFat) : null,
            };

            if (this.dietBuilder.editingIndex !== -1 && this.dietBuilder.meals[this.dietBuilder.editingIndex]) {
                this.dietBuilder.meals[this.dietBuilder.editingIndex] = mealData;
            } else {
                this.dietBuilder.meals.push(mealData);
            }

            this.resetMealInputs();
        },

        addMealToBuilder() {
            this.saveMealToBuilder();
        },

        removeMealFromBuilder(index) {
            this.dietBuilder.meals.splice(index, 1);
            if (this.dietBuilder.editingIndex === index) {
                this.resetMealInputs();
            }
        },

        async submitAssignWorkout() {
            if (!this.selectedMember) {
                alert('Please select a member first.');
                return;
            }

            // Auto-commit any exercise currently typed in inputs
            if (this.workoutBuilder.newName && this.workoutBuilder.newName.trim()) {
                this.addExerciseToBuilder();
            }

            if (!this.workoutBuilder.title.trim()) {
                alert('Please enter a workout plan title.');
                return;
            }

            if (this.workoutBuilder.preset_id === 'custom' && (!this.workoutBuilder.exercises || this.workoutBuilder.exercises.length === 0)) {
                alert('Please add at least one exercise to the routine.');
                return;
            }

            this.planSubmitting = true;
            try {
                const payload = {
                    preset_id: this.workoutBuilder.preset_id,
                    title: this.workoutBuilder.title,
                    goal: this.workoutBuilder.goal,
                    level: this.workoutBuilder.level,
                    exercises: this.workoutBuilder.exercises
                };

                const url = '{{ route('trainer.members.assign-workout', ['id' => '__MEMBER_ID__']) }}'.replace('__MEMBER_ID__', this.selectedMember.id);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                let data = null;
                try {
                    data = await res.json();
                } catch (parseErr) {
                    console.error('Non-JSON response:', parseErr);
                }

                if (res.ok && data && data.success) {
                    this.selectedMember.active_workout = data.plan;
                    // Also update in assignedMembersList
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) found.active_workout = data.plan;
                    this.showAssignModal = false;
                    alert(data.message || 'Workout plan assigned successfully!');
                } else {
                    const errorMsg = data?.message || `Server error (${res.status}): Failed to assign workout.`;
                    alert(errorMsg);
                }
            } catch (e) {
                console.error('submitAssignWorkout error:', e);
                alert('Network error while assigning workout: ' + (e.message || 'Please check connection.'));
            } finally {
                this.planSubmitting = false;
            }
        },

        async submitAssignDiet() {
            if (!this.selectedMember) {
                alert('Please select a member first.');
                return;
            }

            // Auto-commit any meal currently typed in inputs
            if (this.dietBuilder.newDesc && this.dietBuilder.newDesc.trim()) {
                this.saveMealToBuilder();
            }

            if (!this.dietBuilder.title.trim()) {
                alert('Please enter a diet plan title.');
                return;
            }

            if (this.dietBuilder.preset_id === 'custom' && (!this.dietBuilder.meals || this.dietBuilder.meals.length === 0)) {
                alert('Please add at least one meal to the diet plan.');
                return;
            }

            this.planSubmitting = true;
            try {
                const payload = {
                    preset_id: this.dietBuilder.preset_id,
                    title: this.dietBuilder.title,
                    daily_calories: this.dietBuilder.daily_calories,
                    protein_grams: this.dietBuilder.protein_grams,
                    carbs_grams: this.dietBuilder.carbs_grams,
                    fat_grams: this.dietBuilder.fat_grams,
                    meals: this.dietBuilder.meals
                };

                const url = '{{ route('trainer.members.assign-diet', ['id' => '__MEMBER_ID__']) }}'.replace('__MEMBER_ID__', this.selectedMember.id);

                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                let data = null;
                try {
                    data = await res.json();
                } catch (parseErr) {
                    console.error('Non-JSON response:', parseErr);
                }

                if (res.ok && data && data.success) {
                    this.selectedMember.active_diet = data.plan;
                    // Also update in assignedMembersList
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) found.active_diet = data.plan;
                    this.showAssignModal = false;
                    alert(data.message || 'Diet plan assigned successfully!');
                } else {
                    const errorMsg = data?.message || `Server error (${res.status}): Failed to assign diet.`;
                    alert(errorMsg);
                }
            } catch (e) {
                console.error('submitAssignDiet error:', e);
                alert('Network error while assigning diet: ' + (e.message || 'Please check connection.'));
            } finally {
                this.planSubmitting = false;
            }
        },

        async removeWorkoutPlan() {
            if (!this.selectedMember || !this.selectedMember.active_workout) return;
            if (!confirm(`Are you sure you want to remove the assigned workout plan '${this.selectedMember.active_workout.title}' from ${this.selectedMember.name}?`)) {
                return;
            }
            this.planSubmitting = true;
            try {
                const url = '{{ route('trainer.members.remove-workout', ['id' => '__MEMBER_ID__']) }}'.replace('__MEMBER_ID__', this.selectedMember.id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                let data = null;
                try { data = await res.json(); } catch (_) {}
                if (res.ok && data && data.success) {
                    this.selectedMember.active_workout = null;
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) found.active_workout = null;
                    this.showWorkoutDetails = false;
                    this.showAssignModal = false;
                    alert(data.message || 'Workout plan removed.');
                } else {
                    alert(data?.message || `Server error (${res.status}): Failed to remove workout.`);
                }
            } catch (e) {
                console.error(e);
                alert('Error removing workout plan.');
            } finally {
                this.planSubmitting = false;
            }
        },

        async removeDietPlan() {
            if (!this.selectedMember || !this.selectedMember.active_diet) return;
            if (!confirm(`Are you sure you want to remove the assigned diet plan '${this.selectedMember.active_diet.title}' from ${this.selectedMember.name}?`)) {
                return;
            }
            this.planSubmitting = true;
            try {
                const url = '{{ route('trainer.members.remove-diet', ['id' => '__MEMBER_ID__']) }}'.replace('__MEMBER_ID__', this.selectedMember.id);
                const res = await fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                });
                let data = null;
                try { data = await res.json(); } catch (_) {}
                if (res.ok && data && data.success) {
                    this.selectedMember.active_diet = null;
                    const found = this.assignedMembersList.find(m => String(m.id) === String(this.selectedMember.id));
                    if (found) found.active_diet = null;
                    this.showDietDetails = false;
                    this.showAssignModal = false;
                    alert(data.message || 'Diet plan removed.');
                } else {
                    alert(data?.message || `Server error (${res.status}): Failed to remove diet.`);
                }
            } catch (e) {
                console.error(e);
                alert('Error removing diet plan.');
            } finally {
                this.planSubmitting = false;
            }
        }
    }" 
    x-on:trainer-tab-changed.window="setTab($event.detail)"
    class="min-h-screen pb-24 lg:pb-12 text-slate-900 dark:text-slate-100 bg-slate-50/60 dark:bg-[#070b12] transition-colors">

        <div class="max-w-2xl mx-auto space-y-3.5 px-3 sm:px-0 pt-2 sm:pt-4">
            
            <!-- Sleek App Bar / Top Identity Card (Hidden on profile tab to avoid duplicate name card) -->
            <div x-show="tab !== 'profile'" class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800/90 shadow-xs flex items-center justify-between gap-3">
                <div class="flex items-center gap-3 min-w-0">
                    @if(!empty($resolvedLogo))
                        <div class="h-10 w-10 sm:h-11 sm:w-11 rounded-xl bg-slate-50 dark:bg-slate-900 p-1 border border-slate-200/90 dark:border-slate-800 flex items-center justify-center shrink-0">
                            <img src="{{ $resolvedLogo }}" alt="{{ $tenant->name }}" class="h-full w-full object-contain rounded-lg">
                        </div>
                    @else
                        <div class="w-10 h-10 sm:h-11 sm:w-11 rounded-xl bg-amber-500/10 text-amber-500 border border-amber-500/20 font-bold text-sm flex items-center justify-center shrink-0">
                            {{ strtoupper(substr($tenant->name ?: 'G', 0, 2)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs font-semibold text-slate-500 dark:text-slate-400 truncate">{{ $tenant->name }}</span>
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.2 rounded text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Coach</span>
                            </span>
                        </div>
                        <h1 class="text-sm sm:text-base font-bold text-slate-900 dark:text-white truncate">
                            {{ $trainer?->full_name ?? $user->name }}
                        </h1>
                    </div>
                </div>

                <div class="text-right shrink-0">
                    <span class="text-[11px] text-slate-500 dark:text-slate-400 block">{{ $trainer?->branch?->name ?? 'Main Branch' }}</span>
                    <span class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ $assignedCount }} {{ \Illuminate\Support\Str::plural('Client', $assignedCount) }}</span>
                </div>
            </div>

            <!-- Desktop Segmented Tabs (Screens >= sm) -->
            <div class="hidden sm:flex items-center gap-1 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-semibold">
                <button type="button" @click="setTab('today')" :class="tab === 'today' ? 'bg-white dark:bg-[#0d1320] text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-lg transition-all text-center cursor-pointer">
                    Today
                </button>
                <button type="button" @click="setTab('members')" :class="tab === 'members' ? 'bg-white dark:bg-[#0d1320] text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-lg transition-all text-center cursor-pointer">
                    Members ({{ $assignedCount }})
                </button>
                <button type="button" @click="setTab('sessions')" :class="tab === 'sessions' ? 'bg-white dark:bg-[#0d1320] text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-lg transition-all text-center cursor-pointer">
                    Sessions
                </button>
                <button type="button" @click="setTab('profile')" :class="tab === 'profile' ? 'bg-white dark:bg-[#0d1320] text-amber-600 dark:text-amber-400 shadow-xs font-bold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" class="flex-1 py-2 px-3 rounded-lg transition-all text-center cursor-pointer">
                    Profile
                </button>
            </div>

            <!-- ========================================================= -->
            <!-- SCREEN 1: TODAY (Live Coach Briefing & Daily Flow)       -->
            <!-- ========================================================= -->
            <div x-show="tab === 'today'" x-cloak class="space-y-3.5">
                
                <!-- 1. Coach Daily Briefing & Progress Card -->
                <div class="p-4 sm:p-5 rounded-2xl bg-[#0c1424] dark:bg-[#090e1a] text-white border border-slate-800 shadow-xs relative overflow-hidden">
                    <div class="relative z-10 space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-400 block">
                                    {{ \Carbon\Carbon::now()->format('l, d F') }}
                                </span>
                                <h2 class="text-base sm:text-lg font-bold text-white mt-0.5">
                                    @php
                                        $hour = (int) now()->format('H');
                                        $greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
                                        $firstName = explode(' ', trim($trainer?->full_name ?? $user->name))[0];
                                    @endphp
                                    {{ $greeting }}, {{ $firstName }} 👋
                                </h2>
                            </div>
                            <div class="text-right">
                                <span class="text-xl sm:text-2xl font-black text-amber-400 leading-none block">{{ $todayCompletedCount }}/{{ max(1, $todayCount) }}</span>
                                <span class="text-[10px] text-slate-400">Sessions done</span>
                            </div>
                        </div>

                        <!-- Progress Bar -->
                        @php
                            $todayPct = $todayCount > 0 ? min(100, round(($todayCompletedCount / $todayCount) * 100)) : 0;
                        @endphp
                        <div class="w-full h-1.5 rounded-full bg-slate-800 overflow-hidden">
                            <div class="h-full rounded-full bg-amber-500 transition-all duration-500" style="width: {{ $todayPct }}%"></div>
                        </div>

                        <!-- 4 Daily Metric Chips -->
                        <div class="grid grid-cols-4 gap-1.5 pt-2 border-t border-slate-800/80 text-center">
                            <div class="p-1.5 sm:p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                                <span class="text-[9px] sm:text-[10px] text-slate-400 block truncate">Done</span>
                                <span class="text-xs font-bold text-emerald-400 mt-0.5 block truncate">{{ $todayCompletedCount }}</span>
                            </div>
                            <div class="p-1.5 sm:p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                                <span class="text-[9px] sm:text-[10px] text-slate-400 block truncate">Booked</span>
                                <span class="text-xs font-bold text-sky-400 mt-0.5 block truncate">{{ $todayScheduledCount }}</span>
                            </div>
                            <div class="p-1.5 sm:p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                                <span class="text-[9px] sm:text-[10px] text-slate-400 block truncate">Scan</span>
                                <span class="text-xs font-bold mt-0.5 block truncate {{ $pendingCount > 0 ? 'text-amber-400 animate-pulse' : 'text-slate-400' }}">{{ $pendingCount }}</span>
                            </div>
                            <div class="p-1.5 sm:p-2 rounded-xl bg-slate-900/60 border border-slate-800/60">
                                <span class="text-[9px] sm:text-[10px] text-slate-400 block truncate">Rating</span>
                                <span class="text-xs font-bold text-amber-400 mt-0.5 block truncate">★ {{ $averageRating }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Primary Action: Prominent Solid Amber Log Button -->
                <div>
                    <button type="button" 
                            @click="openLogger()" 
                            class="w-full py-3.5 px-4 rounded-2xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-black text-sm shadow-sm transition-all flex items-center justify-center gap-2 cursor-pointer">
                        <svg class="w-5 h-5 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Log PT Session &amp; Show QR</span>
                    </button>
                </div>

                <!-- PT Package Renewal Alert Banner (Client Retention Booster) -->
                @if(isset($renewalAlerts) && $renewalAlerts->isNotEmpty())
                    <div class="p-3.5 rounded-2xl bg-gradient-to-r from-amber-500/15 via-amber-500/10 to-transparent border border-amber-500/30 space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-amber-500">
                            <span class="flex items-center gap-1.5">
                                <span class="text-sm">⚠️</span>
                                <span>PT Renewal Due ({{ $renewalAlerts->count() }})</span>
                            </span>
                            <span class="text-[10px] text-slate-400 font-normal">&le; 2 sessions left</span>
                        </div>
                        <div class="space-y-1.5">
                            @foreach($renewalAlerts as $alertMember)
                                @php
                                    $alertPkg = $alertMember->memberPtPackages->first();
                                    $rem = $alertPkg?->remaining_sessions ?? 0;
                                    $cleanPhone = preg_replace('/[^0-9]/', '', (string)$alertMember->phone);
                                    $waMsg = urlencode("Hi {$alertMember->first_name}, you have only {$rem} PT session(s) left in your package. Please connect with the front desk to renew your package so we keep your training streak uninterrupted!");
                                @endphp
                                <div class="p-2.5 rounded-xl bg-white dark:bg-[#0d1320] border border-amber-500/20 shadow-xs flex items-center justify-between gap-2">
                                    <div class="min-w-0">
                                        <h4 class="font-bold text-xs text-slate-900 dark:text-white truncate">{{ $alertMember->full_name }}</h4>
                                        <span class="text-[10px] text-amber-500 font-semibold block">
                                            {{ $rem == 0 ? 'Exhausted (0 sessions left)' : "{$rem} session(s) remaining" }}
                                        </span>
                                    </div>
                                    @if(!empty($cleanPhone))
                                        <a href="https://wa.me/{{ $cleanPhone }}?text={{ $waMsg }}" 
                                           target="_blank"
                                           class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[11px] flex items-center gap-1 shadow-xs transition-all shrink-0">
                                            <span>💬 Nudge</span>
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 3. Pending Confirmation Alert Card (If any awaiting member scan) -->
                @if($pendingSessions->isNotEmpty())
                    <div class="p-3.5 rounded-2xl bg-amber-500/10 border border-amber-500/30 space-y-2.5">
                        <div class="flex items-center justify-between text-xs font-bold text-amber-600 dark:text-amber-400">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"></span>
                                <span>Awaiting Member Scan ({{ $pendingSessions->count() }})</span>
                            </span>
                            <span class="text-[10px] text-slate-500">Auto-expires in 30m</span>
                        </div>

                        <div class="space-y-2">
                            @foreach($pendingSessions as $pending)
                                <div class="p-3 rounded-xl bg-white dark:bg-[#0d1320] border border-amber-500/20 shadow-xs flex items-center justify-between gap-3">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $pending->member?->full_name }}</h4>
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/15 text-amber-600 dark:text-amber-400">Pending</span>
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ $pending->memberPtPackage?->package_name }} &bull; {{ $pending->duration_minutes }}m &bull; Logged {{ $pending->created_at?->diffForHumans() }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <button type="button" 
                                                @click="showExistingQr({{ $pending->id }}, '{{ $pending->confirmation_token }}', '{{ route('pt-session.confirm', ['token' => $pending->confirmation_token]) }}', '{{ addslashes($pending->member?->full_name ?? '') }}', '{{ addslashes($pending->memberPtPackage?->package_name ?? '') }}', {{ $pending->duration_minutes }}, '{{ $pending->otp_code }}', 'PENDING')"
                                                class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs shadow-xs transition-all flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                                            <span>Show QR</span>
                                        </button>
                                        <button type="button" @click="cancelSession({{ $pending->id }})" title="Cancel session" class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors cursor-pointer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 4. Quick-Start 1-Tap Client Strip ("Quick Train Client") -->
                @if($assignedMembers->isNotEmpty())
                    <div class="space-y-2">
                        <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                            <span>Quick Train Client</span>
                            <button type="button" @click="setTab('members')" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline">
                                View all ({{ $assignedCount }})
                            </button>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            @foreach($assignedMembers->take(4) as $m)
                                @php
                                    $pkg = $m->memberPtPackages->first();
                                    $hasRem = $pkg && $pkg->remaining_sessions > 0;
                                @endphp
                                <div class="p-3 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 shadow-xs flex items-center justify-between gap-2.5">
                                    <div class="flex items-center gap-2.5 min-w-0">
                                        <div class="w-9 h-9 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                                            {{ substr($m->first_name, 0, 1) }}{{ substr($m->last_name ?? '', 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $m->full_name }}</h4>
                                            <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold block truncate">
                                                @if($hasRem)
                                                    {{ $pkg->remaining_sessions }} sessions left
                                                @else
                                                    No sessions left
                                                @endif
                                            </span>
                                        </div>
                                    </div>

                                    @if($hasRem)
                                        <button type="button" 
                                                @click="openLogger({{ $m->id }}, {{ $pkg->id }})" 
                                                class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-600 dark:text-amber-400 font-bold text-[11px] border border-amber-500/20 transition-all shrink-0 cursor-pointer flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            <span>Train</span>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- 5. Today's Appointment Schedule / Timeline -->
                <div class="space-y-2 pt-1">
                    <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                        <span>Today's Appointments &bull; {{ \Carbon\Carbon::today()->format('d M') }}</span>
                        <span class="text-[11px] font-normal text-slate-500">{{ $todaySessions->count() }} booked</span>
                    </div>

                    <div class="space-y-2">
                        @forelse($todaySessions as $session)
                            @php
                                $isDone = in_array($session->status, ['CONFIRMED', 'COMPLETED']);
                                $isScheduled = $session->status === 'SCHEDULED';
                            @endphp
                            <div class="p-3.5 rounded-2xl bg-white dark:bg-[#0d1320] border {{ $isScheduled ? 'border-amber-500/40 bg-amber-500/5' : 'border-slate-200/90 dark:border-slate-800' }} shadow-xs flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-12 text-center shrink-0">
                                        <span class="text-xs font-black {{ $isScheduled ? 'text-amber-500' : 'text-slate-700 dark:text-slate-300' }} block">
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('g:i') }}
                                        </span>
                                        <span class="text-[9px] uppercase tracking-wider text-slate-400 font-bold block">
                                            {{ \Carbon\Carbon::parse($session->start_time)->format('A') }}
                                        </span>
                                    </div>

                                    <div class="w-px h-8 bg-slate-200 dark:bg-slate-800 shrink-0"></div>

                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $session->member?->full_name }}</h4>
                                            @if($isDone)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">Done</span>
                                            @elseif($isScheduled)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">Up Next</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 truncate mt-0.5">
                                            {{ $session->duration_minutes }}m &bull; {{ $session->focus_area ?? 'PT Workout' }}
                                        </p>
                                    </div>
                                </div>

                                <div class="shrink-0 text-right">
                                    @if($isScheduled)
                                        <button type="button" 
                                                @click="openLogger({{ $session->member_id }}, {{ $session->member_pt_package_id }})" 
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all cursor-pointer">
                                            Start PT
                                        </button>
                                    @else
                                        <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                            <span>✓</span>
                                            <span>{{ $session->completed_at?->format('g:i A') ?? 'Finished' }}</span>
                                        </span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center bg-white dark:bg-[#0d1320] border border-dashed border-slate-200 dark:border-slate-800 rounded-2xl text-slate-500 dark:text-slate-400 space-y-1.5">
                                <span class="text-lg">🕒</span>
                                <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">No scheduled appointments for today</h4>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                                    Tap <strong>Train</strong> on any client above or <strong>Log PT Session</strong> whenever a client arrives at the gym.
                                </p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SCREEN 2: MEMBERS (Clients, Progress & Detail Drawer)     -->
            <!-- ========================================================= -->
            <div x-show="tab === 'members'" x-cloak class="space-y-3">
                
                <!-- Search & Filter Bar -->
                <div class="relative">
                    <svg class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" 
                           x-model="memberSearch" 
                           placeholder="Search members by name or code..." 
                           class="w-full pl-9 pr-3.5 py-2.5 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder:text-slate-400 focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                <!-- Members List -->
                <div class="space-y-2.5">
                    @forelse($assignedMembers as $member)
                        @php
                            $activePkg = $member->memberPtPackages->first();
                            $hasRemaining = $activePkg && $activePkg->remaining_sessions > 0;
                            $hasRenewalDue = $activePkg && $activePkg->remaining_sessions <= 2;
                            $used = $activePkg?->used_sessions ?? 0;
                            $total = max(1, $activePkg?->total_sessions ?? 1);
                            $pkgPct = min(100, round(($used / $total) * 100));

                            $meta = $member->metadata ?? [];
                            $meas = $meta['measurements'] ?? [];
                            $latestWeight = !empty($meas) && isset($meas[0]['weight']) ? $meas[0]['weight'] : null;

                            $memberPayload = [
                                'id' => $member->id,
                                'name' => $member->full_name,
                                'initials' => strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name ?? '', 0, 1)),
                                'code' => $member->member_code,
                                'phone' => !empty($member->phone) ? substr($member->phone, 0, 3) . ' •••• ' . substr($member->phone, -3) : '',
                                'raw_phone' => preg_replace('/[^0-9]/', '', (string)$member->phone),
                                'branch' => $member->branch?->name ?? 'Main Branch',
                                'has_renewal_due' => $hasRenewalDue,
                                'latest_weight' => $latestWeight,
                                'active_workout' => $member->activeWorkoutPlan ? [
                                    'id' => $member->activeWorkoutPlan->id,
                                    'title' => $member->activeWorkoutPlan->title,
                                    'goal' => $member->activeWorkoutPlan->goal ?? 'general_fitness',
                                    'level' => $member->activeWorkoutPlan->level ?? 'intermediate',
                                    'exercises_count' => $member->activeWorkoutPlan->exercises ? $member->activeWorkoutPlan->exercises->count() : 0,
                                    'exercises' => $member->activeWorkoutPlan->exercises ? $member->activeWorkoutPlan->exercises->map(fn($e) => [
                                        'day' => $e->day,
                                        'name' => $e->exercise_name,
                                        'sets' => $e->sets,
                                        'reps' => $e->reps,
                                        'rest' => $e->rest_seconds,
                                    ]) : [],
                                ] : null,
                                'active_diet' => $member->activeDietPlan ? [
                                    'id' => $member->activeDietPlan->id,
                                    'title' => $member->activeDietPlan->title,
                                    'calories' => $member->activeDietPlan->daily_calories,
                                    'protein' => $member->activeDietPlan->protein_grams,
                                    'carbs' => $member->activeDietPlan->carbs_grams,
                                    'fat' => $member->activeDietPlan->fat_grams,
                                    'guidelines' => $member->activeDietPlan->guidelines,
                                    'meals_count' => $member->activeDietPlan->meals ? $member->activeDietPlan->meals->count() : 0,
                                    'meals' => $member->activeDietPlan->meals ? $member->activeDietPlan->meals->map(fn($ml) => [
                                        'type' => $ml->meal_type,
                                        'time' => $ml->recommended_time ? substr((string)$ml->recommended_time, 0, 5) : '',
                                        'name' => $ml->meal_name,
                                        'description' => $ml->items_description,
                                        'calories' => $ml->calories,
                                        'protein' => $ml->protein_grams,
                                        'carbs' => $ml->carbs_grams,
                                        'fat' => $ml->fat_grams,
                                    ]) : [],
                                ] : null,
                                'measurements' => array_slice($meas, 0, 25),
                                'active_packages' => $member->memberPtPackages->map(fn($p) => [
                                    'id' => $p->id,
                                    'name' => $p->package_name,
                                    'total' => $p->total_sessions,
                                    'used' => $p->used_sessions,
                                    'remaining' => $p->remaining_sessions,
                                    'days_remaining' => $p->days_remaining,
                                    'end_date' => $p->end_date ? $p->end_date->format('d M Y') : '',
                                ]),
                                'recent_sessions' => $member->ptSessions->map(fn($s) => [
                                    'date' => $s->session_date ? $s->session_date->format('d M Y') : '',
                                    'duration' => $s->duration_minutes,
                                    'focus' => $s->focus_area ?? 'PT Workout',
                                    'status' => $s->status,
                                ])
                            ];
                        @endphp
                        <div x-show="!memberSearch || '{{ strtolower(addslashes($member->full_name . ' ' . $member->member_code)) }}'.includes(memberSearch.toLowerCase().trim())" 
                             class="p-4 rounded-2xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-3 hover:border-slate-300 dark:hover:border-slate-700 transition-all cursor-pointer"
                             @click="openMemberDetail({{ Js::from($memberPayload) }})">
                            
                            <!-- Member Header Row -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0 border border-slate-200 dark:border-slate-700">
                                        {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name ?? '', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <h3 class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $member->full_name }}</h3>
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-500">{{ $member->member_code }}</span>
                                            @if($hasRenewalDue)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/15 text-amber-500 border border-amber-500/30">Renewal Due</span>
                                            @endif
                                            @if($latestWeight)
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300">⚖️ {{ $latestWeight }}kg</span>
                                            @endif
                                        </div>
                                        <p class="text-[11px] text-slate-400 mt-0.5 truncate">
                                            @if(!empty($member->phone))
                                                <span>{{ substr($member->phone, 0, 3) . ' •••• ' . substr($member->phone, -3) }}</span>
                                            @endif
                                            @if($member->branch)
                                                <span>&bull; {{ $member->branch->name }}</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div class="flex items-center gap-2 shrink-0" @click.stop>
                                    @if($hasRemaining)
                                        <button type="button" 
                                                @click="openLogger({{ $member->id }}, {{ $activePkg->id }})" 
                                                class="px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all cursor-pointer flex items-center gap-1">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                            <span>Log</span>
                                        </button>
                                    @endif
                                    <button type="button" @click="openMemberDetail({{ Js::from($memberPayload) }})" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Package Progress Bar -->
                            @if($activePkg)
                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-100 dark:border-slate-800/80 space-y-1.5">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $activePkg->package_name }}</span>
                                        <span class="font-bold text-amber-600 dark:text-amber-400 text-[11px] shrink-0">{{ $activePkg->remaining_sessions }} left</span>
                                    </div>
                                    <div class="w-full h-1.5 rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden">
                                        <div class="h-full rounded-full bg-amber-500 transition-all duration-300" style="width: {{ $pkgPct }}%"></div>
                                    </div>
                                    <div class="flex items-center justify-between text-[10px] text-slate-400">
                                        <span>Used: {{ $activePkg->used_sessions }}/{{ $activePkg->total_sessions }}</span>
                                        <span>Expires {{ $activePkg->end_date->format('d M Y') }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 text-center text-[11px] text-slate-400 italic">
                                    No active PT package currently assigned.
                                </div>
                            @endif

                            @if($member->activeWorkoutPlan || $member->activeDietPlan)
                                <div class="flex items-center gap-1.5 pt-1 text-[10px] font-medium text-slate-500 dark:text-slate-400 flex-wrap">
                                    @if($member->activeWorkoutPlan)
                                        <span class="px-2 py-0.5 rounded-lg bg-sky-500/10 text-sky-400 border border-sky-500/20 truncate max-w-[140px]">🏋️ {{ $member->activeWorkoutPlan->title }}</span>
                                    @endif
                                    @if($member->activeDietPlan)
                                        <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 truncate max-w-[140px]">🥗 {{ $member->activeDietPlan->title }}</span>
                                    @endif
                                </div>
                            @endif

                        </div>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 rounded-2xl text-slate-500 dark:text-slate-400 space-y-2">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto text-lg">
                                👥
                            </div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">No members assigned yet</h4>
                            <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                                Once the gym assigns members or PT packages to your coaching profile, they will appear here automatically.
                            </p>
                        </div>
                    @endforelse
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SCREEN 3: PT SESSIONS (Audit Ledger with Status Filter)   -->
            <!-- ========================================================= -->
            <div x-show="tab === 'sessions'" x-cloak class="space-y-3">
                
                <!-- Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs no-scrollbar">
                    <a href="{{ route('trainer.workspace', ['tab' => 'sessions', 'session_status' => 'all']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 {{ !request('session_status') || request('session_status') === 'all' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                        All
                    </a>
                    <a href="{{ route('trainer.workspace', ['tab' => 'sessions', 'session_status' => 'pending']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 {{ request('session_status') === 'pending' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-amber-600 dark:text-amber-400' }}">
                        Pending @if($pendingCount > 0)({{ $pendingCount }})@endif
                    </a>
                    <a href="{{ route('trainer.workspace', ['tab' => 'sessions', 'session_status' => 'confirmed']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 {{ request('session_status') === 'confirmed' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                        Confirmed
                    </a>
                    <a href="{{ route('trainer.workspace', ['tab' => 'sessions', 'session_status' => 'issue_reported']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 {{ request('session_status') === 'issue_reported' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                        Issue reported @if($issueReportedCount > 0)({{ $issueReportedCount }})@endif
                    </a>
                    <a href="{{ route('trainer.workspace', ['tab' => 'sessions', 'session_status' => 'scheduled']) }}" 
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all shrink-0 {{ request('session_status') === 'scheduled' ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400' }}">
                        Scheduled
                    </a>
                </div>

                <!-- Sessions List -->
                <div class="space-y-2">
                    @forelse($allSessions as $sess)
                        <div x-data="{ openDetail: false }" class="p-3.5 rounded-2xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-2">
                            
                            <!-- Main Row -->
                            <div class="flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <h4 class="font-bold text-slate-900 dark:text-white text-xs truncate">{{ $sess->member?->full_name }}</h4>
                                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 truncate">
                                        {{ $sess->session_date->format('d M Y') }} &bull; {{ $sess->duration_minutes }}m &bull; {{ $sess->memberPtPackage?->package_name ?? 'PT Session' }}
                                    </p>
                                </div>

                                <div class="flex items-center gap-2 shrink-0">
                                    <!-- Status Badges -->
                                    @if($sess->status === 'PENDING')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Pending
                                        </span>
                                    @elseif(in_array($sess->status, ['CONFIRMED', 'COMPLETED']))
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                            Confirmed
                                        </span>
                                    @elseif($sess->status === 'ISSUE_REPORTED')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            Issue reported
                                        </span>
                                    @elseif($sess->status === 'SCHEDULED')
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-sky-500/10 text-sky-600 dark:text-sky-400 border border-sky-500/20">
                                            Scheduled
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-slate-800 text-slate-500">
                                            {{ $sess->status }}
                                        </span>
                                    @endif

                                    <!-- Quick Show QR on Pending -->
                                    @if($sess->status === 'PENDING')
                                        <button type="button" 
                                                @click="showExistingQr({{ $sess->id }}, '{{ $sess->confirmation_token }}', '{{ route('pt-session.confirm', ['token' => $sess->confirmation_token]) }}', '{{ addslashes($sess->member?->full_name ?? '') }}', '{{ addslashes($sess->memberPtPackage?->package_name ?? '') }}', {{ $sess->duration_minutes }}, '{{ $sess->otp_code }}', 'PENDING')"
                                                class="px-2.5 py-1 rounded-lg bg-amber-500 text-slate-950 font-bold text-[10px] transition-all cursor-pointer">
                                            Show QR
                                        </button>
                                    @endif

                                    <!-- Toggle Details Button -->
                                    <button type="button" @click="openDetail = !openDetail" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200">
                                        <svg class="w-4 h-4 transition-transform duration-150" :class="{ 'rotate-180': openDetail }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Expandable Audit Trail -->
                            <div x-show="openDetail" x-cloak class="pt-2 border-t border-slate-100 dark:border-slate-800/80 text-[11px] text-slate-500 dark:text-slate-400 space-y-1">
                                <div class="flex items-center justify-between">
                                    <span>Session Time:</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ \Carbon\Carbon::parse($sess->start_time)->format('g:i A') }}</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span>Logged By:</span>
                                    <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sess->loggedByUser?->name ?? 'Trainer' }} &bull; {{ $sess->created_at?->format('d M, g:i A') }}</span>
                                </div>
                                @if($sess->confirmed_at)
                                    <div class="flex items-center justify-between">
                                        <span>Confirmed At:</span>
                                        <span class="font-medium text-emerald-600 dark:text-emerald-400">{{ $sess->confirmed_at->format('d M, g:i A') }}</span>
                                    </div>
                                @endif
                                @if($sess->focus_area)
                                    <div class="flex items-center justify-between">
                                        <span>Focus Area:</span>
                                        <span class="font-medium text-slate-800 dark:text-slate-200">{{ $sess->focus_area }}</span>
                                    </div>
                                @endif
                                @if($sess->rating)
                                    <div class="flex items-center justify-between">
                                        <span>Client Rating:</span>
                                        <span class="font-bold text-amber-500 flex items-center gap-1">
                                            <span>{{ str_repeat('★', $sess->rating) }}{{ str_repeat('☆', 5 - $sess->rating) }}</span>
                                            <span class="text-[10px]">({{ $sess->rating }}/5)</span>
                                        </span>
                                    </div>
                                @endif
                                @if($sess->member_feedback)
                                    <div class="flex items-center justify-between">
                                        <span>Client Feedback:</span>
                                        <span class="px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-500 font-bold text-[10px]">{{ $sess->member_feedback }}</span>
                                    </div>
                                @endif
                                @if($sess->issue_notes)
                                    <div class="p-2 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-500 text-xs mt-1">
                                        <strong>Member Issue Note:</strong> {{ $sess->issue_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 rounded-2xl text-slate-500 dark:text-slate-400">
                            No PT sessions found matching this filter.
                        </div>
                    @endforelse

                    @if($allSessions->hasPages())
                        <div class="pt-2">
                            {{ $allSessions->links() }}
                        </div>
                    @endif
                </div>

            </div>

            <!-- ========================================================= -->
            <!-- SCREEN 4: PROFILE (Account, Security & Sign Out)          -->
            <!-- ========================================================= -->
            <div x-show="tab === 'profile'" x-cloak class="space-y-3.5">
                
                <!-- Trainer Identity Card -->
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 text-amber-500 font-bold text-base flex items-center justify-center shrink-0 border border-amber-500/20">
                            {{ strtoupper(substr($trainer?->full_name ?? $user->name, 0, 2)) }}
                        </div>
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $trainer?->full_name ?? $user->name }}</h2>
                            <p class="text-xs text-slate-500 dark:text-slate-400">{{ $trainer?->branch?->name ?? 'Main Branch' }} &bull; {{ $trainer?->specialization ?? 'Personal Trainer' }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 dark:border-slate-800 text-xs">
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-400 uppercase font-semibold block">Completed</span>
                            <span class="text-base font-bold text-slate-900 dark:text-white block mt-0.5">{{ $completedTotalCount }}</span>
                        </div>
                        <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-400 uppercase font-semibold block">Clients</span>
                            <span class="text-base font-bold text-amber-500 block mt-0.5">{{ $assignedCount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Change Password Form -->
                <div class="p-4 rounded-2xl bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 shadow-xs space-y-3">
                    <h3 class="text-xs font-bold text-slate-900 dark:text-white">Change Password</h3>

                    <form action="{{ route('trainer.profile.update-password') }}" method="POST" class="space-y-2.5">
                        @csrf
                        <div>
                            <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Current Password</label>
                            <input type="password" name="current_password" required class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">New Password</label>
                                <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 dark:text-slate-400 mb-1">Confirm</label>
                                <input type="password" name="password_confirmation" required minlength="6" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            </div>
                        </div>
                        <button type="submit" class="w-full py-2.5 rounded-xl bg-slate-900 hover:bg-slate-800 dark:bg-slate-800 dark:hover:bg-slate-700 text-white font-bold text-xs transition-all cursor-pointer">
                            Update password
                        </button>
                    </form>
                </div>

                <!-- Sign Out -->
                <div>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full py-3 rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 font-bold text-xs border border-rose-500/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span>Sign out</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

        <!-- ========================================================= -->
        <!-- MEMBER DETAIL SHEET (Slide-Up Drawer)                     -->
        <!-- ========================================================= -->
        <div x-show="showMemberSheet" class="fixed inset-0 z-[100000] overflow-y-auto bg-black/60 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 rounded-t-3xl sm:rounded-2xl max-w-md w-full max-h-[88vh] overflow-y-auto p-4 sm:p-5 pb-24 sm:pb-5 shadow-xl space-y-4" @click.away="showMemberSheet = false">
                
                <!-- Drawer Top Handle on Mobile -->
                <div class="w-10 h-1 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto sm:hidden"></div>

                <!-- Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs flex items-center justify-center shrink-0">
                            <span x-text="selectedMember?.initials"></span>
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-sm font-bold text-slate-900 dark:text-white truncate" x-text="selectedMember?.name"></h3>
                            <p class="text-[11px] text-slate-400 truncate" x-text="selectedMember?.code + (selectedMember?.phone ? ' • ' + selectedMember?.phone : '')"></p>
                        </div>
                    </div>
                    <button type="button" @click="showMemberSheet = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Active Package Breakdown -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Active PT Package</span>
                    <template x-if="selectedMember?.active_packages?.length > 0">
                        <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-2.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="font-bold text-slate-900 dark:text-white" x-text="selectedMember?.active_packages[0]?.name"></span>
                                <span class="font-bold text-amber-600 dark:text-amber-400" x-text="selectedMember?.active_packages[0]?.remaining + ' sessions left'"></span>
                            </div>
                            <div class="flex items-center justify-between text-[11px] text-slate-400">
                                <span x-text="'Used: ' + selectedMember?.active_packages[0]?.used + ' of ' + selectedMember?.active_packages[0]?.total"></span>
                                <span x-text="'Expires: ' + selectedMember?.active_packages[0]?.end_date"></span>
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedMember?.active_packages?.length">
                        <p class="text-xs text-slate-400 italic">No active PT package.</p>
                    </template>
                </div>

                <!-- Body Measurements & Weight Tracker Card -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between gap-2 flex-wrap">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider truncate">Weight &amp; Measurements</span>
                            <template x-if="selectedMember?.measurements?.length > 0">
                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-amber-500/15 text-amber-500 font-mono shrink-0" x-text="selectedMember.measurements.length + ' logs'"></span>
                            </template>
                        </div>
                        <div class="flex items-center gap-1.5 shrink-0 ml-auto">
                            <template x-if="selectedMember?.measurements?.length > 0">
                                <button type="button" 
                                        @click="showMeasHistory = !showMeasHistory" 
                                        class="px-2.5 py-1 rounded-lg bg-slate-200/70 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-[10px] font-bold transition-all cursor-pointer">
                                    <span x-text="showMeasHistory ? 'Hide History' : 'View History'"></span>
                                </button>
                            </template>
                            <button type="button" 
                                    @click="openMeasurementModal(selectedMember)" 
                                    class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-500 border border-amber-500/20 text-[10px] font-bold transition-all cursor-pointer flex items-center gap-1">
                                <span>+ Log Stats</span>
                            </button>
                        </div>
                    </div>

                    <div class="p-3.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-2.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="text-slate-400">Current Weight:</span>
                            <div class="flex items-center gap-2">
                                <span class="font-black text-slate-900 dark:text-white" x-text="selectedMember?.latest_weight ? selectedMember.latest_weight + ' kg' : 'Not recorded'"></span>
                                <template x-if="selectedMember?.measurements?.[0]?.date">
                                    <span class="text-[10px] text-slate-400 font-mono" x-text="'(' + selectedMember.measurements[0].date + ')'"></span>
                                </template>
                            </div>
                        </div>

                        <template x-if="selectedMember?.measurements?.length > 0">
                            <div class="space-y-2">
                                <div class="grid grid-cols-3 sm:grid-cols-6 gap-1.5 pt-1 border-t border-slate-200/60 dark:border-slate-800 text-center">
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Waist</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.waist ? selectedMember.measurements[0].waist + ' in' : '--'"></span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Chest</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.chest ? selectedMember.measurements[0].chest + ' in' : '--'"></span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Arms</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.bicep ? selectedMember.measurements[0].bicep + ' in' : '--'"></span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Body Fat</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.body_fat ? selectedMember.measurements[0].body_fat + '%' : '--'"></span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Hips</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.hips ? selectedMember.measurements[0].hips + ' in' : '--'"></span>
                                    </div>
                                    <div class="p-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800">
                                        <span class="text-[8px] text-slate-400 block font-bold uppercase">Thighs</span>
                                        <span class="text-[11px] font-bold text-slate-800 dark:text-slate-200 block truncate" x-text="selectedMember?.measurements[0]?.thighs ? selectedMember.measurements[0].thighs + ' in' : '--'"></span>
                                    </div>
                                </div>

                                <template x-if="selectedMember?.measurements[0]?.notes">
                                    <div class="p-2 rounded-lg bg-amber-500/5 border border-amber-500/20 text-[10px] text-amber-500 dark:text-amber-400/90 leading-relaxed">
                                        <span class="font-bold">Latest Note:</span> <span x-text="selectedMember.measurements[0].notes"></span>
                                    </div>
                                </template>
                            </div>
                        </template>

                        <template x-if="!selectedMember?.measurements?.length">
                            <p class="text-[11px] text-slate-400 italic">No measurements recorded yet. Tap '+ Log Stats' above.</p>
                        </template>

                        <!-- Expandable Date-Wise Measurement History -->
                        <template x-if="showMeasHistory && selectedMember?.measurements?.length > 0">
                            <div class="pt-3 border-t border-slate-200/70 dark:border-slate-800 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                                        <span>📅 Measurement Timeline History</span>
                                        <span class="px-1.5 py-0.2 rounded text-[9px] bg-slate-200/70 dark:bg-slate-800 font-bold" x-text="selectedMember.measurements.length + ' logs'"></span>
                                    </span>
                                    <span class="text-[9px] text-slate-400">Chronological • Newest First</span>
                                </div>

                                <div class="max-h-[500px] overflow-y-auto space-y-3 pr-1">
                                    <template x-for="(m, mIdx) in selectedMember.measurements" :key="m.id || m.date + mIdx">
                                        <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-[#0c121e] border border-slate-200/90 dark:border-slate-800/90 shadow-xs space-y-3">
                                            <!-- Entry Header Bar (Responsive: pins delete button top-right, chips wrap naturally) -->
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="flex items-center gap-1.5 flex-wrap min-w-0">
                                                    <span class="px-2 py-0.5 rounded-lg text-[10px] font-mono font-bold bg-amber-500/10 text-amber-500 border border-amber-500/25 flex items-center gap-1 shrink-0">
                                                        <span>📅</span>
                                                        <span x-text="m.date"></span>
                                                    </span>
                                                    <template x-if="m.weight">
                                                        <span class="px-2 py-0.5 rounded-lg text-[11px] font-black bg-slate-100 dark:bg-slate-800/90 text-slate-900 dark:text-white border border-slate-200/60 dark:border-slate-700/60 flex items-center gap-1 shrink-0">
                                                            <span>⚖️</span>
                                                            <span x-text="m.weight + ' kg'"></span>
                                                        </span>
                                                    </template>
                                                    <template x-if="m.body_fat">
                                                        <span class="px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-sky-500/10 text-sky-400 border border-sky-500/20 shrink-0" x-text="m.body_fat + '% Fat'"></span>
                                                    </template>
                                                    <template x-if="m.logged_by">
                                                        <span class="text-[9px] text-slate-400 hidden sm:inline" x-text="'By ' + m.logged_by"></span>
                                                    </template>
                                                </div>
                                                <button type="button" 
                                                        @click="deleteMeasurement(m.id)" 
                                                        class="p-1 rounded-lg text-rose-400 hover:bg-rose-500/10 hover:text-rose-300 border border-transparent hover:border-rose-500/20 transition-all cursor-pointer text-xs shrink-0 self-start" 
                                                        title="Delete this check-in">
                                                    🗑️
                                                </button>
                                            </div>

                                            <!-- Body Metrics Grid (Organized cleanly in structured mini-tiles) -->
                                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 text-center">
                                                <template x-if="m.waist">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Waist</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.waist + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.chest">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Chest</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.chest + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.bicep">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Arms</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.bicep + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.hips">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Hips</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.hips + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.thighs">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Thighs</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.thighs + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.shoulders">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Shoulders</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.shoulders + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.calves">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Calves</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.calves + ' in'"></span>
                                                    </div>
                                                </template>
                                                <template x-if="m.neck">
                                                    <div class="p-2 rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200/60 dark:border-slate-800">
                                                        <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Neck</span>
                                                        <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5" x-text="m.neck + ' in'"></span>
                                                    </div>
                                                </template>
                                            </div>

                                            <!-- Custom Metrics Tags -->
                                            <template x-if="m.custom_metrics?.length > 0">
                                                <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                    <template x-for="cm in m.custom_metrics" :key="cm.name">
                                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-amber-500/10 text-amber-400 border border-amber-500/20" x-text="cm.name + ': ' + cm.value"></span>
                                                    </template>
                                                </div>
                                            </template>

                                            <!-- Coach Observation Note -->
                                            <template x-if="m.notes">
                                                <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-300 italic flex items-start gap-2">
                                                    <span class="text-xs shrink-0">💬</span>
                                                    <span x-text="'&ldquo;' + m.notes + '&rdquo;'"></span>
                                                </div>
                                            </template>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Workout & Nutrition Plans Card -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Workout &amp; Diet Coaching</span>
                    <div class="space-y-2">
                        <!-- Workout Plan -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] text-sky-400 font-bold block uppercase tracking-wider">Workout Plan</span>
                                        <template x-if="selectedMember?.active_workout?.exercises_count">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-sky-500/15 text-sky-400" x-text="selectedMember.active_workout.exercises_count + ' Exercises'"></span>
                                        </template>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate mt-0.5" x-text="selectedMember?.active_workout?.title || 'None assigned yet'"></h4>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                    <template x-if="selectedMember?.active_workout?.exercises?.length > 0">
                                        <button type="button" 
                                                @click="showWorkoutDetails = !showWorkoutDetails" 
                                                class="px-2.5 py-1 rounded-lg bg-slate-200/60 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-semibold transition-all cursor-pointer">
                                            <span x-text="showWorkoutDetails ? 'Hide' : 'View'"></span>
                                        </button>
                                    </template>
                                    <button type="button" 
                                            @click="openWorkoutAssignModal()" 
                                            class="px-2.5 py-1 rounded-lg bg-sky-500/10 hover:bg-sky-500 hover:text-white text-sky-400 border border-sky-500/20 text-[10px] font-bold transition-all cursor-pointer">
                                        <span x-text="selectedMember?.active_workout ? 'Change' : 'Assign'"></span>
                                    </button>
                                    <template x-if="selectedMember?.active_workout">
                                        <button type="button" 
                                                @click="removeWorkoutPlan()" 
                                                :disabled="planSubmitting"
                                                class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border border-rose-500/20 text-[10px] font-bold transition-all cursor-pointer" title="Remove Workout Routine">
                                            Remove
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Expandable Routine Details -->
                            <template x-if="showWorkoutDetails && selectedMember?.active_workout?.exercises?.length > 0">
                                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/80 space-y-1.5">
                                    <template x-for="ex in selectedMember.active_workout.exercises" :key="ex.name + ex.day">
                                        <div class="p-2 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-100 dark:border-slate-800/60 text-xs flex items-center justify-between gap-2">
                                            <div class="min-w-0">
                                                <span class="text-[9px] text-sky-400 font-bold block" x-text="ex.day"></span>
                                                <span class="font-semibold text-slate-800 dark:text-slate-200 truncate block text-[11px]" x-text="ex.name"></span>
                                            </div>
                                            <div class="text-right shrink-0">
                                                <span class="font-mono text-[10px] text-slate-700 dark:text-slate-300 font-bold block" x-text="ex.sets + ' × ' + ex.reps"></span>
                                                <span class="text-[9px] text-slate-400 block" x-text="ex.rest ? ex.rest + 's rest' : ''"></span>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>

                        <!-- Diet Plan -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 space-y-2">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="text-[10px] text-emerald-400 font-bold block uppercase tracking-wider">Diet / Nutrition Plan</span>
                                        <template x-if="selectedMember?.active_diet?.calories">
                                            <span class="px-1.5 py-0.2 rounded text-[9px] font-semibold bg-emerald-500/15 text-emerald-400" x-text="selectedMember.active_diet.calories + ' kcal • ' + selectedMember.active_diet.protein + 'g P'"></span>
                                        </template>
                                    </div>
                                    <h4 class="text-xs font-bold text-slate-900 dark:text-white truncate mt-0.5" x-text="selectedMember?.active_diet?.title || 'None assigned yet'"></h4>
                                </div>
                                <div class="flex items-center gap-1.5 shrink-0 flex-wrap">
                                    <template x-if="selectedMember?.active_diet?.meals?.length > 0">
                                        <button type="button" 
                                                @click="showDietDetails = !showDietDetails" 
                                                class="px-2.5 py-1 rounded-lg bg-slate-200/60 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-300 text-[10px] font-semibold transition-all cursor-pointer">
                                            <span x-text="showDietDetails ? 'Hide' : 'View'"></span>
                                        </button>
                                    </template>
                                    <button type="button" 
                                            @click="openDietAssignModal()" 
                                            class="px-2.5 py-1 rounded-lg bg-emerald-500/10 hover:bg-emerald-500 hover:text-white text-emerald-400 border border-emerald-500/20 text-[10px] font-bold transition-all cursor-pointer">
                                        <span x-text="selectedMember?.active_diet ? 'Change' : 'Assign'"></span>
                                    </button>
                                    <template x-if="selectedMember?.active_diet">
                                        <button type="button" 
                                                @click="removeDietPlan()" 
                                                :disabled="planSubmitting"
                                                class="px-2.5 py-1 rounded-lg bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 border border-rose-500/20 text-[10px] font-bold transition-all cursor-pointer" title="Remove Diet Plan">
                                            Remove
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Expandable Meals Details -->
                            <template x-if="showDietDetails && selectedMember?.active_diet?.meals?.length > 0">
                                <div class="pt-2 border-t border-slate-200/60 dark:border-slate-800/80 space-y-2">
                                    <template x-for="(ml, mIdx) in selectedMember.active_diet.meals" :key="mIdx + (ml.name || '')">
                                        <div class="p-2.5 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-100 dark:border-slate-800/80 text-xs space-y-1.5">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-1.5 flex-wrap">
                                                    <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400" x-text="ml.type ? ml.type.replace('_', ' ') : 'MEAL'"></span>
                                                    <span class="text-[10px] text-slate-400 font-mono" x-text="ml.time"></span>
                                                    <span class="font-bold text-slate-900 dark:text-white" x-text="ml.name"></span>
                                                </div>
                                                <template x-if="ml.calories">
                                                    <span class="font-bold text-[10px] px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400 shrink-0" x-text="ml.calories + ' kcal'"></span>
                                                </template>
                                            </div>
                                            <!-- Macros Pills -->
                                            <div class="flex items-center gap-1 flex-wrap text-[9px] font-bold">
                                                <template x-if="ml.protein">
                                                    <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400" x-text="ml.protein + 'g Protein'"></span>
                                                </template>
                                                <template x-if="ml.carbs">
                                                    <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400" x-text="ml.carbs + 'g Carbs'"></span>
                                                </template>
                                                <template x-if="ml.fat">
                                                    <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400" x-text="ml.fat + 'g Fat'"></span>
                                                </template>
                                            </div>
                                            <!-- Foods List -->
                                            <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/50 border border-slate-100 dark:border-slate-800/40 text-[11px] text-slate-700 dark:text-slate-300 whitespace-pre-line leading-relaxed" x-text="ml.description"></div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Recent Sessions Timeline -->
                <div class="space-y-2">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Recent Workout History</span>
                    <template x-if="selectedMember?.recent_sessions?.length > 0">
                        <div class="space-y-1.5">
                            <template x-for="s in selectedMember.recent_sessions" :key="s.date + s.duration">
                                <div class="flex items-center justify-between text-xs p-2.5 rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-100 dark:border-slate-800">
                                    <div>
                                        <span class="font-medium text-slate-800 dark:text-slate-200 block" x-text="s.focus + ' (' + s.duration + 'm)'"></span>
                                        <span class="text-[10px] text-slate-400" x-text="s.date"></span>
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded" :class="s.status === 'CONFIRMED' || s.status === 'COMPLETED' ? 'bg-emerald-500/10 text-emerald-600' : 'bg-amber-500/10 text-amber-600'" x-text="s.status"></span>
                                </div>
                            </template>
                        </div>
                    </template>
                    <template x-if="!selectedMember?.recent_sessions?.length">
                        <p class="text-xs text-slate-400 italic">No workout history logged yet.</p>
                    </template>
                </div>

                <!-- Primary Action -->
                <div class="pt-2">
                    <button type="button" 
                            @click="openLogger(selectedMember?.id, selectedMember?.active_packages?.[0]?.id)"
                            class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer shadow-xs">
                        <svg class="w-4 h-4 text-slate-950" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                        <span>Log PT Session for <span x-text="selectedMember?.name"></span></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- TWO-SCREEN PT SESSION LOGGER (Compact Sheet & QR View)    -->
        <!-- ========================================================= -->
        <div x-show="showLoggerModal" class="fixed inset-0 z-[100000] overflow-y-auto bg-black/70 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-t-3xl sm:rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto p-5 sm:p-6 pb-28 sm:pb-6 shadow-xl space-y-4" @click.away="showLoggerModal = false; stopPolling()">
                
                <!-- Drag handle on mobile -->
                <div class="w-10 h-1 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto sm:hidden"></div>

                <!-- Modal Top Bar -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="loggerStep === 'form' ? 'Log PT session' : 'Waiting for member confirmation'"></h3>
                    <button type="button" @click="showLoggerModal = false; stopPolling()" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Error Alert in Modal -->
                <template x-if="loggerError">
                    <div class="p-2.5 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 text-xs flex items-center gap-1.5">
                        <span>⚠️</span>
                        <span x-text="loggerError"></span>
                    </div>
                </template>

                <!-- ===================================================== -->
                <!-- SCREEN 1: COMPACT LOGGING FORM                        -->
                <!-- ===================================================== -->
                <div x-show="loggerStep === 'form'" class="space-y-3.5">
                    
                    <!-- Member Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Assigned Member <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="loggerForm.member_id" @change="onMemberSelect()" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <option value="">Select assigned client</option>
                            @foreach($assignedMembers as $m)
                                @php
                                    $pkg = $m->memberPtPackages->first();
                                    $rem = $pkg ? $pkg->remaining_sessions : 0;
                                @endphp
                                <option value="{{ $m->id }}">
                                    {{ $m->full_name }} @if($rem > 0)({{ $rem }} sessions left)@else(General Member)@endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Package Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Active PT Package <span class="text-rose-500">*</span>
                        </label>
                        <select x-model="loggerForm.member_pt_package_id" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none">
                            <option value="">Select package</option>
                            @foreach($eligiblePackages as $pkg)
                                <option value="{{ $pkg->id }}">
                                    {{ $pkg->package_name }} &bull; {{ $pkg->remaining_sessions }} left ({{ $pkg->member?->full_name }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Duration Selector -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Duration
                        </label>
                        <div class="grid grid-cols-4 gap-2 text-xs">
                            <button type="button" @click="loggerForm.duration_minutes = 30" :class="loggerForm.duration_minutes == 30 ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'" class="py-2 rounded-xl transition-all cursor-pointer">30m</button>
                            <button type="button" @click="loggerForm.duration_minutes = 45" :class="loggerForm.duration_minutes == 45 ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'" class="py-2 rounded-xl transition-all cursor-pointer">45m</button>
                            <button type="button" @click="loggerForm.duration_minutes = 60" :class="loggerForm.duration_minutes == 60 ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'" class="py-2 rounded-xl transition-all cursor-pointer">60m</button>
                            <button type="button" @click="loggerForm.duration_minutes = 90" :class="loggerForm.duration_minutes == 90 ? 'bg-amber-500 text-slate-950 font-bold' : 'bg-slate-100 dark:bg-slate-900 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-800'" class="py-2 rounded-xl transition-all cursor-pointer">90m</button>
                        </div>
                    </div>

                    <!-- Focus Area with Quick Chips -->
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Workout Focus
                        </label>
                        <input type="text" x-model="loggerForm.focus_area" placeholder="e.g. Strength Training, Core, Leg Day" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-amber-500 focus:outline-none mb-1.5">
                        
                        <!-- Quick Suggestion Chips -->
                        <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar">
                            <template x-for="tag in focusTags" :key="tag">
                                <button type="button" 
                                        @click="loggerForm.focus_area = tag" 
                                        :class="loggerForm.focus_area === tag ? 'bg-amber-500/20 text-amber-500 border-amber-500/30' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border-transparent'" 
                                        class="px-2 py-1 rounded-lg text-[10px] font-medium border transition-colors shrink-0 cursor-pointer" 
                                        x-text="tag">
                                </button>
                            </template>
                        </div>
                    </div>

                    <!-- Primary Action: Save & show QR -->
                    <div class="pt-2">
                        <button type="button" 
                                @click="submitLogger()" 
                                :disabled="loggerSubmitting || !loggerForm.member_id || !loggerForm.member_pt_package_id" 
                                class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 active:bg-amber-600 text-slate-950 font-bold text-xs transition-all flex items-center justify-center gap-1.5 cursor-pointer disabled:opacity-50 shadow-xs">
                            <template x-if="loggerSubmitting">
                                <svg class="w-4 h-4 animate-spin text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            </template>
                            <span>Save &amp; show QR code</span>
                        </button>
                    </div>
                </div>

                <!-- ===================================================== -->
                <!-- SCREEN 2: LARGE HIGH-CONTRAST QR & LIVE CONFIRMATION  -->
                <!-- ===================================================== -->
                <div x-show="loggerStep === 'qr'" class="space-y-4 text-center">
                    
                    <!-- Member & Session Summary -->
                    <div class="space-y-0.5">
                        <h2 class="text-base font-bold text-slate-900 dark:text-white" x-text="loggerSession.memberName"></h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400">
                            <span x-text="loggerSession.packageName"></span> &bull; 
                            <span x-text="loggerSession.duration + ' mins'"></span>
                        </p>
                    </div>

                    <!-- RESULT A: CONFIRMED -->
                    <template x-if="loggerSession.status === 'CONFIRMED' || loggerSession.status === 'COMPLETED'">
                        <div class="p-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-emerald-500 text-white flex items-center justify-center mx-auto text-xl font-bold">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-emerald-600 dark:text-emerald-400">Session confirmed!</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    1 session deducted from member's balance.
                                </p>
                            </div>
                            <button type="button" @click="showLoggerModal = false; window.location.reload()" class="w-full py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs transition-all cursor-pointer">
                                Done
                            </button>
                        </div>
                    </template>

                    <!-- RESULT B: ISSUE REPORTED -->
                    <template x-if="loggerSession.status === 'ISSUE_REPORTED'">
                        <div class="p-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 space-y-3">
                            <div class="w-12 h-12 rounded-full bg-rose-500 text-white flex items-center justify-center mx-auto text-xl font-bold">
                                ⚠️
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-rose-600 dark:text-rose-400">Issue reported</h3>
                                <p class="text-xs text-slate-600 dark:text-slate-300 mt-0.5">
                                    The session has been sent to the manager for review. No sessions deducted.
                                </p>
                            </div>
                            <button type="button" @click="showLoggerModal = false; window.location.reload()" class="w-full py-2.5 rounded-xl bg-slate-800 text-white font-bold text-xs transition-all cursor-pointer">
                                Done
                            </button>
                        </div>
                    </template>

                    <!-- WAITING STATE: LARGE BLACK-ON-WHITE QR -->
                    <template x-if="loggerSession.status === 'PENDING'">
                        <div class="space-y-3">
                            <!-- High Contrast QR Code -->
                            <div class="inline-block p-3 rounded-2xl bg-white border-2 border-slate-900 shadow-sm">
                                <img :src="'https://api.qrserver.com/v1/create-qr-code/?size=240x240&margin=4&data=' + encodeURIComponent(loggerSession.confirmUrl)" 
                                     alt="PT Session QR" 
                                     class="w-52 h-52 sm:w-60 sm:h-60 mx-auto object-contain">
                                
                                <div class="mt-2 pt-2 border-t border-slate-100 text-center">
                                    <span class="text-[10px] text-slate-500 uppercase tracking-widest font-black">Session PIN</span>
                                    <div class="text-base font-mono font-black text-slate-900 tracking-widest mt-0.5" x-text="loggerSession.otpCode"></div>
                                </div>
                            </div>

                            <!-- Explanatory note -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-center gap-1.5 text-xs font-semibold text-amber-600 dark:text-amber-400">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    <span>Waiting for member confirmation...</span>
                                </div>
                                <p class="text-[11px] text-slate-400 max-w-xs mx-auto">
                                    Ask member to scan this QR code or enter the 6-digit Session PIN in their Member App.
                                </p>
                            </div>

                            <!-- Secondary Actions -->
                            <div class="flex items-center justify-center gap-2 pt-1 text-xs">
                                <button type="button" @click="refreshQr()" :disabled="loggerSubmitting" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-medium transition-all border border-slate-300 dark:border-slate-800 cursor-pointer">
                                    Refresh QR
                                </button>
                                <button type="button" @click="copyLink()" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-900 hover:bg-slate-200 text-slate-700 dark:text-slate-300 font-medium transition-all border border-slate-300 dark:border-slate-800 cursor-pointer">
                                    Copy Link
                                </button>
                                <button type="button" @click="cancelSession(loggerSession.id)" class="px-3 py-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 font-medium transition-all cursor-pointer">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </template>

                </div>

            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL 1: QUICK MEASUREMENTS LOGGING                       -->
        <!-- ========================================================= -->
        <div x-show="showMeasurementModal" class="fixed inset-0 z-[100000] overflow-y-auto bg-black/70 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-t-3xl sm:rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-5 sm:p-6 pb-28 sm:pb-6 shadow-xl space-y-4" @click.away="showMeasurementModal = false">
                <!-- Mobile drag bar -->
                <div class="w-10 h-1 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto sm:hidden"></div>

                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white flex items-center gap-1.5">
                            <span>⚖️ Log Body Measurements</span>
                        </h3>
                        <p class="text-[11px] text-slate-400" x-text="'For ' + selectedMember?.name"></p>
                    </div>
                    <button type="button" @click="showMeasurementModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <form @submit.prevent="submitMeasurement()" class="space-y-3.5">
                    
                    <!-- Date Picker (Select Date for measurement) -->
                    <div class="p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/25 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="text-base">📅</span>
                            <label class="text-[11px] font-bold text-slate-800 dark:text-slate-200">Measurement Date *</label>
                        </div>
                        <input type="date" required x-model="measurementForm.date" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono font-bold focus:outline-none focus:border-amber-500">
                    </div>

                    <!-- Core Metrics: Weight & Body Fat -->
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Weight (kg) *</label>
                            <input type="number" step="0.1" required x-model="measurementForm.weight" placeholder="e.g. 74.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Body Fat %</label>
                            <input type="number" step="0.1" x-model="measurementForm.body_fat" placeholder="e.g. 17.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>
                    </div>

                    <!-- Upper Body Metrics -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Upper Body (inches)</span>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Waist (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.waist" placeholder="e.g. 32" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Chest (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.chest" placeholder="e.g. 39" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Bicep / Arms (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.bicep" placeholder="e.g. 14.5" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                        </div>
                    </div>

                    <!-- Hips & Lower Body Metrics -->
                    <div class="space-y-1">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-400">Hips &amp; Lower Body (inches)</span>
                        <div class="grid grid-cols-3 gap-2">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Hips (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.hips" placeholder="e.g. 38" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Thighs (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.thighs" placeholder="e.g. 23" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Shoulders (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.shoulders" placeholder="e.g. 44" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Calves (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.calves" placeholder="e.g. 15" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-0.5">Neck (in)</label>
                                <input type="number" step="0.1" x-model="measurementForm.neck" placeholder="e.g. 15.5" class="w-full px-2.5 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                        </div>
                    </div>

                    <!-- Dynamic Custom Metrics (+ Add Custom Metric) -->
                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Custom Metrics / Parameters</span>
                            <button type="button" 
                                    @click="addCustomMetric()" 
                                    class="px-2 py-0.5 rounded-lg bg-amber-500/10 hover:bg-amber-500 hover:text-slate-950 text-amber-500 text-[10px] font-bold transition-all flex items-center gap-1 cursor-pointer">
                                <span>+ Add Custom Metric</span>
                            </button>
                        </div>

                        <template x-for="(cm, cIdx) in measurementForm.custom_metrics" :key="cIdx">
                            <div class="flex items-center gap-2">
                                <input type="text" x-model="cm.name" placeholder="Metric Name (e.g. Forearms)" class="flex-1 px-2.5 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                <input type="text" x-model="cm.value" placeholder="Value (e.g. 12 in)" class="w-28 px-2.5 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                                <button type="button" @click="removeCustomMetric(cIdx)" class="p-1 rounded text-rose-400 hover:bg-rose-500/10 cursor-pointer">✕</button>
                            </div>
                        </template>
                        <template x-if="measurementForm.custom_metrics.length === 0">
                            <p class="text-[10px] text-slate-400 italic">Optional: Tap '+ Add Custom Metric' to track any extra parameter (e.g. Forearms, BMI).</p>
                        </template>
                    </div>

                    <!-- Coach Notes -->
                    <div>
                        <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Coach Notes &amp; Observations</label>
                        <textarea x-model="measurementForm.notes" rows="2" placeholder="e.g. Looking leaner, waist down 0.5 in, muscle definition improving" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500 resize-none"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" :disabled="measurementSubmitting" class="w-full py-3.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-2 shadow-xs disabled:opacity-50">
                            <span x-text="measurementSubmitting ? 'Saving Stats...' : 'Save Measurements for ' + (measurementForm.date || 'Today')"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ========================================================= -->
        <!-- MODAL 2: ASSIGN WORKOUT OR DIET PLAN                      -->
        <!-- ========================================================= -->
        <div x-show="showAssignModal" class="fixed inset-0 z-[100000] overflow-y-auto bg-black/70 backdrop-blur-xs flex items-end sm:items-center justify-center p-0 sm:p-4" x-cloak>
            <div class="bg-white dark:bg-[#0d1320] border border-slate-200/90 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-t-3xl sm:rounded-2xl max-w-md w-full max-h-[90vh] overflow-y-auto p-5 sm:p-6 pb-28 sm:pb-6 shadow-xl space-y-4" @click.away="showAssignModal = false">
                <!-- Mobile drag bar -->
                <div class="w-10 h-1 bg-slate-300 dark:bg-slate-700 rounded-full mx-auto sm:hidden"></div>

                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-sm font-bold text-slate-900 dark:text-white" x-text="assignType === 'workout' ? 'Assign Workout Plan' : 'Assign Diet Plan'"></h3>
                        <p class="text-[11px] text-slate-400" x-text="'For ' + selectedMember?.name"></p>
                    </div>
                    <button type="button" @click="showAssignModal = false" class="p-1 rounded-lg text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Type Switcher -->
                <div class="grid grid-cols-2 gap-1.5 p-1 rounded-xl bg-slate-100 dark:bg-slate-900 text-xs font-bold">
                    <button type="button" @click="assignType = 'workout'" :class="assignType === 'workout' ? 'bg-white dark:bg-[#0d1320] text-sky-500 shadow-xs' : 'text-slate-500'" class="py-1.5 rounded-lg transition-all">🏋️ Workout Plan</button>
                    <button type="button" @click="assignType = 'diet'" :class="assignType === 'diet' ? 'bg-white dark:bg-[#0d1320] text-emerald-500 shadow-xs' : 'text-slate-500'" class="py-1.5 rounded-lg transition-all">🥗 Diet Plan</button>
                </div>

                <!-- WORKOUT FORM -->
                <template x-if="assignType === 'workout'">
                    <form @submit.prevent="submitAssignWorkout()" class="space-y-4">
                        <!-- Presets Selector -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Choose Split or Start Custom</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <template x-for="p in workoutPresets" :key="p.id">
                                    <button type="button" 
                                            @click="selectWorkoutPreset(p.id)"
                                            class="p-2 rounded-xl border text-left transition-all"
                                            :class="workoutBuilder.preset_id === p.id ? 'bg-sky-500/10 border-sky-500 text-sky-400' : 'bg-slate-50 dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300'">
                                        <span class="font-bold text-[11px] block truncate" x-text="p.title"></span>
                                        <span class="text-[9px] text-slate-400 block mt-0.5" x-text="p.badge"></span>
                                    </button>
                                </template>
                                <button type="button" 
                                        @click="selectWorkoutPreset('custom')"
                                        class="p-2 rounded-xl border text-left transition-all"
                                        :class="workoutBuilder.preset_id === 'custom' ? 'bg-sky-500/10 border-sky-500 text-sky-400' : 'bg-slate-50 dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300'">
                                    <span class="font-bold text-[11px] block truncate">✏️ Custom Routine</span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">Start blank</span>
                                </button>
                            </div>
                        </div>

                        <!-- Plan Title & Goal -->
                        <div class="space-y-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Plan Title *</label>
                                <input type="text" x-model="workoutBuilder.title" required placeholder="e.g. 4-Day Push/Pull/Legs Split" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Goal Focus</label>
                                    <select x-model="workoutBuilder.goal" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                                        <option value="muscle_gain">Muscle Gain</option>
                                        <option value="weight_loss">Weight / Fat Loss</option>
                                        <option value="endurance">Endurance</option>
                                        <option value="general_fitness">General Fitness</option>
                                        <option value="flexibility">Flexibility</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Level</label>
                                    <select x-model="workoutBuilder.level" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                                        <option value="beginner">Beginner</option>
                                        <option value="intermediate">Intermediate</option>
                                        <option value="advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Exercises List Section -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span>🏋️ Exercises In Routine</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-sky-500/15 text-sky-400" x-text="workoutBuilder.exercises.length"></span>
                                </label>
                                <span class="text-[10px] text-slate-400">Add or remove below</span>
                            </div>

                            <!-- List -->
                            <div class="max-h-56 overflow-y-auto space-y-1.5 pr-1">
                                <template x-for="(ex, idx) in workoutBuilder.exercises" :key="idx + ex.name">
                                    <div class="p-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-2 text-xs">
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="px-1.5 py-0.2 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-sky-400" x-text="ex.day.replace('_', ' ')"></span>
                                                <span class="font-bold text-slate-900 dark:text-white truncate" x-text="ex.name"></span>
                                            </div>
                                            <span class="text-[10px] text-slate-400 mt-0.5 block" x-text="ex.sets + ' sets × ' + ex.reps + (ex.rest ? ' • ' + ex.rest + 's rest' : '') + (ex.notes ? ' • ' + ex.notes : '')"></span>
                                        </div>
                                        <button type="button" 
                                                @click="removeExerciseFromBuilder(idx)" 
                                                class="w-6 h-6 rounded-lg text-rose-400 hover:bg-rose-500/10 flex items-center justify-center shrink-0 cursor-pointer" title="Remove Exercise">
                                            ✕
                                        </button>
                                    </div>
                                </template>

                                <template x-if="workoutBuilder.exercises.length === 0">
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                                        No exercises in this plan yet. Use the quick form below to add exercises.
                                    </div>
                                </template>
                            </div>

                            <!-- Compact Add Exercise Box -->
                            <div class="p-3 rounded-xl bg-sky-500/5 border border-sky-500/20 space-y-2">
                                <span class="text-[10px] font-bold text-sky-400 uppercase tracking-wider block">+ Add New Exercise</span>
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-1">
                                        <select x-model="workoutBuilder.newDay" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                                            <option value="day_1">Day 1</option>
                                            <option value="day_2">Day 2</option>
                                            <option value="day_3">Day 3</option>
                                            <option value="day_4">Day 4</option>
                                            <option value="day_5">Day 5</option>
                                            <option value="day_6">Day 6</option>
                                            <option value="day_7">Day 7</option>
                                            <option value="monday">Monday</option>
                                            <option value="tuesday">Tuesday</option>
                                            <option value="wednesday">Wednesday</option>
                                            <option value="thursday">Thursday</option>
                                            <option value="friday">Friday</option>
                                            <option value="saturday">Saturday</option>
                                            <option value="sunday">Sunday</option>
                                        </select>
                                    </div>
                                    <div class="col-span-2">
                                        <input type="text" x-model="workoutBuilder.newName" @keydown.enter.prevent="addExerciseToBuilder()" placeholder="Exercise name (e.g. Incline DB Flyes)" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-sky-500">
                                    </div>
                                </div>
                                <div class="grid grid-cols-3 gap-2 items-center">
                                    <div>
                                        <input type="number" x-model="workoutBuilder.newSets" placeholder="Sets (3)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-sky-500 text-center">
                                    </div>
                                    <div>
                                        <input type="text" x-model="workoutBuilder.newReps" placeholder="Reps (10-12)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-sky-500 text-center">
                                    </div>
                                    <div>
                                        <button type="button" @click="addExerciseToBuilder()" class="w-full py-1.5 rounded-lg bg-sky-500 hover:bg-sky-400 text-slate-950 font-bold text-[11px] transition-all cursor-pointer">
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button type="submit" :disabled="planSubmitting" class="w-full py-3.5 rounded-xl bg-sky-500 hover:bg-sky-400 text-slate-950 font-black text-xs transition-all cursor-pointer flex items-center justify-center gap-2 shadow-xs disabled:opacity-50">
                                <span x-text="planSubmitting ? 'Saving Workout Plan...' : 'Save & Assign Workout Plan (' + workoutBuilder.exercises.length + ' Exercises)'"></span>
                            </button>
                            <template x-if="selectedMember?.active_workout">
                                <button type="button" 
                                        @click="removeWorkoutPlan()" 
                                        :disabled="planSubmitting"
                                        class="w-full py-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-xs disabled:opacity-50">
                                    🗑️ Remove Active Workout Routine from Client
                                </button>
                            </template>
                        </div>
                    </form>
                </template>

                <!-- DIET FORM -->
                <template x-if="assignType === 'diet'">
                    <form @submit.prevent="submitAssignDiet()" class="space-y-4">
                        <!-- Presets Selector -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300">Choose Nutrition Blueprint or Custom</label>
                            <div class="grid grid-cols-2 gap-1.5">
                                <template x-for="p in dietPresets" :key="p.id">
                                    <button type="button" 
                                            @click="selectDietPreset(p.id)"
                                            class="p-2 rounded-xl border text-left transition-all"
                                            :class="dietBuilder.preset_id === p.id ? 'bg-emerald-500/10 border-emerald-500 text-emerald-400' : 'bg-slate-50 dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300'">
                                        <span class="font-bold text-[11px] block truncate" x-text="p.title"></span>
                                        <span class="text-[9px] text-slate-400 block mt-0.5" x-text="p.daily_calories + ' kcal • ' + p.protein_grams + 'g P'"></span>
                                    </button>
                                </template>
                                <button type="button" 
                                        @click="selectDietPreset('custom')"
                                        class="p-2 rounded-xl border text-left transition-all"
                                        :class="dietBuilder.preset_id === 'custom' ? 'bg-emerald-500/10 border-emerald-500 text-emerald-400' : 'bg-slate-50 dark:bg-slate-900 border-slate-200/80 dark:border-slate-800 text-slate-700 dark:text-slate-300 hover:border-slate-300'">
                                    <span class="font-bold text-[11px] block truncate">✏️ Custom Nutrition</span>
                                    <span class="text-[9px] text-slate-400 block mt-0.5">Start blank</span>
                                </button>
                            </div>
                        </div>

                        <!-- Plan Title & Macros Target -->
                        <div class="space-y-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/80 dark:border-slate-800">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">Diet Plan Title *</label>
                                <input type="text" x-model="dietBuilder.title" required placeholder="e.g. High Protein Deficit 2000 kcal" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                            </div>
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">Target Calories</label>
                                    <div class="relative">
                                        <input type="number" x-model="dietBuilder.daily_calories" placeholder="2400" class="w-full pl-2.5 pr-8 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="absolute right-2 top-2.5 text-[9px] text-slate-400 font-bold">kcal</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">Protein</label>
                                    <div class="relative">
                                        <input type="number" x-model="dietBuilder.protein_grams" placeholder="160" class="w-full pl-2.5 pr-6 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="absolute right-2 top-2.5 text-[9px] text-blue-400 font-bold">g</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">Carbs</label>
                                    <div class="relative">
                                        <input type="number" x-model="dietBuilder.carbs_grams" placeholder="260" class="w-full pl-2.5 pr-6 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="absolute right-2 top-2.5 text-[9px] text-amber-400 font-bold">g</span>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-700 dark:text-slate-300 mb-1">Fat</label>
                                    <div class="relative">
                                        <input type="number" x-model="dietBuilder.fat_grams" placeholder="65" class="w-full pl-2.5 pr-6 py-2 rounded-xl bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                        <span class="absolute right-2 top-2.5 text-[9px] text-rose-400 font-bold">g</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Live Macro Progress Tracker -->
                        <div class="p-2.5 rounded-xl bg-slate-900/90 border border-slate-800 text-white space-y-1.5">
                            <div class="flex items-center justify-between text-[10px]">
                                <span class="font-bold text-slate-300 uppercase tracking-wider flex items-center gap-1">
                                    <span>⚡ Scheduled Total vs Target</span>
                                </span>
                                <span class="font-mono text-emerald-400 font-bold" x-text="dietBuilder.meals.length + ' Meals Planned'"></span>
                            </div>
                            <div class="grid grid-cols-4 gap-1.5 text-center">
                                <div class="p-1.5 rounded-lg bg-slate-800/80 border border-slate-700/50">
                                    <span class="text-[9px] text-slate-400 block font-semibold">Calories</span>
                                    <span class="text-[11px] font-black text-amber-400 block" x-text="dietTotals.calories + ' / ' + (dietBuilder.daily_calories || 0)"></span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-800/80 border border-slate-700/50">
                                    <span class="text-[9px] text-slate-400 block font-semibold">Protein</span>
                                    <span class="text-[11px] font-black text-blue-400 block" x-text="dietTotals.protein + ' / ' + (dietBuilder.protein_grams || 0) + 'g'"></span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-800/80 border border-slate-700/50">
                                    <span class="text-[9px] text-slate-400 block font-semibold">Carbs</span>
                                    <span class="text-[11px] font-black text-amber-300 block" x-text="dietTotals.carbs + ' / ' + (dietBuilder.carbs_grams || 0) + 'g'"></span>
                                </div>
                                <div class="p-1.5 rounded-lg bg-slate-800/80 border border-slate-700/50">
                                    <span class="text-[9px] text-slate-400 block font-semibold">Fat</span>
                                    <span class="text-[11px] font-black text-rose-400 block" x-text="dietTotals.fat + ' / ' + (dietBuilder.fat_grams || 0) + 'g'"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Meals List -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                                    <span>🥗 Meals in this Plan</span>
                                    <span class="px-1.5 py-0.2 rounded text-[10px] font-bold bg-emerald-500/15 text-emerald-400" x-text="dietBuilder.meals.length"></span>
                                </label>
                                <span class="text-[10px] text-slate-400">Click ✏️ on any meal to edit items or macros</span>
                            </div>

                            <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                                <template x-for="(m, idx) in dietBuilder.meals" :key="idx + (m.name || '') + (m.time || '')">
                                    <div class="p-2.5 rounded-xl bg-white dark:bg-[#0d1320] border text-xs space-y-1.5 transition-all"
                                         :class="dietBuilder.editingIndex === idx ? 'border-emerald-500 ring-1 ring-emerald-500/30' : 'border-slate-200/80 dark:border-slate-800'">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex items-center gap-1.5 flex-wrap">
                                                <span class="px-1.5 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider bg-slate-100 dark:bg-slate-800 text-emerald-500" x-text="m.type ? m.type.replace('_', ' ') : 'MEAL'"></span>
                                                <span class="text-[10px] text-slate-400 font-mono" x-text="m.time"></span>
                                                <span class="font-bold text-slate-900 dark:text-white" x-text="m.name"></span>
                                            </div>
                                            <div class="flex items-center gap-1">
                                                <button type="button" 
                                                        @click="editMealInBuilder(idx)" 
                                                        class="p-1 rounded text-slate-400 hover:text-emerald-400 hover:bg-emerald-500/10 cursor-pointer" title="Edit Meal Items / Macros">
                                                    ✏️
                                                </button>
                                                <button type="button" 
                                                        @click="removeMealFromBuilder(idx)" 
                                                        class="p-1 rounded text-rose-400 hover:bg-rose-500/10 cursor-pointer" title="Remove Meal">
                                                    ✕
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Macros Breakdown -->
                                        <div class="flex items-center gap-1.5 flex-wrap text-[9px] font-bold">
                                            <template x-if="m.calories">
                                                <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-400" x-text="m.calories + ' kcal'"></span>
                                            </template>
                                            <template x-if="m.protein">
                                                <span class="px-1.5 py-0.5 rounded bg-blue-500/10 text-blue-400" x-text="'P: ' + m.protein + 'g'"></span>
                                            </template>
                                            <template x-if="m.carbs">
                                                <span class="px-1.5 py-0.5 rounded bg-amber-500/10 text-amber-300" x-text="'C: ' + m.carbs + 'g'"></span>
                                            </template>
                                            <template x-if="m.fat">
                                                <span class="px-1.5 py-0.5 rounded bg-rose-500/10 text-rose-400" x-text="'F: ' + m.fat + 'g'"></span>
                                            </template>
                                        </div>

                                        <!-- Multiple Food Items in this Meal -->
                                        <div class="p-2 rounded-lg bg-slate-50 dark:bg-slate-900/60 border border-slate-100 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line" x-text="m.description"></div>
                                    </div>
                                </template>

                                <template x-if="dietBuilder.meals.length === 0">
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-dashed border-slate-200 dark:border-slate-800 text-center text-xs text-slate-400">
                                        No meals added yet. Use the form below to configure Meal 1, Meal 2, etc.
                                    </div>
                                </template>
                            </div>

                            <!-- Add / Edit Meal Builder Form -->
                            <div class="p-3 rounded-xl bg-emerald-500/5 border border-emerald-500/25 space-y-2.5">
                                <div class="flex items-center justify-between">
                                    <span class="text-[10px] font-bold text-emerald-400 uppercase tracking-wider flex items-center gap-1.5">
                                        <span x-text="dietBuilder.editingIndex !== -1 ? '✏️ EDITING ' + (dietBuilder.newName || 'MEAL') : '+ ADD MEAL WITH FOOD ITEMS &amp; MACROS'"></span>
                                    </span>
                                    <template x-if="dietBuilder.editingIndex !== -1">
                                        <button type="button" @click="cancelEditMeal()" class="text-[10px] font-semibold text-slate-400 hover:text-white underline cursor-pointer">Cancel Edit</button>
                                    </template>
                                </div>

                                <!-- Slot & Time -->
                                <div class="grid grid-cols-3 gap-2">
                                    <div class="col-span-2">
                                        <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Meal Slot</label>
                                        <select x-model="dietBuilder.newType" @change="onMealTypeChange()" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                            <option value="breakfast">Breakfast</option>
                                            <option value="morning_snack">Morning Snack</option>
                                            <option value="lunch">Lunch</option>
                                            <option value="evening_snack">Evening Snack</option>
                                            <option value="dinner">Dinner</option>
                                            <option value="post_workout">Post-Workout</option>
                                        </select>
                                    </div>
                                    <div class="col-span-1">
                                        <label class="block text-[10px] font-semibold text-slate-400 mb-0.5 text-center">Time</label>
                                        <input type="text" x-model="dietBuilder.newTime" placeholder="08:00" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 text-center font-mono">
                                    </div>
                                </div>

                                <!-- Meal Label / Name -->
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Meal Name / Title</label>
                                    <input type="text" x-model="dietBuilder.newName" placeholder="e.g. Meal 1: Breakfast" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500">
                                </div>

                                <!-- Food items (allows multiple items like 4 Boiled Eggs, 4 Wheat Bread, 1 Glass Milk) -->
                                <div>
                                    <div class="flex items-center justify-between mb-0.5">
                                        <label class="block text-[10px] font-semibold text-slate-400">Food Items in this Meal *</label>
                                        <span class="text-[9px] text-slate-400">Type all items (separated by comma or newline)</span>
                                    </div>
                                    <textarea x-model="dietBuilder.newDesc" rows="2" placeholder="e.g. 4 Boiled Eggs, 4 Wheat Bread slices, 1 Glass Milk, 1 Banana" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 resize-none"></textarea>
                                </div>

                                <!-- 4 Macros Side-by-Side: Calories, Protein, Carbs, Fat -->
                                <div>
                                    <label class="block text-[10px] font-semibold text-slate-400 mb-0.5">Meal Macros Breakdown</label>
                                    <div class="grid grid-cols-4 gap-1.5">
                                        <div>
                                            <input type="number" x-model="dietBuilder.newCalories" placeholder="Kcal (450)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 text-center">
                                            <span class="text-[8px] text-slate-400 block text-center mt-0.5 font-bold">Kcal</span>
                                        </div>
                                        <div>
                                            <input type="number" x-model="dietBuilder.newProtein" placeholder="Protein (32)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 text-center">
                                            <span class="text-[8px] text-blue-400 block text-center mt-0.5 font-bold">Protein (g)</span>
                                        </div>
                                        <div>
                                            <input type="number" x-model="dietBuilder.newCarbs" placeholder="Carbs (45)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 text-center">
                                            <span class="text-[8px] text-amber-400 block text-center mt-0.5 font-bold">Carbs (g)</span>
                                        </div>
                                        <div>
                                            <input type="number" x-model="dietBuilder.newFat" placeholder="Fat (12)" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-[#0d1320] border border-slate-200 dark:border-slate-800 text-[11px] text-slate-900 dark:text-white focus:outline-none focus:border-emerald-500 text-center">
                                            <span class="text-[8px] text-rose-400 block text-center mt-0.5 font-bold">Fat (g)</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Add / Update Meal Button -->
                                <div>
                                    <button type="button" @click="saveMealToBuilder()" class="w-full py-2 rounded-lg bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-sm">
                                        <span x-text="dietBuilder.editingIndex !== -1 ? '✓ Update ' + (dietBuilder.newName || 'Meal') : '+ Save Meal to Plan'"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="pt-2 space-y-2">
                            <button type="submit" :disabled="planSubmitting" class="w-full py-3.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black text-xs transition-all cursor-pointer flex items-center justify-center gap-2 shadow-xs disabled:opacity-50">
                                <span x-text="planSubmitting ? 'Saving Diet Plan...' : 'Save & Assign Diet Plan (' + dietBuilder.meals.length + ' Meals • ' + dietTotals.calories + ' kcal)'"></span>
                            </button>
                            <template x-if="selectedMember?.active_diet">
                                <button type="button" 
                                        @click="removeDietPlan()" 
                                        :disabled="planSubmitting"
                                        class="w-full py-2.5 rounded-xl border border-rose-500/30 bg-rose-500/10 hover:bg-rose-500 hover:text-white text-rose-400 font-bold text-xs transition-all cursor-pointer flex items-center justify-center gap-1.5 shadow-xs disabled:opacity-50">
                                    🗑️ Remove Active Diet Plan from Client
                                </button>
                            </template>
                        </div>
                    </form>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
