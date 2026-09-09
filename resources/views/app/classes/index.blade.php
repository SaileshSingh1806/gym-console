<x-app-layout header="Group Fitness Classes & Schedules">
    <div class="space-y-6">
        <div class="flex justify-between items-center">
            <h3 class="text-xs font-bold text-white uppercase tracking-wider">Group Classes & Timetables</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            @forelse($classes as $c)
                <div class="p-6 rounded-2xl bg-slate-900 border border-slate-800">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-white text-base">{{ $c->name }}</h4>
                        <span class="px-2 py-0.5 rounded text-[10px] bg-amber-500/10 text-amber-400 border border-amber-500/20 font-bold">Capacity: {{ $c->capacity }}</span>
                    </div>
                    <p class="text-xs text-slate-400 mb-4">{{ $c->description ?? 'All levels welcome.' }}</p>

                    <div class="border-t border-slate-800 pt-3">
                        <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block mb-2">Class Schedule</span>
                        <div class="space-y-1.5 text-xs text-slate-300">
                            @forelse($c->schedules as $sch)
                                <div class="flex justify-between p-2 rounded-lg bg-slate-950 border border-slate-800">
                                    <span class="capitalize font-semibold text-amber-400">{{ $sch->day_of_week }}</span>
                                    <span>{{ date('h:i A', strtotime($sch->start_time)) }} - {{ date('h:i A', strtotime($sch->end_time)) }}</span>
                                    <span class="text-slate-400">{{ $sch->trainer->full_name ?? 'Staff Coach' }}</span>
                                </div>
                            @empty
                                <span class="text-xs text-slate-500">No schedules published yet.</span>
                            @endforelse
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2 p-8 text-center bg-slate-900 border border-slate-800 rounded-2xl text-slate-500 text-xs">
                    No classes added yet.
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>

