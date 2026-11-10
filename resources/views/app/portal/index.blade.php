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
        $gymLogoRaw = $tenant?->logo_url;
        $resolvedGymLogo = $gymLogoRaw ? (\Illuminate\Support\Str::startsWith($gymLogoRaw, ['http://', 'https://', '/']) ? $gymLogoRaw : asset('storage/' . $gymLogoRaw)) : null;
        $gymName = $tenant?->name ?? 'GymDeskHub';
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        showQrModal: false,
        showPortalScanner: false,
        scannerMode: 'camera',
        manualOtpCode: '',
        portalScanning: false,
        portalScanMsg: '',
        portalQrReader: null,
        cameraStarting: false,
        cameraError: false,
        pinSubmitting: false,
        pinSuccess: false,
        pinErrorMsg: '',
        pinSuccessMsg: '',
        showMeasurementModal: false,

        // PT Session Confirmation State
        showPtConfirmModal: false,
        ptConfirmLoading: false,
        activePendingPt: {{ Js::from($pendingPtSessions->first() ? [
            'id' => $pendingPtSessions->first()->id,
            'token' => $pendingPtSessions->first()->confirmation_token,
            'otp_code' => $pendingPtSessions->first()->otp_code,
            'trainer_name' => $pendingPtSessions->first()->trainer?->full_name ?? 'Trainer',
            'package_name' => $pendingPtSessions->first()->memberPtPackage?->package_name ?? 'Personal Training',
            'remaining_sessions' => $pendingPtSessions->first()->memberPtPackage?->remaining_sessions ?? 0,
            'duration_minutes' => $pendingPtSessions->first()->duration_minutes ?? 60,
            'focus_area' => $pendingPtSessions->first()->focus_area ?? 'Strength Training',
        ] : null) }},
        ptRating: 5,
        ptFeedback: '',
        ptConfirmSuccess: false,
        ptSuccessMsg: '',

        init() {
            // Live background polling for pending PT sessions every 6 seconds
            setInterval(() => {
                this.checkPendingPtSessions();
            }, 6000);
        },

        async checkPendingPtSessions() {
            try {
                const res = await fetch('{{ route('app.portal.pending-pt-sessions') }}');
                const data = await res.json();
                if (data && data.pending && data.pending.length > 0) {
                    if (!this.activePendingPt) {
                        this.activePendingPt = data.pending[0];
                    } else if (this.activePendingPt.id !== data.pending[0].id) {
                        this.activePendingPt = data.pending[0];
                    }
                } else if (!this.showPtConfirmModal) {
                    this.activePendingPt = null;
                }
            } catch (e) {}
        },

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

        startPortalScanner(mode = 'camera') {
            this.scannerMode = mode;
            this.showPortalScanner = true;
            this.portalScanMsg = '';
            this.pinErrorMsg = '';
            this.pinSuccess = false;
            this.cameraError = false;

            if (mode === 'camera') {
                this.initCamera();
            }
        },

        initCamera() {
            this.portalScanMsg = '';
            this.cameraStarting = true;
            this.cameraError = false;

            setTimeout(() => {
                const container = document.getElementById('portal-camera-reader');
                if (!container) {
                    this.cameraStarting = false;
                    return;
                }

                if (typeof Html5Qrcode === 'undefined') {
                    this.cameraError = true;
                    this.cameraStarting = false;
                    this.portalScanMsg = 'Scanner library is loading. Please use the Session PIN tab.';
                    return;
                }

                if (this.portalQrReader) {
                    try {
                        this.portalQrReader.stop().catch(() => {});
                    } catch (e) {}
                    this.portalQrReader = null;
                }

                try {
                    this.portalQrReader = new Html5Qrcode('portal-camera-reader');
                    this.portalQrReader.start(
                        { facingMode: 'environment' },
                        { fps: 10, qrbox: { width: 220, height: 220 } },
                        (decodedText) => {
                            this.stopPortalScanner();
                            this.handleScannedCode(decodedText);
                        },
                        (error) => {}
                    ).then(() => {
                        this.cameraStarting = false;
                    }).catch(err => {
                        this.cameraStarting = false;
                        this.cameraError = true;
                        const errStr = String(err).toLowerCase();
                        if (errStr.includes('notallowed') || errStr.includes('permission')) {
                            this.portalScanMsg = 'Camera access was blocked by browser. Please allow camera permissions, or enter the Session PIN.';
                        } else if (errStr.includes('notfound') || errStr.includes('device')) {
                            this.portalScanMsg = 'No camera found. Please enter the 6-digit Session PIN.';
                        } else if (!window.isSecureContext && location.protocol !== 'https:' && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                            this.portalScanMsg = 'Live camera stream requires HTTPS on mobile devices. Please enter the Session PIN below or snap a photo of the QR.';
                        } else {
                            this.portalScanMsg = 'Could not access camera: ' + err;
                        }
                    });
                } catch (e) {
                    this.cameraStarting = false;
                    this.cameraError = true;
                    this.portalScanMsg = 'Camera initialization error: ' + e.message;
                }
            }, 250);
        },

        scanQrFile(e) {
            const file = e.target.files && e.target.files[0];
            if (!file) return;
            if (typeof Html5Qrcode === 'undefined') {
                alert('Scanner library is loading. Please enter the Session PIN.');
                return;
            }
            try {
                const scanner = new Html5Qrcode('portal-camera-reader');
                scanner.scanFile(file, true)
                    .then(decodedText => {
                        this.stopPortalScanner();
                        this.handleScannedCode(decodedText);
                    })
                    .catch(err => {
                        alert('Could not detect QR code in this photo. Please try again or use the 6-digit Session PIN.');
                    });
            } catch (err) {
                alert('Photo scan error: ' + err.message);
            }
        },

        switchScannerTab(mode) {
            this.scannerMode = mode;
            this.portalScanMsg = '';
            this.pinErrorMsg = '';
            if (mode === 'camera') {
                this.initCamera();
            } else if (this.portalQrReader) {
                try {
                    this.portalQrReader.stop().catch(() => {});
                } catch (e) {}
            }
        },

        stopPortalScanner() {
            if (this.portalQrReader) {
                try {
                    this.portalQrReader.stop().then(() => {
                        try { this.portalQrReader.clear(); } catch(e) {}
                    }).catch(() => {});
                } catch (e) {}
            }
            this.showPortalScanner = false;
            this.cameraStarting = false;
            this.cameraError = false;
            this.portalScanMsg = '';
            this.pinErrorMsg = '';
        },

        handleScannedCode(code) {
            const trimmed = String(code).trim();
            // PT Session confirmation QR URL or token check
            if (trimmed.includes('/pt-session/confirm/') || trimmed.startsWith('pts_')) {
                let token = trimmed;
                if (trimmed.includes('/pt-session/confirm/')) {
                    token = trimmed.split('/pt-session/confirm/')[1].split('?')[0].split('/')[0];
                }
                this.openPtConfirmModal({ token: token });
                return;
            }

            // 6-digit numeric backup code
            if (/^\d{6}$/.test(trimmed)) {
                this.manualOtpCode = trimmed;
                this.submitPinConfirm();
                return;
            }

            // Default: Gym attendance check-in
            this.submitPortalCheckin(trimmed);
        },

        async submitPinConfirm() {
            const code = String(this.manualOtpCode).trim();
            if (!code || code.length < 4) {
                this.pinErrorMsg = 'Please enter the 6-digit Session PIN (e.g. 210289).';
                return;
            }
            this.pinSubmitting = true;
            this.pinErrorMsg = '';
            try {
                const res = await fetch('{{ route('app.portal.confirm-pt-session') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        otp_code: code,
                        rating: this.ptRating,
                        feedback: this.ptFeedback
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    this.pinSuccess = true;
                    this.pinSuccessMsg = data.message || 'Workout session confirmed successfully!';
                    if (data.remaining_sessions !== undefined && this.activePendingPt) {
                        this.activePendingPt.remaining_sessions = data.remaining_sessions;
                    }
                    if (this.portalQrReader) {
                        try { this.portalQrReader.stop().catch(() => {}); } catch(e) {}
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);
                } else {
                    this.pinErrorMsg = data.message || 'Invalid or expired Session PIN. Please verify code from coach.';
                }
            } catch (err) {
                this.pinErrorMsg = 'Network error confirming session. Please try again.';
            } finally {
                this.pinSubmitting = false;
            }
        },

        openPtConfirmModal(data = {}) {
            if (data.token) {
                this.activePendingPt = Object.assign(this.activePendingPt || {}, { token: data.token, id: null });
            } else if (data.otp_code) {
                this.activePendingPt = Object.assign(this.activePendingPt || {}, { otp_code: data.otp_code, id: null });
            } else if (data.id) {
                this.activePendingPt = Object.assign(this.activePendingPt || {}, data);
            }
            this.ptRating = 5;
            this.ptFeedback = '';
            this.ptConfirmSuccess = false;
            this.showPtConfirmModal = true;
            this.showPortalScanner = false;
        },

        async submitPtConfirm() {
            if (!this.activePendingPt && !this.manualOtpCode) return;
            this.ptConfirmLoading = true;
            try {
                const res = await fetch('{{ route('app.portal.confirm-pt-session') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        session_id: this.activePendingPt?.id || null,
                        token: this.activePendingPt?.token || null,
                        otp_code: this.activePendingPt?.otp_code || this.manualOtpCode || null,
                        rating: this.ptRating,
                        feedback: this.ptFeedback
                    })
                });
                const data = await res.json();
                if (data && data.success) {
                    this.ptConfirmSuccess = true;
                    this.ptSuccessMsg = data.message || 'Session confirmed successfully!';
                    if (data.remaining_sessions !== undefined && this.activePendingPt) {
                        this.activePendingPt.remaining_sessions = data.remaining_sessions;
                    }
                    setTimeout(() => {
                        this.showPtConfirmModal = false;
                        this.activePendingPt = null;
                        window.location.reload();
                    }, 2200);
                } else {
                    alert(data.message || 'Could not confirm session.');
                }
            } catch (err) {
                alert('Network error confirming session.');
            } finally {
                this.ptConfirmLoading = false;
            }
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
    x-on:switch-portal-tab.window="tab = $event.detail"
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

            <!-- APP SEGMENTED TABS (STICKY TOP) -->
            <div class="sticky top-2 z-20 flex items-center gap-1.5 p-1.5 rounded-2xl bg-slate-200/90 dark:bg-slate-900/90 backdrop-blur-md border border-slate-300/70 dark:border-slate-800 shadow-md text-xs font-bold">
                <button type="button" 
                        @click="setPortalTab('overview')" 
                        :class="tab === 'overview' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm ring-1 ring-slate-900/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Home
                </button>
                <button type="button" 
                        @click="setPortalTab('workouts')" 
                        :class="tab === 'workouts' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm ring-1 ring-slate-900/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Workout
                </button>
                <button type="button" 
                        @click="setPortalTab('diets')" 
                        :class="tab === 'diets' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm ring-1 ring-slate-900/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Diet
                </button>
                <button type="button" 
                        @click="setPortalTab('attendance')" 
                        :class="tab === 'attendance' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm ring-1 ring-slate-900/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Visits
                </button>
                <button type="button" 
                        @click="setPortalTab('memberships')" 
                        :class="tab === 'memberships' ? 'bg-white dark:bg-slate-800 text-slate-950 dark:text-white shadow-sm ring-1 ring-slate-900/5' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'" 
                        class="flex-1 py-2 px-1 text-center rounded-xl transition-all cursor-pointer">
                    Pass
                </button>
            </div>

            <!-- ========================================================= -->
            <!-- LIVE PENDING PT SESSION NOTIFICATION BANNER               -->
            <!-- ========================================================= -->
            <template x-if="activePendingPt">
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-amber-500/20 via-amber-600/15 to-emerald-500/20 border-2 border-amber-500/60 p-4 sm:p-5 shadow-2xl text-slate-900 dark:text-white space-y-3.5 transition-all">
                    <!-- Glow background -->
                    <div class="absolute -top-10 -right-10 w-40 h-40 bg-amber-500/20 rounded-full blur-2xl pointer-events-none"></div>

                    <div class="relative z-10 flex items-start justify-between gap-3">
                        <div class="flex items-start gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl bg-amber-500 text-slate-950 font-black flex items-center justify-center text-xl shrink-0 shadow-lg shadow-amber-500/30">
                                ⚡
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <span class="px-2 py-0.5 rounded-full bg-amber-500 text-slate-950 text-[10px] font-black uppercase tracking-wider">Coach Waiting</span>
                                    <span class="text-[10px] font-bold text-amber-500 dark:text-amber-400 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Confirm your workout session</span>
                                    </span>
                                </div>
                                <h3 class="text-sm sm:text-base font-black truncate mt-1">
                                    Session with <span class="text-amber-500" x-text="activePendingPt.trainer_name"></span>
                                </h3>
                                <p class="text-xs text-slate-600 dark:text-slate-400 mt-0.5">
                                    <span x-text="activePendingPt.package_name"></span> &bull; <span x-text="activePendingPt.duration_minutes + ' Mins'"></span>
                                </p>
                            </div>
                        </div>

                        <template x-if="activePendingPt.otp_code">
                            <div class="text-right shrink-0 bg-white/90 dark:bg-slate-900/90 px-3 py-1.5 rounded-xl border border-amber-500/40 shadow-xs">
                                <span class="text-[9px] uppercase font-bold text-slate-400 block">Session PIN</span>
                                <span class="text-base font-mono font-black text-amber-500 tracking-wider" x-text="activePendingPt.otp_code"></span>
                            </div>
                        </template>
                    </div>

                    <div class="relative z-10 grid grid-cols-2 gap-2.5 pt-1">
                        <button type="button" 
                                @click="openPtConfirmModal()" 
                                class="py-2.5 px-3 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs shadow-md shadow-amber-500/25 flex items-center justify-center gap-1.5 cursor-pointer transition-all active:scale-[0.98]">
                            <span>✓ Confirm Session</span>
                        </button>
                        <button type="button" 
                                @click="startPortalScanner('camera')" 
                                class="py-2.5 px-3 rounded-xl bg-slate-900/80 dark:bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs border border-slate-700 flex items-center justify-center gap-1.5 cursor-pointer transition-all active:scale-[0.98]">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Scan QR Code</span>
                        </button>
                    </div>
                </div>
            </template>

            <!-- TAB 1: HOME / OVERVIEW -->
            <div x-show="tab === 'overview'" x-cloak class="space-y-4">

                <!-- HERO: LUXURY DIGITAL MEMBERSHIP PASS (APP CARD) -->
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 border border-amber-500/25 shadow-2xl p-5 sm:p-6 text-white space-y-5">
                <!-- Ambient Glow Elements -->
                <div class="absolute -top-16 -right-16 w-60 h-60 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-16 -left-16 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <!-- Card Top: Gym Branding & Status Badge -->
                <div class="relative z-10 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        @if(!empty($resolvedGymLogo))
                            <img src="{{ $resolvedGymLogo }}" alt="{{ $gymName }}" class="h-6 w-auto max-w-[80px] rounded-md object-contain bg-white/10 p-0.5">
                        @else
                            <span class="w-6 h-6 rounded-lg bg-amber-400 text-slate-950 font-black text-[10px] flex items-center justify-center shrink-0">
                                {{ strtoupper(substr($gymName, 0, 2)) }}
                            </span>
                        @endif
                        <span class="text-xs font-black tracking-widest uppercase text-amber-400">
                            {{ $gymName }}
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
                            @click="startPortalScanner('camera')" 
                            class="py-3 px-3 rounded-2xl bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-600 hover:brightness-110 active:scale-[0.98] text-slate-950 font-black text-xs shadow-lg shadow-emerald-500/25 flex items-center justify-center gap-2 transition-all cursor-pointer">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span>Scan QR / PT</span>
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

                <!-- FULL-WIDTH PERSONAL TRAINING (PT) COACHING CARD -->
                @if($activePt)
                    @php
                        $ptUsed = (int) ($activePt->used_sessions ?? 0);
                        $ptTotal = (int) ($activePt->total_sessions ?? 1);
                        $ptRemaining = max(0, (int) ($activePt->remaining_sessions ?? ($ptTotal - $ptUsed)));
                        $ptPct = min(100, round(($ptUsed / max(1, $ptTotal)) * 100));
                        $coachName = $activePt->trainer?->full_name ?? ($member->assignedTrainer?->full_name ?? 'Gym Fitness Coach');
                    @endphp
                    <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-purple-950/60 border border-purple-500/30 p-5 sm:p-6 shadow-xl space-y-4 text-white relative overflow-hidden">
                        <!-- Ambient Glow -->
                        <div class="absolute -top-16 -right-16 w-52 h-52 bg-purple-500/15 rounded-full blur-3xl pointer-events-none"></div>

                        <!-- Top Row: Icon, Title, Coach & Sessions Left Counter -->
                        <div class="relative z-10 flex items-start justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-2xl bg-gradient-to-tr from-purple-600 to-indigo-600 text-white flex items-center justify-center font-bold text-xl shadow-lg shadow-purple-500/25 border border-white/20 shrink-0">
                                    🏋️
                                </div>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h3 class="text-base sm:text-lg font-black text-white tracking-tight truncate">{{ $activePt->package_name ?? 'Personal Training Package' }}</h3>
                                        <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-wider bg-purple-500/20 text-purple-300 border border-purple-500/30">Active PT</span>
                                    </div>
                                    <p class="text-xs text-slate-300 mt-0.5 truncate flex items-center gap-1.5 flex-wrap">
                                        <span>Coach:</span>
                                        <strong class="text-white">{{ $coachName }}</strong>
                                        @if($activePt->end_date)
                                            <span class="text-slate-400">&bull; Valid till {{ $activePt->end_date->format('d M, Y') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Remaining Sessions Badge -->
                            <div class="text-right shrink-0">
                                <div class="text-2xl sm:text-3xl font-black text-purple-400 font-mono tracking-tight">{{ $ptRemaining }}</div>
                                <div class="text-[10px] text-slate-400 font-bold uppercase tracking-wider">Sessions Left</div>
                            </div>
                        </div>

                        <!-- Progress Bar & Breakdown -->
                        <div class="relative z-10 space-y-1.5 pt-1 border-t border-white/10">
                            <div class="w-full bg-slate-800/90 rounded-full h-3 p-0.5 overflow-hidden border border-slate-700/60 shadow-inner">
                                <div class="h-full rounded-full bg-gradient-to-r from-purple-500 via-indigo-500 to-amber-400 transition-all duration-500 shadow-sm" style="width: {{ $ptPct }}%"></div>
                            </div>
                            <div class="flex items-center justify-between text-xs text-slate-300">
                                <span>Used: <strong class="text-white font-mono">{{ $ptUsed }}</strong> of {{ $ptTotal }} Sessions ({{ $ptPct }}%)</span>
                                <span class="text-purple-300 font-bold">{{ $ptRemaining }} Available</span>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Assigned Locker (If assigned) -->
                @if($assignedLocker)
                    <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-between text-xs shadow-xs">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500/20 text-amber-500 flex items-center justify-center text-lg font-bold">🔐</div>
                            <div>
                                <span class="text-[10px] font-black uppercase tracking-wider text-amber-500 block">Assigned Gym Locker</span>
                                <span class="font-black text-slate-900 dark:text-white font-mono text-base">Locker #{{ $assignedLocker->locker_number }}</span>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-amber-500/20 text-amber-400 border border-amber-500/30">Active</span>
                    </div>
                @endif

                <!-- BODY MEASUREMENTS & TRANSFORMATION TRACKER -->
                <div x-data="{ showAllMeasurements: false }" class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200/90 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-2xl bg-indigo-500/10 text-indigo-500 flex items-center justify-center font-bold text-lg shrink-0 border border-indigo-500/20">
                                ⚖️
                            </div>
                            <div class="min-w-0">
                                <h3 class="text-sm sm:text-base font-black text-slate-900 dark:text-white tracking-tight truncate">Body Measurements &amp; Progress</h3>
                                <p class="text-[11px] font-medium text-slate-400 truncate">
                                    @if(!empty($latestMeasurement))
                                        Latest check-in: {{ \Carbon\Carbon::parse($latestMeasurement['date'] ?? now())->format('d M Y') }}
                                    @else
                                        Tracked date-wise by your gym trainer
                                    @endif
                                </p>
                            </div>
                        </div>

                        @if(!empty($measurements))
                            <span class="px-2.5 py-1 rounded-xl text-[10px] font-black uppercase tracking-wider bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shrink-0">
                                {{ count($measurements) }} {{ count($measurements) === 1 ? 'Check-in' : 'Check-ins' }}
                            </span>
                        @endif
                    </div>

                    @if(!empty($latestMeasurement))
                        @php
                            $prevMeasurement = count($measurements) > 1 ? $measurements[1] : null;
                            $weightDiff = ($latestMeasurement['weight'] && $prevMeasurement && !empty($prevMeasurement['weight'])) ? round((float)$latestMeasurement['weight'] - (float)$prevMeasurement['weight'], 1) : null;
                            $waistDiff = ($latestMeasurement['waist'] && $prevMeasurement && !empty($prevMeasurement['waist'])) ? round((float)$latestMeasurement['waist'] - (float)$prevMeasurement['waist'], 1) : null;
                            $fatDiff = ($latestMeasurement['body_fat'] && $prevMeasurement && !empty($prevMeasurement['body_fat'])) ? round((float)$latestMeasurement['body_fat'] - (float)$prevMeasurement['body_fat'], 1) : null;
                        @endphp

                        <!-- Latest Stats Primary Highlight Card -->
                        <div class="p-4 sm:p-5 rounded-2xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/60 border border-indigo-500/20 text-white space-y-4 shadow-md">
                            
                            <!-- Header of Latest Check-in -->
                            <div class="flex items-center justify-between text-xs pb-3 border-b border-white/10 flex-wrap gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="px-2.5 py-1 rounded-lg bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 font-mono text-[11px] font-bold">
                                        📅 {{ \Carbon\Carbon::parse($latestMeasurement['date'])->format('d M Y') }}
                                    </span>
                                    <span class="text-[10px] text-slate-400">Latest Assessment</span>
                                </div>
                                <span class="text-[11px] text-slate-300 font-medium">
                                    Coach: <strong class="text-white">{{ $latestMeasurement['logged_by'] ?? 'Trainer' }}</strong>
                                </span>
                            </div>

                            <!-- 4 Key Core Metrics Grid -->
                            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5 text-center">
                                <!-- 1. Weight -->
                                @if(!empty($latestMeasurement['weight']))
                                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10 hover:border-amber-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-black text-amber-400 tracking-wider block">Weight</span>
                                        <div class="text-lg sm:text-xl font-black text-white mt-0.5 font-mono">{{ $latestMeasurement['weight'] }} <span class="text-xs text-slate-400">kg</span></div>
                                        @if($weightDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-black mt-1 {{ $weightDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                                {{ $weightDiff <= 0 ? ($weightDiff . ' kg') : ('+' . $weightDiff . ' kg') }}
                                            </span>
                                        @else
                                            <span class="text-[9px] text-slate-400 block mt-1 font-semibold">Bodyweight</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 2. Body Fat -->
                                @if(!empty($latestMeasurement['body_fat']))
                                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10 hover:border-sky-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-black text-sky-400 tracking-wider block">Body Fat</span>
                                        <div class="text-lg sm:text-xl font-black text-white mt-0.5 font-mono">{{ $latestMeasurement['body_fat'] }}%</div>
                                        @if($fatDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-black mt-1 {{ $fatDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                                {{ $fatDiff <= 0 ? ($fatDiff . '%') : ('+' . $fatDiff . '%') }}
                                            </span>
                                        @else
                                            <span class="text-[9px] text-slate-400 block mt-1 font-semibold">Est. Fat %</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 3. Waist -->
                                @if(!empty($latestMeasurement['waist']))
                                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10 hover:border-emerald-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-black text-emerald-400 tracking-wider block">Waist</span>
                                        <div class="text-lg sm:text-xl font-black text-white mt-0.5 font-mono">{{ $latestMeasurement['waist'] }} <span class="text-xs text-slate-400">in</span></div>
                                        @if($waistDiff !== null)
                                            <span class="inline-block px-1.5 py-0.2 rounded text-[9px] font-black mt-1 {{ $waistDiff <= 0 ? 'bg-emerald-500/20 text-emerald-300' : 'bg-rose-500/20 text-rose-300' }}">
                                                {{ $waistDiff <= 0 ? ($waistDiff . ' in') : ('+' . $waistDiff . ' in') }}
                                            </span>
                                        @else
                                            <span class="text-[9px] text-slate-400 block mt-1 font-semibold">Midsection</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- 4. Arms / Bicep -->
                                @if(!empty($latestMeasurement['bicep']))
                                    <div class="p-3 rounded-2xl bg-white/5 border border-white/10 hover:border-purple-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-black text-purple-400 tracking-wider block">Arms</span>
                                        <div class="text-lg sm:text-xl font-black text-white mt-0.5 font-mono">{{ $latestMeasurement['bicep'] }} <span class="text-xs text-slate-400">in</span></div>
                                        <span class="text-[9px] text-slate-400 block mt-1 font-semibold">Bicep Peak</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Secondary Tape Measurements Grid (Chest, Hips, Thighs, Shoulders, Calves, Neck) -->
                            @php
                                $tapeMetrics = [];
                                if (!empty($latestMeasurement['chest'])) $tapeMetrics[] = ['label' => 'Chest', 'val' => $latestMeasurement['chest'] . ' in', 'icon' => '🫁'];
                                if (!empty($latestMeasurement['hips'])) $tapeMetrics[] = ['label' => 'Hips', 'val' => $latestMeasurement['hips'] . ' in', 'icon' => '📐'];
                                if (!empty($latestMeasurement['thighs'])) $tapeMetrics[] = ['label' => 'Thighs', 'val' => $latestMeasurement['thighs'] . ' in', 'icon' => '🦵'];
                                if (!empty($latestMeasurement['shoulders'])) $tapeMetrics[] = ['label' => 'Shoulders', 'val' => $latestMeasurement['shoulders'] . ' in', 'icon' => '🥋'];
                                if (!empty($latestMeasurement['calves'])) $tapeMetrics[] = ['label' => 'Calves', 'val' => $latestMeasurement['calves'] . ' in', 'icon' => '🦶'];
                                if (!empty($latestMeasurement['neck'])) $tapeMetrics[] = ['label' => 'Neck', 'val' => $latestMeasurement['neck'] . ' in', 'icon' => '🧣'];
                            @endphp

                            @if(!empty($tapeMetrics))
                                <div class="space-y-1.5 pt-2 border-t border-white/10">
                                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400 block">Tape Measurements</span>
                                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-2 text-center">
                                        @foreach($tapeMetrics as $tm)
                                            <div class="p-2 rounded-xl bg-white/5 border border-white/10">
                                                <span class="text-[9px] text-slate-400 uppercase font-bold block">{{ $tm['label'] }}</span>
                                                <span class="text-xs font-black text-white font-mono block mt-0.5">{{ $tm['val'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Custom Metrics Chips (e.g. Forearms, BMI) -->
                            @if(!empty($latestMeasurement['custom_metrics']) && is_array($latestMeasurement['custom_metrics']))
                                @php
                                    $validCustom = array_filter($latestMeasurement['custom_metrics'], fn($c) => !empty($c['name']) && !empty($c['value']));
                                @endphp
                                @if(!empty($validCustom))
                                    <div class="flex items-center gap-1.5 flex-wrap pt-1">
                                        @foreach($validCustom as $cm)
                                            <span class="px-2.5 py-1 rounded-xl bg-indigo-500/20 text-indigo-300 border border-indigo-500/30 text-[10px] font-semibold">
                                                <span class="text-slate-400">{{ $cm['name'] }}:</span> <strong class="text-white">{{ $cm['value'] }}</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            @endif

                            <!-- Coach's Observation / Advice Quote -->
                            @if(!empty($latestMeasurement['notes']))
                                <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300 leading-relaxed space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="text-sm">💬</span>
                                        <span class="font-black uppercase tracking-wider text-[10px] text-amber-400">Coach's Observation &amp; Feedback</span>
                                    </div>
                                    <p class="font-medium text-amber-200/90 pl-5 text-[11px]">{{ $latestMeasurement['notes'] }}</p>
                                </div>
                            @endif
                        </div>

                        <!-- Date-Wise Historical Logs Section -->
                        <div class="pt-1 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-black text-slate-900 dark:text-white uppercase tracking-wider">Date-Wise Progress History</span>
                                @if(count($measurements) > 1)
                                    <button type="button" 
                                            @click="showAllMeasurements = !showAllMeasurements" 
                                            class="text-xs font-bold text-amber-500 hover:underline cursor-pointer flex items-center gap-1">
                                        <span x-text="showAllMeasurements ? 'Show Recent Only' : 'View All ({{ count($measurements) }} Dates) &rarr;'"></span>
                                    </button>
                                @endif
                            </div>

                            <!-- List of Measurements by Date -->
                            <div class="space-y-2">
                                @foreach($measurements as $idx => $m)
                                    <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/60 border border-slate-200/80 dark:border-slate-800 text-xs space-y-2 transition-all"
                                         x-show="{{ $idx === 0 ? 'true' : 'showAllMeasurements' }}">
                                        
                                        <!-- Log Header -->
                                        <div class="flex items-center justify-between gap-2 flex-wrap">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-lg bg-amber-500/15 text-amber-500 font-bold font-mono text-[10px] border border-amber-500/25">
                                                    📅 {{ \Carbon\Carbon::parse($m['date'] ?? now())->format('d M Y') }}
                                                </span>
                                                @if(!empty($m['weight']))
                                                    <span class="font-black text-slate-900 dark:text-white text-xs">⚖️ {{ $m['weight'] }} kg</span>
                                                @endif
                                                @if(!empty($m['body_fat']))
                                                    <span class="text-sky-600 dark:text-sky-400 font-bold text-[10px] bg-sky-500/10 px-1.5 py-0.5 rounded">{{ $m['body_fat'] }}% Fat</span>
                                                @endif
                                            </div>
                                            <span class="text-[10px] text-slate-400">
                                                By {{ $m['logged_by'] ?? 'Trainer' }}
                                            </span>
                                        </div>

                                        <!-- Tape Measurement Mini-Tiles Grid -->
                                        <div class="grid grid-cols-3 sm:grid-cols-4 gap-1.5 text-center">
                                            @if(!empty($m['waist']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Waist</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['waist'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['chest']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Chest</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['chest'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['bicep']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Arms</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['bicep'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['hips']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Hips</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['hips'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['thighs']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Thighs</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['thighs'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['shoulders']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Shoulders</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['shoulders'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['calves']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Calves</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['calves'] }}"</span>
                                                </div>
                                            @endif
                                            @if(!empty($m['neck']))
                                                <div class="p-2 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800">
                                                    <span class="text-[8px] font-black uppercase tracking-wider text-slate-400 block">Neck</span>
                                                    <span class="text-[11px] font-black text-slate-800 dark:text-slate-100 block mt-0.5">{{ $m['neck'] }}"</span>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Custom Metrics -->
                                        @if(!empty($m['custom_metrics']) && is_array($m['custom_metrics']))
                                            <div class="flex items-center gap-1.5 flex-wrap pt-0.5">
                                                @foreach($m['custom_metrics'] as $cm)
                                                    @if(!empty($cm['name']) && !empty($cm['value']))
                                                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-bold bg-amber-500/10 text-amber-500 border border-amber-500/20">{{ $cm['name'] }}: {{ $cm['value'] }}</span>
                                                    @endif
                                                @endforeach
                                            </div>
                                        @endif

                                        @if(!empty($m['notes']))
                                            <div class="p-2.5 rounded-xl bg-slate-50 dark:bg-slate-900/60 border border-slate-200/60 dark:border-slate-800 text-[11px] text-slate-600 dark:text-slate-300 italic flex items-start gap-2">
                                                <span class="text-xs shrink-0">💬</span>
                                                <span>&ldquo;{{ $m['notes'] }}&rdquo;</span>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="py-6 px-4 text-center rounded-2xl bg-slate-50 dark:bg-slate-950/40 border border-dashed border-slate-200 dark:border-slate-800 space-y-1.5">
                            <div class="text-xs font-bold text-slate-700 dark:text-slate-300">No body measurements recorded yet</div>
                            <p class="text-[11px] text-slate-400 max-w-sm mx-auto leading-relaxed">
                                Your personal trainer will record your weight, body fat %, and tape measurements date-wise during your coaching sessions.
                            </p>
                        </div>
                    @endif
                </div>

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

                <!-- PERSONAL TRAINING STATUS & RECENT SESSIONS (OVERVIEW) -->
                @if($member->memberPtPackages->isNotEmpty() || $ptSessionsHistory->isNotEmpty())
                    @php
                        $activePtPackage = $member->memberPtPackages->firstWhere('status', 'ACTIVE') ?? $member->memberPtPackages->first();
                        $latestPt = $ptSessionsHistory->first();
                    @endphp
                    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-base font-black shrink-0 border border-amber-500/20">
                                    ⚡
                                </div>
                                <div>
                                    <h3 class="text-sm font-extrabold text-slate-900 dark:text-white">Personal Training (PT)</h3>
                                    <p class="text-[11px] text-slate-400">
                                        {{ $activePtPackage ? $activePtPackage->package_name : '1-on-1 Coaching' }}
                                    </p>
                                </div>
                            </div>
                            <button type="button" @click="setPortalTab('attendance')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">
                                All History &rarr;
                            </button>
                        </div>

                        <!-- PT Package Balance Pill -->
                        @if($activePtPackage)
                            <div class="p-3.5 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 space-y-2">
                                <div class="flex items-center justify-between text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-amber-500/20 text-amber-500 text-[10px] font-black flex items-center justify-center shrink-0">
                                            {{ substr($activePtPackage->trainer?->first_name ?? ($member->assignedTrainer?->first_name ?? 'C'), 0, 1) }}
                                        </div>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">
                                            Coach {{ $activePtPackage->trainer?->full_name ?? ($member->assignedTrainer?->full_name ?? 'Assigned Coach') }}
                                        </span>
                                    </div>
                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase bg-emerald-500/15 text-emerald-500 border border-emerald-500/30">
                                        {{ $activePtPackage->remaining_sessions }} Sessions Left
                                    </span>
                                </div>
                                <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-2 rounded-full" style="width: {{ $activePtPackage->total_sessions > 0 ? min(100, round(($activePtPackage->used_sessions / $activePtPackage->total_sessions) * 100)) : 0 }}%"></div>
                                </div>
                                <div class="flex justify-between text-[10px] text-slate-400">
                                    <span>{{ $activePtPackage->used_sessions }} used of {{ $activePtPackage->total_sessions }} total</span>
                                    <span>Expires {{ $activePtPackage->end_date?->format('d M Y') ?? 'N/A' }}</span>
                                </div>
                            </div>
                        @endif

                        <!-- Latest Confirmed Session Preview -->
                        @if($latestPt)
                            <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/20 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-[10px] uppercase font-bold text-slate-400">Latest Completed Session</span>
                                    <span class="text-[10px] font-bold text-amber-500">
                                        {{ $latestPt->session_date ? $latestPt->session_date->format('d M Y') : $latestPt->created_at->format('d M Y') }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-black text-slate-900 dark:text-white">
                                        🎯 {{ $latestPt->focus_area ?? 'Strength Training' }} ({{ $latestPt->duration_minutes ?? 60 }}m)
                                    </span>
                                    @if($latestPt->rating)
                                        <span class="text-amber-400 text-xs">
                                            @for($i = 1; $i <= $latestPt->rating; $i++)★@endfor
                                        </span>
                                    @endif
                                </div>
                                @if(!empty($latestPt->member_feedback))
                                    <p class="text-[11px] text-slate-400 italic">“{{ $latestPt->member_feedback }}”</p>
                                @endif
                            </div>
                        @endif
                    </div>
                @endif

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

                <!-- Gym Feedback & Suggestions Desk Card -->
                <div class="rounded-3xl bg-gradient-to-br from-amber-500/10 via-slate-900/60 to-slate-900 border border-amber-500/20 p-5 sm:p-6 shadow-sm space-y-3">
                    <div class="flex items-start justify-between gap-3">
                        <div class="space-y-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-base">💬</span>
                                <h3 class="text-sm font-bold text-slate-900 dark:text-white">Gym Feedback &amp; Suggestions</h3>
                                <span class="px-2 py-0.5 rounded-full text-[9px] font-bold bg-amber-500/15 text-amber-500 border border-amber-500/25">Direct to Owner</span>
                            </div>
                            <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">
                                Have an idea, maintenance issue, or coaching feedback? Your suggestions go directly to gym management.
                            </p>
                        </div>
                    </div>
                    <div class="pt-1">
                        <a href="{{ route('app.feedback.index') }}" class="w-full py-2.5 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs transition-all flex items-center justify-center gap-1.5 shadow-xs cursor-pointer">
                            <span>+ Share Feedback or Suggestion</span>
                            <span>&rarr;</span>
                        </a>
                    </div>
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
                    <span class="text-[11px] font-semibold text-slate-500 dark:text-slate-400">
                        {{ $thisMonthCheckins }} visits this month
                    </span>
                </div>

                <!-- MOTIVATIONAL ATTENDANCE & CONSISTENCY HERO WIDGET -->
                <div class="rounded-3xl bg-gradient-to-br from-slate-900 via-slate-900 to-indigo-950/40 border border-slate-800 p-5 shadow-lg space-y-4 text-white relative overflow-hidden">
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

                <!-- PERSONAL TRAINING SESSIONS HISTORY (DATE-WISE TIMELINE) -->
                <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                    <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500/10 text-amber-500 flex items-center justify-center text-lg font-black shrink-0 border border-amber-500/20">
                                ⚡
                            </div>
                            <div>
                                <h3 class="text-base font-black text-slate-900 dark:text-white">PT Sessions History</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Personal training sessions logged with your coach</p>
                            </div>
                        </div>
                        <span class="px-2.5 py-1 rounded-xl text-xs font-black bg-amber-500/10 text-amber-500 dark:text-amber-400 border border-amber-500/20">
                            {{ $ptSessionsHistory->count() }} {{ Str::plural('Session', $ptSessionsHistory->count()) }}
                        </span>
                    </div>

                    @if($ptSessionsHistory->isNotEmpty())
                        <div class="space-y-3">
                            @foreach($ptSessionsHistory as $session)
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950/70 border border-slate-200/80 dark:border-slate-800 hover:border-amber-500/40 transition-all space-y-3">
                                    <!-- Top Row: Date, Time & Verified Badge -->
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-10 h-10 rounded-xl bg-amber-500/15 text-amber-500 border border-amber-500/30 flex flex-col items-center justify-center shrink-0">
                                                <span class="text-[9px] font-black uppercase leading-none">{{ $session->session_date ? $session->session_date->format('M') : $session->created_at->format('M') }}</span>
                                                <span class="text-sm font-black leading-none mt-0.5">{{ $session->session_date ? $session->session_date->format('d') : $session->created_at->format('d') }}</span>
                                            </div>
                                            <div>
                                                <div class="text-xs font-black text-slate-900 dark:text-white">
                                                    {{ $session->session_date ? $session->session_date->format('l, d M Y') : $session->created_at->format('l, d M Y') }}
                                                </div>
                                                <div class="text-[11px] text-slate-400 font-mono mt-0.5 flex items-center gap-1.5 flex-wrap">
                                                    <span>🕒 {{ $session->confirmed_at ? $session->confirmed_at->format('h:i A') : ($session->start_time ? \Carbon\Carbon::parse($session->start_time)->format('h:i A') : 'Recorded') }}</span>
                                                    <span>•</span>
                                                    <span>⏱️ {{ $session->duration_minutes ?? 60 }} mins</span>
                                                </div>
                                            </div>
                                        </div>
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-black uppercase bg-emerald-500/15 text-emerald-500 dark:text-emerald-400 border border-emerald-500/30 flex items-center gap-1 shrink-0">
                                            <span>✓</span>
                                            <span>Confirmed</span>
                                        </span>
                                    </div>

                                    <!-- Middle Row: Coach & Workout Focus -->
                                    <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-200/60 dark:border-slate-800/80 text-xs">
                                        <div class="space-y-0.5">
                                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Coach</span>
                                            <div class="font-bold text-amber-500 flex items-center gap-1.5 truncate">
                                                <div class="w-4 h-4 rounded-full bg-amber-500/20 text-amber-500 text-[9px] font-black flex items-center justify-center shrink-0">
                                                    {{ substr($session->trainer?->first_name ?? 'C', 0, 1) }}
                                                </div>
                                                <span class="truncate">{{ $session->trainer?->full_name ?? 'Assigned Coach' }}</span>
                                            </div>
                                        </div>
                                        <div class="space-y-0.5">
                                            <span class="text-[10px] text-slate-400 uppercase tracking-wider font-semibold">Focus Area</span>
                                            <div class="font-bold text-slate-800 dark:text-slate-200 truncate">
                                                🎯 {{ $session->focus_area ?? 'Strength Training' }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bottom Row: Package & Star Rating -->
                                    <div class="flex items-center justify-between text-xs pt-2 border-t border-slate-200/40 dark:border-slate-800/60 flex-wrap gap-2">
                                        <div class="text-[11px] text-slate-400 truncate max-w-[200px]">
                                            <span class="text-slate-500">Plan:</span>
                                            <span class="font-semibold text-slate-700 dark:text-slate-300">{{ $session->memberPtPackage?->package_name ?? 'Personal Training' }}</span>
                                        </div>
                                        @if($session->rating)
                                            <div class="flex items-center gap-1 shrink-0">
                                                <div class="flex text-amber-400 text-xs">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <span>{{ $i <= $session->rating ? '★' : '☆' }}</span>
                                                    @endfor
                                                </div>
                                                <span class="text-[10px] font-bold text-amber-500 dark:text-amber-400 ml-0.5">{{ $session->rating }}/5</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Member Feedback Quote (if present) -->
                                    @if(!empty($session->member_feedback))
                                        <div class="p-2.5 rounded-xl bg-slate-900/90 dark:bg-slate-900/90 border border-slate-800 text-[11px] text-slate-300 italic flex items-start gap-1.5">
                                            <span class="text-amber-400 text-xs shrink-0">“</span>
                                            <span class="flex-1">{{ $session->member_feedback }}</span>
                                            <span class="text-amber-400 text-xs shrink-0">”</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-8 text-center text-xs text-slate-400 bg-slate-50 dark:bg-slate-950/50 rounded-2xl space-y-2">
                            <div class="text-3xl">⚡</div>
                            <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">No PT sessions confirmed yet</p>
                            <p class="text-[11px] text-slate-500 dark:text-slate-400 max-w-xs mx-auto leading-relaxed">When your coach logs a personal training session and you confirm via QR or Session PIN, your full session history will appear here date-wise.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 5: MEMBERSHIP, PASS & BILLS -->
            <div x-show="tab === 'memberships'" x-cloak class="space-y-4">
                <!-- HERO: LUXURY DIGITAL MEMBERSHIP PASS (APP CARD) -->
                <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-900 via-slate-850 to-slate-950 border border-amber-500/25 shadow-2xl p-5 sm:p-6 text-white space-y-5">
                    <div class="absolute -top-16 -right-16 w-60 h-60 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>
                    <div class="absolute -bottom-16 -left-16 w-60 h-60 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <!-- Card Top: Gym Branding & Status Badge -->
                    <div class="relative z-10 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            @if(!empty($resolvedGymLogo))
                                <img src="{{ $resolvedGymLogo }}" alt="{{ $gymName }}" class="h-6 w-auto max-w-[80px] rounded-md object-contain bg-white/10 p-0.5">
                            @else
                                <span class="w-6 h-6 rounded-lg bg-amber-400 text-slate-950 font-black text-[10px] flex items-center justify-center shrink-0">
                                    {{ strtoupper(substr($gymName, 0, 2)) }}
                                </span>
                            @endif
                            <span class="text-xs font-black tracking-widest uppercase text-amber-400">
                                {{ $gymName }}
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
                            <span>Scan QR / PT</span>
                        </button>

                        <button type="button" 
                                @click="showQrModal = true" 
                                class="py-3 px-3 rounded-2xl bg-white/10 hover:bg-white/15 active:scale-[0.98] border border-white/15 text-white font-black text-xs shadow-md flex items-center justify-center gap-2 transition-all cursor-pointer">
                            <svg class="w-4 h-4 text-amber-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                            <span>Digital Pass</span>
                        </button>
                    </div>
                </div>

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

                <!-- Personal Training Packages -->
                @if($member->memberPtPackages->isNotEmpty())
                    <div class="rounded-3xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 p-5 sm:p-6 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-black text-slate-900 dark:text-white">Personal Training Packages</h3>
                            <button type="button" @click="setPortalTab('attendance')" class="text-xs font-bold text-amber-500 hover:underline cursor-pointer">
                                PT History &rarr;
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($member->memberPtPackages as $ptPkg)
                                <div class="p-4 rounded-2xl bg-slate-50 dark:bg-slate-950 border border-slate-200/80 dark:border-slate-800 space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="text-xs text-slate-400 font-medium">PT Package</span>
                                            <div class="text-base font-black text-slate-900 dark:text-white">{{ $ptPkg->package_name }}</div>
                                            <div class="text-xs text-amber-500 font-bold mt-0.5">Coach {{ $ptPkg->trainer?->full_name ?? ($member->assignedTrainer?->full_name ?? 'Assigned Coach') }}</div>
                                        </div>
                                        <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black uppercase {{ $ptPkg->status === 'ACTIVE' ? 'bg-emerald-500/10 text-emerald-500 border border-emerald-500/20' : 'bg-slate-700/50 text-slate-300' }}">
                                            {{ $ptPkg->status }}
                                        </span>
                                    </div>

                                    <div class="w-full bg-slate-200 dark:bg-slate-800 rounded-full h-2 overflow-hidden">
                                        <div class="bg-gradient-to-r from-amber-500 to-emerald-500 h-2 rounded-full" style="width: {{ $ptPkg->total_sessions > 0 ? min(100, round(($ptPkg->used_sessions / $ptPkg->total_sessions) * 100)) : 0 }}%"></div>
                                    </div>

                                    <div class="flex justify-between text-xs pt-1 border-t border-slate-200/60 dark:border-slate-800">
                                        <span class="text-slate-400">Sessions:</span>
                                        <span class="font-bold text-emerald-500">{{ $ptPkg->remaining_sessions }} Left <span class="text-slate-400 font-normal">({{ $ptPkg->used_sessions }} / {{ $ptPkg->total_sessions }} completed)</span></span>
                                    </div>

                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-400">Validity:</span>
                                        <span class="font-bold text-slate-800 dark:text-slate-200">{{ $ptPkg->start_date?->format('d M Y') }} &rarr; {{ $ptPkg->end_date?->format('d M Y') }}</span>
                                    </div>

                                    <div class="flex justify-between text-xs">
                                        <span class="text-slate-400">Total Fee:</span>
                                        <span class="font-bold text-slate-900 dark:text-white">{{ $currency }}{{ number_format($ptPkg->final_amount, 2) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

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

            <!-- ========================================================= -->
            <!-- DUAL-MODE SMART SCANNER MODAL (SCAN QR OR ENTER SESSION PIN) -->
            <!-- ========================================================= -->
            <div x-show="showPortalScanner" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm">
                <div @click.away="if (!pinSubmitting && !pinSuccess) stopPortalScanner()" class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-5 text-center space-y-4 relative text-white">
                    <template x-if="!pinSubmitting && !pinSuccess">
                        <button type="button" @click="stopPortalScanner()" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    </template>
                    
                    <!-- SUCCESS STATE INSIDE SCANNER -->
                    <template x-if="pinSuccess">
                        <div class="py-6 space-y-3">
                            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-3xl mx-auto shadow-lg animate-bounce">
                                ✓
                            </div>
                            <h3 class="text-lg font-black text-white">Session Confirmed!</h3>
                            <p class="text-xs text-slate-300 max-w-xs mx-auto" x-text="pinSuccessMsg"></p>
                            <template x-if="activePendingPt?.remaining_sessions !== undefined">
                                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs inline-block">
                                    <span class="text-slate-400">Remaining Balance:</span>
                                    <span class="font-black text-amber-400 ml-1" x-text="activePendingPt.remaining_sessions + ' Sessions Left'"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- SCANNER / PIN FORM -->
                    <template x-if="!pinSuccess">
                        <div class="space-y-4">
                            <div>
                                <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Gym &amp; PT Scanner</span>
                                <h3 class="text-base sm:text-lg font-black text-white mt-1.5">Scan QR or Enter PIN</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Point camera at Trainer's QR or Front Desk Standee</p>
                            </div>

                            <!-- Dual Toggle Tabs: Camera vs Session PIN -->
                            <div class="flex items-center p-1 rounded-xl bg-slate-800/80 border border-slate-700/80 text-xs font-bold">
                                <button type="button" 
                                        @click="switchScannerTab('camera')" 
                                        :class="scannerMode === 'camera' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white'" 
                                        class="flex-1 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>📷 Camera Scan</span>
                                </button>
                                <button type="button" 
                                        @click="switchScannerTab('code')" 
                                        :class="scannerMode === 'code' ? 'bg-amber-500 text-slate-950 shadow-sm' : 'text-slate-400 hover:text-white'" 
                                        class="flex-1 py-1.5 rounded-lg transition-all cursor-pointer flex items-center justify-center gap-1.5">
                                    <span>🔢 Session PIN</span>
                                </button>
                            </div>

                            <!-- TAB A: CAMERA SCANNER -->
                            <div x-show="scannerMode === 'camera'" class="space-y-3">
                                <div x-show="cameraStarting" class="py-8 text-center text-xs text-amber-400 animate-pulse font-bold flex items-center justify-center gap-2">
                                    <svg class="animate-spin w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                    <span>Starting Camera...</span>
                                </div>

                                <div id="portal-camera-reader" class="w-full aspect-square max-w-[260px] mx-auto rounded-2xl overflow-hidden bg-black border border-slate-700 shadow-inner"></div>

                                <!-- Camera Error / HTTP Warning Notice -->
                                <div x-show="cameraError || portalScanMsg" class="p-3 rounded-2xl bg-amber-500/10 border border-amber-500/30 text-amber-300 text-xs text-left space-y-2">
                                    <p class="text-[11px] leading-relaxed" x-text="portalScanMsg || 'Live camera access may be restricted by browser permissions or require HTTPS.'"></p>
                                    <div class="flex items-center gap-2 pt-1">
                                        <button type="button" @click="switchScannerTab('code')" class="flex-1 py-2 rounded-xl bg-amber-500 text-slate-950 font-black text-[11px] cursor-pointer hover:bg-amber-400 transition-all text-center">
                                            🔢 Enter Session PIN
                                        </button>
                                        <label for="portal-file-input" class="py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-700 border border-slate-700 text-white font-bold text-[11px] cursor-pointer text-center">
                                            📷 Snap Photo
                                        </label>
                                    </div>
                                </div>

                                <input type="file" id="portal-file-input" accept="image/*" capture="environment" class="hidden" @change="scanQrFile($event)">
                                <p class="text-[11px] text-slate-400">Position the QR code inside the frame to scan automatically.</p>
                            </div>

                            <!-- TAB B: 6-DIGIT SESSION PIN DIRECT CONFIRM -->
                            <div x-show="scannerMode === 'code'" class="space-y-3 py-1">
                                <div class="p-3.5 rounded-2xl bg-slate-950/60 border border-slate-800 space-y-2 text-left">
                                    <label class="block text-xs font-bold text-slate-300">Enter 6-Digit Session PIN</label>
                                    <p class="text-[11px] text-slate-400 leading-relaxed">Type the 6-digit PIN displayed on your trainer's screen (e.g. 210289).</p>
                                    <input type="text" 
                                           x-model="manualOtpCode" 
                                           maxlength="6" 
                                           placeholder="e.g. 210289" 
                                           class="w-full px-4 py-3 rounded-xl bg-slate-900 border border-amber-500/40 text-amber-400 font-mono font-black text-2xl text-center tracking-[0.3em] focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400">
                                </div>

                                <!-- Quick Star Rating in PIN tab -->
                                <div class="space-y-1 pt-1">
                                    <label class="block text-[11px] font-bold text-slate-300">Rate Today's Workout</label>
                                    <div class="flex items-center justify-center gap-2 text-2xl">
                                        <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                            <button type="button" 
                                                    @click="ptRating = star" 
                                                    class="cursor-pointer transition-transform hover:scale-125 focus:outline-none">
                                                <span :class="star <= ptRating ? 'text-amber-400 drop-shadow' : 'text-slate-600'">★</span>
                                            </button>
                                        </template>
                                    </div>
                                </div>

                                <!-- Quick Feedback Chips in PIN tab -->
                                <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                    <button type="button" @click="ptFeedback = 'Pushed to limits, great pump!'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">🔥 Great Pump</button>
                                    <button type="button" @click="ptFeedback = 'Focused on correct form & mobility'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">👌 Good Form</button>
                                    <button type="button" @click="ptFeedback = 'High intensity workout session'" class="px-2 py-0.5 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">🏃 Intense</button>
                                </div>

                                <!-- Inline Error Banner -->
                                <div x-show="pinErrorMsg" class="p-2.5 rounded-xl bg-rose-500/15 border border-rose-500/30 text-rose-300 text-xs font-semibold text-center" x-text="pinErrorMsg"></div>

                                <button type="button" 
                                        @click="submitPinConfirm()" 
                                        :disabled="pinSubmitting"
                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:brightness-110 active:scale-[0.98] text-slate-950 font-black text-xs shadow-lg shadow-amber-500/25 cursor-pointer transition-all disabled:opacity-50 flex items-center justify-center gap-2">
                                    <span x-show="!pinSubmitting">✓ Confirm with Session PIN</span>
                                    <span x-show="pinSubmitting" class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Verifying PIN...</span>
                                    </span>
                                </button>
                            </div>

                            <button type="button" @click="stopPortalScanner()" class="w-full py-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-bold text-xs cursor-pointer">
                                Cancel
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            <!-- ========================================================= -->
            <!-- PT SESSION CONFIRMATION & RATING MODAL                    -->
            <!-- ========================================================= -->
            <div x-show="showPtConfirmModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/85 backdrop-blur-sm">
                <div @click.away="if (!ptConfirmLoading && !ptConfirmSuccess) showPtConfirmModal = false" class="w-full max-w-sm rounded-3xl bg-slate-900 border border-slate-800 shadow-2xl p-5 sm:p-6 text-center space-y-4 relative text-white">
                    <template x-if="!ptConfirmLoading && !ptConfirmSuccess">
                        <button type="button" @click="showPtConfirmModal = false" class="absolute top-4 right-4 text-slate-400 hover:text-white text-lg cursor-pointer">&times;</button>
                    </template>

                    <!-- Success State -->
                    <template x-if="ptConfirmSuccess">
                        <div class="py-4 space-y-3">
                            <div class="w-16 h-16 rounded-full bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 flex items-center justify-center text-3xl mx-auto shadow-lg animate-bounce">
                                ✓
                            </div>
                            <h3 class="text-lg font-black text-white">Session Confirmed!</h3>
                            <p class="text-xs text-slate-300 max-w-xs mx-auto" x-text="ptSuccessMsg"></p>
                            <template x-if="activePendingPt?.remaining_sessions !== undefined">
                                <div class="p-3 rounded-2xl bg-slate-950 border border-slate-800 text-xs inline-block">
                                    <span class="text-slate-400">Remaining Balance:</span>
                                    <span class="font-black text-amber-400 ml-1" x-text="activePendingPt.remaining_sessions + ' Sessions Left'"></span>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Interactive Confirmation Form -->
                    <template x-if="!ptConfirmSuccess">
                        <div class="space-y-4">
                            <!-- Header with Coach details -->
                            <div class="space-y-1">
                                <span class="px-3 py-1 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-black uppercase tracking-wider">Workout Completed</span>
                                <h3 class="text-lg font-black text-white mt-1">Confirm PT Session</h3>
                                <p class="text-xs text-slate-400">With <span class="font-bold text-amber-400" x-text="activePendingPt?.trainer_name || 'Your Coach'"></span></p>
                            </div>

                            <!-- Session Summary Badge -->
                            <div class="p-3 rounded-2xl bg-slate-950/70 border border-slate-800 text-xs flex items-center justify-between text-left">
                                <div>
                                    <span class="font-bold text-white block" x-text="activePendingPt?.package_name || 'Personal Training'"></span>
                                    <span class="text-[11px] text-slate-400 block" x-text="activePendingPt?.focus_area || 'Strength Training'"></span>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="px-2 py-0.5 rounded-lg bg-emerald-500/10 text-emerald-400 font-bold text-[10px] border border-emerald-500/20 block" x-text="(activePendingPt?.duration_minutes || 60) + ' Mins'"></span>
                                    <span class="text-[10px] text-slate-400 block mt-0.5" x-text="activePendingPt?.remaining_sessions ? activePendingPt.remaining_sessions + ' Left' : ''"></span>
                                </div>
                            </div>

                            <!-- Star Rating (1 to 5 Stars) -->
                            <div class="space-y-1.5 pt-1">
                                <label class="block text-xs font-bold text-slate-300">Rate Today's Workout</label>
                                <div class="flex items-center justify-center gap-2 text-2xl">
                                    <template x-for="star in [1, 2, 3, 4, 5]" :key="star">
                                        <button type="button" 
                                                @click="ptRating = star" 
                                                class="cursor-pointer transition-transform hover:scale-125 focus:outline-none">
                                            <span :class="star <= ptRating ? 'text-amber-400 drop-shadow' : 'text-slate-600'">★</span>
                                        </button>
                                    </template>
                                </div>
                                <span class="text-[10px] text-slate-400 block" x-text="ptRating === 5 ? 'Excellent Workout! 🔥' : (ptRating === 4 ? 'Great Session! 💪' : (ptRating === 3 ? 'Good Workout 👍' : 'Challenging session'))"></span>
                            </div>

                            <!-- Quick Feedback Chips -->
                            <div class="flex items-center justify-center gap-1.5 flex-wrap">
                                <button type="button" @click="ptFeedback = 'Pushed to limits, great pump!'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">🔥 Great Pump</button>
                                <button type="button" @click="ptFeedback = 'Focused on correct form & mobility'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">👌 Good Form</button>
                                <button type="button" @click="ptFeedback = 'High intensity workout session'" class="px-2.5 py-1 rounded-lg text-[10px] font-semibold bg-slate-800 hover:bg-slate-700 text-slate-300 cursor-pointer">🏃 Intense</button>
                            </div>

                            <!-- Optional note textarea -->
                            <div>
                                <textarea x-model="ptFeedback" 
                                          rows="2" 
                                          placeholder="Optional coach feedback or workout note..." 
                                          class="w-full px-3 py-2 rounded-xl bg-slate-950 border border-slate-800 text-xs text-white focus:outline-none focus:border-amber-400 resize-none"></textarea>
                            </div>

                            <!-- Confirm & Deduct Button -->
                            <div class="pt-1">
                                <button type="button" 
                                        @click="submitPtConfirm()" 
                                        :disabled="ptConfirmLoading" 
                                        class="w-full py-3.5 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-amber-500 hover:brightness-110 active:scale-[0.98] text-slate-950 font-black text-xs shadow-xl shadow-amber-500/20 flex items-center justify-center gap-2 cursor-pointer transition-all disabled:opacity-50">
                                    <span x-show="!ptConfirmLoading">✓ Confirm &amp; Deduct 1 Session</span>
                                    <span x-show="ptConfirmLoading" class="flex items-center gap-1.5">
                                        <svg class="animate-spin w-4 h-4 text-slate-950" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                        <span>Confirming...</span>
                                    </span>
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

        @endif

    </div>
</x-app-layout>