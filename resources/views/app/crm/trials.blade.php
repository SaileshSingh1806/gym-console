<x-app-layout header="Trial & Demo Sessions">
    @php
        $currency = $tenant->currency_symbol ?? '₹';
        $currentMonthName = \Carbon\Carbon::createFromDate($year, $month, 1)->format('F Y');
        $prevMonthDate = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth();
        $nextMonthDate = \Carbon\Carbon::createFromDate($year, $month, 1)->addMonth();
        
        $firstDayOfMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
        $daysInMonth = $firstDayOfMonth->daysInMonth;
        // Day of week: 1 (Mon) - 7 (Sun) in ISO
        $startDayOfWeek = $firstDayOfMonth->isoWeekday(); 
        $prevMonthDays = \Carbon\Carbon::createFromDate($year, $month, 1)->subMonth()->daysInMonth;
    @endphp

    <div class="space-y-6" x-data="{
        showTrialModal: false,
        selectedTrial: null,
        showDetailsModal: false,
        viewTrial(t) {
            this.selectedTrial = t;
            this.showDetailsModal = true;
        }
    }">

        <!-- Notifications -->
        @if(session('success'))
            <div class="p-4 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-between shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="text-xs font-semibold">{{ session('success') }}</span>
                </div>
                <button type="button" onclick="this.parentElement.remove()" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold p-1">✕</button>
            </div>
        @endif

        <!-- ==================== 4 TOP KPI STATS ==================== -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5">
            <!-- Today's Trials -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Today's Trials</span>
                    <div class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">{{ $todayTrialsCount }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            </div>

            <!-- Upcoming -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Upcoming</span>
                    <div class="text-2xl font-black text-purple-600 dark:text-purple-400 tracking-tight">{{ $upcomingTrialsCount }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Completed (Month) -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Completed ({{ date('M', mktime(0, 0, 0, $month, 1)) }})</span>
                    <div class="text-2xl font-black text-emerald-600 dark:text-emerald-400 tracking-tight">{{ $completedThisMonth }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>

            <!-- Cancelled (Month) -->
            <div class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 flex items-center justify-between shadow-sm">
                <div class="space-y-1">
                    <span class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Cancelled ({{ date('M', mktime(0, 0, 0, $month, 1)) }})</span>
                    <div class="text-2xl font-black text-rose-600 dark:text-rose-400 tracking-tight">{{ $cancelledThisMonth }}</div>
                </div>
                <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- ==================== ACTION BAR ==================== -->
        <div class="flex items-center justify-between">
            <button type="button" @click="showTrialModal = true" class="px-4 py-2.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs shadow-lg shadow-indigo-600/25 flex items-center gap-2 transition-all cursor-pointer">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                <span>Book Demo</span>
            </button>
        </div>

        <!-- ==================== MONTHLY CALENDAR ==================== -->
        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
            <!-- Calendar Navigation Header -->
            <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <a href="{{ route('app.crm.trials', ['month' => $prevMonthDate->month, 'year' => $prevMonthDate->year]) }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/></svg>
                </a>

                <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">
                    {{ $currentMonthName }}
                </h3>

                <a href="{{ route('app.crm.trials', ['month' => $nextMonthDate->month, 'year' => $nextMonthDate->year]) }}" class="p-2 rounded-xl bg-slate-100 dark:bg-slate-950 text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white border border-slate-200 dark:border-slate-800 hover:border-slate-300 dark:hover:border-slate-700 transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>

            <!-- Scrollable Calendar Grid Container on Mobile -->
            <div class="overflow-x-auto">
                <div class="min-w-[640px]">
                    <!-- Weekday Column Headers (Mon - Sun) -->
                    <div class="grid grid-cols-7 border-b border-slate-200 dark:border-slate-800/80 bg-slate-50 dark:bg-slate-950/40 text-center text-xs font-bold text-slate-500 dark:text-slate-400 py-2.5">
                        <div>Mon</div>
                        <div>Tue</div>
                        <div>Wed</div>
                        <div>Thu</div>
                        <div>Fri</div>
                        <div>Sat</div>
                        <div>Sun</div>
                    </div>

                    <!-- Calendar Days Grid -->
                    <div class="grid grid-cols-7 divide-x divide-y divide-slate-100 dark:divide-slate-800/60 bg-white dark:bg-slate-950/20">
                        <!-- Previous month trailing days -->
                        @for($i = $startDayOfWeek - 1; $i >= 1; $i--)
                            @php $prevDayNum = $prevMonthDays - $i + 1; @endphp
                            <div class="min-h-[100px] p-2 bg-slate-50 dark:bg-slate-950/30 text-slate-400 dark:text-slate-600 text-xs">
                                <span class="font-bold">{{ $prevDayNum }}</span>
                            </div>
                        @endfor

                        <!-- Current month days -->
                        @for($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dayDateString = sprintf('%04d-%02d-%02d', $year, $month, $d);
                                $isToday = $dayDateString === date('Y-m-d');
                                $dayTrials = $trials->filter(function($t) use ($dayDateString) {
                                    return $t->trial_date && $t->trial_date->toDateString() === $dayDateString;
                                });
                            @endphp
                            <div class="min-h-[100px] p-2 {{ $isToday ? 'bg-indigo-50/70 dark:bg-indigo-950/20 ring-1 ring-inset ring-indigo-500/30' : 'hover:bg-slate-50 dark:hover:bg-slate-800/20' }} transition-colors relative flex flex-col justify-between">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-xs font-bold {{ $isToday ? 'text-indigo-600 dark:text-indigo-400 px-1.5 py-0.5 rounded-md bg-indigo-100 dark:bg-indigo-500/20' : 'text-slate-700 dark:text-slate-300' }}">{{ $d }}</span>
                                </div>

                                <!-- Scheduled Demo Event Chips -->
                                <div class="space-y-1 overflow-y-auto max-h-20">
                                    @foreach($dayTrials as $tr)
                                        <button type="button" @click="viewTrial({{ Js::from($tr) }})" class="w-full text-left p-1 rounded-md bg-indigo-50 dark:bg-indigo-950/80 hover:bg-indigo-100 dark:hover:bg-indigo-900/90 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-500/30 text-[10px] font-semibold truncate transition-colors flex items-center gap-1 cursor-pointer" title="{{ $tr->trial_time }} {{ $tr->lead?->name }}">
                                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 dark:bg-indigo-400 shrink-0"></span>
                                            <span class="font-mono text-[9px] text-indigo-600 dark:text-indigo-200">{{ $tr->trial_time ? substr($tr->trial_time, 0, 5) : '10:00' }}</span>
                                            <span class="truncate">{{ $tr->lead?->name ?? 'Guest Demo' }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @endfor

                        <!-- Next month trailing days to complete last row -->
                        @php
                            $totalCells = ($startDayOfWeek - 1) + $daysInMonth;
                            $remainingCells = (7 - ($totalCells % 7)) % 7;
                        @endphp
                        @for($n = 1; $n <= $remainingCells; $n++)
                            <div class="min-h-[100px] p-2 bg-slate-50 dark:bg-slate-950/30 text-slate-400 dark:text-slate-600 text-xs">
                                <span class="font-bold">{{ $n }}</span>
                            </div>
                        @endfor
                    </div>
                </div>
            </div>
        </div>

        <!-- ==================== UPCOMING TRIALS LIST ==================== -->
        <div class="p-5 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 space-y-4 shadow-sm">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-slate-900 dark:text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Upcoming Trials</span>
                </h3>
            </div>

            <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                @forelse($upcomingList as $trial)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs hover:bg-slate-50 dark:hover:bg-slate-800/30 px-2 rounded-xl transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20 flex flex-col items-center justify-center shrink-0">
                                <span class="text-xs font-black leading-none">{{ $trial->trial_date ? $trial->trial_date->format('d') : '12' }}</span>
                                <span class="text-[8.5px] uppercase font-bold text-purple-600 dark:text-purple-300 leading-none mt-0.5">{{ $trial->trial_date ? $trial->trial_date->format('M') : 'Sep' }}</span>
                            </div>

                            <div class="min-w-0">
                                <h4 class="font-bold text-slate-900 dark:text-white truncate text-sm">{{ $trial->lead?->name ?? 'Guest Prospect' }}</h4>
                                <div class="text-slate-500 dark:text-slate-400 mt-0.5 flex flex-wrap items-center gap-2 text-[11px]">
                                    <span class="font-mono text-purple-600 dark:text-purple-300 font-bold">{{ $trial->trial_time ?? '10:00 AM' }}</span>
                                    <span>•</span>
                                    <span>{{ $trial->lead?->phone }}</span>
                                    @if($trial->assignedTo)
                                        <span>•</span>
                                        <span>Coach: <strong class="text-slate-700 dark:text-slate-200">{{ $trial->assignedTo->name }}</strong></span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 shrink-0">
                            <!-- Status Badge -->
                            <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-extrabold uppercase {{ $trial->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:border-emerald-500/20' : ($trial->status === 'cancelled' ? 'bg-rose-50 text-rose-700 border border-rose-200 dark:bg-rose-500/10 dark:text-rose-400 dark:border-rose-500/20' : 'bg-purple-50 text-purple-700 border border-purple-200 dark:bg-purple-500/10 dark:text-purple-400 dark:border-purple-500/20') }}">
                                {{ $trial->status }}
                            </span>

                            @if($trial->status === 'upcoming')
                                <!-- Mark Completed -->
                                <form action="{{ route('app.crm.trials.status', $trial->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="status" value="completed">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-[10px] transition-all cursor-pointer">
                                        ✓ Completed
                                    </button>
                                </form>

                                <!-- Cancel -->
                                <form action="{{ route('app.crm.trials.status', $trial->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <input type="hidden" name="status" value="cancelled">
                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-rose-50 text-slate-600 hover:text-rose-600 border border-slate-200 dark:border-transparent dark:bg-slate-800 dark:hover:bg-rose-500/20 dark:text-slate-400 dark:hover:text-rose-400 font-bold text-[10px] transition-all cursor-pointer">
                                        Cancel
                                    </button>
                                </form>
                            @endif

                            <!-- Delete -->
                            <form action="{{ route('app.crm.trials.delete', $trial->id) }}" method="POST" 
                                  data-confirm="Are you sure you want to remove this trial booking record?" 
                                  data-confirm-title="Remove Trial Booking" 
                                  data-confirm-btn="Yes, Remove" 
                                  data-confirm-type="danger" 
                                  class="inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 rounded-lg text-slate-400 hover:text-rose-600 dark:text-slate-500 dark:hover:text-rose-400 transition-colors cursor-pointer" title="Remove Trial">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="py-12 text-center text-slate-500 text-xs space-y-2">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-950 flex items-center justify-center mx-auto text-slate-400 dark:text-slate-600 border border-slate-200 dark:border-slate-800">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <p class="font-medium text-slate-500 dark:text-slate-400">No upcoming demos scheduled.</p>
                        <button type="button" @click="showTrialModal = true" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white font-bold text-xs transition-all shadow-md shadow-indigo-600/25 cursor-pointer">
                            + Book Demo Session
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- ==================== MODAL: BOOK DEMO ==================== -->
        <div x-show="showTrialModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4" @click.away="showTrialModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight flex items-center gap-2">
                        <span>🗓️ Book Demo / Trial Session</span>
                    </h3>
                    <button type="button" @click="showTrialModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <form action="{{ route('app.crm.trials.store') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Select Lead (Demo Booked) *</label>
                        <select name="lead_id" required class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Choose Lead --</option>
                            @forelse($leadsList as $ld)
                                <option value="{{ $ld->id }}">{{ $ld->name }} ({{ $ld->phone }}) - {{ $ld->formatted_stage }}</option>
                            @empty
                                <option value="" disabled>No leads currently in 'Demo Booked' stage</option>
                            @endforelse
                        </select>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Trial Date *</label>
                            <input type="date" name="trial_date" required value="{{ date('Y-m-d') }}" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Trial Time</label>
                            <input type="text" name="trial_time" value="10:00 AM" placeholder="e.g. 01:03 PM" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Assigned Coach / Trainer</label>
                        <select name="assigned_to_user_id" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white text-xs focus:border-indigo-500 focus:outline-none">
                            <option value="">-- Any Available Coach --</option>
                            @foreach($staffMembers as $staff)
                                <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">Notes / Instructions</label>
                        <textarea name="notes" rows="2" placeholder="e.g. 1-day complimentary guest workout pass" class="w-full px-3.5 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-800 text-slate-900 dark:text-white placeholder-slate-400 dark:placeholder-slate-500 text-xs focus:border-indigo-500 focus:outline-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                        <button type="button" @click="showTrialModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold transition-all">Cancel</button>
                        <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md shadow-indigo-600/25 transition-all">Book Demo Session</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==================== MODAL: TRIAL DETAILS ==================== -->
        <div x-show="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto bg-black/60 dark:bg-black/80 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-900 dark:text-slate-200 rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4" @click.away="showDetailsModal = false">
                <div class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-white tracking-tight">Demo Session Details</h3>
                    <button type="button" @click="showDetailsModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white text-xs font-bold p-1">✕</button>
                </div>

                <template x-if="selectedTrial">
                    <div class="space-y-3 text-xs">
                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1.5">
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Prospect Info</div>
                            <div class="text-sm font-black text-slate-900 dark:text-white" x-text="selectedTrial.lead?.name || 'Guest Lead'"></div>
                            <div class="text-slate-500 dark:text-slate-400" x-text="selectedTrial.lead?.phone || 'No phone'"></div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 text-center">
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                                <div class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Date</div>
                                <div class="font-bold text-slate-900 dark:text-white text-xs mt-0.5" x-text="selectedTrial.trial_date ? selectedTrial.trial_date.substring(0, 10) : ''"></div>
                            </div>
                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800">
                                <div class="text-[9px] font-bold text-slate-500 dark:text-slate-400 uppercase">Time</div>
                                <div class="font-bold text-indigo-600 dark:text-indigo-400 text-xs mt-0.5" x-text="selectedTrial.trial_time || '10:00 AM'"></div>
                            </div>
                        </div>

                        <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 space-y-1">
                            <div class="text-[10px] font-bold text-slate-500 dark:text-slate-400 uppercase">Notes</div>
                            <div class="text-slate-700 dark:text-slate-300" x-text="selectedTrial.notes || 'Complimentary trial workout session'"></div>
                        </div>
                    </div>
                </template>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-200 dark:border-slate-800">
                    <button type="button" @click="showDetailsModal = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 dark:bg-slate-800 dark:hover:bg-slate-700 dark:text-slate-300 text-xs font-bold transition-all">Close</button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

