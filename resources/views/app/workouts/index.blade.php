<x-app-layout header="">
    <div class="space-y-6">
        <!-- Success / Error Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 shadow-sm">
                <div class="font-bold text-sm mb-1">Please correct the following errors:</div>
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
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                    <span>🏋️ Workout Plans</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Build personalized exercise splits, strength programs, and cardio regimens for gym members.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openWorkoutModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Workout Routine</span>
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Routines</div>
                <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $plans->total() }}</div>
                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Active programs</div>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Member Specific</div>
                <div class="text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $plans->where('is_template', false)->count() }}</div>
                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Assigned to clients</div>
            </div>

            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm col-span-2 sm:col-span-1">
                <div class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Routine Templates</div>
                <div class="text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $plans->where('is_template', true)->count() }}</div>
                <div class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Reusable splits</div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-2 flex-wrap">
                <button type="button" onclick="filterWorkouts('all')" id="btnFilterAll" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm cursor-pointer">All Routines ({{ $plans->total() }})</button>
                <button type="button" onclick="filterWorkouts('member')" id="btnFilterMember" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Member Assigned</button>
                <button type="button" onclick="filterWorkouts('template')" id="btnFilterTemplate" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer">Templates Only</button>
            </div>

            <div class="relative min-w-[260px]">
                <input type="text" id="workoutSearchInput" oninput="searchWorkouts()" placeholder="Search routine title or member..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors shadow-sm">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Workout Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="workoutGrid">
            @forelse($plans as $plan)
                @php
                    $formattedGoal = ucwords(str_replace(['_', '-'], ' ', $plan->goal ?? 'General Fitness'));
                    $formattedLevel = ucfirst(str_replace(['_', '-'], ' ', $plan->level ?? 'All Levels'));
                    $isMemberPlan = !$plan->is_template && $plan->member;

                    $whatsappWorkoutText = "🏋️ *Workout Routine from " . (auth()->user()->tenant->name ?? 'Gym') . "*\n";
                    $whatsappWorkoutText .= "*Routine:* " . $plan->title . "\n";
                    if ($plan->goal) $whatsappWorkoutText .= "*Goal:* " . $formattedGoal . " | *Level:* " . $formattedLevel . "\n";
                    $whatsappWorkoutText .= "---------------------------\n";
                    foreach ($plan->exercises as $exIdx => $ex) {
                        $dayLabel = $ex->day ? "[" . ucwords(str_replace('_', ' ', $ex->day)) . "] " : '';
                        $whatsappWorkoutText .= ($exIdx + 1) . ". *" . $dayLabel . $ex->exercise_name . "*\n";
                        $whatsappWorkoutText .= "   Sets: " . $ex->sets . " × " . $ex->reps . ($ex->weight ? " (" . $ex->weight . ")" : "") . ($ex->rest_seconds ? " | Rest: " . $ex->rest_seconds . "s" : "") . "\n";
                    }
                    if ($plan->notes) {
                        $whatsappWorkoutText .= "---------------------------\n*Notes:* " . $plan->notes . "\n";
                    }
                    $whatsappWorkoutText .= "\nKeep pushing your limits! 💪";
                @endphp

                <div class="workout-card p-4.5 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm group"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($plan->member?->full_name ?? 'template') }}">
                    <div>
                        <!-- Header -->
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-2.5 mb-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1 flex-wrap">
                                    @if($plan->is_template)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                            Master Template
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                                            Member Assigned
                                        </span>
                                    @endif

                                    <span class="text-xs text-slate-500 dark:text-slate-400">
                                        Level: <strong class="text-slate-800 dark:text-slate-200">{{ $formattedLevel }}</strong>
                                    </span>
                                </div>

                                <h4 class="font-black text-slate-900 dark:text-white text-base leading-tight">{{ $plan->title }}</h4>

                                @if($isMemberPlan)
                                    <div class="flex items-center gap-2 mt-1.5">
                                        <div class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-[9px]">
                                            {{ substr($plan->member->first_name, 0, 1) }}
                                        </div>
                                        <a href="{{ route('app.members.show', $plan->member_id) }}" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                            {{ $plan->member->full_name }}
                                        </a>
                                        @if($plan->member->phone)
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">({{ $plan->member->phone }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            @if($plan->goal)
                                <span class="self-start px-2.5 py-1 rounded-xl text-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold uppercase tracking-wider shrink-0">
                                    {{ $formattedGoal }}
                                </span>
                            @endif
                        </div>

                        @if($plan->trainer)
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-2">Coach: <strong class="text-slate-800 dark:text-slate-200">{{ $plan->trainer->full_name }}</strong></p>
                        @endif

                        <!-- Exercises List -->
                        <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-2">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">Prescribed Exercises ({{ $plan->exercises->count() }})</span>
                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1">
                                @forelse($plan->exercises as $ex)
                                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1.5 sm:gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-xs">
                                        <div class="min-w-0 flex-1">
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold shrink-0">
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

                        @if($plan->notes)
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-400 mt-3 leading-relaxed">
                                <span class="font-bold text-slate-800 dark:text-slate-300 block mb-0.5">Program Notes:</span>
                                {{ $plan->notes }}
                            </div>
                        @endif
                    </div>

                    <!-- Footer Actions -->
                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-4 flex flex-wrap items-center justify-between gap-2">
                        @if($isMemberPlan && $plan->member?->phone)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $plan->member->phone) }}?text={{ urlencode($whatsappWorkoutText) }}" 
                               target="_blank" 
                               class="px-3 py-1.5 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 hover:bg-emerald-100 text-emerald-700 dark:text-emerald-400 text-xs font-bold border border-emerald-200 dark:border-emerald-500/20 flex items-center gap-1.5 transition-colors">
                                <svg class="w-3.5 h-3.5 fill-current shrink-0" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                <span>Share on WhatsApp</span>
                            </a>
                        @else
                            <div></div>
                        @endif

                        <form action="{{ route('app.workouts.delete', $plan->id) }}" method="POST" 
                              data-confirm="Are you sure you want to delete the workout routine '{{ addslashes($plan->title) }}'?" 
                              data-confirm-title="Delete Workout Routine" 
                              data-confirm-btn="Yes, Delete Routine" 
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 dark:bg-rose-950/40 dark:hover:bg-rose-900/60 dark:text-rose-400 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                <span>Delete Routine</span>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
                    <div class="w-14 h-14 rounded-2xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 flex items-center justify-center mx-auto mb-3">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <h3 class="text-base font-bold text-slate-900 dark:text-white mb-1">No Workout Routines Created</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-sm mx-auto mb-4">Design exercise routines and workout splits for your gym members.</p>
                    <button type="button" onclick="openWorkoutModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                        + Create First Routine
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

    <!-- Create Workout Routine Modal -->
    <div id="workoutModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-3xl overflow-hidden shadow-2xl relative my-auto max-h-[90vh] flex flex-col text-slate-900 dark:text-slate-200">
            <form method="POST" action="{{ route('app.workouts.store') }}" class="flex flex-col max-h-[90vh]">
                @csrf
                <div class="p-4 sm:p-5 bg-gradient-to-r from-indigo-50 via-slate-50 to-white dark:from-indigo-950/60 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center text-lg font-bold">
                            🏋️
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Create Workout Routine</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure routine name, target goal, experience level, and exercises.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeWorkoutModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
                </div>

                <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <!-- Preset Split Quick Fill -->
                    <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                        <span class="text-[11px] font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider">⚡ Quick Presets:</span>
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <button type="button" onclick="loadWorkoutPreset('push')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Push Day
                            </button>
                            <button type="button" onclick="loadWorkoutPreset('pull')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Pull Day
                            </button>
                            <button type="button" onclick="loadWorkoutPreset('legs')" class="px-2.5 py-1 rounded-lg bg-indigo-50 dark:bg-indigo-600/20 hover:bg-indigo-600 hover:text-white text-indigo-700 dark:text-indigo-300 text-[11px] font-bold border border-indigo-200 dark:border-indigo-500/30 transition-all cursor-pointer">
                                Leg Day
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Routine Title *</label>
                            <input type="text" name="title" id="workoutTitleInput" required placeholder="e.g. 4-Day Push/Pull/Legs Split"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Target Goal</label>
                            <select name="goal" id="workoutGoalInput" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500 shadow-sm">
                                <option value="General Fitness">General Fitness</option>
                                <option value="Hypertrophy">Hypertrophy (Muscle Gain)</option>
                                <option value="Fat Loss">Fat Loss & Conditioning</option>
                                <option value="Strength">Strength & Power</option>
                                <option value="Endurance">Cardio & Endurance</option>
                                <option value="Mobility">Mobility & Posture</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Experience Level</label>
                            <select name="level" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500 shadow-sm">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assign to Member</label>
                            <select name="member_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500 shadow-sm">
                                <option value="">-- Universal Template (No Member) --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Trainer / Coach</label>
                            <select name="trainer_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500 shadow-sm">
                                <option value="">-- Optional Coach --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Exercise List</span>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Add exercises, sets, reps, and weights.</p>
                            </div>
                            <button type="button" onclick="addExerciseRow()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold transition-colors cursor-pointer flex items-center gap-1 shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                <span>Add Exercise</span>
                            </button>
                        </div>

                        <div id="exerciseContainer" class="space-y-2.5">
                            <!-- Dynamic Exercise Rows Added Here -->
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Program Notes / Advice</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Warm up 5 mins before heavy compound movements. Progressive overload each week."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500 shadow-sm"></textarea>
                    </div>
                </div>

                <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" onclick="closeWorkoutModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                        Save Workout Routine
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let exCounter = 0;

        function filterWorkouts(type) {
            const activeClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm cursor-pointer';
            const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer';

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
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            loadWorkoutPreset('push');
            document.getElementById('workoutModal').classList.remove('hidden');
        }

        function closeWorkoutModal() {
            document.getElementById('workoutModal').classList.add('hidden');
        }

        function loadWorkoutPreset(preset) {
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            const titleInput = document.getElementById('workoutTitleInput');
            const goalInput = document.getElementById('workoutGoalInput');

            if (preset === 'push') {
                if (titleInput) titleInput.value = 'Push Day (Chest, Shoulders, Triceps)';
                if (goalInput) goalInput.value = 'Hypertrophy';
                addExerciseRow('Day 1', 'Barbell Flat Bench Press', 4, '8-10', '60 kg', 'Warm up rotator cuffs');
                addExerciseRow('Day 1', 'Incline Dumbbell Press', 3, '10-12', '22 kg', 'Upper chest squeeze');
                addExerciseRow('Day 1', 'Dumbbell Lateral Raises', 4, '12-15', '10 kg', 'Strict form, no swinging');
                addExerciseRow('Day 1', 'Triceps Cable Pushdown', 3, '12-15', '25 kg', 'Full extension');
            } else if (preset === 'pull') {
                if (titleInput) titleInput.value = 'Pull Day (Back & Biceps)';
                if (goalInput) goalInput.value = 'Hypertrophy';
                addExerciseRow('Day 2', 'Lat Pulldowns / Pull-ups', 4, '8-10', '55 kg', 'Pull to upper chest');
                addExerciseRow('Day 2', 'Barbell Bent-Over Rows', 4, '8-10', '50 kg', 'Neutral back');
                addExerciseRow('Day 2', 'Face Pulls', 3, '15', '20 kg', 'Rear delt squeeze');
                addExerciseRow('Day 2', 'Barbell Bicep Curls', 3, '10-12', '25 kg', 'Controlled eccentric');
            } else if (preset === 'legs') {
                if (titleInput) titleInput.value = 'Leg Day (Quads, Hamstrings, Calves)';
                if (goalInput) goalInput.value = 'Strength';
                addExerciseRow('Day 3', 'Barbell Back Squats', 4, '6-8', '70 kg', 'Parallel depth');
                addExerciseRow('Day 3', 'Romanian Deadlifts (RDL)', 4, '8-10', '60 kg', 'Hinge at hips');
                addExerciseRow('Day 3', 'Leg Press', 3, '12-15', '120 kg', 'Continuous tension');
                addExerciseRow('Day 3', 'Standing Calf Raises', 4, '15-20', 'Bodyweight', 'Pause at peak');
            }
        }

        function addExerciseRow(day = 'Day 1', name = '', sets = 3, reps = '10-12', weight = '', notes = '') {
            exCounter++;
            const container = document.getElementById('exerciseContainer');
            const rowId = 'ex_row_' + exCounter;

            const html = `
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2 relative" id="${rowId}">
                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <div class="sm:col-span-3">
                            <input type="text" name="exercises[${exCounter}][day]" value="${day}" placeholder="Day (e.g. Day 1)" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="sm:col-span-9 flex items-center gap-2">
                            <input type="text" name="exercises[${exCounter}][exercise_name]" value="${name}" required placeholder="Exercise Name (e.g. Bench Press)" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 transition-colors shrink-0 cursor-pointer" title="Remove Exercise">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Sets</label>
                            <input type="number" name="exercises[${exCounter}][sets]" value="${sets}" min="1" class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Reps</label>
                            <input type="text" name="exercises[${exCounter}][reps]" value="${reps}" placeholder="8-10" class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Weight / Load</label>
                            <input type="text" name="exercises[${exCounter}][weight]" value="${weight}" placeholder="e.g. 50 kg" class="w-full px-2 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Notes / Cue</label>
                            <input type="text" name="exercises[${exCounter}][notes]" value="${notes}" placeholder="Tempo / cue..." class="w-full px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300 placeholder-slate-400 dark:placeholder-slate-600 focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</x-app-layout>
