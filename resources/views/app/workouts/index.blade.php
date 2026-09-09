<x-app-layout header="Workout Routines & Exercise Library">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Assigned Workout Routines & Templates</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($plans as $plan)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-white text-base">{{ $plan->title }}</h4>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold uppercase">{{ $plan->goal }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">Level: <span class="capitalize text-white">{{ $plan->level }}</span> • Assigned Member: <span class="text-white font-semibold">{{ $plan->member->full_name ?? 'Universal Template' }}</span></p>

                    <div class="border-t border-slate-800 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Prescribed Exercises</span>
                        <div class="space-y-1.5 text-xs text-slate-300">
                            @forelse($plan->exercises as $ex)
                                <div class="flex justify-between p-2 rounded-lg bg-slate-950 border border-slate-800 text-xs">
                                    <span class="font-bold text-white">{{ $ex->exercise_name }}</span>
                                    <span class="text-amber-400">{{ $ex->sets }} sets × {{ $ex->reps }}</span>
                                    <span class="text-slate-400">{{ $ex->weight ?? 'Weight as prescribed' }}</span>
                                </div>
                            @empty
                                <span class="text-xs text-slate-500">No exercises added to this routine.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500 text-xs">
                    No workout routines created yet.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

