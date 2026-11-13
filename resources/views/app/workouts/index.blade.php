<x-app-layout header="">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        .workout-ui-scope {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            -webkit-font-smoothing: antialiased;
        }
        .workout-ui-scope .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>

    <div class="workout-ui-scope space-y-6">
        <!-- Success / Error Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">Close</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 shadow-sm">
                <div class="font-semibold text-sm mb-1">Please fix the following issues:</div>
                <ul class="list-disc pl-5 text-xs space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Top Header & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-semibold text-slate-900 dark:text-white tracking-tight">
                    Workout Routines
                </h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Create custom routines or pick exercises from the video library.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2.5">
                <button type="button" onclick="openExerciseLibraryModal()"
                        class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-800 flex items-center gap-2 transition-all cursor-pointer shadow-sm">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span>Exercise Library</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[11px] font-semibold tabular-nums">{{ count($exercises ?? []) }}</span>
                </button>
                <button type="button" onclick="openWorkoutModal()"
                        class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm shadow-indigo-600/20 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Workout Routine</span>
                </button>
            </div>
        </div>

        <!-- Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Total Routines</div>
                <div class="text-2xl font-semibold text-slate-900 dark:text-white mt-1.5 tabular-nums">{{ $plans->total() }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">All saved programs</div>
            </div>

            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Member Assigned</div>
                <div class="text-2xl font-semibold text-indigo-600 dark:text-indigo-400 mt-1.5 tabular-nums">{{ $plans->where('is_template', false)->count() }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">Assigned to specific members</div>
            </div>

            <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
                <div class="text-xs font-medium text-slate-500 dark:text-slate-400">Templates</div>
                <div class="text-2xl font-semibold text-amber-600 dark:text-amber-400 mt-1.5 tabular-nums">{{ $plans->where('is_template', true)->count() }}</div>
                <div class="text-xs text-slate-400 dark:text-slate-500 mt-1">Reusable splits &amp; master plans</div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <button type="button" onclick="filterWorkouts('all')" id="btnFilterAll"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all bg-indigo-600 text-white shadow-sm cursor-pointer shrink-0">
                    All Routines ({{ $plans->total() }})
                </button>
                <button type="button" onclick="filterWorkouts('member')" id="btnFilterMember"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0">
                    Member Assigned
                </button>
                <button type="button" onclick="filterWorkouts('template')" id="btnFilterTemplate"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0">
                    Templates Only
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <input type="text" id="workoutSearchInput" oninput="searchWorkouts()"
                       placeholder="Search routine title or member..."
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Workout Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5" id="workoutGrid">
            @forelse($plans as $plan)
                @php
                    $formattedGoal = ucwords(str_replace(['_', '-'], ' ', $plan->goal ?? 'General Fitness'));
                    $formattedLevel = ucfirst(str_replace(['_', '-'], ' ', $plan->level ?? 'All Levels'));
                    $isMemberPlan = !$plan->is_template && $plan->member;

                    $whatsappWorkoutText = "*Workout Routine - " . (auth()->user()->tenant->name ?? 'Gym') . "*\n";
                    $whatsappWorkoutText .= "*Routine:* " . $plan->title . "\n";
                    if ($plan->goal) {
                        $whatsappWorkoutText .= "*Goal:* " . $formattedGoal . " | *Level:* " . $formattedLevel . "\n";
                    }
                    $whatsappWorkoutText .= "---------------------------\n";
                    foreach ($plan->exercises as $exIdx => $ex) {
                        $dayLabel = $ex->day ? "[" . ucwords(str_replace('_', ' ', $ex->day)) . "] " : '';
                        $isTimed = ($ex->tracking_type ?? 'reps') === 'time';
                        $durSec = (int) ($ex->duration_seconds ?? 0);
                        $durLabel = $durSec > 0
                            ? (($durSec >= 60 && $durSec % 60 === 0) ? ($durSec / 60) . ' min' : $durSec . ' sec')
                            : ($ex->reps ?: '15 min');
                        $targetLabel = $isTimed
                            ? (($ex->sets ?? 1) > 1 ? ($ex->sets . ' rounds x ' . $durLabel) : $durLabel)
                            : ('Sets: ' . $ex->sets . ' x ' . $ex->reps);
                        $whatsappWorkoutText .= ($exIdx + 1) . ". *" . $dayLabel . $ex->exercise_name . "*\n";
                        $whatsappWorkoutText .= "   " . $targetLabel . ($ex->weight ? " (" . $ex->weight . ")" : "") . ($ex->rest_seconds ? " | Rest: " . $ex->rest_seconds . "s" : "") . "\n";
                        if (!empty($ex->video_url)) {
                            $whatsappWorkoutText .= "   Demo Video: " . $ex->video_url . "\n";
                        }
                    }
                    if ($plan->notes) {
                        $whatsappWorkoutText .= "---------------------------\n*Notes:* " . $plan->notes . "\n";
                    }
                @endphp

                <div class="workout-card p-5 sm:p-6 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($plan->member?->full_name ?? 'template') }}">
                    <div>
                        <!-- Card Header -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 pb-4 border-b border-slate-100 dark:border-slate-800/80">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                    @if($plan->is_template)
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Template
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                                            Member Plan
                                        </span>
                                    @endif

                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Level: <strong class="text-slate-800 dark:text-slate-200 font-semibold">{{ $formattedLevel }}</strong>
                                    </span>
                                </div>

                                <h4 class="font-semibold text-slate-900 dark:text-white text-base leading-snug">{{ $plan->title }}</h4>

                                @if($isMemberPlan)
                                    <div class="flex items-center gap-2 mt-2">
                                        <div class="w-6 h-6 rounded-lg bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ strtoupper(substr($plan->member->first_name, 0, 1)) }}
                                        </div>
                                        <a href="{{ route('app.members.show', $plan->member_id) }}" class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $plan->member->full_name }}
                                        </a>
                                        @if($plan->member->phone)
                                            <span class="text-xs text-slate-400 dark:text-slate-500 tabular-nums">· {{ $plan->member->phone }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($plan->goal)
                                <span class="self-start px-2.5 py-1 rounded-lg text-xs bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700 font-medium shrink-0">
                                    {{ $formattedGoal }}
                                </span>
                            @endif
                        </div>

                        @if($plan->trainer)
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-3">Coach: <strong class="text-slate-800 dark:text-slate-200 font-medium">{{ $plan->trainer->full_name }}</strong></p>
                        @endif

                        <!-- Exercises List -->
                        <div class="pt-4 mt-1">
                            <div class="flex items-center justify-between mb-2.5">
                                <span class="text-xs font-medium text-slate-500 dark:text-slate-400">Exercises ({{ $plan->exercises->count() }})</span>
                            </div>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @forelse($plan->exercises as $ex)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 p-3 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 text-xs">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-medium text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                                <span class="text-[11px] px-2 py-0.5 rounded-md bg-slate-200/80 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-semibold shrink-0">
                                                    {{ ucwords(str_replace('_', ' ', $ex->day ?? 'Day 1')) }}
                                                </span>
                                                <span class="font-semibold truncate">{{ $ex->exercise_name }}</span>
                                                @if(!empty($ex->video_url))
                                                    <button type="button"
                                                            data-video-title="{{ $ex->exercise_name }}"
                                                            data-video-url="{{ $ex->video_url }}"
                                                            data-video-category="{{ ucwords(str_replace('_', ' ', $ex->day ?? 'Exercise')) }}"
                                                            data-video-equipment="Demo"
                                                            data-video-notes="{{ $ex->notes ?? '' }}"
                                                            onclick="openExerciseVideoFromTrigger(this)"
                                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 border border-indigo-500/20 shrink-0 transition-colors cursor-pointer"
                                                            title="Watch form demonstration video">
                                                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                                        <span>Video</span>
                                                    </button>
                                                @endif
                                            </div>
                                            @if($ex->notes)
                                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1 line-clamp-1">{{ $ex->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center sm:flex-col sm:items-end justify-between sm:justify-center text-right shrink-0 pt-1.5 sm:pt-0 border-t sm:border-t-0 border-slate-200/60 dark:border-slate-800 tabular-nums">
                                            @php
                                                $isTimedEx = ($ex->tracking_type ?? 'reps') === 'time';
                                                $durSecEx = (int) ($ex->duration_seconds ?? 0);
                                                $durLabelEx = $durSecEx > 0
                                                    ? (($durSecEx >= 60 && $durSecEx % 60 === 0) ? ($durSecEx / 60) . ' min' : $durSecEx . ' sec')
                                                    : ($ex->reps ?: '15 min');
                                                $targetDisplayEx = $isTimedEx
                                                    ? (($ex->sets ?? 1) > 1 ? ($ex->sets . ' rounds × ' . $durLabelEx) : $durLabelEx)
                                                    : ($ex->sets . ' × ' . $ex->reps);
                                            @endphp
                                            <span class="text-amber-600 dark:text-amber-400 font-semibold text-xs">{{ $targetDisplayEx }}</span>
                                            <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                                @if($ex->weight)<span>{{ $ex->weight }}</span>@endif
                                                @if($ex->weight && $ex->rest_seconds)<span> · </span>@endif
                                                @if($ex->rest_seconds)<span>{{ $ex->rest_seconds }}s rest</span>@endif
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center rounded-xl bg-slate-50 dark:bg-slate-950/40 text-slate-400 text-xs">
                                        No exercises added to this routine yet.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        @if($plan->notes)
                            <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200/80 dark:border-slate-800 text-xs text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                                <span class="font-semibold text-slate-800 dark:text-slate-200 block mb-0.5">Program Notes</span>
                                {{ $plan->notes }}
                            </div>
                        @endif
                    </div>

                    <!-- Footer Actions -->
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-4 mt-4 flex flex-wrap items-center justify-between gap-2.5">
                        @if($isMemberPlan && $plan->member?->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $plan->member->phone) }}?text={{ urlencode($whatsappWorkoutText) }}"
                               target="_blank"
                               class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-colors shadow-sm">
                                <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Share on WhatsApp</span>
                            </a>
                        @else
                            <div></div>
                        @endif

                        <form action="{{ route('app.workouts.delete', $plan->id) }}" method="POST"
                              data-confirm="Are you sure you want to delete the workout routine '{{ addslashes($plan->title) }}'?"
                              data-confirm-title="Delete Workout Routine"
                              data-confirm-btn="Delete Routine"
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 dark:bg-slate-800 dark:hover:bg-rose-950/60 dark:text-slate-300 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 text-xs font-semibold transition-colors cursor-pointer flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Delete</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 sm:p-16 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-base font-semibold text-slate-900 dark:text-white mb-1">No Workout Routines Yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-5">Build a custom routine from scratch or add exercises directly from the video library.</p>
                    <button type="button" onclick="openWorkoutModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer">
                        Create Workout Routine
                    </button>
                </div>
            @endforelse
        </div>

        @if($plans->hasPages())
            <div class="mt-6">
                {{ $plans->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== CREATE WORKOUT ROUTINE MODAL ==================== -->
    <div id="workoutModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-6 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-5xl overflow-hidden shadow-2xl relative my-auto max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            <form id="workoutPlanForm" method="POST" action="{{ route('app.workouts.store') }}" class="flex flex-col max-h-[92vh]">
                @csrf
                <div class="px-6 py-4 bg-slate-50/90 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-slate-900 dark:text-white leading-tight">Create Workout Routine</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Build a completely custom workout or insert exercises from the video library.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeWorkoutModal()" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer" title="Close">✕</button>
                </div>

                <div class="p-5 sm:p-7 space-y-6 overflow-y-auto flex-1">
                    <!-- Section 1: Routine Overview -->
                    <div class="p-5 sm:p-6 rounded-2xl bg-slate-50/80 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Routine Details</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Set the routine name, goal, difficulty, and optional member assignment.</p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="startBlankWorkout()" class="px-3 py-1.5 rounded-lg bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-medium border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer">
                                    Start Blank
                                </button>
                                <button type="button" onclick="loadWorkoutPreset('push')" class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-500/20 transition-colors cursor-pointer">
                                    Sample: Push Day
                                </button>
                                <button type="button" onclick="loadWorkoutPreset('pull')" class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-500/20 transition-colors cursor-pointer">
                                    Sample: Pull Day
                                </button>
                                <button type="button" onclick="loadWorkoutPreset('legs')" class="px-3 py-1.5 rounded-lg bg-indigo-500/10 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-500/20 transition-colors cursor-pointer">
                                    Sample: Leg Day
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2 space-y-1.5">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Routine Title <span class="text-rose-500">*</span></label>
                                <input type="text" name="title" id="workoutTitleInput" required placeholder="e.g. Custom 4-Day Upper / Lower Split"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm font-medium placeholder-slate-400 focus:outline-none focus:border-indigo-500">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Target Goal</label>
                                <select name="goal" id="workoutGoalInput" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-indigo-500">
                                    <option value="general_fitness">General Fitness</option>
                                    <option value="muscle_gain">Muscle Gain (Hypertrophy)</option>
                                    <option value="weight_loss">Fat Loss &amp; Conditioning</option>
                                    <option value="endurance">Cardio &amp; Endurance</option>
                                    <option value="flexibility">Mobility &amp; Flexibility</option>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Experience Level</label>
                                <select name="level" id="workoutLevelInput" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-indigo-500">
                                    <option value="beginner">Beginner</option>
                                    <option value="intermediate">Intermediate</option>
                                    <option value="advanced">Advanced</option>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Assign to Member</label>
                                <select name="member_id" id="workoutMemberInput" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-indigo-500">
                                    <option value="">Reusable Template (No Member)</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Assigned Coach</label>
                                <select name="trainer_id" id="workoutTrainerInput" class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-sm focus:outline-none focus:border-indigo-500">
                                    <option value="">Optional coach</option>
                                    @foreach($trainers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Exercises Builder -->
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2.5">
                                    <h4 class="text-sm font-semibold text-slate-900 dark:text-white">Exercises Schedule</h4>
                                    <span id="workoutExercisesSummary" class="px-2 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-medium tabular-nums">0 exercises</span>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Add your own custom movements or pick exercises with demo videos from the library.</p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="addExerciseRow()"
                                        class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                                    + Custom Exercise
                                </button>
                                <button type="button" onclick="addExerciseRow('day_1', '', 1, '', '', 0, '', '', '', '', '', 'time', 15, 'minutes')"
                                        class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                                    + Cardio / Timed
                                </button>
                                <button type="button" onclick="openWorkoutExercisePicker()"
                                        class="px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-semibold shadow-sm transition-colors cursor-pointer">
                                    Pick from Exercise Library
                                </button>
                            </div>
                        </div>

                        <div id="exerciseContainer" class="space-y-3">
                            <!-- Dynamic Exercise Rows -->
                        </div>
                    </div>

                    <!-- Section 3: Program Notes -->
                    <div class="space-y-1.5">
                        <label class="block text-xs font-medium text-slate-700 dark:text-slate-300">Program Notes &amp; Progression Rules</label>
                        <textarea name="notes" id="workoutNotesInput" rows="2" placeholder="Optional warm-up guidance, progressive overload notes, or weekly progression reminders..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:border-indigo-500 leading-relaxed"></textarea>
                    </div>
                </div>

                <div class="px-6 py-4 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" onclick="closeWorkoutModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-300 text-xs font-semibold border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm shadow-indigo-600/20 transition-all cursor-pointer">
                        Save Workout Routine
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== 1. DEDICATED EXERCISE VIDEO PLAYER MODAL ==================== -->
    <div id="exerciseVideoModal" class="fixed inset-0 z-[70] flex items-center justify-center hidden bg-black/85 backdrop-blur-md p-2 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl relative my-auto flex flex-col text-white">
            <div class="p-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <h3 id="videoModalTitle" class="text-sm font-semibold text-white leading-tight truncate">Exercise Video</h3>
                        <div class="flex items-center gap-2 mt-1 text-xs text-slate-400 flex-wrap">
                            <span id="videoModalCategory" class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-medium text-[11px]">Category</span>
                            <span id="videoModalEquipment" class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 text-[11px]">Equipment</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeExerciseVideoModal()" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors text-xs font-semibold cursor-pointer shrink-0">✕</button>
            </div>

            <div class="relative bg-black flex items-center justify-center aspect-video w-full max-h-[55vh] sm:max-h-[60vh]">
                <video id="modalVideoPlayer" controls autoplay loop playsinline class="w-full h-full object-contain"></video>
            </div>

            <div class="p-4 bg-slate-950/90 border-t border-slate-800/80 space-y-3">
                <p id="videoModalNotes" class="text-xs text-slate-300 bg-slate-900 p-3 rounded-xl border border-slate-800 leading-relaxed">
                    Form cue: Keep core engaged, control the tempo, and use a full range of motion.
                </p>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-1">
                    <button type="button" id="videoModalCopyBtn" onclick="copyCurrentModalVideoLink()"
                            class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-semibold border border-slate-700 flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span id="videoModalCopyText">Copy Video Link</span>
                    </button>

                    <button type="button" id="videoModalAddBtn" onclick="addCurrentModalVideoToRoutine()"
                            class="w-full sm:w-auto px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>Add to Routine</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 2. EXERCISE VIDEO LIBRARY MODAL ==================== -->
    <div id="exerciseLibraryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/80 backdrop-blur-md p-2 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-6xl overflow-hidden shadow-2xl relative my-auto max-h-[94vh] flex flex-col text-slate-900 dark:text-slate-200">
            <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-semibold text-slate-900 dark:text-white truncate">Exercise Video Library</h3>
                            <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-xs font-semibold tabular-nums shrink-0">
                                {{ count($exercises ?? []) }} Videos
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate">Form demonstration videos grouped by muscle group and equipment.</p>
                    </div>
                </div>
                <button type="button" onclick="closeExerciseLibraryModal()" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer text-xs font-semibold shrink-0">✕</button>
            </div>

            <div class="p-3.5 sm:p-4 bg-slate-50/70 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 shrink-0 space-y-3">
                <div class="flex flex-col md:flex-row gap-3 items-stretch md:items-center justify-between">
                    <div class="relative w-full md:w-80 shrink-0">
                        <input type="text" id="libSearchInput" oninput="debounceLibFilter()"
                               placeholder="Search exercises by title, muscle, or equipment..."
                               class="w-full pl-9 pr-8 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="button" onclick="clearLibSearch()" id="libClearSearchBtn" class="hidden absolute right-2.5 top-2 text-xs text-slate-400 hover:text-slate-600 font-semibold cursor-pointer">✕</button>
                    </div>

                    <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto text-xs pb-1 md:pb-0">
                        <span class="text-xs text-slate-400 font-medium shrink-0">Equipment:</span>
                        <button type="button" onclick="setLibEquipment('all')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-semibold bg-amber-500 text-slate-950 shrink-0 cursor-pointer" data-equip="all">All</button>
                        <button type="button" onclick="setLibEquipment('Dumbbell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Dumbbell">Dumbbell</button>
                        <button type="button" onclick="setLibEquipment('Barbell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Barbell">Barbell</button>
                        <button type="button" onclick="setLibEquipment('Cable')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Cable">Cable</button>
                        <button type="button" onclick="setLibEquipment('Machine')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Machine">Machine</button>
                        <button type="button" onclick="setLibEquipment('Bodyweight')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Bodyweight">Bodyweight</button>
                        <button type="button" onclick="setLibEquipment('Kettlebell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-equip="Kettlebell">Kettlebell</button>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs">
                    <button type="button" onclick="setLibCategory('all')" class="lib-cat-btn px-3 py-1.5 rounded-xl font-semibold bg-indigo-600 text-white shrink-0 shadow-xs transition-all cursor-pointer" data-cat="all">
                        All ({{ count($exercises ?? []) }})
                    </button>
                    @foreach($exerciseCategories ?? [] as $cName => $cMeta)
                        @if($cName !== 'All')
                            <button type="button" onclick="setLibCategory('{{ $cName }}')" class="lib-cat-btn px-3 py-1.5 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 transition-colors cursor-pointer" data-cat="{{ $cName }}">
                                {{ $cName }} ({{ $cMeta['count'] ?? 0 }})
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="px-4 py-2 bg-slate-100/80 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 shrink-0 flex-wrap gap-1">
                <span id="libCounterText" class="font-medium tabular-nums">Showing {{ count($exercises ?? []) }} videos</span>
                <span class="text-xs text-slate-400 hidden sm:inline">Hover a card to preview · Click to watch full video</span>
            </div>

            <div class="p-4 sm:p-5 overflow-y-auto flex-1" id="libGridScrollContainer">
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4" id="libExercisesGrid"></div>

                <div id="libLoadMoreContainer" class="mt-6 text-center pb-2 hidden">
                    <button type="button" onclick="loadMoreLibExercises()" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                        Load More (<span id="libLoadMoreRemaining">0</span> remaining)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 3. WORKOUT EXERCISE PICKER MODAL (LIBRARY + CUSTOM) ==================== -->
    <div id="workoutExercisePickerModal" class="fixed inset-0 z-[60] flex items-center justify-center hidden bg-black/75 backdrop-blur-sm p-2 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-4xl overflow-hidden shadow-2xl relative my-auto max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0 gap-3">
                <div class="min-w-0">
                    <h4 class="text-sm sm:text-base font-semibold text-slate-900 dark:text-white truncate">Add Exercises to Routine</h4>
                    <p class="text-xs text-slate-500 dark:text-slate-400 truncate mt-0.5">Pick from the exercise library or add your own custom movement.</p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <button type="button" onclick="togglePickerCustomExerciseForm()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                        + Custom Exercise
                    </button>
                    <button type="button" onclick="closeWorkoutExercisePicker()" class="w-8 h-8 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-xs font-semibold cursor-pointer">✕</button>
                </div>
            </div>

            <!-- Inline Custom Exercise Form inside Picker -->
            <div id="pickerCustomExerciseBox" class="hidden p-4 sm:p-5 bg-indigo-50/40 dark:bg-slate-950/90 border-b border-slate-200 dark:border-slate-800 space-y-3 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Add Custom Exercise to Selected Day</span>
                    <button type="button" onclick="togglePickerCustomExerciseForm(false)" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer">Close</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                    <input type="text" id="pickerCustomExName" placeholder="e.g. Treadmill Walk / Heavy Sled Push" class="sm:col-span-2 w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    <select id="pickerCustomExType" onchange="togglePickerCustomTrackingMode()" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        <option value="reps">Strength (Sets × Reps)</option>
                        <option value="time">Cardio / Timed (min / sec)</option>
                    </select>
                    <input type="text" id="pickerCustomExWeight" placeholder="Load / Speed (e.g. 40 kg / 6.5 km/h)" class="w-full px-3 py-2 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-6 gap-2.5 items-end">
                    <div>
                        <label id="pickerCustomExSetsLabel" class="block text-[11px] font-medium text-slate-500 mb-1">Sets</label>
                        <input type="number" id="pickerCustomExSets" min="1" value="3" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div id="pickerCustomExRepsWrap">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Reps</label>
                        <input type="text" id="pickerCustomExReps" value="10-12" placeholder="10-12" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div id="pickerCustomExDurationWrap" class="hidden">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Duration</label>
                        <div class="flex items-center gap-1">
                            <input type="number" id="pickerCustomExDuration" min="1" value="15" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            <select id="pickerCustomExUnit" class="px-1.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="minutes">min</option>
                                <option value="seconds">sec</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Rest (sec)</label>
                        <input type="number" id="pickerCustomExRest" min="0" value="60" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Form Cues / Notes</label>
                        <input type="text" id="pickerCustomExNotes" placeholder="Optional incline, pace, or form cues" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <button type="button" onclick="submitPickerCustomExercise()" class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                        Add to Day
                    </button>
                </div>
            </div>

            <div class="p-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 shrink-0 space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5">
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-medium text-slate-700 dark:text-slate-300 shrink-0">Target Day:</label>
                        <select id="pickerTargetDay" class="px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
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

                    <div class="relative w-full sm:w-72">
                        <input type="text" id="pickerSearchInput" oninput="debouncePickerFilter()"
                               placeholder="Search exercise library..."
                               class="w-full px-3.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500">
                    </div>
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto text-xs pb-1">
                    <button type="button" onclick="setPickerCategory('all')" class="picker-cat-btn px-2.5 py-1 rounded-md font-semibold bg-amber-500 text-slate-950 shrink-0 cursor-pointer" data-cat="all">All ({{ count($exercises ?? []) }})</button>
                    @foreach($exerciseCategories ?? [] as $cName => $cMeta)
                        @if($cName !== 'All')
                            <button type="button" onclick="setPickerCategory('{{ $cName }}')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer" data-cat="{{ $cName }}">
                                {{ $cName }}
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="p-4 overflow-y-auto flex-1 space-y-2.5" id="pickerExercisesList"></div>

            <div class="p-3.5 bg-slate-100 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0 gap-2">
                <div class="text-xs font-medium text-slate-700 dark:text-slate-300 truncate tabular-nums" id="pickerAddedSummary">
                    Click <strong>+ Add</strong> or use <strong>+ Custom Exercise</strong> above
                </div>
                <button type="button" onclick="closeWorkoutExercisePicker()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold shadow-sm transition-all cursor-pointer shrink-0">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT LOGIC ==================== -->
    <script>
        const exerciseLibraryData = {!! json_encode($exercises ?? []) !!};
        const exerciseCategoriesData = {!! json_encode($exerciseCategories ?? []) !!};

        const workoutDayOptions = [
            { value: 'day_1', label: 'Day 1' },
            { value: 'day_2', label: 'Day 2' },
            { value: 'day_3', label: 'Day 3' },
            { value: 'day_4', label: 'Day 4' },
            { value: 'day_5', label: 'Day 5' },
            { value: 'day_6', label: 'Day 6' },
            { value: 'day_7', label: 'Day 7' },
            { value: 'monday', label: 'Monday' },
            { value: 'tuesday', label: 'Tuesday' },
            { value: 'wednesday', label: 'Wednesday' },
            { value: 'thursday', label: 'Thursday' },
            { value: 'friday', label: 'Friday' },
            { value: 'saturday', label: 'Saturday' },
            { value: 'sunday', label: 'Sunday' },
        ];

        let exCounter = 0;
        let pickerCurrentCategory = 'all';
        let pickerAddedCountThisSession = 0;

        let libCurrentCategory = 'all';
        let libCurrentEquipment = 'all';
        let libDisplayLimit = 32;
        let currentModalExercise = null;

        let libFilterTimeout = null;
        let pickerFilterTimeout = null;

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function normalizeDayValue(day) {
            const raw = String(day || 'day_1').toLowerCase().replace(/\s+/g, '_');
            return workoutDayOptions.some(d => d.value === raw) ? raw : 'day_1';
        }

        function formatDayLabel(day) {
            const norm = normalizeDayValue(day);
            const found = workoutDayOptions.find(d => d.value === norm);
            return found ? found.label : 'Day 1';
        }

        function debounceLibFilter() {
            clearTimeout(libFilterTimeout);
            libFilterTimeout = setTimeout(() => {
                libDisplayLimit = 32;
                renderLibExercises();
            }, 180);
        }

        function debouncePickerFilter() {
            clearTimeout(pickerFilterTimeout);
            pickerFilterTimeout = setTimeout(() => {
                renderPickerExercises();
            }, 180);
        }

        function clearLibSearch() {
            document.getElementById('libSearchInput').value = '';
            document.getElementById('libClearSearchBtn').classList.add('hidden');
            libDisplayLimit = 32;
            renderLibExercises();
        }

        function filterWorkouts(type) {
            const activeClass = 'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all bg-indigo-600 text-white shadow-sm cursor-pointer shrink-0';
            const inactiveClass = 'px-3.5 py-1.5 rounded-xl text-xs font-semibold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0';

            document.getElementById('btnFilterAll').className = type === 'all' ? activeClass : inactiveClass;
            document.getElementById('btnFilterMember').className = type === 'member' ? activeClass : inactiveClass;
            document.getElementById('btnFilterTemplate').className = type === 'template' ? activeClass : inactiveClass;

            const cards = document.querySelectorAll('.workout-card');
            cards.forEach(card => {
                const planType = card.getAttribute('data-plan-type');
                if (type === 'all' || planType === type) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function searchWorkouts() {
            const query = document.getElementById('workoutSearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.workout-card');
            cards.forEach(card => {
                const title = card.getAttribute('data-title') || '';
                const member = card.getAttribute('data-member') || '';
                if (!query || title.includes(query) || member.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function openWorkoutModal() {
            document.getElementById('workoutPlanForm')?.reset();
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            addExerciseRow();
            updateWorkoutExercisesSummary();
            document.getElementById('workoutModal').classList.remove('hidden');
        }

        function closeWorkoutModal() {
            document.getElementById('workoutModal').classList.add('hidden');
        }

        function startBlankWorkout() {
            document.getElementById('workoutTitleInput').value = '';
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            addExerciseRow();
            updateWorkoutExercisesSummary();
            document.getElementById('workoutTitleInput')?.focus();
        }

        function updateWorkoutExercisesSummary() {
            const container = document.getElementById('exerciseContainer');
            const summaryEl = document.getElementById('workoutExercisesSummary');
            if (!container || !summaryEl) return;

            const count = container.children.length;
            const days = new Set();
            container.querySelectorAll('select[name$="[day]"]').forEach(sel => {
                if (sel.value) days.add(sel.value);
            });
            const dayCount = Math.max(days.size, count > 0 ? 1 : 0);
            summaryEl.textContent = `${count} ${count === 1 ? 'exercise' : 'exercises'}${count > 0 ? ` · ${dayCount} ${dayCount === 1 ? 'day' : 'days'}` : ''}`;
        }

        function loadWorkoutPreset(preset) {
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            const titleInput = document.getElementById('workoutTitleInput');
            const goalInput = document.getElementById('workoutGoalInput');

            if (preset === 'push') {
                if (titleInput) titleInput.value = 'Push Day (Chest, Shoulders, Triceps)';
                if (goalInput) goalInput.value = 'muscle_gain';
                const bench = exerciseLibraryData.find(e => e.title.includes('Bench Press') && e.category === 'Chest');
                const inc = exerciseLibraryData.find(e => e.title.includes('Incline') && e.category === 'Chest');
                const lat = exerciseLibraryData.find(e => e.title.includes('Lateral Raise') && e.category === 'Shoulders');
                const tri = exerciseLibraryData.find(e => e.category === 'Triceps');

                if (bench) addExerciseRow('day_1', bench.title, 4, '8-10', '60 kg', 90, bench.notes, bench.category, bench.sub_category, bench.equipment, bench.video_url);
                if (inc) addExerciseRow('day_1', inc.title, 3, '10-12', '22 kg', 75, inc.notes, inc.category, inc.sub_category, inc.equipment, inc.video_url);
                if (lat) addExerciseRow('day_1', lat.title, 4, '12-15', '10 kg', 60, lat.notes, lat.category, lat.sub_category, lat.equipment, lat.video_url);
                if (tri) addExerciseRow('day_1', tri.title, 3, '12-15', '25 kg', 60, tri.notes, tri.category, tri.sub_category, tri.equipment, tri.video_url);
            } else if (preset === 'pull') {
                if (titleInput) titleInput.value = 'Pull Day (Back & Biceps)';
                if (goalInput) goalInput.value = 'muscle_gain';
                const row = exerciseLibraryData.find(e => e.title.includes('Barbell Row') && e.category === 'Back');
                const dead = exerciseLibraryData.find(e => e.title.includes('Deadlift') && e.category === 'Back');
                const bic = exerciseLibraryData.find(e => e.category === 'Biceps');

                if (row) addExerciseRow('day_2', row.title, 4, '8-10', '50 kg', 90, row.notes, row.category, row.sub_category, row.equipment, row.video_url);
                if (dead) addExerciseRow('day_2', dead.title, 4, '6-8', '80 kg', 120, dead.notes, dead.category, dead.sub_category, dead.equipment, dead.video_url);
                if (bic) addExerciseRow('day_2', bic.title, 3, '10-12', '20 kg', 60, bic.notes, bic.category, bic.sub_category, bic.equipment, bic.video_url);
            } else if (preset === 'legs') {
                if (titleInput) titleInput.value = 'Leg Day (Quads, Glutes & Calves)';
                if (goalInput) goalInput.value = 'muscle_gain';
                const sq = exerciseLibraryData.find(e => e.title.includes('Squat') && e.category === 'Quads');
                const glu = exerciseLibraryData.find(e => e.category === 'Glutes');
                const calf = exerciseLibraryData.find(e => e.category === 'Calves');

                if (sq) addExerciseRow('day_3', sq.title, 4, '6-8', '70 kg', 120, sq.notes, sq.category, sq.sub_category, sq.equipment, sq.video_url);
                if (glu) addExerciseRow('day_3', glu.title, 4, '8-10', '60 kg', 90, glu.notes, glu.category, glu.sub_category, glu.equipment, glu.video_url);
                if (calf) addExerciseRow('day_3', calf.title, 4, '15-20', 'Bodyweight', 45, calf.notes, calf.category, calf.sub_category, calf.equipment, calf.video_url);
            }

            if (document.getElementById('exerciseContainer').children.length === 0) {
                addExerciseRow();
            }
            updateWorkoutExercisesSummary();
        }

        function inferTrackingFromReps(defaultReps, category = '') {
            const raw = String(defaultReps || '').trim();
            const match = raw.match(/^(\d+(?:\.\d+)?)\s*(min|mins|minute|minutes|sec|secs|second|seconds)$/i);
            if (match) {
                const isSec = /^sec/i.test(match[2]);
                return {
                    type: 'time',
                    durationValue: Number(match[1]),
                    durationUnit: isSec ? 'seconds' : 'minutes',
                    sets: 1,
                    restSeconds: 0,
                };
            }
            if (String(category || '').toLowerCase() === 'cardio' && !raw) {
                return { type: 'time', durationValue: 15, durationUnit: 'minutes', sets: 1, restSeconds: 0 };
            }
            return { type: 'reps', durationValue: 15, durationUnit: 'minutes', sets: null, restSeconds: 60 };
        }

        function setExerciseTrackingMode(rowId, mode) {
            const normalized = mode === 'time' ? 'time' : 'reps';
            const typeInput = document.getElementById(`${rowId}_tracking_type`);
            const repsBtn = document.getElementById(`${rowId}_mode_reps`);
            const timeBtn = document.getElementById(`${rowId}_mode_time`);
            const repsWrap = document.getElementById(`${rowId}_reps_wrap`);
            const durationWrap = document.getElementById(`${rowId}_duration_wrap`);
            const setsLabel = document.getElementById(`${rowId}_sets_label`);
            const setsInput = document.getElementById(`${rowId}_sets_input`);
            const restInput = document.getElementById(`${rowId}_rest_input`);
            const nameInput = document.getElementById(`${rowId}_name_input`);
            const weightLabel = document.getElementById(`${rowId}_weight_label`);
            const weightInput = document.getElementById(`${rowId}_weight_input`);
            const badgeEl = document.getElementById(`${rowId}_custom_badge`);

            if (typeInput) typeInput.value = normalized;

            const activeBtnClass = 'px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-600 text-white shadow-sm transition-colors cursor-pointer';
            const inactiveBtnClass = 'px-2.5 py-1 rounded-md text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer';
            if (repsBtn) repsBtn.className = normalized === 'reps' ? activeBtnClass : inactiveBtnClass;
            if (timeBtn) timeBtn.className = normalized === 'time' ? activeBtnClass : inactiveBtnClass;

            if (repsWrap) repsWrap.classList.toggle('hidden', normalized === 'time');
            if (durationWrap) durationWrap.classList.toggle('hidden', normalized !== 'time');

            if (setsLabel) setsLabel.textContent = normalized === 'time' ? 'Rounds' : 'Sets';
            if (weightLabel) weightLabel.textContent = normalized === 'time' ? 'Pace / Level' : 'Load / Weight';
            if (weightInput) {
                weightInput.placeholder = normalized === 'time' ? 'e.g. Speed 6.5 / Incline 2%' : 'e.g. 50 kg';
            }
            if (nameInput) {
                nameInput.placeholder = normalized === 'time' ? 'e.g. Treadmill Walk / Run, Cycling, Plank' : 'e.g. Flat Barbell Bench Press';
            }
            if (badgeEl) {
                badgeEl.textContent = normalized === 'time' ? 'Cardio / Timed' : 'Custom';
            }

            if (setsInput) {
                if (normalized === 'time' && setsInput.value === '3') setsInput.value = '1';
                if (normalized === 'reps' && setsInput.value === '1') setsInput.value = '3';
            }
            if (restInput) {
                if (normalized === 'time' && restInput.value === '60') restInput.value = '0';
                if (normalized === 'reps' && restInput.value === '0') restInput.value = '60';
            }
        }

        function addExerciseRow(day = 'day_1', name = '', sets = 3, reps = '10-12', weight = '', restSeconds = 60, notes = '', category = '', subCategory = '', equipment = '', videoUrl = '', trackingType = 'reps', durationValue = 15, durationUnit = 'minutes') {
            exCounter++;
            const container = document.getElementById('exerciseContainer');
            const rowId = 'ex_row_' + exCounter;
            const normDay = normalizeDayValue(day);
            const isTime = trackingType === 'time';
            const resolvedSets = sets != null ? sets : (isTime ? 1 : 3);
            const resolvedRest = restSeconds !== '' && restSeconds != null ? restSeconds : (isTime ? 0 : 60);
            const resolvedDurationVal = durationValue && Number(durationValue) > 0 ? Number(durationValue) : 15;
            const resolvedDurationUnit = durationUnit === 'seconds' ? 'seconds' : 'minutes';
            const safeName = escapeHtml(name);
            const safeWeight = escapeHtml(weight);
            const safeReps = escapeHtml(reps || '10-12');
            const safeNotes = escapeHtml(notes);
            const safeUrl = escapeHtml(videoUrl);

            const dayOptionsHtml = workoutDayOptions
                .map(d => `<option value="${d.value}" ${d.value === normDay ? 'selected' : ''}>${d.label}</option>`)
                .join('');

            const categoryBadge = (category || equipment) ? `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                    <span>${escapeHtml(category || 'Library')}</span>
                    ${equipment ? `<span class="opacity-50">·</span><span>${escapeHtml(equipment)}</span>` : ''}
                </span>
            ` : `
                <span id="${rowId}_custom_badge" class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-200/70 dark:bg-slate-800 text-slate-600 dark:text-slate-300">
                    ${isTime ? 'Cardio / Timed' : 'Custom'}
                </span>
            `;

            const videoBadge = videoUrl ? `
                <button type="button"
                        data-video-title="${safeName}"
                        data-video-url="${safeUrl}"
                        data-video-category="${escapeHtml(category || formatDayLabel(normDay))}"
                        data-video-equipment="${escapeHtml(equipment || 'Exercise')}"
                        data-video-notes="${safeNotes}"
                        onclick="openExerciseVideoFromTrigger(this)"
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[11px] font-medium bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 transition-colors cursor-pointer" title="Watch exercise demo">
                    <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    <span>Video</span>
                </button>
            ` : '';

            const activeModeClass = 'px-2.5 py-1 rounded-md text-[11px] font-semibold bg-indigo-600 text-white shadow-sm transition-colors cursor-pointer';
            const inactiveModeClass = 'px-2.5 py-1 rounded-md text-[11px] font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white transition-colors cursor-pointer';

            const html = `
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 space-y-3.5 transition-colors" id="${rowId}">
                    <input type="hidden" name="exercises[${exCounter}][tracking_type]" id="${rowId}_tracking_type" value="${isTime ? 'time' : 'reps'}">
                    <div class="flex flex-wrap items-center justify-between gap-2 pb-3 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2 flex-wrap min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs flex items-center justify-center font-semibold tabular-nums shrink-0">${container.children.length + 1}</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white truncate" id="${rowId}_header_title">${safeName || (isTime ? 'Cardio / Timed Exercise' : 'Custom Exercise')}</span>
                            ${categoryBadge}
                            ${videoBadge}
                        </div>

                        <div class="flex items-center gap-2 flex-wrap shrink-0">
                            <div class="inline-flex items-center p-0.5 rounded-lg bg-slate-200/70 dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                                <button type="button" id="${rowId}_mode_reps" onclick="setExerciseTrackingMode('${rowId}', 'reps')" class="${!isTime ? activeModeClass : inactiveModeClass}">Reps</button>
                                <button type="button" id="${rowId}_mode_time" onclick="setExerciseTrackingMode('${rowId}', 'time')" class="${isTime ? activeModeClass : inactiveModeClass}">Cardio / Timed</button>
                            </div>
                            <button type="button" onclick="openWorkoutExercisePicker(document.getElementById('${rowId}_day').value)" class="px-2.5 py-1 rounded-lg bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-medium border border-amber-500/25 transition-colors cursor-pointer">
                                Pick from Library
                            </button>
                            <button type="button" onclick="removeExerciseRow('${rowId}')" class="px-2.5 py-1 rounded-lg text-xs text-rose-500 hover:bg-rose-500/10 font-medium transition-colors cursor-pointer">Remove</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Day / Split</label>
                            <select name="exercises[${exCounter}][day]" id="${rowId}_day" onchange="updateWorkoutExercisesSummary()" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                ${dayOptionsHtml}
                            </select>
                        </div>
                        <div class="sm:col-span-5">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Exercise Name <span class="text-rose-500">*</span></label>
                            <input type="text" id="${rowId}_name_input" name="exercises[${exCounter}][exercise_name]" value="${safeName}" required oninput="document.getElementById('${rowId}_header_title').innerText = this.value || 'Custom Exercise'" placeholder="${isTime ? 'e.g. Treadmill Walk / Run, Cycling, Plank' : 'e.g. Flat Barbell Bench Press'}" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-4">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Video URL (Optional)</label>
                            <input type="text" name="exercises[${exCounter}][video_url]" value="${safeUrl}" placeholder="Auto-filled from library or paste URL" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-700 dark:text-slate-300 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-12 gap-3">
                        <div class="sm:col-span-2">
                            <label id="${rowId}_sets_label" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">${isTime ? 'Rounds' : 'Sets'}</label>
                            <input type="number" id="${rowId}_sets_input" name="exercises[${exCounter}][sets]" value="${resolvedSets}" min="1" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2 ${isTime ? 'hidden' : ''}" id="${rowId}_reps_wrap">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Reps</label>
                            <input type="text" name="exercises[${exCounter}][reps]" value="${safeReps}" placeholder="8-10" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2 ${isTime ? '' : 'hidden'}" id="${rowId}_duration_wrap">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Duration</label>
                            <div class="flex items-center gap-1.5">
                                <input type="number" name="exercises[${exCounter}][duration_value]" value="${resolvedDurationVal}" min="1" placeholder="15" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                <select name="exercises[${exCounter}][duration_unit]" class="px-2 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-medium text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                    <option value="minutes" ${resolvedDurationUnit === 'minutes' ? 'selected' : ''}>min</option>
                                    <option value="seconds" ${resolvedDurationUnit === 'seconds' ? 'selected' : ''}>sec</option>
                                </select>
                            </div>
                        </div>
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Rest (sec)</label>
                            <input type="number" id="${rowId}_rest_input" name="exercises[${exCounter}][rest_seconds]" value="${resolvedRest}" min="0" placeholder="60" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label id="${rowId}_weight_label" class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">${isTime ? 'Pace / Level' : 'Load / Weight'}</label>
                            <input type="text" id="${rowId}_weight_input" name="exercises[${exCounter}][weight]" value="${safeWeight}" placeholder="${isTime ? 'e.g. Speed 6.5 / Incline 2%' : 'e.g. 50 kg'}" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="col-span-2 sm:col-span-4">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Form Cues / Notes</label>
                            <input type="text" name="exercises[${exCounter}][notes]" value="${safeNotes}" placeholder="Tempo, incline, pace, or form cues..." class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            updateWorkoutExercisesSummary();
            return rowId;
        }

        function removeExerciseRow(rowId) {
            const el = document.getElementById(rowId);
            if (el) el.remove();
            updateWorkoutExercisesSummary();
        }

        // ==================== VIDEO MODAL LOGIC ====================
        function openExerciseVideoFromTrigger(el) {
            if (!el) return;
            openExerciseVideoModal(
                el.dataset.videoTitle || '',
                el.dataset.videoUrl || '',
                el.dataset.videoCategory || '',
                el.dataset.videoEquipment || '',
                el.dataset.videoNotes || ''
            );
        }

        function openExerciseVideoModal(title, videoUrl, category = '', equipment = '', notes = '') {
            currentModalExercise = { title, videoUrl, category, equipment, notes };

            document.getElementById('videoModalTitle').innerText = title;
            document.getElementById('videoModalCategory').innerText = category || 'General';
            document.getElementById('videoModalEquipment').innerText = equipment || 'Exercise';
            document.getElementById('videoModalNotes').innerText = notes || 'Form cue: Maintain steady tempo, brace your core, and work through a full range of motion.';

            const player = document.getElementById('modalVideoPlayer');
            player.src = videoUrl;
            player.load();
            player.play().catch(() => {});

            document.getElementById('videoModalCopyText').innerText = 'Copy Video Link';
            document.getElementById('exerciseVideoModal').classList.remove('hidden');
        }

        function closeExerciseVideoModal() {
            const player = document.getElementById('modalVideoPlayer');
            player.pause();
            player.removeAttribute('src');
            player.load();
            document.getElementById('exerciseVideoModal').classList.add('hidden');
        }

        function copyCurrentModalVideoLink() {
            if (!currentModalExercise || !currentModalExercise.videoUrl) return;
            copyToClipboard(currentModalExercise.videoUrl, document.getElementById('videoModalCopyBtn'), 'videoModalCopyText');
        }

        function copyExerciseVideoLink(url, btnElement) {
            copyToClipboard(url, btnElement);
        }

        function copyToClipboard(text, btnElement, textSpanId = null) {
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    if (textSpanId) {
                        const span = document.getElementById(textSpanId);
                        if (span) {
                            const orig = span.innerText;
                            span.innerText = 'Copied';
                            setTimeout(() => { span.innerText = orig; }, 2000);
                        }
                    } else if (btnElement) {
                        const origHtml = btnElement.innerHTML;
                        btnElement.innerHTML = 'Copied';
                        btnElement.classList.add('text-emerald-500');
                        setTimeout(() => {
                            btnElement.innerHTML = origHtml;
                            btnElement.classList.remove('text-emerald-500');
                        }, 2000);
                    }
                }).catch(() => fallbackCopy(text));
            } else {
                fallbackCopy(text);
            }
        }

        function fallbackCopy(text) {
            const textarea = document.createElement('textarea');
            textarea.value = text;
            document.body.appendChild(textarea);
            textarea.select();
            document.execCommand('copy');
            document.body.removeChild(textarea);
            alert('Video link copied to clipboard.');
        }

        function addCurrentModalVideoToRoutine() {
            if (!currentModalExercise) return;
            const ex = currentModalExercise;
            closeExerciseVideoModal();

            const modal = document.getElementById('workoutModal');
            if (modal.classList.contains('hidden')) {
                document.getElementById('workoutPlanForm')?.reset();
                document.getElementById('exerciseContainer').innerHTML = '';
                exCounter = 0;
                modal.classList.remove('hidden');
            }

            addExerciseRow('day_1', ex.title, 3, '10-12', '', 60, ex.notes, ex.category, ex.equipment, ex.equipment, ex.videoUrl);
        }

        // ==================== EXERCISE VIDEO LIBRARY MODAL ====================
        function openExerciseLibraryModal() {
            libCurrentCategory = 'all';
            libCurrentEquipment = 'all';
            libDisplayLimit = 32;
            document.getElementById('libSearchInput').value = '';
            document.getElementById('libClearSearchBtn').classList.add('hidden');
            setLibCategory('all');
            setLibEquipment('all');
            document.getElementById('exerciseLibraryModal').classList.remove('hidden');
        }

        function closeExerciseLibraryModal() {
            document.getElementById('exerciseLibraryModal').classList.add('hidden');
            document.querySelectorAll('#libExercisesGrid video').forEach(v => {
                v.pause();
            });
        }

        function setLibCategory(cat) {
            libCurrentCategory = cat;
            libDisplayLimit = 32;

            document.querySelectorAll('.lib-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'lib-cat-btn px-3 py-1.5 rounded-xl font-semibold bg-indigo-600 text-white shrink-0 shadow-xs transition-all cursor-pointer';
                } else {
                    btn.className = 'lib-cat-btn px-3 py-1.5 rounded-xl font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 transition-colors cursor-pointer';
                }
            });

            renderLibExercises();
        }

        function setLibEquipment(equip) {
            libCurrentEquipment = equip;
            libDisplayLimit = 32;

            document.querySelectorAll('.lib-equip-btn').forEach(btn => {
                if (btn.getAttribute('data-equip') === equip) {
                    btn.className = 'lib-equip-btn px-2.5 py-1 rounded-lg font-semibold bg-amber-500 text-slate-950 shrink-0 cursor-pointer';
                } else {
                    btn.className = 'lib-equip-btn px-2.5 py-1 rounded-lg font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer';
                }
            });

            renderLibExercises();
        }

        function loadMoreLibExercises() {
            libDisplayLimit += 32;
            renderLibExercises(false);
        }

        function getFilteredExercises() {
            const q = (document.getElementById('libSearchInput')?.value || '').toLowerCase().trim();
            const clearBtn = document.getElementById('libClearSearchBtn');
            if (clearBtn) {
                clearBtn.classList.toggle('hidden', !q);
            }

            return exerciseLibraryData.filter(ex => {
                const matchesCat = libCurrentCategory === 'all' || ex.category.toLowerCase() === libCurrentCategory.toLowerCase();
                const matchesEquip = libCurrentEquipment === 'all' || ex.equipment.toLowerCase() === libCurrentEquipment.toLowerCase();
                const matchesQ = !q || ex.title.toLowerCase().includes(q) || ex.category.toLowerCase().includes(q) || ex.equipment.toLowerCase().includes(q);
                return matchesCat && matchesEquip && matchesQ;
            });
        }

        function renderLibExercises(resetScroll = true) {
            const container = document.getElementById('libExercisesGrid');
            if (!container) return;

            const filtered = getFilteredExercises();
            const filteredCount = filtered.length;

            const counterEl = document.getElementById('libCounterText');
            if (counterEl) {
                counterEl.innerText = `Showing ${Math.min(filteredCount, libDisplayLimit)} of ${filteredCount} videos`;
            }

            if (filteredCount === 0) {
                container.innerHTML = `
                    <div class="col-span-full p-12 text-center bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
                        <h4 class="text-sm font-semibold text-slate-800 dark:text-slate-200">No exercise video matched</h4>
                        <p class="text-xs text-slate-500 mt-1">Try a different muscle category, equipment filter, or search term.</p>
                    </div>
                `;
                document.getElementById('libLoadMoreContainer').classList.add('hidden');
                return;
            }

            const visibleItems = filtered.slice(0, libDisplayLimit);

            let html = '';
            visibleItems.forEach(ex => {
                const safeTitle = escapeHtml(ex.title);
                const safeCat = escapeHtml(ex.category);
                const safeEquip = escapeHtml(ex.equipment);
                const safeNotes = escapeHtml(ex.notes || '');
                const safeUrl = escapeHtml(ex.video_url);

                html += `
                    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex flex-col justify-between hover:border-indigo-500/50 hover:shadow-md transition-all group">
                        <div>
                            <div class="relative aspect-video bg-black overflow-hidden cursor-pointer group/vid"
                                 data-video-title="${safeTitle}"
                                 data-video-url="${safeUrl}"
                                 data-video-category="${safeCat}"
                                 data-video-equipment="${safeEquip}"
                                 data-video-notes="${safeNotes}"
                                 onclick="openExerciseVideoFromTrigger(this)">
                                <video src="${safeUrl}#t=0.1" preload="metadata" muted playsinline loop
                                       onmouseenter="this.play().catch(()=>{})"
                                       onmouseleave="this.pause();"
                                       class="w-full h-full object-cover"></video>

                                <div class="absolute inset-0 bg-black/25 group-hover/vid:bg-black/10 flex items-center justify-center transition-all">
                                    <div class="w-10 h-10 rounded-full bg-white/90 dark:bg-slate-900/90 text-slate-900 dark:text-white flex items-center justify-center shadow-lg transition-transform">
                                        <svg class="w-4 h-4 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>
                            </div>

                            <div class="p-3.5 space-y-2">
                                <h4 class="font-semibold text-slate-900 dark:text-white text-xs leading-snug line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    ${safeTitle}
                                </h4>

                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        ${safeCat}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                        ${safeEquip}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2">
                            <button type="button" onclick="copyExerciseVideoLink('${safeUrl}', this)"
                                    class="text-xs font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-1 transition-colors cursor-pointer" title="Copy video URL">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span>Copy Link</span>
                            </button>

                            <button type="button" onclick="startRoutineWithExercise(${ex.id})"
                                    class="px-3 py-1.5 rounded-lg bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-500 text-white text-xs font-semibold transition-all cursor-pointer">
                                + Add to Routine
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            const loadMoreContainer = document.getElementById('libLoadMoreContainer');
            if (filteredCount > libDisplayLimit) {
                loadMoreContainer.classList.remove('hidden');
                document.getElementById('libLoadMoreRemaining').innerText = filteredCount - libDisplayLimit;
            } else {
                loadMoreContainer.classList.add('hidden');
            }

            if (resetScroll) {
                document.getElementById('libGridScrollContainer')?.scrollTo({ top: 0, behavior: 'smooth' });
            }
        }

        function startRoutineWithExercise(exId) {
            const ex = exerciseLibraryData.find(e => e.id === exId);
            if (!ex) return;

            const modal = document.getElementById('workoutModal');
            if (modal.classList.contains('hidden')) {
                document.getElementById('workoutPlanForm')?.reset();
                document.getElementById('exerciseContainer').innerHTML = '';
                exCounter = 0;
                modal.classList.remove('hidden');
            }

            const inferred = inferTrackingFromReps(ex.default_reps, ex.category);
            addExerciseRow(
                'day_1',
                ex.title,
                inferred.sets ?? ex.default_sets,
                ex.default_reps,
                '',
                inferred.restSeconds,
                ex.notes,
                ex.category,
                ex.equipment,
                ex.equipment,
                ex.video_url,
                inferred.type,
                inferred.durationValue,
                inferred.durationUnit
            );
            closeExerciseLibraryModal();
        }

        // ==================== WORKOUT BUILDER EXERCISE PICKER MODAL ====================
        function openWorkoutExercisePicker(targetDay = null) {
            if (targetDay) {
                const daySelect = document.getElementById('pickerTargetDay');
                if (daySelect) daySelect.value = normalizeDayValue(targetDay);
            }
            pickerAddedCountThisSession = 0;
            document.getElementById('pickerSearchInput').value = '';
            togglePickerCustomExerciseForm(false);
            document.getElementById('pickerAddedSummary').innerHTML = 'Click <strong>+ Add</strong> or use <strong>+ Custom Exercise</strong> above';
            setPickerCategory('all');
            document.getElementById('workoutExercisePickerModal').classList.remove('hidden');
        }

        function closeWorkoutExercisePicker() {
            document.getElementById('workoutExercisePickerModal').classList.add('hidden');
            togglePickerCustomExerciseForm(false);
        }

        function togglePickerCustomTrackingMode() {
            const type = document.getElementById('pickerCustomExType')?.value || 'reps';
            const isTime = type === 'time';
            document.getElementById('pickerCustomExRepsWrap')?.classList.toggle('hidden', isTime);
            document.getElementById('pickerCustomExDurationWrap')?.classList.toggle('hidden', !isTime);

            const setsLabel = document.getElementById('pickerCustomExSetsLabel');
            const setsInput = document.getElementById('pickerCustomExSets');
            const restInput = document.getElementById('pickerCustomExRest');
            if (setsLabel) setsLabel.textContent = isTime ? 'Rounds' : 'Sets';
            if (setsInput) {
                if (isTime && setsInput.value === '3') setsInput.value = '1';
                if (!isTime && setsInput.value === '1') setsInput.value = '3';
            }
            if (restInput) {
                if (isTime && restInput.value === '60') restInput.value = '0';
                if (!isTime && restInput.value === '0') restInput.value = '60';
            }
        }

        function togglePickerCustomExerciseForm(forceState = null, prefillName = '') {
            const box = document.getElementById('pickerCustomExerciseBox');
            if (!box) return;
            const show = forceState !== null ? forceState : box.classList.contains('hidden');
            box.classList.toggle('hidden', !show);
            if (show) {
                const nameInput = document.getElementById('pickerCustomExName');
                if (nameInput && prefillName) {
                    nameInput.value = prefillName;
                }
                const lower = (nameInput?.value || '').toLowerCase();
                if (/treadmill|cycle|cycling|bike|elliptical|cross\s*trainer|run|jog|walk|plank|skipping|rope|rowing|stair/.test(lower)) {
                    const typeSel = document.getElementById('pickerCustomExType');
                    if (typeSel) {
                        typeSel.value = 'time';
                        togglePickerCustomTrackingMode();
                    }
                }
                nameInput?.focus();
            }
        }

        function submitPickerCustomExercise() {
            const nameEl = document.getElementById('pickerCustomExName');
            const typeEl = document.getElementById('pickerCustomExType');
            const weightEl = document.getElementById('pickerCustomExWeight');
            const setsEl = document.getElementById('pickerCustomExSets');
            const repsEl = document.getElementById('pickerCustomExReps');
            const durEl = document.getElementById('pickerCustomExDuration');
            const unitEl = document.getElementById('pickerCustomExUnit');
            const restEl = document.getElementById('pickerCustomExRest');
            const notesEl = document.getElementById('pickerCustomExNotes');

            const name = (nameEl?.value || '').trim();
            if (!name) {
                alert('Please enter an exercise name.');
                nameEl?.focus();
                return;
            }

            const trackingType = typeEl?.value === 'time' ? 'time' : 'reps';
            const targetDay = document.getElementById('pickerTargetDay')?.value || 'day_1';
            const sets = parseInt(setsEl?.value) || (trackingType === 'time' ? 1 : 3);
            const reps = (repsEl?.value || '').trim() || '10-12';
            const durationValue = parseInt(durEl?.value) || 15;
            const durationUnit = unitEl?.value === 'seconds' ? 'seconds' : 'minutes';
            const rest = parseInt(restEl?.value);
            const restSec = Number.isNaN(rest) ? (trackingType === 'time' ? 0 : 60) : rest;
            const weight = (weightEl?.value || '').trim();
            const notes = (notesEl?.value || '').trim();

            addExerciseRow(targetDay, name, sets, reps, weight, restSec, notes, '', '', '', '', trackingType, durationValue, durationUnit);
            pickerAddedCountThisSession++;

            if (nameEl) nameEl.value = '';
            if (weightEl) weightEl.value = '';
            if (notesEl) notesEl.value = '';
            togglePickerCustomExerciseForm(false);

            const summaryEl = document.getElementById('pickerAddedSummary');
            if (summaryEl) {
                summaryEl.innerHTML = `<strong>${pickerAddedCountThisSession} exercise(s)</strong> added to <strong>${formatDayLabel(targetDay)}</strong>.`;
            }
        }

        function setPickerCategory(cat) {
            pickerCurrentCategory = cat;
            document.querySelectorAll('.picker-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-semibold bg-amber-500 text-slate-950 shrink-0 cursor-pointer';
                } else {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 cursor-pointer';
                }
            });
            renderPickerExercises();
        }

        function renderPickerExercises() {
            const container = document.getElementById('pickerExercisesList');
            if (!container) return;

            const rawQuery = (document.getElementById('pickerSearchInput')?.value || '').trim();
            const q = rawQuery.toLowerCase();

            const filtered = exerciseLibraryData.filter(ex => {
                const matchesCat = pickerCurrentCategory === 'all' || ex.category.toLowerCase() === pickerCurrentCategory.toLowerCase();
                const matchesQ = !q || ex.title.toLowerCase().includes(q) || ex.category.toLowerCase().includes(q) || ex.equipment.toLowerCase().includes(q);
                return matchesCat && matchesQ;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center space-y-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400">No library exercise matched "${escapeHtml(rawQuery)}".</div>
                        <button type="button" onclick="togglePickerCustomExerciseForm(true, document.getElementById('pickerSearchInput').value.trim())"
                                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                            + Add "${escapeHtml(rawQuery || 'Custom Exercise')}" as Custom Exercise
                        </button>
                    </div>
                `;
                return;
            }

            const items = filtered.slice(0, 50);

            let html = '';
            items.forEach(ex => {
                const safeTitle = escapeHtml(ex.title);
                const safeCat = escapeHtml(ex.category);
                const safeEquip = escapeHtml(ex.equipment);
                const safeNotes = escapeHtml(ex.notes || '');
                const safeUrl = escapeHtml(ex.video_url);

                html += `
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:border-amber-500/40 transition-colors" id="picker_item_${ex.id}">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-16 h-11 rounded-lg bg-black overflow-hidden relative shrink-0 cursor-pointer group/prev"
                                 data-video-title="${safeTitle}"
                                 data-video-url="${safeUrl}"
                                 data-video-category="${safeCat}"
                                 data-video-equipment="${safeEquip}"
                                 data-video-notes="${safeNotes}"
                                 onclick="openExerciseVideoFromTrigger(this)">
                                <video src="${safeUrl}#t=0.1" preload="metadata" muted playsinline class="w-full h-full object-cover"></video>
                                <div class="absolute inset-0 bg-black/30 group-hover/prev:bg-black/10 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="text-xs font-semibold text-slate-900 dark:text-white leading-tight">${safeTitle}</span>
                                    <span class="px-2 py-0.5 rounded-md text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">${safeCat} · ${safeEquip}</span>
                                </div>
                                ${ex.notes ? `<div class="text-xs text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">${safeNotes}</div>` : ''}
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 shrink-0">
                            <button type="button"
                                    data-video-title="${safeTitle}"
                                    data-video-url="${safeUrl}"
                                    data-video-category="${safeCat}"
                                    data-video-equipment="${safeEquip}"
                                    data-video-notes="${safeNotes}"
                                    onclick="openExerciseVideoFromTrigger(this)"
                                    class="px-2.5 py-1.5 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-medium hover:text-indigo-600 transition-colors cursor-pointer">
                                Preview
                            </button>
                            <button type="button" id="btn_picker_add_${ex.id}" onclick="addExerciseFromPicker(${ex.id})"
                                    class="px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-semibold transition-all cursor-pointer">
                                + Add
                            </button>
                        </div>
                    </div>
                `;
            });

            if (filtered.length > 50) {
                html += `<div class="p-2 text-center text-xs text-slate-400">Showing first 50 of ${filtered.length} results. Refine with search above.</div>`;
            }

            container.innerHTML = html;
        }

        function addExerciseFromPicker(exId) {
            const ex = exerciseLibraryData.find(e => e.id === exId);
            if (!ex) return;

            const targetDay = document.getElementById('pickerTargetDay')?.value || 'day_1';
            const inferred = inferTrackingFromReps(ex.default_reps, ex.category);

            addExerciseRow(
                targetDay,
                ex.title,
                inferred.sets ?? ex.default_sets,
                ex.default_reps,
                '',
                inferred.restSeconds,
                ex.notes,
                ex.category,
                ex.equipment,
                ex.equipment,
                ex.video_url,
                inferred.type,
                inferred.durationValue,
                inferred.durationUnit
            );

            pickerAddedCountThisSession++;

            const btn = document.getElementById('btn_picker_add_' + ex.id);
            if (btn) {
                btn.className = 'px-3.5 py-1.5 rounded-lg bg-emerald-600 text-white text-xs font-semibold transition-all';
                btn.innerText = 'Added';
                setTimeout(() => {
                    btn.className = 'px-3.5 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-semibold transition-all cursor-pointer';
                    btn.innerText = '+ Add';
                }, 1500);
            }

            const summaryEl = document.getElementById('pickerAddedSummary');
            if (summaryEl) {
                summaryEl.innerHTML = `<strong>${pickerAddedCountThisSession} exercise(s)</strong> added to <strong>${formatDayLabel(targetDay)}</strong>.`;
            }
        }
    </script>
</x-app-layout>
