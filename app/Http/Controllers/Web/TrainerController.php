<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DietPlan;
use App\Models\Member;
use App\Models\MemberPtPackage;
use App\Models\PtSession;
use App\Models\Trainer;
use App\Models\User;
use App\Models\WorkoutPlan;
use App\Services\TenantContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class TrainerController extends Controller
{
    public function workspace(Request $request): View|RedirectResponse
    {
        $user = auth()->user();
        $tenant = TenantContext::getTenant() ?? $user->tenant;

        if (! $tenant) {
            return redirect()->route('login');
        }

        // Resolve Trainer Profile
        $trainer = $this->resolveTrainer($user, $request);

        $activeTab = $request->query('tab', 'today');
        if (! in_array($activeTab, ['today', 'members', 'sessions', 'profile'])) {
            $activeTab = 'today';
        }

        // Server-side Scoping: Assigned members OR members in trainer's branch
        $assignedMembers = $trainer ? Member::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($trainer, $user) {
                if (in_array($user->role, ['super_admin', 'gym_owner', 'gym_manager'])) {
                    if ($trainer->branch_id) {
                        $q->where('branch_id', $trainer->branch_id);
                    }
                } else {
                    $q->where('trainer_id', $trainer->id)
                        ->orWhereHas('memberPtPackages', fn ($sub) => $sub->where('trainer_id', $trainer->id));
                    if ($trainer->branch_id) {
                        $q->orWhere(function ($branchQ) use ($trainer) {
                            $branchQ->where('branch_id', $trainer->branch_id)
                                ->where(function ($tQ) use ($trainer) {
                                    $tQ->whereNull('trainer_id')->orWhere('trainer_id', $trainer->id);
                                });
                        });
                    }
                }
            })
            ->when($trainer->branch_id, fn ($q) => $q->where('branch_id', $trainer->branch_id))
            ->with([
                'branch',
                'activeWorkoutPlan.exercises',
                'activeDietPlan.meals',
                'memberPtPackages' => fn ($q) => $q->where(function ($sub) use ($trainer) {
                    $sub->where('trainer_id', $trainer->id)->orWhereNull('trainer_id');
                })->where('status', 'ACTIVE')->where('end_date', '>=', now()->toDateString())->with('ptPlan'),
                'ptSessions' => fn ($q) => $q->where('trainer_id', $trainer->id)->latest('session_date')->latest('start_time')->take(5),
            ])
            ->get() : collect();

        // 1. Screen "Today": Today's scheduled PT sessions
        $todaySessions = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->whereDate('session_date', now()->toDateString())
            ->whereIn('status', ['SCHEDULED', 'CONFIRMED', 'COMPLETED'])
            ->with(['member.branch', 'memberPtPackage'])
            ->orderBy('start_time')
            ->get() : collect();

        // 2. Pending Confirmations (Awaiting member scan/confirm)
        $pendingSessions = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($trainer, $user) {
                $q->where('trainer_id', $trainer->id)
                    ->orWhere('logged_by_user_id', $user->id);
            })
            ->where('status', 'PENDING')
            ->with(['member.branch', 'memberPtPackage'])
            ->latest()
            ->get() : collect();

        // 3. Screen "PT Sessions": History with status filters
        $sessionStatus = $request->query('session_status', 'all');
        $sessionsQuery = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where(function ($q) use ($trainer, $user) {
                $q->where('trainer_id', $trainer->id)
                    ->orWhere('logged_by_user_id', $user->id);
            })
            ->with(['member.branch', 'memberPtPackage', 'loggedByUser', 'confirmedByMember'])
            ->latest('session_date')
            ->latest('start_time') : null;

        if ($sessionsQuery) {
            if ($sessionStatus === 'pending') {
                $sessionsQuery->where('status', 'PENDING');
            } elseif ($sessionStatus === 'confirmed') {
                $sessionsQuery->whereIn('status', ['CONFIRMED', 'COMPLETED']);
            } elseif ($sessionStatus === 'issue_reported') {
                $sessionsQuery->where('status', 'ISSUE_REPORTED');
            } elseif ($sessionStatus === 'scheduled') {
                $sessionsQuery->where('status', 'SCHEDULED');
            }

            $allSessions = $sessionsQuery->paginate(20, ['*'], 'sessions_page')->withQueryString();
        } else {
            $allSessions = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 20);
        }

        // Renewal Alerts (Packages with <= 2 sessions remaining)
        $renewalAlerts = $assignedMembers->filter(function ($member) {
            $pkg = $member->memberPtPackages->first();
            return $pkg && $pkg->remaining_sessions <= 2;
        })->values();

        // Trainer Rating Stats
        $averageRating = $trainer ? round((float) PtSession::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->whereNotNull('rating')
            ->avg('rating'), 1) : 5.0;
        if ($averageRating <= 0) {
            $averageRating = 5.0;
        }

        $totalRatingsCount = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->whereNotNull('rating')
            ->count() : 0;

        // Workout and Diet Templates for quick assigning
        $workoutTemplates = WorkoutPlan::where('tenant_id', $tenant->id)
            ->where('is_template', true)
            ->select(['id', 'title', 'goal', 'level'])
            ->get();

        $dietTemplates = DietPlan::where('tenant_id', $tenant->id)
            ->where('is_template', true)
            ->select(['id', 'title', 'daily_calories', 'protein_grams', 'carbs_grams', 'fat_grams'])
            ->get();

        $workoutPresets = array_values($this->getWorkoutPresets());
        $dietPresets = array_values($this->getDietPresets());

        // Stats
        $todayCount = $todaySessions->count();
        $todayCompletedCount = $todaySessions->whereIn('status', ['CONFIRMED', 'COMPLETED'])->count();
        $todayScheduledCount = $todaySessions->where('status', 'SCHEDULED')->count();
        $pendingCount = $pendingSessions->count();
        $assignedCount = $assignedMembers->count();
        $completedTotalCount = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->whereIn('status', ['CONFIRMED', 'COMPLETED'])
            ->count() : 0;
        $issueReportedCount = $trainer ? PtSession::where('tenant_id', $tenant->id)
            ->where('trainer_id', $trainer->id)
            ->where('status', 'ISSUE_REPORTED')
            ->count() : 0;

        // Active eligible packages for the 3-step logger
        $eligiblePackages = $assignedMembers->flatMap(function ($member) {
            return $member->memberPtPackages->filter(fn ($pkg) => $pkg->remaining_sessions > 0)->each(function ($pkg) use ($member) {
                $pkg->setRelation('member', $member);
            });
        })->values();

        return view('trainer.workspace', compact(
            'tenant',
            'user',
            'trainer',
            'activeTab',
            'assignedMembers',
            'todaySessions',
            'pendingSessions',
            'allSessions',
            'todayCount',
            'todayCompletedCount',
            'todayScheduledCount',
            'pendingCount',
            'assignedCount',
            'completedTotalCount',
            'issueReportedCount',
            'eligiblePackages',
            'sessionStatus',
            'renewalAlerts',
            'averageRating',
            'totalRatingsCount',
            'workoutTemplates',
            'dietTemplates',
            'workoutPresets',
            'dietPresets'
        ));
    }

    protected function resolveTrainer(User $user, ?Request $request = null): ?Trainer
    {
        $tenant = TenantContext::getTenant() ?? $user->tenant;
        if (! $tenant) {
            return null;
        }

        $trainer = $user->trainerProfile;
        if (! $trainer) {
            $trainer = Trainer::where('tenant_id', $tenant->id)
                ->where(function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                    if (! empty($user->phone)) {
                        $q->orWhere('phone', $user->phone);
                    }
                    if (! empty($user->email)) {
                        $q->orWhere('email', $user->email);
                    }
                })->first();
        }

        // Gym Owner / Manager inspecting trainer workspace
        if (! $trainer && in_array($user->role, ['super_admin', 'gym_owner', 'gym_manager'])) {
            $trainerId = $request?->query('trainer_id') ?? $request?->input('trainer_id');
            if ($trainerId) {
                $trainer = Trainer::where('tenant_id', $tenant->id)->find($trainerId);
            }
            if (! $trainer) {
                $trainer = Trainer::where('tenant_id', $tenant->id)->first();
            }
        }

        return $trainer;
    }

    protected function authorizeTrainerMember(User $user, int $memberId, ?Request $request = null): Member
    {
        $tenant = TenantContext::getTenant() ?? $user->tenant;
        $trainer = $this->resolveTrainer($user, $request);

        $memberQuery = Member::where('tenant_id', $tenant->id)->where('id', $memberId);
        if (! in_array($user->role, ['super_admin', 'gym_owner', 'gym_manager'])) {
            $memberQuery->where(function ($q) use ($trainer) {
                if ($trainer) {
                    $q->where('trainer_id', $trainer->id)
                        ->orWhereHas('memberPtPackages', fn ($sub) => $sub->where('trainer_id', $trainer->id));
                    if ($trainer->branch_id) {
                        $q->orWhere('branch_id', $trainer->branch_id);
                    }
                }
            });
        }

        return $memberQuery->firstOrFail();
    }

    public function storeMeasurement(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);

        $validated = $request->validate([
            'date' => 'nullable|date',
            'weight' => 'nullable|numeric|min:20|max:500',
            'body_fat' => 'nullable|numeric|min:1|max:70',
            'waist' => 'nullable|numeric|min:10|max:200',
            'chest' => 'nullable|numeric|min:10|max:200',
            'bicep' => 'nullable|numeric|min:5|max:100',
            'hips' => 'nullable|numeric|min:10|max:200',
            'thighs' => 'nullable|numeric|min:5|max:150',
            'shoulders' => 'nullable|numeric|min:10|max:200',
            'calves' => 'nullable|numeric|min:5|max:100',
            'neck' => 'nullable|numeric|min:5|max:100',
            'custom_metrics' => 'nullable|array',
            'notes' => 'nullable|string|max:500',
        ]);

        $meta = $member->metadata ?? [];
        $measurements = $meta['measurements'] ?? [];

        $customMetrics = [];
        if (! empty($validated['custom_metrics']) && is_array($validated['custom_metrics'])) {
            foreach ($validated['custom_metrics'] as $cm) {
                if (! empty($cm['name']) && isset($cm['value']) && $cm['value'] !== '') {
                    $customMetrics[] = [
                        'name' => trim((string) $cm['name']),
                        'value' => trim((string) $cm['value']),
                    ];
                }
            }
        }

        $entryDate = $validated['date'] ?? now()->toDateString();

        $entry = [
            'id' => uniqid(),
            'date' => $entryDate,
            'weight' => $validated['weight'] ?? null,
            'body_fat' => $validated['body_fat'] ?? null,
            'waist' => $validated['waist'] ?? null,
            'chest' => $validated['chest'] ?? null,
            'bicep' => $validated['bicep'] ?? null,
            'hips' => $validated['hips'] ?? null,
            'thighs' => $validated['thighs'] ?? null,
            'shoulders' => $validated['shoulders'] ?? null,
            'calves' => $validated['calves'] ?? null,
            'neck' => $validated['neck'] ?? null,
            'custom_metrics' => $customMetrics,
            'notes' => $validated['notes'] ?? null,
            'logged_by' => $user->name,
            'created_at' => now()->toDateTimeString(),
        ];

        array_unshift($measurements, $entry);

        // Sort measurements strictly by date descending
        usort($measurements, function ($a, $b) {
            return strcmp($b['date'] ?? '', $a['date'] ?? '');
        });

        // Set latest weight from newest entry
        if (! empty($measurements[0]['weight'])) {
            $meta['weight'] = $measurements[0]['weight'];
        }

        $meta['measurements'] = array_slice($measurements, 0, 50);
        $member->metadata = $meta;
        $member->save();

        ActivityLog::log('measurement_logged', "Logged body measurements for member {$member->full_name} for date {$entryDate}", $member);

        return response()->json([
            'success' => true,
            'message' => 'Measurement logged successfully!',
            'measurement' => $entry,
            'measurements' => $meta['measurements'],
        ]);
    }

    public function deleteMeasurement(Request $request, int $id, string $measurementId): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);

        $meta = $member->metadata ?? [];
        $measurements = $meta['measurements'] ?? [];

        $measurements = array_values(array_filter($measurements, fn ($m) => ($m['id'] ?? '') !== $measurementId));

        if (! empty($measurements) && ! empty($measurements[0]['weight'])) {
            $meta['weight'] = $measurements[0]['weight'];
        }

        $meta['measurements'] = $measurements;
        $member->metadata = $meta;
        $member->save();

        ActivityLog::log('measurement_deleted', "Deleted a body measurement log for member {$member->full_name}", $member);

        return response()->json([
            'success' => true,
            'message' => 'Measurement removed successfully!',
            'measurements' => $measurements,
        ]);
    }

    public function assignWorkout(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);
        $tenant = TenantContext::getTenant() ?? $user->tenant;
        $trainer = $this->resolveTrainer($user, $request);

        $presetId = $request->input('preset_id');
        $templateId = $request->input('template_id');
        $title = trim((string) $request->input('title', ''));
        $goal = $this->normalizeWorkoutGoal($request->input('goal', 'general_fitness'));
        $level = $this->normalizeWorkoutLevel($request->input('level', 'intermediate'));
        $notes = trim((string) $request->input('notes', ''));

        $presets = $this->getWorkoutPresets();

        if (empty($title)) {
            if ($presetId && isset($presets[$presetId])) {
                $title = $presets[$presetId]['title'];
            } else {
                $title = 'Custom Workout Routine';
            }
        }

        $newPlan = WorkoutPlan::create([
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'trainer_id' => $trainer?->id,
            'title' => $title,
            'goal' => $goal,
            'level' => $level,
            'start_date' => now()->toDateString(),
            'is_template' => false,
            'notes' => $notes,
        ]);

        $customExercises = $request->input('exercises', []);
        $allowedDays = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday', 'day_1', 'day_2', 'day_3', 'day_4', 'day_5', 'day_6', 'day_7'];

        // 1. If dynamic exercise list was sent from mobile builder
        if (! empty($customExercises) && is_array($customExercises)) {
            $sort = 1;
            foreach ($customExercises as $ex) {
                $name = trim((string) ($ex['name'] ?? $ex['exercise_name'] ?? ''));
                if (empty($name)) {
                    continue;
                }
                $day = strtolower(trim((string) ($ex['day'] ?? 'day_1')));
                if (! in_array($day, $allowedDays)) {
                    $day = 'day_1';
                }
                $newPlan->exercises()->create([
                    'day' => $day,
                    'exercise_name' => $name,
                    'sets' => max(1, (int) ($ex['sets'] ?? 3)),
                    'reps' => trim((string) ($ex['reps'] ?? '10-12')),
                    'weight' => ! empty($ex['weight']) ? trim((string) $ex['weight']) : null,
                    'rest_seconds' => max(0, (int) ($ex['rest'] ?? $ex['rest_seconds'] ?? 60)),
                    'notes' => ! empty($ex['notes']) ? trim((string) $ex['notes']) : null,
                    'sort_order' => $sort++,
                ]);
            }
        }
        // 2. Otherwise if built-in preset was selected
        elseif ($presetId && isset($presets[$presetId])) {
            foreach ($presets[$presetId]['exercises'] as $ex) {
                $newPlan->exercises()->create($ex);
            }
        }
        // 3. Otherwise if DB template was selected
        elseif ($templateId) {
            $template = WorkoutPlan::where('tenant_id', $tenant->id)
                ->where('is_template', true)
                ->with('exercises')
                ->find($templateId);

            if ($template) {
                foreach ($template->exercises as $ex) {
                    $exData = $ex->toArray();
                    unset($exData['id'], $exData['workout_plan_id'], $exData['created_at'], $exData['updated_at']);
                    $newPlan->exercises()->create($exData);
                }
            }
        }

        $newPlan->load('exercises');

        return response()->json([
            'success' => true,
            'message' => "Workout plan '{$newPlan->title}' assigned to {$member->full_name}!",
            'plan' => [
                'id' => $newPlan->id,
                'title' => $newPlan->title,
                'goal' => $newPlan->goal,
                'level' => $newPlan->level,
                'exercises_count' => $newPlan->exercises->count(),
                'exercises' => $newPlan->exercises->map(fn ($e) => [
                    'day' => $e->day,
                    'name' => $e->exercise_name,
                    'sets' => $e->sets,
                    'reps' => $e->reps,
                    'rest' => $e->rest_seconds,
                    'notes' => $e->notes,
                ]),
            ],
        ]);
    }

    public function assignDiet(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);
        $tenant = TenantContext::getTenant() ?? $user->tenant;
        $trainer = $this->resolveTrainer($user, $request);

        $presetId = $request->input('preset_id');
        $templateId = $request->input('template_id');
        $title = trim((string) $request->input('title', ''));
        $calories = (int) $request->input('daily_calories', 2000);
        $protein = (int) $request->input('protein_grams', 130);
        $carbs = (int) $request->input('carbs_grams', 200);
        $fat = (int) $request->input('fat_grams', 60);
        $guidelines = trim((string) $request->input('guidelines', ''));

        $presets = $this->getDietPresets();

        if (empty($title)) {
            if ($presetId && isset($presets[$presetId])) {
                $title = $presets[$presetId]['title'];
            } else {
                $title = 'Custom Nutrition Blueprint';
            }
        }

        $newPlan = DietPlan::create([
            'tenant_id' => $tenant->id,
            'member_id' => $member->id,
            'trainer_id' => $trainer?->id,
            'title' => $title,
            'daily_calories' => $calories,
            'protein_grams' => $protein,
            'carbs_grams' => $carbs,
            'fat_grams' => $fat,
            'start_date' => now()->toDateString(),
            'is_template' => false,
            'guidelines' => $guidelines,
        ]);

        $customMeals = $request->input('meals', []);
        $allowedMealTypes = ['breakfast', 'morning_snack', 'lunch', 'evening_snack', 'dinner', 'post_workout'];

        // 1. If dynamic food / meals list was sent from mobile builder
        if (! empty($customMeals) && is_array($customMeals)) {
            $sort = 1;
            foreach ($customMeals as $m) {
                $mealName = trim((string) ($m['name'] ?? $m['meal_name'] ?? ''));
                $foodDesc = trim((string) ($m['description'] ?? $m['items_description'] ?? ''));
                if (empty($mealName) && empty($foodDesc)) {
                    continue;
                }
                if (empty($mealName)) {
                    $mealName = 'Meal ' . $sort;
                }
                if (empty($foodDesc)) {
                    $foodDesc = $mealName;
                }

                $type = strtolower(trim((string) ($m['type'] ?? $m['meal_type'] ?? 'breakfast')));
                if (! in_array($type, $allowedMealTypes)) {
                    $type = 'breakfast';
                }

                $time = ! empty($m['time'] ?? $m['recommended_time'] ?? null) ? trim((string) ($m['time'] ?? $m['recommended_time'])) : null;
                if ($time && strlen($time) === 5) {
                    $time .= ':00';
                }

                $newPlan->meals()->create([
                    'meal_type' => $type,
                    'recommended_time' => $time,
                    'meal_name' => $mealName,
                    'items_description' => $foodDesc,
                    'calories' => isset($m['calories']) && $m['calories'] !== '' ? (int) $m['calories'] : null,
                    'protein_grams' => isset($m['protein']) && $m['protein'] !== '' ? (int) $m['protein'] : (isset($m['protein_grams']) ? (int) $m['protein_grams'] : null),
                    'carbs_grams' => isset($m['carbs']) && $m['carbs'] !== '' ? (int) $m['carbs'] : (isset($m['carbs_grams']) ? (int) $m['carbs_grams'] : null),
                    'fat_grams' => isset($m['fat']) && $m['fat'] !== '' ? (int) $m['fat'] : (isset($m['fat_grams']) ? (int) $m['fat_grams'] : null),
                    'sort_order' => $sort++,
                ]);
            }
        }
        // 2. Otherwise if built-in preset was selected
        elseif ($presetId && isset($presets[$presetId])) {
            foreach ($presets[$presetId]['meals'] as $meal) {
                $newPlan->meals()->create($meal);
            }
        }
        // 3. Otherwise if DB template was selected
        elseif ($templateId) {
            $template = DietPlan::where('tenant_id', $tenant->id)
                ->where('is_template', true)
                ->with('meals')
                ->find($templateId);

            if ($template) {
                foreach ($template->meals as $meal) {
                    $newPlan->meals()->create([
                        'meal_type' => $meal->meal_type,
                        'recommended_time' => $meal->recommended_time,
                        'meal_name' => $meal->meal_name,
                        'items_description' => $meal->items_description,
                        'calories' => $meal->calories,
                        'sort_order' => $meal->sort_order,
                    ]);
                }
            }
        }

        $newPlan->load('meals');

        return response()->json([
            'success' => true,
            'message' => "Diet plan '{$newPlan->title}' assigned to {$member->full_name}!",
            'plan' => [
                'id' => $newPlan->id,
                'title' => $newPlan->title,
                'daily_calories' => $newPlan->daily_calories,
                'protein_grams' => $newPlan->protein_grams,
                'carbs_grams' => $newPlan->carbs_grams,
                'fat_grams' => $newPlan->fat_grams,
                'meals_count' => $newPlan->meals->count(),
                'meals' => $newPlan->meals->map(fn ($m) => [
                    'type' => $m->meal_type,
                    'time' => $m->recommended_time ? substr((string) $m->recommended_time, 0, 5) : '',
                    'name' => $m->meal_name,
                    'description' => $m->items_description,
                    'calories' => $m->calories,
                    'protein' => $m->protein_grams,
                    'carbs' => $m->carbs_grams,
                    'fat' => $m->fat_grams,
                ]),
            ],
        ]);
    }

    public function removeWorkoutPlan(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);

        WorkoutPlan::where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->where('is_template', false)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Workout plan unassigned from {$member->full_name}.",
        ]);
    }

    public function removeDietPlan(Request $request, int $id): JsonResponse
    {
        $user = auth()->user();
        $member = $this->authorizeTrainerMember($user, $id, $request);

        DietPlan::where('tenant_id', $member->tenant_id)
            ->where('member_id', $member->id)
            ->where('is_template', false)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => "Diet plan unassigned from {$member->full_name}.",
        ]);
    }

    /**
     * Map arbitrary goal string to strictly allowed workout_plans.goal enum values:
     * 'weight_loss', 'muscle_gain', 'endurance', 'general_fitness', 'flexibility'
     */
    protected function normalizeWorkoutGoal(?string $goal): string
    {
        $goal = strtolower(trim((string) $goal));

        return match ($goal) {
            'fat_loss', 'weight_loss', 'cutting', 'weight loss', 'fat loss & toning' => 'weight_loss',
            'muscle_gain', 'hypertrophy', 'muscle hypertrophy', 'bulking', 'muscle gain', 'strength', 'strength & conditioning' => 'muscle_gain',
            'endurance', 'cardio', 'stamina' => 'endurance',
            'flexibility', 'mobility', 'yoga', 'mobility & posture' => 'flexibility',
            default => 'general_fitness',
        };
    }

    /**
     * Map arbitrary level string to strictly allowed workout_plans.level enum values:
     * 'beginner', 'intermediate', 'advanced'
     */
    protected function normalizeWorkoutLevel(?string $level): string
    {
        $level = strtolower(trim((string) $level));

        return match ($level) {
            'advanced', 'pro', 'expert' => 'advanced',
            'beginner', 'novice', 'entry' => 'beginner',
            default => 'intermediate',
        };
    }

    /**
     * Pre-built battle-tested gym workout splits
     */
    protected function getWorkoutPresets(): array
    {
        return [
            'ppl_4day' => [
                'id' => 'ppl_4day',
                'title' => '4-Day Push / Pull / Legs Split',
                'badge' => 'Most Popular',
                'goal' => 'muscle_gain',
                'goal_label' => 'Muscle Hypertrophy',
                'level' => 'intermediate',
                'summary' => '4 days/week • Chest, Back, Legs & Shoulders with progressive volume',
                'exercises' => [
                    ['day' => 'day_1', 'exercise_name' => 'Barbell Bench Press', 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 90, 'notes' => 'Push: Chest Flat', 'sort_order' => 1],
                    ['day' => 'day_1', 'exercise_name' => 'Incline Dumbbell Press', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 60, 'notes' => 'Push: Upper Chest', 'sort_order' => 2],
                    ['day' => 'day_1', 'exercise_name' => 'Cable Triceps Pushdown', 'sets' => 3, 'reps' => '12-15', 'rest_seconds' => 60, 'notes' => 'Push: Triceps', 'sort_order' => 3],
                    ['day' => 'day_2', 'exercise_name' => 'Barbell Bent-Over Row', 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 90, 'notes' => 'Pull: Mid Back', 'sort_order' => 4],
                    ['day' => 'day_2', 'exercise_name' => 'Lat Pulldown (Wide Grip)', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 60, 'notes' => 'Pull: Lats', 'sort_order' => 5],
                    ['day' => 'day_2', 'exercise_name' => 'Barbell Bicep Curls', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 60, 'notes' => 'Pull: Biceps', 'sort_order' => 6],
                    ['day' => 'day_3', 'exercise_name' => 'Barbell Back Squat', 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 120, 'notes' => 'Legs: Quads & Glutes', 'sort_order' => 7],
                    ['day' => 'day_3', 'exercise_name' => 'Romanian Deadlift', 'sets' => 3, 'reps' => '10-12', 'rest_seconds' => 90, 'notes' => 'Legs: Hamstrings', 'sort_order' => 8],
                    ['day' => 'day_3', 'exercise_name' => 'Standing Calf Raises', 'sets' => 4, 'reps' => '15', 'rest_seconds' => 45, 'notes' => 'Legs: Calves', 'sort_order' => 9],
                    ['day' => 'day_4', 'exercise_name' => 'Overhead Military Press', 'sets' => 4, 'reps' => '8-10', 'rest_seconds' => 90, 'notes' => 'Shoulders & Core', 'sort_order' => 10],
                    ['day' => 'day_4', 'exercise_name' => 'Dumbbell Lateral Raise', 'sets' => 4, 'reps' => '12-15', 'rest_seconds' => 45, 'notes' => 'Side Delts', 'sort_order' => 11],
                    ['day' => 'day_4', 'exercise_name' => 'Hanging Leg Raises', 'sets' => 3, 'reps' => '15', 'rest_seconds' => 45, 'notes' => 'Core & Lower Abs', 'sort_order' => 12],
                ],
            ],
            'fat_loss_3day' => [
                'id' => 'fat_loss_3day',
                'title' => '3-Day Fat Loss & Metabolic Circuit',
                'badge' => 'High Burn',
                'goal' => 'weight_loss',
                'goal_label' => 'Fat Loss & Conditioning',
                'level' => 'beginner',
                'summary' => '3 days/week • High calorie burn, metabolic circuits & active core',
                'exercises' => [
                    ['day' => 'day_1', 'exercise_name' => 'Goblet Squats', 'sets' => 3, 'reps' => '15', 'rest_seconds' => 45, 'notes' => 'Full Body HIIT', 'sort_order' => 1],
                    ['day' => 'day_1', 'exercise_name' => 'Push-Ups (or Knee Push-Ups)', 'sets' => 3, 'reps' => '12-15', 'rest_seconds' => 45, 'notes' => 'Chest & Core', 'sort_order' => 2],
                    ['day' => 'day_1', 'exercise_name' => 'Mountain Climbers', 'sets' => 3, 'reps' => '40 sec', 'rest_seconds' => 30, 'notes' => 'Cardio Circuit', 'sort_order' => 3],
                    ['day' => 'day_2', 'exercise_name' => 'Kettlebell Swings', 'sets' => 4, 'reps' => '15', 'rest_seconds' => 45, 'notes' => 'Posterior Chain', 'sort_order' => 4],
                    ['day' => 'day_2', 'exercise_name' => 'Dumbbell Renegade Rows', 'sets' => 3, 'reps' => '10 each', 'rest_seconds' => 45, 'notes' => 'Back & Core Stability', 'sort_order' => 5],
                    ['day' => 'day_2', 'exercise_name' => 'Jump Rope / High Knees', 'sets' => 4, 'reps' => '45 sec', 'rest_seconds' => 30, 'notes' => 'High Intensity Cardio', 'sort_order' => 6],
                    ['day' => 'day_3', 'exercise_name' => 'Walking Dumbbell Lunges', 'sets' => 3, 'reps' => '12 each', 'rest_seconds' => 45, 'notes' => 'Lower Body Conditioning', 'sort_order' => 7],
                    ['day' => 'day_3', 'exercise_name' => 'Dumbbell Shoulder Press', 'sets' => 3, 'reps' => '12', 'rest_seconds' => 45, 'notes' => 'Shoulders & Triceps', 'sort_order' => 8],
                    ['day' => 'day_3', 'exercise_name' => 'Forearm Plank Hold', 'sets' => 3, 'reps' => '45 sec', 'rest_seconds' => 30, 'notes' => 'Isometric Core', 'sort_order' => 9],
                ],
            ],
            'strength_3day' => [
                'id' => 'strength_3day',
                'title' => '3-Day Full Body Strength & Power',
                'badge' => 'Pure Strength',
                'goal' => 'muscle_gain',
                'goal_label' => 'Strength & Power',
                'level' => 'advanced',
                'summary' => '3 days/week • Heavy compound lifts (Squat, Deadlift, Bench Press)',
                'exercises' => [
                    ['day' => 'day_1', 'exercise_name' => 'Barbell Back Squat', 'sets' => 5, 'reps' => '5', 'rest_seconds' => 180, 'notes' => 'Heavy Squat Day', 'sort_order' => 1],
                    ['day' => 'day_1', 'exercise_name' => 'Standing Overhead Press', 'sets' => 5, 'reps' => '5', 'rest_seconds' => 120, 'notes' => 'Heavy Press Day', 'sort_order' => 2],
                    ['day' => 'day_2', 'exercise_name' => 'Conventional Deadlift', 'sets' => 5, 'reps' => '5', 'rest_seconds' => 180, 'notes' => 'Heavy Pull Day', 'sort_order' => 3],
                    ['day' => 'day_2', 'exercise_name' => 'Weighted Pull-Ups', 'sets' => 4, 'reps' => '6-8', 'rest_seconds' => 120, 'notes' => 'Upper Pull Day', 'sort_order' => 4],
                    ['day' => 'day_3', 'exercise_name' => 'Flat Barbell Bench Press', 'sets' => 5, 'reps' => '5', 'rest_seconds' => 180, 'notes' => 'Heavy Chest Day', 'sort_order' => 5],
                    ['day' => 'day_3', 'exercise_name' => 'Barbell Pendlay Rows', 'sets' => 5, 'reps' => '5', 'rest_seconds' => 120, 'notes' => 'Horizontal Pull Day', 'sort_order' => 6],
                ],
            ],
        ];
    }

    /**
     * Pre-built high-performance diet blueprints
     */
    protected function getDietPresets(): array
    {
        return [
            'muscle_gain_2400' => [
                'id' => 'muscle_gain_2400',
                'title' => 'High Protein Muscle Gain (2,400 kcal)',
                'badge' => 'Bulking & Mass',
                'daily_calories' => 2400,
                'protein_grams' => 160,
                'carbs_grams' => 260,
                'fat_grams' => 65,
                'guidelines' => 'Drink at least 3.5L of water daily. Ensure 7-8 hours of quality sleep for muscular hypertrophy.',
                'meals' => [
                    ['meal_type' => 'breakfast', 'recommended_time' => '08:00:00', 'meal_name' => 'Meal 1: Power Breakfast', 'items_description' => '3 whole eggs + 2 egg whites omelet, 2 slices whole wheat toast, 1 banana, black coffee', 'calories' => 520, 'protein_grams' => 34, 'carbs_grams' => 52, 'fat_grams' => 18, 'sort_order' => 1],
                    ['meal_type' => 'morning_snack', 'recommended_time' => '11:00:00', 'meal_name' => 'Meal 2: Mid-Morning Fuel', 'items_description' => '1 scoop Whey Protein in water/milk, 30g almonds & walnuts', 'calories' => 340, 'protein_grams' => 30, 'carbs_grams' => 10, 'fat_grams' => 20, 'sort_order' => 2],
                    ['meal_type' => 'lunch', 'recommended_time' => '13:30:00', 'meal_name' => 'Meal 3: Lean Protein Lunch', 'items_description' => '150g grilled chicken breast (or 150g paneer), 1.5 cup brown rice, 1 bowl yellow dal, fresh green salad', 'calories' => 680, 'protein_grams' => 48, 'carbs_grams' => 85, 'fat_grams' => 16, 'sort_order' => 3],
                    ['meal_type' => 'evening_snack', 'recommended_time' => '17:00:00', 'meal_name' => 'Meal 4: Pre-Workout Snack', 'items_description' => '2 multigrain toasts with 2 tbsp peanut butter, 1 apple', 'calories' => 310, 'protein_grams' => 12, 'carbs_grams' => 45, 'fat_grams' => 9, 'sort_order' => 4],
                    ['meal_type' => 'dinner', 'recommended_time' => '20:30:00', 'meal_name' => 'Meal 5: Recovery Dinner', 'items_description' => '150g grilled fish / paneer bhurji, 2 multigrain rotis, 1 bowl steamed broccoli & cucumbers, 1 cup curd', 'calories' => 550, 'protein_grams' => 36, 'carbs_grams' => 68, 'fat_grams' => 14, 'sort_order' => 5],
                ],
            ],
            'fat_loss_1600' => [
                'id' => 'fat_loss_1600',
                'title' => 'Calorie Deficit Fat Loss (1,600 kcal)',
                'badge' => 'Cutting & Shred',
                'daily_calories' => 1600,
                'protein_grams' => 140,
                'carbs_grams' => 140,
                'fat_grams' => 45,
                'guidelines' => 'Avoid sugary beverages and refined oils. Prioritize green vegetables and lean proteins at each meal.',
                'meals' => [
                    ['meal_type' => 'breakfast', 'recommended_time' => '08:30:00', 'meal_name' => 'Meal 1: Lean Breakfast', 'items_description' => '4 egg whites scrambled with baby spinach, tomatoes and mushrooms, 1 slice multigrain toast, green tea', 'calories' => 280, 'protein_grams' => 26, 'carbs_grams' => 22, 'fat_grams' => 8, 'sort_order' => 1],
                    ['meal_type' => 'lunch', 'recommended_time' => '13:00:00', 'meal_name' => 'Meal 2: Shred Lunch', 'items_description' => '150g grilled lemon chicken / boiled soya chunks, large bowl tossed cucumber tomato salad, 1 phulka', 'calories' => 480, 'protein_grams' => 44, 'carbs_grams' => 42, 'fat_grams' => 12, 'sort_order' => 2],
                    ['meal_type' => 'evening_snack', 'recommended_time' => '17:00:00', 'meal_name' => 'Meal 3: Metabolic Snack', 'items_description' => '1 scoop whey isolate with water or 1 bowl boiled sprouted moong chaat with lemon', 'calories' => 220, 'protein_grams' => 26, 'carbs_grams' => 18, 'fat_grams' => 4, 'sort_order' => 3],
                    ['meal_type' => 'dinner', 'recommended_time' => '20:00:00', 'meal_name' => 'Meal 4: Light Dinner', 'items_description' => 'Hot vegetable clear soup, 120g stir-fry tofu / chicken with bell peppers & mushrooms, cucumbers', 'calories' => 420, 'protein_grams' => 34, 'carbs_grams' => 32, 'fat_grams' => 14, 'sort_order' => 4],
                    ['meal_type' => 'post_workout', 'recommended_time' => '22:00:00', 'meal_name' => 'Meal 5: Night Fuel', 'items_description' => '1 cup warm unsweetened almond milk / skimmed milk with a pinch of turmeric', 'calories' => 200, 'protein_grams' => 10, 'carbs_grams' => 12, 'fat_grams' => 7, 'sort_order' => 5],
                ],
            ],
            'pure_veg_2000' => [
                'id' => 'pure_veg_2000',
                'title' => 'Pure Veg High Protein (2,000 kcal)',
                'badge' => '100% Vegetarian',
                'daily_calories' => 2000,
                'protein_grams' => 130,
                'carbs_grams' => 220,
                'fat_grams' => 55,
                'guidelines' => 'Ensure a protein source (paneer, soya, dal, curd) in every single meal. Stay well hydrated.',
                'meals' => [
                    ['meal_type' => 'breakfast', 'recommended_time' => '08:30:00', 'meal_name' => 'Meal 1: Paneer Besan Chilla', 'items_description' => '2 besan chillas stuffed with 75g crumbled low-fat paneer, green mint coriander chutney', 'calories' => 440, 'protein_grams' => 26, 'carbs_grams' => 42, 'fat_grams' => 18, 'sort_order' => 1],
                    ['meal_type' => 'morning_snack', 'recommended_time' => '11:30:00', 'meal_name' => 'Meal 2: Protein Shake / Chana', 'items_description' => '1 scoop plant/whey protein or 1 tall glass chilled buttermilk with 40g roasted chana', 'calories' => 250, 'protein_grams' => 24, 'carbs_grams' => 22, 'fat_grams' => 6, 'sort_order' => 2],
                    ['meal_type' => 'lunch', 'recommended_time' => '13:30:00', 'meal_name' => 'Meal 3: Wholesome Veg Thali', 'items_description' => '100g low fat paneer curry, 1 large bowl thick moong dal, 2 whole wheat rotis, cucumber carrot salad', 'calories' => 580, 'protein_grams' => 36, 'carbs_grams' => 72, 'fat_grams' => 16, 'sort_order' => 3],
                    ['meal_type' => 'evening_snack', 'recommended_time' => '17:30:00', 'meal_name' => 'Meal 4: Sprout Power Bowl', 'items_description' => '1 bowl boiled sprouts chaat with chopped onions, tomatoes, lemon and pomegranate', 'calories' => 220, 'protein_grams' => 14, 'carbs_grams' => 36, 'fat_grams' => 3, 'sort_order' => 4],
                    ['meal_type' => 'dinner', 'recommended_time' => '20:30:00', 'meal_name' => 'Meal 5: Soya & Quinoa Bowl', 'items_description' => 'Soya chunks gravy (50g dry soya cooked with tomatoes & onion), 1 small bowl quinoa or 2 rotis, 1 cup Greek curd', 'calories' => 510, 'protein_grams' => 30, 'carbs_grams' => 58, 'fat_grams' => 12, 'sort_order' => 5],
                ],
            ],
        ];
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->with('error', 'The current password provided is incorrect.');
        }

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Your password has been updated successfully!');
    }
}

