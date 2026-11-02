<x-app-layout header="My Member Portal">
    @php
        $currency = $tenant?->currency_symbol ?? '₹';
        $activeTab = $tab ?? 'overview';
        $finalAmt = (float) ($activeMembership?->final_amount ?? 0);
        $paidAmt = (float) ($activeMembership?->paid_amount ?? 0);
        $remainingAmt = max(0, $finalAmt - $paidAmt);
        $activeWorkout = $member?->activeWorkoutPlan ?? $member?->workoutPlans?->first();
        $activeDiet = $member?->activeDietPlan ?? $member?->dietPlans?->first();
        $latestMeasurement = !empty($measurements) ? $measurements[0] : null;

        // Check if member attended gym today
        $todayCheckin = $member?->attendance?->first(function ($att) {
            return ($att->date && \Carbon\Carbon::parse($att->date)->isToday()) 
                || ($att->check_in && \Carbon\Carbon::parse($att->check_in)->isToday());
        });

        // Attendance stats for motivation system
        $thisMonthCheckins = $member ? $member->attendance->filter(function($a) {
            $d = $a->date ? \Carbon\Carbon::parse($a->date) : ($a->check_in ? \Carbon\Carbon::parse($a->check_in) : null);
            return $d && $d->isCurrentMonth();
        })->count() : 0;

        $totalCheckins = $member ? $member->attendance->count() : 0;

        // Gamified 20-Day Monthly Target
        $monthlyGoal = 20;
        $goalPercent = min(100, round(($thisMonthCheckins / $monthlyGoal) * 100));
        $sessionsNeeded = max(0, $monthlyGoal - $thisMonthCheckins);

        // Motivational Tier & Badge System
        if ($thisMonthCheckins >= 20) {
            $tierName = 'Diamond Legend';
            $tierIcon = '👑';
            $tierBadge = 'bg-purple-500/20 text-purple-300 border-purple-500/30';
            $tierBarGrad = 'from-purple-500 via-pink-500 to-indigo-500';
            $tierMessage = 'Elite Discipline! You are in the top 1% of gym athletes.';
            $nextMilestone = 'Maximum Level Achieved! Keep dominating.';
        } elseif ($thisMonthCheckins >= 12) {
            $tierName = 'Gold Beast';
            $tierIcon = '🥇';
            $tierBadge = 'bg-amber-500/20 text-amber-300 border-amber-500/30';
            $tierBarGrad = 'from-amber-500 via-orange-500 to-amber-400';
            $tierMessage = 'Insane consistency! Your transformation is unstoppable.';
            $nextMilestone = $sessionsNeeded . ' more visits to reach Diamond Legend 👑';
        } elseif ($thisMonthCheckins >= 5) {
            $tierName = 'Silver Warrior';
            $tierIcon = '🥈';
            $tierBadge = 'bg-cyan-500/20 text-cyan-300 border-cyan-500/30';
            $tierBarGrad = 'from-cyan-500 via-blue-500 to-indigo-500';
            $tierMessage = 'Great momentum! You are building ironclad discipline.';
            $nextMilestone = (12 - $thisMonthCheckins) . ' more visits to unlock Gold Beast 🥇';
        } else {
            $tierName = 'Bronze Starter';
            $tierIcon = '🥉';
            $tierBadge = 'bg-emerald-500/20 text-emerald-300 border-emerald-500/30';
            $tierBarGrad = 'from-emerald-500 to-teal-400';
            $tierMessage = 'Every workout counts. Step up, show up, and crush it!';
            $nextMilestone = (5 - $thisMonthCheckins) . ' more visits to unlock Silver Warrior 🥈';
        }

        // Current week (Mon to Sun) checkin timeline
        $currentWeekDays = [];
        $startOfWeek = now()->copy()->startOfWeek(); // Monday
        $attendedDates = $member ? $member->attendance->map(function($a) {
            $d = $a->date ? \Carbon\Carbon::parse($a->date) : ($a->check_in ? \Carbon\Carbon::parse($a->check_in) : null);
            return $d ? $d->toDateString() : null;
        })->filter()->all() : [];

        for ($i = 0; $i < 7; $i++) {
            $dayDate = $startOfWeek->copy()->addDays($i);
            $isAttended = in_array($dayDate->toDateString(), $attendedDates);
            $isToday = $dayDate->isToday();
            $isFuture = $dayDate->isFuture() && !$isToday;
            $currentWeekDays[] = [
                'day' => $dayDate->format('D'),
                'date' => $dayDate->format('j'),
                'attended' => $isAttended,
                'isToday' => $isToday,
                'isFuture' => $isFuture,
            ];
        }

        // Active PT Package & Assigned Locker
        $activePt = $member?->memberPtPackages?->where('status', 'ACTIVE')->first();
        $assignedLocker = $member?->serviceBookings?->where('status', 'ACTIVE')->whereNotNull('locker_number')->first();
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        showQrModal: false,
        showPortalScanner: false,
        portalScanning: false,
        portalScanMsg: '',
        portalQrReader: null,
        showMeasurementModal: false,
        setPortalTab(name) {
            this.tab = name;
            window.dispatchEvent(new CustomEvent('portal-tab-changed', { detail: name }));
            if (window.history && window.history.replaceState) {
                const url = new URL(window.location);
                if (name === 'overview') {
                    url.searchParams.delete('tab');
                } else {
                    url.searchParams.set('tab', name);
                }
                window.history.replaceState(null, '', url.toString());
            }
        },
        startPortalScanner() {
            this.showPortalScanner = true;
            this.portalScanMsg = '';
            this.$nextTick(() => {
                if (typeof Html5Qrcode !== 'undefined') {
                    if (this.portalQrReader) {
                        this.portalQrReader.stop().catch(() => {});
                    }
                    this.portalQrReader = new Html5Qrcode('portal-camera-reader');
                    this.portalQrReader.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 250, height: 250 } },
                        (decodedText) => {
                            this.stopPortalScanner();
                            this.submitPortalCheckin(decodedText);
                        },
                        (error) => {}
                    ).catch(err => {
                        this.portalScanMsg = 'Camera error: ' + err;
                    });
                }
            });
        },
        stopPortalScanner() {
            if (this.portalQrReader) {
                this.portalQrReader.stop().then(() => {
                    this.portalQrReader.clear();
                }).catch(() => {});
            }
            this.showPortalScanner = false;
        },
        submitPortalCheckin(scannedText) {
            this.portalScanning = true;
            fetch('{{ route('app.portal.scan-checkin') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ token: scannedText })
            })
            .then(res => res.json())
            .then(data => {
                this.portalScanning = false;
                alert(data.message || (data.success ? 'Attendance check-in confirmed!' : 'Could not record attendance.'));
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(err => {
                this.portalScanning = false;
                alert('Check-in error: ' + err.message);
            });
        }
    }" 
    @switch-portal-tab.window="tab = $event.detail"
    class="max-w-xl md:max-w-2xl mx-auto space-y-5 pb-24 sm:pb-12 pt-1">

        @if(! $member)
            <!-- Unlinked Account Notice -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-xl text-center space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-black text-slate-900 dark:text-white">Welcome, {{ auth()->user()->name }}!</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400 max-w-md mx-auto leading-relaxed">Your member account is active, but your mobile number has not yet been linked to an active gym profile. Please contact the front desk reception to verify your registration.</p>
            </div>
        @else

            <!-- HERO: LUXURY DIGITAL MEMBERSHIP PASS (APP CARD) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 border border-amber-500/25 shadow-2xl p-5 sm:p-6 text-white space-y-5">
                <!-- Ambient Glow Elements -->
                <div class="absolute -top-16 -right-16 w-60 h-60 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Card Top: Gym Branding & Status Badge -->
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                        <span class="text-xs font-black tracking-widest uppercase text-amber-400">
                            {{ $tenant->name ?? 'GymDeskHub' }}
                        </span>
                    </div>
                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase tracking-wider {{ $member->status === 'ACTIVE' ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($member->status === 'SUSPENDED' ? 'bg-rose-500/20 text-rose-400 border border-rose-500/30' : 'bg-slate-700/50 text-slate-300 border border-slate-600/40') }}">
                        {{ $member->status }} PASS
                    </span>
                </div>

                <!-- Card Middle: Member Info -->
                <div class="relative z-10 flex items-center gap-4">
                    <div class="relative shrink-0">
                        @if(!empty($member->photo_path))
                            <img src="{{ asset('storage/' . $member->photo_path) }}" alt="{{ $member->full_name }}" class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl object-cover border-2 border-amber-500/40 shadow-lg">
                        @else
                            <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-500 to-amber-600 flex items-center justify-center text-slate-950 font-black text-2xl sm:text-3xl shadow-lg shadow-orange-500/20 border border-white/20">
                                {{ strtoupper(substr($member->first_name ?: 'M', 0, 1)) }}{{ strtoupper(substr($member->last_name ?: '', 0, 1)) }}
                            </div>
                        @endif
                    </div>

                    <div class="min-w-0 flex-1 space-y-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <h2 class="text-xl sm:text-2xl font-black text-white tracking-tight truncate">{{ $member->full_name }}</h2>
                            <span class="px-2 py-0.5 rounded-md bg-white/10 text-slate-200 text-[11px] font-mono font-bold border border-white/10">
                                {{ $member->member_code }}
                            </span>
                        </div>
                        <div class="flex flex-wrap items-center gap-x-3 gap-y-0.5 text-xs text-slate-300">
                            @if($activeMembership)
                                <span class="font-bold text-amber-300">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</span>
                            @else
                                <span class="text-slate-400">No active plan</span>
                            @endif
                            @if($member->branch)
                                <span>• {{ $member->branch->name }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Card Validity & Days Remaining Pill -->
                @if($activeMembership)
                    <div class="relative z-10 pt-3 border-t border-white/10 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-slate-400 text-[11px]">Valid Until:</span>
                            <span class="font-bold text-white ml-1">{{ $activeMembership->end_date?->format('d M, Y') ?? 'N/A' }}</span>
                        </div>
                        @if($daysLeft !== null)
                            <span class="px-3 py-1 rounded-xl text-xs font-black {{ $daysLeft > 7 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($daysLeft >= 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30') }}">
                                {{ $daysLeft >= 0 ? ($daysLeft . ' Days Left') : ('Expired ' . abs($daysLeft) . 'd ago') }}
                            </span>
                        @endif
                    </div>
                @endif

                <!-- Card Action Buttons: 1-Tap Check-in & Pass -->
                <div class="relative z-10 grid grid-cols-2 gap-2.5 pt-1">
                    <button type="button" 
                            @click="startPortalScanner()" 
                            class="py-3 px-3 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:brightness-110 active:scale-[0.98] text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Scan Gym QR</span>
                    </button>

                    <button type="button" 
                            @click="showQrModal = true" 
                            class="py-3 px-3 rounded-2xl bg-white/10 hover:bg-white/15 active:scale-[0.98] border border-white/15 text-white font-black text-xs shadow-md flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        <span>Digital Pass</span>
                    </button>
                </div>
            </div>

            <!-- TODAY'S CHECK-IN STATUS STRIP -->
            <div class="px-4 py-3 rounded-2xl {{ $todayCheckin ? 'bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 dark:text-emerald-400' : 'bg-slate-100 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300' }} flex items-center justify-between text-xs font-bold shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full {{ $todayCheckin ? 'bg-emerald-500 animate-pulse' : 'bg-slate-400' }}"></span>
                    @if($todayCheckin)
                        <span>Checked in today at {{ $todayCheckin->check_in ? \Carbon\Carbon::parse($todayCheckin->check_in)->format('h:i A') : 'Recorded' }} 🔥</span>
                    @else
                        <span>Not checked in yet today 🕒</span>
                    @endif
                </div>
                <button type="button" @click="setPortalTab('attendance')" class="text-[11px] font-semibold text-slate-500 dark:text-slate-400 hover:text-amber-500 transition-colors">
                    {{ $thisMonthCheckins }} visits this month &rarr;
                </button>
            </div>

            <!-- MOTIVATIONAL ATTENDANCE & CONSISTENCY HERO WIDGET -->
            <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/40 border border-slate-800 p-5 shadow-lg space-y-4 text-white relative overflow-hidden">
                <!-- Background Accent Glow -->
                <div class="absolute -top-12 -right-12 w-40 h-40 bg-indigo-500/10 rounded-full blur-2xl pointer-events-none"></div>

                <!-- Top Row: Tier Badge & Monthly Progress -->
                <div class="relative z-10 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2.5">
                        <span class="text-2xl">{{ $tierIcon }}</span>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-black text-white tracking-tight">{{ $tierName }}</h3>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $tierBadge }} border">
                                    {{ $thisMonthCheckins }} Workouts
                                </span>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-0.5 leading-snug">{{ $tierMessage }}</p>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-lg font-black text-amber-400 font-mono">{{ $goalPercent }}%</div>
                        <div class="text-[10px] text-slate-400">Monthly Target</div>
                    </div>
                </div>

                <!-- Progress Bar toward Monthly Goal -->
                <div class="relative z-10 space-y-1.5">
                    <div class="w-full bg-slate-800/90 rounded-full h-3 p-0.5 overflow-hidden border border-slate-700/60 shadow-inner">
                        <div class="h-full rounded-full bg-gradient-to-r {{ $tierBarGrad }} transition-all duration-500 shadow-sm" style="width: {{ $goalPercent }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-slate-400">
                        <span>{{ $thisMonthCheckins }} of {{ $monthlyGoal }} Monthly Workouts</span>
                        <span class="text-amber-400 font-bold">{{ $nextMilestone }}</span>
                    </div>
                </div>

                <!-- Weekly Consistency Dots (Mon - Sun Streak) -->
                <div class="relative z-10 pt-2 border-t border-slate-800/80">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[11px] font-black uppercase tracking-wider text-slate-400 flex items-center gap-1">
                            <span>⚡ Weekly Streak</span>
                        </span>
                        <span class="text-[10px] text-slate-400">
                            {{ count(array_filter($currentWeekDays, fn($d) => $d['attended'])) }} / 7 Days Active
                        </span>
                    </div>

                    <div class="grid grid-cols-7 gap-1.5 sm:gap-2 text-center">
                        @foreach($currentWeekDays as $day)
                            <div class="py-2 px-1 rounded-2xl flex flex-col items-center justify-center gap-1 border transition-all {{ $day['attended'] ? 'bg-emerald-500/20 border-emerald-500/40 text-emerald-300 shadow-xs' : ($day['isToday'] ? 'bg-amber-500/15 border-amber-500/40 text-amber-300 ring-1 ring-amber-500/30' : 'bg-slate-950/50 border-slate-800/80 text-slate-500') }}">
                                <span class="text-[10px] font-bold uppercase">{{ $day['day'] }}</span>
                                <div class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-black {{ $day['attended'] ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($day['isToday'] ? 'border-2 border-amber-400 text-amber-300' : 'bg-slate-800 text-slate-400') }}">
                                    @if($day['attended'])
                                        ✓
                                    @else
                                        {{ $day['date'] }}
                                    @endif
                                </div>
                                <span class="text-[9px] font-bold uppercase tracking-tight">
                                    @if($day['attended'])
                                        Done
                                    @elseif($day['isToday'])
                                        Today
                                    @else
                                        —
                                    @endif
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- APP SEGMENTED TABS (HOME, WORKOUT, DIET, VISITS, PASS) -->
            <div class="flex items-center gap-1.5 p-1 rounded-2xl bg-slate-200/80 dark:bg-slate-900 border border-slate-300/60 dark:border-slate-800 text-xs font-bold">
                <button type="button" 
                        @click="setPortalTab('overview')" 
                        :class="tab === 'overview' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Home
                </button>
                <button type="button" 
                        @click="setPortalTab('workouts')" 
                        :class="tab === 'workouts' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Workout
                </button>
                <button type="button" 
                        @click="setPortalTab('diets')" 
                        :class="tab === 'diets' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Diet
                </button>
                <button type="button" 
                        @click="setPortalTab('attendance')" 
                        :class="tab === 'attendance' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Visits
                </button>
                <button type="button" 
                        @click="setPortalTab('memberships')" 
                        :class="tab === 'memberships' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Pass
                </button>
            </div>

            <!-- TAB 1: HOME / OVERVIEW -->
            <div x-show="tab === 'overview'" x-cloak class="space-y-4">
                
                <!-- Today's Workout Quick Card -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold text-base shrink-0">
                                🏋️
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Workout Routine</h3>
                                <p class="text-[11px] font-medium text-slate-400">{{ $activeWorkout ? $activeWorkout->title : 'No Routine Prescribed' }}</p>
                            </div>
                        </div>
                        <button type="button" @click="setPortalTab('workouts')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">View All &rarr;</button>
                    </div>

                    @if($activeWorkout && $activeWorkout->exercises->isNotEmpty())
                        <div class="grid grid-cols-2 gap-2 pt-1">
                            @foreach($activeWorkout->exercises->take(4) as $ex)
                                <div class="p-3 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                                    <div class="min-w-0 pr-1">
                                        <div class="text-xs font-black text-slate-900 dark:text-white truncate">{{ $ex->exercise_name }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $ex->target_muscle ?? 'General' }}</div>
                                    </div>
                                    <span class="text-xs font-bold text-amber-500 shrink-0">{{ $ex->sets }}×{{ $ex->reps }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-5 px-4 text-center rounded-2xl bg-slate-50 dark:bg-slate-950/40 border border-dashed border-slate-200 dark:border-slate-800 space-y-1.5">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">No workout routine assigned yet</div>
                            <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                Contact your gym trainer to assign a personalized routine suited to your fitness goals.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Today's Nutrition / Diet Quick Card -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold text-base shrink-0">
                                🥗
                            </div>
                            <div class="space-y-0.5">
                                <h3 class="text-sm font-black text-slate-900 dark:text-white tracking-tight">Nutrition Target</h3>
                                <p class="text-[11px] font-medium text-slate-400">{{ $activeDiet ? ($activeDiet->daily_calories . ' kcal goal') : 'Custom Nutrition' }}</p>
                            </div>
                        </div>
                        <button type="button" @click="setPortalTab('diets')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">Details &rarr;</button>
                    </div>

                    @if($activeDiet)
                        <div class="grid grid-cols-3 gap-2 pt-1 text-center">
                            <div class="p-3 rounded-2xl bg-blue-500/10 border border-blue-500/20">
                                <span class="text-[10px] font-black uppercase text-blue-500 tracking-wider">Protein</span>
                                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $activeDiet->protein_grams ?? 120 }}g</div>
                            </div>
                            <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20">
                                <span class="text-[10px] font-black uppercase text-emerald-500 tracking-wider">Carbs</span>
                                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $activeDiet->carbs_grams ?? 200 }}g</div>
                            </div>
                            <div class="p-3 rounded-2xl bg-rose-500/10 border border-rose-500/20">
                                <span class="text-[10px] font-black uppercase text-rose-500 tracking-wider">Fats</span>
                                <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">{{ $activeDiet->fat_grams ?? 60 }}g</div>
                            </div>
                        </div>
                    @else
                        <div class="py-5 px-4 text-center rounded-2xl bg-slate-50 dark:bg-slate-950/40 border border-dashed border-slate-200 dark:border-slate-800 space-y-1.5">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">No diet plan assigned yet</div>
                            <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                Ask your trainer or nutritionist to generate a personalized diet and meal timetable.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Assigned Amenities Banner (If PT or Locker Exists) -->
                @if($assignedLocker || $activePt)
                    <div class="grid grid-cols-2 gap-3">
                        @if($assignedLocker)
                            <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-amber-500">My Locker</span>
                                    <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5 font-mono">#{{ $assignedLocker->locker_number }}</div>
                                </div>
                                <span class="text-xl">🔐</span>
                            </div>
                        @endif

                        @if($activePt)
                            <div class="p-4 rounded-2xl bg-purple-500/10 border border-purple-500/20 flex items-center justify-between">
                                <div>
                                    <span class="text-[10px] font-black uppercase tracking-wider text-purple-500">PT Sessions</span>
                                    <div class="text-base font-black text-slate-900 dark:text-white mt-0.5">
                                        {{ max(0, $activePt->total_sessions - $activePt->used_sessions) }} <span class="text-xs font-normal text-slate-400">Left</span>
                                    </div>
                                </div>
                                <span class="text-xl">🏋️</span>
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Recent Visits Preview -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Recent Gym Visits</h3>
                        <button type="button" @click="setPortalTab('attendance')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">Full Log &rarr;</button>
                    </div>

                    @if($member->attendance->isNotEmpty())
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($member->attendance->take(3) as $att)
                                <div class="py-2.5 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $att->date ? $att->date->format('D, d M Y') : ($att->check_in ? $att->check_in->format('D, d M Y') : 'Recent Visit') }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $att->check_in ? $att->check_in->format('h:i A') : 'Recorded' }}</div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                        {{ $att->status ?? 'Present' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950/50 rounded-2xl">
                            No attendance visits recorded yet.
                        </div>
                    @endif
                </div>

                <!-- Latest Receipts Quick Access -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-3">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Latest Fee Receipt</h3>
                        <button type="button" @click="setPortalTab('memberships')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">All Receipts &rarr;</button>
                    </div>

                    @if($member->payments->isNotEmpty())
                        @php $firstPayment = $member->payments->first(); @endphp
                        <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between">
                            <div>
                                <div class="text-xs font-mono font-black text-slate-900 dark:text-white">{{ $firstPayment->receipt_number }}</div>
                                <div class="text-[11px] text-slate-400">{{ $firstPayment->payment_date?->format('d M, Y') ?? 'N/A' }} • {{ $firstPayment->payment_method }}</div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-black text-emerald-500">{{ $currency }}{{ number_format($firstPayment->amount, 2) }}</span>
                                <a href="{{ route('app.invoices.show', $firstPayment->id) }}" target="_blank" class="p-2 rounded-xl bg-amber-500/10 text-amber-500 hover:bg-amber-500 hover:text-slate-950 transition-all font-bold text-xs" title="Download Invoice">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950/50 rounded-2xl">
                            No payment records found.
                        </div>
                    @endif
                </div>

            </div>

            <!-- TAB 2: WORKOUT PLANS -->
            <div x-show="tab === 'workouts'" x-cloak class="space-y-4">
                @if($member->workoutPlans->isNotEmpty())
                    @foreach($member->workoutPlans as $plan)
                        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $plan->title }}</h3>
                                    @if($plan->goal)
                                        <p class="text-xs text-slate-400 mt-0.5">Goal: <span class="font-bold text-slate-700 dark:text-slate-300">{{ $plan->goal }}</span></p>
                                    @endif
                                </div>
                                <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                    {{ $plan->level ?? 'Custom' }}
                                </span>
                            </div>

                            @if(!empty($plan->notes))
                                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/20 text-xs text-amber-400/90 leading-relaxed">
                                    <span class="font-black uppercase tracking-wider block text-[10px] mb-0.5">Coach's Instructions:</span>
                                    {{ $plan->notes }}
                                </div>
                            @endif

                            <div class="space-y-2.5">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Assigned Exercises</h4>
                                @if($plan->exercises->isNotEmpty())
                                    <div class="space-y-2">
                                        @foreach($plan->exercises as $idx => $ex)
                                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 flex items-center justify-between gap-3">
                                                <div class="flex items-center gap-3 min-w-0">
                                                    <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 font-bold text-xs flex items-center justify-center shrink-0">
                                                        {{ $idx + 1 }}
                                                    </span>
                                                    <div class="min-w-0">
                                                        <div class="text-sm font-extrabold text-slate-900 dark:text-white truncate">{{ $ex->exercise_name }}</div>
                                                        <div class="text-[11px] text-slate-400">{{ $ex->target_muscle ?? 'General' }}</div>
                                                    </div>
                                                </div>

                                                <div class="text-right shrink-0 space-y-0.5">
                                                    <span class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-black text-amber-500 block">
                                                        {{ $ex->sets }} × {{ $ex->reps }}
                                                    </span>
                                                    @if($ex->weight_kg)
                                                        <span class="text-[10px] text-slate-400 font-bold block">{{ $ex->weight_kg }} kg</span>
                                                    @endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="py-6 text-center text-xs text-slate-400">No exercise list found for this plan.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto text-xl">
                            🏋️
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Workout Routine Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">Ask your gym trainer to prescribe a custom exercise routine suited to your fitness goals.</p>
                    </div>
                @endif
            </div>

            <!-- TAB 3: DIET & NUTRITION -->
            <div x-show="tab === 'diets'" x-cloak class="space-y-4">
                @if($member->dietPlans->isNotEmpty())
                    @foreach($member->dietPlans as $diet)
                        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                                <div>
                                    <h3 class="text-lg font-black text-slate-900 dark:text-white">{{ $diet->title }}</h3>
                                    <p class="text-xs text-slate-400 mt-0.5">Prescribed Nutrition & Macro Targets</p>
                                </div>
                                <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                    {{ $diet->daily_calories ?? 2000 }} kcal
                                </span>
                            </div>

                            <!-- Macro Breakdown -->
                            <div class="grid grid-cols-3 gap-2">
                                <div class="p-3 rounded-2xl bg-blue-500/10 border border-blue-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-blue-500">Protein</span>
                                    <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ $diet->protein_grams ?? 120 }}g</div>
                                </div>
                                <div class="p-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-emerald-500">Carbs</span>
                                    <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ $diet->carbs_grams ?? 220 }}g</div>
                                </div>
                                <div class="p-3 rounded-2xl bg-rose-500/10 border border-rose-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-rose-500">Fats</span>
                                    <div class="text-lg font-black text-slate-900 dark:text-white mt-0.5">{{ $diet->fat_grams ?? 60 }}g</div>
                                </div>
                            </div>

                            @if(!empty($diet->guidelines))
                                <div class="p-3.5 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 text-xs text-emerald-400/90 leading-relaxed">
                                    <span class="font-black uppercase tracking-wider block text-[10px] mb-0.5">Guidelines & Rules:</span>
                                    {{ $diet->guidelines }}
                                </div>
                            @endif

                            <!-- Meal Schedule -->
                            <div class="space-y-2.5">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Daily Meals</h4>
                                @if($diet->meals->isNotEmpty())
                                    <div class="space-y-2.5">
                                        @foreach($diet->meals as $meal)
                                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-1.5">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-800 pb-1.5">
                                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 text-[10px] font-black uppercase">
                                                        {{ $meal->meal_name ?? 'Meal' }}
                                                    </span>
                                                    @if($meal->meal_time)
                                                        <span class="text-xs text-slate-400 font-bold">@ {{ $meal->meal_time }}</span>
                                                    @endif
                                                </div>
                                                <div class="text-xs font-medium text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                                                    {{ $meal->items }}
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="py-4 text-center text-xs text-slate-400">No meal schedule added.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-10 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center mx-auto text-xl">
                            🥗
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Diet Plan Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto leading-relaxed">Contact your trainer to prescribe a meal timetable and nutritional plan.</p>
                    </div>
                @endif
            </div>

            <!-- TAB 4: VISITS & ATTENDANCE -->
            <div x-show="tab === 'attendance'" x-cloak class="space-y-4">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Gym Visit History</h3>
                            <p class="text-xs text-slate-400">Automated check-ins via QR / Biometric</p>
                        </div>
                        <span class="px-3 py-1 rounded-xl bg-emerald-500/10 text-emerald-500 text-xs font-black border border-emerald-500/20">
                            {{ $totalCheckins }} Total Visits
                        </span>
                    </div>

                    @if($member->attendance->isNotEmpty())
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($member->attendance as $att)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-3">
                                        <div class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></div>
                                        <div>
                                            <div class="font-bold text-slate-900 dark:text-white">
                                                {{ $att->date ? $att->date->format('l, d M Y') : ($att->check_in ? $att->check_in->format('l, d M Y') : 'Recent Visit') }}
                                            </div>
                                            <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                                In: {{ $att->check_in ? $att->check_in->format('h:i A') : '—' }}
                                                @if($att->check_out)
                                                    • Out: {{ $att->check_out->format('h:i A') }}
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-500 border border-emerald-500/20 shrink-0">
                                        {{ $att->status ?? 'Present' }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-slate-400">No attendance visits recorded yet.</div>
                    @endif
                </div>
            </div>

            <!-- TAB 5: MEMBERSHIP, PASS & BILLS -->
            <div x-show="tab === 'memberships'" x-cloak class="space-y-4">
                <!-- Plan Summary Card -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Active Plan & Validity</h3>

                    @if($activeMembership)
                        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <span class="text-xs text-slate-400 font-medium">Enrolled Plan</span>
                                    <div class="text-base font-black text-slate-900 dark:text-white">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</div>
                                </div>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                    {{ $activeMembership->status }}
                                </span>
                            </div>

                            <div class="flex justify-between text-xs pt-1 border-t border-slate-200/60 dark:border-slate-800">
                                <span class="text-slate-400">Duration:</span>
                                <span class="font-bold text-slate-800 dark:text-slate-200">{{ $activeMembership->start_date?->format('d M, Y') }} &rarr; {{ $activeMembership->end_date?->format('d M, Y') }}</span>
                            </div>

                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Total Fee:</span>
                                <span class="font-bold text-slate-900 dark:text-white">{{ $currency }}{{ number_format($activeMembership->final_amount, 2) }}</span>
                            </div>

                            <div class="flex justify-between text-xs">
                                <span class="text-slate-400">Paid:</span>
                                <span class="font-bold text-emerald-500">{{ $currency }}{{ number_format($activeMembership->paid_amount, 2) }}</span>
                            </div>

                            @if($remainingAmt > 0)
                                <div class="flex justify-between text-xs pt-1 border-t border-slate-200/60 dark:border-slate-800">
                                    <span class="text-rose-500 font-bold">Balance Due:</span>
                                    <span class="font-black text-rose-500">{{ $currency }}{{ number_format($remainingAmt, 2) }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No active membership found.
                        </div>
                    @endif
                </div>

                <!-- Invoices & Receipts -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                    <h3 class="text-base font-black text-slate-900 dark:text-white">Tax Invoices & Receipts</h3>

                    @if($member->payments->isNotEmpty())
                        <div class="divide-y divide-slate-100 dark:divide-slate-800/80">
                            @foreach($member->payments as $payment)
                                <div class="py-3 flex items-center justify-between text-xs">
                                    <div class="space-y-0.5">
                                        <div class="font-mono font-black text-slate-900 dark:text-white">{{ $payment->receipt_number }}</div>
                                        <div class="text-[11px] text-slate-400">{{ $payment->payment_date?->format('d M, Y') ?? 'N/A' }} • {{ $payment->payment_method }}</div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <span class="font-black text-emerald-500">{{ $currency }}{{ number_format($payment->amount, 2) }}</span>
                                        <a href="{{ route('app.invoices.show', $payment->id) }}" target="_blank" class="px-2.5 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500 text-amber-500 hover:text-slate-950 font-bold text-xs transition-colors flex items-center gap-1 cursor-pointer">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            <span>View</span>
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-6 text-center text-xs text-slate-400">No payment receipts found.</div>
                    @endif
                </div>
            </div>

            <!-- MODAL: DIGITAL QR PASS -->
            <div x-show="showQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm">
                <div @click.away="showQrModal = false" class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 text-center space-y-5 relative text-white">
                    <button type="button" @click="showQrModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    <div>
                        <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Gym Check-in Pass</span>
                        <h3 class="text-xl font-black text-white mt-2">{{ $member->full_name }}</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $member->member_code }}</p>
                    </div>

                    <!-- QR Code -->
                    <div class="p-4 rounded-2xl bg-white mx-auto inline-block shadow-lg">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($member->qr_code_token ?? $member->member_code) }}" alt="QR Code" class="w-44 h-44 mx-auto">
                    </div>

                    <p class="text-xs text-slate-400">Scan this pass at the front desk scanner or turnstile to log your attendance.</p>

                    <button type="button" @click="showQrModal = false" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                        Close
                    </button>
                </div>
            </div>

            <!-- MODAL: CAMERA SCANNER (MEMBER SCANS RECEPTION QR) -->
            <div x-show="showPortalScanner" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm">
                <div @click.away="stopPortalScanner()" class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 text-center space-y-4 relative text-white">
                    <button type="button" @click="stopPortalScanner()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    <div>
                        <span class="px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-black uppercase tracking-wider">Self Check-In Scanner</span>
                        <h3 class="text-lg font-black text-white mt-1.5">Scan Reception QR</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Point camera at the QR Standee at the gym front desk</p>
                    </div>

                    <div id="portal-camera-reader" class="w-full aspect-square max-w-[280px] mx-auto rounded-2xl overflow-hidden bg-black border border-slate-700"></div>

                    <template x-if="portalScanMsg">
                        <p class="text-xs text-rose-400" x-text="portalScanMsg"></p>
                    </template>

                    <button type="button" @click="stopPortalScanner()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                        Cancel
                    </button>
                </div>
            </div>

        @endif

    </div>

    <!-- html5-qrcode scanner script -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</x-app-layout>