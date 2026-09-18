<x-app-layout header="">
    <div class="space-y-6">
        <!-- Success / Error Alerts -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-sm">✕</button>
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
                    <span>🏋️ Workout Plan</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Build personalized exercise splits, strength programs, and cardio regimens for gym members.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openWorkoutModal()" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
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
            <div class="flex items-center gap-2">
                <button type="button" onclick="filterWorkouts('all')" id="btnFilterAll" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm">All Routines ({{ $plans->total() }})</button>
                <button type="button" onclick="filterWorkouts('member')" id="btnFilterMember" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Member Assigned</button>
                <button type="button" onclick="filterWorkouts('template')" id="btnFilterTemplate" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">Templates Only</button>
            </div>

            <div class="relative min-w-[260px]">
                <input type="text" id="workoutSearchInput" oninput="searchWorkouts()" placeholder="Search routine title or member..." class="w-full pl-9 pr-4 py-1.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Workout Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4" id="workoutGrid">
            @forelse($plans as $plan)
                <div class="workout-card p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex flex-col justify-between hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($plan->member?->full_name ?? 'template') }}">
                    <div>
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h4 class="font-bold text-slate-900 dark:text-white text-base leading-tight">{{ $plan->title }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Level: <span class="capitalize text-slate-800 dark:text-slate-200 font-semibold">{{ $plan->level }}</span> •
                                    Assigned: <span class="text-indigo-600 dark:text-indigo-400 font-semibold">{{ $plan->member?->full_name ?? 'Universal Template' }}</span>
                                </p>
                            </div>
                            <span class="px-2.5 py-1 rounded-xl text-[10px] bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 font-bold uppercase tracking-wider whitespace-nowrap">{{ $plan->goal }}</span>
                        </div>

                        @if($plan->trainer)
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 mb-3">Coach: <strong class="text-slate-800 dark:text-slate-200">{{ $plan->trainer->full_name }}</strong></p>
                        @endif

                        <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-3">
                            <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider block mb-2">Prescribed Exercises ({{ $plan->exercises->count() }})</span>
                            <div class="space-y-1.5 max-h-56 overflow-y-auto pr-1">
                                @forelse($plan->exercises as $ex)
                                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-xs">
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white flex items-center gap-2">
                                                <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-mono">{{ $ex->day ?? 'Day 1' }}</span>
                                                <span>{{ $ex->exercise_name }}</span>
                                            </div>
                                            @if($ex->notes)
                                                <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">{{ $ex->notes }}</p>
                                            @endif
                                        </div>
                                        <div class="text-right">
                                            <span class="text-amber-600 dark:text-amber-400 font-bold">{{ $ex->sets }} sets × {{ $ex->reps }}</span>
                                            @if($ex->weight)
                                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block font-mono">{{ $ex->weight }}</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <span class="text-xs text-slate-400 dark:text-slate-500">No exercises added to this routine yet.</span>
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

                    <div class="border-t border-slate-100 dark:border-slate-800 pt-3 mt-4 flex items-center justify-end">
                        <form action="{{ route('app.workouts.delete', $plan->id) }}" method="POST" 
                              data-confirm="Are you sure you want to delete the workout routine '{{ addslashes($plan->title) }}'?" 
                              data-confirm-title="Delete Workout Routine" 
                              data-confirm-btn="Yes, Delete Routine" 
                              data-confirm-type="danger">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 text-xs font-semibold border border-slate-200 dark:border-slate-700 hover:border-rose-300 dark:hover:border-rose-900/50 transition-colors cursor-pointer">
                                Delete Routine
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
                    <button type="button" onclick="openWorkoutModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all">
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
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Create Workout Routine</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure routine name, target goal, experience level, and exercises.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeWorkoutModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
                </div>

                <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Routine Title *</label>
                            <input type="text" name="title" required placeholder="e.g. 4-Day Push/Pull/Legs Split"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Target Goal</label>
                            <select name="goal" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="Hypertrophy">Hypertrophy (Muscle Gain)</option>
                                <option value="Fat Loss">Fat Loss & Conditioning</option>
                                <option value="Strength">Strength & Power</option>
                                <option value="Endurance">Cardio & Endurance</option>
                                <option value="General Fitness">General Fitness</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Experience Level</label>
                            <select name="level" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assign to Member</label>
                            <select name="member_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="">-- Universal Template (No Member) --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Trainer / Coach</label>
                            <select name="trainer_id" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
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
                            <div class="flex items-center gap-2">
                                <button type="button" onclick="loadPplPreset()" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 text-[11px] font-semibold border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer">
                                    + Insert Push Day Sample
                                </button>
                                <button type="button" onclick="addExerciseRow()" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold transition-colors cursor-pointer">
                                    + Add Exercise
                                </button>
                            </div>
                        </div>

                        <div id="exerciseContainer" class="space-y-2">
                            <!-- Dynamic Exercise Rows Added Here -->
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Program Notes / Advice</label>
                        <textarea name="notes" rows="2" placeholder="e.g. Warm up 5 mins before heavy compound movements. Progressive overload each week."
                                  class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500"></textarea>
                    </div>
                </div>

                <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" onclick="closeWorkoutModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all cursor-pointer">
                        Save Workout Routine
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let exCounter = 0;

        function filterWorkouts(type) {
            const activeClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm';
            const inactiveClass = 'px-3 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800';

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
            loadPplPreset();
            document.getElementById('workoutModal').classList.remove('hidden');
        }

        function closeWorkoutModal() {
            document.getElementById('workoutModal').classList.add('hidden');
        }

        function loadPplPreset() {
            document.getElementById('exerciseContainer').innerHTML = '';
            exCounter = 0;
            addExerciseRow('Day 1 - Push', 'Barbell Bench Press', 4, '8-10', 'As per RPE 8');
            addExerciseRow('Day 1 - Push', 'Incline Dumbbell Press', 3, '10-12', 'Moderate');
            addExerciseRow('Day 1 - Push', 'Triceps Rope Pushdown', 3, '12-15', 'Light/Moderate');
        }

        function addExerciseRow(day = 'Day 1', name = '', sets = 3, reps = '10-12', weight = '') {
            exCounter++;
            const container = document.getElementById('exerciseContainer');
            const rowId = 'ex_row_' + exCounter;

            const html = `
                <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 grid grid-cols-1 sm:grid-cols-6 gap-2 items-center" id="${rowId}">
                    <div>
                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5 sm:hidden">Day / Split</label>
                        <input type="text" name="exercises[${exCounter}][day]" value="${day}" placeholder="Day 1" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5 sm:hidden">Exercise Name</label>
                        <input type="text" name="exercises[${exCounter}][exercise_name]" value="${name}" required placeholder="Exercise name" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5 sm:hidden">Sets</label>
                        <input type="number" name="exercises[${exCounter}][sets]" value="${sets}" min="1" placeholder="Sets" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5 sm:hidden">Reps</label>
                        <input type="text" name="exercises[${exCounter}][reps]" value="${reps}" placeholder="Reps" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                    </div>
                    <div class="flex items-center gap-1.5">
                        <div class="flex-1">
                            <label class="block text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-0.5 sm:hidden">Weight</label>
                            <input type="text" name="exercises[${exCounter}][weight]" value="${weight}" placeholder="Weight" class="w-full px-2 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white">
                        </div>
                        <button type="button" onclick="document.getElementById('${rowId}').remove()" class="text-rose-500 hover:text-rose-600 dark:text-rose-400 dark:hover:text-rose-300 px-1 font-bold pt-3 sm:pt-0">✕</button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }
    </script>
</x-app-layout>
