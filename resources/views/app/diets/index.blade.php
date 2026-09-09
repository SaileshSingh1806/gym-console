<x-app-layout header="Diet & Nutrition Meal Plans">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Custom Meal Plans & Macro Schedules</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($plans as $plan)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-white text-base">{{ $plan->title }}</h4>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-bold">{{ $plan->daily_calories ?? 2000 }} kcal</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Target: P: {{ $plan->protein_grams ?? 150 }}g • C: {{ $plan->carbs_grams ?? 200 }}g • F: {{ $plan->fat_grams ?? 60 }}g</p>

                    <div class="border-t border-slate-800 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Meals Breakdown</span>
                        <div class="space-y-1.5 text-xs text-slate-300">
                            @forelse($plan->meals as $meal)
                                <div class="p-2 rounded-lg bg-slate-950 border border-slate-800">
                                    <div class="flex justify-between font-semibold text-amber-400">
                                        <span class="capitalize">{{ str_replace('_', ' ', $meal->meal_type) }} ({{ $meal->meal_name }})</span>
                                        <span>{{ $meal->recommended_time ? date('h:i A', strtotime($meal->recommended_time)) : '' }}</span>
                                    </div>
                                    <p class="text-[11px] text-slate-400 mt-1">{{ $meal->items_description }}</p>
                                </div>
                            @empty
                                <span class="text-xs text-slate-500">No meals added to plan.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500 text-xs">
                    No nutrition plans created yet.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

