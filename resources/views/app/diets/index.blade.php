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

        <!-- Top Header & Main Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2.5">
                    <span>🥗 Diet & Nutrition</span>
                </h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Design customized member diet regimes, track macro splits, and dispatch clean nutrition.</p>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" onclick="openAiDietModal()" class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-pink-600 hover:from-purple-500 hover:to-pink-500 text-white text-xs font-black shadow-lg shadow-purple-600/25 flex items-center gap-2 transition-all cursor-pointer border border-purple-400/30">
                    <span class="text-sm">✨</span>
                    <span>AI Diet Generator</span>
                    <span class="px-1.5 py-0.2 rounded-full bg-white/20 text-[9px] font-extrabold uppercase tracking-wider">AI AGENT</span>
                </button>
                <button type="button" onclick="openDietModal()" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 flex items-center gap-2 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Diet Plan</span>
                </button>
            </div>
        </div>

        <!-- Filter Tabs & Search Bar -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-2.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm">
            <div class="flex items-center gap-1.5 overflow-x-auto">
                <button type="button" onclick="filterPlans('all')" id="btnFilterAll" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm">
                    All Plans ({{ $totalPlans }})
                </button>
                <button type="button" onclick="filterPlans('member')" id="btnFilterMember" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                    Member Assigned ({{ $memberPlansCount }})
                </button>
                <button type="button" onclick="filterPlans('template')" id="btnFilterTemplate" class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800">
                    Templates ({{ $templatePlansCount }})
                </button>
            </div>

            <div class="relative min-w-[260px]">
                <input type="text" id="dietSearchInput" oninput="searchDietPlans()" placeholder="Search plan or member name..." class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition-colors">
                <svg class="w-4 h-4 text-slate-400 dark:text-slate-500 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- Diet Plans Grid (Spacious 2-Column Responsive Layout) -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-4" id="dietPlansGrid">
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
                    $totalMacroGrams = max(1, $protein + $carbs + $fat);
                    $protPct = round(($protein / $totalMacroGrams) * 100);
                    $carbsPct = round(($carbs / $totalMacroGrams) * 100);
                    $fatPct = max(0, 100 - $protPct - $carbsPct);
                @endphp

                <div x-data="{ expanded: false }" 
                     class="diet-plan-card flex flex-col justify-between p-4.5 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all shadow-sm group"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($memberName) }}">
                    <div>
                        <!-- Card Header -->
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    @if($plan->is_template)
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 uppercase tracking-wider">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                            Master Template
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-[10px] font-bold bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 uppercase tracking-wider">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                            Member Assigned
                                        </span>
                                    @endif

                                    @if($plan->trainer)
                                        <span class="text-[11px] text-slate-500 dark:text-slate-400">Coach: <strong class="text-slate-800 dark:text-slate-200">{{ $plan->trainer->full_name }}</strong></span>
                                    @endif
                                </div>

                                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight leading-snug">{{ $plan->title }}</h3>

                                @if(!$plan->is_template && $plan->member)
                                    <div class="flex items-center gap-2 pt-0.5">
                                        <div class="w-5 h-5 rounded-full bg-slate-100 dark:bg-slate-800 text-indigo-600 dark:text-indigo-400 border border-slate-200 dark:border-slate-700 flex items-center justify-center font-bold text-[9px]">
                                            {{ substr($plan->member->first_name, 0, 1) }}
                                        </div>
                                        <span class="text-xs font-semibold text-slate-700 dark:text-slate-300">{{ $plan->member->full_name }}</span>
                                        @if($plan->member->phone)
                                            <span class="text-[10px] text-slate-400 dark:text-slate-500 font-mono">({{ $plan->member->phone }})</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="text-right shrink-0">
                                <div class="px-2.5 py-1 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 text-xs font-black tracking-tight inline-flex items-center gap-1">
                                    <span>🔥</span>
                                    <span>{{ $plan->daily_calories ? number_format($plan->daily_calories) : '2,000' }}</span>
                                    <span class="text-[10px] font-normal text-emerald-600/80 dark:text-emerald-400/80">kcal</span>
                                </div>
                                @if($plan->start_date)
                                    <div class="text-[10px] text-slate-400 dark:text-slate-500 mt-1 font-mono">
                                        {{ $plan->start_date->format('d M') }} {{ $plan->end_date ? '→ ' . $plan->end_date->format('d M Y') : '' }}
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Macro Target Distribution -->
                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 mb-2.5 space-y-2">
                            <div class="flex items-center justify-between text-[11px] font-bold">
                                <span class="text-slate-500 dark:text-slate-400 uppercase tracking-wider text-[10px]">Daily Macro Split</span>
                                <div class="flex items-center gap-2.5 text-xs font-mono">
                                    <span class="text-rose-600 dark:text-rose-400"><strong class="font-bold">{{ $protein }}g</strong> <span class="text-[10px] text-slate-400 dark:text-slate-500">P</span></span>
                                    <span class="text-amber-600 dark:text-amber-400"><strong class="font-bold">{{ $carbs }}g</strong> <span class="text-[10px] text-slate-400 dark:text-slate-500">C</span></span>
                                    <span class="text-cyan-600 dark:text-cyan-400"><strong class="font-bold">{{ $fat }}g</strong> <span class="text-[10px] text-slate-400 dark:text-slate-500">F</span></span>
                                </div>
                            </div>

                            <!-- Macro Percentage Multi-Color Bar -->
                            <div class="h-1.5 w-full rounded-full bg-slate-200 dark:bg-slate-800 flex overflow-hidden">
                                <div class="bg-rose-500 h-full transition-all" style="width: {{ $protPct }}%" title="Protein: {{ $protPct }}%"></div>
                                <div class="bg-amber-400 h-full transition-all" style="width: {{ $carbsPct }}%" title="Carbohydrates: {{ $carbsPct }}%"></div>
                                <div class="bg-cyan-400 h-full transition-all" style="width: {{ $fatPct }}%" title="Fats: {{ $fatPct }}%"></div>
                            </div>
                        </div>

                        <!-- Clickable Expand / Collapse Toggle Bar -->
                        <button type="button" 
                                @click="expanded = !expanded" 
                                class="w-full my-1.5 py-2 px-3 rounded-xl bg-slate-50 dark:bg-slate-950 hover:bg-slate-100 dark:hover:bg-slate-950/90 border border-slate-200 dark:border-slate-800/80 hover:border-indigo-500/40 text-xs font-semibold text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white flex items-center justify-between transition-all cursor-pointer">
                            <span class="flex items-center gap-2">
                                <span>🍽️</span>
                                <span>{{ $plan->meals->count() }} Scheduled Meals</span>
                                @if($plan->guidelines)
                                    <span class="text-[10px] text-indigo-600 dark:text-indigo-400 font-normal">&bull; Advice</span>
                                @endif
                            </span>
                            <span class="flex items-center gap-1 text-[11px] text-indigo-600 dark:text-indigo-400 font-bold">
                                <span x-text="expanded ? 'Hide Meals' : 'View Meals'"></span>
                                <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </span>
                        </button>

                        <!-- Collapsible Meal Schedule & Guidelines -->
                        <div x-show="expanded" 
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-1"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-cloak 
                             class="space-y-3 pt-2 mb-3">
                            <div class="space-y-2 max-h-72 overflow-y-auto pr-1">
                                @forelse($plan->meals as $meal)
                                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800/80 flex items-start gap-3 hover:border-slate-300 dark:hover:border-slate-700 transition-colors">
                                        <div class="w-7 h-7 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-center text-xs shrink-0">
                                            {{ match($meal->meal_type) {
                                                'breakfast' => '🍳',
                                                'morning_snack' => '🍎',
                                                'lunch' => '🥗',
                                                'evening_snack' => '🥤',
                                                'dinner' => '🍲',
                                                'post_workout' => '🥛',
                                                default => '🍽️'
                                            } }}
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-center justify-between gap-2">
                                                <div class="flex items-center gap-2 truncate">
                                                    <span class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ $meal->meal_name }}</span>
                                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-white dark:bg-slate-900 text-slate-600 dark:text-slate-400 uppercase font-semibold border border-slate-200 dark:border-slate-800 shrink-0">
                                                        {{ str_replace('_', ' ', $meal->meal_type) }}
                                                    </span>
                                                </div>

                                                <div class="flex items-center gap-2 shrink-0">
                                                    @if($meal->recommended_time)
                                                        <span class="text-[10px] font-mono text-slate-600 dark:text-slate-400 bg-white dark:bg-slate-900 px-1.5 py-0.5 rounded-md border border-slate-200 dark:border-slate-800">
                                                            {{ date('h:i A', strtotime($meal->recommended_time)) }}
                                                        </span>
                                                    @endif
                                                    @if($meal->calories)
                                                        <span class="text-[10px] font-black text-amber-600 dark:text-amber-400">{{ $meal->calories }} kcal</span>
                                                    @endif
                                                </div>
                                            </div>

                                            @if($meal->items_description)
                                                <p class="text-[11px] text-slate-600 dark:text-slate-400 mt-1 leading-relaxed whitespace-pre-line">{{ $meal->items_description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center rounded-xl bg-slate-50 dark:bg-slate-950 border border-dashed border-slate-200 dark:border-slate-800 text-slate-400 dark:text-slate-500 text-xs">
                                        No meals added to this schedule yet.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Nutritionist & Hydration Advice -->
                            @if($plan->guidelines)
                                <div class="p-3 rounded-xl bg-indigo-50 dark:bg-indigo-950/20 border border-indigo-200 dark:border-indigo-900/30 text-[11px] text-indigo-700 dark:text-indigo-300 leading-relaxed flex items-start gap-2">
                                    <span class="text-sm">💧</span>
                                    <div class="whitespace-pre-line">
                                        <strong class="font-bold text-indigo-900 dark:text-indigo-200 block mb-0.5">Hydration &amp; Dietary Advice:</strong>
                                        {{ $plan->guidelines }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Card Actions -->
                    <div class="border-t border-slate-100 dark:border-slate-800/80 pt-4 mt-2 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-2.5">
                        <!-- WhatsApp Action Button -->
                        <button type="button"
                                onclick="openWhatsAppModal({{ json_encode($planJson) }}, {{ json_encode($memberName) }}, {{ json_encode($recipientPhone) }}, {{ json_encode($gymName) }})"
                                class="flex-1 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold flex items-center justify-center gap-2 shadow-lg shadow-emerald-600/20 transition-all">
                            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                            <span>Share on WhatsApp</span>
                        </button>

                        <div class="flex items-center gap-2">
                            <!-- Quick Copy Message -->
                            <button type="button"
                                    onclick="quickCopyPlan({{ json_encode($planJson) }}, {{ json_encode($memberName) }}, {{ json_encode($gymName) }})"
                                    class="py-2.5 px-3 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors"
                                    title="Copy formatted WhatsApp text">
                                <svg class="w-3.5 h-3.5 text-slate-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span>Copy Text</span>
                            </button>

                            <!-- Edit Plan -->
                            <button type="button"
                                    onclick="editDietPlan({{ json_encode($planJson) }})"
                                    class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-700 transition-colors"
                                    title="Edit Diet Plan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>

                            <!-- Delete Plan -->
                            <form action="{{ route('app.diets.delete', $plan->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to delete diet plan '{{ addslashes($plan->title) }}'?" 
                                  data-confirm-title="Delete Diet Plan" 
                                  data-confirm-btn="Yes, Delete Plan" 
                                  data-confirm-type="danger" 
                                  class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-rose-50 dark:hover:bg-rose-950/60 text-slate-600 dark:text-slate-400 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200 dark:border-slate-700 hover:border-rose-300 dark:hover:border-rose-900/50 transition-colors cursor-pointer" title="Delete Plan">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-16 text-center bg-white dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-800 rounded-3xl shadow-sm">
                    <div class="w-16 h-16 rounded-3xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto mb-4 shadow-inner">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 dark:text-white mb-1">No Diet Plans Created Yet</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto mb-6">Create personalized nutrition programs for your members or build reusable diet templates that you can send over WhatsApp with 1 click.</p>
                    <div>
                        <button type="button" onclick="openDietModal()" class="px-5 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all">
                            + Create First Diet Plan
                        </button>
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

    <!-- ==================== WHATSAPP DISPATCH MODAL ==================== -->
    <div id="whatsappModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 dark:bg-black/80 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-2xl overflow-hidden shadow-2xl relative my-8 text-slate-900 dark:text-slate-200">
            <!-- Modal Header -->
            <div class="p-5 bg-gradient-to-r from-emerald-50 via-slate-50 to-white dark:from-emerald-950/60 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 flex items-center justify-center shadow-inner">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-white">Send Diet Plan via WhatsApp</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400">Review recipient phone number and live preview before sending.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWhatsAppModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-5">
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
                        <button type="button" onclick="copyModalWaText()" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 font-bold flex items-center gap-1">
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
            <div class="p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                <button type="button" onclick="closeWhatsAppModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors">
                    Cancel
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="copyModalWaText()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-700 transition-colors flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy Text</span>
                    </button>

                    <button type="button" onclick="sendWhatsAppNow()" class="px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-black shadow-lg shadow-emerald-600/30 flex items-center gap-2 transition-all">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                        <span>Open WhatsApp Now</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== CREATE / EDIT DIET PLAN MODAL ==================== -->
    <div id="dietPlanModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 dark:bg-black/80 backdrop-blur-sm p-4 overflow-y-auto">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl w-full max-w-3xl overflow-hidden shadow-2xl relative my-8 text-slate-900 dark:text-slate-200">
            <form id="dietPlanForm" method="POST" action="{{ route('app.diets.store') }}">
                @csrf
                <input type="hidden" name="_method" id="dietFormMethod" value="POST">

                <!-- Modal Header -->
                <div class="p-5 bg-gradient-to-r from-indigo-50 via-slate-50 to-white dark:from-indigo-950/60 dark:via-slate-900 dark:to-slate-900 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 dark:border-indigo-500/30 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white" id="dietModalTitle">Create New Diet Plan</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Configure calories, macro targets, and scheduled meals.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDietModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center transition-colors">✕</button>
                </div>

                <!-- Modal Form Body -->
                <div class="p-6 space-y-5 max-h-[72vh] overflow-y-auto">
                    <!-- Title & Plan Type -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Plan Title *</label>
                            <input type="text" name="title" id="dietInputTitle" required placeholder="e.g. 4-Week High Protein Hypertrophy Split"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs placeholder-slate-400 dark:placeholder-slate-500 focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Plan Type</label>
                            <select name="is_template" id="dietInputIsTemplate" onchange="togglePlanTypeFields()"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="0">Member Specific</option>
                                <option value="1">Master Template</option>
                            </select>
                        </div>
                    </div>

                    <!-- Member & Trainer Select (Hidden when Template) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="memberTrainerGroup">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assign to Member</label>
                            <select name="member_id" id="dietInputMemberId"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="">-- Select Active Member --</option>
                                @foreach($members as $m)
                                    <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No phone' }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Assigned Coach / Trainer</label>
                            <select name="trainer_id" id="dietInputTrainerId"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:outline-none focus:border-indigo-500">
                                <option value="">-- Select Trainer (Optional) --</option>
                                @foreach($trainers as $t)
                                    <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Calorie & Macros Targets -->
                    <div>
                        <span class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-2">Daily Nutrition Target & Macro Split</span>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800">
                            <div>
                                <label class="block text-[11px] font-bold text-emerald-600 dark:text-emerald-400 mb-1">Total Calories (kcal)</label>
                                <input type="number" name="daily_calories" id="dietInputCalories" placeholder="2000" min="0" max="15000"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-emerald-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-rose-600 dark:text-rose-400 mb-1">Protein (g)</label>
                                <input type="number" name="protein_grams" id="dietInputProtein" placeholder="150" min="0" max="1000"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-rose-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">Carbohydrates (g)</label>
                                <input type="number" name="carbs_grams" id="dietInputCarbs" placeholder="200" min="0" max="1500"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-amber-500">
                            </div>

                            <div>
                                <label class="block text-[11px] font-bold text-cyan-600 dark:text-cyan-400 mb-1">Fats (g)</label>
                                <input type="number" name="fat_grams" id="dietInputFats" placeholder="60" min="0" max="1000"
                                       class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs font-bold focus:outline-none focus:border-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- Dates (Optional) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" id="datesGroup">
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

                    <!-- Scheduled Meals Builder -->
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider">Scheduled Meals Builder</h4>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400">Add meal timings, item descriptions, and calorie breakdowns.</p>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" onclick="loadStandardMealsPreset()" class="px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-amber-600 dark:text-amber-400 text-[11px] font-semibold border border-slate-200 dark:border-slate-700 transition-colors">
                                    + Insert 5-Meal Day
                                </button>
                                <button type="button" onclick="addMealRow()" class="px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-[11px] font-bold shadow transition-colors">
                                    + Add Meal
                                </button>
                            </div>
                        </div>

                        <div id="mealsContainer" class="space-y-3">
                            <!-- Dynamic meal rows will be inserted here -->
                        </div>
                    </div>

                    <!-- General Guidelines / Advice -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider mb-1">Dietary Guidelines & Hydration Advice</label>
                        <textarea name="guidelines" id="dietInputGuidelines" rows="3" placeholder="e.g. Drink 3.5 - 4 liters of water daily. Avoid refined sugars, fried food, and sodas. Sleep 7-8 hours daily."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:outline-none focus:border-indigo-500 leading-relaxed"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="p-5 bg-slate-50 dark:bg-slate-950 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between gap-3">
                    <button type="button" onclick="closeDietModal()" class="px-4 py-2.5 rounded-xl bg-white dark:bg-slate-900 hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold border border-slate-200 dark:border-slate-800 transition-colors">
                        Cancel
                    </button>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-lg shadow-indigo-600/20 transition-all flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="dietSubmitBtnText">Save Diet Plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Toast Notification for Quick Copy -->
    <div id="copyToast" class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-2xl bg-emerald-600 text-white text-xs font-bold shadow-2xl flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>WhatsApp message copied to clipboard!</span>
    </div>

    <!-- Page Scripts -->
    <script>
        let currentWaText = '';
        let mealCounter = 0;

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
                btnText.innerText = 'Copied!';
                setTimeout(() => btnText.innerText = 'Copy Message', 2000);
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
                cleanPhone = '91' + cleanPhone; // Auto-prepend Indian code if 10 digits
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
            const activeClass = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all bg-indigo-600 text-white shadow-sm';
            const inactiveClass = 'px-3.5 py-1.5 rounded-xl text-xs font-bold transition-all text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800';

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

        // Create / Edit Modal
        function togglePlanTypeFields() {
            const isTemplate = document.getElementById('dietInputIsTemplate').value === '1';
            const memberSelect = document.getElementById('dietInputMemberId');
            if (isTemplate) {
                memberSelect.value = '';
            }
        }

        function openDietModal() {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "{{ route('app.diets.store') }}";
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Create New Diet Plan';
            document.getElementById('dietSubmitBtnText').innerText = 'Save Diet Plan';
            document.getElementById('mealsContainer').innerHTML = '';
            mealCounter = 0;

            // Pre-add 1 meal row by default
            addMealRow();

            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        function closeDietModal() {
            document.getElementById('dietPlanModal').classList.add('hidden');
        }

        function addMealRow(meal = null) {
            mealCounter++;
            const container = document.getElementById('mealsContainer');
            const rowId = 'meal_row_' + mealCounter;

            const mealType = meal ? meal.meal_type : (mealCounter === 1 ? 'breakfast' : (mealCounter === 2 ? 'morning_snack' : (mealCounter === 3 ? 'lunch' : (mealCounter === 4 ? 'evening_snack' : 'dinner'))));
            const mealName = meal ? meal.meal_name : '';
            const recommendedTime = meal ? meal.recommended_time : '';
            const calories = meal ? (meal.calories || '') : '';
            const items = meal ? (meal.items_description || '') : '';

            const html = `
                <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 relative group" id="${rowId}">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                            <span class="w-5 h-5 rounded-full bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 text-[10px] flex items-center justify-center font-mono">${container.children.length + 1}</span>
                            <span>Meal Details</span>
                        </span>
                        <button type="button" onclick="document.getElementById('${rowId}').remove()" class="text-xs text-rose-500 dark:text-rose-400 hover:text-rose-600 dark:hover:text-rose-300 font-semibold">✕ Remove</button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-4 gap-2.5 mb-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Meal Type</label>
                            <select name="meals[${mealCounter}][meal_type]" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
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
                            <input type="text" name="meals[${mealCounter}][meal_name]" value="${mealName}" required placeholder="e.g. Oats with Milk" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Time</label>
                            <input type="time" name="meals[${mealCounter}][recommended_time]" value="${recommendedTime}" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Calories (kcal)</label>
                            <input type="number" name="meals[${mealCounter}][calories]" value="${calories}" placeholder="400" min="0" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase mb-1">Food Items & Portions (Formatted in WhatsApp)</label>
                        <input type="text" name="meals[${mealCounter}][items_description]" value="${items}" placeholder="e.g. 50g rolled oats, 250ml milk, 1 scoop whey, 5 almonds" class="w-full px-2.5 py-1.5 rounded-lg bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-800 text-xs text-slate-800 dark:text-slate-300 focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        }

        function loadStandardMealsPreset() {
            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;

            const presetMeals = [
                { meal_type: 'breakfast', recommended_time: '08:30', meal_name: 'Power Breakfast', calories: 400, items_description: '40g oats with milk, 3 boiled egg whites, 1 banana' },
                { meal_type: 'morning_snack', recommended_time: '11:30', meal_name: 'Mid-Morning Boost', calories: 150, items_description: '10 almonds, 4 walnuts, 1 cup green tea without sugar' },
                { meal_type: 'lunch', recommended_time: '13:30', meal_name: 'High Protein Lunch', calories: 600, items_description: '150g brown rice/2 rotis, 150g chicken breast or paneer, green salad' },
                { meal_type: 'evening_snack', recommended_time: '17:30', meal_name: 'Pre-Workout Fuel', calories: 220, items_description: '1 apple, 1 slice whole wheat bread with peanut butter' },
                { meal_type: 'dinner', recommended_time: '20:30', meal_name: 'Clean Recovery Dinner', calories: 430, items_description: '2 phulkas, 1 bowl dal, sautéed green vegetables, 100g curd' },
            ];

            presetMeals.forEach(m => addMealRow(m));
        }

        function editDietPlan(plan) {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "/app/diets/" + plan.id;
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Edit Diet Plan: ' + plan.title;
            document.getElementById('dietSubmitBtnText').innerText = 'Update Diet Plan';

            document.getElementById('dietInputTitle').value = plan.title || '';
            document.getElementById('dietInputIsTemplate').value = plan.is_template ? '1' : '0';
            document.getElementById('dietInputMemberId').value = plan.member_id || '';
            document.getElementById('dietInputTrainerId').value = plan.trainer_id || '';
            document.getElementById('dietInputCalories').value = plan.daily_calories || '';
            document.getElementById('dietInputProtein').value = plan.protein_grams || '';
            document.getElementById('dietInputCarbs').value = plan.carbs_grams || '';
            document.getElementById('dietInputFats').value = plan.fat_grams || '';
            document.getElementById('dietInputStartDate').value = plan.start_date || '';
            document.getElementById('dietInputEndDate').value = plan.end_date || '';
            document.getElementById('dietInputGuidelines').value = plan.guidelines || '';

            togglePlanTypeFields();

            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;

            if (plan.meals && plan.meals.length > 0) {
                plan.meals.forEach(m => addMealRow(m));
            } else {
                addMealRow();
            }

            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        // ==================== AI DIET GENERATOR MODAL SCRIPT ====================
        @php
            $membersDirectoryData = $members->map(function($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->full_name,
                    'gender' => $m->gender ?? 'male',
                    'age' => $m->dob ? $m->dob->age : 26,
                    'phone' => $m->phone ?? '',
                ];
            })->values();
        @endphp
        const membersDirectory = {!! json_encode($membersDirectoryData) !!};

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
            const mealsPerDay = parseInt(document.getElementById('aiInputMealsCount').value) || 4;
            const workoutTime = document.getElementById('aiInputWorkoutTime').value || 'morning';
            const foodPreferences = document.getElementById('aiInputFoodPref').value || '';
            const foodsToAvoid = document.getElementById('aiInputFoodsAvoid').value || '';
            const allergies = document.getElementById('aiInputAllergies').value || '';
            const additionalNotes = document.getElementById('aiInputNotes').value || '';

            // Switch to Loading View
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
                meals_per_day: mealsPerDay,
                workout_time: workoutTime,
                food_preferences: foodPreferences,
                foods_to_avoid: foodsToAvoid,
                allergies: allergies,
                additional_notes: additionalNotes,
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
            
            const badgeEl = document.getElementById('aiResBadge');
            if (badgeEl) {
                if (data.is_gemini) {
                    badgeEl.className = 'px-2 py-0.5 rounded-full bg-purple-500/20 text-purple-600 dark:text-purple-300 border border-purple-500/30 text-[10px] font-black uppercase tracking-wider flex items-center gap-1';
                    badgeEl.innerHTML = `✨ <span>Google Gemini AI (${data.gemini_model || 'gemini-1.5-flash'})</span>`;
                } else {
                    badgeEl.className = 'px-2 py-0.5 rounded-full bg-indigo-500/20 text-indigo-600 dark:text-indigo-300 border border-indigo-500/30 text-[10px] font-black uppercase tracking-wider flex items-center gap-1';
                    badgeEl.innerHTML = `⚡ <span>Smart Diet Engine</span>`;
                }
            }

            document.getElementById('aiResBmr').innerText = 'BMR: ' + data.bmr_calculated + ' kcal';
            document.getElementById('aiResTdee').innerText = 'TDEE: ' + data.tdee_calculated + ' kcal';

            // Meals
            const mealsContainer = document.getElementById('aiResMealsList');
            mealsContainer.innerHTML = '';

            data.meals.forEach((meal, idx) => {
                const mealHtml = `
                    <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800/80 pb-2.5">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 font-mono text-xs font-bold flex items-center justify-center">${idx + 1}</span>
                                <h4 class="text-xs font-black text-slate-900 dark:text-white">${meal.meal_name}</h4>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 font-mono">⏰ ${meal.recommended_time}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] font-bold">
                                <span class="px-2 py-0.5 rounded-full bg-slate-200 dark:bg-slate-800 text-amber-600 dark:text-amber-400 font-mono">${meal.target_macros.calories} kcal</span>
                                <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 dark:bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 font-mono">P: ${meal.target_macros.protein_g}g</span>
                                <span class="px-2 py-0.5 rounded-full bg-emerald-500/10 dark:bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 font-mono">C: ${meal.target_macros.carbs_g}g</span>
                                <span class="px-2 py-0.5 rounded-full bg-rose-500/10 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400 font-mono">F: ${meal.target_macros.fat_g}g</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed font-sans pl-2 border-l-2 border-indigo-500/40">
                            ${meal.items_description}
                        </div>
                        ${meal.alternatives ? `
                            <div class="p-2.5 rounded-xl bg-slate-100 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800/60 text-[11px] text-slate-600 dark:text-slate-400">
                                <span class="text-indigo-600 dark:text-indigo-400 font-bold">🔄 Alternative Option:</span>
                                <p class="mt-0.5 text-slate-700 dark:text-slate-300 whitespace-pre-line">${meal.alternatives}</p>
                            </div>
                        ` : ''}
                    </div>
                `;
                mealsContainer.insertAdjacentHTML('beforeend', mealHtml);
            });

            // Supplements
            const suppContainer = document.getElementById('aiResSupplementsList');
            suppContainer.innerHTML = '';
            data.optional_supplements.forEach(s => {
                suppContainer.insertAdjacentHTML('beforeend', `
                    <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-slate-900 dark:text-white">${s.name}</span>
                            <span class="px-2 py-0.2 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[9px] font-bold">OPTIONAL</span>
                        </div>
                        <p class="text-[11px] text-indigo-600 dark:text-indigo-300 font-mono mt-1">${s.dosage}</p>
                        <p class="text-[10px] text-slate-500 dark:text-slate-400 mt-0.5">${s.purpose}</p>
                    </div>
                `);
            });

            // Guidelines
            const guidelinesContainer = document.getElementById('aiResGuidelinesList');
            guidelinesContainer.innerHTML = '';
            data.guidelines.forEach(g => {
                guidelinesContainer.insertAdjacentHTML('beforeend', `<li class="flex items-start gap-2"><span class="text-emerald-500 dark:text-emerald-400">✓</span><span class="text-slate-700 dark:text-slate-300">${g}</span></li>`);
            });

            document.getElementById('aiResDisclaimer').innerText = data.medical_disclaimer;
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
            text += `💧 *Hydration Goal:* ${d.daily_totals.water_liters} Litres / day\n\n`;
            text += `📋 *DAILY MEAL REGIME:*\n`;
            
            d.meals.forEach((m, idx) => {
                text += `\n*${idx + 1}. ${m.meal_name}* (⏰ ${m.recommended_time}) [${m.target_macros.calories} kcal]\n`;
                text += `${m.items_description}\n`;
                if (m.alternatives) {
                    text += `_🔄 Alternative: ${m.alternatives.replace(/\n/g, ' ')}_\n`;
                }
            });

            text += `\n━━━━━━━━━━━━━━━━━━━━━\n`;
            text += `💊 *OPTIONAL SUPPLEMENTS:*\n`;
            d.optional_supplements.forEach(s => {
                text += `• *${s.name}:* ${s.dosage} (${s.purpose})\n`;
            });

            text += `\n📌 *GUIDELINES:*\n`;
            d.guidelines.forEach(g => {
                text += `• ${g}\n`;
            });

            text += `\n_⚠️ Note: This diet is designed for general gym fitness goals._`;

            navigator.clipboard.writeText(text).then(() => {
                const copyBtn = document.getElementById('aiBtnCopyText');
                copyBtn.innerHTML = `<span>✓ Copied to Clipboard!</span>`;
                setTimeout(() => {
                    copyBtn.innerHTML = `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg><span>Copy WhatsApp Text</span>`;
                }, 2000);
            });
        }
    </script>

    <!-- ==================== AI DIET GENERATOR MODAL ==================== -->
    <div id="aiDietModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/60 dark:bg-black/85 backdrop-blur-md p-3 sm:p-5 overflow-y-auto">
        <div class="relative w-full max-w-4xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl shadow-2xl my-6 overflow-hidden max-h-[92vh] flex flex-col text-slate-900 dark:text-slate-200">
            
            <!-- Modal Header -->
            @php
                $globalGeminiKey = \App\Models\Setting::getGlobal('gemini_api_key') ?: config('services.gemini.api_key');
                $globalGeminiModel = \App\Models\Setting::getGlobal('gemini_model') ?: config('services.gemini.model', 'gemini-1.5-flash');
                $hasGeminiConfigured = !empty($globalGeminiKey);
            @endphp
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950/70 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-purple-600 via-indigo-600 to-pink-600 text-white flex items-center justify-center text-lg shadow-lg shadow-purple-600/30">
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
                        <p class="text-xs text-slate-500 dark:text-slate-400">Generates precision calories, macros, pre/post workout timing, and practical Indian meals based on age, goals &amp; health factors.</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiDietModal()" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white flex items-center justify-center text-sm font-bold transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <!-- Modal Content (Scrollable) -->
            <div class="p-6 overflow-y-auto flex-1 space-y-6">

                <!-- 1. FORM INPUT VIEW -->
                <div id="aiDietFormView" class="space-y-6">
                    <form id="aiDietForm" onsubmit="submitAiDietGeneration(event)" class="space-y-5">
                        
                        <!-- Member Quick Select -->
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200 dark:border-slate-800/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-bold text-slate-700 dark:text-slate-300">👤 Select Existing Member (Optional):</span>
                            </div>
                            <div class="w-full sm:w-72">
                                <select id="aiMemberSelect" onchange="handleAiMemberSelect()" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                    <option value="">-- Manual Entry / Custom Member --</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- 1. Personal & Body Metrics -->
                        <div class="space-y-3">
                            <h4 class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                                <span>1. Member Profile & Body Metrics</span>
                            </h4>
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
                                        <option value="other">Other</option>
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

                        <!-- 2. Goals & Training Routine -->
                        <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                            <h4 class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                                <span>2. Fitness Goals & Training Schedule</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Fitness Goal *</label>
                                    <select id="aiInputGoal" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="muscle_gain">Muscle Gain & Hypertrophy</option>
                                        <option value="fat_loss">Fat Loss & Leaning</option>
                                        <option value="weight_loss">Weight Loss</option>
                                        <option value="weight_gain">Weight Gain (Bulk)</option>
                                        <option value="maintenance">Maintenance</option>
                                        <option value="general_fitness">General Fitness</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Activity Level *</label>
                                    <select id="aiInputActivity" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="moderately_active">Moderately Active (Gym 3-5 days)</option>
                                        <option value="very_active">Very Active (Gym 6-7 days)</option>
                                        <option value="lightly_active">Lightly Active (Gym 1-3 days)</option>
                                        <option value="sedentary">Sedentary (Desk job / minimal)</option>
                                        <option value="extremely_active">Extremely Active (2x daily training)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Workout Timing *</label>
                                    <select id="aiInputWorkoutTime" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="morning">Morning (08:00 AM - 11:00 AM)</option>
                                        <option value="evening">Evening (05:00 PM - 08:00 PM)</option>
                                        <option value="early_morning">Early Morning (06:00 AM - 08:00 AM)</option>
                                        <option value="afternoon">Afternoon (12:00 PM - 03:00 PM)</option>
                                        <option value="night">Night (08:00 PM - 10:00 PM)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Meals Per Day *</label>
                                    <select id="aiInputMealsCount" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="4">4 Meals / Day (Standard)</option>
                                        <option value="5">5 Meals / Day (Optimal)</option>
                                        <option value="3">3 Meals / Day (Compact)</option>
                                        <option value="6">6 Meals / Day (Frequent)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Diet Preferences & Dietary Restrictions -->
                        <div class="space-y-3 pt-2 border-t border-slate-200 dark:border-slate-800/80">
                            <h4 class="text-[11px] font-extrabold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider flex items-center gap-1.5">
                                <span>3. Dietary Preferences & Restrictions</span>
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Diet Preference *</label>
                                    <select id="aiInputDietPref" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:border-indigo-500 focus:outline-none">
                                        <option value="vegetarian">Vegetarian (Lacto / High-Protein)</option>
                                        <option value="non_vegetarian">Non-Vegetarian (Eggs, Chicken, Fish)</option>
                                        <option value="eggetarian">Eggetarian (Eggs + Veg Dairy)</option>
                                        <option value="vegan">Vegan (100% Plant-Based)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Food Preferences</label>
                                    <input type="text" id="aiInputFoodPref" placeholder="e.g. North Indian, Oats, Paneer, Rice" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Foods to Avoid</label>
                                    <input type="text" id="aiInputFoodsAvoid" placeholder="e.g. Deep fried, Sugary snacks, Soya" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Allergies</label>
                                    <input type="text" id="aiInputAllergies" placeholder="e.g. Peanuts, Gluten, Dairy (or None)" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-[10px] font-bold text-slate-600 dark:text-slate-400 uppercase mb-1">Additional Notes / Medical Concerns</label>
                                <input type="text" id="aiInputNotes" placeholder="e.g. Desk job, prefers budget-friendly home cooking, takes morning coffee" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-xs text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 focus:border-indigo-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Action Submit -->
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

                <!-- 2. LOADING STATE VIEW -->
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

                <!-- 3. AI RESULTS VIEW -->
                <div id="aiDietResultView" class="hidden space-y-5">
                    
                    <!-- Result Header Banner -->
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

                    <!-- Macro Target Cards -->
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
                            <span class="text-[10px] text-slate-500 dark:text-slate-400 font-bold uppercase block">Carbohydrates</span>
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

                    <!-- Daily Meal Schedule -->
                    <div class="space-y-3">
                        <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-1.5">
                            <span>🍽️ Scheduled Indian Meals &amp; Portion Breakdown</span>
                        </h4>
                        <div id="aiResMealsList" class="space-y-3"></div>
                    </div>

                    <!-- Supplements & Guidelines -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">💊 Optional Training Supplements</h4>
                            <div id="aiResSupplementsList" class="space-y-2"></div>
                        </div>

                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-2.5">
                            <h4 class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">📌 Lifestyle &amp; Cooking Guidelines</h4>
                            <ul id="aiResGuidelinesList" class="space-y-2 text-xs text-slate-700 dark:text-slate-300"></ul>
                        </div>
                    </div>

                    <!-- Disclaimer -->
                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-400 text-[11px] leading-relaxed flex items-start gap-2.5">
                        <span class="text-amber-500 dark:text-amber-400 text-sm shrink-0">⚠️</span>
                        <p id="aiResDisclaimer"></p>
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-3 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                        <button type="button" onclick="document.getElementById('aiDietResultView').classList.add('hidden'); document.getElementById('aiDietFormView').classList.remove('hidden');" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold transition-colors cursor-pointer flex items-center gap-1.5">
                            <span>&larr; Adjust Parameters</span>
                        </button>
                        <button type="button" onclick="closeAiDietModal()" class="px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white text-xs font-bold transition-colors cursor-pointer">
                            Close
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
