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
                    <span>🏋️ Workout Plans &amp; Exercise Library</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Build personalized exercise splits from our comprehensive Exercise Library or custom routines for gym members.</p>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 sm:gap-2.5">
                <button type="button" onclick="openExerciseLibraryModal()" class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-300 text-xs font-bold border border-amber-500/25 flex items-center gap-2 transition-all cursor-pointer shadow-xs">
                    <span class="text-sm">🎬</span>
                    <span>Exercise Video Library ({{ count($exercises ?? []) }})</span>
                </button>
                <button type="button" onclick="openWorkoutModal()" class="w-full sm:w-auto justify-center px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Create Workout Routine</span>
                </button>
            </div>
        </div>

        <!-- Metric Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-4">
            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Routines</div>
                <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $plans->total() }}</div>
                <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Active programs</div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
                <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Member Specific</div>
                <div class="text-xl sm:text-2xl font-black text-indigo-600 dark:text-indigo-400 mt-1">{{ $plans->where('is_template', false)->count() }}</div>
                <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Assigned to clients</div>
            </div>

            <div class="p-3.5 sm:p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm col-span-2 sm:col-span-1">
                <div class="text-[10px] sm:text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Routine Templates</div>
                <div class="text-xl sm:text-2xl font-black text-amber-600 dark:text-amber-400 mt-1">{{ $plans->where('is_template', true)->count() }}</div>
                <div class="text-[10px] sm:text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">Reusable splits</div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-1.5 sm:gap-2 overflow-x-auto pb-1 sm:pb-0 scrollbar-none">
                <button type="button" onclick="filterWorkouts('all')" id="btnFilterAll" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm cursor-pointer shrink-0">All Routines ({{ $plans->total() }})</button>
                <button type="button" onclick="filterWorkouts('member')" id="btnFilterMember" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0">Member Assigned</button>
                <button type="button" onclick="filterWorkouts('template')" id="btnFilterTemplate" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 cursor-pointer shrink-0">Templates Only</button>
            </div>

            <div class="relative w-full sm:w-72">
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
                        if (!empty($ex->video_url)) {
                            $whatsappWorkoutText .= "   ▶️ Video Demo: " . $ex->video_url . "\n";
                        }
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
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2 flex-wrap">
                                                <span class="text-[10px] px-2 py-0.5 rounded-md bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-mono font-bold shrink-0">
                                                    {{ ucwords(str_replace('_', ' ', $ex->day ?? 'Day 1')) }}
                                                </span>
                                                <span class="truncate">{{ $ex->exercise_name }}</span>
                                                @if(!empty($ex->video_url))
                                                    <button type="button" onclick="openExerciseVideoModal('{{ addslashes($ex->exercise_name) }}', '{{ $ex->video_url }}', '{{ $ex->day }}', 'Exercise Demo')" 
                                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/20 border border-indigo-500/20 shrink-0 transition-colors cursor-pointer" 
                                                            title="Watch Form Demonstration Video">
                                                        <span>▶️</span>
                                                        <span class="hidden sm:inline">Demo</span>
                                                    </button>
                                                @endif
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
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                            <div>
                                <span class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Exercise List</span>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Pick from Exercise Library or add custom movements with sets, reps &amp; cues.</p>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="openWorkoutExercisePicker()" class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all cursor-pointer flex items-center gap-1.5 shadow-sm">
                                    <span>🏋️ + Pick from Exercise Library</span>
                                </button>
                                <button type="button" onclick="addExerciseRow()" class="w-full sm:w-auto justify-center px-3.5 py-2 rounded-xl bg-slate-200 dark:bg-slate-800 hover:bg-slate-300 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1">
                                    <span>+ Custom Row</span>
                                </button>
                            </div>
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

                <div class="p-3.5 sm:p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
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

    <!-- ==================== 1. DEDICATED EXERCISE VIDEO PLAYER MODAL ==================== -->
    <div id="exerciseVideoModal" class="fixed inset-0 z-[70] flex items-center justify-center hidden bg-black/85 backdrop-blur-md p-2 sm:p-4 overflow-y-auto">
        <div class="bg-slate-900 border border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl relative my-auto flex flex-col text-white">
            <!-- Header -->
            <div class="p-3.5 sm:p-4 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-8 h-8 sm:w-9 sm:h-9 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center text-sm font-bold shadow-xs shrink-0">
                        ▶️
                    </div>
                    <div class="min-w-0">
                        <h3 id="videoModalTitle" class="text-xs sm:text-sm font-black text-white leading-tight truncate">Exercise Video</h3>
                        <div class="flex items-center gap-1.5 sm:gap-2 mt-0.5 text-xs text-slate-400 flex-wrap">
                            <span id="videoModalCategory" class="px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 font-bold text-[10px]">Category</span>
                            <span id="videoModalEquipment" class="px-2 py-0.5 rounded-md bg-slate-800 text-slate-300 font-mono text-[10px]">Equipment</span>
                        </div>
                    </div>
                </div>
                <button type="button" onclick="closeExerciseVideoModal()" class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition-colors font-bold cursor-pointer shrink-0">✕</button>
            </div>

            <!-- Video Player -->
            <div class="relative bg-black flex items-center justify-center aspect-video w-full max-h-[55vh] sm:max-h-[60vh]">
                <video id="modalVideoPlayer" controls autoplay loop playsinline class="w-full h-full object-contain"></video>
            </div>

            <!-- Form / Coaching Cue -->
            <div class="p-3.5 sm:p-4 bg-slate-950/90 border-t border-slate-800/80 space-y-3">
                <p id="videoModalNotes" class="text-xs text-slate-300 bg-slate-900 p-2.5 rounded-xl border border-slate-800 leading-relaxed">
                    💡 Proper technique: Keep core tight, maintain controlled tempo, and perform full range of motion.
                </p>

                <!-- Action Bar -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2 pt-1">
                    <button type="button" id="videoModalCopyBtn" onclick="copyCurrentModalVideoLink()" 
                            class="w-full sm:w-auto px-3.5 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                        <span id="videoModalCopyText">Copy Video Link</span>
                    </button>

                    <button type="button" id="videoModalAddBtn" onclick="addCurrentModalVideoToRoutine()" 
                            class="w-full sm:w-auto px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-lg shadow-indigo-600/20 transition-all flex items-center justify-center gap-1.5 cursor-pointer">
                        <span>➕ Add to Routine</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 2. MASTER 702 EXERCISE VIDEO LIBRARY MODAL ==================== -->
    <div id="exerciseLibraryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/85 backdrop-blur-md p-1.5 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-6xl overflow-hidden shadow-2xl relative my-auto max-h-[96vh] sm:max-h-[94vh] flex flex-col text-slate-900 dark:text-slate-200">
            <!-- Modal Header -->
            <div class="p-3.5 sm:p-5 bg-gradient-to-r from-amber-50 via-slate-50 to-white dark:from-slate-950 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5 sm:gap-3 min-w-0">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl sm:rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center text-base sm:text-lg font-bold shadow-xs shrink-0">
                        🎬
                    </div>
                    <div class="min-w-0">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                            <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white truncate">Master Exercise Video Library</h3>
                            <span class="px-2 sm:px-2.5 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] sm:text-[11px] font-mono font-bold shrink-0">
                                702 Videos
                            </span>
                        </div>
                        <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5 truncate hidden xs:block sm:block">Demonstration videos with target muscles &amp; equipment.</p>
                    </div>
                </div>
                <button type="button" onclick="closeExerciseLibraryModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer font-bold shrink-0">✕</button>
            </div>

            <!-- Filter Controls Bar -->
            <div class="p-3 sm:p-4 bg-slate-50 dark:bg-slate-950/70 border-b border-slate-200 dark:border-slate-800 shrink-0 space-y-2.5 sm:space-y-3">
                <!-- Search & Equipment Row -->
                <div class="flex flex-col md:flex-row gap-2.5 md:gap-3 items-stretch md:items-center justify-between">
                    <div class="relative w-full md:w-80 shrink-0">
                        <input type="text" id="libSearchInput" oninput="debounceLibFilter()" 
                               placeholder="Search 700+ exercises by title or muscle..." 
                               class="w-full pl-9 pr-8 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500 shadow-xs">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        <button type="button" onclick="clearLibSearch()" id="libClearSearchBtn" class="hidden absolute right-2.5 top-2.5 text-xs text-slate-400 hover:text-slate-600 font-bold">✕</button>
                    </div>

                    <!-- Equipment Filter Chips -->
                    <div class="flex items-center gap-1.5 overflow-x-auto w-full md:w-auto text-[11px] pb-1 md:pb-0 scrollbar-none">
                        <span class="text-[10px] text-slate-400 font-bold uppercase shrink-0">Equipment:</span>
                        <button type="button" onclick="setLibEquipment('all')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold bg-amber-500 text-slate-950 shrink-0 shadow-xs" data-equip="all">All</button>
                        <button type="button" onclick="setLibEquipment('Dumbbell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Dumbbell">Dumbbell</button>
                        <button type="button" onclick="setLibEquipment('Barbell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Barbell">Barbell</button>
                        <button type="button" onclick="setLibEquipment('Cable')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Cable">Cable</button>
                        <button type="button" onclick="setLibEquipment('Machine')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Machine">Machine</button>
                        <button type="button" onclick="setLibEquipment('Bodyweight')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Bodyweight">Bodyweight</button>
                        <button type="button" onclick="setLibEquipment('Kettlebell')" class="lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-equip="Kettlebell">Kettlebell</button>
                    </div>
                </div>

                <!-- Primary Category Filter Pills -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 text-xs scrollbar-none">
                    <button type="button" onclick="setLibCategory('all')" class="lib-cat-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white shrink-0 shadow-xs transition-all" data-cat="all">
                        ⚡ All (702)
                    </button>
                    @foreach($exerciseCategories ?? [] as $cName => $cMeta)
                        @if($cName !== 'All')
                            <button type="button" onclick="setLibCategory('{{ $cName }}')" class="lib-cat-btn px-3 py-1.5 rounded-xl font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 transition-colors" data-cat="{{ $cName }}">
                                <span>{{ $cMeta['icon'] ?? '🏋️' }}</span>
                                <span>{{ $cName }} ({{ $cMeta['count'] ?? 0 }})</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Video Count Status Bar (Exactly matching user reference image) -->
            <div class="px-3 sm:px-4 py-2 bg-slate-100/80 dark:bg-slate-950/80 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 shrink-0 flex-wrap gap-1">
                <span id="libCounterText" class="font-bold text-xs">Showing 702 of 702 videos</span>
                <span class="text-[10px] sm:text-[11px] text-slate-400 hidden sm:inline">💡 Hover to preview • Click to play full video</span>
            </div>

            <!-- Exercise Cards Grid (Responsive Grid) -->
            <div class="p-3 sm:p-5 overflow-y-auto flex-1" id="libGridScrollContainer">
                <div class="grid grid-cols-1 xs:grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 sm:gap-4" id="libExercisesGrid">
                    <!-- Dynamically populated from JS -->
                </div>

                <!-- Load More Button -->
                <div id="libLoadMoreContainer" class="mt-6 text-center pb-2 hidden">
                    <button type="button" onclick="loadMoreLibExercises()" class="w-full sm:w-auto px-6 py-2.5 rounded-2xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-800 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 shadow-sm transition-all cursor-pointer">
                        ↓ Load More Exercises (<span id="libLoadMoreRemaining">0</span> remaining)
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 3. WORKOUT EXERCISE PICKER POPUP (WITH VIDEO PREVIEWS) ==================== -->
    <div id="workoutExercisePickerModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/80 backdrop-blur-md p-2 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl sm:rounded-3xl w-full max-w-4xl overflow-hidden shadow-2xl relative my-auto max-h-[96vh] sm:max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            <!-- Header -->
            <div class="p-3.5 sm:p-4 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5 min-w-0">
                    <span class="text-lg sm:text-xl shrink-0">🏋️</span>
                    <div class="min-w-0">
                        <h4 class="text-xs sm:text-sm font-black text-slate-900 dark:text-white truncate">Add Exercises to Workout Routine</h4>
                        <p class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 truncate hidden xs:block">Watch video previews and insert movements directly into your workout plan.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWorkoutExercisePicker()" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-xs font-bold cursor-pointer shrink-0">✕</button>
            </div>

            <!-- Target Day Selector & Category Filters -->
            <div class="p-3 sm:p-3.5 bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 shrink-0 space-y-2.5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 sm:gap-2.5">
                    <div class="flex flex-col xs:flex-row items-stretch xs:items-center gap-1.5 sm:gap-2">
                        <label class="text-[10px] sm:text-[11px] font-bold text-slate-700 dark:text-slate-300 uppercase shrink-0">Target Day:</label>
                        <select id="pickerTargetDay" class="w-full xs:w-auto px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                            <option value="Day 1">Day 1 (Push / Chest)</option>
                            <option value="Day 2">Day 2 (Pull / Back)</option>
                            <option value="Day 3">Day 3 (Legs / Quads)</option>
                            <option value="Day 4">Day 4 (Shoulders &amp; Arms)</option>
                            <option value="Day 5">Day 5 (Upper Body)</option>
                            <option value="Day 6">Day 6 (Lower Body / Conditioning)</option>
                            <option value="Day 7">Day 7 (Full Body / Active Recovery)</option>
                        </select>
                    </div>

                    <div class="relative w-full sm:w-72">
                        <input type="text" id="pickerSearchInput" oninput="debouncePickerFilter()" 
                               placeholder="Search 700+ exercises..." 
                               class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500 shadow-xs">
                    </div>
                </div>

                <!-- Category Chips -->
                <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] pb-1 scrollbar-none">
                    <button type="button" onclick="setPickerCategory('all')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold bg-amber-500 text-slate-950 shrink-0" data-cat="all">All (702)</button>
                    @foreach($exerciseCategories ?? [] as $cName => $cMeta)
                        @if($cName !== 'All')
                            <button type="button" onclick="setPickerCategory('{{ $cName }}')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0" data-cat="{{ $cName }}">
                                <span>{{ $cMeta['icon'] ?? '' }}</span>
                                <span>{{ $cName }}</span>
                            </button>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Exercises List with Video Previews -->
            <div class="p-3 sm:p-4 overflow-y-auto flex-1 space-y-2.5" id="pickerExercisesList">
                <!-- Populated by JS -->
            </div>

            <!-- Sticky Bottom Toolbar -->
            <div class="p-3 bg-slate-100 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0 gap-2">
                <div class="text-[11px] sm:text-xs font-bold text-slate-700 dark:text-slate-300 truncate" id="pickerAddedSummary">
                    <span>Click <strong>+ Add to Day</strong> to insert into routine</span>
                </div>
                <button type="button" onclick="closeWorkoutExercisePicker()" class="px-4 sm:px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-md transition-all cursor-pointer shrink-0">
                    Done &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== JAVASCRIPT LOGIC ==================== -->
    <script>
        const exerciseLibraryData = {!! json_encode($exercises ?? []) !!};
        const exerciseCategoriesData = {!! json_encode($exerciseCategories ?? []) !!};

        let exCounter = 0;
        let pickerCurrentCategory = 'all';
        let pickerAddedCountThisSession = 0;

        let libCurrentCategory = 'all';
        let libCurrentEquipment = 'all';
        let libDisplayLimit = 32;
        let currentModalExercise = null;

        let libFilterTimeout = null;
        let pickerFilterTimeout = null;

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
                const bench = exerciseLibraryData.find(e => e.title.includes('Bench Press') && e.category === 'Chest');
                const inc = exerciseLibraryData.find(e => e.title.includes('Incline') && e.category === 'Chest');
                const lat = exerciseLibraryData.find(e => e.title.includes('Lateral Raise') && e.category === 'Shoulders');
                const tri = exerciseLibraryData.find(e => e.category === 'Triceps');

                if (bench) addExerciseRow('Day 1', bench.title, 4, '8-10', '60 kg', bench.notes, bench.category, bench.sub_category, bench.equipment, bench.video_url);
                if (inc) addExerciseRow('Day 1', inc.title, 3, '10-12', '22 kg', inc.notes, inc.category, inc.sub_category, inc.equipment, inc.video_url);
                if (lat) addExerciseRow('Day 1', lat.title, 4, '12-15', '10 kg', lat.notes, lat.category, lat.sub_category, lat.equipment, lat.video_url);
                if (tri) addExerciseRow('Day 1', tri.title, 3, '12-15', '25 kg', tri.notes, tri.category, tri.sub_category, tri.equipment, tri.video_url);
            } else if (preset === 'pull') {
                if (titleInput) titleInput.value = 'Pull Day (Back & Biceps)';
                if (goalInput) goalInput.value = 'Hypertrophy';
                const row = exerciseLibraryData.find(e => e.title.includes('Barbell Row') && e.category === 'Back');
                const dead = exerciseLibraryData.find(e => e.title.includes('Deadlift') && e.category === 'Back');
                const bic = exerciseLibraryData.find(e => e.category === 'Biceps');

                if (row) addExerciseRow('Day 2', row.title, 4, '8-10', '50 kg', row.notes, row.category, row.sub_category, row.equipment, row.video_url);
                if (dead) addExerciseRow('Day 2', dead.title, 4, '6-8', '80 kg', dead.notes, dead.category, dead.sub_category, dead.equipment, dead.video_url);
                if (bic) addExerciseRow('Day 2', bic.title, 3, '10-12', '20 kg', bic.notes, bic.category, bic.sub_category, bic.equipment, bic.video_url);
            } else if (preset === 'legs') {
                if (titleInput) titleInput.value = 'Leg Day (Quads, Glutes & Calves)';
                if (goalInput) goalInput.value = 'Strength';
                const sq = exerciseLibraryData.find(e => e.title.includes('Squat') && e.category === 'Quads');
                const glu = exerciseLibraryData.find(e => e.category === 'Glutes');
                const calf = exerciseLibraryData.find(e => e.category === 'Calves');

                if (sq) addExerciseRow('Day 3', sq.title, 4, '6-8', '70 kg', sq.notes, sq.category, sq.sub_category, sq.equipment, sq.video_url);
                if (glu) addExerciseRow('Day 3', glu.title, 4, '8-10', '60 kg', glu.notes, glu.category, glu.sub_category, glu.equipment, glu.video_url);
                if (calf) addExerciseRow('Day 3', calf.title, 4, '15-20', 'Bodyweight', calf.notes, calf.category, calf.sub_category, calf.equipment, calf.video_url);
            }
        }

        function addExerciseRow(day = 'Day 1', name = '', sets = 3, reps = '10-12', weight = '', notes = '', category = '', subCategory = '', equipment = '', videoUrl = '') {
            exCounter++;
            const container = document.getElementById('exerciseContainer');
            const rowId = 'ex_row_' + exCounter;

            const categoryBadge = (category || subCategory) ? `
                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                    <span>${category}</span>
                    ${subCategory ? `<span class="opacity-50">•</span><span>${subCategory}</span>` : ''}
                    ${equipment ? `<span class="opacity-50">•</span><span class="text-slate-500">${equipment}</span>` : ''}
                </span>
            ` : '';

            const videoBadge = videoUrl ? `
                <button type="button" onclick="openExerciseVideoModal('${name.replace(/'/g, "\\'")}', '${videoUrl}', '${category}', '${subCategory}')" 
                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 border border-amber-500/20 transition-colors cursor-pointer" title="Watch Exercise Form Video">
                    <span>▶️</span>
                    <span>Demo Video</span>
                </button>
            ` : '';

            const html = `
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5 relative group hover:border-indigo-500/30 transition-colors shadow-xs" id="${rowId}">
                    <input type="hidden" name="exercises[${exCounter}][video_url]" value="${videoUrl || ''}">
                    <div class="flex flex-col xs:flex-row sm:flex-row items-start xs:items-center sm:items-center justify-between pb-1.5 border-b border-slate-200/80 dark:border-slate-800/80 gap-1.5">
                        <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap min-w-0">
                            <span class="w-5 h-5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs flex items-center justify-center font-mono font-bold shrink-0">${container.children.length + 1}</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200 truncate">${name || 'Custom Exercise'}</span>
                            ${categoryBadge}
                            ${videoBadge}
                        </div>

                        <div class="flex items-center gap-2 self-end xs:self-auto shrink-0">
                            <button type="button" onclick="openWorkoutExercisePicker('${day}')" class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:underline cursor-pointer flex items-center gap-1">
                                <span>📚</span>
                                <span>Library</span>
                            </button>
                            <button type="button" onclick="document.getElementById('${rowId}').remove()" class="p-1 rounded-lg text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 hover:text-rose-600 transition-colors cursor-pointer" title="Remove Exercise">✕</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                        <div class="sm:col-span-3">
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Day / Split</label>
                            <input type="text" name="exercises[${exCounter}][day]" value="${day}" placeholder="e.g. Day 1" class="w-full px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                        <div class="sm:col-span-9">
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Exercise Movement Title *</label>
                            <input type="text" name="exercises[${exCounter}][exercise_name]" value="${name}" required placeholder="Exercise Name (e.g. Flat Barbell Bench Press)" class="w-full px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Sets</label>
                            <input type="number" name="exercises[${exCounter}][sets]" value="${sets}" min="1" class="w-full px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Reps</label>
                            <input type="text" name="exercises[${exCounter}][reps]" value="${reps}" placeholder="8-10" class="w-full px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono font-bold text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Weight / Load</label>
                            <input type="text" name="exercises[${exCounter}][weight]" value="${weight}" placeholder="e.g. 50 kg" class="w-full px-2 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs font-mono text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 uppercase mb-0.5">Form Cue / Notes</label>
                            <input type="text" name="exercises[${exCounter}][notes]" value="${notes}" placeholder="Tempo, elbow angle, cues..." class="w-full px-2.5 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-[11px] text-slate-700 dark:text-slate-300 placeholder-slate-400 focus:outline-none focus:border-indigo-500 shadow-xs">
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        // ==================== VIDEO MODAL LOGIC ====================
        function openExerciseVideoModal(title, videoUrl, category = '', equipment = '', notes = '') {
            currentModalExercise = { title, videoUrl, category, equipment, notes };

            document.getElementById('videoModalTitle').innerText = title;
            document.getElementById('videoModalCategory').innerText = category || 'General';
            document.getElementById('videoModalEquipment').innerText = equipment || 'Exercise';
            document.getElementById('videoModalNotes').innerText = notes || 'Proper technique: Maintain smooth cadence, engage core and target muscles through full range of motion.';

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
                            span.innerText = '✓ Copied!';
                            setTimeout(() => { span.innerText = orig; }, 2000);
                        }
                    } else if (btnElement) {
                        const origHtml = btnElement.innerHTML;
                        btnElement.innerHTML = '✓ Copied!';
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
            alert('Video link copied to clipboard!');
        }

        function addCurrentModalVideoToRoutine() {
            if (!currentModalExercise) return;
            const ex = currentModalExercise;
            closeExerciseVideoModal();

            const modal = document.getElementById('workoutModal');
            if (modal.classList.contains('hidden')) {
                openWorkoutModal();
            }

            addExerciseRow('Day 1', ex.title, 3, '10-12', '', ex.notes, ex.category, ex.equipment, ex.equipment, ex.videoUrl);
        }

        // ==================== MASTER 702 EXERCISE LIBRARY MODAL ====================
        function openExerciseLibraryModal() {
            libCurrentCategory = 'all';
            libCurrentEquipment = 'all';
            libDisplayLimit = 32;
            document.getElementById('libSearchInput').value = '';
            document.getElementById('libClearSearchBtn').classList.add('hidden');
            renderLibExercises();
            document.getElementById('exerciseLibraryModal').classList.remove('hidden');
        }

        function closeExerciseLibraryModal() {
            document.getElementById('exerciseLibraryModal').classList.add('hidden');
            // Pause any running videos in the library grid
            document.querySelectorAll('#libExercisesGrid video').forEach(v => {
                v.pause();
            });
        }

        function setLibCategory(cat) {
            libCurrentCategory = cat;
            libDisplayLimit = 32;

            document.querySelectorAll('.lib-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'lib-cat-btn px-3 py-1.5 rounded-xl font-bold bg-indigo-600 text-white shrink-0 shadow-xs transition-all';
                } else {
                    btn.className = 'lib-cat-btn px-3 py-1.5 rounded-xl font-bold text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0 transition-colors';
                }
            });

            renderLibExercises();
        }

        function setLibEquipment(equip) {
            libCurrentEquipment = equip;
            libDisplayLimit = 32;

            document.querySelectorAll('.lib-equip-btn').forEach(btn => {
                if (btn.getAttribute('data-equip') === equip) {
                    btn.className = 'lib-equip-btn px-2.5 py-1 rounded-lg font-bold bg-amber-500 text-slate-950 shrink-0 shadow-xs';
                } else {
                    btn.className = 'lib-equip-btn px-2.5 py-1 rounded-lg font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0';
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
                if (q) clearBtn.classList.remove('hidden');
                else clearBtn.classList.add('hidden');
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
            const totalCount = exerciseLibraryData.length;
            const filteredCount = filtered.length;

            const counterEl = document.getElementById('libCounterText');
            if (counterEl) {
                counterEl.innerText = `Showing ${Math.min(filteredCount, libDisplayLimit)} of ${filteredCount} videos (Total 702)`;
            }

            if (filteredCount === 0) {
                container.innerHTML = `
                    <div class="col-span-full p-12 text-center bg-slate-50 dark:bg-slate-950/50 rounded-2xl border border-dashed border-slate-300 dark:border-slate-800">
                        <span class="text-3xl block mb-2">🔍</span>
                        <h4 class="text-sm font-bold text-slate-800 dark:text-slate-200">No exercise video found</h4>
                        <p class="text-xs text-slate-500 mt-1">Try another category, equipment filter, or search keyword.</p>
                    </div>
                `;
                document.getElementById('libLoadMoreContainer').classList.add('hidden');
                return;
            }

            const visibleItems = filtered.slice(0, libDisplayLimit);

            let html = '';
            visibleItems.forEach(ex => {
                const escapedTitle = ex.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const escapedUrl = ex.video_url;

                html += `
                    <div class="bg-white dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-2xl overflow-hidden flex flex-col justify-between hover:border-indigo-500/50 hover:shadow-lg transition-all group">
                        <div>
                            <!-- Video Box (matching reference image) -->
                            <div class="relative aspect-video bg-black overflow-hidden cursor-pointer group/vid" 
                                 onclick="openExerciseVideoModal('${escapedTitle}', '${escapedUrl}', '${ex.category}', '${ex.equipment}', '${(ex.notes || '').replace(/'/g, "\\'")}')">
                                <video src="${escapedUrl}#t=0.1" preload="metadata" muted playsinline loop 
                                       onmouseenter="this.play().catch(()=>{})" 
                                       onmouseleave="this.pause();" 
                                       class="w-full h-full object-cover"></video>
                                
                                <!-- Play Button Overlay -->
                                <div class="absolute inset-0 bg-black/25 group-hover/vid:bg-black/10 flex items-center justify-center transition-all">
                                    <div class="w-10 h-10 rounded-full bg-white/90 dark:bg-slate-900/90 text-slate-900 dark:text-white flex items-center justify-center shadow-lg group-hover/vid:scale-115 transition-transform">
                                        <svg class="w-4 h-4 ml-0.5 fill-current" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                                    </div>
                                </div>

                                <!-- Duration / HD Badge -->
                                <span class="absolute bottom-2 right-2 px-1.5 py-0.5 rounded bg-black/75 text-[9px] font-mono text-white font-bold backdrop-blur-xs">
                                    HD Video
                                </span>
                            </div>

                            <!-- Content Area -->
                            <div class="p-3.5 space-y-2">
                                <h4 class="font-black text-slate-900 dark:text-white text-xs leading-snug line-clamp-2 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                    ${ex.title}
                                </h4>

                                <!-- Badges (matching reference image: Abs, dumbbell etc.) -->
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200 dark:border-slate-700">
                                        ${ex.category}
                                    </span>
                                    <span class="px-2 py-0.5 rounded-md text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">
                                        ${ex.equipment.toLowerCase()}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Footer (Copy Link + Add to Routine) -->
                        <div class="p-3 pt-0 border-t border-slate-100 dark:border-slate-800/80 flex items-center justify-between gap-2 mt-2">
                            <button type="button" onclick="copyExerciseVideoLink('${escapedUrl}', this)" 
                                    class="text-[11px] font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 flex items-center gap-1 transition-colors cursor-pointer" title="Copy MP4 Video URL">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                <span>Copy Link</span>
                            </button>

                            <button type="button" onclick="startRoutineWithExercise(${ex.id})" 
                                    class="px-2.5 py-1.5 rounded-xl bg-slate-900 dark:bg-indigo-600 hover:bg-indigo-600 dark:hover:bg-indigo-500 text-white text-[11px] font-bold shadow-xs transition-all flex items-center gap-1 cursor-pointer">
                                <span>+ Add to Routine</span>
                            </button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Load more container
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
                openWorkoutModal();
            }

            addExerciseRow('Day 1', ex.title, ex.default_sets, ex.default_reps, '', ex.notes, ex.category, ex.equipment, ex.equipment, ex.video_url);
            closeExerciseLibraryModal();
        }

        // ==================== WORKOUT BUILDER EXERCISE PICKER POPUP ====================
        function openWorkoutExercisePicker(targetDay = null) {
            if (targetDay) {
                const daySelect = document.getElementById('pickerTargetDay');
                if (daySelect) daySelect.value = targetDay;
            }
            pickerCurrentCategory = 'all';
            pickerAddedCountThisSession = 0;
            document.getElementById('pickerSearchInput').value = '';
            document.getElementById('pickerAddedSummary').innerHTML = '<span>Click <strong>+ Add to Day</strong> to insert into routine</span>';
            renderPickerExercises();
            document.getElementById('workoutExercisePickerModal').classList.remove('hidden');
        }

        function closeWorkoutExercisePicker() {
            document.getElementById('workoutExercisePickerModal').classList.add('hidden');
        }

        function setPickerCategory(cat) {
            pickerCurrentCategory = cat;
            document.querySelectorAll('.picker-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-bold bg-amber-500 text-slate-950 shrink-0';
                } else {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-200 dark:hover:bg-slate-800 shrink-0';
                }
            });
            renderPickerExercises();
        }

        function renderPickerExercises() {
            const container = document.getElementById('pickerExercisesList');
            if (!container) return;

            const q = (document.getElementById('pickerSearchInput')?.value || '').toLowerCase().trim();

            const filtered = exerciseLibraryData.filter(ex => {
                const matchesCat = pickerCurrentCategory === 'all' || ex.category.toLowerCase() === pickerCurrentCategory.toLowerCase();
                const matchesQ = !q || ex.title.toLowerCase().includes(q) || ex.category.toLowerCase().includes(q) || ex.equipment.toLowerCase().includes(q);
                return matchesCat && matchesQ;
            });

            if (filtered.length === 0) {
                container.innerHTML = `<div class="p-8 text-center text-xs text-slate-500">No matching exercise found for "${q}".</div>`;
                return;
            }

            // Show top 50 in picker for speed
            const items = filtered.slice(0, 50);

            let html = '';
            items.forEach(ex => {
                const escapedTitle = ex.title.replace(/'/g, "\\'").replace(/"/g, '&quot;');
                const escapedUrl = ex.video_url;

                html += `
                    <div class="p-2.5 sm:p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 flex flex-col xs:flex-row sm:flex-row items-stretch xs:items-center sm:items-center justify-between gap-2.5 hover:border-amber-500/40 transition-colors shadow-xs" id="picker_item_${ex.id}">
                        <div class="flex items-center gap-2.5 sm:gap-3 min-w-0 flex-1">
                            <!-- Mini Video Preview -->
                            <div class="w-14 h-10 sm:w-16 sm:h-11 rounded-lg bg-black overflow-hidden relative shrink-0 cursor-pointer group/prev" 
                                 onclick="openExerciseVideoModal('${escapedTitle}', '${escapedUrl}', '${ex.category}', '${ex.equipment}')">
                                <video src="${escapedUrl}#t=0.1" preload="metadata" muted playsinline class="w-full h-full object-cover"></video>
                                <div class="absolute inset-0 bg-black/30 group-hover/prev:bg-black/10 flex items-center justify-center">
                                    <span class="w-5 h-5 rounded-full bg-white/90 text-slate-900 flex items-center justify-center text-[9px] font-bold">▶</span>
                                </div>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 sm:gap-2 flex-wrap">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white leading-tight">${ex.title}</span>
                                    <span class="px-1.5 py-0.5 rounded-md text-[9px] sm:text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800/60">${ex.category} • ${ex.equipment}</span>
                                </div>
                                <div class="text-[10px] sm:text-[11px] text-slate-500 dark:text-slate-400 mt-0.5 line-clamp-1">
                                    💡 ${ex.notes}
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-1.5 sm:gap-2 shrink-0 pt-1 xs:pt-0 border-t xs:border-t-0 border-slate-200/60 dark:border-slate-800/60">
                            <button type="button" onclick="openExerciseVideoModal('${escapedTitle}', '${escapedUrl}', '${ex.category}', '${ex.equipment}')" 
                                    class="px-2.5 py-1.5 rounded-xl bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-xs font-bold hover:text-indigo-600 flex items-center gap-1 transition-colors cursor-pointer" title="Watch Video">
                                <span>▶️</span>
                                <span class="text-[11px]">Preview</span>
                            </button>
                            <button type="button" id="btn_picker_add_${ex.id}" onclick="addExerciseFromPicker(${ex.id})"
                                    class="px-3.5 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all cursor-pointer shadow-xs">
                                + Add
                            </button>
                        </div>
                    </div>
                `;
            });

            if (filtered.length > 50) {
                html += `<div class="p-2 text-center text-xs text-slate-400">Showing first 50 results of ${filtered.length}. Type in the search box to refine.</div>`;
            }

            container.innerHTML = html;
        }

        function addExerciseFromPicker(exId) {
            const ex = exerciseLibraryData.find(e => e.id === exId);
            if (!ex) return;

            const targetDay = document.getElementById('pickerTargetDay')?.value || 'Day 1';

            addExerciseRow(targetDay, ex.title, ex.default_sets, ex.default_reps, '', ex.notes, ex.category, ex.equipment, ex.equipment, ex.video_url);

            pickerAddedCountThisSession++;

            // Visual feedback on the button
            const btn = document.getElementById('btn_picker_add_' + ex.id);
            if (btn) {
                btn.className = 'px-3 py-1.5 rounded-xl bg-emerald-500 text-white text-xs font-black transition-all shadow-xs';
                btn.innerText = '✓ Added';
                setTimeout(() => {
                    btn.className = 'px-3 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black transition-all cursor-pointer shadow-xs';
                    btn.innerText = '+ Add';
                }, 1500);
            }

            // Update bottom bar
            const summaryEl = document.getElementById('pickerAddedSummary');
            if (summaryEl) {
                summaryEl.innerHTML = `🛒 <strong>${pickerAddedCountThisSession} movement(s) added</strong> to <strong>${targetDay}</strong>! Add more or click Done.`;
            }
        }
    </script>
</x-app-layout>
