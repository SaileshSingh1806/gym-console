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
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        showQrModal: false,
        showMeasurementModal: false,
        measurementDate: '{{ now()->format('Y-m-d') }}',
        measurementWeight: '{{ $latestMeasurement['weight'] ?? '' }}',
        measurementChest: '{{ $latestMeasurement['chest'] ?? '' }}',
        measurementShoulders: '{{ $latestMeasurement['shoulders'] ?? '' }}',
        measurementWaist: '{{ $latestMeasurement['waist'] ?? '' }}',
        measurementHips: '{{ $latestMeasurement['hips'] ?? '' }}',
        measurementBicepLeft: '{{ $latestMeasurement['bicep_left'] ?? '' }}',
        measurementBicepRight: '{{ $latestMeasurement['bicep_right'] ?? '' }}',
        measurementThighLeft: '{{ $latestMeasurement['thigh_left'] ?? '' }}',
        measurementThighRight: '{{ $latestMeasurement['thigh_right'] ?? '' }}',
        measurementCalves: '{{ $latestMeasurement['calves'] ?? '' }}',
        measurementNeck: '{{ $latestMeasurement['neck'] ?? '' }}',
        measurementBodyFat: '{{ $latestMeasurement['body_fat'] ?? '' }}',
        measurementNotes: ''
    }" class="space-y-6">

        @if(! $member)
            <!-- No Member Profile Found Notice -->
            <div class="p-8 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm text-center max-w-xl mx-auto space-y-4">
                <div class="w-16 h-16 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <h2 class="text-xl font-bold text-slate-900 dark:text-white">Welcome, {{ auth()->user()->name }}!</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400">Your login is active, but your profile has not yet been linked to an active gym member profile. Please contact your gym reception to link your account or verify your membership details.</p>
            </div>
        @else

            <!-- Profile Banner & Top Info -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-slate-850 to-slate-900 border border-slate-800 shadow-xl p-6 sm:p-8 text-white">
                <!-- Background Ambient Glow -->
                <div class="absolute -top-24 -right-24 w-80 h-80 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-orange-600/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div class="flex items-center gap-4 sm:gap-6">
                        <!-- Member Photo / Avatar -->
                        <div class="relative shrink-0">
                            @if(!empty($member->photo_path))
                                <img src="{{ asset('storage/' . $member->photo_path) }}" alt="{{ $member->full_name }}" class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl object-cover border-2 border-amber-500/40 shadow-lg">
                            @else
                                <div class="w-20 h-20 sm:w-24 sm:h-24 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 flex items-center justify-center text-slate-950 font-black text-2xl sm:text-3xl shadow-lg shadow-orange-500/20">
                                    {{ strtoupper(substr($member->first_name ?: 'M', 0, 1)) }}{{ strtoupper(substr($member->last_name ?: '', 0, 1)) }}
                                </div>
                            @endif
                            <span class="absolute -bottom-1 -right-1 px-2 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider {{ $member->status === 'ACTIVE' ? 'bg-emerald-500 text-slate-950 shadow-sm' : ($member->status === 'SUSPENDED' ? 'bg-rose-500 text-white' : 'bg-slate-700 text-slate-200') }}">
                                {{ $member->status }}
                            </span>
                        </div>

                        <!-- Member Info -->
                        <div class="space-y-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <h1 class="text-2xl sm:text-3xl font-black text-white tracking-tight">{{ $member->full_name }}</h1>
                                <span class="px-2.5 py-0.5 rounded-lg bg-amber-500/20 text-amber-400 border border-amber-500/30 text-xs font-mono font-bold">
                                    {{ $member->member_code }}
                                </span>
                            </div>
                            <div class="flex flex-wrap items-center gap-y-1 gap-x-4 text-xs text-slate-400">
                                @if($member->phone)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                        {{ $member->phone }}
                                    </span>
                                @endif
                                @if($member->branch)
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                        {{ $member->branch->name }}
                                    </span>
                                @endif
                                @if($member->assignedTrainer)
                                    <span class="flex items-center gap-1 text-amber-400">
                                        <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                        Trainer: {{ $member->assignedTrainer->full_name }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Quick Stats & Digital Pass Button -->
                    <div class="flex flex-wrap items-center gap-3 self-stretch md:self-auto justify-start md:justify-end">
                        <button type="button" @click="showQrModal = true" class="px-4 py-2.5 rounded-2xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-lg shadow-amber-500/20 flex items-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Digital QR Pass</span>
                        </button>
                    </div>
                </div>

                <!-- Membership Countdown Bar -->
                @if($activeMembership)
                    <div class="mt-6 pt-5 border-t border-slate-800/80 flex flex-wrap items-center justify-between gap-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs text-slate-400 font-semibold">Active Plan</div>
                                <div class="text-sm font-black text-white">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-6 text-xs">
                            <div>
                                <span class="text-slate-400">Valid Till:</span>
                                <span class="font-bold text-white ml-1">{{ $activeMembership->end_date?->format('d M, Y') ?? 'N/A' }}</span>
                            </div>
                            @if($daysLeft !== null)
                                <span class="px-3 py-1 rounded-xl text-xs font-black {{ $daysLeft > 10 ? 'bg-emerald-500/20 text-emerald-400 border border-emerald-500/30' : ($daysLeft >= 0 ? 'bg-amber-500/20 text-amber-400 border border-amber-500/30' : 'bg-rose-500/20 text-rose-400 border border-rose-500/30') }}">
                                    {{ $daysLeft >= 0 ? ($daysLeft . ' Days Remaining') : ('Expired ' . abs($daysLeft) . ' Days Ago') }}
                                </span>
                            @endif
                            @if($remainingAmt > 0)
                                <span class="px-3 py-1 rounded-xl text-xs font-black bg-rose-500/20 text-rose-400 border border-rose-500/30">
                                    Due: {{ $currency }}{{ number_format($remainingAmt, 2) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Quick Key Metrics Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
                <!-- 1. Membership Status -->
                <div @click="tab = 'memberships'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Plan</span>
                        <div class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $activeMembership->plan?->name ?? 'No Plan' }}</div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $daysLeft !== null && $daysLeft >= 0 ? $daysLeft . ' days left' : 'Inactive' }}</div>
                </div>

                <!-- 2. Workouts -->
                <div @click="tab = 'workouts'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Workouts</span>
                        <div class="w-6 h-6 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $activeWorkout?->title ?? 'None' }}</div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $activeWorkout ? $activeWorkout->exercises->count() . ' Exercises' : 'No Routine' }}</div>
                </div>

                <!-- 3. Diet & Nutrition -->
                <div @click="tab = 'diets'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Diet</span>
                        <div class="w-6 h-6 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $activeDiet?->daily_calories ? $activeDiet->daily_calories . ' kcal' : 'Custom Diet' }}</div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $activeDiet ? $activeDiet->meals->count() . ' Meals Daily' : 'Not assigned' }}</div>
                </div>

                <!-- 4. PT Sessions -->
                @php
                    $activePt = $member->memberPtPackages->where('status', 'ACTIVE')->first();
                @endphp
                <div @click="tab = 'pt'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">PT Sessions</span>
                        <div class="w-6 h-6 rounded-lg bg-purple-500/10 text-purple-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">
                        {{ $activePt ? max(0, $activePt->total_sessions - $activePt->used_sessions) . ' Left' : '0' }}
                    </div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $activePt ? 'of ' . $activePt->total_sessions . ' total' : 'No active PT' }}</div>
                </div>

                <!-- 5. Services & Locker -->
                @php
                    $activeServicesCount = $member->serviceBookings->where('status', 'ACTIVE')->count();
                    $assignedLocker = $member->serviceBookings->where('status', 'ACTIVE')->whereNotNull('locker_number')->first();
                @endphp
                <div @click="tab = 'services'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Services</span>
                        <div class="w-6 h-6 rounded-lg bg-orange-500/10 text-orange-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $assignedLocker ? 'Locker #' . $assignedLocker->locker_number : ($activeServicesCount . ' Bookings') }}</div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">{{ $activeServicesCount > 0 ? 'Active amenities' : 'None' }}</div>
                </div>

                <!-- 6. Attendance Visits -->
                @php
                    $thisMonthCheckins = $member->attendance->filter(fn($a) => ($a->date && \Carbon\Carbon::parse($a->date)->isCurrentMonth()) || ($a->check_in && \Carbon\Carbon::parse($a->check_in)->isCurrentMonth()))->count();
                @endphp
                <div @click="tab = 'attendance'" class="p-4 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 hover:border-amber-500/50 cursor-pointer transition-all shadow-sm">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-[10px] font-black uppercase tracking-wider text-slate-500 dark:text-slate-400">Check-ins</span>
                        <div class="w-6 h-6 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-sm font-black text-slate-900 dark:text-white truncate">{{ $thisMonthCheckins }} Visits</div>
                    <div class="text-[11px] font-bold text-slate-400 mt-0.5">This Month</div>
                </div>
            </div>

            <!-- Tab Navigation Bar -->
            <div class="border-b border-slate-200 dark:border-slate-800 overflow-x-auto">
                <div class="flex items-center gap-2 min-w-max pb-2">
                    <button type="button" @click="tab = 'overview'" :class="tab === 'overview' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Overview</span>
                    </button>

                    <button type="button" @click="tab = 'memberships'" :class="tab === 'memberships' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <span>Membership & Receipts</span>
                    </button>

                    <button type="button" @click="tab = 'workouts'" :class="tab === 'workouts' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Workouts</span>
                    </button>

                    <button type="button" @click="tab = 'diets'" :class="tab === 'diets' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Diet & Nutrition</span>
                    </button>

                    <button type="button" @click="tab = 'pt'" :class="tab === 'pt' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span>Personal Training</span>
                    </button>

                    <button type="button" @click="tab = 'services'" :class="tab === 'services' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                        <span>Services & Lockers</span>
                    </button>

                    <button type="button" @click="tab = 'classes'" :class="tab === 'classes' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span>Classes</span>
                    </button>

                    <button type="button" @click="tab = 'measurements'" :class="tab === 'measurements' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span>Measurements</span>
                    </button>

                    <button type="button" @click="tab = 'attendance'" :class="tab === 'attendance' ? 'bg-slate-900 text-white dark:bg-slate-800 dark:text-white font-black' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-bold'" class="px-4 py-2 rounded-xl text-xs transition-all cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Attendance</span>
                    </button>
                </div>
            </div>

            <!-- TAB 1: OVERVIEW -->
            <div x-show="tab === 'overview'" x-cloak class="space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Today's Workout Quick View -->
                    <div class="lg:col-span-2 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                </div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Prescribed Workout Routine</h3>
                            </div>
                            <button @click="tab = 'workouts'" class="text-xs font-bold text-amber-500 hover:underline">View All &rarr;</button>
                        </div>

                        @if($activeWorkout && $activeWorkout->exercises->isNotEmpty())
                            <div class="space-y-2.5">
                                <div class="flex items-center justify-between text-xs text-slate-500 dark:text-slate-400 bg-slate-50 dark:bg-slate-950 p-3 rounded-xl border border-slate-100 dark:border-slate-800">
                                    <span class="font-bold text-slate-800 dark:text-slate-200">{{ $activeWorkout->title }}</span>
                                    <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 font-bold uppercase text-[10px]">{{ $activeWorkout->level ?? 'Custom' }}</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5">
                                    @foreach($activeWorkout->exercises->take(4) as $ex)
                                        <div class="p-3 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                                            <div>
                                                <div class="text-xs font-black text-slate-900 dark:text-white">{{ $ex->exercise_name }}</div>
                                                <div class="text-[10px] text-slate-400 font-medium">{{ $ex->target_muscle ?? 'General' }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs font-bold text-amber-500">{{ $ex->sets }} × {{ $ex->reps }}</div>
                                                @if($ex->weight_kg)
                                                    <div class="text-[10px] text-slate-400">{{ $ex->weight_kg }} kg</div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-xs text-slate-400">
                                No active workout plan prescribed yet. Contact your gym trainer to assign a personalized routine.
                            </div>
                        @endif
                    </div>

                    <!-- Today's Nutrition & Calories Goal -->
                    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 rounded-xl bg-emerald-500/10 text-emerald-500 flex items-center justify-center font-bold">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                </div>
                                <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Nutrition Target</h3>
                            </div>
                            <button @click="tab = 'diets'" class="text-xs font-bold text-amber-500 hover:underline">Details &rarr;</button>
                        </div>

                        @if($activeDiet)
                            <div class="space-y-4">
                                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-500/20 text-center">
                                    <div class="text-[10px] font-black uppercase text-emerald-500 tracking-wider">Target Daily Calories</div>
                                    <div class="text-3xl font-black text-slate-900 dark:text-white mt-1">{{ $activeDiet->daily_calories ?: '2,000' }} <span class="text-xs text-slate-400 font-normal">kcal</span></div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-500">Protein:</span>
                                        <span class="text-slate-900 dark:text-white">{{ $activeDiet->protein_grams ?? 120 }}g</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-amber-500 h-2 rounded-full" style="width: 75%"></div>
                                    </div>

                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-500">Carbohydrates:</span>
                                        <span class="text-slate-900 dark:text-white">{{ $activeDiet->carbs_grams ?? 200 }}g</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-blue-500 h-2 rounded-full" style="width: 60%"></div>
                                    </div>

                                    <div class="flex justify-between text-xs font-bold">
                                        <span class="text-slate-500">Fats:</span>
                                        <span class="text-slate-900 dark:text-white">{{ $activeDiet->fat_grams ?? 60 }}g</span>
                                    </div>
                                    <div class="w-full bg-slate-100 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-rose-500 h-2 rounded-full" style="width: 45%"></div>
                                    </div>
                                </div>
                            </div>
                        @else
                            <div class="py-8 text-center text-xs text-slate-400">
                                No custom diet assigned yet.
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Recent Payments & Attendance Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Latest Receipts -->
                    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Recent Payment Receipts</h3>
                            <button @click="tab = 'memberships'" class="text-xs font-bold text-amber-500 hover:underline">All Receipts &rarr;</button>
                        </div>
                        @if($member->payments->isNotEmpty())
                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($member->payments->take(3) as $payment)
                                    <div class="py-3 flex items-center justify-between">
                                        <div>
                                            <div class="text-xs font-black text-slate-900 dark:text-white">{{ $payment->receipt_number }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $payment->payment_date?->format('d M, Y') ?? 'N/A' }} • {{ $payment->payment_method }}</div>
                                        </div>
                                        <div class="text-right flex items-center gap-3">
                                            <span class="text-sm font-black text-emerald-500">{{ $currency }}{{ number_format($payment->amount, 2) }}</span>
                                            <a href="{{ route('app.invoices.show', $payment->id) }}" target="_blank" class="p-1.5 rounded-lg bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:text-amber-500" title="View Tax Invoice">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-xs text-slate-400">No payment records found.</div>
                        @endif
                    </div>

                    <!-- Latest Attendance Log -->
                    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-extrabold text-slate-900 dark:text-white">Recent Gym Visits</h3>
                            <button @click="tab = 'attendance'" class="text-xs font-bold text-amber-500 hover:underline">Full Log &rarr;</button>
                        </div>
                        @if($member->attendance->isNotEmpty())
                            <div class="divide-y divide-slate-100 dark:divide-slate-800">
                                @foreach($member->attendance->take(3) as $att)
                                    <div class="py-3 flex items-center justify-between">
                                        <div class="flex items-center gap-3">
                                            <div class="w-2.5 h-2.5 rounded-full bg-emerald-500"></div>
                                            <div>
                                                <div class="text-xs font-bold text-slate-900 dark:text-white">{{ $att->date ? $att->date->format('l, d M Y') : ($att->check_in ? $att->check_in->format('l, d M Y') : 'Recent Visit') }}</div>
                                                <div class="text-[11px] text-slate-400">{{ $att->check_in ? $att->check_in->format('h:i A') : 'Checked In' }}</div>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">{{ $att->status ?? 'Present' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="py-6 text-center text-xs text-slate-400">No attendance visits recorded yet.</div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 2: MEMBERSHIP & BILLING -->
            <div x-show="tab === 'memberships'" x-cloak class="space-y-6">
                <!-- Current Active Membership Card -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Membership Status & Billing Summary</h3>

                    @if($activeMembership)
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800">
                            <div>
                                <span class="text-xs text-slate-400 font-medium">Enrolled Plan</span>
                                <div class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $activeMembership->plan?->name ?? 'Standard Plan' }}</div>
                                <span class="inline-block mt-2 px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                    {{ $activeMembership->status }}
                                </span>
                            </div>

                            <div>
                                <span class="text-xs text-slate-400 font-medium">Duration & Validity</span>
                                <div class="text-sm font-bold text-slate-900 dark:text-white mt-1">
                                    {{ $activeMembership->start_date?->format('d M, Y') }} &rarr; {{ $activeMembership->end_date?->format('d M, Y') }}
                                </div>
                                <div class="text-xs text-amber-500 font-bold mt-1">
                                    {{ $daysLeft !== null && $daysLeft >= 0 ? ($daysLeft . ' days remaining') : 'Expired' }}
                                </div>
                            </div>

                            <div>
                                <span class="text-xs text-slate-400 font-medium">Fee Breakdown</span>
                                <div class="flex justify-between text-xs mt-1">
                                    <span class="text-slate-500">Total Amount:</span>
                                    <span class="font-bold text-slate-900 dark:text-white">{{ $currency }}{{ number_format($activeMembership->final_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs mt-0.5">
                                    <span class="text-slate-500">Paid Amount:</span>
                                    <span class="font-bold text-emerald-500">{{ $currency }}{{ number_format($activeMembership->paid_amount, 2) }}</span>
                                </div>
                                <div class="flex justify-between text-xs mt-0.5 pt-1 border-t border-slate-200 dark:border-slate-800">
                                    <span class="text-slate-500 font-bold">Balance Due:</span>
                                    <span class="font-bold {{ $remainingAmt > 0 ? 'text-rose-500' : 'text-slate-400' }}">{{ $currency }}{{ number_format($remainingAmt, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="p-8 text-center text-sm text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No active membership found. Please contact the gym reception.
                        </div>
                    @endif

                    <!-- Fee Invoices & Payment History -->
                    <div class="space-y-4 pt-4">
                        <h4 class="text-base font-extrabold text-slate-900 dark:text-white">Official Tax Invoices & Receipts</h4>

                        @if($member->payments->isNotEmpty())
                            <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                                <table class="w-full text-left text-xs">
                                    <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-extrabold uppercase text-[10px]">
                                        <tr>
                                            <th class="px-4 py-3">Receipt / Invoice #</th>
                                            <th class="px-4 py-3">Date</th>
                                            <th class="px-4 py-3">Payment For</th>
                                            <th class="px-4 py-3">Mode</th>
                                            <th class="px-4 py-3">Amount</th>
                                            <th class="px-4 py-3 text-right">Invoice</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                        @foreach($member->payments as $payment)
                                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                                <td class="px-4 py-3.5 font-mono font-bold text-slate-900 dark:text-white">{{ $payment->receipt_number }}</td>
                                                <td class="px-4 py-3.5 text-slate-500">{{ $payment->payment_date?->format('d M, Y') ?? 'N/A' }}</td>
                                                <td class="px-4 py-3.5 font-medium text-slate-800 dark:text-slate-200">
                                                    {{ $payment->membership?->plan?->name ?? 'Gym Membership / Fees' }}
                                                </td>
                                                <td class="px-4 py-3.5">
                                                    <span class="px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 font-semibold text-[10px]">
                                                        {{ $payment->payment_method }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3.5 font-black text-emerald-500">{{ $currency }}{{ number_format($payment->amount, 2) }}</td>
                                                <td class="px-4 py-3.5 text-right">
                                                    <a href="{{ route('app.invoices.show', $payment->id) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 text-amber-500 font-bold text-xs transition-colors">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                                                        <span>Print / PDF</span>
                                                    </a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="p-8 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                                No payment receipts recorded yet.
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 3: WORKOUT PLANS -->
            <div x-show="tab === 'workouts'" x-cloak class="space-y-6">
                @if($member->workoutPlans->isNotEmpty())
                    @foreach($member->workoutPlans as $plan)
                        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                                <div>
                                    <div class="flex items-center gap-3">
                                        <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $plan->title }}</h3>
                                        <span class="px-2.5 py-0.5 rounded-md text-[10px] font-black uppercase tracking-wider bg-amber-500/10 text-amber-500 border border-amber-500/20">
                                            {{ $plan->level ?? 'Custom' }}
                                        </span>
                                    </div>
                                    @if($plan->goal)
                                        <p class="text-xs text-slate-400 mt-1">Goal: <span class="text-slate-800 dark:text-slate-200 font-bold">{{ $plan->goal }}</span></p>
                                    @endif
                                </div>

                                <div class="text-right text-xs text-slate-400">
                                    @if($plan->trainer)
                                        <div>Prescribed by: <span class="font-bold text-white">{{ $plan->trainer->full_name }}</span></div>
                                    @endif
                                    @if($plan->start_date)
                                        <div>Assigned: {{ $plan->start_date->format('d M, Y') }}</div>
                                    @endif
                                </div>
                            </div>

                            @if(!empty($plan->notes))
                                <div class="p-4 rounded-2xl bg-amber-500/5 border border-amber-500/20 text-xs text-amber-400/90 leading-relaxed">
                                    <span class="font-bold uppercase tracking-wider block text-[10px] mb-1">Coach's Instructions & Notes:</span>
                                    {{ $plan->notes }}
                                </div>
                            @endif

                            <!-- Exercises List -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Assigned Exercises & Sets</h4>

                                @if($plan->exercises->isNotEmpty())
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        @foreach($plan->exercises as $idx => $ex)
                                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800/80 flex items-start justify-between gap-4">
                                                <div class="space-y-1">
                                                    <div class="flex items-center gap-2">
                                                        <span class="w-5 h-5 rounded-md bg-amber-500/10 text-amber-500 font-bold text-[10px] flex items-center justify-center">
                                                            {{ $idx + 1 }}
                                                        </span>
                                                        <div class="text-sm font-extrabold text-slate-900 dark:text-white">{{ $ex->exercise_name }}</div>
                                                    </div>
                                                    <div class="text-xs text-slate-400 font-medium">Target: {{ $ex->target_muscle ?? 'General' }}</div>
                                                    @if(!empty($ex->notes))
                                                        <div class="text-[11px] text-slate-500 italic mt-1">"{{ $ex->notes }}"</div>
                                                    @endif
                                                </div>

                                                <div class="text-right shrink-0 space-y-1">
                                                    <div class="px-2.5 py-1 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-xs font-black text-amber-500">
                                                        {{ $ex->sets }} Sets × {{ $ex->reps }}
                                                    </div>
                                                    @if($ex->weight_kg)
                                                        <div class="text-xs font-bold text-slate-700 dark:text-slate-300">{{ $ex->weight_kg }} kg</div>
                                                    @endif
                                                    @if($ex->rest_seconds)
                                                        <div class="text-[10px] text-slate-400">{{ $ex->rest_seconds }}s rest</div>
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
                    <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Workout Plans Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">Ask your trainer or gym coach to prescribe a custom exercise routine suited to your fitness goals.</p>
                    </div>
                @endif
            </div>

            <!-- TAB 4: DIET & NUTRITION -->
            <div x-show="tab === 'diets'" x-cloak class="space-y-6">
                @if($member->dietPlans->isNotEmpty())
                    @foreach($member->dietPlans as $diet)
                        <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-100 dark:border-slate-800 pb-4">
                                <div>
                                    <h3 class="text-xl font-black text-slate-900 dark:text-white">{{ $diet->title }}</h3>
                                    <p class="text-xs text-slate-400 mt-1">Prescribed Nutrition & Macro Targets</p>
                                </div>
                                <div class="text-right text-xs text-slate-400">
                                    @if($diet->trainer)
                                        <div>Prescribed by: <span class="font-bold text-white">{{ $diet->trainer->full_name }}</span></div>
                                    @endif
                                    @if($diet->start_date)
                                        <div>Assigned: {{ $diet->start_date->format('d M, Y') }}</div>
                                    @endif
                                </div>
                            </div>

                            <!-- Macro Breakdown Banner -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                <div class="p-4 rounded-2xl bg-gradient-to-br from-amber-500/10 to-transparent border border-amber-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-amber-500 tracking-wider">Total Calories</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $diet->daily_calories ?? 2000 }} <span class="text-xs font-normal text-slate-400">kcal</span></div>
                                </div>

                                <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-500/10 to-transparent border border-blue-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-blue-500 tracking-wider">Protein Target</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $diet->protein_grams ?? 120 }} <span class="text-xs font-normal text-slate-400">g</span></div>
                                </div>

                                <div class="p-4 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-transparent border border-emerald-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-emerald-500 tracking-wider">Carbs Target</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $diet->carbs_grams ?? 220 }} <span class="text-xs font-normal text-slate-400">g</span></div>
                                </div>

                                <div class="p-4 rounded-2xl bg-gradient-to-br from-rose-500/10 to-transparent border border-rose-500/20 text-center">
                                    <span class="text-[10px] font-black uppercase text-rose-500 tracking-wider">Fats Target</span>
                                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-1">{{ $diet->fat_grams ?? 60 }} <span class="text-xs font-normal text-slate-400">g</span></div>
                                </div>
                            </div>

                            @if(!empty($diet->guidelines))
                                <div class="p-4 rounded-2xl bg-emerald-500/5 border border-emerald-500/20 text-xs text-emerald-400/90 leading-relaxed">
                                    <span class="font-bold uppercase tracking-wider block text-[10px] mb-1">Nutrition Guidelines & Rules:</span>
                                    {{ $diet->guidelines }}
                                </div>
                            @endif

                            <!-- Meal Timetable -->
                            <div class="space-y-3">
                                <h4 class="text-xs font-black uppercase tracking-wider text-slate-400">Daily Meal Schedule</h4>

                                @if($diet->meals->isNotEmpty())
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        @foreach($diet->meals as $meal)
                                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                                <div class="flex items-center justify-between border-b border-slate-200/60 dark:border-slate-800 pb-2">
                                                    <div class="flex items-center gap-2">
                                                        <span class="px-2 py-0.5 rounded bg-amber-500/10 text-amber-500 text-[10px] font-black uppercase">
                                                            {{ $meal->meal_name ?? 'Meal' }}
                                                        </span>
                                                        @if($meal->meal_time)
                                                            <span class="text-xs text-slate-400 font-bold">@ {{ $meal->meal_time }}</span>
                                                        @endif
                                                    </div>
                                                    @if($meal->calories)
                                                        <span class="text-xs font-black text-slate-700 dark:text-slate-300">{{ $meal->calories }} kcal</span>
                                                    @endif
                                                </div>

                                                <div class="text-xs font-medium text-slate-800 dark:text-slate-200 whitespace-pre-line leading-relaxed">
                                                    {{ $meal->items }}
                                                </div>

                                                @if(!empty($meal->notes))
                                                    <div class="text-[11px] text-slate-400 italic pt-1">
                                                        Note: {{ $meal->notes }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="py-6 text-center text-xs text-slate-400">No meal timetable registered for this plan.</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="p-12 rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-center space-y-3">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mx-auto">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        </div>
                        <h4 class="text-base font-bold text-slate-900 dark:text-white">No Diet Plan Assigned</h4>
                        <p class="text-xs text-slate-400 max-w-sm mx-auto">Contact your nutritionist or trainer to generate a personalized diet and meal timetable.</p>
                    </div>
                @endif
            </div>

            <!-- TAB 5: PERSONAL TRAINING -->
            <div x-show="tab === 'pt'" x-cloak class="space-y-6">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Assigned Personal Training (PT) Packages</h3>

                    @if($member->memberPtPackages->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            @foreach($member->memberPtPackages as $ptPkg)
                                <div class="p-6 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-base font-black text-slate-900 dark:text-white">{{ $ptPkg->package_name ?: ($ptPkg->ptPlan?->name ?? 'Personal Training') }}</h4>
                                            <p class="text-xs text-slate-400">Coach: <span class="font-bold text-amber-500">{{ $ptPkg->trainer?->full_name ?? 'Assigned Trainer' }}</span></p>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-lg text-xs font-black uppercase {{ $ptPkg->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-200 dark:bg-slate-800 text-slate-400' }}">
                                            {{ $ptPkg->status }}
                                        </span>
                                    </div>

                                    <!-- Session Progress Bar -->
                                    <div class="space-y-2">
                                        <div class="flex justify-between text-xs font-bold">
                                            <span class="text-slate-500">Sessions Completed:</span>
                                            <span class="text-slate-900 dark:text-white">{{ $ptPkg->used_sessions }} / {{ $ptPkg->total_sessions }}</span>
                                        </div>
                                        @php
                                            $ptPercent = $ptPkg->total_sessions > 0 ? min(100, round(($ptPkg->used_sessions / $ptPkg->total_sessions) * 100)) : 0;
                                        @endphp
                                        <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2.5 overflow-hidden">
                                            <div class="bg-gradient-to-r from-amber-500 to-orange-500 h-2.5 rounded-full" style="width: {{ $ptPercent }}%"></div>
                                        </div>
                                        <div class="flex justify-between text-[11px] text-slate-400">
                                            <span>Remaining: <strong class="text-emerald-500 font-bold">{{ max(0, $ptPkg->total_sessions - $ptPkg->used_sessions) }} sessions</strong></span>
                                            <span>Valid till: {{ $ptPkg->end_date?->format('d M, Y') ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No personal training packages enrolled.
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 6: SERVICES & LOCKERS -->
            <div x-show="tab === 'services'" x-cloak class="space-y-6">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Assigned Gym Services & Locker Amenities</h3>

                    @if($member->serviceBookings->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($member->serviceBookings as $booking)
                                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-xl bg-orange-500/10 text-orange-500 flex items-center justify-center font-bold">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                            </div>
                                            <div>
                                                <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $booking->service?->name ?? 'Gym Service' }}</h4>
                                                <span class="text-[10px] text-slate-400 font-bold uppercase">{{ $booking->service?->category ?? 'Amenity' }}</span>
                                            </div>
                                        </div>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $booking->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-200 dark:bg-slate-800 text-slate-400' }}">
                                            {{ $booking->status }}
                                        </span>
                                    </div>

                                    @if($booking->locker_number)
                                        <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between">
                                            <span class="text-xs font-bold text-amber-500">Assigned Locker</span>
                                            <span class="text-base font-black text-amber-500 font-mono">#{{ $booking->locker_number }}</span>
                                        </div>
                                    @endif

                                    <div class="text-xs text-slate-400 space-y-1">
                                        @if($booking->total_sessions > 1)
                                            <div class="flex justify-between">
                                                <span>Sessions Left:</span>
                                                <span class="font-bold text-slate-900 dark:text-white">{{ $booking->sessions_left }} of {{ $booking->total_sessions }}</span>
                                            </div>
                                        @endif
                                        <div class="flex justify-between">
                                            <span>Booking Date:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $booking->booking_date?->format('d M, Y') ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No active gym services or locker bookings found.
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 7: CLASSES & SCHEDULES -->
            <div x-show="tab === 'classes'" x-cloak class="space-y-6">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Enrolled Group & Studio Classes</h3>

                    @if($member->classBookings->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($member->classBookings as $booking)
                                <div class="p-5 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <h4 class="text-sm font-black text-slate-900 dark:text-white">{{ $booking->schedule?->gymClass?->name ?? 'Class Session' }}</h4>
                                        <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $booking->status === 'CONFIRMED' ? 'bg-emerald-500/10 text-emerald-500' : 'bg-slate-200 dark:bg-slate-800 text-slate-400' }}">
                                            {{ $booking->status }}
                                        </span>
                                    </div>
                                    <div class="text-xs text-slate-400 space-y-1">
                                        <div class="flex justify-between">
                                            <span>Coach / Trainer:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $booking->schedule?->trainer?->full_name ?? ($booking->schedule?->gymClass?->instructor?->full_name ?? 'Gym Coach') }}</span>
                                        </div>
                                        <div class="flex justify-between">
                                            <span>Timing:</span>
                                            <span class="font-bold text-slate-900 dark:text-white">{{ $booking->schedule?->start_time ? date('h:i A', strtotime($booking->schedule->start_time)) : 'N/A' }}</span>
                                        </div>
                                        @if($booking->schedule?->room_or_studio)
                                            <div class="flex justify-between">
                                                <span>Studio / Room:</span>
                                                <span class="font-bold text-slate-900 dark:text-white">{{ $booking->schedule->room_or_studio }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-12 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            You are not enrolled in any group classes yet.
                        </div>
                    @endif

                    <!-- Available Classes Timetable -->
                    <div class="pt-4 space-y-4">
                        <h4 class="text-base font-extrabold text-slate-900 dark:text-white">Active Gym Class Schedule</h4>
                        @if($availableClassSchedules->isNotEmpty())
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                                @foreach($availableClassSchedules as $sched)
                                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/70 dark:border-slate-800 flex items-center justify-between">
                                        <div>
                                            <div class="text-xs font-black text-slate-900 dark:text-white">{{ $sched->gymClass?->name }}</div>
                                            <div class="text-[10px] text-slate-400 font-bold uppercase">{{ $sched->day_of_week }} • {{ date('h:i A', strtotime($sched->start_time)) }}</div>
                                        </div>
                                        <span class="px-2 py-1 rounded bg-amber-500/10 text-amber-500 text-[10px] font-bold">{{ $sched->room_or_studio ?? 'Studio' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- TAB 8: MEASUREMENTS & FITNESS STATS -->
            <div x-show="tab === 'measurements'" x-cloak class="space-y-6">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Body Measurements & Fitness Tracker</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Track your physical transformation progress over time</p>
                        </div>
                        <button type="button" @click="showMeasurementModal = true" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs flex items-center gap-2 cursor-pointer shadow-md shadow-amber-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            <span>Log New Measurement</span>
                        </button>
                    </div>

                    <!-- Latest Snapshot Cards -->
                    @if($latestMeasurement)
                        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-6 gap-3">
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Weight</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['weight'] ?? '—' }} <span class="text-xs font-normal text-slate-400">kg</span></div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Chest</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['chest'] ?? '—' }} <span class="text-xs font-normal text-slate-400">in</span></div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Waist</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['waist'] ?? '—' }} <span class="text-xs font-normal text-slate-400">in</span></div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Arms</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['bicep_left'] ?? ($latestMeasurement['bicep_right'] ?? '—') }} <span class="text-xs font-normal text-slate-400">in</span></div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Thighs</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['thigh_left'] ?? ($latestMeasurement['thigh_right'] ?? '—') }} <span class="text-xs font-normal text-slate-400">in</span></div>
                            </div>
                            <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 text-center">
                                <span class="text-[10px] font-black uppercase text-slate-400">Body Fat</span>
                                <div class="text-xl font-black text-slate-900 dark:text-white mt-1">{{ $latestMeasurement['body_fat'] ?? '—' }} <span class="text-xs font-normal text-slate-400">%</span></div>
                            </div>
                        </div>
                    @endif

                    <!-- History Table -->
                    @if(!empty($measurements))
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-extrabold uppercase text-[10px]">
                                    <tr>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Weight</th>
                                        <th class="px-4 py-3">Chest</th>
                                        <th class="px-4 py-3">Waist</th>
                                        <th class="px-4 py-3">Arms</th>
                                        <th class="px-4 py-3">Thighs</th>
                                        <th class="px-4 py-3">Body Fat</th>
                                        <th class="px-4 py-3">Notes</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    @foreach($measurements as $m)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td class="px-4 py-3 font-bold text-slate-900 dark:text-white">{{ $m['date'] ?? 'N/A' }}</td>
                                            <td class="px-4 py-3 font-semibold text-slate-700 dark:text-slate-300">{{ !empty($m['weight']) ? $m['weight'] . ' kg' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ !empty($m['chest']) ? $m['chest'] . ' in' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ !empty($m['waist']) ? $m['waist'] . ' in' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ !empty($m['bicep_left']) ? $m['bicep_left'] . ' in' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ !empty($m['thigh_left']) ? $m['thigh_left'] . ' in' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-600 dark:text-slate-400">{{ !empty($m['body_fat']) ? $m['body_fat'] . ' %' : '—' }}</td>
                                            <td class="px-4 py-3 text-slate-400 italic">{{ $m['notes'] ?? '—' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No body measurement records logged yet. Click 'Log New Measurement' above to record your current stats.
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 9: ATTENDANCE -->
            <div x-show="tab === 'attendance'" x-cloak class="space-y-6">
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-6 sm:p-8 shadow-sm space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-900 dark:text-white">Gym Visit & Check-in History</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Your automated attendance logs recorded via biometric, RFID, or Digital QR scan</p>
                        </div>
                        <div class="px-3 py-1.5 rounded-xl bg-emerald-500/10 text-emerald-500 text-xs font-black border border-emerald-500/20">
                            {{ $member->attendance->count() }} Total Check-ins
                        </div>
                    </div>

                    @if($member->attendance->isNotEmpty())
                        <div class="overflow-x-auto rounded-2xl border border-slate-200 dark:border-slate-800">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 dark:bg-slate-800/80 text-slate-600 dark:text-slate-300 font-extrabold uppercase text-[10px]">
                                    <tr>
                                        <th class="px-4 py-3">Date</th>
                                        <th class="px-4 py-3">Check-in Time</th>
                                        <th class="px-4 py-3">Check-out Time</th>
                                        <th class="px-4 py-3">Branch</th>
                                        <th class="px-4 py-3 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 bg-white dark:bg-slate-900">
                                    @foreach($member->attendance as $att)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/50">
                                            <td class="px-4 py-3.5 font-bold text-slate-900 dark:text-white">{{ $att->date ? $att->date->format('l, d M Y') : ($att->check_in ? $att->check_in->format('l, d M Y') : '—') }}</td>
                                            <td class="px-4 py-3.5 font-mono text-emerald-500 font-bold">{{ $att->check_in ? $att->check_in->format('h:i:s A') : '—' }}</td>
                                            <td class="px-4 py-3.5 font-mono text-slate-500">
                                                {{ $att->check_out ? $att->check_out->format('h:i:s A') : '—' }}
                                            </td>
                                            <td class="px-4 py-3.5 text-slate-600 dark:text-slate-400">{{ $att->branch?->name ?? ($member->branch?->name ?? 'Main Branch') }}</td>
                                            <td class="px-4 py-3.5 text-right">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                                    {{ $att->status ?? 'Present' }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-12 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950 rounded-2xl">
                            No attendance records found yet.
                        </div>
                    @endif
                </div>
            </div>

            <!-- MODAL: DIGITAL QR GYM PASS -->
            <div x-show="showQrModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm">
                <div @click.away="showQrModal = false" class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-6 text-center space-y-6 relative">
                    <button type="button" @click="showQrModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white text-sm cursor-pointer">&times;</button>
                    <div>
                        <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Gym Check-in Pass</span>
                        <h3 class="text-xl font-black text-white mt-2">{{ $member->full_name }}</h3>
                        <p class="text-xs text-slate-400 font-mono mt-0.5">{{ $member->member_code }}</p>
                    </div>

                    <!-- QR Code Display -->
                    <div class="p-4 rounded-2xl bg-white mx-auto inline-block shadow-lg">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode($member->qr_code_token ?? $member->member_code) }}" alt="QR Code" class="w-44 h-44 mx-auto">
                    </div>

                    <div class="space-y-1">
                        <p class="text-xs text-slate-400 font-medium">Scan this QR token at the gym front desk or turnstile scanner to check in instantly.</p>
                        <p class="text-[11px] text-amber-400 font-bold font-mono">Token: {{ $member->qr_code_token ?? $member->member_code }}</p>
                    </div>

                    <button type="button" @click="showQrModal = false" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                        Close Pass
                    </button>
                </div>
            </div>

            <!-- MODAL: LOG BODY MEASUREMENT -->
            <div x-show="showMeasurementModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-sm overflow-y-auto">
                <div @click.away="showMeasurementModal = false" class="w-full max-w-lg rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-2xl p-6 sm:p-8 space-y-6 my-8">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Log Body Measurements</h3>
                        <button type="button" @click="showMeasurementModal = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-white font-bold text-xl cursor-pointer">&times;</button>
                    </div>

                    <form action="{{ route('app.members.store-measurement', $member->id) }}" method="POST" class="space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 uppercase tracking-wider mb-1">Date</label>
                            <input type="date" name="date" x-model="measurementDate" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs font-bold text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Weight (kg)</label>
                                <input type="number" step="0.1" name="weight" x-model="measurementWeight" placeholder="e.g. 75.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Chest (in)</label>
                                <input type="number" step="0.1" name="chest" x-model="measurementChest" placeholder="e.g. 40.0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Waist (in)</label>
                                <input type="number" step="0.1" name="waist" x-model="measurementWaist" placeholder="e.g. 32.0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Bicep / Arm (in)</label>
                                <input type="number" step="0.1" name="bicep_left" x-model="measurementBicepLeft" placeholder="e.g. 15.0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Thigh (in)</label>
                                <input type="number" step="0.1" name="thigh_left" x-model="measurementThighLeft" placeholder="e.g. 23.0" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 mb-1">Body Fat (%)</label>
                                <input type="number" step="0.1" name="body_fat" x-model="measurementBodyFat" placeholder="e.g. 15.5" class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 mb-1">Personal Notes</label>
                            <textarea name="notes" x-model="measurementNotes" rows="2" placeholder="e.g. Morning fasting weight, post workout session..." class="w-full px-3 py-2 rounded-xl bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 text-xs text-slate-900 dark:text-white focus:outline-none focus:border-amber-500"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-3">
                            <button type="button" @click="showMeasurementModal = false" class="px-4 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-bold text-xs cursor-pointer">
                                Cancel
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs cursor-pointer shadow-md shadow-amber-500/20">
                                Save Measurements
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        @endif

    </div>
</x-app-layout>
