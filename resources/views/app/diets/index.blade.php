<x-app-layout header="">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .diet-ui-scope {
            font-family: 'Inter', 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }
        .diet-ui-scope .tabular-nums {
            font-variant-numeric: tabular-nums;
        }
    </style>

    <div class="diet-ui-scope space-y-6">
        <!-- Success / Error Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if(session('error'))
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('error') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-fg-muted hover:text-fg-muted dark:hover:text-white text-xs font-semibold p-1 cursor-pointer">✕</button>
            </div>
        @endif

        @if($errors->any())
            <div class="p-4 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-600 dark:text-rose-400">
                <div class="font-semibold text-xs mb-1">Please fix the following issues:</div>
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
                <h2 class="text-xl sm:text-2xl font-semibold text-fg dark:text-white tracking-tight">
                    Diet &amp; Nutrition Plans
                </h2>
                <p class="text-sm text-fg-muted dark:text-fg-muted mt-1">Build custom meal plans, set macro targets, and share with members.</p>
            </div>

            <!-- Primary Actions Group -->
            <div class="flex items-center gap-2 sm:gap-2.5 flex-wrap">
                <button type="button" onclick="openFoodLibraryModal()" 
                        class="px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-fg-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    <span>Food Library</span>
                    <span class="px-1.5 py-0.5 rounded-md bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold tabular-nums">{{ count($indianFoods ?? []) }}</span>
                </button>

                <button type="button" onclick="openAiDietModal()" 
                        class="px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Quick Plan Generator</span>
                </button>

                <form action="{{ route('app.diets.seed') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" 
                            class="px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold border border-line dark:border-line transition-all cursor-pointer"
                            title="Load starter templates">
                        Starter Templates
                    </button>
                </form>

                <button type="button" onclick="openDietModal()" 
                        class="px-4 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer ops-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 4v16m8-8H4"/></svg>
                    <span>Create Diet Plan</span>
                </button>
            </div>
        </div>

        <!-- ==================== PLANS TOOLBAR & SEARCH BAR ==================== -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3 p-3 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line">
            <div class="flex items-center gap-2.5 px-1">
                <span class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-accent text-accent-on ops-primary">
                    Diet Plans ({{ $templatePlansCount }})
                </span>
                <span class="text-xs text-fg-muted dark:text-fg-muted">Saved custom diets &amp; reusable meal plans</span>
            </div>

            <div class="relative w-full sm:w-72">
                <input type="text" id="dietSearchInput" oninput="searchDietPlans()" 
                       placeholder="Search plan title..." 
                       class="w-full pl-9 pr-4 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white placeholder-fg-subtle dark:placeholder-fg-subtle focus:outline-none focus:border-accent transition-colors">
                <svg class="w-4 h-4 text-fg-muted dark:text-fg-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
        </div>

        <!-- ==================== RESPONSIVE DIET PLANS GRID ==================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-2 2xl:grid-cols-3 gap-5" id="dietPlansGrid">
            @forelse($plans as $plan)
                @php
                    $isMemberPlan = !$plan->is_template && $plan->member;
                    $recipientPhone = $plan->member?->phone ?? '';
                    $memberName = $plan->member?->full_name ?? ($plan->is_template ? 'Template Plan' : 'Unassigned Member');
                    $gymName = $tenant->name ?? 'GymDeskHub';

                    $planJson = [
                        'id' => $plan->id,
                        'title' => $plan->title,
                        'daily_calories' => $plan->daily_calories,
                        'protein_grams' => $plan->protein_grams,
                        'carbs_grams' => $plan->carbs_grams,
                        'fat_grams' => $plan->fat_grams,
                        'start_date' => $plan->start_date ? $plan->start_date->format('d M Y') : '',
                        'end_date' => $plan->end_date ? $plan->end_date->format('d M Y') : '',
                        'start_date_iso' => $plan->start_date ? $plan->start_date->format('Y-m-d') : '',
                        'end_date_iso' => $plan->end_date ? $plan->end_date->format('Y-m-d') : '',
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
                                'protein_grams' => $m->protein_grams,
                                'carbs_grams' => $m->carbs_grams,
                                'fat_grams' => $m->fat_grams,
                                'items_description' => $m->items_description,
                            ];
                        })->toArray(),
                    ];

                    $protein = (int) ($plan->protein_grams ?? 0);
                    $carbs = (int) ($plan->carbs_grams ?? 0);
                    $fat = (int) ($plan->fat_grams ?? 0);
                @endphp

                <div x-data="{ expanded: false }" 
                     class="diet-plan-card bg-surface dark:bg-surface border border-line dark:border-line rounded-xl p-5 hover:border-line dark:hover:border-line transition-all flex flex-col justify-between"
                     data-plan-type="{{ $plan->is_template ? 'template' : 'member' }}"
                     data-title="{{ strtolower($plan->title) }}"
                     data-member="{{ strtolower($memberName) }}">
                    <div>
                        <!-- Card Header: Member or Template identity -->
                        <div class="flex items-start justify-between gap-3 pb-3 border-b border-line dark:border-line">
                            <div class="flex items-center gap-3 min-w-0">
                                @if(!$plan->is_template && $plan->member)
                                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/60 dark:border-indigo-800/50 flex items-center justify-center font-semibold text-sm shrink-0">
                                        {{ strtoupper(substr($plan->member->first_name ?? 'M', 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="text-sm font-semibold text-fg dark:text-white truncate">{{ $plan->member->full_name }}</h4>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-800/50">Member</span>
                                        </div>
                                        <p class="text-xs text-fg-muted dark:text-fg-muted font-mono">{{ $plan->member->phone ?? 'No phone' }}</p>
                                    </div>
                                @else
                                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 border border-amber-200/60 dark:border-amber-800/50 flex items-center justify-center shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-1.5">
                                            <h4 class="text-sm font-semibold text-fg dark:text-white truncate">Template Plan</h4>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-800/50">Reusable</span>
                                        </div>
                                        <p class="text-xs text-fg-muted dark:text-fg-muted">Can be assigned to any member</p>
                                    </div>
                                @endif
                            </div>

                            <!-- Target Calories Badge -->
                            <div class="text-right shrink-0">
                                <span class="px-2.5 py-1 rounded-xl bg-raised dark:bg-raised text-fg dark:text-white font-mono font-semibold text-xs inline-block">
                                    {{ $plan->daily_calories ? number_format($plan->daily_calories) : '2,000' }} <span class="text-xs text-fg-muted font-normal">kcal</span>
                                </span>
                            </div>
                        </div>

                        <!-- Plan Title & Coach -->
                        <div class="py-3">
                            <h3 class="text-sm font-semibold text-fg dark:text-fg leading-snug line-clamp-1" title="{{ $plan->title }}">
                                {{ $plan->title }}
                            </h3>
                            @if($plan->trainer)
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Coach: <span class="text-fg-muted dark:text-fg-muted font-medium">{{ $plan->trainer->full_name }}</span></p>
                            @endif
                        </div>

                        <!-- Clean Macronutrients Strip -->
                        <div class="grid grid-cols-3 gap-2 p-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center text-xs">
                            <div>
                                <span class="block text-xs text-fg-muted font-semibold">Protein</span>
                                <span class="font-semibold text-fg dark:text-fg">{{ $protein }}g</span>
                            </div>
                            <div class="border-x border-line dark:border-line">
                                <span class="block text-xs text-fg-muted font-semibold">Carbs</span>
                                <span class="font-semibold text-fg dark:text-fg">{{ $carbs }}g</span>
                            </div>
                            <div>
                                <span class="block text-xs text-fg-muted font-semibold">Fats</span>
                                <span class="font-semibold text-fg dark:text-fg">{{ $fat }}g</span>
                            </div>
                        </div>

                        <!-- Meals Summary & Preview Toggle -->
                        <div class="mt-3">
                            <div class="text-xs text-fg-muted dark:text-fg-muted font-medium mb-1.5 flex items-center justify-between">
                                <span>{{ $plan->meals->count() }} Daily Meals:</span>
                                @if($plan->start_date)
                                    <span class="text-xs font-mono text-fg-muted">{{ $plan->start_date->format('d M') }} {{ $plan->end_date ? '→ ' . $plan->end_date->format('d M') : '' }}</span>
                                @endif
                            </div>

                            <!-- Compact List of Meals (First 3) -->
                            <div class="space-y-1.5">
                                @forelse($plan->meals->take(3) as $m)
                                    <div class="px-2.5 py-1.5 rounded-lg bg-raised dark:bg-slate-950/60 border border-line dark:border-line flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span class="font-semibold text-fg dark:text-fg truncate">{{ $m->meal_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-fg-muted shrink-0 font-mono">
                                            @if($m->recommended_time)
                                                <span>{{ date('h:i A', strtotime($m->recommended_time)) }}</span>
                                            @endif
                                            @if($m->calories)
                                                <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $m->calories }} kcal</span>
                                            @endif
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 text-center rounded-lg bg-raised dark:bg-slate-950/40 text-fg-muted text-xs">
                                        No meals added to schedule yet.
                                    </div>
                                @endforelse

                                @if($plan->meals->count() > 3)
                                    <button type="button" @click="expanded = !expanded" class="w-full text-xs text-indigo-600 dark:text-indigo-400 hover:underline text-center py-1 font-semibold flex items-center justify-center gap-1 cursor-pointer">
                                        <span x-text="expanded ? 'Hide full meal plan' : '+ See all {{ $plan->meals->count() }} meals & details'"></span>
                                        <svg class="w-3 h-3 transition-transform duration-200" :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                    </button>
                                @endif
                            </div>

                            <!-- Expandable Full Meal Schedule -->
                            <div x-show="expanded" x-cloak class="mt-2.5 space-y-2 pt-2 border-t border-line dark:border-line">
                                @foreach($plan->meals->skip(3) as $m)
                                    <div class="px-2.5 py-1.5 rounded-lg bg-raised dark:bg-slate-950/60 border border-line dark:border-line flex items-center justify-between text-xs">
                                        <div class="flex items-center gap-2 truncate">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                                            <span class="font-semibold text-fg dark:text-fg truncate">{{ $m->meal_name }}</span>
                                        </div>
                                        <div class="flex items-center gap-2 text-xs text-fg-muted shrink-0 font-mono">
                                            @if($m->recommended_time)
                                                <span>{{ date('h:i A', strtotime($m->recommended_time)) }}</span>
                                            @endif
                                            @if($m->calories)
                                                <span class="font-semibold text-fg-muted dark:text-fg-muted">{{ $m->calories }} kcal</span>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach

                                @if($plan->guidelines)
                                    <div class="p-2.5 rounded-xl bg-raised dark:bg-surface text-xs text-fg-muted dark:text-fg-muted border border-line dark:border-line">
                                        <strong class="font-semibold text-fg dark:text-fg block mb-0.5">Hydration &amp; Guidelines:</strong>
                                        <p class="whitespace-pre-line">{{ $plan->guidelines }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons Footer -->
                    <div class="pt-4 mt-4 border-t border-line dark:border-line flex items-center justify-between gap-2">
                        <button type="button"
                                onclick="openWhatsAppModal({{ json_encode($planJson) }}, {{ json_encode($memberName) }}, {{ json_encode($recipientPhone) }}, {{ json_encode($gymName) }})"
                                class="flex-1 py-2 px-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center justify-center gap-1.5 transition-colors cursor-pointer">
                            <svg class="w-4 h-4 fill-current shrink-0" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                            <span>Share on WhatsApp</span>
                        </button>

                        <button type="button"
                                onclick="editDietPlan({{ json_encode($planJson) }})"
                                class="p-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold border border-line dark:border-line transition-colors cursor-pointer"
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
                            <button type="submit" class="p-2 rounded-xl bg-raised dark:bg-raised hover:bg-rose-50 dark:hover:bg-rose-950/60 text-fg-muted hover:text-rose-600 dark:text-fg-muted dark:hover:text-rose-400 border border-line dark:border-line transition-colors cursor-pointer" title="Delete Plan">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="col-span-full p-12 sm:p-16 text-center bg-surface dark:bg-surface border border-dashed border-line dark:border-line rounded-xl">
                    <div class="w-16 h-16 rounded-xl bg-indigo-600/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <h3 class="text-lg font-semibold text-fg dark:text-white mb-1">No Diet Plans Yet</h3>
                    <p class="text-xs text-fg-muted dark:text-fg-muted max-w-md mx-auto mb-6">Create a custom daily meal plan for a member or load starter templates.</p>
                    <div class="flex items-center justify-center gap-3 flex-wrap">
                        <button type="button" onclick="openDietModal()" class="px-5 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all cursor-pointer ops-primary">
                            Create Diet Plan
                        </button>
                        <form action="{{ route('app.diets.seed') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line transition-all cursor-pointer">
                                Load Starter Templates
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
        <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-5xl overflow-hidden relative my-auto max-h-[92vh] flex flex-col text-fg dark:text-fg">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 bg-raised dark:bg-slate-950/80 border-b border-line dark:border-line flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-semibold text-fg dark:text-white">Food &amp; Macro Library</h3>
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-medium border border-amber-500/20 tabular-nums">{{ count($indianFoods ?? []) }} Items</span>
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Calories, protein, carbs, fats, and fiber per serving.</p>
                    </div>
                </div>
                <button type="button" onclick="closeFoodLibraryModal()" class="w-8 h-8 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <!-- Search & Filters Strip -->
            <div class="p-4 border-b border-line dark:border-line bg-raised dark:bg-slate-950/50 space-y-3 shrink-0">
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                    <div class="relative flex-1">
                        <input type="text" id="foodLibrarySearch" oninput="filterFoodLibrary()" 
                               placeholder="Search chicken, paneer, eggs, oats, roti, rice, whey..."
                               class="w-full pl-9 pr-4 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg placeholder-fg-subtle focus:outline-none focus:border-amber-500">
                        <svg class="w-4 h-4 text-fg-muted absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </div>

                    <div class="flex items-center gap-1.5 overflow-x-auto pb-1">
                        <button type="button" onclick="setFoodCategoryFilter('all')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-500 text-slate-950 shrink-0" data-cat="all">All ({{ count($indianFoods ?? []) }})</button>
                        <button type="button" onclick="setFoodCategoryFilter('non_veg_protein')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="non_veg_protein">Non-Veg &amp; Eggs</button>
                        <button type="button" onclick="setFoodCategoryFilter('veg_protein')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="veg_protein">Veg Protein</button>
                        <button type="button" onclick="setFoodCategoryFilter('carbs')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="carbs">Carbs</button>
                        <button type="button" onclick="setFoodCategoryFilter('fats')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="fats">Fats</button>
                        <button type="button" onclick="setFoodCategoryFilter('fruits')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="fruits">Fruits</button>
                        <button type="button" onclick="setFoodCategoryFilter('salads')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="salads">Fiber &amp; Sabzi</button>
                        <button type="button" onclick="setFoodCategoryFilter('gym_meals')" class="food-cat-btn px-3 py-1.5 rounded-lg text-xs font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="gym_meals">Meals &amp; Shakes</button>
                    </div>
                </div>
            </div>

            <!-- Food Cards Grid -->
            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-3">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3.5" id="foodLibraryGrid">
                    @foreach($indianFoods ?? [] as $food)
                        <div class="food-item-card p-3.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line hover:border-amber-500/40 transition-all flex flex-col justify-between"
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
                                            <h4 class="text-xs font-semibold text-fg dark:text-white leading-tight">{{ $food['name'] }}</h4>
                                        </div>
                                        <div class="text-xs text-fg-muted dark:text-fg-muted mt-0.5 font-medium pl-5">{{ $food['hindi_name'] ?? '' }} &bull; Serving: {{ $food['serving'] }}</div>
                                    </div>

                                    <span class="px-2 py-0.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 text-xs font-mono font-semibold shrink-0">
                                        {{ $food['calories'] }} <span class="text-xs font-normal">kcal</span>
                                    </span>
                                </div>

                                <!-- Macros Badges -->
                                <div class="grid grid-cols-4 gap-1.5 text-center my-2 text-xs font-mono">
                                    <div class="p-1 rounded-md bg-rose-500/10 dark:bg-rose-500/15 text-rose-600 dark:text-rose-400">
                                        <span class="block text-xs font-sans text-fg-muted">Prot</span>
                                        <strong>{{ $food['protein'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-amber-500/10 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <span class="block text-xs font-sans text-fg-muted">Carb</span>
                                        <strong>{{ $food['carbs'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-cyan-500/10 dark:bg-cyan-500/15 text-cyan-600 dark:text-cyan-400">
                                        <span class="block text-xs font-sans text-fg-muted">Fat</span>
                                        <strong>{{ $food['fat'] }}g</strong>
                                    </div>
                                    <div class="p-1 rounded-md bg-teal-500/10 dark:bg-teal-500/15 text-teal-600 dark:text-teal-400">
                                        <span class="block text-xs font-sans text-fg-muted">Fiber</span>
                                        <strong>{{ $food['fiber'] }}g</strong>
                                    </div>
                                </div>

                                <p class="text-xs text-fg-muted dark:text-fg-muted leading-snug line-clamp-2">{{ $food['description'] }}</p>
                            </div>

                            <div class="pt-2.5 mt-2 border-t border-line dark:border-line flex items-center justify-between">
                                <span class="text-[10px] px-2 py-0.5 rounded-full {{ $food['gym_purpose'] === 'weight_loss' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400' : ($food['gym_purpose'] === 'muscle_gain' ? 'bg-indigo-500/10 text-indigo-600 dark:text-indigo-400' : 'bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-300') }} font-medium">
                                    {{ $food['gym_purpose'] === 'weight_loss' ? 'Weight Loss' : ($food['gym_purpose'] === 'muscle_gain' ? 'Muscle Gain' : 'All Goals') }}
                                </span>

                                <button type="button" 
                                        onclick="copyFoodDetails({{ json_encode($food) }})"
                                        class="text-xs font-semibold text-amber-600 dark:text-amber-400 hover:text-amber-500 flex items-center gap-1 cursor-pointer">
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
        <div class="bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-2xl overflow-hidden relative my-auto max-h-[90vh] flex flex-col text-fg dark:text-fg">
            <!-- Modal Header -->
            <div class="p-4 sm:p-5 border-b border-line dark:border-line flex items-center justify-between shrink-0 bg-surface border">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 dark:border-emerald-500/30 flex items-center justify-center">
                        <svg class="w-6 h-6 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-base font-semibold text-fg dark:text-white">Send Diet Plan via WhatsApp</h3>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Review recipient phone number and live preview before sending.</p>
                    </div>
                </div>
                <button type="button" onclick="closeWhatsAppModal()" class="w-8 h-8 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer">✕</button>
            </div>

            <!-- Modal Body -->
            <div class="p-4 sm:p-6 space-y-4 overflow-y-auto flex-1">
                <!-- Recipient Phone & Plan Info -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Recipient Mobile Number *</label>
                        <div class="relative">
                            <input type="text" id="waRecipientPhone" placeholder="e.g. 9876543210 or 919876543210"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-fg dark:text-white font-mono text-sm focus:outline-none focus:border-emerald-500 transition-colors">
                            <span class="absolute right-3 top-2.5 text-xs text-fg-muted dark:text-fg-muted font-semibold">WHATSAPP</span>
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Indian 10-digit numbers automatically get country code added.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1.5">Recipient / Member</label>
                        <input type="text" id="waRecipientName" readonly
                               class="w-full px-3.5 py-2.5 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line text-fg-muted dark:text-fg-muted text-sm focus:outline-none cursor-default font-medium">
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-1">Assigned recipient in GymDeskHub records.</p>
                    </div>
                </div>

                <!-- Live Message Preview Box -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="text-xs font-semibold text-fg-muted dark:text-fg-muted flex items-center gap-2">
                            <span>Live WhatsApp Message Preview</span>
                            <span class="text-xs px-2 py-0.5 rounded-md bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 font-semibold">Formatted Preview</span>
                        </label>
                        <button type="button" onclick="copyModalWaText()" class="text-xs text-emerald-600 dark:text-emerald-400 hover:text-emerald-500 font-semibold flex items-center gap-1 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            <span id="copyModalBtnText">Copy Message</span>
                        </button>
                    </div>

                    <div class="p-4 rounded-xl bg-[#0b141a] border border-[#202c33] text-fg font-sans text-xs leading-relaxed max-h-72 overflow-y-auto whitespace-pre-wrap select-all" id="waMessagePreview">
                        <!-- Message text inserted here -->
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:p-5 bg-raised dark:bg-surface border-t border-line dark:border-line flex items-center justify-between gap-3 shrink-0">
                <button type="button" onclick="closeWhatsAppModal()" class="px-4 py-2.5 rounded-xl bg-surface dark:bg-surface hover:bg-raised dark:hover:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-fg text-xs font-semibold border border-line dark:border-line transition-colors cursor-pointer">
                    Cancel
                </button>

                <div class="flex items-center gap-3">
                    <button type="button" onclick="copyModalWaText()" class="px-4 py-2.5 rounded-xl bg-surface dark:bg-raised hover:bg-raised dark:hover:bg-slate-700 text-fg-muted dark:text-fg hover:text-fg dark:hover:text-fg text-xs font-semibold border border-line dark:border-line transition-colors flex items-center gap-1.5 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                        <span>Copy Text</span>
                    </button>

                    <button type="button" onclick="sendWhatsAppNow()" class="px-5 sm:px-6 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12.031 6.172c-3.181 0-5.767 2.586-5.768 5.766-.001 1.298.38 2.27 1.019 3.287l-.582 2.128 2.18-.573c.978.58 1.911.928 3.145.929 3.178 0 5.767-2.587 5.768-5.766 0-3.18-2.587-5.771-5.762-5.771zm3.392 8.244c-.144.405-.837.774-1.17.824-.312.045-.694.062-2.106-.525-1.579-.656-2.611-2.261-2.69-2.366-.079-.105-.635-.845-.635-1.611 0-.766.401-1.144.543-1.299.143-.155.312-.194.417-.194.104 0 .208.002.299.006.096.004.225-.036.35.267.13.315.442 1.082.481 1.161.039.079.065.172.013.277-.052.105-.078.17-.156.261-.078.092-.164.205-.234.276-.078.078-.16.163-.069.319.091.156.405.669.868 1.082.597.532 1.101.697 1.258.775.156.078.247.065.338-.039.091-.104.39-.456.494-.612.104-.156.208-.13.351-.078.143.052.909.429 1.065.507.156.078.26.117.299.182.039.065.039.378-.105.783z"/></svg>
                        <span>Send WhatsApp</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 3. CREATE / EDIT DIET PLAN MODAL ==================== -->
    <div id="dietPlanModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/75 backdrop-blur-sm p-3 sm:p-5 overflow-y-auto">
        <div class="diet-ui-scope bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-5xl overflow-hidden relative my-auto max-h-[92vh] flex flex-col text-fg dark:text-fg">
            <form id="dietPlanForm" method="POST" action="{{ route('app.diets.store') }}" class="flex flex-col max-h-[92vh]">
                @csrf
                <input type="hidden" name="_method" id="dietFormMethod" value="POST">

                <!-- Modal Header -->
                <div class="px-5 sm:px-7 py-4 bg-surface dark:bg-surface border-b border-line dark:border-line flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-3.5">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base sm:text-lg font-semibold text-fg dark:text-white tracking-tight" id="dietModalTitle">Create Diet Plan</h3>
                            <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Set daily targets and build meals using custom items or the food library.</p>
                        </div>
                    </div>
                    <button type="button" onclick="closeDietModal()" class="w-8 h-8 rounded-lg bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center transition-colors cursor-pointer" aria-label="Close">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Modal Form Body (Scrollable) -->
                <div class="p-5 sm:p-7 overflow-y-auto flex-1 space-y-6">
                    <!-- Section 1: Plan Details & Schedule -->
                    <div class="p-5 sm:p-6 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-5">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-fg dark:text-white">Plan Details</h4>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Saved in Diet Plans so coaches can assign it to any member from Trainer or Member views.</p>
                            </div>

                            <input type="hidden" name="is_template" id="dietInputIsTemplate" value="1">
                        </div>

                        <div class="space-y-1.5">
                            <label class="block text-xs font-medium text-fg-muted dark:text-fg-muted">
                                Plan Title <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="title" id="dietInputTitle" required placeholder="e.g. Lean Muscle Plan (2,200 kcal)"
                                   class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm font-medium focus:outline-none focus:border-accent">
                        </div>

                        <!-- Coach & Dates -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 pt-1">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted dark:text-fg-muted">Assigned Coach</label>
                                <select name="trainer_id" id="dietInputTrainerId"
                                        class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm focus:outline-none focus:border-accent">
                                    <option value="">No specific coach</option>
                                    @foreach($trainers as $t)
                                        <option value="{{ $t->id }}">{{ $t->full_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted dark:text-fg-muted">Start Date</label>
                                <input type="date" name="start_date" id="dietInputStartDate"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm focus:outline-none focus:border-accent">
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-medium text-fg-muted dark:text-fg-muted">End Date</label>
                                <input type="date" name="end_date" id="dietInputEndDate"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm focus:outline-none focus:border-accent">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Daily Targets -->
                    <div class="p-5 sm:p-6 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-semibold text-fg dark:text-white">Daily Calorie &amp; Macro Targets</h4>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Adjust manually or pick a macro split.</p>
                            </div>

                            <!-- Macro Presets Quick Buttons -->
                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="autoCalculateMacros('muscle_gain')" class="px-3 py-1.5 rounded-lg bg-surface dark:bg-surface text-fg-muted dark:text-fg border border-line dark:border-line text-xs font-medium hover:border-indigo-500 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors cursor-pointer">
                                    High Protein
                                </button>
                                <button type="button" onclick="autoCalculateMacros('fat_loss')" class="px-3 py-1.5 rounded-lg bg-surface dark:bg-surface text-fg-muted dark:text-fg border border-line dark:border-line text-xs font-medium hover:border-emerald-500 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors cursor-pointer">
                                    Fat Loss
                                </button>
                                <button type="button" onclick="autoCalculateMacros('maintenance')" class="px-3 py-1.5 rounded-lg bg-surface dark:bg-surface text-fg-muted dark:text-fg border border-line dark:border-line text-xs font-medium hover:border-amber-500 hover:text-amber-600 dark:hover:text-amber-400 transition-colors cursor-pointer">
                                    Balanced
                                </button>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="space-y-1.5">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-fg-muted dark:text-fg-muted">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    <span>Calories (kcal) <span class="text-rose-500">*</span></span>
                                </label>
                                <input type="number" name="daily_calories" id="dietInputCalories" required value="2000" min="0" max="15000"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm font-semibold tabular-nums focus:outline-none focus:border-accent">
                            </div>

                            <div class="space-y-1.5">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-fg-muted dark:text-fg-muted">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    <span>Protein (g)</span>
                                </label>
                                <input type="number" name="protein_grams" id="dietInputProtein" value="150" min="0" max="1000"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm font-semibold tabular-nums focus:outline-none focus:border-accent">
                            </div>

                            <div class="space-y-1.5">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-fg-muted dark:text-fg-muted">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    <span>Carbs (g)</span>
                                </label>
                                <input type="number" name="carbs_grams" id="dietInputCarbs" value="200" min="0" max="1500"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm font-semibold tabular-nums focus:outline-none focus:border-accent">
                            </div>

                            <div class="space-y-1.5">
                                <label class="flex items-center gap-1.5 text-xs font-medium text-fg-muted dark:text-fg-muted">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    <span>Fats (g)</span>
                                </label>
                                <input type="number" name="fat_grams" id="dietInputFats" value="55" min="0" max="1000"
                                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg text-sm font-semibold tabular-nums focus:outline-none focus:border-accent">
                            </div>
                        </div>

                        <!-- Live Planned vs Target Bar -->
                        <div class="pt-3 border-t border-line dark:border-line flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                            <div class="text-fg-muted dark:text-fg-muted">
                                Meal Sum:
                                <span id="planMealsTotalSummary" class="font-semibold text-fg dark:text-white tabular-nums ml-1">0 kcal · 0g P · 0g C · 0g F</span>
                            </div>
                            <button type="button" onclick="syncTargetsFromMeals()" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline self-start sm:self-auto cursor-pointer">
                                Match targets to meal sum
                            </button>
                        </div>
                    </div>

                    <!-- Section 3: Daily Meals Builder -->
                    <div class="space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-line dark:border-line pb-3">
                            <div>
                                <h4 class="text-sm font-semibold text-fg dark:text-white">Daily Meals</h4>
                                <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Add custom foods, pick from the library, or enter meal items directly.</p>
                            </div>

                            <div class="flex items-center gap-2 flex-wrap">
                                <button type="button" onclick="startBlankCustomPlan()" class="px-3 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-medium border border-line dark:border-line transition-colors cursor-pointer">
                                    Start Blank
                                </button>
                                <button type="button" onclick="loadStandardMealsPreset()" class="px-3 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-medium border border-line dark:border-line transition-colors cursor-pointer">
                                    Load 5-Meal Sample
                                </button>
                                <button type="button" onclick="addMealRow()" class="px-3.5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-colors cursor-pointer ops-primary">
                                    + Add Meal
                                </button>
                            </div>
                        </div>

                        <div id="mealsContainer" class="space-y-4">
                            <!-- Dynamic meal rows will be inserted here -->
                        </div>
                    </div>

                    <!-- General Guidelines / Advice -->
                    <div class="p-5 rounded-xl bg-raised dark:bg-slate-950/60 border border-line dark:border-line space-y-2">
                        <label class="block text-xs font-medium text-fg-muted dark:text-fg-muted">Guidelines &amp; Hydration Notes</label>
                        <textarea name="guidelines" id="dietInputGuidelines" rows="3" placeholder="Optional water intake, cooking oil notes, or supplement reminders..."
                                  class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-fg dark:text-fg placeholder-fg-subtle dark:placeholder-fg-subtle text-sm focus:outline-none focus:border-accent leading-relaxed"></textarea>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-5 sm:px-7 py-4 bg-surface dark:bg-surface border-t border-line dark:border-line flex items-center justify-between gap-3 shrink-0">
                    <button type="button" onclick="closeDietModal()" class="px-4 py-2.5 rounded-xl bg-raised dark:bg-surface hover:bg-slate-200 dark:hover:bg-raised text-fg-muted dark:text-fg-muted text-xs font-semibold border border-line dark:border-line transition-colors cursor-pointer">
                        Cancel
                    </button>

                    <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all flex items-center gap-2 cursor-pointer ops-primary">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span id="dietSubmitBtnText">Save Diet Plan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== 4. FOOD PICKER & CUSTOM FOOD MODAL ==================== -->
    <div id="mealFoodPickerModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/75 backdrop-blur-sm p-3 sm:p-5 overflow-y-auto">
        <div class="diet-ui-scope bg-surface dark:bg-surface border border-line dark:border-line rounded-xl w-full max-w-2xl overflow-hidden relative my-auto max-h-[88vh] flex flex-col text-fg dark:text-fg">
            <div class="px-5 py-4 bg-raised dark:bg-surface border-b border-line dark:border-line flex items-center justify-between shrink-0 gap-3">
                <div>
                    <h4 class="text-sm font-semibold text-fg dark:text-white">Add Food to Meal</h4>
                    <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5" id="pickerTargetMealName">Adding items to meal</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="togglePickerCustomForm()" class="px-3 py-1.5 rounded-lg bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-colors cursor-pointer ops-primary">
                        + Custom Food
                    </button>
                    <button type="button" onclick="closeMealFoodPicker()" class="w-8 h-8 rounded-lg bg-raised dark:bg-raised text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center text-xs font-semibold cursor-pointer">✕</button>
                </div>
            </div>

            <!-- Inline Custom Food Form inside Picker (shown on demand or when food missing) -->
            <div id="pickerCustomFoodBox" class="hidden p-4 sm:p-5 bg-indigo-50/40 dark:bg-slate-950/90 border-b border-line dark:border-line space-y-3 shrink-0">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-fg dark:text-white">New Custom Food Item</span>
                    <button type="button" onclick="togglePickerCustomForm(false)" class="text-xs text-fg-muted hover:text-fg dark:hover:text-fg cursor-pointer">Close</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <div class="sm:col-span-1">
                        <input type="text" id="pickerCustomName" placeholder="Food name (e.g. Ragi Malt)" class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <input type="text" id="pickerCustomServing" placeholder="Serving (e.g. 1 glass / 150g)" class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <select id="pickerCustomDietType" class="w-full px-3 py-2 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg focus:outline-none focus:border-accent">
                            <option value="veg">Veg</option>
                            <option value="eggetarian">Eggetarian</option>
                            <option value="non_veg">Non-Veg</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 items-end">
                    <div>
                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Calories</label>
                        <input type="number" id="pickerCustomCalories" min="0" placeholder="250" class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold tabular-nums text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Protein (g)</label>
                        <input type="number" id="pickerCustomProtein" min="0" step="0.5" placeholder="12" class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold tabular-nums text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Carbs (g)</label>
                        <input type="number" id="pickerCustomCarbs" min="0" step="0.5" placeholder="30" class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold tabular-nums text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <div>
                        <label class="block text-xs text-fg-muted dark:text-fg-muted mb-1">Fats (g)</label>
                        <input type="number" id="pickerCustomFats" min="0" step="0.5" placeholder="6" class="w-full px-3 py-1.5 rounded-lg bg-surface dark:bg-surface border border-line dark:border-line text-xs font-semibold tabular-nums text-fg dark:text-fg focus:outline-none focus:border-accent">
                    </div>
                    <button type="button" onclick="submitPickerCustomFood()" class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                        Add Item
                    </button>
                </div>
            </div>

            <div class="p-3.5 bg-raised dark:bg-slate-950/50 border-b border-line dark:border-line shrink-0 space-y-2.5">
                <input type="text" id="pickerSearchInput" oninput="filterMealPickerList()" 
                       placeholder="Search food library (eggs, chicken, paneer, soya, oats, rice, roti, whey)..."
                       class="w-full px-3.5 py-2.5 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg placeholder-fg-subtle focus:outline-none focus:border-accent">
                <div class="flex items-center gap-1.5 overflow-x-auto text-xs pb-1">
                    <button type="button" onclick="setPickerCategory('all')" class="picker-cat-btn px-2.5 py-1 rounded-md font-semibold bg-amber-500 text-slate-950 shrink-0" data-cat="all">All ({{ count($indianFoods ?? []) }})</button>
                    <button type="button" onclick="setPickerCategory('non_veg_protein')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="non_veg_protein">Non-Veg</button>
                    <button type="button" onclick="setPickerCategory('veg_protein')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="veg_protein">Veg Protein</button>
                    <button type="button" onclick="setPickerCategory('carbs')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="carbs">Carbs</button>
                    <button type="button" onclick="setPickerCategory('fats')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="fats">Fats</button>
                    <button type="button" onclick="setPickerCategory('fruits')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="fruits">Fruits</button>
                    <button type="button" onclick="setPickerCategory('salads')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="salads">Fiber &amp; Sabzi</button>
                    <button type="button" onclick="setPickerCategory('gym_meals')" class="picker-cat-btn px-2.5 py-1 rounded-md font-medium text-fg-muted dark:text-fg-muted hover:bg-raised dark:hover:bg-raised shrink-0" data-cat="gym_meals">Meals &amp; Shakes</button>
                </div>
            </div>

            <div class="p-4 overflow-y-auto flex-1 space-y-2" id="pickerFoodListContainer">
                <!-- Dynamically populated from JS -->
            </div>

            <!-- Sticky Bottom Toolbar -->
            <div class="px-5 py-3.5 bg-raised dark:bg-surface border-t border-line dark:border-line flex items-center justify-between shrink-0">
                <div class="text-xs font-medium text-fg-muted dark:text-fg-muted tabular-nums" id="pickerActiveMealSummary">
                    <span>Click <strong>+ Add</strong> on any item or add a custom food.</span>
                </div>
                <button type="button" onclick="closeMealFoodPicker()" class="px-5 py-2 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all cursor-pointer ops-primary">
                    Done
                </button>
            </div>
        </div>
    </div>

    <!-- Toast Notification for Quick Copy -->
    <div id="copyToast" class="fixed bottom-6 right-6 z-50 px-4 py-3 rounded-xl bg-emerald-600 text-white text-xs font-semibold flex items-center gap-2 transform translate-y-20 opacity-0 transition-all duration-300 pointer-events-none">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <span>Action completed successfully!</span>
    </div>

    <!-- ==================== AI DIET GENERATOR MODAL ==================== -->
    <div id="aiDietModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/70 backdrop-blur-sm p-3 sm:p-4 overflow-y-auto">
        <div class="relative w-full max-w-4xl bg-surface dark:bg-surface border border-line dark:border-line rounded-xl my-auto overflow-hidden max-h-[90vh] flex flex-col text-fg dark:text-fg">
            @php
                $globalGeminiKey = \App\Models\Setting::getGlobal('gemini_api_key') ?: config('services.gemini.api_key');
                $globalGeminiModel = \App\Models\Setting::getGlobal('gemini_model') ?: config('services.gemini.model', 'gemini-1.5-flash');
                $hasGeminiConfigured = !empty($globalGeminiKey);
            @endphp
            <div class="px-4 sm:px-6 py-4 border-b border-line dark:border-line bg-raised dark:bg-slate-950/70 flex items-center justify-between shrink-0">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <h3 class="text-base font-semibold text-fg dark:text-white tracking-tight">Quick Plan Generator</h3>
                            @if($hasGeminiConfigured)
                                <span class="px-2 py-0.5 rounded-full bg-indigo-500/10 text-indigo-600 dark:text-indigo-300 border border-indigo-500/20 text-xs font-medium">
                                    {{ $globalGeminiModel }}
                                </span>
                            @endif
                        </div>
                        <p class="text-xs text-fg-muted dark:text-fg-muted mt-0.5">Calculate BMR, daily calories, macros, and meal timings from member stats.</p>
                    </div>
                </div>
                <button type="button" onclick="closeAiDietModal()" class="w-8 h-8 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white flex items-center justify-center text-sm font-semibold transition-colors cursor-pointer">
                    ✕
                </button>
            </div>

            <div class="p-4 sm:p-6 overflow-y-auto flex-1 space-y-6">
                <!-- FORM VIEW -->
                <div id="aiDietFormView" class="space-y-6">
                    <form id="aiDietForm" onsubmit="submitAiDietGeneration(event)" class="space-y-5">
                        <div class="p-4 rounded-xl bg-raised dark:bg-slate-950/70 border border-line dark:border-line flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                            <span class="text-xs font-medium text-fg-muted dark:text-fg-muted">Member (Optional)</span>
                            <div class="w-full sm:w-72">
                                <select id="aiMemberSelect" onchange="handleAiMemberSelect()" class="w-full px-3 py-2 rounded-xl bg-surface dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-fg focus:outline-none focus:border-accent">
                                    <option value="">Manual entry</option>
                                    @foreach($members as $m)
                                        <option value="{{ $m->id }}">{{ $m->full_name }} ({{ $m->phone ?? 'No Phone' }})</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">1. Member Profile &amp; Metrics</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Name *</label>
                                    <input type="text" id="aiInputName" required placeholder="e.g. Rahul Sharma" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Age *</label>
                                    <input type="number" id="aiInputAge" required value="26" min="12" max="90" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Gender *</label>
                                    <select id="aiInputGender" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                        <option value="male">Male</option>
                                        <option value="female">Female</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Height (cm) *</label>
                                    <input type="number" id="aiInputHeight" required value="172" min="100" max="250" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Weight (kg) *</label>
                                    <input type="number" id="aiInputWeight" required value="72" min="30" max="250" step="0.5" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                </div>
                            </div>
                        </div>

                        <div class="space-y-3 pt-2 border-t border-line dark:border-line">
                            <h4 class="text-xs font-semibold text-indigo-600 dark:text-indigo-400">2. Fitness Goals &amp; Training</h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Fitness Goal *</label>
                                    <select id="aiInputGoal" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                        <option value="muscle_gain">Muscle Gain &amp; Hypertrophy</option>
                                        <option value="fat_loss">Fat Loss &amp; Leaning</option>
                                        <option value="weight_loss">Weight Loss</option>
                                        <option value="weight_gain">Weight Gain (Clean Bulk)</option>
                                        <option value="maintenance">Maintenance</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Activity Level *</label>
                                    <select id="aiInputActivity" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                        <option value="moderately_active">Moderately Active (Gym 3-5 days)</option>
                                        <option value="very_active">Very Active (Gym 6-7 days)</option>
                                        <option value="lightly_active">Lightly Active (Gym 1-3 days)</option>
                                        <option value="sedentary">Sedentary (Desk job / minimal)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Workout Timing *</label>
                                    <select id="aiInputWorkoutTime" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                        <option value="morning">Morning (08:00 AM - 11:00 AM)</option>
                                        <option value="evening">Evening (05:00 PM - 08:00 PM)</option>
                                        <option value="early_morning">Early Morning (06:00 AM - 08:00 AM)</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-fg-muted dark:text-fg-muted mb-1">Diet Preference *</label>
                                    <select id="aiInputDietPref" class="w-full px-3 py-2 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-xs text-fg dark:text-white focus:border-accent focus:outline-none">
                                        <option value="vegetarian">Vegetarian (Lacto / High-Protein)</option>
                                        <option value="non_vegetarian">Non-Vegetarian (Eggs, Chicken, Fish)</option>
                                        <option value="eggetarian">Eggetarian (Eggs + Veg Dairy)</option>
                                        <option value="vegan">Vegan (100% Plant-Based)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 border-t border-line dark:border-line flex items-center justify-end gap-3">
                            <button type="button" onclick="closeAiDietModal()" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold transition-colors cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-6 py-2.5 rounded-xl bg-accent hover:bg-accent text-accent-on text-xs font-semibold transition-all cursor-pointer ops-primary">
                                Generate Plan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- LOADING VIEW -->
                <div id="aiDietLoadingView" class="hidden py-16 flex flex-col items-center justify-center text-center space-y-4">
                    <div class="w-12 h-12 rounded-full border-2 border-indigo-500/30 border-t-indigo-500 animate-spin"></div>
                    <div class="space-y-1">
                        <h4 class="text-sm font-semibold text-fg dark:text-white">Generating Diet Plan...</h4>
                        <p class="text-xs text-fg-muted dark:text-fg-muted">Calculating BMR, macro split, and meal schedule.</p>
                    </div>
                </div>

                <!-- AI RESULTS VIEW -->
                <div id="aiDietResultView" class="hidden space-y-5">
                    <div class="p-5 rounded-xl border border-purple-200 dark:border-purple-500/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-surface border-line">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <span id="aiResBadge" class="px-2 py-0.5 rounded-full bg-indigo-500/15 text-indigo-700 dark:text-indigo-300 border border-indigo-500/30 text-xs font-semibold">Generated Plan</span>
                                <h3 id="aiResPlanTitle" class="text-base font-semibold text-fg dark:text-white tracking-tight"></h3>
                            </div>
                            <div class="flex items-center gap-3 text-xs text-fg-muted dark:text-fg-muted mt-1">
                                <span id="aiResBmr" class="font-mono"></span>
                                <span>•</span>
                                <span id="aiResTdee" class="font-mono"></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" id="aiBtnCopyText" onclick="copyAiDietWhatsAppText()" class="px-3.5 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg text-xs font-semibold border border-line dark:border-line flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                <span>Copy WhatsApp Text</span>
                            </button>
                            <button type="button" id="aiBtnSavePlan" onclick="saveCurrentAiDietPlan()" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold flex items-center gap-1.5 transition-all cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>Save to Member Plans</span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2.5">
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Calories</span>
                            <span id="aiResCalories" class="text-sm font-semibold text-amber-600 dark:text-amber-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Protein</span>
                            <span id="aiResProtein" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Carbs</span>
                            <span id="aiResCarbs" class="text-sm font-semibold text-emerald-600 dark:text-emerald-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Fats</span>
                            <span id="aiResFat" class="text-sm font-semibold text-rose-600 dark:text-rose-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Fiber</span>
                            <span id="aiResFiber" class="text-sm font-semibold text-teal-600 dark:text-teal-400 font-mono mt-0.5 block"></span>
                        </div>
                        <div class="p-3 rounded-xl bg-raised dark:bg-surface border border-line dark:border-line text-center">
                            <span class="text-xs text-fg-muted dark:text-fg-muted font-semibold block">Hydration</span>
                            <span id="aiResWater" class="text-sm font-semibold text-cyan-600 dark:text-cyan-400 font-mono mt-0.5 block"></span>
                        </div>
                    </div>

                    <div class="space-y-3">
                        <h4 class="text-xs font-semibold text-fg dark:text-white">Scheduled Meals</h4>
                        <div id="aiResMealsList" class="space-y-3"></div>
                    </div>

                    <div class="pt-3 border-t border-line dark:border-line flex items-center justify-between">
                        <button type="button" onclick="document.getElementById('aiDietResultView').classList.add('hidden'); document.getElementById('aiDietFormView').classList.remove('hidden');" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted text-xs font-semibold transition-colors cursor-pointer">
                            &larr; Adjust Parameters
                        </button>
                        <button type="button" onclick="closeAiDietModal()" class="px-4 py-2 rounded-xl bg-raised dark:bg-raised hover:bg-slate-200 dark:hover:bg-slate-700 text-fg-muted dark:text-fg-muted hover:text-fg dark:hover:text-white text-xs font-semibold transition-colors cursor-pointer">
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

        function escapeHtml(str) {
            return String(str ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#39;');
        }

        function buildWhatsAppMessage(plan, recipientName, gymName) {
            let text = "*DIET PLAN*\n";
            text += "--------------------\n";
            text += "*Gym:* " + (gymName || 'GymDeskHub') + "\n";
            if (recipientName && recipientName !== 'Template Plan') {
                text += "*Member:* " + recipientName + "\n";
            }
            text += "*Plan:* " + plan.title + "\n";
            if (plan.daily_calories) {
                text += "*Daily Target:* " + plan.daily_calories + " kcal\n";
            }
            if (plan.protein_grams || plan.carbs_grams || plan.fat_grams) {
                text += "*Macros:* P " + (plan.protein_grams || 0) + "g | C " + (plan.carbs_grams || 0) + "g | F " + (plan.fat_grams || 0) + "g\n";
            }
            if (plan.start_date) {
                text += "*Dates:* " + plan.start_date + (plan.end_date ? " to " + plan.end_date : "") + "\n";
            }
            text += "--------------------\n\n";

            if (plan.meals && plan.meals.length > 0) {
                text += "*DAILY MEALS:*\n\n";
                plan.meals.forEach((m, idx) => {
                    const typeName = (m.meal_type || 'meal').replace('_', ' ').toUpperCase();
                    const timeStr = m.recommended_time ? " (" + formatTime(m.recommended_time) + ")" : "";
                    const calStr = m.calories ? " [" + m.calories + " kcal]" : "";

                    text += "*" + (idx + 1) + ". " + (m.meal_name || typeName) + "*" + timeStr + calStr + "\n";
                    if (m.items_description) {
                        text += m.items_description.trim() + "\n";
                    }
                    text += "\n";
                });
            }

            if (plan.guidelines) {
                text += "--------------------\n";
                text += "*Guidelines:*\n";
                text += plan.guidelines.trim() + "\n";
                text += "--------------------\n\n";
            }

            text += "Sent by " + (gymName || 'GymDeskHub');
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

        function openDietModal() {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "{{ route('app.diets.store') }}";
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Create Diet Plan';
            document.getElementById('dietSubmitBtnText').innerText = 'Save Diet Plan';
            document.getElementById('dietInputIsTemplate').value = '1';
            document.getElementById('mealsContainer').innerHTML = '';
            mealCounter = 0;
            for (let k in mealRowFoods) delete mealRowFoods[k];

            // Start with 1 clean customizable meal row (user can build custom or click Load 5-Meal Sample)
            addMealRow();

            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        function startBlankCustomPlan() {
            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;
            for (let k in mealRowFoods) delete mealRowFoods[k];
            const firstRowId = addMealRow();
            toggleCustomFoodComposer(firstRowId, true);
            showToast('Started blank custom meal builder');
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
            const mealName = meal ? (meal.meal_name || 'Meal') : (mealCounter === 1 ? 'Breakfast' : (mealCounter === 2 ? 'Mid-Morning Snack' : (mealCounter === 3 ? 'Lunch' : (mealCounter === 4 ? 'Pre/Post Workout' : 'Dinner'))));
            const recommendedTime = meal ? (meal.recommended_time || '') : (mealCounter === 1 ? '08:30' : (mealCounter === 2 ? '11:30' : (mealCounter === 3 ? '13:30' : (mealCounter === 4 ? '17:30' : '20:30'))));
            const calories = meal && meal.calories != null ? meal.calories : '';
            const protein = meal && meal.protein_grams != null ? meal.protein_grams : '';
            const carbs = meal && meal.carbs_grams != null ? meal.carbs_grams : '';
            const fat = meal && meal.fat_grams != null ? meal.fat_grams : '';
            const items = meal ? (meal.items_description || '') : '';
            const safeMealName = escapeHtml(mealName);

            const html = `
                <div class="p-4 sm:p-5 rounded-2xl bg-slate-50/90 dark:bg-slate-950/80 border border-slate-200 dark:border-slate-800 relative group transition-colors space-y-4" id="${rowId}">
                    <div class="flex flex-wrap items-center justify-between gap-2.5 pb-3 border-b border-slate-200/80 dark:border-slate-800">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-6 h-6 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-xs flex items-center justify-center font-semibold tabular-nums shrink-0">${container.children.length + 1}</span>
                            <span class="text-sm font-semibold text-slate-900 dark:text-white truncate" id="${rowId}_header_title">${safeMealName}</span>
                            <span id="${rowId}_macro_pill" class="text-[11px] font-medium px-2.5 py-0.5 rounded-md bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-300 tabular-nums shrink-0">0 kcal</span>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button" onclick="toggleCustomFoodComposer('${rowId}')"
                                    class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                                + Custom Food
                            </button>
                            <button type="button" onclick="openMealFoodPicker('${rowId}')"
                                    class="px-3 py-1.5 rounded-lg bg-amber-500/15 hover:bg-amber-500/25 text-amber-700 dark:text-amber-300 text-xs font-semibold border border-amber-500/30 transition-colors cursor-pointer">
                                Food Library
                            </button>
                            <button type="button" onclick="removeMealRow('${rowId}')"
                                    class="px-2 py-1.5 rounded-lg text-xs text-rose-500 hover:text-rose-600 hover:bg-rose-500/10 font-medium transition-colors cursor-pointer">Remove</button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-6 gap-3">
                        <div class="sm:col-span-2">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Meal Type</label>
                            <select name="meals[${mealCounter}][meal_type]" onchange="onMealTypeChange('${rowId}', this.value)" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="breakfast" ${mealType === 'breakfast' ? 'selected' : ''}>Breakfast</option>
                                <option value="morning_snack" ${mealType === 'morning_snack' ? 'selected' : ''}>Morning Snack</option>
                                <option value="lunch" ${mealType === 'lunch' ? 'selected' : ''}>Lunch</option>
                                <option value="evening_snack" ${mealType === 'evening_snack' ? 'selected' : ''}>Evening Snack</option>
                                <option value="dinner" ${mealType === 'dinner' ? 'selected' : ''}>Dinner</option>
                                <option value="post_workout" ${mealType === 'post_workout' ? 'selected' : ''}>Post Workout</option>
                            </select>
                        </div>

                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Meal Title *</label>
                            <input type="text" name="meals[${mealCounter}][meal_name]" id="${rowId}_title" value="${safeMealName}" required oninput="document.getElementById('${rowId}_header_title').innerText = this.value || 'Meal'" placeholder="e.g. Pre-Workout Meal or Power Breakfast" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Time</label>
                            <input type="time" name="meals[${mealCounter}][recommended_time]" value="${escapeHtml(recommendedTime)}" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white tabular-nums focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Calories (kcal)</label>
                            <input type="number" name="meals[${mealCounter}][calories]" id="${rowId}_cals" value="${calories}" placeholder="0" min="0" oninput="onManualMealMacroInput('${rowId}')" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-rose-600 dark:text-rose-400 mb-1.5">Protein (g)</label>
                            <input type="number" name="meals[${mealCounter}][protein_grams]" id="${rowId}_protein" value="${protein}" placeholder="0" min="0" oninput="onManualMealMacroInput('${rowId}')" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-amber-600 dark:text-amber-400 mb-1.5">Carbs (g)</label>
                            <input type="number" name="meals[${mealCounter}][carbs_grams]" id="${rowId}_carbs" value="${carbs}" placeholder="0" min="0" oninput="onManualMealMacroInput('${rowId}')" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-cyan-600 dark:text-cyan-400 mb-1.5">Fats (g)</label>
                            <input type="number" name="meals[${mealCounter}][fat_grams]" id="${rowId}_fat" value="${fat}" placeholder="0" min="0" oninput="onManualMealMacroInput('${rowId}')" class="w-full px-3 py-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>

                    <!-- Inline Custom Food Composer -->
                    <div id="${rowId}_custom_composer" class="hidden p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-indigo-200 dark:border-indigo-500/40 space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">Add Custom Food Item to Meal</span>
                            <button type="button" onclick="toggleCustomFoodComposer('${rowId}', false)" class="text-xs text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 cursor-pointer">Close</button>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <input type="text" id="${rowId}_cf_name" placeholder="Food name (e.g. Grilled Tofu)" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            <input type="text" id="${rowId}_cf_serving" placeholder="Serving (e.g. 150g / 1 bowl)" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            <select id="${rowId}_cf_diet" class="w-full px-3 py-2 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                                <option value="veg">Veg</option>
                                <option value="eggetarian">Eggetarian</option>
                                <option value="non_veg">Non-Veg</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5 items-end">
                            <div>
                                <label class="block text-[11px] font-medium text-slate-500 mb-1">Calories</label>
                                <input type="number" id="${rowId}_cf_cals" min="0" placeholder="220" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-rose-500 mb-1">Protein (g)</label>
                                <input type="number" id="${rowId}_cf_p" min="0" step="0.5" placeholder="18" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-amber-500 mb-1">Carbs (g)</label>
                                <input type="number" id="${rowId}_cf_c" min="0" step="0.5" placeholder="12" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-medium text-cyan-500 mb-1">Fats (g)</label>
                                <input type="number" id="${rowId}_cf_f" min="0" step="0.5" placeholder="8" class="w-full px-2.5 py-1.5 rounded-lg bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 text-xs font-semibold tabular-nums text-slate-900 dark:text-white focus:outline-none focus:border-indigo-500">
                            </div>
                            <button type="button" onclick="addCustomFoodToMeal('${rowId}')" class="px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                                Add Item
                            </button>
                        </div>
                    </div>

                    <!-- Selected Food Items & Free-Text Meal Entry -->
                    <div class="space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400">Food Items &amp; Portions</label>
                            <span class="text-[11px] text-slate-400">Add custom foods, pick from library, or write your own menu below</span>
                        </div>

                        <div id="${rowId}_foods_container" class="space-y-2">
                            <!-- Populated by renderMealFoods -->
                        </div>

                        <div class="pt-1">
                            <label class="block text-xs font-medium text-slate-600 dark:text-slate-400 mb-1.5">Custom Meal Items / Prep Notes (Free Text)</label>
                            <textarea id="${rowId}_custom_notes" rows="2" oninput="syncMealDescription('${rowId}')" placeholder="Write any custom meal directly (e.g. 150g grilled paneer + 2 multigrain rotis + mint chutney) or prep instructions..."
                                      class="w-full px-3.5 py-2.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 text-xs text-slate-800 dark:text-slate-200 placeholder-slate-400 focus:outline-none focus:border-indigo-500 leading-relaxed"></textarea>
                        </div>

                        <!-- Synced items description for form submit & WhatsApp -->
                        <textarea name="meals[${mealCounter}][items_description]" id="${rowId}_items" class="hidden"></textarea>
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
                            diet_type: f.diet_type,
                            isCustom: false
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

            return rowId;
        }

        function toggleCustomFoodComposer(rowId, forceState = null) {
            const box = document.getElementById(rowId + '_custom_composer');
            if (!box) return;
            const show = forceState !== null ? forceState : box.classList.contains('hidden');
            box.classList.toggle('hidden', !show);
            if (show) {
                document.getElementById(rowId + '_cf_name')?.focus();
            }
        }

        function addCustomFoodToMeal(rowId) {
            const nameEl = document.getElementById(rowId + '_cf_name');
            const servingEl = document.getElementById(rowId + '_cf_serving');
            const dietEl = document.getElementById(rowId + '_cf_diet');
            const calsEl = document.getElementById(rowId + '_cf_cals');
            const pEl = document.getElementById(rowId + '_cf_p');
            const cEl = document.getElementById(rowId + '_cf_c');
            const fEl = document.getElementById(rowId + '_cf_f');

            const name = (nameEl?.value || '').trim();
            if (!name) {
                alert('Please enter a food name.');
                nameEl?.focus();
                return;
            }

            const serving = (servingEl?.value || '').trim() || '1 serving';
            const calories = parseFloat(calsEl?.value) || 0;
            const protein = parseFloat(pEl?.value) || 0;
            const carbs = parseFloat(cEl?.value) || 0;
            const fat = parseFloat(fEl?.value) || 0;
            const dietType = dietEl?.value || 'veg';

            if (!mealRowFoods[rowId]) {
                mealRowFoods[rowId] = [];
            }

            mealRowFoods[rowId].push({
                foodId: 'custom_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                name,
                serving,
                qty: 1,
                calories,
                protein,
                carbs,
                fat,
                diet_type: dietType,
                isCustom: true
            });

            if (nameEl) nameEl.value = '';
            if (servingEl) servingEl.value = '';
            if (calsEl) calsEl.value = '';
            if (pEl) pEl.value = '';
            if (cEl) cEl.value = '';
            if (fEl) fEl.value = '';

            toggleCustomFoodComposer(rowId, false);
            renderMealFoods(rowId);
            showToast('Custom food added to meal.');
        }

        function removeMealRow(rowId) {
            const el = document.getElementById(rowId);
            if (el) el.remove();
            delete mealRowFoods[rowId];
            updatePlanTotalCalories();
        }

        function onMealTypeChange(rowId, type) {
            const typeNames = {
                'breakfast': 'Breakfast',
                'morning_snack': 'Mid-Morning Snack',
                'lunch': 'Lunch',
                'evening_snack': 'Evening Snack',
                'dinner': 'Dinner',
                'post_workout': 'Post Workout'
            };
            const titleInput = document.getElementById(rowId + '_title');
            if (titleInput && (!titleInput.value || Object.values(typeNames).includes(titleInput.value.trim()))) {
                titleInput.value = typeNames[type] || 'Meal';
                document.getElementById(rowId + '_header_title').innerText = titleInput.value;
            }
        }

        function renderMealFoods(rowId) {
            const container = document.getElementById(rowId + '_foods_container');
            if (!container) return;

            const items = mealRowFoods[rowId] || [];

            if (items.length === 0) {
                container.innerHTML = `
                    <div class="p-3 rounded-xl border border-dashed border-slate-300 dark:border-slate-700 bg-white/60 dark:bg-slate-900/40 flex flex-wrap items-center justify-between gap-2 text-xs text-slate-500 dark:text-slate-400">
                        <span>No food items added yet — add your own or type freely below.</span>
                        <div class="flex items-center gap-3">
                            <button type="button" onclick="toggleCustomFoodComposer('${rowId}', true)" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline cursor-pointer">
                                + Add Custom Food
                            </button>
                            <button type="button" onclick="openMealFoodPicker('${rowId}')" class="text-amber-600 dark:text-amber-400 font-semibold hover:underline cursor-pointer">
                                Pick from Food Library
                            </button>
                        </div>
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
                const safeName = escapeHtml(item.name);
                const safeServing = escapeHtml(item.serving);
                const safeFoodId = escapeHtml(item.foodId);

                html += `
                    <div class="flex items-center justify-between p-2.5 sm:p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 gap-3 shadow-xs">
                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-2.5 h-2.5 rounded-full ${dotColor} shrink-0"></span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="text-xs font-semibold text-slate-900 dark:text-white truncate">${safeName}</span>
                                    <span class="text-[11px] text-slate-400">(${safeServing})</span>
                                    ${item.isCustom ? '<span class="px-1.5 py-0.5 rounded bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-[10px] font-medium">Custom</span>' : ''}
                                </div>
                                <div class="flex items-center gap-2.5 text-[11px] tabular-nums text-slate-500 dark:text-slate-400 mt-0.5">
                                    <span class="text-amber-600 dark:text-amber-400 font-semibold">${totCals} kcal</span>
                                    <span>P: <strong class="text-rose-500 dark:text-rose-400">${totP}g</strong></span>
                                    <span>C: <strong class="text-amber-500 dark:text-amber-400">${totC}g</strong></span>
                                    <span>F: <strong class="text-cyan-500 dark:text-cyan-400">${totF}g</strong></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            <div class="flex items-center border border-slate-200 dark:border-slate-700 rounded-lg overflow-hidden bg-slate-50 dark:bg-slate-800 text-xs font-semibold">
                                <button type="button" onclick="changeMealFoodQty('${rowId}', '${safeFoodId}', -1)" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">-</button>
                                <span class="px-2 py-1 tabular-nums text-slate-900 dark:text-white">${item.qty}x</span>
                                <button type="button" onclick="changeMealFoodQty('${rowId}', '${safeFoodId}', 1)" class="px-2 py-1 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-200 cursor-pointer">+</button>
                            </div>
                            <button type="button" onclick="removeMealFood('${rowId}', '${safeFoodId}')" class="px-2 py-1 rounded-lg bg-rose-500/10 text-rose-500 hover:bg-rose-500 hover:text-white text-xs font-medium transition-colors cursor-pointer" title="Remove item">Remove</button>
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
                formattedLines.push(`${qtyPrefix}${item.name} (${item.serving}) — ${cals} kcal · ${p}g P`);
            });

            if (customNotes) {
                if (list.length > 0) {
                    formattedLines.push(`• Note: ${customNotes}`);
                } else {
                    formattedLines.push(customNotes);
                }
            }

            const descriptionText = formattedLines.join('\n');
            const itemsInput = document.getElementById(rowId + '_items');
            if (itemsInput) {
                itemsInput.value = descriptionText;
            }

            const calsInput = document.getElementById(rowId + '_cals');
            const pInput = document.getElementById(rowId + '_protein');
            const cInput = document.getElementById(rowId + '_carbs');
            const fInput = document.getElementById(rowId + '_fat');

            if (list.length > 0) {
                if (calsInput) calsInput.value = totalCals;
                if (pInput) pInput.value = Math.round(totalP);
                if (cInput) cInput.value = Math.round(totalC);
                if (fInput) fInput.value = Math.round(totalF);
            }

            onManualMealMacroInput(rowId);
        }

        function onManualMealMacroInput(rowId) {
            const cals = parseInt(document.getElementById(rowId + '_cals')?.value) || 0;
            const p = parseInt(document.getElementById(rowId + '_protein')?.value) || 0;
            const c = parseInt(document.getElementById(rowId + '_carbs')?.value) || 0;
            const f = parseInt(document.getElementById(rowId + '_fat')?.value) || 0;

            const pill = document.getElementById(rowId + '_macro_pill');
            if (pill) {
                pill.textContent = `${cals} kcal · ${p}g P · ${c}g C · ${f}g F`;
            }

            updatePlanTotalCalories();
        }

        function getPlanMealTotals() {
            let totalCals = 0;
            let totalP = 0;
            let totalC = 0;
            let totalF = 0;

            document.querySelectorAll('input[id$="_cals"]').forEach(inp => { totalCals += parseInt(inp.value) || 0; });
            document.querySelectorAll('input[id$="_protein"]').forEach(inp => { totalP += parseInt(inp.value) || 0; });
            document.querySelectorAll('input[id$="_carbs"]').forEach(inp => { totalC += parseInt(inp.value) || 0; });
            document.querySelectorAll('input[id$="_fat"]').forEach(inp => { totalF += parseInt(inp.value) || 0; });

            return { totalCals, totalP, totalC, totalF };
        }

        function updatePlanTotalCalories() {
            const { totalCals, totalP, totalC, totalF } = getPlanMealTotals();
            const summaryEl = document.getElementById('planMealsTotalSummary');
            if (summaryEl) {
                summaryEl.textContent = `${totalCals} kcal · ${totalP}g P · ${totalC}g C · ${totalF}g F`;
            }
        }

        function syncTargetsFromMeals() {
            const { totalCals, totalP, totalC, totalF } = getPlanMealTotals();
            if (totalCals === 0 && totalP === 0 && totalC === 0 && totalF === 0) {
                showToast('Add meal macros first before syncing targets.');
                return;
            }
            document.getElementById('dietInputCalories').value = totalCals;
            document.getElementById('dietInputProtein').value = totalP;
            document.getElementById('dietInputCarbs').value = totalC;
            document.getElementById('dietInputFats').value = totalF;
            showToast('Daily targets matched to meal sum.');
        }

        function loadStandardMealsPreset() {
            const container = document.getElementById('mealsContainer');
            container.innerHTML = '';
            mealCounter = 0;
            for (let k in mealRowFoods) delete mealRowFoods[k];

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
                    meal_name: 'Mid-Morning Fruit & Tea',
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
                    meal_name: 'Pre-Workout Fuel',
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
            showToast('Loaded 5-meal sample day.');
        }

        function editDietPlan(plan) {
            document.getElementById('dietPlanForm').reset();
            document.getElementById('dietPlanForm').action = "/app/diets/" + plan.id;
            document.getElementById('dietFormMethod').value = 'POST';
            document.getElementById('dietModalTitle').innerText = 'Edit Diet Plan: ' + plan.title;
            document.getElementById('dietSubmitBtnText').innerText = 'Update Diet Plan';

            document.getElementById('dietInputTitle').value = plan.title || '';
            document.getElementById('dietInputIsTemplate').value = '1';

            document.getElementById('dietInputTrainerId').value = plan.trainer_id || '';
            document.getElementById('dietInputCalories').value = plan.daily_calories || '';
            document.getElementById('dietInputProtein').value = plan.protein_grams || '';
            document.getElementById('dietInputCarbs').value = plan.carbs_grams || '';
            document.getElementById('dietInputFats').value = plan.fat_grams || '';
            document.getElementById('dietInputStartDate').value = plan.start_date_iso || plan.start_date || '';
            document.getElementById('dietInputEndDate').value = plan.end_date_iso || plan.end_date || '';
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

            updatePlanTotalCalories();
            document.getElementById('dietPlanModal').classList.remove('hidden');
        }

        // ==================== FOOD PICKER FOR MEAL BUILDER ====================
        let pickerCurrentCategory = 'all';

        function openMealFoodPicker(rowId) {
            activeMealRowIdForPicker = rowId;
            const mealName = document.getElementById(rowId + '_title')?.value || 'Current Meal';
            document.getElementById('pickerTargetMealName').innerText = 'Adding items to: ' + mealName;
            document.getElementById('pickerSearchInput').value = '';
            togglePickerCustomForm(false);
            setPickerCategory('all');
            document.getElementById('mealFoodPickerModal').classList.remove('hidden');
        }

        function closeMealFoodPicker() {
            document.getElementById('mealFoodPickerModal').classList.add('hidden');
            togglePickerCustomForm(false);
            activeMealRowIdForPicker = null;
        }

        function togglePickerCustomForm(forceState = null, prefillName = '') {
            const box = document.getElementById('pickerCustomFoodBox');
            if (!box) return;
            const show = forceState !== null ? forceState : box.classList.contains('hidden');
            box.classList.toggle('hidden', !show);
            if (show) {
                const nameInput = document.getElementById('pickerCustomName');
                if (nameInput && prefillName) {
                    nameInput.value = prefillName;
                }
                nameInput?.focus();
            }
        }

        function submitPickerCustomFood() {
            if (!activeMealRowIdForPicker) return;
            const nameEl = document.getElementById('pickerCustomName');
            const servingEl = document.getElementById('pickerCustomServing');
            const dietEl = document.getElementById('pickerCustomDietType');
            const calsEl = document.getElementById('pickerCustomCalories');
            const pEl = document.getElementById('pickerCustomProtein');
            const cEl = document.getElementById('pickerCustomCarbs');
            const fEl = document.getElementById('pickerCustomFats');

            const name = (nameEl?.value || '').trim();
            if (!name) {
                alert('Please enter a food name.');
                nameEl?.focus();
                return;
            }

            const serving = (servingEl?.value || '').trim() || '1 serving';
            const calories = parseFloat(calsEl?.value) || 0;
            const protein = parseFloat(pEl?.value) || 0;
            const carbs = parseFloat(cEl?.value) || 0;
            const fat = parseFloat(fEl?.value) || 0;
            const dietType = dietEl?.value || 'veg';

            if (!mealRowFoods[activeMealRowIdForPicker]) {
                mealRowFoods[activeMealRowIdForPicker] = [];
            }

            mealRowFoods[activeMealRowIdForPicker].push({
                foodId: 'custom_' + Date.now() + '_' + Math.floor(Math.random() * 10000),
                name,
                serving,
                qty: 1,
                calories,
                protein,
                carbs,
                fat,
                diet_type: dietType,
                isCustom: true
            });

            if (nameEl) nameEl.value = '';
            if (servingEl) servingEl.value = '';
            if (calsEl) calsEl.value = '';
            if (pEl) pEl.value = '';
            if (cEl) cEl.value = '';
            if (fEl) fEl.value = '';

            togglePickerCustomForm(false);
            renderMealFoods(activeMealRowIdForPicker);
            renderMealPickerList();
            showToast('Custom food added to meal.');
        }

        function setPickerCategory(cat) {
            pickerCurrentCategory = cat;
            document.querySelectorAll('.picker-cat-btn').forEach(btn => {
                if (btn.getAttribute('data-cat') === cat) {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-semibold bg-amber-500 text-slate-950 shrink-0';
                } else {
                    btn.className = 'picker-cat-btn px-2.5 py-1 rounded-md font-medium text-slate-500 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 shrink-0';
                }
            });
            renderMealPickerList();
        }

        function filterMealPickerList() {
            renderMealPickerList();
        }

        function renderMealPickerList() {
            const container = document.getElementById('pickerFoodListContainer');
            const rawQuery = (document.getElementById('pickerSearchInput').value || '').trim();
            const q = rawQuery.toLowerCase();

            const currentMealFoods = (activeMealRowIdForPicker && mealRowFoods[activeMealRowIdForPicker]) ? mealRowFoods[activeMealRowIdForPicker] : [];
            const summaryEl = document.getElementById('pickerActiveMealSummary');
            if (summaryEl) {
                const totalItems = currentMealFoods.reduce((s, it) => s + it.qty, 0);
                const totalCals = Math.round(currentMealFoods.reduce((s, it) => s + (it.qty * it.calories), 0));
                const totalP = Math.round(currentMealFoods.reduce((s, it) => s + (it.qty * it.protein), 0));
                if (totalItems > 0) {
                    summaryEl.innerHTML = `<strong class="text-slate-900 dark:text-white">${totalItems} items</strong> in meal · <strong class="text-slate-900 dark:text-white">${totalCals} kcal</strong> · <strong class="text-slate-900 dark:text-white">${totalP}g Protein</strong>`;
                } else {
                    summaryEl.innerHTML = `Select from list or click <strong>+ Custom Food</strong> above`;
                }
            }

            let filtered = indianFoodsData.filter(item => {
                const matchesCat = pickerCurrentCategory === 'all' || item.category === pickerCurrentCategory;
                const matchesQuery = !q || item.name.toLowerCase().includes(q) || (item.hindi_name && item.hindi_name.toLowerCase().includes(q));
                return matchesCat && matchesQuery;
            });

            if (filtered.length === 0) {
                container.innerHTML = `
                    <div class="p-8 text-center space-y-3">
                        <div class="text-xs text-slate-500 dark:text-slate-400">No library food matched "${escapeHtml(rawQuery)}".</div>
                        <button type="button" onclick="togglePickerCustomForm(true, document.getElementById('pickerSearchInput').value.trim())"
                                class="px-3.5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-semibold transition-colors cursor-pointer">
                            + Add "${escapeHtml(rawQuery || 'Custom Item')}" as Custom Food
                        </button>
                    </div>
                `;
                return;
            }

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
                                <span class="text-xs font-semibold text-slate-900 dark:text-white truncate">${escapeHtml(food.name)}</span>
                                <span class="text-[11px] text-slate-400">(${escapeHtml(food.serving)})</span>
                            </div>
                            <div class="flex items-center gap-3 text-[11px] tabular-nums mt-1 text-slate-500 dark:text-slate-400 pl-5">
                                <span class="text-amber-600 dark:text-amber-400 font-semibold">${food.calories} kcal</span>
                                <span>P: <strong class="text-rose-500 dark:text-rose-400">${food.protein}g</strong></span>
                                <span>C: <strong class="text-amber-500 dark:text-amber-400">${food.carbs}g</strong></span>
                                <span>F: <strong class="text-cyan-500 dark:text-cyan-400">${food.fat}g</strong></span>
                            </div>
                        </div>

                        <div class="flex items-center gap-1.5 shrink-0">
                            ${isAdded ? `
                                <div class="flex items-center gap-1">
                                    <span class="px-2.5 py-1.5 rounded-lg bg-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-semibold border border-emerald-500/30 tabular-nums">
                                        Added (${existing.qty}x)
                                    </span>
                                    <button type="button" onclick="addFoodItemToActiveMeal('${food.id}')" class="w-7 h-7 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold flex items-center justify-center cursor-pointer shadow-xs" title="Add another serving">+</button>
                                </div>
                            ` : `
                                <button type="button"
                                        onclick="addFoodItemToActiveMeal('${food.id}')"
                                        class="px-3 py-1.5 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-semibold transition-all cursor-pointer shadow-xs">
                                    + Add
                                </button>
                            `}
                        </div>
                    </div>
                `;
            });

            container.innerHTML = html;
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
                    diet_type: food.diet_type,
                    isCustom: false
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
            const text = `*${food.name}*${food.hindi_name ? ' (' + food.hindi_name + ')' : ''}\n• Serving: ${food.serving}\n• Calories: ${food.calories} kcal\n• Protein: ${food.protein}g | Carbs: ${food.carbs}g | Fat: ${food.fat}g | Fiber: ${food.fiber}g\n• Purpose: ${food.gym_purpose}\n• Info: ${food.description}`;
            navigator.clipboard.writeText(text).then(() => {
                showToast(`Copied ${food.name} nutrition details.`);
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
            document.getElementById('aiResPlanTitle').innerText = data.plan_title || 'Generated Diet Plan';
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
                                <span class="w-5 h-5 rounded-full bg-indigo-500/10 dark:bg-indigo-500/20 text-indigo-600 dark:text-indigo-400 tabular-nums text-[10px] font-semibold flex items-center justify-center">${idx + 1}</span>
                                <h4 class="text-xs font-semibold text-slate-900 dark:text-white">${escapeHtml(meal.meal_name)}</h4>
                                <span class="text-[11px] text-slate-500 tabular-nums">${escapeHtml(meal.recommended_time)}</span>
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] tabular-nums font-medium">
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400">${meal.target_macros.calories} kcal</span>
                                <span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-500">P: ${meal.target_macros.protein_g}g</span>
                                <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500">C: ${meal.target_macros.carbs_g}g</span>
                                <span class="px-2 py-0.5 rounded bg-cyan-500/10 text-cyan-500">F: ${meal.target_macros.fat_g}g</span>
                            </div>
                        </div>
                        <div class="text-xs text-slate-700 dark:text-slate-200 whitespace-pre-line leading-relaxed pl-2 border-l-2 border-indigo-500/40">${escapeHtml(meal.items_description)}</div>
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
                    btn.innerHTML = `<span>Saved. Reloading...</span>`;
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
            let text = `*${d.plan_title}*\n`;
            text += `------------------------\n`;
            text += `*Daily Target:* ${d.daily_totals.calories} kcal\n`;
            text += `*Protein:* ${d.daily_totals.protein_grams}g | *Carbs:* ${d.daily_totals.carbs_grams}g | *Fats:* ${d.daily_totals.fat_grams}g\n`;
            text += `*Hydration Goal:* ${d.daily_totals.water_liters} L / day\n\n`;
            text += `*Daily Meal Plan:*\n`;

            d.meals.forEach((m, idx) => {
                text += `\n*${idx + 1}. ${m.meal_name}* (${m.recommended_time}) — ${m.target_macros.calories} kcal\n`;
                text += `${m.items_description}\n`;
            });

            navigator.clipboard.writeText(text).then(() => {
                showToast('Diet plan copied to clipboard.');
            });
        }
    </script>
</x-app-layout>
