<x-app-layout header="">
    <div class="space-y-6">
        <!-- Success / Error Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-700 dark:hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 shadow-sm">
                <div class="font-bold text-xs mb-1">Please fix the following issues:</div>
                <ul class="list-disc pl-5 text-xs space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ==================== TOP HEADER & ACTIONS ==================== -->
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl sm:text-2xl font-bold text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>Diet &amp; Nutrition Plans</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Create personalized daily meal regimes for members, set calories &amp; macros, and dispatch via WhatsApp.</p>
            </div>

            <!-- Primary Actions Group -->
            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                <!-- Secondary: Browse Indian Foods -->
                <button type="button" onclick="openFoodLibraryModal()" 
                        class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                    <span class="text-sm">🍲</span>
                    <span>Indian Food Library</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] font-mono font-bold">{{ count($indianFoods ?? []) }}</span>
                </button>

                <!-- Secondary: AI Diet Assistant -->
                <button type="button" onclick="openAiDietModal()" 
                        class="px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-200 text-xs font-semibold border border-slate-200 dark:border-slate-800 shadow-sm flex items-center gap-2 transition-all cursor-pointer">
                    <span class="text-sm">✨</span>
                    <span>AI Diet Generator</span>
                </button>

                <!-- Starter Presets -->
                <form action="{{ route('app.diets.seed') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 text-xs font-medium border border-slate-200 dark:border-slate-800 shadow-sm transition-all cursor-pointer"
                            title="Load pre-built Indian templates">
                        <span>📋 Presets</span>
                    </button>
                </form>

                <!-- Prominent Primary Action: Assign Diet to Member -->
                <button type="button" onclick="openDietModal()" 
                        class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-sm shadow-indigo-600/30 flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    <span>+ Assign Diet to Member</span>
                </button>
            </div>
        </div>

        <!-- ==================== QUICK MEMBER DIET BAR ==================== -->
        <div class="p-4 sm:p-5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/60 flex items-center justify-center font-bold text-lg shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-slate-900 dark:text-white">Quick Member Diet Plan Builder</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400">Select any gym member to immediately assign or edit their customized nutrition regime.</p>
                </div>
            </div>

            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                <select id="quickMemberSelect" class="px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-medium focus:border-indigo-500 min-w-[220px]">
                    <option value="">-- Select Member to Assign --</option>
                    @foreach($members as $m)
                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No phone' }})</option>
                    @endforeach
                </select>
                <button type="button" onclick="openDietModalForSelectedMember()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-sm transition-all whitespace-nowrap cursor-pointer">
                    Build Plan &rarr;
                </button>
            </div>
        </div>

        <!-- ==================== FILTER TABS & SEARCH BAR ==================== -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-2 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                <button type="button" onclick="filterPlans('all')" id="btnFilterAll" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm shrink-0">
                    All Plans ({{ $totalPlans }})
                </button>
                <button type="button" onclick="filterPlans('member')" id="btnFilterMember" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0">
                    Member Assigned ({{ $memberPlansCount }})
                </button>
                <button type="button" onclick="filterPlans('template')" id="btnFilterTemplate" 
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0">
                    Templates ({{ $templatePlansCount }})
                </button>
            </div>

            <div class="relative w-full sm:w-72">
                <input type="text" id="dietSearchInput" oninput="searchDietPlans()" 
                       placeholder="Search member, phone or plan..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- ==================== RESPONSIVE DIET PLANS GRID ==================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3 gap-5" id="dietPlansGrid">
            @forelse($plans as $plan)
                @php
                    $isMemberPlan = !$plan->is_template && $plan->member;
                    $recipientPhone = $plan->member?->phone ?? '';
                    $memberName = $plan->member?->full_name ?? ($plan->is_template ? 'Template Plan' : 'Unassigned Member');
                    $gymName = $tenant->name ?? 'Gym Console';

                    $planJson = [
                        'id' => $plan->id,
                        'title' => $plan->title,
                        'daily_calories' => $plan->daily_calories,
                        'protein_grams' => $plan->protein_grams,
                        'carbs_grams' => $plan->carbs_grams,
                        'fat_grams' => $plan->fat_grams,
                        'start_date' => $plan->start_date ? $plan->start_date->format('d M Y') : '',
                        'end_date' => $plan->end_date ? $plan->end_date->format('d M Y') : '',
                        'guidelines' => $plan->guidelines,
                        'is_template' => $plan->is_template,
                        'member_id' => $plan->member_id,
                        'trainer_id' => $plan->trainer_id,
                        'meals' => $plan->meals->map(function($m) {
                            return [
                                'meal_type' => $m->meal_type,
                                'recommended_time' => $m->recommended_time ? substr($m->recommended_time, 0, 5) : '',
                                'meal_name' => $m->meal_name,
                                'calories' => $m->calories,
                                'items_description' => $m->items_description,
                            ];
                        })->toArray(),
                    ];

                    $protein = (int) ($plan->protein_grams ?? 0);
                    $carbs = (int) ($plan->carbs_grams ?? 0);
                    $fat = (int) ($plan->fat_grams ?? 0);
                @endphp

                <div x-data="{ expanded: false }" 
                     class="diet-plan-card bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 rounded-2xl p-5 shadow-sm hover:border-slate-300 dark:hover:border-slate-700 transition-all flex flex-col justify-between"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($memberName) }}">
                    <div>
                        <!-- Card Header: Member or Template identity -->
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-3 min-w-0">
                                @if(!$plan->is_template && $plan->member)
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($plan->member->first_name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">{{ $plan->member->full_name }}</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">Member</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400 font-mono">{{ $plan->member->phone ?? 'No phone' }}</p>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/50 flex items-center justify-center font-bold text-base shrink-0">
                                        📋
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white truncate">Master Template</h4>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/50">Reusable</span>
                                        </div>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Can be assigned to any member</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Target Calories Badge -->
                            <div class="text-right shrink-0">
                                <span class="px-2.5 py-1 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-white font-mono font-bold text-xs inline-block">
                                    {{ $plan->daily_calories ? number_format($plan->daily_calories) : '2,000' }} <span class="text-[10px] text-slate-500 font-normal">kcal</span>
                                </span>
                            </div>
                        </div>

                        <!-- Plan Title & Coach -->
                        <div class="py-3">
                            <h3 class="text-sm font-bold text-slate-800 dark:text-slate-100 leading-snug line-clamp-1" title="{{ $plan->title }}">
                                {{ $plan->title }}
                            </h3>
                            @if($plan->trainer)
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Coach: <span class="text-slate-700 dark:text-slate-300 font-medium">{{ $plan->trainer->full_name }}</span></p>
                            @endif
                        </div>

                        <!-- Clean Macronutrients Strip -->
                        <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-100 dark:border-slate-800/80 text-center text-xs">
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase font-semibold">Protein</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $protein }}g</span>
                            </div>
                            <div class="border-x border-slate-200 dark:border-slate-800">
                                <span class="block text-[10px] text-slate-400 uppercase font-semibold">Carbs</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $carbs }}g</span>
                            </div>
                            <div>
                                <span class="block text-[10px] text-slate-400 uppercase font-semibold">Fats</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $fat }}g</span>
                            </div>
                        </div>

                        <!-- Meals Summary & Preview Toggle -->
                        <div class="mt-3">
                            <div class="text-xs text-slate-500 dark:text-slate-400 font-medium mb-1.5 flex items-center justify-between">
                                <span>{{ $plan->meals->count() }} Daily Meals:</span>
                                @if($plan->start_date)
                                    <span class="text-[11px] font-mono text-slate-400">{{ $plan->start_date->format('d M') }} {{ $plan->end_date ? '→ ' . $plan->end_date->format('d M') : '' }}</span>
                                @endif
                            </div>

                            <!-- Compact List of Meals (First 3) -->
                            <div class="space-y-1.5">
                                @forelse($plan->meals->take(3) as $m)
                                    <div class="px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $m->meal_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 shrink-0 font-mono">
                                            @if($m->recommended_time)
                                                <span>{{ date('h:i A', strtotime($m->recommended_time)) }}</span>
                                            @endif
                                            @if($m->calories)
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $m->calories }} kcal</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center rounded-lg bg-slate-50 dark:bg-slate-950/40 text-slate-400 text-xs">
                                        No meals added to schedule yet.
                                    </div>
                                @endforelse

                                @if($plan->meals->count() > 3)
                                    <button type="button" @click="expanded = !expanded" class="w-full text-[11px] text-indigo-600 dark:text-indigo-400 hover:underline text-center py-1 font-semibold flex items-center justify-center gap-1 cursor-pointer">
                                        <span x-text="expanded ? 'Hide full meal plan' : '+ See all {{ $plan->meals->count() }} meals & details'"></span>
                                        <svg class="w-3 h-3 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                @endif
                            </div>

                            <!-- Expandable Full Meal Schedule -->
                            <div x-show="expanded" x-cloak class="mt-2.5 space-y-2 pt-2 border-t border-slate-100 dark:border-slate-800/60">
                                @foreach($plan->meals->skip(3) as $m)
                                    <div class="px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950/60 border border-slate-100 dark:border-slate-800/60 flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span class="font-semibold text-slate-800 dark:text-slate-200 truncate">{{ $m->meal_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-[11px] text-slate-500 shrink-0 font-mono">
                                            @if($m->recommended_time)
                                                <span>{{ date('h:i A', strtotime($m->recommended_time)) }}</span>
                                            @endif
                                            @if($m->calories)
                                                <span class="font-bold text-slate-700 dark:text-slate-300">{{ $m->calories }} kcal</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                @if($plan->guidelines)
                                    <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 text-[11px] text-slate-600 dark:text-slate-400 border border-slate-100 dark:border-slate-800">
                                        <strong class="font-semibold text-slate-800 dark:text-slate-200 block mb-0.5">Hydration &amp; Guidelines:</strong>
                                        <p class="whitespace-pre-line">{{ $plan->guidelines }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Footer -->
                    <div class="pt-4 mt-4 border-t border-slate-100 dark:border-slate-800 flex items-center justify-between gap-2">
                        <button type="button"
                                onclick="openWhatsAppModal({{ json_encode($planJson) }}, {{ json_encode($memberName) }}, {{ json_encode($recipientPhone) }}, {{ json_encode($gymName) }})"
                                class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-center gap-1.5 transition-colors cursor-pointer shadow-sm">
                            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                            <span>Share on WhatsApp</span>
                        </button>

                        <button type="button"
                                onclick="editDietPlan({{ json_encode($planJson) }})"
                                class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer"
                                title="Edit Plan">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>

                        <form action="{{ route('app.diets.delete', $plan->id) }}" method="POST" 
                              data-confirm="Are you sure you want to delete diet plan '{{ addslashes($plan->title) }}'?" 
                              data-confirm-title="Delete Diet Plan" 
                              data-confirm-btn="Yes, Delete Plan" 
                              data-confirm-type="danger" 
                              class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 transition-colors cursor-pointer" title="Delete Plan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 sm:p-16 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
                    <div class="w-16 h-16 rounded-3xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto mb-4 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white mb-1">No Member Diet Plans Created Yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">Assign a custom daily nutrition program to any gym member or use our pre-built Indian templates.</p>
                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        <button type="button" onclick="openDietModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/20 transition-all cursor-pointer">
                            + Create Member Diet Plan
                        </button>
                        <form action="{{ route('app.diets.seed') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700 transition-all cursor-pointer">
                                📋 Load Indian Starter Templates
                            </button>
                        </form>
                    </div>
                </div>
            @endforelse
        </div>

        @if($plans->hasPages())
            <div class="mt-6">
                {{ $plans->links() }}
            </div>
        @endif
    </div>

    <!-- ==================== 1. INDIAN FOOD LIBRARY & MACROS EXPLORER MODAL ==================== -->
    <div id="foodLibraryModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-5xl overflow-hidden shadow-2xl relative my-auto max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-amber-50 via-slate-50 to-white dark:from-amber-950/40 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center text-xl shrink-0">
                        🍲
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Indian Gym Food &amp; Macro Database</h3>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-[10px] font-bold border border-amber-500/20">{{ count($indianFoods ?? []) }}+ Verified Foods</span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Accurate Calories, Protein, Carbs, Fats &amp; Fiber measured for Indian bodybuilding &amp; fat loss.</p>
                    </div>
                </div>
                <button type="button" onclick="closeFoodLibraryModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <!-- Search & Filters Strip -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/50 space-y-3 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="foodLibrarySearch" oninput="filterFoodLibrary()" 
                               placeholder="Search e.g. chicken, paneer, eggs, soya, oats, roti, rice, whey, banana..."
                               class="w-full pl-9 pr-4 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500">
                        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                        <button type="button" onclick="setFoodCategoryFilter('all')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500 text-slate-950 shadow-sm shrink-0" data-cat="all">All ({{ count($indianFoods ?? []) }})</button>
                        <button type="button" onclick="setFoodCategoryFilter('non_veg_protein')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="non_veg_protein">🍗 Non-Veg &amp; Eggs</button>
                        <button type="button" onclick="setFoodCategoryFilter('veg_protein')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="veg_protein">🧀 Veg Protein</button>
                        <button type="button" onclick="setFoodCategoryFilter('carbs')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="carbs">🍚 Clean Carbs</button>
                        <button type="button" onclick="setFoodCategoryFilter('fats')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="fats">🥜 Healthy Fats</button>
                        <button type="button" onclick="setFoodCategoryFilter('fruits')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="fruits">🍌 Gym Fruits</button>
                        <button type="button" onclick="setFoodCategoryFilter('salads')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="salads">🥗 Fiber &amp; Sabzi</button>
                        <button type="button" onclick="setFoodCategoryFilter('gym_meals')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0" data-cat="gym_meals">🍱 Ready Meals &amp; Shakes</button>
                    </div>
                </div>
            </div>

            <!-- Food Cards Grid -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5" id="foodLibraryGrid">
                    @foreach($indianFoods ?? [] as $food)
                        <div class="food-item-card p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 hover:border-amber-500/40 transition-all flex flex-col justify-between"
                             data-cat="{{ $food['category'] }}"
                             data-name="{{ strtolower($food['name']) }}"
                             data-hindi="{{ strtolower($food['hindi_name'] ?? '') }}">
                            <div>
                                <div class="flex items-start justify-between gap-2 mb-1.5">
                                    <div>
                                        <div class="flex items-center gap-1.5">
                                            @if($food['diet_type'] === 'non_veg')
                                                <span class="w-3.5 h-3.5 border-2 border-rose-600 p-0.5 flex items-center justify-center shrink-0" title="Non-Vegetarian">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                                </span>
                                            @elseif($food['diet_type'] === 'eggetarian')
                                                <span class="w-3.5 h-3.5 border-2 border-amber-600 p-0.5 flex items-center justify-center shrink-0" title="Eggetarian">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                                </span>
                                            @else
                                                <span class="w-3.5 h-3.5 border-2 border-emerald-600 p-0.5 flex items-center justify-center shrink-0" title="Vegetarian / Vegan">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                                </span>
                                            @endif
                                            <h4 class="text-xs font-black text-slate-900 dark:text-white leading-tight">{{ $food['name'] }}</h4>
                                        </div>
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5 font-medium pl-5">{{ $food['hindi_name'] ?? '' }} &bull; Serving: {{ $food['serving'] }}</div>
                                    </div>

                                    <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-mono font-bold shrink-0">
                                        {{ $food['calories'] }} <span class="text-[9px] font-normal">kcal</span>
                                    </span>
                                </div>

                                <!-- Macros Badges -->
                                <div class="grid grid-cols-4 gap-1.5 text-center my-2 text-[10px] font-mono">
                                    <div class="p-1 rounded-md bg-rose-500/10 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400">
                                        <span class="block text-[8px] uppercase font-sans text-slate-400">Prot</span>
                                        <strong>{{ $food['protein'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <span class="block text-[8px] uppercase font-sans text-slate-400">Carb</span>
                                        <strong>{{ $food['carbs'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-cyan-500/10 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400">
                                        <span class="block text-[8px] uppercase font-sans text-slate-400">Fat</span>
                                        <strong>{{ $food['fat'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-teal-500/10 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400">
                                        <span class="block text-[8px] uppercase font-sans text-slate-400">Fiber</span>
                                        <strong>{{ $food['fiber'] }}g</strong>
                                    </div>
                                </div>

                                <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-snug line-clamp-2">{{ $food['description'] }}</p>
                            </div>

                            <div class="pt-2.5 mt-2 border-t border-slate-200 dark:border-slate-800/80 flex items-center justify-between">
                                <span class="text-[9px] px-2 py-0.5 rounded-full {{ $food['gym_purpose'] === 'weight_loss' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($food['gym_purpose'] === 'muscle_gain' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-purple-500/10 text-purple-600 dark:text-purple-400') }} font-bold uppercase tracking-wider">
                                    {{ $food['gym_purpose'] === 'weight_loss' ? '🔥 Weight Loss' : ($food['gym_purpose'] === 'muscle_gain' ? '💪 Muscle Gain' : '⚡ All Goals') }}
                                </span>

                                <button type="button" 
                                        onclick="copyFoodDetails({{ json_encode($food) }})"
                                        class="text-[11px] font-bold text-amber-600 dark:text-amber-400 hover:text-amber-500 flex items-center gap-1 cursor-pointer">
                                    <span>Copy Info</span>
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 2. WHATSAPP DISPATCH MODAL ==================== -->
    <div id="whatsappModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl relative my-auto max-h-[90vh] flex flex-col text-slate-900 dark:text-slate-200">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 bg-gradient-to-r from-emerald-50 via-slate-50 to-white dark:from-emerald-950/60 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Send Diet Plan via WhatsApp</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Review recipient phone number and live preview before sending.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWhatsAppModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                <!-- Recipient Phone & Plan Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Recipient Mobile Number *</label>
                        <div class="relative">
                            <input type="text" id="waRecipientPhone" placeholder="e.g. 9876543210 or 919876543210"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white font-mono text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <span class="absolute right-3 top-2.5 text-[10px] text-slate-400 dark:text-slate-500 font-bold">WHATSAPP</span>
                        </div>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Indian 10-digit numbers automatically get country code added.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1.5">Recipient / Member</label>
                        <input type="text" id="waRecipientName" readonly
                               class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-950/70 border border-slate-300 dark:border-slate-800 text-slate-700 dark:text-slate-300 text-sm focus:outline-none cursor-default font-medium">
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-1">Assigned recipient in gym console records.</p>
                    </div>
                </div>

                <!-- Live Message Preview Box -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider flex items-center gap-2">
                            <span>💬 Live WhatsApp Message Preview</span>
                            <span class="text-[10px] px-2 py-0.5 rounded-md bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-semibold">Formatted Preview</span>
                        </label>
                        <button type="button" onclick="copyModalWaText()" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 font-bold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            <span id="copyModalBtnText">Copy Message</span>
                        </button>
                    </div>

                    <div class="p-4 rounded-2xl bg-[#0b141a] border border-[#202c33] text-slate-200 font-sans text-xs leading-relaxed max-h-72 overflow-y-auto whitespace-pre-wrap select-all shadow-inner" id="waMessagePreview">
                        <!-- Message text inserted here -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="closeWhatsAppModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">
                    Cancel
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="copyModalWaText()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-700 transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy Text</span>
                    </button>

                    <button type="button" onclick="sendWhatsAppNow()" class="px-5 sm:px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                        <span>Send WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 3. CREATE / EDIT DIET PLAN MODAL ==================== -->
    <div id="dietPlanModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-4xl overflow-hidden shadow-2xl relative my-auto max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            <form id="dietPlanForm" method="POST" action="{{ route('app.diets.store') }}" class="flex flex-col max-h-[92vh]">
                @csrf
                <input type="hidden" name="_method" id="dietFormMethod" value="POST">

                <!-- Modal Header -->
                <div class="p-4 sm:p-5 bg-gradient-to-r from-indigo-50 via-slate-50 to-white dark:from-indigo-950/60 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white" id="dietModalTitle">Create New Diet Plan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure calories, macro targets, and pick from pre-defined Indian Gym foods.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDietModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
                </div>

                <!-- Modal Form Body (Scrollable) -->
                <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-5">
                    <!-- Step 1: Plan Target (Member vs Master Template) -->
                    <div class="p-4 rounded-2xl bg-indigo-50/60 dark:bg-indigo-950/20 border border-indigo-200/80 dark:border-indigo-800/40 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-indigo-600 dark:text-indigo-400">Step 1</span>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Who is this diet plan for?</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Assign directly to a gym member or save as a reusable master template.</p>
                            </div>

                            <input type="hidden" name="is_template" id="dietInputIsTemplate" value="0">

                            <!-- Segmented Switcher -->
                            <div class="inline-flex p-1 bg-slate-200/80 dark:bg-slate-800 rounded-xl shrink-0">
                                <button type="button" id="btnModeMember" onclick="setPlanTypeMode(0)"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer">
                                    <span>👤 Assign to Member</span>
                                </button>
                                <button type="button" id="btnModeTemplate" onclick="setPlanTypeMode(1)"
                                        class="px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer">
                                    <span>📋 Reusable Template</span>
                                </button>
                            </div>
                        </div>

                        <!-- Member Selection Box (Shown in Member Mode) -->
                        <div id="memberSelectGroup" class="space-y-1.5 pt-1">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                                Select Member <span class="text-rose-500">*</span>
                            </label>
                            <select name="member_id" id="dietInputMemberId" onchange="handleModalMemberChange()"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-indigo-500 shadow-sm">
                                <option value="">-- Choose Member from Active Gym Members --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}" data-name="{{ $m->full_name }}">{{ $m->full_name }} ({{ $m->phone ?? 'No Phone' }})</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-emerald-600 dark:text-emerald-400 flex items-center gap-1 font-medium">
                                <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>Assigned plan will appear on the member's profile &amp; can be sent to their WhatsApp with 1 click.</span>
                            </p>
                        </div>

                        <!-- Template Explanatory Note (Shown only in Template Mode) -->
                        <div id="templateNoteGroup" class="hidden p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-700 dark:text-amber-300 font-medium">
                            📋 <strong>Master Template:</strong> This diet won't belong to a specific member. It will be saved as a reusable template that you can clone for any member later.
                        </div>

                        <!-- Plan Title -->
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">
                                Plan Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="dietInputTitle" required placeholder="e.g. Muscle Building &amp; Leaning Plan (2,200 kcal)"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Macro Target Box with Quick Preset Calculators -->
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Daily Calorie &amp; Macro Targets</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Set total daily calorie budget and macronutrient distribution.</p>
                            </div>

                            <!-- Macro Presets Quick Buttons -->
                            <div class="flex items-center gap-1.5 flex-wrap">
                                <span class="text-[10px] text-slate-400 uppercase font-bold">Auto Split:</span>
                                <button type="button" onclick="autoCalculateMacros('muscle_gain')" class="px-2 py-1 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 text-[10px] font-bold hover:bg-indigo-500/20 transition-colors">
                                    💪 Muscle Gain (High Prot)
                                </button>
                                <button type="button" onclick="autoCalculateMacros('fat_loss')" class="px-2 py-1 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 text-[10px] font-bold hover:bg-emerald-500/20 transition-colors">
                                    🔥 Fat Loss (Cutting)
                                </button>
                                <button type="button" onclick="autoCalculateMacros('maintenance')" class="px-2 py-1 rounded-lg bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 text-[10px] font-bold hover:bg-slate-300 dark:hover:bg-slate-700 transition-colors">
                                    ⚖️ Balanced
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">Target Calories (kcal) *</label>
                                <input type="number" name="daily_calories" id="dietInputCalories" required value="2000" min="500" max="10000"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-black font-mono focus:outline-none focus:border-amber-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-rose-600 dark:text-rose-400 uppercase mb-1">Protein (Grams)</label>
                                <input type="number" name="protein_grams" id="dietInputProtein" value="150" min="0" max="1000"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-black font-mono focus:outline-none focus:border-rose-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase mb-1">Carbohydrates (Grams)</label>
                                <input type="number" name="carbs_grams" id="dietInputCarbs" value="200" min="0" max="1500"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-black font-mono focus:outline-none focus:border-amber-500">
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-cyan-600 dark:text-cyan-400 uppercase mb-1">Fats (Grams)</label>
                                <input type="number" name="fat_grams" id="dietInputFats" value="55" min="0" max="500"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-black font-mono focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- Dates & Coach Info -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assigned Trainer / Coach</label>
                            <select name="trainer_id" id="dietInputTrainerId"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="">-- No Specific Trainer --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Start Date</label>
                            <input type="date" name="start_date" id="dietInputStartDate"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">End Date</label>
                            <input type="date" name="end_date" id="dietInputEndDate"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Scheduled Meals Builder with Integrated Indian Food Library Picker -->
                    <div class="space-y-3">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 pb-2">
                            <div>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                                    <span>🍽️ Scheduled Meals Builder</span>
                                    <span class="text-[10px] px-2 py-0.5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-semibold">Indian Food Integrated</span>
                                </h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Add meal timings, pick from pre-defined Indian gym foods, or type custom items.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="loadStandardMealsPreset()" class="px-2.5 py-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 hover:bg-amber-500/20 text-[11px] font-bold border border-amber-500/20 transition-colors">
                                    ⚡ Insert 5-Meal Indian Day
                                </button>
                                <button type="button" onclick="addMealRow()" class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold shadow transition-colors cursor-pointer">
                                    + Add Meal
                                </button>
                            </div>
                        </div>

                        <div id="mealsContainer" class="space-y-3.5">
                            <!-- Dynamic meal rows will be inserted here -->
                        </div>
                    </div>

                    <!-- General Guidelines / Advice -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dietary Guidelines &amp; Hydration Advice</label>
                        <textarea name="guidelines" id="dietInputGuidelines" rows="3" placeholder="e.g. Drink 3.5 - 4 liters of water daily. Avoid refined sugars, deep fried foods, and sodas. Sleep 7-8 hours for optimal protein synthesis."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500 leading-relaxed"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-4 sm:p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3 shrink-0">
                    <button type="button" onclick="closeDietModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors cursor-pointer">
                        Cancel
                    </button>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="dietSubmitBtnText">Save Diet Plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== 4. QUICK FOOD PICKER POPUP FOR MEAL BUILDER ==================== -->
    <div id="mealFoodPickerModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl relative my-auto max-h-[88vh] flex flex-col text-slate-900 dark:text-slate-200">
            <div class="p-4 bg-slate-50 dark:bg-slate-950 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2.5">
                    <span class="text-lg">🍲</span>
                    <div>
                        <h4 class="text-sm font-black text-slate-900 dark:text-white">Select Indian Food for Meal</h4>
                        <p class="text-[11px] text-slate-500 dark:text-slate-400" id="pickerTargetMealName">Adding items to meal</p>
                    </div>
                </div>
                <button type="button" onclick="closeMealFoodPicker()" class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-400 hover:text-white flex items-center justify-center text-xs font-bold">✕</button>
            </div>

            <div class="p-3 bg-slate-50/50 dark:bg-slate-950/50 border-b border-slate-200 dark:border-slate-800 shrink-0 space-y-2">
                <input type="text" id="pickerSearchInput" oninput="filterMealPickerList()" 
                       placeholder="Type food name e.g. eggs, chicken, paneer, soya, oats, rice, roti, whey..."
                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-amber-500">
                <div class="flex items-center gap-1.5 overflow-x-auto text-[11px] pb-1">
                    <button type="button" onclick="setPickerCategory('all')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold bg-amber-500 text-slate-950 shrink-0" data-cat="all">All ({{ count($indianFoods ?? []) }})</button>
                    <button type="button" onclick="setPickerCategory('non_veg_protein')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="non_veg_protein">🍗 Non-Veg</button>
                    <button type="button" onclick="setPickerCategory('veg_protein')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="veg_protein">🧀 Veg Protein</button>
                    <button type="button" onclick="setPickerCategory('carbs')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="carbs">🍚 Carbs</button>
                    <button type="button" onclick="setPickerCategory('fats')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="fats">🥜 Fats</button>
                    <button type="button" onclick="setPickerCategory('fruits')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="fruits">🍌 Fruits</button>
                    <button type="button" onclick="setPickerCategory('salads')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="salads">🥗 Fiber &amp; Sabzi</button>
                    <button type="button" onclick="setPickerCategory('gym_meals')" class="picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800 shrink-0" data-cat="gym_meals">🍱 Meals &amp; Shakes</button>
                </div>
            </div>

            <div class="p-3 sm:p-4 overflow-y-auto flex-1 space-y-2" id="pickerFoodListContainer">
                <!-- Dynamically populated from JS -->
            </div>

            <!-- Sticky Bottom Toolbar -->
            <div class="p-3.5 bg-slate-100 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between shrink-0">
                <div class="text-xs font-bold text-slate-700 dark:text-slate-300" id="pickerActiveMealSummary">
                    <span>Click <strong>+ Add</strong> on any foods above</span>
                </div>
                <button type="button" onclick="closeMealFoodPicker()" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-black shadow-md transition-all cursor-pointer">
                    Done &rarr;
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Quick Copy -->
    <div id="copyToast" class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-xs font-bold shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Action completed successfully!</span>
    </div>

    <!-- ==================== AI DIET GENERATOR MODAL ==================== -->
    <div id="aiDietModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl my-auto overflow-hidden max-h-[90vh] flex flex-col text-slate-900 dark:text-slate-200">
            @php
                $globalGeminiKey = \App\Models\Setting::getGlobal('gemini_api_key') ?: config('services.gemini.api_key');
                $globalGeminiModel = \App\Models\Setting::getGlobal('gemini_model') ?: config('services.gemini.model', 'gemini-1.5-flash');
                $hasGeminiConfigured = !empty($globalGeminiKey);
            @endphp
            <div class="px-4 sm:px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/70 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-pink-600 text-white flex items-center justify-center text-lg shadow-lg shadow-purple-600/30 shrink-0">
                        ✨
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">AI Personalized Diet Planner</h3>
                            @if($hasGeminiConfigured)
                                <span class="px-2.5 py-0.5 rounded-full bg-purple-500/10 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/20 dark:border-purple-500/30 text-[10px] font-mono font-bold flex items-center gap-1.5">
                                    <span class="w-1.5 h-1.5 rounded-full bg-purple-500 dark:bg-purple-400 animate-pulse"></span>
                                    <span>Powered by Google Gemini AI ({{ $globalGeminiModel }})</span>
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-300 dark:border-slate-700 text-[10px] font-mono font-bold flex items-center gap-1">
                                    <span>⚡ Smart Multi-Factor Diet Engine</span>
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">Generates precision calories, macros, pre/post workout timing, and practical Indian meals based on age, goals &amp; health factors.</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiDietModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-sm font-bold transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-6">
                <!-- FORM VIEW -->
                <div id="aiDietFormView" class="space-y-6">
                    <form id="aiDietForm" onsubmit="submitAiDietGeneration(event)" class="space-y-5">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">👤 Select Member (Optional):</span>
                            <div class="w-full sm:w-72">
                                <select id="aiMemberSelect" onchange="handleAiMemberSelect()" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                    <option value="">-- Manual Entry / Custom Member --</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">1. Member Profile &amp; Metrics</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Name *</label>
                                    <input type="text" id="aiInputName" required placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Age *</label>
                                    <input type="number" id="aiInputAge" required value="26" min="12" max="90" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Gender *</label>
                                    <select id="aiInputGender" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Height (cm) *</label>
                                    <input type="number" id="aiInputHeight" required value="172" min="100" max="250" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Weight (kg) *</label>
                                    <input type="number" id="aiInputWeight" required value="72" min="30" max="250" step="0.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                            <h4 class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">2. Fitness Goals &amp; Training</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Fitness Goal *</label>
                                    <select id="aiInputGoal" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="muscle_gain">Muscle Gain &amp; Hypertrophy</option>
                                        <option value="fat_loss">Fat Loss &amp; Leaning</option>
                                        <option value="weight_loss">Weight Loss</option>
                                        <option value="weight_gain">Weight Gain (Clean Bulk)</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Activity Level *</label>
                                    <select id="aiInputActivity" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="moderately_active">Moderately Active (Gym 3-5 days)</option>
                                        <option value="very_active">Very Active (Gym 6-7 days)</option>
                                        <option value="lightly_active">Lightly Active (Gym 1-3 days)</option>
                                        <option value="sedentary">Sedentary (Desk job / minimal)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Workout Timing *</label>
                                    <select id="aiInputWorkoutTime" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="morning">Morning (08:00 AM - 11:00 AM)</option>
                                        <option value="evening">Evening (05:00 PM - 08:00 PM)</option>
                                        <option value="early_morning">Early Morning (06:00 AM - 08:00 AM)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Diet Preference *</label>
                                    <select id="aiInputDietPref" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="vegetarian">Vegetarian (Lacto / High-Protein)</option>
                                        <option value="non_vegetarian">Non-Vegetarian (Eggs, Chicken, Fish)</option>
                                        <option value="eggetarian">Eggetarian (Eggs + Veg Dairy)</option>
                                        <option value="vegan">Vegan (100% Plant-Based)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-end gap-3">
                            <button type="button" onclick="closeAiDietModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-xs font-black shadow-lg shadow-purple-600/30 flex items-center gap-2 transition-all cursor-pointer">
                                <span>✨ Generate AI Diet Plan</span>
                                <span>&rarr;</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- LOADING VIEW -->
                <div id="aiDietLoadingView" class="hidden py-16 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="relative w-16 h-16">
                        <div class="absolute inset-0 rounded-full bg-gradient-to-tr from-purple-600 via-indigo-600 to-pink-600 animate-spin opacity-75 blur-sm"></div>
                        <div class="relative w-16 h-16 rounded-full bg-white dark:bg-slate-900 border border-purple-500/40 flex items-center justify-center text-2xl">
                            ✨
                        </div>
                    </div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-extrabold text-slate-900 dark:text-white">Synthesizing Personalized Indian Diet Plan...</h4>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Calculating BMR, optimizing macronutrient ratios & structuring timed Indian meals.</p>
                    </div>
                </div>

                <!-- AI RESULTS VIEW -->
                <div id="aiDietResultView" class="hidden space-y-5">
                    <div class="p-5 rounded-3xl bg-gradient-to-r from-purple-50 via-indigo-50 to-white dark:from-purple-950/40 dark:via-indigo-950/40 dark:to-slate-900 border border-purple-200 dark:border-purple-500/30 shadow-xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="aiResBadge" class="px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-500/30 text-[10px] font-black uppercase tracking-wider">AI Generated</span>
                                <h3 id="aiResPlanTitle" class="text-base font-black text-slate-900 dark:text-white tracking-tight"></h3>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-slate-500 dark:text-slate-400 mt-1">
                                <span id="aiResBmr" class="font-mono"></span>
                                <span>•</span>
                                <span id="aiResTdee" class="font-mono"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" id="aiBtnCopyText" onclick="copyAiDietWhatsAppText()" class="px-3.5 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-slate-700/80 flex items-center gap-1.5 transition-all cursor-pointer shadow-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span>Copy WhatsApp Text</span>
                            </button>
                            <button type="button" id="aiBtnSavePlan" onclick="saveCurrentAiDietPlan()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/25 flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Save to Member Plans</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Calories</span>
                            <span id="aiResCalories" class="text-sm font-black text-amber-600 dark:text-amber-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Protein</span>
                            <span id="aiResProtein" class="text-sm font-black text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Carbs</span>
                            <span id="aiResCarbs" class="text-sm font-black text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Fats</span>
                            <span id="aiResFat" class="text-sm font-black text-rose-600 dark:text-rose-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Fiber</span>
                            <span id="aiResFiber" class="text-sm font-black text-teal-600 dark:text-teal-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-center">
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Hydration</span>
                            <span id="aiResWater" class="text-sm font-black text-cyan-600 dark:text-cyan-400 font-mono mt-0.5 block"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">🍽️ Scheduled Indian Meals</h4>
                        <div id="aiResMealsList" class="space-y-3"></div>
                    </div>

                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <button type="button" onclick="document.getElementById('aiDietResultView').classList.add('hidden'); document.getElementById('aiDietFormView').classList.remove('hidden');" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer">
                            &larr; Adjust Parameters
                        </button>
                        <button type="button" onclick="closeAiDietModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold transition-colors cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== PAGE SCRIPTS & INDIAN FOOD LOGIC ==================== -->
    <script>
        // Indian Gym Food Library Data injected from PHP
        const indianFoodsData = {!! json_encode($indianFoods ?? []) !!};

        let currentWaText = '';
        let mealCounter = 0;
        let activeMealRowIdForPicker = null;

        function showToast(message) {
            const toast = document.getElementById('copyToast');
            if (message) toast.querySelector('span').innerText = message;
            toast.classList.remove('translate-y-20', 'opacity-0');
            setTimeout(() => {
                toast.classList.add('translate-y-20', 'opacity-0');
            }, 2500);
        }

        function formatTime(timeStr) {
            if (!timeStr) return '';
            const parts = timeStr.split(':');
            let h = parseInt(parts[0], 10);
            const m = parts[1] || '00';
            const ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12;
            h = h ? h : 12;
            return (h < 10 ? '0' + h : h) + ':' + m + ' ' + ampm;
        }

        function buildWhatsAppMessage(plan, recipientName, gymName) {
            let text = "🥗 *DIET & NUTRITION PLAN* 🥗\n";
            text += "━━━━━━━━━━━━━━━━━━━━\n";
            text += "🏋️ *Gym:* " + (gymName || 'Gym Console') + "\n";
            if (recipientName && recipientName !== 'Template Plan') {
                text += "👤 *Member:* " + recipientName + "\n";
            }
            text += "📋 *Plan:* " + plan.title + "\n";
            if (plan.daily_calories) {
                text += "🔥 *Daily Target:* " + plan.daily_calories + " kcal\n";
            }
            if (plan.protein_grams || plan.carbs_grams || plan.fat_grams) {
                text += "📊 *Macros:* Protein: " + (plan.protein_grams || 0) + "g | Carbs: " + (plan.carbs_grams || 0) + "g | Fats: " + (plan.fat_grams || 0) + "g\n";
            }
            if (plan.start_date) {
                text += "📅 *Duration:* " + plan.start_date + (plan.end_date ? " to " + plan.end_date : "") + "\n";
            }
            text += "━━━━━━━━━━━━━━━━━━━━\n\n";

            if (plan.meals && plan.meals.length > 0) {
                text += "🍽️ *DAILY MEAL SCHEDULE:*\n\n";
                const icons = {
                    'breakfast': '🍳',
                    'morning_snack': '🍎',
                    'lunch': '🥗',
                    'evening_snack': '🥤',
                    'dinner': '🍲',
                    'post_workout': '🥛'
                };
                plan.meals.forEach((m, idx) => {
                    const icon = icons[m.meal_type] || '🍽️';
                    const typeName = (m.meal_type || 'meal').replace('_', ' ').toUpperCase();
                    const timeStr = m.recommended_time ? " (" + formatTime(m.recommended_time) + ")" : "";
                    const calStr = m.calories ? " [" + m.calories + " kcal]" : "";

                    text += icon + " *" + (idx + 1) + ". " + typeName + "*" + timeStr + "\n";
                    text += "• *Meal:* " + m.meal_name + calStr + "\n";
                    if (m.items_description) {
                        text += "• *Menu:* " + m.items_description.trim() + "\n";
                    }
                    text += "\n";
                });
            }

            if (plan.guidelines) {
                text += "━━━━━━━━━━━━━━━━━━━━\n";
                text += "💧 *Coach Guidelines:*\n";
                text += plan.guidelines.trim() + "\n";
                text += "━━━━━━━━━━━━━━━━━━━━\n\n";
            }

            text += "💪 *Stay consistent & achieve your goals!*\n";
            text += "— *" + (gymName || 'Gym Console') + " Nutrition Team*";
            return text;
        }

        // WhatsApp Modal
        function openWhatsAppModal(plan, recipientName, recipientPhone, gymName) {
            currentWaText = buildWhatsAppMessage(plan, recipientName, gymName);
            document.getElementById('waRecipientPhone').value = recipientPhone || '';
            document.getElementById('waRecipientName').value = recipientName || 'Template Plan';
            document.getElementById('waMessagePreview').textContent = currentWaText;
            document.getElementById('whatsappModal').classList.remove('hidden');
        }

        function closeWhatsAppModal() {
            document.getElementById('whatsappModal').classList.add('hidden');
        }

        function copyModalWaText() {
            if (!currentWaText) return;
            navigator.clipboard.writeText(currentWaText).then(() => {
                showToast('WhatsApp message copied to clipboard!');
                const btnText = document.getElementById('copyModalBtnText');
                if (btnText) {
                    btnText.innerText = 'Copied!';
                    setTimeout(() => btnText.innerText = 'Copy Message', 2000);
                }
            });
        }

        function quickCopyPlan(plan, recipientName, gymName) {
            const text = buildWhatsAppMessage(plan, recipientName, gymName);
            navigator.clipboard.writeText(text).then(() => {
                showToast('WhatsApp message copied to clipboard!');
            });
        }

        function sendWhatsAppNow() {
            let rawPhone = document.getElementById('waRecipientPhone').value.trim();
            let cleanPhone = rawPhone.replace(/[^0-9]/g, '');

            if (cleanPhone.length === 10) {
                cleanPhone = '91' + cleanPhone; // Auto-prepend Indian country code
            }

            const encodedMessage = encodeURIComponent(currentWaText);
            let waUrl = '';

            if (cleanPhone.length >= 10) {
                waUrl = 'https://api.whatsapp.com/send?phone=' + cleanPhone + '&text=' + encodedMessage;
            } else {
                waUrl = 'https://api.whatsapp.com/send?text=' + encodedMessage;
            }

            window.open(waUrl, '_blank');
        }

        // Client-side Filtering
        function filterPlans(type) {
            const activeClass = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm shrink-0';
            const inactiveClass = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0';

            document.getElementById('btnFilterAll').className = type === 'all' ? activeClass : inactiveClass;
            document.getElementById('btnFilterMember').className = type === 'member' ? activeClass : inactiveClass;
            document.getElementById('btnFilterTemplate').className = type === 'template' ? activeClass : inactiveClass;

            const cards = document.querySelectorAll('.diet-plan-card');
            cards.forEach(card => {
                const planType = card.getAttribute('data-plan-type');
                if (type === 'all' || planType === type) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function searchDietPlans() {
            const query = document.getElementById('dietSearchInput').value.toLowerCase().trim();
            const cards = document.querySelectorAll('.diet-plan-card');
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

        // Create / Edit Modal Mode Toggle
        function setPlanTypeMode(isTemplate) {
            const templateVal = isTemplate ? '1' : '0';
            document.getElementById('dietInputIsTemplate').value = templateVal;

            const btnMember = document.getElementById('btnModeMember');
            const btnTemplate = document.getElementById('btnModeTemplate');
            const memberGroup = document.getElementById('memberSelectGroup');
            const templateNote = document.getElementById('templateNoteGroup');
            const memberSelect = document.getElementById('dietInputMemberId');

            if (isTemplate) {
                btnTemplate.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer';
                btnMember.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer';
                memberGroup.classList.add('hidden');
                templateNote.classList.remove('hidden');
                memberSelect.removeAttribute('required');
                memberSelect.value = '';
            } else {
                btnMember.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm flex items-center gap-1.5 cursor-pointer';
                btnTemplate.className = 'px-3.5 py-1.5 rounded-lg text-xs font-bold transition-all text-slate-600 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center gap-1.5 cursor-pointer';
                memberGroup.classList.remove('hidden');
                templateNote.classList.add('hidden');
                memberSelect.setAttribute('required', 'required');
            }
        }

        function handleModalMemberChange() {
            const memberSelect = document.getElementById('dietInputMemberId');
            const titleInput = document.getElementById('dietInputTitle');
            const selectedOpt = memberSelect.options[memberSelect.selectedIndex];
            if (selectedOpt && selectedOpt.value) {
                const memberName = selectedOpt.getAttribute('data-name') || selectedOpt.text.split('(')[0].trim();
                if (!titleInput.value || titleInput.value.startsWith('Diet Plan')) {
                    titleInput.value = `Diet Plan - ${memberName} (2,000 kcal)`;
                }
            }
        }

        function openDietModal(memberId = null) {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "{{ route('app.diets.store') }}";
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Create Diet Plan';
            document.getElementById('dietSubmitBtnText').innerText = 'Save Diet Plan';
            document.getElementById('mealsContainer').innerHTML = '';
            mealCounter = 0;
            for (let k in mealRowFoods) delete mealRowFoods[k];

            // Default to Member Mode
            setPlanTypeMode(0);

            if (memberId) {
                const memberSelect = document.getElementById('dietInputMemberId');
                memberSelect.value = memberId;
                handleModalMemberChange();
            }

            // Pre-load standard 5-meal Indian day by default for quick customization
            loadStandardMealsPreset();

            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        function openDietModalForSelectedMember() {
            const selectEl = document.getElementById('quickMemberSelect');
            const selectedId = selectEl.value;
            if (!selectedId) {
                selectEl.focus();
                openDietModal();
                return;
            }
            openDietModal(selectedId);
        }

        function closeDietModal() {
            document.getElementById('dietPlanModal').classList.add('hidden');
        }

        // Auto-Calculate Macros by Fitness Goal
        function autoCalculateMacros(goal) {
            const calInput = document.getElementById('dietInputCalories');
            let cals = parseInt(calInput.value) || 2000;

            let pGrams = 150, cGrams = 200, fGrams = 55;

            if (goal === 'muscle_gain') {
                // 30% Protein, 50% Carbs, 20% Fats
                pGrams = Math.round((cals * 0.30) / 4);
                cGrams = Math.round((cals * 0.50) / 4);
                fGrams = Math.round((cals * 0.20) / 9);
            } else if (goal === 'fat_loss') {
                // 40% Protein, 35% Carbs, 25% Fats (High Protein Cutting)
                pGrams = Math.round((cals * 0.40) / 4);
                cGrams = Math.round((cals * 0.35) / 4);
                fGrams = Math.round((cals * 0.25) / 9);
            } else if (goal === 'maintenance') {
                // 25% Protein, 50% Carbs, 25% Fats
                pGrams = Math.round((cals * 0.25) / 4);
                cGrams = Math.round((cals * 0.50) / 4);
                fGrams = Math.round((cals * 0.25) / 9);
            }

            document.getElementById('dietInputProtein').value = pGrams;
            document.getElementById('dietInputCarbs').value = cGrams;
            document.getElementById('dietInputFats').value = fGrams;

            showToast(`Macros calculated for ${goal.replace('_', ' ')} (${cals} kcal)!`);
        }

        const mealRowFoods = {};

        function addMealRow(meal = null, initialFoodIds = null) {
            mealCounter++;
            const container = document.getElementById('mealsContainer');
            const rowId = 'meal_row_' + mealCounter;
            mealRowFoods[rowId] = [];

            const mealType = meal ? meal.meal_type : (mealCounter === 1 ? 'breakfast' : (mealCounter === 2 ? 'morning_snack' : (mealCounter === 3 ? 'lunch' : (mealCounter === 4 ? 'evening_snack' : 'dinner'))));
            const mealName = meal ? meal.meal_name : (mealCounter === 1 ? 'Breakfast' : (mealCounter === 2 ? 'Mid-Morning Snack' : (mealCounter === 3 ? 'Lunch' : (mealCounter === 4 ? 'Pre/Post Workout' : 'Dinner'))));
            const recommendedTime = meal ? meal.recommended_time : (mealCounter === 1 ? '08:30' : (mealCounter === 2 ? '11:30' : (mealCounter === 3 ? '13:30' : (mealCounter === 4 ? '17:30' : '20:30'))));
            const calories = meal ? (meal.calories || '') : '';
            const items = meal ? (meal.items_description || '') : '';

            const html = `
                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 relative group transition-colors space-y-3" id="${rowId}">
                    <div class="flex items-center justify-between pb-1 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2">
                            <span class="w-5 h-5 rounded-md bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs flex items-center justify-center font-mono font-bold">${container.children.length + 1}</span>
                            <span class="text-xs font-bold text-slate-800 dark:text-slate-200" id="${rowId}_header_title">${mealName}</span>
                            <span id="${rowId}_macro_pill" class="text-[10px] font-mono font-bold px-2 py-0.5 rounded-md bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">0 kcal</span>
                        </div>
                        
                        <div class="flex items-center gap-2">
                            <button type="button" onclick="openMealFoodPicker('${rowId}')" 
                                    class="px-2.5 py-1.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-300 text-[11px] font-bold border border-amber-500/30 flex items-center gap-1.5 transition-colors cursor-pointer">
                                <span>🍲 + Pick Indian Food</span>
                            </button>
                            <button type="button" onclick="removeMealRow('${rowId}')" 
                                    class="text-xs text-rose-500 hover:text-rose-600 font-semibold p-1 cursor-pointer">✕ Remove</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Meal Type</label>
                            <select name="meals[${mealCounter}][meal_type]" onchange="onMealTypeChange('${rowId}', this.value)" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="breakfast" ${mealType === 'breakfast' ? 'selected' : ''}>🍳 Breakfast</option>
                                <option value="morning_snack" ${mealType === 'morning_snack' ? 'selected' : ''}>🍎 Morning Snack</option>
                                <option value="lunch" ${mealType === 'lunch' ? 'selected' : ''}>🥗 Lunch</option>
                                <option value="evening_snack" ${mealType === 'evening_snack' ? 'selected' : ''}>🥤 Evening Snack</option>
                                <option value="dinner" ${mealType === 'dinner' ? 'selected' : ''}>🍲 Dinner</option>
                                <option value="post_workout" ${mealType === 'post_workout' ? 'selected' : ''}>🥛 Post Workout</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Meal Title *</label>
                            <input type="text" name="meals[${mealCounter}][meal_name]" id="${rowId}_title" value="${mealName}" required oninput="document.getElementById('${rowId}_header_title').innerText = this.value || 'Meal'" placeholder="e.g. Power Breakfast" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Recommended Time</label>
                            <input type="time" name="meals[${mealCounter}][recommended_time]" value="${recommendedTime}" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Calories (kcal)</label>
                            <input type="number" name="meals[${mealCounter}][calories]" id="${rowId}_cals" value="${calories}" placeholder="0" min="0" oninput="updatePlanTotalCalories()" class="w-full px-2.5 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white font-mono font-bold focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Selected Food Items Interactive Container -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">Selected Food Items &amp; Portions</label>
                            <span class="text-[10px] text-slate-400 font-medium">Automatic macro calculation</span>
                        </div>

                        <div id="${rowId}_foods_container" class="space-y-1.5">
                            <!-- Populated by renderMealFoods -->
                        </div>

                        <!-- Optional custom notes input -->
                        <div class="pt-1">
                            <input type="text" id="${rowId}_custom_notes" oninput="syncMealDescription('${rowId}')" placeholder="+ Custom notes or instructions (e.g. cooked in 1 tsp olive oil, with green chutney)..." 
                                   class="w-full px-3 py-1.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500">
                        </div>

                        <!-- Synced items description for form submit & WhatsApp -->
                        <textarea name="meals[${mealCounter}][items_description]" id="${rowId}_items" class="hidden">${items}</textarea>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);

            if (initialFoodIds && initialFoodIds.length > 0) {
                initialFoodIds.forEach(fId => {
                    const f = indianFoodsData.find(item => item.id === fId);
                    if (f) {
                        mealRowFoods[rowId].push({
                            foodId: f.id,
                            name: f.name,
                            serving: f.serving,
                            qty: 1,
                            calories: f.calories,
                            protein: f.protein,
                            carbs: f.carbs,
                            fat: f.fat,
                            diet_type: f.diet_type
                        });
                    }
                });
                renderMealFoods(rowId);
            } else if (items) {
                document.getElementById(rowId + '_custom_notes').value = items;
                renderMealFoods(rowId);
            } else {
                renderMealFoods(rowId);
            }
        }

        function removeMealRow(rowId) {
            const el = document.getElementById(rowId);
            if (el) el.remove();
            delete mealRowFoods[rowId];
            updatePlanTotalCalories();
        }

        function onMealTypeChange(rowId, type) {
            const typeNames = {
                'breakfast': '🍳 Breakfast',
                'morning_snack': '🍎 Mid-Morning Snack',
                'lunch': '🥗 Lunch',
                'evening_snack': '🥤 Evening Snack',
                'dinner': '🍲 Dinner',
                'post_workout': '🥛 Post Workout'
            };
            const titleInput = document.getElementById(rowId + '_title');
            if (titleInput && (!titleInput.value || Object.values(typeNames).some(t => t.includes(titleInput.value)))) {
                titleInput.value = (typeNames[type] || 'Meal').replace(/^[^\w\s]+/, '').trim();
                document.getElementById(rowId + '_header_title').innerText = titleInput.value;
            }
        }

        function renderMealFoods(rowId) {
            const container = document.getElementById(rowId + '_foods_container');
            if (!container) return;

            const items = mealRowFoods[rowId] || [];

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="p-3 rounded-xl border border-dashed border-slate-300 dark:border-slate-800 bg-white/50 dark:bg-slate-900/40 text-center flex items-center justify-center gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>No food items selected yet.</span>
                        <button type="button" onclick="openMealFoodPicker('${rowId}')" class="text-amber-600 dark:text-amber-400 font-bold hover:underline cursor-pointer">
                            + Pick from 140+ Indian Foods
                        </button>
                    </div>
                `;
                syncMealDescription(rowId);
                return;
            }

            let html = '';
            items.forEach(item => {
                const totCals = Math.round(item.qty * item.calories);
                const totP = Math.round(item.qty * item.protein * 10) / 10;
                const totC = Math.round(item.qty * item.carbs * 10) / 10;
                const totF = Math.round(item.qty * item.fat * 10) / 10;

                const dotColor = item.diet_type === 'non_veg' ? 'bg-rose-600' : (item.diet_type === 'eggetarian' ? 'bg-amber-500' : 'bg-emerald-600');

                html += `
                    <div class="flex items-center justify-between p-2 sm:p-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 gap-2.5 shadow-xs">
                        <div class="flex items-center gap-2 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full ${dotColor} shrink-0"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate">${item.name}</span>
                                    <span class="text-[11px] text-slate-400">(${item.serving})</span>
                                </div>
                                <div class="flex items-center gap-2 text-[10px] font-mono text-slate-500 dark:text-slate-400">
                                    <span class="text-amber-600 dark:text-amber-400 font-bold">${totCals} kcal</span>
                                    <span>P: <strong class="text-rose-500 dark:text-rose-400">${totP}g</strong></span>
                                    <span>C: <strong class="text-amber-500 dark:text-amber-400">${totC}g</strong></span>
                                    <span>F: <strong class="text-cyan-500 dark:text-cyan-400">${totF}g</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-800 text-xs font-bold">
                                <button type="button" onclick="changeMealFoodQty('${rowId}', '${item.foodId}', -1)" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">-</button>
                                <span class="px-2 py-1 font-mono text-slate-900 dark:text-white">${item.qty}x</span>
                                <button type="button" onclick="changeMealFoodQty('${rowId}', '${item.foodId}', 1)" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">+</button>
                            </div>
                            <button type="button" onclick="removeMealFood('${rowId}', '${item.foodId}')" class="w-6 h-6 rounded-lg bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white flex items-center justify-center text-xs font-bold transition-colors cursor-pointer" title="Remove item">✕</button>
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
            syncMealDescription(rowId);
        }

        function changeMealFoodQty(rowId, foodId, delta) {
            const list = mealRowFoods[rowId] || [];
            const item = list.find(it => it.foodId === foodId);
            if (!item) return;
            item.qty += delta;
            if (item.qty <= 0) {
                mealRowFoods[rowId] = list.filter(it => it.foodId !== foodId);
            }
            renderMealFoods(rowId);
        }

        function removeMealFood(rowId, foodId) {
            const list = mealRowFoods[rowId] || [];
            mealRowFoods[rowId] = list.filter(it => it.foodId !== foodId);
            renderMealFoods(rowId);
        }

        function syncMealDescription(rowId) {
            const list = mealRowFoods[rowId] || [];
            const customNotes = document.getElementById(rowId + '_custom_notes')?.value?.trim() || '';

            let formattedLines = [];
            let totalCals = 0;
            let totalP = 0;
            let totalC = 0;
            let totalF = 0;

            list.forEach(item => {
                const cals = Math.round(item.qty * item.calories);
                const p = Math.round(item.qty * item.protein * 10) / 10;
                const c = Math.round(item.qty * item.carbs * 10) / 10;
                const f = Math.round(item.qty * item.fat * 10) / 10;

                totalCals += cals;
                totalP += p;
                totalC += c;
                totalF += f;

                const qtyPrefix = item.qty > 1 ? `${item.qty}x ` : '';
                formattedLines.push(`• ${qtyPrefix}${item.name} (${item.serving}) - ${cals} kcal, ${p}g P`);
            });

            if (customNotes) {
                formattedLines.push(`• Note: ${customNotes}`);
            }

            const descriptionText = formattedLines.join('\n');
            const itemsInput = document.getElementById(rowId + '_items');
            if (itemsInput) {
                itemsInput.value = descriptionText;
            }

            const calsInput = document.getElementById(rowId + '_cals');
            if (calsInput && list.length > 0) {
                calsInput.value = totalCals;
            }

            const pill = document.getElementById(rowId + '_macro_pill');
            if (pill) {
                const displayCals = list.length > 0 ? totalCals : (parseInt(calsInput?.value) || 0);
                pill.innerHTML = `${displayCals} kcal • ${Math.round(totalP)}g P`;
            }

            updatePlanTotalCalories();
        }

        function updatePlanTotalCalories() {
            let sum = 0;
            document.querySelectorAll('input[id$="_cals"]').forEach(inp => {
                sum += parseInt(inp.value) || 0;
            });
        }

        function loadStandardMealsPreset() {
            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;

            const presetMeals = [
                { 
                    meal_type: 'breakfast', 
                    recommended_time: '08:30', 
                    meal_name: 'High Protein Oats & Eggs', 
                    food_ids: ['food_egg_white_4', 'food_rolled_oats_dry', 'food_almonds'] 
                },
                { 
                    meal_type: 'morning_snack', 
                    recommended_time: '11:30', 
                    meal_name: 'Mid-Morning Fruit & Antioxidants', 
                    food_ids: ['food_apple_medium', 'food_green_tea'] 
                },
                { 
                    meal_type: 'lunch', 
                    recommended_time: '13:30', 
                    meal_name: 'Lean Protein & Brown Rice Bowl', 
                    food_ids: ['food_chicken_breast_boiled', 'food_brown_rice_boiled', 'food_green_salad_bowl'] 
                },
                { 
                    meal_type: 'evening_snack', 
                    recommended_time: '17:30', 
                    meal_name: 'Pre-Workout Fuel & Energy', 
                    food_ids: ['food_banana_medium', 'food_peanut_butter_1tbsp', 'food_black_coffee'] 
                },
                { 
                    meal_type: 'dinner', 
                    recommended_time: '20:30', 
                    meal_name: 'Clean Recovery Dinner', 
                    food_ids: ['food_low_fat_paneer', 'food_roti_wheat', 'food_moong_dal_cooked', 'food_cucumber_kheera'] 
                },
            ];

            presetMeals.forEach(m => addMealRow(m, m.food_ids));
            showToast('5-Meal Indian Gym Day loaded into builder!');
        }

        function editDietPlan(plan) {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "/app/diets/" + plan.id;
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Edit Diet Plan: ' + plan.title;
            document.getElementById('dietSubmitBtnText').innerText = 'Update Diet Plan';

            document.getElementById('dietInputTitle').value = plan.title || '';
            if (plan.is_template) {
                setPlanTypeMode(1);
            } else {
                setPlanTypeMode(0);
                document.getElementById('dietInputMemberId').value = plan.member_id || '';
            }

            document.getElementById('dietInputTrainerId').value = plan.trainer_id || '';
            document.getElementById('dietInputCalories').value = plan.daily_calories || '';
            document.getElementById('dietInputProtein').value = plan.protein_grams || '';
            document.getElementById('dietInputCarbs').value = plan.carbs_grams || '';
            document.getElementById('dietInputFats').value = plan.fat_grams || '';
            document.getElementById('dietInputStartDate').value = plan.start_date || '';
            document.getElementById('dietInputEndDate').value = plan.end_date || '';
            document.getElementById('dietInputGuidelines').value = plan.guidelines || '';

            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;
            for (let k in mealRowFoods) delete mealRowFoods[k];

            if (plan.meals && plan.meals.length > 0) {
                plan.meals.forEach(m => addMealRow(m));
            } else {
                addMealRow();
            }

            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        // ==================== FOOD PICKER FOR MEAL BUILDER ====================
        let pickerCurrentCategory = 'all';

        function openMealFoodPicker(rowId) {
            activeMealRowIdForPicker = rowId;
            const rowEl = document.getElementById(rowId);
            const mealName = rowEl.querySelector('input[type="text"]')?.value || 'Current Meal';
            document.getElementById('pickerTargetMealName').innerText = 'Adding items to: ' + mealName;
            document.getElementById('pickerSearchInput').value = '';
            pickerCurrentCategory = 'all';
            renderMealPickerList();
            document.getElementById('mealFoodPickerModal').classList.remove('hidden');
        }

        function closeMealFoodPicker() {
            document.getElementById('mealFoodPickerModal').classList.add('hidden');
            activeMealRowIdForPicker = null;
        }

        function setPickerCategory(cat) {
            pickerCurrentCategory = cat;
            document.querySelectorAll('.picker-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-bold bg-amber-500 text-slate-950';
                } else {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-bold text-slate-400 hover:bg-slate-800';
                }
            });
            renderMealPickerList();
        }

        function filterMealPickerList() {
            renderMealPickerList();
        }

        function renderMealPickerList() {
            const container = document.getElementById('pickerFoodListContainer');
            const q = (document.getElementById('pickerSearchInput').value || '').toLowerCase().trim();

            let filtered = indianFoodsData.filter(item => {
                const matchesCat = pickerCurrentCategory === 'all' || item.category === pickerCurrentCategory;
                const matchesQuery = !q || item.name.toLowerCase().includes(q) || (item.hindi_name && item.hindi_name.toLowerCase().includes(q));
                return matchesCat && matchesQuery;
            });

            if (filtered.length === 0) {
                container.innerHTML = `<div class="p-6 text-center text-xs text-slate-500">No matching food found for "${q}".</div>`;
                return;
            }

            const currentMealFoods = (activeMealRowIdForPicker && mealRowFoods[activeMealRowIdForPicker]) ? mealRowFoods[activeMealRowIdForPicker] : [];

            let html = '';
            filtered.forEach(food => {
                const dotColor = food.diet_type === 'non_veg' ? 'bg-rose-600' : (food.diet_type === 'eggetarian' ? 'bg-amber-600' : 'bg-emerald-600');
                const borderDot = food.diet_type === 'non_veg' ? 'border-rose-600' : (food.diet_type === 'eggetarian' ? 'border-amber-600' : 'border-emerald-600');

                const existing = currentMealFoods.find(it => it.foodId === food.id);
                const isAdded = !!existing;

                html += `
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border ${isAdded ? 'border-emerald-500/50 bg-emerald-500/5 dark:bg-emerald-500/10' : 'border-slate-200 dark:border-slate-800'} flex items-center justify-between gap-3 hover:border-amber-500/40 transition-colors">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 border-2 ${borderDot} p-0.5 flex items-center justify-center shrink-0">
                                    <span class="w-1 h-1 rounded-full ${dotColor}"></span>
                                </span>
                                <span class="text-xs font-bold text-slate-900 dark:text-white truncate">${food.name}</span>
                                <span class="text-[10px] text-slate-400 font-mono">(${food.serving})</span>
                            </div>
                            <div class="flex items-center gap-3 text-[10px] font-mono mt-1 text-slate-500 dark:text-slate-400 pl-5">
                                <span class="text-amber-500 font-bold">${food.calories} kcal</span>
                                <span>P: <strong class="text-rose-400">${food.protein}g</strong></span>
                                <span>C: <strong class="text-amber-400">${food.carbs}g</strong></span>
                                <span>F: <strong class="text-cyan-400">${food.fat}g</strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            ${isAdded ? `
                                <div class="flex items-center gap-1">
                                    <span class="px-2.5 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-bold border border-emerald-500/30">
                                        ✓ Added (${existing.qty}x)
                                    </span>
                                    <button type="button" onclick="addFoodItemToActiveMeal('${food.id}')" class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black flex items-center justify-center cursor-pointer shadow-sm" title="Add another serving">+</button>
                                </div>
                            ` : `
                                <button type="button" 
                                        onclick="addFoodItemToActiveMeal('${food.id}')"
                                        class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold transition-all cursor-pointer shadow-sm">
                                    + Add
                                </button>
                            `}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;

            // Update bottom summary bar in the picker
            const summaryEl = document.getElementById('pickerActiveMealSummary');
            if (summaryEl) {
                const totalItems = currentMealFoods.reduce((s, it) => s + it.qty, 0);
                const totalCals = currentMealFoods.reduce((s, it) => s + (it.qty * it.calories), 0);
                const totalP = Math.round(currentMealFoods.reduce((s, it) => s + (it.qty * it.protein), 0));
                if (totalItems > 0) {
                    summaryEl.innerHTML = `🥘 <strong>${totalItems} items</strong> in meal • <strong>${totalCals} kcal</strong> • <strong>${totalP}g Protein</strong>`;
                } else {
                    summaryEl.innerHTML = `Click <strong>+ Add</strong> on any foods above`;
                }
            }
        }

        function addFoodItemToActiveMeal(foodId) {
            if (!activeMealRowIdForPicker) return;
            const food = indianFoodsData.find(f => f.id === foodId);
            if (!food) return;

            if (!mealRowFoods[activeMealRowIdForPicker]) {
                mealRowFoods[activeMealRowIdForPicker] = [];
            }

            const existing = mealRowFoods[activeMealRowIdForPicker].find(it => it.foodId === foodId);
            if (existing) {
                existing.qty++;
            } else {
                mealRowFoods[activeMealRowIdForPicker].push({
                    foodId: food.id,
                    name: food.name,
                    serving: food.serving,
                    qty: 1,
                    calories: food.calories,
                    protein: food.protein,
                    carbs: food.carbs,
                    fat: food.fat,
                    diet_type: food.diet_type
                });
            }

            renderMealFoods(activeMealRowIdForPicker);
            renderMealPickerList();
        }

        // ==================== FOOD LIBRARY MODAL LOGIC ====================
        let foodLibCurrentCategory = 'all';

        function openFoodLibraryModal() {
            document.getElementById('foodLibrarySearch').value = '';
            setFoodCategoryFilter('all');
            document.getElementById('foodLibraryModal').classList.remove('hidden');
        }

        function closeFoodLibraryModal() {
            document.getElementById('foodLibraryModal').classList.add('hidden');
        }

        function setFoodCategoryFilter(cat) {
            foodLibCurrentCategory = cat;
            document.querySelectorAll('.food-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold bg-amber-500 text-slate-950 shadow-sm';
                } else {
                    btn.className = 'food-cat-btn px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800';
                }
            });
            filterFoodLibrary();
        }

        function filterFoodLibrary() {
            const q = (document.getElementById('foodLibrarySearch').value || '').toLowerCase().trim();
            const cards = document.querySelectorAll('.food-item-card');

            cards.forEach(card => {
                const cat = card.getAttribute('data-cat');
                const name = card.getAttribute('data-name') || '';
                const hindi = card.getAttribute('data-hindi') || '';

                const matchesCat = foodLibCurrentCategory === 'all' || cat === foodLibCurrentCategory;
                const matchesQuery = !q || name.includes(q) || hindi.includes(q);

                if (matchesCat && matchesQuery) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        function copyFoodDetails(food) {
            const text = `🥗 *${food.name}* (${food.hindi_name || ''})\n• Serving: ${food.serving}\n• Calories: ${food.calories} kcal\n• Protein: ${food.protein}g | Carbs: ${food.carbs}g | Fat: ${food.fat}g | Fiber: ${food.fiber}g\n• Purpose: ${food.gym_purpose}\n• Info: ${food.description}`;
            navigator.clipboard.writeText(text).then(() => {
                showToast(`Copied ${food.name} macro details!`);
            });
        }

        // ==================== AI DIET GENERATOR SCRIPTS ====================
        const membersDirectory = {!! json_encode($members->map(fn($m) => [
            'id' => $m->id,
            'name' => $m->full_name,
            'gender' => $m->gender ?? 'male',
            'age' => $m->dob ? $m->dob->age : 26,
            'phone' => $m->phone ?? '',
        ])->values()) !!};

        let currentAiDietResult = null;

        function openAiDietModal() {
            document.getElementById('aiDietForm').reset();
            document.getElementById('aiDietFormView').classList.remove('hidden');
            document.getElementById('aiDietLoadingView').classList.add('hidden');
            document.getElementById('aiDietResultView').classList.add('hidden');
            document.getElementById('aiDietModal').classList.remove('hidden');
        }

        function closeAiDietModal() {
            document.getElementById('aiDietModal').classList.add('hidden');
        }

        function handleAiMemberSelect() {
            const memberId = document.getElementById('aiMemberSelect').value;
            if (!memberId) return;

            const member = membersDirectory.find(m => m.id == memberId);
            if (member) {
                document.getElementById('aiInputName').value = member.name || '';
                if (member.gender) {
                    document.getElementById('aiInputGender').value = member.gender.toLowerCase();
                }
                if (member.age) {
                    document.getElementById('aiInputAge').value = member.age;
                }
            }
        }

        async function submitAiDietGeneration(event) {
            event.preventDefault();
            
            const memberId = document.getElementById('aiMemberSelect').value || null;
            const name = document.getElementById('aiInputName').value || 'Gym Member';
            const age = parseInt(document.getElementById('aiInputAge').value) || 25;
            const gender = document.getElementById('aiInputGender').value || 'male';
            const height = parseFloat(document.getElementById('aiInputHeight').value) || 172;
            const weight = parseFloat(document.getElementById('aiInputWeight').value) || 70;
            const goal = document.getElementById('aiInputGoal').value || 'muscle_gain';
            const activityLevel = document.getElementById('aiInputActivity').value || 'moderately_active';
            const dietPreference = document.getElementById('aiInputDietPref').value || 'vegetarian';
            const workoutTime = document.getElementById('aiInputWorkoutTime').value || 'morning';

            document.getElementById('aiDietFormView').classList.add('hidden');
            document.getElementById('aiDietLoadingView').classList.remove('hidden');
            document.getElementById('aiDietResultView').classList.add('hidden');

            const payload = {
                member_id: memberId,
                name: name,
                age: age,
                gender: gender,
                height: height,
                weight: weight,
                goal: goal,
                activity_level: activityLevel,
                diet_preference: dietPreference,
                workout_time: workoutTime,
                _token: '{{ csrf_token() }}'
            };

            try {
                const response = await fetch('{{ route('app.diets.generate-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const resData = await response.json();

                if (resData.success && resData.data) {
                    currentAiDietResult = { ...resData.data, payload: payload };
                    renderAiDietResult(resData.data);
                    document.getElementById('aiDietLoadingView').classList.add('hidden');
                    document.getElementById('aiDietResultView').classList.remove('hidden');
                } else {
                    alert('Could not generate diet: ' + (resData.message || 'Unknown error occurred.'));
                    document.getElementById('aiDietLoadingView').classList.add('hidden');
                    document.getElementById('aiDietFormView').classList.remove('hidden');
                }
            } catch (err) {
                console.error(err);
                alert('An error occurred during AI plan generation. Please try again.');
                document.getElementById('aiDietLoadingView').classList.add('hidden');
                document.getElementById('aiDietFormView').classList.remove('hidden');
            }
        }

        function renderAiDietResult(data) {
            document.getElementById('aiResPlanTitle').innerText = data.plan_title || 'Personalized AI Diet Plan';
            document.getElementById('aiResCalories').innerText = data.daily_totals.calories + ' kcal';
            document.getElementById('aiResProtein').innerText = data.daily_totals.protein_grams + 'g';
            document.getElementById('aiResCarbs').innerText = data.daily_totals.carbs_grams + 'g';
            document.getElementById('aiResFat').innerText = data.daily_totals.fat_grams + 'g';
            document.getElementById('aiResFiber').innerText = data.daily_totals.fiber_grams + 'g';
            document.getElementById('aiResWater').innerText = data.daily_totals.water_liters + ' L';
            
            document.getElementById('aiResBmr').innerText = 'BMR: ' + data.bmr_calculated + ' kcal';
            document.getElementById('aiResTdee').innerText = 'TDEE: ' + data.tdee_calculated + ' kcal';

            const mealsContainer = document.getElementById('aiResMealsList');
            mealsContainer.innerHTML = '';

            data.meals.forEach((meal, idx) => {
                const mealHtml = `
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-2">
                            <div class="flex items-center gap-2">
                                <span class="w-5 h-5 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-mono text-[10px] font-bold flex items-center justify-center">${idx + 1}</span>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white">${meal.meal_name}</h4>
                                <span class="text-[10px] text-slate-500 font-mono">⏰ ${meal.recommended_time}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] font-mono font-bold">
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500">${meal.target_macros.calories} kcal</span>
                                <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-500">P: ${meal.target_macros.protein_g}g</span>
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500">C: ${meal.target_macros.carbs_g}g</span>
                                <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-500">F: ${meal.target_macros.fat_g}g</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed pl-2 border-l-2 border-indigo-500/40">
                            ${meal.items_description}
                        </div>
                    </div>
                `;
                mealsContainer.insertAdjacentHTML('beforeend', mealHtml);
            });
        }

        async function saveCurrentAiDietPlan() {
            if (!currentAiDietResult || !currentAiDietResult.payload) return;

            const btn = document.getElementById('aiBtnSavePlan');
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = `<span>Saving...</span>`;

            try {
                const payload = { ...currentAiDietResult.payload, auto_save: true };
                const response = await fetch('{{ route('app.diets.generate-ai') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });

                const resData = await response.json();

                if (resData.success) {
                    btn.innerHTML = `<span>✓ Plan Saved! Reloading...</span>`;
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } else {
                    alert('Error saving plan: ' + (resData.message || 'Please try again.'));
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                }
            } catch (err) {
                console.error(err);
                alert('Could not save plan.');
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        }

        function copyAiDietWhatsAppText() {
            if (!currentAiDietResult) return;
            const d = currentAiDietResult;
            let text = `🥗 *${d.plan_title}*\n`;
            text += `━━━━━━━━━━━━━━━━━━━━━\n`;
            text += `🔥 *Daily Calorie Target:* ${d.daily_totals.calories} kcal\n`;
            text += `💪 *Protein:* ${d.daily_totals.protein_grams}g | 🍚 *Carbs:* ${d.daily_totals.carbs_grams}g | 🥑 *Fats:* ${d.daily_totals.fat_grams}g\n`;
            text += `💧 *Hydration Goal:* ${d.daily_totals.water_liters} L / day\n\n`;
            text += `📋 *DAILY MEAL REGIME:*\n`;
            
            d.meals.forEach((m, idx) => {
                text += `\n*${idx + 1}. ${m.meal_name}* (⏰ ${m.recommended_time}) [${m.target_macros.calories} kcal]\n`;
                text += `${m.items_description}\n`;
            });

            navigator.clipboard.writeText(text).then(() => {
                showToast('AI Diet WhatsApp text copied to clipboard!');
            });
        }
    </script>
</x-app-layout>
